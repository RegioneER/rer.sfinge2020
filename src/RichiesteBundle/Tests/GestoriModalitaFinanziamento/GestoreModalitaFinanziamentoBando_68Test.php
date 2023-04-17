<?php

namespace RichiesteBundle\Tests\GestoriModalitaFinanziamento;

use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\GestoriModalitaFinanziamento\GestoreModalitaFinanziamentoBando_68;
use RichiesteBundle\Entity\ProponenteRepository;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\VoceModalitaFinanziamento;
use RichiesteBundle\Entity\ModalitaFinanziamento;
use RichiesteBundle\Entity\VocePianoCosto;
use RichiesteBundle\GestoriPianiCosto\GestorePianoCostoBando_68;
use RichiesteBundle\Entity\PianoCosto;


class GestoreModalitaFinanziamentoBando_68Test extends TestBaseService
{
    /**
     * @var GestoreModalitaFinanziamentoBando_68
     */
    protected $gestore;

    /**
     * @var Proponente
     */
    protected $proponente;
    
    public function setUp()
    {
        parent::setUp();
        $this->gestore = new GestoreModalitaFinanziamentoBando_68($this->container);
        $proponenteRepository = $this->createMock(ProponenteRepository::class);
        $this->proponente = new Proponente();
        $proponenteRepository->method('find')->willReturn($this->proponente);
        $this->em->method('getRepository')->willReturn($proponenteRepository);
    }

    public function testValidaCorretto(){
        $voceP = $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_P, 50, 50);
        $voceF = $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_F, 50, 50);
        $this->addVocePiano(GestorePianoCostoBando_68::VOCE_TOTALE, 100);
        $res = $this->gestore->validaModalitaFinanziamentoRichiesta(1,1);

        $this->assertTrue($res->getEsito());
        $this->assertEmpty($res->getTuttiMessaggi());
    }

    protected function addVoceModalita(string $codice, float $percentuale, float $importo):VoceModalitaFinanziamento{
        $modalita = new ModalitaFinanziamento();
        $modalita->setCodice($codice);
        $voce = new VoceModalitaFinanziamento($this->proponente, $modalita);
        $voce->setImporto($importo);
        $voce->setPercentuale($percentuale);
        $this->proponente->addVociModalitaFinanziamento($voce);

        return $voce;
    }

    protected function addVocePiano(string $codice, float $importo):VocePianoCosto{
        $piano = new PianoCosto();
        $piano->setCodice($codice);
        $voce = new VocePianoCosto();
        $voce->setProponente($this->proponente);
        $voce->setImportoAnno1($importo);
        $voce->setPianoCosto($piano);
        $this->proponente->addVociPianoCosto($voce);

        return $voce;
    }

    public function testValidaPercentualiNonCorretto(){
        $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_P, 60, 50);
        $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_F, 40, 50);
        $this->addVocePiano(GestorePianoCostoBando_68::VOCE_TOTALE, 100);
        $res = $this->gestore->validaModalitaFinanziamentoRichiesta(1,1);

        $this->assertFalse($res->getEsito());
        $this->assertContains('La percentuale indicata è errata', $res->getTuttiMessaggi());
    }


    public function testValidaImportoNonCorretto(){
        $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_P, 50, 51);
        $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_F, 50, 40);
        $this->addVocePiano(GestorePianoCostoBando_68::VOCE_TOTALE, 100);
        $res = $this->gestore->validaModalitaFinanziamentoRichiesta(1,1);

        $this->assertFalse($res->getEsito());
        $this->assertContains('Il totale delle voci deve corrispondere al totale del piano costi', $res->getTuttiMessaggi());
    }

    public function testPercentualeSuperiore100(){
        $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_P, 51, 51);
        $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_F, 50, 49);
        $this->addVocePiano(GestorePianoCostoBando_68::VOCE_TOTALE, 100);
        $res = $this->gestore->validaModalitaFinanziamentoRichiesta(1,1);

        $this->assertFalse($res->getEsito());
        $this->assertContains('La somma delle percentuali deve essere pari al 100%', $res->getTuttiMessaggi());
    }

    public function testVoceModalitaNonPresente(){
        $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_F, 50, 49);
        $this->addVocePiano(GestorePianoCostoBando_68::VOCE_TOTALE, 100);

        $this->expectExceptionMessage('Voce di modalità finanziamento non presente');
        $res = $this->gestore->validaModalitaFinanziamentoRichiesta(1,1);
    }

    public function testTotalePianoCostiNonPresente(){
        $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_F, 50, 50);
        $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_P, 50, 50);
        $this->expectExceptionMessage("Voce piano costo 'TOTALE' finanziamento non presente");
        $res = $this->gestore->validaModalitaFinanziamentoRichiesta(1,1);
    }

    public function testPercetualeDivisa0(){
        $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_P, 100, 0);
        $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_F, 0, 0);
        $this->addVocePiano(GestorePianoCostoBando_68::VOCE_TOTALE, 0);
        $res = $this->gestore->validaModalitaFinanziamentoRichiesta(1,1);
        $this->assertTrue($res->getEsito());
    }

    public function testMinimo15PercentoP(){
        $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_P, 14.999, 0);
        $this->addVoceModalita(GestoreModalitaFinanziamentoBando_68::CODICE_VOCE_F, 85.001, 0);
        $this->addVocePiano(GestorePianoCostoBando_68::VOCE_TOTALE, 0);
        $res = $this->gestore->validaModalitaFinanziamentoRichiesta(1,1);
        $this->assertFalse($res->getEsito());
        $this->assertContains('La percentuale dei mezzi propri incrementali deve essere minimo il 15%', $res->getTuttiMessaggi());
    }
}