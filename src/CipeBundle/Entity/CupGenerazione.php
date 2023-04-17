<?php

namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\AttivEconomicaBeneficiarioAteco2007;
use CipeBundle\Entity\DatiGeneraliProgetto;
use CipeBundle\Entity\Descrizione;
use CipeBundle\Entity\Finanziamento;
use CipeBundle\Entity\Localizzazione;
use CipeBundle\Entity\RichiestaCupGenerazione;
use CipeBundle\Entity\WsGeneraCup;


/**
 * CupGenerazione
 *
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 * @see http://cb.schema31.it/cb/issue/173174
 * 
 * 
 * <!ELEMENT CUP_GENERAZIONE (DATI_GENERALI_PROGETTO, MASTER?, LOCALIZZAZIONE+, DESCRIZIONE, ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007?, FINANZIAMENTO)>
	<!ATTLIST CUP_GENERAZIONE
          soggetto_titolare CDATA #IMPLIED
          uo_soggetto_titolare CDATA #IMPLIED
          user_titolare CDATA #IMPLIED
          id_progetto CDATA #REQUIRED>
 */
class CupGenerazione extends CipeEntityService {

	/**
	 *  --------   attributi ------------
	 */
	
	/**
	 * soggetto titolare
	 * @var String
	 */
	protected $soggetto_titolare = null;
	function getSoggetto_titolare() { return $this->soggetto_titolare; }
	function setSoggetto_titolare($soggetto_titolare) { $this->soggetto_titolare = self::setFilterParam($soggetto_titolare, "string"); }

	
	/**
     * (Unità organizzativa) : Codice dell'Unità organizzativa del soggetto titolare. 
     * @var String
     */
    protected $uo_soggetto_titolare = null;
	function getUo_soggetto_titolare() { return $this->uo_soggetto_titolare; }
	function setUo_soggetto_titolare($uo_soggetto_titolare) { $this->uo_soggetto_titolare = self::setFilterParam($uo_soggetto_titolare, "string"); }
		
    /**
     * UserId titolare) : UserId del’utente titolare. 
     * @var String
     */
    protected $user_titolare = null;
	function getUser_titolare() { return $this->user_titolare;}
	function setUser_titolare($user_titolare) { $this->user_titolare = self::setFilterParam($user_titolare, "string");}
		
    /**
     * Numero progressivo identificativo del progetto nell’ambito del file XML. 
	 * E' il riferimento che deve essere indicato nel caso in cui il progetto deve essere Master per un altro progetto presente nello stesso file stesso. 
	 * In tal caso, nel file XML, il progetto Master deve precedere il progetto collegato.
     * @var string
     */
    protected $id_progetto = null;
	function getId_progetto() {	return $this->id_progetto; }
	function setId_progetto($id_progetto) { $this->id_progetto = self::setFilterParam($id_progetto, "string"); }

	/** 
	 * -------- elementi innestati ---------
	 */
    /**
     * Short description of attribute DatiGeneraliProgetto
     * @var DatiGeneraliProgetto
	 * @see http://cb.schema31.it/cb/issue/173181
     */
    protected $DatiGeneraliProgetto = null;
    function getDatiGeneraliProgetto() { return $this->DatiGeneraliProgetto; }
    function setDatiGeneraliProgetto( DatiGeneraliProgetto $DatiGeneraliProgetto) { $this->DatiGeneraliProgetto = $DatiGeneraliProgetto; }

	
    /**
     * @var Localizzazione
	 * @see http://cb.schema31.it/cb/issue/173187
     */
    protected $Localizzazione = null;
	function getLocalizzazione() { return $this->Localizzazione; }
	function setLocalizzazione(Localizzazione $Localizzazione) { $this->Localizzazione = $Localizzazione; }

		
    /**
     * @var Descrizione
	 * @see http://cb.schema31.it/cb/issue/173201
     */
    protected $Descrizione = null;
	function getDescrizione() { return $this->Descrizione; }
	function setDescrizione(Descrizione $Descrizione) { $this->Descrizione = $Descrizione; }

	/**
     * @var AttivEconomicaBeneficiarioAteco2007
	 * @see http://cb.schema31.it/cb/issue/173212
     */
    protected $AttivEconomicaBeneficiarioAteco2007 = null;
	function getAttivEconomicaBeneficiarioAteco2007() { return $this->AttivEconomicaBeneficiarioAteco2007; }
	function setAttivEconomicaBeneficiarioAteco2007(AttivEconomicaBeneficiarioAteco2007 $AttivEconomicaBeneficiarioAteco2007) { $this->AttivEconomicaBeneficiarioAteco2007 = $AttivEconomicaBeneficiarioAteco2007; }

