<?php

namespace RichiesteBundle\Tests\GestoriRichiestePA\SezioniRiepilogo;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Client;

class PianoCostoFunctionalTest extends WebTestCase
{
    const PASSWORD = 'password';
    const USERNAME = 'DMCVCN81A15G273T';

    /**
     * @var Client
     */
    protected $client;

    public function setUp(){
        $this->client = static::createClient();
        $this->doLogin();
    }

    protected function doLogin()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Accedi')->form(array(
            '_username' => self::USERNAME,
            '_password' => self::PASSWORD,
        ));
        $this->client->submit($form);
    }
    
    /**
     * @dataProvider urlDataProvider
     */
    public function testVerificaFunzionamentoPagina($url){
        $crawler = $this->client->request(Request::METHOD_GET, $url);
        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());
    }

    public function urlDataProvider(): array{
        return [
            ['/richieste/procedura_pa/5455/sezione/piano_costo/5614'],
        ];
    }

}