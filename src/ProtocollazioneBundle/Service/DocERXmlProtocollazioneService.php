<?php

namespace ProtocollazioneBundle\Service;

use BaseBundle\Service\RecursiveXmlBuilder;

/**
 * Description of DocERXmlProtocollazioneService
 *
 * @author gaetanoborgosano
 */


class DocERXmlProtocollazioneService {

    /** @var RecursiveXmlBuilder */
    protected $XmlBuilder;

    protected static $base_path="/../Resources/schemi/xml_schema/protocollazione/";
    protected static $protocollazioneXmlFiles = "";
    protected static $protocollazioneRootXmlNode = "Segnatura";
    protected static $protocollazioneXsdFile = "xsd/Protocollazione.xsd";
    protected static $protecollazioneXsdValitation = false;
    protected static $protocollazioneEncoding = "UTF-8";
    protected static $protocollazioneNamespace = 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
    protected $arrayParams = array();


    public function __construct($XmlBuilder) {
        $this->XmlBuilder = $XmlBuilder;
        $this->initXmlBuilder();
    }

    static function getBase_path() { return self::$base_path; }
    static function getProtocollazioneXmlFiles() { 	return self::$protocollazioneXmlFiles; }
    static function getProtocollazioneRootXmlNode() { return self::$protocollazioneRootXmlNode; }
    static function getProtocollazioneXsdFile() { return self::$protocollazioneXsdFile; }
    static function getProtecollazioneXsdValitation() { return self::$protecollazioneXsdValitation; }
    static function getProtocollazioneEncoding() { return self::$protocollazioneEncoding; }
    static function getProtocollazioneNamespace() { return self::$protocollazioneNamespace; }
    static function setProtocollazioneXmlFiles($protocollazioneXmlFiles) { self::$protocollazioneXmlFiles = $protocollazioneXmlFiles; }
    static function setProtocollazioneRootXmlNode($protocollazioneRootXmlNode) { self::$protocollazioneRootXmlNode = $protocollazioneRootXmlNode; }
    static function setProtocollazioneXsdFile($protocollazioneXsdFile) { self::$protocollazioneXsdFile = $protocollazioneXsdFile; }
    static function setProtecollazioneXsdValitation($protecollazioneXsdValitation) { self::$protecollazioneXsdValitation = $protecollazioneXsdValitation; }
    static function setProtocollazioneEncoding($protocollazioneEncoding) { self::$protocollazioneEncoding = $protocollazioneEncoding; }
    static function setProtocollazioneNamespace($protocollazioneNamespace) { self::$protocollazioneNamespace = $protocollazioneNamespace; }

    protected function getXmlBuilder() { return $this->XmlBuilder; }
    function setXmlBuilder(RecursiveXmlBuilder $XmlBuilder) { $this->XmlBuilder = $XmlBuilder; }

    protected function initXmlBuilder() {
        $base		 = __DIR__. self::getBase_path();
        $XmlBuilder	 = $this->getXmlBuilder();
        $dirXmlFiles	 = $base.self::getProtocollazioneXmlFiles();
        $rootXmlNodeFile = $dirXmlFiles.self::getProtocollazioneRootXmlNode().".xml";
        $xsdFile	 = $base.self::getProtocollazioneXsdFile();
        $xsdValidation	 = self::getProtecollazioneXsdValitation();
        $encoding	 = self::getProtocollazioneEncoding();
        $xmlnamespace	 = self::getProtocollazioneNamespace();
        $rootXmlNode	 = file_get_contents($rootXmlNodeFile);
        if(!$rootXmlNode) {			
            throw new \Exception("Impossibile caricare il file radice $rootXmlNodeFile");       
        }
        $XmlBuilder->init($dirXmlFiles, $rootXmlNode, $xsdFile, $xsdValidation, $encoding, $xmlnamespace);
        $this->setXmlBuilder($XmlBuilder);
    }

    protected function buildArray($name, $value) {
        return array("__".$name."__" => $value);
    }

    public function buildXml($param) {
        $xml = $this->getXmlBuilder()->buildXml($param);
        $rootNode = "<{$this->getProtocollazioneRootXmlNode()}>";
        $newRootNode = "<{$this->getProtocollazioneRootXmlNode()}{$this->getXmlBuilder()->getStringXmlNamespace()}>";
        $xml = str_replace($rootNode, $newRootNode, $xml);
        return $xml;
    }

    private function checkParam($param) {
        if(\is_null($param)) { return false; }
        return true;
    }

