<?php

namespace AuditBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use DocumentoBundle\Entity\DocumentoFile;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use DocumentoBundle\Component\ResponseException;
use DocumentoBundle\Form\Type\DocumentoFileType;
use BaseBundle\Form\SalvaType;
use AuditBundle\Entity\DocumentoCampioneGiustificativo;

class AttuazioneController extends BaseController {

	/**
	 * @Route("/elenco_audit/{id_tipo}", name="elenco_audit_attuazione")
	 * @PaginaInfo(titolo="Audit Attuazione", sottoTitolo="elenco audit in attuazione")
	 * @Menuitem(menuAttivo = "audit")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Audit")
	 * 				})
	 */
	public function elencoAuditAction($id_tipo) {

		$tipo = $this->getEm()->getRepository('AuditBundle\Entity\TipoAudit')->findOneById($id_tipo);
		$audits = $this->getEm()->getRepository('AuditBundle\Entity\Audit')->findByTipo($tipo);

		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();

		$ruoloLettura = $this->isGranted('ROLE_AUDIT_LETTURA');

		$dati["audits"] = $audits;
		$dati["tipiAudit"] = $tipiAudit;
		$dati["menu"] = "attuazione";
		$dati['tipo_audit'] = $tipo;
		$dati['ruolo_lettura'] = $ruoloLettura;

		return $this->render("AuditBundle:Attuazione:elencoAudit.html.twig", $dati);
	}

