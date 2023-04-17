<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 *
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Istruttoria\RipartizioneImportiPagamentoRepository")
 * @ORM\Table(name="ripartizioni_importi_pagamenti_beneficiario")
 */
class RipartizioneImportiPagamentoBeneficiario extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="ripartizioni_importi_pagamento_beneficiario")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $pagamento;
	
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
		return "RipartizioneImportiPagamentoBeneficiario";
	}
	
	function getId() {
		return $this->id;
	}

	function getPagamento() {
		return $this->pagamento;
	}

	function getImportoContributo() {
		return $this->importo_contributo;
	}

	function getProponente() {
		return $this->proponente;
	}

	function getImportiPerProponente() {
		return $this->importi_per_proponente;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setPagamento($pagamento) {
		$this->pagamento = $pagamento;
	}

	function setImportoContributo($importo_contributo) {
		$this->importo_contributo = $importo_contributo;
	}

	function setProponente($proponente) {
		$this->proponente = $proponente;
	}

	function setImportiPerProponente($importi_per_proponente) {
		$this->importi_per_proponente = $importi_per_proponente;
	}


}
