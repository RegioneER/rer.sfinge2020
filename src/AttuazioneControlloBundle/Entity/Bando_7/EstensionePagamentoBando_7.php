<?php

namespace AttuazioneControlloBundle\Entity\Bando_7;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use AttuazioneControlloBundle\Entity\EstensionePagamento;

/**
 * @ORM\Entity()
 * @ORM\Table(name="estensioni_pagamenti_bando_7")
 */
class EstensionePagamentoBando_7 extends EstensionePagamento {

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $descrizione_prototipo;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $altre_informazioni;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Bando_7\DocumentoLavorazione773", mappedBy="estensione_pagamento_bando7")
	 */
	protected $documenti_lavorazioni;    
	
	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istruttoria_relazione_tecnica; 

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $rinuncia_maggiorazione;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $note;

	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istruttoria_773_dichiarazioni_proponenti; 
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $maggiorazione_non_liquidabile;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $maggiorazione_incremento_occupazionale_non_liquidabile;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $riparametrizzazione_30_percento;

	/**
	 * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
	 */
	protected $importo_spettante_saldo_definitivo;
    
    /**
	 * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
	 */
	protected $importo_ri_ammesso;
    
    /**
	 * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
	 */
	protected $importo_sp_ammesso;

	
	public function __construct() {
		parent::__construct();
		$this->documenti_lavorazioni = new ArrayCollection();
	}

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
	
	public function getDocumentiLavorazioni() {
		return $this->documenti_lavorazioni;
	}

	public function setDocumentiLavorazioni($documenti_lavorazioni) {
		$this->documenti_lavorazioni = $documenti_lavorazioni;
	}
	
	function getIstruttoriaRelazioneTecnica() {
		return $this->istruttoria_relazione_tecnica;
	}

	function setIstruttoriaRelazioneTecnica($istruttoria_relazione_tecnica) {
		$this->istruttoria_relazione_tecnica = $istruttoria_relazione_tecnica;
	}	

	function getRinunciaMaggiorazione() {
		return $this->rinuncia_maggiorazione;
	}

	function getNote() {
		return $this->note;
	}

	function setRinunciaMaggiorazione($rinuncia_maggiorazione) {
		$this->rinuncia_maggiorazione = $rinuncia_maggiorazione;
	}

	function setNote($note) {
		$this->note = $note;
	}

	function getMaggiorazioneNonLiquidabile() {
		return $this->maggiorazione_non_liquidabile;
	}

	function setMaggiorazioneNonLiquidabile($maggiorazione_non_liquidabile) {
		$this->maggiorazione_non_liquidabile = $maggiorazione_non_liquidabile;
	}	
	
	function getIstruttoria773DichiarazioniProponenti() {
		return $this->istruttoria_773_dichiarazioni_proponenti;
	}

	function setIstruttoria773DichiarazioniProponenti($istruttoria_773_dichiarazioni_proponenti) {
		$this->istruttoria_773_dichiarazioni_proponenti = $istruttoria_773_dichiarazioni_proponenti;
	}

	function getMaggiorazioneIncrementoOccupazionaleNonLiquidabile() {
		return $this->maggiorazione_incremento_occupazionale_non_liquidabile;
	}

	function getRiparametrizzazione30Percento() {
		return $this->riparametrizzazione_30_percento;
	}

	function setMaggiorazioneIncrementoOccupazionaleNonLiquidabile($maggiorazione_incremento_occupazionale_non_liquidabile) {
		$this->maggiorazione_incremento_occupazionale_non_liquidabile = $maggiorazione_incremento_occupazionale_non_liquidabile;
	}

	function setRiparametrizzazione30Percento($riparametrizzazione_30_percento) {
		$this->riparametrizzazione_30_percento = $riparametrizzazione_30_percento;
	}

	function getImportoSpettanteSaldoDefinitivo() {
		return $this->importo_spettante_saldo_definitivo;
	}

	function setImportoSpettanteSaldoDefinitivo($importo_spettante_saldo_definitivo) {
		$this->importo_spettante_saldo_definitivo = $importo_spettante_saldo_definitivo;
	}	
    
    public function getImportoRiAmmesso() {
        return $this->importo_ri_ammesso;
    }

    public function getImportoSpAmmesso() {
        return $this->importo_sp_ammesso;
    }

    public function setImportoRiAmmesso($importo_ri_ammesso) {
        $this->importo_ri_ammesso = $importo_ri_ammesso;
    }

    public function setImportoSpAmmesso($importo_sp_ammesso) {
        $this->importo_sp_ammesso = $importo_sp_ammesso;
    }
	
}
