<?php


namespace RichiesteBundle\Form\Entity\Bando26;

use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaProfessionisti extends Richiesta
{

    /**
     * @Assert\NotBlank()
     */
    protected $regime;

    protected $multi_proponente;
    
    /**
     * @Assert\NotBlank()
     */
    protected $categoria;

    function getRegime() {
        return $this->regime;
    }

    function setRegime($regime) {
        $this->regime = $regime;
    }

    /**
     * @return mixed
     */
    public function getMultiProponente()
    {
        return $this->multi_proponente;
    }

    /**
     * @param mixed $multi_proponente
     */
    public function setMultiProponente($multi_proponente)
    {
        $this->multi_proponente = $multi_proponente;
    }
    
    public function getCategoria(){
        return $this->categoria;
    }
    
    public function setCategoria( $value ){
        $this->categoria = $value;
    }

}