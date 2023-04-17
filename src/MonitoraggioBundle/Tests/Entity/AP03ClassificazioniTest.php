<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;

class AP03ClassificazioniTest extends TestCase {
    public function testGetTracciato() {
        $tc4 = new \MonitoraggioBundle\Entity\TC4Programma();
        $tc4->setCodProgramma('setCodProgramma');

        $tc11 = new \MonitoraggioBundle\Entity\TC11TipoClassificazione();
        $tc11->setTipoClass('setTipoClass');

        $tc12 = new \MonitoraggioBundle\Entity\TC12Classificazione();
        $tc12->setCodice('setCodice');

        $entity = new \MonitoraggioBundle\Entity\AP03Classificazioni();
        $entity
        ->setTc4Programma($tc4)
        ->setTc11TipoClassificazione($tc11)
        ->setCodLocaleProgetto('setCodLocaleProgetto')
        ->setClassificazione($tc12)
        ->setFlgCancellazione('S');

        $tracciato = $entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 5);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setCodProgramma');
        $this->assertEquals($match[0][2], 'setTipoClass');
        $this->assertEquals($match[0][3], 'setCodice');
        $this->assertEquals($match[0][4], 'S');
    }
}
