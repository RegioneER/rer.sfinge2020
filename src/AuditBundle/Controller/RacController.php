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

class RacController extends BaseController {

	/**
	 * @Route("/elenco_audit_rac/{id_tipo}", name="elenco_audit_rac")
	 * @PaginaInfo(titolo="Audit RAC", sottoTitolo="elenco RAC")
	 * @Menuitem(menuAttivo = "audit")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Audit")
	 * 				})
	 */
	public function elencoAuditAction($id_tipo) {

		$tipo = $this->getEm()->getRepository('AuditBundle\Entity\TipoAudit')->findOneById($id_tipo);
		$audits = $this->getEm()->getRepository('AuditBundle\Entity\Audit')->findByTipo($tipo);

		$tipiAudit = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$dati["audits"] = $audits;
		$dati["tipiAudit"] = $tipiAudit;
		$dati["menu"] = "rac";
		$dati['tipo_audit'] = $tipo;
		$dati['ruolo_lettura'] = 0;

		return $this->render("AuditBundle:Attuazione:elencoAudit.html.twig", $dati);
	}
	
	/**
	 * @Route("/allegati_audit_rac/{id_audit}", name="allegati_audit_rac")
	 * @PaginaInfo(titolo="Audit RAC", sottoTitolo="documenti allegati")
	 * @Menuitem(menuAttivo = "audit")
	 */
	public function documentiAuditRacAction($id_audit) {
		$em = $this->getEm();
		$audit = $em->getRepository('AuditBundle\Entity\Audit')->find($id_audit);

		if (count($audit->getAuditRac()) == 0) {
			$audit_rac = new \AuditBundle\Entity\AuditRac();
			$audit_rac->setAudit($audit);
			try {
				$em->persist($audit_rac);
				$em->flush();
			} catch (ResponseException $e) {
				$this->addFlash('error', $e->getMessage());
			}
		} else {
			$audit_racs = $audit->getAuditRac();
			$audit_rac = $audit_racs[0];
		}

		$tipo = $audit->getTipo();

		$request = $this->getCurrentRequest();

		$documento = new \AuditBundle\Entity\DocumentoAuditRac();
		$documento_file = new \DocumentoBundle\Entity\DocumentoFile();

		$listaTipi = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByTipologia(array('audit_rac'));

		$opzioni_form["lista_tipi"] = $listaTipi;
		$form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
		$form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {

					$this->container->get("documenti")->carica($documento_file, 0);

					$documento->setDocumentoFile($documento_file);
					$documento->setAuditRac($audit_rac);
					$em->persist($documento);

					$em->flush();
					return $this->redirect($this->generateUrl('allegati_audit_rac', array('id_audit' => $id_audit)));
				} catch (ResponseException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		$dati["menu"] = "rac";
		$dati["audit_rac"] = $audit_rac;
		$dati["tipo"] = $tipo->getId();
		$dati["tipiAudit"] = $this->getEm()->getRepository("AuditBundle\Entity\TipoAudit")->findAll();
		$dati["form"] = $form->createView();
		$dati["ruolo_lettura"] = 0;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit", $this->generateUrl("elenco_pianificazioni", array('id_tipo' => $tipo->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Audit RAC");

		return $this->render("AuditBundle:Rac:documentiAuditRac.html.twig", $dati);
	}
	
	/**
	 * @Route("/elimina_documento_rac/{id_documento_rac}", name="elimina_documento_rac")
	 */
	public function eliminaDocumentoRac($id_documento_rac) {

		$em = $this->getEm();
		$this->get('base')->checkCsrf('token');
		$documento = $em->getRepository("AuditBundle\Entity\DocumentoAuditRac")->find($id_documento_rac);

		$id_audit = $documento->getAuditRac()->getAudit()->getId();
		
		try {
			$em->remove($documento->getDocumentoFile());
			$em->remove($documento);
			$em->flush();
			return $this->addSuccessRedirect("Documento eliminato correttamente", "allegati_audit_rac", array("id_audit" => $id_audit));
		} catch (ResponseException $e) {
			$this->addFlash('error', $e->getMessage());
		}
	}
}
