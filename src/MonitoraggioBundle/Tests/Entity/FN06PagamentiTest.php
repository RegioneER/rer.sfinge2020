<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\TC39CausalePagamento;
use MonitoraggioBundle\Entity\FN06Pagamenti;

class FN06PagamentiTest extends TestCase {
    /**
     * @var FN06Pagamenti
     */
    protected $entity;

    public function setUp() {
        $this->entity = new FN06Pagamenti();
    }

    public function testGetTracciato() {
        $tc39 = new TC39CausalePagamento();
        $tc39->setCausalePagamento('setCausaleDisimpegno');

        $data = new \DateTime('2000-01-01');

        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setCodPagamento('setCodImpegno')
            ->setTipologiaPag('setTipologiaImpegno')
            ->setDataPagamento($data)
            ->setImportoPag('123.1')
            ->setTc39CausalePagamento($tc39)
            ->setNotePag('setNoteImpegno')
            ->setFlgCancellazione('S');

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 8);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setCodImpegno');
        $this->assertSame($match[0][2], 'setTipologiaImpegno');
        $this->assertSame($match[0][3], '01/01/2000');
        $this->assertEquals($match[0][4], '123,10');
        $this->assertEquals($match[0][5], 'setCausaleDisimpegno');
        $this->assertEquals($match[0][6], 'setNoteImpegno');
        $this->assertEquals($match[0][7], 'S');
    }

    public function testId() {
        $this->assertNull($this->entity->getId());
    }
}
