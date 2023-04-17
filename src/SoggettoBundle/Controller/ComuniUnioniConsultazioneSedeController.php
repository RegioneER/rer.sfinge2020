<?php

namespace SoggettoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SoggettoBundle\Entity\Sede;
use SoggettoBundle\Form\SedeType;
use Symfony\Component\HttpFoundation\Request;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\PaginaInfo;
use BaseBundle\Annotation\ControlloAccesso;
/**
 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Soggetti Giuridici", route="elenco_soggetti_giuridici")})
 */
class ComuniUnioniConsultazioneSedeController extends Controller {
	
	/**
	 * @Route("/elenco/{id_soggetto}", name="elenco_sedi_operative_comune")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco sedi")})
	 * @PaginaInfo(titolo="Comune - Elenco sedi")
	 * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="show")
	 */
	public function elencoSediOperativeAction($id_soggetto) {

		$em = $this->getDoctrine()->getManager();

		$azienda = $em->getRepository('SoggettoBundle:Soggetto')->findOneById($id_soggetto);
			
		$sedi = $azienda->getSedi();

		$pubblico = false;

		return $this->render('SoggettoBundle:Sede:elencoSediOperative.html.twig', array(
					'sedi' => $sedi, 'azienda' => $azienda, 'pubblico' => $pubblico
		));
	}
	
	/**
	 * @Route("/visualizza/{id_soggetto}/{id_sede}", name="comune_visualizza_sede_operativa")
	 * @Template("SoggettoBundle:Sede:sede.html.twig")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Gestione sedi", route="elenco_sedi_operative",parametri={"id_soggetto"}), @ElementoBreadcrumb(testo="Visualizza sede")})
	 * @PaginaInfo(titolo="Azienda - Visualizza sede")
	 * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="show")
	 */
	public function visualizzaSedeOperativaAction(Request $request, $id_soggetto, $id_sede) {

		$em = $this->getDoctrine()->getManager();
		$funzioniService = $this->get('funzioni_utili');
		$azienda = $em->getRepository('SoggettoBundle:Soggetto')->findOneById($id_soggetto);
				
		$sede = $em->getRepository('SoggettoBundle:Sede')->findOneById($id_sede);
		//controllo se sede a aziende sono coerenti si può migliorare ma al momento non mi viene niente in mente
		if(!$em->getRepository('SoggettoBundle:Sede')->isSedeAzienda($azienda->getId(), $sede->getId())) {
			$this->addFlash('error', "Accesso non autorizzato");
			return $this->redirect($this->generateUrl("home"));
		}
		$data = $funzioniService->getIndirizzoSedeOperativaAzienda($request, $sede->getIndirizzo());
		$options["dataIndirizzo"] = $data;
		$options["visualizzazione"] = true;
		$options["url_indietro"] = $this->generateUrl("elenco_sedi_operative_comune",array("id_soggetto"=>$id_soggetto));
		$form = $this->createForm(SedeType::class, $sede, $options);

		$form_params["form"] = $form->createView();
		$form_params["azienda"] = $azienda;

		return $form_params;
	}
}
