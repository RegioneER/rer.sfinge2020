<?php

namespace PaginaBundle\Annotations;

/**
 * Questa classe serve come annotazione per i controller in modo da bloccare i menu.
 *
 * Gli elementi sono degli oggetti di tipo Menuitem, a sua volta una annotation,
 * ma le cui istanze possono essere create esplicitamente nelle action.
 * 
 * Esempio di annotazione da mettere nel controller
 *
 * 
 * Nella action gli elementi possono essere aggiunti (o resettati) tramite il servizio Pagina.
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("menuAttivo", type = "string")
 * })
 */

class Menuitem
{
	
	/** 
	 * @var string
	 */
	public $menuAttivo;

}