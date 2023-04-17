<?php

namespace AttuazioneControlloBundle\Tests\CalcoloIndicatori;

use AttuazioneControlloBundle\CalcoloIndicatori\Indicatore_102;
use RichiesteBundle\Entity\Proponente;


class Indicatore_102Test extends TestIndicatore{
    public function testCalcolaValoreRealizzato() {
        $proponente = new Proponente();
        $this->richiesta->addProponente($proponente);
        $calcolo = new Indicatore_102($this->container, $this->richiesta);
        $res = $calcolo->getValore();
        $this->assertNotNull($res);
        $this->assertEquals(1.0, $res);
    }
}