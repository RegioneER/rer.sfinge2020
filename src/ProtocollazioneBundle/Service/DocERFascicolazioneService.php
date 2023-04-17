<?php

namespace ProtocollazioneBundle\Service;

use ProtocollazioneBundle\Service\DocERAuthenticationService;
use ProtocollazioneBundle\Service\DocERBaseService;

/**
 * Description of DocERFascicolazioneService
 *
 * @author gaetanoborgosano
 * @author refactoring by Davide Cannistraro & Gaetano Borgosano
 */
class DocERFascicolazioneService extends DocERBaseService {
	
    private $fascicolazioneservice_EP = "/WSFascicolazione/services/WSFascicolazione";
    private $fascicolazioneservice_URL = "/WSFascicolazione/services/WSFascicolazione?wsdl";
    // xmlns:fas="http://fascicolazione.docer.kdm.it"
    private static $fascicolazione_namespaces = array("fas" => "http://fascicolazione.docer.kdm.it");
    private static $fascicolazione_method_namespace_prefix = "fas";
    static function getFascicolazione_namespaces() { return self::$fascicolazione_namespaces; }
    static function getFascicolazione_method_namespace_prefix() { return self::$fascicolazione_method_namespace_prefix; }
    static function setFascicolazione_namespaces($fascicolazione_namespaces) { self::$fascicolazione_namespaces = $fascicolazione_namespaces; }
    static function setFascicolazione_method_namespace_prefix($fascicolazione_method_namespace_prefix) { self::$fascicolazione_method_namespace_prefix = $fascicolazione_method_namespace_prefix; }

    public function __construct($token, $serviceContainer) {
        parent::__construct($serviceContainer);
        $this->setToken($token);
        $this->setNamespaces(self::getFascicolazione_namespaces());
        $this->setMethodNamespacePrefix(self::getFascicolazione_method_namespace_prefix());
        $this->initSoapClient($this->fascicolazioneservice_URL, $this->fascicolazioneservice_EP);
    }
	
    /**
     * fascicolaById
     * @param integer $documentoId
     * @param string $datiProtocollo - xml contenente i dati di protocollo
     * @throws \Exception
     */
    public function fascicolaById($documentoId, $datiProtocollo) {
        $this->setApp_function("fascicolaById");
        $paramsArray = array( "documentoId"     => $documentoId,
                                "datiProtocollo"  => "![CDATA[$datiProtocollo]]"
                            );
        $mode = "EP";
        $timeout = $this->getTimeout();
        $MTOMFilterResponseMode = 'response';
        $resp = $this->getSoapClient()->__curl_call("fascicolaById", $paramsArray, $mode, $timeout, $MTOMFilterResponseMode, false);
        $fault = $this->checkSoapFaultOnSoapRespString($resp);
        if(!\is_null($fault)) {
            throw new \Exception($fault);
        }				

        $xml = new \SimpleXMLElement($resp);

        $xml = dom_import_simplexml($xml);

        $rel = $xml->nodeValue;
        if ($rel) {
            return $rel;
        } else {
            throw new \Exception("Errore: impossibile creare il fascicolo tramite IdDocument ");
        }
    }
	
