<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;

interface IGestoreVariazioniConcreta extends IGestoreVariazioni {
    public function validaVariazione(): Response;

    public function dettaglioVariazione(): Response;

    public function gestioneDocumentiVariazione(): Response;

    public function dammiVociMenuElencoVariazioni(): array;

    public function inviaVariazione(): Response;

    public function controllaValiditaVariazione(): EsitoValidazione;
}
