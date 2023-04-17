<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\IN00IndicatoriRisultato;

class IN00IndicatoriRisultatoTest extends TestCase {
    /**
     * @var IN00IndicatoriRisultato
     */
    protected $entity;

    public function setUp() {
        $this->entity = new IN00IndicatoriRisultato();
    }

    public function testGetTracciato() {
        $tc42_43 = new \MonitoraggioBundle\Entity\TC42_43IndicatoriRisultato();
        $tc42_43->setCodIndicatore('indicatore');

        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setTipoIndicatoreDiRisultato('setTipoIndicatoreDiRisultato')
            ->setFlgCancellazione('S')
            ->setIndicatoreId($tc42_43);

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        \preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 4);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setTipoIndicatoreDiRisultato');
        $this->assertEquals($match[0][2], 'indicatore');
        $this->assertEquals($match[0][3], 'S');
    }

    public function testId() {
        $this->assertNull($this->entity->getId());
    }
}
