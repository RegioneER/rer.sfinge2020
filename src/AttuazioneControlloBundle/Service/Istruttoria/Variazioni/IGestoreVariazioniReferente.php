<?php

namespace AttuazioneControlloBundle\Service\Istruttoria\Variazioni;

use RichiesteBundle\Entity\Proponente;
use Symfony\Component\HttpFoundation\Response;

interface IGestoreVariazioniReferente {
    public function dettaglioReferente(Proponente $proponente): Response;
}
