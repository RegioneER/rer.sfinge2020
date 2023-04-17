<?php
namespace ProtocollazioneBundle\Service;

use ProtocollazioneBundle\Service\CurlSoapClient;
use ProtocollazioneBundle\Service\DocERLogService;

/**
 * Description of SoapService
 *
 * @author gaetanoborgosano
 * @author refactoring by Davide Cannistraro
 */
class SoapService {

	protected $DocERLogService;
	function getDocERLogService() {	return $this->DocERLogService; }
	function setDocERLogService($DocERLogService) { $this->DocERLogService = $DocERLogService; }

		
	protected $app_function;
	function getApp_function() { return $this->app_function; }
	function setApp_function($app_function) { $this->app_function = $app_function; }
		
    protected $soap_wsdl_url;
    protected $soap_ep;
    protected $soap_context;

    /** @var CurlSoapClient */
    protected $SoapClient;
    /** @var \Exception */
    protected $lastEx;

    // GETTERS - SETTERS	

    /**
     * getSoap_wsdl_url
     * @return String
     */
    public function getSoap_wsdl_url() {
            return $this->soap_wsdl_url;
    }

    /**
     * getSoap_ep
     * @return String
     */
    public function getSoap_ep() {
            return $this->soap_ep;
    }

    /**
     * getSoap_context
     * @return Array
     */
    public function getSoap_context() {
            return $this->soap_context;
    }

    /**
     * getLastEx
     * @return \Exception|NULL
     */
    public function getLastEx() {
            return $this->lastEx;
    }

    /**
     * getSoapClient
     * @return \SoapClient|NULL
     */
    public function getSoapClient() {
            return $this->SoapClient;
    }

    /**
     * setSoap_wsdl_url
     * @param String $soap_wsdl_url
     */
    public function setSoap_wsdl_url($soap_wsdl_url) {
            $this->soap_wsdl_url = $soap_wsdl_url;
    }

    /**
     * setSoap_ep
     * @param String $soap_ep
     */
    public function setSoap_ep($soap_ep) {
            $this->soap_ep = $soap_ep;
    }

    /**
     * setSoap_context
     * @param String $soap_context
     */
    public function setSoap_context($soap_context) {
            $this->soap_context = $soap_context;
    }

    /**
     * setSoapClient
     * @param \SoapClient $SoapClient
     */
    public function setSoapClient($SoapClient) {
            $this->SoapClient = $SoapClient;
    }

    
    public function __construct($DocERLogService) {
		$this->setDocERLogService($DocERLogService);
        $this->soap_context = stream_context_create( array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true)));
    }	


    /**
     * 
     * @param String|NULL $soap_wsdl_url
     * @param String|NULL $soap_ep
     * @param String|NULL $soap_context
     * @return \SoapClient|NULL
     */
    public function initSoapClient($token, $soap_wsdl_url = null, $soap_ep = null, $soap_context = null) {
        if (!\is_null($soap_wsdl_url)) {
            $this->setSoap_wsdl_url($soap_wsdl_url);        
        }
        if (!\is_null($soap_ep)) {
            $this->setSoap_ep ($soap_ep);
        }
        if (!\is_null($soap_context)) {
            $this->setSoap_context($soap_context);
        }

        $this->createNewSoapClient($token);
        return $this->getSoapClient();
    }

    protected function createNewSoapClient($token) {
        try {
            $this->lastEx = null;
            $SoapClient = new CurlSoapClient($this->getSoap_wsdl_url(), array('location'        =>  $this->getSoap_ep(), 
                                                                              'stream_context'  =>  $this->getSoap_context())
                                                                             );
            $SoapClient->setToken($token);
			$SoapClient->setLoggerService($this->getDocERLogService());
			$SoapClient->setApp_function($this->getApp_function());
            $this->setSoapClient($SoapClient);
        } catch (\Exception $ex) {
            $this->lastEx = $ex;
            return null;
        }
    }

}
