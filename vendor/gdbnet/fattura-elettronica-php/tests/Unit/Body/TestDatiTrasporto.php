<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\DatiTrasporto;
use Gdbnet\FatturaElettronica\Structures\Anagrafica;
use Gdbnet\FatturaElettronica\Structures\Fiscale;
use Gdbnet\FatturaElettronica\Structures\Indirizzo;
use PHPUnit\Framework\TestCase;

class TestDatiTrasporto extends TestCase
{
    public function test_class_fiscale_is_created()
    {
        $fiscale = new Fiscale('IT', '01234567890');

        $this->assertInstanceOf(Fiscale::class, $fiscale);

        return $fiscale;
    }

    /**
     * @depends test_class_fiscale_is_created
     * @return DatiTrasporto
     */
    public function test_class_is_created(Fiscale $fiscale)
    {
        $datiTrasporto = new DatiTrasporto();
        $datiTrasporto->setIdFiscaleIVA($fiscale);

        $this->assertInstanceOf(DatiTrasporto::class, $datiTrasporto);

        return $datiTrasporto;
    }

    public function test_class_anagrafica_is_created()
    {
        $anagrafica = new Anagrafica('test testing');

        $this->assertInstanceOf(Anagrafica::class, $anagrafica);

        return $anagrafica;
    }

    public function test_class_indirizzo_is_created()
    {
        $indirizzo = new Indirizzo('via test 2', 'Roma', '00100', 'IT');

        $this->assertInstanceOf(Indirizzo::class, $indirizzo);

        return $indirizzo;
    }

    /**
     * @depends test_class_is_created
     * @depends test_class_anagrafica_is_created
     * @depends test_class_indirizzo_is_created
     * @param DatiTrasporto $datiTrasporto
     * @return DatiTrasporto
     */
    public function test_class_returns_correct_array(
        DatiTrasporto $datiTrasporto,
        Anagrafica $anagrafica,
        Indirizzo $indirizzo
    )
    {
        $datiTrasporto->setAnagrafica($anagrafica)
            ->setCodiceFiscale('RSSMRA80A01H501U')
            ->setDescrizione('testing')
            ->setCausaleTrasporto('merce')
            ->setDataInizioTrasporto('2000-01-01')
            ->setDataOraConsegna('2000-01-01T00:00:00')
            ->setDataOraRitiro('2000-01-01T00:00:00')
            ->setIndirizzoResa($indirizzo)
            ->setMezzoTrasporto('auto')
            ->setNumeroColli(1)
            ->setNumeroLicenzaGuida('12345')
            ->setPesoLordo(11)
            ->setPesoNetto(10)
            ->setTipoResa('EXW')
            ->setUnitaMisuraPeso('KG');

        $this->assertInstanceOf(DatiTrasporto::class, $datiTrasporto);

        $array = $datiTrasporto->toArray();
        $this->assertArrayHasKey('DatiAnagraficiVettore', $array);
        $this->assertArrayHasKey('IdFiscaleIVA', $array['DatiAnagraficiVettore']);
        $this->assertArrayHasKey('CodiceFiscale', $array['DatiAnagraficiVettore']);
        $this->assertArrayHasKey('Anagrafica', $array['DatiAnagraficiVettore']);
        $this->assertArrayHasKey('NumeroLicenzaGuida', $array['DatiAnagraficiVettore']);
        $this->assertArrayHasKey('MezzoTrasporto', $array);
        $this->assertArrayHasKey('CausaleTrasporto', $array);
        $this->assertArrayHasKey('NumeroColli', $array);
        $this->assertArrayHasKey('Descrizione', $array);
        $this->assertArrayHasKey('UnitaMisuraPeso', $array);
        $this->assertArrayHasKey('PesoLordo', $array);
        $this->assertArrayHasKey('PesoNetto', $array);
        $this->assertArrayHasKey('DataOraRitiro', $array);
        $this->assertArrayHasKey('DataInizioTrasporto', $array);
        $this->assertArrayHasKey('TipoResa', $array);
        $this->assertArrayHasKey('IndirizzoResa', $array);
        $this->assertArrayHasKey('DataOraConsegna', $array);

        return $datiTrasporto;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DatiTrasporto $datiTrasporto
     */
    public function test_class_is_created_from_array(DatiTrasporto $datiTrasporto)
    {
        $array = $datiTrasporto->toArray();

        $datiTrasporto = DatiTrasporto::fromArray($array);

        $this->assertInstanceOf(DatiTrasporto::class, $datiTrasporto);
        $this->assertInstanceOf(Anagrafica::class, $datiTrasporto->getAnagrafica());
        $this->assertInstanceOf(Fiscale::class, $datiTrasporto->getIdFiscaleIVA());
        $this->assertEquals('RSSMRA80A01H501U', $datiTrasporto->getCodiceFiscale());
        $this->assertEquals('12345', $datiTrasporto->getNumeroLicenzaGuida());
        $this->assertEquals('auto', $datiTrasporto->getMezzoTrasporto());
        $this->assertEquals('merce', $datiTrasporto->getCausaleTrasporto());
        $this->assertEquals(1, $datiTrasporto->getNumeroColli());
        $this->assertEquals('testing', $datiTrasporto->getDescrizione());
        $this->assertEquals('KG', $datiTrasporto->getUnitaMisuraPeso());
        $this->assertEquals(11, $datiTrasporto->getPesoLordo());
        $this->assertEquals(10, $datiTrasporto->getPesoNetto());
        $this->assertEquals('2000-01-01T00:00:00', $datiTrasporto->getDataOraRitiro());
        $this->assertEquals('2000-01-01', $datiTrasporto->getDataInizioTrasporto());
        $this->assertEquals('EXW', $datiTrasporto->getTipoResa());
        $this->assertInstanceOf(Indirizzo::class, $datiTrasporto->getIndirizzoResa());
        $this->assertEquals('2000-01-01T00:00:00', $datiTrasporto->getDataOraConsegna());
    }
}