	/**
	 * @Route("/elenco_audit_organismo/{id_audit}", name="elenco_audit_organismo_attuazione")
	 * @PaginaInfo(titolo="Audit Attuazione",sottoTitolo="elenco audit organismo")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function elencoAuditOrganismoAction($id_audit) {

		$audit = $this->getEm()->getRepository('AuditBundle\Entity\Audit')->find($id_audit);
		$auditsOrganismi = $this->getEm()->getRepository('AuditBundle\Entity\AuditOrganismo')->findByAudit($audit);

		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$ruoloLettura = $this->isGranted('ROLE_AUDIT_LETTURA');

		$dati["audits_organismi"] = $auditsOrganismi;
		$dati["tipiAudit"] = $tipiAudit;
		$dati["pianificazione"] = $audit;
		$dati["menu"] = "attuazione";
		$dati['ruolo_lettura'] = $ruoloLettura;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $audit->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco audit organismo");

		return $this->render("AuditBundle:Attuazione:elencoAuditOrganismo.html.twig", $dati);
	}

	/**
	 * @Route("/elenco_audit_operazione/{id_audit}", name="elenco_audit_operazione_attuazione")
	 * @PaginaInfo(titolo="Audit Attuazione",sottoTitolo="campioni")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function elencoAuditOperazioniAction($id_audit) {

		$audit = $this->getEm()->getRepository('AuditBundle\Entity\Audit')->find($id_audit);
		$campioniOperazioni = $this->getEm()->getRepository('AuditBundle\Entity\AuditOperazione')->findByAudit($audit);

		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$ruoloLettura = $this->isGranted('ROLE_AUDIT_LETTURA');

		$dati["campioni_operazioni"] = $campioniOperazioni;
		$dati["tipiAudit"] = $tipiAudit;
		$dati["audit"] = $audit;
		$dati["menu"] = "attuazione";
		$dati['ruolo_lettura'] = $ruoloLettura;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $audit->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco campioni");

		return $this->render("AuditBundle:Attuazione:elencoAuditOperazioni.html.twig", $dati);
	}

	/**
	 * @Route("/visualizza_audit_operazione/{id_audit}/{id_audit_operazione}/{page}", defaults={"page" = "1"}, name="visualizza_audit_operazione_attuazione")
	 * @PaginaInfo(titolo="Elenco operazioni selezionate")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function visualizzaAuditOperazioneAction($id_audit, $id_audit_operazione, $page) {
		$em = $this->getEm();
		$tipiAudit = $em->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$audit = $em->getRepository('AuditBundle\Entity\Audit')->find($id_audit);
		$operazione = $em->getRepository('AuditBundle\Entity\AuditOperazione')->find($id_audit_operazione);

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $audit->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco campioni", $this->generateUrl("elenco_audit_operazione_attuazione", array('id_audit' => $audit->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco operazioni selezionate");

		$campioniOperazioni = $em->getRepository('AuditBundle\Entity\AuditCampioneOperazione')->findByOperazione($operazione);

		$pagination = $this->container->get('knp_paginator')->paginate(
				$campioniOperazioni, $this->getCurrentRequest()->attributes->getInt('page', $page), 10
		);

		$ruoloLettura = $this->isGranted('ROLE_AUDIT_LETTURA');

		$dati["campioni_operazione"] = $pagination;
		$dati["audit"] = $audit;
		$dati["tipiAudit"] = $tipiAudit;
		$dati["menu"] = "attuazione";
		$dati['ruolo_lettura'] = $ruoloLettura;

		return $this->render("AuditBundle:Attuazione:auditOperazione.html.twig", $dati);
	}

	/**
	 * @Route("/documenti_campione_operazione/{id_audit_campione_operazione}", name="documenti_campione_operazione")
	 * @PaginaInfo(titolo="Checklist Operazione", sottoTitolo="")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function documentiCampioneOperazioneAction($id_audit_campione_operazione) {
		$em = $this->getEm();
		$tipiAudit = $em->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$campioneOperazione = $em->getRepository('AuditBundle\Entity\AuditCampioneOperazione')->find($id_audit_campione_operazione);
		$auditOperazione = $campioneOperazione->getAuditOperazione();
		$audit = $auditOperazione->getAudit();

		$request = $this->getCurrentRequest();

		$documento_campione = new \AuditBundle\Entity\DocumentoCampioneOperazione();
		$documento_file = new DocumentoFile();

		$listaTipi = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByTipologia(array('audit_altro', 'audit_report_contraddittorio', 'audit_report_post_contraddittorio', 'audit_fascicolo_operazione'));


		$opzioni_form["lista_tipi"] = $listaTipi;
		$form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
		$form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Salva'));

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {

					$this->container->get("documenti")->carica($documento_file, 0);

					$documento_campione->setDocumentoFile($documento_file);
					$documento_campione->setAuditCampioneOperazione($campioneOperazione);
					$em->persist($documento_campione);

					$em->flush();
					return $this->redirect($this->generateUrl('documenti_campione_operazione', array('id_audit_campione_operazione' => $id_audit_campione_operazione)));
				} catch (ResponseException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		$ruoloLettura = $this->isGranted('ROLE_AUDIT_LETTURA');

		$dati["menu"] = "attuazione";
		$dati["campioneOperazione"] = $campioneOperazione;
		$dati["tipiAudit"] = $tipiAudit;
		$dati["form"] = $form->createView();
		$dati['ruolo_lettura'] = $ruoloLettura;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $audit->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco campioni", $this->generateUrl("elenco_audit_operazione_attuazione", array('id_audit' => $audit->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco operazioni selezionate", $this->generateUrl("visualizza_audit_operazione_attuazione", array('id_audit' => $audit->getId(), 'id_audit_operazione' => $auditOperazione->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Checklist operazione");

		return $this->render("AuditBundle:Attuazione:documentiOperazione.html.twig", $dati);
	}

	/**
	 * @Route("/controllo_operazione/{id_audit_campione_operazione}", name="controllo_operazione_attuazione")
	 * @PaginaInfo(titolo="Controllo Operazione", sottoTitolo="")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function controlloOperazioneAction($id_audit_campione_operazione) {

		$em = $this->getEm();
		$tipiAudit = $em->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$campioneOperazione = $em->getRepository('AuditBundle\Entity\AuditCampioneOperazione')->find($id_audit_campione_operazione);
		$auditOperazione = $campioneOperazione->getAuditOperazione();
		$audit = $auditOperazione->getAudit();

		if (is_null($campioneOperazione->getSpesaIrregolarePreContraddittorio())) {
			$campioneOperazione->setSpesaIrregolarePreContraddittorio(0);
		}

		if (is_null($campioneOperazione->getSpesaIrregolarePostContraddittorio())) {
			$campioneOperazione->setSpesaIrregolarePostContraddittorio(0);
		}
		
		if (is_null($campioneOperazione->getContributoIrregolarePreContraddittorio())) {
			$campioneOperazione->setContributoIrregolarePreContraddittorio(0);
		}

		if (is_null($campioneOperazione->getContributoIrregolarePostContraddittorio())) {
			$campioneOperazione->setContributoIrregolarePostContraddittorio(0);
		}
		
		if (is_null($campioneOperazione->getSpesaCuscinetto())) {
			$campioneOperazione->setSpesaCuscinetto(0);
		}

		if (is_null($campioneOperazione->getContributoPubblicoCuscinetto())) {
			$campioneOperazione->setContributoPubblicoCuscinetto(0);
		}

		$campioneOperazione->percentuale_giustificativi = $campioneOperazione->calcolaPercentualeGiustificativiControllati();

		$form = $this->createForm('AuditBundle\Form\Attuazione\ControlloOperazioneType', $campioneOperazione, array('url_indietro' => $this->generateUrl('visualizza_audit_operazione_attuazione', array("id_audit" => $audit->getId(), "id_audit_operazione" => $auditOperazione->getid()))));
		$form->get('spesa_sottoposta_audit')->setData($campioneOperazione->calcolaSottopostaAuditCampioni());
		
		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {

			$form->handleRequest($request);

			if ($form->isValid()) {
				try {
					$em->flush();
					$this->addFlash('success', "Controllo operazione salvato correttamente");
					return $this->redirect($this->generateUrl('visualizza_audit_operazione_attuazione', array("id_audit" => $audit->getId(), "id_audit_operazione" => $auditOperazione->getid())));
				} catch (\Exception $e) {
					$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
					$this->get("logger")->error($e->getMessage());
				}
			}
		}
		$ruoloLettura = $this->isGranted('ROLE_AUDIT_LETTURA');

		$dati["menu"] = "attuazione";
		$dati["campioneOperazione"] = $campioneOperazione;
		$dati["tipiAudit"] = $tipiAudit;
		$dati["form"] = $form->createView();
		$dati["ruolo_lettura"] = $ruoloLettura;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $audit->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco campioni", $this->generateUrl("elenco_audit_operazione_attuazione", array('id_audit' => $audit->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco operazioni selezionate", $this->generateUrl("visualizza_audit_operazione_attuazione", array('id_audit' => $audit->getId(), 'id_audit_operazione' => $auditOperazione->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Controllo operazione");

		return $this->render("AuditBundle:Attuazione:controlloOperazione.html.twig", $dati);
	}

	/**
	 * @Route("/genera_pdf_report/{id_audit_campione_operazione}", name="genera_pdf_report_operazione_attuazione")
	 */
	public function generaPdfReportOperazioneAction($id_audit_campione_operazione) {
		$campioneOperazione = $this->getEm()->getRepository('AuditBundle\Entity\AuditCampioneOperazione')->find($id_audit_campione_operazione);
		$richiesta = $campioneOperazione->getRichiesta();
		$dati["operazione"] = $campioneOperazione;
		$dati["facsimile"] = false;
		$dati["richiesta"] = $richiesta;

		$avanzamento = $this->container->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->calcolaAvanzamentoPianoCosti($richiesta, null, null);
		$dati["avanzamento"] = $avanzamento;

		$pdf = $this->container->get("pdf");
		$pdf->load("AuditBundle:Attuazione:pdfReportOperazione.html.twig", $dati);


		//return $this->render("AuditBundle:Attuazione:pdfReportOperazione.html.twig", $dati);

		$date = new \DateTime();
		$data = $date->format('d-m-Y');
		return $pdf->download('Report Operazione ' . $campioneOperazione->getId() . " " . $data);
	}

