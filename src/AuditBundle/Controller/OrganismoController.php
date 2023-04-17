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

class OrganismoController extends BaseController {
	
	/**
	 * @Route("/associa_requisiti/{id_organismo}", name="associa_requisiti")
	 * @PaginaInfo(titolo="Associazione requisiti organismo",sottoTitolo="consente l'associazione dei requisiti all'organismo")
	 * @Menuitem(menuAttivo = "audit")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Organismi Requisiti", route="organismi_requisiti"),
	 *                       @ElementoBreadcrumb(testo="Associazione Requisiti Organismo")})
	 */
	public function associaRequisitiAction($id_organismo) {

		$em = $this->getEm();
		$requisti = $em->getRepository("AuditBundle\Entity\Requisito")->findAll();
		$organismo = $em->getRepository("AuditBundle\Entity\Organismo")->find($id_organismo);

		$options["url_indietro"] = $this->generateUrl("organismi_requisiti");

		$form = $this->createForm("AuditBundle\Form\Organismi\AssociazioneRequisitoOrganismoType", $organismo, $options);

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				$em = $this->getEm();
				try {
					$em->flush();
					$this->addFlash("success", "Dati correttamente salvati");
					return $this->redirectToRoute("organismi_requisiti");
				} catch (\Exception $e) {
					$this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza. " . $e->getMessage());
				}
			}
		}

		$dati["form"] = $form->createView();
		$dati["organismo"] = $organismo;
		$dati["requisiti"] = $requisti;

		return $this->render("AuditBundle:OrganismoRequisito:associaRequisiti.html.twig", $dati);
	}
}
