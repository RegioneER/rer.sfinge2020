<?php

namespace AttuazioneControlloBundle\Entity\Bando_8;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use AttuazioneControlloBundle\Entity\EstensionePagamento;

/**
 * @ORM\Entity()
 * @ORM\Table(name="estensioni_pagamenti_bando_8")
 */
class EstensionePagamentoBando_8 extends EstensionePagamento {

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $descrizione_prototipo;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $altre_informazioni;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $descrizione_attivita_realizzate;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $descrizione_attivita_realizzate_or5;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $scostamenti_modifiche;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $contributo_imprese;
	
	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istruttoria_relazione_tecnica; 
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $relazione_tecnica_sintetica;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $conclusione_sviluppi_futuri;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Bando_8\DocumentoLavorazione774", mappedBy="estensione_pagamento_bando8")
	 */
	protected $documenti_lavorazioni;  
	
	public function getDescrizionePrototipo() {
		return $this->descrizione_prototipo;
	}

	public function setDescrizionePrototipo($descrizione_prototipo) {
		$this->descrizione_prototipo = $descrizione_prototipo;
	}
	
	public function getAltreInformazioni() {
		return $this->altre_informazioni;
	}

	public function setAltreInformazioni($altre_informazioni) {
		$this->altre_informazioni = $altre_informazioni;
	}
	
	public function getDescrizioneAttivitaRealizzate() {
		return $this->descrizione_attivita_realizzate;
	}

	public function getScostamentiModifiche() {
		return $this->scostamenti_modifiche;
	}

	public function getContributoImprese() {
		return $this->contributo_imprese;
	}

	public function setDescrizioneAttivitaRealizzate($descrizione_attivita_realizzate) {
		$this->descrizione_attivita_realizzate = $descrizione_attivita_realizzate;
	}

	public function setScostamentiModifiche($scostamenti_modifiche) {
		$this->scostamenti_modifiche = $scostamenti_modifiche;
	}

	public function setContributoImprese($contributo_imprese) {
		$this->contributo_imprese = $contributo_imprese;
	}

	public function getDescrizioneAttivitaRealizzateOr5() {
		return $this->descrizione_attivita_realizzate_or5;
	}

	public function setDescrizioneAttivitaRealizzateOr5($descrizione_attivita_realizzate_or5) {
		$this->descrizione_attivita_realizzate_or5 = $descrizione_attivita_realizzate_or5;
	}


	function getIstruttoriaRelazioneTecnica() {
		return $this->istruttoria_relazione_tecnica;
	}

	function setIstruttoriaRelazioneTecnica($istruttoria_relazione_tecnica) {
		$this->istruttoria_relazione_tecnica = $istruttoria_relazione_tecnica;
	}
	
	function getRelazioneTecnicaSintetica() {
		return $this->relazione_tecnica_sintetica;
	}

	function setRelazioneTecnicaSintetica($relazione_tecnica_sintetica) {
		$this->relazione_tecnica_sintetica = $relazione_tecnica_sintetica;
	}

	function getConclusioneSviluppiFuturi() {
		return $this->conclusione_sviluppi_futuri;
	}

	function setConclusioneSviluppiFuturi($conclusione_sviluppi_futuri) {
		$this->conclusione_sviluppi_futuri = $conclusione_sviluppi_futuri;
	}
	
	public function __construct() {
		parent::__construct();
		$this->documenti_lavorazioni = new ArrayCollection();
	}

	
	public function getDocumentiLavorazioni() {
		return $this->documenti_lavorazioni;
	}

	public function setDocumentiLavorazioni($documenti_lavorazioni) {
		$this->documenti_lavorazioni = $documenti_lavorazioni;
	}


}
