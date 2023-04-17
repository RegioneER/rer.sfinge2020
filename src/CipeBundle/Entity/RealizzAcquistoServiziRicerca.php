<?php


namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\Descrizione;
use CipeBundle\Entity\Classificazioni\CupTipoIndirizzo;
use CipeBundle\Entity\Classificazioni\CupStrumentoProgrammazione;
use CipeBundle\Entity\TipoDescrizione;


/**
 * 
 * <!ELEMENT REALIZZ_ACQUISTO_SERVIZI_RICERCA EMPTY>
<!ATTLIST REALIZZ_ACQUISTO_SERVIZI_RICERCA
          denominazione_progetto CDATA #REQUIRED
          ente CDATA #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #IMPLIED
          ind_area_rifer CDATA #IMPLIED
          descrizione_intervento CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
 * 
 * <xs:element name="REALIZZ_ACQUISTO_SERVIZI_RICERCA">
		<xs:complexType>
			<xs:complexContent>
				<xs:restriction base="xs:anyType">
					<xs:attribute name="denominazione_progetto" type="xs:string" use="required"/>
					<xs:attribute name="ente" type="xs:string" use="required"/>
					<xs:attribute name="tipo_ind_area_rifer">
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
					<xs:attribute name="ind_area_rifer" type="xs:string"/>
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
class RealizzAcquistoServiziRicerca extends TipoDescrizione
{
	/**
	 * Denominazione del progetto.
	 * Valorizzazione dell'attributo obbligatoria.
	 * Lunghezza massima 100 caratteri.
	 * Lunghezza minima 5 caratteri.
	 * Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti. Non sono consentiti solo numeri o solo segni matematici.
	 */
			
	public $denominazione_progetto=null;
	function getDenominazione_progetto() { return $this->denominazione_progetto; }
	function setDenominazione_progetto($denominazione_progetto) { $this->denominazione_progetto = self::setFilterParam($denominazione_progetto, "string"); }

	/**
	 * Nome dell’Ente che realizza il progetto.
	 * Valorizzazione dell'attributo obbligatoria.
	 * Lunghezza massima 100 caratteri.
     * Lunghezza minima 5 caratteri.
     * Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti. Non sono consentiti solo numeri o solo segni matematici.
	 */
	public $ente=null;
	function getEnte() { return $this->ente; }
	function setEnte($ente) { $this->ente = self::setFilterParam($ente, "string"); }

			
	/**
     * Descrizione dell’intervento. 
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
		$this->setXmlName("REALIZZ_ACQUISTO_SERVIZI_RICERCA");
	}
	
	/**
	 <!ELEMENT REALIZZ_ACQUISTO_SERVIZI_RICERCA EMPTY>
<!ATTLIST REALIZZ_ACQUISTO_SERVIZI_RICERCA
          denominazione_progetto CDATA #REQUIRED
          ente CDATA #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #IMPLIED
          ind_area_rifer CDATA #IMPLIED
          descrizione_intervento CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
 * 
	 * @return boolean
	 */
    public function validate(ExecutionContext $context) { 
		$type = "attr";
				
		// denominazione_progetto
		$val = $this->getDenominazione_progetto();
		$this->commonValidateStringParam($type, $val, "denominazione_progetto", 5, 100);

		// ente
		$val = $this->getEnte();
		$this->commonValidateStringParam($type, $val, "ente", 5, 100);
	
		// descrizione_intervento
		$val = $this->getDescrizione_intervento();
		$this->commonValidateStringParam($type, $val, "descrizione_intervento", 5, 100);

		$this->setCampi_obbligatori(array(self::STRUM_PROGR));
		$this->setLunghezza_massima_altre_informazioni(100);
		return parent::validate($context);
	}
	
	/**
	 * <xs:element name="REALIZZ_ACQUISTO_SERVIZI_RICERCA">
		<xs:complexType>
			<xs:complexContent>
				<xs:restriction base="xs:anyType">
					<xs:attribute name="denominazione_progetto" type="xs:string" use="required"/>
					<xs:attribute name="ente" type="xs:string" use="required"/>
					<xs:attribute name="tipo_ind_area_rifer">
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
					<xs:attribute name="ind_area_rifer" type="xs:string"/>
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
											"attr_name" => "denominazione_progetto",
											"attr_value" => $this->getDenominazione_progetto()
									),
									array(
											"attr_name" => "ente",
											"attr_value" => $this->getEnte()
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
											"attr_value" => $this->getDescrizione_intervento(),
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