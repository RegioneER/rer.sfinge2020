<?php

namespace ProtocollazioneBundle\Service;

use ProtocollazioneBundle\Service\DocERBaseService;

/**
 * Description of DocERDocumentService
 *
 * @author gaetanoborgosano - davidecannistraro
 * @author refactoring by Davide Cannistraro
 */
class DocERDocumentService extends DocERBaseService {
	
    //Proprietà statiche
    private $documentservices_EP = "/WSDocer/services/DocerServices";
    private $documentservices_URL = "/WSDocer/services/DocerServices?wsdl";
    private static $documentservices_namespaces = array("web" => "http://webservices.docer.kdm.it");
    private static $documentservices_method_namespace_prefix = "web";
    static function setDocumentservices_EP($documentservices_EP) { $this->$documentservices_EP = $documentservices_EP; }
    static function setDocumentservices_URL($documentservices_URL) { $this->$documentservices_URL = $documentservices_URL; }
    
    static function getDocumentservices_namespaces() { return self::$documentservices_namespaces; }
    static function getDocumentservices_method_namespace_prefix() { return self::$documentservices_method_namespace_prefix; }
    static function setDocumentservices_namespaces($documentservices_namespaces) { self::$documentservices_namespaces = $documentservices_namespaces; }
    static function setDocumentservices_method_namespace_prefix($documentservices_method_namespace_prefix) { self::$documentservices_method_namespace_prefix = $documentservices_method_namespace_prefix; }
	
 
    public function __construct($token, $serviceContainer) {
        parent::__construct($serviceContainer);
        $this->setToken($token);
        $this->setNamespaces(self::getDocumentservices_namespaces());
        $this->setMethodNamespacePrefix(self::getDocumentservices_method_namespace_prefix());
        
        $this->initSoapClient($this->documentservices_URL, $this->documentservices_EP);
    }
	
    /**
     * deleteDocument
     * @param String $docId
     * @return String $rel
     * @throws \Exception
     */	
    public function deleteDocument($docId) {
        $this->setApp_function("deleteDocument");
        try {
            $paramsArray = array( "docId" => $docId );
            $mode = "EP";
            $timeout = $this->getTimeout();
            $MTOMFilterResponseMode = 'response';
            $resp = $this->getSoapClient()->__curl_call("deleteDocument", $paramsArray, $mode, $timeout, $MTOMFilterResponseMode, false);
            if (!$resp) {
                throw new \Exception("Errore: il documento non puo' essere eliminato da DocER");                
            }

            $fault = $this->checkSoapFaultOnSoapRespString($resp);
            if(!\is_null($fault)) {
                throw new \Exception($fault);
            }

            $xmlSE = new \SimpleXMLElement($resp);
            $xml = dom_import_simplexml($xmlSE);

            $rel = $xml->nodeValue;
            if ($rel) {
                return $rel;
            } else {
                throw new \Exception("Errore: il documento non puo' essere eliminato da DocER");
            }

        } catch (\Exception $ex) {
            throw $ex;
        }
    }
	
    /**
     * createDocument
     * @param String $filePathName
     * @param String $fileContent
     * @param String $docname
     * @param String $cod_ente
     * @param String $cod_aoo
     * @param String $type_id
     * @param String $tipo_componente
     * @return String idDocument
     * @throws \Exception
     */	
    public function createDocument($filePathName, $fileContent, $docname, $cod_ente, $cod_aoo, $type_id, $tipo_componente) {
        $this->setApp_function("createDocument");

        try {

            if(\is_null($filePathName) && \is_null($fileContent)) {
                throw new \Exception("Errore: 'filePathName' e 'fileContent' non possono essere entrambi nulli");
            }

            if(!\is_null($filePathName) && \is_null($fileContent)) {
                $fileContent = file_get_contents($filePathName);
            }
            $docname_str = str_replace("&", "&amp;", $docname);
            $metadata = array( array("metadata" => array("key" => 'DOCNAME',         "value" => $docname_str)),
                               array("metadata" => array("key" => 'COD_ENTE',        "value" => $cod_ente)),
                               array("metadata" => array("key" => 'COD_AOO',         "value" => $cod_aoo)),
                               array("metadata" => array("key" => 'TYPE_ID',         "value" => $type_id)),
                               array("metadata" => array("key" => 'TIPO_COMPONENTE', "value" => $tipo_componente)),
                               array("metadata" => array("key" => 'FLAG_CONSERV',    "value" => 1)),
                               array("metadata" => array("key" => 'STATO_CONSERV',   "value" => 1)),
                               array("metadata" => array("key" => 'FORZA_CONSERV',   "value" => 'true'))
                             );

            $paramsArray = array( $metadata );                
            $paramsArray['file'] = base64_encode($fileContent);
            $mode = "EP";
            $timeout = $this->getTimeout();
            $MTOMFilterResponseMode = "response";
            $resp = $this->getSoapClient()->__curl_call("createDocument", $paramsArray, $mode, $timeout, $MTOMFilterResponseMode, false);
            $fault = $this->checkSoapFaultOnSoapRespString($resp);
            if(!\is_null($fault)) {
                throw new \Exception($fault);
            }				

            $xmlSE = new \SimpleXMLElement($resp);
            $xml = dom_import_simplexml($xmlSE);

            $idDocument = $xml->nodeValue; 
            return $idDocument;

        } catch (\Exception $ex) {
            throw $ex;
        }
    }
	
    /**
     * setACLDocument
     * @param String $idDocument
     * @param String $username
     * @param Array associativo $acl
     * @return Bool
     * @throws \Exception
     */
    public function setACLDocument($idDocument, $username, $acl) {
        $this->setApp_function("setACLDocument");

        try {
            $paramsArray = array("docId" => intval($idDocument),
                                 array(
                                        array("acls" => array("key" => $username, "value" => $acl)),
                                 )
            );
            $mode = "EP";
            $timeout = $this->getTimeout();
            $MTOMFilterResponseMode = 'response';
            $resp = $this->getSoapClient()->__curl_call("setACLDocument", $paramsArray, $mode, $timeout, $MTOMFilterResponseMode, false);
            $fault = $this->checkSoapFaultOnSoapRespString($resp);
            if(!\is_null($fault)) {
                throw new \Exception($fault);
            }				

            $xml = new \SimpleXMLElement($resp);
            $xml = dom_import_simplexml($xml);

            $aclDocument = $xml->nodeValue;
            if ($aclDocument) {
                return $aclDocument;
            } else {
                throw new \Exception("Errore: impossibile settare le ACL");
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * addRelated
     * @param String $idDocument
     * @param Array $related
     * @return Bool
     * @throws \Exception
     */
    public function addRelated($idDocument, $related) {
        $this->setApp_function("addRelated");
        try {
            $relatedArray = array();
            foreach ($related as $value) {
                $relatedArray[] = array("related" => $value);
            }

            $paramsArray = array( "docId" => intval($idDocument), $relatedArray );

            $mode = "EP";
            $timeout = $this->getTimeout();
            $MTOMFilterResponseMode = 'response';
            $resp = $this->getSoapClient()->__curl_call("addRelated", $paramsArray, $mode, $timeout, $MTOMFilterResponseMode, false);
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
                throw new \Exception("Errore: impossibile eseguire l'addRelated");
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
	
}
