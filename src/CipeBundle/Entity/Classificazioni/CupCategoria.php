<?php

namespace CipeBundle\Entity\Classificazioni;

use CipeBundle\Entity\Classificazioni\CupClassificazione;
use Doctrine\ORM\Mapping as ORM;
use CipeBundle\Entity\Classificazioni\CupSottosettore;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CupNatura
 *
 * @author gaetanoborgosano
 * @ORM\Table(name="cup_categorie",
 *  indexes={
 *      @ORM\Index(name="idx_cup_categoria_cup_sottosettore_id", columns={"CupSottosettore_id"})
 *  },
 *  uniqueConstraints={@ORM\UniqueConstraint(name="idx_cup_categoria_codice_cup_sottosettore_id", columns={"codice", "CupSottosettore_id"}) 
 *  }
 * )
 * @ORM\Entity(repositoryClass="CipeBundle\Entity\Classificazioni\CupClassificazioneRepository")
 */
class CupCategoria extends CupClassificazione {
	
	/**
	 *
	 * @var CupSottosettore
	 * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupSottosettore", inversedBy="CupCategorie")
     * @ORM\JoinColumn(name="CupSottosettore_id", referencedColumnName="id", nullable=false)
	 */
	protected $CupSottosettore;
	function getCupSottosettore() { return $this->CupSottosettore; }
	function setCupSottosettore(CupSottosettore $CupSottosettore) { $this->CupSottosettore = $CupSottosettore; }

}
