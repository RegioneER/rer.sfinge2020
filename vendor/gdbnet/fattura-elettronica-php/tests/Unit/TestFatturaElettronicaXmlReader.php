<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit;

use Gdbnet\FatturaElettronica\FatturaElettronica;
use Gdbnet\FatturaElettronica\FatturaElettronicaXmlReader;
use Gdbnet\FatturaElettronica\Header\FatturaElettronicaHeader;
use PHPUnit\Framework\TestCase;

class TestFatturaElettronicaXmlReader extends TestCase
{
    public function test_reader_class_is_created()
    {
        $xml = file_get_contents(__DIR__ . '/../samples/IT01234567890_FPA02.xml');
        $reader = new FatturaElettronicaXmlReader();
        $fattura = $reader->decodeXml($xml);

        $this->assertInstanceOf(FatturaElettronica::class, $fattura);
        $this->assertEquals('AAAAAA', $fattura->getCodiceDestinatario());
        $this->assertEquals('2017-01-18', $fattura->getDataFattura());
        $this->assertEquals(null, $fattura->getImportoTotaleDocumento());
        $this->assertEquals(1, count($fattura->getBodys()));
        $this->assertInstanceOf(FatturaElettronicaHeader::class, $fattura->getHeader());
        $this->assertEquals('123', $fattura->getNumeroFattura());
        $this->assertEquals('01234567890', $fattura->getPivaCedente()->getIdCodice());
        $this->assertEquals(null, $fattura->getPivaCommittente());
        $this->assertEquals('FPA12', $fattura->getVersione());
    }
}