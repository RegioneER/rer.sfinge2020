<?php

namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\ConcessioneContributiNoUnitaProduttive;
use CipeBundle\Entity\ConcessioneIncentiviUnitaProduttive;
use CipeBundle\Entity\CupGenerazione;
use CipeBundle\Entity\AcquistoBeni;
use CipeBundle\Entity\LavoriPubblici;
use CipeBundle\Entity\RealizzAcquistoServiziRicerca;
use CipeBundle\Entity\RealizzAcquistoServiziNoFormazioneRicerca;
use CipeBundle\Entity\Classificazioni\CupTipoIndirizzo;
use CipeBundle\Entity\Classificazioni\CupStrumentoProgrammazione;

/**
 * TipoDescrizione
 *
 * raccoglietore di elementi comuni tra i seguenti nodi:
 * <!ELEMENT DESCRIZIONE (LAVORI_PUBBLICI | CONCESSIONE_INCENTIVI_UNITA_PRODUTTIVE | REALIZZ_ACQUISTO_SERVIZI_RICERCA | REALIZZ_ACQUISTO_SERVIZI_FORMAZIONE | REALIZZ_ACQUISTO_SERVIZI_NO_FORMAZIONE_RICERCA | ACQUISTO_BENI | PARTECIP_AZIONARIE_CONFERIM_CAPITALE | CONCESSIONE_CONTRIBUTI_NO_UNITA_PRODUTTIVE | CUP_CUMULATIVO)>
 * 
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 */
class TipoDescrizione extends CipeEntityService
{
    
	const TIPO_IND_AREA_RIFER = 1;
	const IND_AREA_RIFER = 2;
	const STRUM_PROGR = 3;
	const DESC_STRUM_PROGR = 4;
	const ALTRE_INFORMAZIONI = 5;
	const FLAG_LEGGE_OBIETTIVO = 6;
	const NUM_DELIBERA_CIPE = 7;
	const ANNO_DELIBERA = 8;
	
	/**
	 * Contiene il tipo dell’indirizzo o area di riferimento (01=Via, 02=Viale, 03=Piazza, 04=Corso, 05=Altro). 
     * @var String
     */
    public $tipo_ind_area_rifer = null;
	function getTipo_ind_area_rifer() {	return $this->tipo_ind_area_rifer; }
	function setTipo_ind_area_rifer($tipo_ind_area_rifer) { $this->tipo_ind_area_rifer = self::setFilterParam($tipo_ind_area_rifer, "string"); }

	/**
	 * Indirizzo del progetto. Lunghezza massima 100 caratteri. Lunghezza minima 5 caratteri. 
	 * Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti. 
	 * Non sono consentiti solo numeri o solo segni matematici. 
	 * @var String
	 */
	public $ind_area_rifer = null;
	function getInd_area_rifer() { return $this->ind_area_rifer; }
	function setInd_area_rifer($ind_area_rifer) { $this->ind_area_rifer = self::setFilterParam($ind_area_rifer, "string"); }
	
	
	  public $strum_progr = null;
	function getStrum_progr() { return $this->strum_progr; }
	function setStrum_progr($strum_progr) { $this->strum_progr = self::setFilterParam($strum_progr, "string"); }

    /**
     * Descrizione dello strumento di programmazione. 
	 * Valorizzazione dell'attributo facoltativa. 
	 * Digitabile solo se il codice dello strumento di programmazione indicato per l'attributo strm_progr è diverso da 00. 
	 * Obbligatorio se se il codice dello strumento di programmazione indicato per l'attributo strm_progr è 99. 
	 * Lunghezza massima 100 caratteri. Lunghezza minima 5 caratteri. 
	 * Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti. 
	 * Non sono consentiti solo numeri o solo segni matematici. 
     * @var String
     */
    public $desc_strum_progr = null;
	function getDesc_strum_progr() { return $this->desc_strum_progr; }
	function setDesc_strum_progr($desc_strum_progr) { $this->desc_strum_progr = self::setFilterParam($desc_strum_progr, "string"); }
	
	
	/**
     * Eventuali altre informazioni relative al progetto. 
	 * Valorizzazione dell'attributo facoltativa. 
	 * Lunghezza massima 4000 caratteri. Lunghezza minima 5 caratteri. 
	 * Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti. 
	 * Non sono consentiti solo numeri o solo segni matematici.
     *
     * @var String
     */
    public $altre_informazioni = null;
	function getAltre_informazioni() { return $this->altre_informazioni; }
	function setAltre_informazioni($altre_informazioni) { $this->altre_informazioni = self::setFilterParam($altre_informazioni, "string"); }
		
	
	/**
     * @var String
     */
	protected $flagLeggeObiettivo = null;
	function getFlagLeggeObiettivo() { return $this->flagLeggeObiettivo; }
	function setFlagLeggeObiettivo($flagLeggeObiettivo) { $this->flagLeggeObiettivo = self::setFilterParam($flagLeggeObiettivo, "string"); }

