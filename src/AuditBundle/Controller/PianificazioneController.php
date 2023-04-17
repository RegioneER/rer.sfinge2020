<?php

namespace AuditBundle\Controller;

use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use DocumentoBundle\Component\ResponseException;

class PianificazioneController extends BaseController {

	/**
	 * @Route("/elenco_pianificazioni/{id_tipo}", name="elenco_pianificazioni")
	 * @PaginaInfo(titolo="Audit Pianificazioni",sottoTitolo="elenco pianificazioni")
	 * @Menuitem(menuAttivo = "audit")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Audit")
	 * 				})
	 */
	public function elencoPianificazioniAction($id_tipo) {

		$tipo = $this->getEm()->getRepository('AuditBundle\Entity\TipoAudit')->findById($id_tipo);
		$pianificazioni = $this->getEm()->getRepository('AuditBundle\Entity\Audit')->findByTipo($tipo);

		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();

		$dati["pianificazioni"] = $pianificazioni;
		$dati["tipiAudit"] = $tipiAudit;
		$dati["menu"] = "pianificazione";
		$dati['ruolo_lettura'] = 0;

		$twig = 'AuditBundle:Pianificazioni:elencoPianificazioni.html.twig';
		return $this->render($twig, $dati);
	}

	/**
	 * @Route("/elenco_pianificazioni_organismo/{id_pianificazione}", name="elenco_pianificazioni_organismo")
	 * @PaginaInfo(titolo="Audit Pianificazioni",sottoTitolo="elenco pianificazioni organismi")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function elencoPianificazioniOrganismoAction($id_pianificazione) {

		$pianificazione = $this->getEm()->getRepository('AuditBundle\Entity\Audit')->find($id_pianificazione);
		$pianificazioniOrganismi = $this->getEm()->getRepository('AuditBundle\Entity\AuditOrganismo')->findByAudit($pianificazione);

		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$dati["pianificazioni_organismi"] = $pianificazioniOrganismi;
		$dati["tipiAudit"] = $tipiAudit;
		$dati["pianificazione"] = $pianificazione;
		$dati["menu"] = "pianificazione";
		$dati['ruolo_lettura'] = 0;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_pianificazioni", array('id_tipo' => $pianificazione->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco Pianificazioni Organismi");

		return $this->render("AuditBundle:Pianificazioni:elencoPianificazioniOrganismo.html.twig", $dati);
	}

	/**
	 * @Route("/lista_organismi/{id_pianificazione}", name="lista_organismi")
	 * @PaginaInfo(titolo="Elenco organismi",sottoTitolo="Lista organismi")
	 * @Menuitem(menuAttivo = "audit")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Organismi")})
	 */
	public function listaOrganismiAction($id_pianificazione) {

		$pianificazione = $this->getEm()->getRepository('AuditBundle\Entity\Audit')->findOneById($id_pianificazione);
		$organismi = $this->getEm()->getRepository('AuditBundle\Entity\Organismo')->findNotInPianificazione($id_pianificazione);

		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$dati["tipiAudit"] = $tipiAudit;
		$dati["pianificazione"] = $pianificazione;
		$dati["organismi"] = $organismi;
		$dati["menu"] = "pianificazione";
		$dati['ruolo_lettura'] = 0;

		return $this->render("AuditBundle:Pianificazioni:listaOrganismi.html.twig", $dati);
	}

	/**
	 * @Route("/crea_pianificazione_organismo/{id_pianificazione}/{id_organismo}", name="crea_pianificazione_organismo")
	 * @PaginaInfo(titolo="Elenco organismi",sottoTitolo="Lista organismi")
	 * @Menuitem(menuAttivo = "audit")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Organismi")})
	 */
	public function creaPianificazioneOrganismoAction($id_pianificazione, $id_organismo) {

		$em = $this->getEm();
		$pianificazione = $em->getRepository('AuditBundle\Entity\Audit')->findOneById($id_pianificazione);
		$organismo = $em->getRepository('AuditBundle\Entity\Organismo')->findOneById($id_organismo);

		$pianificazioneOrganismo = new \AuditBundle\Entity\AuditOrganismo();

		$pianificazioneOrganismo->setAudit($pianificazione);
		$pianificazioneOrganismo->setOrganismo($organismo);

		try {
			$em->persist($pianificazioneOrganismo);
			$em->flush();
			$this->addFlash('success', "Modifiche salvate correttamente");
		} catch (\Exception $e) {
			$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
			$this->get("logger")->error($e->getMessage());
		}

		$dati["pianificazione"] = $pianificazione;
		$dati["id_pianificazione"] = $id_pianificazione;
		$dati["menu"] = "pianificazione";

		return $this->redirectToRoute("elenco_pianificazioni_organismo", $dati);
	}

