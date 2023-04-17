<?php

namespace IstruttorieBundle\Service;

interface IGestoreIstruttoria {

	public function aggiornaIstruttoriaRichiesta($id_richiesta, $opzioni = array());
	
	public function avanzaFaseIstruttoriaRichiesta($istruttoria_richiesta);
	
	public function esitoFinaleIstruttoria($id_richiesta);
	
	public function getScelteEsitoFinale();
	
	public function isEsitoFinaleEmettibile($istruttoria_richiesta);
	
	public function isEsitoFinalePositivoEmettibile($istruttoria_richiesta);
	
	public function operazioniAvanzamentoFase($istruttoria_richiesta, $fase);
	
	public function datiCup($id_richiesta);
	
	public function getSelezioniCup($id_richiesta, $esisteCup);
	
	public function isFaseAvanzabile($istruttoria_richiesta);

	public function creaIntegrazione($id_valutazione_checklist);

	public function avanzamentoATC($id_richiesta);

	public function validaATC($form);
	
	public function getEmailATCConfig($istruttoria_richiesta);
	
	public function generaATC($istruttoria_richiesta);

	public function nucleoIstruttoria($id_richiesta);
        
    public function eliminaDocumentoNucleoIstruttoria($idRichiesta, $id_documento);

}
