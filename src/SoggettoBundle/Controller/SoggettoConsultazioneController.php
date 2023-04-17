<?php

namespace SoggettoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SoggettoBundle\Form\Entity\RicercaSoggetto;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use BaseBundle\Annotation\ControlloAccesso;
use Symfony\Component\HttpFoundation\Request;

class SoggettoConsultazioneController extends SoggettoBaseController {

	/**
	 * @Route("/elenco_soggetti/{sort}/{direction}/{page}", defaults={"sort" = "s.id", "direction" = "asc", "page" = "1"}, name="elenco_soggetti")
	 * @Template("SoggettoBundle:Soggetto:elencoSoggetti.html.twig")
	 * @Menuitem(menuAttivo = "elencoSoggettiGiuridici")
	 * @PaginaInfo(titolo="Elenco altri soggetti",sottoTitolo="pagina per gestione dei soggetti censiti a sistema")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Soggetti")})
	 */
	public function elencoSoggettiAction() {
		return $this->redirectToRoute('elenco_soggetti_giuridici');
	}

	/**
	 * @Route("/elenco_soggetti_pulisci", name="elenco_soggetti_pulisci")
	 */
	public function elencoSoggettiPulisciAction() {
        return $this->redirectToRoute('elenco_soggetti_giuridici');
	}

	/**
	 * @Route("/soggetto_visualizza/{id_soggetto}", name="soggetto_visualizza")
	 * @Template("SoggettoBundle:Soggetto:soggetto.html.twig")
	 * @PaginaInfo(titolo="Visualizza soggetto",sottoTitolo="pagina per visualizzare i dati del soggetto selezionato")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Soggetti", route="elenco_soggetti"), @ElementoBreadcrumb(testo="visualizza soggetto")})
	 * @Menuitem(menuAttivo = "elencoSoggettiGiuridici")
	 * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="show")
	 */
	public function visualizzaSoggettoAction(Request $request, $id_soggetto) {

		$em = $this->getDoctrine()->getManager();
		$funzioniService = $this->get('funzioni_utili');
		$soggetto = $em->getRepository('SoggettoBundle:Soggetto')->findOneById($id_soggetto);
		$data = $funzioniService->getDataComuniFromRequestSedeLegale($request, $soggetto);

		$options["readonly"] = true;
		$options["dataIndirizzo"] = $data;
		$options["em"] = $this->getEm();
		$options["url_indietro"] = $this->generateUrl("elenco_soggetti_giuridici");
        $options["tipo"] = $soggetto->getTipoByFormaGiuridica();

		$form = $this->createForm('SoggettoBundle\Form\SoggettoType', $soggetto, $options);

		$form_params["form"] = $form->createView();
		$form_params["azienda"] = $soggetto;

		return $form_params;
	}

}
