<?php

namespace IstruttorieBundle\Service;

use BaseBundle\Service\BaseService;

abstract class AGestoreChecklist extends BaseService implements IGestoreChecklist {

	public function calcolaPunteggio($valutazione_checklist) {

		$punteggio = 0;
		
		foreach ($valutazione_checklist->getValutazioniElementi() as $valutazione) {
			$elemento = $valutazione->getElemento();
			
			if ($elemento->getSignificativo() && !is_null($valutazione->getValore())) {
				if ($elemento->getTipo() == "integer") {
					$punteggio += $valutazione->getValore();
				} else if ($elemento->getTipo() == "checkbox" && !is_null($elemento->getPunteggioMassimo())) {
					$punteggio += ($valutazione->getValore() == "1" ? $valutazione->getValore() : 0);	
				} else {
					throw new \Exception("Errore non previsto");
				}
			}
		}
		
		return $punteggio;		
	}
	
	public function verificaRisposte($valutazione_checklist, $risposte_ammissibili) {
		foreach ($valutazione_checklist->getValutazioniElementi() as $valutazione) {
			$elemento = $valutazione->getElemento();
			if ($elemento->getSignificativo() && !in_array($valutazione->getValoreRaw(), $risposte_ammissibili)) {
				return false;
			}
		}
		
		return true;
	}
	
	public function verificaPunteggioRisposte($valutazione_checklist) {
		foreach ($valutazione_checklist->getValutazioniElementi() as $valutazione) {			
			$elemento = $valutazione->getElemento();
			if($elemento->getTipo() != 'integer'){
				continue;
			}
			if ($valutazione->getValoreRaw() > $elemento->getPunteggioMassimo() || $valutazione->getValoreRaw() < $elemento->getPunteggioMinimoAmmissibilita()){
				return false;
			}
		}
		
		return true;
	}	

}
