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
use RichiesteBundle\Ricerche\RicercaAcquisizione;
use DocumentoBundle\Component\ResponseException;
use RichiesteBundle\Entity\Proponente;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/acquisizioni")
 */
class AcquisizioniController extends AbstractController {
    /**
     * @Route("/seleziona_procedura", name="seleziona_procedura_acquisizioni")
     * @Method({"GET", "POST"})
     * @Template("RichiesteBundle:ProcedureParticolari:selezionaProcedura.html.twig")
     * @PaginaInfo(titolo="Elenco bandi", sottoTitolo="mostra l'elenco delle procedure trasporto disponibili")
     * @Menuitem(menuAttivo="creaTrasporto")
     */
    public function selezionaProceduraAction(Request $request) {
        $em = $this->getEm();
        $this->creaRichiesta();
        $options["url_indietro"] = $this->generateUrl("elenco_richieste");
        $options["classeProcedura"] = 'SfingeBundle:Acquisizioni';
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
            if (\is_null($procedura)) {
                $this->addFlash('error', "Non è stata selezionato alcuna procedura");
                return ["form" => $form->createView()];
            }
            if ($form->isValid()) {
                $this->setInfoRichiestaDaProcedura();
                $connection = $em->getConnection();
                try {
                    $connection->beginTransaction();
                    $em->persist($proponente);
                    $em->persist($this->richiesta);
                    $this->get("sfinge.stati")->avanzaStato($this->richiesta, "PRE_INSERITA");
                    $em->flush();
                    $connection->commit();
                    $this->addFlash('success', "Modifiche salvate correttamente");
                    return $this->redirect($this->generateUrl('nuova_richiesta_acquisizioni', ['id_richiesta' => $this->richiesta->getId()]));
                } catch (ResponseException $e) {
                    if ($connection->isTransactionActive()) {
                        $connection->rollBack();
                    }
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }
        return ["form" => $form->createView()];
    }

    /**
     * @Route("/{id_richiesta}/nuova_richiesta_acquisizioni", name="nuova_richiesta_acquisizioni")
     * @Method({"GET", "POST"})
     * @Menuitem(menuAttivo="creaTrasporto")
     * @param mixed $id_richiesta
     */
    public function nuovaRichiestaAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->nuovaRichiesta($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/elenco/{sort}/{direction}/{page}", defaults={"sort" : "i.id", "direction" : "asc", "page" : "1"}, name="elenco_richieste_acquisizioni")
     * @PaginaInfo(titolo="Elenco richieste", sottoTitolo="mostra l'elenco delle richieste presentate")
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco richieste")})
     */
    public function elencoRichiesteAction() {
        $datiRicerca = new RicercaAcquisizione();
        $datiRicerca->setUtente($this->getUser());

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        $params = ['richieste' => $risultato["risultato"],
            "form_ricerca_richieste" => $risultato["form_ricerca"],
            "filtro_attivo" => $risultato["filtro_attivo"], ];
        $view = $this->renderView("RichiesteBundle:ProcedureParticolari:elencoRichiestePP.html.twig", $params);

        return new \Symfony\Component\HttpFoundation\Response($view);
    }

    /**
     * @Route("/elenco_richieste_pulisci", name="elenco_richieste_pulisci_acquisizioni")
     */
    public function elencoRichiestePulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaAcquisizione());
        return $this->redirectToRoute("elenco_richieste_acquisizioni");
    }

