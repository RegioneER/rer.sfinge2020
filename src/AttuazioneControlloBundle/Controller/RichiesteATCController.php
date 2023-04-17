<?php

namespace AttuazioneControlloBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use BaseBundle\Annotation\ControlloAccesso;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Entity\ProceduraAggiudicazione;
use BaseBundle\Exception\SfingeException;

/**
 * @Route("/beneficiario/richieste_atc")
 */
class RichiesteATCController extends \BaseBundle\Controller\BaseController {

	/**
	 * @Route("/elenco", name="elenco_gestione_beneficiario")
	 * @Template()
	 * @PaginaInfo(titolo="Elenco progetti in gestione",sottoTitolo="mostra l'elenco dei progetti in gestione")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_gestione_beneficiario")})
	 */
	public function elencoRichiesteAction() {
		$soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
		$soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
		$utente = $this->getUser();
		if (is_null($soggetto)) {
			return $this->addErrorRedirect("Soggetto non valido", "home");
		}
		$richieste = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->getRichiesteDaSoggettoInGestione($soggetto->getId());
		$richiesteOut = $this->valutaVisibilitaRichiesta($richieste, $soggetto, $utente);

		return array("richieste" => $richiesteOut);
	}

	/**
	 * @Route("/{id_richiesta}/accetta_contributo", name="accetta_contributo")
	 * @PaginaInfo(titolo="Accettazione contributo",sottoTitolo="pagina di riepilogo della richiesta che consente l'accettazione del contributo")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_gestione_beneficiario")})
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}) 
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
	 */
	public function accettaContributoAction($id_richiesta) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->accettaContributo($id_richiesta);
	}

	/**
	 * @Route("/{id_richiesta}/gestione_indicatori_ben", name="gestione_monitoraggio_indicatori_ben")
	 * @PaginaInfo(titolo="Indicatori di output", sottoTitolo="pagina di gestione degli indicatori di output")
	 * @Menuitem(menuAttivo="elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 */
	public function gestioneIndicatoriBenAction($id_richiesta) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->gestioneIndicatori($richiesta);
	}

	/**
	 * @Route("/{id_richiesta}/gestione_fasi_procedurali_ben", name="gestione_monitoraggio_fasi_procedurali_ben")
	 * @PaginaInfo(titolo="Fasi procedurali", sottoTitolo="pagina di gestione delle fasi procedurali")
	 * @Menuitem(menuAttivo="elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 */
	public function gestioneFasiProceduraliAction($id_richiesta) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->gestioneFasiProcedurali($richiesta);
	}

	/**
	 * @Route("/{id_richiesta}/crea_impegno_ben", name="crea_monitoraggio_impegni_ben")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 */
	public function creaImpegniAction($id_richiesta) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}
		$impegno = new RichiestaImpegni($richiesta);
		$richiesta->addMonImpegni($impegno);
		$em = $this->getEm();
		try {
			$em->persist($impegno);
			$em->flush($impegno);
		} catch (\Exception $e) {
			$this->get('logger')->error($e->getMessage());
			$this->addErrorRedirect('Errore durante la creazione dell\'impegno', 'gestione_monitoraggio_impegni_ben', ['id_richiesta' => $id_richiesta]);
		}
		return $this->addSuccessRedirect('Impegno inserito con successo', 'gestione_modifica_monitoraggio_impegni_ben', [
					'id_richiesta' => $id_richiesta,
					'id_impegno' => $impegno->getId(),
		]);
	}

	/**
	 * @Route("/{id_richiesta}/modifica_impegno_ben/{id_impegno}", name="gestione_modifica_monitoraggio_impegni_ben", defaults={"id_impegno": NULL})
	 * @PaginaInfo(titolo="Modifica impegno o disimpegno", sottoTitolo="pagina di gestione degli impegni e dei disimpegni")
	 * @Menuitem(menuAttivo="elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:RichiestaImpegni", opzioni={"id": "id_impegno"})
	 */
	public function modificaImpegnoAction($id_richiesta, $id_impegno) {
		$em = $this->getEm();
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}

		$impegno = $em->getRepository('AttuazioneControlloBundle:RichiestaImpegni')->find($id_impegno);
		if ($richiesta->getSoggetto()->getId() !== $impegno->getSoggetto()->getId()) {
			$impegno = null;
		}

		$paginaService = $this->get('pagina');
		$paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$paginaService->aggiungiElementoBreadcrumb("Impegni e disimpegni", $this->generateUrl("gestione_monitoraggio_impegni_ben", array("id_richiesta" => $id_richiesta)));
		$paginaService->aggiungiElementoBreadcrumb("Modifica impegno o disimpegno");

		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->gestioneFormImpegno($richiesta, $impegno);
	}

	/**
	 * @Route("/{id_richiesta}/elimina_impegno_ben/{id_impegno}", name="gestione_elimina_monitoraggio_impegni_ben")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:RichiestaImpegni", opzioni={"id": "id_impegno"})
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 */
	public function eliminaImpegnoAction($id_richiesta, $id_impegno) {
		$this->get('base')->checkCsrf('token');
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->eliminaImpegno($richiesta, $id_impegno);
	}

	/**
	 * @Route("/{id_richiesta}/elimina_documento_impegno_ben/{id_documento}", name="elimina_documento_impegno_ben")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 * @ControlloAccesso(contesto="richiesta", classe="AttuazioneControlloBundle:DocumentoImpegno", opzioni={"id": "id_documento"})
	 * @throws \Exception
	 */
	public function eliminaDocumentoImpegnoAction($id_richiesta, $id_documento) {
		$this->get('base')->checkCsrf('token');
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}

		$documento = $this->getEm()->getRepository('AttuazioneControlloBundle:DocumentoImpegno')->find($id_documento);
		if (\is_null($documento)) {
			throw new SfingeException('Documento non trovato');
		}
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->eliminaDocumentoImpegno($richiesta, $documento);
	}

	/**
	 * @Route("/{id_richiesta}/gestione_procedura_aggiudicazione_ben", name="gestione_monitoraggio_procedura_aggiudicazione_ben")
	 * @PaginaInfo(titolo="Procedure di aggiudicazione", sottoTitolo="pagina di gestione degli impegni e dei disimpegni")
	 * @Menuitem(menuAttivo="elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 */
	public function gestioneProceduraAggiudicazioneAction($id_richiesta) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->gestioneProceduraAggiudicazione($richiesta);
	}

	/**
	 * @Route("/{id_richiesta}/modifica_procedura_aggiudicazione_ben/{id_procedura_aggiudicazione}", name="gestione_monitoraggio_modifica_procedura_aggiudicazione_ben", defaults={"id_procedura_aggiudicazione": NULL})
	 * @PaginaInfo(titolo="Procedure di aggiudicazione", sottoTitolo="Modifica o inserimento delle procedure di aggiudicazione")
	 * @Menuitem(menuAttivo="elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 */
	public function gestioneModificaProceduraAggiudicazioneAction($id_richiesta, $id_procedura_aggiudicazione) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->gestioneModificaProceduraAggiudicazione($richiesta, $id_procedura_aggiudicazione);
	}

	/**
	 * @Route("/{id_richiesta}/crea_procedura_aggiudicazione_ben", name="gestione_monitoraggio_crea_procedura_aggiudicazione_ben")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 */
	public function creaProceduraAggiudicazioneAction($id_richiesta) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}
		$procedura = new ProceduraAggiudicazione($richiesta);
		$richiesta->addMonProcedureAggiudicazione($procedura);
		try {
			$em = $this->getEm();
			$em->persist($procedura);
			$em->flush();
		} catch (\Exception $ex) {
			$this->get('logger')->error($ex->getMessage());
			return $this->addErrorRedirect(
							"Errore durante la creazione della procedura di aggiudicazione", 'gestione_monitoraggio_procedura_aggiudicazione_ben', ['id_richiesta' => $id_richiesta]
			);
		}
		return $this->addSuccessRedirect('Procedura di aggiudicazione creata con successo', 'gestione_monitoraggio_modifica_procedura_aggiudicazione_ben', ['id_richiesta' => $id_richiesta, 'id_procedura_aggiudicazione' => $procedura->getId()]
		);
	}

	/**
	 * @Route("/{id_richiesta}/elimina_procedura_aggiudicazione_ben/{id_procedura_aggiudicazione}", name="gestione_monitoraggio_elimina_procedura_aggiudicazione_ben")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:ProceduraAggiudicazione", opzioni={"id": "id_procedura_aggiudicazione"})
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 */
	public function gestioneEliminaProceduraAggiudicazioneAction($id_richiesta, $id_procedura_aggiudicazione) {
		$this->get('base')->checkCsrf('token');
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->gestioneEliminaProceduraAggiudicazione($richiesta, $id_procedura_aggiudicazione);
	}

	/**
	 * @Route("/{id_richiesta}/gestione_indicatori_ben", name="gestione_monitoraggio_indicatori_ben")
	 * @PaginaInfo(titolo="Indicatori di output", sottoTitolo="pagina di gestione degli indicatori di output")
	 * @Menuitem(menuAttivo="elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 */
	public function gestioneIndicatoriAction($id_richiesta) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->gestioneIndicatori($richiesta);
	}

	/**
	 * @Route("/{id_richiesta}/gestione_indicatore_ben/{id_indicatore}", name="gestione_monitoraggio_singolo_indicatore_ben")
	 * @PaginaInfo(titolo="Indicatori di output", sottoTitolo="pagina di gestione degli indicatori di output")
	 * @Menuitem(menuAttivo="elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 */
	public function gestioneSingoloIndicatoreAction($id_richiesta, $id_indicatore) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}
		/** @var $indicatore */
		$indicatore = $this->getEm()->getRepository('RichiesteBundle:IndicatoreOutput')->find($id_indicatore);
		if (\is_null($indicatore)) {
			throw new SfingeException("Indicatore $id_indicatore non trovato");
		}
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->gestioneSingoloIndicatore($richiesta, $indicatore);
	}

	/**
	 * @Route("/{id_richiesta}/elimina_indicatore_ben/{id_indicatore}/{id_documento}", name="elimina_documento_indicatore_ben")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 * @throws \Exception
	 */
	public function eliminaDocumentoIndicatoreOutputAction($id_richiesta, $id_indicatore, $id_documento) {
		$this->get('base')->checkCsrf('token');
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}
		$indicatore = $this->getEm()->getRepository('RichiesteBundle:IndicatoreOutput')->find($id_indicatore);
		if (\is_null($indicatore)) {
			throw new SfingeException('Indicatore non trovato');
		}
		$documento = $this->getEm()->getRepository('DocumentoBundle:DocumentoFile')->find($id_documento);
		if (\is_null($documento)) {
			throw new SfingeException('Documento non trovato');
		}
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->eliminaDocumentoIndicatoreOutput($richiesta, $indicatore, $documento);
	}

	/**
	 * @Route("/{id_richiesta}/gestione_impegni_ben", name="gestione_monitoraggio_impegni_ben")
	 * @PaginaInfo(titolo="Impegni e disimpegni", sottoTitolo="pagina di gestione degli impegni e dei disimpegni")
	 * @Menuitem(menuAttivo="elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 */
	public function gestioneImpegniAction($id_richiesta) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}

		$paginaService = $this->get('pagina');
		$paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$paginaService->aggiungiElementoBreadcrumb("Impegni e disimpegni");

		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->gestioneImpegni($richiesta);
	}

	/**
	 * @Route("/{id_richiesta}/gestione_documenti_avvio", name="gestione_documenti_avvio")
	 * @PaginaInfo(titolo="Documenti avvio progetto")
	 * @Menuitem(menuAttivo="elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
	 */
	public function elencoDocumentiAvvioAction($id_richiesta) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta $id_richiesta non trovata");
		}
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->gestioneAvvioProgetto($richiesta);
	}
        
        /**
	 * @Route("/{id_richiesta}/riepilogo_accetta_contributo", name="riepilogo_accetta_contributo")
	 * @PaginaInfo(titolo="Riepilogo accettazione contributo",sottoTitolo="pagina di riepilogo della richiesta a seguito di accettazione del contributo")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_gestione_beneficiario")})
	 * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}) 
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
	 */
	public function riepilogoAccettaContributoAction($id_richiesta) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->riepilogoAccettaContributo($id_richiesta);
	}

}
