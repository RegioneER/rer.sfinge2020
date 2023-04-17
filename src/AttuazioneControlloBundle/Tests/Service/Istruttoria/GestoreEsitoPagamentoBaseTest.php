<?php

namespace AttuazioneControlloBundle\Tests\Service\Istruttoria;

use BaseBundle\Tests\Service\TestBaseService;
use AttuazioneControlloBundle\Service\Istruttoria\GestoreEsitoPagamentoBase;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Service\Istruttoria\GestorePagamentiBase;
use AttuazioneControlloBundle\Service\Istruttoria\GestorePagamentiService;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Bando;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use AttuazioneControlloBundle\Entity\MandatoPagamento;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use MonitoraggioBundle\Repository\TC39CausalePagamentoRepository;
use MonitoraggioBundle\Entity\TC39CausalePagamento;
use MonitoraggioBundle\Repository\TC33FonteFinanziariaRepository;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use AttuazioneControlloBundle\Entity\Finanziamento;
use MonitoraggioBundle\Repository\TC34DeliberaCIPERepository;
use MonitoraggioBundle\Entity\TC34DeliberaCIPE;
use MonitoraggioBundle\Repository\TC35NormaRepository;
use MonitoraggioBundle\Entity\TC35Norma;
use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use MonitoraggioBundle\Service\GestoreFinanziamentoService;
use MonitoraggioBundle\Service\GestoriFinanziamento\Privato;
use AttuazioneControlloBundle\Entity\VariazionePianoCosti;

class GestoreEsitoPagamentoBaseTest extends TestBaseService {
    /**
     * @var GestoreEsitoPagamentoBase
     */
    protected $gestoreEsito;

    /**
     * @var GestorePagamentiBase|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gestoreIstruttoria;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var TC33FonteFinanziaria
     */
    protected $tc33Privato;

    /**
     * @var TC33FonteFinanziaria
     */
    protected $tc33UE;

    /**
     * @var TC33FonteFinanziaria
     */
    protected $tc33Stato;

    /**
     * @var TC33FonteFinanziaria
     */
    protected $tc33Regione;

    /**
     * @var TC35Norma
     */
    protected $norma;

    /**
     * @var TC34DeliberaCIPE
     */
    protected $delibera;

    public function setUp() {
        parent::setUp();

        $this->gestoreIstruttoria = $this->createMock(GestorePagamentiBase::class);
        $gestoreIstruttoriaService = $this->createMock(GestorePagamentiService::class);
        $gestoreIstruttoriaService->method('getGestore')->willReturn($this->gestoreIstruttoria);
        $this->container->set('gestore_istruttoria_pagamenti', $gestoreIstruttoriaService);
        $this->gestoreEsito = new GestoreEsitoPagamentoBase($this->container);
        $this->request = new Request();
        $this->requestStack->push($this->request);

        $atc = new AttuazioneControlloRichiesta();
        $this->richiesta = new Richiesta();
        $atc->setRichiesta($this->richiesta);
        $this->richiesta->setAttuazioneControllo($atc);
        $procedura = new Bando();
        $this->richiesta->setProcedura($procedura);
        $istruttoria = new IstruttoriaRichiesta();
        $istruttoria->setRichiesta($this->richiesta);
        $this->richiesta->setIstruttoria($istruttoria);

        $this->tc33Privato = new TC33FonteFinanziaria(TC33FonteFinanziaria::PRIVATO);
        $this->tc33UE = new TC33FonteFinanziaria(TC33FonteFinanziaria::FESR);
        $this->tc33Stato = new TC33FonteFinanziaria(TC33FonteFinanziaria::STATO);
        $this->tc33Regione = new TC33FonteFinanziaria(TC33FonteFinanziaria::REGIONE);
        $this->norma = new TC35Norma();
        $this->delibera = new TC34DeliberaCIPE();
    }

    protected function inizializzaTestMandato(): void {
        $this->gestoreIstruttoria->expects($this->atLeastOnce())->method('getAmmissibilitaChecklist')->willReturn(true);
        $this->container->get('router')->method('generate')->willReturn('url');
        $this->formAction(true);

        $tc39Repository = $this->createMock(TC39CausalePagamentoRepository::class);
        $tc39 = new TC39CausalePagamento();
        $tc39Repository->method('findOneBy')->willReturn($tc39);
        $tc33Repository = $this->createMock(TC33FonteFinanziariaRepository::class);
        $tc33 = new TC33FonteFinanziaria();
        $tc33Repository->method('findOneBy')->will($this->returnValueMap([
            [["cod_fondo" => "PRT"], null, new TC33FonteFinanziaria('PRT')],
            [["cod_fondo" => "ERDF"], null, new TC33FonteFinanziaria('ERDF')],
            [["cod_fondo" => "FDR"], null, new TC33FonteFinanziaria('FDR')],
            [["cod_fondo" => "FPREG"], null, new TC33FonteFinanziaria('FPREG')],
        ]));
        $tc34Repository = $this->createMock(TC34DeliberaCIPERepository::class);
        $tc34Repository->method('findOneBy')->willReturn($this->delibera);
        $tc35Repository = $this->createMock(TC35NormaRepository::class);
        $tc35Repository->method('findOneBy')->willReturn($this->norma);

        $this->em->method('getRepository')->will($this->returnValueMap([
            ['MonitoraggioBundle:TC39CausalePagamento', $tc39Repository],
            ['MonitoraggioBundle:TC33FonteFinanziaria', $tc33Repository],
            [TC33FonteFinanziaria::class, $tc33Repository],
            ['MonitoraggioBundle:TC34DeliberaCIPE', $tc34Repository],
            ['MonitoraggioBundle:TC35Norma', $tc35Repository],
        ]));
    }

