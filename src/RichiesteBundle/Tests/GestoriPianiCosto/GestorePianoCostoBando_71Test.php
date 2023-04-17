<?php

namespace RichiesteBundle\Tests\GestoriPianiCosto;

use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\GestoriPianiCosto\GestorePianoCostoBando_71;
use RichiesteBundle\Entity\PianoCosto;
use RichiesteBundle\Entity\VocePianoCosto;
use RichiesteBundle\Entity\SezionePianoCosto;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Entity\TipologiaNaturaLaboratorio;
use RichiesteBundle\Entity\ProponenteRepository;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Procedura;

class GestorePianoCostoBando_71Test extends TestBaseService {
    /**
     * @var GestorePianoCostoBando_71
     */
    protected $gestore;

    /**
     * @var Proponente
     */
    protected $proponente;

    /**
     * @var Procedura
     */
    protected $procedura;

    public function setUp() {
        parent::setUp();
        $this->procedura = new Bando();
        $richiesta = new Richiesta();
        $richiesta->setProcedura($this->procedura);
        $this->procedura->addRichieste($richiesta);

        $this->proponente = new Proponente();
        $this->proponente->setId(1);
        $this->proponente->setRichiesta($richiesta);
        $richiesta->addProponente($this->proponente);

        $this->gestore = new GestorePianoCostoBando_71($this->container);

        $repo = $this->createMock(ProponenteRepository::class);
        $repo->method('find')->willReturn($this->proponente);

        $this->em->method('getRepository')
            ->with('RichiesteBundle:Proponente')
            ->willReturn($repo);
    }

    public function testValidazioneProponenteVuotoOk() {
        $sezione = new SezionePianoCosto();
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_PERSONALE, 0.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_ATTREZZATURE, 0.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_CONSULENZA, 0.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_ALTRE, 0.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_GENERALI, 0.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::TOT, 0.0);

        $esito = $this->gestore->validaPianoDeiCostiProponente($this->proponente);

        $this->assertInstanceOf(EsitoValidazione::class, $esito);
        $this->assertSame(true, $esito->getEsito());
        $this->assertEmpty($esito->getMessaggi());
        $this->assertEmpty($esito->getMessaggiSezione());
    }

    protected function addVoce(SezionePianoCosto $sezione, string $codice, float $importo) {
        $piano = new PianoCosto();
        $piano->setCodice($codice);
        $piano->setSezionePianoCosto($sezione);
        $sezione->addPianiCosto($piano);

        $voceSpese = new VocePianoCosto();
        $voceSpese->setProponente($this->proponente);
        $voceSpese->setPianoCosto($piano);
        $voceSpese->setImportoAnno1($importo);
        $this->proponente->addVociPianoCosto($voceSpese);
    }

    public function testValidazioneProponenteLimiteMasssimoEntiPubbliciOk() {
        $naturaLaborio = new TipologiaNaturaLaboratorio();
        $naturaLaborio->setPubblico(true);
        $this->proponente->setTipoNaturaLaboratorio($naturaLaborio);

        $sezione = new SezionePianoCosto();
        $this->procedura->addSezioniPianiCosto($sezione);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_PERSONALE, 300.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_ATTREZZATURE, 0.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_CONSULENZA, 0.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_ALTRE, 700.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_GENERALI, 250.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::TOT, 1250.0);

        $esito = $this->gestore->validaPianoDeiCostiProponente($this->proponente);

        $this->assertInstanceOf(EsitoValidazione::class, $esito);
        $this->assertTrue($esito->getEsito());
        $this->assertEmpty($esito->getMessaggi());
        $this->assertEmpty($esito->getMessaggiSezione());
    }

    public function testTotaleNonValido() {
        $sezione = new SezionePianoCosto();
        $this->procedura->addSezioniPianiCosto($sezione);

        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_PERSONALE, 400.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_ATTREZZATURE, 0.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_CONSULENZA, 0.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_ALTRE, 450.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_GENERALI, 250.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::TOT, 1000.0);

        $esito = $this->gestore->validaPianoDeiCostiProponente($this->proponente);

        $this->assertInstanceOf(EsitoValidazione::class, $esito);
        $this->assertFalse($esito->getEsito());
        $this->assertContains("Il totale non corrisponde al totale delle voci", $esito->getTuttiMessaggi());
    }

    public function testValidazioneProponenteOltreLimiteMasssimoEntiPubblici() {
        $naturaLaborio = new TipologiaNaturaLaboratorio();
        $naturaLaborio->setPubblico(true);
        $this->proponente->setTipoNaturaLaboratorio($naturaLaborio);

        $sezione = new SezionePianoCosto();
        $this->procedura->addSezioniPianiCosto($sezione);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_PERSONALE, 1000.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_ATTREZZATURE, 0.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_CONSULENZA, 0.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_ALTRE, 0.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::SPESE_GENERALI, 250.0);
        $this->addVoce($sezione, GestorePianoCostoBando_71::TOT, 1250.0);

        $esito = $this->gestore->validaPianoDeiCostiProponente($this->proponente);

        $this->assertInstanceOf(EsitoValidazione::class, $esito);
        $this->assertTrue($esito->getEsito());
    }

}
