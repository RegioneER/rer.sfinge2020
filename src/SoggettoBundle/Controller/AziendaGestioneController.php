<?php

namespace SoggettoBundle\Controller;

use AnagraficheBundle\Entity\Persona;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SoggettoBundle\Entity\Adrier;
use SoggettoBundle\Entity\AdrierPersona;
use SoggettoBundle\Entity\Azienda;
use SoggettoBundle\Entity\TipoIncarico;
use SoggettoBundle\Entity\StatoIncarico;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use Symfony\Component\Form\FormError;
use BaseBundle\Annotation\ControlloAccesso;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SoggettoBundle\Form\Entity\NuovaAzienda;
use SoggettoBundle\Form\NuovaAziendaType;
use function is_null;

/**
 * Class AziendaGestioneController
 */
class AziendaGestioneController extends SoggettoBaseController {

    /**
     * @Route("/crea_azienda", name="crea_azienda")
     * @Menuitem(menuAttivo = "creaSoggettoGiuridico")
     * @PaginaInfo(titolo="Nuova Azienda",sottoTitolo="")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco aziende", route="elenco_aziende"), @ElementoBreadcrumb(testo="Crea azienda")})
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws Exception
     */
    public function creaAziendaAction(Request $request): Response {
        $em = $this->getDoctrine()->getManager();

        $formModel = new NuovaAzienda();

        try {
            if ($request->query->has('codice_fiscale')) {
                $codiceFiscaleAzienda = $request->query->get('codice_fiscale');
                $formModel->azienda->setCodiceFiscale($codiceFiscaleAzienda);

                /** @var Adrier $adrier */
                $adrier = $this->container->get('app.adrier_service')->dettaglioAdrier($codiceFiscaleAzienda, 'DettaglioCompletoImpresa');
                if (!is_null($adrier) && 'OK' === $adrier->getHeader()->getEsito()) {
                    $datiImpresa = $adrier->getDati()->getDatiImpresa();
                    $formModel->azienda->setCodiceFiscale($datiImpresa->getEstremiImpresa()->getCodiceFiscale());
                    $formModel->azienda->setDenominazione($datiImpresa->getEstremiImpresa()->getDenominazione());
                    $formModel->azienda->setPartitaIva($datiImpresa->getEstremiImpresa()->getPartitaIva());
                    $formModel->azienda->setCodiceAteco($this->getCodiceAtecoDaAdrier($datiImpresa->getInformazioniSede()->getAtecoPrincipale()));
                    $formModel->azienda->setCodiceAtecoSecondario($this->getCodiceAtecoDaAdrier($datiImpresa->getInformazioniSede()->getAtecoSecondario()));
                    $formModel->azienda->setDataCostituzione($datiImpresa->getDurataSocieta()->getDtCostituzione());
                    $formModel->azienda->setFormaGiuridica($this->getFormaGiuridicaDaAdrier($datiImpresa->getEstremiImpresa()->getFormaGiuridica()->getDescrizione()));
                    $formModel->azienda->setCcia($this->getRegistroCciaaDiDaAdrier($datiImpresa->getEstremiImpresa()->getDatiIscrizioneRea()->getCciaa()));
                    $formModel->azienda->setRea($datiImpresa->getEstremiImpresa()->getDatiIscrizioneRea()->getNRea());
                    $formModel->azienda->setDataRea($datiImpresa->getEstremiImpresa()->getDatiIscrizioneRea()->getData());
                    $formModel->azienda->setVia($datiImpresa->getInformazioniSede()->getIndirizzo()->getVia());
                    $formModel->azienda->setCivico($datiImpresa->getInformazioniSede()->getIndirizzo()->getNCivico());

                    // da verificare perchè non ho trovato aziende con sede legale all'estero per poter fare un test
                    $formModel->azienda->setStato($this->getStatoSedeDaAdrier($datiImpresa->getInformazioniSede()->getIndirizzo()->getCComune()));

                    $formModel->azienda->setComune($this->getComuneSedeDaAdrier($datiImpresa->getInformazioniSede()->getIndirizzo()->getCComune()));
                    $formModel->azienda->setCap($datiImpresa->getInformazioniSede()->getIndirizzo()->getCap());
                    $formModel->azienda->setLocalita(implode(" - ", [$datiImpresa->getInformazioniSede()->getIndirizzo()->getFrazione(), $datiImpresa->getInformazioniSede()->getIndirizzo()->getAltreIndicazioni()]));
                    $formModel->azienda->setTel($datiImpresa->getInformazioniSede()->getIndirizzo()->getTelefono());
                    $formModel->azienda->setFax($datiImpresa->getInformazioniSede()->getIndirizzo()->getFax());
                    $formModel->azienda->setEmailPec($datiImpresa->getInformazioniSede()->getIndirizzo()->getIndirizzoPec());

                    if ($datiImpresa->getLegaleRappresentante() instanceof AdrierPersona) {
                        $formModel->legaleRappresentante->setNome($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getNome());
                        $formModel->legaleRappresentante->setCognome($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getCognome());
                        $formModel->legaleRappresentante->setSesso($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getSesso());

                        $formModel->legaleRappresentante->setCodiceFiscale($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getCodiceFiscale());
                        $formModel->legaleRappresentante->setNazionalita($this->getStatoLrDaAdrier($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getEstremiNascita()->getStato(), $formModel->legaleRappresentante->getCodiceFiscale()));
                        $formModel->legaleRappresentante->setDataNascita($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getEstremiNascita()->getData());
                        $formModel->legaleRappresentante->setStatoNascita($this->getStatoLrDaAdrier($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getEstremiNascita()->getStato(), $formModel->legaleRappresentante->getCodiceFiscale()));
                        $formModel->legaleRappresentante->setComune($this->getComuneLrDaAdrier($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getEstremiNascita()->getComune(), $formModel->legaleRappresentante->getCodiceFiscale()));
                    }
                }
                $codiceFiscaleLegaleRappresentante = $formModel->legaleRappresentante->getCodiceFiscale();
                if ($codiceFiscaleLegaleRappresentante) {
                    // Collego persona se già presente a sistema
                    $personaDB = $em->getRepository(Persona::class)->findOneBy([
                        'codice_fiscale' => $codiceFiscaleLegaleRappresentante,
                    ]);
                    if ($personaDB) {
                        $formModel->legaleRappresentante = $personaDB;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->addFlash('error', "Il servizio Adrier non risponde, proseguire con la compilazione manuale dei dati");
            $this->get("logger")->error($e->getMessage());
        }

        $options["url_indietro"] = $request->headers->get('referer');
        $options["tipo"] = $request->query->get('tipo');

        $form = $this->createForm(NuovaAziendaType::class, $formModel, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // Controlli post submit
            $formAzienda = $form->get('azienda');
            $piva_presente = $em->getRepository("SoggettoBundle:Soggetto")->isCodicePresente('piva', $formAzienda->get('partita_iva')->getData());
            if ($piva_presente) {
                $formAzienda->get('partita_iva')->addError(new FormError('Partita IVA già presente a sistema'));
            }
            $cf_presente = $em->getRepository("SoggettoBundle:Soggetto")->isCodicePresente('cf', $formAzienda->get('codice_fiscale')->getData());
            if ($cf_presente) {
                $formAzienda->get('codice_fiscale')->addError(new FormError('Codice fiscale già presente a sistema'));
            }
            $a = $formAzienda->get('senza_piva')->getData();
            $b = $formAzienda->get('partita_iva')->getData();
            if (!is_null($formAzienda->get('partita_iva')->getData()) && $formAzienda->get('senza_piva')->getData()) {
                $formAzienda->get('senza_piva')->addError(new FormError('Non è possibile apporre il flag se la partita iva è presente'));
            }

            if ($form->isValid()) {
                $codice_organismo = $em->getRepository("SoggettoBundle:Soggetto")->getMaxCodiceOrganismo() + 1;
                $formModel->azienda->setCodiceOrganismo($codice_organismo);
                try {
                    $legaleRappresentanteDaDB = $em->getRepository(Persona::class)->findOneBy([
                        'codice_fiscale' => $formModel->legaleRappresentante->getCodiceFiscale(),
                    ]);
                    if ($legaleRappresentanteDaDB) {
                        $legaleRappresentanteDaDB->mergeData($formModel->legaleRappresentante);
                        $formModel->legaleRappresentante = $legaleRappresentanteDaDB;
                    }

                    $tipoIncaricoLegaleRappresentante = $this->trovaDaCostante("SoggettoBundle:TipoIncarico", TipoIncarico::LR);
                    $statoAttivo = $this->trovaDaCostante("SoggettoBundle:StatoIncarico", StatoIncarico::ATTIVO);
                    $incaricoLegaleRappresentante = $formModel->azienda->incarica($formModel->legaleRappresentante, $tipoIncaricoLegaleRappresentante, $statoAttivo);
                    $em->persist($formModel->azienda);
                    $em->persist($formModel->legaleRappresentante);
                    $em->persist($incaricoLegaleRappresentante);

                    $tipoIncaricoUtentePrincipale = $this->trovaDaCostante("SoggettoBundle:TipoIncarico", TipoIncarico::UTENTE_PRINCIPALE);
                    $incaricoUtentePrincipale = $formModel->azienda->incarica($this->getPersona(), $tipoIncaricoUtentePrincipale, $statoAttivo);
                    $em->persist($incaricoUtentePrincipale);

                    $this->aggiungiSedeLegaleAlleSedi($formModel->azienda, $em);

                    $em->flush();

                    $this->addFlash('success', "Modifiche salvate correttamente");
                    return $this->redirect($this->generateUrl('elenco_soggetti_giuridici'));
                } catch (Exception $e) {
                    $this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
                    $this->get("logger")->error($e->getMessage());
                }
            }
        }

        $form_params = ["form" => $form->createView()];
        return $this->render('@Soggetto/Soggetto/nuovaAzienda.html.twig', $form_params);
    }

    /**
     * @Route("/azienda_modifica/{id_soggetto}", name="azienda_modifica")
     * @Menuitem(menuAttivo = "elencoSoggettiGiuridici")
     * @PaginaInfo(titolo="Modifica Azienda",sottoTitolo="pagina per modificare i dati dell'azienda selezionata")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco aziende", route="elenco_aziende"), @ElementoBreadcrumb(testo="Modifica azienda")})
     * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="edit")
     * @ParamConverter("azienda", options={"id" = "id_soggetto"})
     */
    public function modificaAziendaAction(Request $request, Azienda $azienda): Response {
        $em = $this->getDoctrine()->getManager();

        $options = [
            "readonly" => false,
            "url_indietro" => $this->generateUrl("elenco_aziende"),
            "tipo" => $azienda->getTipoByFormaGiuridica(),
        ];

        $form = $this->createForm('SoggettoBundle\Form\AziendaType', $azienda, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            $piva_presente = $em->getRepository("SoggettoBundle:Soggetto")->isCodicePresente('piva', $form['partita_iva']->getData(), $azienda);
            $cf_presente = $em->getRepository("SoggettoBundle:Soggetto")->isCodicePresente('cf', $form['codice_fiscale']->getData(), $azienda);

            if ($piva_presente) {
                $form->get('partita_iva')->addError(new FormError('Partita IVA già presente a sistema'));
            } else if ($cf_presente) {
                $form->get('codice_fiscale')->addError(new FormError('Codice fiscale già presente a sistema'));
            }
            $a = $form->get('senza_piva')->getData();
            $b = $form->get('partita_iva')->getData();
            if (!is_null($form->get('partita_iva')->getData()) && $form->get('senza_piva')->getData()) {
                $form->get('senza_piva')->addError(new FormError('Non è possibile apporre il flag se la partita iva è presente'));
            }

            if ($form->isValid()) {
                try {
                    $em->persist($azienda);
                    $em->flush();
                    $this->addFlash('success', "Modifiche salvate correttamente");

                    return $this->redirect($this->generateUrl('elenco_soggetti_giuridici'));
                } catch (Exception $e) {
                    $this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
                    $this->get("logger")->error($e->getMessage());
                }
            }
        } else {
            if (!$azienda->isFormaGiuridicaCoerente()) {
                $this->addFlash('warning', 'Attenzione! La forma giuridica indicata potrebbe non essere corretta.');
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["azienda"] = $azienda;

        return $this->render('SoggettoBundle:Soggetto:azienda.html.twig', $form_params);
    }

}
