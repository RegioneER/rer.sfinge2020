<?php

namespace RichiesteBundle\Form\Entity\Bando125;

use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaIrap2020 extends Richiesta
{
    /**
     * @Assert\NotBlank()
     */
    protected $tipologia_proponente;

    /**
     * @return mixed
     */
    public function getTipologiaProponente()
    {
        return $this->tipologia_proponente;
    }

    /**
     * @param mixed $tipologia_proponente
     */
    public function setTipologiaProponente($tipologia_proponente): void
    {
        $this->tipologia_proponente = $tipologia_proponente;
    }
}
