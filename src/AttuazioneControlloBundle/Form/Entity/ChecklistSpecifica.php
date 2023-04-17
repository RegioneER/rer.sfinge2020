<?php

namespace AttuazioneControlloBundle\Form\Entity;

use AttuazioneControlloBundle\Entity\Controlli\ElementoChecklistControllo;
use SfingeBundle\Entity\Procedura;

class ChecklistSpecifica 
{
    /**
     * @var Procedura[]
     */
    public $procedure;
    public $elemento;
    public function __construct(ElementoChecklistControllo $elemento, array $procedure)
    {
        $this->elemento = $elemento;
        $this->procedure = $procedure;
    }
}