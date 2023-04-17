<?php

namespace AttuazioneControlloBundle\Tests\CalcoloIndicatori;

use AttuazioneControlloBundle\CalcoloIndicatori\Indicatore_6_142014IT16RFOP008;

class Indicatore_6_142014IT16RFOP008Test extends TestIndicatore {
    public function testCalcolaValoreRealizzato(): void {
        $calcolo = new Indicatore_6_142014IT16RFOP008($this->container, $this->richiesta);

        $this->assertEquals(1.0, $calcolo->getValore());
    }
}
