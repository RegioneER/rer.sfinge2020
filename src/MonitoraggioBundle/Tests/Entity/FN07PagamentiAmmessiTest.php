<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\TC39CausalePagamento;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\FN07PagamentiAmmessi;

class FN07PagamentiAmmessiTest extends TestCase {
    /**
     * @var FN07PagamentiAmmessi
     */
    protected $entity;

    public function setUp() {
        $this->entity = new FN07PagamentiAmmessi();
    }

    public function testGetTracciato() {
        $tc39 = new TC39CausalePagamento();
        $tc39->setCausalePagamento('setCausaleDisimpegno');

        $tc4 = new TC4Programma();
        $tc4->setCodProgramma('setCodProgramma');

        $tc36 = new TC36LivelloGerarchico();
        $tc36->setCodLivGerarchico('setCodLivGerarchico');

        $data = new \DateTime('2000-01-01');

        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setCodPagamento('setCodImpegno')
            ->setTipologiaPag('setTipologiaImpegno')
            ->setDataPagamento($data)
            ->setTc4Programma($tc4)
            ->setTc36LivelloGerarchico($tc36)
            ->setDataPagAmm($data)
            ->setTipologiaPagAmm('setTipologiaPagAmm')
            ->setTc39CausalePagamento($tc39)
            ->setImportoPagAmm('123.1')
            ->setNotePag('setNoteImpegno')
            ->setFlgCancellazione('S');

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 12);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setCodImpegno');
        $this->assertSame($match[0][2], 'setTipologiaImpegno');
        $this->assertSame($match[0][3], '01/01/2000');

        $this->assertSame($match[0][4], 'setCodProgramma');
        $this->assertSame($match[0][5], 'setCodLivGerarchico');
        $this->assertSame($match[0][6], '01/01/2000');
        $this->assertSame($match[0][7], 'setTipologiaPagAmm');

        $this->assertEquals($match[0][8], 'setCausaleDisimpegno');
        $this->assertEquals($match[0][9], '123,10');
        $this->assertEquals($match[0][10], 'setNoteImpegno');
        $this->assertEquals($match[0][11], 'S');
    }

    public function testId() {
        $this->assertNull($this->entity->getId());
    }
}
