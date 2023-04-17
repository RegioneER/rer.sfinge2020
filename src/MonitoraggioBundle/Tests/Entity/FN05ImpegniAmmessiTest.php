<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\TC38CausaleDisimpegno;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use MonitoraggioBundle\Entity\FN05ImpegniAmmessi;

class FN05ImpegniAmmessiTest extends TestCase {
    /**
     * @var FN05ImpegniAmmessi
     */
    protected $entity;

    public function setUp() {
        $this->entity = new FN05ImpegniAmmessi();
    }

    public function testGetTracciato() {
        $tc38 = new TC38CausaleDisimpegno();
        $tc38->setCausaleDisimpegno('dis');

        $tc4 = new TC4Programma();
        $tc4->setCodProgramma('setCodProgramma');

        $tc36 = new TC36LivelloGerarchico();
        $tc36->setCodLivGerarchico('setCodLivGerarchico');

        $data = new \DateTime('2000-01-01');

        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setCodImpegno('setCodImpegno')
            ->setTipologiaImpegno('setTipologiaImpegno')
            ->setDataImpegno($data)
            ->setTc4Programma($tc4)
            ->setTc36LivelloGerarchico($tc36)
            ->setDataImpAmm($data)
            ->setTipologiaImpAmm('setTipologiaImpAmm')
            ->setTc38CausaleDisimpegnoAmm($tc38)
            ->setImportoImpAmm('123.1')
            ->setNoteImp('setNoteImp')
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
        $this->assertEquals($match[0][4], 'setCodProgramma');
        $this->assertEquals($match[0][5], 'setCodLivGerarchico');
        $this->assertEquals($match[0][6], '01/01/2000');
        $this->assertEquals($match[0][7], 'setTipologiaImpAmm');
        $this->assertEquals($match[0][8], 'dis');
        $this->assertEquals($match[0][9], '123,10');
        $this->assertEquals($match[0][10], 'setNoteImp');
        $this->assertEquals($match[0][11], 'S');
    }

    public function testFlagCancellazione(): void {
        $this->assertNull($this->entity->getFlgCancellazione());
        $this->entity->setFlgCancellazione('S');

        $this->assertSame('S', $this->entity->getFlgCancellazione());
    }

    public function testTc38(): void {
        $this->assertNull($this->entity->getTc38CausaleDisimpegnoAmm());

        $tc = new TC38CausaleDisimpegno();

        $this->entity->setTc38CausaleDisimpegnoAmm($tc);
        $this->assertSame($tc, $this->entity->getTc38CausaleDisimpegnoAmm());
    }

    /**
     * @dataProvider tc38ValidDataProvider
     */
    public function testTc38Valid(?string $tipologiaImpegno, ?TC38CausaleDisimpegno $tc38, bool $esito): void {
        $this->entity
        ->setTipologiaImpAmm($tipologiaImpegno)
        ->setTc38CausaleDisimpegnoAmm($tc38);

        $res = $this->entity->isTC38TipologiaDisimpegnoValid();
        $this->assertSame($esito, $res);
    }

    public function tc38ValidDataProvider(): array {
        $tc38 = new TC38CausaleDisimpegno();
        return [
            ['I', null, true],
            ['I-TR', null, true],
            ['', null, false],
            [null, null, false],
            ['D', $tc38, true],
            [null, $tc38, true],
        ];
    }

    public function testId() {
        $this->assertNull($this->entity->getId());
    }
}
