<?php

namespace AttuazioneControlloBundle\Entity\Autodichiarazioni;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Description of ElencoProcedura
 *
 * @author gdisparti
 */

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Autodichiarazioni\ElencoProceduraRepository")
 * @ORM\Table(name="atd_elenchi_procedure")
 */
class ElencoProcedura {

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
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Autodichiarazioni\Elenco")
	 * @ORM\JoinColumn(name="elenco_id", nullable=false)
	 */
	protected $elenco;
	
	
	public function getId() {
		return $this->id;
	}

	public function getProcedura() {
		return $this->procedura;
	}

	public function getModalitaPagamento() {
		return $this->modalitaPagamento;
	}

	public function getElenco() {
		return $this->elenco;
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

	public function setElenco($elenco) {
		$this->elenco = $elenco;
	}
}
