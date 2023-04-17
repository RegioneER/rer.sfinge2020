<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use RichiesteBundle\Entity\Richiesta;
use ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;
use SfingeBundle\Entity\Bando;


class EsportazioneRichiestaBase extends EsportazioneBase
{
    const NUM_PG = '123456';
    const PG = 'PG';
    const ANNO_PG = '2016';

    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var MonitoraggioConfigurazioneEsportazioneTavole
     */
    protected $tavola;

    protected function setUp()
    {
        parent::setUp();
        $this->richiesta = new Richiesta();
        $protocollo = new RichiestaProtocolloFinanziamento();
        $protocollo->setRegistroPg(self::PG);
        $protocollo->setAnnoPg(self::ANNO_PG);
        $protocollo->setNumPg(self::NUM_PG);
        $protocollo->setRichiesta($this->richiesta);
        $this->richiesta->addRichiesteProtocollo($protocollo);
        $procedura = new Bando();
        $this->richiesta->setProcedura($procedura);
        $procedura->addRichieste($this->richiesta);
        $configurazione = new MonitoraggioConfigurazioneEsportazioneRichiesta();
        $this->tavola = new MonitoraggioConfigurazioneEsportazioneTavole($configurazione);
    }

    public static function GetProtocollo(): string{
        return self::PG . '/'. self::ANNO_PG . '/' . self::NUM_PG;
    }
}