	/**
	 * @Route("/genera_pdf_report_html/{id_audit_campione_operazione}", name="genera_pdf_report_operazione_attuazione_html")
	 */
	public function generaPdfReportOperazioneHtmlAction($id_audit_campione_operazione) {
		$campioneOperazione = $this->getEm()->getRepository('AuditBundle\Entity\AuditCampioneOperazione')->find($id_audit_campione_operazione);
		$richiesta = $campioneOperazione->getRichiesta();
		$dati["operazione"] = $campioneOperazione;
		$dati["facsimile"] = false;
		$dati["richiesta"] = $richiesta;

		$avanzamento = $this->container->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->calcolaAvanzamentoPianoCosti($richiesta, null, null);
		$dati["avanzamento"] = $avanzamento;

		$pdf = $this->container->get("pdf");
		$pdf->load("AuditBundle:Attuazione:pdfReportOperazione.html.twig", $dati);


		return $this->render("AuditBundle:Attuazione:pdfReportOperazione.html.twig", $dati);
	}

	/**
	 * @Route("/associa_giustificativi_campione_pulisci/{id_audit_campione_operazione}", name="associa_giustificativi_campione_attuazione_pulisci")
	 */
	public function elencoAttuazionePulisciAction($id_audit_campione_operazione) {
		$this->get("ricerca")->pulisci(new \AuditBundle\Form\Entity\RicercaUniversoGiustificativi());
		return $this->redirectToRoute("associa_giustificativi_campione_attuazione", array("id_audit_campione_operazione" => $id_audit_campione_operazione));
	}

