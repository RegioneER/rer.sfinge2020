<?php

namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\CupGenerazione;
use CipeBundle\Entity\Classificazioni\CupTipoCoperturaFinanziaria;


/**
 * Short description of class Finanziamento
 *
 * <!ELEMENT FINANZIAMENTO (CODICE_TIPOLOGIA_COP_FINANZ+)>
	<!ATTLIST FINANZIAMENTO
          sponsorizzazione (N | P | T) #IMPLIED
          finanza_progetto (A | N | P) #IMPLIED
          costo CDATA #REQUIRED
          finanziamento CDATA #REQUIRED>
<!ELEMENT CODICE_TIPOLOGIA_COP_FINANZ (#PCDATA)*>
 * @see http://cb.schema31.it/cb/issue/173209
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 */
class Finanziamento extends CipeEntityService
{
   
    // --- ATTRIBUTES ---

    /**
	 * Opzionale. Valori possibili N (No), T (Totale), P (Parziale). 
	 * Non deve essere valorizzato qualora la natura del CUP è diversa da “lavori pubblici” e da "acquisto e fornitura di servizi ". Vedi l'elemento "Descrizione" (http://cb.schema31.it/cb/issue/173201)
	 * Deve essere impostato a ”NO”, qualora il CUP è cumulativo. 
	 * Alternativo a Finanza di progetto. Se impostato T (Totale), Finanza di progetto deve essere impostato a N (No).
     * @var String
     */
    public $sponsorizzazione = null;
	function getSponsorizzazione() { return $this->sponsorizzazione; }
	function setSponsorizzazione($sponsorizzazione) { $this->sponsorizzazione = self::setFilterParam($sponsorizzazione, "string"); }

    /**
	 * Opzionale. Valori possibili N (No), P (Pura), A (Assistita). 
	 * Alternativo a Sponsorizzazione. 
	 * Se impostato P (Pura), Sponsorizzazione deve essere impostato a N (No). 
     * @var String
     */
    public $finanza_progetto = null;
	function getFinanza_progetto() { return $this->finanza_progetto; }
	function setFinanza_progetto($finanza_progetto) { $this->finanza_progetto = self::setFilterParam($finanza_progetto, "string"); }

    /**
	 * Costo del progetto in migliaia di euro. 
	 * Max 12 interi e 3 decimali. 
	 * Deve essere maggiore di 0. 
	 * Se il progetto è cumulativo il costo deve essere minore di 1.000.000 di euro. 
     * @var Integer
     */
    public $costo = null;
	function getCosto() { return $this->costo; }
	function setCosto($costo) { $this->costo = self::setFilterParam($costo, "float"); }

    /**
	 * Finanziamento assegnato al progetto in migliaia di euro. 
	 * Max 12 interi e 3 decimali. 
	 * Deve essere maggiore di 0. 
	 * Per natura diversa da 07 - CONCESSIONE DI AIUTI e tipologia di copertura finanziaria non contenente la tipologia 007 - PRIVATA, 
	 * l’importo del finanziamento deve essere uguale a quello del costo.
	 * 
	 * Per tipologia di copertura finanziaria contente la tipologia 007 - PRIVATA, 
	 * l’importo del finanziamento deve essere minore di quello del costo.
	 * 
	 * Nel caso di finanza di progetto “pura” l’importo del finanziamento deve essere 0.
	 * Nel caso di Finanza di progetto “assistita”, il finanziamento deve essere diverso da 0 ed inferiore al costo del progetto.
	 * Nel caso di Sponsorizzazione Totale il finanziamento deve essere uguale a 0.
	 * Per Natura diversa da Aiuti il finanziamento deve essere minore del costo se è presente la tipologia di copertura finanziaria “Privata”. 
     * @var String
     */
    public $finanziamento = null;
	function getFinanziamento() { return $this->finanziamento; }
	function setFinanziamento( $finanziamento) { $this->finanziamento = self::setFilterParam($finanziamento, "float"); }

