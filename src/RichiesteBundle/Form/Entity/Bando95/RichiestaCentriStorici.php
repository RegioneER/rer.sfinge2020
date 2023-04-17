<?php

namespace RichiesteBundle\Form\Entity\Bando95;

use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaCentriStorici extends Richiesta
{
    /**
     * @Assert\NotBlank()
     */
    protected $categoria;

    
    public function getCategoria(){
        return $this->categoria;
    }
    
    public function setCategoria( $value ){
        $this->categoria = $value;
    }
}
