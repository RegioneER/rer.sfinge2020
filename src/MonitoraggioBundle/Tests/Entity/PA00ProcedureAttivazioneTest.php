<?php

namespace MonitoraggioBundle\Tests\Entity;

use MonitoraggioBundle\Entity\PA00ProcedureAttivazione;
use MonitoraggioBundle\Entity\TC2TipoProceduraAttivazione;
use MonitoraggioBundle\Entity\TC3ResponsabileProcedura;
use PHPUnit\Framework\TestCase;

class PA00ProcedureAttivazioneTest extends TestCase {
    /**
     * @var PA00ProcedureAttivazione
     */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->entity = new PA00ProcedureAttivazione();
    }

    public function testGetTracciato() {
        $tc2 = new TC2TipoProceduraAttivazione();
        $tc2->setTipProceduraAtt('1');
        $tc3 = new TC3ResponsabileProcedura();
        $tc3->setCodTipoRespProc('2');
        $data = new \DateTime();
        $this->entity
            ->setTc2TipoProceduraAttivazione($tc2)
            ->setTc3ResponsabileProcedura($tc3)
            ->setCodProcAtt('setCodProcAtt')
            ->setCodProcAttLocale('setCodProcAttLocale')
            ->setCodAiutoRna('setCodAiutoRna')
            ->setFlagAiuti('N')
            ->setDescrProceduraAtt('setDescrProceduraAtt')
            ->setDenomRespProc('setDenomRespProc')
            ->setDataAvvioProcedura($data)
            ->setDataFineProcedura($data)
            ->setFlgCancellazione(null);

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 11);
        $this->assertEquals($match[0][0], 'setCodProcAtt');
        $this->assertEquals($match[0][1], 'setCodProcAttLocale');
        $this->assertEquals($match[0][2], 'setCodAiutoRna');

        $this->assertEquals($match[0][3], '1');
        $this->assertEquals($match[0][4], 'N');
        $this->assertEquals($match[0][5], 'setDescrProceduraAtt');
        $this->assertEquals($match[0][6], '2');
        $this->assertEquals($match[0][7], 'setDenomRespProc');
        $this->assertEquals($match[0][8], $data->format('d/m/Y'));
        $this->assertEquals($match[0][9], $data->format('d/m/Y'));
        $this->assertEquals($match[0][10], null);
    }
}
