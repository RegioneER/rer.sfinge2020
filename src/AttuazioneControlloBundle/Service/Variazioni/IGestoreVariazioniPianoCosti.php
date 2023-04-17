<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use RichiesteBundle\Entity\Proponente;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Utility\EsitoValidazione;

interface IGestoreVariazioniPianoCosti extends IGestoreVariazioniConcreta {
    public function pianoCostiVariazione($annualita, Proponente $proponente = null): Response;

    public function validaPianoDeiCosti(int $annualita = null, Proponente $proponente = null): EsitoValidazione;
}
