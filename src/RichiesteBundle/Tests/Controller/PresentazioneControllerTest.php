<?php
namespace RichiesteBundle\Tests\Controller;

use BaseBundle\Tests\TestWeb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @runTestsInSeparateProcesses
 */
class PresentazioneControllerTest extends TestWeb{
    const ELENCO_RICHIESTE_URL = '/richieste/common/elenco';
    const DATI_PROGETTO_URL= '/richieste/common/5504/dati_progetto';
    const MODIFICA_INTERVENTO_URL = '/richieste/common/5504/1182/modifica_intervento';
    const PIANO_COSTI_URL= '/richieste/common/5504/piano_costi/5668';
    const IMPEGNI_URL = 'richieste/common/5504/impegni';

    /**
     * @group functional_test
     */
    public function testElencoRichiesteAction(){
        $crawler =$this->client->request(Request::METHOD_GET, self::ELENCO_RICHIESTE_URL);
        /** @var Response $response */
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $header = $crawler->filter('body > div.header');
        $this->assertNotEmpty($header);
        $content = $crawler->filter('body > div.page-container');
        $this->assertNotEmpty($content);

        $menu = $content->filter('div.page-sidebar');
        $this->assertNotEmpty($menu);

        $elenco = $content->filter('div.page-content-inner table.table');
        $this->assertNotEmpty($elenco);

        $righe = $elenco->filter('tbody > tr');
        $this->assertNotEmpty($righe);
    }

    /**
     * @group functional_test
     */
    public function testGestioneDatiProgettoAction()
    {
        $crawler =$this->client->request(Request::METHOD_GET, self::DATI_PROGETTO_URL);
        /** @var Response $response */
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $form = $crawler->filter('div.page-content-inner form');
        $this->assertNotEmpty($form);
    }

    /**
     * @group functional_test
     */
    public function testModificaInterventoAction(){
        $crawler =$this->client->request(Request::METHOD_GET, self::MODIFICA_INTERVENTO_URL);
        /** @var Response $response */
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $form = $crawler->filter('div.page-content-inner form');
        $this->assertNotEmpty($form);
    }

    /**
     * @group functional_test
     */
    public function testPianoDeiCostiAction(){
        $crawler =$this->client->request(Request::METHOD_GET, self::PIANO_COSTI_URL);
        /** @var Response $response */
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $form = $crawler->filter('div.page-content-inner form');
        $this->assertNotEmpty($form);
    }
    
    /**
     * @group functional_test
     */
    public function testImpegniRichiestaAction(){
        $crawler =$this->client->request(Request::METHOD_GET, self::IMPEGNI_URL);
        /** @var Response $response */
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $form = $crawler->filter('div.page-content-inner form');
        $this->assertNotEmpty($form);
    }
}