<?php

namespace AttuazioneControlloBundle\Tests\Service\Istruttoria\Variazioni;

use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Entity\VariazioneSedeOperativa;
use AttuazioneControlloBundle\Service\Istruttoria\Variazioni\GestoreVariazioniSedeOperativaBase;
use BaseBundle\Entity\Indirizzo;
use BaseBundle\Tests\Service\TestBaseService;
use GeoBundle\Entity\GeoComune;
use MonitoraggioBundle\Entity\LocalizzazioneGeografica;
use MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\SedeOperativa;
use SoggettoBundle\Entity\Sede;
use SoggettoBundle\Service\SoggettoVersioning;
use Symfony\Component\Form\FormInterface;

class GestoreVariazioniSedeOperativaBaseTest extends TestBaseService {
    /** @var GestoreVariazioniSedeOperativaBase */
    private $gestore;
    /** @var VariazioneSedeOperativa */
    private $variazione;

    public function setUp() {
        parent::setUp();
        $sedeCorrente = new Sede();
        $richiesta = new Richiesta();
        $mandatario = new Proponente($richiesta);
        $mandatario->setMandatario(true);
        $richiesta->addProponente($mandatario);
        $atc = new AttuazioneControlloRichiesta();
        $atc->setRichiesta($richiesta);
        $this->variazione = new VariazioneSedeOperativa($atc, $sedeCorrente);
        $nuovaSede = new Sede();
        $indirizzoNuovo = new Indirizzo();
        $indirizzoNuovo->setVia('via dei pazzi');
        $indirizzoNuovo->setNumeroCivico('0');
        $indirizzoNuovo->setCap('90011');
        $comuneNuovo = new GeoComune();
        $tc16Nuovo = new TC16LocalizzazioneGeografica();
        $comuneNuovo->setTc16LocalizzazioneGeografica($tc16Nuovo);
        $indirizzoNuovo->setComune($comuneNuovo);
        $nuovaSede->setIndirizzo($indirizzoNuovo);
        $this->variazione->setSedeOperativaVariata($nuovaSede);
        $this->gestore = new GestoreVariazioniSedeOperativaBase($this->variazione, $this->container);
    }

    public function testApplicaVariazioneLatoMonitoraggioSenzaSede(): void {
        $this->applicaVariazione();

        /** @var SedeOperativa */
        $sedeOperativa = $this->variazione->getRichiesta()->getMandatario()->getSedi()->first();

        $this->assertInstanceOf(SedeOperativa::class, $sedeOperativa);
        $sede = $sedeOperativa->getSede();
        $indirizzo = $sede->getIndirizzo();

        $this->assertEquals('via dei pazzi', $indirizzo->getVia());
        $this->assertEquals('0', $indirizzo->getNumeroCivico());
        $this->assertEquals('90011', $indirizzo->getCap());
        $this->assertNotNull($indirizzo->getComune());
    }

