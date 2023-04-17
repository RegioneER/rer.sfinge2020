<?php
namespace IstruttorieBundle\Tests\Controller;

use BaseBundle\Tests\TestWeb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


/**
 * @runTestsInSeparateProcesses
 */
class IstruttoriaControllerTest extends TestWeb {

    const VALUTA_CHECKLIST_URL = '/istruttorie/istruttoria/valuta/6036';
    const AVANZAMENTO_ATC = '/istruttorie/istruttoria/3684/avanzamento_atc';

    /**
     * @group functional_test
     */
    public function testValutaChecklistAction()
    {
        $this->formTest(self::VALUTA_CHECKLIST_URL);
    }

    /**
     * @group functional_test
     */
    public function testAvanzamentoATCAction()
    {
        $this->formTest(self::AVANZAMENTO_ATC);
    }
}