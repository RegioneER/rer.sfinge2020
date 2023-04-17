<?php

namespace IstruttorieBundle\Tests\Service;

use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\RichiestaRepository;
use IstruttorieBundle\Service\GestoreIstruttoriaBase;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use RichiesteBundle\Entity\Proponente;
use SoggettoBundle\Entity\Azienda;
use IstruttorieBundle\Entity\EsitoIstruttoria;
use SfingeBundle\Entity\AttoRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\SubmitButton;
use GeoBundle\Entity\GeoComune;
use MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica;
use MonitoraggioBundle\Entity\TC47StatoProgetto;
use MonitoraggioBundle\Repository\TC47StatoProgettoRepository;
use SoggettoBundle\Entity\SoggettoRepository;
use SoggettoBundle\Entity\Soggetto;
use MonitoraggioBundle\Repository\TC24RuoloSoggettoRepository;
use MonitoraggioBundle\Repository\TC15StrumentoAttuativoRepository;
use MonitoraggioBundle\Repository\TC33FonteFinanziariaRepository;
use MonitoraggioBundle\Repository\TC34DeliberaCIPERepository;
use MonitoraggioBundle\Repository\TC35NormaRepository;
use MonitoraggioBundle\Repository\TC4ProgrammaRepository;
use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Azione;
use MonitoraggioBundle\Entity\TC12Classificazione;
use MonitoraggioBundle\Entity\TC11TipoClassificazione;
use MonitoraggioBundle\Repository\TC36LivelloGerarchicoRepository;
use MonitoraggioBundle\Entity\TC37VoceSpesa;
use RichiesteBundle\Entity\PianoCosto;
use RichiesteBundle\Entity\VocePianoCosto;
use IstruttorieBundle\Entity\IstruttoriaVocePianoCosto;
use AttuazioneControlloBundle\Entity\RichiestaStatoAttuazioneProgetto;
use MonitoraggioBundle\Entity\TC24RuoloSoggetto;
use MonitoraggioBundle\Entity\TC15StrumentoAttuativo;
use AttuazioneControlloBundle\Entity\StrumentoAttuativo;
use MonitoraggioBundle\Entity\TC34DeliberaCIPE;
use MonitoraggioBundle\Entity\TC44_45IndicatoriOutput;
use MonitoraggioBundle\Entity\IndicatoriOutputAzioni;
use SfingeBundle\Entity\Asse;
use SfingeBundle\Entity\ObiettivoSpecifico;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use MonitoraggioBundle\Repository\TC16LocalizzazioneGeograficaRepository;
use CipeBundle\Entity\Classificazioni\CupTipologia;
use MonitoraggioBundle\Entity\TC5TipoOperazione;
use MonitoraggioBundle\Service\IGestoreFinanziamento;
use MonitoraggioBundle\Service\GestoreFinanziamentoService;
use MonitoraggioBundle\Service\GestoreImpegniService;
use MonitoraggioBundle\Service\IGestoreImpegni;
use MonitoraggioBundle\Entity\TC4Programma;

class GestoreIstruttoriaBaseTest extends TestBaseService {
    /**
     * @var IGestoreFinanziamento
     */
    protected $gestoreFinanziamento;

    /**
     * @var IGestoreImpegni
     */
    protected $gestoreImpegni;

    public function setUp() {
        parent::setUp();
        $this->gestoreFinanziamento = $this->createMock(IGestoreFinanziamento::class);
        $gestoreFinanziamentoService = $this->createMock(GestoreFinanziamentoService::class);
        $gestoreFinanziamentoService->method('getGestore')->willReturn($this->gestoreFinanziamento);
        $this->container->set('monitoraggio.gestore_finanziamento', $gestoreFinanziamentoService);

        $gestoreImpegniService = $this->createMock(GestoreImpegniService::class);
        $this->gestoreImpegni = $this->createMock(IGestoreImpegni::class);
        $gestoreImpegniService->method('getGestore')->willReturn($this->gestoreImpegni);
        $this->container->set('monitoraggio.impegni', $gestoreImpegniService);
    }

