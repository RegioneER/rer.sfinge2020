<?php

namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\CupGenerazione;
use CipeBundle\Entity\DettaglioCup;
use CipeBundle\Entity\Classificazioni\CupNatura;
use CipeBundle\Entity\Classificazioni\CupSettore;
use CipeBundle\Entity\Classificazioni\CupSottosettore;
use CipeBundle\Entity\Classificazioni\CupCategoria;


/**
 * Short description of class DatiGeneraliProgetto
 *
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 * @see http://cb.schema31.it/cb/issue/173181
 *  
 * <!ELEMENT DATI_GENERALI_PROGETTO EMPTY>
<!ATTLIST DATI_GENERALI_PROGETTO
          anno_decisione CDATA #REQUIRED
          cumulativo (N | S) "N"
          codifica_locale CDATA #IMPLIED
          natura CDATA #REQUIRED
          tipologia CDATA #REQUIRED
          settore CDATA #REQUIRED
          sottosettore CDATA #REQUIRED
          categoria CDATA #REQUIRED
          cpv1 CDATA #IMPLIED
          cpv2 CDATA #IMPLIED
          cpv3 CDATA #IMPLIED
          cpv4 CDATA #IMPLIED
          cpv5 CDATA #IMPLIED
          cpv6 CDATA #IMPLIED
          cpv7 CDATA #IMPLIED>
 */
class DatiGeneraliProgetto extends CipeEntityService
{
    
    // --- ATTRIBUTES ---

    /**
     * Short description of attribute anno_decisione
     * Rappresenta l’anno in cui è presa la decisione di attuazione del progetto di investimento pubblico. 
	 * L'attributo assume valori maggiori o uguali a 1950 e deve essere composto da 4 cifre.
     * @var String
     */
    protected $anno_decisione = null;
	function getAnno_decisione() { return $this->anno_decisione; }
	function setAnno_decisione($anno_decisione) { $this->anno_decisione = self::setFilterParam($anno_decisione, "integer"); }

    /**
     * Valori possibili : S (Si) o N (No). 
	 * Se impostato ad S il finanziamento deve essere inferiore a 1.000.000 di EURO. 
	 * Se la natura è 03-REALIZZAZIONE DI LAVORI PUBBLICI (OPERE ED IMPIANTISTICA) o 07- CONCESSIONE DI INCENTIVI AD UNITA’ PRODUTTIVE, 
	 * non può essere selezionato il CUP cumulativo. 
	 * Se il cup è cumulativo, nella maschera dei dati finanziari, non può essere indicata la tipologia di copertura finanziaria 006-COMUNITARIA. 
     * @var String
     */
    protected $cumulativo = null;
	function getCumulativo() { return $this->cumulativo; }
	function setCumulativo($cumulativo) { $this->cumulativo = self::setFilterParam($cumulativo, "string"); }

		
    /**
     * Eventuale codifica di riconoscimento del progetto d'investimento pubblico utilizzata dal soggetto titolare. 
	 * Valorizzazione dell'attributo facoltativa; lunghezza massima: 60 caratteri.
     * @var String
     */
    protected $codifica_locale = null;
	function getCodifica_locale() { return $this->codifica_locale; }
	function setCodifica_locale($codifica_locale) { $this->codifica_locale = self::setFilterParam($codifica_locale, "string"); }
	
    /**
     * Codice relativo alla "Natura" del progetto d'investimento. 
     * @var String
     */
    protected $natura = null;
	function getNatura() { return $this->natura; }
	function setNatura($natura) { $this->natura = self::setFilterParam($natura, "string"); }

   /**     
     * Codice relativo alla "Tipologia" del progetto d'investimento da scegliere tra tutte le tipologie associate alla natura indicata.
     * @var String
     */
    protected $tipologia = null;
	function getTipologia() { return $this->tipologia; }
	function setTipologia($tipologia) { $this->tipologia = self::setFilterParam($tipologia, "string"); }

   /**
     * Codice relativo al "Settore" del progetto d'investimento.
     * @var String
     */
    protected $settore = null;
	function getSettore() { return $this->settore; }
	function setSettore($settore) { $this->settore = self::setFilterParam($settore, "string"); }

		
    /**
     * Codice relativo al "Sottosettore" del progetto d'investimento.
     * @var String
     */
    protected $sottosettore = null;
	function getSottosettore() { return $this->sottosettore; }
	function setSottosettore($sottosettore) { $this->sottosettore = self::setFilterParam($sottosettore, "string"); }

	/**
     * Codice relativo alla "Categoria" del progetto d'investimento.
     * @var String
     */
    protected $categoria = null;
	function getCategoria() { return $this->categoria; }
	function setCategoria($categoria) { $this->categoria = self::setFilterParam($categoria, "string"); }

		
    /**
     * @var Integer
     */
    protected $cpv1 = null;
	function getCpv1() { return $this->cpv1; }
	function setCpv1($cpv1) { $this->cpv1 = self::setFilterParam($cpv1, "integer"); }

