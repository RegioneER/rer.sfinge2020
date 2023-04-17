<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaAP02;
use Doctrine\Common\Persistence\ObjectRepository;
use MonitoraggioBundle\Entity\TC7ProgettoComplesso;
use MonitoraggioBundle\Entity\TC8GrandeProgetto;
use MonitoraggioBundle\Entity\TC9TipoLivelloIstituzione;
use MonitoraggioBundle\Entity\TC10TipoLocalizzazione;
use MonitoraggioBundle\Entity\TC13GruppoVulnerabileProgetto;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;
use MonitoraggioBundle\Repository\AP02InformazioniGeneraliRepository;
use MonitoraggioBundle\Repository\TC7ProgettoComplessoRepository;
use MonitoraggioBundle\Repository\TC8GrandeProgettoRepository;
use MonitoraggioBundle\Repository\TC9TipoLivelloIstituzioneRepository;
use MonitoraggioBundle\Repository\TC10TipoLocalizzazioneRepository;
use MonitoraggioBundle\Repository\TC13GruppoVulnerabileProgettoRepository;
use MonitoraggioBundle\Entity\AP02InformazioniGenerali;

class EsportaAP02Test extends EsportazioneRichiestaBase {
    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaAP02($this->container);
    }

    public function testImportazioneConSuccesso(): void {
        $tc7 = new TC7ProgettoComplesso();
        $tc8 = new TC8GrandeProgetto();
        $tc9 = new TC9TipoLivelloIstituzione();
        $tc10 = new TC10TipoLocalizzazione();
        $tc13 = new TC13GruppoVulnerabileProgetto();

        $tc7Repository = $this->mockFindOneByMethod(TC7ProgettoComplessoRepository::class, $tc7);
        $tc8Repository = $this->mockFindOneByMethod(TC8GrandeProgettoRepository::class, $tc8);
        $tc9Repository = $this->mockFindOneByMethod(TC9TipoLivelloIstituzioneRepository::class, $tc9);
        $tc10Repository = $this->mockFindOneByMethod(TC10TipoLocalizzazioneRepository::class, $tc10);
        $tc13Repository = $this->mockFindOneByMethod(TC13GruppoVulnerabileProgettoRepository::class, $tc13);

        $this->em->expects($this->any())
                ->method('getRepository')
                ->withConsecutive(
                    ['MonitoraggioBundle:TC7ProgettoComplesso'],
                    ['MonitoraggioBundle:TC8GrandeProgetto'],
                    ['MonitoraggioBundle:TC9TipoLivelloIstituzione'],
                    ['MonitoraggioBundle:TC10TipoLocalizzazione'],
                    ['MonitoraggioBundle:TC13GruppoVulnerabileProgetto']
                )
                ->willReturnOnConsecutiveCalls(
                    $tc7Repository,
                    $tc8Repository,
                    $tc9Repository,
                    $tc10Repository,
                    $tc13Repository
                );

        $res = $this->esporta->importa($this->getInput());

        $this->assertEquals($res->getTc7ProgettoComplesso(), $tc7);
        $this->assertEquals($res->getTc8GrandeProgetto(), $tc8);
        $this->assertEquals($res->getTc9TipoLivelloIstituzione(), $tc9);
        $this->assertEquals($res->getTc10TipoLocalizzazione(), $tc10);
        $this->assertEquals($res->getTc13GruppoVulnerabileProgetto(), $tc13);
        $this->assertEquals($res->getCodLocaleProgetto(), 'COD_LOCALE_PROGETTO');
        $this->assertEquals($res->getGeneratoreEntrate(), 'GENERA TORE_ENTRA TE');
        $this->assertEquals($res->getFondoDiFondi(), 'FONDO_DI_FONDI');
        $this->assertEquals($res->getFondoDiFondi(), 'FONDO_DI_FONDI');
        $this->assertEquals($res->getFlgCancellazione(), 'FLG_CANCELLAZIONE');
    }

    private function getInput(): array {
        return [
            'COD_LOCALE_PROGETTO',
            'COD_PRG_COMPLESSO',
            'GRANDE_PROGETTO',
            'GENERA TORE_ENTRA TE',
            'LIV_ISTITUZIONE_STR_FIN',
            'FONDO_DI_FONDI',
            'TIPO_LOCALIZZAZIONE',
            'COD_VULNERABILI',
            'FLG_CANCELLAZIONE',
        ];
    }

    protected function mockFindOneByMethod($repositoryClass, $returnObject = null, $spy = null) {
        $spy = \is_null($spy) ? $this->any() : $spy;

        $mock = $this->createMock($repositoryClass);
        $mock->expects($spy)
                ->method('findOneBy')
                ->willReturn($returnObject);

        return $mock;
    }

    public function testImportazioneSenzaTipoLocalizzazione(): void {
        $this->em->expects($this->any())
                ->method('getRepository')
                ->withConsecutive(
                    ['MonitoraggioBundle:TC7ProgettoComplesso'],
                    ['MonitoraggioBundle:TC8GrandeProgetto'],
                    ['MonitoraggioBundle:TC9TipoLivelloIstituzione'],
                    ['MonitoraggioBundle:TC10TipoLocalizzazione']
                )
                ->willReturn(
                    $this->mockFindOneByMethod(ObjectRepository::class, null)
                );

        $this->expectExceptionMessage('Tipo localizzazione non valida');

        $res = $this->esporta->importa($this->getInput());
    }

    public function testImportazioneSenzaGruppoVunerabile(): void {
        $tc10 = new TC10TipoLocalizzazione();

        $repositoryMock = $this->createMock(ObjectRepository::class);
        $tc10Repository = $this->mockFindOneByMethod(TC10TipoLocalizzazioneRepository::class, $tc10);

        $this->em->expects($this->any())
                ->method('getRepository')
                ->withConsecutive(
                    ['MonitoraggioBundle:TC7ProgettoComplesso'],
                    ['MonitoraggioBundle:TC8GrandeProgetto'],
                    ['MonitoraggioBundle:TC9TipoLivelloIstituzione'],
                    ['MonitoraggioBundle:TC10TipoLocalizzazione'],
                    ['MonitoraggioBundle:TC13GruppoVulnerabileProgetto']
                )
                ->willReturnOnConsecutiveCalls(
                    $repositoryMock,
                    $repositoryMock,
                    $repositoryMock,
                    $tc10Repository,
                    $repositoryMock
                );

        $this->expectExceptionMessage("Gruppo vulnerabile progetto non valido");
        $res = $this->esporta->importa($this->getInput());
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

    public function testErroreEsportazioneSenzaTipoLocalizzazione() {
        $this->expectExceptionMessage('Tipo localizzazione non definito per il progetto PG/2016/123456');
        $res = $this->esporta->execute($this->richiesta, $this->tavola);
        $this->assertEquals('PG/2016/123456', $res->getCodLocaleProgetto());
        $this->assertNull($res->getTc7ProgettoComplesso());
    }

    public function testErroreEsportazioneSenzaGruppoVulnerabile(): void {
        $tc10 = new TC10TipoLocalizzazione();
        $this->richiesta->setMonTipoLocalizzazione($tc10);

        $this->expectExceptionMessage('Gruppo vulnerabile non definito per il progetto PG/2016/123456');

        $res = $this->esporta->execute($this->richiesta, $this->tavola);
    }

    public function testEsportazioneNonNecessaria(): void {
        $repo = $this->createMock(AP02InformazioniGeneraliRepository::class);

        $repo->expects($this->atLeast(1))
        ->method('isEsportabile')
        ->with($this->isInstanceOf(MonitoraggioConfigurazioneEsportazioneRichiesta::class))
        ->willReturn(false);

        $this->em->method('getRepository')->willReturn($repo);
        $this->expectExceptionMessage('Esportazione struttura AP02 per il progetto PG/2016/123456 non necessaria');
        $res = $this->esporta->execute($this->richiesta, $this->tavola, true);
    }

    public function testEsportazioneConSuccesso(): void {
        $tc10 = new TC10TipoLocalizzazione();
        $tc13 = new TC13GruppoVulnerabileProgetto();
        $this->richiesta->setMonGruppoVulnerabile($tc13);
        $this->richiesta->setMonTipoLocalizzazione($tc10);

        $res = $this->esporta->execute($this->richiesta, $this->tavola);

        $this->assertNotNull($res);
        $this->assertInstanceOf(AP02InformazioniGenerali::class, $res);
        $this->assertEquals(self::PG . '/' . self::ANNO_PG . '/' . self::NUM_PG, $res->getCodLocaleProgetto());
        $this->assertSame($tc10, $res->getTc10TipoLocalizzazione());
        $this->assertSame($tc13, $res->getTc13GruppoVulnerabileProgetto());
        $this->assertNull($res->getTc7ProgettoComplesso());
        $this->assertNull($res->getTc9TipoLivelloIstituzione());
        $this->assertNull($res->getTc8GrandeProgetto());
        $this->assertNull($res->getFlgCancellazione());
    }
}
