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
 * @Route("/beneficiario/pagamenti/relazione")
 */
class RelazioneTecnicaController extends \BaseBundle\Controller\BaseController {

	/**
	 * @Route("/{id_pagamento}/elenco_referenti_relazione", name="elenco_referenti_relazione")
	 * @PaginaInfo(titolo="Autore relazione")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
	 */
	public function elencoPersonaReferenteAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->elencoAutoriRelazione($id_pagamento);
	}

	/**
	 * @Route("/{id_pagamento}/aggiungi_referente_relazione/{id_persona}", name="aggiungi_referente_relazione")
	 * @PaginaInfo(titolo="Dettaglio referente")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
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
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
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
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
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
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
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
	 * @Route("/{id_pagamento}/elenco_obiettivi_realizzativi", name="elenco_obiettivi_realizzativi")
	 * @PaginaInfo(titolo="Obiettivi realizzativi")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function elencoObiettiviRealizzativiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->elencoObiettiviRealizzativi($id_pagamento);
	}

	/**
	 * @Route("/{id_pagamento}/compila_obiettivo_realizzativo/{id_obiettivo}", name="compila_obiettivo_realizzativo")
	 * @PaginaInfo(titolo="Obiettivi realizzativi")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function compilaObiettivoRealizzativoAction($id_obiettivo, $id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->compilaDettaglioOr($id_obiettivo, $id_pagamento);
		return $response->getResponse();
	}

	/**
	 * @Route("/{id_pagamento}/elenco_personale_voce1", name="elenco_personale_voce1")
	 * @PaginaInfo(titolo="Obiettivi realizzativi")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function elencoPersonaleVoce1Action($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->elencoPersonale($id_pagamento, 'RICERCATORI_VOCE_1');
	}

	/**
	 * @Route("/{id_pagamento}/elenco_personale_voce2", name="elenco_personale_voce2")
	 * @PaginaInfo(titolo="Obiettivi realizzativi")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function elencoPersonaleVoce2Action($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->elencoPersonale($id_pagamento, 'PERSONALE_VOCE_2');
	}

	/**
	 * @Route("/{id_pagamento}/elenco_personale_voce3", name="elenco_personale_voce3")
	 * @PaginaInfo(titolo="Obiettivi realizzativi")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function elencoPersonaleVoce3Action($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->elencoPersonale($id_pagamento, 'PERSONALE_VOCE_3');
	}

	/**
	 * @Route("/{id_pagamento}/elenco_collaborazioni_laboratori", name="elenco_collaborazioni_laboratori")
	 * @PaginaInfo(titolo="Obiettivi realizzativi")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function elencoCollaborazioniLabAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->elencoCollaborazioniEsterne($id_pagamento, 'LABORATORI');
	}

	/**
	 * @Route("/{id_pagamento}/elenco_collaborazioni_consulenze", name="elenco_collaborazioni_consulenze")
	 * @PaginaInfo(titolo="Obiettivi realizzativi")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function elencoCollaborazioniConsAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->elencoCollaborazioniEsterne($id_pagamento, 'CONSULENZE');
	}

	/**
	 * @Route("/{id_pagamento}/compila_collaborazioni/{id_collaborazione}/{tipo}", name="compila_collaborazioni")
	 * @PaginaInfo(titolo="Collaborazione esterna")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function compilaCollaborazioneAction($id_pagamento, $id_collaborazione, $tipo) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->compilaCollaborazioniEsterne($id_collaborazione, $id_pagamento, $tipo);
		return $response->getResponse();
	}

	/**
	 * @Route("/{id_pagamento}/elenco_attrezzature", name="elenco_attrezzature")
	 * @PaginaInfo(titolo="Attrezzature e strumentazioni")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function elencoAttrezzatureStrumentazioniAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->elencoAttrezzatureStrumentazioni($id_pagamento, 'ALL');
	}

	/**
	 * @Route("/{id_pagamento}/compila_attrezzatura/{id_attrezzatura}/{tipo}", name="compila_attrezzatura")
	 * @PaginaInfo(titolo="Attrezzatura e strumentazioni")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function compilaAttrezzatureStrumentazioniAction($id_pagamento, $id_attrezzatura, $tipo) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->compilaAttrezzatureStrumentazioni($id_attrezzatura, $id_pagamento, $tipo);
		return $response->getResponse();
	}

	/**
	 * @Route("/{id_pagamento}/elenco_prototipi", name="elenco_prototipi")
	 * @PaginaInfo(titolo="Prototipi, dimostratori e impianti pilota")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function elencoPrototipiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->elencoPrototipiDimostratoriPilota($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/compila_prototipo/{id_prototipo}", name="compila_prototipo")
	 * @PaginaInfo(titolo="Prototipi, dimostratori e impianti pilota")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function compilaPrototipoAction($id_pagamento, $id_prototipo) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->compilaPrototipo($id_prototipo, $id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/elenco_brevetti", name="elenco_brevetti")
	 * @PaginaInfo(titolo="Brevetti")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function elencoBrevettiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->elencoBrevetti($id_pagamento);
	}
	
	/**
	 * @Route("/{id_pagamento}/compila_brevetto/{id_brevetto}", name="compila_brevetto")
	 * @PaginaInfo(titolo="Prototipi, dimostratori e impianti pilota")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function compilaBrevettoAction($id_pagamento, $id_brevetto) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->compilaBrevetto($id_brevetto, $id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/altre_informazioni", name="altre_informazioni")
	 * @PaginaInfo(titolo="Prototipi, dimostratori e impianti pilota")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function compilaAltreInformazioniAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->altreInformazioni($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/attivita_realizzate", name="attivita_realizzate")
	 * @PaginaInfo(titolo="Attività realizzate")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function attivitaRealizzateAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->attivitaRealizzate($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/attivita_realizzate_or5", name="attivita_realizzate_or5")
	 * @PaginaInfo(titolo="Attività realizzate")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function attivitaRealizzateOr5Action($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->attivitaRealizzateOr5($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/elenco_attivita_obiettivi_realizzativi", name="elenco_attivita_obiettivi_realizzativi")
	 * @PaginaInfo(titolo="Attività obiettivi realizzativi")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function elencoAttivitaObiettiviRealizzativiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->elencoAttivitaOr($id_pagamento);
		return $response;
	}
	
	/**
	 * @Route("/{id_pagamento}/aggiungi_attivita_obiettivi_realizzativi", name="aggiungi_attivita_obiettivi_realizzativi")
	 * @PaginaInfo(titolo="Attività realizzate")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function aggiungiAttivitaObiettiviRealizzativiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->aggiungiAttivitaOr($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/elenco_materiale_obiettivi_realizzativi", name="elenco_materiale_obiettivi_realizzativi")
	 * @PaginaInfo(titolo="Attività obiettivi realizzativi")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function elencoMaterialeObiettiviRealizzativiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->elencoMaterialiOr($id_pagamento);
		return $response;
	}
	
	/**
	 * @Route("/{id_pagamento}/aggiungi_materiale_obiettivi_realizzativi", name="aggiungi_materiale_obiettivi_realizzativi")
	 * @PaginaInfo(titolo="Attività materiale")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function aggiungiMaterialeObiettiviRealizzativiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->aggiungiMaterialeOr($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/modifica_attivita_obiettivi_realizzativi/{id_attivita}", name="modifica_attivita_obiettivi_realizzativi")
	 * @PaginaInfo(titolo="Attività realizzate")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function modificaAttivitaObiettiviRealizzativiAction($id_pagamento, $id_attivita) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->modificaAttivitaOr($id_pagamento, $id_attivita);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/modifica_materiale_obiettivi_realizzativi/{id_materiale}", name="modifica_materiale_obiettivi_realizzativi")
	 * @PaginaInfo(titolo="Attività materiale")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function modificaMaterialeObiettiviRealizzativiAction($id_pagamento, $id_materiale) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->modificaMaterialeOr($id_pagamento, $id_materiale);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/elimina_materiale_obiettivi_realizzativi/{id_materiale}", name="elimina_materiale_obiettivi_realizzativi")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function rimuoviMaterialeObiettiviRealizzativiAction($id_pagamento, $id_materiale) {
		$this->get('base')->checkCsrf('token');
		try {
			$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
			return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->rimuoviMaterialeOr($id_pagamento, $id_materiale);
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "elenco_referenti_relazione", array("id_pagamento" => $id_pagamento));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "elenco_referenti_relazione", array("id_pagamento" => $id_pagamento));
		}
	} 
	
	/**
	 * @Route("/{id_pagamento}/elimina_attivita_obiettivi_realizzativi/{id_attivita}", name="elimina_attivita_obiettivi_realizzativi")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function rimuoviAttivitaObiettiviRealizzativiAction($id_pagamento, $id_attivita) {
		$this->get('base')->checkCsrf('token');
		try {
			$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
			return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->rimuoviAttivitaOr($id_pagamento, $id_attivita);
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "elenco_referenti_relazione", array("id_pagamento" => $id_pagamento));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "elenco_referenti_relazione", array("id_pagamento" => $id_pagamento));
		}
	} 
	
	/**
	 *
	 * @Route("/{id_pagamento}/genera_pdf_relazione_tecnica", name="genera_pdf_relazione_tecnica")
	 * @Method({"GET"})
	 * @Menuitem(menuAttivo = "elencoRichieste")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function generaPdf($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getRichiesta()->getProcedura())->generaPdf($id_pagamento);
	}

	/**
	 * @Route("/{id_pagamento}/ore_persona_or/{or}", name="ore_persona_or")
	 * @PaginaInfo(titolo="Ore/persona per Obiettivo Realizzativo")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function orePersonaORAction($id_pagamento, $or) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->orePersonaOR($id_pagamento, $or);
	}   
    
	/**
	 * @Route("/{id_pagamento}/tipologie_contratto_personale", name="tipologie_contratto_personale")
	 * @PaginaInfo(titolo="Unità di personale che ha lavorato sul progetto")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function tipologieContrattoPersonaleAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->tipologieContrattoPersonale($id_pagamento);
	}  
	
	
	/**
	 * @Route("/{id_pagamento}/relazione_tecnica_sintetica", name="relazione_tecnica_sintetica")
	 * @PaginaInfo(titolo="Relazione Tecnica Sintetica")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function compilaRelazioneTecnicaSinteticaAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->relazioneTecnicaSintetica($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/conclusione_sviluppi_futuri", name="conclusione_sviluppi_futuri")
	 * @PaginaInfo(titolo="Relazione Tecnica Sintetica")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function compilaConclusioneSviluppiFuturiAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->conclusioneSviluppiFuturi($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/elenco_collaborazioni_esterne_imprese", name="elenco_collaborazioni_esterne_imprese")
	 * @PaginaInfo(titolo="Collaborazioni esterne con imprese")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function elencoCollaborazioniEsterneImpreseAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->elencoCollaborazioniEsterneImprese($id_pagamento);
		return $response;
	}
	
	/**
	 * @Route("/{id_pagamento}/aggiungi_collaborazioni_esterne_imprese", name="aggiungi_collaborazioni_esterne_imprese")
	 * @PaginaInfo(titolo="Collaborazioni imprese esterne")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function aggiungiCollaborazioniEsterneImpreseAction($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->aggiungiCollaborazioniEsterneImprese($id_pagamento);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/modifica_collaborazioni_esterne_imprese/{id_collaborazione}", name="modifica_collaborazioni_esterne_imprese")
	 * @PaginaInfo(titolo="Collaborazioni imprese esterne")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function modificaCollaborazioniEsterneImpreseAction($id_pagamento, $id_collaborazione) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$response = $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->modificaCollaborazioniEsterneImprese($id_pagamento, $id_collaborazione);
		return $response->getResponse();
	}
	
	/**
	 * @Route("/{id_pagamento}/elimina_collaborazioni_esterne_imprese/{id_collaborazione}", name="elimina_collaborazioni_esterne_imprese")
	 * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
	 */
	public function rimuoviCollaborazioniEsterneImpreseAction($id_pagamento, $id_collaborazione) {
		$this->get('base')->checkCsrf('token');
		try {
			$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
			return $this->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura())->rimuoviCollaborazioniEsterneImprese($id_pagamento, $id_collaborazione);
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "elenco_referenti_relazione", array("id_pagamento" => $id_pagamento));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "elenco_referenti_relazione", array("id_pagamento" => $id_pagamento));
		}
	}
    
}
