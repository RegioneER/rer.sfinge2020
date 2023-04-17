<?php


namespace Gdbnet\FatturaElettronica\Tests\Unit\Header;

use Gdbnet\FatturaElettronica\Header\CessionarioCommittente;
use Gdbnet\FatturaElettronica\Structures\Anagrafica;
use Gdbnet\FatturaElettronica\Structures\Fiscale;
use Gdbnet\FatturaElettronica\Structures\Indirizzo;
use PHPUnit\Framework\TestCase;

class TestCessionarioCommittente extends TestCase
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

    public function test_class_indirizzo_is_created()
    {
        $indirizzo = new Indirizzo('via test 2', 'Roma', '00100', 'IT');

        $this->assertInstanceOf(Indirizzo::class, $indirizzo);

        return $indirizzo;
    }

    /**
     * @depends test_class_anagrafica_is_created
     * @depends test_class_indirizzo_is_created
     * @depends test_class_fiscale_is_created
     */
    public function test_class_is_created($anagrafica, $indirizzo, $fiscale)
    {
        $cessionarioCommittente = new CessionarioCommittente($anagrafica, $indirizzo, $fiscale);

        $this->assertInstanceOf(CessionarioCommittente::class, $cessionarioCommittente);

        return $cessionarioCommittente;
    }

    /**
     * @depends test_class_is_created
     * @depends test_class_indirizzo_is_created
     * @depends test_class_fiscale_is_created
     */
    public function test_class_returns_correct_array(
        CessionarioCommittente $cessionarioCommittente,
        Indirizzo $indirizzo,
        Fiscale $fiscale
    )
    {
        $cessionarioCommittente->setCodiceFiscale('RSSMRA80A01H501U')
            ->setStabileOrganizzazione($indirizzo)
            ->setRappresentanteFiscaleCognome('testing')
            ->setRappresentanteFiscaleNome('test')
            ->setRappresentanteFiscaleDenominazione('test testing')
            ->setRappresentanteFiscaleIdFiscaleIVA($fiscale);

        $this->assertInstanceOf(CessionarioCommittente::class, $cessionarioCommittente);

        $array = $cessionarioCommittente->toArray();
        $this->assertArrayHasKey('DatiAnagrafici', $array);
        $this->assertArrayHasKey('IdFiscaleIVA', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('CodiceFiscale', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('Anagrafica', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('Sede', $array);
        $this->assertArrayHasKey('StabileOrganizzazione', $array);
        $this->assertArrayHasKey('RappresentanteFiscale', $array);
        $this->assertArrayHasKey('IdFiscaleIVA', $array['RappresentanteFiscale']);
        $this->assertArrayHasKey('Denominazione', $array['RappresentanteFiscale']);
        $this->assertArrayHasKey('Nome', $array['RappresentanteFiscale']);
        $this->assertArrayHasKey('Cognome', $array['RappresentanteFiscale']);

        return $cessionarioCommittente;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param CessionarioCommittente $cessionarioCommittente
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_is_created_from_array(CessionarioCommittente $cessionarioCommittente)
    {
        $array = $cessionarioCommittente->toArray();

        $cessionarioCommittente = CessionarioCommittente::fromArray($array);

        $this->assertInstanceOf(CessionarioCommittente::class, $cessionarioCommittente);
        $this->assertInstanceOf(Anagrafica::class, $cessionarioCommittente->getAnagrafica());
        $this->assertInstanceOf(Fiscale::class, $cessionarioCommittente->getIdFiscaleIVA());
        $this->assertInstanceOf(Indirizzo::class, $cessionarioCommittente->getSede());
        $this->assertInstanceOf(Indirizzo::class, $cessionarioCommittente->getStabileOrganizzazione());
        $this->assertEquals('RSSMRA80A01H501U', $cessionarioCommittente->getCodiceFiscale());
        $this->assertEquals('test testing', $cessionarioCommittente->getRappresentanteFiscaleDenominazione());
        $this->assertEquals('testing', $cessionarioCommittente->getRappresentanteFiscaleCognome());
        $this->assertEquals('test', $cessionarioCommittente->getRappresentanteFiscaleNome());
        $this->assertInstanceOf(Fiscale::class, $cessionarioCommittente->getRappresentanteFiscaleIdFiscaleIVA());
    }
}