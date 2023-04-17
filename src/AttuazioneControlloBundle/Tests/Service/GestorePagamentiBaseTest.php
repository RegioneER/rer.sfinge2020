<?php

namespace AttuazioneControlloBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use AttuazioneControlloBundle\Entity\Pagamento;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\IndicatoreOutput;
use MonitoraggioBundle\Entity\TC44_45IndicatoriOutput;
use AttuazioneControlloBundle\Entity\IterProgetto;
use MonitoraggioBundle\Entity\TC46FaseProcedurale;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use MonitoraggioBundle\Entity\TC38CausaleDisimpegno;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use CipeBundle\Entity\Classificazioni\CupNatura;
use AttuazioneControlloBundle\Entity\ProceduraAggiudicazione;
use MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione;
use MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG;
use SfingeBundle\Entity\Bando;
use AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura;
use AttuazioneControlloBundle\Service\GestorePagamentiBase;
use CipeBundle\Entity\Classificazioni\CupTipologia;
use MonitoraggioBundle\Entity\TC5TipoOperazione;
use MonitoraggioBundle\Service\GestoreImpegniService;
use MonitoraggioBundle\Service\IGestoreImpegni;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;

class GestorePagamentiBaseTest extends KernelTestCase {
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        self::bootKernel();
        $this->container = static::$kernel->getContainer();
    }

    /**
     * @dataProvider getValidaMonitoraggioIndicatori
     */
    public function testValidaMonitoraggioIndicatori(Pagamento $pagamento, $risultatoAtteso) {
        $istanzaGestore = new GestorePagamentiBase($this->container);
        $esito = $istanzaGestore->validaMonitoraggioIndicatori($pagamento);
        $this->assertEquals($risultatoAtteso, $esito->getEsito());
    }

    /**
     * @return array
     */
    public function getValidaMonitoraggioIndicatori() {
        $tc44_45 = new TC44_45IndicatoriOutput();
        $tc44_45->setResponsabilitaUtente(true);

        //Caso ideale -> TRUE
        $pagamento1 = self::createPagamento(ModalitaPagamento::SALDO_FINALE);
        $richiesta1 = $pagamento1->getAttuazioneControlloRichiesta()->getRichiesta();
        $ind1 = new IndicatoreOutput($richiesta1);
        $ind1->setValoreRealizzato('1')
            ->setIndicatore($tc44_45)
            ->setValProgrammato('1');
        $richiesta1->addMonIndicatoreOutput($ind1);

        //Caso senza valore realizzato -> FALSE
        $pagamento2 = self::createPagamento(ModalitaPagamento::SALDO_FINALE);
        $richiesta2 = $pagamento2->getAttuazioneControlloRichiesta()->getRichiesta();
        $ind2 = new IndicatoreOutput($richiesta2);
        $richiesta2->addMonIndicatoreOutput($ind2);
        $ind2->setValoreRealizzato(null)
            ->setIndicatore($tc44_45)
            ->setValProgrammato('1');

        //Caso pagamento in fase anticipo -> TRUE
        $pagamento3 = self::createPagamento(ModalitaPagamento::ANTICIPO);
        $richiesta3 = $pagamento2->getAttuazioneControlloRichiesta()->getRichiesta();
        $ind3 = new IndicatoreOutput($richiesta3);
        $richiesta3->addMonIndicatoreOutput($ind3);
        $ind3->setValoreRealizzato(null)
            ->setIndicatore($tc44_45)
            ->setValProgrammato('1');

        //Valore programmato NULL -> True per retrocompatibilita
        $pagamento4 = self::createPagamento(ModalitaPagamento::SALDO_FINALE);
        $richiesta4 = $pagamento4->getAttuazioneControlloRichiesta()->getRichiesta();
        $ind4 = new IndicatoreOutput($richiesta4);
        $ind4->setValoreRealizzato('1000')
            ->setIndicatore($tc44_45)
            ->setValProgrammato(null);
        $richiesta4->addMonIndicatoreOutput($ind4);

        //Valore realizzato tipo stringa -> FALSE
        $pagamento5 = self::createPagamento(ModalitaPagamento::SALDO_FINALE);
        $richiesta5 = $pagamento5->getAttuazioneControlloRichiesta()->getRichiesta();
        $ind5 = new IndicatoreOutput($richiesta5);
        $ind5->setValoreRealizzato('stringa')
            ->setIndicatore($tc44_45)
            ->setValProgrammato('1');
        $richiesta5->addMonIndicatoreOutput($ind5);

        //Valore realizzato negativo -> FALSE
        $pagamento6 = self::createPagamento(ModalitaPagamento::SALDO_FINALE);
        $richiesta6 = $pagamento6->getAttuazioneControlloRichiesta()->getRichiesta();
        $ind6 = new IndicatoreOutput($richiesta6);
        $ind6->setValoreRealizzato('-1')
            ->setIndicatore($tc44_45)
            ->setValProgrammato('1');
        $richiesta6->addMonIndicatoreOutput($ind6);

        //Pagamento con due indicatore 1 valido 1 no -> FALSE
        $pagamento7 = self::createPagamento(ModalitaPagamento::SALDO_FINALE);
        $richiesta7 = $pagamento7->getAttuazioneControlloRichiesta()->getRichiesta();
        $ind7_1 = new IndicatoreOutput($richiesta7);
        $richiesta7->addMonIndicatoreOutput($ind7_1);
        $ind7_1->setValoreRealizzato('-1')
            ->setIndicatore($tc44_45)
            ->setValProgrammato('1');
        $ind7_2 = new IndicatoreOutput($richiesta7);
        $ind7_2->setValoreRealizzato('1')
            ->setIndicatore($tc44_45)
            ->setValProgrammato('1');
        $richiesta7->addMonIndicatoreOutput($ind7_2);

        //2 Indicatori validi -> TRUE
        $pagamento8 = self::createPagamento(ModalitaPagamento::SALDO_FINALE);
        $richiesta8 = $pagamento6->getAttuazioneControlloRichiesta()->getRichiesta();
        $ind8_1 = new IndicatoreOutput($richiesta8);
        $ind8_1->setValoreRealizzato('1')
            ->setIndicatore($tc44_45)
            ->setValProgrammato('1');
        $ind8_2 = new IndicatoreOutput($richiesta8);
        $ind8_2->setValoreRealizzato('1000')
            ->setIndicatore($tc44_45)
            ->setValProgrammato(null);
        $richiesta8->addMonIndicatoreOutput($ind8_1);
        $richiesta8->addMonIndicatoreOutput($ind8_2);

        return [
            [$pagamento1, true],
            [$pagamento2, false],
            [$pagamento3, true],
            [$pagamento4, true],
            [$pagamento5, false],
            [$pagamento6, false],
            [$pagamento7, false],
            [$pagamento8, true],
        ];
    }

    /**
     * @return Pagamento
     */
    private static function createPagamento($modalita): Pagamento {
        $modalitaSaldo = new ModalitaPagamento();
        $modalitaSaldo->setCodice($modalita);
        $pagamento1 = new Pagamento();
        $pagamento1->setModalitaPagamento($modalitaSaldo);
        $procedura = new Bando();
        $modalitaPagamentoProcedura = new ModalitaPagamentoProcedura();
        $modalitaPagamentoProcedura->setProcedura($procedura);
        $modalitaPagamentoProcedura->setModalitaPagamento($modalitaSaldo);
        $procedura->addModalitaPagamento($modalitaPagamentoProcedura);
        $richiesta = new Richiesta();
        $richiesta->setMonTipoOperazione(new TC5TipoOperazione());
        $atc = new AttuazioneControlloRichiesta();
        $richiesta->setAttuazioneControllo($atc);
        $richiesta->setProcedura($procedura);
        $procedura->addRichieste($richiesta);
        $atc->setRichiesta($richiesta);
        $pagamento1->setAttuazioneControlloRichiesta($atc);
        $atc->addPagamenti($pagamento1);
        $istruttoria = new IstruttoriaRichiesta();
        $istruttoria->setRichiesta($richiesta);
        $richiesta->setIstruttoria($istruttoria);
        $istruttoria->setCupNatura(new CupNatura());
        $istruttoria->setCupTipologia(new CupTipologia());

        return $pagamento1;
    }

    public function testValidaImpegniValidi(): void {
        $pagamento = self::generateImpegni(ModalitaPagamento::SALDO_FINALE, '2017-01-01', 'I', 99);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $gestoreImpegniService = $this->createMock(GestoreImpegniService::class);
        $gestoreImpegni = $this->createMock(IGestoreImpegni::class);
        $gestoreImpegniService->method('getGestore')->willReturn($gestoreImpegni);
        $this->container->set('monitoraggio.impegni', $gestoreImpegniService);
        
        $gestoreImpegni->expects($this->once())->method('validaImpegniBeneficiario')->willReturn(new ConstraintViolationList());
        
        $istanzaGestore = new GestorePagamentiBase($this->container);
        $res = $istanzaGestore->validaImpegni($pagamento);

        $this->assertNotNull($res);
        $this->assertTrue($res->getEsito());
    }

    public function testValidaImpegniNonValidi(): void {
        $pagamento = self::generateImpegni(ModalitaPagamento::SALDO_FINALE, '2017-01-01', 'I', 99);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $gestoreImpegniService = $this->createMock(GestoreImpegniService::class);
        $gestoreImpegni = $this->createMock(IGestoreImpegni::class);
        $gestoreImpegniService->method('getGestore')->willReturn($gestoreImpegni);
        $this->container->set('monitoraggio.impegni', $gestoreImpegniService);

        $violazione = new ConstraintViolation('msg', 'template', [], '','','valore', false, 1);
        $elencoViolazioni = new ConstraintViolationList([$violazione]);
        $gestoreImpegni->expects($this->once())->method('validaImpegniBeneficiario')->willReturn($elencoViolazioni);
        $istanzaGestore = new GestorePagamentiBase($this->container);
        $res = $istanzaGestore->validaImpegni($pagamento);

        $this->assertNotEmpty($res);
        $this->assertFalse($res->getEsito());
    }

    /**
     * @param string                     $stato
     * @param string|null                $data
     * @param string                     $tipologia
     * @param float                      $importo
     * @param TC38CausaleDisimpegno|null $tc38
     * @param string|null                $note
     *
     * @return Pagamento
     */
    private static function generateImpegni($stato, ?string $data, string $tipologia, float $importo, TC38CausaleDisimpegno $tc38 = null, $note = null, string $codiceNatura = '06'): Pagamento {
        $pagamento1 = self::createPagamento($stato);
        $richiesta1 = $pagamento1->getAttuazioneControlloRichiesta()->getRichiesta();
        $istruttoria = new IstruttoriaRichiesta();
        $richiesta1->setIstruttoria($istruttoria);
        $istruttoria->setRichiesta($istruttoria);
        $natura = new CupNatura();
        $istruttoria->setCupNatura($natura);
        $natura->setCodice($codiceNatura);

        $impegno = new RichiestaImpegni($richiesta1);
        $impegno->setDataImpegno(\is_null($data) ? null : new \DateTime($data))
        ->setTipologiaImpegno($tipologia)
        ->setImportoImpegno($importo)
        ->setNoteImpegno($note)
        ->setTc38CausaleDisimpegno($tc38);
        $richiesta1->addMonImpegni($impegno);

        return $pagamento1;
    }

    /**
     * @dataProvider getProcedureAggiudicazioneValues
     */
    public function testProcedureAggiudicazione(Pagamento $pagamento, $codiceNatura, $risultatoAtteso) {
        $natura = new CupNatura(); 
        $natura->setCodice($codiceNatura); 
        $istruttoria = new IstruttoriaRichiesta();  
        $istruttoria->setCupNatura($natura);
        $atc = $pagamento->getAttuazioneControlloRichiesta();
        $atc->setProcedureAggiudicazione(true);
        $atc->getRichiesta()->setIstruttoria($istruttoria);

        $istanzaGestore = new GestorePagamentiBase($this->container);
        $esito = $istanzaGestore->validaProceduraAggiudicazione($pagamento);
        $this->assertEquals($esito->getEsito(), $risultatoAtteso);
    }

    public function getProcedureAggiudicazioneValues() {
        $dummyTC22 = new TC22MotivoAssenzaCIG();
        $dummyTC23 = new TC23TipoProceduraAggiudicazione();
        return [
            [self::generateProceduraAggiudicazione(ModalitaPagamento::ANTICIPO, '123456', null, null, null, null, null, null, null), '01', true],
            [self::generateProceduraAggiudicazione(ModalitaPagamento::ANTICIPO, '9999', null, null, null, null, null, null), '01', false],
            [self::generateProceduraAggiudicazione(ModalitaPagamento::ANTICIPO, '9999', null, null, null, null, null, null), CupNatura::CONCESSIONE_INCENTIVI_ATTIVITA_PRODUTTIVE, true],
            [self::generateProceduraAggiudicazione(ModalitaPagamento::ANTICIPO, '9999', null, null, null, null, null, $dummyTC22), '03', false],
            [self::generateProceduraAggiudicazione(ModalitaPagamento::ANTICIPO, '9999', 'descrizione', null, null, null, null, $dummyTC22), '01', false],
            [self::generateProceduraAggiudicazione(ModalitaPagamento::ANTICIPO, '9999', 'descrizione', 9999, null, null, null, $dummyTC22), '03', false],
            [self::generateProceduraAggiudicazione(ModalitaPagamento::ANTICIPO, '9999', 'descrizione', 9999, '2015-01-01', 123, '2016-01-01', $dummyTC22, $dummyTC23), '03', true],
        ];
    }

    public static function generateProceduraAggiudicazione($modalita, $cig, $descrizione, $importo, $data, $importoAgg, $dataPubblicazione, TC22MotivoAssenzaCIG $motivoAssenzaCig = null, TC23TipoProceduraAggiudicazione $tipoProcedura = null) {
        $pagamento = self::createPagamento($modalita);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $proceduraAggiudicazione = new ProceduraAggiudicazione($richiesta);
        $proceduraAggiudicazione->setCig($cig)
            ->setMotivoAssenzaCig($motivoAssenzaCig)
            ->setDescrizioneProceduraAggiudicazione($descrizione)
            ->setImportoProceduraAggiudicazione($importo)
            ->setDataAggiudicazione(\is_null($data) ? null : new \DateTime($data))
            ->setTipoProceduraAggiudicazione($tipoProcedura)
            ->setImportoAggiudicato($importoAgg)
            ->setDataPubblicazione(\is_null($dataPubblicazione) ? null : new \DateTime($dataPubblicazione));
        $richiesta->addMonProcedureAggiudicazione($proceduraAggiudicazione);

        return $pagamento;
    }
}
