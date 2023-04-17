<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\DatiDDT;
use PHPUnit\Framework\TestCase;

class TestDatiDDT extends TestCase
{
    public function test_class_is_created()
    {
        $datiDDT = new DatiDDT('N12345', '2000-01-01');

        $this->assertInstanceOf(DatiDDT::class, $datiDDT);

        return $datiDDT;
    }

    /**
     * @depends test_class_is_created
     * @param DatiDDT $datiDDT
     * @return DatiDDT
     */
    public function test_class_returns_correct_array(DatiDDT $datiDDT)
    {
        $datiDDT->addRiferimentoNumeroLinea(1);

        $this->assertInstanceOf(DatiDDT::class, $datiDDT);

        $array = $datiDDT->toArray();
        $this->assertArrayHasKey('NumeroDDT', $array);
        $this->assertArrayHasKey('DataDDT', $array);
        $this->assertArrayHasKey('RiferimentoNumeroLinea', $array);
        $this->assertIsArray($array['RiferimentoNumeroLinea']);

        return $datiDDT;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DatiDDT $datiDDT
     */
    public function test_class_is_created_from_array(DatiDDT $datiDDT)
    {
        $array = $datiDDT->toArray();

        $datiDDT = DatiDDT::fromArray($array);

        $this->assertInstanceOf(DatiDDT::class, $datiDDT);
        $this->assertEquals('N12345', $datiDDT->getNumeroDDT());
        $this->assertEquals('2000-01-01', $datiDDT->getDataDDT());
        $this->assertIsArray($datiDDT->getRiferimentoNumeroLinea());
    }
}