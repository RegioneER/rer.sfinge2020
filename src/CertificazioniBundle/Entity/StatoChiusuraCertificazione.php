<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\Stato;

/**
 * @ORM\Entity()
 */
class StatoChiusuraCertificazione extends Stato
{

    const CHI_LAVORAZIONE = "CHI_LAVORAZIONE";
	const CHI_BLOCCATA = "CHI_BLOCCATA";
    const CHI_VALIDATA = "CHI_VALIDATA";
    const CHI_INVIATA = "CHI_INVIATA";
    const CHI_APPROVATA = "CHI_APPROVATA";
    
    public function __toString() {
        return $this->getDescrizione();
    }
}