    private function applicaVariazione() {
        $form = $this->createMock(FormInterface::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
        $formPulsanti = $this->createMock(FormInterface::class);
        $form->method('get')->with("pulsanti")->willReturn($formPulsanti);
        $this->formFactory->method('create')->willReturn($form);
        $soggettoVersion = $this->createMock(SoggettoVersioning::class);
        $this->container->set('soggetto.versioning', $soggettoVersion);
        $this->gestore->esitoFinale();
    }

    public function testApplicaVariazioneSenzaLocalizzazione(): void {
        $this->applicaVariazione();

        $localizzazioni = $this->variazione->getRichiesta()->getMonLocalizzazioneGeografica();
        $this->assertCount(1, $localizzazioni);

        /** @var LocalizzazioneGeografica */
        $localizzazione = $localizzazioni->first();

        $this->assertEquals('via dei pazzi, 0', $localizzazione->getIndirizzo());
        $this->assertNotNull($localizzazione->getLocalizzazione());
        $this->assertEquals('90011', $localizzazione->getCap());
    }

    public function testApplicaVariazioneLatoMonitoraggioUnaSede(): void {
        $proponente = $this->variazione->getRichiesta()->getMandatario();
        $sedeOperativaEsistente = new SedeOperativa($proponente);
        $proponente->addSedi($sedeOperativaEsistente);
        $indirizzoEsistente = new Indirizzo();
        $indirizzoEsistente->setVia('vecchia via');
        $sedeEsistente = new Sede(null, $indirizzoEsistente);
        $this->variazione->setSedeOperativa($sedeEsistente);
        $sedeOperativaEsistente->setSede($sedeEsistente);

        $this->applicaVariazione();

        /** @var SedeOperativa */
        $sedeOperativa = $this->variazione->getRichiesta()->getMandatario()->getSedi()->first();
        $this->assertInstanceOf(SedeOperativa::class, $sedeOperativa);
        $sede = $sedeOperativa->getSede();
        $indirizzo = $sede->getIndirizzo();

        $this->assertEquals('via dei pazzi', $indirizzo->getVia());
        $this->assertEquals('0', $indirizzo->getNumeroCivico());
        $this->assertEquals('90011', $indirizzo->getCap());
        $this->assertNotNull($indirizzo->getComune());
    }

    public function testApplicaVariazioneLatoMonitoraggioLocalizzazionePresente(): void {
        $richiesta = $this->variazione->getRichiesta();
        $proponente = $richiesta->getMandatario();
        $sedeOperativaEsistente = new SedeOperativa($proponente);
        $proponente->addSedi($sedeOperativaEsistente);
        $indirizzoEsistente = new Indirizzo();
        $indirizzoEsistente->setVia('vecchia via');
        $sedeEsistente = new Sede(null, $indirizzoEsistente);
        $sedeOperativaEsistente->setSede($sedeEsistente);
        $localizzazioneEsistente = new LocalizzazioneGeografica();
        $richiesta->addMonLocalizzazioneGeografica($localizzazioneEsistente);

        $this->applicaVariazione();

        /** @var SedeOperativa */
        $sedeOperativa = $this->variazione->getRichiesta()->getMandatario()->getSedi()->first();

        $this->assertInstanceOf(SedeOperativa::class, $sedeOperativa);
        $localizzazioni = $richiesta->getMonLocalizzazioneGeografica();

        $this->assertCount(1, $localizzazioni);
        /** @var LocalizzazioneGeografica */
        $localizzazione = $localizzazioni->first();

        $this->assertEquals('via dei pazzi, 0', $localizzazione->getIndirizzo());
        $this->assertNotNull($localizzazione->getLocalizzazione());
    }
    
    public function testAggiungoLocalizzazioneSu2(): void {
        $richiesta = $this->variazione->getRichiesta();
        $proponente = $richiesta->getMandatario();

        $sedeOperativaEsistente = new SedeOperativa($proponente);
        $proponente->addSedi($sedeOperativaEsistente);
        $indirizzoEsistente = new Indirizzo();
        $indirizzoEsistente->setVia('vecchia via');
        $indirizzoEsistente->setNumeroCivico('0');
        $comune = new GeoComune();
        $indirizzoEsistente->setComune($comune);
        $sedeEsistente = new Sede(null, $indirizzoEsistente);
        $this->variazione->setSedeOperativa($sedeEsistente);
        $sedeOperativaEsistente->setSede($sedeEsistente);
        $localizzazioneEsistente = new LocalizzazioneGeografica($richiesta);
        $localizzazioneEsistente->setIndirizzo('vecchia via sbagliata, 0');
        $richiesta->addMonLocalizzazioneGeografica($localizzazioneEsistente);

        //Seconda sede
        $sedeOperativaEsistente = new SedeOperativa($proponente);
        $proponente->addSedi($sedeOperativaEsistente);
        $indirizzoEsistente = new Indirizzo();
        $indirizzoEsistente->setVia('vecchia via 2');
        $comune = new GeoComune();
        $indirizzoEsistente->setComune($comune);
        $sedeEsistente = new Sede(null, $indirizzoEsistente);
        $sedeOperativaEsistente->setSede($sedeEsistente);
        $localizzazioneEsistente = new LocalizzazioneGeografica($richiesta);
        $localizzazioneEsistente->setIndirizzo('vecchia via 2, 0');
        $richiesta->addMonLocalizzazioneGeografica($localizzazioneEsistente);

        $this->applicaVariazione();

        /** @var SedeOperativa */
        $sedeOperativa = $this->variazione->getRichiesta()->getMandatario()->getSedi()->first();

        $this->assertInstanceOf(SedeOperativa::class, $sedeOperativa);
        $indirizzi = $richiesta->getMonLocalizzazioneGeografica()->map(function(LocalizzazioneGeografica $l){
            return $l->getIndirizzo();
        })->toArray();

        $this->assertCount(3, $indirizzi);

        $this->assertContains('vecchia via sbagliata, 0', $indirizzi);
        $this->assertContains('vecchia via 2, 0', $indirizzi);
        $this->assertContains('via dei pazzi, 0', $indirizzi);
    }

    public function testCambio1LocalizzazioneSu2(): void {
        $richiesta = $this->variazione->getRichiesta();
        $proponente = $richiesta->getMandatario();

        $sedeOperativaEsistente = new SedeOperativa($proponente);
        $proponente->addSedi($sedeOperativaEsistente);
        $indirizzoEsistente = new Indirizzo();
        $indirizzoEsistente->setVia('vecchia via');
        $indirizzoEsistente->setNumeroCivico('0');
        $comune = new GeoComune();
        $indirizzoEsistente->setComune($comune);
        $sedeEsistente = new Sede(null, $indirizzoEsistente);
        $this->variazione->setSedeOperativa($sedeEsistente);
        $sedeOperativaEsistente->setSede($sedeEsistente);
        $localizzazioneEsistente = new LocalizzazioneGeografica($richiesta);
        $localizzazioneEsistente->setIndirizzo('vecchia via, 0');
        $richiesta->addMonLocalizzazioneGeografica($localizzazioneEsistente);

        //Seconda sede
        $sedeOperativaEsistente = new SedeOperativa($proponente);
        $proponente->addSedi($sedeOperativaEsistente);
        $indirizzoEsistente = new Indirizzo();
        $indirizzoEsistente->setVia('vecchia via 2');
        $comune = new GeoComune();
        $indirizzoEsistente->setComune($comune);
        $sedeEsistente = new Sede(null, $indirizzoEsistente);
        $sedeOperativaEsistente->setSede($sedeEsistente);
        $localizzazioneEsistente = new LocalizzazioneGeografica($richiesta);
        $localizzazioneEsistente->setIndirizzo('vecchia via 2, 0');
        $richiesta->addMonLocalizzazioneGeografica($localizzazioneEsistente);

        $this->applicaVariazione();

        /** @var SedeOperativa */
        $sedeOperativa = $this->variazione->getRichiesta()->getMandatario()->getSedi()->first();

        $this->assertInstanceOf(SedeOperativa::class, $sedeOperativa);
        $localizzazioni = $richiesta->getMonLocalizzazioneGeografica()->filter(function(LocalizzazioneGeografica $l){
            return $l->getIndirizzo() == 'via dei pazzi, 0';
        });

        $this->assertCount(1, $localizzazioni);
        /** @var LocalizzazioneGeografica */
        $localizzazione = $localizzazioni->first();

        $this->assertNotNull($localizzazione->getLocalizzazione());
    }

}
