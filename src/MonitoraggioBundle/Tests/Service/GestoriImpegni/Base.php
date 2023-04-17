<?php

namespace MonitoraggioBundle\Tests\Service\GestoriImpegni;

use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\Entity\Richiesta;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use MonitoraggioBundle\Entity\TC38CausaleDisimpegno;
use MonitoraggioBundle\Repository\TC38CausaleDisimpegnoRepository;
use SfingeBundle\Entity\ProgrammaProcedura;
use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Azione;
use SfingeBundle\Entity\ObiettivoSpecifico;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Service\IGestoreImpegni;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Entity\Pagamento;
use ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento;
use MonitoraggioBundle\Entity\TC5TipoOperazione;
use AttuazioneControlloBundle\Entity\MandatoPagamento;

class Base extends TestBaseService {
    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var IGestoreImpegni
     */
    protected $gestore;

    /**
     * @var TC36LivelloGerarchico
     */
    protected $livelloObiettivo;

    private $istruttoria;

    public function setUp() {
        parent::setUp();

        $this->inizializzaRichiesta();

        $tc38Repository = $this->createMock(TC38CausaleDisimpegnoRepository::class);
        $tc38Repository->method('findOneBy')->willReturn(new TC38CausaleDisimpegno());

        $this->em->method('getRepository')->will($this->returnValueMap([
            [TC38CausaleDisimpegno::class, $tc38Repository],
        ]));
    }

    protected function inizializzaRichiesta() {
        $this->richiesta = new Richiesta();
        $this->istruttoria = new IstruttoriaRichiesta();
        $this->istruttoria->setRichiesta($this->richiesta);

        $this->richiesta->setIstruttoria($this->istruttoria);
        $atc = new AttuazioneControlloRichiesta();
        $atc->setRichiesta($this->richiesta);
        $this->richiesta->setAttuazioneControllo($atc);
        $procedura = new Bando();
        $programma = new TC4Programma();
        $programmaProcedura = new ProgrammaProcedura($procedura, $programma);
        $procedura->addMonProcedureProgrammi($programmaProcedura);
        $azione = new Azione();
        $azione->addProcedure($procedura);
        $procedura->addAzioni($azione);
        $this->richiesta->setProcedura($procedura);
        $obiettivo = new ObiettivoSpecifico();
        $azione->setObiettivoSpecifico($obiettivo);
        $this->livelloObiettivo = new TC36LivelloGerarchico();
        $this->livelloObiettivo->addObiettiviSpecifici($obiettivo);
        $obiettivo->setLivelloGerarchico($this->livelloObiettivo);
        $richiestaProgramma = new RichiestaProgramma($this->richiesta, '1', $programma);
        $this->richiesta->addMonProgrammi($richiestaProgramma);
        $richiestaLivello = new RichiestaLivelloGerarchico($richiestaProgramma, $this->livelloObiettivo);
        $richiestaProgramma->addMonLivelliGerarchici($richiestaLivello);

        $protocollo = new RichiestaProtocolloFinanziamento();
        $protocollo->setRegistroPg('PG');
        $protocollo->setAnnoPg('2018');
        $protocollo->setNumPg('123456');
        $protocollo->setRichiesta($this->richiesta);
        $this->richiesta->addRichiesteProtocollo($protocollo);

        $tipoOperazione = new TC5TipoOperazione();
        $this->richiesta->setMonTipoOperazione($tipoOperazione);
    }

    protected function setTipologiaSoggetto(bool $tipologia) {
        $this->richiesta->setMonPrgPubblico($tipologia);
    }

    protected function addImpegno(string $tipologia, float $importo): RichiestaImpegni {
        $impegno = new RichiestaImpegni($this->richiesta);
        $impegno->setTipologiaImpegno($tipologia)
        ->setImportoImpegno($importo);
        $this->richiesta->addMonImpegni($impegno);

        return $impegno;
    }

    protected function setCostoAmmesso(float $costo): void {
        $this->richiesta->getIstruttoria()->setCostoAmmesso($costo);
    }

    protected function addPagamento(float $rendicontatoAmmesso): Pagamento {
        $pagamento = new Pagamento();
        $pagamento->setImportoRendicontatoAmmesso($rendicontatoAmmesso);
        $atc = $this->richiesta->getAttuazioneControllo();
        $atc->addPagamenti($pagamento);
        $pagamento->setAttuazioneControlloRichiesta($atc);

        return $pagamento;
    }

    protected function setMandato(Pagamento $pagamento, float $importo): MandatoPagamento {
        $mandato = new MandatoPagamento();
        $mandato->setPagamento($pagamento);
        $mandato->setImportoPagato($importo);
        $pagamento->setMandatoPagamento($mandato);

        return $mandato;
    }



    public function testValidaNoImpegniSenzaContributo(): void {
        $this->setNaturaCup('03');
        $res = $this->gestore->validaImpegniBeneficiario();

        $this->assertNotNull($res);
        $this->assertEmpty($res);
    }

    public function testValidaNoImpegni(): void {
        $this->setNaturaCup('03');
        $pagamento = $this->addPagamento(1000);
        $mandato = $this->setMandato($pagamento, 1000);

        $res = $this->gestore->validaImpegniBeneficiario();

        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
    }

    public function testValidaImpegno(): void {
        $this->setNaturaCup('03');
        $pagamento = $this->addPagamento(1000);
        $mandato = $this->setMandato($pagamento, 1000);
        $impegno = $this->addImpegno('I', 1000);
        $impegno->setDataImpegno(new \DateTime('yesterday'));

        $res = $this->gestore->validaImpegniBeneficiario();

        $this->assertNotNull($res);
        $this->assertEmpty($res);
    }

    public function testValidaImpegnoNonValido(): void {
        $this->setNaturaCup('03');
        $pagamento = $this->addPagamento(1000);
        $mandato = $this->setMandato($pagamento, 1000);
        $impegno = $this->addImpegno('I', 1000);
        $impegno->setDataImpegno(null);

        $res = $this->gestore->validaImpegniBeneficiario();

        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
    }

    protected function setNaturaCup(string $natura): void {
        $tc5 = $this->richiesta->getMonTipoOperazione();
        $tc5->setCodiceNaturaCup($natura);
    }
}
