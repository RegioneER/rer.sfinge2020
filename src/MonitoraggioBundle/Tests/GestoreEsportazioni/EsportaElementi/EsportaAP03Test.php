<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use Doctrine\Common\Persistence\ObjectRepository;
use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaAP03;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\TC11TipoClassificazione;
use MonitoraggioBundle\Entity\TC12Classificazione;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Repository\AP03ClassificazioniRepository;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\RichiestaProgrammaClassificazione;
use Doctrine\Common\Collections\Collection;
use MonitoraggioBundle\Entity\AP03Classificazioni;

class EsportaAP03Test extends EsportazioneRichiestaBase {
    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaAP03($this->container);
    }

    public function testImportazione() {
        $tc4 = new TC4Programma();
        $tc11 = new TC11TipoClassificazione();
        $tc12 = new TC12Classificazione();

        $this->setUpRepositories($tc4, $tc11, $tc12);

        $input = [
            'COD_LOCALE_PROGETTO',
            '1', //tc4
            'TIPO_CLASS', //tc11
            'COD_CLASSIFICAZIONE', //tc12
            'S', //FLG_CANCELLAZIONE
        ];
        $res = $this->esporta->importa($input);
        $this->assertEquals($res->getTc4Programma(), $tc4);
        $this->assertEquals($res->getTc11TipoClassificazione(), $tc11);
        $this->assertEquals($res->getClassificazione(), $tc12);
        $this->assertEquals($res->getCodLocaleProgetto(), 'COD_LOCALE_PROGETTO');
        $this->assertEquals($res->getFlgCancellazione(), 'S');
    }

    protected function setUpRepositories($tc4, $tc11, $tc12){
        $tc4Repository = $this->createMock(ObjectRepository::class);
        $tc11Repository = $this->createMock(ObjectRepository::class);
        $tc12Repository = $this->createMock(ObjectRepository::class);

        $tc4Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc4);

        $tc11Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc11);

        $tc12Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc12);

        $this->em->expects($this->any())
        ->method('getRepository')
        ->withConsecutive(
            ['MonitoraggioBundle:TC4Programma'],
            ['MonitoraggioBundle:TC11TipoClassificazione'],
            ['MonitoraggioBundle:TC12Classificazione']
        )
        ->willReturnOnConsecutiveCalls(
            $tc4Repository,
            $tc11Repository,
            $tc12Repository
        );
    }

    public function testImportazioneSenzaProgramma()
    {
        $this->expectExceptionMessage('Programma non valido');

        $tc11 = new TC11TipoClassificazione();
        $tc12 = new TC12Classificazione();

        $this->setUpRepositories(NULL, $tc11, $tc12);

        $input = [
            'COD_LOCALE_PROGETTO',
            '1', //tc4
            'TIPO_CLASS', //tc11
            'COD_CLASSIFICAZIONE', //tc12
            'S', //FLG_CANCELLAZIONE
        ];
        $res = $this->esporta->importa($input);
    }

    public function testImportazioneSenzaTipoClassificazione()
    {
        $this->expectExceptionMessage('Tipo classificazione non valida');

        $tc4 = new TC4Programma();
        $tc12 = new TC12Classificazione();

        $this->setUpRepositories($tc4, NULL, $tc12);

        $input = [
            'COD_LOCALE_PROGETTO',
            '1', //tc4
            'TIPO_CLASS', //tc11
            'COD_CLASSIFICAZIONE', //tc12
            'S', //FLG_CANCELLAZIONE
        ];
        $res = $this->esporta->importa($input);
    }

    public function testImportazioneSenzaClassificazione()
    {
        $this->expectExceptionMessage('Classificazione non valida');

        $tc4 = new TC4Programma();
        $tc11 = new TC11TipoClassificazione();

        $this->setUpRepositories($tc4, $tc11, NULL);

        $input = [
            'COD_LOCALE_PROGETTO',
            '1', //tc4
            'TIPO_CLASS', //tc11
            'COD_CLASSIFICAZIONE', //tc12
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

    public function testEsportazioneSenzaProgramma() {
        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage("Nessun programma associato per il progetto " . self::GetProtocollo());
        $this->esporta->execute($this->richiesta, $this->tavola);
    }

    public function testEsportazioneNonNecessaria() {
        $repo = $this->createMock(AP03ClassificazioniRepository::class);
        $this->esportazioneNonNecessaria($repo);
    }

    public function testEsportazioneOk() {
        $tc4 = new TC4Programma();
        $programma = new RichiestaProgramma($this->richiesta);
        $programma->setTc4Programma($tc4);
        $classificazione = new RichiestaProgrammaClassificazione($programma);
        $tc12 = new TC12Classificazione();
        $tc11 = new TC11TipoClassificazione();
        $tc12->setTipoClassificazione($tc11);
        $classificazione->setClassificazione($tc12);
        $programma->addClassificazioni($classificazione);
        $this->richiesta->addMonProgrammi($programma);

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);
        $this->assertInstanceOf(Collection::class, $res);

        /** @var AP03Classificazioni $element */
        $element = $res->first();
        $this->assertNotFalse($element);
        $this->assertInstanceOf(AP03Classificazioni::class, $element);
        $this->assertSame($tc12, $element->getClassificazione());
        $this->assertSame($tc4, $element->getTc4Programma());
        $this->assertEquals(self::GetProtocollo(), $element->getCodLocaleProgetto());
        $this->assertSame($tc11, $element->getTc11TipoClassificazione());
    }
}
