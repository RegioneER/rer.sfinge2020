<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\IN01IndicatoriOutput;

class IN01IndicatoriOutputTest extends TestCase {
    /**
     * @var IN01IndicatoriOutput
     */
    protected $entity;

    public function setUp() {
        $this->entity = new IN01IndicatoriOutput();
    }

    public function testGetTracciato() {
        $tc44_45 = new \MonitoraggioBundle\Entity\TC44_45IndicatoriOutput();
        $tc44_45->setCodIndicatore('indicatore');

        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setTipoIndicatoreDiOutput('setTipoIndicatoreDiRisultato')
            ->setIndicatoreId($tc44_45)
            ->setValProgrammato(99.1)
            ->setValoreRealizzato(1.25)
            ->setFlgCancellazione('S');

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        \preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 6);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setTipoIndicatoreDiRisultato');
        $this->assertEquals($match[0][2], 'indicatore');
        $this->assertEquals($match[0][3], '99,10');
        $this->assertEquals($match[0][4], '1,25');
        $this->assertEquals($match[0][5], 'S');
    }

    public function testId() {
        $this->assertNull($this->entity->getId());
    }

    public function testValoreProgrammato() {
        $this->assertNull($this->entity->getValProgrammato());
        $this->entity->setValProgrammato(99);

        $this->assertEquals(99, $this->entity->getValProgrammato());
    }

    public function testValoreRealizzato() {
        $this->assertNull($this->entity->getValoreRealizzato());
        $this->entity->setValoreRealizzato(99);

        $this->assertEquals(99, $this->entity->getValoreRealizzato());
    }
}