    protected function setTipologiaSoggetto(string $tipologia): void {
        $istruttoria = $this->richiesta->getIstruttoria();
        $istruttoria->setTipologiaSoggetto($tipologia);
    }

    protected function creaPagamento(string $modalitaPagamento): Pagamento {
        $atc = $this->richiesta->getAttuazioneControllo();

        $pagamento = new Pagamento();
        $pagamento->setAttuazioneControlloRichiesta($atc);
        $atc->addPagamenti($pagamento);
        $mandato = new MandatoPagamento();
        $mandato->setPagamento($pagamento);
        $mandato->setDataMandato(new \DateTime);
        $pagamento->setMandatoPagamento($mandato);
        $modalita = new ModalitaPagamento();
        $modalita->setCodice($modalitaPagamento);
        $pagamento->setModalitaPagamento($modalita);

        return $pagamento;
    }

    protected function setCostoAmmesso(float $costo): void {
        $this->richiesta->getIstruttoria()->setCostoAmmesso($costo);
    }

    protected function setContributoAmmesso(float $costo): void {
        $this->richiesta->getIstruttoria()->setContributoAmmesso(100);
    }

    protected function addGiustificativo(Pagamento $pagamento): GiustificativoPagamento {
        $giustificativo = new GiustificativoPagamento();
        $pagamento->addGiustificativi($giustificativo);

        return $giustificativo;
    }

    protected function formAction(bool $post = true): void {
        $this->request->setMethod($post ? Request::METHOD_POST : Request::METHOD_GET);
        $form = $this->createMock(Form::class);
        $form->method('isSubmitted')->willReturn($post);
        $form->method('isValid')->willReturn($post);
        $this->formFactory->method('create')->willReturn($form);
    }

    protected function aggiungiFinanziamento(TC33FonteFinanziaria $fonte, float $importo): Finanziamento {
        $f = new Finanziamento($this->richiesta);
        $f->setTc33FonteFinanziaria($fonte);
        $f->setTc35Norma($this->norma);
        $f->setTc34DeliberaCipe($this->delibera);
        $f->setImporto($importo);
        $this->richiesta->addMonFinanziamenti($f);

        return $f;
    }

    protected function assertFinanziamento(TC33FonteFinanziaria $fonte, float $importoAtteso): void {
        $finanziamenti = $this->richiesta->getMonFinanziamenti($fonte->getCodFondo());

        $this->assertNotEmpty($finanziamenti);
        /** @var Finanziamento $finanziamento */
        $finanziamento = $finanziamenti->first();

        $this->assertEquals($importoAtteso, $finanziamento->getImporto(), '', 0.001);
    }

    protected function aggiungiVariazione(float $costoAmmesso, float $contributoConcesso): void {
        $atc = $this->richiesta->getAttuazioneControllo();
        $variazione = new VariazionePianoCosti();
        $variazione->setAttuazioneControlloRichiesta($atc);
        $variazione->setCostoAmmesso($costoAmmesso);
        $variazione->setContributoAmmesso($contributoConcesso);

        $atc->addVariazioni($variazione);
    }

    public function testFinanziamento() {
        $this->formAction();
        $this->inizializzaTestMandato();
        $this->setTipologiaSoggetto('PRIVATO');

        $this->setContributoAmmesso(50);  //contributo concesso
        $this->setCostoAmmesso(50); //costo ammesso

        $this->aggiungiVariazione(100, 100);

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(100); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(100); //Contributo erogato
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(100); //rendicontato ammesso

        $finanzimentoService = $this->createMock(GestoreFinanziamentoService::class);
        $gestoreFinanziamentoPrivato = $this->createMock(Privato::class);
        $finanzimentoService
            ->expects($this->once())
            ->method('getGestore')
            ->with($this->richiesta)
            ->willReturn($gestoreFinanziamentoPrivato);
        $this->container->set('monitoraggio.gestore_finanziamento', $finanzimentoService);

        $gestoreFinanziamentoPrivato->expects($this->once())->method('aggiornaFinanziamento');
        $gestoreFinanziamentoPrivato->expects($this->once())->method('persistFinanziamenti');

        $pagamento = $this->creaPagamento(ModalitaPagamento::SALDO_FINALE);
        $this->gestoreEsito->mandato($pagamento);
    }
}
