<?php
namespace SoggettoBundle\Tests\Controller;

use BaseBundle\Tests\TestWeb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @runTestsInSeparateProcesses
 */
class AziendaConsultazioneControllerTest extends TestWeb{
    
    const INDEX_PAGE_URL_OLD = '/aziende/consultazione/elenco_aziende';
    const INDEX_PAGE_URL= '/soggetti_giuridici/consultazione/elenco_soggetti_giuridici';
    const VISUALIZZA_AZIENDA_URL = '/aziende/consultazione/azienda_visualizza/1';
    const ELENCO_SEDI_URL = '/aziende/consultazione/sedi_operative/elenco/1';
    const AGGIUNGI_SEDE_URL = '/aziende/gestione/sedi_operative/aggiungi/1';

    /**
     * @group functional_test
     */
    public function testElencoAziendeActionOld(){
        $crawler =$this->client->request(Request::METHOD_GET, self::INDEX_PAGE_URL_OLD);
        /** @var Response $response */
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @group functional_test
     */
    public function testElencoAziendeAction(){
        $crawler =$this->client->request(Request::METHOD_GET, self::INDEX_PAGE_URL);
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
    public function testVisualizzaAziendaAction()
    {
        $crawler =$this->client->request(Request::METHOD_GET, self::VISUALIZZA_AZIENDA_URL);
        /** @var Response $response */
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $header = $crawler->filter('body > div.header');
        $this->assertNotEmpty($header);

        $content = $crawler->filter('body > div.page-container');
        $this->assertNotEmpty($content);

        $menu = $content->filter('div.page-sidebar');
        $this->assertNotEmpty($menu);

        $form = $content->filter('div.page-content-inner form');
        $this->assertNotEmpty($form);
    }

    /**
     * @group functional_test
     */
    public function testElencoSediOperativeAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, self::INDEX_PAGE_URL);
        /** @var Response $response */
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $table = $crawler->filter('div.page-container table.table');
        $this->assertNotEmpty($table);
    }

    /**
     * @group functional_test
     */
    public function testAggungiSedeOperativaAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, self::AGGIUNGI_SEDE_URL);
        /** @var Response $response */
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $table = $crawler->filter('div.page-container form');
        $this->assertNotEmpty($table);
    }
}