	/**
	 * @Route("/dettaglio_giustificativo/{id_campione_giustificativo}", name="dettaglio_giustificativo_audit")
	 * @PaginaInfo(titolo="Dettaglio giustificativo", sottoTitolo="")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function dettaglioGiustificativoAction($id_campione_giustificativo) {

		$em = $this->getEm();
		$tipiAudit = $em->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$campioneGiustificativo = $em->getRepository('AuditBundle\Entity\AuditCampioneGiustificativo')->find($id_campione_giustificativo);
		$operazione = $campioneGiustificativo->getAuditCampioneOperazione()->getAuditOperazione();
		$audit = $operazione->getAudit();

		$form = $this->createForm('AuditBundle\Form\Attuazione\DettaglioGiustificativoType', $campioneGiustificativo, array('url_indietro' => $this->generateUrl('controllo_operazione_attuazione', array('id_audit_campione_operazione' => $campioneGiustificativo->getAuditCampioneOperazione()->getId()))));

		$tipologiaDocumento = $em->getRepository('DocumentoBundle:TipologiaDocumento')->findOneBy([
			'codice' => 'AUDIT_CAMPIONE_GIUSTIFICATIVO',
		]);
		$opzioni_form["lista_tipi"] = array($tipologiaDocumento);
		$nuovoDocumentoCampione = new DocumentoCampioneGiustificativo();
		$nuovoDocumento = new DocumentoFile();
		
		$nuovoDocumento->setTipologiaDocumento($tipologiaDocumento);
		$nuovoDocumentoCampione->setDocumentoFile($nuovoDocumento);

		$formDocumento = $this->createForm(DocumentoFileType::class, $nuovoDocumento, $opzioni_form);

		$formDocumento->add('submit', SalvaType::class, [
			'label_salva' => 'Carica',
			'label' => false,
		]);

		$request = $this->getCurrentRequest();

		$formDocumento->handleRequest($request);
		if ($formDocumento->isSubmitted() && $formDocumento->isValid()) {
			try {
				$this->container->get("documenti")->carica($nuovoDocumento);
				$nuovoDocumentoCampione->setAuditCampione($campioneGiustificativo);
				$em->persist($nuovoDocumentoCampione);
				$em->flush();
			} catch (\Exception $e) {
				$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
				$this->get("logger")->error($e->getMessage());
			}
		}

		$form->handleRequest($request);
		if ($form->isSubmitted()) {
			if ($campioneGiustificativo->getConforme() == false && $campioneGiustificativo->getNaturaIrregolarita() == 'Non applicabile') {
				$form->addError(new \Symfony\Component\Form\FormError('In caso di conforme uguale a "No" la natura irregolarità non può essere "Non applicabile"'));
			}

			if ($form->isValid()) {
				try {
					$em->flush();
					$this->addFlash('success', "Controllo operazione salvato correttamente");
					return $this->redirect($this->generateUrl('controllo_operazione_attuazione', array('id_audit_campione_operazione' => $campioneGiustificativo->getAuditCampioneOperazione()->getId())));
				} catch (\Exception $e) {
					$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
					$this->get("logger")->error($e->getMessage());
				}
			}
		}
		$ruoloLettura = $this->isGranted('ROLE_AUDIT_LETTURA');

		$dati["menu"] = "attuazione";
		$dati["campioneGiustificativo"] = $campioneGiustificativo;
		$dati["tipiAudit"] = $tipiAudit;
		$dati["form"] = $form->createView();
		$dati["formDocumento"] = $formDocumento->createView();
		$dati["ruolo_lettura"] = $ruoloLettura;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $audit->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco campioni", $this->generateUrl("elenco_audit_operazione_attuazione", array('id_audit' => $audit->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco operazioni selezionate", $this->generateUrl("visualizza_audit_operazione_attuazione", array('id_audit' => $audit->getId(), 'id_audit_operazione' => $operazione->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Controllo operazione", $this->generateUrl("controllo_operazione_attuazione", array('id_audit_campione_operazione' => $campioneGiustificativo->getAuditCampioneOperazione()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio giustificativo");

		return $this->render("AuditBundle:Attuazione:dettaglioGiustificativo.html.twig", $dati);
	}

	/**
	 * @Route("/associa_giustificativi_campione/{id_audit_campione_operazione}/{sort}/{direction}/{page}", defaults={"sort" = "a.id", "direction" = "asc", "page" = "1"}, name="associa_giustificativi_campione_attuazione")
	 * @PaginaInfo(titolo="Associa giustificativi")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function associaGiustificativoCampioneAction($id_audit_campione_operazione) {
		$em = $this->getEm();
		$campioneOperazione = $em->getRepository('AuditBundle\Entity\AuditCampioneOperazione')->find($id_audit_campione_operazione);
		$auditOperazione = $campioneOperazione->getAuditOperazione();
		$audit = $auditOperazione->getAudit();

		$datiRicerca = new \AuditBundle\Form\Entity\RicercaUniversoGiustificativi();
		$datiRicerca->setRichiesta($campioneOperazione->getRichiesta());

		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		$options = array();
		$options["url_indietro"] = $this->generateUrl("visualizza_audit_operazione_attuazione", array("id_audit" => $audit->getId(), "id_audit_operazione" => $auditOperazione->getId()));

		$campioni_indicizzati = array();
		if (!is_null($campioneOperazione->getCampioni())) {
			foreach ($campioneOperazione->getCampioni() as $campione) {
				$campioni_indicizzati[$campione->getGiustificativo()->getId()] = $campione;
			}
		}

		foreach ($risultato["risultato"] as $giustificativo) {
			$audit_campione_operazione = new \AuditBundle\Entity\AuditCampioneGiustificativo();
			$audit_campione_operazione->setGiustificativo($giustificativo);

			if (isset($campioni_indicizzati[$giustificativo->getId()])) {
				$audit_campione_operazione->setSelezionato(true);
			}

			$campioneOperazione->addCampioneEsteso($audit_campione_operazione);
		}

		$form = $this->createForm("AuditBundle\Form\Attuazione\AssociazioneGiustificativiCampioneType", $campioneOperazione, $options);

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {

				foreach ($form->get("campioni_estesi")->all() as $form_campione) {
					$campione_esteso = $form_campione->getData();

					if (isset($campioni_indicizzati[$campione_esteso->getGiustificativo()->getId()])) {
						$campione_nuovo = $campioni_indicizzati[$campione_esteso->getGiustificativo()->getId()];
					} else {
						$campione_nuovo = new \AuditBundle\Entity\AuditCampioneGiustificativo();
					}

					if ($campione_esteso->getSelezionato()) {
						$campione_nuovo->setGiustificativo($campione_esteso->getGiustificativo());
						$campioneOperazione->addCampione($campione_nuovo);
					} else {
						if (!is_null($campione_nuovo->getId())) {
							$em->remove($campione_nuovo);
						}
					}
				}

				$em = $this->getEm();
				try {
					$em->flush();
					$this->addFlash("success", "L'associazione dei giustificativi è stata correttamente salvata");
				} catch (\Exception $e) {
					$this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza. " . $e->getMessage());
				}
			}
		}

		$dati = array('risultati' => $risultato["risultato"], "formRicerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"], "id_audit_campione_operazione" => $id_audit_campione_operazione);
		$dati["form"] = $form->createView();
		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$dati["tipiAudit"] = $tipiAudit;
		$dati["menu"] = "attuazione";
		$dati["risultati"] = $risultato["risultato"];
		$dati["formRicerca"] = $risultato["form_ricerca"];
		$dati["filtro_attivo"] = $risultato["filtro_attivo"];
		$dati["ruolo_lettura"] = 0;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $audit->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco campioni", $this->generateUrl("elenco_audit_operazione_attuazione", array('id_audit' => $audit->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco operazioni selezionate", $this->generateUrl("visualizza_audit_operazione_attuazione", array('id_audit' => $audit->getId(), 'id_audit_operazione' => $auditOperazione->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Associa Giustificativi");

		return $this->render("AuditBundle:Attuazione:associaCampioneGiustificativi.html.twig", $dati);
	}

	/**
	 * @Route("/valutazione_audit_organismo/{id_audit_organismo}", name="valutazione_audit_organismo")
	 * @PaginaInfo(titolo="Valutazione audit organismo", sottoTitolo="")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function valutazioneAuditOrganismoAction($id_audit_organismo) {

		$em = $this->getEm();
		$audit_organismo = $em->getRepository('AuditBundle\Entity\AuditOrganismo')->find($id_audit_organismo);

		$documento_attuazione_organismo = new \AuditBundle\Entity\DocumentoAttuazioneOrganismo();
		$documento_file = new DocumentoFile();
		$documento_attuazione_organismo->setAuditOrganismo($audit_organismo);
		$documento_attuazione_organismo->setDocumentoFile($documento_file);

		$listaTipi = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findby(array("codice" => array("AUDIT_RAPPORTO_PROVVISORIO", "AUDIT_RAPPORTO_DEFINITIVO", "AUDIT_COMUNICAZIONE_INTERVISTA", "AUDIT_ALTRO")));

		$opzioni_form["lista_tipi"] = $listaTipi;
		$form_doc = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
		$form_doc->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

		$form = $this->createForm('AuditBundle\Form\Attuazione\ValutazioneAuditOrganismoType', $audit_organismo, array('url_indietro' => $this->generateUrl('elenco_audit_organismo_attuazione', array("id_audit" => $audit_organismo->getAudit()->getId()))));

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {

			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				try {
					$em->flush();
					$this->addFlash('success', "Valutazione salvata correttamente");
					return $this->redirect($this->generateUrl('elenco_audit_organismo_attuazione', array("id_audit" => $audit_organismo->getAudit()->getId())));
				} catch (\Exception $e) {
					$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
					$this->get("logger")->error($e->getMessage());
				}
			}

			$form_doc->handleRequest($request);
			if ($form_doc->isSubmitted() && $form_doc->isValid()) {
				try {

					$this->container->get("documenti")->carica($documento_file, 0);
					$em->persist($documento_attuazione_organismo);

					$em->flush($documento_attuazione_organismo);
					$this->addFlash('success', "Documento salvato correttamente");
					return $this->redirect($this->generateUrl('valutazione_audit_organismo', array("id_audit_organismo" => $audit_organismo->getId())));
				} catch (ResponseException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		// $dati["id_pianificazione"] = $audit_organismo->getAudit()->getId();
		// $dati["documenti"] = $audit_organismo->getDocumentiPianificazioneOrganismo();
		$dati["menu"] = "attuazione";
		$dati["audit_organismo"] = $audit_organismo;
		$dati["form"] = $form->createView();
		$dati["form_doc"] = $form_doc->createView();

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $audit_organismo->getAudit()->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco audit organismo", $this->generateUrl('elenco_audit_organismo_attuazione', array('id_audit' => $audit_organismo->getAudit()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Valutazione audit organismo");

		return $this->render("AuditBundle:Attuazione:valutazioneAuditOrganismo.html.twig", $dati);
	}

	/**
	 * @Route("/elimina_documento_organismo/{id_documento_organismo}", name="elimina_documento_organismo_attuazione")
	 */
	public function eliminaDocumentoOrganismoAction($id_documento_organismo) {
		$this->get('base')->checkCsrf('token');
		$em = $this->getEm();
		$documento = $em->getRepository("AuditBundle\Entity\DocumentoAttuazioneOrganismo")->find($id_documento_organismo);

		try {
			$em->remove($documento->getDocumentoFile());
			$em->remove($documento);
			$em->flush();

			$this->addFlash('success', "Documento eliminato correttamente");
		} catch (ResponseException $e) {
			$this->get('monolog.logger.schema31')->error($e->getMessage());
			$this->addFlash('error', "Errore nell'eliminazione del documento. Riprovare o contattare l'assistenza");
		}

		return $this->redirect($this->generateUrl("valutazione_audit_organismo", array("id_audit_organismo" => $documento->getAuditOrganismo()->getId())));
	}

