<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaAP01;
use MonitoraggioBundle\Entity\TC1ProceduraAttivazione;
use Doctrine\Common\Persistence\ObjectRepository;
use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;
use MonitoraggioBundle\Repository\AP01AssociazioneProgettiProceduraRepository;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;
use ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento;
use SfingeBundle\Entity\Bando;
use MonitoraggioBundle\Repository\TC1ProceduraAttivazioneRepository;

class EsportaAP01Test extends EsportazioneRichiestaBase {
    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaAP01($this->container);
    }

    public function testImportazioneConSuccesso() {
        $tc1 = new TC1ProceduraAttivazione();

        $tc1Repository = $this->createMock(ObjectRepository::class);

        $tc1Repository->expects($this->any())
                ->method('findOneBy')
                ->willReturn($tc1);

        $this->em->expects($this->any())
                ->method('getRepository')
                ->with($this->equalTo('MonitoraggioBundle:TC1ProceduraAttivazione'))
                ->willReturn($tc1Repository);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc1',
            'flg_cancellazione',
        ];

        $res = $this->esporta->importa($input);

        $this->assertEquals($res->getCodLocaleProgetto(), 'COD_LOCALE_PROGETTO');
        $this->assertEquals($res->getTc1ProceduraAttivazione(), $tc1);
        $this->assertEquals($res->getFlgCancellazione(), 'flg_cancellazione');
    }

    public function testEccezioneImportazioneTC1Nontrovata() {
        $tc1Repository = $this->createMock(TC1ProceduraAttivazioneRepository::class);
        $this->em->expects($this->any())
                ->method('getRepository')
                ->with($this->equalTo('MonitoraggioBundle:TC1ProceduraAttivazione'))
                ->willReturn($tc1Repository);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc1',
            'flg_cancellazione',
        ];
        $this->expectExceptionMessage('Procedura attivazione non valida');
        $res = $this->esporta->importa($input);
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testInputNull() {
        $this->esporta->importa(null);
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testEmptyInputNull() {
        $this->esporta->importa([]);
    }

    public function testEsportazioneNonNecessaria() {
        $richiesta = new Richiesta();
        $protocollo = new RichiestaProtocolloFinanziamento();
        $protocollo->setAnno_pg(2016);
        $protocollo->setNum_pg('123456');
        $protocollo->setRegistro_pg('PG');
        $richiesta->addRichiesteProtocollo($protocollo);

        $configurazione = new MonitoraggioConfigurazioneEsportazioneRichiesta($richiesta);
        $tavola = $configurazione = new MonitoraggioConfigurazioneEsportazioneTavole($configurazione);

        $repository = $this->createMock(AP01AssociazioneProgettiProceduraRepository::class);
        $repository
            ->method('isEsportabile')
            ->with($this->isInstanceOf(MonitoraggioConfigurazioneEsportazioneRichiesta::class))
            ->willReturn(false);

        $this->em->expects($this->once())->method('getRepository')->willReturn($repository);

        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Esportazione struttura AP01 per il progetto PG/2016/123456 non necessaria');
        $this->esporta->execute($richiesta, $tavola, true);
    }

    public function testEsportazioneAP01() {
        $richiesta = new Richiesta();
        $protocollo = new RichiestaProtocolloFinanziamento();
        $protocollo->setAnno_pg(2016);
        $protocollo->setNum_pg('123456');
        $protocollo->setRegistro_pg('PG');
        $richiesta->addRichiesteProtocollo($protocollo);

        $configurazione = new MonitoraggioConfigurazioneEsportazioneRichiesta($richiesta);
        $tavola = $configurazione = new MonitoraggioConfigurazioneEsportazioneTavole($configurazione);

        $repository = $this->createMock(AP01AssociazioneProgettiProceduraRepository::class);
        $repository->method('isEsportabile')->willReturn(true);
        $this->em->expects($this->once())->method('getRepository')->willReturn($repository);

        $procedura = new Bando();
        $richiesta->setProcedura($procedura);
        $tc1 = new TC1ProceduraAttivazione();
        $tc1->setCodProcAtt('proceduraAttivazione');
        $procedura->setMonProcAtt($tc1);

        $res = $this->esporta->execute($richiesta, $tavola, true);

        $this->assertEquals('PG/2016/123456', $res->getCodLocaleProgetto());
        $this->assertEquals($tc1, $res->getTc1ProceduraAttivazione());
        $this->assertNull($res->getFlgCancellazione());
    }
}
