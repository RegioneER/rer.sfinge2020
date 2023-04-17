<?php

namespace SoggettoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SoggettoBundle\Entity\Soggetto;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use SoggettoBundle\Entity\OrganismoIntermedio;
use SoggettoBundle\Entity\StatoIncarico;
use SoggettoBundle\Entity\TipoIncarico;
use Symfony\Component\Form\FormError;

class SoggettoGiuridicoGestioneController extends SoggettoBaseController
{

    /**
     * @Route("/crea_soggetto_giuridico", name="crea_soggetto_giuridico")
     * @Template("SoggettoBundle:Soggetto:soggettoGiuridico.html.twig")
     * @Menuitem(menuAttivo = "creaSoggettoGiuridico")
     * @PaginaInfo(titolo="Nuovo Soggetto Giuridico",sottoTitolo="")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Soggetti Giuridici", route="elenco_soggetti_giuridici"), @ElementoBreadcrumb(testo="Crea soggetto giuridico")})
     */
    public function creaSoggettoGiuridicoAction()
    {
        $em = $this->getDoctrine()->getManager();

        $azienda = new Soggetto();
        $request = $this->getCurrentRequest();

        $options["readonly"]     = false;
        $options["url_indietro"] = $this->generateUrl("elenco_soggetti_giuridici");

        $form = $this->createForm('SoggettoBundle\Form\SoggettoGiuridicoType', null, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();

                $soggetti_presenti = $em->getRepository('SoggettoBundle:Soggetto')->findBy(['codice_fiscale' => $data['codice_fiscale']]);

                $isLaboratorioRicerca = false;

                /** @var Soggetto $soggetto_presente */
                foreach ($soggetti_presenti as $soggetto_presente) {
                    if ($soggetto_presente->isLaboratorioRicerca()) {
                        $isLaboratorioRicerca = true;
                        break;
                    }
                }

                if ($data['tipo'] == Soggetto::UNIVERSITA && count($soggetti_presenti) > 0 && $soggetti_presenti[0] instanceof Soggetto && $isLaboratorioRicerca) {
                    return $this->redirectToRoute('crea_soggetto_giuridico_lab_check', [
                        'codice_fiscale' => $data['codice_fiscale'],
                        'tipo' => $data['tipo'],
                    ]);
                } 
                if ($data['tipo'] == Soggetto::UNIVERSITA && count($soggetti_presenti) > 0 && $soggetti_presenti[0] instanceof Soggetto && !$isLaboratorioRicerca) {
                    $form->get('codice_fiscale')->addError(new FormError('Codice fiscale già presente a sistema ed associato ad un soggetto giuridico NON classificato come ' . Soggetto::TESTO_UNIVERSITA));
                } 
                elseif (count($soggetti_presenti) > 0 && ! $soggetti_presenti[0] instanceof OrganismoIntermedio) {
                    $tipoIncaricoUtentePrincipale = $em->getRepository('SoggettoBundle:TipoIncarico')->findOneBy(['codice' => TipoIncarico::UTENTE_PRINCIPALE]);
                    $statoIncaricoUtentePrincipale = $em->getRepository('SoggettoBundle:StatoIncarico')->findOneBy(['codice' => StatoIncarico::ATTIVO]);
                    $incarichiUtentiPrincipali = $em->getRepository('SoggettoBundle:IncaricoPersona')
                        ->findBy(['soggetto' => $soggetti_presenti[0]->getId(), 'tipo_incarico' => $tipoIncaricoUtentePrincipale->getId(),
                            'stato' => $statoIncaricoUtentePrincipale->getId()]);

                    $messaggioSpiegazioneUtentePrincipale = '';
                    $utentiPrincipali = [];
                    foreach ($incarichiUtentiPrincipali as $incaricoUtentePrincipale) {
                        $utentiPrincipali[] = $incaricoUtentePrincipale->getIncaricato()->getNomeCognome();
                    }

                    if ($utentiPrincipali) {
                        if (count($utentiPrincipali) == 1) {
                            $congiunzione1 = 'L’utente principale attualmente attivo è ';
                            $congiunzione2 = 'la suddetta persona';
                        } else {
                            $congiunzione1 = 'Gli utenti principali attualmente attivi sono ';
                            $congiunzione2 = 'le suddette persone';
                        }

                        $utentiPrincipali = $this->container->get("funzioni_utili")->concatenazioneInLinguaggioNaturale($utentiPrincipali);

                        $messaggioSpiegazioneUtentePrincipale = 'Il soggetto che si sta cercando di censire è già presente a sistema,
                            se si vuole operare per tale soggetto è necessario contattare il suo utente principale per farsi incaricare.
                            ' . $congiunzione1 .  '<strong>' . $utentiPrincipali . '</strong>.
                            <br/>
                            Nel caso in cui non fosse possibile rintracciare ' . $congiunzione2. ' deve seguire la procedura riportata 
                            anche in home-page e che riportiamo di seguito:
                            <br/>
                            <br/>
                            RICHIESTA MODIFICA UTENTE PRINCIPALE COLLEGATO A UN SOGGETTO GIURIDICO
                            <br/>
                            <br/>
                            Si ricorda ai cortesi proponenti/beneficiari che, nel caso fosse necessario provvedere alla sostituzione dell’Utente Principale collegato ad un soggetto giuridico, la procedura da seguire è:
                            <br/>
                            <br/>
                            - aprire una SEGNALAZIONE attraverso il form in testa alla pagina di SFINGE2020;
                            <br/>
                            - allegare alla segnalazione la richiesta di modifica firmata dal Legale Rappresentante del soggetto giuridico.
                            <br/>
                            <br/>
                            Ricordiamo che la richiesta sarà conservata agli atti dalla Regione Emilia-Romagna, motivo per il quale consigliamo 
                            l’utilizzo della firma elettronica e, ove non altrimenti possibile, il ricorso alla firma autografa con allegato 
                            il documento di identità del Legale Rappresentante; in merito a quest’ultima modalità, 
                            segnaliamo che sarà verificata la coerenza tra le firme poste in calce al documento di identità 
                            e alla richiesta e saranno segnalate, per gli opportuni accertamenti, le situazioni in cui sarà 
                            riscontrabile una evidente anomalia.';
                    }
                    $this->addFlash('error', $messaggioSpiegazioneUtentePrincipale);
                    $form->get('codice_fiscale')->addError(new FormError('Codice fiscale già presente a sistema'));
                } else {
                    // cerco i dati in SAP

                    // rimando alla form di creazione vera e propria

                    switch ($data['tipo']) {
                        case Soggetto::PROFESSIONISTA:
                        case Soggetto::AZIENDA:
                            return $this->redirectToRoute('crea_azienda', [
                                'codice_fiscale' => $data['codice_fiscale'],
                                'tipo' => $data['tipo'],
                            ]);
                            break;
                        case Soggetto::COMUNE:
                            return $this->redirectToRoute('crea_comune_unione', [
                                'codice_fiscale' => $data['codice_fiscale'],
                                'tipo' => $data['tipo'],
                            ]);
                            break;
                        case Soggetto::ALTRI:
                        case Soggetto::UNIVERSITA:
                            return $this->redirectToRoute('crea_soggetto', [
                                'codice_fiscale'      => $data['codice_fiscale'],
                                'tipo' => isset($data['laboratorio_ricerca']) ? Soggetto::UNIVERSITA : $data['tipo'],
                            ]);
                            break;
                        case Soggetto::PERSONA_FISICA:
                            return $this->redirectToRoute('crea_persona_fisica', [
                                'codice_fiscale'      => $data['codice_fiscale'],
                                'tipo' => $data['tipo'],
                            ]);
                            break;
                    }
                }
            }
        }

