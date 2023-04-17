<?php
namespace RichiesteBundle\Tests\Utility;

use PHPUnit\Framework\TestCase;
use RichiesteBundle\Utility\EsitoValidazione;



class EsitoValidazioneTest extends TestCase
{
    /**
     * @var EsitoValidazione
     */
    protected $x;

    /**
     * @var EsitoValidazione
     */
    protected $y;

    public function setUp(){
        $this->x = new EsitoValidazione();
        $this->y = new EsitoValidazione();
    }
    /**
     * @dataProvider esitoMergeDataProvider
     */
    public function testEsitoMerge(bool $esito1, bool $esito2, bool $risultato) : void
    {
        $this->x->setEsito($esito1);
        $this->y->setEsito($esito2);
        $merge = $this->x->merge($this->y);

        $this->assertSame($risultato, $merge->getEsito());
    }

    public function esitoMergeDataProvider(){
        return [
            [true, true, true],
            [true, false, false],
            [false, true, false],
            [false, false, false],
        ];
    }

    public function testValueObject(){
        $res = $this->x->merge($this->y);

        $this->assertNotSame($this->x, $res);
        $this->assertNotSame($this->y, $res);
    }

    public function testMergeMessaggi(){
        $msg1 = 'messaggio 1';
        $msg2 = 'messaggio 2';
        $this->x->addMessaggio($msg1);
        $this->y->addMessaggio($msg2);

        $merge = $this->x->merge($this->y);

        $this->assertContains($msg1, $merge->getMessaggi());
        $this->assertContains($msg2, $merge->getMessaggi());
    }

    public function testMergeMessaggiSezione(){
        $msg1 = 'messaggio 1';
        $msg2 = 'messaggio 2';
        $this->x->addMessaggioSezione($msg1);
        $this->y->addMessaggioSezione($msg2);

        $merge = $this->x->merge($this->y);

        $this->assertContains($msg1, $merge->getMessaggiSezione());
        $this->assertContains($msg2, $merge->getMessaggiSezione());
    }
}
