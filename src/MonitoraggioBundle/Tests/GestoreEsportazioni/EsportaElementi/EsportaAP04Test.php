<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use Doctrine\Common\Persistence\ObjectRepository;
use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaAP04;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\TC14SpecificaStato;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Repository\AP04ProgrammaRepository;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use Doctrine\Common\Collections\Collection;

class EsportaAP04Test extends EsportazioneRichiestaBase {
    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaAP04($this->container);
    }

    public function testVerificaDisattivo(): void {
        $tc4 = new TC4Programma();
        $tc14 = new TC14SpecificaStato();

        $this->setupRepositories($tc4, $tc14);

        $input = [
            'COD_LOCALE_PROGETTO',
            '2', //tc4
            'STATO',
            'SPECIFICA_STATO', //tc14
        ];
        $res = $this->esporta->importa($input);
        $this->assertEquals($res->getTc4Programma(), $tc4);
        $this->assertEquals($res->getTc14SpecificaStato(), $tc14);
        $this->assertEquals($res->getStato(), 'STATO');
        $this->assertEquals($res->getCodLocaleProgetto(), 'COD_LOCALE_PROGETTO');
    }

    protected function setupRepositories($tc4, $tc14) {
        $tc4Repository = $this->createMock(ObjectRepository::class);
        $tc14Repository = $this->createMock(ObjectRepository::class);

        $tc4Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc4);

        $tc14Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc14);

        $this->em->expects($this->any())
        ->method('getRepository')
        ->withConsecutive(
            ['MonitoraggioBundle:TC4Programma'],
            ['MonitoraggioBundle:TC14SpecificaStato']
        )
        ->willReturnOnConsecutiveCalls(
            $tc4Repository,
            $tc14Repository
        );
    }

    public function testImportazioneSenzaProgramma(): void {
        $tc14 = new TC14SpecificaStato();

        $this->setupRepositories(null, $tc14);

        $input = [
                'COD_LOCALE_PROGETTO',
                '2', //tc4
                'STATO',
                'SPECIFICA_STATO', //tc14
            ];

        $this->expectExceptionMessage('Programma non valido');

        $res = $this->esporta->importa($input);
    }

    public function testImportazioneSenzaSpecificaStato(): void {
        $tc4 = new TC4Programma();

        $this->setupRepositories($tc4, null);

        $input = [
                'COD_LOCALE_PROGETTO',
                '2', //tc4
                '2',
                'SPECIFICA_STATO', //tc14
            ];

        $this->expectExceptionMessage('Specifica stato non valido');

        $res = $this->esporta->importa($input);
    }

    public function testVerificaAttivo(): void {
        $tc4 = new TC4Programma();

        $tc4Repository = $this->createMock(ObjectRepository::class);
        $tc14Repository = $this->createMock(ObjectRepository::class);

        $tc4Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc4);

        $tc14Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn(null);

        $this->em->expects($this->any())
        ->method('getRepository')
        ->withConsecutive(
            ['MonitoraggioBundle:TC4Programma'],
            ['MonitoraggioBundle:TC14SpecificaStato']
        )
        ->willReturnOnConsecutiveCalls(
            $tc4Repository,
            $tc14Repository
        );

        $input = [
            'COD_LOCALE_PROGETTO',
            '1', //tc4
            'STATO',
            'SPECIFICA_STATO', //tc14
        ];
        $res = $this->esporta->importa($input);
        $this->assertEquals($res->getTc4Programma(), $tc4);
        $this->assertNull($res->getTc14SpecificaStato());
        $this->assertEquals($res->getStato(), 'STATO');
        $this->assertEquals($res->getCodLocaleProgetto(), 'COD_LOCALE_PROGETTO');
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testInputNull(): void {
        $this->esporta->importa(null);
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testEmptyInputNull(): void {
        $this->esporta->importa([]);
    }

    public function testEsportazioneNonNecessaria(): void {
        $repo = $this->createMock(AP04ProgrammaRepository::class);
        $this->esportazioneNonNecessaria($repo);
    }

    public function testEsportazioneNoProgramma(): void {
        $this->expectException(EsportazioneException::class);

        $this->esporta->execute($this->richiesta, $this->tavola, false);
    }

    public function testEsportazioneOk(): void {
        $tc4 = new TC4Programma();
        $programma = new RichiestaProgramma($this->richiesta);
        $programma->setTc4Programma($tc4);
        $this->richiesta->addMonProgrammi($programma);

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);
        $this->assertInstanceOf(Collection::class, $res);
        $this->assertEquals(1, $res->count());
        $element = $res->first();
        $this->assertNotFalse($element);
        $this->assertSame($tc4, $element->getTc4Programma());
        $this->assertEquals(self::GetProtocollo(), $element->getCodLocaleProgetto());
    }
}
