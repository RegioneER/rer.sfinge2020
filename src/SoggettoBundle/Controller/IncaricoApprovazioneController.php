<?php

namespace SoggettoBundle\Controller;


use SoggettoBundle\Entity\IncaricoPersona;
use SoggettoBundle\Entity\StatoIncarico;
use SoggettoBundle\Entity\TipoIncarico;
use SoggettoBundle\Form\Entity\RicercaIncaricatiGestione;
use SoggettoBundle\Form\Entity\RicercaPersonaIncaricabile;
use SoggettoBundle\Form\IncaricoType;
use SoggettoBundle\Form\RicercaPersonaIncaricabileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\PaginaInfo;
use Symfony\Component\HttpFoundation\Request;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;


class IncaricoApprovazioneController extends SoggettoBaseController {
	
	/**
	 * @Route("/boccia_incarico/{id_incarico}", name="boccia_incarico")
	 * @ControlloAccesso(contesto="incaricoPersona", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" = "id_incarico"})
	 */
	public function bocciaIncaricoAction($id_incarico) {
		$incarico = $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->findOneById($id_incarico);
		return $this->redirectToRoute("boccia_incarico_form", array("id_incarico" => $id_incarico));
	}

	/**
	 * @Route("/boccia_incarico_form/{id_incarico}", name="boccia_incarico_form")
	 * @Template("SoggettoBundle:Incarico:bocciaIncarico.html.twig")
	 * @Menuitem(menuAttivo = "elencoIncarichi")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Dettaglio incarico", route="dettaglio_incarico",parametri={"id_incarico"}), @ElementoBreadcrumb(testo="Boccia Incarico")})
	 * @PaginaInfo(titolo="Boccia incarico",sottoTitolo="")
	 * @ControlloAccesso(contesto="incaricoPersona", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" = "id_incarico"})
	 */
	public function bocciaIncaricoFormAction(Request $request, $id_incarico) {
		$em = $this->getDoctrine()->getManager();
		$incarico = $em->getRepository('SoggettoBundle:IncaricoPersona')->findOneById($id_incarico);

		if (is_null($incarico)) {
			return $this->addErrorRedirect("Incarico non trovato", "elenco_incarichi");
		}
		
		$soggetto = $incarico->getSoggetto();
		
		if (is_null($soggetto)) {
			return $this->addErrorRedirect("Soggetto non specificato", "elenco_incarichi");
		}

		if (!$incarico->getStato()->uguale(StatoIncarico::ATTESA_CONFERMA)) {
			return $this->addErrorRedirect("Incarico non bocciabile", "elenco_incarichi");
		}

		$options["url_indietro"] = $this->generateUrl("dettaglio_incarico",array("id_incarico" => $id_incarico));
		$form = $this->createForm('SoggettoBundle\Form\BocciaIncaricoType', $incarico, $options);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			$nota = $form->getData()->getNota();
			if (is_null($nota) || $nota == "") {
				$this->addFlash('error', "Inserire una nota");
                return $this->redirect($this->generateUrl("boccia_incarico_form", array("id_incarico" => $id_incarico)));
			}

			if ($form->isValid()) {
				try {
					$incarico->setStato($this->trovaDaCostante(new StatoIncarico(), StatoIncarico::BOCCIATO));
					$em->persist($incarico);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
					if (!$this->invioEmailBocciaIncarico($incarico)) {
						$this->addFlash("warning", "Incarico bocciato correttamente, ma non è stato possibile inviare la email");
					}
					return $this->redirect($this->generateUrl('elenco_incarichi'));
				} catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}
		
		$form_params["form"] = $form->createView();
		$form_params["incarico"] = $incarico;

		return $form_params;
	}

	/**
	 * @Route("/attiva_incarico/{id_incarico}", name="attiva_incarico")
	 * @ControlloAccesso(contesto="incaricoPersona", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" = "id_incarico"})
	 */
	public function attivaIncaricoAction($id_incarico) {
		$incarico = $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->findOneById($id_incarico);
		return $this->redirectToRoute("attiva_incarico_form", array("id_incarico" => $id_incarico));
	}

