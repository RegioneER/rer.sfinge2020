<?php

namespace AttuazioneControlloBundle\Service\Istruttoria\Variazioni;

use RichiesteBundle\Entity\Proponente;
use Symfony\Component\HttpFoundation\Response;

interface IGestoreVariazioniDatiBancari {
    public function dettaglioDatiBancari(Proponente $proponente): Response;
}
