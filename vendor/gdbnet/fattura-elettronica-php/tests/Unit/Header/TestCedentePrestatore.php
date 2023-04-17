<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Header;

use Gdbnet\FatturaElettronica\Header\CedentePrestatore;
use Gdbnet\FatturaElettronica\Structures\Anagrafica;
use Gdbnet\FatturaElettronica\Structures\Fiscale;
use Gdbnet\FatturaElettronica\Structures\Indirizzo;
use PHPUnit\Framework\TestCase;

class TestCedentePrestatore extends TestCase
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
     * @depends test_class_fiscale_is_created
     * @depends test_class_anagrafica_is_created
     * @depends test_class_indirizzo_is_created
     */
    public function test_class_is_created($fiscale, $anagrafica, $indirizzo)
    {
        $cedentePrestatore = new CedentePrestatore($fiscale, $anagrafica, $indirizzo, 'RF01');

        $this->assertInstanceOf(CedentePrestatore::class, $cedentePrestatore);

        return $cedentePrestatore;
    }

    /**
     * @depends test_class_is_created
     * @depends test_class_indirizzo_is_created
     * @param CedentePrestatore $cedentePrestatore
     * @return CedentePrestatore
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_returns_correct_array(CedentePrestatore $cedentePrestatore, Indirizzo $indirizzo)
    {
        $cedentePrestatore->setAlboProfessionale('test')
            ->setCapitaleSociale(1000)
            ->setCodiceFiscale('RSSMRA80A01H501U')
            ->setDataIscrizioneAlbo('2000-01-01')
            ->setEmail('test@example.com')
            ->setFax('0000000000')
            ->setNumeroIscrizioneAlbo('10')
            ->setNumeroREA('10')
            ->setProvinciaAlbo('RM')
            ->setRegimeFiscale('RF01')
            ->setRiferimentoAmministrazione('test')
            ->setSocioUnico('SU')
            ->setStabileOrganizzazione($indirizzo)
            ->setStatoLiquidazione('LN')
            ->setTelefono('0000000000')
            ->setUfficio('RM');

        $this->assertInstanceOf(CedentePrestatore::class, $cedentePrestatore);

        $array = $cedentePrestatore->toArray();
        $this->assertArrayHasKey('DatiAnagrafici', $array);
        $this->assertArrayHasKey('IdFiscaleIVA', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('CodiceFiscale', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('Anagrafica', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('RegimeFiscale', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('AlboProfessionale', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('ProvinciaAlbo', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('NumeroIscrizioneAlbo', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('DataIscrizioneAlbo', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('Sede', $array);
        $this->assertArrayHasKey('StabileOrganizzazione', $array);
        $this->assertArrayHasKey('IscrizioneREA', $array);
        $this->assertArrayHasKey('Ufficio', $array['IscrizioneREA']);
        $this->assertArrayHasKey('NumeroREA', $array['IscrizioneREA']);
        $this->assertArrayHasKey('CapitaleSociale', $array['IscrizioneREA']);
        $this->assertArrayHasKey('SocioUnico', $array['IscrizioneREA']);
        $this->assertArrayHasKey('StatoLiquidazione', $array['IscrizioneREA']);
        $this->assertArrayHasKey('Contatti', $array);
        $this->assertArrayHasKey('Telefono', $array['Contatti']);
        $this->assertArrayHasKey('Fax', $array['Contatti']);
        $this->assertArrayHasKey('Email', $array['Contatti']);
        $this->assertArrayHasKey('RiferimentoAmministrazione', $array);

        return $cedentePrestatore;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param CedentePrestatore $cedentePrestatore
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_is_created_from_array(CedentePrestatore $cedentePrestatore)
    {
        $array = $cedentePrestatore->toArray();

        $cedentePrestatore = CedentePrestatore::fromArray($array);

        $this->assertInstanceOf(CedentePrestatore::class, $cedentePrestatore);
        $this->assertInstanceOf(Anagrafica::class, $cedentePrestatore->getAnagrafica());
        $this->assertInstanceOf(Fiscale::class, $cedentePrestatore->getIdFiscaleIVA());
        $this->assertInstanceOf(Indirizzo::class, $cedentePrestatore->getSede());
        $this->assertInstanceOf(Indirizzo::class, $cedentePrestatore->getStabileOrganizzazione());
        $this->assertEquals('RSSMRA80A01H501U', $cedentePrestatore->getCodiceFiscale());
        $this->assertEquals('RF01', $cedentePrestatore->getRegimeFiscale());
        $this->assertEquals('test', $cedentePrestatore->getAlboProfessionale());
        $this->assertEquals('RM', $cedentePrestatore->getProvinciaAlbo());
        $this->assertEquals('10', $cedentePrestatore->getNumeroIscrizioneAlbo());
        $this->assertEquals('2000-01-01', $cedentePrestatore->getDataIscrizioneAlbo());
        $this->assertEquals('RM', $cedentePrestatore->getUfficio());
        $this->assertEquals('10', $cedentePrestatore->getNumeroREA());
        $this->assertEquals(1000, $cedentePrestatore->getCapitaleSociale());
        $this->assertEquals('SU', $cedentePrestatore->getSocioUnico());
        $this->assertEquals('LN', $cedentePrestatore->getStatoLiquidazione());
        $this->assertEquals('0000000000', $cedentePrestatore->getTelefono());
        $this->assertEquals('0000000000', $cedentePrestatore->getFax());
        $this->assertEquals('test@example.com', $cedentePrestatore->getEmail());
    }
}