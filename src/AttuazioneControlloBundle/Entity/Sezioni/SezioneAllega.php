<?php

namespace AttuazioneControlloBundle\Entity\Sezioni;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Description of SezioneAllega
 *
 * @author gdisparti
 */

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Sezioni\SezioneAllegaRepository")
 * @ORM\Table(name="rnd_sezione_allega")
 */
class SezioneAllega {

	/**
	 *
	 * @ORM\Column(name="id", type="bigint")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura")
	 * @ORM\JoinColumn(name="procedura_id", nullable=false)
	 */
	protected $procedura;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\ModalitaPagamento")
	 * @ORM\JoinColumn(name="modalita_pagamento_id", nullable=true)
	 */
	protected $modalitaPagamento;
	
	/**
	 *
	 * @ORM\Column(name="testo", type="text")
	 */
	protected $testo;
	
	
	public function getId() {
		return $this->id;
	}

	public function getProcedura() {
		return $this->procedura;
	}

	public function getModalitaPagamento() {
		return $this->modalitaPagamento;
	}

	public function getTesto() {
		return $this->testo;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setProcedura($procedura) {
		$this->procedura = $procedura;
	}

	public function setModalitaPagamento($modalitaPagamento) {
		$this->modalitaPagamento = $modalitaPagamento;
	}

	public function setTesto($testo) {
		$this->testo = $testo;
	}

}
