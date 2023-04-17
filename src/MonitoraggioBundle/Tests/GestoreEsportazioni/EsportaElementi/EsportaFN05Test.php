<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaFN05;
use MonitoraggioBundle\Repository\FN05ImpegniAmmessiRepository;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use MonitoraggioBundle\Entity\FN05ImpegniAmmessi;
use MonitoraggioBundle\Repository\TC4ProgrammaRepository;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use MonitoraggioBundle\Entity\TC38CausaleDisimpegno;
use MonitoraggioBundle\Exception\EsportazioneException;

class EsportaFN05Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaFN05
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaFN05($this->container);
    }

    public function testEsportazioneNonNecessaria() {
        $repo = $this->createMock(FN05ImpegniAmmessiRepository::class);
        $this->esportazioneNonNecessaria($repo);
    }

    public function testEsportazioneConSuccesso() {
        $programma = new RichiestaProgramma($this->richiesta);
        $this->richiesta->addMonProgrammi($programma);
        $liv = new RichiestaLivelloGerarchico($programma);
        $programma->addMonLivelliGerarchici($liv);
        $impegno = new RichiestaImpegni($this->richiesta);
        $this->richiesta->addMonImpegni($impegno);
        $impAmm = new ImpegniAmmessi($impegno, $liv);
        $impegno->addMonImpegniAmmessi($impAmm);

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);
        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        $first = $res->first();

        $this->assertNotFalse($first);
        $this->assertInstanceOf(FN05ImpegniAmmessi::class, $first);
    }

    public function testImportazioneErroreInput()
    {
        $this->expectException(EsportazioneException::class);
        $res = $this->esporta->importa([]);
    }

    /**
     * @dataProvider getDisimpegno
     */
    public function testImportaConSuccesso($input) {
        $tc4 = new TC4Programma();
        $tc36 = new TC36LivelloGerarchico();
        $tc38 = new TC38CausaleDisimpegno();
        $this->setUpRepository($tc4, $tc36, $tc38);

        

        $res = $this->esporta->importa($input);
        $this->assertNotNull($res);
        $this->assertInstanceOf(FN05ImpegniAmmessi::class, $res);
    }

    public function getDisimpegno():array{
        return [
            [[
                'cod_locale',
                'impegno',
                'tip_impegno',
                '01/01/2010',
                'programma',
                'liv',
                '01/01/2019',
                'D',
                'causale',
                '999',
                'note',
                null
            ]]
            ];
    }

    protected function setUpRepository($tc4, $tc36, $tc38) {
        $repoTc4 = $this->createMock(TC4ProgrammaRepository::class);
        $repoTc4->method('findOneBy')->willReturn($tc4);

        $repoTc36 = $this->createMock(TC4ProgrammaRepository::class);
        $repoTc36->method('findOneBy')->willReturn($tc36);

        $repoTc38 = $this->createMock(TC4ProgrammaRepository::class);
        $repoTc38->method('findOneBy')->willReturn($tc38);

        $this->em->method('getRepository')->will(
            $this->returnValueMap([
                ['MonitoraggioBundle:TC4Programma',  $repoTc4],
                ['MonitoraggioBundle:TC36LivelloGerarchico',  $repoTc36],
                ['MonitoraggioBundle:TC38CausaleDisimpegno',  $repoTc38],
            ])
        );
    }

    /**
     * @dataProvider getDisimpegno
     */
    public function testImportazioneSenzaProgramma($input){
        $tc4 = new TC4Programma();
        $tc36 = new TC36LivelloGerarchico();
        $tc38 = new TC38CausaleDisimpegno();
        $this->setUpRepository(null, $tc36, $tc38);

        $this->expectException(EsportazioneException::class);
        $res = $this->esporta->importa($input);
    }

    /**
     * @dataProvider getDisimpegno
     */
    public function testImportazioneSenzaLivelloGerarchico($input){
        $tc4 = new TC4Programma();
        $tc36 = new TC36LivelloGerarchico();
        $tc38 = new TC38CausaleDisimpegno();
        $this->setUpRepository($tc4, null, $tc38);

        $this->expectException(EsportazioneException::class);
        $res = $this->esporta->importa($input);
    }

    /**
     * @dataProvider getDisimpegno
     */
    public function testImportazioneSenzaCausale($input){
        $tc4 = new TC4Programma();
        $tc36 = new TC36LivelloGerarchico();
        $this->setUpRepository($tc4, $tc36, null);

        $this->expectException(EsportazioneException::class);
        $res = $this->esporta->importa($input);
    }
}
