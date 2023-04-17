<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\DatiDDT;
use Gdbnet\FatturaElettronica\Body\DatiGenerali;
use Gdbnet\FatturaElettronica\Body\DatiGeneraliDocumento;
use Gdbnet\FatturaElettronica\Body\DatiSAL;
use Gdbnet\FatturaElettronica\Body\DatiTrasporto;
use Gdbnet\FatturaElettronica\Structures\DatiRiferimento;
use Gdbnet\FatturaElettronica\Structures\Fiscale;
use PHPUnit\Framework\TestCase;

class TestDatiGenerali extends TestCase
{
    public function test_class_dati_generali_documento_is_created()
    {
        $datiGeneraliDocumento = new DatiGeneraliDocumento(
            'TD01',
            '2000-01-01',
            '00001'
        );

        $this->assertInstanceOf(DatiGeneraliDocumento::class, $datiGeneraliDocumento);

        return $datiGeneraliDocumento;
    }

    /**
     * @depends test_class_dati_generali_documento_is_created
     * @return DatiGenerali
     */
    public function test_class_is_created(DatiGeneraliDocumento $datiGeneraliDocumento)
    {
        $datiGenerali = new DatiGenerali($datiGeneraliDocumento);

        $this->assertInstanceOf(DatiGenerali::class, $datiGenerali);

        return $datiGenerali;
    }

    public function test_class_dati_riferimento_is_created()
    {
        $datiRiferimento = new DatiRiferimento('abc');

        $this->assertInstanceOf(DatiRiferimento::class, $datiRiferimento);

        return $datiRiferimento;
    }

    public function test_class_dati_dal_is_created()
    {
        $datiSAL = new DatiSAL(10);

        $this->assertInstanceOf(DatiSAL::class, $datiSAL);

        return $datiSAL;
    }

    public function test_class_dati_ddt_is_created()
    {
        $datiDDT = new DatiDDT('N12345', '2000-01-01');

        $this->assertInstanceOf(DatiDDT::class, $datiDDT);

        return $datiDDT;
    }

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
    public function test_class_dati_trasporto_is_created(Fiscale $fiscale)
    {
        $datiTrasporto = new DatiTrasporto();
        $datiTrasporto->setIdFiscaleIVA($fiscale);

        $this->assertInstanceOf(DatiTrasporto::class, $datiTrasporto);

        return $datiTrasporto;
    }

    /**
     * @depends test_class_is_created
     * @depends test_class_dati_riferimento_is_created
     * @depends test_class_dati_dal_is_created
     * @depends test_class_dati_ddt_is_created
     * @depends test_class_dati_trasporto_is_created
     * @param DatiGenerali $datiGenerali
     * @param DatiRiferimento $datiRiferimento
     * @param DatiSAL $datiSAL
     * @param DatiDDT $datiDDT
     * @param DatiTrasporto $datiTrasporto
     * @return DatiGenerali
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_returns_correct_array(
        DatiGenerali $datiGenerali,
        DatiRiferimento $datiRiferimento,
        DatiSAL $datiSAL,
        DatiDDT $datiDDT,
        DatiTrasporto $datiTrasporto
    )
    {
        $datiGenerali->addDatiContratto($datiRiferimento)
            ->addDatiConvenzione($datiRiferimento)
            ->addDatiOrdineAcquisto($datiRiferimento)
            ->addDatiFattureCollegate($datiRiferimento)
            ->addDatiRicezione($datiRiferimento)
            ->addDatiSAL($datiSAL)
            ->addDatiDDT($datiDDT)
            ->setNumeroFatturaPrincipale('test12345')
            ->setDataFatturaPrincipale('2000-01-01')
            ->setDatiTrasporto($datiTrasporto);

        $this->assertInstanceOf(DatiGenerali::class, $datiGenerali);

        $array = $datiGenerali->toArray();
        $this->assertArrayHasKey('DatiGeneraliDocumento', $array);
        $this->assertArrayHasKey('DatiOrdineAcquisto', $array);
        $this->assertArrayHasKey('DatiContratto', $array);
        $this->assertArrayHasKey('DatiConvenzione', $array);
        $this->assertArrayHasKey('DatiRicezione', $array);
        $this->assertArrayHasKey('DatiFattureCollegate', $array);
        $this->assertArrayHasKey('DatiSAL', $array);
        $this->assertArrayHasKey('DatiDDT', $array);
        $this->assertArrayHasKey('DatiTrasporto', $array);
        $this->assertArrayHasKey('FatturaPrincipale', $array);

        return $datiGenerali;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DatiGenerali $datiGenerali
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_is_created_from_array(DatiGenerali $datiGenerali)
    {
        $array = $datiGenerali->toArray();

        $datiGenerali = DatiGenerali::fromArray($array);

        $this->assertInstanceOf(DatiGenerali::class, $datiGenerali);
        $this->assertInstanceOf(DatiGeneraliDocumento::class, $datiGenerali->getDatiGeneraliDocumento());
        $this->assertIsArray($datiGenerali->getDatiOrdineAcquisto());
        $this->assertIsArray($datiGenerali->getDatiContratto());
        $this->assertIsArray($datiGenerali->getDatiConvenzione());
        $this->assertIsArray($datiGenerali->getDatiRicezione());
        $this->assertIsArray($datiGenerali->getDatiFattureCollegate());
        $this->assertIsArray($datiGenerali->getDatiSAL());
        $this->assertInstanceOf(DatiTrasporto::class, $datiGenerali->getDatiTrasporto());
        $this->assertEquals('test12345', $datiGenerali->getNumeroFatturaPrincipale());
        $this->assertEquals('2000-01-01', $datiGenerali->getDataFatturaPrincipale());
    }
}