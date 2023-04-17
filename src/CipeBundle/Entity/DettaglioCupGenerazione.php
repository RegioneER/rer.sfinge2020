<?php

namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\CupGenerazione;
use CipeBundle\Entity\Classificazioni\CupTipoCoperturaFinanziaria;
use CipeBundle\Entity\DatiCup;

/**
 * Short description of class DettaglioCupGenerazione
 *
 * <xs:element name="DETTAGLIO_CUP_GENERAZIONE">
		<xs:complexType>
			<xs:choice>
				<xs:element ref="DATI_CUP"/>
				<xs:element ref="MESSAGGI_DI_SCARTO"/>
			</xs:choice>
			<xs:attribute name="id_progetto" type="xs:string" use="required"/>
			<xs:attribute name="codifica_locale" type="xs:string" use="required"/>
		</xs:complexType>
	</xs:element>
 * @see http://cb.schema31.it/cb/issue/191756
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 */
class DettaglioCupGenerazione extends CipeEntityService
{
   
    // --- ATTRIBUTES ---
	public $id_progetto;
	function getId_progetto() { return $this->id_progetto; }
	function setId_progetto($id_progetto) { $this->id_progetto = self::setFilterParam($id_progetto, "string"); }

		
	public $codifica_locale;
	function getCodifica_locale() { return $this->codifica_locale; }
	function setCodifica_locale($codifica_locale) { $this->codifica_locale = self::setFilterParam($codifica_locale, "string"); }

	/**
	 * @var DatiCup
	 */
	public $dati_cup;
	function getDati_cup() { return $this->dati_cup; }
	function setDati_cup(DatiCup $dati_cup) { $this->dati_cup = $dati_cup; }

	
	public $messaggi_di_scarto;
	function getMessaggi_di_scarto() { return $this->messaggi_di_scarto; }
	function setMessaggi_di_scarto($messaggi_di_scarto) { $this->messaggi_di_scarto = self::setFilterParam($messaggi_di_scarto, "string"); }
	function addMessaggio_di_scarto($messaggio_di_scarto) {
		$messaggi_di_scarto = $this->getMessaggi_di_scarto();
		$messaggi_di_scarto[] = $messaggio_di_scarto;
		$this->setMessaggi_di_scarto($messaggi_di_scarto);
	}
	
	
	public function __construct() {
		parent::__construct(null);
		$this->setXmlName("DETTAGLIO_CUP_GENERAZIONE");
		$this->setMessaggi_di_scarto(array());
	}
	
	/**
	 * <!ELEMENT DETTAGLIO_CUP_GENERAZIONE (DATI_CUP | MESSAGGI_DI_SCARTO)>
		<!ATTLIST DETTAGLIO_CUP_GENERAZIONE
          id_progetto CDATA #REQUIRED
          codifica_locale CDATA #REQUIRED>
	 */
    public function validate(ExecutionContext $context) { 
		
		$type = "attr";
		$this->setValidateStatus("id_progetto"		, $this->isNotNullAndIsNotEmpty($this->getId_progetto()) , $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		$this->setValidateStatus("codifica_locale"	, $this->isNotNullAndIsNotEmpty($this->getCodifica_locale()) , $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);

		
		$type = "inner";
		if(!\is_null($this->getDati_cup())) $this->setValidateStatus("DATI_CUP", $this->getDati_cup()->validate($context), $type);
		else {
			foreach ($this->getMessaggi_di_scarto() as $messaggio_scarto) {
				$this->setValidateStatus("MESSAGGI_DI_SCARTO"	, $this->isNotNullAndIsNotEmpty($messaggio_scarto) , $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
			}
		} 
			

		return parent::validate($context);
	}
	
	
	/**
	 * <<!ELEMENT DETTAGLIO_CUP_GENERAZIONE (DATI_CUP | MESSAGGI_DI_SCARTO)>
		<!ATTLIST DETTAGLIO_CUP_GENERAZIONE
          id_progetto CDATA #REQUIRED
          codifica_locale CDATA #REQUIRED>
	 * 
	 * @return string
	 * @throws \Exception
	 */
	public function serialize() {
		try {
			parent::serialize();
			$nodeName = $this->getXmlName();
			$attributes = array(
									array(
											"attr_name" => "id_progetto",
											"attr_value" => $this->getId_progetto()
									),
									array(
											"attr_name" => "codifica_locale",
											"attr_value" => $this->getCodifica_locale()
									),
								);
			
						
			$innerElements 
						= array(
										array(
												"nodeName"	=> null,
												"value"		=> $this->getDati_cup()->serialize()
										),
										array(
												"nodeName"	=> 'MESSAGGI_DI_SCARTO',
												"value"		=> $this->getMessaggi_di_scarto()
										),

							
								);
			
			$xml = $this->generateXmlNode($nodeName, $attributes, null, $innerElements);
			return $xml;
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	// TODO VERIFICA
	
	public function deserialize($xmlp) {
		try {
			$xml = parent::deserialize($xmlp);
			$xml_dati_cup = (string) $xml->DATI_CUP;
			if(!\is_null($xml_dati_cup) && strlen($xml_dati_cup)>0) {
				$dati_cup = new DatiCup();
				$dati_cup->deserialize($xml_dati_cup);
				$this->setDati_cup($dati_cup);
			}
			$xml_nodi_messaggi_scarto = $xml->MESSAGGI_DI_SCARTO;
			foreach ($xml_nodi_messaggi_scarto as $xml_messaggio_scarto) {
				$messaggio_di_scarto = (string) $xml_messaggio_scarto;
				if(!\is_null($messaggio_di_scarto) && strlen($messaggio_di_scarto) > 0) $this->addMessaggio_di_scarto($messaggio_di_scarto);
			}
			
			return $this;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	
	
	

} /* end of class DettaglioCupGenerazione */

