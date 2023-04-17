<?php

namespace RichiesteBundle\Service;

interface IGestorePriorita {

	public function gestionePriorita($id_richiesta, $opzioni = array());
	
	public function validaPriorita($id_richiesta);
}