    public function testPassaggioATC() {
        $classificazioneAzione = new TC12Classificazione();
        $tipoClassificazioneAzione = new TC11TipoClassificazione();
        $tipoClassificazioneAzione->addClassificazioni($classificazioneAzione);
        $classificazioneAzione->setTipoClassificazione($tipoClassificazioneAzione);
        $azione = new Azione();
        $azione->addClassificazioni($classificazioneAzione);
        $obiettivoSpecifico = new ObiettivoSpecifico();
        $azione->setObiettivoSpecifico($obiettivoSpecifico);
        $obiettivoSpecifico->addAzioni($azione);
        $livObiettivo = new TC36LivelloGerarchico();
        $obiettivoSpecifico->setLivelloGerarchico($livObiettivo);
        $tipoIndicatore = new TC44_45IndicatoriOutput();
        $indicatoreAzione = new IndicatoriOutputAzioni();
        $indicatoreAzione->setAzione($azione);
        $indicatoreAzione->setIndicatoreOutput($tipoIndicatore);
        $asse = new Asse();
        $indicatoreAzione->setAsse($asse);
        $azione->addIndicatoriOutputAzioni($indicatoreAzione);
        $procedura = new Bando();
        $procedura->addAzioni($azione);
        $procedura->setAsse($asse);
        $richiesta = new Richiesta();
        $richiesta->setProcedura($procedura);
        $proponente = new Proponente();
        $proponente->setMandatario(true);
        $proponente->setSedeLegaleComeOperativa(true);

        $soggetto = new Azienda();
        $comune = new GeoComune();
        $localizzazioneGeografica = new TC16LocalizzazioneGeografica();
        $localizzazioneGeografica
            ->setCodiceRegione('100')
            ->setCodiceProvincia('111')
            ->setCodiceComune('011');
        $comune->setTc16LocalizzazioneGeografica($localizzazioneGeografica);
        $soggetto->setComune($comune);
        $soggetto->setCap('90011');
        $proponente->setSoggetto($soggetto);
        $richiesta->addProponenti($proponente);

        $istruttoria = new IstruttoriaRichiesta();
        $esitoIstruttoria = new EsitoIstruttoria();
        $esitoIstruttoria->setEsitoPositivo(true);
        $istruttoria->setEsito($esitoIstruttoria);
        $istruttoria->setConcessione(true);
        $istruttoria->setAmmissibilitaAtto(true);
        $istruttoria->setRichiesta($richiesta);
        $istruttoria->setTipologiaSoggetto(IstruttoriaRichiesta::PRIVATO);
        $istruttoria->setContributoAmmesso(8500);
        $istruttoria->setCostoAmmesso(10000);
        $richiesta->setIstruttoria($istruttoria);
        $tipologiaCup = new CupTipologia();
        $tipologiaCup->setTc5TipoOperazione(new TC5TipoOperazione());
        $istruttoria->setCupTipologia($tipologiaCup);

        $this->aggiungiVociPianoCosto($richiesta);

        $richiestaRepository = $this->createMock(RichiestaRepository::class);
        $richiestaRepository->method('find')->willReturn($richiesta);
        $this->container->get('security.authorization_checker')->method('isGranted')->willReturn(true);
        $attiRepository = $this->createMock(AttoRepository::class);

        $statoProgettoRepository = $this->createMock(TC47StatoProgettoRepository::class);
        $statoProgetto = new TC47StatoProgetto();
        $statoProgetto->setStatoProgetto(TC47StatoProgetto::CODICE_IN_CORSO_ESECUZIONE);
        $statoProgettoRepository->method('findOneBy')->willReturn($statoProgetto);

        $emiliaRomagna = new Soggetto();
        $comuneBologna = new GeoComune();
        $soggettoRepository = $this->createMock(SoggettoRepository::class);
        $soggettoRepository->method('findOneBy')->willReturn($emiliaRomagna);
        $soggettoRepository->method('find')->willReturn($emiliaRomagna);

        $ruoloSoggettoRepository = $this->createMock(TC24RuoloSoggettoRepository::class);
        $ruoloProgrammatore = new TC24RuoloSoggetto();
        $ruoloBeneficiario = new TC24RuoloSoggetto();
        $ruoloSoggettoRepository->method('findOneBy')->willReturn($ruoloBeneficiario);
        // ->will($this->returnValueMap(array(
        //     array(array("cod_ruolo_sog" => TC24RuoloSoggetto::PROGRAMMATORE), $ruoloProgrammatore),
        //     array(array("cod_ruolo_sog" => TC24RuoloSoggetto::BENEFICIARIO), $ruoloBeneficiario),
        // )));
        $strumentoAttuativoRepository = $this->createMock(TC15StrumentoAttuativoRepository::class);
        $strumentoAttuativo = new TC15StrumentoAttuativo();
        $strumentoAttuativoRepository->method('findOneBy')->willReturn($strumentoAttuativo);

        $fonteFinanziariaRepository = $this->createMock(TC33FonteFinanziariaRepository::class);

        $deliberaCipeRepository = $this->createMock(TC34DeliberaCIPERepository::class);
        $delibera = new TC34DeliberaCIPE();
        $deliberaCipeRepository->method('findOneBy')->willReturn($delibera);

        $normaRepository = $this->createMock(TC35NormaRepository::class);
        $programmaRepository = $this->createMock(TC4ProgrammaRepository::class);
        $programmaRepository->method('findOneBy')->willReturn(new TC4Programma());
        $livelloGerarchicoRepository = $this->createMock(TC36LivelloGerarchicoRepository::class);

        $localizzazioneRepository = $this->createMock(TC16LocalizzazioneGeograficaRepository::class);
        $localizzazioneRepository->method('findOneBy')->willReturn(new TC16LocalizzazioneGeografica());

        $this->router->method('generate')->willReturn('stringa non nulla');

        $this->em->method('getRepository')->will(
            $this->returnValueMap([
                ['RichiesteBundle:Richiesta', $richiestaRepository],
                ['SfingeBundle\Entity\Atto', $attiRepository],
                ['MonitoraggioBundle:TC47StatoProgetto', $statoProgettoRepository],
                ['SoggettoBundle:Soggetto', $soggettoRepository],
                ['MonitoraggioBundle:TC24RuoloSoggetto', $ruoloSoggettoRepository],
                ['MonitoraggioBundle:TC15StrumentoAttuativo', $strumentoAttuativoRepository],
                ['MonitoraggioBundle:TC33FonteFinanziaria', $fonteFinanziariaRepository],
                ['MonitoraggioBundle:TC34DeliberaCIPE', $deliberaCipeRepository],
                ['MonitoraggioBundle:TC35Norma', $normaRepository],
                ['MonitoraggioBundle:TC4Programma', $programmaRepository],
                ['MonitoraggioBundle:TC36LivelloGerarchico', $livelloGerarchicoRepository],
                ['MonitoraggioBundle:TC16LocalizzazioneGeografica', $localizzazioneRepository],
        ]));

        $request = new Request();
        $request->setMethod('POST');
        $this->requestStack->push($request);

        $form = $this->createMock(FormInterface::class);
        $formPulsanteValida = $this->createMock(SubmitButton::class);
        $formPulsanteValida->method('isClicked')->wilLReturn(true);
        $formBottoni = $this->createMock(FormInterface::class);
        $formBottoni->method('has')->wilLReturn(true);
        $formBottoni->method('get')->wilLReturn($formPulsanteValida);

        $formAmmissibileATC = $this->createMock(FormInterface::class);
        $formConcessioneATC = $this->createMock(FormInterface::class);

        $form->method('get')->will($this->returnValueMap([
            ['pulsanti', $formBottoni],
            ['atto_ammissibilita_atc', $formAmmissibileATC],
            ['atto_concessione_atc', $formConcessioneATC],
        ]));

        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn($istruttoria);

        $this->formFactory->method('create')->willReturn($form);

        $gestore = new GestoreIstruttoriaBase($this->container);

        $this->em->expects($this->once())->method('persist')->with($this->logicalAnd(
            $this->isInstanceOf(Richiesta::class),
            $this->callback(self::class . '::verificaVoceSpesa'),
            $this->callback(self::class . '::verificaLocalizzazioneGeografica'),
            $this->callback(self::class . '::verificaStatoInizialeAttuazioneProgetto'),
            $this->callback(self::class . '::verificaSoggettiCollegati'),
            $this->callback(self::class . '::verificaStrumentoAttuativo'),
            $this->callback(self::class . '::verificaindicatoriOutput')
        ));
        $this->em->expects($this->once())->method('flush');

        $res = $gestore->avanzamentoATC(0);
        $this->assertContains('La richiesta è passata in attuazione e controllo', $this->flashBag->get('success'));
    }