	/**
	 * @Route("/scheda_organismo/{id_pianificazione_organismo}", name="scheda_organismo")
	 * @PaginaInfo(titolo="Elenco organismi",sottoTitolo="Lista organismi")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function schedaOrganismoAction($id_pianificazione_organismo) {

		$em = $this->getEm();
		$pianificazioneOrganismo = $em->getRepository('AuditBundle\Entity\AuditOrganismo')->find($id_pianificazione_organismo);

		$documento = new \DocumentoBundle\Entity\DocumentoFile();
		$tipologia = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneByCodice("AUDIT");
		$documento->setTipologiaDocumento($tipologia);

		$pianificazioneOrganismo->setDocumento($documento);

		$form = $this->createForm('AuditBundle\Form\Pianificazione\SchedaOrganismoType', $pianificazioneOrganismo, array('url_indietro' => $this->generateUrl('elenco_pianificazioni_organismo', array("id_pianificazione" => $pianificazioneOrganismo->getAudit()->getId()))));

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {

			$form->bind($request);

			if ($form->isValid()) {
				try {
					if (!is_null($documento->getFile())) {
						$this->get("documenti")->carica($documento, 0);
						$documentoPianificazione = new \AuditBundle\Entity\DocumentoPianificazioneOrganismo();
						$documentoPianificazione->setDocumentoFile($documento);
						$documentoPianificazione->setAuditOrganismo($pianificazioneOrganismo);
						$em->persist($documentoPianificazione);
					}

					$em->persist($pianificazioneOrganismo);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
					return $this->redirect($this->generateUrl('scheda_organismo', array("id_pianificazione_organismo" => $pianificazioneOrganismo->getId())));
				} catch (\Exception $e) {
					$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
					$this->get("logger")->error($e->getMessage());
				}
			}
		}

		$dati["id_pianificazione"] = $pianificazioneOrganismo->getAudit()->getId();
		$dati["documenti"] = $pianificazioneOrganismo->getDocumentiPianificazioneOrganismo();
		$dati["menu"] = "pianificazione";
		$dati["form"] = $form->createView();

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_pianificazioni", array('id_tipo' => $pianificazioneOrganismo->getAudit()->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pianificazioni organismi", $this->generateUrl('elenco_pianificazioni_organismo', array('id_pianificazione' => $pianificazioneOrganismo->getAudit()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Scheda Organismo");

		return $this->render("AuditBundle:Pianificazioni:schedaOrganismo.html.twig", $dati);
	}

	/**
	 * @Route("/elimina_documento_organismo/{id_documento_organismo}", name="elimina_documento_organismo")
	 */
	public function eliminaDocumentoOrganismo($id_documento_organismo) {

		$em = $this->getEm();
		$documento = $em->getRepository("AuditBundle\Entity\DocumentoPianificazioneOrganismo")->find($id_documento_organismo);

		$id_pianificazione_organismo = $documento
						->getAuditOrganismo()->getId();
		//->getPianificazioneOrganismo()->getId();

		try {
			$em->remove($documento->getDocumentoFile());
			$em->remove($documento);
			$em->flush();
			return $this->addSuccessRedirect("Documento eliminato correttamente", "scheda_organismo", array("id_pianificazione_organismo" => $id_pianificazione_organismo));
		} catch (ResponseException $e) {
			$this->addFlash('error', $e->getMessage());
		}
	}

