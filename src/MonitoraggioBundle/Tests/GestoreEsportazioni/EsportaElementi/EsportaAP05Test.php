<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use Doctrine\Common\Persistence\ObjectRepository;
use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaAP05;
use MonitoraggioBundle\Entity\TC5TipoOperazione;
use MonitoraggioBundle\Entity\TC6TipoAiuto;
use MonitoraggioBundle\Entity\TC48TipoProceduraAttivazioneOriginaria;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\TC11TipoClassificazione;
use MonitoraggioBundle\Entity\TC12Classificazione;
use MonitoraggioBundle\Entity\TC15StrumentoAttuativo;
use MonitoraggioBundle\Repository\AP05StrumentoAttuativoRepository;
use MonitoraggioBundle\Exception\EsportazioneException;
use AttuazioneControlloBundle\Entity\StrumentoAttuativo;
use Doctrine\Common\Collections\Collection;
use MonitoraggioBundle\Entity\AP05StrumentoAttuativo;

class EsportaAP05Test extends EsportazioneRichiestaBase
{
    
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->esporta = new EsportaAP05($this->container);
    }

    public function testVerificaArray()
    {
        $tc15 = new TC15StrumentoAttuativo();
        $this->setUpRepositories($tc15);
        
        $input = array(
            'COD_LOCALE_PROGETTO',
            'COD_CLASSIFICAZIONE', //tc15
            'S', //FLG_CANCELLAZIONE
        );
        $res = $this->esporta->importa($input);
        $this->assertEquals($res->getTc15StrumentoAttuativo(), $tc15);
        $this->assertEquals($res->getCodLocaleProgetto(), 'COD_LOCALE_PROGETTO');
        $this->assertEquals($res->getFlgCancellazione(), 'S');
    }

    protected function setUpRepositories($tc15){
        $tc15Repository = $this->createMock(ObjectRepository::class);

        $tc15Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc15);

        $this->em->expects($this->any())
        ->method('getRepository')
        ->withConsecutive(
            array('MonitoraggioBundle:TC15StrumentoAttuativo')
        )
        ->willReturnOnConsecutiveCalls(
            $tc15Repository
        );
    }

    public function testSenzaStrumentoAttuativo()
    {
        $this->setUpRepositories(null);
        
        $input = array(
            'COD_LOCALE_PROGETTO',
            'COD_CLASSIFICAZIONE', //tc15
            'S', //FLG_CANCELLAZIONE
        );

        $this->expectExceptionMessage('Strumento attuativo non valido');

        $res = $this->esporta->importa($input);
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

    public function testNonEsportabile()
    {
        $repo = $this->createMock(AP05StrumentoAttuativoRepository::class);
        $this->esportazioneNonNecessaria($repo);
    }

    public function testNessunStrumentoAttuativo()
    {
        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Nessuno strumento attuativo per il progetto '. self::GetProtocollo());

        $this->esporta->execute($this->richiesta, $this->tavola, false);
    }

    public function testEsportazioneOk(){
        $tc15 = new TC15StrumentoAttuativo();
        $strumento = new StrumentoAttuativo($this->richiesta);
        $strumento->setTc15StrumentoAttuativo($tc15);

        $this->richiesta->addMonStrumentiAttuativi($strumento);
        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);

        $this->assertNotNull($res);
        $this->assertInstanceOf(Collection::class, $res);
        $this->assertNotEmpty($res);
        /** @var AP05StrumentoAttuativo $first  */
        $first = $res->first();
        $this->assertSame($tc15, $first->getTc15StrumentoAttuativo());
        $this->assertSame($this->tavola, $first->getMonitoraggioConfigurazioneEsportazioniTavola());
        $this->assertSame($this->richiesta->getProtocollo(), $first->getCodLocaleProgetto());
    }

}
