<?php

namespace FascicoloBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use FascicoloBundle\Entity\Fascicolo;
use FascicoloBundle\Entity\Pagina;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FascicoloBundle\Form\Type\PaginaType;
use FascicoloBundle\Entity\Frammento;
use FascicoloBundle\Form\Type\FrammentoType;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Fascicoli", route="visualizza_fascicoli")})
 * @Route("/frammento")
 */
class FrammentoController extends Controller
{
  	
	/**
     * @Route("/crea-frammento/{id_pagina}", name="crea_frammento")
	 * @Template("FascicoloBundle:Frammento:creaFrammento.html.twig")
	 * @PaginaInfo(titolo="Crea Frammento")
	 * @Menuitem(menuAttivo = "visualizza_fascicoli")
     */	
	public function creaFrammentoAction(Request $request, $id_pagina) {
		$em = $this->getDoctrine()->getManager();
		
		$pagina = $em->getRepository("FascicoloBundle\Entity\Pagina")->findOneById($id_pagina);
		$ordinamentoMaggiore = $em->getRepository("FascicoloBundle\Entity\Frammento")->getOrdinamentoMaggiore($id_pagina);
		
		$frammento = new Frammento();
		$frammento->setPagina($pagina);
		$frammento->setOrdinamento($ordinamentoMaggiore+1);
		
		$this->get('fascicolo')->creaBreadcrumbFrammento($frammento); 
	
		$form = $this->createForm(new FrammentoType(), $frammento);
		
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			
			$alias = $frammento->getAlias();
			$frammentoVerifica = $em->getRepository("FascicoloBundle\Entity\Frammento")->findOneBy(array('alias'=>$alias,'pagina'=>$frammento->getPagina()->getId()));
			if ($frammentoVerifica) {
				$form->get('alias')->addError(new \Symfony\Component\Form\FormError('L\'alias specificato è già presente a sistema all\'interno della pagina'));
			}

			if ($form->isValid()) {
				try {
					$em->persist($frammento);
					$em->flush();
					$this->addFlash('success', "Frammento creato correttamente");
					return $this->redirect($this->generateUrl('modifica_frammento', array("id_frammento" => $frammento->getId())));
				}catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}
		
		$form_params["form"] = $form->createView();
		return $form_params;
	}

	/**
	 * @Route("/modifica-frammento/{id_frammento}", name="modifica_frammento")
	 * @Template("FascicoloBundle:Frammento:modificaFrammento.html.twig")
	 * @PaginaInfo(titolo="Modifica Frammento")
	 * @Menuitem(menuAttivo = "visualizza_fascicoli")
	 */
	public function modificaFrammentoAction(Request $request, $id_frammento) {
		
		$em = $this->getDoctrine()->getManager();
		$frammento = $em->getRepository("FascicoloBundle\Entity\Frammento")->findOneById($id_frammento);
		
		$this->get('fascicolo')->creaBreadcrumbFrammento($frammento); 
		
		$form = $this->createForm(new FrammentoType(true), $frammento);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			$alias = $frammento->getAlias();
			$frammentoVerifica = $em->getRepository("FascicoloBundle\Entity\Frammento")->findOneBy(array('alias'=>$alias,'pagina'=>$frammento->getPagina()->getId()));
			if ($frammentoVerifica && $frammentoVerifica->getId()!=$frammento->getId()) {
				$form->get('alias')->addError(new \Symfony\Component\Form\FormError('L\'alias specificato è già presente a sistema all\'interno della pagina'));
			}
			if ($form->isValid()) {
				try {
					$em->persist($frammento);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
				} catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}
		$form_params["form"] = $form->createView();
		$form_params["id_frammento"] = $frammento->getId();
		return $form_params;
	}
	