    /**
     * @var Integer
     */
    protected $cpv2 = null;
	function getCpv2() { return $this->cpv2; }
	function setCpv2($cpv2) { $this->cpv2 = self::setFilterParam($cpv2, "integer"); }	
	
    /**
     * @var Integer
     */
    protected $cpv3 = null;
	function getCpv3() { return $this->cpv3; }
	function setCpv3($cpv3) { $this->cpv3 = self::setFilterParam($cpv3, "integer"); }	
	
    /**
     * @var Integer
     */
    protected $cpv4 = null;
	function getCpv4() { return $this->cpv4; }
	function setCpv4($cpv4) { $this->cpv4 = self::setFilterParam($cpv4, "integer"); }

    /**
     * @var Integer
     */
    protected $cpv5 = null;
	function getCpv5() { return $this->cpv5; }
	function setCpv5($cpv5) { $this->cpv5 = self::setFilterParam($cpv5, "integer"); }
	
    /**
     * @var Integer
     */
    protected $cpv6 = null;
	function getCpv6() { return $this->cpv6; }
	function setCpv6($cpv6) { $this->cpv6 = self::setFilterParam($cpv6, "integer"); }
	
    /**
     * @var Integer
     */
    protected $cpv7 = null;
	function getCpv7() { return $this->cpv7; }
	function setCpv7($cpv7) { $this->cpv7 = self::setFilterParam($cpv7, "integer"); }
	

	/**
	 * <!ELEMENT DATI_GENERALI_PROGETTO EMPTY>
*/
    public function __construct() {
		parent::__construct(null);
		$this->setXmlName("DATI_GENERALI_PROGETTO");
	}
	
