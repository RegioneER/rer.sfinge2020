<?php

namespace CipeBundle\Entity\Classificazioni;

use CipeBundle\Entity\Classificazioni\CupClassificazione;
use Doctrine\ORM\Mapping as ORM;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CupNatura
 *
 * @author gaetanoborgosano
 * @ORM\Table(name="cup_tipi_copertura_finanziaria")
 * @ORM\Entity(repositoryClass="CipeBundle\Entity\Classificazioni\CupClassificazioneRepository")
 */
class CupTipoCoperturaFinanziaria extends CupClassificazione {
	
}
