<?php
namespace RichiesteBundle\Form\Entity\Bando98;

use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaLegge14 extends Richiesta
{
    /**
     * @Assert\NotBlank()
     */
    protected $tipologiaProgetto;

    /**
     * @return mixed
     */
    public function getTipologiaProgetto()
    {
        return $this->tipologiaProgetto;
    }

    /**
     * @param $value
     */
    public function setTipologiaProgetto($value)
    {
        $this->tipologiaProgetto = $value;
    }

    /**
     * @return string
     */
    public function getLetteraTipologiaProgetto()
    {
        $letteraTipologiaProgetto = str_replace('_', '', $this->getTipologiaProgetto());
        $letteraTipologiaProgetto = str_replace('tipologia', '', $letteraTipologiaProgetto);
        return strtoupper($letteraTipologiaProgetto);
    }
}
