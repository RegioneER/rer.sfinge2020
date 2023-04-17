<?php

namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\CupGenerazione;
use CipeBundle\Entity\Classificazioni\CupTipoCoperturaFinanziaria;


/**
 * Short description of class Finanziamento
 *
 * <xs:element name="DATI_CUP">
		<xs:complexType>
			<xs:sequence>
				<xs:element ref="CODICE_CUP"/>
				<xs:element ref="ELENCO_CUP_CON_DELEGHE" minOccurs="0" maxOccurs="unbounded"/>
				<xs:element ref="ELENCO_CUP_SIMILI" minOccurs="0" maxOccurs="unbounded"/>
				<xs:element ref="ELENCO_CUP_SENZA_NATURA_PRIVATA" minOccurs="0"/>
			</xs:sequence>
		</xs:complexType>
	</xs:element>
 * @see http://cb.schema31.it/cb/issue/191756
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 */
class DatiCup extends CipeEntityService
{
   
	public $codice_cup;
	function getCodice_cup() { return $this->codice_cup; }
	function setCodice_cup($codice_cup) { $this->codice_cup = self::setFilterParam($codice_cup, "string"); }
	
	    
	public function __construct() {
		parent::__construct(null);
		$this->setXmlName("CODICE_CUP");
	}
	
	/**
	 * 
	 * <!ELEMENT DATI_CUP (CODICE_CUP, ELENCO_CUP_CON_DELEGHE*, ELENCO_CUP_SIMILI*, ELENCO_CUP_SENZA_NATURA_PRIVATA?)>
		<!ELEMENT CODICE_CUP (#PCDATA)*>
	 */
    public function validate(ExecutionContext $context) { 
		
		
		$type = "inner";
		$this->setValidateStatus("CODICE_CUP"		, $this->isNotNullAndIsNotEmpty($this->getCodice_cup()) , $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		return parent::validate($context);
	}
	
	
	/**
	 * <!ELEMENT DATI_CUP (CODICE_CUP, ELENCO_CUP_CON_DELEGHE*, ELENCO_CUP_SIMILI*, ELENCO_CUP_SENZA_NATURA_PRIVATA?)>
		<!ELEMENT CODICE_CUP (#PCDATA)*>
	 * 
	 * @return string
	 * @throws \Exception
	 */
	public function serialize() {
		try {
			parent::serialize();
			$nodeName = $this->getXmlName();
			$attributes = array(
							
								);
			
			$innerElements 
						= array(
										array(
												"nodeName"	=> 'CODICE_CUP',
												"value"		=> $this->getCodice_cup()
										)
								);
			
			$xml = $this->generateXmlNode($nodeName, $attributes, null, $innerElements);
			return $xml;
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	
	

} /* end of class Finanziamento */

?>