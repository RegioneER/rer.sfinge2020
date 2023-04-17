<?php

namespace MonitoraggioBundle\Tests\Service;

use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Service\IGestoreIterProgetto;
use MonitoraggioBundle\Service\GestoreIterProgettoBase;
use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\TipoIter;
use MonitoraggioBundle\Entity\TC46FaseProcedurale;
use AttuazioneControlloBundle\Entity\IterProgetto;
use MonitoraggioBundle\Repository\TC46FaseProceduraleRepository;
use RichiesteBundle\Utility\EsitoValidazione;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use MonitoraggioBundle\Entity\TC5TipoOperazione;
use CipeBundle\Entity\Classificazioni\CupNatura;
use SfingeBundle\Entity\Procedura;
use SfingeBundle\Entity\ProceduraPA;
use BaseBundle\Entity\StatoRichiesta;
use SfingeBundle\Entity\IngegneriaFinanziaria;

class GestoreIterProgettoBaseTest extends TestBaseService {
    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var IGestoreIterProgetto
     */
    protected $gestore;

    public function setUp() {
        parent::setUp();
        $this->richiesta = new Richiesta();
        $procedura = new Bando();
        $this->richiesta->setProcedura($procedura);
        $procedura->addRichieste($this->richiesta);
        $tipoIter = new TipoIter();
        $tipoIter->setCodice(TC46FaseProcedurale::NATURA_LAVORI_PUBBLICI);
        $procedura->setTipoIter($tipoIter);
        $atc = new AttuazioneControlloRichiesta();
        $atc->setRichiesta($this->richiesta);
        $this->richiesta->setAttuazioneControllo($atc);
        $this->richiesta->setMonTipoOperazione(new TC5TipoOperazione());

        $this->gestore = new GestoreIterProgettoBase($this->container, $this->richiesta);
    }

    public function testAggiungiIterPubblico() {
        $this->setTipoIter(TC46FaseProcedurale::NATURA_LAVORI_PUBBLICI);
        $faseProcedurale = new TC46FaseProcedurale();
        $tc46Repository = $this->createMock(TC46FaseProceduraleRepository::class);
        $tc46Repository->method('findBy')->willReturn([
            $faseProcedurale,
        ]);

        $this->em->method('getRepository')->willreturn($tc46Repository);

        $this->em->expects($this->once())->method('persist')->with(
            $this->logicalAnd(
                $this->isInstanceOf(IterProgetto::class),
                $this->callback(function (IterProgetto $iter) {
                    return $iter->getRichiesta() == $this->richiesta;
                })
            )
        );

        $this->gestore->aggiungiFasiProcedurali();
    }

    protected function setTipoIter(?string $codice): void {
        $this->richiesta->getProcedura()->getTipoIter()->setCodice($codice);
        $this->richiesta->getMonTipoOperazione()->setCodiceNaturaCup($codice);
    }

    /**
     * @dataProvider hasSezioneRichiestaVisibileDataProvider
     */
    public function testHasSezioneRichiestaVisibile(Procedura $bando, string $statoRichiesta, bool $esito): void {
        $stato = new StatoRichiesta();
        $stato->setCodice($statoRichiesta);
        $this->richiesta->setStato($stato);
        $this->richiesta->setProcedura($bando);

        $res = $this->gestore->hasSezioneRichiestaVisibile();

        $this->assertSame($esito, $res);
    }

    public function hasSezioneRichiestaVisibileDataProvider(): array {
        $bandoAcquisizione = $this->creaProcedura(Bando::class, '02');
        $bandoPubblico = $this->creaProcedura(Bando::class, '03');
        $ProceduraPAPubblico = $this->creaProcedura(ProceduraPA::class, '03');
        $proceduraPAPrivato = $this->creaProcedura(ProceduraPA::class, '02');
        $ingegneriaFinanziaria = $this->creaProcedura(IngegneriaFinanziaria::class, '02');

        return [
            [$bandoAcquisizione, StatoRichiesta::PRE_PROTOCOLLATA, false],
            [$bandoPubblico, StatoRichiesta::PRE_PROTOCOLLATA, true],
            [$ProceduraPAPubblico, StatoRichiesta::PRE_INSERITA, true],
            [$ProceduraPAPubblico, StatoRichiesta::PRE_PROTOCOLLATA, true],
            [$proceduraPAPrivato, StatoRichiesta::PRE_PROTOCOLLATA, false],
            [$ingegneriaFinanziaria, StatoRichiesta::PRE_PROTOCOLLATA, true],
        ];
    }

    protected function creaProcedura(string $classe, string $codiceIterProgetto): Procedura {
        $iter = new TipoIter();
        $iter->setCodice($codiceIterProgetto);
        /** @var Procedura $procedura */
        $procedura = new $classe();
        $procedura->setTipoIter($iter);

        return $procedura;
    }

