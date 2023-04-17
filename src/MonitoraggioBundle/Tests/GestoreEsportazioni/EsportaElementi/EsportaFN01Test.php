<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaFN01;
use MonitoraggioBundle\Repository\TC4ProgrammaRepository;
use MonitoraggioBundle\Repository\TC36LivelloGerarchicoRepository;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use MonitoraggioBundle\Entity\FN01CostoAmmesso;
use MonitoraggioBundle\Exception\EsportazioneException;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use MonitoraggioBundle\Repository\FN01CostoAmmessoRepository;

class EsportaFN01Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaFN01
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaFN01($this->container);
    }

    public function testImportaOk()
    {
        $input = [
            'cod_locale',
            'programma',
            'liv_gerarchico',
            999,
            NULL
        ];
        $tc4 = new TC4Programma();
        $tc36 = new TC36LivelloGerarchico();
        $this->setUpRepositories($tc4, $tc36);

        $res = $this->esporta->importa($input);

        $this->assertNotNull($res);
        $this->assertInstanceOf(FN01CostoAmmesso::class, $res);
        $this->assertNull($res->getFlgCancellazione());
        $this->assertEquals('cod_locale', $res->getCodLocaleProgetto());
        $this->assertEquals('999.00',$res->getImportoAmmesso());
    }

    protected function setUpRepositories(?TC4Programma $tc4, ?TC36LivelloGerarchico $tc36):void {
        $repoTC4 = $this->createMock(TC4ProgrammaRepository::class);
        $repoTC4->method('findOneBy')->willReturn($tc4);
        $repoTC36 = $this->createMock(TC36LivelloGerarchicoRepository::class);
        $repoTC36->method('findOneBy')->willReturn($tc36);
        $this->em->method('getRepository')->will(
            $this->returnValueMap([
                ['MonitoraggioBundle:TC4Programma', $repoTC4],
                ['MonitoraggioBundle:TC36LivelloGerarchico',$repoTC36],
            ])
        );
    }

    public function testImportazioneDatiNonPresenti(){
        $input =[];
        $this->expectException(EsportazioneException::class);
        $res = $this->esporta->importa($input);
    }

    public function testImportazioneSenzaProgramma()
    {
        $input = [
            'cod_locale',
            'programma',
            'liv_gerarchico',
            999,
            NULL
        ];

        $tc4 = new TC4Programma();
        $tc36 = new TC36LivelloGerarchico();
        $this->setUpRepositories(null, $tc36);
        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->importa($input);

    }

    public function testImportazioneSenzaLivGerarchico()
    {
        $input = [
            'cod_locale',
            'programma',
            'liv_gerarchico',
            999,
            NULL
        ];

        $tc4 = new TC4Programma();
        $tc36 = new TC36LivelloGerarchico();
        $this->setUpRepositories($tc4, null);
        $this->expectException(EsportazioneException::class);
        
        $res = $this->esporta->importa($input);

    }

    public function testEsportazioneOk()
    {
        $programma = new RichiestaProgramma($this->richiesta);
        $tc4 = new TC4Programma();
        $programma->setTc4Programma($tc4);
        $this->richiesta->addMonProgrammi($programma);
        $liv = new RichiestaLivelloGerarchico($programma);
        $tc36 = new TC36LivelloGerarchico();
        $liv->setImportoCostoAmmesso(999);
        $liv->setTc36LivelloGerarchico($tc36);
        $programma->addMonLivelliGerarchici($liv);

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);
        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        /** @var FN01CostoAmmesso $first */
        $first = $res->first();
        $this->assertNotFalse($first);

        $this->assertEquals($this->richiesta->getProtocollo(), $first->getCodLocaleProgetto());
        $this->assertSame($tc4, $first->getTc4Programma());
        $this->assertSame($tc36, $first->getTc36LivelloGerarchico());
    }

    public function testEsportazioneNonNecessaria(){
        $repo = $this->createMock(FN01CostoAmmessoRepository::class);
        $this->esportazioneNonNecessaria($repo);
    }

    public function testEsportazioneOkProgrammaCancellato()
    {
        $programma = new RichiestaProgramma($this->richiesta);
        $tc4 = new TC4Programma();
        $programma->setTc4Programma($tc4);
        $programma->setDataCancellazione(new \DateTime());
        $this->richiesta->addMonProgrammi($programma);
        $liv = new RichiestaLivelloGerarchico($programma);
        $tc36 = new TC36LivelloGerarchico();
        $liv->setImportoCostoAmmesso(999);
        $liv->setTc36LivelloGerarchico($tc36);
        $programma->addMonLivelliGerarchici($liv);

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);
        $this->assertNotNull($res);
        $this->assertEmpty($res);
    }
}