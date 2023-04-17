<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\FN00Finanziamento;
use MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica;

class FN00FinanziamentoTest extends TestCase {
    /**
     * @var FN00Finanziamento
     */
    protected $entity;

    public function setUp() {
        $this->entity = new \MonitoraggioBundle\Entity\FN00Finanziamento();
    }

    public function testGetTracciato() {
        $tc16 = new \MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica();
        $tc16->setCodiceRegione('999');
        $tc16->setCodiceProvincia('999');
        $tc16->setCodiceComune('999');

        $tc33 = new \MonitoraggioBundle\Entity\TC33FonteFinanziaria();
        $tc33->setCodFondo('setCodFondo');

        $tc34 = new \MonitoraggioBundle\Entity\TC34DeliberaCIPE();
        $tc34->setCodDelCipe('setCodDelCipe');

        $tc35 = new \MonitoraggioBundle\Entity\TC35Norma();
        $tc35->setCodNorma('setCodNorma');

        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setCfCofinanz('setCfCofinanz')
            ->setImporto(123)
            ->setTc16LocalizzazioneGeografica($tc16)
            ->setTc33FonteFinanziaria($tc33)
            ->setTc34DeliberaCipe($tc34)
            ->setTc35Norma($tc35)
            ->setFlgCancellazione('S');

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 8);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setCodFondo');
        $this->assertEquals($match[0][2], 'setCodNorma');
        $this->assertEquals($match[0][3], 'setCodDelCipe');
        $this->assertEquals($match[0][4], '999999999');
        $this->assertEquals($match[0][5], 'setCfCofinanz');
        $this->assertSame($match[0][6], '123,00');
        $this->assertEquals($match[0][7], 'S');
    }

    public function testId() {
        $this->assertNull($this->entity->getId());
    }

    public function testLocalizzazioneGeografica() {
        $this->assertNull($this->entity->getTc16LocalizzazioneGeografica());

        $tc = new TC16LocalizzazioneGeografica();
        $res = $this->entity->setTc16LocalizzazioneGeografica($tc);
        $this->assertSame($this->entity, $res);
        $this->assertSame($tc, $this->entity->getTc16LocalizzazioneGeografica());
    }
}
