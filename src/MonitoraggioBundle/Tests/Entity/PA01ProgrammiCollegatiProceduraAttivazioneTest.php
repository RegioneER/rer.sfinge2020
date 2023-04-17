<?php

namespace MonitoraggioBundle\Tests\Entity;

use MonitoraggioBundle\Entity\PA01ProgrammiCollegatiProceduraAttivazione;
use MonitoraggioBundle\Entity\TC4Programma;
use PHPUnit\Framework\TestCase;

class PA01ProgrammiCollegatiProceduraAttivazioneTest extends TestCase {
    /**
     * @var PA01ProgrammiCollegatiProceduraAttivazione
     */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->entity = new PA01ProgrammiCollegatiProceduraAttivazione();
    }

    public function testGetTracciato() {
        $tc4 = new TC4Programma();
        $tc4->setCodProgramma('2014IT05FMOP001');

        $this->entity
            ->setCodProcAtt('setCodProcAtt')
            ->setTc4Programma($tc4)
            ->setImporto('999')
            ->setFlgCancellazione('S');

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 4);
        $this->assertEquals($match[0][0], 'setCodProcAtt');
        $this->assertEquals($match[0][1], '2014IT05FMOP001');
        $this->assertEquals($match[0][2], '999');
        $this->assertEquals($match[0][3], 'S');
    }
}
