<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\AltriDatiGestionali;
use Gdbnet\FatturaElettronica\Body\CodiceArticolo;
use Gdbnet\FatturaElettronica\Body\DettaglioLinea;
use Gdbnet\FatturaElettronica\Structures\ScontoMaggiorazione;
use PHPUnit\Framework\TestCase;

class TestDettaglioLinea extends TestCase
{
    public function test_class_is_created()
    {
        $dettaglioLinea = new DettaglioLinea(
            1,
            'test',
            10,
            10,
            22
        );

        $this->assertInstanceOf(DettaglioLinea::class, $dettaglioLinea);

        return $dettaglioLinea;
    }

    public function test_class_sconto_maggiorazione_is_created()
    {
        $scontoMaggiorazione = new ScontoMaggiorazione('SC', 10);

        $this->assertInstanceOf(ScontoMaggiorazione::class, $scontoMaggiorazione);

        return $scontoMaggiorazione;
    }

    public function test_class_codice_articolo_is_created()
    {
        $codiceArticolo = new CodiceArticolo('TIPO', 'test');

        $this->assertInstanceOf(CodiceArticolo::class, $codiceArticolo);

        return $codiceArticolo;
    }

    public function test_class_altri_dati_gestionali_is_created()
    {
        $altriDatiGestionali = new AltriDatiGestionali('test');

        $this->assertInstanceOf(AltriDatiGestionali::class, $altriDatiGestionali);

        return $altriDatiGestionali;
    }

    /**
     * @depends test_class_is_created
     * @depends test_class_sconto_maggiorazione_is_created
     * @depends test_class_codice_articolo_is_created
     * @depends test_class_altri_dati_gestionali_is_created
     * @return DettaglioLinea
     */
    public function test_class_returns_correct_array(
        DettaglioLinea $dettaglioLinea,
        ScontoMaggiorazione $scontoMaggiorazione,
        CodiceArticolo $codiceArticolo,
        AltriDatiGestionali $altriDatiGestionali
    )
    {
        $dettaglioLinea->setNatura('N1')
            ->setRiferimentoAmministrazione('test')
            ->setRitenuta(true)
            ->addAltriDatiGestionali($altriDatiGestionali)
            ->addScontoMaggiorazione($scontoMaggiorazione)
            ->addCodiceArticolo($codiceArticolo)
            ->setDataFinePeriodo('2000-01-01')
            ->setDataInizioPeriodo('2000-01-01')
            ->setTipoCessionePrestazione('testing')
            ->setQuantita(1)
            ->setUnitaMisura('UM');

        $this->assertInstanceOf(DettaglioLinea::class, $dettaglioLinea);

        $array = $dettaglioLinea->toArray();
        $this->assertArrayHasKey('NumeroLinea', $array);
        $this->assertArrayHasKey('TipoCessionePrestazione', $array);
        $this->assertArrayHasKey('CodiceArticolo', $array);
        $this->assertArrayHasKey('Descrizione', $array);
        $this->assertArrayHasKey('Quantita', $array);
        $this->assertArrayHasKey('UnitaMisura', $array);
        $this->assertArrayHasKey('DataInizioPeriodo', $array);
        $this->assertArrayHasKey('DataFinePeriodo', $array);
        $this->assertArrayHasKey('PrezzoUnitario', $array);
        $this->assertArrayHasKey('ScontoMaggiorazione', $array);
        $this->assertArrayHasKey('PrezzoTotale', $array);
        $this->assertArrayHasKey('AliquotaIVA', $array);
        $this->assertArrayHasKey('Ritenuta', $array);
        $this->assertArrayHasKey('Natura', $array);
        $this->assertArrayHasKey('RiferimentoAmministrazione', $array);
        $this->assertArrayHasKey('AltriDatiGestionali', $array);

        return $dettaglioLinea;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DettaglioLinea $dettaglioLinea
     */
    public function test_class_is_created_from_array(DettaglioLinea $dettaglioLinea)
    {
        $array = $dettaglioLinea->toArray();

        $dettaglioLinea = DettaglioLinea::fromArray($array);

        $this->assertInstanceOf(DettaglioLinea::class, $dettaglioLinea);
        $this->assertEquals(1, $dettaglioLinea->getNumeroLinea());
        $this->assertEquals('testing', $dettaglioLinea->getTipoCessionePrestazione());
        $this->assertIsArray($dettaglioLinea->getCodiceArticolo());
        $this->assertEquals('test', $dettaglioLinea->getDescrizione());
        $this->assertEquals(1, $dettaglioLinea->getQuantita());
        $this->assertEquals('UM', $dettaglioLinea->getUnitaMisura());
        $this->assertEquals('2000-01-01', $dettaglioLinea->getDataInizioPeriodo());
        $this->assertEquals('2000-01-01', $dettaglioLinea->getDataFinePeriodo());
        $this->assertEquals(10, $dettaglioLinea->getPrezzoUnitario());
        $this->assertIsArray($dettaglioLinea->getScontoMaggiorazione());
        $this->assertEquals(10, $dettaglioLinea->getPrezzoTotale());
        $this->assertEquals(22, $dettaglioLinea->getAliquotaIVA());
        $this->assertTrue($dettaglioLinea->getRitenuta());
        $this->assertEquals('N1', $dettaglioLinea->getNatura());
        $this->assertEquals('test', $dettaglioLinea->getRiferimentoAmministrazione());
        $this->assertIsArray($dettaglioLinea->getAltriDatiGestionali());
    }
}