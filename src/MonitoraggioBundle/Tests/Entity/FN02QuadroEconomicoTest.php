<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\FN02QuadroEconomico;

class FN02QuadroEconomicoTest extends TestCase {
    public function testGetTracciato() {
        $tc37 = new \MonitoraggioBundle\Entity\TC37VoceSpesa();
        $tc37->setVoceSpesa('setVoceSpesa');

        $entity = new \MonitoraggioBundle\Entity\FN02QuadroEconomico();
        $entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setImporto(999)
            ->setTc37VoceSpesa($tc37)
            ->setFlgCancellazione('S');

        $tracciato = $entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 4);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setVoceSpesa');
        $this->assertSame($match[0][2], '999,00');
        $this->assertEquals($match[0][3], 'S');
    }

    public function testId() {
        $fn02 = new FN02QuadroEconomico();
        $this->assertNull($fn02->getId());
    }
}
