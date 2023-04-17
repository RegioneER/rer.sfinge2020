<?php

namespace CertificazioniBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaPagamentiDaCertificare extends RicercaPagamenti {
	
	public function getType()
    {
        return "CertificazioniBundle\Form\RicercaPagamentiDaCertificareType";
    }

    public function getNomeMetodoRepository()
    {
        return "getPagamentiDaCertificare";
    }
}
