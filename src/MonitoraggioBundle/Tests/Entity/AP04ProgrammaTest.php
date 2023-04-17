<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;

class AP04ProgrammaTest extends TestCase {
    public function testGetTracciato() {
        $tc4 = new \MonitoraggioBundle\Entity\TC4Programma();
        $tc4->setCodProgramma('setCodProgramma');

        $tc14 = new \MonitoraggioBundle\Entity\TC14SpecificaStato();
        $tc14->setSpecificaStato('setSpecificaStato');

        $entity = new \MonitoraggioBundle\Entity\AP04Programma();
        $entity
        ->setTc4Programma($tc4)
        ->setTc14SpecificaStato($tc14)
        ->setCodLocaleProgetto('setCodLocaleProgetto')
        ->setStato('setStato');

        $tracciato = $entity->getTracciato();
        $this->assertNotNull($tracciato);

        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 4);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setCodProgramma');
        $this->assertEquals($match[0][2], 'setStato');
        $this->assertEquals($match[0][3], 'setSpecificaStato');
    }
}