    /**
     * @dataProvider validazioneSenzaIterDataProvider
     */
    public function testValidazioneIter(bool $expected, IterProgetto $iter): void {
        $iter->setRichiesta($this->richiesta);
        $this->richiesta->addMonIterProgetti($iter);
        $tc46Repo = $this->createMock(TC46FaseProceduraleRepository::class);
        $tc46Repo->method('findBy')->willReturn([]);
        $this->em->method('getRepository')->will($this->returnValueMap([
            [TC46FaseProcedurale::class, $tc46Repo],
            ['MonitoraggioBundle:TC46FaseProcedurale', $tc46Repo],
        ]));
        $res = $this->gestore->validaInPresentazioneDomanda();

        $this->assertNotNull($res);
        $this->assertInstanceOf(EsitoValidazione::class, $res);
        $this->assertSame($expected, $res->getEsito());
    }

    public function validazioneSenzaIterDataProvider(): array {
        $i1 = new IterProgetto();
        $i1->setFaseProcedurale(new TC46FaseProcedurale());
        $i1->setDataInizioPrevista(new \DateTime('2000-01-01'));

        $i2 = clone $i1;
        $i2->setDataFinePrevista(new \DateTime('1990-01-01'));

        $i3 = clone $i2;
        $i3->setDataInizioPrevista(new \DateTime('2000-01-01'));
        $i3->setDataFinePrevista(new \DateTime('2001-01-01'));

        return [
            [false, new IterProgetto()],
            [false, $i1],
            [false, $i2],
            [true, $i3],
        ];
    }

    /**
     * @dataProvider getValidaMonitoraggioFasiProcedurali
     */
    public function testValidaMonitoraggioFasiProcedurali(string $modalita, ?string $dataInizioPrevista, ?string $dataFinePrevista,
                                                         ?string $dataInizioEffettivo, ?string $dataFineEffettivo, bool $risultatoAtteso) {
        $this->insertPagamento($modalita);
        $this->setTipoIter(CupNatura::REALIZZAZIONE_LAVORI_PUBBLICI);
        $this->insertFaseProcedurale($dataInizioPrevista, $dataFinePrevista, $dataInizioEffettivo, $dataFineEffettivo);

        $esito = $this->gestore->validaInSaldo();

        $this->assertEquals($risultatoAtteso, $esito->getEsito());
    }

    /**
     * @return array
     */
    public function getValidaMonitoraggioFasiProcedurali(): array {
        return [
            [ModalitaPagamento::SALDO_FINALE, '2000-01-01', '2015-01-01', '2017-01-01', '2017-01-01', true],
            [ModalitaPagamento::SALDO_FINALE, '2000-01-01', '2017-01-01', '2017-01-01', '2018-01-02', true],
            [ModalitaPagamento::SALDO_FINALE, '2000-01-01', '2000-01-01', '2017-01-01', '2018-01-02', true],
            [ModalitaPagamento::SALDO_FINALE, '2000-01-01', '2015-01-01', 		  null, '2017-01-01', false],
            [ModalitaPagamento::SALDO_FINALE, '2000-01-01', '2015-01-01', '2017-01-01', 		null, false],
            [ModalitaPagamento::SALDO_FINALE, 		  null, '2000-01-01', '2000-01-01', '2015-01-01', true],
            [ModalitaPagamento::SALDO_FINALE, '2000-01-01', 		null, '2017-01-01', '2019-01-01', true],
            [ModalitaPagamento::SALDO_FINALE,         null, 		null, '2017-01-01', '2019-01-01', true],
            [ModalitaPagamento::SALDO_FINALE, '2000-01-01', 		null, '2017-01-01', '2015-01-01', false],
            [ModalitaPagamento::SALDO_FINALE,         null, 		null, '2017-01-01', '2015-01-01', false],
            [ModalitaPagamento::SALDO_FINALE, '2000-01-01', '2017-01-01', 		  null, 		null, false],
            [ModalitaPagamento::SAL, 		  '2000-01-01',	'2017-01-01',		  null, 		null, true],
        ];
    }

    private function insertFaseProcedurale(?string $dataInizioPrevista, ?string $dataFinePrevista,
                                            ?string $dataInizioEffettivo, ?string $dataFineEffettivo): IterProgetto {
        $fase = new TC46FaseProcedurale();

        $iter = new IterProgetto($this->richiesta);
        $iter
            ->setDataInizioPrevista(\is_null($dataInizioPrevista) ? null : new \DateTime($dataInizioPrevista))
            ->setDataInizioEffettiva(\is_null($dataInizioEffettivo) ? null : new \DateTime($dataInizioEffettivo))
            ->setDataFinePrevista(\is_null($dataFineEffettivo) ? null : new \DateTime($dataFineEffettivo))
            ->setDataFineEffettiva(\is_null($dataFineEffettivo) ? null : new \DateTime($dataFineEffettivo))
            ->setFaseProcedurale($fase);

        $this->richiesta->addMonIterProgetti($iter);

        return $iter;
    }

    protected function insertPagamento(string $codice): Pagamento {
        $pagamento = new Pagamento();
        $atc = $this->richiesta->getAttuazioneControllo();
        $pagamento->setAttuazioneControlloRichiesta($atc);
        $atc->addPagamenti($pagamento);
        $modalita = new ModalitaPagamento();
        $modalita->setCodice($codice);
        $pagamento->setModalitaPagamento($modalita);

        return $pagamento;
    }
}
