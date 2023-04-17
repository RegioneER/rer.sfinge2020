<?php

namespace RichiesteBundle\Tests\GestoriPianiCosto;

use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\Entity\ProponenteRepository;
use RichiesteBundle\GestoriPianiCosto\GestorePianoCostoBando_60;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\VocePianoCosto;
use RichiesteBundle\Entity\PianoCosto;
use RichiesteBundle\Entity\SezionePianoCosto;
use RichiesteBundle\Entity\RichiestaRepository;
use SfingeBundle\Entity\Bando;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\PianoCostoRepository;
use Symfony\Component\HttpFoundation\Request;

class GestorePianoCostoBando_60Test extends TestBaseService {
    /**
     * @var ProponenteRepository
     */
    protected $proponenteRepository;

    /**
     * @var PianoCostoRepository
     */
    protected $pianoCostiRepository;

    /**
     * @var SezionePianoCosto[]
     */
    protected $sezioni;

    /**
     * @var GestorePianoCostoBando_60
     */
    protected $gestorePianoCosti;

    public function setUp() {
        parent::setUp();
        $this->sezioni = array(
            $this->createSezione(GestorePianoCostoBando_60::CODICE_SEZIONE_RICERCA),
            $this->createSezione(GestorePianoCostoBando_60::CODICE_SEZIONE_SVILUPPO),
        );
        $richiesta = new Richiesta();
        $richiesta->setProcedura(new Bando());
        $richiestaRepository = $this->createMock(RichiestaRepository::class);
        $richiestaRepository->method('find')->willReturn($richiesta);
        $this->pianoCostiRepository = $this->createMock(PianoCostoRepository::class);
        $this->pianoCostiRepository->method('getSezioniDaProcedura')->willReturn($this->sezioni);

        $this->proponenteRepository = $this->createMock(ProponenteRepository::class);
        $this->em->method('getRepository')
        ->will($this->returnValueMap(array(
            array('RichiesteBundle:Richiesta', $richiestaRepository),
            array('RichiesteBundle:Proponente', $this->proponenteRepository),
            array('RichiesteBundle:PianoCosto', $this->pianoCostiRepository),
        )));
        $request = new Request(array(
            'id_richiesta' => 1,
            'id_bando' => null,
        ));
        $this->requestStack->push($request);

        $this->gestorePianoCosti = new GestorePianoCostoBando_60($this->container);
    }

    private function createSezione($codice) {
        $sezione = new SezionePianoCosto();
        $sezione->setCodice($codice);
        return $sezione;
    }

    public function xtestValidazioneNonEsistenzaPianoCosto() {
        $proponente = new Proponente();
        $this->proponenteRepository->method('find')->willReturn($proponente);

        $this->gestorePianoCosti = new GestorePianoCostoBando_60($this->container);
        $esito = $this->gestorePianoCosti->validaPianoDeiCostiProponente(10);
        $this->assertNotNull($esito);
        $this->assertSame(false, $esito->getEsito());
        $this->assertContains('Compilare il piano costi', $esito->getMessaggiSezione());
    }

    public function xtestValidazioneCampoNullo() {
        $voce = new VocePianoCosto();
        $proponente = new Proponente();
        $proponente->addVociPianoCosto($voce);
        $this->proponenteRepository->method('find')->willReturn($proponente);

        $esito = $this->gestorePianoCosti->validaPianoDeiCostiProponente(10);
        $this->assertNotNull($esito);
        $this->assertSame(false, $esito->getEsito());
        $this->assertContains("L'importo non può essere non valorizzato", $esito->getMessaggiSezione());
    }

    public function xtestValidazioneCampoMinoreZero() {
        $voce = new VocePianoCosto();
        $voce->setImportoAnno1(-1);
        $proponente = new Proponente();
        $proponente->addVociPianoCosto($voce);
        $this->proponenteRepository->method('find')->willReturn($proponente);

        $esito = $this->gestorePianoCosti->validaPianoDeiCostiProponente(10);
        $this->assertNotNull($esito);
        $this->assertSame(false, $esito->getEsito());
        $this->assertContains("L'importo non può essere minore di zero", $esito->getMessaggiSezione());
    }


