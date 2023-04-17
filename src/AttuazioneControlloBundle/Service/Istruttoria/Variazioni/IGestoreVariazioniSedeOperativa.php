<?php

namespace AttuazioneControlloBundle\Service\Istruttoria\Variazioni;

use Symfony\Component\HttpFoundation\Response;

interface IGestoreVariazioniSedeOperativa {
    public function dettaglioSedeOperativa(): Response;
}