    public function aggiungiVociPianoCosto(Richiesta $richiesta) {
        $tipoVoceSpesa = new TC37VoceSpesa();
        $tipoVoceSpesa->setVoceSpesa('tipo1');
        $pianocosto = new PianoCosto();
        $pianocosto->setMonVoceSpesa($tipoVoceSpesa);
        $voce1 = new VocePianoCosto();
        $voce1->setRichiesta($richiesta);
        $voce1->setPianoCosto($pianocosto);
        $voce1->setImportoAnno1(1000);
        $voce1->setImportoAnno2(2000);
        $istruttoriaVocePianoCosto1 = new IstruttoriaVocePianoCosto();
        $istruttoriaVocePianoCosto1->setImportoAmmissibileAnno1(1000);
        $istruttoriaVocePianoCosto1->setImportoAmmissibileAnno2(2000);
        $istruttoriaVocePianoCosto1->setVocePianoCosto($voce1);
        $voce1->setIstruttoria($istruttoriaVocePianoCosto1);
        $richiesta->addVociPianoCosto($voce1);

        $pianocosto2 = new PianoCosto();
        $pianocosto2->setMonVoceSpesa($tipoVoceSpesa);
        $voce2 = new VocePianoCosto();
        $voce2->setRichiesta($richiesta);
        $voce2->setPianoCosto($pianocosto);
        $voce2->setImportoAnno1(1000);
        $voce2->setImportoAnno2(2000);
        $istruttoriaVocePianoCosto2 = new IstruttoriaVocePianoCosto();
        $istruttoriaVocePianoCosto2->setImportoAmmissibileAnno1(1000);
        $istruttoriaVocePianoCosto2->setImportoAmmissibileAnno2(2000);
        $istruttoriaVocePianoCosto2->setVocePianoCosto($voce2);
        $voce2->setIstruttoria($istruttoriaVocePianoCosto2);
        $richiesta->addVociPianoCosto($voce2);

        $tipoVoceSpesa2 = new TC37VoceSpesa();
        $tipoVoceSpesa2->setVoceSpesa('voce2');
        $pianocosto3 = new PianoCosto();
        $pianocosto3->setMonVoceSpesa($tipoVoceSpesa2);
        $voce3 = new VocePianoCosto();
        $voce3->setRichiesta($richiesta);
        $voce3->setPianoCosto($pianocosto3);
        $voce3->setImportoAnno1(1000);
        $voce3->setImportoAnno2(2000);
        $istruttoriaVocePianoCosto3 = new IstruttoriaVocePianoCosto();
        $istruttoriaVocePianoCosto3->setImportoAmmissibileAnno1(1000);
        $istruttoriaVocePianoCosto3->setImportoAmmissibileAnno2(1500);
        $istruttoriaVocePianoCosto3->setVocePianoCosto($voce3);
        $voce3->setIstruttoria($istruttoriaVocePianoCosto3);
        $richiesta->addVociPianoCosto($voce3);

        $pianoCosto4 = new PianoCosto();
        $voce4 = new VocePianoCosto();
        $voce4->setRichiesta($richiesta);
        $voce4->setPianoCosto($pianoCosto4);
        $voce4->setImportoAnno1(1000);
        $istrVoce4 = new IstruttoriaVocePianoCosto();
        $istrVoce4->setImportoAmmissibileAnno1(1000);
        $voce4->setIstruttoria($istrVoce4);
        $richiesta->addVociPianoCosto($voce4);
    }