    public function creaProponente():Proponente{
        $procedura = new Bando();
        $richiesta = new Richiesta();
        $richiesta->setProcedura($procedura);
        $proponente = new Proponente();
        $proponente->setRichiesta($richiesta);
        $richiesta->addProponente($proponente);

        return $proponente;
    }
    /**
     * @dataProvider totaliDataProvider
     */
    public function testValidazioneTotale($valoriVoci, $importototale, $valoreEsito) {
        $codiceSezione = GestorePianoCostoBando_60::CODICE_SEZIONE_RICERCA;
        $proponente = $this->creaProponente();
        $sezione = new SezionePianoCosto();
        $sezione->setCodice($codiceSezione);
        $tipoVoce = new PianoCosto();
        $tipoVoce->setSezionePianoCosto($sezione);
        foreach ($valoriVoci as $valore) {
            $voce = $this->creaVocePianoCosto($tipoVoce, $valore);
            $proponente->addVociPianoCosto($voce);
        }
        $tipoTotale = new PianoCosto();
        $tipoTotale->setCodice(GestorePianoCostoBando_60::CODICE_TOTALE);
        $tipoTotale->setSezionePianoCosto($sezione);
        
        $totale = $this->creaVocePianoCosto($tipoTotale, $importototale);
        $proponente->addVociPianoCosto($totale);

        $importoSpese = \array_reduce($valoriVoci, function($totale, $valore){
            return $totale + $valore;
        }, 0) * GestorePianoCostoBando_60::COEFFICENTE;
        $tipoSpese = new PianoCosto();
        $tipoSpese->setSezionePianoCosto($sezione);
        $tipoSpese->setCodice(GestorePianoCostoBando_60::CODICE_SPESE);
        $spese = $this->creaVocePianoCosto($tipoSpese, $importoSpese);
        $proponente->addVociPianoCosto($spese);

        $this->proponenteRepository->method('find')->willReturn($proponente);

        $esito = $this->gestorePianoCosti->validaPianoDeiCostiProponente($proponente);
        $this->assertNotNull($esito);
        $this->assertSame($valoreEsito, $esito->getEsito());
        if (false == $esito->getEsito()) {
            $this->assertContains("Il totale non corrisponde alla somma delle voci", $esito->getMessaggi());
        }
    }

    public function totaliDataProvider() {
        return array(
            array(array(1), 1.15, true),
            array(array(0.1, 0.2), 0.345, true),
            array(array(1, 2, 3, 4, 5), 17.25, true),
            array(array(1, 2, 3), 999, false),
        );
    }

    /**
     * @dataProvider validaSpeseDataProvider
     */
    public function testValidaSpeseGenerali($codiceSezione, array $valoriVoci, $importoSpese, $valoreEsito) {
        $proponente = $this->creaProponente();
        $sezione = new SezionePianoCosto();
        $sezione->setCodice($codiceSezione);
        $tipoVoce = new PianoCosto();
        $tipoVoce->setSezionePianoCosto($sezione);
        foreach ($valoriVoci as $valore) {
            $voce = new VocePianoCosto();
            $voce->setImportoAnno1($valore);
            $proponente->addVociPianoCosto($voce);
            $voce->setPianoCosto($tipoVoce);
        }

        $tipoVoceSpese = new PianoCosto();
        $tipoVoceSpese->setSezionePianoCosto($sezione);
        $tipoVoceSpese->setCodice( GestorePianoCostoBando_60::CODICE_SPESE );
        $voceSpese = new VocePianoCosto();
        $voceSpese->setPianoCosto($tipoVoceSpese);
        $voceSpese->setImportoAnno1($importoSpese);
        $proponente->addVociPianoCosto($voceSpese);

        $tipoTotale = new PianoCosto();
        $tipoTotale->setCodice(GestorePianoCostoBando_60::CODICE_TOTALE);
        $tipoTotale->setSezionePianoCosto($sezione);
        $totale = new VocePianoCosto();
        $totale->setPianoCosto($tipoTotale);
        $importoTotale = \array_reduce($valoriVoci, function ($carry, $value) {
            return $carry + $value;
        }, $importoSpese);
        $totale->setImportoAnno1($importoTotale);
        $proponente->addVociPianoCosto($totale);

        $this->proponenteRepository->method('find')->willReturn($proponente);

        $esito = $this->gestorePianoCosti->validaPianoDeiCostiProponente($proponente);
        $this->assertNotNull($esito);
        $this->assertSame($valoreEsito, $esito->getEsito());
        if (false == $esito->getEsito()) {
            $this->assertContains("Il valore delle spese generali non corrisponde al 30% delle altre voci", $esito->getMessaggiSezione());
        }
    }

