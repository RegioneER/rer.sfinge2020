<?php

namespace AttuazioneControlloBundle\Tests\CalcoloIndicatori;

use AttuazioneControlloBundle\CalcoloIndicatori\Indicatore_4_C_12014IT16RFOP008;
use SfingeBundle\Entity\Azione;
use RichiesteBundle\Entity\Bando5\OggettoUbicazioneEdificio;
use BaseBundle\Entity\IndirizzoCatastale;
use RichiesteBundle\Entity\Proponente;

class Indicatore_4_C_12014IT16RFOP008Test extends TestIndicatore {
    public function testValoreRealizzatoBando5() {
        $azione = new Azione();
        $azione->setCodice('4.1.2');
        $this->richiesta->getProcedura()->addAzioni($azione);
        $oggettoUbicazione = new OggettoUbicazioneEdificio();
        $indirizzoCatastale = new IndirizzoCatastale();
        $oggettoUbicazione->addIndirizzoCatastale($indirizzoCatastale);
        $this->richiesta->addOggettoRichiesta($oggettoUbicazione);

        $proponente = new Proponente();
        $this->richiesta->addProponente($proponente);

        $calcolo = new Indicatore_4_C_12014IT16RFOP008($this->container, $this->richiesta);
        $res = $calcolo->getValore();

        $this->assertNotNull($res);
        $this->assertSame(1.0, $res);
    }

    public function testAzioneSbagliata() {
        $azione = new Azione();
        $azione->setCodice('-');
        $this->richiesta->getProcedura()->addAzioni($azione);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Azione non prevista per questo indicatore');

        $calcolo = new Indicatore_4_C_12014IT16RFOP008($this->container, $this->richiesta);
        $calcolo->getValore();
    }
}
