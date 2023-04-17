<?php

namespace RichiesteBundle\Service;

interface IGestoreRisorse {

	public function elencoRisorse($id_richiesta, $tipo);
	
	public function gestioneRisorsa($id_risorsa, $id_richiesta, $tipo, $opzioni = array());
	
	public function validaRisorsa($id_risorsa, $id_richiesta);
	
	public function aggiungiRisorsa($id_richiesta, $opzioni = array());
	
	public function cancellaRisorsa($id_risorsa, $id_richiesta);

}
