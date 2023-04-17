<?php

namespace MonitoraggioBundle\Tests\Service\GestoriFinanziamento;

use BaseBundle\Tests\Service\TestBaseService;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Service\Istruttoria\GestorePagamentiBase;
use PHPUnit\Framework\MockObject\MockObject;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Bando;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use AttuazioneControlloBundle\Entity\MandatoPagamento;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use MonitoraggioBundle\Repository\TC33FonteFinanziariaRepository;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use AttuazioneControlloBundle\Entity\Finanziamento;
use MonitoraggioBundle\Repository\TC34DeliberaCIPERepository;
use MonitoraggioBundle\Entity\TC34DeliberaCIPE;
use MonitoraggioBundle\Repository\TC35NormaRepository;
use MonitoraggioBundle\Entity\TC35Norma;
use MonitoraggioBundle\Service\GestoriFinanziamento\Privato;
use MonitoraggioBundle\Service\IGestoreFinanziamento;
use RichiesteBundle\Entity\Proponente;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Entity\OrganismoIntermedio;
use GeoBundle\Entity\GeoComune;
use SoggettoBundle\Entity\SoggettoRepository;
use SfingeBundle\Entity\Azione;
use SfingeBundle\Entity\ObiettivoSpecifico;
use SfingeBundle\Entity\Asse;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use SfingeBundle\Entity\Procedura;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use MonitoraggioBundle\Entity\TC4Programma;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use GeoBundle\Entity\GeoProvincia;
use GeoBundle\Entity\GeoRegione;
use MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica;
use MonitoraggioBundle\Repository\TC16LocalizzazioneGeograficaRepository;
use AttuazioneControlloBundle\Entity\VariazionePianoCosti;

class TestGestoreFinanziamento extends TestBaseService {
    /**
     * @var IGestoreFinanziamento
     */
    protected $gestore;

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
     * @var TC33FonteFinanziaria
     */
    protected $tc33AltroPubblico;

    /**
     * @var TC33FonteFinanziaria
     */
    protected $tc33Comune;

    /**
     * @var TC35Norma
     */
    protected $norma;

    /**
     * @var TC34DeliberaCIPE
     */
    protected $delibera;

    /**
     * @var Proponente
     */
    protected $proponente;

