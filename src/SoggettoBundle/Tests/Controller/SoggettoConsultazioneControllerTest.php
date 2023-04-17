<?php

namespace SoggettoBundle\Tests\Controller;

use BaseBundle\Tests\TestWeb;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @runTestsInSeparateProcesses
 */
class SoggettoConsultazioneControllerTest extends TestWeb {
    const ELENCO_ALTRI_SOGGETTI_OLD = '/soggetti/consultazione/elenco_soggetti';
    const CREA_SOGGETTO_URL = '/soggetti_giuridici/gestione/crea_soggetto_giuridico';

    /**
     * @group functional_test
     */
    public function testElencoSoggettiAction() {
        $this->client->request(Request::METHOD_GET, self::ELENCO_ALTRI_SOGGETTI_OLD);
        
        /** @var Response $response */
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @group functional_test
     */
    public function testCreaSoggettoAction() {
        $crawler = $this->formTest(self::CREA_SOGGETTO_URL);
    }
}
