<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Annotations;

/**
 * Description of RicercaFormType
 *
 * @author lfontana
 * @Annotation
 * @Target({"PROPERTY"})
 */
class RicercaFormType {
    
    /**
     *
     * @var string
     */
    public $property;
    
    /**
     *
     * @var string
     */
    public $type;
    
    /**
     *
     * @var string
     */
    public $label;
    
    /**
     * @var array
     */
    public $options;
    
    /**
     *
     * @var integer
     */
    public $ordine;
}
