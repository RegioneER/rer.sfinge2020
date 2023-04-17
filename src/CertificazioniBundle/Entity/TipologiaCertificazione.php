<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityTipo;

/**
 * TipologiaCertificazione
 *
 * @ORM\Table(name="tipi_certificazione")
 * @ORM\Entity(repositoryClass="CertificazioniBundle\Entity\TipologiaCertificazioneRepository")
 */
class TipologiaCertificazione extends EntityTipo
{
   
}
