<?php

namespace MonitoraggioBundle\Tests\Entity;

use MonitoraggioBundle\Entity\AP05StrumentoAttuativo;
use MonitoraggioBundle\Entity\TC15StrumentoAttuativo;
use PHPUnit\Framework\TestCase;

class AP05StrumentoAttuativoTest extends TestCase {
    public function testGetTracciato() {
        $tc15 = new TC15StrumentoAttuativo();
        $tc15->setCodStruAtt('01');

        $entity = new AP05StrumentoAttuativo();
        $entity
            ->setCodLocaleProgetto(321)
            ->setTc15StrumentoAttuativo($tc15)
            ->setFlgCancellazione('S');

        $tracciato = $entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 3);
        $this->assertEquals($match[0][0], '321');
        $this->assertEquals($match[0][1], '01');
        $this->assertEquals($match[0][2], 'S');
    }
}
