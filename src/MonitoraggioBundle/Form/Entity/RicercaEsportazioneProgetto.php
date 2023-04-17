<?php

namespace MonitoraggioBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;
use SfingeBundle\Entity\Procedura;
use MonitoraggioBundle\Entity\MonitoraggioEsportazione;

class RicercaEsportazioneProgetto extends AttributiRicerca
{
    /** @var Procedura|null */
    public $procedura;

    /** @var string|null */
    public $protocollo;

    /** @var MonitoraggioEsportazione */
    public $esportazione;

    public function __construct(MonitoraggioEsportazione $esportazione){
        $this->esportazione = $esportazione;
    }

    public function getType()
    {
        return "MonitoraggioBundle\Form\Ricerca\RicercaEsportazioneProgettoType";
    }

    public function getNomeRepository()
    {
        return 'MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneRichiesta';
    }

    public function getNomeMetodoRepository()
    {
        return 'ricercaProgettiEsportabili';
    }

    public function getNumeroElementiPerPagina(){
        return 10;
    }

    public function getNomeParametroPagina()
    {
        return 'page';
    }
}