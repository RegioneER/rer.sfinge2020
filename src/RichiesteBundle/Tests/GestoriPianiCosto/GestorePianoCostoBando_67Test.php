<?php
namespace RichiesteBundle\Tests\GestoriPianiCosto;

use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\GestoriPianiCosto\GestorePianoCostoBando_67;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\VocePianoCosto;
use RichiesteBundle\Entity\PianoCosto;
use RichiesteBundle\Entity\TipoVoceSpesa;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\ProponenteRepository;
use RichiesteBundle\Entity\SezionePianoCosto;
use RichiesteBundle\Service\GestoreModalitaFinanziamentoService;
use RichiesteBundle\GestoriModalitaFinanziamento\GestoreModalitaFinanziamentoBando_67;
use RichiesteBundle\Utility\EsitoValidazione;


class GestorePianoCostoBando_67Test extends TestBaseService{

    /**
     * @var GestorePianoCostoBando_67
     */
    protected $gestore;

    /**
     * @var Proponente
     */
    protected $proponente;

    public function setUp(){
        parent::setUp();
        $this->gestore = new GestorePianoCostoBando_67($this->container);
        $this->proponente = new Proponente();
        $this->proponente->setId(1);
        $richiesta = new Richiesta();
        $richiesta->addProponente($this->proponente);
        $this->proponente->setRichiesta($richiesta);

        $proponenteRepository = $this->createMock(ProponenteRepository::class);
        $this->em->method('getRepository')->willReturn($proponenteRepository);
        $proponenteRepository->method('find')->willReturn($this->proponente);

        $this->istanziaModalitaFinaziamento(new EsitoValidazione(true));
    }

    protected function istanziaModalitaFinaziamento(EsitoValidazione $esito){
        $gestoreModalitaFinanziamento = $this->createMock(GestoreModalitaFinanziamentoBando_67::class);
        $gestoreModalitaFinanziamento->method('validaModalitaFinanziamentoRichiesta')->willReturn($esito);

        $serviceModalitaFinanziamento = $this->createMock(GestoreModalitaFinanziamentoService::class);
        $serviceModalitaFinanziamento->method('getGestore')->willReturn($gestoreModalitaFinanziamento);
        
        $this->container->set('gestore_modalita_finanziamento', $serviceModalitaFinanziamento);
    }
    
    /**
     * @dataProvider totaleDataProvider
     */
    public function testValidaTotale(array $importi, float $totale, bool $risultato)
    {
        $sezione = new SezionePianoCosto();
        $sezione->setCodice(GestorePianoCostoBando_67::SEZIONE_A);

        $defVoce = $this->creaPianoCosto($sezione, 'voce');
        $defTotale = $this->creaPianoCosto($sezione, GestorePianoCostoBando_67::COD_TOTALE);

        foreach($importi as $importo){
            $this->creaVocePianoCosto($defVoce, $importo);
        }
        $this->creaVocePianoCosto($defTotale, $totale);

        

        $esito = $this->gestore->validaPianoDeiCostiProponente($this->proponente);

        $this->assertSame($risultato, $esito->getEsito());
    }

    protected function creaPianoCosto(SezionePianoCosto $sezione, string $codiceTipo):PianoCosto{
        $tipo = new TipoVoceSpesa();
        $def = new PianoCosto();
        $def->setCodice($codiceTipo);
        $def->setTipoVoceSpesa($tipo);
        $def->setSezionePianoCosto($sezione);
        $sezione->addPianiCosto($def);


        return $def;
    }

    public function totaleDataProvider(){
        return [
            [[1,2,3,4,5], 15, true],
            [[1,2,3,4,5], 14, false],
        ];
    }

    /**
     * @dataProvider validazioneSpeseSezioneADataProvider
     */
    public function testValidazioneSpeseSezioneA(float $importoSpese, float $importoTotale, bool $atteso){
        $sezione = new SezionePianoCosto();
        $sezione->setCodice(GestorePianoCostoBando_67::SEZIONE_A);
        $voceSpese = $this->creaPianoCosto($sezione, GestorePianoCostoBando_67::COD_SPESE_SEZ_A);
        $spese = $this->creaVocePianoCosto($voceSpese, $importoSpese);
        $voceSpeseAltro = $this->creaPianoCosto($sezione, 'altro');
        $voleAltro = $this->creaVocePianoCosto($voceSpeseAltro, $importoTotale - $importoSpese);
        $voceTot = $this->creaPianoCosto($sezione, GestorePianoCostoBando_67::COD_TOTALE);
        $tot = $this->creaVocePianoCosto($voceTot, $importoTotale);




        $esito = $this->gestore->validaPianoDeiCostiProponente($this->proponente);

        $this->assertEquals($atteso, $esito->getEsito());
    }

