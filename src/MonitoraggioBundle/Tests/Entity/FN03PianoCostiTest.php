<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\FN03PianoCosti;

class FN03PianoCostiTest extends TestCase {
    /**
     * @var FN03PianoCosti
     */
    protected $entity;

    public function setUp() {
        $this->entity = new FN03PianoCosti();
    }

    public function testGetTracciato() {
        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setAnnoPiano(2000)
            ->setImpRealizzato(123.1)
            ->setImpDaRealizzare(321.05)
            ->setFlgCancellazione('S');

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 5);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], '2000');
        $this->assertSame($match[0][2], '123,10');
        $this->assertSame($match[0][3], '321,05');
        $this->assertEquals($match[0][4], 'S');
    }

    public function testId() {
        $this->assertNull($this->entity->getId());
    }

    /**
     * @dataProvider importoValidDataProvider
     */
    public function testImportoValid(?float $daRealizzare, ?float $realizzato, bool $atteso): void {
        $this->entity->setImpDaRealizzare($daRealizzare);
        $this->entity->setImpRealizzato($realizzato);

        $res = $this->entity->isImportoValid();

        $this->assertSame($atteso, $res);
    }

    public function importoValidDataProvider(): array {
        return [
            [null, null, true],
            [0.0, 0.0, true],
            [-1, null, false],
            [1, null, true],
            [null, -1, false],
            [null, 1, true],
            [32, 1, true],
            [-32, -1, false],
        ];
    }
}
