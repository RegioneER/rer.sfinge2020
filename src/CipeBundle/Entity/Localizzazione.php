<?php

namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\CupGenerazione;
use CipeBundle\Entity\Classificazioni\CupStato;

/**
 * Localizzazione
 *
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 * @see http://cb.schema31.it/cb/issue/173187
 * 
 * <!ELEMENT LOCALIZZAZIONE EMPTY>
	<!ATTLIST LOCALIZZAZIONE
          stato CDATA #REQUIRED
          regione CDATA #REQUIRED
          provincia CDATA #REQUIRED
          comune CDATA #REQUIRED>
 */
class Localizzazione extends CipeEntityService
{
    
    // --- ATTRIBUTES ---

    /**
	 * Codice dello stato
     * @var String
     */
    public $stato = null;
	function getStato() { return $this->stato; }
	function setStato($stato) { $this->stato = self::setFilterParam($stato, "string"); }
	function isItalia() { return (ltrim($this->getStato(), "0") == "5"); }
		
    /**
	 * Valorizzazione dell'attributo obbligatoria se indicato lo stato 05 (Italia).
	 * Può essere utilizzato il codice -1 per indicare tutte le regioni. 
     * @var String
     */
    public $regione = null;
	function getRegione() { return $this->regione; }
	function setRegione($regione) { $this->regione = self::setFilterParam($regione, "string"); }



	
    /**
	 * alorizzazione dell'attributo obbligatoria se indicato lo stato 05 (Italia). 
	 * Può essere utilizzato il codice -1 per indicare tutte le province. 
	 * Se indicate tutte le regioni deve obbligatoriamente essere indicato il codice -1. 
     * @var String
     */
    public $provincia = null;
	function getProvincia() {return $this->provincia; }
	function setProvincia($provincia) { $this->provincia = self::setFilterParam($provincia, "string"); }

    /**
	 * Valorizzazione dell'attributo obbligatoria se indicato lo stato 05 (Italia). 
	 * Può essere utilizzato il codice -1 per indicare tutti i comuni. 
	 * Se indicate tutte le province deve obbligatoriamente essere indicato il codice -1.
     * @var String
     */
    public $comune = null;
	function getComune() { return $this->comune; }
	function setComune($comune) { $this->comune = self::setFilterParam($comune, "string"); }
	
	/**
	 * <!ELEMENT LOCALIZZAZIONE EMPTY>
	*/
	public function __construct() {
		parent::__construct(null);
		$this->setXmlName("LOCALIZZAZIONE");
	}
	
	/**
	 ** <!ELEMENT LOCALIZZAZIONE EMPTY>
		<!ATTLIST LOCALIZZAZIONE
          stato CDATA #REQUIRED
          regione CDATA #REQUIRED
          provincia CDATA #REQUIRED
          comune CDATA #REQUIRED>
 	 * @return boolean
	 */
    public function validate(ExecutionContext $context) { 
		$type = "attr";
		$val = $this->getStato();
		if(!$this->isNotNullAndIsNotEmpty($val)) $this->setValidateStatus("stato" , false, $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		if(!$this->validateClassification(new CupStato(), array("codice" => ltrim($val, "0")))) 
			$this->setValidateStatus("stato"	, false	, $type, "[$val] ".self::COMMON_VALIDATE_CODE_NOT_EXIST);

		$pre_ErrorMessage = "per stato Italia ";
		if($this->isItalia()) {
			
			// se val = -1 si intende tutte le regioni/provincie/comuni
			
			$val = $this->getRegione();
			if(!$this->isNotNullAndIsNotEmpty($val)) $this->setValidateStatus("regione"	, false	, $type, $pre_ErrorMessage. self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
			if($val != "-1" && !$this->validateClassification(new \GeoBundle\Entity\GeoRegione(), array("codice" => $val))) 
					$this->setValidateStatus("regione"	, false	, $type, "[$val] ".$pre_ErrorMessage. self::COMMON_VALIDATE_CODE_NOT_EXIST);
			
			$provincia = $this->getProvincia();
			$val = $this->getProvincia();
			if(!$this->isNotNullAndIsNotEmpty($val)) $this->setValidateStatus("provincia", false	, $type, $pre_ErrorMessage. self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
			if($val!="-1" && !$this->validateClassification(new \GeoBundle\Entity\GeoProvincia(), array("codice" => $val))) 
					$this->setValidateStatus("provincia"	, false	, $type, "[$val] ".$pre_ErrorMessage. self::COMMON_VALIDATE_CODE_NOT_EXIST);

			$val = $this->getComune();
			if(!$this->isNotNullAndIsNotEmpty($val)) $this->setValidateStatus("comune"	, false	, $type, $pre_ErrorMessage. self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);

			
			if($val != "-1") {
				if(strlen($val) != 6) 
					$this->setValidateStatus("comune"	, false	, $type, "[$val] ".$pre_ErrorMessage. "il codice comune e' composto da 6 cifre.");
				if($provincia != "-1" && substr($val, 0, -3) != $provincia)
					$this->setValidateStatus("comune"	, false	, $type, "[$val] ".$pre_ErrorMessage. "il codice comune deve comprendere il codice provincia nelle prima 3 cifre.");
				if(!$this->validateClassification(new \GeoBundle\Entity\GeoComune(), array("codice" => substr($val, -3)))) 
						$this->setValidateStatus("comune"	, false	, $type, "[$val] ".$pre_ErrorMessage. self::COMMON_VALIDATE_CODE_NOT_EXIST);
			}		
			
		}
		
		return parent::validate($context);
			
	}
	
	/**
	 * <!ELEMENT LOCALIZZAZIONE EMPTY>
		<!ATTLIST LOCALIZZAZIONE
          stato CDATA #REQUIRED
          regione CDATA #REQUIRED
          provincia CDATA #REQUIRED
          comune CDATA #REQUIRED>
	 * @return String
	 */
	public function serialize() {
		try {
				parent::serialize();
				$nodeName = $this->getXmlName();
				$attributes = array(
										array(
												"attr_name" => "stato",
												"attr_value" => $this->getStato()
										),
										array(
												"attr_name" => "regione",
												"attr_value" => $this->getRegione()
										),					
										array(
												"attr_name" => "provincia",
												"attr_value" => $this->getProvincia()
										),
										array(
												"attr_name" => "comune",
												"attr_value" => $this->getComune()
										),					
									);
				$xml = $this->generateXmlNode($nodeName, $attributes);
				return $xml;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	// --- OPERATIONS ---

} /* end of class Localizzazione */

?>