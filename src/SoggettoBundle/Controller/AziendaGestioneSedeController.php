<?php

namespace SoggettoBundle\Controller;

use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
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
use PaginaBundle\Annotations\Menuitem;
use RichiesteBundle\Service\GestoreResponse;
use DocumentoBundle\Component\ResponseException;

/**
 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Soggetti Giuridici", route="elenco_soggetti_giuridici")})
 */
class AziendaGestioneSedeController extends Controller {
	
	/**
	 * @Route("/aggiungi/{id_soggetto}", name="azienda_aggiungi_sede_operativa")
	 * @Template("SoggettoBundle:Sede:sede.html.twig")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Gestione sedi", route="elenco_sedi_operative",parametri={"id_soggetto"}), @ElementoBreadcrumb(testo="Aggiunta sede")})
	 * @PaginaInfo(titolo="Azienda - Aggiunta sede")
	 * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="edit")
	 */
	public function aggungiSedeOperativaAction(Request $request, $id_soggetto) {

		$em = $this->getDoctrine()->getManager();
		$funzioniService = $this->get('funzioni_utili');
		$azienda = $em->getRepository('SoggettoBundle:Azienda')->findOneById($id_soggetto);
				
		$sede = new Sede();
		$data = $funzioniService->getIndirizzoSedeOperativaAzienda($request, $sede->getIndirizzo());

		$options["dataIndirizzo"] = $data;
		$options["url_indietro"] = $this->generateUrl("elenco_sedi_operative", array("id_soggetto"=>$id_soggetto));
        $options["validation_groups"] = ["Default", "persona", "sede"];
		$form = $this->createForm("SoggettoBundle\Form\SedeType", $sede, $options);

		if ($request->isMethod('POST')) {

			$form->handleRequest($request);

			if ($form->isValid()) {

				$sede->setSoggetto($azienda);
				
				try {
					$em->persist($sede);
					$em->flush();

					$this->addFlash('success', "Modifiche salvate correttamente");

					return $this->redirect($this->generateUrl('elenco_sedi_operative', array('id_soggetto' => $azienda->getId())));
				} catch (ResponseException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		$form_params["form"] = $form->createView();
		$form_params["azienda"] = $azienda;

		return $form_params;
	}
	
	/**
	 * @Route("{id_richiesta}/aggiungi_rich/{id_proponente}", name="azienda_aggiungi_sede_operativa_rich")
	 * @Template("SoggettoBundle:Sede:sede.html.twig")
	 * @PaginaInfo(titolo="Aggiunta sede",sottoTitolo="Aggiungi una sede")
	 * @Breadcrumb(elementi={
	 * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
	 * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
	 * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti", parametri={"id_richiesta"}),
	 * 				@ElementoBreadcrumb(testo="Dettagli proponente", route="dettaglio_proponente", parametri={"id_richiesta", "id_proponente"}),
	 * 				@ElementoBreadcrumb(testo="Aggiunta sede")
	 * 				})
	 * @Menuitem(menuAttivo = "elencoRichieste")
	 * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" = "id_proponente"})
	 * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
	 */
	public function aggiungiSedeOperativaDaRichiestaAction(Request $request, $id_richiesta, $id_proponente) {
		$em = $this->getDoctrine()->getManager();
		$funzioniService = $this->get('funzioni_utili');
		/** @var Proponente $proponente */
		$proponente = $em->getRepository('RichiesteBundle:Proponente')->find($id_proponente);
		$azienda = $proponente->getSoggetto();
		/** @var Richiesta $richiesta */
		$richiesta = $proponente->getRichiesta();
		$sede = new Sede();
		$data = $funzioniService->getIndirizzoSedeOperativaAzienda($request, $sede->getIndirizzo());
		$indietro = $request->query->has('refer') ? 
			$request->query->get('refer') :
			$this->generateUrl("cerca_sede", ["id_richiesta"=>$id_richiesta, 'id_proponente' => $id_proponente]);
		$options["dataIndirizzo"] = $data;
		$options["url_indietro"] = $indietro;

		// Per il bando centri storici ho un type a parte
		if ($richiesta->getProcedura()->isBandoCentriStoriciColpitiDalSisma()) {
            $formType = 'RichiesteBundle\Form\Bando95\SedeType';
        } elseif ($richiesta->getProcedura()->getId() == 154) {
            $formType = 'RichiesteBundle\Form\Bando154\SedeType';
		} else {
			$formType = 'SoggettoBundle\Form\SedeType';
		}
		
		$form = $this->createForm($formType, $sede, $options);

		if ($request->isMethod('POST')) {

			$form->handleRequest($request);

			if ($form->isValid()) {

				$sede->setSoggetto($azienda);
				
				try {
					$em->persist($sede);
					$em->flush();

					$this->addFlash('success', "Modifiche salvate correttamente");

					return $this->redirect($indietro);
				} catch (ResponseException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}

		$form_params["form"] = $form->createView();
		$form_params["azienda"] = $azienda;

		if ($richiesta->getProcedura()->getNumeroProponenti() == 1) {
            $this->container->get("pagina")->resettaBreadcrumb();
            $this->container->get("pagina")->aggiungiElementoBreadcrumb('Elenco richieste', $this->generateUrl('elenco_richieste'));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb('Dettaglio richiesta', $this->generateUrl("dettaglio_richiesta", ['id_richiesta' => $richiesta->getId()]));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb('Dettaglio proponente', $this->generateUrl("dettaglio_proponente", ['id_richiesta' => $richiesta->getId(), 'id_proponente' => $id_proponente]));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb('Elenco sedi', $this->generateUrl("cerca_sede", ['id_richiesta' => $richiesta->getId(), 'id_proponente' => $id_proponente]));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb('Aggiungi sede');
        }

		return $form_params;
	}
}
