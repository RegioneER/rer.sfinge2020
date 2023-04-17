<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\FN08Percettori;
use MonitoraggioBundle\Entity\TC40TipoPercettore;

class FN08PercettoriTest extends TestCase {
    /**
     * @var FN08Percettori
     */
    protected $entity;

    public function setUp() {
        $this->entity = new FN08Percettori();
    }

    public function testGetTracciato() {
        $tc40 = new TC40TipoPercettore();
        $tc40->setTipoPercettore('setCausaleDisimpegno');

        $data = new \DateTime('2000-01-01');

        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setCodPagamento('setCodImpegno')
            ->setTipologiaPag('setTipologiaImpegno')
            ->setDataPagamento($data)
            ->setCodiceFiscale('setCodiceFiscale')
            ->setFlagSoggettoPubblico('setFlagSoggettoPubblico')
            ->setImporto('123.1')
            ->setTc40TipoPercettore($tc40)
            ->setFlgCancellazione('S');

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        \preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 9);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setCodImpegno');
        $this->assertSame($match[0][2], 'setTipologiaImpegno');
        $this->assertSame($match[0][3], '01/01/2000');
        $this->assertEquals($match[0][4], 'setCodiceFiscale');
        $this->assertEquals($match[0][5], 'setFlagSoggettoPubblico');
        $this->assertEquals($match[0][6], 'setCausaleDisimpegno');
        $this->assertEquals($match[0][7], '123,10');
        $this->assertEquals($match[0][8], 'S');
    }

    public function testId() {
        $this->assertNull($this->entity->getId());
    }

    public function testToString() {
        $this->entity->setCodLocaleProgetto('codlocale')
        ->setImporto('1000,00')
        ->setCodPagamento('PAG');

        $this->assertEquals('codlocale - PAG: 1.000,00', $this->entity->__toString());
    }
}
