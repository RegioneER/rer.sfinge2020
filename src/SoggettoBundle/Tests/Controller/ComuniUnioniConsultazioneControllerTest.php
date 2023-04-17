<?php
namespace SoggettoBundle\Tests\Controller;

use BaseBundle\Tests\TestWeb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @runTestsInSeparateProcesses
 */
class ComuniUnioniConsultazioneControllerTest extends TestWeb{
    
    const ELENCO_COMUNE_OLD = '/comuni/consultazione/elenco_comuni_unioni';
    const CREA_COMUNE_URL = '/comuni/gestione/crea_comune_unione';
    
    /**
     * @group functional_test
     */
    public function testElencoSoggettiAction(){
        $this->client->request(Request::METHOD_GET, self::ELENCO_COMUNE_OLD);
        /** @var Response $response */
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @group functional_test
     */
    public function testCreaSoggettoAction()
    {
        $crawler =$this->formTest(self::CREA_COMUNE_URL . '?codice_fiscale=blablabla&tipo=COMUNE');
    }

}