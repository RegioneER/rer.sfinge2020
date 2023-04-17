<?php

namespace SoggettoBundle\Controller;

use BaseBundle\Controller\BaseController;
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
use DocumentoBundle\Component\ResponseException;

class AllegatiOrganismiIntermediController extends BaseController {
	
	/**
	 * @Route("/elenco/{id_asse}", name="elenco_autorita_urbane")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Autorità Urbane")})
	 * @PaginaInfo(titolo="Elenco Autorità Urbane")
	 * ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="show")
	 */
	public function elencoAutoritaUrbaneAction($id_asse = null) {
		
		$this->get("pagina")->setMenuAttivo('asseOrganismoIntermedio' . $id_asse, $this->getCurrentRequest()->getSession());
		
		$em = $this->getDoctrine()->getManager();
		$asse = $em->getRepository('SfingeBundle:Asse')->findOneById($id_asse);		
		return $this->render('SoggettoBundle:AutoritaUrbane:elencoAutoritaUrbane.html.twig', array('asse' => $asse));
	}
	
	/**
	 * @Route("/gestione_allegati/{id_asse}/{id_autorita_urbana}/{id_azione_au}", name="gestione_allegati_autorita_urbane")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Autorità Urbane", route="elenco_autorita_urbane",parametri={"id_asse"}), @ElementoBreadcrumb(testo="Gestione allegati")})
	 * @PaginaInfo(titolo="Gestione allegati alle azioni di Autorità Urbane")
	 * ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="show")
	 */
	public function gestioneAlleagatiAutoritaUrbaneAction($id_asse, $id_autorita_urbana, $id_azione_au) {
		
		$this->get("pagina")->setMenuAttivo('asseOrganismoIntermedio' . $id_asse, $this->getCurrentRequest()->getSession());
		
		$em = $this->getDoctrine()->getManager();
		$request = $this->getCurrentRequest();
		
		$asse            = $em->getRepository('SfingeBundle:Asse')->findOneById($id_asse);
		$autorita_urbana = $em->getRepository('SoggettoBundle:AutoritaUrbana')->findOneById($id_autorita_urbana);
		$azione_au       = $em->getRepository('SoggettoBundle:AzioneAutoritaUrbana')->findOneById($id_azione_au);
		
		$allegato       = new \SoggettoBundle\Entity\AllegatiAzioniAutoritaUrbane();
		$documento_file = new \DocumentoBundle\Entity\DocumentoFile();

		$documenti_caricati = $em->getRepository("SoggettoBundle\Entity\AllegatiAzioniAutoritaUrbane")->findDocumentiCaricati($id_azione_au);
		
		$tipo_doc = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array('codice' => "AA_AUTURB"));

		$opzioni_form['tipo'] = $tipo_doc;

		$form = $this->createForm("DocumentoBundle\Form\Type\DocumentoFileType", $documento_file, $opzioni_form);
		$form->add("submit", "Symfony\Component\Form\Extension\Core\Type\SubmitType", array("label" => "Salva"));
				
		if ($request->isMethod("POST")) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {

					$this->container->get("documenti")->carica($documento_file, 0);

					$allegato->setDocumentoFile($documento_file);
					$allegato->setAzioneAutoritaUrbana($azione_au);
					$em->persist($allegato);

					$em->flush();
					return $this->addSuccessRedirect("Documento caricato correttamente", "gestione_allegati_autorita_urbane",
							array(
								'id_asse'            => $id_asse,
								'id_autorita_urbana' => $id_autorita_urbana,
								'id_azione_au'       => $id_azione_au,
							));
				} catch (ResponseException $e) {
					$this->addFlash("error", $e->getMessage());
				}
			}
		}
		$form_view = $form->createView();		
				
		return $this->render('SoggettoBundle:AutoritaUrbane:gestioneAllegato.html.twig',
				array(
					'asse'            => $asse,
					'autorita_urbana' => $autorita_urbana,
					'azione_au'       => $azione_au,
					'form'            => $form_view,
					'documenti'       => $documenti_caricati,
					)
				);
	}
	
	/**
	 * @Route("/cancella_documenti/{id_documento}", name="cancella_allegato_au")
	 */
	public function cancellaDocumentoAction($id_documento) {
		
		$em = $this->getEm();
		$allegato = $em->getRepository("SoggettoBundle\Entity\AllegatiAzioniAutoritaUrbane")->find($id_documento);
		$azione_autorita_urbana = $allegato->getAzioneAutoritaUrbana();
		$autorita_urbana        = $azione_autorita_urbana->getAutoritaUrbana();
		$asse                   = $autorita_urbana->getAsse();
		try {
			$em->remove($allegato->getDocumentoFile());
			$em->remove($allegato);
			$em->flush();
			return $this->addSuccessRedirect("Documento eliminato correttamente", "gestione_allegati_autorita_urbane",
					array(
						'id_asse'            => $asse->getId(),
						'id_autorita_urbana' => $autorita_urbana->getId(),
						'id_azione_au'       => $azione_autorita_urbana->getId(),
					));
		} catch (ResponseException $e) {
			$this->addFlash('error', $e->getMessage());
		}
		
	}		
	
}
