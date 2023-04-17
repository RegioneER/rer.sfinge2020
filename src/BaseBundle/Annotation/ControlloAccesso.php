<?php

namespace BaseBundle\Annotation;

/**
 * @author Antonio Turdo <aturdo@schema31.it>
 * 
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("contesto", type = "string"),
 *   @Attribute("classe", type = "string"),
 *   @Attribute("opzioni", type = "array"),
 *   @Attribute("azione", type = "string"),
 * })
 */
class ControlloAccesso
{
    /**
     * @var string
	 * 
	 * @Required 
     */
    public $contesto;

    /**
     * @var string
	 * 
	 * @Required 
     */
    public $classe;

    /**
     * An array of opzioni.
     *
     * @var array
	 * 
	 * @Required 
     */
    public $opzioni = array();
	
    /**
     * @var string
     */
    public $azione;	
	
}
