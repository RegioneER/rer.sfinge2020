<?php

namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\CupGenerazione;
use SoggettoBundle\Entity\Ateco;


/**
 * 
 * <!ELEMENT ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007 EMPTY>
	<!ATTLIST ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007
          sezione CDATA #IMPLIED
          divisione CDATA #IMPLIED
          gruppo CDATA #IMPLIED
          classe CDATA #IMPLIED
          categoria CDATA #IMPLIED
          sottocategoria CDATA #IMPLIED>
 * 
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 * @see http://cb.schema31.it/cb/issue/173212
 */
class AttivEconomicaBeneficiarioAteco2007 extends CipeEntityService
{
    /**
	 * Codice relativo alla "Sezione" di Attività economica del beneficiario. 
	 * La classificazione adottata è l'ATECO 2007. 
	 * Valorizzazione dell'attributo obbligatoria per Natura 06, 07 e 08.
	 *  Non valorizzare in caso di Natura uguale a:
	 *		03 - REALIZZAZIONE DI LAVORI PUBBLICI (OPERE ED IMPIANTISTICA) 
	 *		01 - ACQUISTO DI BENI oppure 02 - ACQUISTO O REALIZZAZIONE DI SERVIZI. 
	 * Valorizzazione dell’attributo opzionale per Natura 06 e se impostato ad S l'attributo "cumulativo". 

	 * @var String 
	 */
    protected $sezione = null;
	function getSezione() { return $this->sezione; }
	function setSezione($sezione) { $this->sezione = self::setFilterParam($sezione, "string"); }

	/**
	 * Codice relativo alla "divisione" di Attività economica del beneficiario. 
	 * La classificazione adottata è l'ATECO 2007. 
	 * Non valorizzare in caso di Natura uguale a :
	 *		03 - REALIZZAZIONE DI LAVORI PUBBLICI (OPERE ED IMPIANTISTICA)  
	 *		01 - ACQUISTO DI BENI  
	 *		02 - ACQUISTO O REALIZZAZIONE DI SERVIZI. 

	 * @var String 
	 */
	protected $divisione = null;
	function getDivisione() { return $this->divisione; }
	function setDivisione($divisione) { $this->divisione = self::setFilterParam($divisione, "string"); }

	/**
	 * Codice relativo arelativo al "Gruppo" di Attività economica del beneficiario. 
	 * La classificazione adottata è l'ATECO 2007. 
	 * Non impostare in caso di Natura uguale a 
	 *		03 - REALIZZAZIONE DI LAVORI PUBBLICI (OPERE ED IMPIANTISTICA)  
	 *		01 - ACQUISTO DI BENI oppure 
	 *		02 - ACQUISTO O REALIZZAZIONE DI SERVIZI.
	 * @var String 
	 */
	protected $gruppo = null;
	function getGruppo() { return $this->gruppo; }
	function setGruppo($gruppo) { $this->gruppo = self::setFilterParam($gruppo, "string"); }

	/**
	 * Codice relativo alla "Classe" di Attività economica del beneficiario. 
	 * La classificazione adottata è l'ATECO 2007. 
	 * Non impostare in caso di Natura uguale a 
	 *		03 - REALIZZAZIONE DI LAVORI PUBBLICI (OPERE ED IMPIANTISTICA)  
	 *		01 - ACQUISTO DI BENI oppure 
	 *		02 - ACQUISTO O REALIZZAZIONE DI SERVIZI.
	 * @var String 
	 */
	protected $classe = null;
	function getClasse() { return $this->classe; }
	function setClasse($classe) { $this->classe = self::setFilterParam($classe, "string"); }

	/**
	 * Codice relativo alla "Categoria" di Attività economica del beneficiario. 
	 * La classificazione adottata è l'ATECO 2007. 
	 * L’attributo e facoltativo. 
	 * Non impostare in caso di Natura uguale a 
	 *		03 - REALIZZAZIONE DI LAVORI PUBBLICI (OPERE ED IMPIANTISTICA) 
	 *		01 - ACQUISTO DI BENI 
	 *		02 - ACQUISTO O REALIZZAZIONE DI SERVIZI.
	 * @var String 
	 */
	protected $categoria = null;
	function getCategoria() { return $this->categoria; }
	function setCategoria($categoria) { $this->categoria = self::setFilterParam($categoria, "string"); }

