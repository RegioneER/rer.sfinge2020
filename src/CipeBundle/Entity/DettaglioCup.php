<?php

namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
Use CipeBundle\Entity\DatiGeneraliProgetto;
use CipeBundle\Entity\RispostaCupGenerazione;



/**
 * Short description of class DettaglioCup
 * <!ELEMENT DETTAGLIO_CUP (CODICE_CUP, DATI_GENERALI_PROGETTO, MASTER, DESCRIZIONE, ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007, DATI_TITOLARE_RICHIEDENTE, (LOCALIZZAZIONE)+, (FINANZIAMENTO)?, (INDICATORE)+)>
	<!ELEMENT CODICE_CUP (#PCDATA)*>
	<!ELEMENT DATI_GENERALI_PROGETTO EMPTY>

 *
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 */
class DettaglioCup extends CipeEntityService
{
    /**
     * @var String
     */
    protected $CodiceCup = null;
	function getCodiceCup() { return $this->CodiceCup; }
	function setCodiceCup($CodiceCup) { $this->CodiceCup = self::setFilterParam($CodiceCup, "string"); }

    /**
     * @var DatiGeneraliProgetto
     */
    protected $DatiGeneraliProgetto = null;
	function getDatiGeneraliProgetto() { return $this->DatiGeneraliProgetto; }
	function setDatiGeneraliProgetto(DatiGeneraliProgetto $DatiGeneraliProgetto) { $this->DatiGeneraliProgetto = $DatiGeneraliProgetto; }

	public function __construct() {
		parent::__construct(null);
		$this->setXmlName("DETTAGLIO_CUP");
	}
	
    public function validate(ExecutionContext $context) { 
		 // non si ritiene necessaria la validazione su elementi di risposta
		return parent::validate($context);
	}
	
	public function serialize() {
		try {
				parent::serialize();
				$nodeName = $this->getXmlName();
				$attributes = array();
				$value=null;
				$innerElements = array(
										array(
												"nodeName"	=> "CODICE_CUP",
												"value"		=> $this->getCodiceCup()
										),
										array(
												"nodeName"	=> null,
												"value"		=> $this->getDatiGeneraliProgetto()->serialize()
										)

				);
				
				$xml = $this->generateXmlNode($nodeName, $attributes, $value, $innerElements);
				return $xml;
				
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	public function deserialize($xml) {
		try {
			$xml = parent::deserialize($xml);
			$CodiceCup = (string) $xml->CODICE_CUP;
			$this->setCodiceCup($CodiceCup);
			return $this;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	// --- OPERATIONS ---

} /* end of class DettaglioCup */

?>