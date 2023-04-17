<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Structures;

use Gdbnet\FatturaElettronica\Structures\ScontoMaggiorazione;
use PHPUnit\Framework\TestCase;

class TestscontoMaggiorazione extends TestCase
{
    public function test_class_is_created()
    {
        $scontoMaggiorazione = new ScontoMaggiorazione('SC', 10);

        $this->assertInstanceOf(ScontoMaggiorazione::class, $scontoMaggiorazione);

        return $scontoMaggiorazione;
    }

    /**
     * @depends test_class_is_created
     * @return ScontoMaggiorazione
     */
    public function test_class_returns_correct_array(ScontoMaggiorazione $scontoMaggiorazione)
    {
        $scontoMaggiorazione->setImporto(52.3);

        $this->assertInstanceOf(ScontoMaggiorazione::class, $scontoMaggiorazione);

        $array = $scontoMaggiorazione->toArray();
        $this->assertArrayHasKey('Tipo', $array);
        $this->assertArrayHasKey('Percentuale', $array);
        $this->assertArrayHasKey('Importo', $array);

        return $scontoMaggiorazione;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param ScontoMaggiorazione $scontoMaggiorazione
     */
    public function test_class_is_created_from_array(ScontoMaggiorazione $scontoMaggiorazione)
    {
        $array = $scontoMaggiorazione->toArray();

        $scontoMaggiorazione = ScontoMaggiorazione::fromArray($array);

        $this->assertInstanceOf(ScontoMaggiorazione::class, $scontoMaggiorazione);
        $this->assertEquals('SC', $scontoMaggiorazione->getTipo());
        $this->assertEquals(10, $scontoMaggiorazione->getPercentuale());
        $this->assertEquals(52.3, $scontoMaggiorazione->getImporto());
    }
}