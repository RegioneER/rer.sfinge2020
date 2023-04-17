<?php
namespace AttuazioneControlloBundle\Controller\Istruttoria;

use AttuazioneControlloBundle\Entity\Istruttoria\AllegatoComunicazionePagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaComunicazionePagamento;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Service\Istruttoria\GestorePagamentiBase;
use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/istruttoria/pagamenti")
 */
class PagamentiController extends BaseController {

    /**
     * @Route("/elenco_pagamenti/{sort}/{direction}/{page}", defaults={"sort" = "i.id", "direction" = "asc", "page" = "1"}, name="elenco_istruttoria_pagamenti")
     * @PaginaInfo(titolo="Elenco pagamenti in istruttoria",sottoTitolo="mostra l'elenco dei pagamenti richiesti")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco pagamenti")})
     */
    public function elencoPagamentiAction() {

        ini_set('memory_limit', '512M');

        $datiRicerca = new \AttuazioneControlloBundle\Form\Entity\Istruttoria\RicercaPagamenti();
        $datiRicerca->setUtente($this->getUser());

        $em = $this->getEm();
        $istruttori = $em->getRepository("SfingeBundle\Entity\Utente")->cercaIstruttoriAtc();
        $datiRicerca->setIstruttori($istruttori);

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);
        // Visto che il record-set viene generato tramite DQL ho dovuto fare questo workaround per popolare il campo "certificazioni".
        $risultato["risultato"] = $this->get('app.manager.pagamento_manager')->aggiornaSlidingPaginationConCertificazione($risultato["risultato"]);
        // Ho inserito il calcolo del contatore nell'entity in modo tale che sia richiamabile anche al di fuori del twig.
        // Visto che il record-set viene generato tramite DQL ho dovuto fare questo workaround per popolare il campo "contatore".
        $risultato["risultato"] = $this->get('app.manager.pagamento_manager')->aggiornaSlidingPaginationConValoreContatore($risultato["risultato"]);

