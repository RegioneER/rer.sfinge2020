<?php

namespace SoggettoBundle\Controller;

use DocumentoBundle\Entity\TipologiaDocumento;
use SoggettoBundle\Entity\IncaricoPersona;
use SoggettoBundle\Entity\StatoIncarico;
use SoggettoBundle\Entity\TipoIncarico;
use SoggettoBundle\Form\DocumentiIncaricoType;
use SoggettoBundle\Form\Entity\DocumentiIncarico;
use SoggettoBundle\Form\Entity\RicercaIncaricati;
use SoggettoBundle\Form\Entity\RicercaIncaricatiGestione;
use SoggettoBundle\Form\Entity\RicercaPersonaIncaricabile;
use SoggettoBundle\Form\IncaricoType;
use SoggettoBundle\Form\RicercaPersonaIncaricabileType;
use SoggettoBundle\Ricerche\AttributiRicercaIncaricati;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\PaginaInfo;
use Symfony\Component\HttpFoundation\Request;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use SoggettoBundle\Form\Entity\RicercaOperatoriRichiesta;
use RichiesteBundle\Service\GestoreResponse;

class IncaricoConsultazioneController extends SoggettoBaseController {

    /**
     * @Template("SoggettoBundle:Incarico:elencoIncarichi.html.twig")
     * @Route("/lista/{sort}/{direction}/{page}", defaults={"sort" = "p.id", "direction" = "desc", "page" = "1"}, name="elenco_incarichi")
     * @PaginaInfo(titolo="Elenco incarichi",sottoTitolo="elenco delle persone incaricate per soggetto")
     * @Menuitem(menuAttivo = "elencoIncarichi")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco incarichi")})
     */
    public function elencoIncarichiAction(Request $request) {

        // if (!$this->puoVedereTuttiGliIncarichi()) {
        if ($this->isGranted("ROLE_UTENTE")) {
            $datiRicerca = new RicercaIncaricati();
            $soggettoSession = $request->getSession()->get(self::SESSIONE_SOGGETTO);
            $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
            if (is_null($soggetto)) {
                return $this->addErrorRedirect("Soggetto non specificato", "home");
            }
            $OpRich = $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->haIncaricoPersonaOpRichAttivo($soggetto, $this->getPersona()->getCodiceFiscale());
            if ($OpRich) {
                return $this->addErrorRedirect("Il ruolo di operatore progetto non è abilitato alla visualizzazione della sezione incarichi", "home");
            }
            $datiRicerca->setSoggettoId($soggetto->getId());
        } else {
            $datiRicerca = new RicercaIncaricatiGestione();
        }

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return array('incarichi' => $risultato["risultato"], "form_ricerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]);
    }

    /**
     * @Route("/elenco_incarichi_pulisci", name="elenco_incarichi_pulisci")
     */
    public function elencoIncarichiPulisciAction() {
        if ($this->isGranted("ROLE_UTENTE")) {
            $this->get("ricerca")->pulisci(new RicercaIncaricati());
        } else {
            $this->get("ricerca")->pulisci(new RicercaIncaricatiGestione());
        }
        return $this->redirectToRoute("elenco_incarichi");
    }

    /**
     * @Template("SoggettoBundle:Incarico:dettaglioIncarico.html.twig")
     * @Route("/dettaglio_incarico/{id_incarico}", name="dettaglio_incarico")
     * @PaginaInfo(titolo="Dettaglio incarico",sottoTitolo="dettaglio di un incarico associato ad un soggetto")
     * @Menuitem(menuAttivo = "elencoIncarichi")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco incarichi", route="elenco_incarichi"), @ElementoBreadcrumb(testo="Dettagli incarico")})
     * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" = "id_incarico"})
     * @ControlloAccesso(contesto="incaricoPersona", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" = "id_incarico"})
     */
    public function dettaglioIncaricoAction($id_incarico) {

        $em = $this->getEm();

        $incaricoPersona = $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->find($id_incarico);

        if (is_null($incaricoPersona)) {
            return $this->addErrorRedirect("Incarico non trovato", "elenco_incarichi");
        }

        $documento_incarico = new \SoggettoBundle\Entity\DocumentoIncarico();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();

        $isUtentePrincipale = false;
        $isAdmin = false;
        //Se sono un PA non devo fare la verifica isUtentePrincipale, perchè non ho nessun Soggetto in sessione
        if ($this->isGranted("ROLE_UTENTE")) {
            $isUtentePrincipale = $this->isUtentePrincipale();
        }
        if ($this->isGranted("ROLE_SUPER_ADMIN")) {
            $isAdmin = true;
        }

        $request = $this->getCurrentRequest();
        $lista_tipi = array();
        $lista_tipi[] = $this->trovaDaCostante("DocumentoBundle:TipologiaDocumento", TipologiaDocumento::DELEGA_DELEGATO);
        $opzioni_form['lista_tipi'] = $lista_tipi;

        $lr = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->getLegaleRappresentante($incaricoPersona->getSoggetto());
        if (is_null($lr)) {
            return $this->addErrorRedirect("Prima di inserire una delega occorre avere un legale rappresentante attivo", "elenco_incarichi");
        }
        foreach ($lr as $lrsingolo) {
            $opzioni_form["cf_firmatario"][] = $lrsingolo->getCodiceFiscale();
        }
        $opzioni_form["cf_firmatario"][] = $incaricoPersona->getIncaricato()->getCodiceFiscale();

        $opzioni_form["url"] = $this->generateUrl("elenco_incarichi");
        $form = $this->createForm(\SoggettoBundle\Form\DocumentoIncaricoType::class, $documento_incarico, $opzioni_form);

        $arrayOut = array(
            'incarico_persona' => $incaricoPersona,
            "is_amministratore" => $isUtentePrincipale || $isAdmin,
            "is_super_admin" => $isAdmin,
            "soggetto" => $incaricoPersona->getSoggetto(),
            "documenti_delega" => $incaricoPersona->getDocumentiIncarico()
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $documento_file = $documento_incarico->getDocumentoFile();
                    $this->container->get("documenti")->carica($documento_file);

                    $documento_incarico->setDocumentoFile($documento_file);
                    $documento_incarico->setIncarico($incaricoPersona);
                    $em->persist($documento_incarico);
                    $em->persist($incaricoPersona);
                    $em->flush();
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }
        $arrayOut["form"] = $form->createView();

        return $arrayOut;
    }

    /**
     * @Template("SoggettoBundle:Incarico:elencoOperatoriRichiesta.html.twig")
     * @Route("/lista/{sort}/{direction}/{page}", defaults={"sort" = "p.id", "direction" = "desc", "page" = "1"}, name="elenco_operatori_richiesta")
     * @PaginaInfo(titolo="Elenco incarichi",sottoTitolo="elenco delle persone incaricate per soggetto")
     * @Menuitem(menuAttivo = "elencoIncarichi")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco incarichi")})
     */
    public function elencoOperatoriRichiestaAction() {

        // if (!$this->puoVedereTuttiGliIncarichi()) {
        if ($this->isGranted("ROLE_UTENTE")) {
            $datiRicerca = new RicercaOperatoriRichiesta();
            $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
            $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
            if (is_null($soggetto)) {
                return $this->addErrorRedirect("Soggetto non specificato", "home");
            }

            $datiRicerca->setSoggettoId($soggetto->getId());
        } else {
            $datiRicerca = new RicercaIncaricatiGestione();
        }

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return array('incarichi' => $risultato["risultato"], "form_ricerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]);
    }

    /**
     * @Route("/elenco_operatori_richiesta_pulisci", name="elenco_operatori_richiesta_pulisci")
     */
    public function elencoOperatoriRichiestaPulisciAction() {
        if ($this->isGranted("ROLE_UTENTE")) {
            $this->get("ricerca")->pulisci(new RicercaOperatoriRichiesta());
        } else {
            $this->get("ricerca")->pulisci(new RicercaIncaricatiGestione());
        }
        return $this->redirectToRoute("elenco_incarichi");
    }

}