	/**
	 * Codice relativo alla "Sottocategoria" di Attività economica del beneficiario. 
	 * La classificazione adottata è l'ATECO 2007. L’attributo è facoltativo. 
	 * Non impostare in caso di Natura uguale a 
	 *		03 - REALIZZAZIONE DI LAVORI PUBBLICI (OPERE ED IMPIANTISTICA)  
	 *		01 - ACQUISTO DI BENI  
	 *		02 - ACQUISTO O REALIZZAZIONE DI SERVIZI. 
	 * @var String 
	 */
	protected $sottocategoria = null;
	function getSottocategoria() { return $this->sottocategoria; }
	function setSottocategoria($sottocategoria) { $this->sottocategoria = self::setFilterParam($sottocategoria, "string"); }

	
	public function __construct() {
		parent::__construct(null);
		$this->setXmlName("ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007");
	}
	
	
	protected function isNoNullForNaturaSet($value, $NaturaSetArray) {
		$natura = $this->getSharedElement("natura");
		return (
				$natura != false && \in_array($natura, $NaturaSetArray) &&
				!\is_null($value)
			) ? true : false;
	}	
	
	
	/**
	 * * <!ELEMENT ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007 EMPTY>
		<!ATTLIST ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007
          sezione CDATA #IMPLIED
          divisione CDATA #IMPLIED
          gruppo CDATA #IMPLIED
          classe CDATA #IMPLIED
          categoria CDATA #IMPLIED
          sottocategoria CDATA #IMPLIED>
	 */
    public function validate(ExecutionContext $context) {
		
		$type = "attr";

		$array_03_01_02 = array("03", "01", "02");
		
		$status_divisione		= true;
		$status_gruppo			= true;
		$status_classe			= true;
		$status_categoria		= true;
		$status_sottocategoria	= true;
		
		$natura = $this->getSharedElement("natura");
		// sezione
		/**
		 * 1 - Valorizzazione dell'attributo obbligatoria per Natura 06, 07 e 08.
		 * 2-  Non valorizzare in caso di Natura uguale a:
		 * 		03 - REALIZZAZIONE DI LAVORI PUBBLICI (OPERE ED IMPIANTISTICA) 
		 * 		01 - ACQUISTO DI BENI oppure 02 - ACQUISTO O REALIZZAZIONE DI SERVIZI. 
		 * 3 - Valorizzazione dell’attributo opzionale per Natura 06 e se impostato ad S l'attributo "cumulativo". 
		 */
		// 1
		if($natura != false && \in_array($natura, array("07", "08")) && 
				!$this->isNotNullAndIsNotEmpty($this->getSezione()) 
		   ) {
			$this->setValidateStatus("sezione"	,false ,$type, "Valorizzazione dell'attributo obbligatoria per Natura 07 e 08");
		   }
		

		// 2
		if($this->isNoNullForNaturaSet($this->getSezione(), array("03", "01"))
			) $this->setValidateStatus("sezione"	,false ,$type, " Non valorizzare in caso di Natura uguale a 03, 01");
		
		if($natura != false && \in_array($natura, array("06")) && !$this->getSharedElement("cup_cumulativo") &&
				!$this->isNotNullAndIsNotEmpty($this->getSezione()) 
		   ) $this->setValidateStatus("sezione"	,false ,$type, "Valorizzazione dell'attributo obbligatoria per Natura 06 senza cup_cumulativo");
				
		
		
		/**
		 * PER TUTTI GLI ALTRI ELEMENTI:
		 *	Non valorizzare in caso di Natura uguale a :
		 *		03 - REALIZZAZIONE DI LAVORI PUBBLICI (OPERE ED IMPIANTISTICA)  
		 *		01 - ACQUISTO DI BENI  
		 *		02 - ACQUISTO O REALIZZAZIONE DI SERVIZI. 
		 */
		$err_message = "Non valorizzare in caso di Natura uguale a 01, 02, 03";
		
		if($this->isNoNullForNaturaSet($this->getDivisione(), $array_03_01_02))			$status_divisione		= false;
		if($this->isNoNullForNaturaSet($this->getGruppo(), $array_03_01_02))			$status_gruppo			= false;
		if($this->isNoNullForNaturaSet($this->getClasse(), $array_03_01_02))			$status_classe			= false;
		if($this->isNoNullForNaturaSet($this->getCategoria(), $array_03_01_02))			$status_categoria		= false;
		if($this->isNoNullForNaturaSet($this->getSottocategoria(), $array_03_01_02))	$status_sottocategoria	= false;
		
		$this->setValidateStatus("divisione"		,$status_divisione		,$type, $err_message);
		$this->setValidateStatus("gruppo"			,$status_gruppo			,$type, $err_message);
		$this->setValidateStatus("classe"			,$status_classe			,$type, $err_message);
		$this->setValidateStatus("categoria"		,$status_categoria		,$type, $err_message);
		$this->setValidateStatus("sottocategoria"	,$status_sottocategoria	,$type, $err_message);
		

		
		$ateco = $this->getDoctrine()->getRepository(\get_class(new Ateco()))->ricercaAtecoCipeCup(
																									$this->getSezione(),
																									$this->getDivisione(),
																									$this->getGruppo(),
																									$this->getClasse(),
																									$this->getCategoria(),
																									$this->getSottocategoria()
																								);
		$criteria_ateco = array(
								"sezione"			=> $this->getSezione(),
								"divisione"			=> $this->getDivisione(),
								"gruppo"			=> $this->getGruppo(),
								"classe"			=> $this->getClasse(),
								"categoria"			=> $this->getCategoria(),
								"sottocategoria"	=> $this->getSottocategoria()
								);
        
		if(!$ateco && in_array($natura, array("07", "08")))
			$this->setValidateStatus($this->getXmlName(), false, $type, "[".json_encode($criteria_ateco)."] " . self::COMMON_VALIDATE_CODE_NOT_EXIST);

		return parent::validate($context);
	}
	
	
	/**
	 * <!ATTLIST ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007
          sezione CDATA #IMPLIED
          divisione CDATA #IMPLIED
          gruppo CDATA #IMPLIED
          classe CDATA #IMPLIED
          categoria CDATA #IMPLIED
          sottocategoria CDATA #IMPLIED>
	 * @return string
	 * @throws \Exception
	 */
	public function serialize() {
		try {
			parent::serialize();
			$nodeName = $this->getXmlName();
			$attributes = array(
									array(
											"attr_name" => "sezione",
											"attr_value" => $this->getSezione()
									),
									array(
											"attr_name" => "divisione",
											"attr_value" => $this->getDivisione()
									),					
									array(
											"attr_name" => "gruppo",
											"attr_value" => $this->getGruppo()
									),
									array(
											"attr_name" => "classe",
											"attr_value" => $this->getClasse()
									),
									array(
											"attr_name" => "categoria",
											"attr_value" => $this->getCategoria()
									),
				
									array(
											"attr_name" => "sottocategoria",
											"attr_value" => $this->getSottocategoria()
									)
								);
			
			$xml = $this->generateXmlNode($nodeName, $attributes);
			return $xml;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
    
} 

