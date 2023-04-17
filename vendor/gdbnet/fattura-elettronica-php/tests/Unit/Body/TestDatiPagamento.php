<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\DatiPagamento;
use Gdbnet\FatturaElettronica\Body\DettaglioPagamento;
use PHPUnit\Framework\TestCase;

class TestDatiPagamento extends TestCase
{
    public function test_class_is_created()
    {
        $datiPagamento = new DatiPagamento('TP01');

        $this->assertInstanceOf(DatiPagamento::class, $datiPagamento);

        return $datiPagamento;
    }

    public function test_class_dettaglio_pagamento_is_created()
    {
        $dettaglioPagamento = new DettaglioPagamento('MP01', 10);

        $this->assertInstanceOf(DettaglioPagamento::class, $dettaglioPagamento);

        return $dettaglioPagamento;
    }

    /**
     * @depends test_class_is_created
     * @depends test_class_dettaglio_pagamento_is_created
     * @param DatiPagamento $datiPagamento
     * @param DettaglioPagamento $dettaglioPagamento
     * @return DatiPagamento
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_returns_correct_array(
        DatiPagamento $datiPagamento,
        DettaglioPagamento $dettaglioPagamento
    )
    {
        $datiPagamento->addDettaglioPagamento($dettaglioPagamento);

        $this->assertInstanceOf(DatiPagamento::class, $datiPagamento);

        $array = $datiPagamento->toArray();
        $this->assertArrayHasKey('CondizioniPagamento', $array);
        $this->assertArrayHasKey('DettaglioPagamento', $array);
        $this->assertIsArray($array['DettaglioPagamento']);

        return $datiPagamento;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DatiPagamento $datiPagamento
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_is_created_from_array(DatiPagamento $datiPagamento)
    {
        $array = $datiPagamento->toArray();

        $datiPagamento = DatiPagamento::fromArray($array);

        $this->assertInstanceOf(DatiPagamento::class, $datiPagamento);
        $this->assertEquals('TP01', $datiPagamento->getCondizioniPagamento());
        $this->assertIsArray($datiPagamento->getDettaglioPagamento());
    }
}