<?php

namespace CertificazioniBundle\Form\Entity;

class RicercaDebitoriOlaf extends RicercaDebitori {

	public function getNomeMetodoRepository() {
		return "ricercaProgettiOlafPerAsse";
	}

}
