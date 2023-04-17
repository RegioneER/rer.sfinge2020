<?php

namespace AttuazioneControlloBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use AttuazioneControlloBundle\Entity\IterProgetto;

class IterProgettoTest extends TestCase {
    /**
     * @dataProvider isDataFineEffettivaValidataProvider
     */
    public function testIsDataFineEffettivaValid(IterProgetto $iter, bool $risultatoAtteso, ?string $msg = null): void {
        $this->assertSame($risultatoAtteso, $iter->isDataFineEffettivaValid(), $msg);
    }

    public function isDataFineEffettivaValidataProvider(): array {
        return [
            [new IterProgetto(), true, 'Nessuna data definita'],
            [self::generateIter(null, '2017-12-01'), true, 'Data inizio non definita'],
            [self::generateIter('2015-12-01', null), true, 'Data fine effettiva non definita'],
            [self::generateIter('2020-12-12', '2000-01-01'), false, 'Data inizio > data fine'],
            [self::generateIter('2000-12-12', '2020-01-01'), true, "Caso felice"],
        ];
    }

    private static function generateIter(?string $dataInizio, ?string $dataFine): IterProgetto {
        $res = new IterProgetto();

        return $res->setDataInizioEffettiva(\is_null($dataInizio) ? null : new \DateTime($dataInizio))
            ->setDataFineEffettiva(\is_null($dataFine) ? null : new \DateTime($dataFine));
    }

    /**
     * @dataProvider datePrevisteValideDataProvider
     */
    public function testDatePrevisteValide(?\DateTime $dataInizio, ?\DateTime $dataFine, bool $esito): void {
        $iter = new IterProgetto();
        $iter->setDataInizioPrevista($dataInizio)
            ->setDataFinePrevista($dataFine);
        $valido = $iter->isDataFinePrevistaValid();

        $this->assertSame($esito, $valido);
    }

    public function datePrevisteValideDataProvider(): array {
        return [
            [null, null, true],
            [new \DateTime(), null, true],
            [null, new \DateTime(), true],
            [new \DateTime('2000-01-01'), new \DateTime(), true],
            [new \DateTime(), new \DateTime, true],
        ];

    }
}