	/**
	 * @Route("/sposta-sopra-frammento/{id_frammento}", name="sposta_sopra_frammento") 
	 */
	public function spostaSopraFrammento($id_frammento) {
		$em = $this->getDoctrine()->getManager();
        $frammento = $em->getRepository("FascicoloBundle\Entity\Frammento")->findOneById($id_frammento);
		$ordinamento = $frammento->getOrdinamento();
		$frammentoOrdinamentoMaggiore = $em->getRepository("FascicoloBundle\Entity\Frammento")->getFrammentoOrdinamento($frammento->getPagina()->getId(),$ordinamento-1);
		if(!is_null($frammentoOrdinamentoMaggiore)){
			try {
					$frammento->setOrdinamento($ordinamento-1);
					$frammentoOrdinamentoMaggiore->setOrdinamento($ordinamento);
					$em->persist($frammento);
					$em->persist($frammentoOrdinamentoMaggiore);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
				} catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
		}
		$id_pagina = $frammento->getPagina()->getId();
        return $this->redirectToRoute('modifica_pagina', array('id_pagina' => $id_pagina)); 
	}
	
	/**
	 * @Route("/sposta-sotto-frammento/{id_frammento}", name="sposta_sotto_frammento") 
	 */
	public function spostaSottoFrammento($id_frammento) {
		$em = $this->getDoctrine()->getManager();
        $frammento = $em->getRepository("FascicoloBundle\Entity\Frammento")->findOneById($id_frammento);
		$ordinamento = $frammento->getOrdinamento();
		$frammentoOrdinamentoMinore = $em->getRepository("FascicoloBundle\Entity\Frammento")->getFrammentoOrdinamento($frammento->getPagina()->getId(),$ordinamento+1);
		if(!is_null($frammentoOrdinamentoMinore)){
			try {
					$frammento->setOrdinamento($ordinamento+1);
					$frammentoOrdinamentoMinore->setOrdinamento($ordinamento);
					$em->persist($frammento);
					$em->persist($frammentoOrdinamentoMinore);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
				} catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
		}
		$id_pagina = $frammento->getPagina()->getId();
        return $this->redirectToRoute('modifica_pagina', array('id_pagina' => $id_pagina)); 
	}
	
	 /**
     * @Route("/elimina-frammento/{id_frammento}", name="elimina_frammento")
     */
    public function eliminaFrammentoAction($id_frammento)
    {
		$this->get('base')->checkCsrf('token');
        $em = $this->getDoctrine()->getManager();
        $frammento = $em->getRepository("FascicoloBundle\Entity\Frammento")->findOneById($id_frammento);

        if (!$frammento) {
            throw $this->createNotFoundException('Unable to find Frammento entity.');
        }
		try{
			$em->remove($frammento);
			$em->flush();
			$this->addFlash('success', "Frammento eliminato correttamente");
		} catch (\Exception $ex) {
			$this->addFlash('error', $ex->getMessage());
		}

		$id_pagina = $frammento->getPagina()->getId();
        return $this->redirectToRoute('modifica_pagina', array('id_pagina' => $id_pagina));
    }
	
	/**
     * @Route("/crea-sotto-pagina/{id_frammento}", name="crea_sotto_pagina")
	 * @Template("FascicoloBundle:Frammento:creaSottoPagina.html.twig")
	 * @PaginaInfo(titolo="Crea Pagina")
	 * @Menuitem(menuAttivo = "visualizza_fascicoli")
     */	
	public function creaSottoPaginaAction(Request $request, $id_frammento) {
		$em = $this->getDoctrine()->getManager();
		
		$frammento = $em->getRepository("FascicoloBundle\Entity\Frammento")->findOneById($id_frammento);
		$ordinamentoMaggiore = $em->getRepository("FascicoloBundle\Entity\Pagina")->getOrdinamentoSottoPaginaMaggiore($id_frammento);
		
		$pagina = new Pagina();
		$pagina->setOrdinamento($ordinamentoMaggiore+1);
		$frammento->addSottoPagina($pagina);
		
		$this->get('fascicolo')->creaBreadcrumbPagina($pagina); 
		
		$form = $this->createForm(new PaginaType(), $pagina, array("button" => true));
		
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			
			$alias = $pagina->getAlias();
			$paginaVerifica = $em->getRepository("FascicoloBundle\Entity\Pagina")->findOneBy(array('alias'=>$alias,'frammentoContenitore'=>$pagina->getFrammentoContenitore()->getId()));
			if ($paginaVerifica) {
				$form->get('alias')->addError(new \Symfony\Component\Form\FormError('L\'alias specificato è già presente a sistema all\'interno del frammento contenitore'));
			}
			
			$max = $pagina->getMaxMolteplicita();
			$min = $pagina->getMinMolteplicita();			
			if ($max != "0" && $min != "0" && $max < $min) {
				$form->get('maxMolteplicita')->addError(new \Symfony\Component\Form\FormError('Il massimo ed il minimo della molteplicità non sono coerenti'));
			}			

			if ($form->isValid()) {
				try {
					$em->persist($pagina);
					$em->flush();
					$this->addFlash('success', "Sotto Pagina creata correttamente");
					return $this->redirect($this->generateUrl('modifica_pagina', array("id_pagina" => $pagina->getId())));
				}catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}
		
		$form_params["form"] = $form->createView();
		return $form_params;
	}
	
