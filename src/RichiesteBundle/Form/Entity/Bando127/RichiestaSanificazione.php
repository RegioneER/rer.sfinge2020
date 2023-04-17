<?php

namespace RichiesteBundle\Form\Entity\Bando127;

use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaSanificazione extends Richiesta
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=16, groups={"statoItalia"})
     */
    private $codice_fiscale;

    /**
     * @return mixed
     */
    public function getCodiceFiscale()
    {
        return $this->codice_fiscale;
    }

    /**
     * @param mixed $codice_fiscale
     */
    public function setCodiceFiscale($codice_fiscale): void
    {
        $this->codice_fiscale = $codice_fiscale;
    }
}