    /**
     * @Route("/{id_richiesta}/dettaglio_richiesta_acquisizioni", name="dettaglio_richiesta_acquisizioni")
     * @PaginaInfo(titolo="Richiesta", sottoTitolo="pagina con le sezioni della richiesta da compilare")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @param mixed $id_richiesta
     */
    public function dettaglioRichiesteAction($id_richiesta) {
        $this->getSession()->set("id_richiesta", $id_richiesta);
        $response = $this->get("gestore_richieste")->getGestore()->dettaglioRichiesta($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/piano_costi/{id_proponente}", name="piano_costi_acquisizioni")
     * @Method({"GET", "POST"})
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function pianoDeiCostiAction($id_richiesta, $id_proponente) {
        $em = $this->getDoctrine()->getManager();
        /** @var Proponente $proponente */
        $proponente = $em->getRepository("RichiesteBundle\Entity\Proponente")->find($id_proponente);
        $gestoreRichiesta = $this->get("gestore_richieste")->getGestore($proponente->getRichiesta()->getProcedura());

        if (0 == count($proponente->getVociPianoCosto())) {
            $esitoP = $gestoreRichiesta->generaPianoDeiCosti($proponente->getId());
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

            return $this->redirectToRoute('piano_costi_acquisizioni', ['id_richiesta' => $id_richiesta, 'id_proponente' => $id_proponente]);
        }

        $response = $gestoreRichiesta->aggiornaPianoDeiCosti($proponente->getId());
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elenco_proponenti", name="elenco_proponenti_acquisizioni")
     * @PaginaInfo(titolo="Elenco proponenti", sottoTitolo="mostra l'elenco dei proponenti per la richiesta")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @param mixed $id_richiesta
     */
    public function elencoProponentiAction($id_richiesta) {
        $response = $this->get("gestore_proponenti")->getGestore()->elencoProponenti($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/dettaglio_proponente/{id_proponente}", name="dettaglio_proponente_acquisizioni")
     * @PaginaInfo(titolo="Dettaglio proponente", sottoTitolo="dettaglio di un proponente associato ad una richiesta")
     * @Template("RichiesteBundle:Richieste:dettaglioProponente.html.twig")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti_acquisizioni", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettaglio proponente")
     * 				})
     * @Method({"GET", "POST"})
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function dettaglioProponenteAction($id_richiesta, $id_proponente) {
        $response = $this->get("gestore_proponenti")->getGestore()->dettagliProponente($id_proponente);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/dati_progetto", name="dati_progetto_acquisizioni")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Gestione dei dati del progetto", sottoTitolo="inserire titolo e abstract")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione dati progetto")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @param mixed $id_richiesta
     */
    public function gestioneDatiProgettoAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->gestioneDatiProgetto($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/cerca_referente/{id_proponente}/{page}", defaults={"page" : 1}, name="cerca_referente_acquisizioni")
     * @PaginaInfo(titolo="Aggiunta referente", sottoTitolo="pagina per cercare ed aggiungere un nuovo referente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti_acquisizioni", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettagli proponente", route="dettaglio_proponente_acquisizioni", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Aggiunta referente")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function cercaReferenteAction($id_richiesta, $id_proponente) {
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->cercaReferente($id_proponente);
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_acquisizioni");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste_acquisizioni");
        }

        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/inserisci_referente/{id_proponente}/{persona_id}", name="inserisci_referente_acquisizioni")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Selezione tipologia", sottoTitolo="pagina per indicare la tipologia di relazione con il proponente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti_acquisizioni", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettagli proponente", route="dettaglio_proponente_acquisizioni", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Aggiunta referente", route="cerca_referente_acquisizioni", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Selezione tipologia")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     * @param mixed $persona_id
     */
    public function inserisciReferenteAction($id_richiesta, $id_proponente, $persona_id) {
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->inserisciReferente($id_proponente, $persona_id);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_acquisizioni");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste_acquisizioni");
        }
    }

