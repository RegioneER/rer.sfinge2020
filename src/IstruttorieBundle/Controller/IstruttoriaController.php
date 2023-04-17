<?php

namespace IstruttorieBundle\Controller;

use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use Doctrine\ORM\EntityManager;
use Exception;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use IstruttorieBundle\Entity\ValutazioneChecklistIstruttoria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RichiesteBundle\Service\GestoreResponse;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use IstruttorieBundle\Form\Entity\RicercaIstruttoria;
use DocumentoBundle\Entity\DocumentoFile;
use IstruttorieBundle\Entity\DocumentoIstruttoria;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use DocumentoBundle\Component\ResponseException;
use IstruttorieBundle\Entity\IntegrazioneIstruttoria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class IstruttoriaController extends BaseController {

	/**
	 * @Route("/elenco_inviate/{sort}/{direction}/{page}", defaults={"sort" = "i.id", "direction" = "asc", "page" = "1"}, name="elenco_richieste_inviate")
	 * @PaginaInfo(titolo="Elenco operazioni in istruttoria",sottoTitolo="mostra l'elenco delle operazioni inviate e non ancora istruite")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco operazioni inviate", route="elenco_richieste_inviate")})
	 */
	public function elencoRichiesteInviateAction() {
		$datiRicerca = new RicercaIstruttoria();
		$datiRicerca->setUtente($this->getUser());

		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		return $this->render('IstruttorieBundle:Istruttoria:elencoRichiesteInviate.html.twig', array('richieste' => $risultato["risultato"], "formRicercaIstruttoria" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]));
	}

		/**
	 * @Route("/elenco_istruttorie_pulisci", name="elenco_istruttorie_pulisci")
	 */
	public function elencoIstruttoriePulisciAction() {
		$this->get("ricerca")->pulisci(new RicercaIstruttoria());
		return $this->redirectToRoute("elenco_richieste_inviate");
	}

	/**
	 * @Route("/{id_richiesta}/gestisci", name="gestisci_istruttoria")
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function gestisciAction($id_richiesta) {
		try {
			$gestore_istruttoria = $this->get("gestore_istruttoria")->getGestore();
			$gestore_istruttoria->aggiornaIstruttoriaRichiesta($id_richiesta);
			return $this->redirectToRoute("riepilogo_richiesta", array("id_richiesta" => $id_richiesta));
		} catch (SfingeException $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_inviate");
		} catch (Exception $e) {
			$this->get("logger")->error($e->getMessage());
			throw $e;
			return $this->addErrorRedirect("Errore generico", "elenco_richieste_inviate");
		}
	}

	/**
	 * @Route("/valuta/{id_valutazione_checklist}", name="valuta_checklist_istruttoria")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco operazioni", route="elenco_richieste_inviate"),
	 * 				@ElementoBreadcrumb(testo="Istruttoria operazione")
	 * 				})
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ValutazioneChecklistIstruttoria", opzioni={"id" = "id_valutazione_checklist"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function valutaChecklistAction($id_valutazione_checklist) {
		try {
			$valutazione_checklist = $this->getEm()->getRepository("IstruttorieBundle\Entity\ValutazioneChecklistIstruttoria")->find($id_valutazione_checklist);
			$extra = array();
			if ($valutazione_checklist->getChecklist()->getCodice() == "checklist_sintesi_15") {
				$extra["twig"] = "IstruttorieBundle:Istruttoria/Bando_15:checklistIstruttoriaSintesi.html.twig";
			}
			if ($valutazione_checklist->getChecklist()->getCodice() == "griglia_15") {
				$extra["twig"] = "IstruttorieBundle:Istruttoria/Bando_15:checklistIstruttoriaGriglia.html.twig";
			}
			$gestore = $this->container->get("gestore_checklist")->getGestore($valutazione_checklist->getIstruttoria()->getRichiesta()->getProcedura());

			return $gestore->valuta($valutazione_checklist, $extra)->getResponse();
		} catch (SfingeException $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_inviate");
		} catch (Exception $e) {
			throw $e;
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "elenco_richieste_inviate");
		}
	}

	/**
	 * @Route("/{id_richiesta}/riepilogo", name="riepilogo_richiesta")
	 * @PaginaInfo(titolo="Riepilogo dell'operazione",sottoTitolo="dati riepilogativi all'operazione")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco operazioni inviate", route="elenco_richieste_inviate")})
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function riepilogoRichiestaAction($id_richiesta) {
		$gestore_istruttoria = $this->get("gestore_istruttoria")->getGestore();
        $response = $gestore_istruttoria->riepilogoRichiesta($id_richiesta);
        return $response->getResponse();
	}

	/**
	 * @Route("/{id_richiesta}/riepilogo_proponenti", name="riepilogo_proponenti")
	 * @PaginaInfo(titolo="Riepilogo dei proponenti",sottoTitolo="dati riepilogativi dei proponenti")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_inviate")})
	 */
	public function riepilogoProponentiAction($id_richiesta) {
		$gestore_istruttoria = $this->get("gestore_istruttoria")->getGestore();
		$response = $gestore_istruttoria->riepilogoProponenti($id_richiesta);
		return $response->getResponse();
	}

	/**
	 * @Route("/{id_richiesta}/istruttoria_piano_costi/{id_proponente}/{annualita}", name="istruttoria_piano_costi")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco operazioni", route="elenco_richieste_inviate"),
	 * 				@ElementoBreadcrumb(testo="Piano costi")
	 * 				})
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function istruttoriaPianoCostiAction($id_richiesta, $id_proponente, $annualita) {
		try {
			$response = $this->get("gestore_istruttoria_pianocosto")->getGestore()->istruttoriaPianoCostiProponente($id_proponente, $annualita);
			return $response->getResponse();
		} catch (SfingeException $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_inviate");
		} catch (Exception $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "elenco_richieste_inviate");
		}
	}

	/**
	 * @Route("/{id_richiesta}/totali_piano_costi", name="totali_piano_costi")
	 * @PaginaInfo(titolo="Totali piano costi")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco operazioni", route="elenco_richieste_inviate"),
	 * 				@ElementoBreadcrumb(testo="Totali piano costi")
	 * 				})
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function totaliPianoCostiAction($id_richiesta) {
		try {
			$response = $this->get("gestore_istruttoria_pianocosto")->getGestore()->totaliPianoCosti($id_richiesta);
			return $response->getResponse();
		} catch (SfingeException $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_inviate");
		} catch (Exception $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "elenco_richieste_inviate");
		}
	}

	/**
	 * @Route("/{id_richiesta}/esito_finale", name="esito_finale_istruttoria")
	 * @PaginaInfo(titolo="Esito finale istruttoria")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco operazioni", route="elenco_richieste_inviate"),
	 * 				@ElementoBreadcrumb(testo="Esito finale istruttoria")
	 * 				})
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function esitoFinaleAction($id_richiesta) {
		$gestore_istruttoria = $this->get("gestore_istruttoria")->getGestore();
		return $gestore_istruttoria->esitoFinaleIstruttoria($id_richiesta)->getResponse();
	}

	/**
	 * @Route("/{id_richiesta}/dati_cup", name="dati_cup")
	 * @PaginaInfo(titolo="Dati cup richiesta")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco operazioni", route="elenco_richieste_inviate"),
	 * 				@ElementoBreadcrumb(testo="Dati cup")
	 * 				})
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function datiCupAction($id_richiesta) {
		$gestore_istruttoria = $this->get("gestore_istruttoria")->getGestore();
		return $gestore_istruttoria->datiCup($id_richiesta)->getResponse();
	}

	/**
	 * @Route("/{id_richiesta}/documenti_richiesta", name="documenti_richiesta_istruttoria")
	 * @PaginaInfo(titolo="Documenti richiesta",sottoTitolo="documenti caricati nella richiesta")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco operazioni", route="elenco_richieste_inviate"),
	 * 				@ElementoBreadcrumb(testo="Documenti richiesta")
	 * 				})
	 */
	public function documentiRichiestaAction($id_richiesta) {
		$gestore_istruttoria = $this->get("gestore_istruttoria")->getGestore();
		$response = $gestore_istruttoria->aggiornaDocumentiRichiesta($id_richiesta);
		return $response->getResponse();
	}

	/**
	 * @Route("/{id_richiesta}/avanzamento_atc", name="avanzamento_atc")
	 * @PaginaInfo(titolo="Avanzamento attuazione e controllo")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco operazioni", route="elenco_richieste_inviate"),
	 * 				@ElementoBreadcrumb(testo="Avanzamento attuazione e controllo")
	 * 				})
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function avanzamentoATCAction($id_richiesta) {
		$gestore_istruttoria = $this->get("gestore_istruttoria")->getGestore();
		return $gestore_istruttoria->avanzamentoATC($id_richiesta)->getResponse();
	}

	/**
	 * @Route("/crea_integrazione/{id_valutazione_checklist}", name="crea_integrazione_istruttoria")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_inviate")
	 * 				})
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @PaginaInfo(titolo="Richiesta integrazione",sottoTitolo="inserimento di una richiesta di integrazione")
	 * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ValutazioneChecklistIstruttoria", opzioni={"id" = "id_valutazione_checklist"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function creaIntegrazioneAction($id_valutazione_checklist) {
		try {
			$valutazione_checklist = $this->getEm()->getRepository("IstruttorieBundle\Entity\ValutazioneChecklistIstruttoria")->find($id_valutazione_checklist);

			$richiesta = $valutazione_checklist->getIstruttoria()->getRichiesta();
			$this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo istruttoria', $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $richiesta->getId())));
			$this->get('pagina')->aggiungiElementoBreadcrumb($valutazione_checklist->getChecklist()->getNome(), $this->generateUrl("valuta_checklist_istruttoria", array("id_valutazione_checklist" => $valutazione_checklist->getId())));

			$gestore_istruttoria = $this->get("gestore_istruttoria")->getGestore($richiesta->getProcedura());
			return $gestore_istruttoria->creaIntegrazione($id_valutazione_checklist)->getResponse();
		} catch (SfingeException $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_inviate");
		} catch (Exception $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "elenco_richieste_inviate");
		}
	}
    
    /**
	 * @Route("/gestione_integrazione_istruttoria_pa/{id_integrazione_istruttoria}", name="gestione_integrazione_istruttoria_pa")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_inviate")
	 * 				})
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @PaginaInfo(titolo="Richiesta integrazione",sottoTitolo="inserimento di una richiesta di integrazione")
	 * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:IntegrazioneIstruttoria", opzioni={"id" = "id_integrazione_istruttoria"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function gestioneIntegrazioneAction($id_integrazione_istruttoria) {
		try {
			$integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IntegrazioneIstruttoria")->find($id_integrazione_istruttoria);

			$richiesta = $integrazione_istruttoria->getIstruttoria()->getRichiesta();
			$this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo istruttoria', $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $richiesta->getId())));

			$gestore_istruttoria = $this->get("gestore_istruttoria")->getGestore($richiesta->getProcedura());
			return $gestore_istruttoria->gestioneIntegrazione($id_integrazione_istruttoria)->getResponse();
		} catch (SfingeException $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_inviate");
		} catch (Exception $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "elenco_richieste_inviate");
		}
	}

	/**
	 * @Route("/dettaglio_integrazione/{id_integrazione_istruttoria}/{da_comunicazione}", defaults={"da_comunicazione" = "false"}, name="dettaglio_integrazione_istruttoria_pa")
	 * @Template("IstruttorieBundle:Integrazione:dettaglioIntegrazione.html.twig")
	 * @PaginaInfo(titolo="Dettaglio integrazione")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_inviate")
	 * 				})
	 * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:IntegrazioneIstruttoria", opzioni={"id" = "id_integrazione_istruttoria"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("integrazione_istruttoria", options={"mapping": {"id_integrazione_istruttoria"   : "id"}})
	 */
	public function dettaglioIntegrazioneIstruttoriaAction(IntegrazioneIstruttoria $integrazione_istruttoria, $da_comunicazione) {
		$richiesta = $integrazione_istruttoria->getIstruttoria()->getRichiesta();
		$this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo istruttoria', $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $richiesta->getId())));

		$valutazione_checklist = $integrazione_istruttoria->getValutazioneChecklist();
		$this->get('pagina')->aggiungiElementoBreadcrumb($valutazione_checklist->getChecklist()->getNome(), $this->generateUrl("valuta_checklist_istruttoria", array("id_valutazione_checklist" => $valutazione_checklist->getId())));

		$csrfTokenManager = $this->container->get("security.csrf.token_manager");
		$token = $csrfTokenManager->getToken("token")->getValue();
		$documenti_indicizzati = array();

		if (!is_null($integrazione_istruttoria->getRisposta())) {
			foreach ($integrazione_istruttoria->getRisposta()->getDocumenti() as $documento) {
				$tipologia_id = $documento->getDocumentoFile()->getTipologiaDocumento()->getId();

				$documenti_indicizzati[$tipologia_id] = $documenti_indicizzati[$tipologia_id] ?? [];
				
				$proponente_id = is_null($documento->getProponente()) ? null : $documento->getProponente()->getId();

				$documenti_indicizzati[$tipologia_id][$proponente_id] = $documenti_indicizzati[$tipologia_id][$proponente_id] ?? [];
				
				$documenti_indicizzati[$tipologia_id][$proponente_id][] = $documento;
			}
		}

		$da_comunicazione == 'false' ? $da_comunicazione = false : $da_comunicazione = true;

		return array(
			'integrazione_istruttoria' => $integrazione_istruttoria, 
			"documenti_indicizzati" => $documenti_indicizzati, 
			"da_comunicazione" => $da_comunicazione,
			"token" => $token
		);
	}
    
    /**
     * @Route("/elimina_integrazione_istruttoria_pa/{id_integrazione_istruttoria}", name="elimina_integrazione_istruttoria_pa")
     * @param int $id_integrazione_istruttoria
     * @return mixed
     * @throws Exception
     */
    public function eliminaIntegrazioneAction($id_integrazione_istruttoria) {
        $integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IntegrazioneIstruttoria")->find($id_integrazione_istruttoria);
        $response = $this->get("gestore_istruttoria")->getGestore($integrazione_istruttoria->getProcedura())->eliminaIntegrazioneIstruttoria($id_integrazione_istruttoria);
        return $response->getResponse();
    }
	
	/**
	 * @Route("/{id_integrazione_istruttoria}/genera_pdf_integrazione_richiesta", name="genera_pdf_integrazione_richiesta")
	 */
	public function generaPdfIntegrazioneRichiesta($id_integrazione_istruttoria) {
        $integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IntegrazioneIstruttoria")->find($id_integrazione_istruttoria);
		$richiesta = $integrazione_istruttoria->getIstruttoria()->getRichiesta();
		$response = $this->get("gestore_istruttoria")->getGestore($richiesta->getProcedura())->generaPdfIntegrazione($integrazione_istruttoria, true);
		return $response;
	}


	/**
	 * @Route("/{id_richiesta}/elimina_documento_istruttoria/{id_documento_istrutttoria}", name="elimina_documento_istruttoria")
	 */
	public function eliminaDocumentoIstruttoriaAction($id_documento_istrutttoria, $id_richiesta) {
		$response = $this->get("gestore_istruttoria")->getGestore()->eliminaDocumentoIstruttoria($id_documento_istrutttoria);
		return $response->getResponse();
	}

	/**
	 * @Route("/{id_istruttoria}/elimina_documento_comunicazione/{id_documento}", name="elimina_documento_comunicazione")
	 * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:IstruttoriaRichiesta", opzioni={"id" = "id_istruttoria"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function eliminaDocumentoComunicazioneEsitoAction($id_documento, $id_istruttoria) {
		$istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IstruttoriaRichiesta")->find($id_istruttoria);
		return $this->get("gestore_istruttoria")->getGestore($istruttoria->getRichiesta()->getProcedura())->eliminaDocumentoComunicazioneEsito($id_documento, $istruttoria);
	}
	
	/**
	 * @Route("/{id_istruttoria}/elimina_documento_comunicazione_richiesta/{id_documento}", name="elimina_documento_comunicazione_richiesta")
	 * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:IstruttoriaRichiesta", opzioni={"id" = "id_istruttoria"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function eliminaDocumentoComunicazioneProgettoAction($id_documento, $id_istruttoria) {
		$documento = $this->getEm()->getRepository("IstruttorieBundle\Entity\ComunicazioneProgettoDocumento")->find($id_documento);
		$comunicazione = $documento->getComunicazione();
		$opzioni["url_indietro"] = $this->generateUrl("gestione_comunicazione_progetto", array("id_comunicazione_progetto" => $comunicazione->getId()));
		$response =  $this->get("gestore_comunicazione_progetto")->getGestore($comunicazione->getRichiesta()->getProcedura())->eliminaDocumentoComunicazioneRichiesta($documento, $opzioni);
		return $response->getResponse();
	}

	/**
	 * @Route("/{id_richiesta}/genera_pdf_istruttoria_richiesta", name="genera_pdf_istruttoria_richiesta")
	 */
	public function generaPdfIstruttoriaRichiesta($id_richiesta) {
		$response = $this->get("gestore_istruttoria")->getGestore()->generaPdfIstruttoriaRichiesta($id_richiesta);
		return $response;
	}
    
	/**
	 * @Route("/{id_richiesta}/nucleo", name="nucleo")	
	 * @Menuitem(menuAttivo = "nucleo")
	 * @PaginaInfo(titolo="Nucleo",sottoTitolo="")
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function nucleo($id_richiesta) {
		return $response = $this->get("gestore_istruttoria")->getGestore()->nucleoIstruttoria($id_richiesta)->getResponse();
	}

	/**
	 * @Route("/{id_richiesta}/elimina_documento_nucleo_istruttoria/{id_documento_nucleo}", name="elimina_documento_nucleo_istruttoria")
	 */
	public function eliminaDocumentoNucleoIstruttoria($id_richiesta, $id_documento_nucleo) {
		$response = $this->get("gestore_istruttoria")->getGestore()->eliminaDocumentoNucleoIstruttoria($id_richiesta, $id_documento_nucleo);
		return $response->getResponse();
	}

	/**
	 * @Route("/comunicazione_esito/{id_istruttoria}", name="comunicazione_esito")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_inviate")
	 * 				})
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @PaginaInfo(titolo="Comunicazione esito istruttoria",sottoTitolo="inserimento di una comunicazione di esito")
	 * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:IstruttoriaRichiesta", opzioni={"id" = "id_istruttoria"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function creaComunicazioneEsitoAction($id_istruttoria) {
		try {
			$istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IstruttoriaRichiesta")->find($id_istruttoria);

			$richiesta = $istruttoria->getRichiesta();
			$this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo istruttoria', $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $richiesta->getId())));

			$gestore_istruttoria = $this->get("gestore_istruttoria")->getGestore($richiesta->getProcedura());
			return $gestore_istruttoria->esitoComunicazioneIstruttoria($istruttoria)->getResponse();
		} catch (SfingeException $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_inviate");
		} catch (Exception $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "elenco_richieste_inviate");
		}
	}

	/**
	 * @Route("/{id_istruttoria}/elenco_comunicazioni", name="elenco_comunicazioni")	
	 * @Template("IstruttorieBundle:Istruttoria:elencoComunicazioni.html.twig")
	 * @PaginaInfo(titolo="Comunicazioni",sottoTitolo="")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:IstruttoriaRichiesta", opzioni={"id" = "id_istruttoria"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function elencoComunicazioniAction($id_istruttoria) {
		$istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IstruttoriaRichiesta")->find($id_istruttoria);
		$csrfTokenManager = $this->container->get("security.csrf.token_manager");
		$token = $csrfTokenManager->getToken("token")->getValue();
		return array('istruttoria' => $istruttoria, 'token' => $token, 'menu' => 'comunicazioni');
	}

	/**
	 * @Route("/{id_comunicazione}/genera_facsimile_esito_istruttoria", name="genera_facsimile_esito_istruttoria")
	 */
	public function generaFacsimileComunicazioneEsitoAction($id_comunicazione) {
		$comunicazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoria")->find($id_comunicazione);
		return $this->get("gestore_istruttoria")->getGestore($comunicazione->getIstruttoria()->getRichiesta()->getProcedura())->generaFacsimileComunicazioneEsito($comunicazione);
	}

	/**
	 * @Route("/{id_comunicazione}/scarica_comunicazione", name="scarica_comunicazione")	
	 * @Template("IstruttorieBundle:Istruttoria:elencoComunicazioni.html.twig")
	 * @PaginaInfo(titolo="Comunicazioni",sottoTitolo="")
	 * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function scaricaComunicazioneEsitoAction($id_comunicazione) {

		$comunicazione = $this->getEm()->getRepository("IstruttorieBundle:ComunicazioneEsitoIstruttoria")->find($id_comunicazione);
		if (is_null($comunicazione)) {
			return $this->addErrorRedirect("Comunicazione non valida", "elenco_richieste");
		}
		if (is_null($comunicazione->getDocumento())) {
			return $this->addErrorRedirect("Nessun documento associato alla comuncazione", "elenco_richieste");
		}
		return $this->get("documenti")->scaricaDaId($comunicazione->getDocumento()->getId());
	}
	
	/**
	 * @Route("/{id_istruttoria}/scarica_comunicazione_progetto_richiesta/{id_comunicazione}", name="scarica_comunicazione_progetto_richiesta")	
	 * @Template("IstruttorieBundle:Istruttoria:elencoComunicazioni.html.twig")
	 * @PaginaInfo(titolo="Comunicazioni",sottoTitolo="")
	 * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:IstruttoriaRichiesta", opzioni={"id" = "id_istruttoria"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function scaricaComunicazioneProgettoAction($id_comunicazione, $id_istruttoria) {

		$comunicazione = $this->getEm()->getRepository("IstruttorieBundle:ComunicazioneProgetto")->find($id_comunicazione);
		if (is_null($comunicazione)) {
			return $this->addErrorRedirect("Comunicazione non valida", "elenco_richieste");
		}
		if (is_null($comunicazione->getDocumento())) {
			return $this->addErrorRedirect("Nessun documento associato alla comuncazione", "elenco_richieste");
		}
		return $this->get("documenti")->scaricaDaId($comunicazione->getDocumento()->getId());
	}

	/**
	 * @Route("/cup/settore_natura/{richiesta_id}/{codice}", name="settore_natura")
	 * @param integer $richiesta_id
	 * @param integer $codice
	 * @return JsonResponse
	 */
	public function getCupSettoreDaNatura($richiesta_id, $codice) {
		$em = $this->getEm();	/** @var \Doctrine\ORM\EntityManager $em */
		$richiesta = $em->getRepository('RichiesteBundle:Richiesta')->findOneById($richiesta_id);
		if( \is_null($richiesta) ){
			return new Response('Richiesta non trovata', Response::HTTP_NOT_FOUND);
		}
		$res = $this->get("gestore_istruttoria")->getGestore($richiesta->getProcedura())->getSelezioniCup($richiesta_id, false);
		$res = $res['cup_settore'];
		if(\count($res) == 0){
			$res = $em->getRepository('CipeBundle:Classificazioni\CupSettore')->findAll();
		}
		$res = \array_filter( $res, function($item) use($codice){ /** @var CipeBundle\Entity\Classificazioni\CupSettore $item */
			return \count( \array_filter($item->getCupNature()->toArray(), function($item) use ($codice){ /** @var CipeBundle\Entity\Classificazioni\CupNatura $item */
				return $item->getId() == $codice;
			}));
			
		});
		$res = array_map( function($item){ /** @var CipeBundle\Entity\Classificazioni\CupClassificazione $item */
			return $item->toStdObject();
		}, $res);
		return new JsonResponse($res);
	}

	/**
	 * @Route("/cup/tipologia_natura/{richiesta_id}/{codice}", name="tipologia_natura")
	 * @param integer $richiesta_id
	 * @param integer $codice
	 * @return JsonResponse
	 */
	public function getCupTipologiaDaNatura($richiesta_id, $codice) {
		$em = $this->getEm();
		$richiesta = $em->getRepository("RichiesteBundle:Richiesta")->findOneById($richiesta_id);
		if( \is_null($richiesta) ){
			return new Response('Richiesta non trovata', Response::HTTP_NOT_FOUND);
		}
		$res = $this->get("gestore_istruttoria")->getGestore($richiesta->getProcedura())->getSelezioniCup($richiesta_id, false);
		$res = $res['cup_tipologia'];
		if(\count($res) == 0 ){
			$res = $em->getRepository('CipeBundle:Classificazioni\CupTipologia')->findAll();
		}
		$res = \array_filter( $res, function($item) use($codice){ /** @var CipeBundle\Entity\Classificazioni\CupTipologia $item */
			return $item->getCupNatura()->getId() == $codice;
		});
		$res = array_map( function($item){ /** @var CipeBundle\Entity\Classificazioni\CupClassificazione $item */
			return $item->toStdObject();
		}, $res);
		return new JsonResponse($res);
	}

	/**
	 * @Route("/cup/sottosettore_settore/{richiesta_id}/{codice}", name="sottosettore_settore")
	 * @param integer $richiesta_id
	 * @param integer $codice
	 * @return JsonResponse
	 */
	public function getCupSottoSettoreDaSettore($richiesta_id, $codice) {
		$em = $this->getEm();
		$richiesta = $em->getRepository("RichiesteBundle:Richiesta")->findOneById($richiesta_id);
		if( \is_null($richiesta) ){
			return new Response('Richiesta non trovata', Response::HTTP_NOT_FOUND);
		}
		$res = $this->get("gestore_istruttoria")->getGestore($richiesta->getProcedura())->getSelezioniCup($richiesta_id, false);
		$res = $res['cup_sottosettore'];
		if(\count($res) == 0 ){
			$res = $em->getRepository('CipeBundle:Classificazioni\CupSottosettore')->findAll();
		}		
		$res = \array_filter( $res, function($item) use($codice){ /** @var CipeBundle\Entity\Classificazioni\CupSottosettore $item */
			return $item->getCupSettore()->getId() == $codice;
		});
		$res = array_map( function($item){ /** @var CipeBundle\Entity\Classificazioni\CupClassificazione $item */
			return $item->toStdObject();
		}, $res);
		return new JsonResponse($res);
	}

	/**
	 * @Route("/cup/categoria_sottosettore/{richiesta_id}/{codice}", name="categoria_sottosettore")
	 * @param integer $richiesta_id
	 * @param integer $codice
	 * @return JsonResponse
	 */
	public function getCupCategoriaDaSottoSettore($richiesta_id, $codice) {
		$em = $this->getEm();
		$richiesta = $em->getRepository("RichiesteBundle:Richiesta")->findOneById($richiesta_id);
		if( \is_null($richiesta) ){
			return new Response('Richiesta non trovata', Response::HTTP_NOT_FOUND);
		}
		$res = $this->get("gestore_istruttoria")->getGestore($richiesta->getProcedura())->getSelezioniCup($richiesta_id,false);
		$res = $res['cup_categoria'];
		if(\count($res) == 0 ){
			$res = $em->getRepository('CipeBundle:Classificazioni\CupCategoria')->findAll();
		}	
		$res = \array_filter( $res, function($item) use($codice){ /** @var CipeBundle\Entity\Classificazioni\CupCategoria 			$item */
			return $item->getCupSottosettore()->getId() == $codice;
		});
		$res = array_map( function($item){ /** @var CipeBundle\Entity\Classificazioni\CupClassificazione $item */
			return $item->toStdObject();
		}, $res);
		return new JsonResponse($res);
	}
	
	/**
	 * @Route("/crea_comunicazione_progetto/{id_istruttoria}", name="crea_comunicazione_progetto")
	 * ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ComunicazioneProgetto", opzioni={"id" = "id_valutazione_checklist"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function creaComunicazioneProgettoAction($id_istruttoria) {
		try {
			$istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IstruttoriaRichiesta")->find($id_istruttoria);
			$richiesta = $istruttoria->getRichiesta();
			$gestore_istruttoria = $this->get("gestore_comunicazione_progetto")->getGestore($richiesta->getProcedura());
			return $gestore_istruttoria->CreaComunicazioneProgetto($richiesta, 'RICHIESTA')->getResponse();
		} catch (SfingeException $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_inviate");
		} catch (Exception $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "elenco_richieste_inviate");
		}
	}
	
	/**
	 * @Route("/gestione_comunicazione_progetto/{id_comunicazione_progetto}", name="gestione_comunicazione_progetto")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_inviate")
	 * 				})
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @PaginaInfo(titolo="Comunicazione progetto",sottoTitolo="inserimento di una comunicazione di progetto")
	 * @ControlloAccesso(contesto="richiesta", classe="IstruttorieBundle:ComunicazioneProgetto", opzioni={"id" = "id_comunicazione_progetto"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
	 */
	public function gestioneComunicazioneProgettoAction($id_comunicazione_progetto) {
		try {
			$comunicazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\ComunicazioneProgetto")->find($id_comunicazione_progetto);

			$richiesta = $comunicazione->getRichiesta();
			$this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo istruttoria', $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $richiesta->getId())));

			$gestore = $this->get("gestore_comunicazione_progetto")->getGestore($richiesta->getProcedura());
			return $gestore->gestioneComunicazioneProgetto($comunicazione)->getResponse();
		} catch (SfingeException $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_inviate");
		} catch (Exception $e) {
			$this->get("logger")->error($e->getMessage());
			return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "elenco_richieste_inviate");
		}
	}
	
	/**
	 * @Route("/{id_comunicazione}/genera_facsimile_comunicazione_progetto", name="genera_facsimile_comunicazione_progetto")
	 */
	public function generaFacsimileComunicazioneProgettoAction($id_comunicazione) {
		$comunicazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\ComunicazioneProgetto")->find($id_comunicazione);
		return $this->get("gestore_comunicazione_progetto")->getGestore($comunicazione->getRichiesta()->getProcedura())->generaFacsimileComunicazioneProgetto($comunicazione);
	}
	
	/**
	 * @Route("/dettaglio_comunicazione_progetto_pa/{id_comunicazione_progetto}/{da_comunicazione}", defaults={"da_comunicazione" = "false"}, name="dettaglio_comunicazione_progetto_pa")
	 * @Template("IstruttorieBundle:RispostaComunicazioneProgetto:dettaglioComunicazioneProgetto.html.twig")
	 * @PaginaInfo(titolo="Dettaglio comunicazione progetto")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ComunicazioneProgetto", opzioni={"id" = "id_comunicazione_progetto"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function dettaglioComunicazioneProgettoAction($id_comunicazione_progetto, $da_comunicazione) {
		$comunicazione_progetto = $this->getEm()->getRepository("IstruttorieBundle\Entity\ComunicazioneProgetto")->find($id_comunicazione_progetto);
		$richiesta = $comunicazione_progetto->getRichiesta();
		$istruttoria = $richiesta->getIstruttoria();
		$this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo istruttoria', $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $richiesta->getId())));
		$documenti = array();
		if ($comunicazione_progetto->hasRispostaInviata()) {
			$documenti = $comunicazione_progetto->getRisposta()->getDocumenti();
		}
		$da_comunicazione == 'false' ? $da_comunicazione = false : $da_comunicazione = true;

		return array('menu' => 'comunicazioni', 'istruttoria' => $istruttoria, 'comunicazione_progetto' => $comunicazione_progetto, "documenti" => $documenti, "da_comunicazione" => $da_comunicazione);
	}

	/**
	 * @Route("/dettaglio_comunicazione_esito_pa/{id_comunicazione_progetto}/{da_comunicazione}", defaults={"da_comunicazione" = "false"}, name="dettaglio_comunicazione_esito_pa")
	 * @Template("IstruttorieBundle:RispostaComunicazione:dettaglioComunicazioneEsito.html.twig")
	 * @PaginaInfo(titolo="Dettaglio comunicazione progetto")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione_progetto"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function dettaglioComunicazioneEsitoAction($id_comunicazione_progetto, $da_comunicazione) {
		$comunicazione_esito = $this->getEm()->getRepository("IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoria")->find($id_comunicazione_progetto);
		$richiesta = $comunicazione_esito->getIstruttoria()->getRichiesta();
		$istruttoria = $comunicazione_esito->getIstruttoria();
		$this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo istruttoria', $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $richiesta->getId())));
		$documenti = array();
		if ($comunicazione_esito->hasRispostaInviata()) {
			$documenti = $comunicazione_esito->getRisposta()->getDocumenti();
		}
		$da_comunicazione == 'false' ? $da_comunicazione = false : $da_comunicazione = true;

		return array('menu' => 'comunicazioni', 'istruttoria' => $istruttoria, 'comunicazione_esito' => $comunicazione_esito, "documenti" => $documenti, "da_comunicazione" => $da_comunicazione);
	}
	
	/**
	 * @Route("/elenco_comunicazioni_inviate_da_pa_esito/{sort}/{direction}/{page}", defaults={"sort" = "i.id", "direction" = "asc", "page" = "1"}, name="elenco_comunicazioni_inviate_da_pa_esito")	
	 * @Template("IstruttorieBundle:Comunicazioni:elencoComunicazioniEsPa.html.twig")
	 * @PaginaInfo(titolo="Comunicazioni",sottoTitolo="")
	 * @Menuitem(menuAttivo = "elencoComunicazioniInviate")
	 */
	public function elencoComunicazioniInviateDaPaEsitoAction() {
		$datiRicerca = new \IstruttorieBundle\Form\Entity\RicercaComunicazionePa();
		$datiRicerca->setTipo('ESITO');
        $datiRicerca->setUtente($this->getUser());
		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		$params = array(
			'menu' => 'com_esito', 
			'risultati' => $risultato["risultato"], 
			"form_ricerca" => $risultato["form_ricerca"], 
			"filtro_attivo" => $risultato["filtro_attivo"]);

		return $params;
	}
	
	/**
	 * @Route("/elenco_comunicazioni_inviate_da_pa_var/{sort}/{direction}/{page}", defaults={"sort" = "i.id", "direction" = "asc", "page" = "1"}, name="elenco_comunicazioni_inviate_da_pa_var")	
	 * @Template("IstruttorieBundle:Comunicazioni:elencoComunicazioniVarPa.html.twig")
	 * @PaginaInfo(titolo="Comunicazioni",sottoTitolo="")
	 * @Menuitem(menuAttivo = "elencoComunicazioniInviate")
	 */
	public function elencoComunicazioniInviateDaPaVariazioniAction() {
		$datiRicerca = new \IstruttorieBundle\Form\Entity\RicercaComunicazionePa();
		$datiRicerca->setTipo('VARIAZIONE');
        $datiRicerca->setUtente($this->getUser());
		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		$params = array(
			'menu' => 'com_variazione',
			'risultati' => $risultato["risultato"], 
			"form_ricerca" => $risultato["form_ricerca"], 
			"filtro_attivo" => $risultato["filtro_attivo"]);

		return $params;
	}
	
	/**
	 * @Route("/elenco_comunicazioni_inviate_da_pa_prg/{sort}/{direction}/{page}", defaults={"sort" = "i.id", "direction" = "asc", "page" = "1"}, name="elenco_comunicazioni_inviate_da_pa_prg")	
	 * @Template("IstruttorieBundle:Comunicazioni:elencoComunicazioniPrgPa.html.twig")
	 * @PaginaInfo(titolo="Comunicazioni",sottoTitolo="")
	 * @Menuitem(menuAttivo = "elencoComunicazioniInviate")
	 */
	public function elencoComunicazioniInviateDaPaProgettoAction() {
		$datiRicerca = new \IstruttorieBundle\Form\Entity\RicercaComunicazionePa();
		$datiRicerca->setTipo('PROGETTO');
        $datiRicerca->setUtente($this->getUser());
		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		$params = array(
			'menu' => 'com_generica', 
			'risultati' => $risultato["risultato"],
			"form_ricerca" => $risultato["form_ricerca"], 
			"filtro_attivo" => $risultato["filtro_attivo"]);

		return $params;
	}
	
	/**
	 * @Route("/elenco_comunicazioni_pa_pulisci/", name="elenco_comunicazioni_pa_pulisci")
	 */
	public function elencoComunicazioniPaPulisciAction() {
		$this->get("ricerca")->pulisci(new \IstruttorieBundle\Form\Entity\RicercaComunicazionePa());
		return $this->redirectToRoute("elenco_comunicazioni_inviate_da_pa_esito");
	}
	
    /**
     * @Route("/{id_richiesta}/genera_pdf_check_list", name="genera_pdf_checklist_istruttoria")
     *
     * @param $id_richiesta
     * @return mixed
     * @throws Exception
     */
    public function generaPdfChecklistIstruttoria($id_richiesta) {
        $response = $this->get("gestore_istruttoria")->getGestore()->generaPdfChecklistIstruttoria($id_richiesta);
        return $response;
    }

    /**
     * @Route("/{id_richiesta}/genera_excel_piano_costi", name="genera_excel_piano_costi")
     *
     * @param $id_richiesta
     * @return mixed
     * @throws Exception
     */
    public function generaExcelPianoCostiIstruttoria($id_richiesta) {
        /** @var EntityManager $em */
        $em = $this->getEm();
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $response = $this->get("gestore_istruttoria_pianocosto")->getGestore()->generaExcelPianoCostiIstruttoria($richiesta);
        return $response;
    }

    /**
     * @Route("/{id_valutazione_checklist}/genera_excel_check_list", name="genera_excel_checklist_istruttoria")
     *
     * @param $id_valutazione_checklist
     * @return mixed
     * @throws Exception
     */
    public function generaExcelChecklistIstruttoria($id_valutazione_checklist) {
        /** @var EntityManager $em */
        $em = $this->getEm();
        /** @var ValutazioneChecklistIstruttoria $valutazioneChecklistIstruttoria */
        $valutazioneChecklistIstruttoria = $em->getRepository("IstruttorieBundle:ValutazioneChecklistIstruttoria")->find($id_valutazione_checklist);
        $response = $this->get("gestore_checklist")->getGestore($valutazioneChecklistIstruttoria->getIstruttoria()->getRichiesta()->getProcedura())->generaExcelChecklistIstruttoria($valutazioneChecklistIstruttoria);
        return $response;
    }
    
    /**
	 * @Route("/{id_richiesta}/sblocca_istruttoria_richiesta", name="sblocca_istruttoria_richiesta")
	 * @PaginaInfo(titolo="Riepilogo dell'operazione",sottoTitolo="dati riepilogativi all'operazione")
	 * @Menuitem(menuAttivo = "elencoRichiesteInviate")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco operazioni inviate", route="elenco_richieste_inviate")})
	 * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
	 */
	public function sbloccaIstruttoriaRichiestaAction($id_richiesta) {
        if(!$this->isGranted("ROLE_SUPER_ADMIN")) {
            $this->addFlash('error', "Non sei abilitato ad eseguira l'operazione");
            return $this->redirect($this->generateUrl('riepilogo_richiesta', array('id_richiesta' => $id_richiesta)));
        }
		$gestore_istruttoria = $this->get("gestore_istruttoria")->getGestore();
        $response = $gestore_istruttoria->sbloccaIstruttoriaRichiesta($id_richiesta);
        return $response->getResponse();
	}

    /**
     * @Route("/importo_irap/{id_istruttoria}/{codice_fiscale}", name="importo_irap")
     * @Breadcrumb(elementi={
     *              @ElementoBreadcrumb(testo="Elenco operazioni", route="elenco_richieste_inviate"),
     *              @ElementoBreadcrumb(testo="Istruttoria operazione")
     *              })
     * @Menuitem(menuAttivo = "elencoRichiesteInviate")
     * @PaginaInfo(titolo="Importo IRAP Agenzie delle Entrate")
     * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:IstruttoriaRichiesta", opzioni={"id" = "id_istruttoria"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     *
     * @param int $id_istruttoria
     * @param string $codice_fiscale
     * @return mixed
     * @throws Exception
     */
    public function importoIrapAction(int $id_istruttoria, string $codice_fiscale) {
        /** @var IstruttoriaRichiesta $istruttoria */
        $istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IstruttoriaRichiesta")->find($id_istruttoria);
        /** @var GestoreResponse $response */
        $response = $this->get("gestore_istruttoria")->getGestore($istruttoria->getProcedura())->inserisciImportoIrap($id_istruttoria, $codice_fiscale);
        return $response->getResponse();
    }

    /**
     * @Route("/importo_irap_istruttoria/{id_istruttoria}", name="importo_irap_istruttoria")
     * @Breadcrumb(elementi={
     *              @ElementoBreadcrumb(testo="Elenco operazioni", route="elenco_richieste_inviate"),
     *              @ElementoBreadcrumb(testo="Istruttoria operazione")
     *              })
     * @Menuitem(menuAttivo = "elencoRichiesteInviate")
     * @PaginaInfo(titolo="Importo IRAP")
     * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:IstruttoriaRichiesta", opzioni={"id" = "id_istruttoria"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     *
     * @param int $id_istruttoria
     * @return mixed
     * @throws Exception
     */
    public function importoIrapIstruttoriaAction(int $id_istruttoria) {
        /** @var IstruttoriaRichiesta $istruttoria */
        $istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IstruttoriaRichiesta")->find($id_istruttoria);
        /** @var GestoreResponse $response */
        $response = $this->get("gestore_istruttoria")->getGestore($istruttoria->getProcedura())->inserisciImportoIrapIstruttoria($id_istruttoria);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/sblocca_esito_finale", name="sblocca_esito_finale_istruttoria")
     */
    public function sbloccaEsitoFinaleIstruttoriaAction($id_richiesta): RedirectResponse
    {
        $gestore_istruttoria = $this->get("gestore_istruttoria")->getGestore();
        $gestore_istruttoria->sbloccaEsitoFinaleIstruttoria($id_richiesta);
        return $this->redirectToRoute("esito_finale_istruttoria", ["id_richiesta" => $id_richiesta]);
    }
}