	/**
	 * @Route("/requisiti_audit_organismo/{id_audit_organismo}", name="requisiti_audit_organismo_attuazione")
	 * @PaginaInfo(titolo="Requisiti audit organismo", sottoTitolo="Elenco dei requisiti associati all'audit dell'organismo")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function requisitiAuditOrganismoAction($id_audit_organismo) {
		$audit_organismo = $this->getEm()->getRepository('AuditBundle\Entity\AuditOrganismo')->find($id_audit_organismo);

		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$ruoloLettura = $this->isGranted('ROLE_AUDIT_LETTURA');

		$dati["tipiAudit"] = $tipiAudit;
		$dati["audit_organismo"] = $audit_organismo;
		$dati["menu"] = "attuazione";
		$dati["ruolo_lettura"] = $ruoloLettura;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $audit_organismo->getAudit()->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco audit organismo", $this->generateUrl('elenco_audit_organismo_attuazione', array('id_audit' => $audit_organismo->getAudit()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco Requisiti");

		return $this->render("AuditBundle:Attuazione:requisitiAuditOrganismo.html.twig", $dati);
	}

	/**
	 * @Route("/conformita_audit_requisito/{id_audit_requisito}", name="conformita_audit_requisito_attuazione")
	 * @PaginaInfo(titolo="Test di conformità audit requisito", sottoTitolo="")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function conformitaAuditRequisitoAction($id_audit_requisito) {
		$em = $this->getEm();
		$auditRequisito = $em->getRepository('AuditBundle\Entity\AuditRequisito')->find($id_audit_requisito);

		$documento_attuazione_requisito = new \AuditBundle\Entity\DocumentoAttuazioneRequisito();
		$documento = new \DocumentoBundle\Entity\DocumentoFile();
		$tipologia = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneByCodice("AUDIT");
		$documento->setTipologiaDocumento($tipologia);

		$documento_attuazione_requisito->setDocumentoFile($documento);
		$documento_attuazione_requisito->setAuditRequisito($auditRequisito);

		$audit_organismo = $auditRequisito->getAuditOrganismo();

		$auditRequisito->setDocumento($documento);

		$form_doc = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileSimpleType', $documento);
		$form_doc->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

		$form = $this->createForm('AuditBundle\Form\Attuazione\TestConformitaType', $auditRequisito, array('url_indietro' => $this->generateUrl('requisiti_audit_organismo_attuazione', array("id_audit_organismo" => $audit_organismo->getId()))));

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {

			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				try {
					$em->flush();
					$this->addFlash('success', "Valutazione salvata correttamente");
					return $this->redirect($this->generateUrl('conformita_audit_requisito_attuazione', array("id_audit_requisito" => $auditRequisito->getId())));
				} catch (\Exception $e) {
					$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
					$this->get("logger")->error($e->getMessage());
				}
			}

			$form_doc->handleRequest($request);
			if ($form_doc->isSubmitted() && $form_doc->isValid()) {
				try {

					$this->container->get("documenti")->carica($documento, 0);
					$em->persist($documento_attuazione_requisito);

					$em->flush($documento_attuazione_requisito);
					$this->addFlash('success', "Documento salvato correttamente");
					return $this->redirect($this->generateUrl('conformita_audit_requisito_attuazione', array("id_audit_requisito" => $auditRequisito->getId())));
				} catch (ResponseException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		$dati["id_audit_organismo"] = $auditRequisito->getAuditOrganismo()->getId();
		$dati["documenti"] = $auditRequisito->getDocumentiAttuazioneRequisito();
		$dati["menu"] = "attuazione";
		$dati["form"] = $form->createView();
		$dati["form_doc"] = $form_doc->createView();

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $audit_organismo->getAudit()->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco audit organismo", $this->generateUrl('elenco_audit_organismo_attuazione', array('id_audit' => $audit_organismo->getAudit()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco Requisiti", $this->generateUrl('requisiti_audit_organismo_attuazione', array('id_audit_organismo' => $auditRequisito->getAuditOrganismo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Test conformità");

		return $this->render("AuditBundle:Attuazione:testConformita.html.twig", $dati);
	}

	/**
	 * @Route("/elimina_documento_requisito/{id_documento_requisito}", name="elimina_documento_requisito")
	 */
	public function eliminaDocumentoRequisito($id_documento_requisito) {

		$em = $this->getEm();
		$documento = $em->getRepository("AuditBundle\Entity\DocumentoAttuazioneRequisito")->find($id_documento_requisito);

		$id_audit_requisito = $documento->getAuditRequisito()->getId();

		try {
			$em->remove($documento->getDocumentoFile());
			$em->remove($documento);
			$em->flush();
			return $this->addSuccessRedirect("Documento eliminato correttamente", "conformita_audit_requisito_attuazione", array("id_audit_requisito" => $id_audit_requisito));
		} catch (ResponseException $e) {
			$this->addFlash('error', $e->getMessage());
		}
	}

