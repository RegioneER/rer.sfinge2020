<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use AttuazioneControlloBundle\Entity\VariazioneSingoloReferente;
use RichiesteBundle\Entity\Referente;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;

interface IGestoreVariazioniReferenti {
    public function elencoReferenti(): Response;

    public function validaReferenti(): EsitoValidazione;

    public function modificaReferente(Referente $referente): Response;

    public function eliminaSingolaVariazione(VariazioneSingoloReferente $singolo): Response;
}
