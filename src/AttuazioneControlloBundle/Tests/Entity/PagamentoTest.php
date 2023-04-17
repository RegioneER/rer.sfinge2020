<?php

namespace AttuazioneControlloBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use AttuazioneControlloBundle\Entity\Pagamento;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use SfingeBundle\Entity\Bando;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura;
use AttuazioneControlloBundle\Entity\ProrogaRendicontazione;
use Doctrine\Common\Collections\ArrayCollection;

class PagamentoTest extends TestCase {
    const MOD_PAG = 'MOD_PAG';
    /**
     * @var Pagamento
     */
    protected $pagamento;

    public function setUp() {
        $atc = new AttuazioneControlloRichiesta();
        $richiesta = new Richiesta();
        $richiesta->setAttuazioneControllo($atc);
        $atc->setRichiesta($richiesta);
        $procedura = new Bando();
        $richiesta->setProcedura($procedura);
        $this->pagamento = new Pagamento();
        $this->pagamento->setAttuazioneControlloRichiesta($atc);
        $modalita = new ModalitaPagamento();
        $modalita->setCodice(self::MOD_PAG);
        $modalita->setCodice('codice');
        $this->pagamento->setModalitaPagamento($modalita);

        $modalitaProcedura = new ModalitaPagamentoProcedura();
        $modalitaProcedura->setModalitaPagamento($modalita)
        ->setProcedura($procedura);
        $procedura->addModalitaPagamento($modalitaProcedura);
    }

    public function testIsRendicontazioneAttivaModalitaProceduraNonPresente(): void {
        $this->pagamento->getProcedura()->setModalitaPagamento(new ArrayCollection());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Modalita pagamento per la procedura non definito');

        $this->pagamento->isRendicontazioneAttiva();
    }

    public function testDataTermineRendicontazioneProceduraNonPresente(): void {
        $this->pagamento->getProcedura()->setModalitaPagamento(new ArrayCollection());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Modalita pagamento per la procedura non definito');

        $this->pagamento->getDataTermineRendicontazione();
    }

    /**
     * @dataProvider isRendicontazioneAttivaDataProvider
     */
    public function testIsRendicontazioneAttiva(\DateTime $inizio, \DateTime $fine, bool $esito): void {
        $modalita = $this->setDateRendicontazione($inizio, $fine);
        $this->pagamento->setModalitaPagamento($modalita);
        $res = $this->pagamento->isRendicontazioneAttiva();

        $this->assertSame($esito, $res);
    }

    public function isRendicontazioneAttivaDataProvider(): array {
        $ieri = new \DateTime('yesterday');
        $domani = new \DateTime('tomorrow');

        return [
            [$ieri, $ieri, false],
            [$domani, $domani, false],
            [$domani, $ieri, false],
            [$ieri, $domani, true],
        ];
    }

    protected function setDateRendicontazione(?\DateTime $inizio, ?\DateTime $fine): ModalitaPagamento {
        $procedura = $this->pagamento->getProcedura();
        $mp = new ModalitaPagamentoProcedura();
        $mp->setProcedura($procedura);
        $mp->setDataInizioRendicontazione($inizio);
        $mp->setDataFineRendicontazione($fine);
        $procedura->addModalitaPagamento($mp);
        $m = new ModalitaPagamento();
        $m->setCodice(self::MOD_PAG);
        $mp->setModalitaPagamento($m);

        return $m;
    }

    public function testIsRendicontazioneAttivaForzata() {
        $ieri = new \DateTime('yesterday');
        $modalita = $this->setDateRendicontazione($ieri, $ieri);

        $this->pagamento->setModalitaPagamento($modalita)
        ->setDataFineRendicontazioneForzata(new \DateTime('tomorrow'));

        $res = $this->pagamento->isRendicontazioneAttiva();

        $this->assertTrue($res);
    }

    public function testVerificaProrogaAttiva(): void {
        $atc = $this->createMock(AttuazioneControlloRichiesta::class);
        $pr = new ProrogaRendicontazione($atc);
        $pr->setDataInizio(new \DateTime('yesterday'))
        ->setDataScadenza(new \DateTime('tomorrow'))
        ->setModalitaPagamento($this->pagamento->getModalitaPagamento());
        $atc->method('getProrogaRendicontazione')->willReturn($pr);
        $this->pagamento->setAttuazioneControlloRichiesta($atc);

        $this->assertEquals($pr->getDataInizio(), $this->pagamento->getDataAvvioRendicontazione(), "Verifica data inizio");
        $this->assertEquals($pr->getDataScadenza(), $this->pagamento->getDataTermineRendicontazione(), "Verifica data fine");
    }

    public function testVerficaProrogaAssente(): void {
        $modalitaProcedura = $this->pagamento->getModalitaPagamentoProcedura();
        $inizio = new \DateTime('yesterday');
        $fine = new \DateTime('tomorrow');
        $modalitaProcedura->setDataInizioRendicontazione($inizio);
        $modalitaProcedura->setDataFineRendicontazione($fine);

        $this->assertEquals($inizio, $this->pagamento->getDataAvvioRendicontazione(), "Verifica data inizio");
        $this->assertEquals($fine, $this->pagamento->getDataTermineRendicontazione(), "Verifica data fine");
    }

    public function testIsRendicontazioneAttivaConProroga(): void {
        $atc = $this->createMock(AttuazioneControlloRichiesta::class);
        $bando = new Bando();
        $modalitaProcedura = new ModalitaPagamentoProcedura();
        $modalitaProcedura->setDataFineRendicontazione(new \DateTime('yesterday'))
        ->setDataInizioRendicontazione(new \DateTime('yesterday'))
        ->setProcedura($bando)
        ->setModalitaPagamento($this->pagamento->getModalitaPagamento());
        $bando->addModalitaPagamento($modalitaProcedura);
        $richiesta = new Richiesta();
        $richiesta->setProcedura($bando);
        $atc->method('getRichiesta')->willreturn($richiesta);
        $pr = new ProrogaRendicontazione($atc);
        $pr->setDataInizio(new \DateTime('yesterday'))
        ->setDataScadenza(new \DateTime('tomorrow'))
        ->setModalitaPagamento($this->pagamento->getModalitaPagamento());
        $atc->method('getProrogaRendicontazione')->willReturn($pr);
        $this->pagamento->setAttuazioneControlloRichiesta($atc);

        $res = $this->pagamento->isRendicontazioneAttiva();

        $this->assertTrue($res);
    }

    /**
     * @dataProvider modalitaPagamentoProceduraDataProvider
     * @param mixed $finestraRichiesta
     * @param mixed $finestraRendicontazione
     * @param mixed $esito
     */
    public function testModalitaPagamentoProcedura($finestraRichiesta, $finestraRendicontazione, $esito): void {
        $richiesta = $this->pagamento->getRichiesta();
        $richiesta->setFinestraTemporale($finestraRichiesta);
        /** @var ModalitaPagamentoProcedura $modalitaPagProcedura */
        $modalitaPagProcedura = $richiesta->getProcedura()->getModalitaPagamento()->first();
        $modalitaPagProcedura->setFinestraTemporale($finestraRendicontazione);

        $res = $this->pagamento->getModalitaPagamentoProcedura();

        $this->assertEquals($esito, !\is_null($res));
    }

    public function modalitaPagamentoProceduraDataProvider(): array {
        return [
            //richiesta     modalita    esito
            [null,          null,       true],
            [1,             null,       true],
            [null,              1,      false],
            [1,                 1,      true],
            [1,                 2,      false],
        ];
    }
}
