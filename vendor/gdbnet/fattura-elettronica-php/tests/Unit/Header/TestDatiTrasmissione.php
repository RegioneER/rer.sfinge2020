<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Header;

use Gdbnet\FatturaElettronica\Header\DatiTrasmissione;
use Gdbnet\FatturaElettronica\Structures\Fiscale;
use PHPUnit\Framework\TestCase;

class TestDatiTrasmissione extends TestCase
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
    public function test_class_is_created(Fiscale $fiscale)
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

    /**
     * @depends test_class_is_created
     * @param Fiscale $fiscale
     * @return DatiTrasmissione
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_returns_correct_array(DatiTrasmissione $datiTrasmissione)
    {
        $datiTrasmissione->setTelefono('0000000000')
            ->setEmail('test@example.com')
            ->setPECDestinatario('test@pec.com');

        $this->assertInstanceOf(DatiTrasmissione::class, $datiTrasmissione);

        $array = $datiTrasmissione->toArray();
        $this->assertArrayHasKey('IdTrasmittente', $array);
        $this->assertArrayHasKey('ProgressivoInvio', $array);
        $this->assertArrayHasKey('FormatoTrasmissione', $array);
        $this->assertArrayHasKey('CodiceDestinatario', $array);
        $this->assertArrayHasKey('ContattiTrasmittente', $array);
        $this->assertArrayHasKey('Telefono', $array['ContattiTrasmittente']);
        $this->assertArrayHasKey('Email', $array['ContattiTrasmittente']);
        $this->assertArrayHasKey('PECDestinatario', $array);

        return $datiTrasmissione;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DatiTrasmissione $datiTrasmissione
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_is_created_from_array(DatiTrasmissione $datiTrasmissione)
    {
        $array = $datiTrasmissione->toArray();

        $datiTrasmissione = DatiTrasmissione::fromArray($array);

        $this->assertInstanceOf(DatiTrasmissione::class, $datiTrasmissione);
        $this->assertInstanceOf(Fiscale::class, $datiTrasmissione->getIdTrasmittente());
        $this->assertEquals('test@example.com', $datiTrasmissione->getEmail());
        $this->assertEquals('0000000000', $datiTrasmissione->getTelefono());
        $this->assertEquals('0000000', $datiTrasmissione->getCodiceDestinatario());
        $this->assertEquals('FPR12', $datiTrasmissione->getFormatoTrasmissione());
        $this->assertEquals('test@pec.com', $datiTrasmissione->getPECDestinatario());
    }
}