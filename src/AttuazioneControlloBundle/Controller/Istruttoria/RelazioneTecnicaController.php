<?php

namespace AttuazioneControlloBundle\Controller\Istruttoria;

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
 * @Route("/istruttoria/pagamenti/relazione")
 */
class RelazioneTecnicaController extends \BaseBundle\Controller\BaseController {

	/**
	 * @Route("/{id_pagamento}/elenco_referenti_relazione_istruttoria", name="elenco_referenti_relazione_istruttoria")
	 * @PaginaInfo(titolo="Autore relazione")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function elencoPersonaReferenteAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->elencoAutoriRelazione($id_pagamento);
	}
	
	/**
	 * @Route("/{id_pagamento}/elenco_obiettivi_realizzativi_istruttoria", name="elenco_obiettivi_realizzativi_istruttoria")
	 * @PaginaInfo(titolo="Obiettivi realizzativi")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function elencoObiettiviRealizzativiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->elencoObiettiviRealizzativi($id_pagamento);
	}

	/**
	 * @Route("/{id_pagamento}/compila_obiettivo_realizzativo_istruttoria/{id_obiettivo}", name="compila_obiettivo_realizzativo_istruttoria")
	 * @PaginaInfo(titolo="Obiettivi realizzativi")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function compilaObiettivoRealizzativoAction($id_obiettivo, $id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->compilaDettaglioOr($id_obiettivo, $id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/elenco_attrezzature_istruttoria", name="elenco_attrezzature_istruttoria")
	 * @PaginaInfo(titolo="Attrezzature e strumentazioni")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function elencoAttrezzatureStrumentazioniAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->elencoAttrezzatureStrumentazioni($id_pagamento, 'ALL');
	}
	
	/**
	 * @Route("/{id_pagamento}/elenco_prototipi_istruttoria", name="elenco_prototipi_istruttoria")
	 * @PaginaInfo(titolo="Prototipi, dimostratori e impianti pilota")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function elencoPrototipiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->elencoPrototipiDimostratoriPilota($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/visualizza_prototipo_istruttoria/{id_prototipo}", name="visualizza_prototipo_istruttoria")
	 * @PaginaInfo(titolo="Prototipi, dimostratori e impianti pilota")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function visualizzaPrototipoAction($id_pagamento, $id_prototipo) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->visualizzaPrototipo($id_prototipo, $id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/elenco_brevetti_istruttoria", name="elenco_brevetti_istruttoria")
	 * @PaginaInfo(titolo="Brevetti")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function elencoBrevettiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->elencoBrevetti($id_pagamento);
	}
	
	/**
	 * @Route("/{id_pagamento}/altre_informazioni_istruttoria", name="altre_informazioni_istruttoria")
	 * @PaginaInfo(titolo="Altre informazioni")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function compilaAltreInformazioniAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->altreInformazioni($id_pagamento);
		return $response->getResponse();
	}	
	
	/**
	 * @Route("/{id_pagamento}/compila_attrezzatura_istruttoria/{id_attrezzatura}/{tipo}", name="compila_attrezzatura_istruttoria")
	 * @PaginaInfo(titolo="Attrezzatura e strumentazioni")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function compilaAttrezzatureStrumentazioniAction($id_pagamento, $id_attrezzatura, $tipo) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->compilaAttrezzatureStrumentazioni($id_attrezzatura, $id_pagamento, $tipo);
		return $response->getResponse();
	}	
	
	/**
	 * @Route("/{id_pagamento}/elenco_personale_voce1_istruttoria", name="elenco_personale_voce1_istruttoria")
	 * @PaginaInfo(titolo="Elenco ricercatori voce 1")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function elencoPersonaleVoce1Action($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->elencoPersonale($id_pagamento, 'RICERCATORI_VOCE_1');
	}

	/**
	 * @Route("/{id_pagamento}/elenco_personale_voce2_istruttoria", name="elenco_personale_voce2_istruttoria")
	 * @PaginaInfo(titolo="Elenco personale voce 2")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function elencoPersonaleVoce2Action($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->elencoPersonale($id_pagamento, 'PERSONALE_VOCE_2');
	}

	/**
	 * @Route("/{id_pagamento}/elenco_personale_voce3_istruttoria", name="elenco_personale_voce3_istruttoria")
	 * @PaginaInfo(titolo="Elenco personale voce 3")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function elencoPersonaleVoce3Action($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->elencoPersonale($id_pagamento, 'PERSONALE_VOCE_3');
	}	
	
	/**
	 * @Route("/{id_pagamento}/elenco_collaborazioni_laboratori_istruttoria", name="elenco_collaborazioni_laboratori_istruttoria")
	 * @PaginaInfo(titolo="Elenco collaborazioni laboratori")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function elencoCollaborazioniLabAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->elencoCollaborazioniEsterne($id_pagamento, 'LABORATORI');
	}

	/**
	 * @Route("/{id_pagamento}/elenco_collaborazioni_consulenze_istruttoria", name="elenco_collaborazioni_consulenze_istruttoria")
	 * @PaginaInfo(titolo="Elenco consulenze specialistiche")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function elencoCollaborazioniConsAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->elencoCollaborazioniEsterne($id_pagamento, 'CONSULENZE');
	}

	/**
	 * @Route("/{id_pagamento}/compila_collaborazioni_istruttoria/{id_collaborazione}/{tipo}", name="compila_collaborazioni_istruttoria")
	 * @PaginaInfo(titolo="Collaborazione esterna")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function compilaCollaborazioneAction($id_pagamento, $id_collaborazione, $tipo) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->compilaCollaborazioniEsterne($id_collaborazione, $id_pagamento, $tipo);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/esito_istruttoria_rel_tecnica", name="esito_istruttoria_rel_tecnica")
	 * @PaginaInfo(titolo="Esito istruttoria")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function esitoIstruttoriaRelazioneTecnicaAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->esitoIstruttoriaRelazioneTecnica($id_pagamento);
	}	


	/**
	 * @Route("/{id_pagamento}/aggiungi_referente_relazione/{id_persona}", name="aggiungi_referente_relazione")
	 * @PaginaInfo(titolo="Dettaglio referente")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function aggiungiPersonaReferenteAction($id_pagamento, $id_persona) {

		$parametriUrl = array("id_pagamento" => $id_pagamento, "id_persona" => $id_persona);
		$urlIndietro = $this->generateUrl("dettaglio_pagamento", $parametriUrl);

		return $this->get("inserimento_persona")->inserisciPersona($urlIndietro, "inserisci_referente", $parametriUrl);
	}

	/**
	 * @Route("/{id_pagamento}/inserisci_referente_relazione/{id_persona}", name="inserisci_referente_relazione")
	 * @PaginaInfo(titolo="Dettaglio referente")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function inserisciReferenteAction($id_pagamento, $id_persona) {
		try {
			$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
			$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->inserisciReferenteRelazione($id_pagamento, $id_persona);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "elenco_referenti_relazione", array("id_pagamento" => $id_pagamento));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "elenco_referenti_relazione", array("id_pagamento" => $id_pagamento));
		}
	}

	/**
	 * @Route("/{id_pagamento}/cerca_autore_relazione/{page}",defaults={"page"=1}, name="cerca_autore_relazione")
	 * @PaginaInfo(titolo="Aggiunta autore relazione",sottoTitolo="pagina per cercare un autore")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function cercaReferenteAction($id_pagamento) {
		try {
			$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
			$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->cercaAutoreRelazione($id_pagamento);
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_pagamento", array("id_pagamento" => $id_pagamento));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_pagamento", array("id_pagamento" => $id_pagamento));
		}

		return $response->getResponse();
	}
    
	/**
	 * @Route("/{id_pagamento}/rimuovi_autore_relazione/{id_referente}", name="rimuovi_autore_relazione")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function rimuoviReferenteAction($id_pagamento, $id_referente) {
		$this->get('base')->checkCsrf('token');
		try {
			$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
			return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->rimuoviAutoreRelazione($id_pagamento, $id_referente);
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "elenco_referenti_relazione", array("id_pagamento" => $id_pagamento));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "elenco_referenti_relazione", array("id_pagamento" => $id_pagamento));
		}
	}    
	
	/**
	 * @Route("/{id_pagamento}/compila_brevetto_istruttoria/{id_brevetto}", name="compila_brevetto_istruttoria")
	 * @PaginaInfo(titolo="Prototipi, dimostratori e impianti pilota")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function compilaBrevettoAction($id_pagamento, $id_brevetto) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->compilaBrevetto($id_brevetto, $id_pagamento);
		return $response->getResponse();
	}	
	
	/**
	 *
	 * @Route("/{id_pagamento}/genera_pdf_relazione_tecnica", name="genera_pdf_relazione_tecnica")
	 * @Method({"GET"})
	 * @Menuitem(menuAttivo = "elencoRichieste")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"})  
	 */
	public function generaPdf($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getRichiesta()->getProcedura())->generaPdf($id_pagamento);
	}
	
	/**
	 * @Route("/{id_pagamento}/attivita_realizzate_istruttoria", name="attivita_realizzate_istruttoria")
	 * @PaginaInfo(titolo="Attività realizzate")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function attivitaRealizzateAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->attivitaRealizzate($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/attivita_realizzate_or5_istruttoria", name="attivita_realizzate_or5_istruttoria")
	 * @PaginaInfo(titolo="Attività realizzate")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function attivitaRealizzateOr5Action($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->attivitaRealizzateOr5($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/elenco_attivita_obiettivi_realizzativi_istruttoria", name="elenco_attivita_obiettivi_realizzativi_istruttoria")
	 * @PaginaInfo(titolo="Attività obiettivi realizzativi")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function elencoAttivitaObiettiviRealizzativiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->elencoAttivitaOr($id_pagamento);
		return $response;
	}
	
	/**
	 * @Route("/{id_pagamento}/elenco_materiale_obiettivi_realizzativi_istruttoria", name="elenco_materiale_obiettivi_realizzativi_istruttoria")
	 * @PaginaInfo(titolo="Attività obiettivi realizzativi")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function elencoMaterialeObiettiviRealizzativiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->elencoMaterialiOr($id_pagamento);
		return $response;
	}

	/**
	 * @Route("/{id_pagamento}/tipologie_contratto_personale_istruttoria", name="tipologie_contratto_personale_istruttoria")
	 * @PaginaInfo(titolo="Unità di personale che ha lavorato sul progetto")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function tipologieContrattoPersonaleAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->tipologieContrattoPersonale($id_pagamento);
	} 
	
	/**
	 * @Route("/{id_pagamento}/ore_persona_or_istruttoria/{or}", name="ore_persona_or_istruttoria")
	 * @PaginaInfo(titolo="Ore/persona per Obiettivo Realizzativo")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function orePersonaORAction($id_pagamento, $or) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->orePersonaOR($id_pagamento, $or);
	}
	
	/**
	 * @Route("/{id_pagamento}/relazione_tecnica_sintetica_istruttoria", name="relazione_tecnica_sintetica_istruttoria")
	 * @PaginaInfo(titolo="Relazione Tecnica Sintetica")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function compilaRelazioneTecnicaSinteticaAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->relazioneTecnicaSintetica($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/conclusione_sviluppi_futuri_istruttoria", name="conclusione_sviluppi_futuri_istruttoria")
	 * @PaginaInfo(titolo="Conclusione e Sviluppi Futuri")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function compilaConclusioneSviluppiFuturiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->conclusioneSviluppiFuturi($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/elenco_collaborazioni_esterne_imprese_istruttoria", name="elenco_collaborazioni_esterne_imprese_istruttoria")
	 * @PaginaInfo(titolo="Collaborazioni esterne con imprese")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function elencoCollaborazioniEsterneImpreseAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->elencoCollaborazioniEsterneImprese($id_pagamento);
		return $response;
	}
	
	/**
	 * @Route("/{id_pagamento}/visualizza_collaborazione_esterna_imprese_istruttoria/{id_collaborazione}", name="visualizza_collaborazione_esterna_imprese_istruttoria")
	 * @PaginaInfo(titolo="Collaborazioni imprese esterne")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
	 */
	public function visualizzaCollaborazioneEsternaImpreseAction($id_pagamento, $id_collaborazione) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica_istruttoria")->getGestore($pagamento->getProcedura())->collaborazioneEsternaImprese($id_pagamento, $id_collaborazione);
		return $response->getResponse();
	}
}