	/**
     * @var String
     */
	protected $numDeliberaCipe = null;
	function getNumDeliberaCipe() { return $this->numDeliberaCipe; }
	function setNumDeliberaCipe($numDeliberaCipe) { $this->numDeliberaCipe = self::setFilterParam($numDeliberaCipe, "string"); }
	
	/**
     * @var String
     */
	protected $annoDelibera = null;
	function getAnnoDelibera() { return $this->annoDelibera; }
	function setAnnoDelibera($annoDelibera) { $this->annoDelibera = self::setFilterParam($annoDelibera, "string"); }
	
	
	public function __construct() {
		parent::__construct(null);
	}
	
	
	protected $lunghezza_massima_altre_informazioni=100;
	protected function getLunghezza_massima_altre_informazioni() { return $this->lunghezza_massima_altre_informazioni; }
	protected function setLunghezza_massima_altre_informazioni($lunghezza_massima_altre_informazioni) { $this->lunghezza_massima_altre_informazioni = $lunghezza_massima_altre_informazioni; }

	
	protected $campi_obbligatori=null;
	function getCampi_obbligatori() { return $this->campi_obbligatori; }
	function setCampi_obbligatori($campi_obbligatori) { $this->campi_obbligatori = $campi_obbligatori; }

		
	protected function _validate_TipoInd_area_rifer($type) {
		// tipo_ind_area_rifer
		$val = $this->getTipo_ind_area_rifer();
		if(\is_null($val) && !\in_array(self::TIPO_IND_AREA_RIFER, $this->getCampi_obbligatori())) return;
		if(!$this->isNotNullAndIsNotEmpty($this->isNotNullAndIsNotEmpty($val)))  $this->setValidateStatus("tipo_ind_area_rifer" ,false ,$type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		if(!$this->validateClassification(new CupTipoIndirizzo(), array("codice" => $val))) 
			$this->setValidateStatus("tipo_ind_area_rifer"	, false	, $type, "[$val] ". self::COMMON_VALIDATE_CODE_NOT_EXIST);
	}
	
	protected function _validate_Ind_area_rifer($type) {
		// ind_area_rifer
		$val = $this->getInd_area_rifer();
		if(\is_null($val) && !\in_array(self::IND_AREA_RIFER, $this->getCampi_obbligatori())) return;
		$this->commonValidateStringParam($type, $val, "ind_area_rifer", 5, 100);
	}
	
	
	protected function _validate_Strum_progr($type) {
		// strum_progr
		$val = $this->getStrum_progr();
		if(\is_null($val) && !\in_array(self::STRUM_PROGR, $this->getCampi_obbligatori())) return;
		if(!$this->isNotNullAndIsNotEmpty($val)) $this->setValidateStatus("strum_progr", false, $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		if(!$this->validateClassification(new CupStrumentoProgrammazione(), array("codice" => $val)))	
		$this->setValidateStatus("strum_progr"	,false, $type, "[$val] " . self::COMMON_VALIDATE_CODE_NOT_EXIST);
	}
	
	
	protected function _validate_Desc_strum_progr($type) {
		$val = $this->getDesc_strum_progr();
		if(\is_null($val) && \in_array(self::DESC_STRUM_PROGR, $this->getCampi_obbligatori())) 
			$this->setValidateStatus("desc_strum_progr" ,false ,$type, "Tale elemento non può essere vuoto");	

		if(!\is_null($val)) {
			$this->commonValidateStringParam($type, $val, "desc_strum_progr", 5, 100);
		} else {
			if($this->getStrum_progr() == "99") $this->setValidateStatus("desc_strum_progr" ,false ,$type, "Tale elemento non può essere vuoto se strum_prog=99");
		}
	}
		
	
	public function _validate_Altre_informazioni($type) {
		$val = $this->getAltre_informazioni();
		if(!\is_null($val)) {
			$this->commonValidateStringParam($type, $val, "altre_informazioni", 5, $this->getLunghezza_massima_altre_informazioni());
		}
	}
	
	
	
	public function validate(ExecutionContext $context) { 
		$type = "attr";
		
		$this->_validate_TipoInd_area_rifer($type);
		
		$this->_validate_Ind_area_rifer($type);
			
		$this->_validate_Strum_progr($type);
		
		$this->_validate_Desc_strum_progr($type);
			
		$this->_validate_Altre_informazioni($type);
		

		return parent::validate($context);
	}
    

} /* end of class TipoDescrizione */

