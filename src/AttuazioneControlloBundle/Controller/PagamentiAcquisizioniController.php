<?php

namespace AttuazioneControlloBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use BaseBundle\Annotation\ControlloAccesso;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AttuazioneControlloBundle\Entity\StatoPagamento;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use BaseBundle\Exception\SfingeException;

/**
 * @Route("/acquisizioni/pagamenti")
 */
class PagamentiAcquisizioniController extends \BaseBundle\Controller\BaseController {

	/**
	 * @Route("/{id_richiesta}/elenco_pagamenti", name="elenco_pagamenti_acquisizioni")
	 * @PaginaInfo(titolo="Elenco pagamenti progetto",sottoTitolo="mostra l'elenco dei pagamenti richiesti per un progetto")
	 * @Menuitem(menuAttivo = "elencoRichiesteTr")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_richieste_acquisizioni"),
	 *						 @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta" = "id_richiesta"}),
	 *                       @ElementoBreadcrumb(testo="Elenco pagamenti progetto")})
	 */
	public function elencoPagamentiAction($id_richiesta) {
		$this->getSession()->set("id_richiesta", $id_richiesta);
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		return $this->get("gestore_pagamenti")->getGestore($richiesta->getProcedura())->elencoPagamenti($id_richiesta);
	}

	/**
	 * @Route("/{id_richiesta}/riepilogo", name="riepilogo_richiesta_attuazione_acquisizioni")
	 * @PaginaInfo(titolo="Riepilogo del progetto",sottoTitolo="dati riepilogativi del progetto")
	 * @Menuitem(menuAttivo = "elencoRichiesteTr")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione progetti", route="elenco_gestione_pa")})
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function riepilogoRichiestaAction($id_richiesta) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->riepilogoRichiestaPA($richiesta);
	}

	/**
	 * @Route("/{id_richiesta}/aggiungi", name="aggiungi_pagamento_acquisizioni")
	 * @PaginaInfo(titolo="Creazione richiesta pagamento",sottoTitolo="pagina di creazione di un pagamento")
	 * @Menuitem(menuAttivo = "elencoRichiesteTr")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_richieste_at"),
	 *						 @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta" = "id_richiesta"}),
	 *						 @ElementoBreadcrumb(testo="Elenco pagamenti richiesti", route="elenco_pagamenti_acquisizioni", parametri={"id_richiesta" = "id_richiesta"}),
	 *                       @ElementoBreadcrumb(testo="creazione pagamento")})
	 */
	public function aggiungiPagamentoAction($id_richiesta) {
            $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
            return $this->get("gestore_pagamenti")->getGestore($richiesta->getProcedura())->aggiungiPagamento($id_richiesta);
	}

	/**
	 * @Route("/{id_pagamento}/elimina", name="elimina_pagamento_acquisizioni")
	 */
	public function eliminaPagamentoAction($id_pagamento) {
		$this->get('base')->checkCsrf('token');
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$procedura = $pagamento->getProcedura();
		return $this->get("gestore_pagamenti")->getGestore($procedura)->eliminaPagamento($id_pagamento);
	}

	/**
	 * @Route("/{id_pagamento}/dettaglio", name="dettaglio_pagamento_acquisizioni")
	 * @PaginaInfo(titolo="Dettaglio richiesta pagamento",sottoTitolo="pagina di riepilogo della richiesta di pagamento")
	 * @Menuitem(menuAttivo = "elencoRichiesteTr")
	 */
	public function dettaglioPagamentoAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$procedura = $pagamento->getProcedura();
		return $this->get("gestore_pagamenti")->getGestore($procedura)->dettaglioPagamento($id_pagamento);
	}

	/**
	 * @Route("/{id_pagamento}/dati_generali", name="dati_generali_pagamento_acquisizioni")
	 * @PaginaInfo(titolo="Dati generali pagamento",sottoTitolo="dati generali del pagamento")
	 * @Menuitem(menuAttivo = "elencoRichiesteTr")
	 */
	public function datiGeneraliAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$procedura = $pagamento->getProcedura();
		return $this->get("gestore_pagamenti")->getGestore($procedura)->datiGeneraliPagamento($id_pagamento);
	}
    
	/**
	 * @Route("/{id_pagamento}/mandato", name="mandato_pagamento_acquisizioni")
	 * @PaginaInfo(titolo="Mandato pagamento")
	 * @Menuitem(menuAttivo = "elencoRichiesteTr")
	 */
	public function mandatoAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_pagamenti")->getGestore($pagamento->getProcedura())->mandato($pagamento);
	}    
    
	/**
	 * @Route("/valuta/{id_pagamento}", name="valuta_checklist_istruttoria_pagamenti_acquisizioni")
	 * @Menuitem(menuAttivo = "elencoRichiesteTr")
	 */
	public function valutaChecklistAction($id_pagamento) {
			$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
            return $this->get("gestore_pagamenti")->getGestore($pagamento->getProcedura())->valutaChecklist($pagamento);	
	}  
    
	/**
	 *
	 * @Route("/{id_pagamento}/completa_pagamento", name="completa_pagamento_acquisizioni")
	 * @Method({"GET"})
	 * @Menuitem(menuAttivo = "elencoRichiesteTr")
	 */
	public function completaPagamentoAction($id_pagamento) {
        $this->get('base')->checkCsrf('token');
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_pagamenti")->getGestore($pagamento->getRichiesta()->getProcedura())->completaPagamento($id_pagamento);
	}    
	
	/**
	 * 
	 * @Route("/{id_richiesta}/elenco_documenti_caricati_pag_acquisizioni/{id_pagamento}", name="elenco_documenti_caricati_pag_acquisizioni")
	 * @Method({"GET","POST"})
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_richieste_acquisizioni"),
	 *						 @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta_acquisizioni", parametri={"id_richiesta" = "id_richiesta"}),
	 *                       @ElementoBreadcrumb(testo="Elenco documenti pagamento")})
	 * @PaginaInfo(titolo="Elenco Documenti",sottoTitolo="carica i documenti richiesti")
	 * @Menuitem(menuAttivo = "elencoRichiesteTr")
	 */
	public function elencoDocumentiCaricatiPagAction($id_pagamento, $id_richiesta) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_pagamenti")->getGestore($pagamento->getProcedura())->elencoDocumentiCaricati($id_pagamento);
		return $response->getResponse();
	}

	/**
	 * @Route("/{id_documento_pagamento}/elimina_documento_pagamento_acquisizioni", name="elimina_documento_pagamento_acquisizioni") 
	 */
	public function eliminaDocumentoAction($id_documento_pagamento) {
		return $this->get("gestore_pagamenti")->getGestore()->eliminaDocumentoPagamento($id_documento_pagamento);
	}

}
