<?php

namespace AttuazioneControlloBundle\Controller\Istruttoria;

use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use DocumentoBundle\Entity\DocumentoFile;
use IstruttorieBundle\Entity\DocumentoIstruttoria;
use Symfony\Component\HttpFoundation\Request;
use AttuazioneControlloBundle\Form\Entity\RicercaAttoLiquidazione;
/**
 * @Route("/istruttoria/atti_liquidazione")
 */
class AttiLiquidazioneController extends BaseController {

	/**
	 * @Route("/elenco/{sort}/{direction}/{page}", defaults={"sort" = "a.id", "direction" = "asc", "page" = "1"}, name="elenco_atti_liquidazione")
	 * @Menuitem(menuAttivo = "elencoAttoLiquidazione")
	 * @PaginaInfo(titolo="Elenco atti liquidazione", sottoTitolo="pagina per gestione degli atti di liquidazione")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco atti liquidazione")})
	 */
	public function elencoAttiLiquidazionAction() {

		$datiRicerca = new \AttuazioneControlloBundle\Form\Entity\RicercaAttoLiquidazione();
		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		return $this->render('AttuazioneControlloBundle:AttoLiquidazione:elencoAtti.html.twig', array('atti' => $risultato["risultato"], "formRicercaAtto" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]));
	}

	/**
	 * @Route("/crea_atto", name="crea_atto_liquidazione")
	 * @Template("AttuazioneControlloBundle:AttoLiquidazione:atto.html.twig")
	 * @PaginaInfo(titolo="Nuovo Atto",sottoTitolo="pagina per creare un nuovo atto")
	 * @Menuitem(menuAttivo = "elencoAttoLiquidazione")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco atti liquidazione", route="elenco_atti_liquidazione"), 
	 *                       @ElementoBreadcrumb(testo="Crea Atto")})
	 */
	public function creaAttoAction() {

		$em = $this->getDoctrine()->getManager();
		$atto = new \AttuazioneControlloBundle\Entity\AttoLiquidazione();
		$request = $this->getCurrentRequest();
		$options["readonly"] = false;
		$options["url_indietro"] = $this->generateUrl("elenco_atti_liquidazione");
		$options["mostra_indietro"] = false;
		$form = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\AttoLiquidazioneType', $atto, $options);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {
					$documento = $atto->getDocumento();
					$this->get("documenti")->carica($documento);

					$em->persist($atto);
					$em->flush();
					$this->addFlash('success', "Atto di liquidazione caricato correttamente");

					return $this->redirect($this->generateUrl('elenco_atti_liquidazione'));
				} catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		$form_params["form"] = $form->createView();
		$form_params["atto"] = $atto;

		return $form_params;
	}

	/**
	 * @Route("/visualizza_atto/{id_atto}", name="visualizza_atto_liquidazione")
	 * @Template("AttuazioneControlloBundle:AttoLiquidazione:atto.html.twig")
	 * @PaginaInfo(titolo="Visualizza atto",sottoTitolo="pagina per visualizzare i dati dell'atto selezionato")
	 * @Menuitem(menuAttivo = "elencoAttoLiquidazione")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco atti liquidazione", route="elenco_atti_liquidazione"), 
	 *                       @ElementoBreadcrumb(testo="Visualizza Atto")})
	 */
	public function visualizzaAttoAction($id_atto) {
		$em = $this->getDoctrine()->getManager();
		$atto = $em->getRepository('AttuazioneControlloBundle:AttoLiquidazione')->find($id_atto);

		$options["readonly"] = true;
		$options["url_indietro"] = $this->generateUrl("elenco_atti_liquidazione");

		$form = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\AttoLiquidazioneType', $atto, $options);

		if (is_null($atto->getDocumento())) {
			$path = null;
		} else {
			$path = $this->container->get("funzioni_utili")->encid($atto->getDocumento()->getPath() . $atto->getDocumento()->getNome());
		}

		$form_params["form"] = $form->createView();
		$form_params["atto"] = $atto;
		$form_params["path"] = $path;
		$form_params["mode"] = "show";

		return $form_params;
	}

	/**
	 * @Route("/modifica_atto/{id_atto}", name="modifica_atto_liquidazione")
	 * @Template("AttuazioneControlloBundle:AttoLiquidazione:atto.html.twig")
	 * @PaginaInfo(titolo="Modifica Atto",sottoTitolo="pagina per modificare un atto")
	 * @Menuitem(menuAttivo = "elencoAttoLiquidazione")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco atti liquidazione", route="elenco_atti_liquidazione"), 
	 *                       @ElementoBreadcrumb(testo="Modifica Atto")})
	 */
	public function modificaAttoAction(Request $request, $id_atto) {
		$em = $this->getDoctrine()->getManager();
		$atto = $em->getRepository('AttuazioneControlloBundle:AttoLiquidazione')->find($id_atto);

		$options["readonly"] = false;
		$options["url_indietro"] = $this->generateUrl("elenco_atti_liquidazione");

		$form = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\AttoLiquidazioneType', $atto, $options);
		if (!is_null($atto->getDocumento())) {
			$form->remove('documento');
		}

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {
					$em->beginTransaction();
					$documento = $atto->getDocumento();
					if (!is_null($documento->getFile())) {
						$this->get("documenti")->carica($documento);
					} else {
						$atto->setDocumento($documento);
					}

					$em->persist($atto);
					$em->flush();
					$em->commit();
					$this->addFlash('success', "Modifiche salvate correttamente");

					return $this->redirect($this->generateUrl('elenco_atti_liquidazione'));
				} catch (\Exception $e) {
					$em->rollback();
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		if (is_null($atto->getDocumento()) || is_null($atto->getDocumento()->getPath())) {
			$path = null;
		} else {
			$path = $this->container->get("funzioni_utili")->encid($atto->getDocumento()->getPath() . $atto->getDocumento()->getNome());
		}

		$form_params["form"] = $form->createView();
		$form_params["atto"] = $atto;
		$form_params["path"] = $path;

		return $form_params;
	}

	/**
	 * @Route("/elimina_documento_atto_liquidazione/{id_atto}", name="elimina_documento_atto_liquidazione")
	 */
	public function eliminaDocumentoAttoAction($id_atto) {
		$this->get('base')->checkCsrf('token');
		$em = $this->getEm();
		$atto = $em->getRepository('AttuazioneControlloBundle:AttoLiquidazione')->find($id_atto);

		try {
			$em->remove($atto->getDocumento());
			$atto->setDocumento(null);
			$em->flush();
			$this->addFlash("success", "Il documento è stato correttamente eliminato");
			return $this->redirect($this->generateUrl("modifica_atto_liquidazione", array("id_atto" => $id_atto)));
		} catch (\Exception $e) {
			$this->addFlash("error", "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza");
			return $this->redirect($this->generateUrl("modifica_atto_liquidazione", array("id_atto" => $id_atto)));
		}
	}

	/**
	 * @Route("/elenco_atti_liquidazione_pulisci", name="elenco_atti_liquidazione_pulisci")
	 */
	public function elencoAttiLiquidazionePulisciAction() {
		$this->get("ricerca")->pulisci(new RicercaAttoLiquidazione());
		return $this->redirectToRoute("elenco_atti_liquidazione");
	}

}
