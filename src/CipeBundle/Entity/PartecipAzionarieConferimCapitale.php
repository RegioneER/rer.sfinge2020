<?php


namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\Descrizione;
use CipeBundle\Entity\Classificazioni\CupTipoIndirizzo;
use CipeBundle\Entity\Classificazioni\CupStrumentoProgrammazione;
use CipeBundle\Entity\TipoDescrizione;
use CipeBundle\Entity\Classificazioni\CupFinalita;


/**
 * 
 * <!ELEMENT PARTECIP_AZIONARIE_CONFERIM_CAPITALE EMPTY>
<!ATTLIST PARTECIP_AZIONARIE_CONFERIM_CAPITALE
          ragione_sociale CDATA #REQUIRED
          partita_iva CDATA #REQUIRED
          ragione_sociale_prec CDATA #IMPLIED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          finalita CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #REQUIRED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
 * 	<xs:element name="PARTECIP_AZIONARIE_CONFERIM_CAPITALE">
		<xs:complexType>
			<xs:complexContent>
				<xs:restriction base="xs:anyType">
					<xs:attribute name="ragione_sociale" type="xs:string" use="required"/>
					<xs:attribute name="partita_iva" type="xs:string" use="required"/>
					<xs:attribute name="ragione_sociale_prec" type="xs:string"/>
					<xs:attribute name="tipo_ind_area_rifer" use="required">
						<xs:simpleType>
							<xs:restriction base="xs:NMTOKEN">
								<xs:enumeration value="01"/>
								<xs:enumeration value="02"/>
								<xs:enumeration value="03"/>
								<xs:enumeration value="04"/>
								<xs:enumeration value="05"/>
							</xs:restriction>
						</xs:simpleType>
					</xs:attribute>
					<xs:attribute name="ind_area_rifer" type="xs:string" use="required"/>
					<xs:attribute name="finalita" type="xs:string" use="required"/>
					<xs:attribute name="strum_progr" type="xs:string" use="required"/>
					<xs:attribute name="desc_strum_progr" type="xs:string" use="required"/>
					<xs:attribute name="altre_informazioni" type="xs:string"/>
					<xs:attribute name="flagLeggeObiettivo"	type="xs:string"/>
					<xs:attribute name="numDeliberaCipe" type="xs:string"/>
					<xs:attribute name="annoDelibera" type="xs:string"/>
				</xs:restriction>
			</xs:complexContent>
		</xs:complexType>
	</xs:element>
 *
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 * @see http://cb.schema31.it/cb/issue/173243
 */
class PartecipAzionarieConferimCapitale extends TipoDescrizione
{
	
	protected $ragione_sociale=null;
	function getRagione_sociale() { return $this->ragione_sociale; }
	function setRagione_sociale($ragione_sociale) { $this->ragione_sociale = self::setFilterParam($ragione_sociale, "string"); }

	protected $ragione_sociale_prec=null;
	function getRagione_sociale_prec() { return $this->ragione_sociale_prec; }
	function setRagione_sociale_prec($ragione_sociale_prec) { $this->ragione_sociale_prec = self::setFilterParam($ragione_sociale_prec, "string"); }

	protected $partita_iva=null;
	function getPartita_iva() { return $this->partita_iva; }
	function setPartita_iva($partita_iva) { $this->partita_iva = self::setFilterParam($partita_iva, "string");  }
	
	/**
   
    // --- OPERATIONS ---

	*/
	public function __construct() {
		parent::__construct();
		$this->setXmlName("PARTECIP_AZIONARIE_CONFERIM_CAPITALE");
	}
	
	/**
	 <!ELEMENT PARTECIP_AZIONARIE_CONFERIM_CAPITALE EMPTY>
<!ATTLIST PARTECIP_AZIONARIE_CONFERIM_CAPITALE
          ragione_sociale CDATA #REQUIRED
          partita_iva CDATA #REQUIRED
          ragione_sociale_prec CDATA #IMPLIED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          finalita CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #REQUIRED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
 * 
	 * @return boolean
	 */
    public function validate(ExecutionContext $context) { 
		$this->setLunghezza_massima_altre_informazioni(4000);
		$type = "attr";
		
		// ragione_sociale
		$val = $this->getRagione_sociale();
		$this->commonValidateStringParam($type, $val, "ragione_sociale", 5, 100);
		
		// partita_iva
		$val = $this->getPartita_iva();
		$this->commonValidateStringParam($type, $val, "partita_iva", 11, 16, false);
		
		// ragione_sociale_prec
		$val = $this->getRagione_sociale_prec();
		$this->commonValidateStringParam($type, $val, "ragione_sociale_prec", 5, 100);
		
		
		/* @var $CupNatura CupNatura */
		$CupFinalita = $this->validateClassification(new CupFinalita(), array("codice" => $val, "stato" => 'A'), true);
		
		if(!$CupFinalita) {
			$this->setValidateStatus("finalita"	,false, $type, "[$val] " . self::COMMON_VALIDATE_CODE_NOT_EXIST);
		}
		
		$this->setCampi_obbligatori(array(self::TIPO_IND_AREA_RIFER, self::IND_AREA_RIFER, self::STRUM_PROGR, self::DESC_STRUM_PROGR));
		$this->setLunghezza_massima_altre_informazioni(4000);
		return parent::validate($context);
	}
	
	/**
	 * 	<xs:element name="PARTECIP_AZIONARIE_CONFERIM_CAPITALE">
		<xs:complexType>
			<xs:complexContent>
				<xs:restriction base="xs:anyType">
					<xs:attribute name="ragione_sociale" type="xs:string" use="required"/>
					<xs:attribute name="partita_iva" type="xs:string" use="required"/>
					<xs:attribute name="ragione_sociale_prec" type="xs:string"/>
					<xs:attribute name="tipo_ind_area_rifer" use="required">
						<xs:simpleType>
							<xs:restriction base="xs:NMTOKEN">
								<xs:enumeration value="01"/>
								<xs:enumeration value="02"/>
								<xs:enumeration value="03"/>
								<xs:enumeration value="04"/>
								<xs:enumeration value="05"/>
							</xs:restriction>
						</xs:simpleType>
					</xs:attribute>
					<xs:attribute name="ind_area_rifer" type="xs:string" use="required"/>
					<xs:attribute name="finalita" type="xs:string" use="required"/>
					<xs:attribute name="strum_progr" type="xs:string" use="required"/>
					<xs:attribute name="desc_strum_progr" type="xs:string" use="required"/>
					<xs:attribute name="altre_informazioni" type="xs:string"/>
					<xs:attribute name="flagLeggeObiettivo"	type="xs:string"/>
					<xs:attribute name="numDeliberaCipe" type="xs:string"/>
					<xs:attribute name="annoDelibera" type="xs:string"/>
				</xs:restriction>
			</xs:complexContent>
		</xs:complexType>
	</xs:element>
	 * @return String
	 * @throws \Exception
	 */
	public function serialize() {
		try {
			parent::serialize();
			$nodeName = $this->getXmlName();
			$attributes = array(
									array(
											"attr_name" => "ragione_sociale",
											"attr_value" => $this->getRagione_sociale()
									),
									array(
											"attr_name" => "partita_iva",
											"attr_value" => $this->getPartita_iva()
									),
									array(
											"attr_name" => "ragione_sociale_prec",
											"attr_value" => $this->getRagione_sociale_prec()
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
									)
								);
			
			$xml = $this->generateXmlNode($nodeName, $attributes);
			return $xml;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
} /* end of class AcquistoBeni */