    /**
     * @var SoggettoRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $soggettoRepository;

    public function setUp() {
        parent::setUp();

        $atc = new AttuazioneControlloRichiesta();
        $this->richiesta = new Richiesta();
        $atc->setRichiesta($this->richiesta);
        $this->richiesta->setAttuazioneControllo($atc);
        $procedura = $this->creaProcedura();
        $this->richiesta->setProcedura($procedura);
        $istruttoria = new IstruttoriaRichiesta();
        $istruttoria->setRichiesta($this->richiesta);
        $this->richiesta->setIstruttoria($istruttoria);
        $this->proponente = new Proponente($this->richiesta);
        $this->proponente->setMandatario(true);
        $this->richiesta->addProponenti($this->proponente);
        $programma = new TC4Programma();
        $richiestaProgramma = new RichiestaProgramma($this->richiesta, '1', $programma);
        $this->richiesta->addMonProgrammi($richiestaProgramma);
        $richiestaProgramma->addMonLivelliGerarchici(new RichiestaLivelloGerarchico($richiestaProgramma, $procedura->getAsse()->getLivelloGerarchico()));
        $richiestaProgramma->addMonLivelliGerarchici(new RichiestaLivelloGerarchico($richiestaProgramma, $procedura->getAzioni()->first()->getObiettivoSpecifico()->getLivelloGerarchico()));

        $this->tc33Privato = new TC33FonteFinanziaria(TC33FonteFinanziaria::PRIVATO);
        $this->tc33UE = new TC33FonteFinanziaria(TC33FonteFinanziaria::FESR);
        $this->tc33Stato = new TC33FonteFinanziaria(TC33FonteFinanziaria::STATO);
        $this->tc33Regione = new TC33FonteFinanziaria(TC33FonteFinanziaria::REGIONE);
        $this->tc33AltroPubblico = new TC33FonteFinanziaria(TC33FonteFinanziaria::ALTRO_PUBBLICO);
        $this->tc33Comune = new TC33FonteFinanziaria(TC33FonteFinanziaria::COMUNE);
        $this->norma = new TC35Norma();
        $this->delibera = new TC34DeliberaCIPE();

        $tc33Repository = $this->createMock(TC33FonteFinanziariaRepository::class);
        $tc33 = new TC33FonteFinanziaria();
        $tc33Repository->method('findOneBy')->will($this->returnValueMap([
            [["cod_fondo" => TC33FonteFinanziaria::PRIVATO], null, $this->tc33Privato],
            [["cod_fondo" => TC33FonteFinanziaria::FESR], null, $this->tc33UE],
            [["cod_fondo" => TC33FonteFinanziaria::STATO], null, $this->tc33Stato],
            [["cod_fondo" => TC33FonteFinanziaria::REGIONE], null, $this->tc33Regione],
            [["cod_fondo" => TC33FonteFinanziaria::ALTRO_PUBBLICO], null, $this->tc33AltroPubblico],
            [["cod_fondo" => TC33FonteFinanziaria::COMUNE], null, $this->tc33Comune],
        ]));
        $tc34Repository = $this->createMock(TC34DeliberaCIPERepository::class);
        $tc34Repository->method('findOneBy')->willReturn($this->delibera);
        $tc35Repository = $this->createMock(TC35NormaRepository::class);
        $tc35Repository->method('findOneBy')->willReturn($this->norma);
        $rer = new OrganismoIntermedio();

        $comune = new GeoComune();
        $comune->setTc16LocalizzazioneGeografica(new TC16LocalizzazioneGeografica());
        $provincia = new GeoProvincia();
        $provincia->setTc16LocalizzazioneGeografica(new TC16LocalizzazioneGeografica());
        $comune->setProvincia($provincia);
        $regione = new GeoRegione();
        $provincia->setRegione($regione);
        $regione->setTc16LocalizzazioneGeografica(new TC16LocalizzazioneGeografica());

        $rer->setComune($comune);
        $rer->setCodiceFiscale('80062590379');
        $this->soggettoRepository = $this->createMock(SoggettoRepository::class);
        $this->soggettoRepository->method('find')->with(3438)->willReturn($rer);

        $tc16Repository = $this->createMock(TC16LocalizzazioneGeograficaRepository::class);
        $tc16Repository->method('findOneBy')->willReturn(new TC16LocalizzazioneGeografica());

        $this->em->method('getRepository')->will($this->returnValueMap([
            ['MonitoraggioBundle:TC33FonteFinanziaria', $tc33Repository],
            ['MonitoraggioBundle:TC16LocalizzazioneGeografica', $tc16Repository],
            ['MonitoraggioBundle:TC34DeliberaCIPE', $tc34Repository],
            ['MonitoraggioBundle:TC35Norma', $tc35Repository],
            ['MonitoraggioBundle\Entity\TC33FonteFinanziaria', $tc33Repository],
            ['MonitoraggioBundle\Entity\TC34DeliberaCIPE', $tc34Repository],
            ['MonitoraggioBundle\Entity\TC35Norma', $tc35Repository],
            ['SoggettoBundle:Soggetto', $this->soggettoRepository],
            ['SoggettoBundle\Entity\Soggetto', $this->soggettoRepository],
        ]));
    }

    protected function creaProcedura(): Procedura {
        $procedura = new Bando();
        $azione = new Azione();
        $procedura->addAzioni($azione);
        $azione->addProcedure($procedura);
        $obiettivoSpecifico = new ObiettivoSpecifico();
        $obiettivoSpecifico->addAzioni($azione);
        $obiettivoSpecifico->addProcedure($procedura);
        $azione->setObiettivoSpecifico($obiettivoSpecifico);

        $livelloObiettivoSpecifico = new TC36LivelloGerarchico();
        $livelloObiettivoSpecifico->setCodLivGerarchico('obiettivo');
        $livelloObiettivoSpecifico->addObiettiviSpecifici($obiettivoSpecifico);
        $obiettivoSpecifico->setLivelloGerarchico($livelloObiettivoSpecifico);

        $asse = new Asse();
        $livelloAsse = new TC36LivelloGerarchico();
        $livelloAsse->setCodLivGerarchico('asse');
        $livelloAsse->addAssi($asse);
        $asse->setLivelloGerarchico($livelloAsse);
        $procedura->setAsse($asse);
        $obiettivoSpecifico->setAsse($asse);

        return $procedura;
    }

    protected function creaPagamento(string $modalitaPagamento): Pagamento {
        $atc = $this->richiesta->getAttuazioneControllo();

        $pagamento = new Pagamento();
        $pagamento->setAttuazioneControlloRichiesta($atc);
        $atc->addPagamenti($pagamento);
        $mandato = new MandatoPagamento();
        $mandato->setPagamento($pagamento);
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

    protected function assertFinanziamento(TC33FonteFinanziaria $fondo, float $importoAtteso): Finanziamento {
        $codiceFondo = $fondo->getCodFondo();
        $finanziamenti = $this->richiesta->getMonFinanziamenti($codiceFondo);

        $this->assertNotEmpty($finanziamenti);
        /** @var Finanziamento $finanziamento */
        $finanziamento = $finanziamenti->first();

        $this->assertEquals($importoAtteso, $finanziamento->getImporto(), '', 0.001);

        return $finanziamento;
    }

    protected function aggiungiVariazione(float $costoAmmesso, float $contributoConcesso): VariazionePianoCosti {
        $atc = $this->richiesta->getAttuazioneControllo();
        $variazione = new VariazionePianoCosti();
        $variazione->setAttuazioneControlloRichiesta($atc);
        $variazione->setCostoAmmesso($costoAmmesso);
        $variazione->setContributoAmmesso($contributoConcesso);

        $atc->addVariazioni($variazione);
        return $variazione;
    }

    protected function setSoggetto(Soggetto $soggetto): void {
        $this->proponente->setSoggetto($soggetto);
    }
}
