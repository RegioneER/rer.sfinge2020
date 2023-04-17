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
use FascicoloBundle\Entity\Campo;
use FascicoloBundle\Form\Type\CampoType;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Fascicoli", route="visualizza_fascicoli")})
 * @Route("/campo")
 */
class CampoController extends Controller
{
  	
	/**
     * @Route("/crea-campo/{id_frammento}", name="crea_campo")
	 * @Template("FascicoloBundle:Campo:creaCampo.html.twig")
	 * @PaginaInfo(titolo="Crea campo")
	 * @Menuitem(menuAttivo = "visualizza_fascicoli")
     */	
	public function creaCampoAction(Request $request, $id_frammento) {
		$em = $this->getDoctrine()->getManager();
		
		$frammento = $em->getRepository("FascicoloBundle\Entity\Frammento")->findOneById($id_frammento);
		$ordinamentoMaggiore = $em->getRepository("FascicoloBundle\Entity\Campo")->getOrdinamentoMaggiore($id_frammento);
		
		$this->get('fascicolo')->creaBreadcrumbFrammento($frammento, true); 
		
		$campo = new Campo();
		$campo->setFrammento($frammento);
		$campo->setOrdinamento($ordinamentoMaggiore+1);
		$paginaFrammento = $campo->getFrammento()->getPagina();
		$evidenziato = false;
		if(!is_null($paginaFrammento->getFrammentoContenitore()) && $paginaFrammento->getFrammentoContenitore()->getTipoFrammento()->getCodice()=='tabella'){
			 $evidenziato = true;
		}
		$form = $this->createForm(new CampoType($evidenziato), $campo);
		
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			
			$alias = $campo->getAlias();
			$campoVerifica = $em->getRepository("FascicoloBundle\Entity\Campo")->findOneBy(array('alias'=>$alias,'frammento'=>$campo->getFrammento()->getId()));
			if ($campoVerifica) {
				$form->get('alias')->addError(new \Symfony\Component\Form\FormError('L\'alias specificato è già presente a sistema all\'interno del frammento'));
			}

			if($campo->getTipoCampo()->getCodice()=='choice'){
				if(is_null($campo->getMultiple())) {
					$form->get('multiple')->addError(new \Symfony\Component\Form\FormError('Occorre selezionare un\'opzione'));
				}
				if(is_null($campo->getExpanded())){
					$form->get('expanded')->addError(new \Symfony\Component\Form\FormError('Occorre selezionare un\'opzione'));
				}
				
				$scelte = explode("<br/>", $campo->getScelte());
				//$scelte = preg_split('/\r\n|[\r\n]<br/>/', $campo->getScelte());
				
				foreach ($scelte as $chiave => $scelta) {
					$scelta_esplosa = explode("=>", $scelta);
					if (count($scelta_esplosa) == 2) {
						$nuova_chiave = trim($scelta_esplosa[0]);
						$scelte = $this->replace_key($scelte, $chiave, $nuova_chiave);
						$scelte[$nuova_chiave] = trim($scelta_esplosa[1]);				
					} else {
						$scelte[$chiave] = trim($scelta);	
					}
				}
				
				$campo->setScelte($scelte);			
			} else {
				$campo->setScelte(null);
				$campo->setExpanded(null);
				$campo->setMultiple(null);
				$campo->setQuery(null);
			}
			
			if($campo->getTipoCampo()->getCodice()=='numero'){
				if(is_null($campo->getPrecisione())) {
					$form->get('precisione')->addError(new \Symfony\Component\Form\FormError('Occorre specificare il numero di cifre decimali'));
				}elseif($campo->getPrecisione()<0) {
					$form->get('precisione')->addError(new \Symfony\Component\Form\FormError('Occorre specificare un numero maggiore di zero o zero'));
				}
			} else {
				$campo->setPrecisione(null);
			}
			
			if ($form->isValid()) {
				try {
					$em->persist($campo);
					$em->flush();
					$this->addFlash('success', "Campo creato correttamente");
					return $this->redirect($this->generateUrl('modifica_frammento', array("id_frammento" => $campo->getFrammento()->getId())));
				}catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}	
		}
		