	/**
	 * @Route("/associa_requisiti_organismo/{id_pianificazione_organismo}", name="associa_requisiti_organismo")
	 * @PaginaInfo(titolo="Elenco requisiti associabili",sottoTitolo="Lista requisiti")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function associaRequisitiPianificazioneOrganismoAction($id_pianificazione_organismo) {

		$em = $this->getEm();
		$pianificazioneOrganismo = $em->getRepository('AuditBundle\Entity\AuditOrganismo')->findOneById($id_pianificazione_organismo);

		$requisiti = $pianificazioneOrganismo->getOrganismo()->getRequisiti();

		$dati["id_pianificazione"] = $pianificazioneOrganismo->getAudit()->getId();
		$dati["documenti"] = $pianificazioneOrganismo->getDocumentiPianificazioneOrganismo();
		$dati["requisiti"] = $requisiti;

		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$dati["tipiAudit"] = $tipiAudit;

		$options = array();
		$options["url_indietro"] = $this->generateUrl("elenco_pianificazioni_organismo", array("id_pianificazione" => $dati["id_pianificazione"]));

		$pianificazioni_requisiti_indicizzati = array();
		if (!is_null($pianificazioneOrganismo->getAuditRequisiti())) {
			foreach ($pianificazioneOrganismo->getAuditRequisiti() as $pianificazione_requisito) {
				$pianificazioni_requisiti_indicizzati[$pianificazione_requisito->getRequisito()->getId()] = $pianificazione_requisito;
			}
		}

		foreach ($requisiti as $requisito) {
			if (isset($pianificazioni_requisiti_indicizzati[$requisito->getId()])) {
				$auditPianificazioneRequisito = $pianificazioni_requisiti_indicizzati[$requisito->getId()];
				$auditPianificazioneRequisito->setSelezionato(true);
			} else {
				$auditPianificazioneRequisito = new \AuditBundle\Entity\AuditRequisito();
				$auditPianificazioneRequisito->setRequisito($requisito);
			}

			$pianificazioneOrganismo->addAuditRequisitoEsteso($auditPianificazioneRequisito);
		}

		$form = $this->createForm("AuditBundle\Form\Pianificazione\AssociazionePianificazioneRequisitoType", $pianificazioneOrganismo, $options);

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {

				foreach ($form->get("audit_requisiti_estesi")->all() as $form_requisiti) {
					$pianificazioni_requisiti_esteso = $form_requisiti->getData();

					if (isset($pianificazioni_requisiti_indicizzati[$pianificazioni_requisiti_esteso->getRequisito()->getId()])) {
						$auditPianificazioneRequisitoNuovo = $pianificazioni_requisiti_indicizzati[$pianificazioni_requisiti_esteso->getRequisito()->getId()];
					} else {
						$auditPianificazioneRequisitoNuovo = new \AuditBundle\Entity\AuditRequisito();
					}

					if ($pianificazioni_requisiti_esteso->getSelezionato()) {
						$auditPianificazioneRequisitoNuovo->setRequisito($pianificazioni_requisiti_esteso->getRequisito());
						$pianificazioneOrganismo->addAuditRequisito($auditPianificazioneRequisitoNuovo);
					} else {
						if (!is_null($auditPianificazioneRequisitoNuovo->getId())) {
							$em->remove($auditPianificazioneRequisitoNuovo);
						}
					}
				}

				$em = $this->getEm();
				try {
					$em->flush();
					$this->addFlash("success", "L'associazione è stata correttamente salvata");
					return $this->redirectToRoute("associa_requisiti_organismo", array("id_pianificazione_organismo" => $id_pianificazione_organismo));
				} catch (\Exception $e) {
					$this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza. " . $e->getMessage());
				}
			}
		}

		$dati["form"] = $form->createView();
		$dati["pianificazioneOrganismo"] = $pianificazioneOrganismo;
		$dati["menu"] = "pianificazione";
		$dati["ruolo_lettura"] = 0;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_pianificazioni", array('id_tipo' => $pianificazioneOrganismo->getAudit()->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pianificazioni organismi", $this->generateUrl('elenco_pianificazioni_organismo', array('id_pianificazione' => $pianificazioneOrganismo->getAudit()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Associa requisiti");

		return $this->render("AuditBundle:Pianificazioni:associaRequisitiPianificazioneOrganismo.html.twig", $dati);
	}

	/**
	 * @Route("/associa_operazioni_requisito_pulisci/{id_pianificazione_requisito}", name="associa_operazioni_requisito_pulisci")
	 */
	public function elencoAttuazionePulisciAction($id_pianificazione_requisito) {
		$this->get("ricerca")->pulisci(new \AuditBundle\Form\Entity\RicercaUniverso());
		return $this->redirectToRoute("associa_operazioni_requisito", array("id_pianificazione_requisito" => $id_pianificazione_requisito));
	}

