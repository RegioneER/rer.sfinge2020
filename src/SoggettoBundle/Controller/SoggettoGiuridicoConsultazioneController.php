<?php

namespace SoggettoBundle\Controller;


use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Form\Entity\RicercaSoggettoGiuridico;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use BaseBundle\Annotation\ControlloAccesso;
use Symfony\Component\HttpFoundation\Request;

class SoggettoGiuridicoConsultazioneController extends SoggettoBaseController {

	/**
	 * @Route("/elenco_soggetti_giuridici/{sort}/{direction}/{page}", defaults={"sort" = "s.id", "direction" = "asc", "page" = "1"}, name="elenco_soggetti_giuridici")
	 * @Template("SoggettoBundle:Soggetto:elencoSoggettiGiuridici.html.twig")
	 * @Menuitem(menuAttivo = "elencoSoggettiGiuridici")
	 * @PaginaInfo(titolo="Elenco Soggetti Giuridici",sottoTitolo="pagina per gestione dei soggetti giuridici censiti a sistema")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Soggetti Giuridici")})
	 */
	public function elencoSoggettiGiuridiciAction() {
		$datiRicerca = new RicercaSoggettoGiuridico();
		if ($this->isUtente()) {
			$datiRicerca->setPersonaId($this->getPersonaId());
		}

		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		return array('soggetti' => $risultato["risultato"], "form_ricerca_soggetti" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]);
	}

	/**
	 * @Route("/elenco_soggetti_giuridici_pulisci", name="elenco_soggetti_giuridici_pulisci")
	 */
	public function elencoSoggettiGiuridiciPulisciAction() {
		$this->get("ricerca")->pulisci(new RicercaSoggettoGiuridico());
		return $this->redirectToRoute("elenco_soggetti_giuridici");
	}

	/**
	 * @Route("/soggetto_giuridico_visualizza/{id_soggetto}", name="soggetto_giuridico_visualizza")
	 * @Template("SoggettoBundle:Soggetto:soggetto.html.twig")
	 * @PaginaInfo(titolo="Visualizza soggetto",sottoTitolo="pagina per visualizzare i dati del soggetto selezionato")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Soggetti", route="elenco_soggetti"), @ElementoBreadcrumb(testo="visualizza soggetto")})
	 * @Menuitem(menuAttivo = "elencoSoggetti")
	 * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="show")
	 */
	public function visualizzaSoggettoGiuridicoAction(Request $request, $id_soggetto) {

		$em = $this->getDoctrine()->getManager();
		$funzioniService = $this->get('funzioni_utili');

		/** @var Soggetto $soggetto */
		$soggetto = $em->getRepository('SoggettoBundle:Soggetto')->findOneById($id_soggetto);
		$data = $funzioniService->getDataComuniFromRequestSedeLegale($request, $soggetto);

		$options["readonly"] = true;
		$options["dataIndirizzo"] = $data;
		$options["em"] = $this->getEm();
		$options["url_indietro"] = $this->generateUrl("elenco_soggetti_giuridici");

		$form = $this->createForm('SoggettoBundle\Form\SoggettoType', $soggetto, $options);

        if(!$soggetto->isFormaGiuridicaCoerente()) {
            $this->addFlash('warning', 'Attenzione! La forma giuridica indicata potrebbe non essere corretta.');
        }

		$form_params["form"] = $form->createView();
		$form_params["azienda"] = $soggetto;

		return $form_params;
	}

}
