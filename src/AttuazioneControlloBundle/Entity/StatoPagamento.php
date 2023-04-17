<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\Stato;

/**
 * @ORM\Entity()
 */
class StatoPagamento extends Stato
{

    const PAG_INSERITO = "PAG_INSERITO";
    const PAG_VALIDATO = "PAG_VALIDATO";
    const PAG_FIRMATO = "PAG_FIRMATO";
    const PAG_INVIATO_PA = "PAG_INVIATO_PA";
    const PAG_PROTOCOLLATO = "PAG_PROTOCOLLATO";
    
    public function __toString() {
        return $this->getDescrizione();
    }
}