	/**
	 * @Route("/attiva_incarico_form/{id_incarico}", name="attiva_incarico_form")
	 * @Template("SoggettoBundle:Incarico:attivaIncarico.html.twig")
	 * @Menuitem(menuAttivo = "elencoIncarichi")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Dettaglio incarico", route="dettaglio_incarico",parametri={"id_incarico"}), @ElementoBreadcrumb(testo="Attiva Incarico")})
	 * @PaginaInfo(titolo="Attiva incarico",sottoTitolo="")
	 * @ControlloAccesso(contesto="incaricoPersona", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" = "id_incarico"})
	 */
	public function attivaIncaricoFormAction(Request $request, $id_incarico) {

		$em = $this->getDoctrine()->getManager();
		$incarico = $em->getRepository('SoggettoBundle:IncaricoPersona')->findOneById($id_incarico);
		$documento = $incarico->getIncaricato()->getCartaIdentita();

		$soggetto = $incarico->getSoggetto();
		if (is_null($soggetto)) {
			return $this->addErrorRedirect("Soggetto non specificato", "elenco_incarichi");
		}

		if (is_null($incarico)) {
			return $this->addErrorRedirect("Incarico non trovato", "elenco_incarichi");
		}

		if (!$incarico->getStato()->uguale(StatoIncarico::ATTESA_CONFERMA)) {
			return $this->addErrorRedirect("Incarico non bocciabile", "elenco_incarichi");
		}

		$options["url_indietro"] = $this->generateUrl("dettaglio_incarico",array("id_incarico" => $id_incarico));
		$form = $this->createForm('SoggettoBundle\Form\AttivaIncaricoType', null, $options);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			$data = $form->getData();
			if (is_null($data['data_scadenza']) || $data['data_scadenza'] == "") {
				$this->addFlash('error', "Inserire una data");
                return $this->redirect($this->generateUrl("attiva_incarico_form", array("id_incarico" => $id_incarico)));
			}

			if ($form->isValid()) {
				try {
					$em->beginTransaction();
					$documento->setDataScadenza($data['data_scadenza']);
					$incarico->setStato($this->trovaDaCostante(new StatoIncarico(), StatoIncarico::ATTIVO));
					$em->persist($incarico);
					$em->persist($documento);
					$em->flush();
					$em->commit();
					$this->addFlash('success', "Modifiche salvate correttamente");
					if (!$this->invioEmailAttivaIncarico($incarico)) {
						$this->addFlash("warning", "Incarico approvato correttamente, ma non è stato possibile inviare la email");
					}
					return $this->redirect($this->generateUrl('elenco_incarichi'));
				} catch (\Exception $e) {
					$em->rollback();
					$this->addFlash('error', $e->getMessage());
				}
			}
		}
		
		$form_params["form"] = $form->createView();
		$form_params["incarico"] = $incarico;

		return $form_params;

	}
	
	protected function invioEmailBocciaIncarico($_incaricoPersona) {

		$em = $this->getDoctrine()->getManager();
		$utente = $em->getRepository('SfingeBundle:Utente')->findOneByUsername($_incaricoPersona->getCreatoDa());

		$to = array();
		$to[] = $utente->getEmail();
		$subject = "Sfinge2020: non conferma ruolo";
		$parametriView = array("incarico" => $_incaricoPersona);
		$renderViewTwig = "SoggettoBundle:Incarico:bocciaIncarico.email.html.twig";
		$noHtmlViewTwig = "SoggettoBundle:Incarico:bocciaIncarico.email.twig";

		try {
			$esito = $this->get("messaggi.email")->inviaEmail($to, '', $subject, $renderViewTwig, $parametriView, $noHtmlViewTwig, $indirizzoAggiuntivo = null);
			$this->addFlash('success', "Email inviata con successo all'utente ");
			return true;
		} catch (\Exception $e) {
			$this->addFlash('danger', "Non è stato possibile inoltrare la Email : " . $e->getMEssage());
			return false;
		}
	}

	protected function invioEmailAttivaIncarico($_incaricoPersona) {
		
		$em = $this->getDoctrine()->getManager();
		$utente = $em->getRepository('SfingeBundle:Utente')->findOneByUsername($_incaricoPersona->getCreatoDa());

		$to = array();
		$to[] = $utente->getEmail();
		$subject = "Sfinge2020: conferma ruolo";
		$parametriView = array("incarico" => $_incaricoPersona);
		$renderViewTwig = "SoggettoBundle:Incarico:richiestaAttivazioneIncarico.email.html.twig";
		$noHtmlViewTwig = "SoggettoBundle:Incarico:richiestaAttivazioneIncarico.email.twig";

		try {
			$esito = $this->get("messaggi.email")->inviaEmail($to, '', $subject, $renderViewTwig, $parametriView, $noHtmlViewTwig, $indirizzoAggiuntivo = null);
			$this->addFlash('success', "Email inviata con successo all'utente ");
			return true;
		} catch (\Exception $e) {
			$this->addFlash('danger', "Non è stato possibile inoltrare la Email : " . $e->getMEssage());
			return false;
		}
	}	

}