    public static function verificaVoceSpesa(Richiesta $richiesta) {
        $vociSpesa = $richiesta->getMonVoceSpesa();
        if (2 != \count($vociSpesa)) {
            return false;
        }
        foreach ($vociSpesa as $voceSpesa) {
            $codice = $voceSpesa->getTipoVoceSpesa()->getVoceSpesa();
            switch ($codice) {
                case 'tipo1':
                    if (6000 != $voceSpesa->getImporto()) {
                        return false;
                    }
                    break;
                case 'voce2':
                    if (2500 != $voceSpesa->getImporto()) {
                        return false;
                    }
                break;
                default:
                    return false;
            }
        }
        return true;
    }

    public static function verificaLocalizzazioneGeografica(Richiesta $richiesta) {
        /** @var \MonitoraggioBundle\Entity\LocalizzazioneGeografica $localizzazioneGeografica */
        $localizzazioneGeografica = $richiesta->getMonLocalizzazioneGeografica()->first();
        if (false === $localizzazioneGeografica) {
            return false;
        }
        if ($localizzazioneGeografica->getRichiesta() != $richiesta) {
            return false;
        }

        if (\is_null($localizzazioneGeografica->getLocalizzazione())) {
            return false;
        }
        if ('100111011' != $localizzazioneGeografica->getLocalizzazione()->getCodLocalizzazione()) {
            return false;
        }
        if ('90011' != $localizzazioneGeografica->getCap()) {
            return false;
        }
        return true;
    }

