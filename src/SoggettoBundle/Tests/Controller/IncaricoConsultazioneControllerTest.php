<?php
namespace SoggettoBundle\Tests\Controller;

use BaseBundle\Tests\TestWeb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


/**
 * @runTestsInSeparateProcesses
 */
class IncaricoConsultazioneControllerTest extends TestWeb {

    const ELENCO_INCARICHI = '/incarichi/consultazione/lista';
    const DETTAGLIO_INCARICO = '/incarichi/consultazione/dettaglio_incarico/11';

    /**
     * @group functional_test
     */
    public function testElencoIncarichi()
    {
        $crawler =$this->formTest(self::ELENCO_INCARICHI);
    }

    /**
     * @group functional_test
     */
    public function testdettaglioIncaricoAction()
    {
        $this->elencoTest(self::DETTAGLIO_INCARICO);
    }
}