<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\DatiCassaPrevidenziale;
use PHPUnit\Framework\TestCase;

class TestDatiCassaPrevidenziale extends TestCase
{
    public function test_class_is_created()
    {
        $datiCassaPrevidenziale = new DatiCassaPrevidenziale(
            'TC01',
            10,
            50,
            22
        );

        $this->assertInstanceOf(DatiCassaPrevidenziale::class, $datiCassaPrevidenziale);

        return $datiCassaPrevidenziale;
    }

    /**
     * @depends test_class_is_created
     * @param DatiCassaPrevidenziale $datiCassaPrevidenziale
     * @return DatiCassaPrevidenziale
     */
    public function test_class_returns_correct_array(DatiCassaPrevidenziale $datiCassaPrevidenziale)
    {
        $datiCassaPrevidenziale->setImponibileCassa(100)
            ->setRitenuta(true)
            ->setNatura('N1')
            ->setRiferimentoAmministrazione('test');

        $this->assertInstanceOf(DatiCassaPrevidenziale::class, $datiCassaPrevidenziale);

        $array = $datiCassaPrevidenziale->toArray();
        $this->assertArrayHasKey('TipoCassa', $array);
        $this->assertArrayHasKey('AlCassa', $array);
        $this->assertArrayHasKey('ImportoContributoCassa', $array);
        $this->assertArrayHasKey('ImponibileCassa', $array);
        $this->assertArrayHasKey('AliquotaIVA', $array);
        $this->assertArrayHasKey('Ritenuta', $array);
        $this->assertArrayHasKey('Natura', $array);
        $this->assertArrayHasKey('RiferimentoAmministrazione', $array);

        return $datiCassaPrevidenziale;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DatiCassaPrevidenziale $datiCassaPrevidenziale
     */
    public function test_class_is_created_from_array(DatiCassaPrevidenziale $datiCassaPrevidenziale)
    {
        $array = $datiCassaPrevidenziale->toArray();

        $datiCassaPrevidenziale = DatiCassaPrevidenziale::fromArray($array);

        $this->assertInstanceOf(DatiCassaPrevidenziale::class, $datiCassaPrevidenziale);
        $this->assertEquals('TC01', $datiCassaPrevidenziale->getTipoCassa());
        $this->assertEquals(10, $datiCassaPrevidenziale->getAlCassa());
        $this->assertEquals(50, $datiCassaPrevidenziale->getImportoContributoCassa());
        $this->assertEquals(100, $datiCassaPrevidenziale->getImponibileCassa());
        $this->assertEquals(22, $datiCassaPrevidenziale->getAliquotaIVA());
        $this->assertTrue($datiCassaPrevidenziale->getRitenuta());
        $this->assertEquals('N1', $datiCassaPrevidenziale->getNatura());
        $this->assertEquals('test', $datiCassaPrevidenziale->getRiferimentoAmministrazione());
    }
}