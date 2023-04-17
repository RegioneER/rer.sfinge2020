<?php


namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Entity()
 * @ORM\Table(name="attivita_obiettivi_realizzativi")
 */
class AttivitaObiettivoRealizzativo extends EntityLoggabileCancellabile {
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\EstensionePagamento", inversedBy="attivita_or")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $estensione_pagamento;
	
	/**
	 * @ORM\Column(type="string", length=250, nullable=true)
	 */
	protected $tipo_attivita;
	
	/**
	 * @ORM\Column(type="string", length=250, nullable=true)
	 */
	protected $tipo_target;
	
	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $numero_contatti;
	
	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $numero_partecipazioni;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $link;
	
	public function getId() {
		return $this->id;
	}

	public function getEstensionePagamento() {
		return $this->estensione_pagamento;
	}

	public function getTipoAttivita() {
		return $this->tipo_attivita;
	}

	public function getTipoTarget() {
		return $this->tipo_target;
	}

	public function getNumeroContatti() {
		return $this->numero_contatti;
	}

	public function getNumeroPartecipazioni() {
		return $this->numero_partecipazioni;
	}

	public function getLink() {
		return $this->link;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setEstensionePagamento($estensione_pagamento) {
		$this->estensione_pagamento = $estensione_pagamento;
	}

	public function setTipoAttivita($tipo_attivita) {
		$this->tipo_attivita = $tipo_attivita;
	}

	public function setTipoTarget($tipo_target) {
		$this->tipo_target = $tipo_target;
	}

	public function setNumeroContatti($numero_contatti) {
		$this->numero_contatti = $numero_contatti;
	}

	public function setNumeroPartecipazioni($numero_partecipazioni) {
		$this->numero_partecipazioni = $numero_partecipazioni;
	}

	public function setLink($link) {
		$this->link = $link;
	}


}