	/**
	 * @Route("/sposta-sopra-sotto-pagina/{id_pagina}", name="sposta_sopra_sotto_pagina") 
	 */
	public function spostaSopraSottoPagina($id_pagina) {
		$em = $this->getDoctrine()->getManager();
        $sottoPagina = $em->getRepository("FascicoloBundle\Entity\Pagina")->findOneById($id_pagina);
		$ordinamento = $sottoPagina->getOrdinamento();
		$sottoPaginaOrdinamentoMaggiore = $em->getRepository("FascicoloBundle\Entity\Pagina")->getSottoPaginaOrdinamento($sottoPagina->getFrammentoContenitore()->getId(),$ordinamento-1);
		if(!is_null($sottoPaginaOrdinamentoMaggiore)){
			try {
					$sottoPagina->setOrdinamento($ordinamento-1);
					$sottoPaginaOrdinamentoMaggiore->setOrdinamento($ordinamento);
					$em->persist($sottoPagina);
					$em->persist($sottoPaginaOrdinamentoMaggiore);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
				} catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
		}
		$frammentoContenitore = $sottoPagina->getFrammentoContenitore();
        return $this->redirectToRoute('modifica_frammento', array('id_frammento' => $frammentoContenitore->getId()));
	}
	
	/**
	 * @Route("/sposta-sotto-sotto-pagina/{id_pagina}", name="sposta_sotto_sotto_pagina") 
	 */
	public function spostaSottoSottoPagina($id_pagina) {
		$em = $this->getDoctrine()->getManager();
        $sottoPagina = $em->getRepository("FascicoloBundle\Entity\Pagina")->findOneById($id_pagina);
		$ordinamento = $sottoPagina->getOrdinamento();
		$sottoPaginaOrdinamentoMinore = $em->getRepository("FascicoloBundle\Entity\Pagina")->getSottoPaginaOrdinamento($sottoPagina->getFrammentoContenitore()->getId(),$ordinamento+1);
		if(!is_null($sottoPaginaOrdinamentoMinore)){
			try {
					$sottoPagina->setOrdinamento($ordinamento+1);
					$sottoPaginaOrdinamentoMinore->setOrdinamento($ordinamento);
					$em->persist($sottoPagina);
					$em->persist($sottoPaginaOrdinamentoMinore);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
				} catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
		}
		$frammentoContenitore = $sottoPagina->getFrammentoContenitore();
        return $this->redirectToRoute('modifica_frammento', array('id_frammento' => $frammentoContenitore->getId())); 
	}
	
	/**
     * @Route("/elimina-sotto-pagina/{id_pagina}", name="elimina_sotto_pagina")
     */
    public function eliminaSottoPaginaAction($id_pagina)
    {
		$this->get('base')->checkCsrf('token');
        $em = $this->getDoctrine()->getManager();
        $pagina = $em->getRepository("FascicoloBundle\Entity\Pagina")->findOneById($id_pagina);

        if (!$pagina) {
            throw $this->createNotFoundException('Unable to find Pagina entity.');
        }
		$frammentoContenitore = $pagina->getFrammentoContenitore();
		try{
			$em->remove($pagina);
			$em->flush();
			$this->addFlash('success', "Pagina eliminata correttamente");
		} catch (\Exception $e) {
			$this->addFlash('error', $e->getMessage());
		}

        return $this->redirectToRoute('modifica_frammento', array('id_frammento' => $frammentoContenitore->getId()));
    }
	
}
