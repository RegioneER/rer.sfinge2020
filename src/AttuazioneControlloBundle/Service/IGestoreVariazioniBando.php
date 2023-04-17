<?php

namespace AttuazioneControlloBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Entity\Richiesta;

interface IGestoreVariazioniBando {
    public function elencoVariazioni($id_richiesta): Response;

    public function aggiungiVariazione(Richiesta $richiesta): Response;
}
