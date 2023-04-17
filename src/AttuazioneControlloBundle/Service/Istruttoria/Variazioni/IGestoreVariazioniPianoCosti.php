<?php

namespace AttuazioneControlloBundle\Service\Istruttoria\Variazioni;

use RichiesteBundle\Entity\Proponente;
use Symfony\Component\HttpFoundation\Response;

interface IGestoreVariazioniPianoCosti
{
    public function pianoCostiVariazione($annualita, Proponente $proponente = null): Response;
    
    public function totaliPianoCosti(): Response;
}