    public static function verificaStatoInizialeAttuazioneProgetto(Richiesta $richiesta) {
        /** @var RichiestaStatoAttuazioneProgetto $statoAttuazione */
        $statoAttuazione = $richiesta->getMonStatoProgetti()->first();
        if (false == $statoAttuazione) {
            return false;
        }
        if ($statoAttuazione->getRichiesta() != $richiesta) {
            return false;
        }
        if (\is_null($statoAttuazione->getStatoProgetto())) {
            return false;
        }
        if (TC47StatoProgetto::CODICE_IN_CORSO_ESECUZIONE != $statoAttuazione->getStatoProgetto()->getStatoProgetto()) {
            return false;
        }
        return true;
    }

    public static function verificaSoggettiCollegati(Richiesta $richiesta) {
        $soggettiCollegati = $richiesta->getMonSoggettiCorrelati();
        if (2 != \count($soggettiCollegati)) {
            return false;
        }
        foreach ($soggettiCollegati as $soggettoCollegato) {
            if (\is_null($soggettoCollegato->getSoggetto())) {
                return false;
            }
            if (\is_null($soggettoCollegato->getTc24RuoloSoggetto())) {
                return false;
            }
        }
        return true;
    }

    public static function verificaStrumentoAttuativo(Richiesta $richiesta) {
        /** @var StrumentoAttuativo $strumentoAttuativo */
        $strumentoAttuativo = $richiesta->getMonStrumentiAttuativi()->first();
        if (false == $strumentoAttuativo) {
            return false;
        }
        if ($strumentoAttuativo->getRichiesta() != $richiesta) {
            return false;
        }
        if (\is_null($strumentoAttuativo->getTc15StrumentoAttuativo())) {
            return false;
        }
        return true;
    }

    public static function verificaindicatoriOutput(Richiesta $richiesta) {
        $indicatori = $richiesta->getMonIndicatoreOutput();
        if (0 == \count($indicatori)) {
            return false;
        }
        return true;
    }
}