	/**
	 * @Route("/associa_operazioni_requisito/{id_pianificazione_requisito}/{sort}/{direction}/{page}", defaults={"sort" = "a.id", "direction" = "asc", "page" = "1"}, name="associa_operazioni_requisito")
	 * @PaginaInfo(titolo="Maschera Universo",sottoTitolo="associazione operazioni a requisito")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function associaOperazioniRequisitoAction($id_pianificazione_requisito) {

		$datiRicerca = new \AuditBundle\Form\Entity\RicercaUniverso();
		$datiRicerca->setSezione('SISTEMA');

		$em = $this->getEm();
		$pianificazioneRequisito = $em->getRepository('AuditBundle\Entity\AuditRequisito')->find($id_pianificazione_requisito);
		$pianificazioneOrganismo = $pianificazioneRequisito->getAuditOrganismo();

		$datiRicerca->setAuditOrganismo($pianificazioneOrganismo->getId());
		$datiRicerca->setAuditRequisito($id_pianificazione_requisito);

		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		$options = array();
		$options["url_indietro"] = $this->generateUrl("associa_requisiti_organismo", array("id_pianificazione_organismo" => $pianificazioneOrganismo->getId()));

		$campioni_indicizzati = array();
		if (!is_null($pianificazioneRequisito->getCampioni())) {
			foreach ($pianificazioneRequisito->getCampioni() as $campione) {
				$campioni_indicizzati[$campione->getRichiesta()->getId()] = $campione;
			}
		}

		foreach ($risultato["risultato"] as $richiesta) {
			$audit_campione = new \AuditBundle\Entity\AuditCampione();
			$audit_campione->setRichiesta($richiesta);

			if (isset($campioni_indicizzati[$richiesta->getId()])) {
				$audit_campione->setSelezionato(true);
			}

			$pianificazioneRequisito->addCampioneEsteso($audit_campione);
		}

		$form = $this->createForm("AuditBundle\Form\Pianificazione\AssociazioneOperazioniRequisitoType", $pianificazioneRequisito, $options);

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {

				foreach ($form->get("campioni_estesi")->all() as $form_campione) {
					$campione_esteso = $form_campione->getData();

					if (isset($campioni_indicizzati[$campione_esteso->getRichiesta()->getId()])) {
						$campione_nuovo = $campioni_indicizzati[$campione_esteso->getRichiesta()->getId()];
					} else {
						$campione_nuovo = new \AuditBundle\Entity\AuditCampione();
					}

					if ($campione_esteso->getSelezionato()) {
						$campione_nuovo->setRichiesta($campione_esteso->getRichiesta());
						$pianificazioneRequisito->addCampione($campione_nuovo);
					} else {
						if (!is_null($campione_nuovo->getId())) {
							$em->remove($campione_nuovo);
						}
					}
				}

				$em = $this->getEm();
				try {
					$em->flush();
					$this->addFlash("success", "La pianificazione per il requisito è stata correttamente salvata");
					return $this->redirectToRoute("associa_requisiti_organismo", array("id_pianificazione_organismo" => $pianificazioneOrganismo->getId()));
				} catch (\Exception $e) {
					$this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza. " . $e->getMessage());
				}
			}
		}

		$ruoloLettura = $this->isGranted('ROLE_AUDIT_LETTURA');

		$dati = array('risultati' => $risultato["risultato"], "formRicerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]);
		$dati["form"] = $form->createView();
		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$dati["tipiAudit"] = $tipiAudit;
		$dati["menu"] = "pianificazione";
		$dati["risultati"] = $risultato["risultato"];
		$dati["formRicerca"] = $risultato["form_ricerca"];
		$dati["filtro_attivo"] = $risultato["filtro_attivo"];
		$dati["ruolo_lettura"] = $ruoloLettura;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_pianificazioni", array('id_tipo' => $pianificazioneOrganismo->getAudit()->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pianificazioni organismi", $this->generateUrl('elenco_pianificazioni_organismo', array('id_pianificazione' => $pianificazioneOrganismo->getAudit()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Associa requisiti", $this->generateUrl('associa_requisiti_organismo', array('id_pianificazione_organismo' => $pianificazioneOrganismo->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Associa operazioni");

		return $this->render("AuditBundle:Pianificazioni:associaOperazioniRequisito.html.twig", $dati);
	}

	/**
	 * @Route("/rimuovi_pianificazione_organismo/{id_pianificazione_organismo}", name="rimuovi_pianificazione_organismo")
	 */
	public function rimuoviPianificazioneOrganismo($id_pianificazione_organismo) {

		$em = $this->getEm();
		$pianificazioneOrganismo = $em->getRepository("AuditBundle\Entity\AuditOrganismo")->find($id_pianificazione_organismo);

		$id_pianificazione = $pianificazioneOrganismo->getAudit()->getId();

		if (!$this->isGranted("ROLE_AUDIT")) {
			return $this->addErrorRedirect("Operazione non autorizzata", "elenco_pianificazioni_organismo", array("id_pianificazione" => $id_pianificazione));
		}

		try {
			$em->remove($pianificazioneOrganismo);
			$em->flush();
			return $this->addSuccessRedirect("Organismo eliminato correttamente", "elenco_pianificazioni_organismo", array("id_pianificazione" => $id_pianificazione));
		} catch (ResponseException $e) {
			$this->addFlash('error', $e->getMessage());
		}
	}

