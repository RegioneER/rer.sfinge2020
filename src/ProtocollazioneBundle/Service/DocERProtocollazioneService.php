<?php

namespace ProtocollazioneBundle\Service;

use ProtocollazioneBundle\Service\DocERXmlProtocollazioneService;
use ProtocollazioneBundle\Service\DocERBaseService;


/**
 * Description of DocERProtocollazioneService
 *
 * @author gaetanoborgosano
 * @author refactoring by Davide Cannistraro
 */
class DocERProtocollazioneService extends DocERBaseService {
	
    private $protocollazioneservice_EP = "/WSProtocollazione/services/WSProtocollazione";
    private $protocollazioneservice_URL = "/WSProtocollazione/services/WSProtocollazione?wsdl";
    private static $protocollazioneservice_namespaces = array("prot" => "http://protocollazione.docer.kdm.it");
    private static $protocollazioneservice_method_namespace_prefix = "prot";
    static function getProtocollazioneservice_namespaces() { return self::$protocollazioneservice_namespaces; }
    static function getProtocollazioneservice_method_namespace_prefix() { return self::$protocollazioneservice_method_namespace_prefix; }
    static function setProtocollazioneservice_namespaces($protocollazioneservice_namespaces) { self::$protocollazioneservice_namespaces = $protocollazioneservice_namespaces; }
    static function setProtocollazioneservice_method_namespace_prefix($protocollazioneservice_method_namespace_prefix) { self::$protocollazioneservice_method_namespace_prefix = $protocollazioneservice_method_namespace_prefix; }

    /**
     * @var DocERXmlProtocollazioneService
     */
    protected $BuilderXmlProtocollo;
    function getBuilderXmlProtocollo() { return $this->BuilderXmlProtocollo; }
    function setBuilderXmlProtocollo(DocERXmlProtocollazioneService $BuilderXmlProtocollo) { $this->BuilderXmlProtocollo = $BuilderXmlProtocollo; }


    public function __construct($token, $serviceContainer) {
        parent::__construct($serviceContainer);
        $this->setToken($token);
        $this->setNamespaces(self::getProtocollazioneservice_namespaces());
        $this->setMethodNamespacePrefix(self::getProtocollazioneservice_method_namespace_prefix());
        $this->initSoapClient($this->protocollazioneservice_URL, $this->protocollazioneservice_EP);
        $this->BuilderXmlProtocollo = $serviceContainer->get("docerxmlprotocollazione");
    }
	
	
    /**
     * Questo metodo permette la protocollazione ed eventualmente la fascicolazione contestuale di una unità documentaria già presente all’interno del sistema documentale Doc/er.
     * @param integer $documentoId
     * @param string $datiProtocollo - xml con dati di protocollazione
     * @return int|bool
     * @throws \Exception
     */	
    public function protocollaById($documentoId, $datiProtocollo, $registro_id = null) {
        $this->setApp_function("protocollaById");
        try {
            $paramsArray = array( "documentoId"     => $documentoId,
                                  "datiProtocollo"  => "<![CDATA[$datiProtocollo]]>"
                                );
            $mode = "EP";
            $timeout = $this->getTimeout();
            $MTOMFilterResponseMode = 'response';

            $resp = $this->getSoapClient()->__curl_call("protocollaById", $paramsArray, $mode, $timeout, $MTOMFilterResponseMode, false);
            $fault = $this->checkSoapFaultOnSoapRespString($resp);
            if(!\is_null($fault)) {
                throw new \Exception($fault);
            }				

            $dati_protocollo_array = array();
            $xml = str_replace("&lt;", "<", $resp);
            $xml = str_replace(' standalone="yes"', '', $xml);
            $xml = str_replace(' xmlns:ns="http://webservices.docer.kdm.it"', '', $xml);
            $xml = str_replace(' xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"', '', $xml);
            $xml = str_replace('soapenv:', '', $xml);

            $arrayXmlns = $this->getProtocollazioneservice_namespaces();
            foreach($arrayXmlns as $key=>$value) {
                $xmlns_Prefix = ' xmlns:'.$key.'="'.$value.'"';
                $xmlns = ' xmlns:ns="'.$value.'"';
                $xml = str_replace($xmlns_Prefix, "", $xml);
                $xml = str_replace($xmlns, "", $xml);
            }

            $xml = str_replace('ns:', '', $xml);
//			$xml = str_replace(' ', '', $xml);
            $xml = str_replace("<?xml version='1.0' encoding='UTF-8'?>" ,'', $xml);
            $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
            $xml = new \SimpleXMLElement($xml);
            $return = $xml->Body->protocollaByIdResponse->return;
            $esito = $return->esito;
            $codice = (string) $esito->codice;
            $dati_protocollo_array['CODICE'] = $codice;
            $dati_protocollo = $esito->dati_protocollo;
            $key_array = array("ANNO_PG", "DATA_PG", "NUM_PG", "OGGETTO_PG", "REGISTRO_PG");

            foreach($key_array as $key) {
                    $dati_protocollo_array[$key] = (string) $dati_protocollo->$key;
            }
            return count($dati_protocollo_array) > 0 ? $dati_protocollo_array : false;

        } catch (\Exception $ex) {
            throw $ex;
        }
    }    
}				
