<?php

namespace MonitoraggioBundle\Tests\Entity;

use MonitoraggioBundle\Entity\AP01AssociazioneProgettiProcedura;
use MonitoraggioBundle\Entity\TC1ProceduraAttivazione;
use PHPUnit\Framework\TestCase;

class AP01AssociazioneProgettiProceduraTest extends TestCase {
    public function testGetTracciato() {
        $tc1 = new TC1ProceduraAttivazione();
        $tc1->setCodProcAtt('setCodProcAtt');

        $entity = new AP01AssociazioneProgettiProcedura();
        $entity
            ->setCodLocaleProgetto(321)
            ->setTc1ProceduraAttivazione($tc1)
            ->setFlgCancellazione('S');

        $tracciato = $entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 3);
        $this->assertEquals($match[0][0], '321');
        $this->assertEquals($match[0][1], 'setCodProcAtt');
        $this->assertEquals($match[0][2], 'S');
    }
}
