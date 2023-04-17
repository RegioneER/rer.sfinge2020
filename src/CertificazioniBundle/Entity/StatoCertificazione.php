<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\Stato;

/**
 * @ORM\Entity()
 */
class StatoCertificazione extends Stato
{

    const CERT_INSERITA = "CERT_INSERITA";
    const CERT_PREVALIDATA = "CERT_PREVALIDATA";
    const CERT_VALIDATA = "CERT_VALIDATA";
    const CERT_INVIATA = "CERT_INVIATA";
    const CERT_APPROVATA = "CERT_APPROVATA";
    
    public function __toString() {
        return $this->getDescrizione();
    }
}