	/**
	 * @Route("/elenco_audit_operazione/{id_pianificazione}", name="elenco_audit_operazione")
	 * @PaginaInfo(titolo="Audit Pianificazioni",sottoTitolo="elenco campioni")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function elencoAuditOperazioniAction($id_pianificazione) {

		$pianificazione = $this->getEm()->getRepository('AuditBundle\Entity\Audit')->find($id_pianificazione);
		$campioniOperazioni = $this->getEm()->getRepository('AuditBundle\Entity\AuditOperazione')->findByAudit($pianificazione);

		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$dati["campioni_operazioni"] = $campioniOperazioni;
		$dati["tipiAudit"] = $tipiAudit;
		$dati["pianificazione"] = $pianificazione;
		$dati["menu"] = "pianificazione";
		$dati["ruolo_lettura"] = 0;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_pianificazioni", array('id_tipo' => $pianificazione->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Campioni Operazioni");

		return $this->render("AuditBundle:Pianificazioni:elencoAuditOperazioni.html.twig", $dati);
	}

	/**
	 * @Route("/crea_audit_operazione/{id_pianificazione}", name="crea_audit_operazione")
	 * @PaginaInfo(titolo="Audit Pianificazioni",sottoTitolo="Crea campione")
	 * @Menuitem(menuAttivo = "audit")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Organismi")})
	 */
	public function creaAuditOperazioniAction($id_pianificazione) {

		$em = $this->getEm();
		$pianificazione = $em->getRepository('AuditBundle\Entity\Audit')->findOneById($id_pianificazione);

		$campioneOperazione = new \AuditBundle\Entity\AuditOperazione();

		$campioneOperazione->setAudit($pianificazione);

		$documento = new \DocumentoBundle\Entity\DocumentoFile();
		$tipologia = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneByCodice("AUDIT");
		$documento->setTipologiaDocumento($tipologia);

		$campioneOperazione->setDocumento($documento);

		$form = $this->createForm('AuditBundle\Form\Pianificazione\CampioneOperazioneType', $campioneOperazione, array('url_indietro' => $this->generateUrl('elenco_audit_operazione', array("id_pianificazione" => $pianificazione->getId()))));

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {

			$form->bind($request);

			if ($form->isValid()) {
				try {
					if (!is_null($documento->getFile())) {
						$this->get("documenti")->carica($documento, 0);
						$documentoCampione = new \AuditBundle\Entity\DocumentoOperazione();
						$documentoCampione->setDocumentoFile($documento);
						$documentoCampione->setAuditOperazione($campioneOperazione);
						$em->persist($documentoCampione);
					}

					$em->persist($campioneOperazione);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
					return $this->redirect($this->generateUrl('elenco_audit_operazione', array("id_pianificazione" => $pianificazione->getId())));
				} catch (\Exception $e) {
					$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
					$this->get("logger")->error($e->getMessage());
				}
			}
		}

		$dati["id_pianificazione"] = $campioneOperazione->getAudit()->getId();
		$dati["documenti"] = $campioneOperazione->getDocumentiOperazione();
		$dati["menu"] = "pianificazione";
		$dati["form"] = $form->createView();

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_pianificazioni", array('id_tipo' => $pianificazione->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Crea campione");

