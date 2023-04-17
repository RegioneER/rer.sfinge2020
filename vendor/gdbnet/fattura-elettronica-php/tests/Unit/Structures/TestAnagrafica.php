<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Structures;

use Gdbnet\FatturaElettronica\Structures\Anagrafica;
use PHPUnit\Framework\TestCase;

class TestAnagrafica extends TestCase
{
    public function test_class_is_created()
    {
        $anagrafica = new Anagrafica('test testing');

        $this->assertInstanceOf(Anagrafica::class, $anagrafica);

        return $anagrafica;
    }

    /**
     * @depends test_class_is_created
     * @return Anagrafica
     */
    public function test_class_returns_correct_array(Anagrafica $anagrafica)
    {
        $anagrafica->setNome('test')
            ->setCognome('testing')
            ->setTitolo('tester')
            ->setCodEORI('12345');

        $this->assertInstanceOf(Anagrafica::class, $anagrafica);

        $array = $anagrafica->toArray();
        $this->assertArrayHasKey('Denominazione', $array);
        $this->assertArrayHasKey('Nome', $array);
        $this->assertArrayHasKey('Cognome', $array);
        $this->assertArrayHasKey('Titolo', $array);
        $this->assertArrayHasKey('CodEORI', $array);

        return $anagrafica;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param $anagrafica
     */
    public function test_class_is_created_from_array(Anagrafica $anagrafica)
    {
        $array = $anagrafica->toArray();

        $anagrafica = Anagrafica::fromArray($array);

        $this->assertInstanceOf(Anagrafica::class, $anagrafica);
        $this->assertEquals('test testing', $anagrafica->getDenominazione());
        $this->assertEquals('test', $anagrafica->getNome());
        $this->assertEquals('testing', $anagrafica->getCognome());
        $this->assertEquals('tester', $anagrafica->getTitolo());
        $this->assertEquals('12345', $anagrafica->getCodEORI());
    }
}