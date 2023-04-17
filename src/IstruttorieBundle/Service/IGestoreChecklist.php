<?php

namespace IstruttorieBundle\Service;

interface IGestoreChecklist {
	public function genera($istruttoria, $checklist, $proponente = null);
	
	public function valuta($valutazione_checklist, $extra = array());
	
	public function valida($valutazione_checklist);
	
	public function isAmmissibile($valutazione_checklist);
	
	public function operazioniValidazione($valutazione_checklist);
	
	public function getElementiDaEscludere($istruttoria, $checklist, $proponente = null);
}