		$scelta = $em->getRepository("FascicoloBundle\Entity\TipoCampo")->findOneBy(array('codice'=>'choice'));
		$numero = $em->getRepository("FascicoloBundle\Entity\TipoCampo")->findOneBy(array('codice'=>'numero'));
		$textarea = $em->getRepository("FascicoloBundle\Entity\TipoCampo")->findOneBy(array('codice'=>'textarea'));
		$form_params["numero"] = $numero;
		$form_params["scelta"] = $scelta;
		$form_params['evidenziato'] = $evidenziato;
		$form_params['textarea'] = $textarea;
		$form_params["form"] = $form->createView();
		return $form_params;
	}
	
	private function replace_key($array, $old_key, $new_key) {
		$keys = array_keys($array);
		if (false === $index = array_search($old_key, $keys)) {
			throw new \Exception(sprintf('Key "%s" does not exit', $old_key));
		}
		$keys[$index] = $new_key;
		return array_combine($keys, array_values($array));
	}	
	
	/**
	 * @Route("/modifica-campo/{id_campo}", name="modifica_campo")
	 * @Template("FascicoloBundle:Campo:modificaCampo.html.twig")
	 * @PaginaInfo(titolo="Modifica campo")
	 * @Menuitem(menuAttivo = "visualizza_fascicoli")
	 */
	public function modificaCampoAction(Request $request, $id_campo) {
		
		$em = $this->getDoctrine()->getManager();
		$campo = $em->getRepository("FascicoloBundle\Entity\Campo")->findOneById($id_campo);
		
		$this->get('fascicolo')->creaBreadcrumbFrammento($campo->getFrammento(), true); 
		
		$scelte_salvate = $campo->getScelte();
		if (!is_null($scelte_salvate)) {
			$scelte = "";
			$i = 0;
			foreach ($scelte_salvate as $chiave => $scelta) {
				if ($chiave == $i) {
					$scelte .= "$scelta<br />";
				} else {
					$scelte .= "$chiave => $scelta<br />";
				}
				$i++;
			}
		} else {
			$scelte = null;
		}
			
		$campo->setScelte($scelte);
		
		$paginaFrammento = $campo->getFrammento()->getPagina();
		$evidenziato = false;
		if(!is_null($paginaFrammento->getFrammentoContenitore()) && $paginaFrammento->getFrammentoContenitore()->getTipoFrammento()->getCodice()=='tabella'){
			 $evidenziato = true;
		}
		$form = $this->createForm(new CampoType($evidenziato), $campo);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			
			$alias = $campo->getAlias();
			$campoVerifica = $em->getRepository("FascicoloBundle\Entity\Campo")->findOneBy(array('alias'=>$alias,'frammento'=>$campo->getFrammento()->getId()));
			if ($campoVerifica && $campoVerifica->getId()!=$campo->getId()) {
				$form->get('alias')->addError(new \Symfony\Component\Form\FormError('L\'alias specificato è già presente a sistema all\'interno del frammento'));
			}
			
			if(!is_null($campo->getTipoCampo()) && $campo->getTipoCampo()->getCodice()=='choice'){
				if(is_null($campo->getMultiple())) {
					$form->get('multiple')->addError(new \Symfony\Component\Form\FormError('Occorre selezionare un\'opzione'));
				}
				if(is_null($campo->getExpanded())){
					$form->get('expanded')->addError(new \Symfony\Component\Form\FormError('Occorre selezionare un\'opzione'));
				}
				
				$scelte = explode("<br/>", $campo->getScelte());
				// $scelte = preg_split('/\r\n|[\r\n]<br/>/', $campo->getScelte());
				
				foreach ($scelte as $chiave => $scelta) {
					$scelta_esplosa = explode("=>", $scelta);
					if (count($scelta_esplosa) == 2) {
						$nuova_chiave = trim($scelta_esplosa[0]);
						$scelte = $this->replace_key($scelte, $chiave, $nuova_chiave);
						$scelte[$nuova_chiave] = trim($scelta_esplosa[1]);				
					} else {
						$scelte[$chiave] = trim($scelta);	
					}
				}
				
				$campo->setScelte($scelte);
			} else {
				$campo->setScelte(null);
				$campo->setExpanded(null);
				$campo->setMultiple(null);
				$campo->setQuery(null);
			}
			
			if(!is_null($campo->getTipoCampo()) && $campo->getTipoCampo()->getCodice()=='numero'){
				if(is_null($campo->getPrecisione())) {
					$form->get('precisione')->addError(new \Symfony\Component\Form\FormError('Occorre specificare il numero di cifre decimali'));
				}elseif($campo->getPrecisione()<0) {
					$form->get('precisione')->addError(new \Symfony\Component\Form\FormError('Occorre specificare un numero maggiore di zero o zero'));
				}
			} else {
				$campo->setPrecisione(null);
			}
								
			if ($form->isValid()) {
				try {					
					$em->persist($campo);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
					return $this->redirect($this->generateUrl('modifica_frammento', array("id_frammento" => $campo->getFrammento()->getId())));
				} catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}
		
		$scelta = $em->getRepository("FascicoloBundle\Entity\TipoCampo")->findOneBy(array('codice'=>'choice'));
		$numero = $em->getRepository("FascicoloBundle\Entity\TipoCampo")->findOneBy(array('codice'=>'numero'));
		$textarea = $em->getRepository("FascicoloBundle\Entity\TipoCampo")->findOneBy(array('codice'=>'textarea'));
		$form_params["numero"] = $numero;
		$form_params["form"] = $form->createView();
		$form_params['evidenziato'] = $evidenziato;
		$form_params["scelta"] = $scelta;
		$form_params['textarea'] = $textarea;
		return $form_params;
	}
	
	/**
	 * @Route("/sposta-sopra-campo/{id_campo}", name="sposta_sopra_campo") 
	 */
	public function spostaSopraCampo($id_campo) {
		$em = $this->getDoctrine()->getManager();
        $campo = $em->getRepository("FascicoloBundle\Entity\Campo")->findOneById($id_campo);
		$ordinamento = $campo->getOrdinamento();
		$campoOrdinamentoMaggiore = $em->getRepository("FascicoloBundle\Entity\Campo")->getCampoOrdinamento($campo->getFrammento()->getId(),$ordinamento-1);
		if(!is_null($campoOrdinamentoMaggiore)){
			try {
					$campo->setOrdinamento($ordinamento-1);
					$campoOrdinamentoMaggiore->setOrdinamento($ordinamento);
					$em->persist($campo);
					$em->persist($campoOrdinamentoMaggiore);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
				} catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
		}
		$id_frammento = $campo->getFrammento()->getId();
        return $this->redirect($this->generateUrl('modifica_frammento', array("id_frammento" => $id_frammento)));  
	}
	
	/**
	 * @Route("/sposta-sotto-campo/{id_campo}", name="sposta_sotto_campo") 
	 */
	public function spostaSottoCampo($id_campo) {
		$em = $this->getDoctrine()->getManager();
        $campo = $em->getRepository("FascicoloBundle\Entity\Campo")->findOneById($id_campo);
		$ordinamento = $campo->getOrdinamento();
		$campoOrdinamentoMinore = $em->getRepository("FascicoloBundle\Entity\Campo")->getCampoOrdinamento($campo->getFrammento()->getId(),$ordinamento+1);
		if(!is_null($campoOrdinamentoMinore)){
			try {
					$campo->setOrdinamento($ordinamento+1);
					$campoOrdinamentoMinore->setOrdinamento($ordinamento);
					$em->persist($campo);
					$em->persist($campoOrdinamentoMinore);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
				} catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
		}
		$id_frammento = $campo->getFrammento()->getId();
        return $this->redirect($this->generateUrl('modifica_frammento', array("id_frammento" => $id_frammento))); 
	}
	
	 /**
     * @Route("/elimina-campo/{id_campo}", name="elimina_campo")
     */
    public function eliminaCampoAction($id_campo)
    {
		$this->get('base')->checkCsrf('token');
        $em = $this->getDoctrine()->getManager();
        $campo = $em->getRepository("FascicoloBundle\Entity\Campo")->findOneById($id_campo);

        if (!$campo) {
            throw $this->createNotFoundException('Unable to find Campo entity.');
        }
		$id_frammento = $campo->getFrammento()->getId();
		try{
			$em->remove($campo);
			$em->flush();
			$this->addFlash('success', "Campo eliminato correttamente");
		} catch (\Exception $ex) {
			$this->addFlash('error', $ex->getMessage());
		}

		return $this->redirect($this->generateUrl('modifica_frammento', array("id_frammento" => $id_frammento)));
    }
	
	
	/**
     * @Route("/crea-vincolo/{id_campo}", name="crea_vincolo")
	 * @Template("FascicoloBundle:Vincolo:creaVincolo.html.twig")
	 * @PaginaInfo(titolo="Crea vincolo")
     */	
	public function creaVincoloAction(Request $request, $id_campo) {
		$em = $this->getDoctrine()->getManager();
		
		$campo = $em->getRepository("FascicoloBundle\Entity\Campo")->findOneById($id_campo);
		
		$this->get('fascicolo')->creaBreadcrumbCampo($campo, true); 
		
		$vincolo = new \FascicoloBundle\Entity\Vincolo();
		$vincolo->setCampo($campo);

		$form = $this->createForm("FascicoloBundle\Form\Type\VincoloType", $vincolo, array("id_tipo_campo" => $campo->getTipoCampo()->getId(), "creazione" => true));
		
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);			
			if ($form->isValid()) {
				try {
					$em->persist($vincolo);
					$em->flush();
					$this->addFlash('success', "Vincolo creato correttamente");
					return $this->redirect($this->generateUrl('modifica_vincolo', array("id_vincolo" => $vincolo->getId())));
				}catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}	
		}

		$form_params["form"] = $form->createView();
		return $form_params;
	}
	
	/**
	 * @Route("/modifica-vincolo/{id_vincolo}", name="modifica_vincolo")
	 * @Template("FascicoloBundle:Vincolo:modificaVincolo.html.twig")
	 * @PaginaInfo(titolo="Modifica vincolo")
	 * @Menuitem(menuAttivo = "visualizza_campi")
	 */
	public function modificaVincoloAction(\Symfony\Component\HttpFoundation\Request $request, $id_vincolo) {
		
		$em = $this->getDoctrine()->getManager();
		$vincolo = $em->getRepository("FascicoloBundle\Entity\Vincolo")->findOneById($id_vincolo);
		
		$tipoVincolo = $vincolo->getTipoVincolo();
		$servizio = $this->container->get("fascicolo.vincolo.".$tipoVincolo->getCodice());
		$fields = $servizio->getParametersFields();
		$parametri = $vincolo->getParametri();
		foreach ($fields as $field) {
			$vincolo->$field = $parametri[$field];
		}		
		
		$campo = $vincolo->getCampo();
		
		$this->get('fascicolo')->creaBreadcrumbCampo($campo, true);
		
		$form = $this->createForm("FascicoloBundle\Form\Type\VincoloType", $vincolo, array("id_tipo_campo" => $campo->getTipoCampo()->getId(), "creazione" => false));

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			
			foreach ($fields as $field) {
				$parametri[$field] = $vincolo->$field;
			}
			
			$vincolo->setParametri($parametri);
			
			$servizio->validaVincolo($vincolo, $form);
		
			if ($form->isValid()) {
				try {
					$em->persist($vincolo);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
					return $this->redirect($this->generateUrl('modifica_campo', array("id_campo" => $vincolo->getCampo()->getId())));
				} catch (\Exception $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
		}
			
		$form_params["form"] = $form->createView();
		return $form_params;
	}
	
	 /**
     * @Route("/elimina-vincolo/{id_vincolo}", name="elimina_vincolo")
     */
    public function eliminaVincoloAction($id_vincolo)
    {
		$this->get('base')->checkCsrf('token');
        $em = $this->getDoctrine()->getManager();
        $vincolo = $em->getRepository("FascicoloBundle\Entity\Vincolo")->findOneById($id_vincolo);

        if (!$vincolo) {
            throw $this->createNotFoundException('Unable to find Vincolo entity.');
        }
		$id_campo = $vincolo->getCampo()->getId();
		try{
			$em->remove($vincolo);
			$em->flush();
			$this->addFlash('success', "Vincolo eliminato correttamente");
		} catch (\Exception $ex) {
			$this->addFlash('error', $ex->getMessage());
		}

		return $this->redirect($this->generateUrl('modifica_campo', array("id_campo" => $id_campo)));
    }
	
	
	
}
