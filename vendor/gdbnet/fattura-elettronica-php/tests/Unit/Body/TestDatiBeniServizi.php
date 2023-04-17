<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\DatiBeniServizi;
use Gdbnet\FatturaElettronica\Body\DatiRiepilogo;
use Gdbnet\FatturaElettronica\Body\DettaglioLinea;
use PHPUnit\Framework\TestCase;

class TestDatiBeniServizi extends TestCase
{
    public function test_class_is_created()
    {
        $datiBeniServizi = new DatiBeniServizi();

        $this->assertInstanceOf(DatiBeniServizi::class, $datiBeniServizi);

        return $datiBeniServizi;
    }

    public function test_class_dati_riepilogo_is_created()
    {
        $datiRiepilogo = new DatiRiepilogo(22, 100, 22);

        $this->assertInstanceOf(DatiRiepilogo::class, $datiRiepilogo);

        return $datiRiepilogo;
    }

    public function test_class_dettaglio_linea_is_created()
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

    /**
     * @depends test_class_is_created
     * @depends test_class_dati_riepilogo_is_created
     * @depends test_class_dettaglio_linea_is_created
     * @param DatiBeniServizi $datiBeniServizi
     * @return DatiBeniServizi
     */
    public function test_class_returns_correct_array(
        DatiBeniServizi $datiBeniServizi,
        DatiRiepilogo $datiRiepilogo,
        DettaglioLinea $dettaglioLinea
    )
    {
        $datiBeniServizi->addDatiRiepilogo($datiRiepilogo)
            ->addDettaglioLinea($dettaglioLinea);

        $this->assertInstanceOf(DatiBeniServizi::class, $datiBeniServizi);

        $array = $datiBeniServizi->toArray();
        $this->assertArrayHasKey('DettaglioLinee', $array);
        $this->assertArrayHasKey('DatiRiepilogo', $array);

        return $datiBeniServizi;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DatiBeniServizi $datiBeniServizi
     */
    public function test_class_is_created_from_array(DatiBeniServizi $datiBeniServizi)
    {
        $array = $datiBeniServizi->toArray();

        $datiBeniServizi = DatiBeniServizi::fromArray($array);

        $this->assertInstanceOf(DatiBeniServizi::class, $datiBeniServizi);
        $this->assertIsArray($datiBeniServizi->getDatiRiepilogo());
        $this->assertIsArray($datiBeniServizi->getDettaglioLinee());
    }
}