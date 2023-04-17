<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Header;

use Gdbnet\FatturaElettronica\Header\TerzoIntermediarioOSoggettoEmittente;
use Gdbnet\FatturaElettronica\Structures\Anagrafica;
use Gdbnet\FatturaElettronica\Structures\Fiscale;
use PHPUnit\Framework\TestCase;

class TestTerzoIntermediarioOSoggettoEmittente extends TestCase
{
    public function test_class_fiscale_is_created()
    {
        $fiscale = new Fiscale('IT', '01234567890');

        $this->assertInstanceOf(Fiscale::class, $fiscale);

        return $fiscale;
    }

    public function test_class_anagrafica_is_created()
    {
        $anagrafica = new Anagrafica('test testing');

        $this->assertInstanceOf(Anagrafica::class, $anagrafica);

        return $anagrafica;
    }

    /**
     * @depends test_class_anagrafica_is_created
     * @param Anagrafica $anagrafica
     * @return TerzoIntermediarioOSoggettoEmittente
     */
    public function test_class_is_created(Anagrafica $anagrafica)
    {
        $terzoIntermediarioOSoggettoEmittente = new TerzoIntermediarioOSoggettoEmittente($anagrafica);

        $this->assertInstanceOf(TerzoIntermediarioOSoggettoEmittente::class, $terzoIntermediarioOSoggettoEmittente);

        return $terzoIntermediarioOSoggettoEmittente;
    }

    /**
     * @depends test_class_is_created
     * @depends test_class_fiscale_is_created
     */
    public function test_class_returns_correct_array(
        TerzoIntermediarioOSoggettoEmittente $terzoIntermediarioOSoggettoEmittente,
        Fiscale $fiscale
    )
    {
        $terzoIntermediarioOSoggettoEmittente->setIdFiscaleIVA($fiscale)
            ->setCodiceFiscale('RSSMRA80A01H501U');

        $this->assertInstanceOf(TerzoIntermediarioOSoggettoEmittente::class, $terzoIntermediarioOSoggettoEmittente);

        $array = $terzoIntermediarioOSoggettoEmittente->toArray();
        $this->assertArrayHasKey('DatiAnagrafici', $array);
        $this->assertArrayHasKey('IdFiscaleIVA', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('CodiceFiscale', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('Anagrafica', $array['DatiAnagrafici']);

        return $terzoIntermediarioOSoggettoEmittente;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param TerzoIntermediarioOSoggettoEmittente $terzoIntermediarioOSoggettoEmittente
     */
    public function test_class_is_created_from_array(TerzoIntermediarioOSoggettoEmittente $terzoIntermediarioOSoggettoEmittente)
    {
        $array = $terzoIntermediarioOSoggettoEmittente->toArray();

        $terzoIntermediarioOSoggettoEmittente = TerzoIntermediarioOSoggettoEmittente::fromArray($array);

        $this->assertInstanceOf(TerzoIntermediarioOSoggettoEmittente::class, $terzoIntermediarioOSoggettoEmittente);
        $this->assertInstanceOf(Anagrafica::class, $terzoIntermediarioOSoggettoEmittente->getAnagrafica());
        $this->assertInstanceOf(Fiscale::class, $terzoIntermediarioOSoggettoEmittente->getIdFiscaleIVA());
        $this->assertEquals('RSSMRA80A01H501U', $terzoIntermediarioOSoggettoEmittente->getCodiceFiscale());
    }
}