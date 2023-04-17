<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Annotations;

/**
 * Description of ViewElenco.
 *
 * @author lfontana
 * @Annotation
 * @Target({"PROPERTY"})
 */
class ViewElenco
{
    /**
     * @var string
     */
    public $titolo;

    /**
     * @var int
     */
    public $ordine;

    /**
     * @var bool
     */
    public $show = true;

    /**
     * @var string
     */
    public $property;
}
