<?php

namespace IstruttorieBundle\Service;

interface IGestorePianoCosto {
	
	public function istruttoriaPianoCostiProponente($id_proponente, $annualita);
	
	public function generaIstruttorieVociPianoCosto($voci_piano_costo);
	
	public function calcolaContributoPianoCosto($istruttoria_richiesta);

	public function totaliPianoCosti($id_richiesta);

}
