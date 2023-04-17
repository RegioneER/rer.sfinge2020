<?php
namespace SfingeBundle\Tests\Controller;

use BaseBundle\Tests\TestWeb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @runTestsInSeparateProcesses
 */
class ProcedureConsultazioneControllerTest extends TestWeb {
    const ELENCO_PROCEDURE = '/procedure/consultazione/elenco_atti_amministrativi';
    const VISUALIZZA_MAN_INTERESSE = '/procedure/consultazione/manifestazione_interesse_visualizza/1';
    const ELENCO_DOCUMENTI = '/procedure/gestione/elenco_documenti_atto_amministrativo/1';

    /**
     * @group functional_test
     */
    public function testElencoProcedureAction()
    {
        $this->elencoTest(self::ELENCO_PROCEDURE);
    }

    /**
     * @group functional_test
     */
    public function testVisualizzaManifestazioneAction()
    {
        $this->elencoTest(self::VISUALIZZA_MAN_INTERESSE);
    }

    /**
     * @group functional_test
     */
    public function testElencoDocumentiProcedureAction()
    {
        $document = $this->elencoTest(self::ELENCO_DOCUMENTI);
        $form = $document->filter('form');
        $this->assertNotEmpty($form);
    }
}