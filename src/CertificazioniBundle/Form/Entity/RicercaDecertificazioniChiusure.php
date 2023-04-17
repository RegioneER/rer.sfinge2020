<?php

namespace CertificazioniBundle\Form\Entity;

class RicercaDecertificazioniChiusure extends RicercaDecertificazioni {

	public function getNomeMetodoRepository() {
		return "getRevocheDecertificatiChiusureConti";
	}
	
	public function getType() {
		return "CertificazioniBundle\Form\RicercaRevocheContiType";
	}

}
