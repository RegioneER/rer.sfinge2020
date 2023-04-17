<?php

namespace MonitoraggioBundle\Exception;

class RigaAP3NonTrovata extends \Exception {
    protected $rigaPUC;

    public function __construct($puc, $code = 0, \Exception $previous = null) {
        $this->rigaPUC =$puc;
        parent::__construct("Classificazione con progressivo $puc non trovata", $code, $previous);
    }

    public function getProgressivo(){
        return $this->rigaPUC;
    }
}
