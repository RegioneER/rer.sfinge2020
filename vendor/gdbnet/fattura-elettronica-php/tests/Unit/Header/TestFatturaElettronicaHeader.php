<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Header;

use Gdbnet\FatturaElettronica\Header\CedentePrestatore;
use Gdbnet\FatturaElettronica\Header\CessionarioCommittente;
use Gdbnet\FatturaElettronica\Header\DatiTrasmissione;
use Gdbnet\FatturaElettronica\Header\FatturaElettronicaHeader;
use Gdbnet\FatturaElettronica\Header\RappresentanteFiscale;
use Gdbnet\FatturaElettronica\Header\TerzoIntermediarioOSoggettoEmittente;
use Gdbnet\FatturaElettronica\Structures\Anagrafica;
use Gdbnet\FatturaElettronica\Structures\Fiscale;
use Gdbnet\FatturaElettronica\Structures\Indirizzo;
use PHPUnit\Framework\TestCase;

class TestFatturaElettronicaHeader extends TestCase
{
    public function test_class_fiscale_is_created()
    {
        $fiscale = new Fiscale('IT', '01234567890');

        $this->assertInstanceOf(Fiscale::class, $fiscale);

        return $fiscale;
    }

    /**
     * @depends test_class_fiscale_is_created
     * @param Fiscale $fiscale
     * @return DatiTrasmissione
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_dati_trasmissione_is_created(Fiscale $fiscale)
    {
        $datiTrasmissione = new DatiTrasmissione(
            $fiscale,
            '00001',
            'FPR12',
            '0000000'
        );

        $this->assertInstanceOf(DatiTrasmissione::class, $datiTrasmissione);

        return $datiTrasmissione;
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
     * @depends test_class_fiscale_is_created
     * @depends test_class_anagrafica_is_created
     * @depends test_class_indirizzo_is_created
     */
    public function test_class_cedente_prestatore_is_created($fiscale, $anagrafica, $indirizzo)
    {
        $cedentePrestatore = new CedentePrestatore($fiscale, $anagrafica, $indirizzo, 'RF01');

        $this->assertInstanceOf(CedentePrestatore::class, $cedentePrestatore);

        return $cedentePrestatore;
    }

    /**
     * @depends test_class_anagrafica_is_created
     * @depends test_class_indirizzo_is_created
     * @depends test_class_fiscale_is_created
     */
    public function test_class_cessionario_committente_is_created($anagrafica, $indirizzo, $fiscale)
    {
        $cessionarioCommittente = new CessionarioCommittente($anagrafica, $indirizzo, $fiscale);

        $this->assertInstanceOf(CessionarioCommittente::class, $cessionarioCommittente);

        return $cessionarioCommittente;
    }

    /**
     * @depends test_class_dati_trasmissione_is_created
     * @depends test_class_cedente_prestatore_is_created
     * @depends test_class_cessionario_committente_is_created
     * @return FatturaElettronicaHeader
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_is_created(
        DatiTrasmissione $datiTrasmissione,
        CedentePrestatore $cedentePrestatore,
        CessionarioCommittente $cessionarioCommittente
    )
    {
        $fatturaElettronicaHeader = new FatturaElettronicaHeader(
            $datiTrasmissione,
            $cedentePrestatore,
            $cessionarioCommittente
        );

        $this->assertInstanceOf(FatturaElettronicaHeader::class, $fatturaElettronicaHeader);

        return $fatturaElettronicaHeader;
    }

    /**
     * @depends test_class_fiscale_is_created
     * @depends test_class_anagrafica_is_created
     * @return RappresentanteFiscale
     */
    public function test_class_rapresentante_fiscale_is_created(Fiscale $fiscale, Anagrafica $anagrafica)
    {
        $rappresentanteFiscale = new RappresentanteFiscale($fiscale, $anagrafica);

        $this->assertInstanceOf(RappresentanteFiscale::class, $rappresentanteFiscale);

        return $rappresentanteFiscale;
    }

    /**
     * @depends test_class_anagrafica_is_created
     * @param Anagrafica $anagrafica
     * @return TerzoIntermediarioOSoggettoEmittente
     */
    public function test_class_terzo_intermediario_o_soggetto_emittente_is_created(Anagrafica $anagrafica)
    {
        $terzoIntermediarioOSoggettoEmittente = new TerzoIntermediarioOSoggettoEmittente($anagrafica);

        $this->assertInstanceOf(TerzoIntermediarioOSoggettoEmittente::class, $terzoIntermediarioOSoggettoEmittente);

        return $terzoIntermediarioOSoggettoEmittente;
    }

    /**
     * @depends test_class_is_created
     * @depends test_class_rapresentante_fiscale_is_created
     * @depends test_class_terzo_intermediario_o_soggetto_emittente_is_created
     * @return FatturaElettronicaHeader
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_returns_correct_array(
        FatturaElettronicaHeader $fatturaElettronicaHeader,
        RappresentanteFiscale $rappresentanteFiscale,
        TerzoIntermediarioOSoggettoEmittente $terzoIntermediarioOSoggettoEmittente
    )
    {
        $fatturaElettronicaHeader->setRappresentanteFiscale($rappresentanteFiscale)
            ->setTerzoIntermediarioOSoggettoEmittente($terzoIntermediarioOSoggettoEmittente)
            ->setSoggettoEmittente('TZ');

        $this->assertInstanceOf(FatturaElettronicaHeader::class, $fatturaElettronicaHeader);

        $array = $fatturaElettronicaHeader->toArray();
        $this->assertArrayHasKey('DatiTrasmissione', $array);
        $this->assertArrayHasKey('CedentePrestatore', $array);
        $this->assertArrayHasKey('RappresentanteFiscale', $array);
        $this->assertArrayHasKey('CessionarioCommittente', $array);
        $this->assertArrayHasKey('TerzoIntermediarioOSoggettoEmittente', $array);
        $this->assertArrayHasKey('SoggettoEmittente', $array);

        return $fatturaElettronicaHeader;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param FatturaElettronicaHeader $fatturaElettronicaHeader
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_is_created_from_array(FatturaElettronicaHeader $fatturaElettronicaHeader)
    {
        $array = $fatturaElettronicaHeader->toArray();

        $fatturaElettronicaHeader = FatturaElettronicaHeader::fromArray($array);

        $this->assertInstanceOf(FatturaElettronicaHeader::class, $fatturaElettronicaHeader);
        $this->assertInstanceOf(DatiTrasmissione::class, $fatturaElettronicaHeader->getDatiTrasmissione());
        $this->assertInstanceOf(CedentePrestatore::class, $fatturaElettronicaHeader->getCedentePrestatore());
        $this->assertInstanceOf(CessionarioCommittente::class, $fatturaElettronicaHeader->getCessionarioCommittente());
        $this->assertInstanceOf(RappresentanteFiscale::class, $fatturaElettronicaHeader->getRappresentanteFiscale());
        $this->assertInstanceOf(TerzoIntermediarioOSoggettoEmittente::class, $fatturaElettronicaHeader->getTerzoIntermediarioOSoggettoEmittente());
        $this->assertEquals('TZ', $fatturaElettronicaHeader->getSoggettoEmittente());
    }
}