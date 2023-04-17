<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\Allegati;
use PHPUnit\Framework\TestCase;

class TestAllegati extends TestCase
{
    public function test_class_is_created()
    {
        $allegati = new Allegati('test.txt', 'test12345');

        $this->assertInstanceOf(Allegati::class, $allegati);

        return $allegati;
    }

    /**
     * @depends test_class_is_created
     * @param Allegati $allegati
     * @return Allegati
     */
    public function test_class_returns_correct_array(Allegati $allegati)
    {
        $allegati->setDescrizioneAttachment('test')
            ->setFormatoAttachment('TXT')
            ->setAlgoritmoCompressione('ZIP');

        $this->assertInstanceOf(Allegati::class, $allegati);

        $array = $allegati->toArray();
        $this->assertArrayHasKey('NomeAttachment', $array);
        $this->assertArrayHasKey('AlgoritmoCompressione', $array);
        $this->assertArrayHasKey('FormatoAttachment', $array);
        $this->assertArrayHasKey('DescrizioneAttachment', $array);
        $this->assertArrayHasKey('Attachment', $array);

        return $allegati;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param Allegati $allegati
     */
    public function test_class_is_created_from_array(Allegati $allegati)
    {
        $array = $allegati->toArray();

        $allegati = Allegati::fromArray($array);

        $this->assertInstanceOf(Allegati::class, $allegati);
        $this->assertEquals('test.txt', $allegati->getNomeAttachment());
        $this->assertEquals('ZIP', $allegati->getAlgoritmoCompressione());
        $this->assertEquals('TXT', $allegati->getFormatoAttachment());
        $this->assertEquals('test', $allegati->getDescrizioneAttachment());
        $this->assertEquals('test12345', $allegati->getAttachment());
    }
}