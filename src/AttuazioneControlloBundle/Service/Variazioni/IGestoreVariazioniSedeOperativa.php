<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;

interface IGestoreVariazioniSedeOperativa {
    public function cambioSedeOperativa(): Response;

    public function validaSedeOperativa(): EsitoValidazione;
}
