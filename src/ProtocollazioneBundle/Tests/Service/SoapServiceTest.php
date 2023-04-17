<?php

namespace ProtocollazioneBundle\Tests\Service;
use PHPUnit\Framework\TestCase;
use ProtocollazioneBundle\Service\SoapService;
use ProtocollazioneBundle\Service\DocERLogService;

class SoapServiceTest extends TestCase{

    /**
     * @var SoapService
     */
    protected $soapService;

    public function setUp(){
        
        $logService = $this->createMock(DocERLogService::class);
        $this->soapService = new SoapService($logService);
    }
    
    public function testInitSoapClient()
    {

        $token = 'asdfghjkl';
        $soap_wsdl_url = 'https://docer-test.ente.regione.emr.it/docersystem/services/AuthenticationService?wsdl';
        $soap_ep = 'soap_ep';

        $this->soapService->initSoapClient($token, $soap_wsdl_url, $soap_ep);
        $this->assertEquals($soap_wsdl_url, $this->soapService->getSoap_wsdl_url());
        $this->assertNull($this->soapService->getLastEx());
        
        $soapClient = $this->soapService->getSoapClient();
        $this->assertNotNull($soapClient);

    }

    public function testErroreInitSoapClient(){
        $this->soapService->initSoapClient(NULL, NULL);
        $this->assertNotNull($this->soapService->getLastEx());
    }
}