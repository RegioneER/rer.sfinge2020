<?php
namespace AttuazioneControlloBundle\Tests\Controller\Istruttoria;

use BaseBundle\Tests\TestWeb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;



/**
 * @runTestsInSeparateProcesses
 */
class PagamentiControllerTest extends TestWeb {

    const ELENCO_URL = '/attuazione/istruttoria/pagamenti/elenco_pagamenti';

    public function testElencoPagamentiAction()
    {
     $this->elencoTest(self::ELENCO_URL);   
    }
}