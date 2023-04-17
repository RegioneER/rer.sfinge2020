<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\PagamentoRepository")
 * @ORM\Table(name="durc_pagamenti")
 */
class DurcPagamento extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="durc")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $pagamento;
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $dati_variati;
	
	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $email_pec;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $impresa_iscritta_inps;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $matricola_inps;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $impresa_iscritta_inps_di;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $impresa_iscritta_inail;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $numero_codice_ditta_impresa_assicurata;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $impresa_iscritta_inail_di;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $ccnl;

	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istruttoria_oggetto_pagamento; 	
	
	public function getId() {
		return $this->id;
	}

	public function getPagamento() {
		return $this->pagamento;
	}

	public function getDatiVariati() {
		return $this->dati_variati;
	}

	public function getImpresaIscrittaInps() {
		return $this->impresa_iscritta_inps;
	}

	public function getMatricolaInps() {
		return $this->matricola_inps;
	}

	public function getImpresaIscrittaInpsDi() {
		return $this->impresa_iscritta_inps_di;
	}

	public function getImpresaIscrittaInail() {
		return $this->impresa_iscritta_inail;
	}

	public function getNumeroCodiceDittaImpresaAssicurata() {
		return $this->numero_codice_ditta_impresa_assicurata;
	}

	public function getImpresaIscrittaInailDi() {
		return $this->impresa_iscritta_inail_di;
	}

	public function getCcnl() {
		return $this->ccnl;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setPagamento($pagamento) {
		$this->pagamento = $pagamento;
	}

	public function setDatiVariati($dati_variati) {
		$this->dati_variati = $dati_variati;
	}

	public function setImpresaIscrittaInps($impresa_iscritta_inps) {
		$this->impresa_iscritta_inps = $impresa_iscritta_inps;
	}

	public function setMatricolaInps($matricola_inps) {
		$this->matricola_inps = $matricola_inps;
	}

	public function setImpresaIscrittaInpsDi($impresa_iscritta_inps_di) {
		$this->impresa_iscritta_inps_di = $impresa_iscritta_inps_di;
	}

	public function setImpresaIscrittaInail($impresa_iscritta_inail) {
		$this->impresa_iscritta_inail = $impresa_iscritta_inail;
	}

	public function setNumeroCodiceDittaImpresaAssicurata($numero_codice_ditta_impresa_assicurata) {
		$this->numero_codice_ditta_impresa_assicurata = $numero_codice_ditta_impresa_assicurata;
	}

	public function setImpresaIscrittaInailDi($impresa_iscritta_inail_di) {
		$this->impresa_iscritta_inail_di = $impresa_iscritta_inail_di;
	}

	public function setCcnl($ccnl) {
		$this->ccnl = $ccnl;
	}
	
	public function getEmailPec() {
		return $this->email_pec;
	}

	public function setEmailPec($email_pec) {
		$this->email_pec = $email_pec;
	}

	function getIstruttoriaOggettoPagamento() {
		return $this->istruttoria_oggetto_pagamento;
	}

	function setIstruttoriaOggettoPagamento($istruttoria_oggetto_pagamento) {
		$this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
	}
	
}
