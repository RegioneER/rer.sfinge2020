<?php

namespace CipeBundle\Entity\Classificazioni;

use CipeBundle\Entity\Classificazioni\CupClassificazione;
use Doctrine\ORM\Mapping as ORM;
use CipeBundle\Entity\Classificazioni\CupNatura;
use MonitoraggioBundle\Entity\TC5TipoOperazione;


/**
 * @author gaetanoborgosano
 * @ORM\Table(name="cup_tipologie",
 *  indexes={
 *      @ORM\Index(name="idx_cup_tipologia_cup_natura_id", columns={"CupNatura_id"})
 *  })
 * @ORM\Entity(repositoryClass="CipeBundle\Entity\Classificazioni\CupClassificazioneRepository")
 */
class CupTipologia extends CupClassificazione {
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $formazione;

	/**
	 * @var CupNatura
	 * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupNatura", inversedBy="CupTipologie")
     * @ORM\JoinColumn(name="CupNatura_id", referencedColumnName="id", nullable=false)
	 */
	protected $CupNatura;

	/**
	 * @var TC5TipoOperazione
	 * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC5TipoOperazione")
     * @ORM\JoinColumn(nullable=true)
	 */
	protected $tc5_tipo_operazione;

	function getFormazione() { return $this->formazione; }
	function setFormazione($formazione) { $this->formazione = $formazione; }


	function getCupNatura() { return $this->CupNatura; }
	function setCupNatura(CupNatura $CupNatura) { $this->CupNatura = $CupNatura; }

	public function getTc5TipoOperazione(): ?TC5TipoOperazione {
		return $this->tc5_tipo_operazione;
	}
	
	public function setTc5TipoOperazione(TC5TipoOperazione $tc5): self {
		$this->tc5_tipo_operazione = $tc5;

		return $this;
	}
}
