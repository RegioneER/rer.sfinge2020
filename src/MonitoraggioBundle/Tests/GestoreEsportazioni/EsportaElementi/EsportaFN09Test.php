<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaFN09;
use MonitoraggioBundle\Repository\FN09SpeseCertificateRepository;
use CertificazioniBundle\Repository\CertificazionePagamentoRepository;
use MonitoraggioBundle\Repository\TC41DomandaPagamentoRepository;
use MonitoraggioBundle\Entity\TC41DomandaPagamento;
use CertificazioniBundle\Entity\CertificazionePagamento;
use MonitoraggioBundle\Model\SpesaCertificata;
use CertificazioniBundle\Entity\Certificazione;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Asse;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use AttuazioneControlloBundle\Entity\Pagamento;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\FN09SpeseCertificate;
use MonitoraggioBundle\Repository\TC36LivelloGerarchicoRepository;


class EsportaFN09Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaFN09
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaFN09($this->container);
    }

    public function testImportazioneInputNonValido():void{
        $this->importazioneConInputNonValido();
    }

    public function testEsportazioneNonNecessaria():void{
        $repo = $this->createMock(FN09SpeseCertificateRepository::class);
        $this->esportazioneNonNecessaria($repo);
    }

    public function testEsportazioneConSuccesso():void{
        $certificazione = new Certificazione();
        $certificazionePagamento = new CertificazionePagamento();
        $certificazionePagamento->setCertificazione($certificazione);
        $spesaCertificata = new SpesaCertificata($certificazionePagamento);
        
        $pagamento = new Pagamento();
        $certificazionePagamento->setPagamento($pagamento);
        $atc = new AttuazioneControlloRichiesta();
        $atc->setRichiesta($this->richiesta);
        $pagamento->setAttuazioneControlloRichiesta($atc);
        $this->richiesta->setAttuazioneControllo($atc);
        $tc41 = new TC41DomandaPagamento();
        $this->setEsportazioneRepositoryValues([$spesaCertificata], $tc41);

        $procedura = new Bando();
        $this->richiesta->setProcedura($procedura);
        $asse = new Asse();
        $procedura->setAsse($asse);
        $liv = new TC36LivelloGerarchico();
        $asse->setLivelloGerarchico($liv);

        $res = $this->esporta->execute($this->richiesta, $this->tavola);
        $this->assertNotNull($res);
    }

    protected function setEsportazioneRepositoryValues(?array $certificazioni, ?TC41DomandaPagamento $domandaPagamento): void{
        $certRepository = $this->createMock(CertificazionePagamentoRepository::class);
        $certRepository->method('findAllSpeseCertificate')->willReturn($certificazioni);

        $domandaPagamentoRepository = $this->createMock(TC41DomandaPagamentoRepository::class);
        $domandaPagamentoRepository->method('findOneBy')->willReturn($domandaPagamento);

        $this->em->method('getRepository')->will(
            $this->returnValueMap([
                ['CertificazioneBundle:CertificazionePagamento', $certRepository],
                ['MonitoraggioBundle:TC41DomandaPagamento', $domandaPagamentoRepository],
            ])
        );
    }

    public function testEsportazioneSenzaRisultati():void{
        $tc41 = new TC41DomandaPagamento();
        $this->setEsportazioneRepositoryValues([], $tc41);
        $res = $this->esporta->execute($this->richiesta, $this->tavola);
        $this->assertNotNull($res);
        $this->assertEmpty($res);
    }

    public function testEsportazioneSenzaDomandaPagamento():void{
        $certificazione = new Certificazione();
        $certificazionePagamento = new CertificazionePagamento();
        $certificazionePagamento->setCertificazione($certificazione);
        $spesaCertificata = new SpesaCertificata($certificazionePagamento);
        
        $pagamento = new Pagamento();
        $certificazionePagamento->setPagamento($pagamento);
        $atc = new AttuazioneControlloRichiesta();
        $atc->setRichiesta($this->richiesta);
        $pagamento->setAttuazioneControlloRichiesta($atc);
        $this->richiesta->setAttuazioneControllo($atc);
        $this->setEsportazioneRepositoryValues([$spesaCertificata], null);

        $procedura = new Bando();
        $this->richiesta->setProcedura($procedura);
        $asse = new Asse();
        $procedura->setAsse($asse);
        $liv = new TC36LivelloGerarchico();
        $asse->setLivelloGerarchico($liv);

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->execute($this->richiesta, $this->tavola);
    }
    
    /**
     * @dataProvider getInput
     */
    public function testImportazioneConSuccesso(array $input):void
    {
        $tc36 = new TC36LivelloGerarchico();
        $repoTc36 = $this->createMockFindOneBy(TC36LivelloGerarchicoRepository::class, $tc36);
        $tc41 = new TC41DomandaPagamento();
        $repoTc41 = $this->createMockFindOneBy(TC41DomandaPagamentoRepository::class, $tc41);
        $this->em->method('getRepository')->will(
            $this->returnValueMap([
              ['MonitoraggioBundle:TC36LivelloGerarchico', $repoTc36],  
              ['MonitoraggioBundle:TC41DomandaPagamento', $repoTc41],  
            ]));
        $res = $this->esporta->importa($input);

        $this->assertNotNull($res);
        $this->assertInstanceOf(FN09SpeseCertificate::class, $res);
        $this->assertSame($tc41, $res->getTc41DomandePagamento());
        $this->assertSame($tc36, $res->getTc36LivelloGerarchico());
    }

    public function getInput(): array{
        return [[[
            'cod_progetto',
            '01/01/2001',
            'tc41',
            'tipologia',
            'tc36',
            '999',
            '888',
            ''
        ]]];
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneSenzaLivello(array $input):void
    {
        $repoTc36 = $this->createMockFindOneBy(TC36LivelloGerarchicoRepository::class, null);
        $tc41 = new TC41DomandaPagamento();
        $repoTc41 = $this->createMockFindOneBy(TC41DomandaPagamentoRepository::class, $tc41);
        $this->em->method('getRepository')->will(
            $this->returnValueMap([
              ['MonitoraggioBundle:TC36LivelloGerarchico', $repoTc36],
              ['MonitoraggioBundle:TC41DomandaPagamento', $repoTc41],
        ]));
            
        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Livello gerarchico non valido');

        $res = $this->esporta->importa($input);
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneSenzaDomandaPagamento(array $input):void
    {
        $tc36 = new TC36LivelloGerarchico();
        $repoTc36 = $this->createMockFindOneBy(TC36LivelloGerarchicoRepository::class, $tc36);
        $repoTc41 = $this->createMockFindOneBy(TC41DomandaPagamentoRepository::class, null);
        $this->em->method('getRepository')->will(
            $this->returnValueMap([
              ['MonitoraggioBundle:TC36LivelloGerarchico', $repoTc36],
              ['MonitoraggioBundle:TC41DomandaPagamento', $repoTc41],
            ]));
        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Domanda pagamento non valida');

        $res = $this->esporta->importa($input);
    }
}