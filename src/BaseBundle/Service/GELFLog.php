<?php

namespace BaseBundle\Service;

/**
 * Dipendiamo dalla libreria PEAR gCloud_Monitoring (http://pear.gcloud.schema31.it/)
 */
// require_once 'gCloud_Monitoring/gCloud_Monitoring.php';

Class GELFLog extends gCloud_Monitoring {

    /**
     * Attuale versione della libreria
     *
     * @var string
     * @access public
     */
    const LIBRARY_VERSION = "GELFLog 1.0.20 [Pear]";

    /**
     * Inizializza tutte le proprietà di base del log
     */
    function __construct($streamName = '', $authentication = '') {
        parent::__construct($streamName, $authentication);
        
        $this->protocol = "REST";
    }

}
