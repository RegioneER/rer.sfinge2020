<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\TC38CausaleDisimpegno;
use MonitoraggioBundle\Entity\FN04Impegni;

class FN04ImpegniTest extends TestCase {
    /**
     * @var FN04Impegni
     */
    protected $entity;

    public function setUp() {
        $this->entity = new FN04Impegni();
    }

    public function testGetTracciato() {
        $tc38 = new TC38CausaleDisimpegno();
        $tc38->setCausaleDisimpegno('setCausaleDisimpegno');

        $data = new \DateTime('2000-01-01');

        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setCodImpegno('setCodImpegno')
            ->setTipologiaImpegno('setTipologiaImpegno')
            ->setDataImpegno($data)
            ->setImportoImpegno('123.1')
            ->setNoteImpegno('setNoteImpegno')
            ->setTc38CausaleDisimpegno($tc38)
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

    /**
     * @dataProvider tc38ValidDataProvider
     */
    public function testTc38Valid(?string $tipologiaImpegno, ?TC38CausaleDisimpegno $tc38, bool $esito): void {
        $this->entity
        ->setTipologiaImpegno($tipologiaImpegno)
        ->setTc38CausaleDisimpegno($tc38);

        $res = $this->entity->isTC38TipologiaDisimpegnoValid();
        $this->assertSame($esito, $res);
    }

    public function tc38ValidDataProvider(): array {
        $tc38 = new TC38CausaleDisimpegno();
        return [
            ['I', null, true],
            ['', null, false],
            [null, null, false],
            ['D', $tc38, true],
            [null, $tc38, true],
        ];
    }
}