	/**
	 * @Route("/campioni_audit_requisito/{id_audit_requisito}", name="campioni_audit_requisito_attuazione")
	 * @PaginaInfo(titolo="Campioni audit requisito", sottoTitolo="Elenco delle operazioni campionate per l'audit del requisito")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function campioniAuditRequisitoAction($id_audit_requisito) {

		$auditRequisito = $this->getEm()->getRepository('AuditBundle\Entity\AuditRequisito')->find($id_audit_requisito);

		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$dati["tipiAudit"] = $tipiAudit;
		$dati["audit_requisito"] = $auditRequisito;
		$dati["campioni"] = $auditRequisito->getCampioni();
		$dati["menu"] = "attuazione";
        $ruoloLettura = $this->isGranted('ROLE_AUDIT_LETTURA');
		$dati['ruolo_lettura'] = $ruoloLettura;

		$auditOrganismo = $auditRequisito->getAuditOrganismo();

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $auditOrganismo->getAudit()->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco audit organismo", $this->generateUrl('elenco_audit_organismo_attuazione', array('id_audit' => $auditOrganismo->getAudit()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco Requisiti", $this->generateUrl('requisiti_audit_organismo_attuazione', array('id_audit_organismo' => $auditRequisito->getAuditOrganismo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco operazioni");

		return $this->render("AuditBundle:Attuazione:campioniAuditRequisito.html.twig", $dati);
	}

	/**
	 * @Route("/valutazione_audit_campione/{id_audit_campione}", name="valutazione_audit_campione_attuazione")
	 * @PaginaInfo(titolo="Valutazione audit campione", sottoTitolo="")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function valutazioneAuditCampioneAction($id_audit_campione) {

		$em = $this->getEm();
		$auditCampione = $this->getEm()->getRepository('AuditBundle\Entity\AuditCampione')->find($id_audit_campione);
		if (is_null($auditCampione->getDecurtazioneFinanziaria())) {
			$auditCampione->setDecurtazioneFinanziaria(0);
		}
		if (is_null($auditCampione->getImportoIrregolarePreContr())) {
			$auditCampione->setImportoIrregolarePreContr(0);
		}
		if (is_null($auditCampione->getSpesaPublIrregolarePreContr())) {
			$auditCampione->setSpesaPublIrregolarePreContr(0);
		}
		if (is_null($auditCampione->getImportoIrregolare())) {
			$auditCampione->setImportoIrregolare(0);
		}
		if (is_null($auditCampione->getSpesaPubIrregolare())) {
			$auditCampione->setSpesaPubIrregolare(0);
		}
		$auditRequisito = $auditCampione->getAuditRequisito();

		$documento_campione_requisito = new \AuditBundle\Entity\DocumentoCampioneRequisito();
		$documento = new \DocumentoBundle\Entity\DocumentoFile();
		$tipologia = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneByCodice("AUDIT_CAMPIONE");
		$documento->setTipologiaDocumento($tipologia);
		$documento_campione_requisito->setDocumentoFile($documento);
		$documento_campione_requisito->setAuditCampione($auditCampione);

		$form = $this->createForm('AuditBundle\Form\Attuazione\ValutazioneCampioneType', $auditCampione, array('url_indietro' => $this->generateUrl('campioni_audit_requisito_attuazione', array("id_audit_requisito" => $auditRequisito->getId()))));

		$form_doc = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileSimpleType', $documento);
		$form_doc->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {

			$form->handleRequest($request);

			if ($auditCampione->getImportoIrregolarePreContr() != 0 && $auditCampione->getEsito() == true) {
				$form->get('importo_irregolare_pre_contr')->addError(new \Symfony\Component\Form\FormError("Se giudizione posito l'importo irregolare pre-contraddittorio deve essere 0"));
			}

			if ($auditCampione->getSpesaPublIrregolarePreContr() != 0 && $auditCampione->getEsito() == true) {
				$form->get('spesa_publ_irregolare_pre_contr')->addError(new \Symfony\Component\Form\FormError("Se giudizione posito la spesa pubblica irregolare pre-contraddittori deve essere 0"));
			}
			if (($auditCampione->getSpesaPublIrregolarePreContr() != 0 && $auditCampione->getEsito() == false) && ($auditCampione->getSpesaPublIrregolarePreContr() > $auditCampione->getImportoIrregolarePreContr())) {
				$form->get('spesa_publ_irregolare_pre_contr')->addError(new \Symfony\Component\Form\FormError("La spesa pubblica irregolare pre-contraddittorio deve essere minore dell'importo irregolare pre-contraddittorio"));
			}

			if ($auditCampione->getImportoIrregolare() != 0 && $auditCampione->getEsito() == true) {
				$form->get('importo_irregolare')->addError(new \Symfony\Component\Form\FormError("Se giudizione posito l'importo irregolare deve essere 0"));
			}

			if ($auditCampione->getSpesaPubIrregolare() != 0 && $auditCampione->getEsito() == true) {
				$form->get('spesa_pub_irregolare')->addError(new \Symfony\Component\Form\FormError("Se giudizione posito la spesa pubblica deve essere 0"));
			}

			if (($auditCampione->getSpesaPubIrregolare() != 0 && $auditCampione->getEsito() == false) && ($auditCampione->getSpesaPubIrregolare() > $auditCampione->getImportoIrregolare())) {
				$form->get('spesa_pub_irregolare')->addError(new \Symfony\Component\Form\FormError("La spesa pubblica irregolare deve essere minore dell'importo irregolare"));
			}

			if ($auditCampione->getDecurtazioneFinanziaria() != 0 && $auditCampione->getEsito() == true) {
				$form->get('decurtazione_finanziaria')->addError(new \Symfony\Component\Form\FormError("Se giudizione posito la proposta decurtazione deve essere 0"));
			}

			if (($auditCampione->getDecurtazioneFinanziaria() != 0 && $auditCampione->getEsito() == false) && ($auditCampione->getDecurtazioneFinanziaria() > $auditCampione->getSpesaPubIrregolare())) {
				$form->get('decurtazione_finanziaria')->addError(new \Symfony\Component\Form\FormError("La proposta decurtzione finanziaria  deve essere minore della spesa pubblica irregolare"));
			}

			if ($form->isSubmitted() && $form->isValid()) {
				try {
					$em->flush();
					$this->addFlash('success', "Valutazione salvata correttamente");
					return $this->redirect($this->generateUrl('valutazione_audit_campione_attuazione', array("id_audit_campione" => $auditCampione->getId())));
				} catch (\Exception $e) {
					$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
					$this->get("logger")->error($e->getMessage());
				}
			}

			$form_doc->handleRequest($request);
			if ($form_doc->isSubmitted() && $form_doc->isValid()) {
				try {

					$this->container->get("documenti")->carica($documento, 0);
					$em->persist($documento_campione_requisito);

					$em->flush($documento_campione_requisito);
					$this->addFlash('success', "Documento salvato correttamente");
					return $this->redirect($this->generateUrl('valutazione_audit_campione_attuazione', array("id_audit_campione" => $auditCampione->getId())));
				} catch (ResponseException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		$dati["id_audit_organismo"] = $auditRequisito->getAuditOrganismo()->getId();
		$dati["form_doc"] = $form_doc->createView();
		$dati["documenti"] = $auditRequisito->getDocumentiAttuazioneRequisito();
		$dati["menu"] = "attuazione";
		$dati["form"] = $form->createView();
		$dati["campione"] = $auditCampione;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $auditRequisito->getAuditOrganismo()->getAudit()->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco audit organismo", $this->generateUrl('elenco_audit_organismo_attuazione', array('id_audit' => $auditRequisito->getAuditOrganismo()->getAudit()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco Requisiti", $this->generateUrl('requisiti_audit_organismo_attuazione', array('id_audit_organismo' => $auditRequisito->getAuditOrganismo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco operazioni", $this->generateUrl('campioni_audit_requisito_attuazione', array('id_audit_requisito' => $auditRequisito->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Test conformità");

		return $this->render("AuditBundle:Attuazione:valutazioneAuditCampione.html.twig", $dati);
	}

	/**
	 * @Route("/elimina_documento_campione_requisito/{id_documento_campione}", name="elimina_documento_campione_requisito")
	 */
	public function eliminaDocumentoCampioneRequisito($id_documento_campione) {

		$em = $this->getEm();
		$documento = $em->getRepository("AuditBundle\Entity\DocumentoCampioneRequisito")->find($id_documento_campione);

		$id_audit_campione = $documento->getAuditCampione()->getId();

		try {
			$em->remove($documento->getDocumentoFile());
			$em->remove($documento);
			$em->flush();
			return $this->addSuccessRedirect("Documento eliminato correttamente", "valutazione_audit_campione_attuazione", array("id_audit_campione" => $id_audit_campione));
		} catch (ResponseException $e) {
			$this->addFlash('error', $e->getMessage());
		}
	}

