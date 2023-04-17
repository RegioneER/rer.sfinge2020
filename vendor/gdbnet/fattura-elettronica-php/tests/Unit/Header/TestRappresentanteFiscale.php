<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Header;

use Gdbnet\FatturaElettronica\Header\RappresentanteFiscale;
use Gdbnet\FatturaElettronica\Structures\Anagrafica;
use Gdbnet\FatturaElettronica\Structures\Fiscale;
use PHPUnit\Framework\TestCase;

class TestRappresentanteFiscale extends TestCase
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
     * @depends test_class_fiscale_is_created
     * @depends test_class_anagrafica_is_created
     * @param Fiscale $fiscale
     * @return RappresentanteFiscale
     */
    public function test_class_is_created(Fiscale $fiscale, Anagrafica $anagrafica)
    {
        $rappresentanteFiscale = new RappresentanteFiscale($fiscale, $anagrafica);

        $this->assertInstanceOf(RappresentanteFiscale::class, $rappresentanteFiscale);

        return $rappresentanteFiscale;
    }

    /**
     * @depends test_class_is_created
     */
    public function test_class_returns_correct_array(RappresentanteFiscale $rappresentanteFiscale)
    {
        $rappresentanteFiscale->setCodiceFiscale('RSSMRA80A01H501U');

        $this->assertInstanceOf(RappresentanteFiscale::class, $rappresentanteFiscale);

        $array = $rappresentanteFiscale->toArray();
        $this->assertArrayHasKey('DatiAnagrafici', $array);
        $this->assertArrayHasKey('IdFiscaleIVA', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('CodiceFiscale', $array['DatiAnagrafici']);
        $this->assertArrayHasKey('Anagrafica', $array['DatiAnagrafici']);

        return $rappresentanteFiscale;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param RappresentanteFiscale $rappresentanteFiscale
     */
    public function test_class_is_created_from_array(RappresentanteFiscale $rappresentanteFiscale)
    {
        $array = $rappresentanteFiscale->toArray();

        $rappresentanteFiscale = RappresentanteFiscale::fromArray($array);

        $this->assertInstanceOf(RappresentanteFiscale::class, $rappresentanteFiscale);
        $this->assertInstanceOf(Anagrafica::class, $rappresentanteFiscale->getAnagrafica());
        $this->assertInstanceOf(Fiscale::class, $rappresentanteFiscale->getIdFiscaleIVA());
        $this->assertEquals('RSSMRA80A01H501U', $rappresentanteFiscale->getCodiceFiscale());
    }
}