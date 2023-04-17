<?php

namespace AttuazioneControlloBundle\Tests\Service;

use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Bando;
use AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura;
use AttuazioneControlloBundle\Service\GestorePagamentiBase;
use SfingeBundle\Entity\Procedura;
use BaseBundle\Tests\Service\TestBaseService;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\Proroga;
use AttuazioneControlloBundle\Entity\StatoProroga;
use AttuazioneControlloBundle\Entity\ProrogaRendicontazione;

class GestorePagamentiBaseProrogaTest extends TestBaseService {
    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var Procedura
     */
    protected $procedura;

    /**
     * @var GestorePagamentiBase
     */
    protected $service;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->procedura = new Bando();
        $this->richiesta = new Richiesta();
        $this->richiesta->setProcedura($this->procedura);
        $this->procedura->addRichieste($this->richiesta);

        $atc = new AttuazioneControlloRichiesta();
        $atc->setRichiesta($this->richiesta);
        $this->richiesta->setAttuazioneControllo($atc);

        $this->service = new GestorePagamentiBase($this->container);
    }

    public function testGetModalitaPagamentoNessunaModalitaPresente(): void {
        $res = $this->service->getModalitaPagamento($this->richiesta);

        $this->assertEmpty($res);
    }

    public function testGestModalitaPagamentoUnaPresente(): void {
        $modalita = $this->getModalita();
        $this->addModalitaPagamentoProcedura($modalita);

        $res = $this->service->getModalitaPagamento($this->richiesta);

        $this->assertContains($modalita, $res);
    }

    protected function getModalita(string $codice = 'MODALITA', int $ordine = 0): ModalitaPagamento {
        $modalita = new ModalitaPagamento();
        $modalita->setCodice($codice);
        $modalita->setOrdineCronologico($ordine);

        return $modalita;
    }

    protected function addModalitaPagamentoProcedura(ModalitaPagamento $modalita, \DateTime $inizio = null, \DateTime $fine = null): ModalitaPagamentoProcedura {
        $inizio = $inizio ?: new \DateTime('yesterday');
        $fine = $fine ?: new \DateTime('tomorrow');

        $modalitaProcedura = new ModalitaPagamentoProcedura();
        $modalitaProcedura->setModalitaPagamento($modalita);
        $modalitaProcedura->setDataInizioRendicontazione($inizio);
        $modalitaProcedura->setDataFineRendicontazione($fine);
        $modalitaProcedura->setProcedura($this->procedura);
        $this->procedura->addModalitaPagamento($modalitaProcedura);

        return $modalitaProcedura;
    }

    public function testModalitaNonAperta(): void {
        $modalita = $this->getModalita();
        $this->addModalitaPagamentoProcedura($modalita, new \DateTime('tomorrow'));

        $res = $this->service->getModalitaPagamento($this->richiesta);

        $this->assertEmpty($res);
    }

    public function testModalitaUnaVisibileunaChiusa(): void {
        $modalitaSAL = $this->getModalita(ModalitaPagamento::PRIMO_SAL, 1);
        $this->addModalitaPagamentoProcedura($modalitaSAL);
        $modalitaSALII = $this->getModalita(ModalitaPagamento::SECONDO_SAL, 1);
        $this->addModalitaPagamentoProcedura($modalitaSALII, new \DateTime('tomorrow'), new \DateTime('+2 days'));

        $res = $this->service->getModalitaPagamento($this->richiesta);

        $this->assertContains($modalitaSAL, $res);
        $this->assertNotContains($modalitaSALII, $res);
    }

    public function testModalitaDueAperteUnaVisibile(): void {
        $modalitaSAL = $this->getModalita(ModalitaPagamento::PRIMO_SAL, 1);
        $this->addModalitaPagamentoProcedura($modalitaSAL);
        $modalitaSALII = $this->getModalita(ModalitaPagamento::SECONDO_SAL, 2);
        $this->addModalitaPagamentoProcedura($modalitaSALII);

        $res = $this->service->getModalitaPagamento($this->richiesta);

        $this->assertContains($modalitaSAL, $res);
        $this->assertNotContains($modalitaSALII, $res);
    }

    public function testUniSoluzionePresente(): void {
        $modalitaSAL = $this->getModalita(ModalitaPagamento::PRIMO_SAL, 1);
        $this->addModalitaPagamentoProcedura($modalitaSAL);
        $modalitaUnica = $this->getModalita(ModalitaPagamento::UNICA_SOLUZIONE, 99);
        $this->addModalitaPagamentoProcedura($modalitaUnica);

        $res = $this->service->getModalitaPagamento($this->richiesta);

        $this->assertContains($modalitaSAL, $res);
        $this->assertContains($modalitaUnica, $res);
    }

    public function testSaldoPresente(): void {
        $modalitaSAL = $this->getModalita(ModalitaPagamento::PRIMO_SAL, 1);
        $this->addModalitaPagamentoProcedura($modalitaSAL);
        $modalitaSaldo = $this->getModalita(ModalitaPagamento::SALDO_FINALE, 99);
        $this->addModalitaPagamentoProcedura($modalitaSaldo);

        $res = $this->service->getModalitaPagamento($this->richiesta);

        $this->assertContains($modalitaSAL, $res);
        $this->assertContains($modalitaSaldo, $res);
    }

    public function testPagamentoPregresso() {
        $sal = $this->getModalita(ModalitaPagamento::PRIMO_SAL);
        $this->addModalitaPagamentoProcedura($sal);
        $pagamento = new Pagamento();
        $pagamento->setModalitaPagamento($sal);
        $pagamento->setEsitoIstruttoria(true);
        $atc = $this->richiesta->getAttuazioneControllo();
        $pagamento->setAttuazioneControlloRichiesta($atc);
        $atc->addPagamenti($pagamento);

        $res = $this->service->getModalitaPagamento($this->richiesta);

        $this->assertEmpty($res);
    }

    public function testRendicontazioneChiusaProrogaAperta(): void {
        $modalita = $this->getModalita();
        $this->addModalitaPagamentoProcedura($modalita, new \DateTime('tomorrow'));
        $pr = $this->createProrogaRendicontazione($modalita)
            ->setDataInizio(new \DateTime('yesterday'))
            ->setDataScadenza(new \DateTime('tomorrow'));
        
        $res = $this->service->getModalitaPagamento($this->richiesta);

        $this->assertContains($modalita, $res);   
    }


    protected function createProrogaRendicontazione(ModalitaPagamento $modalita): ProrogaRendicontazione{
        $atc = $this->richiesta->getAttuazioneControllo();
        $pr = new ProrogaRendicontazione($atc);
        $pr->setModalitaPagamento($modalita);
        $atc->addProrogheRendicontazione($pr);

        return $pr;
    }

    public function testRendicontazioneChiusaProrogaNonAttiva(): void {
        $modalita = $this->getModalita();
        $this->addModalitaPagamentoProcedura($modalita, new \DateTime('tomorrow'));
        $pr = $this->createProrogaRendicontazione($modalita)
            ->setDataInizio(new \DateTime('tomorrow'))
            ->setDataScadenza(new \DateTime('+2 days'));
        
        $res = $this->service->getModalitaPagamento($this->richiesta);

        $this->assertEmpty($res);   
    }

    public function testRendicontazioneApertaProrogaAltroPagamento(): void {
        
        $modalitaProroga = $this->getModalita('proroga', 1);
        $this->addModalitaPagamentoProcedura($modalitaProroga, new \DateTime('tomorrow'));
        $pr = $this->createProrogaRendicontazione($modalitaProroga)
            ->setDataInizio(new \DateTime('yesterday'))
            ->setDataScadenza(new \DateTime('tomorrow'));

        $modalita = $this->getModalita('pagamento',99);
        $this->addModalitaPagamentoProcedura($modalita);
        
        $res = $this->service->getModalitaPagamento($this->richiesta);

        $this->assertContains($modalita, $res);   
        $this->assertNotContains($modalitaProroga, $res);   
    }
}