	/**
	 * @Route("/elimina_documento_campione_operazione/{id_doc_campione_operazione}", name="elimina_documento_campione_operazione")
	 */
	public function eliminaDocumentoOperazioneAction($id_doc_campione_operazione) {

		$em = $this->getEm();
		$documentoCampioneOperazione = $em->getRepository("AuditBundle\Entity\DocumentoCampioneOperazione")->findOneById($id_doc_campione_operazione);
		$documentoFile = $documentoCampioneOperazione->getDocumentoFile();
		$campioneOperazione = $documentoCampioneOperazione->getAuditCampioneOperazione();
		try {
			$em->remove($documentoCampioneOperazione);
			$em->remove($documentoFile);
			$em->flush();
			return $this->addSuccessRedirect("Documento eliminato correttamente", "documenti_campione_operazione", array("id_audit_campione_operazione" => $campioneOperazione->getId()));
		} catch (ResponseException $e) {
			$this->addFlash('error', $e->getMessage());
		}
	}

	/**
	 * @Route("/scarica_report_revoche_inviate", name="audit_scarica_report_revoche_inviate")
	 * @return StreamedResponse
	 */
	public function scaricaReportRevocheInviate() {
		\ini_set('memory_limit', '512M');
		$gestore = $this->get('audit_esportazioni');/** @var AuditBundle\Service\GestoreEsportazioni */
		$excelWriter = $gestore->getReportRevocheInviate();

		$response = new StreamedResponse(function () use ($excelWriter) {
			$excelWriter->save('php://output');
		}, \Symfony\Component\HttpFoundation\Response::HTTP_OK, array(
			'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
			'Pragma' => 'public',
			'Cache-Control' => 'maxage=1')
		);
		$disposition = $response->headers->makeDisposition(
				ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'report revoche.xls'
		);

		$response->headers->set('Content-Disposition', $disposition);
		return $response;
	}

	/**
	 * @Route("/scarica_report_revoche_con_recupero", name="audit_scarica_report_revoche_con_recupero")
	 * @return StreamedResponse
	 */
	public function scaricaReportRevocheConRecupero() {
		\ini_set('memory_limit', '512M');
		$gestore = $this->get('audit_esportazioni');/** @var AuditBundle\Service\GestoreEsportazioni */
		$excelWriter = $gestore->getReportRevocheConRecupero();

		$response = new StreamedResponse(function () use ($excelWriter) {
			$excelWriter->save('php://output');
		}, \Symfony\Component\HttpFoundation\Response::HTTP_OK, array(
			'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
			'Pragma' => 'public',
			'Cache-Control' => 'maxage=1')
		);
		$disposition = $response->headers->makeDisposition(
				ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'report recuperi.xls'
		);

		$response->headers->set('Content-Disposition', $disposition);
		return $response;
	}

