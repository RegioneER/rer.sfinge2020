<?php

namespace FascicoloBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FascicoloBundle\Entity\IstanzaFascicolo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;

/**
 * @Route("/istanza")
 */
class DefaultController extends Controller
{
	
	/**
     * @Route("/crea-istanza-fascicolo/{id_fascicolo}", name="crea_istanza_fascicolo")
     */	
	public function creaIstanzaFascicoloAction($id_fascicolo) {
		$em = $this->getDoctrine()->getManager();
		
		$fascicolo = $em->getRepository("FascicoloBundle\Entity\Fascicolo")->findOneById($id_fascicolo);
		$istanzaFascicolo = new IstanzaFascicolo();
		$istanzaFascicolo->setFascicolo($fascicolo);
		
		$indice = new \FascicoloBundle\Entity\IstanzaPagina();
		$indice->setPagina($fascicolo->getIndice());
		$istanzaFascicolo->setIndice($indice);
		
		try {
			$em->persist($istanzaFascicolo);
			$em->flush();
		}catch (\Exception $e) {
			$this->addFlash('error', $e->getMessage());
		}

		return $this->redirect($this->generateUrl('istanza_pagina', array("id_istanza_pagina" => $indice->getId())));

	}
	
    /**
     * @Route("/istanza-pagina/{id_istanza_pagina}/{id_pagina}/{id_istanza_frammento}/{azione}", name="istanza_pagina", defaults={"id_istanza_pagina" = "-", "id_pagina" = "-","id_istanza_frammento" = "-", "azione" = "modifica"})
	 * @Menuitem(menuAttivo = "elenco_istanze")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco istanze", route="visualizza_istanze_fascicolo")})
     */
    public function istanzaPaginaAction(\Symfony\Component\HttpFoundation\Request $request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione)
    {
		return $this->get("fascicolo.istanza")->istanzaPagina($request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione);
    }
	
	/**
     * @Route("/visualizza-istanze-fascicolo", name="visualizza_istanze_fascicolo")
	 * @Template("FascicoloBundle:Default:visualizzaIstanzeFascicolo.html.twig")
	 * @PaginaInfo(titolo="Elenco istanze fascicoli")
	 * @Menuitem(menuAttivo = "elenco_istanze")
     */	
	public function visualizzaIstanzeFascicoloAction() {
		$em = $this->getDoctrine()->getManager();

		$istanzeFascicolo = $em->getRepository("FascicoloBundle\Entity\IstanzaFascicolo")->findAll();

		$param["istanzeFascicolo"] = $istanzeFascicolo;
        return $param;
	}

    /**
     * @Route("/visualizza-elenco-procedure", name="visualizza_elenco_procedure")
     * @Template("FascicoloBundle:Default:visualizzaProcedure.html.twig")
     * @PaginaInfo(titolo="Elenco procedure")
     * @Menuitem(menuAttivo = "elenco_istanze_per_bando")
     * @return mixed
     */
    public function visualizzaBandiAction() {
        $em = $this->getDoctrine()->getManager();
        $bandi = $em->getRepository("SfingeBundle:Procedura")->findAll();
        $param["procedure"] = $bandi;
        return $param;
    }

    /**
     * @param $id_procedura
     * @Route("/visualizza-istanze-fascicolo-per-bando/{id_procedura}/", name="visualizza_istanze_fascicolo_per_bando")
     * @Template("FascicoloBundle:Default:visualizzaIstanzeFascicoloPerBando.html.twig")
     * @Menuitem(menuAttivo = "elenco_istanze_per_bando")
     * @return mixed
     */
    public function visualizzaIstanzeFascicoloPerBandoAction($id_procedura) {
        $em = $this->getDoctrine()->getManager();
        $procedura = $em->getRepository("SfingeBundle:Procedura")->findOneById($id_procedura);
        $this->container->get('pagina')->setTitolo($procedura->getTitolo());
        $this->container->get('pagina')->setSottoTitolo('Elenco istanze fascicoli');
        $istanzeFascicolo = $em->getRepository("FascicoloBundle\Entity\IstanzaFascicolo")->getRichiesteBando($id_procedura);
        $param["istanzeFascicolo"] = $istanzeFascicolo;
        return $param;
    }
	
	/**
     * @Route("/elimina-istanza-fascicolo/{id_istanza_fascicolo}", name="elimina_istanza_fascicolo")
     */
	public function eliminaIstanzaFascicoloAction($id_istanza_fascicolo) {
		$this->get('base')->checkCsrf('token');
		$em = $this->getDoctrine()->getManager();
		$istanzaFascicolo = $em->getRepository("FascicoloBundle\Entity\IstanzaFascicolo")->find($id_istanza_fascicolo);
		
		if (!$istanzaFascicolo) {
            throw $this->createNotFoundException('Unable to find Istanza Fascicolo entity.');
        }

		$em->remove($istanzaFascicolo);

		// salvo i dati
		try {
			$em->flush();
			$this->addFlash('success', 'Istanza Fascicolo eliminata correttamente');
		} catch (\Exception $e) {
			$this->addFlash('error', $e->getMessage());
		}
		return $this->redirect($this->generateUrl('visualizza_istanze_fascicolo'));
	}	
	
	/**
     * @Route("/visualizza-istanza-fascicolo-flat/{id_istanza_fascicolo}", name="visualizza_istanza_fascicolo_flat")
	 * @PaginaInfo(titolo="Visualizza istanza fascicolo (flat)")
	 * @Menuitem(menuAttivo = "elenco_istanze")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco istanze", route="visualizza_istanze_fascicolo")})
     */
	public function visualizzaIstanzaFascicoloFlatAction($id_istanza_fascicolo) {
		$em = $this->getDoctrine()->getManager();
		$istanzaFascicolo = $em->getRepository("FascicoloBundle\Entity\IstanzaFascicolo")->find($id_istanza_fascicolo);
		
		if (!$istanzaFascicolo) {
            throw $this->createNotFoundException('Unable to find Istanza Fascicolo entity.');
        }
		
		$servizio = $this->get("fascicolo.istanza");
		
		$elenco = $servizio->elenca($istanzaFascicolo);
		
		// $a = $this->get("fascicolo.istanza")->getOne($istanzaFascicolo, "prova.form", true);

		return $this->render('FascicoloBundle:Default:visualizzaIstanzaFascicoloFlat.html.twig', array("elenco" => $elenco));
	}
	
	/**
     * @Route("/visualizza-istanza-fascicolo-tree/{id_istanza_fascicolo}", name="visualizza_istanza_fascicolo_tree")
	 * @PaginaInfo(titolo="Visualizza istanza fascicolo (tree)")
	 * @Menuitem(menuAttivo = "elenco_istanze")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco istanze", route="visualizza_istanze_fascicolo")})
     */
	public function visualizzaIstanzaFascicoloTreeAction($id_istanza_fascicolo) {
		$em = $this->getDoctrine()->getManager();
		$istanzaFascicolo = $em->getRepository("FascicoloBundle\Entity\IstanzaFascicolo")->find($id_istanza_fascicolo);
		
		if (!$istanzaFascicolo) {
            throw $this->createNotFoundException('Unable to find Istanza Fascicolo entity.');
        }
		
		// $servizio = $this->get("fascicolo.istanza");

		return $this->render('FascicoloBundle:Default:visualizzaIstanzaFascicoloTree.html.twig', array("istanzaFascicolo" => $istanzaFascicolo));
	}
}