        $form_params["form"]    = $form->createView();
        $form_params["azienda"] = $azienda;

        return $form_params;
    }

    /**
     * @Route("/crea_soggetto_giuridico_lab_check", name="crea_soggetto_giuridico_lab_check")
     * @Template("SoggettoBundle:Soggetto:elencoLaboratoriUniversitaPresenti.html.twig")
     * @Menuitem(menuAttivo = "creaSoggettoGiuridico")
     * @PaginaInfo(titolo="Nuovo Soggetto Giuridico",sottoTitolo="")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Soggetti Giuridici", route="elenco_soggetti_giuridici"), @ElementoBreadcrumb(testo="Crea soggetto giuridico")})
     */
    public function creaSoggettoGiuridicoLabCheckAction()
    {
        $em = $this->getDoctrine()->getManager();

        $azienda = new Soggetto();
        $request = $this->getCurrentRequest();

        if (null !== $request->query->get('codice_fiscale') && count($request->query->all()) > 0) {
            $data = $request->query->all();
            $azienda->setCodiceFiscale($data['codice_fiscale']);
            $azienda->setLaboratorioRicerca((bool) $data['tipo'] == Soggetto::UNIVERSITA);
        }

        $options["readonly"]     = false;
        $options["url_indietro"] = $this->generateUrl("crea_soggetto_giuridico");
        $options["data"] = $data ?? [];

        $form = $this->createForm('SoggettoBundle\Form\SoggettoGiuridicoCheckLabType', null, $options);

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $data = $form->getData();

                return $this->redirectToRoute('crea_soggetto', [
                    'codice_fiscale'      => $data['codice_fiscale'],
                    'tipo' => $data['tipo'],
                ]);
            }
        }

        $form_params["form"]    = $form->createView();
        $form_params["soggetti"] = $em->getRepository('SoggettoBundle:Soggetto')->findBy(['codice_fiscale' => $data['codice_fiscale']]);

        return $form_params;
    }
}