	/**
	 * @Route("/scarica_report_pagamenti_certificati", name="audit_scarica_report_pagamenti_certificati")
	 * @return StreamedResponse
	 */
	public function scaricaReportPagamentiCertificati() {
		\ini_set('memory_limit', '512M');
		$gestore = $this->get('audit_esportazioni');/** @var AuditBundle\Service\GestoreEsportazioni */
		$excelWriter = $gestore->getReportPagamentiCertificati();

		$response = new StreamedResponse(function () use ($excelWriter) {
			$excelWriter->save('php://output');
		}, \Symfony\Component\HttpFoundation\Response::HTTP_OK, array(
			'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
			'Pragma' => 'public',
			'Cache-Control' => 'maxage=1')
		);
		$disposition = $response->headers->makeDisposition(
				ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'report pagamenti.xls'
		);

		$response->headers->set('Content-Disposition', $disposition);
		return $response;
	}

	/**
	 * @Route("/allegati_audit_conti/{id_audit}", name="allegati_audit_conti")
	 * @PaginaInfo(titolo="Audit Attuazione", sottoTitolo="documenti allegati")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function documentiAuditContiAction($id_audit) {
		$em = $this->getEm();
		$audit = $em->getRepository('AuditBundle\Entity\Audit')->find($id_audit);

		if (count($audit->getAuditConti()) == 0) {
			$audit_conti = new \AuditBundle\Entity\AuditConti();
			$audit_conti->setAudit($audit);
			try {
				$em->persist($audit_conti);
				$em->flush();
			} catch (ResponseException $e) {
				$this->addFlash('error', $e->getMessage());
			}
		} else {
			$audit_contis = $audit->getAuditConti();
			$audit_conti = $audit_contis[0];
		}

		$tipo = $audit->getTipo();

		$request = $this->getCurrentRequest();

		$documento = new \AuditBundle\Entity\DocumentoAuditConti();
		$documento_file = new \DocumentoBundle\Entity\DocumentoFile();

		$listaTipi = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByTipologia(array('audit_conti'));

		$opzioni_form["lista_tipi"] = $listaTipi;
		$form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
		$form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {

					$this->container->get("documenti")->carica($documento_file, 0);

					$documento->setDocumentoFile($documento_file);
					$documento->setAuditConti($audit_conti);
					$em->persist($documento);

					$em->flush();
					return $this->redirect($this->generateUrl('allegati_audit_conti', array('id_audit' => $id_audit)));
				} catch (ResponseException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}
		$ruoloLettura = $this->isGranted('ROLE_AUDIT_LETTURA');

		$dati["menu"] = "attuazione";
		$dati["audit_conti"] = $audit_conti;
		$dati["tipo"] = $tipo->getId();
		$dati["tipiAudit"] = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$dati["form"] = $form->createView();
		$dati["ruolo_lettura"] = $ruoloLettura;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_pianificazioni", array('id_tipo' => $tipo->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit conti");

		return $this->render("AuditBundle:Attuazione:documentiAuditConti.html.twig", $dati);
	}

	/**
	 * @Route("/elimina_documento_conti/{id_documento_conti}", name="elimina_documento_conti")
	 */
	public function eliminaDocumentoConti($id_documento_conti) {

		$em = $this->getEm();
		$this->get('base')->checkCsrf('token');
		$documento = $em->getRepository("AuditBundle\Entity\DocumentoAuditConti")->find($id_documento_conti);

		$id_audit = $documento->getAuditConti()->getAudit()->getId();

		try {
			$em->remove($documento->getDocumentoFile());
			$em->remove($documento);
			$em->flush();
			return $this->addSuccessRedirect("Documento eliminato correttamente", "allegati_audit_conti", array("id_audit" => $id_audit));
		} catch (ResponseException $e) {
			$this->addFlash('error', $e->getMessage());
		}
	}

	/**
	 * @Route("/{id_quietanza}/audit_dettaglio_quietanza/{id_campione}", name="audit_dettaglio_quietanza")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function dettaglioQuietanzaAction($id_quietanza, $id_campione) {

		$em = $this->getEm();
		$quietanza = $em->getRepository("AttuazioneControlloBundle\Entity\QuietanzaGiustificativo")->find($id_quietanza);

		$campioneGiustificativo = $em->getRepository('AuditBundle\Entity\AuditCampioneGiustificativo')->find($id_campione);
		$operazione = $campioneGiustificativo->getAuditCampioneOperazione()->getAuditOperazione();
		$audit = $operazione->getAudit();
		$giustificativo = $quietanza->getGiustificativoPagamento();
		$pagamento = $giustificativo->getPagamento();

		$dati["menu"] = "giustificativi";
		$dati["quietanza"] = $quietanza;
		$dati["pagamento"] = $pagamento;
		$dati["id_campione"] = $id_campione;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $audit->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco campioni", $this->generateUrl("elenco_audit_operazione_attuazione", array('id_audit' => $audit->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco operazioni selezionate", $this->generateUrl("visualizza_audit_operazione_attuazione", array('id_audit' => $audit->getId(), 'id_audit_operazione' => $operazione->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Controllo operazione", $this->generateUrl("controllo_operazione_attuazione", array('id_audit_campione_operazione' => $campioneGiustificativo->getAuditCampioneOperazione()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio giustificativo", $this->generateUrl("dettaglio_giustificativo_audit", array('id_campione_giustificativo' => $campioneGiustificativo->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio quietanza");

		return $this->render("AuditBundle:Attuazione:dettaglioQuietanza.html.twig", $dati);
	}

}
