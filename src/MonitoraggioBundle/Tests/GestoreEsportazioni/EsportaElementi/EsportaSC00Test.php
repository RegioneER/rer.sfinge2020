<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use Doctrine\Common\Persistence\ObjectRepository;
use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaSC00;
use MonitoraggioBundle\Entity\TC24RuoloSoggetto;
use MonitoraggioBundle\Entity\TC25FormaGiuridica;
use MonitoraggioBundle\Form\Entity\TabelleContesto\TC26;
use MonitoraggioBundle\Entity\TC26Ateco;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Repository\SC00SoggettiCollegatiRepository;
use AttuazioneControlloBundle\Entity\SoggettiCollegati;
use SoggettoBundle\Entity\FormaGiuridica;
use SoggettoBundle\Entity\Azienda;
use MonitoraggioBundle\Entity\SC00SoggettiCollegati;


class EsportaSC00Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaSC00
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaSC00($this->container);
    }

    public function testVerificaPrivato() {
        $tc24 = new TC24RuoloSoggetto();
        $tc25 = new TC25FormaGiuridica();
        $tc26 = new TC26Ateco();

        $this->setUpRepositories($tc24, $tc25, $tc26);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc26Repository', //tc24
            'CODICE_FISCALE',
            'N', //FLAG_SOGGETTO_PUBBLI CO
            '', //COD_UNI_IPA
            '', //DENOMINAZIONE_SOG
            'DENOMINAZIONE_SOG', //tc25
            'ateco', //tc26
            'NOTE',
            'S', //FLG_CANCELLAZIONE
        ];
        $res = $this->esporta->importa($input);
        $this->assertEquals($res->getTc24RuoloSoggetto(), $tc24);
        $this->assertEquals($res->getTc25FormaGiuridica(), $tc25);
        $this->assertEquals($res->getTc26Ateco(), $tc26);
        $this->assertEquals($res->getCodLocaleProgetto(), 'COD_LOCALE_PROGETTO');
        $this->assertEquals($res->getCodiceFiscale(), 'CODICE_FISCALE');
        $this->assertEquals($res->getCodUniIpa(), '');
        $this->assertEquals($res->getDenominazioneSog(), '');
        $this->assertEquals($res->getNote(), 'NOTE');
        $this->assertEquals($res->getFlagSoggettoPubblico(), 'N');
        $this->assertEquals($res->getFlgCancellazione(), 'S');
    }

    public function testVerificaPrivatoSenzaAteco() {
        $tc24 = new TC24RuoloSoggetto();
        $tc25 = new TC25FormaGiuridica();

        $this->setUpRepositories($tc24, $tc25, null);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc26Repository', //tc24
            'CODICE_FISCALE',
            'N', //FLAG_SOGGETTO_PUBBLI CO
            '', //COD_UNI_IPA
            '', //DENOMINAZIONE_SOG
            'DENOMINAZIONE_SOG', //tc25
            'ateco', //tc26
            'NOTE',
            'S', //FLG_CANCELLAZIONE
        ];
        $res = $this->esporta->importa($input);
        $this->assertEquals($res->getTc24RuoloSoggetto(), $tc24);
        $this->assertEquals($res->getTc25FormaGiuridica(), $tc25);
        $this->assertNull($res->getTc26Ateco());
        $this->assertEquals($res->getCodLocaleProgetto(), 'COD_LOCALE_PROGETTO');
        $this->assertEquals($res->getCodiceFiscale(), 'CODICE_FISCALE');
        $this->assertEquals($res->getCodUniIpa(), '');
        $this->assertEquals($res->getDenominazioneSog(), '');
        $this->assertEquals($res->getNote(), 'NOTE');
        $this->assertEquals($res->getFlagSoggettoPubblico(), 'N');
        $this->assertEquals($res->getFlgCancellazione(), 'S');
    }

    protected function setUpRepositories($tc24, $tc25, $tc26) {
        $tc24Repository = $this->createMock(ObjectRepository::class);
        $tc25Repository = $this->createMock(ObjectRepository::class);
        $tc26Repository = $this->createMock(ObjectRepository::class);


        $tc24Repository
        ->method('findOneBy')
        ->willReturn($tc24);
        $tc25Repository
        ->method('findOneBy')
        ->willReturn($tc25);
        $tc26Repository
        ->method('findOneBy')
        ->willReturn($tc26);
        $this->em
        ->method('getRepository')
        ->will(
            $this->returnValueMap([
                ['MonitoraggioBundle:TC24RuoloSoggetto', $tc24Repository],
                ['MonitoraggioBundle:TC25FormaGiuridica', $tc25Repository],
                ['MonitoraggioBundle:TC26Ateco',  $tc26Repository],
            ])
        );
    }

    public function testImportazioneSenzaRuolo() {
        $tc24 = new TC24RuoloSoggetto();
        $tc25 = new TC25FormaGiuridica();

        $this->setUpRepositories(null, $tc25, null);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc26Repository', //tc24
            'CODICE_FISCALE',
            'N', //FLAG_SOGGETTO_PUBBLI CO
            '', //COD_UNI_IPA
            '', //DENOMINAZIONE_SOG
            'DENOMINAZIONE_SOG', //tc25
            'ateco', //tc26
            'NOTE',
            'S', //FLG_CANCELLAZIONE
        ];
        $this->expectException(EsportazioneException::class);
        $res = $this->esporta->importa($input);
    }

    public function testVerificaPubblico() {
        $tc24 = new TC24RuoloSoggetto();
        $tc25 = new TC25FormaGiuridica();
        // $tc26 = new TC26Ateco();
        $this->setUpRepositories($tc24, $tc25, null);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc26Repository', //tc24
            'CODICE_FISCALE',
            'S', //FLAG_SOGGETTO_PUBBLI CO
            'COD_UNI_IPA', //COD_UNI_IPA
            'DENOMINAZIONE_SOG', //DENOMINAZIONE_SOG
            'FORMA_GIURIDICA', //tc25
            'ateco', //tc26
            'NOTE',
            'S', //FLG_CANCELLAZIONE
        ];
        $res = $this->esporta->importa($input);
        $this->assertEquals($res->getTc24RuoloSoggetto(), $tc24);
        $this->assertEquals($res->getTc25FormaGiuridica(), $tc25);
        $this->assertNull($res->getTc26Ateco());
        $this->assertEquals($res->getCodLocaleProgetto(), 'COD_LOCALE_PROGETTO');
        $this->assertEquals($res->getCodiceFiscale(), 'CODICE_FISCALE');
        $this->assertEquals($res->getCodUniIpa(), 'COD_UNI_IPA');
        $this->assertEquals($res->getDenominazioneSog(), 'DENOMINAZIONE_SOG');
        $this->assertEquals($res->getFlagSoggettoPubblico(), 'S');
        $this->assertEquals($res->getNote(), 'NOTE');
        $this->assertEquals($res->getFlgCancellazione(), 'S');
    }

    public function testVerificaSenzaCodiceFiscale() {
        $tc24 = new TC24RuoloSoggetto();
        $tc25 = new TC25FormaGiuridica();
        $tc26 = new TC26Ateco();

        $this->setUpRepositories($tc24, $tc25, $tc26);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc26Repository', //tc24
            '123456789012345*',
            'N', //FLAG_SOGGETTO_PUBBLI CO
            '', //COD_UNI_IPA
            'DENOMINAZIONE_SOG', //DENOMINAZIONE_SOG
            'FORMA_GIURIDICA', //tc25
            'ateco', //tc26
            'NOTE',
            'S', //FLG_CANCELLAZIONE
        ];
        $res = $this->esporta->importa($input);
        $this->assertEquals($res->getTc24RuoloSoggetto(), $tc24);
        $this->assertEquals($res->getTc25FormaGiuridica(), $tc25);
        $this->assertEquals($res->getTc26Ateco(), $tc26);
        $this->assertEquals($res->getCodLocaleProgetto(), 'COD_LOCALE_PROGETTO');
        $this->assertEquals($res->getCodiceFiscale(), '123456789012345*');
        $this->assertEquals($res->getCodUniIpa(), '');
        $this->assertEquals($res->getDenominazioneSog(), 'DENOMINAZIONE_SOG');
        $this->assertEquals($res->getFlagSoggettoPubblico(), 'N');
        $this->assertEquals($res->getNote(), 'NOTE');
        $this->assertEquals($res->getFlgCancellazione(), 'S');
    }

    public function testImportazioneSenzaFormaGiuridica()
    {
        $tc24 = new TC24RuoloSoggetto();
        $tc26 = new TC26Ateco();

        $this->setUpRepositories($tc24, null, $tc26);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc26Repository', //tc24
            'CODICE_FISCALE',
            'N', //FLAG_SOGGETTO_PUBBLI CO
            '', //COD_UNI_IPA
            '', //DENOMINAZIONE_SOG
            'DENOMINAZIONE_SOG', //tc25
            'ateco', //tc26
            'NOTE',
            'S', //FLG_CANCELLAZIONE
        ];

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->importa($input);
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testVerificaSenzaCodiceFiscaleSenzaAteco() {
        $tc24 = new TC24RuoloSoggetto();
        $tc25 = new TC25FormaGiuridica();
        // $tc26 = new TC26Ateco();

        $this->setUpRepositories($tc24, $tc25, null);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc26Repository', //tc24
            '123456789012345*',
            'N', //FLAG_SOGGETTO_PUBBLI CO
            '', //COD_UNI_IPA
            'DENOMINAZIONE_SOG', //DENOMINAZIONE_SOG
            'FORMA_GIURIDICA', //tc25
            'ateco', //tc26
            'NOTE',
            'S', //FLG_CANCELLAZIONE
        ];
        $res = $this->esporta->importa($input);
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testVerificaPubblicoSenzaDenominazione() {
        $tc24 = new TC24RuoloSoggetto();
        $tc25 = new TC25FormaGiuridica();
        // $tc26 = new TC26Ateco();

        $this->setUpRepositories($tc24, $tc25, null);
        
        $input = [
            'COD_LOCALE_PROGETTO',
            'tc26Repository', //tc24
            '123456789012345',
            'S', //FLAG_SOGGETTO_PUBBLI CO
            '', //COD_UNI_IPA
            '', //DENOMINAZIONE_SOG
            'FORMA_GIURIDICA', //tc25
            'ateco', //tc26
            'NOTE',
            'S', //FLG_CANCELLAZIONE
        ];
        $res = $this->esporta->importa($input);
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testVerificaSenzaCodiceFiscaleSenzaDenominazione() {
        $tc24 = new TC24RuoloSoggetto();
        $tc25 = new TC25FormaGiuridica();
        $tc26 = new TC26Ateco();

       $this->setUpRepositories($tc24, $tc25, $tc26);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc26Repository', //tc24
            '123456789012345*',
            'N', //FLAG_SOGGETTO_PUBBLI CO
            'COD_UNI_IPA', //COD_UNI_IPA
            '', //DENOMINAZIONE_SOG
            'FORMA_GIURIDICA', //tc25
            'ateco', //tc26
            'NOTE',
            'S', //FLG_CANCELLAZIONE
        ];
        $res = $this->esporta->importa($input);
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testVerificaPubblicoSenzaCodUniIpa() {
        $tc24 = new TC24RuoloSoggetto();
        $tc25 = new TC25FormaGiuridica();
        // $tc26 = new TC26Ateco();

        $this->setUpRepositories($tc24, $tc25, null);


        $input = [
            'COD_LOCALE_PROGETTO',
            'tc26Repository', //tc24
            '123456789012345',
            'S', //FLAG_SOGGETTO_PUBBLI CO
            '', //COD_UNI_IPA
            '', //DENOMINAZIONE_SOG
            'FORMA_GIURIDICA', //tc25
            'ateco', //tc26
            'NOTE',
            'S', //FLG_CANCELLAZIONE
        ];
        $res = $this->esporta->importa($input);
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testInputNull() {
        $this->esporta->importa(null);
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testEmptyInputNull() {
        $this->esporta->importa([]);
    }

    public function testEsportazioneNonNecessaria()
    {
        $repo = $this->createMock(SC00SoggettiCollegatiRepository::class);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(EsportazioneException::class);
        $this->esporta->execute($this->richiesta, $this->tavola, true);
    }

    public function testEsportazioneConSuccesso()
    {
        $sog = new SoggettiCollegati($this->richiesta);
        $soggetto = new Azienda();
        $forma = new FormaGiuridica();
        $tc25 = new TC25FormaGiuridica();
        $forma->setTc25FormaGiuridica($tc25);
        $soggetto->setFormaGiuridica($forma);
        $sog->setSoggetto($soggetto);

        $this->richiesta->addMonSoggettiCorrelati($sog);
        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);
        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        /** @var SC00SoggettiCollegati $first */
        $first = $res->first();
        $this->assertNotFalse($first);
        $this->assertSame($tc25, $first->getTc25FormaGiuridica());
    }
}
