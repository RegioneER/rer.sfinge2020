<?php

namespace AttuazioneControlloBundle\Form\Entity;

class GestioneChecklistSpecifica {
    public $elementi;

    public function __construct(array $elementi) {
        $this->elementi = $elementi;
    }

    public function getElementi(): array {
        return $this->elementi;
    }
}
