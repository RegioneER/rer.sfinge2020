<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\PR00IterProgetto;
use MonitoraggioBundle\Entity\TC46FaseProcedurale;

class PR00IterProgettoTest extends TestCase {
    /**
     * @var PR00IterProgetto
     */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->entity = new PR00IterProgetto();
    }

    public function testGetTracciato() {
        $tc46 = new \MonitoraggioBundle\Entity\TC46FaseProcedurale();
        $tc46->setCodFase('setCodFase');

        $data = new \DateTime('2010-01-02');
        $data2 = new \DateTime('2011-11-10');
        $data3 = new \DateTime('2012-01-02');
        $data4 = new \DateTime('2013-01-02');

        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setDataInizioPrevista($data)
            ->setDataInizioEffettiva($data2)
            ->setDataFinePrevista($data3)
            ->setDataFineEffettiva($data4)
            ->setTc46FaseProcedurale($tc46)
            ->setFlgCancellazione('S');

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        \preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 7);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setCodFase');
        $this->assertEquals($match[0][2], '02/01/2010');
        $this->assertEquals($match[0][3], '10/11/2011');
        $this->assertEquals($match[0][4], '02/01/2012');
        $this->assertEquals($match[0][5], '02/01/2013');
        $this->assertEquals($match[0][6], 'S');
    }

    public function testId() {
        $this->assertNull($this->entity->getId());
    }

    public function testFlagCancellazione() {
        $this->assertNull($this->entity->getFlgCancellazione());

        $this->entity->setFlgCancellazione('S');
        $this->assertSame('S', $this->entity->getFlgCancellazione());
    }

    public function testFaseProcedurale() {
        $this->assertNull($this->entity->getFlgCancellazione());

        $tc = new TC46FaseProcedurale();
        $this->entity->setTc46FaseProcedurale($tc);
        $this->assertSame($tc, $this->entity->getTc46FaseProcedurale());
    }
}
