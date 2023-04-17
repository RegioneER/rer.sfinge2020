<?php

namespace AttuazioneControlloBundle\Tests\Entity;

use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\VariazioneVocePianoCosto;
use PHPUnit\Framework\TestCase;
use AttuazioneControlloBundle\Entity\VariazionePianoCosti;
use AttuazioneControlloBundle\Entity\Proroga;
use AttuazioneControlloBundle\Entity\ProrogaRendicontazione;
use AttuazioneControlloBundle\Entity\StatoProroga;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;

class AttuazioneControlloRichiestaTest extends TestCase {
    /**
     * @var AttuazioneControlloRichiesta
     */
    protected $atc;

    public function setUp() {
        $this->atc = new AttuazioneControlloRichiesta();
        $richiesta = new Richiesta();
        $richiesta->setAttuazioneControllo($this->atc);
        $this->atc->setRichiesta($richiesta);
        $istruttoria = new IstruttoriaRichiesta();
        $istruttoria->setRichiesta($richiesta);
        $richiesta->setIstruttoria($istruttoria);
    }

    public function testCostoAmessoSenzaVariazione(): void {
        $this->setCostoAmmessoIstruttoria(1000);

        $res = $this->atc->getCostoAmmesso();

        $this->assertEquals(1000, $res);
    }

    protected function setCostoAmmessoIstruttoria(?float $importo): void {
        $this->getIstruttoriaRichiesta()->setCostoAmmesso($importo);
    }

    protected function getIstruttoriaRichiesta(): IstruttoriaRichiesta {
        return $this->atc->getRichiesta()->getIstruttoria();
    }

    public function testCostoAmmessoVariazione(): void {
        $variazione = $this->creaVariazione();
        // $this->atc->addVariazioni($variazione);		duplicazione

        $this->setCostoAmmessoIstruttoria(1000);
        $variazione->setCostoAmmesso(2000);

        $res = $this->atc->getCostoAmmesso();

        $this->assertEquals(2000, $res);
    }

    protected function creaVariazione(): VariazionePianoCosti {
        $variazione = new VariazionePianoCosti();
        $variazione->setEsitoIstruttoria(true);
        $variazione->setDataInvio(new \DateTime('2010-01-01'));
        $variazione->setAttuazioneControlloRichiesta($this->atc);
        $variazioneVocePianoCosto = new VariazioneVocePianoCosto();
        $variazioneVocePianoCosto->setVariazione($variazione);
        $variazione->addVocePianoCosto($variazioneVocePianoCosto);

        return $variazione;
    }

    public function testCostoAmmessoNulloVariazione(): void {
        $this->creaVariazione();
        $this->setCostoAmmessoIstruttoria(1000);

        $res = $this->atc->getCostoAmmesso();

        $this->assertEquals(1000, $res);
    }

    public function testCostoAmmessoNulloAncheVariazione(): void {
        $this->creaVariazione();

        $res = $this->atc->getCostoAmmesso();

        $this->assertNull($res);
    }

    public function testContributoAmmessoIstruttoria(): void {
        $this->setContributoAmmessoIstruttoria(1000);

        $res = $this->atc->getContributoConcesso();

        $this->assertEquals(1000, $res);
    }

    protected function setContributoAmmessoIstruttoria(?float $contributo): void {
        $this->getIstruttoriaRichiesta()->setContributoAmmesso($contributo);
    }

    public function testContributoAmmessoVariazione(): void {
        $variazione = $this->creaVariazione();
        $variazione->setContributoAmmesso(2000);
        $this->setContributoAmmessoIstruttoria(1000);

        $res = $this->atc->getContributoConcesso();

        $this->assertEquals(2000, $res);
    }

    public function testContributoAmmessoVariazioneNulla() {
        $this->creaVariazione();
        $this->setContributoAmmessoIstruttoria(1000);

        $res = $this->atc->getContributoConcesso();

        $this->assertEquals(1000, $res);
    }

    public function testHasProrogaRendicontazioneAttiva(): void {
        $modalitaPagamento = new ModalitaPagamento();
        $pr = new ProrogaRendicontazione($this->atc);
        $pr->setDataInizio(new \DateTime('yesterday'))
        ->setDataScadenza(new \DateTime('tomorrow'))
        ->setModalitaPagamento($modalitaPagamento);
        $this->atc->addProrogheRendicontazione($pr);

        $res = $this->atc->hasProrogaRendicontazione($modalitaPagamento);

        $this->assertTrue($res, 'La proroga di rendicontazione è attiva');
    }

    public function testHasProrogaRendicontazioneNonAttiva(): void {
        $modalitaPagamento = new ModalitaPagamento();
        $pr = new ProrogaRendicontazione($this->atc);
        $pr->setDataInizio(new \DateTime('tomorrow'))
        ->setDataScadenza(new \DateTime('+2 days'))
        ->setModalitaPagamento($modalitaPagamento);
        $this->atc->addProrogheRendicontazione($pr);

        $res = $this->atc->hasProrogaRendicontazione($modalitaPagamento);

        $this->assertFalse($res, 'La proroga di rendicontazione NON è attiva');
    }

    public function testGetProrogaRendicontazioneAttiva(): void {
        $modalitaPagamento = new ModalitaPagamento();
        $pr = new ProrogaRendicontazione($this->atc);
        $pr->setDataInizio(new \DateTime('yesterday'))
        ->setDataScadenza(new \DateTime('tomorrow'))
        ->setModalitaPagamento($modalitaPagamento);
        $this->atc->addProrogheRendicontazione($pr);

        $res = $this->atc->getProrogaRendicontazione($modalitaPagamento);

        $this->assertEquals($pr, $res);
    }

    public function testGetProrogaRendicontazioneNonAttiva(): void {
        $modalitaPagamento = new ModalitaPagamento();
        $pr = new ProrogaRendicontazione($this->atc);
        $pr->setDataInizio(new \DateTime('tomorrow'))
        ->setDataScadenza(new \DateTime('+2 days'))
        ->setModalitaPagamento($modalitaPagamento);
        $this->atc->addProrogheRendicontazione($pr);

        $res = $this->atc->getProrogaRendicontazione($modalitaPagamento);

        $this->assertNull($res);
    }
}