    /**
	 * Codice della tipologia copertura finanziaria. 
	 * I Codici delle tipologie di copertura finanziaria sono scaricabili dall'applicativo del Sistema CUP mediante la funzione "Scarico Tabelle di Decodifica". 
	 * Nel caso di finanza di progetto “pura” la tipologia di copertura finanziaria può essere solo 007 - PRIVATA. 
	 * Nel caso di Finanza di progetto “assistita” la copertura deve essere caratterizzata dalla tipologia “privata” di default e, necessariamente, da altre tipologie inserite dall’utente.
	 * Nel caso di Sponsorizzazione Totale la tipologia di copertura deve essere solo “privata”.
     * @var String
     */
    public $codici_tipologia_cop_finanz = array();
	function getCodici_tipologia_cop_finanz() { return $this->codici_tipologia_cop_finanz;}
	function setCodici_tipologia_cop_finanz($codici_tipologia_cop_finanz) { $this->codici_tipologia_cop_finanz = (\is_array($codici_tipologia_cop_finanz)) ? $codici_tipologia_cop_finanz : array(); }
	function addCodice_tipologia_cop_finanz($codice_tipologia_cop_finanz) {
		$codici_tipologia_cop_finanz = $this->getCodici_tipologia_cop_finanz();
		$codici_tipologia_cop_finanz[] = self::setFilterParam($codice_tipologia_cop_finanz, "string");
		$this->setCodici_tipologia_cop_finanz($codici_tipologia_cop_finanz);
	}
	function removeCodice_tipologia_cop_finanz($codice_tipologia_cop_finanz) {
		$codici_tipologia_cop_finanz = $this->getCodici_tipologia_cop_finanz();
		if(\in_array($codice_tipologia_cop_finanz, $codici_tipologia_cop_finanz)) 
				unset($codici_tipologia_cop_finanz[$codice_tipologia_cop_finanz]);
		$this->setCodici_tipologia_cop_finanz($codici_tipologia_cop_finanz);
	}
	    
	public function __construct() {
		parent::__construct(null);
		$this->setXmlName("FINANZIAMENTO");
	}
	
