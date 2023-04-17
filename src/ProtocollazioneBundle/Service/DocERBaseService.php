<?php

namespace ProtocollazioneBundle\Service;

use ProtocollazioneBundle\Service\SoapService;

/**
 * Description of DocERBaseService
 *
 * @author Davide Cannistraro
 */
class DocERBaseService {

    protected $serviceContainer;
    protected $SoapService;
    protected $app_function;
    protected $sottotitolo;
    protected $msg_array = array();
    
    /**
     * 
     * @return SoapService
     */
    function getSoapService() { return $this->SoapService; }

    function getApp_function() {  return $this->app_function;  }
    function setApp_function($app_function) {  $this->app_function = get_class($this) . "->" . $app_function; }
    
    function getSottotitolo() { return $this->sottotitolo; }
    function setSottotitolo($sottotitolo) { $this->sottotitolo = $sottotitolo; }

    function getMsg_array() { return $this->msg_array; }    
    function setMsg_array($msg_array) { $this->msg_array[] = $msg_array; }

    
    public function __construct($serviceContainer=null) {
        if(!\is_null($serviceContainer)) { $this->serviceContainer = $serviceContainer; }
	$DocERLogService = $serviceContainer->get("docerlogger");
        $this->SoapService = new SoapService($DocERLogService);
		$this->SoapService->setApp_function($this->getApp_function());
		
    }
                       
	

    protected static $token;
    protected static function getToken() { return self::$token; }
    protected static function setToken($token) { self::$token = $token; }
    public function setDepthToken($token) {
        $this->setToken($token);
        if(!\is_null($this->getSoapClient())) {
            $this->getSoapClient()->setToken($token);
        }
    }

    protected $defaultNamespaceParams = array( "key" => "xsd", "value" => "xsd");
    function getDefaultNamespaceParams() { return $this->defaultNamespaceParams; }
    function setDefaultNamespaceParams($defaultNamespaceParams) { $this->defaultNamespaceParams = $defaultNamespaceParams; }

    protected $namespaceParams=array();
    function getNamespaceParams() {	return $this->namespaceParams; }
    function setNamespaceParams($namespaceParams) { $this->namespaceParams = $namespaceParams; }

    protected $namespaces=array();
    function getNamespaces() { return $this->namespaces; }
    function setNamespaces($namespaces) { $this->namespaces = $namespaces; }
    function addNamespace($namespaceUrl, $namespacePrefix) {
        $namespaces = $this->getNamespaces();
        $namespaces[$namespacePrefix] = $namespaceUrl;
        $this->setNamespaces($namespaces);

    }

    protected $methodNamespacePrefix;
    function getMethodNamespacePrefix() { return $this->methodNamespacePrefix; }
    function setMethodNamespacePrefix($methodNamespacePrefix) { $this->methodNamespacePrefix = $methodNamespacePrefix; }

    protected $timeout = 120;
    function getTimeout() { return $this->timeout; }
    function setTimeout($timeout) {	$this->timeout = $timeout; }


    function getModUrl($url) {
        $base_url = $this->serviceContainer->getParameter("DOCER_BASE_URL");
        return $base_url.$url;
    }
	
    public function getSoapClient() {
        $SoapService = $this->getSoapService();
        return $SoapService->getSoapClient();
    }

    protected function setSoapClient($SoapClient) {
            $this->getSoapService()->setSoapClient($SoapClient);
    }

    function initSoapClient($soap_wsdl_url = null, $soap_ep = null, $soap_context = null) {
        $this->SoapService->initSoapClient($this->getToken(), $this->getModUrl($soap_wsdl_url), $this->getModUrl($soap_ep), $soap_context);
        $this->getSoapClient()->setNamespaces($this->getNamespaces());
        $this->getSoapClient()->setMethodNamespacePrefix($this->getMethodNamespacePrefix());
        $this->getSoapClient()->setDefaultNamespaceParams($this->getDefaultNamespaceParams());

    }
		
    protected function insertCheckParamArray($param, $array_param, $paramsArray) {
        if(!\is_null($param) && strval($param)!="") { $paramsArray[] = $array_param; }
        return $paramsArray;
    }
	
    public function removeSoapEnvHeader($resp) {
        try {
            $xml = str_replace('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">', "<Envelope>", $resp);
            $xml = str_replace('soapenv:', '', $xml);
            $xml = str_replace('xmlns:ax21="http://exceptions.sdk.docer.kdm.it/xsd"', '', $xml);
            $xml = str_replace('xmlns:ax23="http://classes.sdk.docer.kdm.it/xsd"', '', $xml);
            $xml = str_replace('ax21:', '', $xml);
            $xml = str_replace('ax23:', '', $xml);
            return $xml;

        } catch (\Exception $ex) {
            throw $ex;
        }
    }
	
    function checkSoapFaultOnSoapRespString($resp) {
        try {
            if(!strstr($resp, "<?xml")) { return null; }
            $xmlRem = $this->removeSoapEnvHeader($resp);

            $xml = new \SimpleXMLElement($xmlRem);
            $faultstring = null;
            if (!\is_null($xml->Body) && !\is_null($xml->Body->Fault) && !\is_null($xml->Body->Fault->detail)) {
                $detail_xml = dom_import_simplexml($xml->Body->Fault->detail);
                $faultstring = $detail_xml->nodeValue;
            }
            return $faultstring;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
	
    
}
