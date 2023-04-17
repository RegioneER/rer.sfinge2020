<?php

namespace MonitoraggioBundle\Tests\Entity;

use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\TC49CausaleTrasferimento;
use MonitoraggioBundle\Entity\TR00Trasferimenti;
use PHPUnit\Framework\TestCase;

class TR00TrasferimentiTest extends TestCase {
    /**
     * @var TR00Trasferimenti
     */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->entity = new TR00Trasferimenti();
    }

    public function testGetTracciato() {
        $tc4 = new TC4Programma();
        $tc4->setCodProgramma('2014IT05FMOP001');

        $tc49 = new TC49CausaleTrasferimento();
        $tc49->setCausaleTrasferimento('1');

        $data = new \DateTime();

        $this->entity = new TR00Trasferimenti();
        $this->entity
            ->setTc4Programma($tc4)
            ->setDataTrasferimento($data)
            ->setTc49CausaleTrasferimento($tc49)
            ->setCodTrasferimento('setCodTrasferimento')
            ->setImportoTrasferimento('9999')
            ->setCfSogRicevente('setCfSogRicevente')
            ->setFlagSoggettoPubblico('N')
            ->setFlgCancellazione('S');

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 8);
        $this->assertEquals($match[0][0], 'setCodTrasferimento');
        $this->assertEquals($match[0][1], $data->format('d/m/Y'));
        $this->assertEquals($match[0][2], '2014IT05FMOP001');
        $this->assertEquals($match[0][3], '1');
        $this->assertSame($match[0][4], '9999,00');
        $this->assertEquals($match[0][5], 'setCfSogRicevente');
        $this->assertEquals($match[0][6], 'N');
        $this->assertEquals($match[0][7], 'S');
    }
}
