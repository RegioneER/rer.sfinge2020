<?php

namespace RichiesteBundle\Utility;

class EsitoValidazione {
    /**
     * @var bool
     */
    protected $esito;
    /**
     * @var string[]
     */
    protected $messaggi;
    /**
     * @var string[]
     */
    protected $messaggiSezione;
    protected $sezione;

    public function __construct($esito = null, $messaggio = null, $messaggiSezione = null, $sezione = null) {
        $this->esito = $esito;
        $this->messaggi = [];
        $this->messaggiSezione = [];
        if (!is_null($messaggio)) {
            $this->messaggi[] = $messaggio;
        }
        if (!is_null($messaggiSezione)) {
            $this->messaggiSezione[] = $messaggiSezione;
        }
        $this->sezione = $sezione;
    }

    public function getEsito() {
        return $this->esito;
    }

    public function getMessaggi() {
        return $this->messaggi;
    }

    public function getSezione() {
        return $this->sezione;
    }

    public function setEsito($esito) {
        $this->esito = $esito;
    }

    public function setMessaggio($messaggio) {
        $this->messaggi = $messaggio;
    }

    public function setSezione($sezione) {
        $this->sezione = $sezione;
    }

    public function addMessaggio($valore, $chiave = null) {
        if (is_null($chiave)) {
            $this->messaggi[] = $valore;
        } else {
            $this->messaggi[$chiave] = $valore;
        }
    }

    public function getMessaggiSezione() {
        return $this->messaggiSezione;
    }

    public function setMessaggiSezione($messaggiSezione) {
        $this->messaggiSezione = $messaggiSezione;
    }

    public function addMessaggioSezione($valore, $chiave = null) {
        if (is_null($chiave)) {
            $this->messaggiSezione[] = $valore;
        } else {
            $this->messaggiSezione[$chiave] = $valore;
        }
    }

    public function getTuttiMessaggi() {
        $messaggi = [];
        $messaggi = array_merge_recursive($messaggi, $this->messaggiSezione);
        $messaggi = array_merge_recursive($messaggi, $this->messaggi);
        return $messaggi;
    }

    public function merge(self $esito): self {
        $nuovoEsito = clone $this;
        $nuovoEsito->esito = $esito->esito && $nuovoEsito->esito;
        foreach ($esito->messaggi as $messaggio) {
            $nuovoEsito->messaggi[] = $messaggio;
        }
        foreach ($esito->messaggiSezione as $messaggio) {
            $nuovoEsito->messaggiSezione[] = $messaggio;
        }

        return $nuovoEsito;
    }
}
