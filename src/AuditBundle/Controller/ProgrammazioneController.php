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

class ProgrammazioneController extends BaseController {

	/**
	 * @Route("/organismi_requisiti", name="organismi_requisiti")
	 * @Template("AuditBundle:OrganismoRequisito:organismiRequisiti.html.twig")
	 * @PaginaInfo(titolo="Audit Programmazione",sottoTitolo="organismi e requisiti")
	 * @Menuitem(menuAttivo = "audit")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Organismi e Requisiti")
	 * 				})
	 */
	public function organismiRequisitiAction() {
		$em = $this->getEm();

		$organismi = $em->getRepository("AuditBundle\Entity\Organismo")->findAll();
		$requisiti = $em->getRepository("AuditBundle\Entity\Requisito")->findAll();
		$tipiAudit = $em->getRepository("AuditBundle\Entity\TipoAudit")->findAll();

		$dati = array("organismi" => $organismi, "requisiti" => $requisiti, "tipiAudit" => $tipiAudit, "menu" => "programmazione", "ruolo_lettura" => 0);
		return $dati;
	}

	/**
	 * @Route("/aree_tematiche", name="elenco_aree_tematiche")
	 * @Template("AuditBundle:AreaTematica:elencoAreeTematiche.html.twig")
	 * @PaginaInfo(titolo="Audit Programmazione",sottoTitolo="aree tematiche")
	 * @Menuitem(menuAttivo = "audit")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Aree Tematiche")
	 * 				})
	 */
	public function elencoAreeTematicheAction() {
		$em = $this->getEm();

		$areeTematiche = $em->getRepository("AuditBundle\Entity\AreaTematica")->findAll();
		$tipiAudit = $em->getRepository("AuditBundle\Entity\TipoAudit")->findAll();

		$dati = array("areeTematiche" => $areeTematiche, "tipiAudit" => $tipiAudit, "menu" => "programmazione", "ruolo_lettura" => 0,);
		return $dati;
	}

	/**
	 * @Route("/crea_area_tematica", name="crea_area_tematica")
	 * @Template("AuditBundle:AreaTematica:areaTematica.html.twig")
	 * @Menuitem(menuAttivo = "audit")
	 * @PaginaInfo(titolo="Nuova Area Tematica",sottoTitolo="")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Aree Tematiche", route="elenco_aree_tematiche"), @ElementoBreadcrumb(testo="Crea area tematica")})
	 */
	public function creaAreaTematicaAction() {
		$em = $this->getDoctrine()->getManager();

		$areaTematica = new \AuditBundle\Entity\AreaTematica();
		$request = $this->getCurrentRequest();
		$funzioniService = $this->get('funzioni_utili');

		$options["readonly"] = false;
		$options["em"] = $this->getEm();
		$options["url_indietro"] = $this->generateUrl("elenco_aree_tematiche");
		$tipiAudit = $em->getRepository("AuditBundle\Entity\TipoAudit")->findAll();

		
		$form = $this->createForm('AuditBundle\Form\AreaTematicaType', $areaTematica, $options);

		if ($request->isMethod('POST')) {

			$form->bind($request);

			if ($form->isValid()) {

				try {
					$em->persist($areaTematica);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");

					return $this->redirect($this->generateUrl('elenco_aree_tematiche'));
				} catch (\Exception $e) {
					$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
					$this->get("logger")->error($e->getMessage());
				}
			}
		}

		$form_params["form"] = $form->createView();
		$form_params["areaTematica"] = $areaTematica;
		$form_params["tipiAudit"] = $tipiAudit;
		$form_params["ruolo_lettura"] = 0;

		return $form_params;
	}

	/**
	 * @Route("/metodologie_campionamento", name="elenco_metodologie_campionamento")
	 * @Template("AuditBundle:MetodologiaCampionamento:elencoMetodologieCampionamento.html.twig")
	 * @PaginaInfo(titolo="Audit Programmazione",sottoTitolo="metodologie di campionamento")
	 * @Menuitem(menuAttivo = "audit")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Metodologie Campionamento")
	 * 				})
	 */
	public function elencoMetodologieCampionamentoAction() {
		$em = $this->getEm();

		$metodologieCampionamento = $em->getRepository("AuditBundle\Entity\MetodologiaCampionamento")->findAll();
		$tipiAudit = $em->getRepository("AuditBundle\Entity\TipoAudit")->findAll();

		$dati = array("metodologieCampionamento" => $metodologieCampionamento, "tipiAudit" => $tipiAudit, "menu" => "programmazione", "ruolo_lettura" => 0,);
		return $dati;
	}

