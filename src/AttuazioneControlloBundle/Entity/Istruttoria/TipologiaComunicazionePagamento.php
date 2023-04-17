<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tipologie_comunicazione_pagamento")
 */
class TipologiaComunicazionePagamento extends EntityTipo
{
    public function __toString() {
        return $this->getDescrizione();
    }
}
