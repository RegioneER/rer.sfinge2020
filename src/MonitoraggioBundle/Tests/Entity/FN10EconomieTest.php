<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use MonitoraggioBundle\Entity\FN10Economie;

class FN10EconomieTest extends TestCase {
    /**
     * @var FN10Economie
     */
    protected $entity;

    public function setUp() {
        $this->entity = new FN10Economie();
    }

    public function testGetTracciato() {
        $tc33 = new TC33FonteFinanziaria();
        $tc33->setCodFondo('ALTRO_NAZ');

        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setImporto('123.1')
            ->setFlgCancellazione('S')
            ->setTc33FonteFinanziaria($tc33);

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        \preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 4);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'ALTRO_NAZ');
        $this->assertEquals($match[0][2], '123,10');
        $this->assertEquals($match[0][3], 'S');
    }

    public function testId() {
        $this->assertNull($this->entity->getId());
    }

    public function testImporto() {
        $this->assertNull($this->entity->getImporto());

        $this->entity->setImporto(999.99);

        $this->assertEquals(999.99, $this->entity->getImporto());
    }
}