		return $this->render("AuditBundle:Pianificazioni:auditOperazione.html.twig", $dati);
	}

	/**
	 * @Route("/modifica_audit_operazione/{id_pianificazione}/{id_audit_operazione}", name="modifica_audit_operazione")
	 * @PaginaInfo(titolo="Elenco organismi",sottoTitolo="Lista organismi")
	 * @Menuitem(menuAttivo = "audit")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Organismi")})
	 */
	public function modificaAuditOperazioniAction($id_pianificazione, $id_audit_operazione) {
		return $this->gestioneAuditOperazione($id_pianificazione, $id_audit_operazione);
	}

	/**
	 * @Route("/visualizza_audit_operazione/{id_pianificazione}/{id_audit_operazione}", name="visualizza_audit_operazione")
	 * @PaginaInfo(titolo="Elenco organismi",sottoTitolo="Lista organismi")
	 * @Menuitem(menuAttivo = "audit")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Organismi")})
	 */
	public function visualizzaAuditOperazioniAction($id_pianificazione, $id_audit_operazione) {
		return $this->gestioneAuditOperazione($id_pianificazione, $id_audit_operazione, true);
	}

	public function gestioneAuditOperazione($id_pianificazione, $id_audit_operazione, $read = false) {
		$em = $this->getEm();
		$pianificazione = $em->getRepository('AuditBundle\Entity\Audit')->findOneById($id_pianificazione);
		$campioneOperazione = $em->getRepository('AuditBundle\Entity\AuditOperazione')->findOneById($id_audit_operazione);

		$documento = new \DocumentoBundle\Entity\DocumentoFile();
		$tipologia = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneByCodice("AUDIT");
		$documento->setTipologiaDocumento($tipologia);

		$campioneOperazione->setDocumento($documento);

		$options = array('url_indietro' => $this->generateUrl('elenco_audit_operazione', array("id_pianificazione" => $pianificazione->getId())));
		$options['disabled'] = $read;
		$form = $this->createForm('AuditBundle\Form\Pianificazione\CampioneOperazioneType', $campioneOperazione, $options);

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {

			$form->bind($request);

			if ($form->isValid()) {
				try {
					if (!is_null($documento->getFile())) {
						$this->get("documenti")->carica($documento, 0);
						$documentoCampione = new \AuditBundle\Entity\DocumentoOperazione();
						$documentoCampione->setDocumentoFile($documento);
						$documentoCampione->setAuditOperazione($campioneOperazione);
						$em->persist($documentoCampione);
					}

					$em->persist($campioneOperazione);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
					return $this->redirect($this->generateUrl('elenco_audit_operazione', array("id_pianificazione" => $pianificazione->getId())));
				} catch (\Exception $e) {
					$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
					$this->get("logger")->error($e->getMessage());
				}
			}
		}

		$dati["id_pianificazione"] = $campioneOperazione->getAudit()->getId();
		$dati["documenti"] = $campioneOperazione->getDocumentiOperazione();
		$dati["menu"] = "pianificazione";
		$dati["form"] = $form->createView();
		$dati["readonly"] = $options['disabled'];

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_pianificazioni", array('id_tipo' => $pianificazione->getTipo()->getId())));
		if ($read == true) {
			$breadfinale = 'Visualizza campione';
		} else {
			$breadfinale = 'Modifica campione';
		}
		$this->container->get("pagina")->aggiungiElementoBreadcrumb($breadfinale);

		return $this->render("AuditBundle:Pianificazioni:auditOperazione.html.twig", $dati);
	}

	/**
	 * @Route("/campioni_audit_operazione/{id_pianificazione}/{id_audit_operazione}", name="campioni_audit_operazione")
	 * @PaginaInfo(titolo="Campioni audit operazione", sottoTitolo="Elenco delle operazioni campionate per l'audit delle operazioni")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function campioniAuditOperazioneAction($id_pianificazione, $id_audit_operazione) {
		$em = $this->getEm();
		$pianificazione = $em->getRepository('AuditBundle\Entity\Audit')->findOneById($id_pianificazione);
		$AuditOperazione = $em->getRepository('AuditBundle\Entity\AuditOperazione')->findOneById($id_audit_operazione);

		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$dati["tipiAudit"] = $tipiAudit;
		$dati["audit_operazione"] = $AuditOperazione;
		$campioni = $AuditOperazione->getCampioni();

		$dati["campioni"] = $campioni;
		$dati["menu"] = "pianificazione";
		$dati["ruolo_lettura"] = 0;

		/* $this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_audit_attuazione", array('id_tipo' => $audit->getTipo()->getId())));
		  $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco audit organismo", $this->generateUrl('elenco_audit_organismo_attuazione', array('id_audit' => $audit->getId())));
		  $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco Requisiti", $this->generateUrl('requisiti_audit_organismo_attuazione', array('id_audit_organismo' => $pianificazione->getId())));
		  $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco operazioni"); */

		return $this->render("AuditBundle:Pianificazioni:elencoAuditOperazioniCampionate.html.twig", $dati);
	}

	/**
	 * @Route("/associa_operazioni_campione/{id_audit_operazione}/{sort}/{direction}/{page}", defaults={"sort" = "a.id", "direction" = "asc", "page" = "1"}, name="associa_operazioni_campione")
	 * @PaginaInfo(titolo="Elenco requisiti associabili",sottoTitolo="Lista requisiti")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function associaOperazioneCampioneAction($id_audit_operazione) {

		$datiRicerca = new \AuditBundle\Form\Entity\RicercaUniverso();
		$datiRicerca->setSezione('OPERAZIONE');
		$datiRicerca->setAuditOperazione($id_audit_operazione);

		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		$em = $this->getEm();
		$auditOperazione = $em->getRepository('AuditBundle\Entity\AuditOperazione')->findOneById($id_audit_operazione);

		$dati["id_pianificazione"] = $auditOperazione->getAudit()->getId();

		$options = array();
		$options["url_indietro"] = $this->generateUrl("elenco_audit_operazione", array("id_pianificazione" => $dati["id_pianificazione"]));

		$campioni_indicizzati = array();
		if (!is_null($auditOperazione->getCampioni())) {
			foreach ($auditOperazione->getCampioni() as $campione) {
				$campioni_indicizzati[$campione->getRichiesta()->getId()] = $campione;
			}
		}

		foreach ($risultato["risultato"] as $richiesta) {
			$audit_campione_operazione = new \AuditBundle\Entity\AuditCampioneOperazione();
			$audit_campione_operazione->setRichiesta($richiesta);

			if (isset($campioni_indicizzati[$richiesta->getId()])) {
				$audit_campione_operazione->setSelezionato(true);
			}

			$auditOperazione->addCampioneEsteso($audit_campione_operazione);
		}

		$form = $this->createForm("AuditBundle\Form\Pianificazione\AssociazioneOperazioniCampioneType", $auditOperazione, $options);

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {

				foreach ($form->get("campioni_estesi")->all() as $form_campione) {
					$campione_esteso = $form_campione->getData();

					if (isset($campioni_indicizzati[$campione_esteso->getRichiesta()->getId()])) {
						$campione_nuovo = $campioni_indicizzati[$campione_esteso->getRichiesta()->getId()];
					} else {
						$campione_nuovo = new \AuditBundle\Entity\AuditCampioneOperazione();
					}

					if ($campione_esteso->getSelezionato()) {
						$campione_nuovo->setRichiesta($campione_esteso->getRichiesta());
						$auditOperazione->addCampione($campione_nuovo);
					} else {
						if (!is_null($campione_nuovo->getId())) {
                                                    $em->remove($campione_nuovo);
						}
					}
				}

				$em = $this->getEm();
				try {
					$em->flush();
					$this->addFlash("success", "La pianificazione per il requisito è stata correttamente salvata");
					return $this->redirectToRoute("associa_operazioni_campione", array("id_audit_operazione" => $auditOperazione->getId()));
				} catch (\Exception $e) {
					$this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza. " . $e->getMessage());
				}
			}
		}

		$ruoloLettura = $this->isGranted('ROLE_AUDIT_LETTURA');

		$dati = array('risultati' => $risultato["risultato"], "formRicerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]);
		$dati["form"] = $form->createView();
		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$dati["tipiAudit"] = $tipiAudit;
		$dati["menu"] = "pianificazione";
		$dati["risultati"] = $risultato["risultato"];
		$dati["formRicerca"] = $risultato["form_ricerca"];
		$dati["filtro_attivo"] = $risultato["filtro_attivo"];
		$dati["ruolo_lettura"] = $ruoloLettura;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_pianificazioni", array('id_tipo' => $auditOperazione->getAudit()->getTipo()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco campioni operazioni", $this->generateUrl("elenco_audit_operazione", array('id_pianificazione' => $auditOperazione->getAudit()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Associa operazioni");
		return $this->render("AuditBundle:Pianificazioni:associaCampioneOperazioni.html.twig", $dati);
	}

	/**
	 * @Route("/rimuovi_pagamento_campione_operazione/{id_audit_operazione}", name="rimuovi_pagamento_campione_operazione")
	 */
	public function rimuoviPagamentoCampioneOperazioneAction($id_audit_operazione, $id_campione_operazione) {

		$em = $this->getEm();
		$AuditOperazione = $em->getRepository('AuditBundle\Entity\AuditOperazione')->findOneById($id_audit_operazione);
		$auditCampioneOperazione = $em->getRepository('AuditBundle\Entity\AuditCampioneOperazione')->findOneById($id_campione_operazione);

		$id_pianificazione = $AuditOperazione->getId();

		if (!$this->isGranted("ROLE_AUDIT")) {
			return $this->addErrorRedirect("Operazione non autorizzata", "elenco_pianificazioni_organismo", array("id_pianificazione" => $id_pianificazione));
		}

		try {
			$em->remove($auditCampioneOperazione);
			$em->flush();
			return $this->addSuccessRedirect("Organismo eliminato correttamente", "elenco_pianificazioni_organismo", array("id_pianificazione" => $id_pianificazione));
		} catch (ResponseException $e) {
			$this->addFlash('error', $e->getMessage());
		}
	}

	/**
	 * @Route("/associa_operazioni_campioni_pulisci/{id_audit_operazione}", name="associa_operazioni_campioni_pulisci")
	 */
	public function elencoOperazionePulisciAction($id_audit_operazione) {
		$this->get("ricerca")->pulisci(new \AuditBundle\Form\Entity\RicercaUniverso());
		return $this->redirectToRoute("associa_operazioni_campione", array("id_audit_operazione" => $id_audit_operazione));
	}

	/**
	 * @Route("/allegati_strategie/{id_pianificazione}", name="allegati_strategie")
	 * @PaginaInfo(titolo="Audit Pianificazione", sottoTitolo="documenti allegati strategia")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function documentiAllegatiStrategieAction($id_pianificazione) {
		$em = $this->getEm();
		$audit = $em->getRepository('AuditBundle\Entity\Audit')->find($id_pianificazione);

		if (count($audit->getAuditStrategie()) == 0) {
			$strategia = new \AuditBundle\Entity\AuditStrategia();
			$strategia->setAudit($audit);
			try {
				$em->persist($strategia);
				$em->flush();
			} catch (ResponseException $e) {
				$this->addFlash('error', $e->getMessage());
			}
		} else {
			$strategie = $audit->getAuditStrategie();
			$strategia = $strategie[0];
		}

		$tipo = $audit->getTipo();

		$request = $this->getCurrentRequest();

		$documento_strategia = new \AuditBundle\Entity\DocumentoStrategia();
		$documento_file = new \DocumentoBundle\Entity\DocumentoFile();

		$listaTipi = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByTipologia(array('audit_strategia'));

		$opzioni_form["lista_tipi"] = $listaTipi;
		$form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
		$form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {

					$this->container->get("documenti")->carica($documento_file, 0);

					$documento_strategia->setDocumentoFile($documento_file);
					$documento_strategia->setAuditStrategia($strategia);
					$em->persist($documento_strategia);

					$em->flush();
					return $this->redirect($this->generateUrl('allegati_strategie', array('id_pianificazione' => $id_pianificazione)));
				} catch (ResponseException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}
		$ruoloLettura = $this->isGranted('ROLE_AUDIT_LETTURA');

		$dati["menu"] = "pianificazione";
		$dati["strategia"] = $strategia;
		$dati["tipo"] = $tipo->getId();
		$dati["tipiAudit"] = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$dati["form"] = $form->createView();
		$dati['ruolo_lettura'] = $ruoloLettura;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_pianificazioni", array('id_tipo' => $tipo->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Strategie");

		return $this->render("AuditBundle:Pianificazioni:documentiStrategia.html.twig", $dati);
	}
	
	/**
	 * @Route("/elimina_documento_strategia/{id_documento_strategia}", name="elimina_documento_strategia")
	 */
	public function eliminaDocumentoStrategia($id_documento_strategia) {

		$em = $this->getEm();
		$this->get('base')->checkCsrf('token');
		$documento = $em->getRepository("AuditBundle\Entity\DocumentoStrategia")->find($id_documento_strategia);

		$id_pianificazione = $documento->getAuditStrategia()->getAudit()->getId();
		
		try {
			$em->remove($documento->getDocumentoFile());
			$em->remove($documento);
			$em->flush();
			return $this->addSuccessRedirect("Documento eliminato correttamente", "allegati_strategie", array("id_pianificazione" => $id_pianificazione));
		} catch (ResponseException $e) {
			$this->addFlash('error', $e->getMessage());
		}
	}

}
