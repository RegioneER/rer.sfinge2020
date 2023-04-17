<?php
/**
 * @author lfontana
*/

namespace MonitoraggioBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaAssociazioneAzioniIndicatori extends AttributiRicerca{
    public $assi = [];
    public $azioni = [];
    public $indicatori = [];
    
    public function getNomeRepository(): string {
        return "MonitoraggioBundle:IndicatoriOutputAzioni";
    }

    public function getNomeMetodoRepository(): string {
        return "findAzioni";
    }

    public function getType() {
        return "MonitoraggioBundle\Form\Ricerca\RicercaAssociazioneIndicatoriAzioniType";
    }


    public function getNumeroElementiPerPagina(){
        return 10;
    }

    public function getNomeParametroPagina()
    {
        return 'page';
    }

    public function mergeFreshData($freshData) {
		$this->assi= $freshData->assi;
		$this->azioni = $freshData->azioni;
		$this->indicatori = $freshData->indicatori;
	}
}