    /**
     * -- NOTA -- usare la createFascicolo di DocERDocumentService
     *			
     * <fas:metadati>
            <xsd:key>COD_ENTE</xsd:key>
            <xsd:value>EMR</xsd:value>
        </fas:metadati>
        <fas:metadati>
            <xsd:key>COD_AOO</xsd:key>
            <xsd:value>AOO_EMR</xsd:value>
        </fas:metadati>
        <fas:metadati>
             <xsd:key>CLASSIFICA</xsd:key>
             <xsd:value>430.204.10</xsd:value>
        </fas:metadati>
        <fas:metadati>
            <xsd:key>DES_FASCICOLO</xsd:key>
            <xsd:value>Prova fascicolazione test</xsd:value>
        </fas:metadati>
        <fas:metadati>
            <xsd:key>ANNO_FASCICOLO</xsd:key>
            <xsd:value>2015</xsd:value>
        </fas:metadati>
        <fas:metadati>
            <xsd:key>UO_IN_CARICO</xsd:key>
            <xsd:value>00000368</xsd:value>
        </fas:metadati>
        <fas:metadati>
            <xsd:key>PARENT_PROGR_FASCICOLO</xsd:key>
            <xsd:value>5</xsd:value>
        </fas:metadati>
    </fas:creaFascicolo>
     * @return int|bool
     * @throws \Exception
     */
    public function creaFascicolo( $cod_ente, 
                                   $cod_aoo, 
                                   $classifica,
                                   $des_fascicolo=null,
                                   $anno_fascicolo,
                                   $uo_in_carico,
                                   $parent_progr_fascicolo=null,
                                   $metadati_extra = array()
                                 ) {
        $this->setApp_function("creaFascicolo");

        try {
            $array_cod_ente                 = array("metadati" => array("key" => "COD_ENTE", "value" => $cod_ente));
            $array_cod_aoo                  = array("metadati" => array("key" => "COD_AOO", "value" => $cod_aoo));
            $array_classifica               = array("metadati" => array("key" => "CLASSIFICA", "value" => $classifica));
            $array_des_fascicolo            = array("metadati" => array("key" => "DES_FASCICOLO", "value" => "<![CDATA[".$des_fascicolo."]]>"));
            $array_anno_fascicolo           = array("metadati" => array("key" => "ANNO_FASCICOLO", "value" => $anno_fascicolo));
            $array_uo_in_carico             = array("metadati" => array("key" => "UO_IN_CARICO", "value" => $uo_in_carico));
            $array_parent_progr_fascicolo   = array("metadati" => array("key" => "PARENT_PROGR_FASCICOLO", "value" => $parent_progr_fascicolo));

            $metadati = array();
            $metadati = $this->insertCheckParamArray($cod_ente, $array_cod_ente, $metadati);
            $metadati = $this->insertCheckParamArray($cod_aoo, $array_cod_aoo, $metadati);
            $metadati = $this->insertCheckParamArray($classifica, $array_classifica, $metadati);
            $metadati = $this->insertCheckParamArray($des_fascicolo, $array_des_fascicolo, $metadati);
            $metadati = $this->insertCheckParamArray($anno_fascicolo, $array_anno_fascicolo, $metadati);
            $metadati = $this->insertCheckParamArray($uo_in_carico, $array_uo_in_carico, $metadati);
            $metadati = $this->insertCheckParamArray($parent_progr_fascicolo, $array_parent_progr_fascicolo, $metadati);

            if(\is_array($metadati_extra) && count($metadati_extra) >0) {
                $metadati = array_merge($metadati, $metadati_extra);            
            }
            $paramsArray = array( $metadati );
            $mode = "EP";
            $timeout = $this->getTimeout();
            $MTOMFilterResponseMode = 'response';
            $resp = $this->getSoapClient()->__curl_call("creaFascicolo", $paramsArray, $mode, $timeout, $MTOMFilterResponseMode, false);
            $fault = $this->checkSoapFaultOnSoapRespString($resp);
            
            if(!\is_null($fault)) {
                throw new \Exception($fault);
            }				
            
            $resp = $this->removeSoapEnvHeader($resp);
            $xml = str_replace('xmlns:ns="http://webservices.docer.kdm.it"', '', $resp);
            $arrayXmlns = $this->getFascicolazione_namespaces();
            $namespacePrefix = $this->getFascicolazione_method_namespace_prefix();
            foreach($arrayXmlns as $key=>$value) {
                $xmlns_Prefix = 'xmlns:'.$key.'="'.$value.'"';
                $xmlns = 'xmlns:ns="'.$value.'"';
                $xml = str_replace($xmlns_Prefix, "", $xml);
                $xml = str_replace($xmlns, "", $xml);
            }

            $xml = str_replace('ns:', '', $xml);
            $xml = str_replace(' ', '', $xml);
            $xml = str_replace("<?xmlversion='1.0'encoding='UTF-8'?>" ,'', $xml);
            $xml = str_replace("&lt;", "<", $xml);
            $xml = str_replace("&gt;", ">", $xml);

            $xml = new \SimpleXMLElement($xml);

            // creaFascicoloResponse

            $creaFascicoloResponse = $xml->Body->creaFascicoloResponse;
            $esito_fascicolo = $creaFascicoloResponse->return->esito->esito_fascicolo; 
            $datiFascicolo = array();

            $key_array = array(
                    'ANNO_FASCICOLO',
                    'CLASSIFICA',
                    'COD_AOO',
                    'COD_ENTE',
                    'DATA_APERTURA',
                    'DES_FASCICOLO',
                    'ENABLED',
                    'NUM_FASCICOLO',
                    'PARENT_PROGR_FASCICOLO',
                    'PROGR_FASCICOLO',
                    'UO_IN_CARICO'
            );
            foreach($key_array as $key) {
                $value = (string) $esito_fascicolo->$key;
                $datiFascicolo[$key] = $value;
            }

            return count($datiFascicolo) > 0 ? $datiFascicolo : false;

        } catch (\Exception $ex) {
            throw $ex;
        }

    }
	
}
