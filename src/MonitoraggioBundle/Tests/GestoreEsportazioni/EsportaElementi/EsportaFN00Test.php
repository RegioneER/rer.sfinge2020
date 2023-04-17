<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaFN00;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use MonitoraggioBundle\Entity\TC34DeliberaCIPE;
use MonitoraggioBundle\Entity\TC35Norma;
use MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica;
use MonitoraggioBundle\Repository\TC33FonteFinanziariaRepository;
use MonitoraggioBundle\Repository\TC34DeliberaCIPERepository;
use MonitoraggioBundle\Repository\TC35NormaRepository;
use MonitoraggioBundle\Repository\TC16LocalizzazioneGeograficaRepository;
use MonitoraggioBundle\Exception\EsportazioneException;
use Doctrine\Common\Collections\Collection;
use AttuazioneControlloBundle\Entity\Finanziamento;
use MonitoraggioBundle\Entity\FN00Finanziamento;
use MonitoraggioBundle\Repository\FN00FinanziamentoRepository;
use SoggettoBundle\Entity\Azienda;

class EsportaFN00Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaFN00
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaFN00($this->container);
    }

    public function testImportazioneOk() {
        $tc33 = new TC33FonteFinanziaria();
        $tc34 = new TC34DeliberaCIPE();
        $tc35 = new TC35Norma();
        $tc16 = new TC16LocalizzazioneGeografica();

        $this->setUpRepositories($tc33, $tc34, $tc35, $tc16);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc33',
            'COD_NORMA',
            'COD_DEL_CIPE',
            '08P08000', //tc16
            'confinanziamento',
            '1000,00',
            'S', //flg cancellato
        ];

        $res = $this->esporta->importa($input);
        $this->assertEquals($res->getCodLocaleProgetto(), 'COD_LOCALE_PROGETTO');
        $this->assertEquals($res->getCfCofinanz(), 'confinanziamento');
        $this->assertEquals($res->getImporto(), 1000);
        $this->assertEquals($res->getFlgCancellazione(), 'S');
        $this->assertEquals($res->getTc33FonteFinanziaria(), $tc33);
        $this->assertEquals($res->getTc35Norma(), $tc35);
        $this->assertEquals($res->getTc34DeliberaCipe(), $tc34);
    }

    protected function setUpRepositories($tc33, $tc34, $tc35, $tc16) {
        $tc33Repository = $this->createMock(TC33FonteFinanziariaRepository::class);
        $tc34Repository = $this->createMock(TC34DeliberaCIPERepository::class);
        $tc35Repository = $this->createMock(TC35NormaRepository::class);
        $tc16Repository = $this->createMock(TC16LocalizzazioneGeograficaRepository::class);

        $tc33Repository
        ->method('findOneBy')
        ->willReturn($tc33);

        $tc34Repository
        ->method('findOneBy')
        ->willReturn($tc34);

        $tc35Repository
        ->method('findOneBy')
        ->willReturn($tc35);

        $tc16Repository
        ->method('findOneBy')
        ->with($this->equalTo([
            'codice_regione' => '08',
            'codice_provincia' => 'P08',
            'codice_comune' => '000',
        ]))
        ->willReturn($tc16);

        $this->em
        ->method('getRepository')
        ->will(
            $this->returnValueMap([
                ['MonitoraggioBundle:TC33FonteFinanziaria',  $tc33Repository],
                ['MonitoraggioBundle:TC35Norma', $tc35Repository],
                ['MonitoraggioBundle:TC34DeliberaCIPE', $tc34Repository],
                ['MonitoraggioBundle:TC16LocalizzazioneGeografica', $tc16Repository],
            ])
    );
    }

    public function testImportazioneSenzaFonteFinanziaria() {
        $tc34 = new TC34DeliberaCIPE();
        $tc35 = new TC35Norma();
        $tc16 = new TC16LocalizzazioneGeografica();

        $this->setUpRepositories(null, $tc34, $tc35, $tc16);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc33',
            'COD_NORMA',
            'COD_DEL_CIPE',
            '08P08000', //tc16
            'confinanziamento',
            '1000,00',
            'S', //flg cancellato
        ];
        $this->expectException(EsportazioneException::class);
        $res = $this->esporta->importa($input);
    }

    public function testImportazioneSenzaDeliberaCipe() {
        $tc33 = new TC33FonteFinanziaria();
        $tc35 = new TC35Norma();
        $tc16 = new TC16LocalizzazioneGeografica();

        $this->setUpRepositories($tc33, null, $tc35, $tc16);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc33',
            'COD_NORMA',
            'COD_DEL_CIPE',
            '08P08000', //tc16
            'confinanziamento',
            '1000,00',
            'S', //flg cancellato
        ];
        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->importa($input);
    }

    public function testImportazioneSenzaNorma() {
        $tc33 = new TC33FonteFinanziaria();
        $tc34 = new TC34DeliberaCIPE();
        $tc35 = new TC35Norma();
        $tc16 = new TC16LocalizzazioneGeografica();

        $this->setUpRepositories($tc33, $tc34, null, $tc16);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc33',
            'COD_NORMA',
            'COD_DEL_CIPE',
            '08P08000', //tc16
            'confinanziamento',
            '1000,00',
            'S', //flg cancellato
        ];
        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->importa($input);
    }

    public function testImportazioneSenzaLocalizzazioneGeografica() {
        $tc33 = new TC33FonteFinanziaria();
        $tc34 = new TC34DeliberaCIPE();
        $tc35 = new TC35Norma();
        $tc16 = new TC16LocalizzazioneGeografica();

        $this->setUpRepositories($tc33, $tc34, $tc35, null);

        $input = [
            'COD_LOCALE_PROGETTO',
            'tc33',
            'COD_NORMA',
            'COD_DEL_CIPE',
            '08P08000', //tc16
            'confinanziamento',
            '1000,00',
            'S', //flg cancellato
        ];
        $this->expectException(EsportazioneException::class);

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

    public function testEsportazioneOk() {
        $tc16 = new TC16LocalizzazioneGeografica();
        $repo = $this->createMock(TC16LocalizzazioneGeograficaRepository::class);
        $repo->expects($this->once())->method('findOneBy')->willReturn($tc16);
        $this->em->method('getRepository')->willReturn($repo);

        $finanziamento = new Finanziamento($this->richiesta);
        $this->richiesta->addMonFinanziamenti($finanziamento);

        $tc16 = new TC16LocalizzazioneGeografica();
        $finanziamento->setTc16LocalizzazioneGeografica($tc16);

        $confinanziatore = new Azienda();
        $confinanziatore->setCodiceFiscale('setCodiceFiscale');
        $finanziamento->setCofinanziatore($confinanziatore);
        $finanziamento->setImporto('674382');

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);

        $this->assertNotNull($res);
        $this->assertInstanceOf(Collection::class, $res);
        /** @var FN00Finanziamento $first */
        $first = $res->first();
        $this->assertNotFalse($first);

        $this->assertSame($tc16, $first->getTc16LocalizzazioneGeografica());
        $this->assertEquals($confinanziatore->getCodiceFiscale(), $first->getCfCofinanz());
        $this->assertSame($this->tavola, $first->getEsportazioneStrutture());
        $this->assertEquals($finanziamento->getImporto(), $first->getImporto());
    }

    public function testEsportazioneNonPossibile() {
        $repo = $this->createMock(FN00FinanziamentoRepository::class);
        /** @var MockObject|FN00FinanziamentoRepository $repo */
        $this->esportazioneNonNecessaria($repo);
    }

    public function testEsportazioneSenzaLocalizzazione() {
        $finanziamento = new Finanziamento($this->richiesta);
        $this->richiesta->addMonFinanziamenti($finanziamento);

        $confinanziatore = new Azienda();
        $confinanziatore->setCodiceFiscale('setCodiceFiscale');
        $finanziamento->setCofinanziatore($confinanziatore);
        $finanziamento->setImporto('674382');

        $tc16 = new TC16LocalizzazioneGeografica();
        $repo = $this->createMock(TC16LocalizzazioneGeograficaRepository::class);
        $repo->expects($this->once())->method('findOneBy')->willReturn($tc16);
        $this->em->method('getRepository')->willReturn($repo);

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);

        $this->assertNotNull($res);
        $this->assertInstanceOf(Collection::class, $res);
        /** @var FN00Finanziamento $first */
        $first = $res->first();
        $this->assertNotFalse($first);

        $this->assertSame($tc16, $first->getTc16LocalizzazioneGeografica());
        $this->assertEquals($confinanziatore->getCodiceFiscale(), $first->getCfCofinanz());
        $this->assertSame($this->tavola, $first->getEsportazioneStrutture());
        $this->assertEquals($finanziamento->getImporto(), $first->getImporto());
    }

    public function testEsportazioneSenzaCofinaziatore() {
        $finanziamento = new Finanziamento($this->richiesta);
        $this->richiesta->addMonFinanziamenti($finanziamento);

        $repo = $this->createMock(TC16LocalizzazioneGeograficaRepository::class);
        $repo->expects($this->once())->method('findOneBy')->willReturn(null);
        $this->em->method('getRepository')->willReturn($repo);

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);

        $this->assertNotNull($res);
        $this->assertInstanceOf(Collection::class, $res);
        /** @var FN00Finanziamento $first */
        $first = $res->first();
        $this->assertNotFalse($first);

        $this->assertEquals('9999', $first->getCfCofinanz());
    }
}