    /**
     * @var Finanziamento
	 * @see http://cb.schema31.it/cb/issue/173209
     */
    protected $Finanziamento = null;
	function getFinanziamento() { return $this->Finanziamento; }
	function setFinanziamento(Finanziamento $Finanziamento) { $this->Finanziamento = $Finanziamento; }

	/**
	 *  * <!ELEMENT CUP_GENERAZIONE (DATI_GENERALI_PROGETTO, MASTER?, LOCALIZZAZIONE+, DESCRIZIONE, ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007?, FINANZIAMENTO)>

	 */
	public function __construct() {
		parent::__construct(null);
		$this->setXmlName("CUP_GENERAZIONE");
		$this->setValidateArray(array());
	}
	
	/**
	 * <!ELEMENT CUP_GENERAZIONE (DATI_GENERALI_PROGETTO, MASTER?, LOCALIZZAZIONE+, DESCRIZIONE, ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007?, FINANZIAMENTO)>
	<!ATTLIST CUP_GENERAZIONE
          soggetto_titolare CDATA #IMPLIED
          uo_soggetto_titolare CDATA #IMPLIED
          user_titolare CDATA #IMPLIED
          id_progetto CDATA #REQUIRED>
	 * @return boolean
	 */
    public function validate(ExecutionContext $context) { 
		$type = "attr";
		$this->setValidateStatus("id_progetto"								, $this->isNotNullAndIsNotEmpty($this->getId_progetto())					, $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		$type = "inner";
		$this->setValidateStatus("DATI_GENERALI_PROGETTO"					, $this->getDatiGeneraliProgetto()->validate($context)								, $type);
		$this->setValidateStatus("LOCALIZZAZIONE"							, $this->getLocalizzazione()->validate($context)									, $type);
		$this->setValidateStatus("DESCRIZIONE"								, $this->getDescrizione()->validate($context)										, $type);
		$this->setValidateStatus("ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007"	, $this->validateIfNotNull($this->getAttivEconomicaBeneficiarioAteco2007())	, $type);
		$this->setValidateStatus("FINANZIAMENTO"							, $this->getFinanziamento()->validate($context)										, $type);

		return parent::validate($context);
		
		
	}
	
	
	/**
	 * * <!ELEMENT CUP_GENERAZIONE (DATI_GENERALI_PROGETTO, MASTER?, LOCALIZZAZIONE+, DESCRIZIONE, ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007?, FINANZIAMENTO)>
	<!ATTLIST CUP_GENERAZIONE
          soggetto_titolare CDATA #IMPLIED
          uo_soggetto_titolare CDATA #IMPLIED
          user_titolare CDATA #IMPLIED
          id_progetto CDATA #REQUIRED>
	 */
	public function serialize() {
		try {
            
                $master = array(
                    "nodeName"	=> "MASTER",
                    "attributes" => array(
                        array("attr_name" => "cup_master", "attr_value" => ""),
                        array("attr_name" => "id_master", "attr_value" => ""),
                        array("attr_name" => "ragioni_collegamento", "attr_value" => "")
                        )
                );
				parent::serialize();
				$nodeName = $this->getXmlName();
				$attributes = array(
									array(
											"attr_name" => "soggetto_titolare",
											"attr_value" => $this->getSoggetto_titolare()
									),
									array(
											"attr_name" => "uo_soggetto_titolare",
											"attr_value" => $this->getUo_soggetto_titolare()
									),					
									array(
											"attr_name" => "user_titolare",
											"attr_value" => $this->getUser_titolare()
									),
									array(
											"attr_name" => "id_progetto",
											"attr_value" => $this->getId_progetto()
									),					
									);
				$value=null;
				$innerElements = array(
										array(
												"nodeName"	=> null,
												"value"		=> $this->getDatiGeneraliProgetto()->serialize()
										),
                                        $master,
										array(
												"nodeName"	=> null,
												"value"		=> $this->getLocalizzazione()->serialize()
										),
										array(
												"nodeName"	=> null,
												"value"		=> $this->getDescrizione()->serialize()
										),
										array(
												"nodeName"	=> null,
												"value"		=> $this->serializeIfNotNull($this->getAttivEconomicaBeneficiarioAteco2007())
										),
										array(
												"nodeName"	=> null,
												"value"		=> $this->getFinanziamento()->serialize(),
										)

				);
				
				$xml = $this->generateXmlNode($nodeName, $attributes, $value, $innerElements, true);
				return $xml;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
   
} /* end of class CupGenerazione */
