<?php

namespace PaginaBundle\Annotations;

/**
 * Questa classe serve come annotazione per i controller in modo da generare automaticamente la relativa breadcrumb.
 *
 * Gli elementi sono degli oggetti di tipo ElementoBreadcrumb, a sua volta una annotation,
 * ma le cui istanze possono essere create esplicitamente nelle action.
 * 
 * Se l'annotation viene posizionata a livello di classe varrà per tutte le action
 * contenute nel controller. Gli elementi vengono resi in ordine di definizione,
 * e vengono resi prima quelli definiti nella classe.
 *
 * Esempio di annotazione da mettere nel controller
 * "@Breadcrumb(elementi={@ElementoBreadcrumb(testo="Visualizza notizia", route="notizia_show", parametri={"id"})})"
 * 
 * Nella action gli elementi possono essere aggiunti (o resettati) tramite il servizio Pagina.
 *
 * @Annotation
 * @Target({"METHOD","CLASS"})
 * @Attributes({
 *   @Attribute("elementi", type = "array"),
 * })
 */

class Breadcrumb
{
	
	/** 
	 * @var PaginaBundle\Annotations\ElementoBreadcrumb[]
	 */
	public $elementi;

}