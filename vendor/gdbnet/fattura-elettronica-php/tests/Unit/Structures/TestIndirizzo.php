<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Structures;

use Gdbnet\FatturaElettronica\Structures\Indirizzo;
use PHPUnit\Framework\TestCase;

class TestIndirizzo extends TestCase
{
    public function test_class_is_created()
    {
        $indirizzo = new Indirizzo('via test 2', 'Roma', '00100', 'IT');

        $this->assertInstanceOf(Indirizzo::class, $indirizzo);

        return $indirizzo;
    }

    /**
     * @depends test_class_is_created
     * @return Indirizzo
     */
    public function test_class_returns_correct_array(Indirizzo $indirizzo)
    {
        $indirizzo->setProvincia('RM');

        $this->assertInstanceOf(Indirizzo::class, $indirizzo);

        $array = $indirizzo->toArray();
        $this->assertArrayHasKey('Indirizzo', $array);
        $this->assertArrayHasKey('CAP', $array);
        $this->assertArrayHasKey('Comune', $array);
        $this->assertArrayHasKey('Provincia', $array);
        $this->assertArrayHasKey('Nazione', $array);

        return $indirizzo;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param Indirizzo $indirizzo
     */
    public function test_class_is_created_from_array(Indirizzo $indirizzo)
    {
        $array = $indirizzo->toArray();

        $indirizzo = Indirizzo::fromArray($array);

        $this->assertInstanceOf(Indirizzo::class, $indirizzo);
        $this->assertEquals('via test 2', $indirizzo->getIndirizzo());
        $this->assertEquals('00100', $indirizzo->getCAP());
        $this->assertEquals('Roma', $indirizzo->getComune());
        $this->assertEquals('RM', $indirizzo->getProvincia());
        $this->assertEquals('IT', $indirizzo->getNazione());
    }
}