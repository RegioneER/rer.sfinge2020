<?php

namespace AttuazioneControlloBundle\Tests\CalcoloIndicatori;
use AttuazioneControlloBundle\CalcoloIndicatori\Indicatore_6_C_32014IT16RFOP008;
class Indicatore_6_C_32014IT16RFOP008Test extends TestIndicatore {

    public function testValoreRealizzato(){
        $calcolo = new Indicatore_6_C_32014IT16RFOP008($this->container, $this->richiesta);
        $res = $calcolo->getValore();
        $this->assertSame(1.0, $res);
    }
}