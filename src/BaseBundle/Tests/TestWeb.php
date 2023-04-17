<?php

namespace BaseBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\Client;

class TestWeb extends WebTestCase {
    const PASSWORD = 'password';
    const USERNAME = 'DMCVCN81A15G273T';

    const LOGIN_BASE_URL = '/login';
    /**
     * @var Client
     */
    protected $client;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->client = self::createClient();
        $this->doLogin();
    }

    protected function doLogin() {
        $crawler = $this->client->request(Request::METHOD_GET, self::LOGIN_BASE_URL);
        $form = $crawler->selectButton('Accedi')->form([
            '_username' => self::USERNAME,
            '_password' => self::PASSWORD,
        ]);
        $this->client->submit($form);
    }

  
    protected function elencoTest(string $rotta): Crawler {
        $crawler = $this->client->request(Request::METHOD_GET, $rotta);
        /** @var Response $response */
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $header = $crawler->filter('body > div.header');
        $this->assertNotEmpty($header, 'Header pagina non presente');
        $content = $crawler->filter('body > div.page-container');
        $this->assertNotEmpty($content, 'Contenuto pagina non presente');

        $menu = $content->filter('div.page-sidebar');
        $this->assertNotEmpty($menu, 'Menu pagina non presente');

        $elenco = $content->filter('div.page-content-inner table.table');
        $this->assertNotEmpty($elenco, 'Tabella pagina non presente');

        return $crawler;
    }

    
    protected function formTest(string $rotta): Crawler {
        $crawler = $this->client->request(Request::METHOD_GET, $rotta);
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

        return $crawler;
    }
}
