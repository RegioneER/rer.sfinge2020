<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\DatiVeicoli;
use PHPUnit\Framework\TestCase;

class TestDatiVeicoli extends TestCase
{
    public function test_class_is_created()
    {
        $datiVeicoli = new DatiVeicoli('2000-01-01', '10km');

        $this->assertInstanceOf(DatiVeicoli::class, $datiVeicoli);

        return $datiVeicoli;
    }

    /**
     * @depends test_class_is_created
     * @param DatiVeicoli $datiVeicoli
     * @return DatiVeicoli
     */
    public function test_class_returns_correct_array(DatiVeicoli $datiVeicoli)
    {
        $datiVeicoli->setTotalePercorso('20km');

        $this->assertInstanceOf(DatiVeicoli::class, $datiVeicoli);

        $array = $datiVeicoli->toArray();
        $this->assertArrayHasKey('Data', $array);
        $this->assertArrayHasKey('TotalePercorso', $array);

        return $datiVeicoli;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DatiVeicoli $datiVeicoli
     */
    public function test_class_is_created_from_array(DatiVeicoli $datiVeicoli)
    {
        $array = $datiVeicoli->toArray();

        $datiVeicoli = DatiVeicoli::fromArray($array);

        $this->assertInstanceOf(DatiVeicoli::class, $datiVeicoli);
        $this->assertEquals('2000-01-01', $datiVeicoli->getData());
        $this->assertEquals('20km', $datiVeicoli->getTotalePercorso());
    }
}