    /**
     * 
    <xs:complexType name="IndirizzoTelematicoType" mixed="true">
            <xs:attribute name="tipo" use="optional" default="smtp">
                    <xs:simpleType>
                            <xs:restriction base="xs:NMTOKEN">
                                    <xs:enumeration value="uri"/>
                                    <xs:enumeration value="smtp"/>
                                    <xs:enumeration value="NMTOKEN"/>
                            </xs:restriction>
                    </xs:simpleType>
            </xs:attribute>
            <xs:attribute name="note" type="xs:anySimpleType"/>
    </xs:complexType>
     */
    public function buildIndirizzoTelematico($tipo="smtp", $note, $IndirizzoTelematico) {
        try {
            $IndirizzoTelematicoArray = array();
            $IndirizzoTelematicoArray["##tipo##"] = $this->buildArray("tipo", $tipo);
            $IndirizzoTelematicoArray["##note##"] = $this->buildArray("note", $note);
            $IndirizzoTelematicoArray["##IndirizzoTelematico##"] = $this->buildArray("IndirizzoTelematico", $IndirizzoTelematico);
            return $IndirizzoTelematicoArray;

        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * 
    <xs:complexType name="IndirizzoPostaleType">
            <xs:choice>
                    <xs:element name="Denominazione" type="DenominazioneType"/>
                    <xs:sequence>
                            <xs:element name="Toponimo" type="ToponimoType"/>
                            <xs:element name="Civico" type="CivicoType"/>
                            <xs:element name="CAP" type="CAPType"/>
                            <xs:element name="Comune" type="ComuneType"/>
                            <xs:element name="Provincia" type="ProvinciaType"/>
                            <xs:element name="Nazione" type="NazioneType" minOccurs="0"/>
                    </xs:sequence>
            </xs:choice>
    </xs:complexType>
     */
    public function buildIndirizzoPostale($Denominazione, $dug, $Toponimo, $Civico, $Cap, $Comune, $Provincia, $Nazione) {
        try {
            $IndirizzoPostale = array();
            $ToponimoArray = array();
            if($this->checkParam($Denominazione)) {
                $IndirizzoPostale['##Denominazione##'] = $this->buildArray("Denominazione", $Denominazione);
            } else {
                $IndirizzoPostale['##Toponimo##']   = $Toponimo;
                $IndirizzoPostale['##Civico##']     = $this->buildArray("Civico", $Civico);
                $IndirizzoPostale['##Cap##']        = $this->buildArray("Cap", $Cap);
                $IndirizzoPostale['##Comune##']     = $this->buildArray("Comune", $Comune);
                $IndirizzoPostale['##Provincia##']  = $this->buildArray("Provincia", $Provincia);
                
                if($this->checkParam($Nazione))	{
                    $IndirizzoPostale['##Nazione##'] = $this->buildArray("Nazione", $Nazione);             
                }
            }
            return $IndirizzoPostale;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * 
        <xs:sequence>
                <xs:element name="Denominazione" type="DenominazioneType" minOccurs="0"/>
                <xs:element name="Persona" type="PersonaType" maxOccurs="unbounded"/>
        </xs:sequence>
     */
    public function buildMittDestPersona($Denominazione, $Persona) {
        try {
            $MittDestPersonaArray = array();
            if($this->checkParam($Denominazione)) { 
                $MittDestPersonaArray['##Denominazione##']	= $this->buildArray("Denominazione", $Denominazione);                 
            }
            $MittDestPersonaArray['##Persona##']= $Persona;

            return $MittDestPersonaArray;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * 
    <xs:complexType name="PersonaType">
        <xs:sequence>
                <xs:element name="Identificativo" type="IdentificativoType" minOccurs="0"/>
                <xs:element name="Nome" type="NomeType" minOccurs="0"/>
                <xs:element name="Cognome" type="CognomeType" minOccurs="0"/>
                <xs:element name="Titolo" type="TitoloType" minOccurs="0"/>
                <xs:element name="IndirizzoTelematico" type="IndirizzoTelematicoType" minOccurs="0"/>
                <xs:element name="InvioPEC" type="InvioPECType" minOccurs="0"/>
                <xs:element name="IndirizzoPostale" type="IndirizzoPostaleType" minOccurs="0"/>
                <xs:element name="Denominazione" type="DenominazioneType" minOccurs="0"/>
                <xs:element name="CodiceFiscale" type="CodiceFiscaleType" minOccurs="0"/>
                <xs:element name="Metadati" type="MetadatiType" minOccurs="0"/>
        </xs:sequence>
        <xs:attribute name="id" type="xs:string" use="required"/>
    </xs:complexType>
     */
    public function buildPersona($id, 
                                 $Identificativo = null, 
                                 $Nome = null, 
                                 $Cognome = null, 
                                 $IndirizzoTelematico = null,
                                 $IndirizzoPostale = null,
                                 $Metadati  = null
                                ) {
        try {
            $PersonaArray = array();

            $PersonaArray['##id##'] = $this->buildArray("id", $id);
            if($this->checkParam($Identificativo)) { $PersonaArray['##Identificativo##'] = $this->buildArray("Identificativo", $Identificativo); }
            if($this->checkParam($Nome)) { $PersonaArray['##Nome##'] = $this->buildArray("Nome", $Nome); }
            if($this->checkParam($Cognome)) { $PersonaArray['##Cognome##'] = $this->buildArray("Cognome", $Cognome);}
            if($this->checkParam($IndirizzoTelematico))	{ $PersonaArray['##IndirizzoTelematico##'] = $IndirizzoTelematico; }
            if($this->checkParam($IndirizzoPostale)) { $PersonaArray['##IndirizzoPostale##'] = $IndirizzoPostale; }
            if($this->checkParam($Metadati)) { $PersonaArray['##MEtadati##'] = $this->buildArray("Metadati", $Metadati); }

            return $PersonaArray;

        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * 
    <xs:complexType name="RuoloType">
            <xs:sequence>
                    <xs:element name="Denominazione" type="DenominazioneType"/>
                    <xs:element name="Identificativo" type="IdentificativoType" minOccurs="0"/>
                    <xs:element name="Persona" type="PersonaType" minOccurs="0"/>
            </xs:sequence>
    </xs:complexType>
     */
    public function buildRuolo(
                                $Denominazione,
                                $Identificativo=null,
                                $Persona=null
                              ) {
        try {
            $RuoloArray= array();
            $RuoloArray["##Denominazione##"] = $this->buildArray("Denominazione", $Denominazione);
            if($this->checkParam($Identificativo)) { $RuoloArray["##Identificativo##"] = $this->buildArray("Identificativo", $Identificativo); }
            if($this->checkParam($Persona)) { $RuoloArray["##Persona##"] = $Persona; }

            return $RuoloArray;

        } catch (\Exception $ex) {
            throw $ex;
        }

    }

    /**
    <xs:complexType name="AmministrazioneType">
        <xs:sequence>
                <xs:element name="Denominazione" type="DenominazioneType" minOccurs="0"/>
                <xs:element name="CodiceAmministrazione" type="CodiceAmministrazioneType"/>
                <xs:element name="IndirizzoTelematico" type="IndirizzoTelematicoType" minOccurs="0" maxOccurs="unbounded"/>
                <xs:element name="ForzaIndirizzoTelematico" type="xs:string" minOccurs="0"/>
                <xs:choice minOccurs="0">
                        <xs:element name="UnitaOrganizzativa" type="UnitaOrganizzativaType"/>
                        <xs:sequence>
                                <xs:choice minOccurs="0">
                                        <xs:element name="Ruolo" type="RuoloType"/>
                                        <xs:element name="Persona" type="PersonaType"/>
                                </xs:choice>
                                <xs:element name="IndirizzoPostale" type="IndirizzoPostaleType" minOccurs="0"/>
                                <xs:element name="Telefono" type="TelefonoType" minOccurs="0" maxOccurs="unbounded"/>
                                <xs:element name="Fax" type="FaxType" minOccurs="0" maxOccurs="unbounded"/>
                        </xs:sequence>
                </xs:choice>
                <xs:element name="InvioPEC" type="InvioPECType" minOccurs="0"/>
        </xs:sequence>
    </xs:complexType>
     */
    public function buildAmministrazione(
                                            $CodiceAmministrazione,
                                            $Denominazione		= null,
                                            $IndirizzoTelematico	= null,
                                            $ForzaIndirizzoTelematico	= null,
                                            $UnitaOrganizzativa		= null,
                                            $Ruolo			= null,
                                            $Persona			= null,
                                            $IndirizzoPostale		= null,
                                            $Telefono			= null,
                                            $Fax                        = null
                                        ) {
        try {
            $AmministrazioneArray = array();
            $AmministrazioneArray["##CodiceAmministrazione##"] = $this->buildArray("CodiceAmministrazione", $CodiceAmministrazione);
            if($this->checkParam($Denominazione)) { $AmministrazioneArray["##Denominazione##"] = $this->buildArray("Denominazione", $Denominazione); }
            if($this->checkParam($IndirizzoTelematico)) { $AmministrazioneArray["##IndirizzoTelematico##"] = $IndirizzoTelematico; }
            if($this->checkParam($ForzaIndirizzoTelematico)) { $AmministrazioneArray["##ForzaIndirizzoTelematico##"] = $this->buildArray("ForzaIndirizzoTelematico", $ForzaIndirizzoTelematico); }
            if($this->checkParam($UnitaOrganizzativa)) {
                $AmministrazioneArray["##UnitaOrganizzativa##"]	= $UnitaOrganizzativa;
            } else {
                if($this->checkParam($Ruolo)) {
                        $AmministrazioneArray["##Ruolo##"] = $Ruolo;
                } else {
                    if($this->checkParam($Persona)) { $AmministrazioneArray["##Persona##"] = $Persona; }
                }
            }
            if($this->checkParam($IndirizzoPostale)) { $AmministrazioneArray["##IndirizzoPostale##"] = $IndirizzoPostale; }
            if($this->checkParam($Telefono)) { $AmministrazioneArray["##Telefono##"] = $Telefono; }
            if($this->checkParam($Fax)) { $AmministrazioneArray["##Fax##"] = $this->buildArray("Fax", $Fax); }

            return $AmministrazioneArray;

        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
    <xs:complexType name="UnitaOrganizzativaType">
        <xs:sequence>
                <xs:element name="Denominazione" type="DenominazioneType" minOccurs="0"/>
                <xs:element name="Identificativo" type="IdentificativoType"/>
                <xs:choice minOccurs="0">
                        <xs:element name="UnitaOrganizzativa" type="UnitaOrganizzativaType"/>
                        <xs:sequence>
                                <xs:choice minOccurs="0" maxOccurs="unbounded">
                                        <xs:element name="Ruolo" type="RuoloType"/>
                                        <xs:element name="Persona" type="PersonaType"/>
                                </xs:choice>
                                <xs:element name="IndirizzoPostale" type="IndirizzoPostaleType" minOccurs="0"/>
                                <xs:element name="IndirizzoTelematico" type="IndirizzoTelematicoType" minOccurs="0" maxOccurs="unbounded"/>
                                <xs:element name="Telefono" type="TelefonoType" minOccurs="0" maxOccurs="unbounded"/>
                                <xs:element name="Fax" type="FaxType" minOccurs="0" maxOccurs="unbounded"/>
                        </xs:sequence>
                </xs:choice>
        </xs:sequence>
        <xs:attribute name="tipo" default="permanente">
                <xs:simpleType>
                        <xs:restriction base="xs:NMTOKEN">
                                <xs:enumeration value="temporanea"/>
                                <xs:enumeration value="permanente"/>
                        </xs:restriction>
                </xs:simpleType>
        </xs:attribute>
    </xs:complexType>
     */
    public function buildUnitaOrganizzativa(
                                            $Identificativo,
                                            $tipo                   = "permanente",
                                            $Denominazione          = null,
                                            $Ruolo                  = null,
                                            $Persona                = null,
                                            $IndirizzoTelematico    = null,
                                            $Telefono               = null,
                                            $Fax                    = null
                                           ) {
            try {
                $UnitaOrganizzativaArray = array();
                $UnitaOrganizzativaArray["##Identificativo##"]  = $this->buildArray("Identificativo", $Identificativo);
                $UnitaOrganizzativaArray["##tipo##"]            = $this->buildArray("tipo", $tipo);
                if($this->checkParam($Denominazione)) { 
                    $UnitaOrganizzativaArray["##Denominazione##"] = $this->buildArray("Denominazione", $Denominazione);                         
                }
                if($this->checkParam($Ruolo)) {
                    $UnitaOrganizzativaArray["##Ruolo##"] = $Ruolo;
                }
                else {
                    if($this->checkParam($Persona)) { 
                        $UnitaOrganizzativaArray["##Persona##"] = $Persona;                             
                    }
                }
                if($this->checkParam($IndirizzoTelematico)) { $UnitaOrganizzativaArray["##IndirizzoTelematico##"] = $IndirizzoTelematico; }
                if($this->checkParam($Telefono)) { $UnitaOrganizzativaArray["##Telefono##"] = $Telefono; }
                if($this->checkParam($Fax)) { $UnitaOrganizzativaArray["##Fax##"] = $this->buildArray("Fax", $Fax); }

                return $UnitaOrganizzativaArray;
            } catch (\Exception $ex) {
                throw $ex;
            }
    }

    /**
     * 	<xs:complexType name="AOOType">
            <xs:sequence>
                    <xs:element name="Denominazione" type="DenominazioneType" minOccurs="0"/>
                    <xs:element name="CodiceAOO" type="CodiceAOOType"/>
                    <xs:element name="IndirizzoTelematico" type="IndirizzoTelematicoType" minOccurs="0"/>
                    <xs:element name="ForzaIndirizzoTelematico" type="xs:string" minOccurs="0"/>
                    <xs:element name="InvioPEC" type="InvioPECType" minOccurs="0"/>
            </xs:sequence>
    </xs:complexType>
     */
    public function buildAOO(
                                $CodiceAOO,
                                $Denominazione              = null,
                                $IndirizzoTelematico        = null,
                                $ForzaIndirizzoTelematico   = null,
                                $InvioPEC                   = null
                            ) {
        $AOOArray = array();
        $AOOArray["##CodiceAOO##"]					= $this->buildArray("CodiceAOO", $CodiceAOO);
        if($this->checkParam($Denominazione))				$AOOArray["##Denominazione##"]				= $this->buildArray("Denominazione", $Denominazione);
        if($this->checkParam($IndirizzoTelematico))			$AOOArray["##IndirizzoTelematico##"]		= $IndirizzoTelematico;
        if($this->checkParam($ForzaIndirizzoTelematico))	$AOOArray["##ForzaIndirizzoTelematico##"]	= $this->buildArray("ForzaIndirizzoTelematico", $ForzaIndirizzoTelematico);
        if($this->checkParam($InvioPEC))					$AOOArray["##InvioPEC##"]					= $this->buildArray("InvioPEC", $InvioPEC);

        return $AOOArray;
    }

    /**
    <xs:complexType name="DocumentoType">
            <xs:sequence>
                    <xs:element name="Metadati" type="MetadatiType"/>
                    <xs:element name="Acl" type="MetadatiType"/>
            </xs:sequence>
            <xs:attribute name="uri" type="xs:string" use="required"/>
            <xs:attribute name="id" type="xs:integer" use="required"/>
    </xs:complexType>
     */
    public function buildDocumento(
                                                                    $id,
                                                                    $uri,
                                                                    $Metadati,
                                                                    $Acl
                                                                    ) {
            try {
                    $documentoArray = array();
                    $documentoArray["##id##"]		= $this->buildArray("id", $id);
                    $documentoArray["##uri##"]		= $this->buildArray("uri", $uri);
                    $documentoArray["##Metadati##"]	= $this->buildArray("Metadati", $Metadati);
                    $documentoArray["##Acl##"]		= $this->buildArray("Acl", $Acl);

                    return $documentoArray;

            } catch (\Exception $ex) {
                    throw $ex;
            }

    }

    protected function buildMultipleDocument($Document) {
            try {
                    $DocumentArray = array();
                    // id è chiave obbligatoria del documento e discrimina la presenta di un singolo documento invece di una lista di documenti passata nel parametro $Document
                    if ($this->checkParam($Document) && \array_key_exists("id", $Document)) {
                            $DocumentArray["##Documento##"] = $Document;
                    }
                    else {
                            foreach ($Document as $key => $value) {
                                    if (\is_numeric($key))
                                            $DocumentArray["##Documento_$key##"] = $Document;
                            }
                    }
                    return $DocumentArray;
            } catch (\Exception $ex) {
                    throw $ex;
            }
    }

    /**
                    <xs:element name="Allegati">
                            <xs:complexType>
                                    <xs:sequence>
                                            <xs:element name="Documento" type="DocumentoType" minOccurs="0" maxOccurs="unbounded"/>
                                    </xs:sequence>
                            </xs:complexType>
                    </xs:element>
     */
    public function buildAllegati( $Documento ) {
            try {
                    return $this->buildMultipleDocument($Documento);
            } catch (\Exception $ex) {
                    throw $ex;
            }

    }

    /**
                    <xs:element name="Annessi">
                            <xs:complexType>
                                    <xs:sequence>
                                            <xs:element name="Documento" type="DocumentoType" minOccurs="0" maxOccurs="unbounded"/>
                                    </xs:sequence>
                            </xs:complexType>
                    </xs:element>
     */
    public function buildAnnessi( $Documento ) {
            try {
                    return $this->buildMultipleDocument($Documento);
            } catch (\Exception $ex) {
                    throw $ex;
            }

    }


    /**
                    <xs:element name="Annotazioni">
                            <xs:complexType>
                                    <xs:sequence>
                                            <xs:element name="Documento" type="DocumentoType" minOccurs="0" maxOccurs="unbounded"/>
                                    </xs:sequence>
                            </xs:complexType>
                    </xs:element>
     */
    public function buildAnnotazioni( $Documento ) {
            try {
                    return $this->buildMultipleDocument($Documento);
            } catch (\Exception $ex) {
                    throw $ex;
            }

    }

    /**
    <xs:complexType name="DocumentiType">
            <xs:sequence>
                    <xs:element name="Documento" type="DocumentoType"/>
                    <xs:element name="Allegati">
                            <xs:complexType>
                                    <xs:sequence>
                                            <xs:element name="Documento" type="DocumentoType" minOccurs="0" maxOccurs="unbounded"/>
                                    </xs:sequence>
                            </xs:complexType>
                    </xs:element>
                    <xs:element name="Annessi">
                            <xs:complexType>
                                    <xs:sequence>
                                            <xs:element name="Documento" type="DocumentoType" minOccurs="0" maxOccurs="unbounded"/>
                                    </xs:sequence>
                            </xs:complexType>
                    </xs:element>
                    <xs:element name="Annotazioni">
                            <xs:complexType>
                                    <xs:sequence>
                                            <xs:element name="Documento" type="DocumentoType" minOccurs="0" maxOccurs="unbounded"/>
                                    </xs:sequence>
                            </xs:complexType>
                    </xs:element>
            </xs:sequence>
    </xs:complexType>
     */
    public function buildDocumenti(
                                                                    $Documento,
                                                                    $Allegati,
                                                                    $Annessi,
                                                                    $Annotazioni
                                                                    ) {
                    try {
                    $DocumentiArray = array();
                    $DocumentiArray["##Documento##"]	= $Documento;
                    $DocumentiArray["##Allegati##"]		= $Allegati;
                    $DocumentiArray["##Annessi##"]		= $Annessi;
                    $DocumentiArray["##Annotazioni##"]	= $Annotazioni;
                    return $DocumentiArray;

            } catch (\Exception $ex) {
                    throw $ex;
            }

    }

    /**
     * 	<xs:complexType name="FascicoloType">
            <xs:sequence>
                    <xs:element name="CodiceAmministrazione" type="xs:string"/>
                    <xs:element name="CodiceAOO" type="xs:string"/>
                    <xs:element name="Classifica" type="xs:string"/>
                    <xs:element name="Anno" type="xs:int"/>
                    <xs:element name="Progressivo" type="xs:string"/>
            </xs:sequence>
    </xs:complexType>
     */
    public function buildFascicolo(
                                                                    $CodiceAmministrazione,
                                                                    $CodiceAOO,
                                                                    $Classifica,
                                                                    $Anno,
                                                                    $Progressivo
                                                                    ) {
            try {

                    $FascicoloArray = array();
                    $FascicoloArray["##CodiceAmministrazione##"]	= $this->buildArray("CodiceAmministrazione", $CodiceAmministrazione);
                    $FascicoloArray["##CodiceAOO##"]				= $this->buildArray("CodiceAOO", $CodiceAOO);
                    $FascicoloArray["##Classifica##"]				= $this->buildArray("Classifica", $Classifica);
                    $FascicoloArray["##Anno##"]						= $this->buildArray("Anno", $Anno);
                    $FascicoloArray["##Progressivo##"]				= $this->buildArray("Progressivo", $Progressivo);

                    return $FascicoloArray;

            } catch (\Exception $ex) {
                    throw $ex;
            }

    }

    /**
     * 		<xs:element name="FascicoloPrimario" type="FascicoloType" minOccurs="0"/>

     */
    public function buildFascicoloPrimario($Fascicolo) {
            try {
                    $FascicoloPrimarioArray = array();
                    if($this->checkParam($Fascicolo))	$FascicoloPrimarioArray["##Fascicolo##"]	= $Fascicolo;
                    return $FascicoloPrimarioArray;
            } catch (\Exception $ex) {
                    throw $ex;
            }

    }

    /**
    <xs:complexType name="FascicoliSecondariType">
            <xs:sequence>
                    <xs:element name="FascicoloSecondario" type="FascicoloType" minOccurs="0" maxOccurs="unbounded"/>
            </xs:sequence>
    </xs:complexType>
     */
    public function buildFascicoliSecondari($Fascicolo) {
            try {
                    $FascicoliSecondariArray = array();
                    if($this->checkParam($Fascicolo))	$FascicoliSecondariArray["##Fascicolo##"]	= $Fascicolo;
                    return $FascicoliSecondariArray;
            } catch (\Exception $ex) {
                    throw $ex;
            }

    }

    /**
    <xs:complexType name="FirmatarioType">
            <xs:sequence>
                    <xs:element name="Persona" type="PersonaType"/>
            </xs:sequence>
    </xs:complexType>
     */
    public function buildFirmatario($Persona) {
            try {
                    $FirmatarioArray = array();
                    $FirmatarioArray["##Persona##"] = $Persona;
                    return $FirmatarioArray;

            } catch (\Exception $ex) {
                    throw $ex;
            }

    } 

    /**
     * 	<xs:complexType name="IntestazioneType">
            <xs:sequence>
                    <xs:element name="Oggetto" type="xs:string"/>
                    <xs:element name="Flusso" type="FlussoType"/>
                    <xs:element name="Mittenti" type="MittentiType" minOccurs="0"/>
                    <xs:element name="Destinatari" type="DestinatariType" minOccurs="0"/>
                    <xs:element name="Classifica" type="ClassificaType" minOccurs="0"/>
                    <xs:element name="FascicoloPrimario" type="FascicoloType" minOccurs="0"/>
                    <xs:element name="FascicoliSecondari" type="FascicoliSecondariType" minOccurs="0"/>
                    <xs:element name="Smistamento" type="SmistamentoType" minOccurs="0"/>
            </xs:sequence>
    </xs:complexType>
     */
    public function buildIntestazione(
                                                                            $Oggetto,
                                                                            $Flusso,
                                                                            $Mittenti			= null,
                                                                            $Destinatari		= null,
                                                                            $Classifica			= null,
                                                                            $FascicoloPrimario	= null,
                                                                            $FascicoliSecondari = null,
                                                                            $Smistamento		= null
                                                                            ) {
            try {
                                                                                                            $IntestazioneArray = array();
                                                                                                            $IntestazioneArray['##Oggetto##']			= $this->buildArray("Oggetto", $Oggetto);
                                                                                                            $IntestazioneArray['##Flusso##']			= $Flusso;
                    if($this->checkParam($Mittenti))			$IntestazioneArray["##Mittenti##"]			= $Mittenti;
                    if($this->checkParam($Destinatari))			$IntestazioneArray["##Destinatari##"]			= $Destinatari;
                    if($this->checkParam($Classifica))			$IntestazioneArray["##Classifica##"]			= $this->buildArray("Classifica", $Classifica);
                    if($this->checkParam($FascicoloPrimario))	$IntestazioneArray["##FascicoloPrimario##"]	= $FascicoloPrimario;
                    if($this->checkParam($FascicoliSecondari))	$IntestazioneArray["##FascicoliSecondari##"]	= $FascicoliSecondari;
                    if($this->checkParam($Smistamento))			$IntestazioneArray["##Smistamento##"]			= $Smistamento;

                    return $IntestazioneArray;


            } catch (\Exception $ex) {
                    throw $ex;
            }

    }
    /**
     * 
     * @param array $Destinatario_i - un singolo destinatario oppure 1 array di destinatari
     * @throws \Exception
     */
    public function buildDestinatari($Destinatario_i) {
            try {
                    $DestinatariArray = array();
                    if (\array_key_exists("##MittDest##", $Destinatario_i)) {
                            $DestinatariArray["##Destinatario##"] = $Destinatario_i;
                    } else {
                            foreach ($Destinatario_i as $key => $value) {
                                    if (\is_numeric($key)) {
                                            $DestinatariArray["##Destinatario_$key##"] = $value;
                                    }
                            }
                    }
                    return $DestinatariArray;

            } catch (\Exception $ex) {
                    throw $ex;
            }
    }

    public function buildDestinatario($MittDest) {
            $DestinatarioArray = array("##MittDest##" => $MittDest);
            return $DestinatarioArray;
    }

    /**
     * 
     * @param array $Mittente_i - un singolo mittente oppure 1 array di mittenti
     * @throws \Exception
     */
    public function buildMittenti($Mittente_i) {
            try {
                    $MittentiArray = array();
                    if (\array_key_exists("##MittDest##", $Mittente_i)) {
                            $MittentiArray["##Mittente##"] = $Mittente_i;
                    } else {
                            foreach ($Mittente_i as $key => $value) {
                                    if (\is_numeric($key)) {
                                            $MittentiArray["##Mittente_$key##"] = $value;
                                    }
                            }
                    }
                    return $MittentiArray;
            } catch (\Exception $ex) {
                    throw $ex;
            }
    }

    public function buildMittente($MittDest) {
            $MittenteArray = array("##MittDest##" => $MittDest);
            return $MittenteArray;
    }

    /*
     * 	<xs:complexType name="FlussoType">
            <xs:sequence>
                    <xs:element name="TipoRichiesta" type="xs:string" minOccurs="0"/>
                    <xs:element name="Firma" type="xs:string"/>
                    <xs:element name="ForzaRegistrazione" type="xs:int"/>
                    <xs:element name="Firmatario" type="FirmatarioType" minOccurs="0"/>
                    <xs:element name="ProtocolloMittente" type="ProtocolloType" minOccurs="0"/>
            </xs:sequence>
    </xs:complexType>
     */
    public function buildFlusso(
                                                            $Firma,
                                                            $ForzaRegistrazione,
                                                            $TipoRichiesta=null,
                                                            $Firmatario = null,
                                                            $ProtocolloMittente = null
                                                            ) {
            try {
                    $FlussoArray = array();
                    $FlussoArray['##Firma##'] = $this->buildArray("Firma", $Firma);
                    $FlussoArray['##ForzaRegistrazione'] = $this->buildArray("ForzaRegistrazione", $ForzaRegistrazione);
                    if($this->checkParam($TipoRichiesta)) $FlussoArray["##TipoRichiesta##"] = $this->buildArray ("TipoRichiesta", $TipoRichiesta);
                    if($this->checkParam($Firmatario)) $FlussoArray["##Firmatario##"] = $Firmatario;
                    if($this->checkParam($ProtocolloMittente)) $FlussoArray["##ProtocolloMittente##"] = $ProtocolloMittente;

                    return $FlussoArray;

            } catch (\Exception $ex) {
                    throw $ex;
            }
    }

    /**
     * 	<xs:complexType name="MittDestType">
            <xs:sequence>
                    <xs:choice>
                            <xs:sequence>
                                    <xs:element name="Amministrazione" type="AmministrazioneType"/>
                                    <xs:element name="AOO" type="AOOType"/>
                                    <xs:element name="RiferimentiProtocollo" type="ProtocolloType" minOccurs="0"/>
                            </xs:sequence>
                            <xs:sequence>
                                    <xs:element name="Denominazione" type="DenominazioneType" minOccurs="0"/>
                                    <xs:element name="Persona" type="PersonaType" maxOccurs="unbounded"/>
                            </xs:sequence>
                            <xs:element name="PersonaGiuridica" type="PersonaGiuridicaType" minOccurs="0"/>
                    </xs:choice>
                    <xs:element name="IndirizzoTelematico" type="IndirizzoTelematicoType" minOccurs="0"/>
                    <xs:element name="Telefono" type="TelefonoType" minOccurs="0" maxOccurs="unbounded"/>
                    <xs:element name="Fax" type="FaxType" minOccurs="0" maxOccurs="unbounded"/>
                    <xs:element name="IndirizzoPostale" type="IndirizzoPostaleType" minOccurs="0"/>
            </xs:sequence>
    </xs:complexType>
     * 
     ##MittDestAmministrazione##
    ##MittDestPersona##
    ##PersonaGiuridica##
    ##IndirizzoTelematico##
    ##Telefono##
    ##Fax##
    ##IndirizzoPostale##
     */
    public function buildMittDest(
                                                                    $MittDestAmministrazione = null,
                                                                    $MittDestPersona = null,
                                                                    $PersonaGiuridica = null,
                                                                    $IndirizzoTelematico = null,
                                                                    $Telefono = null,
                                                                    $Fax = null,
                                                                    $IndirizzoPostale = null
                                                            ) {
            try {
                    $MittDestArray = array();
                    if($this->checkParam($MittDestAmministrazione))  {
                            $MittDestArray["##MittDestAmministrazione##"] = $MittDestAmministrazione;
                    } else {
                            if($this->checkParam($MittDestPersona)) {
                                    $MittDestArray["##MittDestPersona##"] = $MittDestPersona;
                            } else {
                                    if($this->checkParam($PersonaGiuridica)) $MittDestArray["##PersonaGiuridica##"] = $PersonaGiuridica;
                            }
                    }
                    if($this->checkParam($IndirizzoTelematico)) $MittDestArray["##IndirizzoTelematico##"] = $IndirizzoTelematico;
                    if($this->checkParam($Telefono)) $MittDestArray["##Telefono##"] = $Telefono;
                    if($this->checkParam($Fax)) $MittDestArray["##Fax##"] = $this->buildArray("Fax", $Fax);
                    if($this->checkParam($IndirizzoPostale)) $MittDestArray["##IndirizzoPostale##"] = $IndirizzoPostale;

                    return $MittDestArray;
            } catch (\Exception $ex) {
                    throw $ex;
            }

    }

    /**
                                    <xs:sequence>
                                    <xs:element name="Amministrazione" type="AmministrazioneType"/>
                                    <xs:element name="AOO" type="AOOType"/>
                                    <xs:element name="RiferimentiProtocollo" type="ProtocolloType" minOccurs="0"/>
                            </xs:sequence>
     */
    public function buildMittDestAmministrazione(
                                                                                            $Amministrazione,
                                                                                            $AOO,
                                                                                            $RiferimentiProtocollo= null
                                                                                            ) {
            try {
                    $MittDestAmministrazioneArray = array();
                    $MittDestAmministrazioneArray['##Amministrazione##'] = $Amministrazione;
                    $MittDestAmministrazioneArray['##AOO##'] = $AOO;
                    if($this->checkParam($RiferimentiProtocollo)) $MittDestAmministrazioneArray["##RiferimentiProtocollo##"] = $RiferimentiProtocollo;

                    return $MittDestAmministrazioneArray;

            } catch (\Exception $ex) {
                    throw $ex;
            }
    }


    /**
     * 	<xs:complexType name="PersonaGiuridicaType">
            <xs:sequence>
                    <xs:element name="Denominazione" type="DenominazioneType"/>
                    <xs:element name="IndirizzoPostale" type="IndirizzoPostaleType" minOccurs="0"/>
                    <xs:element name="IndirizzoTelematico" type="IndirizzoTelematicoType" minOccurs="0"/>
                    <xs:element name="ForzaIndirizzoTelematico" type="xs:string" minOccurs="0"/>
                    <xs:element name="InvioPEC" type="InvioPECType" minOccurs="0"/>
                    <xs:element name="Metadati" type="MetadatiType" minOccurs="0"/>
            </xs:sequence>
            <xs:attribute name="tipo" type="xs:string" use="required"/>
            <xs:attribute name="id" type="xs:string" use="required"/>
    </xs:complexType>
     * 
    <PersonaGiuridica ##tipo##>
    ##Denominazione##
    ##IndirizzoPostale##
    ##IndirizzoTelematico##
    ##ForzaIndirizzoTelematico##
    ##InvioPEC##
    ##Metadati##
    </PersonaGiuridica>
     */
    public function buildPersonaGiuridica(
                                                                                    $tipo,
                                                                                    $id,
                                                                                    $Denominazione,
                                                                                    $IndirizzoPostale = null,
                                                                                    $IndirizzoTelematico = null,
                                                                                    $ForzaIndirizzoTelematico = null,
                                                                                    $InvioPEC = null,
                                                                                    $Metadati = null
                                                                                    ) {
            try {
                    $PersonaGiuridicaArray = array();

                    $PersonaGiuridicaArray['##tipo##'] = $this->buildArray("tipo", $tipo);
                    $PersonaGiuridicaArray['##id##'] = $this->buildArray("id", $id);
                    $PersonaGiuridicaArray['##Denominazione##'] = $this->buildArray("Denominazione", $Denominazione);
                    if($this->checkParam($IndirizzoPostale)) $PersonaGiuridicaArray["IndirizzoPostale"] = $IndirizzoPostale;
                    if($this->checkParam($IndirizzoTelematico)) $PersonaGiuridicaArray["IndirizzoTelematico"] = $IndirizzoTelematico;
                    if($this->checkParam($ForzaIndirizzoTelematico)) $PersonaGiuridicaArray["##ForzaIndirizzoTelematico##"] = $this->buildArray("ForzaIndirizzoTelematico", $ForzaIndirizzoTelematico);
                    if($this->checkParam($InvioPEC)) $PersonaGiuridicaArray["##InvioPEC##"] = $this->buildArray("InvioPEC", $InvioPEC);
                    if($this->checkParam($Metadati)) $PersonaGiuridicaArray["##Metadati##"] = $this->buildArray("Metadati", $Metadati);

                    return $PersonaGiuridicaArray;

            } catch (\Exception $ex) {
                    throw $ex;
            }

    }

    /**
     * 	<xs:complexType name="ProtocolloType">
            <xs:sequence>
                    <xs:element name="CodiceAmministrazione" type="xs:string" minOccurs="0"/>
                    <xs:element name="CodiceAOO" type="xs:string" minOccurs="0"/>
                    <xs:element name="Classifica" type="xs:string"/>
                    <xs:element name="Data" type="xs:string" minOccurs="0"/>
                    <xs:element name="Fascicolo" type="xs:string"/>
                    <xs:element name="Numero" type="xs:string" minOccurs="0"/>
            </xs:sequence>
    </xs:complexType>
     */
    public function buildProtocollo(
                                                                                    $Classifica,
                                                                                    $Fascicolo,
                                                                                    $CodiceAmministrazione = null, 
                                                                                    $CodiceAOO = null,
                                                                                    $Data = null,
                                                                                    $Numero = null
                                                                                    ) {
            try {
            $ProtocolloArray = array();
            $ProtocolloArray['##Classifica##'] = $Classifica;
            $ProtocolloArray['##FascicoloSemplice##'] = $Fascicolo;
            if($this->checkParam($CodiceAmministrazione)) $ProtocolloArray['##CodiceAmministrazione##'] = $this->buildArray ('CodiceAmministrazione', $CodiceAmministrazione);
            if($this->checkParam($CodiceAOO)) $ProtocolloArray['##CodiceAOO##'] = $this->buildArray ('CodiceAOO', $CodiceAOO);
            if($this->checkParam($Data)) $ProtocolloArray['##Data##'] = $this->buildArray ('Data', $Data);
            if($this->checkParam($Numero)) $ProtocolloArray['##Numero##'] = $this->buildArray ('Numero', $Numero);

            return $ProtocolloArray;
            } catch (\Exception $ex) {
                    throw $ex;
            }

    }

    /**
     * <xs:element name="ProtocolloMittente" type="ProtocolloType" minOccurs="0"/>
     */
    public function buildProtocolloMittente($Protocollo) {
            try {
                    return array('##Protocollo##' => $Protocollo);

            } catch (\Exception $ex) {
                    throw $ex;
            }
    }

    /**
     * 
    * <xs:element name="RiferimentiProtocollo" type="ProtocolloType" minOccurs="0"/>
     */
    public function buildRiferimentiProtocollo($Protocollo) {
            try {
                    return array('##Protocollo##' => $Protocollo);

            } catch (\Exception $ex) {
                    throw $ex;
            }
    }

    /**
     * 	<xs:complexType name="SegnaturaType">
            <xs:sequence>
                    <xs:element name="Intestazione" type="IntestazioneType"/>
                    <xs:element name="Documenti" type="DocumentiType"/>
            </xs:sequence>
    </xs:complexType>
     */
    public function buildSegnatura($Intestazione, $Documenti) {
            try {
                    $a=1;
                    return array(
                                                    "__xmlnamespace__" => $this->getProtocollazioneNamespace(),
                                                    '##Intestazione##'	=> $Intestazione,
                                                    '##Documenti##'		=> $Documenti
                    );

            } catch (\Exception $ex) {
                    throw $ex;
            }

    }

    /**
     * <xs:complexType name="SmistamentoType">
            <xs:sequence>
                    <xs:element name="UnitaOrganizzativa" type="UnitaOrganizzativaType" minOccurs="0" maxOccurs="unbounded"/>
                    <xs:element name="Persona" type="PersonaType" minOccurs="0" maxOccurs="unbounded"/>
            </xs:sequence>
    </xs:complexType>
     */
    public function buildSmistamento(
                                                                     $UnitaOrganizzativa = null,
                                                                     $Persona = null
                                                                    ) {
            try {
                    $SmistamentoArray = array();
                    if($this->checkParam($UnitaOrganizzativa)) $SmistamentoArray['##UnitaOrganizzativa##'] = $UnitaOrganizzativa;
                    if($this->checkParam($Persona)) $SmistamentoArray['##Persona##'] = $Persona;

            } catch (\Exception $ex) {
                    throw $ex;
            }

    }

    /**
     * <xs:complexType name="TelefonoType" mixed="true">
            <xs:attribute name="note" type="xs:anySimpleType"/>
    </xs:complexType>
     */
    public function buildTelefono(
                                                                    $note,
                                                                    $Telefono
                                                            ) {
            try {
                    $TelefonoArray = array();
                    $TelefonoArray['##note##'] = $this->buildArray ("note", $note);
                    $TelefonoArray['##Telefono##'] = $this->buildArray ("Telefono", $Telefono);
            } catch (\Exception $ex) {
                    throw $ex;
            }

    }

    /**
     * 
    <xs:complexType name="ToponimoType" mixed="true">
            <xs:attribute name="dug" type="xs:anySimpleType"/>
    </xs:complexType>
     */	
    public function buildToponimo(
                                                                    $Toponimo,
                                                                    $dug
                                                             ) {
            try {
                    $ToponimoArray = array();
                    $ToponimoArray['##Toponimo##'] = $this->buildArray ("Toponimo", $Toponimo);
                    $ToponimoArray['##$dug##'] = $this->buildArray ("dug", $dug);			
            } catch (\Exception $ex) {
                    throw $ex;
            }
    }




    public function TrybuildXml() {


            $param = 
                                    array(
                                                    "__xmlnamespace__" => $this->getProtocollazioneNamespace(),
                                                    "##Intestazione##" => 
                                                            array(
                                                                            "##Oggetto##" => $this->buildArray("Oggetto", "Intestazione oggetto"),
                                                                            "##Flusso##" =>
                                                                                    array(
                                                                                            "##TipoRichiesta##" => $this->buildArray("TipoRichiesta", "E"),
                                                                                            "##Firma##" => $this->buildArray("Firma", "FE"),
                                                                                            "##ForzaRegistrazione##" => $this->buildArray("ForzaRegistrazione", "1"),

                                                                                    ),
                                                                            "##Mittenti##" =>
                                                                                    array(
                                                                                            "##Mittente##" =>
                                                                                                    array(
                                                                                                            "##MittDest##" =>
                                                                                                            array(
                                                                                                                    "##MittDestPersona##" =>
                                                                                                                    array(

                                                                                                                            "##Persona##" =>
                                                                                                                            array(
                                                                                                                                    "##id##" => $this->buildArray("id", "RSSMRC66M11Z999Z"),
                                                                                                                                    "##Nome##" => $this->buildArray("Nome", "Mario"),
                                                                                                                                    "##Cognome##" => $this->buildArray("Cognome", "Rossi"),
                                                                                                                                    "##IndirizzoTelematico##" =>
                                                                                                                                            array(
                                                                                                                                                    "##tipo##" => $this->buildArray("tipo", "smtp")
                                                                                                                                            )

                                                                                                                            )
                                                                                                                    )
                                                                                                            )
                                                                                                    ),
                                                                                            "##Mittente_1##" =>
                                                                                                    array(
                                                                                                            "##MittDest##" =>
                                                                                                            array(
                                                                                                                    "##MittDestPersona##" =>
                                                                                                                    array(

                                                                                                                            "##Persona##" =>
                                                                                                                            array(
                                                                                                                                    "##id##" => $this->buildArray("id", "RSSMRC66M11Z999Z"),
                                                                                                                                    "##Nome##" => $this->buildArray("Nome", "Marco"),
                                                                                                                                    "##Cognome##" => $this->buildArray("Cognome", "Rossi"),
                                                                                                                                    "##IndirizzoTelematico##" =>
                                                                                                                                            array(
                                                                                                                                                    "##tipo##" => $this->buildArray("tipo", "smtp")
                                                                                                                                            )

                                                                                                                            )
                                                                                                                    )
                                                                                                            )
                                                                                                    )
                                                                                    ),
                                                                            "##Destinatari##" =>
                                                                                    array(
                                                                                            "##Destinatario##" =>
                                                                                                    array(
                                                                                                            "##MittDest##" =>
                                                                                                            array(
                                                                                                                    "##MittDestAmministrazione##" =>
                                                                                                                    array(
                                                                                                                                    "##Amministrazione##" =>
                                                                                                                                    array(
                                                                                                                                            "##CodiceAmministrazione##" => $this->buildArray("CodiceAmministrazione", "EMR"),
                                                                                                                                            "##UnitaOrganizzativa##" => 
                                                                                                                                                    array(
                                                                                                                                                            "##Identificativo##" => $this->buildArray("Identificativo", "00000368")

                                                                                                                                                    ),

                                                                                                                                    ),
                                                                                                                                    "##AOO##" =>
                                                                                                                                            array(
                                                                                                                                                    "##CodiceAOO##" => $this->buildArray("CodiceAOO", "AOO_EMR")
                                                                                                                                            )
                                                                                                                            )
                                                                                                            )
                                                                                                    )
                                                                                    ),
                                                                            "##FascicoloPrimario##" =>
                                                                                    array(
                                                                                                    "##Fascicolo##" =>
                                                                                                    array(
                                                                                                            "##CodiceAmministrazione##" => $this->buildArray("CodiceAmministrazione", "EMR"),
                                                                                                            "##CodiceAOO##" => $this->buildArray("CodiceAOO", "AOO_EMR"),
                                                                                                            "##Classifica##" => $this->buildArray("Classifica", "430.204.10"),
                                                                                                            "##Anno##" => $this->buildArray("Anno", "2015"),
                                                                                                            "##Progressivo##" => $this->buildArray("Progressivo", "5")
                                                                                                    )
                                                                                            )

                                                                    )


            );

            $xml = $this->buildXml($param);
            $c=1;
            return array("xml"=>$xml, "param" => $param);
    }

    public function buildXmlForAzienda(
                                                                            $Oggetto, 
                                                                            $Mittente_PersonaGiuridica_id, 
                                                                            $Mittente_PersonaGiuridica_Denominazione,
                                                                            $CodiceAOO,
                                                                            $CodiceAmministrazione,
                                                                            $Identificativo,
                                                                            $Classifica,
                                                                            $Fascicolo_primario_Anno,
                                                                            $Fascicolo_primario_Progressivo
                                                                            ) {


            // -----  Dati di Flusso -----
        
            $Firma = "FE";
            $ForzaRegistrazione="1";
            $TipoRichiesta = "E";
            $Firmatario = array();

            $Flusso = $this->buildFlusso($Firma, $ForzaRegistrazione, $TipoRichiesta, $Firmatario);
            // ----------------------------

            // ----- Dati Mittenti -------

            $tipo = "Ente";
            $PersonaGiuridica = $this->buildPersonaGiuridica($tipo, $Mittente_PersonaGiuridica_id, $Mittente_PersonaGiuridica_Denominazione);
            $MittDestMittente = $this->buildMittDest(null, null, $PersonaGiuridica);
            $Mittente_i = $this->buildMittente($MittDestMittente);
            $Mittenti = $this->buildMittenti($Mittente_i);
            // ----------------------------

            // ----- Dati Destinazione -----


                    $Fascicolo = $this->buildFascicolo(
                                                                                                                    $CodiceAmministrazione, 
                                                                                                                    $CodiceAOO, 
                                                                                                                    $Classifica, 
                                                                                                                    $Fascicolo_primario_Anno, 
                                                                                                                    $Fascicolo_primario_Progressivo
                                                                                                                    );
                    $FascicoloPrimario = $this->buildFascicoloPrimario($Fascicolo);


                    $UnitaOrganizzativa = $this->buildUnitaOrganizzativa($Identificativo);
                    $Amministrazione = $this->buildAmministrazione(
                                                                                                                            $CodiceAmministrazione, 
                                                                                                                            null, 
                                                                                                                            null, 
                                                                                                                            null, 
                                                                                                                            $UnitaOrganizzativa
                                                                                                                            );
                    $AOO = $this->buildAOO($CodiceAOO);
                    $MittDestAmministrazione = $this->buildMittDestAmministrazione($Amministrazione, $AOO);
                    $MittDestDestinatario = $this->buildMittDest($MittDestAmministrazione);
                    $Destinatario = $this->buildDestinatario($MittDestDestinatario);
                    $Destinatari = $this->buildDestinatari($Destinatario);




            // ----------------------------

            $FascicoliSecondari = null;
            $Smistamento = null;
            //------ Dati Intestazione -----
            $Intestazione = $this->buildIntestazione(
                                                                                            $Oggetto, 
                                                                                            $Flusso, 
                                                                                            $Mittenti, 
                                                                                            $Destinatari, 
                                                                                            null, 
                                                                                            $FascicoloPrimario, 
                                                                                            $FascicoliSecondari, 
                                                                                            $Smistamento);
            // ----------------------------

            $param = $this->buildSegnatura($Intestazione, null);
            return $this->buildXml($param);
    }





	
	
}
