<?php


namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\Descrizione;
use CipeBundle\Entity\Classificazioni\CupTipoIndirizzo;
use CipeBundle\Entity\Classificazioni\CupStrumentoProgrammazione;
use CipeBundle\Entity\TipoDescrizione;



/**
 * LavoriPubblici
 * <!ELEMENT LAVORI_PUBBLICI EMPTY>
<!ATTLIST LAVORI_PUBBLICI
          nome_str_infrastr CDATA #REQUIRED
          str_infrastr_unica (SI | NO) #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          descrizione_intervento CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
 * 
 * <xs:element name="LAVORI_PUBBLICI">
		<xs:complexType>
			<xs:complexContent>
				<xs:restriction base="xs:anyType">
					<xs:attribute name="nome_str_infrastr" type="xs:string" use="required"/>
					<xs:attribute name="str_infrastr_unica" use="required">
						<xs:simpleType>
							<xs:restriction base="xs:NMTOKEN">
								<xs:enumeration value="SI"/>
								<xs:enumeration value="NO"/>
							</xs:restriction>
						</xs:simpleType>
					</xs:attribute>
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
					<xs:attribute name="descrizione_intervento" type="xs:string" use="required"/>
					<xs:attribute name="strum_progr" type="xs:string" use="required"/>
					<xs:attribute name="desc_strum_progr" type="xs:string"/>
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
class LavoriPubblici extends TipoDescrizione
{
	/**
	 * Nome della struttura o infrastruttura a cui è funzionale l'acquisito del bene.
	 * Valorizzazione dell'attributo obbligatoria.
	 * Lunghezza massima 100 caratteri.
     * Lunghezza minima 5 caratteri.
     * Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti. Non sono consentiti solo numeri o solo segni matematici.
	 * @var String
	 */
	public $nome_str_infrastr=null;
	function getNome_str_infrastr() { return $this->nome_str_infrastr; }
	function setNome_str_infrastr($nome_str_infrastr) { $this->nome_str_infrastr = self::setFilterParam($nome_str_infrastr, "string"); }

	
	public $str_infrastr_unica=null;
	function getStr_infrastr_unica() { return $this->str_infrastr_unica; }
	function setStr_infrastr_unica($str_infrastr_unica) { $this->str_infrastr_unica = self::setFilterParam($str_infrastr_unica, "string"); }

		
	
	/**
     * Descrizione dell'intervento. 
	 * Lunghezza massima 100 caratteri. 
	 * Lunghezza minima 5 caratteri. 
	 * Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti. 
	 * Non sono consentiti solo numeri o solo segni matematici. 
     *
     * @var String
     */
    public $descrizione_intervento = null;
	function getDescrizione_intervento() { return $this->descrizione_intervento; }
	function setDescrizione_intervento($descrizione_intervento) { $this->descrizione_intervento = self::setFilterParam($descrizione_intervento, "string"); }

	
	
    // --- OPERATIONS ---

	
	public function __construct() {
		parent::__construct();
		$this->setXmlName("LAVORI_PUBBLICI");
	}
	
	/**
	 <!ELEMENT LAVORI_PUBBLICI EMPTY>
<!ATTLIST LAVORI_PUBBLICI
          nome_str_infrastr CDATA #REQUIRED
          str_infrastr_unica (SI | NO) #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          descrizione_intervento CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
	 * 
	 * 
	 * @return boolean
	 */
    public function validate(ExecutionContext $context) { 
		$type = "attr";
		
		// nome_str_infrastr
		$val = $this->getNome_str_infrastr();
		$this->commonValidateStringParam($type, $val, "nome_str_infrastr", 5, 255);
			
		// str_infrastr_unica
		$val = $this->getStr_infrastr_unica();
		if(!$this->isNotNullAndIsNotEmpty($val))  $this->setValidateStatus("str_infrastr_unica" ,false ,$type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		if(!\in_array($val, array("SI", "NO"))) $this->setValidateStatus("str_infrastr_unica" ,false	,$type, "[$val] L'elemento può assumere valore 'SI', 'NO");
		
		// descrizione_intervento
		$val = $this->getDescrizione_intervento();
		$this->commonValidateStringParam($type, $val, "descrizione_intervento", 5, 255);
		
		$this->setCampi_obbligatori(array(self::TIPO_IND_AREA_RIFER, self::IND_AREA_RIFER, self::STRUM_PROGR));
		$this->setLunghezza_massima_altre_informazioni(100);
		return parent::validate($context);
	}
	
	/**
	 * <xs:element name="LAVORI_PUBBLICI">
		<xs:complexType>
			<xs:complexContent>
				<xs:restriction base="xs:anyType">
					<xs:attribute name="nome_str_infrastr" type="xs:string" use="required"/>
					<xs:attribute name="str_infrastr_unica" use="required">
						<xs:simpleType>
							<xs:restriction base="xs:NMTOKEN">
								<xs:enumeration value="SI"/>
								<xs:enumeration value="NO"/>
							</xs:restriction>
						</xs:simpleType>
					</xs:attribute>
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
					<xs:attribute name="descrizione_intervento" type="xs:string" use="required"/>
					<xs:attribute name="strum_progr" type="xs:string" use="required"/>
					<xs:attribute name="desc_strum_progr" type="xs:string"/>
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
											"attr_name" => "nome_str_infrastr",
											"attr_value" => $this->getNome_str_infrastr()
									),
									array(
											"attr_name" => "str_infrastr_unica",
											"attr_value" => $this->getStr_infrastr_unica()
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
											"attr_name" => "descrizione_intervento",
											"attr_value" => $this->getDescrizione_intervento()
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

?>