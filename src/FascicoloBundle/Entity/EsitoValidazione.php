<?php

namespace FascicoloBundle\Entity;

class EsitoValidazione {

	protected $esito;
	protected $messaggi;
	protected $messaggiSezione;
	protected $sezione;

	function __construct($esito = null, $messaggio = null, $messaggiSezione = null, $sezione = null) {
		$this->esito = $esito;
		$this->messaggi = array();
		$this->messaggiSezione = array();
		if (!is_null($messaggio)) {
			$this->messaggi=$messaggio;
		}
		if (!is_null($messaggiSezione)) {
			$this->messaggiSezione=$messaggiSezione;
		}
		$this->sezione = $sezione;
	}
	
	function getEsito() {
		return $this->esito;
	}

	function getMessaggi() {
		return $this->messaggi;
	}

	function getSezione() {
		return $this->sezione;
	}

	function setEsito($esito) {
		$this->esito = $esito;
	}

	function setMessaggio($messaggio) {
		$this->messaggi = $messaggio;
	}

	function setSezione($sezione) {
		$this->sezione = $sezione;
	}

	function addMessaggio($valore,$chiave = null){
		if(is_null($chiave)){
			$this->messaggi[] = $valore;
		}else{
			$this->messaggi[$chiave] = $valore;
		}

	}
    
	function addMessaggi($valori){
        foreach ($valori as $valore) {
            $this->addMessaggio($valore);
        }
	}    
	
	public function getMessaggiSezione() {
		return $this->messaggiSezione;
	}

	public function setMessaggiSezione($messaggiSezione) {
		$this->messaggiSezione = $messaggiSezione;
	}
	
	function addMessaggioSezione($valore) {
        if (!in_array($valore, $this->messaggiSezione)) {
            $this->messaggiSezione[] = $valore;
        }
	}

	function getTuttiMessaggi(){
		$messaggi = array();
		$messaggi = array_merge_recursive($messaggi, $this->messaggiSezione);
		$messaggi = array_merge_recursive($messaggi, $this->messaggi);
		return $messaggi;
	}
	
}
