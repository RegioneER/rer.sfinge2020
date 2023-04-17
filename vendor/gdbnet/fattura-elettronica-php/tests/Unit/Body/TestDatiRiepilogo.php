<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\DatiRiepilogo;
use PHPUnit\Framework\TestCase;

class TestDatiRiepilogo extends TestCase
{
    public function test_class_is_created()
    {
        $datiRiepilogo = new DatiRiepilogo(22, 100, 22);

        $this->assertInstanceOf(DatiRiepilogo::class, $datiRiepilogo);

        return $datiRiepilogo;
    }

    /**
     * @depends test_class_is_created
     * @param DatiRiepilogo $datiRiepilogo
     * @return DatiRiepilogo
     */
    public function test_class_returns_correct_array(DatiRiepilogo $datiRiepilogo)
    {
        $datiRiepilogo->setArrotondamento(5)
            ->setNatura('N1')
            ->setRiferimentoNormativo('test')
            ->setEsigibilitaIVA('S')
            ->setSpeseAccessorie(10);

        $this->assertInstanceOf(DatiRiepilogo::class, $datiRiepilogo);

        $array = $datiRiepilogo->toArray();
        $this->assertArrayHasKey('AliquotaIVA', $array);
        $this->assertArrayHasKey('Natura', $array);
        $this->assertArrayHasKey('SpeseAccessorie', $array);
        $this->assertArrayHasKey('Arrotondamento', $array);
        $this->assertArrayHasKey('ImponibileImporto', $array);
        $this->assertArrayHasKey('Imposta', $array);
        $this->assertArrayHasKey('EsigibilitaIVA', $array);
        $this->assertArrayHasKey('RiferimentoNormativo', $array);

        return $datiRiepilogo;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DatiRiepilogo $datiRiepilogo
     */
    public function test_class_is_created_from_array(DatiRiepilogo $datiRiepilogo)
    {
        $array = $datiRiepilogo->toArray();

        $datiRiepilogo = DatiRiepilogo::fromArray($array);

        $this->assertInstanceOf(DatiRiepilogo::class, $datiRiepilogo);
        $this->assertEquals(22, $datiRiepilogo->getAliquotaIVA());
        $this->assertEquals('N1', $datiRiepilogo->getNatura());
        $this->assertEquals(10, $datiRiepilogo->getSpeseAccessorie());
        $this->assertEquals(5, $datiRiepilogo->getArrotondamento());
        $this->assertEquals(100, $datiRiepilogo->getImponibileImporto());
        $this->assertEquals(22, $datiRiepilogo->getImposta());
        $this->assertEquals('S', $datiRiepilogo->getEsigibilitaIVA());
        $this->assertEquals('test', $datiRiepilogo->getRiferimentoNormativo());
    }
}