        return $this->render('AttuazioneControlloBundle:Istruttoria\Pagamenti:elencoPagamenti.html.twig', array('risultati' => $risultato["risultato"], "formRicerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]));
    }

    /**
     * @Route("/elenco_pagamenti_pulisci", name="elenco_istruttoria_pagamenti_pulisci")
     */
    public function elencoPagamentiPulisciAction() {
        $this->get("ricerca")->pulisci(new \AttuazioneControlloBundle\Form\Entity\Istruttoria\RicercaPagamenti());
        return $this->redirectToRoute("elenco_istruttoria_pagamenti");
    }

    /**
     * @Route("/valuta/{id_valutazione_checklist}", name="valuta_checklist_istruttoria_pagamenti")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ValutazioneChecklistIstruttoria", opzioni={"id" = "id_valutazione_checklist"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function valutaChecklistAction($id_valutazione_checklist) {
        $valutazione_checklist = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento")->find($id_valutazione_checklist);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($valutazione_checklist->getPagamento()->getProcedura())->valutaChecklist($valutazione_checklist);
    }

    /**
     * @Route("/{id_pagamento}/riepilogo", name="riepilogo_istruttoria_pagamento")
     * @PaginaInfo(titolo="Riepilogo del pagamento in istruttoria",sottoTitolo="dati riepilogativi del pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function riepilogoPagamentoAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        /* if($pagamento->getProcedura()->getId() == 8 || $pagamento->getProcedura()->getId() == 32){
          $this->addWarning('Le istruttorie per questi bandi sono al momento bloccate.');
          return $this->elencoPagamentiAction();
          } */

        if ($pagamento->getProcedura()->getId() == 8 && $pagamento->getModalitaPagamento()->getCodice() == "ANTICIPO") {
            return $this->get("gestore_istruttoria_pagamenti")->getGestoreBase()->riepilogoPagamento($pagamento);
        }
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->riepilogoPagamento($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/documenti_pagamento", name="documenti_istruttoria_pagamenti")
     * @PaginaInfo(titolo="Documenti pagamento",sottoTitolo="documenti caricati per il pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function documentiPagamentoAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->documentiPagamento($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/documenti_istruttoria_bando7", name="gestione_documenti_istruttoria_bando7")
     * @PaginaInfo(titolo="Documenti istruttoria",sottoTitolo="Gestione documenti istruttoria")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     */
    public function documentiIstruttoriaBando7Action($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->documentiIstruttoriaBando7($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/documenti_istruttoria_bando8", name="gestione_documenti_istruttoria_bando8")
     * @PaginaInfo(titolo="Documenti istruttoria",sottoTitolo="Gestione documenti istruttoria")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     */
    public function documentiIstruttoriaBando8Action($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->documentiIstruttoriaBando8($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/elimina_documento_istruttoria_pagamento/{id_documento}", name="elimina_documento_istruttoria_pagamento")
     */
    public function eliminaDocumentoAttuazioneAction($id_pagamento, $id_documento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->eliminaDocumentoIstruttoriaPagamento($pagamento, $id_documento);
    }

    /**
     * @Route("/{id_pagamento}/modifica_documento_istruttoria_pagamento/{id_documento}", name="modifica_documento_istruttoria_pagamento")
     */
    public function modificaDocumentoAttuazioneAction($id_pagamento, $id_documento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->modificaDocumentoIstruttoriaPagamento($pagamento, $id_documento);
    }

    /**
     * @Route("/{id_pagamento}/richiedi_integrazione", name="richiedi_integrazione_pagamento")
     * @PaginaInfo(titolo="Richiesta integrazione pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function richiediIntegrazioneAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->richiediIntegrazione($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/avanzamento_piano_costi_istruttoria/{id_richiesta}/{id_proponente}/{annualita}", name="avanzamento_piano_costi_istruttoria")
     * @PaginaInfo(titolo="Avanzamento piano costi",sottoTitolo="mostra l'avanzamento del piano costi del pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function avanzamentoPianoCostiAction($id_richiesta, $id_pagamento, $id_proponente, $annualita) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $proponente = $this->getEm()->getRepository("RichiesteBundle\Entity\Proponente")->find($id_proponente);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->avanzamentoPianoCosti($richiesta, $proponente, $pagamento, $annualita == "0" ? null : $annualita);
    }

    /*     * ************* NUOVE ACTION ***************** */

    /**
     * @Route("/{id_pagamento}/date_progetto_istruttoria", name="date_progetto_istruttoria")
     * @PaginaInfo(titolo="Dati generali pagamento",sottoTitolo="date di rendicontazione")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function dateProgettoAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->dateProgettoPagamento($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/dati_bancari_pagamento_istruttoria", name="dati_bancari_pagamento_istruttoria")
     * @PaginaInfo(titolo="Dati generali pagamento",sottoTitolo="dati bancari")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function datiBancariAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->datiBancariPagamento($pagamento);
    }

    /**
     * 
     * @Route("/{id_pagamento}/elenco_ricercatori_istruttoria", name="elenco_ricercatori_istruttoria")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @PaginaInfo(titolo="Elenco ricercatori")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function elencoRicercatoriAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->elencoRicercatori($pagamento);
    }

    /**
     * 
     * @Route("/elenco_documenti_ricercatore_istruttoria/{id_ricercatore}", name="elenco_documenti_ricercatore_istruttoria")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @PaginaInfo(titolo="Elenco Documenti",sottoTitolo="elenco documenti richiesti")
     * ControlloAccesso(contesto="soggetto", classe="AnagraficheBundle:Personale", opzioni={"id" = "id_ricercatore"})
     */
    public function elencoDocumentiRicercatoreAction($id_ricercatore) {
        $ricercatore = $this->getEm()->getRepository("AnagraficheBundle:Personale")->find($id_ricercatore);
        $pagamento = $ricercatore->getPagamento();
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->elencoDocumentiCaricati($id_ricercatore);
        //return $response->getResponse();
    }

    /**
     * 
     * @Route("/istruttoria_documento_ricercatore/{id_documento_ricercatore}", name="istruttoria_documento_ricercatore")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @PaginaInfo(titolo="Elenco Documenti",sottoTitolo="elenco documenti richiesti")
     * ControlloAccesso(contesto="soggetto", classe="AnagraficheBundle:Personale", opzioni={"id" = "id_ricercatore"})
     */
    public function istruttoriaDocRicercatoreAction($id_documento_ricercatore) {
        if ($this->isGranted("ROLE_OPERATORE_COGEA")) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }
        $documento_personale = $this->getEm()->getRepository("AnagraficheBundle:DocumentoPersonale")->find($id_documento_ricercatore);
        $ricercatore = $documento_personale->getPersonale();
        $pagamento = $ricercatore->getPagamento();
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->istruttoriaDocRicercatore($id_documento_ricercatore);
    }

    /**
     * @Route("/visualizza_ricercatore_istruttoria/{id_ricercatore}", name="visualizza_ricercatore_istruttoria")
     * @PaginaInfo(titolo="Dati ricercatore")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AnagraficheBundle:Personale", opzioni={"id" = "id_ricercatore"})
     */
//	public function visualizzaRicercatoreAction($id_ricercatore) {		
//		$ricercatore = $this->getEm()->getRepository("AnagraficheBundle\Entity\Personale")->find($id_ricercatore);
//		$pagamento = $ricercatore->getPagamento();
//		return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->visualizzaRicercatore($ricercatore);
//	}	

    /**
     * @Route("/{id_pagamento}/gestione_durc_istruttoria", name="gestione_durc_istruttoria")
     * @PaginaInfo(titolo="Dati proponenti",sottoTitolo="dati proponenti")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestioneDurcAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->gestioneDurc($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_dichiarazioni_proponenti_istruttoria", name="gestione_dichiarazioni_proponenti_istruttoria")
     * @PaginaInfo(titolo="Documenti proponenti",sottoTitolo="pagina di gestione dei documenti dei proponenti")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestioneDichiarazioniAltriProponentiAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->gestioneDichiarazioniAltriProponenti($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_documenti_istruttoria", name="gestione_documenti_pagamento_istruttoria")
     * @PaginaInfo(titolo="Documenti pagamento",sottoTitolo="pagina di gestione dei documenti del pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestioneDocumentiAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->gestioneDocumentiPagamento($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/singolo_doc_pagamento_istruttoria/{id_documento_pagamento}", name="singolo_doc_pagamento_istruttoria")
     * @PaginaInfo(titolo="Documenti pagamento",sottoTitolo="pagina di gestione dei documenti del pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function istruttoriaSingoloDocumentoPagamentoAction($id_pagamento, $id_documento_pagamento) {
        if ($this->isGranted("ROLE_OPERATORE_COGEA")) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->istruttoriaSingoloDocumentoPagamento($id_pagamento, $id_documento_pagamento);
    }

    /**
     * @Route("/questionario_istruttoria_valuta/{id_pagamento}", name="questionario_pagamento_istruttoria_valuta")
     * @PaginaInfo(titolo="Monitoraggio e dichiarazioni", sottoTitolo="pagina di istruttoria per il monitoraggio e le dichiarazioni")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function istruttoriaQuestionarioAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->istruttoriaMonitoraggio($id_pagamento);
    }

    /**
     * @Route("/questionario_istruttoria/{id_istanza_pagina}/{id_pagina}/{id_istanza_frammento}/{azione}", name="questionario_pagamento_istruttoria", defaults={"id_istanza_pagina" = "-", "id_pagina" = "-","id_istanza_frammento" = "-", "azione" = "modifica"})
     */
    public function questionarioAction(\Symfony\Component\HttpFoundation\Request $request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione) {

        $em = $this->getEm();

        if ($id_istanza_pagina != "-") {
            $istanza_pagina = $em->getRepository("FascicoloBundle\Entity\IstanzaPagina")->find($id_istanza_pagina);
        } else {
            $istanza_frammento = $em->getRepository("FascicoloBundle\Entity\IstanzaFrammento")->find($id_istanza_frammento);
            $istanza_pagina = $istanza_frammento->getIstanzaPagina();
        }

        $istanza_fascicolo = $istanza_pagina->getIstanzaFascicolo();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->findOneBy(array("istanza_fascicolo" => $istanza_fascicolo));

        if (is_null($pagamento)) {
            throw new SfingeException("Pagamento o richiesta non trovati");
        }

        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        if (is_null($richiesta)) {
            throw new SfingeException("Pagamento o richiesta non trovati");
        }

//		$contestoSoggetto = $this->get('contesto')->getContestoRisorsa($richiesta, "soggetto");
//		$accessoConsentito = $this->isGranted(\SoggettoBundle\Security\SoggettoVoter::ALL, $contestoSoggetto);
//		$contestoProcedura = $this->get('contesto')->getContestoRisorsa($richiesta, "procedura");
//		$accessoConsentito |= $this->isGranted(\SfingeBundle\Security\ProceduraVoter::READ, $contestoProcedura);

        $accessoConsentito = true;
        if (!$accessoConsentito) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $this->getSession()->set("fascicolo.route_istanza_pagina", "questionario_pagamento_istruttoria");

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl("riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId())));

        return $this->get("fascicolo.istanza")->istanzaPagina($request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, "visualizza");
    }

    /**
     * @Route("/{id_pagamento}/scarica_pagamento_istruttoria", name="scarica_pagamento_istruttoria")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function scaricaDomandaAction($id_pagamento) {
        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        if (is_null($pagamento)) {
            return $this->addErrorRedirect("Pagamento non valida", "dettaglio_pagamento", array("id_pagamento" => $id_pagamento));
        }

        if (is_null($pagamento->getDocumentoPagamento())) {
            return $this->addErrorRedirect("Nessun documento associato al pagamento", "dettaglio_pagamento", array("id_pagamento" => $id_pagamento));
        }

        return $this->get("documenti")->scaricaDaId($pagamento->getDocumentoPagamento()->getId());
    }

    /**
     * @Route("/{id_pagamento}/scarica_pagamento_firmato_istruttoria", name="scarica_pagamento_firmato_istruttoria")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function scaricaPagamentoFirmatoAction($id_pagamento) {
        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        if (is_null($pagamento)) {
            return $this->addErrorRedirect("Pagamento non valida", "dettaglio_pagamento", array('id_pagamento' => $id_pagamento));
        }

        if (is_null($pagamento->getDocumentoPagamentoFirmato())) {
            return $this->addErrorRedirect("Nessun documento associato al pagamento", "dettaglio_pagamento", array('id_pagamento' => $id_pagamento));
        }

        return $this->get("documenti")->scaricaDaId($pagamento->getDocumentoPagamentoFirmato()->getId());
    }

    /**
     * @Route("/integrazione_pagamento/{id_pagamento}", name="integrazione_pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @PaginaInfo(titolo="Gestione integrazione",sottoTitolo="gestione delle richieste di integrazione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function integrazioneAction($id_pagamento) {
        try {
            $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
            $richiesta = $pagamento->getRichiesta();
            $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->riepilogoIntegrazione($pagamento)->getResponse();
        } catch (SfingeException $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect($e->getMessage(), "riepilogo_istruttoria_pagamento", array("id_pagamento" => $id_pagamento));
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "riepilogo_istruttoria_pagamento", array("id_pagamento" => $id_pagamento));
        }
    }

    /**
     * @Route("/crea_integrazione_pagamento/{id_pagamento}", name="crea_integrazione_pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @PaginaInfo(titolo="Crea integrazione",sottoTitolo="inserimento di una richiesta di integrazione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function creaIntegrazioneAction($id_pagamento) {
        try {
            $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
            $richiesta = $pagamento->getRichiesta();
            $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->creaIntegrazione($pagamento)->getResponse();
        } catch (SfingeException $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect($e->getMessage(), "riepilogo_istruttoria_pagamento", array("id_pagamento" => $id_pagamento));
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "riepilogo_istruttoria_pagamento", array("id_pagamento" => $id_pagamento));
        }
    }

    /**
     * @Route("/gestione_richiesta_integrazione/{id_integrazione}", name="gestione_richiesta_integrazione")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @PaginaInfo(titolo="Gestione integrazione",sottoTitolo="pagina di gestione di una richiesta di integrazione")
     * ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ValutazioneChecklistIstruttoria", opzioni={"id" = "id_valutazione_checklist"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function gestioneRichiestaIntegrazioneAction($id_integrazione) {
        $integrazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento")->find($id_integrazione);
        $pagamento = $integrazione->getPagamento();
        try {
            $richiesta = $pagamento->getRichiesta();
            $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->gestioneRichiestaIntegrazione($integrazione)->getResponse();
        } catch (SfingeException $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect($e->getMessage(), "riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));
        }
    }

    /**
     * @Route("/cancella_richiesta_integrazione/{id_integrazione}", name="cancella_richiesta_integrazione")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ValutazioneChecklistIstruttoria", opzioni={"id" = "id_valutazione_checklist"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function cancellaRichiestaIntegrazioneAction($id_integrazione) {
        $integrazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento")->find($id_integrazione);
        $pagamento = $integrazione->getPagamento();
        try {
            $richiesta = $pagamento->getRichiesta();
            $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->cancellaRichiestaIntegrazione($integrazione);
        } catch (SfingeException $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect($e->getMessage(), "riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));
        }
    }

    /**
     * @Route("/{id_integrazione}/istruttoria_integrazione", name="istruttoria_integrazione")
     * @PaginaInfo(titolo="Istruttoria integrazione",sottoTitolo="pagina per l'istruttoria della risposta del beneficiario alla richiesta di integrazione")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     */
    public function istruttoriaIntegrazioneAction($id_integrazione) {
        $integrazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento")->find($id_integrazione);
        $pagamento = $integrazione->getPagamento();
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->istruttoriaIntegrazione($integrazione);
    }

    /**
     * @Route("/{id_integrazione}/istruttoria_documento_integrazione/{id_documento_integrazione}", name="istruttoria_documento_integrazione")
     * @PaginaInfo(titolo="Istruttoria documento di integrazione",sottoTitolo="pagina di istruttoria dei documenti di integrazione")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"})  
     */
    public function istruttoriaDocumentoIntegrazioneAction($id_integrazione, $id_documento_integrazione) {
        if ($this->isGranted("ROLE_OPERATORE_COGEA")) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }
        $integrazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento")->find($id_integrazione);
        $pagamento = $integrazione->getPagamento();
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->istruttoriaDocumentoIntegrazione($integrazione, $id_documento_integrazione);
    }

    /**
     * @Route("/{id_pagamento}/{id_proponente}/documenti_generali_pagamento_istruttoria", name="documenti_generali_pagamento_istruttoria")
     * @PaginaInfo(titolo="Istruttoria documenti generali",sottoTitolo=" documenti generali del pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function documentiGeneraliPagamentoIstruttoriaAction($id_pagamento, $id_proponente) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->gestioneDocumentiGeneraliPagamento($id_pagamento, $id_proponente);
    }

    /**
     * @Route("/{id_pagamento}/{id_proponente}/istruttoria_documento_generale/{id_documento_generale}", name="istruttoria_documento_generale")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function istruttoriaDocumentoGeneraleAction($id_documento_generale, $id_pagamento, $id_proponente) {
        if ($this->isGranted("ROLE_OPERATORE_COGEA")) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->istruttoriaDocumentoGenerale($id_documento_generale, $id_proponente);
    }

    /**
     * @Route("/checklist_generale/{id_pagamento}", name="checklist_generale")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function checklistGenerale($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->checklistGenerale($pagamento);
    }

    /**
     * @Route("/richiesta_chiarimento_pagamento/{id_pagamento}", name="richiesta_chiarimento_pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @PaginaInfo(titolo="Gestione Richieste di chiarimenti",sottoTitolo="gestione delle richieste di chiarumenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function richiestaChiarimentiAction($id_pagamento) {
        try {
            $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
            //$this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo istruttoria', $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $richiesta->getId())));
            $richiesta = $pagamento->getRichiesta();
            $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->riepilogoRichiestaChiarimenti($pagamento)->getResponse();
        } catch (SfingeException $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect($e->getMessage(), "riepilogo_istruttoria_pagamento", array("id_pagamento" => $id_pagamento));
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "riepilogo_istruttoria_pagamento", array("id_pagamento" => $id_pagamento));
        }
    }

    /**
     * @Route("/crea_richiesta_chiarimenti_pagamento/{id_pagamento}", name="crea_richiesta_chiarimenti_pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @PaginaInfo(titolo="Crea richiesta di chiarimenti",sottoTitolo="inserimento di una richiesta di chiarimenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function creaRichiestaChiarimentiAction($id_pagamento) {
        try {
            $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
            //$this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo istruttoria', $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $richiesta->getId())));
            $richiesta = $pagamento->getRichiesta();
            $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->creaRichiestaChiarimenti($pagamento)->getResponse();
        } catch (SfingeException $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect($e->getMessage(), "riepilogo_istruttoria_pagamento", array("id_pagamento" => $id_pagamento));
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "riepilogo_istruttoria_pagamento", array("id_pagamento" => $id_pagamento));
        }
    }

    /**
     * @Route("/gestione_richiesta_chiarimenti/{id_richiesta_chiarimenti}", name="gestione_richiesta_chiarimenti")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @PaginaInfo(titolo="Gestione richieste di chiarimenti",sottoTitolo="pagina di gestione di una richiesta di chiarimenti")
     * ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ValutazioneChecklistIstruttoria", opzioni={"id" = "id_valutazione_checklist"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function gestioneRichiestaChiarimentiAction($id_richiesta_chiarimenti) {
        $richiesta_chiarimento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento")->find($id_richiesta_chiarimenti);
        $pagamento = $richiesta_chiarimento->getPagamento();
        try {
            //$this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo istruttoria', $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $richiesta->getId())));
            $richiesta = $pagamento->getRichiesta();
            $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->gestioneRichiestaChiarimenti($richiesta_chiarimento)->getResponse();
        } catch (SfingeException $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect($e->getMessage(), "riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));
        }
    }

    /**
     * @Route("/elimina_allegato_richiesta_chiarimenti/{id_allegato}", name="elimina_allegato_richiesta_chiarimenti")
     * ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ValutazioneChecklistIstruttoria", opzioni={"id" = "id_valutazione_checklist"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function eliminaAllegatoRichiestaChiarimento($id_allegato): Response {
        /** @var \AttuazioneControlloBundle\Entity\Istruttoria\AllegatoRichiestaChiarimento $allegato */
        $allegato = $this->getEm()->getRepository('AttuazioneControlloBundle:Istruttoria\AllegatoRichiestaChiarimento')->find($id_allegato);
        $richiesta_chiarimento = $allegato->getRichiestaChiarimento();
        $pagamento = $richiesta_chiarimento->getPagamento();
        $richiesta = $pagamento->getRichiesta();
        /** @var \AttuazioneControlloBundle\Service\Istruttoria\IGestorePagamenti $gestore_istruttoria */
        $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());

        try {
            $gestore_istruttoria->eliminaAllegatoRichiestaChiarimento($allegato);
            return $this->addSuccessRedirect(
                            'Allegato eliminato con successo',
                            'gestione_richiesta_chiarimenti',
                            ['id_richiesta_chiarimenti' => $richiesta_chiarimento->getId()]
            );
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect(
                            "Errore durante eliminazione dell'allegato",
                            'gestione_richiesta_chiarimenti',
                            ['id_richiesta_chiarimenti' => $richiesta_chiarimento->getId()]
            );
        }
    }

    /**
     * @Route("/{id_richiesta_chiarimenti}/istruttoria_richiesta_chiarimenti", name="istruttoria_richiesta_chiarimenti")
     * @PaginaInfo(titolo="Istruttoria richiesta di chiarimenti",sottoTitolo="pagina per l'istruttoria della risposta del beneficiario alla richiesta di chiarimenti")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     */
    public function istruttoriaRichiestaChiarimentiAction($id_richiesta_chiarimenti) {
        $richiesta_chiarimento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento")->find($id_richiesta_chiarimenti);
        $pagamento = $richiesta_chiarimento->getPagamento();
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->istruttoriaRichiestaChiarimenti($richiesta_chiarimento);
    }

    /**
     * @Route("/{id_richiesta_chiarimenti}/istruttoria_documento_rich_chiar/{id_documento_rich_chiar}", name="istruttoria_documento_rich_chiar")
     * @PaginaInfo(titolo="Istruttoria documento di integrazione",sottoTitolo="pagina di istruttoria dei documenti di integrazione")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"})  
     */
    public function istruttoriaDocumentoRichChiarAction($id_richiesta_chiarimenti, $id_documento_rich_chiar) {
        if ($this->isGranted("ROLE_OPERATORE_COGEA")) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }
        $richiesta_chiarimenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento")->find($id_richiesta_chiarimenti);
        $pagamento = $richiesta_chiarimenti->getPagamento();
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->istruttoriaDocumentoRichChiar($richiesta_chiarimenti, $id_documento_rich_chiar);
    }

    /**
     * @Route("/pdf_integrazione_istruttoria/{id_pagamento}", name="pdf_integrazione_istruttoria")
     * PaginaInfo(titolo="Monitoraggio e dichiarazioni", sottoTitolo="pagina di istruttoria per il monitoraggio e le dichiarazioni")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function pdfIntegrazioneIstruttoriaAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->pdfIntegrazioneIstruttoria($pagamento, true);
    }

    /**
     * @Route("/pdf_chiarimenti_istruttoria/{id_pagamento}", name="pdf_chiarimenti_istruttoria")
     * PaginaInfo(titolo="Monitoraggio e dichiarazioni", sottoTitolo="pagina di istruttoria per il monitoraggio e le dichiarazioni")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function pdfChiarimentiIstruttoriaAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->pdfChiarimentiIstruttoria($pagamento, true);
    }

    /**
     * @Route("/cancella_richiesta_chiarimenti/{id_richiesta_chiarimenti}", name="cancella_richiesta_chiarimenti")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ValutazioneChecklistIstruttoria", opzioni={"id" = "id_valutazione_checklist"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function cancellaRichiestaChiarimentiAction($id_richiesta_chiarimenti) {
        $richiesta_chiarimenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento")->find($id_richiesta_chiarimenti);
        $pagamento = $richiesta_chiarimenti->getPagamento();
        try {
            $richiesta = $pagamento->getRichiesta();
            $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->cancellaRichiestaChiarimenti($richiesta_chiarimenti);
        } catch (SfingeException $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect($e->getMessage(), "riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));
        }
    }

    /**
     * @Route("/{id_pagamento}/riepilogo_anticipi_pagamento", name="riepilogo_anticipi_pagamento")
     * @PaginaInfo(titolo="Anticipi del pagamento in istruttoria",sottoTitolo="riepilogo anticipi del pagamento")
     * @Menuitem(menuAttivo = "elencoAnticipiPagamento")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function riepilogoAnticipiPagamentoAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $pagamento_anticipo = $pagamento;
        $attuazione_controllo_richiesta = $pagamento->getAttuazioneControlloRichiesta();
        $pagamenti = $attuazione_controllo_richiesta->getPagamenti();
        foreach ($pagamenti as $pag) {
            if ($pag->getModalitaPagamento()->getCodice() == "ANTICIPO") {
                $pagamento_anticipo = $pag;
            }
        }
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->riepilogoAnticipiPagamento($pagamento, $pagamento_anticipo);
    }

    /**
     * @Route("/{id_pagamento}/gestisci_anticipo/{id_anticipo}", name="gestisci_anticipo_pagamento")
     * @PaginaInfo(titolo="Riepilogo anticipo del pagamento in istruttoria",sottoTitolo="dati riepilogativi dell'anticipo del pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestisciAnticipoAction($id_pagamento, $id_anticipo) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $anticipo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RipartizioneImportiPagamento")->find($id_anticipo);
        $pagamento_anticipo = $pagamento;
        $attuazione_controllo_richiesta = $pagamento->getAttuazioneControlloRichiesta();
        $pagamenti = $attuazione_controllo_richiesta->getPagamenti();
        foreach ($pagamenti as $pag) {
            if ($pag->getModalitaPagamento()->getCodice() == "ANTICIPO") {
                $pagamento_anticipo = $pag;
            }
        }
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->gestisciAnticipoPagamento($pagamento, $pagamento_anticipo, $anticipo);
    }

    /**
     * @Route("/crea_anticipo_pagamento/{id_pagamento}", name="crea_anticipo_pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @PaginaInfo(titolo="Crea anticipo",sottoTitolo="inserimento di un anticipo pagamento")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function creaAnticipoPagamentoAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $pagamento_anticipo = $pagamento;
        $attuazione_controllo_richiesta = $pagamento->getAttuazioneControlloRichiesta();
        $pagamenti = $attuazione_controllo_richiesta->getPagamenti();
        foreach ($pagamenti as $pag) {
            if ($pag->getModalitaPagamento()->getCodice() == "ANTICIPO") {
                $pagamento_anticipo = $pag;
            }
        }

        $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura());
        return $gestore_istruttoria->creaAnticipoPagamento($pagamento, $pagamento_anticipo);
    }

    /**
     * @Route("/{id_pagamento}/documenti_progetto", name="documenti_progetto_istruttoria")
     * @PaginaInfo(titolo="Documenti progetto",sottoTitolo="documenti di progetto caricati per il pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function documentiProgettoAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->gestioneDocumentiProgetto($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_antimafia", name="gestione_antimafia_istruttoria")
     * @PaginaInfo(titolo="Gestione antimafia",sottoTitolo="pagina di gestione antimafia")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestioneAntimafiaAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->gestioneAntimafiaPagamento($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/aggiungi_documento_istruttoria_pagamento", name="aggiungi_documento_istruttoria_pagamento")
     * @PaginaInfo(titolo="Caricamento documento istruttoria",sottoTitolo="pagina di caricamento di un documento per istruttoria pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function aggiungiDocumentoIstruttoriaPagamentoAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->aggiungiDocumentoIstruttoriaPagamento($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/relazione_finale_istruttoria", name="relazione_finale_istruttoria")
     * @PaginaInfo(titolo="Relazione finale a saldo",sottoTitolo="pagina di istruttoria della relazione finale a saldo")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function relazioneFinaleSaldoAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->relazioneFinale($pagamento);
    }

    /**
     * @Route("/aggiungi_checklist_appalti/{id_pagamento}", name="aggiungi_checklist_appalti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function aggiungiChecklistAppaltiAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->aggiungiChecklistAppalti($pagamento);
    }

    /**
     * @Route("/elimina_valutazione_checklist/{id_valutazione_checklist}", name="elimina_valutazione_checklist")
     */
    public function eliminaValutazioneChecklistAction($id_valutazione_checklist) {
        $valutazioneChecklist = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento")->find($id_valutazione_checklist);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($valutazioneChecklist->getProcedura())->eliminaValutazioneChecklist($valutazioneChecklist);
    }

    /**
     * @Route("/{id_valutazione_checklist}/aggiungi_documento_checklist_pagamento", name="aggiungi_documento_checklist_pagamento")
     * @PaginaInfo(titolo="Caricamento documento checklist",sottoTitolo="pagina di caricamento di un documento per checklist pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     */
    public function aggiungiDocumentoChecklistPagamentoAction($id_valutazione_checklist) {
        $valutazioneChecklistPagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento")->find($id_valutazione_checklist);
        $pagamento = $valutazioneChecklistPagamento->getPagamento();
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->aggiungiDocumentoChecklistPagamento($valutazioneChecklistPagamento);
    }

    /**
     * @Route("/{id_valutazione_checklist}/elimina_documento_checklist_pagamento/{id_documento}", name="elimina_documento_checklist_pagamento")
     */
    public function eliminaDocumentoChecklistAction($id_valutazione_checklist, $id_documento) {
        $valutazioneChecklistPagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento")->find($id_valutazione_checklist);
        $pagamento = $valutazioneChecklistPagamento->getPagamento();
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->eliminaDocumentoChecklistPagamento($valutazioneChecklistPagamento, $id_documento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_indicatori_output", name="gestione_indicatori_output_istruttoria")
     * @PaginaInfo(titolo="Elenco indicatori output",sottoTitolo="pagina di istruttoria degli indicatori output")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestioneIndicatoriOutputAction($id_pagamento): Response {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->gestioneIndicatoriOutput($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/indicatore_istruttoria/{id_indicatore}", name="indicatore_istruttoria")
     * @PaginaInfo(titolo="Dettaglio indicatore output",sottoTitolo="Dettaglio e documentazione dell'indicatore output")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function istruttoriaIndicatoreAction($id_pagamento, $id_indicatore): Response {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $indicatore = $this->getEm()->getRepository('RichiesteBundle:IndicatoreOutput')->find($id_indicatore);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->gestioneIndicatoreOutput($pagamento, $indicatore);
    }

    /**
     * @Route("estrazione_pag_voci/{id_procedura}", name="estrazione_pag_voci")
     */
    public function estrazionePagVociAction($id_procedura) {
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find($id_procedura);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($procedura)->estrazionePagamentiConVoci($procedura);
    }

    /**
     * @Route("/{id_procedura}/esporta_pagamenti", name="esporta_pagamenti")
     */
    public function esportaPagamentiAction($id_procedura) {
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find($id_procedura);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($procedura)->esportaPagamenti($procedura);
    }

    /**
     * @Route("/comunicazioni_pagamento/{id}", name="comunicazioni_pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @PaginaInfo(titolo="Gestione comunicazione", sottoTitolo="gestione delle comunicazioni")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     * 
     * @param Pagamento $pagamento
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function comunicazioniPagamentoAction(Pagamento $pagamento) {
        try {
            $richiesta = $pagamento->getRichiesta();
            /** @var GestorePagamentiBase $gestore_istruttoria */
            $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->riepilogoComunicazionePagamento($pagamento)->getResponse();
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza.",
                            "riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));
        }
    }

    /**
     * @Route("/crea_comunicazione_pagamento/{id}", name="crea_comunicazione_pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @PaginaInfo(titolo="Crea comunicazione",sottoTitolo="inserimento di una comunicazione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     * 
     * @param Pagamento $pagamento
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function creaComunicazionePagamentoAction(Pagamento $pagamento) {
        try {
            $richiesta = $pagamento->getRichiesta();
            /** @var GestorePagamentiBase $gestore_istruttoria */
            $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->creaComunicazionePagamento($pagamento)->getResponse();
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza.",
                            "riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));
        }
    }

    /**
     * @Route("/gestione_comunicazione_pagamento/{id}", name="gestione_comunicazione_pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @PaginaInfo(titolo="Gestione comunicazione",sottoTitolo="pagina di gestione di una comunicazione")
     * @ControlloAccesso(contesto="comunicazionePagamento", classe="AttuazioneControlloBundle:Istruttoria\ComunicazionePagamento", opzioni={"id": "id"}, azione=\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::READ)
     * 
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function gestioneComunicazionePagamentoAction(ComunicazionePagamento $comunicazionePagamento) {
        $pagamento = $comunicazionePagamento->getPagamento();
        try {
            $richiesta = $pagamento->getRichiesta();
            /** @var GestorePagamentiBase $gestore_istruttoria */
            $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->gestioneComunicazionePagamento($comunicazionePagamento)->getResponse();
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza.", "riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));
        }
    }

    /**
     * @Route("/cancella_comunicazione_pagamento/{id}", name="cancella_comunicazione_pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="comunicazionePagamento", classe="AttuazioneControlloBundle:Istruttoria\ComunicazionePagamento", opzioni={"id": "id"}, azione=\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::WRITE)
     * 
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cancellaComunicazionePagamentoAction(ComunicazionePagamento $comunicazionePagamento) {
        $pagamento = $comunicazionePagamento->getPagamento();
        try {
            $richiesta = $pagamento->getRichiesta();
            /** @var GestorePagamentiBase $gestore_istruttoria */
            $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->cancellaComunicazionePagamento($comunicazionePagamento);
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza.",
                            "riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));
        }
    }

    /**
     * @Route("/pdf_comunicazione_pagamento/{id}", name="pdf_comunicazione_pagamento")
     * PaginaInfo(titolo="Monitoraggio e dichiarazioni", sottoTitolo="pagina di istruttoria per il monitoraggio e le dichiarazioni")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="comunicazionePagamento", classe="AttuazioneControlloBundle:Istruttoria\ComunicazionePagamento", opzioni={"id": "id"}, azione=\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::READ) 
     * 
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return mixed
     * @throws \Exception
     */
    public function pdfComunicazionePagamentoAction(ComunicazionePagamento $comunicazionePagamento) {
        /** @var GestorePagamentiBase $gestore_istruttoria */
        $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($comunicazionePagamento->getPagamento()->getRichiesta()->getProcedura());
        return $gestore_istruttoria->pdfComunicazionePagamento($comunicazionePagamento, true, true);
    }

    /**
     * @Route("/elimina_allegato_comunicazione_pagamento/{id}", name="elimina_allegato_comunicazione_pagamento")
     * 
     * @param AllegatoComunicazionePagamento $allegatoComunicazionePagamento
     * @return Response
     * @throws \Exception
     */
    public function eliminaAllegatoComunicazionePagamento(AllegatoComunicazionePagamento $allegatoComunicazionePagamento): Response {
        $comunicazionePagamento = $allegatoComunicazionePagamento->getComunicazionePagamento();
        $pagamento = $comunicazionePagamento->getPagamento();
        $richiesta = $pagamento->getRichiesta();
        /** @var GestorePagamentiBase $gestore_istruttoria */
        $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());

        try {
            $gestore_istruttoria->eliminaAllegatoComunicazionePagamento($allegatoComunicazionePagamento);
            return $this->addSuccessRedirect(
                            'Allegato eliminato con successo.',
                            'gestione_comunicazione_pagamento',
                            ['id' => $comunicazionePagamento->getId()]
            );
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect(
                            "Errore durante eliminazione dell'allegato.",
                            'gestione_comunicazione_pagamento',
                            ['id' => $comunicazionePagamento->getId()]
            );
        }
    }

    /**
     * @Route("/istruttoria_comunicazione_pagamento/{id}", name="istruttoria_comunicazione_pagamento")
     * @PaginaInfo(titolo="Istruttoria comunicazione pagamento", sottoTitolo="pagina per l'istruttoria della risposta del beneficiario alla comunicazione di pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * 
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return mixed
     * @throws \Exception
     */
    public function istruttoriaComunicazionePagamentoAction(ComunicazionePagamento $comunicazionePagamento) {
        /** @var GestorePagamentiBase $gestore_istruttoria */
        $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($comunicazionePagamento->getPagamento()->getRichiesta()->getProcedura());
        return $gestore_istruttoria->istruttoriaComunicazionePagamento($comunicazionePagamento);
    }

    /**
     * @Route("/istruttoria_documento_comunicazione_pagamento/{id}", name="istruttoria_documento_comunicazione_pagamento")
     * @PaginaInfo(titolo="Istruttoria documento di comunicazione pagamento", sottoTitolo="pagina di istruttoria dei documenti di comunicazione pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * 
     * @param DocumentoRispostaComunicazionePagamento $documentoRispostaComunicazionePagamento
     * @return mixed
     * @throws \Exception
     */
    public function istruttoriaDocumentoComunicazionePagamentoAction(DocumentoRispostaComunicazionePagamento $documentoRispostaComunicazionePagamento) {
        if ($this->isGranted("ROLE_OPERATORE_COGEA")) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }
        $pagamento = $documentoRispostaComunicazionePagamento->getRispostaComunicazionePagamento()->getComunicazione()->getPagamento();
        /** @var GestorePagamentiBase $gestore_istruttoria */
        $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getRichiesta()->getProcedura());
        return $gestore_istruttoria->istruttoriaDocumentoComunicazionePagamento($documentoRispostaComunicazionePagamento);
    }

    /**
     * @Route("/genera_pdf_esito_pag/{id}", name="genera_pdf_esito_pag")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     * 
     */
    public function pdfEsitoPagamentoAction(Pagamento $pagamento) {
        /** @var GestoreEsitoPagamentoBase $gestore_istruttoria */
        $gestore_istruttoria = $this->get("gestore_esito_pagamento")->getGestore($pagamento->getRichiesta()->getProcedura());
        return $gestore_istruttoria->pdfEsitoIstruttoriaPagamento($pagamento, true, true);
    }

    /**
     * @Route("/esporta_pagamenti_mandato", name="esporta_pagamenti_mandato")
     */
    public function esportaPagamentiMandatoAction() {
        return $this->get("gestore_istruttoria_pagamenti")->getGestoreBase()->esportaPagamentiMandato();
    }

    /**
     * @Route("/{id_pagamento}/riapri_pagamento", name="riapri_pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function riapriPagamentoAction($id_pagamento)
    {
        try {
            $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
            $richiesta = $pagamento->getRichiesta();
            $gestore_istruttoria = $this->get("gestore_istruttoria_pagamenti")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->riapriPagamento($pagamento);
        } catch (SfingeException $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect($e->getMessage(), "riepilogo_istruttoria_pagamento", ["id_pagamento" => $pagamento->getId()]);
        } catch (Exception $e) {dump($e->getMessage());
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l’assistenza", "riepilogo_istruttoria_pagamento", ["id_pagamento" => $pagamento->getId()]);
        }
    }
    
    /**
     * @Route("/esporta_pagamenti_globali", name="esporta_pagamenti_globali")
     */
    public function esportaPagamentiGlobali2022Action() {
        
        if (!$this->isSuperAdmin()) {
            return $this->addErrorRedirect("Non hai i privilegi per effettuare questa operazione", "home");
        }
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find(1);
        return $this->get("gestore_istruttoria_pagamenti")->getGestore($procedura)->esportaPagamentiGlobali();
    }
}
