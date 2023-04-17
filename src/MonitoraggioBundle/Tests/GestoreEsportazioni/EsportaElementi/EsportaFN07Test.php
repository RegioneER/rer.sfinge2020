<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaFN07;
use MonitoraggioBundle\Repository\FN07PagamentiAmmessiRepository;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Entity\RichiestaPagamento;
use AttuazioneControlloBundle\Entity\PagamentoAmmesso;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use MonitoraggioBundle\Form\Entity\TabelleContesto\TC12_4;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\TC39CausalePagamento;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Repository\TC4ProgrammaRepository;
use MonitoraggioBundle\Repository\TC36LivelloGerarchicoRepository;
use MonitoraggioBundle\Repository\TC39CausalePagamentoRepository;
use MonitoraggioBundle\Entity\FN07PagamentiAmmessi;


class EsportaFN07Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaFN07
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaFN07($this->container);
    }

    public function testImportazioneInputErrato()
    {
        $this->importazioneConInputNonValido();
    }
    
    public function testEsportazioneNonNecessaria(){
        $repo = $this->createMock(FN07PagamentiAmmessiRepository::class);
        $this->esportazioneNonNecessaria($repo);
    }

    public function testEsportazioneOk(){
        $pag = new RichiestaPagamento();
        $pag->setRichiesta($this->richiesta);
        $this->richiesta->addMonRichiestePagamento($pag);
        $programma = new RichiestaProgramma($this->richiesta);
        $programma->setTc4Programma(new TC4Programma());
        $liv = new RichiestaLivelloGerarchico($programma);
        $liv->setTc36LivelloGerarchico(new TC36LivelloGerarchico());
        $pagAmm = new PagamentoAmmesso($pag, $liv);
        $tc39 =  new TC39CausalePagamento();
        $pagAmm->setCausale($tc39);
        $pag->addPagamentiAmmessi($pagAmm);

        $res = $this->esporta->execute($this->richiesta, $this->tavola);

        $this->assertNotEmpty($res);

        $amm = $res->first();
    }

    public function testEsportazioneSenzaCausale()
    {
        $pag = new RichiestaPagamento();
        $pag->setRichiesta($this->richiesta);
        $this->richiesta->addMonRichiestePagamento($pag);
        $programma = new RichiestaProgramma($this->richiesta);
        $programma->setTc4Programma(new TC4Programma());
        $liv = new RichiestaLivelloGerarchico($programma);
        $liv->setTc36LivelloGerarchico(new TC36LivelloGerarchico());
        $pagAmm = new PagamentoAmmesso($pag, $liv);
        $pag->addPagamentiAmmessi($pagAmm);

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->execute($this->richiesta, $this->tavola);

    }

    public function testEsportazioneSenzaLivelloGerarchico()
    {
        $pag = new RichiestaPagamento();
        $pag->setRichiesta($this->richiesta);
        $this->richiesta->addMonRichiestePagamento($pag);
        $pagAmm = new PagamentoAmmesso($pag);
        $tc39 =  new TC39CausalePagamento();
        $pagAmm->setCausale($tc39);
        $pag->addPagamentiAmmessi($pagAmm);

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->execute($this->richiesta, $this->tavola);
    }

    /**
     * @dataProvider getInput
     */
    public function  testImportazioneOk($input): void {
        $tc4 = new TC4Programma();
        $tc36 = new TC36LivelloGerarchico();
        $tc39 = new TC39CausalePagamento();
        $this->setRepositories($tc4, $tc36, $tc39);
        
        $res = $this->esporta->importa($input);

        $this->assertNotNull($res);
        $this->assertInstanceOf(FN07PagamentiAmmessi::class, $res);
        $this->assertSame($tc4, $res->getTc4Programma());
        $this->assertSame($tc36, $res->getTc36LivelloGerarchico());
        $this->assertSame($tc39, $res->getTc39CausalePagamento());
        $this->assertEquals(new \DateTime('2010-01-01'), $res->getDataPagamento());
        $this->assertEquals(new \DateTime('2002-02-02'), $res->getDataPagAmm());
        $this->assertEquals(998, $res->getImportoPagAmm());
    }

    public function getInput(): array{
        return [[[
            'cod_loc_progetto',
            'cod_pag',
            'T',
            '01/01/2010',
            'programma',
            'liv',
            '02/02/2002',
            'T',
            'caus',
            '998',
            'note',
            null
        ]]];
    }

    protected function setRepositories($tc4, $tc36, $tc39){
        $tc4Repo = $this->createMockFindOneBy(TC4ProgrammaRepository::class, $tc4);
        $tc36Repo = $this->createMockFindOneBy(TC36LivelloGerarchicoRepository::class, $tc36);
        $tc39Repo = $this->createMockFindOneBy(TC39CausalePagamentoRepository::class, $tc39);
        $this->em->method('getRepository')->will(
            $this->returnValueMap([
                ['MonitoraggioBundle:TC4Programma', $tc4Repo],
                ['MonitoraggioBundle:TC36LivelloGerarchico', $tc36Repo],
                ['MonitoraggioBundle:TC39CausalePagamento', $tc39Repo],
            ])
        );
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneSenzaProgramma($input){
        $tc36 = new TC36LivelloGerarchico();
        $tc39 = new TC39CausalePagamento();
        $this->setRepositories(null, $tc36, $tc39);
        
        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Programma non valido');

        $res = $this->esporta->importa($input);
    }


    /**
     * @dataProvider getInput
     */
    public function testImportazioneSenzaCausale($input){
        $tc4 = new TC4Programma();
        $tc36 = new TC36LivelloGerarchico();
        $this->setRepositories($tc4, $tc36, null);
        
        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Causale pagamento non valido');

        $res = $this->esporta->importa($input);
    }

    /**
     * @dataProvider getInput
     */
    public function  testImportazioneSenzaLivelloGerarchico($input): void {
        $tc4 = new TC4Programma();
        $tc39 = new TC39CausalePagamento();
        $this->setRepositories($tc4, null, $tc39);

        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Livello gerarchico non valido');
        
        $res = $this->esporta->importa($input);
    }
}