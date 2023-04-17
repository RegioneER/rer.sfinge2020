<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaAP06;
use MonitoraggioBundle\Repository\TC16LocalizzazioneGeograficaRepository;
use MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\AP06LocalizzazioneGeografica;
use MonitoraggioBundle\Entity\LocalizzazioneGeografica;
use MonitoraggioBundle\Repository\AP06LocalizzazioneGeograficaRepository;


class EsportaAP06Test extends EsportazioneRichiestaBase
{

    /**
     * @var EsportaAP06
     */
    protected $esporta;

    public function setup(){
        parent::setUp();
        $this->esporta = new EsportaAP06($this->container);
    }

    public function testImportazioneOk()
    {
        $input = [
            'cod_locale_progetto',
            'regione',
            'provincia',
            'comune',
            'indirizzo',
            'cap',
            null
        ];
        $tc16 = new TC16LocalizzazioneGeografica();
        $this->setUpRepositories($tc16);
        $res = $this->esporta->importa($input);

        $this->assertInstanceOf(AP06LocalizzazioneGeografica::class, $res);
        $this->assertEquals('cod_locale_progetto', $res->getCodLocaleProgetto());
        $this->assertSame($tc16, $res->getLocalizzazioneGeografica());
    }

    protected function setUpRepositories($tc16){
        $repo = $this->createMock(TC16LocalizzazioneGeograficaRepository::class);
        $repo->method('findOneBy')->willReturn($tc16);
        $this->em->method('getRepository')->willreturn($repo);
    }

    public function testErroreInput()
    {
        $this->expectException(EsportazioneException::class);
        $res = $this->esporta->importa([]);
    }

    public function testImportazioneNoLocalizzazione()
    {
        $input = [
            'cod_locale_progetto',
            'regione',
            'provincia',
            'comune',
            'indirizzo',
            'cap',
            null
        ];
        $this->setUpRepositories(null);

        $this->expectException(EsportazioneException::class);
        $res = $this->esporta->importa($input);
    }

    public function testEsportazioneOk(){
        $tc16 = new TC16LocalizzazioneGeografica();
        $loc = new LocalizzazioneGeografica($this->richiesta);
        $loc->setLocalizzazione($tc16);
        $this->richiesta->addMonLocalizzazioneGeografica($loc);
        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);
        $this->assertNotNull($res);

        $this->assertInstanceOf(AP06LocalizzazioneGeografica::class, $res);
        $this->assertSame($tc16, $res->getLocalizzazioneGeografica());
        $this->assertEquals($this->richiesta->getProtocollo(),$res->getCodLocaleProgetto());
        $this->assertSame($this->tavola, $res->getMonitoraggioConfigurazioneEsportazioniTavola());
    }

    public function testNonEsportabile(){
        $repo = $this->createMock(AP06LocalizzazioneGeograficaRepository::class);
        $this->esportazioneNonNecessaria($repo);

    }

    public function testEsportazioneNoLocalizzazione(){
        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Nessuna localizzazione geografica per il progetto '. $this->richiesta->getProtocollo());

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);
    }
}