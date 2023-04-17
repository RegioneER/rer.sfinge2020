<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Structures;

use Gdbnet\FatturaElettronica\Structures\Fiscale;
use PHPUnit\Framework\TestCase;

class TestFiscale extends TestCase
{
    public function test_class_is_created()
    {
        $fiscale = new Fiscale('IT', '01234567890');

        $this->assertInstanceOf(Fiscale::class, $fiscale);

        return $fiscale;
    }

    /**
     * @depends test_class_is_created
     * @param Fiscale $fiscale
     * @return Fiscale
     */
    public function test_class_returns_correct_array(Fiscale $fiscale)
    {
        $this->assertInstanceOf(Fiscale::class, $fiscale);

        $array = $fiscale->toArray();
        $this->assertArrayHasKey('IdPaese', $array);
        $this->assertArrayHasKey('IdCodice', $array);

        return $fiscale;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param Fiscale $fiscale
     */
    public function test_class_is_created_from_array(Fiscale $fiscale)
    {
        $array = $fiscale->toArray();

        $fiscale = Fiscale::fromArray($array);

        $this->assertInstanceOf(Fiscale::class, $fiscale);
        $this->assertEquals('IT', $fiscale->getIdPaese());
        $this->assertEquals('01234567890', $fiscale->getIdCodice());
    }
}