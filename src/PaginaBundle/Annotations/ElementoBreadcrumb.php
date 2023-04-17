<?php

namespace PaginaBundle\Annotations;

/**
 * 
 * Il 'testo' è l'etichetta che verra renderizzata nella breadcrumb,
 * mentre la 'route' è il nome di una rotta del controller a cui il link punta.
 *
 * Nel caso ci siano rotte che necessitano di parametri occorre indicarli nei 'parametri'.
 * In questo caso i valori dei vari parametri vengono recuperati dalla request, quindi, per intenderci, devono essere presenti nella query string.
 * 
 * @Annotation
 * @Target({"ANNOTATION"})
 * @Attributes({
 *   @Attribute("testo", type = "string"),
 *   @Attribute("route", type = "string"),
 *   @Attribute("parametri", type = "array"),
 * })
 */

class ElementoBreadcrumb
{	
	/** 
	 * @var string
	 * 
	 * @Required 
	 */
	public $testo;
	
	/** 
	 * @var string
	 */
	public $route;
	
	/** 
	 * @var array<string>
	 */
	public $parametri;
	
	protected $url;

	public function getUrl() {
		return $this->url;
	}

	public function setUrl($url) {
		$this->url = $url;
	}

}