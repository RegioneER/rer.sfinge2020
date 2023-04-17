<?php

namespace RichiesteBundle\Form\Entity\Bando28;

use RichiesteBundle\Entity\Richiesta;

use Symfony\Component\Validator\Constraints as Assert;


class RichiestaFiere extends Richiesta{
    
    protected $multiProponente;
    
    protected $tipologia;
    
    public function getMultiProponente(){
        return $this->multiProponente;
    }
    
    public function setMultiProponente( $value ){
        $this->multiProponente = $value;
    }
    
    public function getTipologia(){
        return $this->tipologia;
    }
    
    public function setTipologia( $value ){
        $this->tipologia = $value;
    }
}