<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\CodiceArticolo;
use PHPUnit\Framework\TestCase;

class TestCodiceArticolo extends TestCase
{
    public function test_class_is_created()
    {
        $codiceArticolo = new CodiceArticolo('TIPO', 'test');

        $this->assertInstanceOf(CodiceArticolo::class, $codiceArticolo);

        return $codiceArticolo;
    }

    /**
     * @depends test_class_is_created
     * @param CodiceArticolo $codiceArticolo
     * @return CodiceArticolo
     */
    public function test_class_returns_correct_array(CodiceArticolo $codiceArticolo)
    {
        $codiceArticolo->setCodiceValore('testing');

        $this->assertInstanceOf(CodiceArticolo::class, $codiceArticolo);

        $array = $codiceArticolo->toArray();
        $this->assertArrayHasKey('CodiceTipo', $array);
        $this->assertArrayHasKey('CodiceValore', $array);

        return $codiceArticolo;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param CodiceArticolo $codiceArticolo
     */
    public function test_class_is_created_from_array(CodiceArticolo $codiceArticolo)
    {
        $array = $codiceArticolo->toArray();

        $codiceArticolo = CodiceArticolo::fromArray($array);

        $this->assertInstanceOf(CodiceArticolo::class, $codiceArticolo);
        $this->assertEquals('TIPO', $codiceArticolo->getCodiceTipo());
        $this->assertEquals('testing', $codiceArticolo->getCodiceValore());
    }
}