<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaFN02;
use MonitoraggioBundle\Entity\FN02QuadroEconomico;
use MonitoraggioBundle\Entity\TC37VoceSpesa;
use MonitoraggioBundle\Repository\TC37VoceSpesaRepository;
use MonitoraggioBundle\Exception\EsportazioneException;
use AttuazioneControlloBundle\Service\GestoreRichiesteATCBase;
use AttuazioneControlloBundle\Service\GestoreRichiesteATCService;
use Doctrine\Common\Collections\Collection;
use MonitoraggioBundle\Repository\FN02QuadroEconomicoRepository;


class EsportaFN02Test extends EsportazioneRichiestaBase {
   
    /**
     * @var EsportaFN02
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaFN02($this->container);
    }

    public function testImportazioneOk(){
        $input = [
            'cod_locale_progetto',
            'voce_spesa',
            '9887',
            ''
        ];
        $tc37 = new TC37VoceSpesa();
        $repo = $this->createMock(TC37VoceSpesaRepository::class);
        $repo->method('findOneBy')->willreturn($tc37);
        $this->em->method('getRepository')->willReturn($repo);
        $res = $this->esporta->importa($input);
        $this->assertNotNull($res);
        $this->assertInstanceOf(FN02QuadroEconomico::class, $res);
        $this->assertEquals(9887, $res->getImporto());
        $this->assertEquals('cod_locale_progetto', $res->getCodLocaleProgetto());
        $this->assertEquals($tc37, $res->getTc37VoceSpesa());
    }

    public function testImportazioneInputNotValid(){
        $this->expectException(EsportazioneException::class);
        $res = $this->esporta->importa([]);
    }

    public function testImportazioneVoceSpesaNonTrovata(){
        $input = [
            'cod_locale_progetto',
            'voce_spesa',
            '9887',
            ''
        ];
        $repo = $this->createMock(TC37VoceSpesaRepository::class);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(EsportazioneException::class);
        $res = $this->esporta->importa($input);
    }

    public function testEsportazioneOkNoVociCancellate(){
        $gestoreATC = $this->createMock(GestoreRichiesteATCBase::class);
        $tcVoce = new TC37VoceSpesa();
        $quadro= [
            ['voce' => $tcVoce, 'importo'=> 999]
        ];
        $gestoreATC->method('getQuadroEconomico')->willReturn($quadro);
        $atcService = $this->createMock(GestoreRichiesteATCService::class);
        $atcService->method('getGestore')->willReturn($gestoreATC);
        $this->container->set('gestore_richieste_atc', $atcService);

        $repo = $this->createMock(FN02QuadroEconomicoRepository::class);
        $repo->method('findVociNonPresenti')->willReturn([]);
        $this->em->method('getRepository')->willReturn($repo);

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);

        $this->assertNotNull($res);
        $this->assertInstanceOf(Collection::class, $res);
        $this->assertNotEmpty($res);
        /** @var FN02QuadroEconomico $first */
        $first = $res->first();
        $this->assertNotFalse($first);
        $this->assertEquals(999, $first->getImporto());
        $this->assertSame($tcVoce, $first->getTc37VoceSpesa());
        $this->assertNull($first->getFlgCancellazione());
    }
    
    public function testEsportazioneOkSoloVociCancellate(){
        $gestoreATC = $this->createMock(GestoreRichiesteATCBase::class);
        $quadro= [];
        $gestoreATC->method('getQuadroEconomico')->willReturn($quadro);
        $atcService = $this->createMock(GestoreRichiesteATCService::class);
        $atcService->method('getGestore')->willReturn($gestoreATC);
        $tcVoce = new TC37VoceSpesa();
        $vocePresente = new FN02QuadroEconomico();
        $vocePresente->setCodLocaleProgetto('voce_cancellata')
        ->setTc37VoceSpesa($tcVoce)
        ->setImporto(999);
        $this->container->set('gestore_richieste_atc', $atcService);

        $repo = $this->createMock(FN02QuadroEconomicoRepository::class);
        $repo->method('findVociNonPresenti')->willReturn([
            $vocePresente
        ]);
        $this->em->method('getRepository')->willReturn($repo);

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);

        $this->assertNotNull($res);
        $this->assertInstanceOf(Collection::class, $res);
        $this->assertNotEmpty($res);
        /** @var FN02QuadroEconomico $first */
        $first = $res->first();
        $this->assertNotFalse($first);
        $this->assertEquals(999, $first->getImporto());
        $this->assertSame($tcVoce, $first->getTc37VoceSpesa());
        $this->assertEquals('S', $first->getFlgCancellazione());
    }

    public function testEsportazioneNonNecessaria(){
        $repo = $this->createMock(FN02QuadroEconomicoRepository::class);
        $this->esportazioneNonNecessaria($repo);
    }
}