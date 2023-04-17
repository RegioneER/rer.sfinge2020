<?php

namespace RichiesteBundle\Form\Entity\Bando174;

use BaseBundle\Service\AttributiRicerca;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaArtigiani extends Richiesta {

    /**
     * @Assert\NotBlank()
     */
    protected $annualita;

    public function getAnnualita() {
        return $this->annualita;
    }

    public function setAnnualita($annualita): void {
        $this->annualita = $annualita;
    }

}
