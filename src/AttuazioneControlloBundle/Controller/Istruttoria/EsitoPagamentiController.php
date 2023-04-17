<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AttuazioneControlloBundle\Controller\Istruttoria;

use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use DocumentoBundle\Entity\DocumentoFile;
use IstruttorieBundle\Entity\DocumentoIstruttoria;

/**
 * @Route("/istruttoria/esito_pagamenti")
 */
class EsitoPagamentiController  extends BaseController {

	/**
	 * @Route("/{id_pagamento}/esito_finale", name="esito_finale_istruttoria_pagamenti")
	 * @PaginaInfo(titolo="Esito finale istruttoria pagamento")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function esitoFinaleAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		if ($pagamento->isProceduraParticolare() == true) {
			return $this->get("gestore_pagamenti")->getGestore($pagamento->getProcedura())->esitoFinale($pagamento);
		} else {
			return $this->get("gestore_esito_pagamento")->getGestore($pagamento->getProcedura())->esitoFinale($pagamento);
		}
	}

	/**
	 * @Route("/{id_documento_esito_istruttoria}/esito_finale_elimina_doc", name="esito_finale_elimina_doc")
	 * @PaginaInfo(titolo="Esito finale istruttoria pagamento")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function esitoFinaleEliminaDocAction($id_documento_esito_istruttoria) {
		$documento_esito_istruttoria = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\DocumentoEsitoIstruttoria")->find($id_documento_esito_istruttoria);
		$pagamento = $documento_esito_istruttoria->getEsitoIstruttoriaPagamento()->getPagamento();
		return $this->get("gestore_esito_pagamento")->getGestore($pagamento->getProcedura())->eliminaDocumento($id_documento_esito_istruttoria, $pagamento);
	}
	
	/**
	 * @Route("/{id_pagamento}/mandato", name="mandato_pagamento")
	 * @PaginaInfo(titolo="Mandato pagamento")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function mandatoAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		if ($pagamento->isProceduraParticolare() == true) {
			return $this->get("gestore_pagamenti")->getGestore($pagamento->getProcedura())->mandato($pagamento);
		} else {
			return $this->get("gestore_esito_pagamento")->getGestore($pagamento->getProcedura())->mandato($pagamento);
		}
	}

	/**
	 * @Route("/pdf_esito_istruttoria_pagamento/{id_pagamento}", name="pdf_esito_istruttoria_pagamento")
	 * PaginaInfo(titolo="Monitoraggio e dichiarazioni", sottoTitolo="pagina di istruttoria per il monitoraggio e le dichiarazioni")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"})  
	 */
	public function pdfEsitoIstruttoriaPagamentoAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_esito_pagamento")->getGestore($pagamento->getProcedura())->pdfEsitoIstruttoriaPagamento($pagamento);
	}
        
        
        /**
	 * @Route("/pdf_esito_istruttoria_pagamento_html/{id_pagamento}", name="pdf_esito_istruttoria_pagamento_html")
	 * PaginaInfo(titolo="Monitoraggio e dichiarazioni", sottoTitolo="pagina di istruttoria per il monitoraggio e le dichiarazioni")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"})  
	 */
	public function pdfEsitoIstruttoriaPagamentoHtmlAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_esito_pagamento")->getGestore($pagamento->getProcedura())->pdfEsitoIstruttoriaPagamentoHtml($pagamento);
	}
	
}
