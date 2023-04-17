<?php


namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\Descrizione;
use CipeBundle\Entity\Classificazioni\CupTipoIndirizzo;
use CipeBundle\Entity\Classificazioni\CupStrumentoProgrammazione;
use CipeBundle\Entity\TipoDescrizione;



/**
 * ConcessioneIncentiviUnitaProduttive
 * <!ELEMENT CONCESSIONE_INCENTIVI_UNITA_PRODUTTIVE EMPTY>
	<!ATTLIST CONCESSIONE_INCENTIVI_UNITA_PRODUTTIVE
          denominazione_impresa_stabilimento CDATA #REQUIRED
          partita_iva CDATA #REQUIRED
          denominazione_impresa_stabilimento_prec CDATA #IMPLIED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          descrizione_intervento CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
 *
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 * @see http://cb.schema31.it/cb/issue/173243
 */
class ConcessioneIncentiviUnitaProduttive extends TipoDescrizione
{
    /**
	 * Denominazione dell’impresa o stabilimento coinvolto nel progetto. 
	 * Lunghezza massima 100 caratteri. Lunghezza minima 5 caratteri. 
	 * Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti. 
	 * Non sono consentiti solo numeri o solo segni matematici. 
     * @var String
     */
    public $denominazione_impresa_stabilimento = null;
	function getDenominazione_impresa_stabilimento() { return $this->denominazione_impresa_stabilimento; }
	function setDenominazione_impresa_stabilimento($denominazione_impresa_stabilimento) { $this->denominazione_impresa_stabilimento = self::setFilterParam($denominazione_impresa_stabilimento, "string"); }
	
    /**
	 * Partita IVA o codice fiscale dell’impresa o dello stabilimento coinvolto nel progetto. 
     * @var String
     */
    public $partita_iva = null;
	function getPartita_iva() { return $this->partita_iva; }
	function setPartita_iva($partita_iva) { $this->partita_iva = self::setFilterParam($partita_iva, "string"); }

	/**
	 * Precedente denominazione dell’impresa o stabilimento coinvolto nel progetto. 
	 * Valorizzazione dell'attributo opzionale. 
	 * Lunghezza massima 100 caratteri. 
	 * Lunghezza minima 5 caratteri. 
	 * Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti. 
	 * Non sono consentiti solo numeri o solo segni matematici. 
     * @var String
     */
    public $denominazione_impresa_stabilimento_prec = null;
	function getDenominazione_impresa_stabilimento_prec() { return $this->denominazione_impresa_stabilimento_prec; }
	function setDenominazione_impresa_stabilimento_prec($denominazione_impresa_stabilimento_prec) { $this->denominazione_impresa_stabilimento_prec = self::setFilterParam($denominazione_impresa_stabilimento_prec, "string"); }

    
	/**
     * Descrizione dell’intervento. 
	 * Lunghezza massima 100 caratteri. 
	 * Lunghezza minima 5 caratteri. 
	 * Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti. 
	 * Non sono consentiti solo numeri o solo segni matematici. 
     *
     * @var String
     */
    public $descrizione_intervento = null;
	function getDescrizione_intervento() { return $this->descrizione_intervento; }
	function setDescrizione_intervento($descrizione_intervento) { $this->descrizione_intervento = self::setFilterParam($descrizione_intervento, "string"); }

    
	
    // --- OPERATIONS ---

	
	public function __construct() {
		parent::__construct();
		$this->setXmlName("CONCESSIONE_INCENTIVI_UNITA_PRODUTTIVE");
	}
	
	/**
	 * <!ELEMENT CONCESSIONE_INCENTIVI_UNITA_PRODUTTIVE EMPTY>
	<!ATTLIST CONCESSIONE_INCENTIVI_UNITA_PRODUTTIVE
          denominazione_impresa_stabilimento CDATA #REQUIRED
          partita_iva CDATA #REQUIRED
          denominazione_impresa_stabilimento_prec CDATA #IMPLIED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          descrizione_intervento CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
	 * @return boolean
	 */
    public function validate(ExecutionContext $context) { 
		$type = "attr";
		// denominazione_impresa_stabilimento
		$val = $this->getDenominazione_impresa_stabilimento();
		$this->commonValidateStringParam($type, $val, "denominazione_impresa_stabilimento", 5, 100);

		// partita_iva
		$val = $this->getPartita_iva();
		$this->commonValidateStringParam($type, $val, "partita_iva", 11, 16, false);
		
		// denominazione_impresa_stabilimento_prec
		$val = $this->getPartita_iva();
		if(!\is_null($val)) {
			$this->commonValidateStringParam($type, $val, "denominazione_impresa_stabilimento_prec", 5, 255);
		}

		// descrizione_intervento
		$val = $this->getDescrizione_intervento();
		$this->commonValidateStringParam($type, $val, "descrizione_intervento", 5, 255);

		$this->setCampi_obbligatori(array(self::TIPO_IND_AREA_RIFER, self::IND_AREA_RIFER, self::STRUM_PROGR));
		$this->setLunghezza_massima_altre_informazioni(100);
		return parent::validate($context);
	}
	
	/**
	 * * <!ELEMENT CONCESSIONE_INCENTIVI_UNITA_PRODUTTIVE EMPTY>
	<!ATTLIST CONCESSIONE_INCENTIVI_UNITA_PRODUTTIVE
          denominazione_impresa_stabilimento CDATA #REQUIRED
          partita_iva CDATA #REQUIRED
          denominazione_impresa_stabilimento_prec CDATA #IMPLIED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          descrizione_intervento CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
	 * @return String
	 * @throws \Exception
	 */
	public function serialize() {
		try {
			parent::serialize();
			$nodeName = $this->getXmlName();
			$attributes = array(
									array(
											"attr_name" => "denominazione_impresa_stabilimento",
											"attr_value" => $this->getDenominazione_impresa_stabilimento()
									),
									array(
											"attr_name" => "partita_iva",
											"attr_value" => $this->getPartita_iva()
									),					
									array(
											"attr_name" => "denominazione_impresa_stabilimento_prec",
											"attr_value" => $this->getDenominazione_impresa_stabilimento_prec()
									),
									array(
											"attr_name" => "tipo_ind_area_rifer",
											"attr_value" => $this->getTipo_ind_area_rifer()
									),
									array(
											"attr_name" => "ind_area_rifer",
											"attr_value" => $this->getInd_area_rifer()
									),
				
									array(
											"attr_name" => "descrizione_intervento",
											"attr_value" => $this->getDescrizione_intervento()
									),
									array(
											"attr_name" => "strum_progr",
											"attr_value"	=> $this->getStrum_progr(),
									),
									array(
											"attr_name" => "desc_strum_progr",
											"attr_value"	=> $this->getDesc_strum_progr(),
									),
									array(
											"attr_name" => "altre_informazioni",
											"attr_value" => $this->getAltre_informazioni()
									),
				
									array(
											"attr_name" => "flagLeggeObiettivo",
											"attr_value" => $this->getFlagLeggeObiettivo()
									),
				
									array(
											"attr_name" => "numDeliberaCipe",
											"attr_value" => $this->getNumDeliberaCipe()
									),
				
									array(
											"attr_name" => "annoDelibera",
											"attr_value" => $this->getAnnoDelibera()
									)
								);
			
			$xml = $this->generateXmlNode($nodeName, $attributes);
			return $xml;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
} /* end of class ConcessioneIncentiviUnitaProduttive */

?>