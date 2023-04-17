<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use Doctrine\Common\Persistence\ObjectRepository;
use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaAP00;
use MonitoraggioBundle\Entity\TC5TipoOperazione;
use MonitoraggioBundle\Entity\TC6TipoAiuto;
use MonitoraggioBundle\Entity\TC48TipoProceduraAttivazioneOriginaria;
use MonitoraggioBundle\Repository\AP00AnagraficaProgettiRepository;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;
use MonitoraggioBundle\Entity\MonitoraggioEsportazione;
use ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use CipeBundle\Entity\Classificazioni\CupSettore;
use CipeBundle\Entity\Classificazioni\CupSottosettore;
use CipeBundle\Entity\Classificazioni\CupCategoria;
use CipeBundle\Entity\Classificazioni\CupNatura;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use MonitoraggioBundle\Exception\EsportazioneException;
use CipeBundle\Entity\Classificazioni\CupTipologia;

class EsportaAP00Test extends EsportazioneBase {
   

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaAP00($this->container);
    }

    public function testEsportaCasoFelice() {
        $ap00Repository = $this->createMock(AP00AnagraficaProgettiRepository::class);

        $ap00Repository->expects($spy = $this->once())
        ->method('isEsportabile')
        ->with( $this->isInstanceOf(MonitoraggioConfigurazioneEsportazioneRichiesta::class))
        ->willReturn(true);

        $this->em->expects($this->once())
        ->method('getRepository')
        ->with('MonitoraggioBundle:AP00AnagraficaProgetti')
        ->willReturn($ap00Repository);

        $input = $this->creaProgetto();
        $tavola = $this->createTavolaRichiesta($input);

        $res = $this->esporta->execute($input, $tavola, true);
        $this->assertEquals('PG/2016/123456', $res->getCodLocaleProgetto());
        $this->assertEquals('titolo', $res->getTitoloProgetto());
        $this->assertEquals('12345678901', $res->getTc5TipoOperazione()->getTipoOperazione());
        $this->assertEquals('1234567890123456', $res->getCup());
        $this->assertEquals('A', $res->getTc6TipoAiuto()->getTipoAiuto());
        $this->assertEquals(new \DateTime('2019-01-01'), $res->getDataInizio());
        $this->assertEquals(new \DateTime('2021-01-01'), $res->getDataFinePrevista());
        $this->assertEquals(new \DateTime('2020-01-01'), $res->getDataFineEffettiva());
        $this->assertEquals('1', $res->getTc48TipoProceduraAttivazioneOriginaria()->getTipProcAttOrig());
    }

    /**
     * @return MonitoraggioConfigurazioneEsportazioneTavole
     */
    private function createTavolaRichiesta(Richiesta $richiesta) {
        $esportazione = new MonitoraggioEsportazione();
        $configurazione = new MonitoraggioConfigurazioneEsportazioneRichiesta($esportazione);
        $configurazione->setRichiesta($richiesta);
        $esportazione->addMonitoraggioConfigurazione($configurazione);
        $tavola = new MonitoraggioConfigurazioneEsportazioneTavole($configurazione);
        $configurazione->addMonitoraggioConfigurazioneEsportazioneTavole($tavola);

        return $tavola;
    }

    /**
     * @return Richiesta
     */
    private function creaProgetto() {
        $richiesta = new Richiesta();
        $protocollo = new RichiestaProtocolloFinanziamento();
        $protocollo->setRichiesta($richiesta);
        $richiesta->addRichiesteProtocollo($protocollo);

        $protocollo->setAnno_pg(2016);
        $protocollo->setData_pg(new \DateTime('2011-01-01'));
        $protocollo->setNum_pg('123456');
        $protocollo->setRegistro_pg('PG');

        $tipoOperazione = new TC5TipoOperazione();
        $tipoOperazione->setTipoOperazione('12345678901');

        $tipoAiuto = new TC6TipoAiuto();
        $tipoAiuto->setTipoAiuto('A');

        $tipoProcedura = new TC48TipoProceduraAttivazioneOriginaria();
        $tipoProcedura->setTipProcAttOrig('1');

        $istruttoria = new IstruttoriaRichiesta();

        // $settoreCup = new CupSettore();
        // $settoreCup->setCodice('01');
        // $sottoSettoreCup = new CupSottosettore();
        // $sottoSettoreCup->setCodice('02');
        // $sottoSettoreCup->setCupSettore();
        // $settoreCup->addCupSottosettore($sottoSettoreCup);
        // $categoriaCup = new CupCategoria();
        // $categoriaCup->setCodice('03');
        // $categoriaCup->setCupSottosettore($sottoSettoreCup);
        // $sottoSettoreCup->addCupCategoria($categoriaCup);

        $tc5 = new TC5TipoOperazione();
        $tc5->setTipoOperazione('12345678901');
        $tipologiaCup = new CupTipologia();
        $tipologiaCup->setTc5TipoOperazione($tc5);
        $istruttoria->setCupTipologia($tipologiaCup);

        $naturaCup = new CupNatura();

        // $istruttoria->setCupSettore($settoreCup);
        // $istruttoria->setCupSottosettore($sottoSettoreCup);
        // $istruttoria->setCupCategoria($categoriaCup);
        $istruttoria->setCupNatura($naturaCup);

        $istruttoria->setRichiesta($richiesta);
        $istruttoria->setCodiceCup('1234567890123456');

        $atc = new AttuazioneControlloRichiesta();
        $atc->setRichiesta($richiesta);
        $atc->setDataTermine(new \DateTime('2021-01-01'));
        $atc->setDataTermineEffettivo(new \DateTime('2020-01-01'));
        $atc->setDataAvvio(new \DateTime('2019-01-01'));

        $richiesta->setTitolo('titolo')
        ->setAbstract('abstract')
        ->setMonTipoOperazione($tipoOperazione)
        ->setIstruttoria($istruttoria)
        ->setMonTipoProceduraAttOrig($tipoProcedura)
        ->setMonTipoAiuto($tipoAiuto)
        ->setAttuazioneControllo($atc);

        return $richiesta;
    }

    public function testEsportazioneSenzaControllo() {
        $this->em->expects($this->never())->method('getRepository');

        $richiesta = $this->creaProgetto();
        $tavola = $this->createTavolaRichiesta($richiesta);
        $this->esporta->execute($richiesta, $tavola);
    }

    public function testEsportazioneNonNecessaria() {
        $repository = $this->createMock(AP00AnagraficaProgettiRepository::class);
        $repository->expects($this->once())
        ->method('isEsportabile')
        ->willReturn(false);

        $this->em->expects($this->once())
        ->method('getRepository')
        ->willReturn($repository);

        $richiesta = $this->creaProgetto();
        $tavola = $this->createTavolaRichiesta($richiesta);

        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Esportazione struttura AP00 per il progetto PG/2016/123456 non necessaria');
        $res = $this->esporta->execute($richiesta, $tavola, true);
    }

    public function testImportaCasoFelice() {
        $tc5 = new TC5TipoOperazione();
        $tc6 = new TC6TipoAiuto();
        $tc48 = new TC48TipoProceduraAttivazioneOriginaria();

        $tc5Repository = $this->createMock(ObjectRepository::class);
        $tc6Repository = $this->createMock(ObjectRepository::class);
        $tc48Repository = $this->createMock(ObjectRepository::class);
        $tc5Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc5);

        $tc6Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc6);

        $tc48Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc48);

        $this->em->expects($this->any())
        ->method('getRepository')
        ->withConsecutive(
            array('MonitoraggioBundle:TC5TipoOperazione'),
            array('MonitoraggioBundle:TC6TipoAiuto'),
            array('MonitoraggioBundle:TC48TipoProceduraAttivazioneOriginaria')
        )
        ->willReturnOnConsecutiveCalls(
            $tc5Repository,
            $tc6Repository,
            $tc48Repository
        );

        $input = array(
            'COD_LOCALE_PROGETTO',
            'TITOLO_PROGETTO',
            'SINTESI_PRG',
            $tc5, //TIPO_OPERAZIONE
            'CUP',
            'TIPO_AIUTO',
            '01/01/2019', //DATA INIZIO
            '01/01/2018', //DATA FINE PREVISTA
            '01/01/2017', //DATA FINE EFFETTIVA
            '01', //TIP_PROC_A TT_ORIG
            'CODICE_PROC_A TT_ORIG',
            'S', //FLG_CANCELLAZIONE
        );
        $res = $this->esporta->importa($input);
        $this->assertEquals($res->getTc5TipoOperazione(), $tc5);
        $this->assertEquals($res->getTc6TipoAiuto(), $tc6);
        $this->assertEquals($res->getTc48TipoProceduraAttivazioneOriginaria(), $tc48);
        $this->assertEquals($res->getCodLocaleProgetto(), 'COD_LOCALE_PROGETTO');
        $this->assertEquals($res->getTitoloProgetto(), 'TITOLO_PROGETTO');
        $this->assertEquals($res->getSintesiPrg(), 'SINTESI_PRG');
        $this->assertEquals($res->getCup(), 'CUP');
        $this->assertEquals($res->getDataInizio(), new \DateTime('2019-01-01'));
        $this->assertEquals($res->getDataFinePrevista(), new \DateTime('2018-01-01'));
        $this->assertEquals($res->getDataFineEffettiva(), new \DateTime('2017-01-01'));
        $this->assertEquals($res->getCodiceProcAttOrig(), 'CODICE_PROC_A TT_ORIG');
        $this->assertEquals($res->getFlgCancellazione(), 'S');
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testTc5Null() {
        $tc5 = null;
        $tc6 = new TC6TipoAiuto();
        $tc48 = new TC48TipoProceduraAttivazioneOriginaria();

        $tc5Repository = $this->createMock(ObjectRepository::class);
        $tc6Repository = $this->createMock(ObjectRepository::class);
        $tc48Repository = $this->createMock(ObjectRepository::class);
        $tc5Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc5);

        $tc6Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc6);

        $tc48Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc48);

        $this->em->expects($this->any())
        ->method('getRepository')
        ->withConsecutive(
            array('MonitoraggioBundle:TC5TipoOperazione'),
            array('MonitoraggioBundle:TC6TipoAiuto'),
            array('MonitoraggioBundle:TC48TipoProceduraAttivazioneOriginaria')
        )
        ->willReturnOnConsecutiveCalls(
            $tc5Repository,
            $tc6Repository,
            $tc48Repository
        );

        $input = array(
            'COD_LOCALE_PROGETTO',
            'TITOLO_PROGETTO',
            'SINTESI_PRG',
            $tc5, //TIPO_OPERAZIONE
            'CUP',
            'TIPO_AIUTO',
            '01/01/2019', //DATA INIZIO
            '01/01/2018', //DATA FINE PREVISTA
            '01/01/2017', //DATA FINE EFFETTIVA
            '01', //TIP_PROC_A TT_ORIG
            'CODICE_PROC_A TT_ORIG',
            'S', //FLG_CANCELLAZIONE
        );
        $res = $this->esporta->importa($input);
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testTc6Null() {
        $tc5 = new TC5TipoOperazione();
        $tc6 = null;
        $tc48 = new TC48TipoProceduraAttivazioneOriginaria();

        $tc5Repository = $this->createMock(ObjectRepository::class);
        $tc6Repository = $this->createMock(ObjectRepository::class);
        $tc48Repository = $this->createMock(ObjectRepository::class);
        $tc5Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc5);

        $tc6Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc6);

        $tc48Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc48);

        $this->em->expects($this->any())
        ->method('getRepository')
        ->withConsecutive(
            array('MonitoraggioBundle:TC5TipoOperazione'),
            array('MonitoraggioBundle:TC6TipoAiuto'),
            array('MonitoraggioBundle:TC48TipoProceduraAttivazioneOriginaria')
        )
        ->willReturnOnConsecutiveCalls(
            $tc5Repository,
            $tc6Repository,
            $tc48Repository
        );

        $input = array(
            'COD_LOCALE_PROGETTO',
            'TITOLO_PROGETTO',
            'SINTESI_PRG',
            $tc5, //TIPO_OPERAZIONE
            'CUP',
            'TIPO_AIUTO',
            '01/01/2019', //DATA INIZIO
            '01/01/2018', //DATA FINE PREVISTA
            '01/01/2017', //DATA FINE EFFETTIVA
            '01', //TIP_PROC_A TT_ORIG
            'CODICE_PROC_A TT_ORIG',
            'S', //FLG_CANCELLAZIONE
        );
        $res = $this->esporta->importa($input);
    }

    /**
     * @expectedException \MonitoraggioBundle\Exception\EsportazioneException
     */
    public function testTc48Null() {
        $tc5 = new TC5TipoOperazione();
        $tc6 = new TC6TipoAiuto();
        $tc48 = null;

        $tc5Repository = $this->createMock(ObjectRepository::class);
        $tc6Repository = $this->createMock(ObjectRepository::class);
        $tc48Repository = $this->createMock(ObjectRepository::class);
        $tc5Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc5);

        $tc6Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc6);

        $tc48Repository->expects($this->any())
        ->method('findOneBy')
        ->willReturn($tc48);

        $this->em->expects($this->any())
        ->method('getRepository')
        ->withConsecutive(
            array('MonitoraggioBundle:TC5TipoOperazione'),
            array('MonitoraggioBundle:TC6TipoAiuto'),
            array('MonitoraggioBundle:TC48TipoProceduraAttivazioneOriginaria')
        )
        ->willReturnOnConsecutiveCalls(
            $tc5Repository,
            $tc6Repository,
            $tc48Repository
        );

        $input = array(
            'COD_LOCALE_PROGETTO',
            'TITOLO_PROGETTO',
            'SINTESI_PRG',
            $tc5, //TIPO_OPERAZIONE
            'CUP',
            'TIPO_AIUTO',
            '01/01/2019', //DATA INIZIO
            '01/01/2018', //DATA FINE PREVISTA
            '01/01/2017', //DATA FINE EFFETTIVA
            '01', //TIP_PROC_A TT_ORIG
            'CODICE_PROC_A TT_ORIG',
            'S', //FLG_CANCELLAZIONE
        );
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
        $this->esporta->importa(array());
    }
}
