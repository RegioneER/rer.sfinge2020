<?php

namespace AttuazioneControlloBundle\Tests\CalcoloIndicatori;

use PHPUnit\Framework\TestCase;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\Proponente;
use AttuazioneControlloBundle\CalcoloIndicatori\Indicatore_101;

class Indicatore_101Test extends TestIndicatore {

    public function testCalcolaValoreRealizzato() {
        $proponente = new Proponente();
        $this->richiesta->addProponente($proponente);
        $calcolo = new Indicatore_101($this->container, $this->richiesta);
        $res = $calcolo->getValore();
        $this->assertNotNull($res);
        $this->assertEquals(1.0, $res);
    }
}
