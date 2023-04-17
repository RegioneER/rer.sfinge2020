<?php

namespace RichiesteBundle\Controller;

use BaseBundle\Exception\SfingeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use RichiesteBundle\Ricerche\RicercaIngegneriaFinanziaria;
use DocumentoBundle\Component\ResponseException;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/ingegneria_finanziaria")
 */
class IngegneriaFinanziariaController extends AbstractController {
    /**
     * @Route("/seleziona_procedura", name="seleziona_procedura_ing_fin")
     * @Method({"GET", "POST"})
     * @Template("RichiesteBundle:ProcedureParticolari:selezionaProcedura.html.twig")
     * @PaginaInfo(titolo="Elenco bandi", sottoTitolo="mostra l'elenco delle procedure di assistenza tecnica disponibili")
     * @Menuitem(menuAttivo="creaRichiestaIngFin")
     */
    public function selezionaProceduraAction(Request $request) {
        $em = $this->getEm();
        $this->creaRichiesta();
        $options = [
            "url_indietro" => $this->generateUrl("elenco_richieste_ing_fin"),
            'classeProcedura' => 'SfingeBundle:IngegneriaFinanziaria',
        ];
        $this->richiesta->setAbilitaGestioneBandoChiuso(false);

        $form = $this->createForm('RichiesteBundle\Form\SelezionaProceduraType', $this->richiesta, $options);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //Inserisco di dafault emilia romagana per evitare che tutte le query creino problemi negli elenchi
            $soggettoDefault = $this->getEm()->getRepository("SoggettoBundle:OrganismoIntermedio")->findOneBy(["codice_fiscale" => "80062590379"]);

            $proponente = new \RichiesteBundle\Entity\Proponente();
            $proponente->setSoggetto($soggettoDefault);
            $proponente->setRichiesta($this->richiesta);
            $proponente->setMandatario(true);

            $procedura = $this->richiesta->getProcedura();
            if (is_null($procedura)) {
                $this->addFlash('error', "Non è stata selezionato alcuna procedura");
                return ["form" => $form->createView()];
            }

            $richieste = $em->getRepository("RichiesteBundle\Entity\Richiesta")->getRichiesteDaSoggetto($soggettoDefault->getId(), $procedura->getId());
            if (!is_null($richieste) && count($richieste) >= $procedura->getNumeroRichieste()) {
                $form->get('procedura')->addError(new \Symfony\Component\Form\FormError("È già stato raggiunto il numero di richieste ammesso per la procedura / bando selezionato"));
            }

            if ($form->isValid()) {
                try {
                    $this->setInfoRichiestaDaProcedura();
                    $em->persist($proponente);
                    $em->persist($this->richiesta);
                    $em->flush();
                    $this->get("sfinge.stati")->avanzaStato($this->richiesta, "PRE_INSERITA");
                    $em->flush();
                    $this->addFlash('success', "Modifiche salvate correttamente");
                    return $this->redirect($this->generateUrl('nuova_richiesta_ing_fin', ['id_richiesta' => $this->richiesta->getId()]));
                } catch (ResponseException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        return ["form" => $form->createView()];
    }

    /**
     * @Route("/{id_richiesta}/nuova_richiesta_ing_fin", name="nuova_richiesta_ing_fin")
     * @Method({"GET", "POST"})
     * @Menuitem(menuAttivo="creaRichiestaIngFin")
     * @param mixed $id_richiesta
     */
    public function nuovaRichiestaAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->nuovaRichiesta($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/elenco/{sort}/{direction}/{page}", defaults={"sort" : "i.id", "direction" : "asc", "page" : "1"}, name="elenco_richieste_ing_fin")
     * @PaginaInfo(titolo="Elenco richieste", sottoTitolo="mostra l'elenco delle richieste presentate")
     * @Menuitem(menuAttivo="elencoRichiesteIngFin")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco richieste")})
     */
    public function elencoRichiesteAction() {
        // $statiRichiesta = $this->getEm()->getRepository("BaseBundle\Entity\StatoRichiesta")->findAll();

        $datiRicerca = new RicercaIngegneriaFinanziaria();
        $datiRicerca->setUtente($this->getUser());
        // $datiRicerca->setStatiRichiesta($statiRichiesta);

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        $params = ['richieste' => $risultato["risultato"],
            "form_ricerca_richieste" => $risultato["form_ricerca"],
            "filtro_attivo" => $risultato["filtro_attivo"], ];

        return $this->render("RichiesteBundle:ProcedureParticolari:elencoRichiestePP.html.twig", $params);
    }

    /**
     * @Route("/elenco_richieste_pulisci", name="elenco_richieste_pulisci_ing_fin")
     */
    public function elencoRichiestePulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaIngegneriaFinanziaria());
        return $this->redirectToRoute("elenco_richieste_ing_fin");
    }

    /**
     * @Route("/{id_richiesta}/dettaglio", name="dettaglio_richiesta_ing_fin")
     * @PaginaInfo(titolo="Richiesta", sottoTitolo="pagina con le sezioni della richiesta da compilare")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_ing_fin"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteIngFin")
     * @param mixed $id_richiesta
     */
    public function dettaglioRichiestaAction($id_richiesta) {
        $this->getSession()->set("id_richiesta", $id_richiesta);
        $richiesta = $this->getRichiesta($id_richiesta);
        $response = $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->dettaglioRichiesta($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/piano_costi/{id_proponente}", name="piano_costi_ing_fin")
     * @Method({"GET", "POST"})
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function pianoDeiCostiAction($id_richiesta, $id_proponente) {
        $em = $this->getDoctrine()->getManager();

        $proponente = $em->getRepository("RichiesteBundle\Entity\Proponente")->find($id_proponente);

        if (0 == count($proponente->getVociPianoCosto())) {
            $esitoP = $this->get("gestore_richieste")->getGestore()->generaPianoDeiCosti($proponente->getId());
            if (!$esitoP) {
                $this->addFlash('error', "Errore durante la generazione del piano costo, contattare l'assistenza tecnica");
                return $this->addErrorRedirect("Errore nella generazione del piano costi", "home");
            }
            try {
                $em->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', "Errore durante la generazione del piano costo, contattare l'assistenza tecnica");
                return $this->addErrorRedirect("Errore generico nel salvataggio a database dei dati", "home");
            }

            /* Bella Cagata  perchè è come se non scrivesse a db ?
             * TODO: Trovare una soluzione più elegante anzi direi sensata a questa illogica scelta
             */
            return $this->redirectToRoute('piano_costi_ing_fin', ['id_richiesta' => $id_richiesta, 'id_proponente' => $id_proponente]);
        }

        $response = $this->get("gestore_richieste")->getGestore()->aggiornaPianoDeiCosti($proponente->getId());
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elenco_proponenti", name="elenco_proponenti_ing_fin")
     * @PaginaInfo(titolo="Elenco proponenti", sottoTitolo="mostra l'elenco dei proponenti per la richiesta")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_ing_fin"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_ing_fin", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteIngFin")
     * @param mixed $id_richiesta
     */
    public function elencoProponentiAction($id_richiesta) {
        $richiesta = $this->getRichiesta($id_richiesta);
        $response = $this->get("gestore_proponenti")->getGestore($richiesta->getProcedura())->elencoProponenti($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/dettaglio_proponente/{id_proponente}", name="dettaglio_proponente_ing_fin")
     * @PaginaInfo(titolo="Dettaglio proponente", sottoTitolo="dettaglio di un proponente associato ad una richiesta")
     * @Template("RichiesteBundle:Richieste:dettaglioProponente.html.twig")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_ing_fin"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_ing_fin", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti_ing_fin", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettaglio proponente")
     * 				})
     * @Method({"GET", "POST"})
     * @Menuitem(menuAttivo="elencoRichiesteIngFin")
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function dettaglioProponenteAction($id_richiesta, $id_proponente) {
        $richiesta = $this->getRichiesta($id_richiesta);

        $response = $this->get("gestore_proponenti")->getGestore($richiesta->getProcedura())->dettagliProponente($id_proponente);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/dati_progetto", name="dati_progetto_ing_fin")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Gestione dei dati del progetto", sottoTitolo="inserire titolo e abstract")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_ing_fin"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_ing_fin", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione dati progetto")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteIngFin")
     * @param mixed $id_richiesta
     */
    public function gestioneDatiProgettoAction($id_richiesta) {
        $richiesta = $this->getRichiesta($id_richiesta);

        $response = $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->gestioneDatiProgetto($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/cerca_referente/{id_proponente}/{page}", defaults={"page" : 1}, name="cerca_referente_ing_fin")
     * @PaginaInfo(titolo="Aggiunta referente", sottoTitolo="pagina per cercare ed aggiungere un nuovo referente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_ing_fin"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_ing_fin", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti_ing_fin", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettagli proponente", route="dettaglio_proponente_ing_fin", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Aggiunta referente")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteIngFin")
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function cercaReferenteAction($id_richiesta, $id_proponente) {
        $richiesta = $this->getRichiesta($id_richiesta);
        try {
            $response = $this->get("gestore_proponenti")->getGestore($richiesta->getProcedura())->cercaReferente($id_proponente);
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_ing_fin");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste_ing_fin");
        }

        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/inserisci_referente/{id_proponente}/{persona_id}", name="inserisci_referente_ing_fin")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Selezione tipologia", sottoTitolo="pagina per indicare la tipologia di relazione con il proponente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_ing_fin"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_ing_fin", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti_ing_fin", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettagli proponente", route="dettaglio_proponente_ing_fin", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Aggiunta referente", route="cerca_referente_ing_fin", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Selezione tipologia")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteIngFin")
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     * @param mixed $persona_id
     */
    public function inserisciReferenteAction($id_richiesta, $id_proponente, $persona_id) {
        $richiesta = $this->getRichiesta($id_richiesta);
        try {
            $response = $this->get("gestore_proponenti")->getGestore($richiesta->getProcedura())->inserisciReferente($id_proponente, $persona_id);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_ing_fin");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste_ing_fin");
        }
    }

    /**
     * @Route("/{id_richiesta}/completa_richiesta", name="completa_richiesta_ing_fin")
     * @Method({"GET"})
     * @param mixed $id_richiesta
     */
    public function completaRichiesta($id_richiesta) {
        $this->get('base')->checkCsrf('token');
        try {
            $response = $this->get("gestore_richieste")->getGestore()->completaRichiesta($id_richiesta);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_ing_fin");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste_ing_fin");
        }
    }

    /**
     * @Route("/{id_richiesta}/elenco_documenti_richiesta", name="elenco_documenti_richiesta_ing_fin")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Elenco Documenti", sottoTitolo="carica i documenti richiesti")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_ing_fin"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_ing_fin", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco Documenti")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function elencoDocumentiRichiestaAction($id_richiesta) {
        $richiesta = $this->getRichiesta($id_richiesta);

        $response = $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->elencoDocumenti($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elimina_documento_richiesta/{id_documento_richiesta}", name="elimina_documento_richiesta_ing_fin")
     * @param mixed $id_documento_richiesta
     * @param mixed $id_richiesta
     */
    public function eliminaDocumentoRichiestaAction($id_documento_richiesta, $id_richiesta) {
        $this->get('base')->checkCsrf('token');
        $response = $this->get("gestore_richieste")->getGestore()->eliminaDocumentoRichiesta($id_documento_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/dati_trasferimento_fondo", name="dati_trasferimento_fondo")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Gestione dei dati di trasferimento del fondo", sottoTitolo="selezionare l'atto di trasferimento del fondo")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione trasferimento fondo")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteAt")
     * @param mixed $id_richiesta
     */
    public function gestioneTrasferimentoFondoAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->datiTrasferimentoFondo($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/gestione_indicatori_ing_fin", name="gestione_indicatori_ing_fin")
     * @PaginaInfo(titolo="Indicatori", sottoTitolo="pagina di gestione degli indicatori")
     * @Menuitem(menuAttivo="elencoRichieste")
     * @Breadcrumb(elementi={
     * 			@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_ing_fin"),
     * 			@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_ing_fin", parametri={"id_richiesta"}),
     * 			@ElementoBreadcrumb(testo="Indicatori")
     * 	})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function gestioneIndicatoriAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->findOneById($id_richiesta);
        if (\is_null($richiesta)) {
            throw new SfingeException('Pagamento non trovato');
        }
        return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->gestioneIndicatori($richiesta);
    }

    /**
     * @Route("/{id_richiesta}/dati_cup_ing_fin", name="dati_cup_ing_fin")
     * @PaginaInfo(titolo="Dati cup richiesta")
     * @Menuitem(menuAttivo="elencoRichieste")
     * @Breadcrumb(elementi={
     * 			@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_ing_fin"),
     * 			@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_ing_fin", parametri={"id_richiesta"}),
     * 			@ElementoBreadcrumb(testo="Dati cup")
     * 	})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function datiCupAction($id_richiesta) {
        $gestore_istruttoria = $this->get("gestore_richieste")->getGestore();
        return $gestore_istruttoria->datiCup($id_richiesta)->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/iter_progetto", name="gestione_iter_progetto_ing_fin")
     * @PaginaInfo(titolo="Fasi procedurali", sottoTitolo="pagina di gestione delle fasi procedurali")
     * * @Menuitem(menuAttivo="elencoRichiesteAt")
     * @Breadcrumb(elementi={
     * 			@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_ing_fin"),
     * 			@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_ing_fin", parametri={"id_richiesta"}),
     *			@ElementoBreadcrumb(testo="Gestione fasi procedurali")
     * 	})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function gestioneIterProgettoAction($id_richiesta): Response {
        $richiesta = $this->getRichiesta($id_richiesta);
        $gestoreRichieste = $this->getGestoreRichiesta($richiesta);

        return $gestoreRichieste->gestioneFasiProcedurali($richiesta);
    }

    /**
     * @Route("/{id_richiesta}/gestione_impegni_ing_fin", name="gestione_impegni_ing_fin")
     * @PaginaInfo(titolo="Impegni e disimpegni", sottoTitolo="pagina di gestione degli impegni e dei disimpegni")
     * * @Menuitem(menuAttivo="elencoRichiesteAt")
     * @Breadcrumb(elementi={
     * 			@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_ing_fin"),
     * 			@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_ing_fin", parametri={"id_richiesta"}),
     * 			@ElementoBreadcrumb(testo="Gestione impegni")
     * 	})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function gestioneImpegniAction($id_richiesta) {
        $richiesta = $this->getRichiesta($id_richiesta);
        return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->gestioneImpegni($richiesta);
    }

    /**
     * @Route("/{id_richiesta}/modifica_impegno_ing_fin/{id_impegno}", name="modifica_impegno_ing_fin", defaults={"id_impegno" : NULL})
     * @PaginaInfo(titolo="Impegni e disimpegni", sottoTitolo="pagina di gestione degli impegni e dei disimpegni")
     * @Menuitem(menuAttivo="elencoRichiesteAt")
     * @Breadcrumb(elementi={
     * 			@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 			@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_ing_fin", parametri={"id_richiesta"}),
     * 			@ElementoBreadcrumb(testo="Gestione impegni", route="gestione_impegni_ing_fin", parametri={"id_richiesta", "id_impegno"}),
     *			@ElementoBreadcrumb(testo="Modifica impegno")
     * 	})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_impegno
     */
    public function modificaImpegnoAction($id_richiesta, $id_impegno) {
        $richiesta = $this->getRichiesta($id_richiesta);
        $impegno = null;
        if (!\is_null($id_impegno)) {
            $impegno = $this->getEm()->getRepository('AttuazioneControlloBundle:RichiestaImpegni')->find($id_impegno);
            if ($richiesta->getSoggetto()->getId() !== $impegno->getSoggetto()->getId()) {
                $impegno = null;
            }
        }
        return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->gestioneFormImpegno($richiesta, $impegno);
    }

    /**
     * @Route("/{id_richiesta}/elimina_impegno_ing_fin/{id_impegno}", name="elimina_impegno_ing_fin")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_impegno
     */
    public function eliminaImpegnoAction($id_richiesta, $id_impegno) {
        $richiesta = $this->getRichiesta($id_richiesta);
        return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->eliminaImpegno($richiesta, $id_impegno);
    }
}