	/**
	 * <!ELEMENT DATI_GENERALI_PROGETTO EMPTY>
		<!ATTLIST DATI_GENERALI_PROGETTO
          anno_decisione CDATA #REQUIRED
          cumulativo (N | S) "N"
          codifica_locale CDATA #IMPLIED
          natura CDATA #REQUIRED
          tipologia CDATA #REQUIRED
          settore CDATA #REQUIRED
          sottosettore CDATA #REQUIRED
          categoria CDATA #REQUIRED
          cpv1 CDATA #IMPLIED
          cpv2 CDATA #IMPLIED
          cpv3 CDATA #IMPLIED
          cpv4 CDATA #IMPLIED
          cpv5 CDATA #IMPLIED
          cpv6 CDATA #IMPLIED
          cpv7 CDATA #IMPLIED>
	 * @return boolean
	 */
    public function validate(ExecutionContext $context) { 
		
		$type = "attr";
		
		// valida anno_decisione
		$val = $this->getAnno_decisione();
		if(!$this->isNotNullAndIsNotEmpty($val))	$this->setValidateStatus("anno_decisione", false, $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		if(intval($val) < 1950)						$this->setValidateStatus("anno_decisione", false, $type, "[$val] L'elemento dev'essere maggiore o uguale di 1950");
		$this->commonValidateStringParam($type, $val, "anno_decisione", 4, 4, false);

		// valida cumulativo
		$val = $this->getCumulativo();
		if(!$this->isNotNullAndIsNotEmpty($val))		$this->setValidateStatus("cumulativo", false, $type, "L'elemento non puo' essere vuoto.");
		if(!\in_array($val, array("S", "N")))			$this->setValidateStatus("cumulativo", false, $type, "[$val] L'elemento puo' assumere valore S o N");
		
		
		//codifica_locale
		$val = $this->getCodifica_locale();
		if(!\is_null($val)) $this->commonValidateStringParam ($type, $val, "codifica_locale", 0, 60, false);
		
		// NATURA
		/**
		 * Se la natura è 03-REALIZZAZIONE DI LAVORI PUBBLICI (OPERE ED IMPIANTISTICA) o 07- CONCESSIONE DI INCENTIVI AD UNITA’ PRODUTTIVE, 
		 * non può essere selezionato il CUP cumulativo. 
		 */
		$natura = $val = $this->getNatura();
		$status_natura = true;
		if(!$this->isNotNullAndIsNotEmpty($val)) {
			$this->setValidateStatus("natura", false, $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
			$status_natura = false;
		}
		/* @var $CupNatura CupNatura */
		$CupNatura = $this->validateClassification(new CupNatura(), array("codice" => $val), true);
		
		if(!$CupNatura) {
			$this->setValidateStatus("natura", false, $type, "[$val] " . self::COMMON_VALIDATE_CODE_NOT_EXIST);
			$status_natura = false;
		}
		
		
		
		// cumulativo
		$val = $this->getCumulativo();
		if($status_natura && \in_array($this->getNatura(), array("03", "07")) && $this->getCumulativo() !='N')
			$this->setValidateStatus("cumulativo" , false	,$type, "[$val] Se la natura è 03 o 07 [{$this->getNatura()}] non può essere selezionato il CUP cumulativo.");

		$val = $this->getTipologia();
		if(!$this->isNotNullAndIsNotEmpty($val)) $this->setValidateStatus("tipologia"	,false, $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		if($CupNatura && !$CupNatura->checkCupCodiceTipologia($val)) $this->setValidateStatus("tipologia"	,false, $type, "[$val] " . self::COMMON_VALIDATE_CODE_NOT_EXIST);
		
		$settore = $val = $this->getSettore();
		if(!$this->isNotNullAndIsNotEmpty($val)) $this->setValidateStatus("settore"		,false, $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		/* @var $CupSettore CupSettore */
		$CupSettore = ($CupNatura) ? $CupNatura->checkCupCodiceSettore($settore, true) : false;
		if(!$CupSettore) $this->setValidateStatus("settore"		,false, $type, "[$val] " . self::COMMON_VALIDATE_CODE_NOT_EXIST);
		
				
		$sottosettore = $val = $this->getSottosettore();
		if(!$this->isNotNullAndIsNotEmpty($val)) $this->setValidateStatus("sottosettore"	,false, $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		/* @var $CupSottosettore CupSottosettore */
		$CupSottosettore = ($CupSettore) ? $CupSettore->checkCupCodiceSottosettore($sottosettore, true) : false;
		if(!$CupSottosettore) $this->setValidateStatus("sottosettore"	,false, $type, "[$val] " . self::COMMON_VALIDATE_CODE_NOT_EXIST);
			
		$val = $this->getCategoria();
		if(!$this->isNotNullAndIsNotEmpty($val)) $this->setValidateStatus("categoria"	,false, $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		if(	$CupSottosettore && !$CupSottosettore->checkCupCodiceCategoria($val) )	
			$this->setValidateStatus("categoria"	,false, $type, "[$val] " . self::COMMON_VALIDATE_CODE_NOT_EXIST);

		return parent::validate($context);
	}
	
	
	/**
	 * <!ELEMENT DATI_GENERALI_PROGETTO EMPTY>
		<!ATTLIST DATI_GENERALI_PROGETTO
          anno_decisione CDATA #REQUIRED
          cumulativo (N | S) "N"
          codifica_locale CDATA #IMPLIED
          natura CDATA #REQUIRED
          tipologia CDATA #REQUIRED
          settore CDATA #REQUIRED
          sottosettore CDATA #REQUIRED
          categoria CDATA #REQUIRED
          cpv1 CDATA #IMPLIED
          cpv2 CDATA #IMPLIED
          cpv3 CDATA #IMPLIED
          cpv4 CDATA #IMPLIED
          cpv5 CDATA #IMPLIED
          cpv6 CDATA #IMPLIED
          cpv7 CDATA #IMPLIED>
	 * @throws \Exception
	 */
	public function serialize() {
		try {
				parent::serialize();
				$nodeName = $this->getXmlName();
				$attributes = array(
									array(
											"attr_name" => "anno_decisione",
											"attr_value" => $this->getAnno_decisione()
									),
									array(
											"attr_name" => "cumulativo",
											"attr_value" => $this->getCumulativo()
									),					
									array(
											"attr_name" => "codifica_locale",
											"attr_value" => $this->getCodifica_locale()
									),
									array(
											"attr_name" => "natura",
											"attr_value" => $this->getNatura()
									),
									array(
											"attr_name" => "tipologia",
											"attr_value" => $this->getTipologia()
									),
										array(
											"attr_name" => "settore",
											"attr_value" => $this->getSettore()
									),
									array(
											"attr_name" => "sottosettore",
											"attr_value" => $this->getSottosettore()
									),
									array(
											"attr_name" => "categoria",
											"attr_value" => $this->getCategoria()
									),
									array(
											"attr_name" => "cpv1",
											"attr_value" => $this->getCpv1()
									),
									array(
											"attr_name" => "cpv2",
											"attr_value" => $this->getCpv2()
									),
									array(
											"attr_name" => "cpv3",
											"attr_value" => $this->getCpv3()
									),
									array(
											"attr_name" => "cpv4",
											"attr_value" => $this->getCpv4()
									),
									array(
											"attr_name" => "cpv5",
											"attr_value" => $this->getCpv5()
									),
									array(
											"attr_name" => "cpv6",
											"attr_value" => $this->getCpv6()
									),
									array(
											"attr_name" => "cpv7",
											"attr_value" => $this->getCpv7()
									),
								);
				$xml = $this->generateXmlNode($nodeName, $attributes);
				return $xml;
				
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	
} /* end of class DatiGeneraliProgetto */

?>