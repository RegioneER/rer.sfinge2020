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
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Fascicoli", route="visualizza_fascicoli")})
 * @Route("/pagina")
 */
class PaginaController extends Controller
{
  	
	/**
	 * @Route("/modifica-pagina/{id_pagina}", name="modifica_pagina")
	 * @Template("FascicoloBundle:Pagina:modificaPagina.html.twig")
	 * @PaginaInfo(titolo="Modifica Pagina")
	 * @Menuitem(menuAttivo = "visualizza_fascicoli")
	 */
	public function modificaPaginaAction(Request $request, $id_pagina) {
		
		$em = $this->getDoctrine()->getManager();
		$pagina = $em->getRepository("FascicoloBundle\Entity\Pagina")->findOneById($id_pagina);
		
		$this->get('fascicolo')->creaBreadcrumbPagina($pagina); 
		
		$form = $this->createForm(new PaginaType(), $pagina, array("button" => true));

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			$alias = $pagina->getAlias();
			$paginaVerifica = $em->getRepository("FascicoloBundle\Entity\Pagina")->findOneBy(array('alias'=>$alias,'frammentoContenitore'=>$pagina->getFrammentoContenitore()->getId()));
			if ($paginaVerifica && $paginaVerifica->getId()!=$pagina->getId()) {
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
					$this->addFlash('success', "Modifiche salvate correttamente");
				} catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}
		$form_params["form"] = $form->createView();
		$form_params["id_pagina"] = $pagina->getId();
		return $form_params;
	}
	
	 /**
     * @Route("/elimina-pagina/{id_pagina}", name="elimina_pagina")
	 * @Menuitem(menuAttivo = "visualizza_fascicoli")
     */
    public function eliminaPaginaAction($id_pagina)
    {
		$this->get('base')->checkCsrf('token');
        $em = $this->getDoctrine()->getManager();
        $pagina = $em->getRepository("FascicoloBundle\Entity\Pagina")->findOneById($id_pagina);
		$id_fascicolo = $pagina->getFascicolo()->getId();

        if (!$pagina) {
            throw $this->createNotFoundException('Unable to find Pagina entity.');
        }
		try{
			$em->remove($pagina);
			$em->flush();
			$this->addFlash('success', "Pagina eliminata correttamente");
		} catch (\Exception $ex) {
			$this->addFlash('error', $ex->getMessage());
		}

        return $this->redirectToRoute('visualizza_pagine', array('id_fascicolo' => $id_fascicolo));
    }
    
	 /**
     * @Route("/clona-pagina/{id_pagina}", name="clona_pagina")
     */
    public function clonaPaginaAction($id_pagina)
    {
        $em = $this->getDoctrine()->getManager();
        $pagina = $em->getRepository("FascicoloBundle\Entity\Pagina")->find($id_pagina);

        if (!$pagina) {
            throw $this->createNotFoundException('Unable to find Pagina entity.');
        }
		try{
			$paginaClonata = clone $pagina;
            $paginaClonata->setAlias($paginaClonata->getAlias()."_cloned");
			$em->persist($paginaClonata);
			$em->flush();
			$this->addFlash('success', "Pagina clonata correttamente");
		} catch (\Exception $e) {
			$this->addFlash('error', $e->getMessage());
		}
        
        if (!is_null($pagina->getFrammentoContenitore())) {
            return $this->redirectToRoute('modifica_frammento', array("id_frammento" => $pagina->getFrammentoContenitore()->getId()));
        } else {
            return $this->redirectToRoute('modifica_fascicolo', array("id_fascicolo" => $pagina->getFascicolo()->getId()));
        }
    }    
	
}
