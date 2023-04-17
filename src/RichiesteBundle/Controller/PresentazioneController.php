<?php

namespace RichiesteBundle\Controller;

use BaseBundle\Exception\SfingeException;
use Exception;
use RichiesteBundle\Entity\AmbitoTematicoS3Proponente;
use RichiesteBundle\Service\GestoreResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use RichiesteBundle\Ricerche\RicercaRichiestaLatoPA;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Service\GestoreObiettiviRealizzativiService;
use RichiesteBundle\Service\IGestoreObiettiviRealizzativi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use RichiesteBundle\Entity\ObiettivoRealizzativo;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Form\IncaricoPersonaRichiestaType;
use RichiesteBundle\Security\RichiestaVoter;
use RichiesteBundle\Service\IGestorePianoCosto;
use Symfony\Component\HttpFoundation\Request;
use SoggettoBundle\Form\IncaricoProgettoType;

/**
 * @Route("/common")
 */
class PresentazioneController extends AbstractController {
    /**
     * @Route("/elenco/{sort}/{direction}/{page}", defaults={"sort" : "i.id", "direction" : "asc", "page" : "1"}, name="elenco_richieste")
     * Template("RichiesteBundle:Richieste:elencoRichieste.html.twig")
     * @PaginaInfo(titolo="Elenco richieste", sottoTitolo="mostra l'elenco delle richieste presentate")
     * @Menuitem(menuAttivo="elencoRichieste")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco richieste")})
     */
    public function elencoRichiesteAction() {
        \ini_set('memory_limit', '256M');

        if ($this->isGranted("ROLE_UTENTE")) {
            $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
            $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
            if (is_null($soggetto)) {
                return $this->addErrorRedirect("Soggetto non valido", "home");
            }
            $utente = $this->getUser();
            $richieste = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->getRichiesteDaSoggetto($soggetto->getId());

            $richiesteOut = $this->valutaVisibilitaRichiesta($richieste, $soggetto, $utente);

            $response = $this->render("RichiesteBundle:Richieste:elencoRichieste.html.twig", ["richieste" => $richiesteOut]);
        } else {
            $datiRicerca = new RicercaRichiestaLatoPA();
            $datiRicerca->setUtente($this->getUser());

            $risultato = $this->get("ricerca")->ricerca($datiRicerca);

            $params = [
                'richieste' => $risultato["risultato"],
                "form_ricerca_richieste" => $risultato["form_ricerca"],
                "filtro_attivo" => $risultato["filtro_attivo"],
            ];
            $response = $this->render("RichiesteBundle:Richieste:elencoRichiestePa.html.twig", $params);
        }

        return $response;
    }

