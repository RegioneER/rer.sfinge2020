<?php

namespace SoggettoBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SoggettoBundle\Entity\Azienda;
use SoggettoBundle\Entity\IncaricoPersona;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Entity\TipoIncarico;
use SoggettoBundle\Form\AziendaType;
use SoggettoBundle\Form\RicercaSoggettoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use SoggettoBundle\Entity\StatoIncarico;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use SoggettoBundle\Form\Entity\RicercaAzienda;
use Symfony\Component\Form\FormError;
use BaseBundle\Annotation\ControlloAccesso;
use Symfony\Component\HttpFoundation\Request;

class AziendaConsultazioneController extends SoggettoBaseController {


	/**
	 * @Route("/elenco_aziende/{sort}/{direction}/{page}", defaults={"sort" = "a.id", "direction" = "asc", "page" = "1"}, name="elenco_aziende")
	 * @Menuitem(menuAttivo = "elencoSoggettiGiuridici")
	 * @PaginaInfo(titolo="Elenco aziende",sottoTitolo="pagina per gestione delle aziende censite a sistema")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco aziende")})
	 */
	public function elencoAziendeAction() {
	    return $this->redirectToRoute('elenco_soggetti_giuridici');
    }

	/**
	 * @Route("/elenco_aziende_pulisci", name="elenco_aziende_pulisci")
	 */
	public function elencoAziendePulisciAction() {
        return $this->redirectToRoute('elenco_soggetti_giuridici');
	}
	
	/**
	 * @Route("/azienda_visualizza/{id_soggetto}", name="azienda_visualizza")
	 * @Template("SoggettoBundle:Soggetto:azienda.html.twig")
	 * @Menuitem(menuAttivo = "elencoSoggettiGiuridici")
	 * @PaginaInfo(titolo="Dettagli azienda",sottoTitolo="pagina per visualizzare i dati di un'azienda")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco aziende", route="elenco_aziende"), @ElementoBreadcrumb(testo="Dettagli azienda")})
	 * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="show")
	 */
	public function visualizzaAziendaAction(Request $request, $id_soggetto) {
		$em = $this->getDoctrine()->getManager();

		$funzioniService = $this->get('funzioni_utili');

		/** @var Soggetto $azienda */
		$azienda = $em->getRepository('SoggettoBundle:Azienda')->findOneById($id_soggetto);
		$data = $funzioniService->getDataComuniFromRequestSedeLegale($request, $azienda);

		$options["readonly"] = true;
		$options["dataIndirizzo"] = $data;
		$options["em"] = $this->getEm();
		$options["url_indietro"] = $this->generateUrl("elenco_soggetti_giuridici");
        $options["tipo"] = $azienda->getTipoByFormaGiuridica();

		$form = $this->createForm('SoggettoBundle\Form\AziendaType', $azienda, $options);

        if(!$azienda->isFormaGiuridicaCoerente()) {
            $this->addFlash('warning', 'Attenzione! La forma giuridica indicata potrebbe non essere corretta.');
        }

		$form_params["form"] = $form->createView();
		$form_params["azienda"] = $azienda;

		return $form_params;
	}

}
