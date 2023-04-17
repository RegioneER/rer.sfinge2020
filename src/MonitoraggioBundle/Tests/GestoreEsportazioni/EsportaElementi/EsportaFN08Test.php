<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaFN08;
use MonitoraggioBundle\Repository\FN08PercettoriRepository;
use Doctrine\Common\Collections\ArrayCollection;
use AttuazioneControlloBundle\Entity\RichiestaPagamento;
use AttuazioneControlloBundle\Entity\PagamentiPercettoriGiustificativo;
use MonitoraggioBundle\Entity\TC40TipoPercettore;
use MonitoraggioBundle\Repository\TC40TipoPercettoreRepository;
use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use MonitoraggioBundle\Entity\FN08Percettori;
use AttuazioneControlloBundle\Entity\PagamentiPercettoriSoggetto;
use SoggettoBundle\Entity\Azienda;
use SoggettoBundle\Entity\FormaGiuridica;
use MonitoraggioBundle\Exception\EsportazioneException;
use AttuazioneControlloBundle\Entity\PagamentiPercettori;

class EsportaFN08Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaFN08
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaFN08($this->container);
    }

    public function testEsportazioneNonNecessaria() {
        $repo = $this->createMock(FN08PercettoriRepository::class);
        $this->esportazioneNonNecessaria($repo);
    }

    public function testImportazioneErroreInput() {
        $this->importazioneConInputNonValido();
    }

    public function testEsportazioneConSuccessoPrivato(): void {
        $pag = new RichiestaPagamento();
        $pag->setRichiesta($this->richiesta);
        $this->richiesta->addMonRichiestePagamento($pag);
        $perc = new PagamentiPercettoriGiustificativo();
        $tc40 = new TC40TipoPercettore();
        $perc->setTipoPercettore($tc40);
        $pag->addPercettori($perc);
        $giustificativo = new GiustificativoPagamento();
        $giustificativo->setCodiceFiscaleFornitore('COD_FISCALE');
        $perc->setGiustificativoPagamento($giustificativo);
        $res = $this->esporta->execute($this->richiesta, $this->tavola);
        $this->assertNotNull($res);
        $this->assertInstanceOf(ArrayCollection::class, $res);
        /** @var FN08Percettori $first */
        $first = $res->first();
        $this->assertEquals('COD_FISCALE', $first->getCodiceFiscale());
        $this->assertEquals('N', $first->getFlagSoggettoPubblico());
    }

    public function testEsportazionePrivatoGiustificativoAssente(): void {
        $pag = new RichiestaPagamento();
        $pag->setRichiesta($this->richiesta);
        $this->richiesta->addMonRichiestePagamento($pag);
        $perc = new PagamentiPercettoriGiustificativo();
        $tc40 = new TC40TipoPercettore();
        $perc->setTipoPercettore($tc40);
        $pag->addPercettori($perc);
        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Giustificativo assente');
        $res = $this->esporta->execute($this->richiesta, $this->tavola);
    }

    public function testEsportazioneConSuccessoPubblico(): void {
        $pag = new RichiestaPagamento();
        $pag->setRichiesta($this->richiesta);
        $this->richiesta->addMonRichiestePagamento($pag);
        $perc = new PagamentiPercettoriSoggetto();
        $tc40 = new TC40TipoPercettore();
        $perc->setTipoPercettore($tc40);
        $pag->addPercettori($perc);
        $soggetto = new Azienda();
        $soggetto->setCodiceFiscale('COD_FISCALE');
        $formaGiuridica = new FormaGiuridica();
        $formaGiuridica->setSoggettoPubblico(true);
        $soggetto->setFormaGiuridica($formaGiuridica);
        $perc->setSoggetto($soggetto);
        $res = $this->esporta->execute($this->richiesta, $this->tavola);
        $this->assertNotNull($res);
        $this->assertInstanceOf(ArrayCollection::class, $res);
        /** @var FN08Percettori $first */
        $first = $res->first();
        $this->assertEquals('COD_FISCALE', $first->getCodiceFiscale());
        $this->assertEquals('S', $first->getFlagSoggettoPubblico());
    }

    public function testEsportazionePubblicoSoggettoAssente(): void {
        $pag = new RichiestaPagamento();
        $pag->setRichiesta($this->richiesta);
        $this->richiesta->addMonRichiestePagamento($pag);
        $perc = new PagamentiPercettoriSoggetto();
        $tc40 = new TC40TipoPercettore();
        $perc->setTipoPercettore($tc40);
        $pag->addPercettori($perc);

        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage("Soggetto assente");

        $res = $this->esporta->execute($this->richiesta, $this->tavola);
    }

    public function testEsportazionePubblicoFormaGiuridicaAssente(): void {
        $pag = new RichiestaPagamento();
        $pag->setRichiesta($this->richiesta);
        $this->richiesta->addMonRichiestePagamento($pag);
        $perc = new PagamentiPercettoriSoggetto();
        $tc40 = new TC40TipoPercettore();
        $perc->setTipoPercettore($tc40);
        $pag->addPercettori($perc);
        $soggetto = new Azienda();
        $soggetto->setCodiceFiscale('COD_FISCALE');
        $perc->setSoggetto($soggetto);

        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage("Forma giuridica assente");

        $res = $this->esporta->execute($this->richiesta, $this->tavola);
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneConSuccesso($input) {
        $tc40 = new TC40TipoPercettore();
        $repo = $this->createMockFindOneBy(TC40TipoPercettoreRepository::class, $tc40);
        $this->em->method('getRepository')->willReturn($repo);

        $res = $this->esporta->importa($input);

        $this->assertInstanceOf(FN08Percettori::class, $res);
        $this->assertSame($tc40, $res->getTc40TipoPercettore());
        $this->assertNull($res->getFlgCancellazione());
    }

    public function getInput(): array {
        return [[[
            'cod_progetto',
            'cod_pagamento',
            'P',
            '01/01/2001',
            'cod_fiscale',
            'S',
            'tipo',
            '124',
            '',
        ]]];
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneSenzaTipoPercettore($input): void {
        $repo = $this->createMockFindOneBy(TC40TipoPercettoreRepository::class, null);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Tipo percettore non valido');

        $res = $this->esporta->importa($input);
    }

    public function testEsportazioneConTipoNonValido():void{
        $pag = new RichiestaPagamento();
        $pag->setRichiesta($this->richiesta);
        $this->richiesta->addMonRichiestePagamento($pag);
        $perc = new PagamentiPercettori();
        $tc40 = new TC40TipoPercettore();
        $perc->setTipoPercettore($tc40);
        $pag->addPercettori($perc);

        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage("Tipologia percettore non valida");

        $res = $this->esporta->execute($this->richiesta, $this->tavola);
    }
}