    /**
     * @Route("/elenco_richieste_pulisci", name="elenco_richieste_pulisci")
     */
    public function elencoRichiestePulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaRichiestaLatoPA());
        return $this->redirectToRoute("elenco_richieste");
    }

    /**
     * @Route("/{id_richiesta}/dettaglio", name="dettaglio_richiesta")
     * @PaginaInfo(titolo="Richiesta", sottoTitolo="pagina con le sezioni della richiesta da compilare")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function dettaglioRichiestaAction($id_richiesta) {
        $this->getSession()->set("id_richiesta", $id_richiesta);
        $response = $this->get("gestore_richieste")->getGestore()->dettaglioRichiesta($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/questionario/{id_istanza_pagina}/{id_pagina}/{id_istanza_frammento}/{azione}", name="questionario", defaults={"id_istanza_pagina" : "-", "id_pagina" : "-", "id_istanza_frammento" : "-", "azione" : "modifica"})
     * @param mixed $id_istanza_pagina
     * @param mixed $id_pagina
     * @param mixed $id_istanza_frammento
     * @param mixed $azione
     */
    public function questionarioAction(\Symfony\Component\HttpFoundation\Request $request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione) {
        if ("-" != $id_istanza_pagina) {
            $istanza_pagina = $this->getEm()->getRepository("FascicoloBundle\Entity\IstanzaPagina")->find($id_istanza_pagina);
        } else {
            $istanza_frammento = $this->getEm()->getRepository("FascicoloBundle\Entity\IstanzaFrammento")->find($id_istanza_frammento);
            $istanza_pagina = $istanza_frammento->getIstanzaPagina();
        }

        $istanza_fascicolo = $istanza_pagina->getIstanzaFascicolo();
        $oggetto_richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\OggettoRichiesta")->findOneBy(["istanza_fascicolo" => $istanza_fascicolo]);

        $richiesta = $oggetto_richiesta->getRichiesta();
        $id_richiesta = $richiesta->getId();

        $isRichiestaDisabilitata = $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isRichiestaDisabilitata($id_richiesta);

        if (!$this->isUtente() || $isRichiestaDisabilitata) {
            $azione = "visualizza";
        }

        $contestoSoggetto = $this->get('contesto')->getContestoRisorsa($richiesta, "soggetto");
        $accessoConsentito = $this->isGranted(\SoggettoBundle\Security\SoggettoVoter::ALL, $contestoSoggetto);

        $contestoProcedura = $this->get('contesto')->getContestoRisorsa($richiesta, "procedura");
        $accessoConsentito |= $this->isGranted(\SfingeBundle\Security\ProceduraVoter::READ, $contestoProcedura);

        if (!$accessoConsentito) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $this->container->get("pagina")->setMenuAttivo("elencoRichieste", $this->getSession());
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richieste", $this->generateUrl("elenco_richieste"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrl("dettaglio_richiesta", ["id_richiesta" => $id_richiesta]));

        $this->getSession()->set("fascicolo.route_istanza_pagina", "questionario");

        return $this->get("fascicolo.istanza")->istanzaPagina($request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione, $id_richiesta);
    }

    /**
     * @Route("/{id_richiesta}/dati_marca_da_bollo", name="dati_marca_da_bollo")
     * @PaginaInfo(titolo="Marca da bollo", sottoTitolo="pagina con i dati della marca da bollo della richiesta")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dati generali")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function datiMarcaDaBolloAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->datiMarcaDaBollo($id_richiesta);
        if ($response instanceof GestoreResponse) {
            return $response->getResponse();
        } else {
            return $response;
        }
    }

    /**
     * @Route("/{id_richiesta}/dati_generali", name="dati_generali")
     * @PaginaInfo(titolo="Dati generali", sottoTitolo="pagina con i dati generali della richiesta")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dati generali")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function datiGeneraliAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->datiGenerali($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elenco_proponenti", name="elenco_proponenti")
     * @PaginaInfo(titolo="Elenco proponenti", sottoTitolo="mostra l'elenco dei proponenti per la richiesta")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function elencoProponentiAction($id_richiesta) {
        $response = $this->get("gestore_proponenti")->getGestore()->elencoProponenti($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/professionista/{id_proponente}", name="dettaglio_professionista")
     * @PaginaInfo(titolo="Professionista", sottoTitolo="mostra del professionista proponente per la richiesta")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     *              @ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettaglio professionista")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function dettaglioProfessionista($id_richiesta, $id_proponente) {
        return $this->get("gestore_proponenti")
                    ->getGestore()
                    ->dettaglioProfessionista($id_richiesta, $id_proponente)
                    ->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/dettaglio_proponente/{id_proponente}", name="dettaglio_proponente")
     * @PaginaInfo(titolo="Dettaglio proponente", sottoTitolo="dettaglio di un proponente associato ad una richiesta")
     * @Template("RichiesteBundle:Richieste:dettaglioProponente.html.twig")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettaglio proponente")
     * 				})
     * @Method({"GET", "POST"})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function dettaglioProponenteAction($id_richiesta, $id_proponente) {
        $response = $this->get("gestore_proponenti")->getGestore()->dettagliProponente($id_proponente);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elenco_documenti_proponente/{id_proponente}", name="elenco_documenti_proponente")
     * @PaginaInfo(titolo="Elenco documenti proponente", sottoTitolo="documenti relativi ad un proponente associato ad una richiesta")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco documenti proponente")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function elencoDocumentiProponenteAction($id_richiesta, $id_proponente) {
        $response = $this->get("gestore_proponenti")->getGestore()->elencoDocumentiProponente($id_richiesta, $id_proponente);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/dettaglio_referente/{id_proponente}/{id_referente}", name="dettaglio_referente")
     * @PaginaInfo(titolo="Dettaglio referente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettaglio proponente", route="dettaglio_proponente", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Dettaglio referente")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Referente", opzioni={"id" : "id_referente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_referente
     */
    public function dettaglioReferenteAction($id_richiesta, $id_referente) {
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->dettagliReferente($id_referente);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/dettaglio_referente_intervento/{id_referente}", name="dettaglio_referente_intervento")
     * @PaginaInfo(titolo="Dettaglio referente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco interventi", route="elenco_interventi", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettaglio referente")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggettoMandatarioDaIntervento", classe="RichiesteBundle:Referente", opzioni={"id" : "id_referente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_referente
     */
    public function dettaglioReferenteInterventoAction($id_richiesta, $id_referente) {
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->dettagliReferenteIntervento($id_referente);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/piano_costi/{id_proponente}", name="piano_costi")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("proponente", options={"id" : "id_proponente"})
     * @param string $id_richiesta
     */
    public function pianoDeiCostiAction($id_richiesta, Proponente $proponente): Response {
        $em = $this->getDoctrine()->getManager();

        $procedura = $proponente->getRichiesta()->getProcedura();
        /** @var IGestorePianoCosto $gestorePianoCosti */
        $gestorePianoCosti = $this->get("gestore_piano_costo")->getGestore($procedura);

        if (0 == count($proponente->getVociPianoCosto())) {
            $esitoP = $gestorePianoCosti->generaPianoDeiCostiProponente($proponente->getId());
            if (!$esitoP) {
                $this->addFlash('error', "Errore durante la generazione del piano costo, contattare l'assistenza tecnica");
                return $this->addErrorRedirect("Errore nella generazione del piano costi", "home");
            }

            try {
                if (true == $procedura->getModalitaFinanziamentoAttiva()) {
                    $this->get("gestore_modalita_finanziamento")->getGestore($procedura)->generaModalitaFinanziamentoRichiesta($proponente);
                }

                $em->flush();
            } catch (SfingeException $e) {
                $this->addFlash('error', "Errore durante la generazione del piano costo, contattare l'assistenza tecnica");
                $this->addErrorRedirect($e->getMessage(), 'home');
            } catch (\Exception $e) {
                $this->addFlash('error', "Errore durante la generazione del piano costo, contattare l'assistenza tecnica");
                return $this->addErrorRedirect("Errore generico nel salvataggio a database dei dati", "home");
            }
        }

        $response = $gestorePianoCosti->aggiornaPianoDeiCostiProponente($proponente->getId());
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/totale_piano_costi", name="totale_piano_costi")
     * @Method({"GET", "POST"})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function totalePianoDeiCostiAction($id_richiesta) {
        $em = $this->getDoctrine()->getManager();

        $richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);

        $response = $this->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->totalePianoDeiCosti($richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/stati_avanzamento", name="stati_avanzamento")
     * @Method({"GET", "POST"})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function statiAvanzamentoAction($id_richiesta) {
        $em = $this->getDoctrine()->getManager();

        $richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);

        if (0 == count($richiesta->getVociFaseProcedurale())) {
            $esito = $this->get("gestore_fase_procedurale")->getGestore()->generaFaseProceduraleRichiesta($richiesta->getId());
            if (!$esito->res) {
                $messaggio = "Errore durante la generazione dello stato di avanzamento, contattare l'assistenza tecnica " . "( " . $esito->messaggio . " )";
                return $this->addErrorRedirect($messaggio, "home");
            }
            /* Bella Cagata  perchè è come se non scrivesse a db ?
             * TODO: Trovare una soluzione più elegante anzi direi sensata a questa illogica scelta
             */
            return $this->redirectToRoute('stati_avanzamento', ['id_richiesta' => $id_richiesta]);
        }

        $response = $this->get("gestore_fase_procedurale")->getGestore()->aggiornaFaseProceduraleRichiesta($richiesta->getId());
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elenco_documenti_richiesta", name="elenco_documenti_richiesta")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Elenco Documenti", sottoTitolo="carica i documenti richiesti")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco Documenti")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function elencoDocumentiRichiestaAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->elencoDocumenti($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/dati_progetto", name="dati_progetto")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Gestione dei dati del progetto", sottoTitolo="inserire titolo e abstract")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione dati progetto")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function gestioneDatiProgettoAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->gestioneDatiProgetto($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/scarica_domanda", name="scarica_domanda")
     * @Method({"GET"})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function scaricaDomandaAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        if (is_null($richiesta)) {
            return $this->addErrorRedirect("Richiesta non valida", "elenco_richieste");
        }

        if (is_null($richiesta->getDocumentoRichiesta())) {
            return $this->addErrorRedirect("Nessun documento associato alla richiesta", "elenco_richieste");
        }

        return $this->get("documenti")->scaricaDaId($richiesta->getDocumentoRichiesta()->getId());
    }

    /**
     * @Route("/{id_richiesta}/scarica_domanda_firmata", name="scarica_domanda_firmata")
     * @Method({"GET"})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function scaricaDomandaFirmataAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        if (is_null($richiesta)) {
            return $this->addErrorRedirect("Richiesta non valida", "elenco_richieste");
        }

        if (is_null($richiesta->getDocumentoRichiestaFirmato())) {
            return $this->addErrorRedirect("Nessun documento associato alla richiesta", "elenco_richieste");
        }

        return $this->get("documenti")->scaricaDaId($richiesta->getDocumentoRichiestaFirmato()->getId());
    }

    /**
     * @Route("/{id_richiesta}/elenco_documenti_caricati", name="elenco_documenti_caricati")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Elenco Documenti Richiesta", sottoTitolo="documenti caricati per la richiesta")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco Documenti")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function elencoDocumentiCaricatiAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->elencoDocumentiCaricati($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/gestione_priorita", name="gestione_priorita")
     * @PaginaInfo(titolo="Gestione priorità", sottoTitolo="pagina con le priorità")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione priorità")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function gestionePrioritaAction($id_richiesta) {
        $response = $this->get("gestore_priorita")->getGestore()->gestionePriorita($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/aggiungi_plesso_edificio", name="aggiungi_plesso_edificio")
     * @PaginaInfo(titolo="Dati del plesso/edificio", sottoTitolo="Pagina di inserimento dei dati del plesso/edificio")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     *				@ElementoBreadcrumb(testo="Gestione dati progetto", route="dati_progetto", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Aggiungi plesso/edificio")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function aggiungiEdificioPlessoAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->aggiungiEdificioPlesso($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/modifica_plesso_edificio/{id_indirizzo_catastale}", name="modifica_plesso_edificio")
     * @PaginaInfo(titolo="Dati del plesso/edificio", sottoTitolo="Pagina di modifica/dettaglio dei dati del plesso/edificio")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     *				@ElementoBreadcrumb(testo="Gestione dati progetto", route="dati_progetto", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettagli plesso/edificio")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_indirizzo_catastale
     */
    public function modificaEdificioPlessoAction($id_richiesta, $id_indirizzo_catastale) {
        $response = $this->get("gestore_richieste")->getGestore()->modificaEdificioPlesso($id_richiesta, $id_indirizzo_catastale);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elenco_fornitori", name="elenco_fornitori")
     * @PaginaInfo(titolo="Elenco fornitori", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco fornitori")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function elencoFornitoriAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->elencoFornitori($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elenco_interventi", name="elenco_interventi")
     * @PaginaInfo(titolo="Elenco sedi di intervento", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco sedi di intervento")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function elencoInterventiAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->elencoInterventi($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/{id_intervento}/modifica_intervento", name="modifica_intervento")
     * @PaginaInfo(titolo="Modifica sede di intervento", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco sedi di intervento", route="elenco_interventi", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Modifica sede di intervento")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_intervento
     */
    public function modificaInterventoAction($id_richiesta, $id_intervento) {
        $response = $this->get("gestore_richieste")->getGestore()->modificaIntervento($id_richiesta, $id_intervento);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/crea_fornitore", name="crea_fornitore")
     * @PaginaInfo(titolo="Crea fornitore", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco fornitori", route="elenco_fornitori", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Crea fornitore")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function creaFornitoreAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->creaFornitore($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/{id_fornitore}/modifica_fornitore", name="modifica_fornitore")
     * @PaginaInfo(titolo="Modifica fornitore", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco fornitori", route="elenco_fornitori", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Modifica fornitori")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_fornitore
     */
    public function modificaFornitoreAction($id_richiesta, $id_fornitore) {
        $response = $this->get("gestore_richieste")->getGestore()->modificaFornitore($id_richiesta, $id_fornitore);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/{id_fornitore}/visualizza_fornitore", name="visualizza_fornitore")
     * @PaginaInfo(titolo="Visualizza fornitore", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco fornitori", route="elenco_fornitori", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Visualizza fornitori")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_fornitore
     */
    public function visualizzaFornitoreAction($id_richiesta, $id_fornitore) {
        $response = $this->get("gestore_richieste")->getGestore()->visualizzaFornitore($id_richiesta, $id_fornitore);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/{id_fornitore}/elimina_fornitore", name="elimina_fornitore")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_fornitore
     */
    public function eliminaFornitoreAction($id_richiesta, $id_fornitore) {
        $response = $this->get("gestore_richieste")->getGestore()->eliminaFornitore($id_richiesta, $id_fornitore);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/{id_fornitore}/crea_fornitore_servizio", name="crea_fornitore_servizio")
     * @PaginaInfo(titolo="Visualizza fornitore", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco fornitori", route="elenco_fornitori", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco servizi", route="elenco_fornitore_servizi", parametri={"id_richiesta", "id_fornitore"}),
     * 				@ElementoBreadcrumb(testo="Crea servizio")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_fornitore
     */
    public function creaFornitoreServizioAction($id_richiesta, $id_fornitore) {
        $response = $this->get("gestore_richieste")->getGestore()->aggiungiFornitoreServizio($id_richiesta, $id_fornitore);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/{id_fornitore}/elenco_fornitore_servizi", name="elenco_fornitore_servizi")
     * @PaginaInfo(titolo="Elenco servizi", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco fornitori", route="elenco_fornitori", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco servizi")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_fornitore
     */
    public function elencoFornitoreServiziAction($id_richiesta, $id_fornitore) {
        $response = $this->get("gestore_richieste")->getGestore()->elencoServiziFornitore($id_richiesta, $id_fornitore);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/{id_fornitore}/{id_fornitore_servizio}/modifica_fornitore_servizio", name="modifica_fornitore_servizio")
     * @PaginaInfo(titolo="Modifica fornitore", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco fornitori", route="elenco_fornitori", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco servizi", route="elenco_fornitore_servizi", parametri={"id_richiesta", "id_fornitore"}),
     * 				@ElementoBreadcrumb(testo="Modifica servizio")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_fornitore
     * @param mixed $id_fornitore_servizio
     */
    public function modificaFornitoreServizioAction($id_richiesta, $id_fornitore, $id_fornitore_servizio) {
        $response = $this->get("gestore_richieste")->getGestore()->modificaFornitoreServizio($id_richiesta, $id_fornitore, $id_fornitore_servizio);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/{id_fornitore}/{id_fornitore_servizio}/visualizza_fornitore_servizio", name="visualizza_fornitore_servizio")
     * @PaginaInfo(titolo="Modifica fornitore", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco fornitori", route="elenco_fornitori", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco servizi", route="elenco_fornitore_servizi", parametri={"id_richiesta", "id_fornitore"}),
     * 				@ElementoBreadcrumb(testo="Visualizza servizio")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_fornitore
     * @param mixed $id_fornitore_servizio
     */
    public function visualizzaFornitoreServizioAction($id_richiesta, $id_fornitore, $id_fornitore_servizio) {
        $response = $this->get("gestore_richieste")->getGestore()->visualizzaFornitoreServizio($id_richiesta, $id_fornitore, $id_fornitore_servizio);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/{id_fornitore}/{id_fornitore_servizio}/elimina_fornitore_servizio", name="elimina_fornitore_servizio")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_fornitore
     * @param mixed $id_fornitore_servizio
     */
    public function eliminaFornitoreServizioAction($id_richiesta, $id_fornitore, $id_fornitore_servizio) {
        $response = $this->get("gestore_richieste")->getGestore()->eliminaFornitoreServizio($id_richiesta, $id_fornitore, $id_fornitore_servizio);
        return $response->getResponse();
    }

    /**
     * @Route("/dati_aggiuntivi_intervento/{id_istanza_pagina}/{id_pagina}/{id_istanza_frammento}/{azione}", name="dati_aggiuntivi_intervento", defaults={"id_istanza_pagina" : "-", "id_pagina" : "-", "id_istanza_frammento" : "-", "azione" : "modifica"})
     * @param mixed $id_istanza_pagina
     * @param mixed $id_pagina
     * @param mixed $id_istanza_frammento
     * @param mixed $azione
     */
    public function datiAggiuntiviInterventoAction(\Symfony\Component\HttpFoundation\Request $request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione) {
        if ("-" != $id_istanza_pagina) {
            $istanza_pagina = $this->getEm()->getRepository("FascicoloBundle\Entity\IstanzaPagina")->find($id_istanza_pagina);
        } else {
            $istanza_frammento = $this->getEm()->getRepository("FascicoloBundle\Entity\IstanzaFrammento")->find($id_istanza_frammento);
            $istanza_pagina = $istanza_frammento->getIstanzaPagina();
        }

        $istanza_fascicolo = $istanza_pagina->getIstanzaFascicolo();
        $intervento = $this->getEm()->getRepository("RichiesteBundle\Entity\Intervento")->findOneBy(["istanza_fascicolo" => $istanza_fascicolo]);

        $oggetto_richiesta = $intervento->getOggettoRichiesta();
        $richiesta = $oggetto_richiesta->getRichiesta();
        $id_richiesta = $richiesta->getId();

        $isRichiestaDisabilitata = $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isRichiestaDisabilitata($id_richiesta);

        if (!$this->isUtente() || $isRichiestaDisabilitata) {
            $azione = "visualizza";
        }

        $contestoSoggetto = $this->get('contesto')->getContestoRisorsa($richiesta, "soggetto");
        $accessoConsentito = $this->isGranted(\SoggettoBundle\Security\SoggettoVoter::ALL, $contestoSoggetto);

        $contestoProcedura = $this->get('contesto')->getContestoRisorsa($richiesta, "procedura");
        $accessoConsentito |= $this->isGranted(\SfingeBundle\Security\ProceduraVoter::READ, $contestoProcedura);

        if (!$accessoConsentito) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $this->container->get("pagina")->setMenuAttivo("elencoRichieste", $this->getSession());
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richieste", $this->generateUrl("elenco_richieste"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrl("dettaglio_richiesta", ["id_richiesta" => $id_richiesta]));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco sedi di intervento", $this->generateUrl("elenco_interventi", ["id_richiesta" => $id_richiesta]));

        $this->getSession()->set("fascicolo.route_istanza_pagina", "dati_aggiuntivi_intervento");

        return $this->get("fascicolo.istanza")->istanzaPagina($request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione);
    }

    /**
     * @Route("/questionario_proponente/{id_istanza_pagina}/{id_pagina}/{id_istanza_frammento}/{azione}", name="questionario_proponente", defaults={"id_istanza_pagina" : "-", "id_pagina" : "-", "id_istanza_frammento" : "-", "azione" : "modifica"})
     * @param mixed $id_istanza_pagina
     * @param mixed $id_pagina
     * @param mixed $id_istanza_frammento
     * @param mixed $azione
     */
    public function questionarioProponenteAction(\Symfony\Component\HttpFoundation\Request $request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione) {
        if ("-" != $id_istanza_pagina) {
            $istanza_pagina = $this->getEm()->getRepository("FascicoloBundle\Entity\IstanzaPagina")->find($id_istanza_pagina);
        } else {
            $istanza_frammento = $this->getEm()->getRepository("FascicoloBundle\Entity\IstanzaFrammento")->find($id_istanza_frammento);
            $istanza_pagina = $istanza_frammento->getIstanzaPagina();
        }

        $istanza_fascicolo = $istanza_pagina->getIstanzaFascicolo();
        $proponente = $this->getEm()->getRepository("RichiesteBundle\Entity\Proponente")->findOneBy(["istanza_fascicolo" => $istanza_fascicolo]);

        $richiesta = $proponente->getRichiesta();
        $id_richiesta = $richiesta->getId();

        $isRichiestaDisabilitata = $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isRichiestaDisabilitata($id_richiesta);

        if (!$this->isUtente() || $isRichiestaDisabilitata) {
            $azione = "visualizza";
        }

        $contestoSoggetto = $this->get('contesto')->getContestoRisorsa($richiesta, "soggetto");
        $accessoConsentito = $this->isGranted(\SoggettoBundle\Security\SoggettoVoter::ALL, $contestoSoggetto);

        $contestoProcedura = $this->get('contesto')->getContestoRisorsa($richiesta, "procedura");
        $accessoConsentito |= $this->isGranted(\SfingeBundle\Security\ProceduraVoter::READ, $contestoProcedura);

        if (!$accessoConsentito) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $this->container->get("pagina")->setMenuAttivo("elencoRichieste", $this->getSession());
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richieste", $this->generateUrl("elenco_richieste"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrl("dettaglio_richiesta", ["id_richiesta" => $id_richiesta]));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco proponenti", $this->generateUrl("elenco_proponenti", ["id_richiesta" => $id_richiesta]));

        $this->getSession()->set("fascicolo.route_istanza_pagina", "questionario_proponente");

        return $this->get("fascicolo.istanza")->istanzaPagina($request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione, $id_richiesta, 'PROPONENTE');
    }

    /**
     * @Route("/{id_richiesta}/richiesta_maggiorazione", name="richiesta_maggiorazione")
     * @PaginaInfo(titolo="Richiesta maggiorazione", sottoTitolo="Richiesta maggiorazione")
     * @Template("RichiesteBundle:Richieste:richiestaMaggiorazione.html.twig")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Richiesta Maggiorazione")
     * 				})
     * @Method({"GET", "POST"})
     * @Menuitem(menuAttivo="elencoRichieste")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function richiestaMaggiorazioneAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->richiestaMaggiorazione($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/richiesta_premialita", name="richiesta_premialita")
     * @PaginaInfo(titolo="Richiesta premialita", sottoTitolo="Richiesta premialita")
     * @Template("RichiesteBundle:Richieste:richiestaPremialita.html.twig")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Richiesta premialita")
     * 				})
     * @Method({"GET", "POST"})
     * @Menuitem(menuAttivo="elencoRichieste")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param $id_richiesta
     * @return mixed
     * @throws \Exception
     */
    public function richiestaPremialitaAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->richiestaPremialita($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/autodichiarazioni_autorizzazioni_richiesta", name="autodichiarazioni_autorizzazioni_richiesta")
     * @PaginaInfo(titolo="Gestione autodichiarazioni", sottoTitolo="pagina di gestione autodichiarazioni")
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param $id_richiesta
     * @return mixed
     * @throws \Exception
     */
    public function gestioneAutodichiarazioniAutorizzazioniAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->gestioneAutodichiarazioniAutorizzazioniAction($richiesta);
    }

    /**
	 * @Route("/{id_richiesta}/impegni", name="impegni_richiesta")
	 * @PaginaInfo(titolo="Impegni",sottoTitolo="Impegni")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
	 * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
	 * 				@ElementoBreadcrumb(testo="Richiesta Maggiorazione")
	 * 				})
	 * @Method({"GET","POST"})
	 * @Menuitem(menuAttivo = "elencoRichieste")
	 * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function impegniRichiestaAction($id_richiesta) {
        $richiesta = $this->getRichiesta($id_richiesta);
		$response = $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->impegniRichiesta($richiesta);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_richiesta}/{id_proponente}/elenco_interventi_sede/{id_sede}", name="elenco_interventi_sede")
	 * @PaginaInfo(titolo="Elenco interventi",sottoTitolo="")
	 * @Menuitem(menuAttivo = "elencoRichieste")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function elencoInterventiSedeAction($id_richiesta, $id_sede, $id_proponente) {
        $id_sede = $id_sede == 'null' ? null : $id_sede;
		$response = $this->get("gestore_richieste")->getGestore()->elencoInterventiSede($id_richiesta, $id_sede);
		return $response->getResponse();
	}
    
    /**
	 * @Route("/{id_richiesta}/elenco_interventi_sede/", name="elenco_interventi_sede_esterno")
	 * @PaginaInfo(titolo="Elenco fornitori",sottoTitolo="")
	 * @Menuitem(menuAttivo = "elencoRichieste")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function elencoInterventiSedeEsternoAction($id_richiesta) {
		$response = $this->get("gestore_richieste")->getGestore()->elencoInterventiSede($id_richiesta);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_richiesta}/{id_sede}/aggiungi_intervento_sede", name="aggiungi_intervento_sede")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function aggiungiInterventiSedeAction($id_richiesta, $id_sede) {
        $id_sede = $id_sede == 'null' ? null : $id_sede;
		$response = $this->get("gestore_richieste")->getGestore()->aggiungiInterventoSede($id_richiesta, $id_sede);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_richiesta}/{id_sede}/modifica_intervento_sede/{id_intervento}", name="modifica_intervento_sede")
	 * @PaginaInfo(titolo="Modifica intervento",sottoTitolo="")
	 * @Menuitem(menuAttivo = "elencoRichieste")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function modificaInterventiSedeAction($id_richiesta, $id_sede, $id_intervento) {
        $id_sede = $id_sede == 'null' ? null : $id_sede;
		$response = $this->get("gestore_richieste")->getGestore()->modificaInterventoSede($id_richiesta, $id_sede, $id_intervento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_richiesta}/{id_proponente}/elimina_intervento_sede/{id_intervento}", name="elimina_intervento_sede")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function eliminaInterventoSedeAction($id_richiesta, $id_proponente, $id_intervento) {
		$response = $this->get("gestore_richieste")->getGestore()->eliminaInterventoSede($id_richiesta, $id_proponente, $id_intervento);
		return $response->getResponse();
	}

    /**
     * @Route("/{id_richiesta}/elimina_intervento_richiesta/{id_intervento}", name="elimina_intervento_richiesta")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function eliminaInterventoRichiestaAction($id_richiesta, $id_intervento) {
        $response = $this->get("gestore_richieste")->getGestore()->eliminaInterventoRichiesta($id_richiesta, $id_intervento);
        return $response->getResponse();
    }
	
	/**
	 * @Route("/{id_richiesta}/{id_proponente}/gestione_sede_operativa/{id_sede}", name="gestione_sede_operativa")
	 * @PaginaInfo(titolo="Gestione sede operativa",sottoTitolo="")
	 * @Menuitem(menuAttivo = "elencoRichieste")
	 * @Breadcrumb(elementi={
	 * 			@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
	 * 			@ElementoBreadcrumb(testo="Dettaglio proponente", route="dettaglio_proponente", parametri={"id_proponente", "id_richiesta"}),
	 * 			@ElementoBreadcrumb(testo="Gestione sede operativa")
	 * 		})
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function gestioneSedeOperativaAction($id_richiesta, $id_sede, $id_proponente) {
		$response = $this->get("gestore_proponenti")->getGestore()->gestioneSedeOperativa($id_richiesta, $id_proponente, $id_sede);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_richiesta}/elenco_risorse_progetto/{tipo}", name="elenco_risorse_progetto")
	 * @PaginaInfo(titolo="Elenco risorse progetto",sottoTitolo="")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
	 * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
	 * 				@ElementoBreadcrumb(testo="Elenco risorse progetto")
	 * 				})
	 * @Menuitem(menuAttivo = "elencoRichieste")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function elencoRisorseProgettoAction($id_richiesta, $tipo) {
		$response = $this->get("gestore_risorse")->getGestore()->elencoRisorse($id_richiesta, $tipo);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_richiesta}/aggiungi_risorsa_progetto/{tipo}",defaults={"tipo" = "default"}, name="aggiungi_risorsa_progetto")
	 * @PaginaInfo(titolo="Aggiungi una risorsa del progetto",sottoTitolo="")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
	 * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
	 * 				@ElementoBreadcrumb(testo="Elenco risorse progetto", route="elenco_risorse_progetto", parametri={"id_richiesta", "tipo"}),
	 * 				@ElementoBreadcrumb(testo="Aggiungi una risorsa")
	 * 				})
	 * @Menuitem(menuAttivo = "elencoRichieste")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
	 */
	public function aggiungiRisorsaAction($id_richiesta, $tipo) {
		$response = $this->get("gestore_risorse")->getGestore()->AggiungiRisorsa($id_richiesta, $opzioni = array());
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_richiesta}/gestione_risorsa_progetto/{id_risorsa}/{tipo}", name="gestione_risorsa_progetto")
	 * @PaginaInfo(titolo="Gestione risorsa del progetto",sottoTitolo="")
	 * @Menuitem(menuAttivo = "elencoRichieste")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
	 * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
	 * 				@ElementoBreadcrumb(testo="Elenco risorse progetto", route="elenco_risorse_progetto", parametri={"id_richiesta", "tipo"}),
	 * 				@ElementoBreadcrumb(testo="Gestione risorsa")
	 * 				})
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function gestioneRisorsaProgettoAction($id_richiesta, $id_risorsa, $tipo) {
		$response = $this->get("gestore_risorse")->getGestore()->gestioneRisorsa($id_risorsa, $id_richiesta, $tipo, array());
		return $response->getResponse();
	}
 
	/**
	 * @Route("/{id_richiesta}/elimina_risorsa_progetto/{id_risorsa}/{tipo}", name="elimina_risorsa_progetto")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function eliminaRisorsaProgettoAction($id_richiesta, $id_risorsa, $tipo) {
		$response = $this->get("gestore_risorse")->getGestore()->eliminaRisorsa($id_risorsa, $id_richiesta, $tipo, array());
		return $response->getResponse();
	}


	/**
	 * @Route("/{id_richiesta}/indicatori", name="elenco_indicatori_richiesta")
	  * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 * @Breadcrumb(elementi={
	 *		@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
	 *		@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
	 *		@ElementoBreadcrumb(testo="Indicatori output")
	 *	})
	 * @Menuitem(menuAttivo = "elencoRichieste")
	 * @PaginaInfo(titolo="Indicatori di output",sottoTitolo="mostra l'elenco degli indicatori di output associati")
	 */
	public function gestioneIndicatoreOutputAction($id_richiesta): Response{
		$richiesta = $this->getRichiesta($id_richiesta);
		$gestore = $this->getGestoreRichiesta($richiesta);
		$response = $gestore->gestioneIndicatoreOutput($richiesta);

		return $response->getResponse();
	}

    /**
     * @Route("/{id_richiesta}/programma_richiesta", name="programma_richiesta")
     * @PaginaInfo(titolo="Gestione programma", sottoTitolo="pagina di gestione del programma")
     * * @Breadcrumb(elementi={
     *      @ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     *      @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     *      @ElementoBreadcrumb(testo="Gestione dati programma")
     *      })
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param int $id_richiesta
     * 
     * @return mixed
     * @throws \Exception
     */
    public function gestioneProgrammaAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $response = $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->gestioneProgramma($richiesta);
        return $response->getResponse();
    }
    
    /**
     * @Route("/{id_richiesta}/elenco_documenti_programma", name="elenco_documenti_programma")
     * @PaginaInfo(titolo="Elenco documenti programma", sottoTitolo="pagina di gestione dei documenti del programma")
     * * @Breadcrumb(elementi={
     *      @ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     *      @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     *      @ElementoBreadcrumb(testo="Elenco documenti programma")
     *      })
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param int $id_richiesta
     *
     * @return mixed
     * @throws \Exception
     */
    public function elencoDocumentiProgrammaAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $response = $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->elencoDocumentiProgramma($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elimina_documento_programma/{id_documento_programma}", name="elimina_documento_programma")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Bando98\DocumentoProgrammaLegge14", opzioni={"id" : "id_documento_programma"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_documento_programma
     * @param mixed $id_richiesta
     * 
     * @return mixed
     * @throws \Exception
     */
    public function eliminaDocumentoProgrammaAction($id_documento_programma, $id_richiesta) {
        $this->get('base')->checkCsrf('token');
        $response = $this->get("gestore_richieste")->getGestore()->eliminaDocumentoProgramma($id_documento_programma, $id_richiesta);
        return $response->getResponse();
    }
    
    /**
     * @Route("/{id_richiesta}/iter_progetto", name="richiesta_iter_progetto")
     * @PaginaInfo(titolo="Gestione fasi produrali", sottoTitolo="pagina per gestire le fasi procedurali del progetto")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione fasi produrali")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     */
    public function fasiProceduraliAction(string $id_richiesta): Response {
        $richiesta = $this->getRichiesta($id_richiesta);

        return $this->get('gestore_richieste')->getGestore($richiesta->getProcedura())->gestioneIterProgetto($richiesta);
    }
    
    /**
     * @Route("/{id_richiesta}/obiettivi_realizzativi", name = IGestoreObiettiviRealizzativi::ROUTE_ELENCO_OBIETTIVI)
     * @PaginaInfo(titolo="Gestione obiettivi realizzativi", sottoTitolo="pagina per gestire gli obiettivi realizzativi del progetto")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione obiettivi realizzativi")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @ParamConverter("richiesta", options={"mapping": {"id_richiesta"   : "id"}})
     */
    public function elencoObiettiviRealizzativiAction(Richiesta $richiesta): Response
    {
        /** @var GestoreObiettiviRealizzativiService $factory */
        $factory = $this->get(GestoreObiettiviRealizzativiService::SERVICE_NAME);
        $service = $factory->getGestore($richiesta);

        return $service->elencoObiettivi();
    }

    /**
     * @Route("/{id_richiesta}/nuovo_obiettivo_realizzativo", name = IGestoreObiettiviRealizzativi::ROUTE_NUOVO_OBIETTIVO)
     * @PaginaInfo(titolo="Gestione obiettivi realizzativi", sottoTitolo="pagina per gestire gli obiettivi realizzativi del progetto")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(
     *                  testo="Gestione obiettivi realizzativi", 
     *                  route=IGestoreObiettiviRealizzativi::ROUTE_ELENCO_OBIETTIVI,
     *                  parametri={"id_richiesta"}
     *              ),
     * 				@ElementoBreadcrumb(testo="Aggiungi nuovo obiettivo realizzativo")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(
     *                      contesto="soggetto", 
     *                      classe="RichiesteBundle:Richiesta", 
     *                      opzioni={"id" : "id_richiesta"}
     * )
     * @ControlloAccesso(
     *                      contesto="richiesta", 
     *                      classe="RichiesteBundle:Richiesta", 
     *                      opzioni={"id" : "id_richiesta"}, 
     *                      azione=\RichiesteBundle\Security\RichiestaVoter::WRITE
     * )
     * @ParamConverter("richiesta", options={"mapping": {"id_richiesta"   : "id"}})
     */
    public function inserisciObiettiviRealizzativiAction(Richiesta $richiesta): Response {
        /** @var GestoreObiettiviRealizzativiService $factory */
        $factory = $this->get(GestoreObiettiviRealizzativiService::SERVICE_NAME);
        $service = $factory->getGestore($richiesta);

        return $service->nuovoObiettivo();
    }

    /**
     * @Route(
     *          "/{id_richiesta}/obiettivo_realizzativo/{id_obiettivo}", 
     *          name = IGestoreObiettiviRealizzativi::ROUTE_MODIFICA_OBIETTIVO
     * )
     * @PaginaInfo(
     *              titolo="Modifica/Visualizza obiettivo realizzativo", 
     *              sottoTitolo="pagina per visualizzare/modificare un obiettivo realizzativo associato ad un progetto"
     * )
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(
     *                  testo="Gestione obiettivi realizzativi", 
     *                  route=IGestoreObiettiviRealizzativi::ROUTE_ELENCO_OBIETTIVI,
     *                  parametri={"id_richiesta"}
     *              ),
     *              @ElementoBreadcrumb(testo="Modifica/Visualizza obiettivo realizzativo")
     * 	})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(
     *                  contesto="richiesta", 
     *                  classe="RichiesteBundle:ObiettivoRealizzativo", 
     *                  opzioni={"id": "id_obiettivo"}, 
     *                  azione=RichiestaVoter::WRITE
     * )
     * @ControlloAccesso(
     *                  contesto="soggetto", 
     *                  classe="RichiesteBundle:Richiesta", 
     *                  opzioni={"id" : "id_richiesta"}
     * )
     * 
     * @ParamConverter("richiesta", options={"mapping": {"id_richiesta"   : "id"}})
     * @ParamConverter("obiettivo", options={"mapping": {"id_obiettivo"   : "id"}})
     */
    public function modificaObiettiviRealizzativiAction(Richiesta $richiesta, ObiettivoRealizzativo $obiettivo): Response {
        /** @var GestoreObiettiviRealizzativiService $factory */
        $factory = $this->get(GestoreObiettiviRealizzativiService::SERVICE_NAME);
        $service = $factory->getGestore($richiesta);

        return $service->modificaObiettivo($obiettivo);
    }
    /**
     * @Route(
     *         "/elimina_obiettivo_realizzativo/{id}", 
     *         name = IGestoreObiettiviRealizzativi::ROUTE_ELIMINA_OBIETTIVO
     * )
     * @ControlloAccesso(
     *                  contesto="richiesta", 
     *                  classe="RichiesteBundle:ObiettivoRealizzativo", 
     *                  opzioni={"id": "id"}, 
     *                  azione=RichiestaVoter::READ
     * )
     * @ControlloAccesso(
     *                  contesto="soggetto", 
     *                  classe="RichiesteBundle:ObiettivoRealizzativo", 
     *                  opzioni={"id": "id"},
     * )
     */
    public function eliminaObiettivoRealizzativoAction(ObiettivoRealizzativo $obiettivo): Response {
        $this->get('base')->checkCsrf('token');
        /** @var GestoreObiettiviRealizzativiService $factory */
        $factory = $this->get(GestoreObiettiviRealizzativiService::SERVICE_NAME);
        
        $richiesta = $obiettivo->getRichiesta();
        $service = $factory->getGestore($richiesta);

        return $service->eliminaObiettivo($obiettivo);
    }

    /**
     * @Route("/{id_richiesta}/dati_irap", name="dati_irap")
     * @PaginaInfo(titolo="Dati IRAP", sottoTitolo="pagina con i dati IRAP della richiesta")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dati generali")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param $id_richiesta
     * @return mixed
     * @throws \Exception
     */
    public function datiIrapAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->datiIrap($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elimina_richiesta", name="elimina_richiesta")
     * @ParamConverter("richiesta", options={"mapping": {"id_richiesta" : "id"}})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param Richiesta $richiesta
     * @return Response
     * @throws \Exception
     */
    public function eliminaRichiestaAction(Richiesta $richiesta): Response {
        $this->get('base')->checkCsrf('token');

        $response = $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->eliminaRichiesta($richiesta);

        return $response;
    }

 /**
     * @Route("/{id_richiesta}/elenco_sedi_operative_richiesta", name="elenco_sedi_operative_richiesta")
     * @PaginaInfo(titolo="Elenco unità locali",sottoTitolo="")
     * @Breadcrumb(elementi={
     *              @ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     *              @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
 *                  @ElementoBreadcrumb(testo="Elenco unità locali")
     *                })
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param int $id_richiesta
     * @return mixed
     * @throws Exception
     */
    public function elencoSediOperativeRichiestaAction(int $id_richiesta)
    {
        $response = $this->get("gestore_richieste")->getGestore()->elencoSediOperativeRichiesta($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/aggiungi_sede_operativa_richiesta", name="aggiungi_sede_operativa_richiesta")
     * @Template("RichiesteBundle:Richieste:sedeOperativaRichiesta.html.twig")
     * @PaginaInfo(titolo="Aggiunta unità locale",sottoTitolo="")
     * @Breadcrumb(elementi={
     *              @ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     *              @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     *              @ElementoBreadcrumb(testo="Elenco unità locali", route="elenco_sedi_operative_richiesta", parametri={"id_richiesta"}),
     *              @ElementoBreadcrumb(testo="Aggiunta unità locale")
     *                })
     * @Menuitem(menuAttivo = "elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     *
     * @param int $id_richiesta
     * @return RedirectResponse
     * @throws Exception
     */
    public function aggiungiSedeOperativaRichiestaAction(int $id_richiesta)
    {
        $response = $this->get("gestore_richieste")->getGestore()->aggiungiSedeOperativaRichiesta($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/modifica_sede_operativa_richiesta/{id_sede_operativa_richiesta}", name="modifica_sede_operativa_richiesta")
     * @PaginaInfo(titolo="Modifica unità locale",sottoTitolo="")
     * @Breadcrumb(elementi={
     *              @ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     *              @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     *              @ElementoBreadcrumb(testo="Elenco unità locali", route="elenco_sedi_operative_richiesta", parametri={"id_richiesta"}),
     *              @ElementoBreadcrumb(testo="Modifica unità locale")
     *                })
     * @Menuitem(menuAttivo = "elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     *
     * @param int $id_richiesta
     * @param int $id_sede_operativa_richiesta
     * @return mixed
     * @throws Exception
     */
    public function modificaSedeOperativaRichiestaAction(int $id_richiesta, int $id_sede_operativa_richiesta)
    {
        $response = $this->get("gestore_richieste")->getGestore()->modificaSedeOperativaRichiesta($id_richiesta,
            $id_sede_operativa_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/rimuovi_sede_operativa_richiesta/{id_sede_operativa_richiesta}", name="rimuovi_sede_operativa_richiesta")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     *
     * @param int $id_richiesta
     * @param int $id_sede_operativa_richiesta
     * @return mixed|Response
     */
    public function rimuoviSedeOperativaRichiestaAction(int $id_richiesta, int $id_sede_operativa_richiesta)
    {
        $this->get('base')->checkCsrf('token');
        try {
            $response = $this->get("gestore_richieste")->getGestore()->rimuoviSedeOperativaRichiesta($id_richiesta,
                $id_sede_operativa_richiesta);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (Exception $e) {
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/dettaglio_beneficiario", name="dettaglio_beneficiario")
     * @PaginaInfo(titolo="Dettaglio beneficiario", sottoTitolo="")
     * @Template("RichiesteBundle:Richieste:dettaglioProponente.html.twig")
     * @Breadcrumb(elementi={
     *              @ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     *              @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     *              @ElementoBreadcrumb(testo="Dettaglio beneficiario")
     *                })
     * @Method({"GET", "POST"})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * 
     * @param int $id_richiesta
     * @return mixed
     * @throws Exception
     */
    public function dettaglioBeneficiarioAction(int $id_richiesta)
    {
        $response = $this->get("gestore_richieste")->getGestore()->dettaglioBeneficiario($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/dati_rinnova_scadenza", name="dati_rinnova_scadenza")
     * @PaginaInfo(titolo="Rinnova scadenza", sottoTitolo="")
     * @Template("RichiesteBundle:Richieste:dettaglioProponente.html.twig")
     * @Breadcrumb(elementi={
     *              @ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     *              @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     *              @ElementoBreadcrumb(testo="Rinnova scadenza")
     *                })
     * @Method({"GET", "POST"})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     *
     * @param int $id_richiesta
     * @return mixed
     * @throws Exception
     */
    public function datiRinnovaScadenzaAction(int $id_richiesta)
    {
        $response = $this->get("gestore_richieste")->getGestore()->datiRinnovaScadenza($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/genera_pdf_prenotazione", name="genera_pdf_prenotazione")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * 
     * @param $id_richiesta
     * @return Response|void
     * @throws SfingeException
     */
    public function generaPdfPrenotazioneAction($id_richiesta)
    {
        $richiesta = $this->getRichiesta($id_richiesta);
        try {
            return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->generaPdfPrenotazione($id_richiesta);
        } catch(SfingeException $e) {
            throw $e;
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch(\Exception $e) {
            throw $e;
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/elenco_documenti_richiesta_dropzone", name="elenco_documenti_richiesta_dropzone")
     * @Method({"GET", "POST"})
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function elencoDocumentiRichiestaDropzoneAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->elencoDocumentiRichiestaDropzone($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/carica_documento_richiesta_dropzone", name="carica_documento_richiesta_dropzone")
     * @Method({"POST"})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function caricaDocumentoDropzoneAction(Request $request, $id_richiesta)
    {
        $arrayResult = $this->get("gestore_richieste")->getGestore()->caricaDocumentoDropzone($request, $id_richiesta);
        return new JsonResponse($arrayResult);
    }

    /**
     * @Route("/{id_richiesta}/concat_chunks_documento_richiesta_dropzone", name="concat_chunks_documento_richiesta_dropzone")
     * @Method({"POST"})
     */
    public function concatChunksDocumentoDropzoneAction(Request $request, $id_richiesta)
    {
        $arrayResult = $this->get("gestore_richieste")->getGestore()->concatChunksDocumentoDropzone($request, $id_richiesta);
        return new JsonResponse($arrayResult);
    }

    /**
     * @Route("/questionario_programma/{id_istanza_pagina}/{id_richiesta}/{id_pagina}/{id_istanza_frammento}/{azione}", name="questionario_programma", defaults={"id_istanza_pagina" : "-", "id_pagina" : "-", "id_istanza_frammento" : "-", "azione" : "modifica"})
     * @param mixed $id_istanza_pagina
     * @param mixed $id_richiesta
     * @param mixed $id_pagina
     * @param mixed $id_istanza_frammento
     * @param mixed $azione
     */
    public function questionarioProgrammaAction(Request $request, $id_istanza_pagina, $id_richiesta, $id_pagina, $id_istanza_frammento, $azione)
    {
        if ("-" != $id_istanza_pagina) {
            $istanza_pagina = $this->getEm()->getRepository("FascicoloBundle\Entity\IstanzaPagina")->find($id_istanza_pagina);
        } else {
            $istanza_frammento = $this->getEm()->getRepository("FascicoloBundle\Entity\IstanzaFrammento")->find($id_istanza_frammento);
            $istanza_pagina = $istanza_frammento->getIstanzaPagina();
        }

        $istanza_fascicolo = $istanza_pagina->getIstanzaFascicolo();
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
       // $id_richiesta = $richiesta->getId();

        $isRichiestaDisabilitata = $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isRichiestaDisabilitata($id_richiesta);

        if (!$this->isUtente() || $isRichiestaDisabilitata) {
            $azione = "visualizza";
        }

        $contestoSoggetto = $this->get('contesto')->getContestoRisorsa($richiesta, "soggetto");
        $accessoConsentito = $this->isGranted(\SoggettoBundle\Security\SoggettoVoter::ALL, $contestoSoggetto);

        $contestoProcedura = $this->get('contesto')->getContestoRisorsa($richiesta, "procedura");
        $accessoConsentito |= $this->isGranted(\SfingeBundle\Security\ProceduraVoter::READ, $contestoProcedura);

        if (!$accessoConsentito) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $this->container->get("pagina")->setMenuAttivo("elencoRichieste", $this->getSession());
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richieste", $this->generateUrl("elenco_richieste"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrl("dettaglio_richiesta", ["id_richiesta" => $id_richiesta]));

        $this->getSession()->set("fascicolo.route_istanza_pagina", "questionario_programma");

        return $this->get("fascicolo.istanza")->istanzaPagina($request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione, $id_richiesta);
    }
    
    /**
     * @Route("/{id_richiesta}/dichiarazioni_dsnh", name="dichiarazioni_dsnh")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Gestione dichiarazioni DNSH", sottoTitolo="selezionare una opzione")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione dichiarazioni DNSH")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function gestioneDichiarazioniDsnhAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->gestioneDichiarazioniDnsh($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/gestione_ambiti_tematici_s3", name="gestione_ambiti_tematici_s3")
     * @PaginaInfo(titolo="Gestione ambiti prioritari S3", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione ambiti prioritari S3")
     *                })
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("richiesta", options={"mapping": {"id_richiesta":"id"}})
     * @param Richiesta $richiesta
     * @return mixed
     * @throws Exception
     */
    public function gestioneAmbitiTematiciS3Action(Richiesta $richiesta)
    {
        try {
            return $this->get("gestore_ambiti_tematici_s3")
                ->getGestore()->gestioneAmbitiTematiciS3($richiesta);
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (Exception $e) {
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/aggiungi_ambito_tematico_s3", name="aggiungi_ambito_tematico_s3")
     * @PaginaInfo(titolo="Aggiungi ambito tematico prioritari S3", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione ambiti prioritari S3", route="gestione_ambiti_tematici_s3", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Aggiungi ambito prioritario S3")
     *                })
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("richiesta", options={"mapping": {"id_richiesta":"id"}})
     * @param Richiesta $richiesta
     * @return mixed
     * @throws Exception
     */
    public function aggiungiAmbitoTematicoS3Action(Richiesta $richiesta)
    {
        try {
            $response = $this->get("gestore_ambiti_tematici_s3")->getGestore()->aggiungiAmbitoTematicoS3($richiesta);
            return $response->getResponse();
        } catch (Exception $e) {
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/elimina_ambito_tematico_s3_proponente/{id_ambito_tematico_s3_proponente}", name="elimina_ambito_tematico_s3_proponente")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function eliminaAmbitoTematicoS3Action($id_richiesta, $id_ambito_tematico_s3_proponente)
    {
        try {
            $response = $this->get("gestore_ambiti_tematici_s3")->getGestore()->eliminaAmbitoTematicoS3Proponente($id_ambito_tematico_s3_proponente);
            return $response->getResponse();
        } catch (Exception $e) {
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/gestione_descrittori/{id_ambito_tematico_s3_proponente}", name="gestione_descrittori")
     * @PaginaInfo(titolo="Gestione descrittori", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione ambiti prioritari S3", route="gestione_ambiti_tematici_s3", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione descrittori")
     *                })
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("ambitoTematicoS3Proponente", options={"mapping": {"id_ambito_tematico_s3_proponente":"id"}})
     * @param AmbitoTematicoS3Proponente $ambitoTematicoS3Proponente
     * @return mixed
     * @throws Exception
     */
    public function gestioneDescrittoriAction(AmbitoTematicoS3Proponente $ambitoTematicoS3Proponente)
    {
        try {
            return $this->get("gestore_ambiti_tematici_s3")
                ->getGestore()->gestioneDescrittori($ambitoTematicoS3Proponente);
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (Exception $e) {dump($e);
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/aggiungi_descrittore_ambito_tematico_s3/{id_ambito_tematico_s3_proponente}", name="aggiungi_descrittore_ambito_tematico_s3")
     * @PaginaInfo(titolo="Aggiungi descrittore", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione ambiti prioritari S3", route="gestione_ambiti_tematici_s3", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione descrittori", route="gestione_descrittori", parametri={"id_richiesta", "id_ambito_tematico_s3_proponente"}),
     * 				@ElementoBreadcrumb(testo="Aggiungi descrittore")
     *                })
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("ambitoTematicoS3Proponente", options={"mapping": {"id_ambito_tematico_s3_proponente":"id"}})
     * @param AmbitoTematicoS3Proponente $ambitoTematicoS3Proponente
     * @return mixed
     * @throws Exception
     */
    public function aggiungiDescrittoreAmbitoTematicoS3Action(AmbitoTematicoS3Proponente $ambitoTematicoS3Proponente)
    {
        try {
            $response = $this->get("gestore_ambiti_tematici_s3")->getGestore()
                ->aggiungiDescrittoreAmbitoTematicoS3($ambitoTematicoS3Proponente);
            return $response->getResponse();
        } catch (Exception $e) {
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/elimina_descrittore_ambito_tematico_s3/{id_ambito_tematico_s3_proponente}/{id_descrittore}", name="elimina_descrittore_ambito_tematico_s3")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function eliminaDescrittoreAmbitoTematicoS3Action($id_ambito_tematico_s3_proponente, $id_descrittore)
    {
        try {
            $response = $this->get("gestore_ambiti_tematici_s3")->getGestore()
                ->eliminaDescrittoreAmbitoTematicoS3($id_ambito_tematico_s3_proponente, $id_descrittore);
            return $response->getResponse();
        } catch (Exception $e) {
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/modifica_descrittore_ambito_tematico_s3/{id_ambito_tematico_s3_proponente}/{id_descrittore}", name="modifica_descrittore_ambito_tematico_s3")
     * @PaginaInfo(titolo="Aggiungi ambito tematico prioritario S3", sottoTitolo="pagina per gli ambiti prioritari S3")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione ambiti prioritari S3")
     *                })
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param int $id_ambito_tematico_s3_proponente
     * @param int $id_descrittore
     * @return mixed
     * @throws Exception
     */
    public function modificaDescrittoreAmbitoTematicoS3Action($id_ambito_tematico_s3_proponente, $id_descrittore)
    {
        try {
            $response = $this->get("gestore_ambiti_tematici_s3")->getGestore()
                ->modificaDescrittoreAmbitoTematicoS3($id_ambito_tematico_s3_proponente, $id_descrittore);
            return $response->getResponse();
        } catch (Exception $e) {
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }
}
