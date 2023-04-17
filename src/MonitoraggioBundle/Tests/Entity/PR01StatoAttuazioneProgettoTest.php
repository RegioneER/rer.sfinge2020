<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\PR01StatoAttuazioneProgetto;
use MonitoraggioBundle\Entity\TC47StatoProgetto;

class PR01StatoAttuazioneProgettoTest extends TestCase {
    /**
     * @var PR01StatoAttuazioneProgetto
     */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->entity = new PR01StatoAttuazioneProgetto();
    }

    public function testGetTracciato() {
        $tc47 = new \MonitoraggioBundle\Entity\TC47StatoProgetto();
        $tc47->setStatoProgetto('setStatoProgetto');

        $data = new \DateTime('2010-01-02');

        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setDataRiferimento($data)
            ->setTc47StatoProgetto($tc47)
            ->setFlgCancellazione('S');

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        \preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 4);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setStatoProgetto');
        $this->assertEquals($match[0][2], '02/01/2010');
        $this->assertEquals($match[0][3], 'S');
    }

    public function testGetId() {
        $this->assertNull($this->entity->getId());
    }

    public function testFlagCancellazione() {
        $this->assertNull($this->entity->getFlgCancellazione());

        $this->entity->setFlgCancellazione('S');
        $this->assertSame('S', $this->entity->getFlgCancellazione());
    }

    public function testCodLocaleProgetto() {
        $this->entity->setCodLocaleProgetto('cod_locale');

        $this->assertSame('cod_locale', $this->entity->getCodLocaleProgetto());
    }

    public function testStatoProgetto() {
        $tc = new TC47StatoProgetto();
        $this->entity->setTc47StatoProgetto($tc);
        $this->assertSame($tc, $this->entity->getTc47StatoProgetto());
    }
}
