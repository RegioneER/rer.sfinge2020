<?php

namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\Descrizione;
use CipeBundle\Entity\Classificazioni\CupStrumentoProgrammazione;
use CipeBundle\Entity\Classificazioni\CupTipoIndirizzo;
use CipeBundle\Entity\Classificazioni\CupTipoCoperturaFinanziaria;
use CipeBundle\Entity\TipoDescrizione;


/**
 * ConcessioneIncentiviNoUnitaProduttive
 *
 * <!ELEMENT CONCESSIONE_CONTRIBUTI_NO_UNITA_PRODUTTIVE EMPTY>
<!ATTLIST CONCESSIONE_CONTRIBUTI_NO_UNITA_PRODUTTIVE
          beneficiario CDATA #REQUIRED
          partita_iva CDATA #REQUIRED
          struttura CDATA #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          desc_intervento CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
 
 * @see http://cb.schema31.it/cb/issue/173202
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 */
class ConcessioneContributiNoUnitaProduttive extends TipoDescrizione
{
    // --- ATTRIBUTES ---

    /**
	 * Denominazione del beneficiario dei contributi. Lunghezza massima 100 caratteri. 
	 * Lunghezza minima 5 caratteri. Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti.
	 * Non sono consentiti solo numeri o solo segni matematici. 
     * @var String
     */
    protected $beneficiario = null;
	function getBeneficiario() { return $this->beneficiario; }
	function setBeneficiario($beneficiario) { $this->beneficiario = self::setFilterParam($beneficiario, "string"); }


		
    /**
	 * Partita IVA o codice fiscale dell’azienda oggetto della partecipazione o del conferimento. Lunghezza massima 16 caratteri. 
     * @var String
     */
    protected $partita_iva = null;
	function getPartita_iva() { return $this->partita_iva; }
	function setPartita_iva($partita_iva) { $this->partita_iva = self::setFilterParam($partita_iva, "string"); }

    /**
	 * Denominazione della struttura su cui si interviene grazie al contributo. 
	 * Lunghezza massima 100 caratteri. Lunghezza minima 5 caratteri. 
	 * Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti. 
	 * Non sono consentiti solo numeri o solo segni matematici. 
     * @var String
     */
    protected $struttura = null;
	function getStruttura() { return $this->struttura; }
	function setStruttura($struttura) { $this->struttura = self::setFilterParam($struttura, "string"); }

   		
    /**
	 * Descrizione dell’intervento. 
	 * Lunghezza massima 100 caratteri. Lunghezza minima 5 caratteri. 
	 * Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti. 
	 * Non sono consentiti solo numeri o solo segni matematici. 
     * @var String
     */
    protected $desc_intervento = null;
	function getDesc_intervento() { return $this->desc_intervento; }
	function setDesc_intervento($desc_intervento) { $this->desc_intervento = self::setFilterParam($desc_intervento, "string"); }

   
    
	/**
	 * <!ELEMENT CONCESSIONE_CONTRIBUTI_NO_UNITA_PRODUTTIVE EMPTY>

	 */
	public function __construct() {
		parent::__construct();
		$this->setXmlName("CONCESSIONE_CONTRIBUTI_NO_UNITA_PRODUTTIVE");
	}
	
	
	/**
	 *  <!ELEMENT CONCESSIONE_CONTRIBUTI_NO_UNITA_PRODUTTIVE EMPTY>
		<!ATTLIST CONCESSIONE_CONTRIBUTI_NO_UNITA_PRODUTTIVE
          beneficiario CDATA #REQUIRED
          partita_iva CDATA #REQUIRED
          struttura CDATA #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          desc_intervento CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
	 * @return boolean
	 */
    public function validate(ExecutionContext $context) { 
		$this->setLunghezza_massima_altre_informazioni(4000);
		$type = "attr";
		
		// beneficiario
		$val = $this->getBeneficiario();
		$this->commonValidateStringParam($type, $val, "beneficiario", 5, 100);
		
		// partita_iva
		$val = $this->getPartita_iva();
		$this->commonValidateStringParam($type, $val, "partita_iva", 11, 16, false);

		// struttura
		$val = $this->getStruttura();
		$this->commonValidateStringParam($type, $val, "beneficiario", 5, 100);
		
		// descrizione_intervento
		$val = $this->getDesc_intervento();
		$this->commonValidateStringParam($type, $val, "desc_intervento", 5, 100);
		
		$this->setCampi_obbligatori(array(self::TIPO_IND_AREA_RIFER, self::IND_AREA_RIFER, self::STRUM_PROGR));
		$this->setLunghezza_massima_altre_informazioni(4000);
		return parent::validate($context);
		
	}
	
	/**
	 * <!ATTLIST CONCESSIONE_CONTRIBUTI_NO_UNITA_PRODUTTIVE
          beneficiario CDATA #REQUIRED
          partita_iva CDATA #REQUIRED
          struttura CDATA #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          desc_intervento CDATA #REQUIRED
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
											"attr_name" => "beneficiario",
											"attr_value" => $this->getBeneficiario()
									),
									array(
											"attr_name" => "partita_iva",
											"attr_value" => $this->getPartita_iva()
									),					
									array(
											"attr_name" => "struttura",
											"attr_value" => $this->getStruttura()
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
											"attr_name" => "desc_intervento",
											"attr_value" => $this->getDesc_intervento()
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
									),
								);
			
			$xml = $this->generateXmlNode($nodeName, $attributes);
			return $xml;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

} /* end of class ConcessioneIncentiviNoUnitaProduttive */

?>