	/**
	 * @Route("/crea_metodologia_campionamento", name="crea_metodologia_campionamento")
	 * @Template("AuditBundle:MetodologiaCampionamento:metodologiaCampionamento.html.twig")
	 * @Menuitem(menuAttivo = "audit")
	 * @PaginaInfo(titolo="Nuova Metodologia di Campionamento",sottoTitolo="")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Metodologie di Campionamento", route="elenco_metodologie_campionamento"), @ElementoBreadcrumb(testo="Crea metodologia di campionamento")})
	 */
	public function creaMetodologiaCampionamentoAction() {
		$em = $this->getDoctrine()->getManager();

		$metodologiaCampionamento = new \AuditBundle\Entity\MetodologiaCampionamento();
		$request = $this->getCurrentRequest();

		$options["readonly"] = false;
		$options["em"] = $this->getEm();
		$options["url_indietro"] = $this->generateUrl("elenco_metodologie_campionamento");
		$tipiAudit = $em->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		
		$form = $this->createForm('AuditBundle\Form\MetodologiaCampionamentoType', $metodologiaCampionamento, $options);

		if ($request->isMethod('POST')) {

			$form->bind($request);

			if ($form->isValid()) {

				try {
					$em->persist($metodologiaCampionamento);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");

					return $this->redirect($this->generateUrl('elenco_metodologie_campionamento'));
				} catch (\Exception $e) {
					$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
					$this->get("logger")->error($e->getMessage());
				}
			}
		}

		$form_params["form"] = $form->createView();
		$form_params["metodologiaCampionamento"] = $metodologiaCampionamento;
		$form_params["tipiAudit"] = $tipiAudit;
		$form_params["ruolo_lettura"] = 0;

		return $form_params;
	}

	/**
	 * @Route("/tipi_irregolarita", name="elenco_tipi_irregolarita")
	 * @Template("AuditBundle:TipoIrregolarita:elencoTipiIrregolarita.html.twig")
	 * @PaginaInfo(titolo="Audit Programmazione",sottoTitolo="tipi irregolarità")
	 * @Menuitem(menuAttivo = "audit")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Audit")
	 * 				})
	 */
	public function elencoTipiIrregolaritaAction() {
		$em = $this->getEm();

		$tipiIrregolarita = $em->getRepository("AuditBundle\Entity\TipoIrregolarita")->findAll();
		$tipiAudit = $em->getRepository("AuditBundle\Entity\TipoAudit")->findAll();


		$dati = array("tipiIrregolarita" => $tipiIrregolarita, "tipiAudit" => $tipiAudit, "menu" => "programmazione", "ruolo_lettura" => 0,);
		return $dati;
	}

	/**
	 * Route("/crea_tipo_irregolarita", name="crea_tipo_irregolarita")
	 * @Template("AuditBundle:TipoIrregolarita:tipoIrregolarita.html.twig")
	 * @Menuitem(menuAttivo = "audit")
	 * @PaginaInfo(titolo="Nuovo Tipo Irregolarità",sottoTitolo="")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Tipo Irregolarità", route="elenco_tipi_irregolarita"), @ElementoBreadcrumb(testo="Crea tipo irregolarità")})
	 */
	public function creaTipoIrregolaritaAction() {
		$em = $this->getDoctrine()->getManager();

		$tipoIrregolarita = new \AuditBundle\Entity\TipoIrregolarita();
		$request = $this->getCurrentRequest();

		$options["readonly"] = false;
		$options["em"] = $this->getEm();
		$options["url_indietro"] = $this->generateUrl("elenco_metodologie_campionamento");
		$tipiAudit = $em->getRepository("AuditBundle\Entity\TipoAudit")->findAll();

		$form = $this->createForm('AuditBundle\Form\TipoIrregolaritaType', $tipoIrregolarita, $options);

		if ($request->isMethod('POST')) {

			$form->bind($request);

			if ($form->isValid()) {

				try {
					$em->persist($tipoIrregolarita);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");

					return $this->redirect($this->generateUrl('elenco_tipi_irregolarita'));
				} catch (\Exception $e) {
					$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
					$this->get("logger")->error($e->getMessage());
				}
			}
		}

		$form_params["form"] = $form->createView();
		$form_params["tipoIrregolarita"] = $tipoIrregolarita;
		$form_params["tipiAudit"] = $tipiAudit;

		return $form_params;
	}

}
