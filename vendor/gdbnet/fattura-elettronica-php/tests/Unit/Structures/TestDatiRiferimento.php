<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Structures;

use Gdbnet\FatturaElettronica\Structures\DatiRiferimento;
use PHPUnit\Framework\TestCase;

class TestDatiRiferimento extends TestCase
{
    public function test_class_is_created()
    {
        $datiRiferimento = new DatiRiferimento('abc');

        $this->assertInstanceOf(DatiRiferimento::class, $datiRiferimento);

        return $datiRiferimento;
    }

    /**
     * @depends test_class_is_created
     * @return DatiRiferimento
     */
    public function test_class_returns_correct_array(DatiRiferimento $datiRiferimento)
    {
        $datiRiferimento->setRiferimentoNumeroLinea([1,2,3])
            ->setData('2000-01-01')
            ->setNumItem('123')
            ->setCodiceCommessaConvenzione('456')
            ->setCodiceCUP('789')
            ->setCodiceCIG('101112');

        $this->assertInstanceOf(DatiRiferimento::class, $datiRiferimento);

        $array = $datiRiferimento->toArray();
        $this->assertArrayHasKey('RiferimentoNumeroLinea', $array);
        $this->assertArrayHasKey('IdDocumento', $array);
        $this->assertArrayHasKey('Data', $array);
        $this->assertArrayHasKey('NumItem', $array);
        $this->assertArrayHasKey('CodiceCommessaConvenzione', $array);
        $this->assertArrayHasKey('CodiceCUP', $array);
        $this->assertArrayHasKey('CodiceCIG', $array);

        return $datiRiferimento;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DatiRiferimento $datiRiferimento
     */
    public function test_class_is_created_from_array(DatiRiferimento $datiRiferimento)
    {
        $array = $datiRiferimento->toArray();

        $datiRiferimento = DatiRiferimento::fromArray($array);

        $this->assertInstanceOf(DatiRiferimento::class, $datiRiferimento);
        $this->assertIsArray($datiRiferimento->getRiferimentoNumeroLinea());
        $this->assertEquals('abc', $datiRiferimento->getIdDocumento());
        $this->assertEquals('2000-01-01', $datiRiferimento->getData());
        $this->assertEquals('123', $datiRiferimento->getNumItem());
        $this->assertEquals('456', $datiRiferimento->getCodiceCommessaConvenzione());
        $this->assertEquals('789', $datiRiferimento->getCodiceCUP());
        $this->assertEquals('101112', $datiRiferimento->getCodiceCIG());
    }
}