	/**
	 * <!ELEMENT FINANZIAMENTO (CODICE_TIPOLOGIA_COP_FINANZ+)>
	<!ATTLIST FINANZIAMENTO
          sponsorizzazione (N | P | T) #IMPLIED
          finanza_progetto (A | N | P) #IMPLIED
          costo CDATA #REQUIRED
          finanziamento CDATA #REQUIRED>
<!ELEMENT CODICE_TIPOLOGIA_COP_FINANZ (#PCDATA)*>
	 */
    public function validate(ExecutionContext $context) { 
		
		
		$type = "attr";
		$array_sponsorizzazione = (
									$this->getSharedElement("cup_cumulativo") ||
									($this->getFinanza_progetto() == 'P') ||
									$this->getSharedElement("no_sponsorizzazione")
									) ? array("N") : array("N", "P", "T");
		$array_finanza_progetto = ($this->getSponsorizzazione() == 'T') ? array("N") : array("N", "P", "A");
		

		
		//sponsorizzazione
		$val = $this->getSponsorizzazione();
		if(!\is_null($val) && !\in_array($val, $array_sponsorizzazione))
				$this->setValidateStatus("sponsorizzazione", false, $type, "[$val] ammessi i seguenti valori:".  implode(", ", $array_sponsorizzazione));
								

								
//		if(self::isNullOrEmpty($this->getSponsorizzazione()) && self::isNullOrEmpty($this->getFinanza_progetto())) {
//			$this->setValidateStatus("sponsorizzazione"	,false	,$type, "sponsorizzazione e Finanza_progetto non possono essere entrambi nulli");
//			$this->setValidateStatus("finanza_progetto"	,false	,$type, "sponsorizzazione e Finanza_progetto non possono essere entrambi nulli");
//
//		}
		
		// finanza_progetto
		$val = $this->getFinanza_progetto();
		if(!\is_null($val) && !\in_array($val, $array_finanza_progetto))
				$this->setValidateStatus("finanza_progetto", false, $type, "[$val] ammessi i seguenti valori:".  implode(", ", $array_finanza_progetto));
	
		
		// costo
		$val = $this->getCosto();
		if(!$this->isNotNullAndIsNotEmpty($val)) $this->setValidateStatus("costo" ,false ,$type ,  self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		if(!$this->checkNumericPattern($val, 1, 12, 0, 3)) $this->setValidateStatus("costo" ,false	, $type , "[$val] formato ammesso: 1-12 interi, 0-3 decimali");
		if($this->getSharedElement("cup_cumulativo") && $val > 1000000) 
			$this->setValidateStatus("costo" ,false	,$type, "[$val] Se il progetto e' cumulativo il costo deve essere minore di 1.000.000 di euro. ");

		// finanziamento
		$val = $this->getFinanziamento();
		if(!$this->isNotNullAndIsNotEmpty($val)) $this->setValidateStatus("finanziamento" ,false ,$type ,  self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		if(!$this->checkNumericPattern($val, 1, 12, 0, 3)) $this->setValidateStatus("finanziamento" ,false	, $type , "[$val] formato ammesso: 1-12 interi, 0-3 decimali");
		if($this->getSharedElement("cup_cumulativo") && $val > 1000000)
			$this->setValidateStatus("finanziamento" ,false	,$type, " [$val] Se il progetto e' cumulativo il finanziamento deve essere minore di 1.000.000 di euro. ");

		/**
		 * 1 - Per natura diversa da 07 - CONCESSIONE DI AIUTI e tipologia di copertura finanziaria non contenente la tipologia 007 - PRIVATA, 
		 * l’importo del finanziamento deve essere uguale a quello del costo.
		 * 2 - Per tipologia di copertura finanziaria contente la tipologia 007 - PRIVATA, 
		 * l’importo del finanziamento deve essere minore di quello del costo.
		 * 3 - Nel caso di finanza di progetto “pura” l’importo del finanziamento deve essere 0.
		 * 4 - Nel caso di Finanza di progetto “assistita”, il finanziamento deve essere diverso da 0 ed inferiore al costo del progetto.
		 * 5 - Nel caso di Sponsorizzazione Totale il finanziamento deve essere uguale a 0.
		 * 6 - Per Natura diversa da Aiuti (si presume codice 06 - CONCESSIONE DI AIUTI A SOGGETTI DIVERSI DA UNITA PRODUTTIVE) il finanziamento deve essere minore del costo se è presente la tipologia di copertura finanziaria “Privata”. 

		 */
			// 1
			if
				(
					$this->getSharedElement("natura") != false					&& 
					$this->getSharedElement("natura") != "07"					&&
					!\in_array("007", $this->getCodici_tipologia_cop_finanz())	&&
					$this->getFinanziamento() != $this->getCosto()
				)
				$this->setValidateStatus("finanziamento" ,false	,$type, "[$val] Per natura diversa da 07 e tipologia di copertura finanziaria non contenente la tipologia 007, l’importo del finanziamento deve essere uguale a quello del costo.");

		
		
				// 2
			if
				(
					\in_array("007", $this->getCodici_tipologia_cop_finanz())	&&
					$this->getFinanziamento() >= $this->getCosto()	
				)
				$this->setValidateStatus("finanziamento" ,false	,$type, "[$val] Nel caso di finanza di progetto “pura” l’importo del finanziamento deve essere 0.");

			
				// 3
			if
				(
					$this->getFinanza_progetto() == "P" && $this->getFinanziamento() !=0	
				) 
				$this->setValidateStatus("finanziamento" ,false	,$type, "[$val] Per tipologia di copertura finanziaria contente la tipologia 007 l’importo del finanziamento deve essere minore di quello del costo.");

				// 4
			if
				(
					$this->getFinanza_progetto() == "A" && 
						!($this->getFinanziamento() !=0 && $this->getFinanziamento() < $this->getCosto())	
				)
				$this->setValidateStatus("finanziamento" ,false	,$type, "[$val] Nel caso di Finanza di progetto 'assistita', il finanziamento deve essere diverso da 0 ed inferiore al costo del progetto.");

			
				// 5
			if
				(
					$this->getSponsorizzazione() == "T" && $this->getFinanziamento() !=0
				) 
				$this->setValidateStatus("finanziamento" ,false	,$type, "[$val] Nel caso di Sponsorizzazione Totale il finanziamento deve essere uguale a 0.");

			
				// 6
			if
				(
					\in_array("007", $this->getCodici_tipologia_cop_finanz()) &&
					$this->getSharedElement("natura") != false				  && 
					$this->getSharedElement("natura") != "06"				  &&
					!($this->getFinanziamento() < $this->getCosto())
				)
				$this->setValidateStatus("finanziamento" ,false	,$type, "[$val] Per Natura diversa da 06 il finanziamento deve essere minore del costo se è presente la tipologia di copertura finanziaria “Privata”. ");

				
	
		
		
		/**
		 * Nel caso di finanza di progetto “pura” l’importo del finanziamento deve essere 0.
		 * Nel caso di Finanza di progetto “assistita”, il finanziamento deve essere diverso da 0 ed inferiore al costo del progetto.
		 * Nel caso di Sponsorizzazione Totale il finanziamento deve essere uguale a 0.
		 * Per Natura diversa da Aiuti il finanziamento deve essere minore del costo se è presente la tipologia di copertura finanziaria “Privata”. 
		 * Nel caso di finanza progetto "pura" la tipologia di copertura finanziaria può essere solo 007
		 */
		
		
		$codici_tipologia_cop_finanz = $this->getCodici_tipologia_cop_finanz();
		if(count($codici_tipologia_cop_finanz) ==0) 
			$this->setValidateStatus("CODICE_TIPOLOGIA_COP_FINANZ"	,false ,$type, "E' necessario specificare almeno un elemento.");	

		if($this->getFinanza_progetto() == "P") {
		    if(count($codici_tipologia_cop_finanz) != 1)
				$this->setValidateStatus("CODICE_TIPOLOGIA_COP_FINANZ"	,false ,$type, "Per finanza progetto PURA (P) è ammesso un solo codice di copertura finanziaria");	
			else {
				if($codici_tipologia_cop_finanz[0] != "007")
					$this->setValidateStatus("CODICE_TIPOLOGIA_COP_FINANZ"	,false ,$type, "Per finanza progetto PURA (P) è ammesso solo il codice di copertura finanziaria 007");	
			}
		}

		
		foreach($codici_tipologia_cop_finanz as $codice_tipologia_cop_finanz) {
			if(!$this->isNotNullAndIsNotEmpty($codice_tipologia_cop_finanz))
				$this->setValidateStatus("CODICE_TIPOLOGIA_COP_FINANZ"	,false ,$type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
			if(!$this->validateClassification(new CupTipoCoperturaFinanziaria(), array("codice" =>$codice_tipologia_cop_finanz)))	
				$this->setValidateStatus("CODICE_TIPOLOGIA_COP_FINANZ"	,false, $type, "[$codice_tipologia_cop_finanz] " . self::COMMON_VALIDATE_CODE_NOT_EXIST);
		}
		$type = "inner";
		

		return parent::validate($context);
	}
	
	
	/**
	 * <!ELEMENT FINANZIAMENTO (CODICE_TIPOLOGIA_COP_FINANZ+)>
		<!ATTLIST FINANZIAMENTO
          sponsorizzazione (N | P | T) #IMPLIED
          finanza_progetto (A | N | P) #IMPLIED
          costo CDATA #REQUIRED
          finanziamento CDATA #REQUIRED>
		<!ELEMENT CODICE_TIPOLOGIA_COP_FINANZ (#PCDATA)*>
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
											"attr_name" => "sponsorizzazione",
											"attr_value" => $this->getSponsorizzazione()
									),
									array(
											"attr_name" => "finanza_progetto",
											"attr_value" => $this->getFinanza_progetto()
									),					
									array(
											"attr_name" => "costo",
											"attr_value" => $this->getCosto()
									),
									array(
											"attr_name" => "finanziamento",
											"attr_value" => $this->getFinanziamento()
									)
								);
			
			
			$xml_codici_tipologia_cop_finanz ="";
			$codici_tipologia_cop_finanz = $this->getCodici_tipologia_cop_finanz();
			
			foreach($codici_tipologia_cop_finanz as $codice_tipologia_cop_finanz) {
				$xml_codice_tipologia_cop_finanz = $this->generateXmlNode("CODICE_TIPOLOGIA_COP_FINANZ", array(), $codice_tipologia_cop_finanz);
				$xml_codici_tipologia_cop_finanz.=$xml_codice_tipologia_cop_finanz;
				
			}
			
			$innerElements 
						= array(
										array(
												"nodeName"	=> null,
												"value"		=> $xml_codici_tipologia_cop_finanz
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