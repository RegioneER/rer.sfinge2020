<?php

namespace SoggettoBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SoggettoBundle\Entity\IncaricoPersona;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Entity\TipoIncarico;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use SoggettoBundle\Entity\StatoIncarico;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use SoggettoBundle\Form\Entity\RicercaComuneUnione;
use Symfony\Component\Form\FormError;
use SoggettoBundle\Entity\ComuneUnione;
use BaseBundle\Annotation\ControlloAccesso;
use Symfony\Component\HttpFoundation\Request;

class ComuniUnioniConsultazioneController extends SoggettoBaseController {

	/**
	 * @Route("/elenco_comuni_unioni/{sort}/{direction}/{page}", defaults={"sort" = "a.id", "direction" = "asc", "page" = "1"}, name="elenco_comuni_unioni")
	 * @Menuitem(menuAttivo = "elencoSoggettiGiuridici")
	 * @PaginaInfo(titolo="Elenco comuni e Unioni dei comuni")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco comuni e unioni comuni")})
	 */
	public function elencoComuniUnioniAction() {
        return $this->redirectToRoute('elenco_soggetti_giuridici');
	}

	/**
	 * @Route("/elenco_comuni_unioni_pulisci", name="elenco_comuni_unioni_pulisci")
	 */
	public function elencoComuniUnioniPulisciAction() {
        return $this->redirectToRoute('elenco_soggetti_giuridici');
	}

	/**
	 * @Route("/comune_unione_visualizza/{id_soggetto}", name="comune_unione_visualizza")
	 * @Template("SoggettoBundle:Soggetto:comuneUnione.html.twig")
	 * @Menuitem(menuAttivo = "elencoSoggettiGiuridici")
	 * @PaginaInfo(titolo="Dettagli comune/unione",sottoTitolo="pagina per visualizzare i dati di un comune/unione")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco comuni/unioni", route="elenco_comuni_unioni"), @ElementoBreadcrumb(testo="Dettagli comune/unione")})
	 * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="show")
	 */
	public function visualizzaComuneUnioneAction(Request $request, $id_soggetto) {
		$em = $this->getDoctrine()->getManager();

		$funzioniService = $this->get('funzioni_utili');
		/** @var Soggetto $comuneUnione */
		$comuneUnione = $em->getRepository('SoggettoBundle:ComuneUnione')->findOneById($id_soggetto);
		$data = $funzioniService->getDataComuniFromRequestSedeLegale($request, $comuneUnione);

		$options["readonly"] = true;
		$options["dataIndirizzo"] = $data;
		$options["em"] = $this->getEm();
		$options["url_indietro"] = $this->generateUrl("elenco_soggetti_giuridici");
        $options["tipo"] = $comuneUnione->getTipoByFormaGiuridica();

		$form = $this->createForm('SoggettoBundle\Form\ComuneUnioneType', $comuneUnione, $options);

        if(!$comuneUnione->isFormaGiuridicaCoerente()) {
            $this->addFlash('warning', 'Attenzione! La forma giuridica indicata potrebbe non essere corretta.');
        }

		$form_params["form"] = $form->createView();
		$form_params["comuneUnione"] = $comuneUnione;

		return $form_params;
	}

}
