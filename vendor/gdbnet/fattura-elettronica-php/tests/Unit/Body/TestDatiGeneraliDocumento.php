<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\DatiCassaPrevidenziale;
use Gdbnet\FatturaElettronica\Body\DatiGeneraliDocumento;
use Gdbnet\FatturaElettronica\Structures\ScontoMaggiorazione;
use PHPUnit\Framework\TestCase;

class TestDatiGeneraliDocumento extends TestCase
{
    public function test_class_is_created()
    {
        $datiGeneraliDocumento = new DatiGeneraliDocumento(
            'TD01',
            '2000-01-01',
            '00001'
        );

        $this->assertInstanceOf(DatiGeneraliDocumento::class, $datiGeneraliDocumento);

        return $datiGeneraliDocumento;
    }

    public function test_class_sconto_is_created()
    {
        $scontoMaggiorazione = new ScontoMaggiorazione('SC', 10);

        $this->assertInstanceOf(ScontoMaggiorazione::class, $scontoMaggiorazione);

        return $scontoMaggiorazione;
    }

    public function test_class_dati_cassa_is_created()
    {
        $datiCassaPrevidenziale = new DatiCassaPrevidenziale(
            'TC01',
            10,
            50,
            22
        );

        $this->assertInstanceOf(DatiCassaPrevidenziale::class, $datiCassaPrevidenziale);

        return $datiCassaPrevidenziale;
    }

    /**
     * @depends test_class_is_created
     * @depends test_class_sconto_is_created
     * @depends test_class_dati_cassa_is_created
     * @param DatiGeneraliDocumento $datiGeneraliDocumento
     * @param ScontoMaggiorazione $scontoMaggiorazione
     * @param DatiCassaPrevidenziale $datiCassaPrevidenziale
     * @return DatiGeneraliDocumento
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_returns_correct_array(
        DatiGeneraliDocumento $datiGeneraliDocumento,
        ScontoMaggiorazione $scontoMaggiorazione,
        DatiCassaPrevidenziale $datiCassaPrevidenziale
    )
    {
        $datiGeneraliDocumento->setArrotondamento(5)
            ->setAliquotaRitenuta(22)
            ->setArt73(true)
            ->setBolloVirtuale(true)
            ->setImportoBollo(10)
            ->setTipoRitenuta('RT01')
            ->setImportoRitenuta(10)
            ->setCausalePagamento('test')
            ->addScontoMaggiorazione($scontoMaggiorazione)
            ->addCausale('fattura')
            ->setImportoTotaleDocumento(100)
            ->addDatiCassaPrevidenziale($datiCassaPrevidenziale);

        $this->assertInstanceOf(DatiGeneraliDocumento::class, $datiGeneraliDocumento);

        $array = $datiGeneraliDocumento->toArray();
        $this->assertArrayHasKey('TipoDocumento', $array);
        $this->assertArrayHasKey('Divisa', $array);
        $this->assertArrayHasKey('Data', $array);
        $this->assertArrayHasKey('Numero', $array);
        $this->assertArrayHasKey('DatiRitenuta', $array);
        $this->assertArrayHasKey('DatiBollo', $array);
        $this->assertArrayHasKey('DatiCassaPrevidenziale', $array);
        $this->assertArrayHasKey('ScontoMaggiorazione', $array);
        $this->assertArrayHasKey('ImportoTotaleDocumento', $array);
        $this->assertArrayHasKey('Arrotondamento', $array);
        $this->assertArrayHasKey('Causale', $array);
        $this->assertArrayHasKey('Art73', $array);

        return $datiGeneraliDocumento;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DatiGeneraliDocumento $datiGeneraliDocumento
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_is_created_from_array(DatiGeneraliDocumento $datiGeneraliDocumento)
    {
        $array = $datiGeneraliDocumento->toArray();

        $datiGeneraliDocumento = DatiGeneraliDocumento::fromArray($array);

        $this->assertInstanceOf(DatiGeneraliDocumento::class, $datiGeneraliDocumento);
        $this->assertEquals('TD01', $datiGeneraliDocumento->getTipoDocumento());
        $this->assertEquals('EUR', $datiGeneraliDocumento->getDivisa());
        $this->assertEquals('2000-01-01', $datiGeneraliDocumento->getData());
        $this->assertEquals('00001', $datiGeneraliDocumento->getNumero());
        $this->assertEquals('RT01', $datiGeneraliDocumento->getTipoRitenuta());
        $this->assertEquals(10, $datiGeneraliDocumento->getImportoRitenuta());
        $this->assertEquals(22, $datiGeneraliDocumento->getAliquotaRitenuta());
        $this->assertEquals('test', $datiGeneraliDocumento->getCausalePagamento());
        $this->assertTrue($datiGeneraliDocumento->getBolloVirtuale());
        $this->assertEquals(10, $datiGeneraliDocumento->getImportoBollo());
        $this->assertIsArray($datiGeneraliDocumento->getDatiCassaPrevidenziale());
        $this->assertIsArray($datiGeneraliDocumento->getScontoMaggiorazione());
        $this->assertEquals(100, $datiGeneraliDocumento->getImportoTotaleDocumento());
        $this->assertEquals(5, $datiGeneraliDocumento->getArrotondamento());
        $this->assertIsArray($datiGeneraliDocumento->getCausale());
        $this->assertTrue($datiGeneraliDocumento->getArt73());
    }
}