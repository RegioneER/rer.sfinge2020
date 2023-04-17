<?php

namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\CupGenerazione;
use CipeBundle\Entity\RispostaCupGenerazione;
use CipeBundle\Entity\WsGeneraCup;

use Doctrine\ORM\Mapping as ORM;

/**
 * Short description of class RichiestaCupGenerazione
 * <!ELEMENT RICHIESTA_GENERAZIONE_CUP (ID_RICHIESTA, USER, PASSWORD, CUP_GENERAZIONE)>
 * 
 * @see http://cb.schema31.it/cb/issue/173536
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 */
class RichiestaCupGenerazione extends CipeEntityService
{
    
    /**
	 * (obbligatorio) : All’interno di questo TAG deve essere inserito un progressivo numerico. 
     * @var Integer
     */
    protected $IdRichiesta = null;
	function getIdRichiesta() { return $this->IdRichiesta; }
	function setIdRichiesta($IdRichiesta) { $this->IdRichiesta = self::setFilterParam($IdRichiesta, "integer"); }

    /**
     * (obbligatorio) : All’interno di questo TAG deve essere inserita la UserID dell’utente che che verrà utilizzata per la generazione del CUP. 
     * @var String
     */
    protected $User = null;
	function getUser() { return $this->User; }
	function setUser($User) { $this->User = self::setFilterParam($User, "string"); }

    /**
     * (obbligatorio) : All’interno di questo tag deve essere inserita la PASSWORD corrispondente alla UserID utilizzata. 
     * @var String
     */
    protected $Password = null;
	function getPassword() { return $this->Password; }
	function setPassword($Password) { $this->Password = self::setFilterParam($Password, "string"); }

    /**
     * (XML) : All’interno di questo tag deve essere inserito il codice XML contenente tutti i dati necessari alla generazione del CUP.
     *
     * @var CupGenerazione
	 * @see http://cb.schema31.it/cb/issue/173537
     */
    protected $CupGenerazione = null;
    function getCupGenerazione() { return $this->CupGenerazione; }
    function setCupGenerazione( CupGenerazione $CupGenerazione) { $this->CupGenerazione = $CupGenerazione; }

    /**
     * Short description of attribute TimestampUltimaRichiesta
     *
     * @var \DateTime
     */
    protected $TimestampUltimaRichiesta = null;
	function getTimestampUltimaRichiesta() { return $this->TimestampUltimaRichiesta; }
	function setTimestampUltimaRichiesta($TimestampUltimaRichiesta) { $this->TimestampUltimaRichiesta = self::setFilterParam($TimestampUltimaRichiesta, "datetime"); }

    /**
     * Rappresenta la risposta associata alla richiesta
     *
     * @var RispostaCupGenerazione
	 * @see http://cb.schema31.it/cb/issue/173545
     */
    protected $Risposta = null;
	function getRisposta() { return $this->Risposta; }
	function setRisposta(RispostaCupGenerazione $Risposta) { $this->Risposta = $Risposta; }
	
	
	/**
	 *	<!ELEMENT RICHIESTA_GENERAZIONE_CUP (ID_RICHIESTA, USER, PASSWORD, CUP_GENERAZIONE)>
	 */
	public function __construct() {
		parent::__construct(null);
		$this->setXmlName("RICHIESTA_GENERAZIONE_CUP");
	}


	/**
	 * 		<!ELEMENT RICHIESTA_GENERAZIONE_CUP (ID_RICHIESTA, USER, PASSWORD, CUP_GENERAZIONE)>

	 */
    public function validate(ExecutionContext $context) { 
		$type = "inner";
		$message = self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY;
		$this->setValidateStatus("ID_RICHIESTA"		, $this->isNotNullAndIsNotEmpty($this->getIdRichiesta())	, $type, $message);
		$this->setValidateStatus("USER"				, $this->isNotNullAndIsNotEmpty($this->getUser())			, $type, $message);
		$this->setValidateStatus("PASSWORD"			, $this->isNotNullAndIsNotEmpty($this->getPassword())		, $type, $message);
		$this->setValidateStatus("CUP_GENERAZIONE"	, $this->getCupGenerazione()->validate($context)	, $type, self::COMMON_VALIDATE_MESSAGE);
		return parent::validate($context);
	}
	
		
	/**
	 * <?xml version="1.0" encoding="UTF-8"?>
		<!ELEMENT RICHIESTA_GENERAZIONE_CUP (ID_RICHIESTA, USER, PASSWORD, CUP_GENERAZIONE)>
		<!ELEMENT ID_RICHIESTA (#PCDATA)*>
		<!ELEMENT USER (#PCDATA)*>
		<!ELEMENT PASSWORD (#PCDATA)*>
		<!ELEMENT CUP_GENERAZIONE (DATI_GENERALI_PROGETTO, MASTER?, LOCALIZZAZIONE+, DESCRIZIONE, ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007?, FINANZIAMENTO)>
	 * @return string - xml
	 * @throws \Exception
	 */
	public function serialize() {
		try {
				parent::serialize();
				$nodeName = $this->getXmlName();
				$attributes = array();
				$value=null;
				$innerElements = array(
										array(
												"nodeName"	=> "ID_RICHIESTA",
												"value"		=> $this->getIdRichiesta()
										),
										array(
												"nodeName"	=> "USER",
												"value"		=> $this->getUser(),
										),
										array(
												"nodeName"	=> "PASSWORD",
												"value"		=> $this->getPassword(),
										),
										array(
												"nodeName"	=> null,
												"value"		=> $this->getCupGenerazione()->serialize(),
										)

				);
				
				$xml = $this->generateXmlNode($nodeName, $attributes, $value, $innerElements);
				return $xml;
				
		} catch (\Exception $ex) {
			throw $ex;
		}
		
		
		
	}

} /* end of class RichiestaCupGenerazione */

?>