<?php

namespace RichiesteBundle\Form\Entity\Bando109;

use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaTurismo2019 extends Richiesta
{
    /**
     * @Assert\NotBlank()
     */    
    protected $regime;

    /**
     * @Assert\NotBlank()
     */
    protected $tipologia_struttura;

    /**
     * @return mixed
     */
    public function getRegime()
    {
        return $this->regime;
    }

    /**
     * @param mixed $regime
     */
    public function setRegime($regime): void
    {
        $this->regime = $regime;
    }

    /**
     * @return mixed
     */
    public function getTipologiaStruttura()
    {
        return $this->tipologia_struttura;
    }

    /**
     * @param mixed $tipologia_struttura
     */
    public function setTipologiaStruttura($tipologia_struttura): void
    {
        $this->tipologia_struttura = $tipologia_struttura;
    }
}
