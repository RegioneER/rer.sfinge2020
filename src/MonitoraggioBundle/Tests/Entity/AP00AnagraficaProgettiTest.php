<?php

namespace MonitoraggioBundle\Tests\Entity;

use MonitoraggioBundle\Entity\AP00AnagraficaProgetti;
use MonitoraggioBundle\Entity\TC5TipoOperazione;
use MonitoraggioBundle\Entity\TC6TipoAiuto;
use MonitoraggioBundle\Entity\TC48TipoProceduraAttivazioneOriginaria;
use PHPUnit\Framework\TestCase;

class AP00AnagraficaProgettiTest extends TestCase {
    public function testGetTracciato() {
        $tc5 = new TC5TipoOperazione();
        $tc5->setTipoOperazione('01.00');
        $tc6 = new TC6TipoAiuto();
        $tc6->setTipoAiuto('A');
        $tc48 = new TC48TipoProceduraAttivazioneOriginaria();
        $tc48->setTipProcAttOrig('1');

        $data = new \DateTime();

        $entity = new AP00AnagraficaProgetti();
        $entity
            ->setTc5TipoOperazione($tc5)
            ->setTc6TipoAiuto($tc6)
            ->setTc48TipoProceduraAttivazioneOriginaria($tc48)
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setTitoloProgetto('setTitoloProgetto')
            ->setSintesiPrg('setSintesiPrg')
            ->setCup('setCup')
            ->setDataInizio($data)
            ->setDataFinePrevista($data)
            ->setDataFineEffettiva($data)
            ->setCodiceProcAttOrig('setCodiceProcAttOrig')
            ->setFlgCancellazione('S');

        $tracciato = $entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 12);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setTitoloProgetto');
        $this->assertEquals($match[0][2], 'setSintesiPrg');
        $this->assertEquals($match[0][3], '01.00');
        $this->assertEquals($match[0][4], 'setCup');
        $this->assertEquals($match[0][5], 'A');
        $this->assertEquals($match[0][6], $data->format('d/m/Y'));
        $this->assertEquals($match[0][7], $data->format('d/m/Y'));
        $this->assertEquals($match[0][8], $data->format('d/m/Y'));
        $this->assertEquals($match[0][9], '1');
        $this->assertEquals($match[0][10], 'setCodiceProcAttOrig');
        $this->assertEquals($match[0][11], 'S');
    }
}
