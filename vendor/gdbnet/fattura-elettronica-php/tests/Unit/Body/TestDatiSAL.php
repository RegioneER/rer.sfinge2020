<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\DatiSAL;
use PHPUnit\Framework\TestCase;

class TestDatiSAL extends TestCase
{
    public function test_class_is_created()
    {
        $datiSAL = new DatiSAL(10);

        $this->assertInstanceOf(DatiSAL::class, $datiSAL);

        return $datiSAL;
    }

    /**
     * @depends test_class_is_created
     * @param DatiSAL $datiSAL
     * @return DatiSAL
     */
    public function test_class_returns_correct_array(DatiSAL $datiSAL)
    {
        $datiSAL->setRiferimentoFase(20);

        $this->assertInstanceOf(DatiSAL::class, $datiSAL);

        $array = $datiSAL->toArray();
        $this->assertArrayHasKey('RiferimentoFase', $array);

        return $datiSAL;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DatiSAL $datiSAL
     */
    public function test_class_is_created_from_array(DatiSAL $datiSAL)
    {
        $array = $datiSAL->toArray();

        $datiSAL = DatiSAL::fromArray($array);

        $this->assertInstanceOf(DatiSAL::class, $datiSAL);
        $this->assertEquals(20, $datiSAL->getRiferimentoFase());
    }
}