<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaFN06;
use MonitoraggioBundle\Repository\FN06PagamentiRepository;
use MonitoraggioBundle\Entity\FN06Pagamenti;
use MonitoraggioBundle\Repository\TC39CausalePagamentoRepository;
use MonitoraggioBundle\Entity\TC39CausalePagamento;
use MonitoraggioBundle\Exception\EsportazioneException;
use AttuazioneControlloBundle\Entity\RichiestaPagamento;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;

class EsportaFN06Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaFN06
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaFN06($this->container);
    }

    public function testEsportazioneNonNecessaria() {
        $repo = $this->createMock(FN06PagamentiRepository::class);
        $this->esportazioneNonNecessaria($repo);
    }

    public function testImportazioneInputNonValido()
    {
       $this->importazioneConInputNonValido();
    }

    /**
     * @dataProvider getInputImportazione
     */
    public function testImportazioneConSuccesso($input){
        $tc39 = new TC39CausalePagamento();
        $tc39Repository = $this->createMockFindOneBy(TC39CausalePagamentoRepository::class, $tc39);
        $this->em->method('getRepository')->willreturn($tc39Repository);

        $res = $this->esporta->importa($input);

        $this->assertNotNull($res);
        $this->assertInstanceOf(FN06Pagamenti::class, $res);
        $this->assertSame($tc39, $res->getTc39CausalePagamento());
        $this->assertEquals('P', $res->getTipologiaPag());
        $this->assertEquals(new \DateTime('2010-01-01'), $res->getDataPagamento());
        $this->assertEquals(999.12, $res->getImportoPag());
    }

    public function getInputImportazione()
    {
        return [[[
            'cod_progetto',
            'cod_pagamento',
            'P',
            '01/01/2010',
            '999.12',
            'causale',
            'note',
            null
        ]]];
    }

    /**
     * @dataProvider getInputImportazione
     */
    public function testImportazioneCausaleErrata($input)
    {
        $tc39Repository = $this->createMockFindOneBy(TC39CausalePagamentoRepository::class, null);
        $this->em->method('getRepository')->willreturn($tc39Repository);

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->importa($input);
    }

    public function testImportazioneDateNonValide(){
        $tc39 = new TC39CausalePagamento();
        $tc39Repository = $this->createMockFindOneBy(TC39CausalePagamentoRepository::class, $tc39);
        $this->em->method('getRepository')->willreturn($tc39Repository);

        $input = [
            'cod_progetto',
            'cod_pagamento',
            'P',
            'data_non_valida',
            '999.12',
            'causale',
            'note',
            null
        ];

        $res = $this->esporta->importa($input);

        $this->assertNull($res->getDataPagamento());
    }

    public function testEsportazioneConSuccesso()
    {
        $atc = new AttuazioneControlloRichiesta();
        $atc->setRichiesta($this->richiesta);

        $pagamento = new Pagamento();
        $pagamento->setAttuazioneControlloRichiesta($atc);
        $modalita = new ModalitaPagamento();
        $pagamento->setModalitaPagamento($modalita);


        $pag = new RichiestaPagamento($pagamento);
        $this->richiesta->addMonRichiestePagamento($pag);
        $pag->setImporto(1000);

        $res = $this->esporta->execute($this->richiesta, $this->tavola);

        $this->assertNotNull($res);
        $this->assertNotEmpty($res);

        /** @var FN06Pagamenti $first */
        $first = $res->first();
        $this->assertEquals(1000, $first->getImportoPag());
        $this->assertNull($first->getFlgCancellazione());
    }
}
