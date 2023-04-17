<?php

namespace ProtocollazioneBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of DocERAuthenticationService
 *
 * @author gaetanoborgosano
 * @author refactoring by Davide Cannistraro
 */

class DocERAuthenticationService extends DocERBaseService {
	
    //Proprietà statiche
    private $authentication_EP = "/docersystem/services/AuthenticationService";
    private $authentication_URL = "/docersystem/services/AuthenticationService?wsdl";
	
    static $session_identifier;
    static function getSession_identifier() { return self::$session_identifier; }
    static function setSession_identifier($session_identifier) { self::$session_identifier = $session_identifier; }

    private static $authentication_namespaces = array("ns1" => "http://authentication.core.docer.kdm.it");
    static function getAuthentication_namespaces() { return self::$authentication_namespaces; }
    static function setAuthentication_namespaces($authentication_namespaces) { self::$authentication_namespaces = $authentication_namespaces; }

    private static $authentication_method_namespace_prefix = "ns1";
    static function getAuthentication_method_namespace_prefix() { return self::$authentication_method_namespace_prefix; }
    static function setAuthentication_method_namespace_prefix($authentication_method_namespace_prefix) { self::$authentication_method_namespace_prefix = $authentication_method_namespace_prefix; }

    protected $serviceContainer;
    
    public function __construct(ContainerInterface $serviceContainer) {
        parent::__construct($serviceContainer);
        $this->serviceContainer = $serviceContainer;
        $this->setNamespaces(self::$authentication_namespaces);
        $this->setMethodNamespacePrefix($this->getAuthentication_method_namespace_prefix());
                
        $this->initSoapClient($this->authentication_URL, $this->authentication_EP);
    }


    /**
     * login
     * @param String $username
     * @param String $password
     * @param String $codiceEnte
     * @param String $application
     * @return String|\Exception
     * @throws \Exception
     */
    public function login($username, $password, $codiceEnte=null, $application = "") {
        $this->setApp_function("login");

        $parAut = new \stdClass();
        $parAut->username	= $username;
        $parAut->password	= $password;
        $parAut->codiceEnte	= $codiceEnte;	   
        $parAut->application	= $application;
        try {
            // Controllo di sessione gia' attiva
            if(!\is_null($this->getToken())) {
                throw new \Exception("Avviso: il token deve essere nullo");
            }

            $SoapClient = $this->getSoapClient();
            if(\is_null($SoapClient)) {
                throw ($this->getSoapService()->getLastEx());
            }

            $token = $SoapClient->login($parAut);
            if($token) {
                $this->setDepthToken($token->return);
                return $token->return;
            }
            else {
                throw new \Exception("Errore: impossibile effettuare il login");
            }

        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * logout
     * @return boolean|\Exception
     * @throws \Exception
     */
    public function logout() {
        $this->setApp_function("logout");
        
        try {
            $token = $this->getToken();
            if(\is_null($token)) {
                throw new \Exception("Avviso: il token non puo' essere nullo");
            }

            $SoapClient = $this->getSoapClient();
            if(\is_null($SoapClient)) {
                throw ($this->getSoapService()->getLastEx());
            }	

            $parAut = new \stdClass();
            $parAut->token = $token;
            $status = $SoapClient->logout($parAut);
            if($status) {
                // Chiusura della sessione
                $this->setDepthToken($token);
                return true;
            }
            throw new \Exception("Errore: impossibile effettuare il logout");


        } catch (\Exception $ex) {
            throw $ex;
        }
    }

	

}
