<?php

namespace AttuazioneControlloBundle\Tests\CalcoloIndicatori;

use AttuazioneControlloBundle\CalcoloIndicatori\Indicatore_7_12014IT16RFOP008;


class Indicatore_7_12014IT16RFOP008Test extends TestIndicatore
{
    public function testCalcolaValoreRealizzato() {
        $calcolo = new Indicatore_7_12014IT16RFOP008($this->container, $this->richiesta);
        $res = $calcolo->getValore();
        $this->assertNotNull($res);
        $this->assertEquals(1.0, $res);
    }
}