<?php

namespace PaginaBundle\Services;

/**
 * Servizio di utilità per manipolare breadcrumb, titolo e sottotitolo di una pagina.
 * 
 * Da un controller, per aggiungere un elemento breadcrumb:
 * 
 * $this->get('pagina')->aggiungiElementoBreadcrumb('prova', $this->generateUrl("notizia_index"));
 * 
 * Per resettare il breadcrumb (annullando gli elementi definiti a livello di classe):
 * 
 * $this->get('pagina')->resettaBreadcrumb();
 * 
 * Per settare titolo e sottotitolo:
 * 
 * 	$this->get('pagina')->setTitolo('Nuovo titolo');
 *	$this->get('pagina')->setSottoTitolo('Nuovo sottotitolo');
 * 
 * @author aturdo
 */
class Pagina {
	
	private $twig;

	public function __construct($twig) {
		$this->twig = $twig;
    }
	
	public function aggiungiElementoBreadcrumb($testo, $url = null) {
  		$globals = $this->twig->getGlobals();
 		$br1 = new \PaginaBundle\Annotations\ElementoBreadcrumb();
 		$br1->testo = $testo;
 		$br1->setUrl($url);
 		$globals["elementiBreadcrumb"][] = $br1;
 		
 		$this->twig->addGlobal("elementiBreadcrumb", $globals["elementiBreadcrumb"]);		
	}
	
	public function resettaBreadcrumb() {
		$this->twig->addGlobal("elementiBreadcrumb", array());
	}
	
	public function setTitolo($titolo) {
		$this->twig->addGlobal('titolo', $titolo);
	}
	
	public function setSottoTitolo($sottoTitolo) {
		$this->twig->addGlobal('sottoTitolo', $sottoTitolo);
	}	
	
	public function setMenuAttivo($menuAttivo, $session) {
		$session->set("current_link", $menuAttivo);
	}
	        
}
