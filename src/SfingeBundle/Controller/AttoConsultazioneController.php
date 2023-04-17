<?php

namespace SfingeBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;

use SfingeBundle\Entity\Atto;

use SfingeBundle\Form\Entity\RicercaAtto;
use PaginaBundle\Annotations\Menuitem;

class AttoConsultazioneController extends BaseController {

	/**
	 * @Route("/visualizza_atto/{id_atto}", name="visualizza_atto")
	 * @Template("SfingeBundle:Atto:visualizzaAtto.html.twig")
	 * @PaginaInfo(titolo="Visualizza atto",sottoTitolo="pagina per visualizzare i dati dell'atto selezionato")
	 * @Menuitem(menuAttivo = "elencoAtti")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Gestione atti", route="elenco_atti",parametri={"id_atto"}), @ElementoBreadcrumb(testo="Visualizza Atto")})
	 */
	public function visualizzaAttoAction($id_atto) {
		$em = $this->getDoctrine()->getManager();
		$atto = $em->getRepository('SfingeBundle:Atto')->findOneById($id_atto);

		$options["readonly"] = true;
		$options["url_indietro"] = $this->generateUrl("elenco_atti");

		$form = $this->createForm('SfingeBundle\Form\AttoType', $atto, $options);

		$form_params["form"] = $form->createView();
		$form_params["atto"] = $atto;

		return $form_params;
	}

	/**
	 * @Route("/elenco_atti/{sort}/{direction}/{page}", defaults={"sort" = "a.id", "direction" = "asc", "page" = "1"}, name="elenco_atti")
	 * @Menuitem(menuAttivo = "elencoAtto")
	 * @PaginaInfo(titolo="Elenco atti", sottoTitolo="pagina per gestione degli atti")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco atti")})
	 */
	public function elencoAttiAction() {

		$datiRicerca = new RicercaAtto();
		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		return $this->render('SfingeBundle:Atto:elencoAtti.html.twig', array('atti' => $risultato["risultato"], "formRicercaAtto" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]
		));
	}

	/**
	 * @Route("/elenco_atti_pulisci", name="elenco_atti_pulisci")
	 */
	public function elencoAttiPulisciAction() {
		$this->get("ricerca")->pulisci(new RicercaAtto());
		return $this->redirectToRoute("elenco_atti");
	}

}
