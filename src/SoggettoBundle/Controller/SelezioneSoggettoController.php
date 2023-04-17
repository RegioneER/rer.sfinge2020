<?php

namespace SoggettoBundle\Controller;

use BaseBundle\Controller\BaseController;
use Doctrine\Common\Collections\ArrayCollection;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Entity\TipoIncarico;
use SoggettoBundle\Form\Entity\RicercaIncaricati;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;

class SelezioneSoggettoController extends SoggettoBaseController {

	/**
	 * @Route("/{contesto}", name="selezione_soggetti" ,defaults={"contesto" = "tutti"})
	 * @PaginaInfo(titolo="Soggetti",sottoTitolo="")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Selezione soggetto")})
	 * @Menuitem(menuAttivo = "selezioneSoggetti")
	 */
	public function elencoSoggettiAction($contesto) {

		$soggetti = $this->getEm()->getRepository('SoggettoBundle:Soggetto')->cercaTuttiDaPersonaIncarico($this->getPersonaId());
		if (count($soggetti)) {
			//se ho un sola azienda faccio il redirect alla pagina di destinazione
			if (count($soggetti) == 1) {
				return $this->reindirizza($contesto, $soggetti[0]);
			}
		} else {
			$this->addFlash('warning', "Non è stato inserito nessun soggetto, si prega di farlo nella sezione Soggetti");
		}

		return $this->render('SoggettoBundle:Soggetto:selezioneSoggetto.html.twig', array(
					'soggetti' => $soggetti,
					'contesto' => $contesto
		));
	}

	/**
	 * Controller che in base al contesto mette in sessione il soggetto selezionato e fa il redirect
	 * @Route("/selezione/{contesto}/{id_soggetto}", name="seleziona_soggetto", defaults={"contesto" = "tutti"})
	 * @Menuitem(menuAttivo = "selezioneSoggetti")
	 * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione=\SoggettoBundle\Security\SoggettoVoter::SHOW)
	 */
	public function selezionaSoggettoAction($contesto, $id_soggetto) {
		$em = $this->getDoctrine()->getManager();

		$soggetto = $em->getRepository('SoggettoBundle:Soggetto')->find($id_soggetto);

		if (is_null($soggetto)) {
			throw new \Exception("Soggetto non trovato");
		}

		return $this->reindirizza($contesto, $soggetto);
	}

	private function reindirizza($contesto, Soggetto $soggetto) {

		//metto il soggetto in sessione
		$soggetto->setContesto($contesto);
		$this->getSession()->set(self::SESSIONE_SOGGETTO, $soggetto);
		$this->get("ricerca")->pulisci(new RicercaIncaricati());

		switch ($contesto) {
			case "persone":
				return $this->redirectToRoute("elenco_incarichi");
			case "richieste_elenco":
				return $this->redirectToRoute("elenco_richieste");
			case "richieste_nuova":
				return $this->redirectToRoute("selezione_bando");
			case "integrazioni_elenco":
				return $this->redirectToRoute("elenco_integrazioni");
			case "comunicazioni_elenco":
				return $this->redirectToRoute("elenco_comunicazioni_beneficiario");
			case "elenco_gestione_beneficiario":
				$this->get("ricerca")->pulisci(new RicercaIncaricati());
				return $this->redirectToRoute("elenco_gestione_beneficiario");
		}
	}

}
