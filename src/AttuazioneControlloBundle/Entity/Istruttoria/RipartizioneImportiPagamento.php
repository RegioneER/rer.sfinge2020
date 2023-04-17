<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 *
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Istruttoria\RipartizioneImportiPagamentoRepository")
 * @ORM\Table(name="ripartizioni_importi_pagamenti")
 */
class RipartizioneImportiPagamento extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="ripartizioni_importi_pagamento")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $pagamento;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_atto;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $numero_atto;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_anticipo;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_contributo;
	
	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $proponente;


	protected $importi_per_proponente;
		
	public function __construct() {
	}

	public function getNomeClasse() {
		return "RipartizioneImportiPagamento";
	}
	
	function getId() {
		return $this->id;
	}

	function getPagamento() {
		return $this->pagamento;
	}

	function getDataAtto() {
		return $this->data_atto;
	}

	function getNumeroAtto() {
		return $this->numero_atto;
	}


	function getProponente() {
		return $this->proponente;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setPagamento($pagamento) {
		$this->pagamento = $pagamento;
	}

	function setDataAtto($data_atto) {
		$this->data_atto = $data_atto;
	}

	function setNumeroAtto($numero_atto) {
		$this->numero_atto = $numero_atto;
	}

	function setProponente($proponente) {
		$this->proponente = $proponente;
	}
	
	function getImportoAnticipo() {
		return $this->importo_anticipo;
	}

	function getImportoContributo() {
		return $this->importo_contributo;
	}

	function setImportoAnticipo($importo_anticipo) {
		$this->importo_anticipo = $importo_anticipo;
	}

	function setImportoContributo($importo_contributo) {
		$this->importo_contributo = $importo_contributo;
	}

	function getImportiPerProponente() {
		return $this->importi_per_proponente;
	}

	function setImportiPerProponente($importi_per_proponente) {
		$this->importi_per_proponente = $importi_per_proponente;
	}

	
}
