<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use RichiesteBundle\Utility\EsitoValidazione;
use AttuazioneControlloBundle\Entity\VariazioneDatiBancariProponente;
use Symfony\Component\HttpFoundation\Response;

interface IGestoreVariazioniDatiBancari extends IGestoreVariazioniConcreta {
    public function validaDatiBancari(): EsitoValidazione;

    public function validaDatiBancariProponente(VariazioneDatiBancariProponente $datBancari): EsitoValidazione;

    public function modificaDatiBancariProponente(VariazioneDatiBancariProponente $dati): Response;
}
