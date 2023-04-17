<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use Doctrine\Common\Persistence\ObjectRepository;
use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaFN04;
use MonitoraggioBundle\Entity\TC38CausaleDisimpegno;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use Doctrine\Common\Collections\Collection;
use MonitoraggioBundle\Entity\FN04Impegni;
use MonitoraggioBundle\Repository\FN04ImpegniRepository;
use MonitoraggioBundle\Exception\EsportazioneException;

class EsportaFN04Test extends EsportazioneRichiestaBase
{
    /**
     * @var EsportaFN04
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->esporta = new EsportaFN04($this->container);
    }

    public function testVerificaArray()
    {
        $tc38 = new TC38CausaleDisimpegno();

        $tc38Repository = $this->createMock(ObjectRepository::class);

        $tc38Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc38);

        $this->em->expects($this->any())
        ->method('getRepository')
        ->withConsecutive(
            array('MonitoraggioBundle:TC38CausaleDisimpegno')
        )
        ->willReturnOnConsecutiveCalls(
            $tc38Repository
        );

        $input = array(
            'COD_LOCALE_PROGETTO',
            'COD_IMPEGNO',
            'TIPOLOGIA_IMPEGNO',
            '01/02/2010',
            '1000,00', //tc16
            'CAUSALE_DISIMPEGNO',
            'NOTE_IMPEGNO',
            'S', //flg cancellato
        );

        $res = $this->esporta->importa($input);
        $this->assertEquals($res->getCodLocaleProgetto(), 'COD_LOCALE_PROGETTO');
        $this->assertEquals($res->getCodImpegno(), 'COD_IMPEGNO');
        $this->assertEquals($res->getTipologiaImpegno(), 'TIPOLOGIA_IMPEGNO');
        $this->assertEquals($res->getDataImpegno(), new \DateTime('2010-02-01'));
        $this->assertEquals($res->getImportoImpegno(), 1000);
        $this->assertEquals($res->getTc38CausaleDisimpegno(), $tc38);
        $this->assertEquals($res->getNoteImpegno(), 'NOTE_IMPEGNO');
        $this->assertEquals($res->getFlgCancellazione(), 'S');
    }

   
    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testVerificaArrayDisimpegnoNullo()
    {

        $tc38Repository = $this->createMock(ObjectRepository::class);

        $tc38Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn(NULL);

        $this->em->expects($this->any())
        ->method('getRepository')
        ->withConsecutive(
            array('MonitoraggioBundle:TC38CausaleDisimpegno')
        )
        ->willReturnOnConsecutiveCalls(
            $tc38Repository
        );

        $input = array(
            'COD_LOCALE_PROGETTO',
            'COD_IMPEGNO',
            'D',
            '01/02/2010',
            '1000,00', //tc16
            'CAUSALE_DISIMPEGNO',
            'NOTE_IMPEGNO',
            'S', //flg cancellato
        );

        $res = $this->esporta->importa($input);

    }

    public function testVerificaArrayConCausaleNullo()
    {

        $tc38Repository = $this->createMock(ObjectRepository::class);

        $tc38Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn(NULL);

        $this->em->expects($this->any())
        ->method('getRepository')
        ->withConsecutive(
            array('MonitoraggioBundle:TC38CausaleDisimpegno')
        )
        ->willReturnOnConsecutiveCalls(
            $tc38Repository
        );

        $input = array(
            'COD_LOCALE_PROGETTO',
            'COD_IMPEGNO',
            'I',
            '01/02/2010',
            '1000,00', //tc16
            'CAUSALE_DISIMPEGNO',
            'NOTE_IMPEGNO',
            'S', //flg cancellato
        );

        $res = $this->esporta->importa($input);
        $this->assertNull($res->getTc38CausaleDisimpegno());
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testInputNull()
    {
        $this->esporta->importa(null);
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testEmptyInputNull()
    {
        $this->esporta->importa(array());
    }

    public function testEsportazioneOk()
    {
        $impegno = new RichiestaImpegni($this->richiesta);
        $this->richiesta->addMonImpegni($impegno);

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);

        $this->assertNotNull($res);
        $this->assertInstanceOf(Collection::class, $res);
        $this->assertNotEmpty($res);

        /** @var FN04Impegni */
        $first = $res->first();
        $this->assertNotFalse($first);

        $this->assertInstanceOf(FN04Impegni::class, $first);
    }

    public function testEsportazioneNonNecessaria()
    {
        $repo = $this->createMock(FN04ImpegniRepository::class);
        $this->esportazioneNonNecessaria($repo);
    }
}
