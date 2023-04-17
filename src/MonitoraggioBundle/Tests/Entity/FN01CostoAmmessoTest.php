<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\FN01CostoAmmesso;

class FN01CostoAmmessoTest extends TestCase {
    public function testGetTracciato() {
        $tc4 = new \MonitoraggioBundle\Entity\TC4Programma();
        $tc4->setCodProgramma('setCodProgramma');

        $tc36 = new \MonitoraggioBundle\Entity\TC36LivelloGerarchico();
        $tc36->setCodLivGerarchico('setCodLivGerarchico');

        $entity = new \MonitoraggioBundle\Entity\FN01CostoAmmesso();
        $entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setImportoAmmesso(999)
            ->setTc4Programma($tc4)
            ->setTc36LivelloGerarchico($tc36)
            ->setFlgCancellazione('S');

        $tracciato = $entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 5);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setCodProgramma');
        $this->assertEquals($match[0][2], 'setCodLivGerarchico');
        $this->assertSame($match[0][3], '999,00');
        $this->assertEquals($match[0][4], 'S');
    }

    public function testId() {
        $fn01 = new FN01CostoAmmesso();
        $this->assertNull($fn01->getId());
    }
}
