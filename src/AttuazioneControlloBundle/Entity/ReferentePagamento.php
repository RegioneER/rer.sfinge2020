<?php

namespace AttuazioneControlloBundle\Entity;

use AnagraficheBundle\Entity\Persona;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="referenti_pagamento")
 */
class ReferentePagamento extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AnagraficheBundle\Entity\Persona")
	 * @ORM\JoinColumn(name="persona_id", referencedColumnName="id", nullable=false)
	 */
	private $persona;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="referenti")
	 * @ORM\JoinColumn(name="pagamento_id", referencedColumnName="id", nullable=true)
	 */
	private $pagamento;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\TipoReferenza")
	 * @ORM\JoinColumn(name="tipo_referente_id", referencedColumnName="id", nullable=false)
	 * @Assert\NotNull()
	 */
	private $tipo_referenza;
	
	public function getId() {
		return $this->id;
	}

	public function getPersona() {
		return $this->persona;
	}

	public function getPagamento() {
		return $this->pagamento;
	}

	public function getTipoReferenza() {
		return $this->tipo_referenza;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setPersona($persona) {
		$this->persona = $persona;
	}

	public function setPagamento($pagamento) {
		$this->pagamento = $pagamento;
	}

	public function setTipoReferenza($tipo_referenza) {
		$this->tipo_referenza = $tipo_referenza;
	}


	
}