    public function validaSpeseDataProvider() {
        return array(
            array(GestorePianoCostoBando_60::CODICE_SEZIONE_RICERCA, array(100), 30, true),
            // array(GestorePianoCostoBando_60::CODICE_SEZIONE_RICERCA, array(1), 0.3, true),
            // array(GestorePianoCostoBando_60::CODICE_SEZIONE_RICERCA, array(100), 40, false),
            
        );
    }

    public function testEntrambeSezioni(){
        $proponente = $this->creaProponente();
        
        $sezioneRicerca = $this->createSezione(GestorePianoCostoBando_60::CODICE_SEZIONE_RICERCA);

        $pianoCosto = $this->creaPianoCosto($sezioneRicerca, 'altro');
        $voce = $this->creaVocePianoCosto($pianoCosto, 1000);        
        $proponente->addVociPianoCosto($voce);

        $pianoCostoSpese = $this->creaPianoCosto($sezioneRicerca, GestorePianoCostoBando_60::CODICE_SPESE);
        $voce = $this->creaVocePianoCosto($pianoCostoSpese, 300);
        $proponente->addVociPianoCosto($voce);

        $pianoCostoTotale = $this->creaPianoCosto($sezioneRicerca, GestorePianoCostoBando_60::CODICE_TOTALE);
        $voce = $this->creaVocePianoCosto($pianoCostoTotale, 1300);
        $proponente->addVociPianoCosto($voce);
        
        $sezioneSviluppo = $this->createSezione(GestorePianoCostoBando_60::CODICE_SEZIONE_SVILUPPO);

        $pianoCosto = $this->creaPianoCosto($sezioneSviluppo, 'altro');
        $voce = $this->creaVocePianoCosto($pianoCosto, 1000);        
        $proponente->addVociPianoCosto($voce);

        $pianoCostoSpese = $this->creaPianoCosto($sezioneSviluppo, GestorePianoCostoBando_60::CODICE_SPESE);
        $voce = $this->creaVocePianoCosto($pianoCostoSpese, 150);
        $proponente->addVociPianoCosto($voce);

        $pianoCostoTotale = $this->creaPianoCosto($sezioneSviluppo, GestorePianoCostoBando_60::CODICE_TOTALE);
        $voce = $this->creaVocePianoCosto($pianoCostoTotale, 1150);
        $proponente->addVociPianoCosto($voce);


        $this->proponenteRepository->method('find')->willReturn($proponente);
        $esito = $this->gestorePianoCosti->validaPianoDeiCostiProponente($proponente);
        $this->assertNotNull($esito);
        $this->assertEquals(true, $esito->getEsito());
    }

    /**
     * @param SezionePianoCosto $sezione
     * @param string $codice
     * @return PianoCosto
     */
    private function creaPianoCosto(SezionePianoCosto $sezione, $codice){
        $res = new PianoCosto();
        $res->setCodice($codice);
        $res->setSezionePianoCosto($sezione);
        return $res;
    }

    /**
     * @return VocePianoCosto
     */
    private function creaVocePianoCosto(PianoCosto $piano, $importo){
        $res = new VocePianoCosto();
        $res->setImportoAnno1($importo);
        $res->setPianoCosto($piano);
        return $res;
    }
}
