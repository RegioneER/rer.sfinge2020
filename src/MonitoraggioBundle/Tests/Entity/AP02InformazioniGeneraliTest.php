<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\AP02InformazioniGenerali;
use MonitoraggioBundle\Entity\TC9TipoLivelloIstituzione;

class AP02InformazioniGeneraliTest extends TestCase {
    public function testGetTracciato() {
        $tc7 = new \MonitoraggioBundle\Entity\TC7ProgettoComplesso();
        $tc7->setCodPrgComplesso('setCodPrgComplesso');

        $tc8 = new \MonitoraggioBundle\Entity\TC8GrandeProgetto();
        $tc8->setGrandeProgetto('setGrandeProgetto');

        $tc9 = new \MonitoraggioBundle\Entity\TC9TipoLivelloIstituzione();
        $tc9->setLivIstituzioneStrFin('setLivIstituzioneStrFin');

        $tc10 = new \MonitoraggioBundle\Entity\TC10TipoLocalizzazione();
        $tc10->setTipoLocalizzazione('setTipoLocalizzazione');

        $tc13 = new \MonitoraggioBundle\Entity\TC13GruppoVulnerabileProgetto();
        $tc13->setCodVulnerabili('setCodVulnerabili');

        $entity = new \MonitoraggioBundle\Entity\AP02InformazioniGenerali();
        $entity
            ->setTc7ProgettoComplesso($tc7)
            ->setTc8GrandeProgetto($tc8)
            ->setTc9TipoLivelloIstituzione($tc9)
            ->setTc10TipoLocalizzazione($tc10)
            ->setTc13GruppoVulnerabileProgetto($tc13)
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setGeneratoreEntrate('setGeneratoreEntrate')
            ->setFondoDiFondi('setFondoDiFondi')
            ->setFlgCancellazione('S');

        $tracciato = $entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 9);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setCodPrgComplesso');
        $this->assertEquals($match[0][2], 'setGrandeProgetto');
        $this->assertEquals($match[0][3], 'setGeneratoreEntrate');
        $this->assertEquals($match[0][4], 'setLivIstituzioneStrFin');
        $this->assertEquals($match[0][5], 'setFondoDiFondi');
        $this->assertEquals($match[0][6], 'setTipoLocalizzazione');
        $this->assertEquals($match[0][7], 'setCodVulnerabili');
        $this->assertEquals($match[0][8], 'S');
    }

    public function testIsFondoDiFondiObbligatorioValid1() {
        $entity = new AP02InformazioniGenerali();
        $this->assertEquals($entity->isFondoDiFondiObbligatorioValid(), true);
    }

    public function testIsFondoDiFondiObbligatorioValid2() {
        $tc9 = new TC9TipoLivelloIstituzione();
        $tc9->setLivIstituzioneStrFin(1);
        $entity = new AP02InformazioniGenerali();
        $entity->setTc9TipoLivelloIstituzione($tc9);
        $this->assertEquals($entity->isFondoDiFondiObbligatorioValid(), true);
    }

    public function testIsFondoDiFondiObbligatorioValid3() {
        $tc9 = new TC9TipoLivelloIstituzione();
        $tc9->setLivIstituzioneStrFin(2);
        $entity = new AP02InformazioniGenerali();
        $entity->setTc9TipoLivelloIstituzione($tc9);
        $this->assertEquals($entity->isFondoDiFondiObbligatorioValid(), false);
    }

    public function testIsFondoDiFondiObbligatorioValid4() {
        $tc9 = new TC9TipoLivelloIstituzione();
        $tc9->setLivIstituzioneStrFin(2);
        $entity = new AP02InformazioniGenerali();
        $entity->setTc9TipoLivelloIstituzione($tc9)
        ->setFondoDiFondi('S');
        $this->assertEquals($entity->isFondoDiFondiObbligatorioValid(), true);
    }
}
