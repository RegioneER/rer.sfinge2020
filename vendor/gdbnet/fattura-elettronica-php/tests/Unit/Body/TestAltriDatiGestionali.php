<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\AltriDatiGestionali;
use PHPUnit\Framework\TestCase;

class TestAltriDatiGestionali extends TestCase
{
    public function test_class_is_created()
    {
        $altriDatiGestionali = new AltriDatiGestionali('test');

        $this->assertInstanceOf(AltriDatiGestionali::class, $altriDatiGestionali);

        return $altriDatiGestionali;
    }

    /**
     * @depends test_class_is_created
     * @param AltriDatiGestionali $altriDatiGestionali
     * @return AltriDatiGestionali
     */
    public function test_class_returns_correct_array(AltriDatiGestionali $altriDatiGestionali)
    {
        $altriDatiGestionali->setRiferimentoData('2000-01-01')
            ->setRiferimentoNumero(10)
            ->setRiferimentoTesto('text');

        $this->assertInstanceOf(AltriDatiGestionali::class, $altriDatiGestionali);

        $array = $altriDatiGestionali->toArray();
        $this->assertArrayHasKey('TipoDato', $array);
        $this->assertArrayHasKey('RiferimentoTesto', $array);
        $this->assertArrayHasKey('RiferimentoNumero', $array);
        $this->assertArrayHasKey('RiferimentoData', $array);

        return $altriDatiGestionali;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param AltriDatiGestionali $altriDatiGestionali
     */
    public function test_class_is_created_from_array(AltriDatiGestionali $altriDatiGestionali)
    {
        $array = $altriDatiGestionali->toArray();

        $altriDatiGestionali = AltriDatiGestionali::fromArray($array);

        $this->assertInstanceOf(AltriDatiGestionali::class, $altriDatiGestionali);
        $this->assertEquals('test', $altriDatiGestionali->getTipoDato());
        $this->assertEquals('2000-01-01', $altriDatiGestionali->getRiferimentoData());
        $this->assertEquals(10, $altriDatiGestionali->getRiferimentoNumero());
        $this->assertEquals('text', $altriDatiGestionali->getRiferimentoTesto());
    }
}