    public function validazioneSpeseSezioneADataProvider()
    {
        return [
            [ 15, 100, true],
            [ 0, 100, true],
            [ 20, 100, false],
        ];
    }
    
    protected function creaVocePianoCosto(PianoCosto $definizione, $importo) : VocePianoCosto{
        $voce = new VocePianoCosto();
        $richiesta = $this->proponente->getRichiesta();
        $voce->setProponente($this->proponente);
        $voce->setPianoCosto($definizione);
        $voce->setRichiesta($richiesta);        
        $voce->setImportoAnno1($importo);
        $this->proponente->addVociPianoCosto($voce);
        $richiesta->addVociPianoCosto($voce);

        return $voce;
    }

    public function testAnnualita(){
        $annualita = $this->gestore->getAnnualita(1);
        $this->assertNotNull($annualita);
        $this->assertNotEmpty($annualita);
    }

    /**
     * @dataProvider massimaleSezioneBTestDataProvider
     */
    public function testMassimaleSezioneB(float $importoSezioneA, float $importoSezioneB, bool $risultato){
        $sezioneA = new SezionePianoCosto();
        $sezioneA->setCodice(GestorePianoCostoBando_67::SEZIONE_A);
        $pianoAltroA = $this->creaPianoCosto($sezioneA, 'altro');
        $voceAltroA = $this->creaVocePianoCosto($pianoAltroA, $importoSezioneA);
        $pianoTotA = $this->creaPianoCosto($sezioneA, GestorePianoCostoBando_67::COD_TOTALE);
        $voceTotA = $this->creaVocePianoCosto($pianoTotA, $importoSezioneA);

        $sezioneB = new SezionePianoCosto();
        $sezioneB->setCodice(GestorePianoCostoBando_67::SEZIONE_B);
        $pianoB = $this->creaPianoCosto($sezioneB, '');
        $voceB = $this->creaVocePianoCosto($pianoB, $importoSezioneB);
        $pianoB = $this->creaPianoCosto($sezioneB, GestorePianoCostoBando_67::COD_TOTALE);
        $voceB = $this->creaVocePianoCosto($pianoB, $importoSezioneB);

        $esito = $this->gestore->validaPianoDeiCostiProponente($this->proponente);

        $this->assertSame($risultato, $esito->getEsito());
    }

    public function massimaleSezioneBTestDataProvider(){
        return [
            [0, 4000, false],
            [1000, 150, true],
        ];
    }


    /**
     * @dataProvider totaliValidiDataProvider
     */
	public function testTotaleValidi($importo, $valido) : EsitoValidazione{
        $sezioneA = new SezionePianoCosto();
        $sezioneA->setCodice(GestorePianoCostoBando_67::SEZIONE_A);
        $pianoAltroA = $this->creaPianoCosto($sezioneA, 'altro');
        $voceAltroA = $this->creaVocePianoCosto($pianoAltroA, $importo);
        $pianoTotA = $this->creaPianoCosto($sezioneA, GestorePianoCostoBando_67::COD_TOTALE);
        $voceTotA = $this->creaVocePianoCosto($pianoTotA, $importo);

        $esito = $this->gestore->validaPianoDeiCostiProponente($this->proponente);
        $this->assertSame($valido, $esito->getEsito());
        return $esito;
    }
    
    public function totaliValidiDataProvider(){
        return [
            [1000, true],
            [0, false],
            [null, false],
        ];
    }

    public function testEsitoFalseModalitaFinanziamento(){
        $msg = 'messaggio errore validazione modalita finanziamento';
        $msgSezione = 'messaggio sezione';
        $esito = new EsitoValidazione(false);
        $esito->addMessaggio($msg);
        $esito->addMessaggioSezione($msgSezione);
        
        $this->istanziaModalitaFinaziamento($esito);

        $res = $this->testTotaleValidi(1000, false);
        $this->assertContains($msg, $res->getMessaggi());
        $this->assertContains($msgSezione, $res->getMessaggiSezione());
    }

  
}