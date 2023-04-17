<?php


namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\Descrizione;
use CipeBundle\Entity\Classificazioni\CupTipoIndirizzo;
use CipeBundle\Entity\Classificazioni\CupStrumentoProgrammazione;
use CipeBundle\Entity\TipoDescrizione;


/**
 <!ELEMENT REALIZZ_ACQUISTO_SERVIZI_FORMAZIONE EMPTY>
<!ATTLIST REALIZZ_ACQUISTO_SERVIZI_FORMAZIONE
          denom_progetto CDATA #REQUIRED
          denom_ente_corso CDATA #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          obiett_corso CDATA #REQUIRED
          mod_intervento_frequenza CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #REQUIRED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
 * 
 * 	<xs:element name="REALIZZ_ACQUISTO_SERVIZI_FORMAZIONE">
		<xs:complexType>
			<xs:complexContent>
				<xs:restriction base="xs:anyType">
					<xs:attribute name="denom_progetto" type="xs:string" use="required"/>
					<xs:attribute name="denom_ente_corso" type="xs:string" use="required"/>
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
					<xs:attribute name="obiett_corso" type="xs:string" use="required"/>
					<xs:attribute name="mod_intervento_frequenza" type="xs:string" use="required"/>
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
class RealizzAcquistoServiziFormazione extends TipoDescrizione
{
	protected $denom_progetto;
	function getDenom_progetto() { return $this->denom_progetto; }
	function setDenom_progetto($denom_progetto) { $this->denom_progetto = self::setFilterParam($denom_progetto, "string"); }

	protected $denom_ente_corso;
	function getDenom_ente_corso() { return $this->denom_ente_corso; }
	function setDenom_ente_corso($denom_ente_corso) { $this->denom_ente_corso = self::setFilterParam($denom_ente_corso, "string");  }

	protected $obiett_corso;
	function getObiett_corso() { return $this->obiett_corso; }
	function setObiett_corso($obiett_corso) { $this->obiett_corso = $obiett_corso; }

	protected $mod_intervento_frequenza;
	function getMod_intervento_frequenza() { return $this->mod_intervento_frequenza; }
	function setMod_intervento_frequenza($mod_intervento_frequenza) { $this->mod_intervento_frequenza = self::setFilterParam($mod_intervento_frequenza, "string");  }

		




 // --- OPERATIONS ---

	
	public function __construct() {
		parent::__construct();
		$this->setXmlName("REALIZZ_ACQUISTO_SERVIZI_FORMAZIONE");
	}
	
	/**
	 <!ELEMENT REALIZZ_ACQUISTO_SERVIZI_FORMAZIONE EMPTY>
<!ATTLIST REALIZZ_ACQUISTO_SERVIZI_FORMAZIONE
          denom_progetto CDATA #REQUIRED
          denom_ente_corso CDATA #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          obiett_corso CDATA #REQUIRED
          mod_intervento_frequenza CDATA #REQUIRED
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
		$type = "attr";
				
		// denom_progetto
		$val = $this->getDenom_progetto();
		$this->commonValidateStringParam($type, $val, "denom_progetto", 5, 100);

		// denom_ente_corso
		$val = $this->getDenom_ente_corso();
		$this->commonValidateStringParam($type, $val, "denom_ente_corso", 5, 255);

		// obiett_corso
		$val = $this->getObiett_corso();
		$this->commonValidateStringParam($type, $val, "obiett_corso", 5, 256);

		// mod_intervento_frequenza
		$val = $this->getMod_intervento_frequenza();
		$this->commonValidateStringParam($type, $val, "mod_intervento_frequenza", 5, 255);

		$this->setCampi_obbligatori(array(self::TIPO_IND_AREA_RIFER, self::IND_AREA_RIFER, self::STRUM_PROGR));
		$this->setLunghezza_massima_altre_informazioni(4000);
		return parent::validate($context);
		
	}
	
	/**
	 * <xs:element name="REALIZZ_ACQUISTO_SERVIZI_FORMAZIONE">
		<xs:complexType>
			<xs:complexContent>
				<xs:restriction base="xs:anyType">
					<xs:attribute name="denom_progetto" type="xs:string" use="required"/>
					<xs:attribute name="denom_ente_corso" type="xs:string" use="required"/>
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
					<xs:attribute name="obiett_corso" type="xs:string" use="required"/>
					<xs:attribute name="mod_intervento_frequenza" type="xs:string" use="required"/>
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
											"attr_name" => "denom_progetto",
											"attr_value" => $this->getDenom_progetto()
									),
									array(
											"attr_name" => "denom_ente_corso",
											"attr_value" => $this->getDenom_ente_corso()
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
											"attr_name" => "obiett_corso",
											"attr_value" => $this->getObiett_corso(),
									),
									array(
											"attr_name" => "mod_intervento_frequenza",
											"attr_value" => $this->getMod_intervento_frequenza()
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
	
} /* end of class RealizzAcquistoServiziNoFormazioneRicerca */

?>