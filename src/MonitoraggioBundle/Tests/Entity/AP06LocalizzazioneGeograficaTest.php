<?php

namespace MonitoraggioBundle\Tests\Entity;

use MonitoraggioBundle\Entity\AP06LocalizzazioneGeografica;
use MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica;
use PHPUnit\Framework\TestCase;

class AP06LocalizzazioneGeograficaTest extends TestCase {
    /**
     * @var AP06LocalizzazioneGeografica
     */
    protected $ap06;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->ap06 = new AP06LocalizzazioneGeografica();
    }

    public function testGetTracciato() {
        $tc16 = new TC16LocalizzazioneGeografica();
        $tc16->setCodiceComune('000')
        ->setCodiceProvincia('000')
        ->setCodiceRegione('000');
        $indirizzo = 'via dei pazzi numero 0';

        $entity = new AP06LocalizzazioneGeografica();
        $entity->setCodCap(12345)
            ->setCodLocaleProgetto(321)
            ->setLocalizzazioneGeografica($tc16)
            ->setIndirizzo($indirizzo)
            ->setFlgCancellazione('S');

        $tracciato = $entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/[^|]+(?=|[^|]*$)/', $tracciato, $match);

        $this->assertEquals($match[0][0], '321');
        $this->assertEquals($match[0][1], '000');
        $this->assertEquals($match[0][2], '000');
        $this->assertEquals($match[0][3], '000');
        $this->assertEquals($match[0][4], $indirizzo);
        $this->assertEquals($match[0][5], '12345');
        $this->assertEquals($match[0][6], 'S');

        $this->assertEquals(\count($match[0]), 7);
    }

    public function testLocalizzazioneGeografica() {
        $localizzazione = new TC16LocalizzazioneGeografica();
        $this->ap06->setLocalizzazioneGeografica($localizzazione);

        $this->assertSame($localizzazione, $this->ap06->getLocalizzazioneGeografica());

        $this->ap06->setLocalizzazioneGeografica(null);
        $this->assertNull($this->ap06->getLocalizzazioneGeografica());
    }

    public function testCodiceLocaleProgetto() {
        $codice = 'stringa a caso';
        $this->ap06->setCodLocaleProgetto($codice);

        $this->assertSame($codice, $this->ap06->getCodLocaleProgetto());

        $this->ap06->setCodLocaleProgetto(null);
        $this->assertNull($this->ap06->getCodLocaleProgetto());
    }

    public function testIndirizzo() {
        $this->assertNull($this->ap06->getIndirizzo());
        $i = 'indirizzo';
        $this->ap06->setIndirizzo($i);

        $this->assertSame($i, $this->ap06->getIndirizzo());
    }

    public function testCap() {
        $this->assertNull($this->ap06->getCodCap());
        $i = 'sdjiofjds';
        $this->ap06->setCodCap($i);

        $this->assertSame($i, $this->ap06->getCodCap());
    }

    public function testCancellazione() {
        $this->ap06->setFlgCancellazione('');
        $this->assertNull($this->ap06->getFlgCancellazione());
        $this->ap06->setFlgCancellazione('S');
        $this->assertEquals('S', $this->ap06->getFlgCancellazione());
    }
}