    /**
     * @Route("/{id_richiesta}/completa_richiesta", name="completa_richiesta_acquisizioni")
     * @Method({"GET"})
     * @param mixed $id_richiesta
     */
    public function completaRichiesta($id_richiesta) {
        $this->get('base')->checkCsrf('token');
        try {
            $response = $this->get("gestore_richieste")->getGestore()->completaRichiesta($id_richiesta);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_acquisizioni");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste_acquisizioni");
        }
    }

    /**
     * @Route("/{id_richiesta}/dati_protocollo", name="dati_protocollo_acquisizioni")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Gestione dei dati del progetto", sottoTitolo="inserire titolo e abstract")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Gestione dati progetto")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @param mixed $id_richiesta
     */
    public function gestioneDatiProtocolloAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->datiProtocollo($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elenco_documenti_richiesta", name="elenco_documenti_richiesta_acquisizioni")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Elenco Documenti", sottoTitolo="carica i documenti richiesti")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco Documenti")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function elencoDocumentiRichiestaAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->elencoDocumenti($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elimina_documento_richiesta/{id_documento_richiesta}", name="elimina_documento_richiesta_acquisizioni")
     * @param mixed $id_documento_richiesta
     * @param mixed $id_richiesta
     */
    public function eliminaDocumentoRichiestaAction($id_documento_richiesta, $id_richiesta) {
        $this->get('base')->checkCsrf('token');
        $response = $this->get("gestore_richieste")->getGestore()->eliminaDocumentoRichiesta($id_documento_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/gestione_indicatori_acquisizioni", name="gestione_indicatori_acquisizioni")
     * @PaginaInfo(titolo="Indicatori", sottoTitolo="pagina di gestione degli indicatori")
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @Breadcrumb(elementi={
     * 			@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 			@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
     * 			@ElementoBreadcrumb(testo="Indicatori")
     * 	})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function gestioneIndicatoriAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->findOneById($id_richiesta);
        if (\is_null($richiesta)) {
            throw new SfingeException('Richiesta non trovata');
        }
        return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->gestioneIndicatori($richiesta);
    }

    /**
     * @Route("/{id_richiesta}/dati_cup_acquisizioni", name="dati_cup_acquisizioni")
     * @PaginaInfo(titolo="Dati cup richiesta")
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @Breadcrumb(elementi={
     * 			@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 			@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
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
     * @Route("/{id_richiesta}/gestione_impegni_acquisizioni", name="gestione_impegni_acquisizioni")
     * @PaginaInfo(titolo="Impegni e disimpegni", sottoTitolo="pagina di gestione degli impegni e dei disimpegni")
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @Breadcrumb(elementi={
     * 			@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 			@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
     * 			@ElementoBreadcrumb(testo="Gestione impegni")
     * 	})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function gestioneImpegniAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->findOneById($id_richiesta);
        if (\is_null($richiesta)) {
            throw new SfingeException('Richiesta non trovata');
        }
        return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->gestioneImpegni($richiesta);
    }

    /**
     * @Route("/{id_richiesta}/modifica_impegno_acquisizioni/{id_impegno}", name="modifica_impegno_acquisizioni", defaults={"id_impegno" : NULL})
     * @PaginaInfo(titolo="Impegni e disimpegni", sottoTitolo="pagina di gestione degli impegni e dei disimpegni")
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @Breadcrumb(elementi={
     * 			@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 			@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
     * 			@ElementoBreadcrumb(testo="Gestione impegni", route="gestione_impegni_acquisizioni", parametri={"id_richiesta", "id_impegno"}),
     *			@ElementoBreadcrumb(testo="Modifica impegno")
     * 	})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_impegno
     */
    public function modificaImpegnoAction($id_richiesta, $id_impegno) {
        $em = $this->getEm();
        $impegno = null;
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->findOneById($id_richiesta);
        if (!\is_null($id_impegno)) {
            $impegno = $em->getRepository('AttuazioneControlloBundle:RichiestaImpegni')->findOneById($id_impegno);
            if ($richiesta->getSoggetto()->getId() !== $impegno->getSoggetto()->getId()) {
                $impegno = null;
            }
        }
        return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->gestioneFormImpegno($richiesta, $impegno);
    }

    /**
     * @Route("/{id_richiesta}/elimina_impegno_acquisizioni/{id_impegno}", name="elimina_impegno_acquisizioni")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_impegno
     */
    public function eliminaImpegnoAction($id_richiesta, $id_impegno) {
        $this->get('base')->checkCsrf('token');
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->findOneById($id_richiesta);
        if (\is_null($richiesta)) {
            throw new SfingeException('Pagamento non trovato');
        }
        return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->eliminaImpegno($richiesta, $id_impegno);
    }

    /**
     * @Route("/{id_richiesta}/gestione_procedura_aggiudicazione_acquisizioni", name="gestione_procedura_aggiudicazione_acquisizioni")
     * @PaginaInfo(titolo="Procedure di aggiudicazione", sottoTitolo="pagina di gestione degli impegni e dei disimpegni")
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @Breadcrumb(elementi={
     * 			@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 			@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
     * 			@ElementoBreadcrumb(testo="Gestione procedure aggiudicazione")
     * 	})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     */
    public function gestioneProceduraAggiudicazioneAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->findOneById($id_richiesta);
        if (\is_null($richiesta)) {
            throw new SfingeException('Richiesta non trovata');
        }
        return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->gestioneProceduraAggiudicazione($richiesta);
    }

    /**
     * @Route("/{id_richiesta}/modifica_procedura_aggiudicazione_acquisizioni/{id_procedura_aggiudicazione}", name="modifica_procedura_aggiudicazione_acquisizioni", defaults={"id_procedura_aggiudicazione" : NULL})
     * @PaginaInfo(titolo="Procedure di aggiudicazione", sottoTitolo="Modifica o inserimento delle procedure di aggiudicazione")
     * @Menuitem(menuAttivo="elencoRichiesteTr")
     * @Breadcrumb(elementi={
     * 			@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 			@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
     * 			@ElementoBreadcrumb(testo="Gestione procedure aggiudicazione", route="gestione_procedura_aggiudicazione_acquisizioni", parametri={"id_richiesta"}),
     *			@ElementoBreadcrumb(testo="Modifica procedura aggiudicazione")
     * 	})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_procedura_aggiudicazione
     */
    public function gestioneModificaProceduraAggiudicazioneAction($id_richiesta, $id_procedura_aggiudicazione) {
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->findOneById($id_richiesta);
        if (\is_null($richiesta)) {
            throw new SfingeException('Richiesta non trovata');
        }
        return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->gestioneModificaProceduraAggiudicazione($richiesta, $id_procedura_aggiudicazione);
    }

    /**
     * @Route("/{id_richiesta}/elimina_procedura_aggiudicazione_acquisizioni/{id_procedura_aggiudicazione}", name="elimina_procedura_aggiudicazione_acquisizioni")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:ProceduraAggiudicazione", opzioni={"id" : "id_procedura_aggiudicazione"})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_procedura_aggiudicazione
     */
    public function gestioneEliminaProceduraAggiudicazioneAction($id_richiesta, $id_procedura_aggiudicazione) {
        $this->get('base')->checkCsrf('token');
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->findOneById($id_richiesta);
        if (\is_null($richiesta)) {
            throw new SfingeException('Richiesta non trovata');
        }
        return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->gestioneEliminaProceduraAggiudicazione($richiesta, $id_procedura_aggiudicazione);
    }

    /**
     * @Route("/{id_richiesta}/iter_progetto", name="gestione_iter_progetto_acquisizioni")
     * @PaginaInfo(titolo="Fasi procedurali", sottoTitolo="pagina di gestione delle fasi procedurali")
     * @Menuitem(menuAttivo="elencoRichiesteAt")
     * @Breadcrumb(elementi={
     * 			@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_acquisizioni"),
     * 			@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta"}),
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
}
