<?php

namespace MonitoraggioBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Utility\EsitoValidazione;


interface IGestoreIterProgetto
{
    public function aggiungiFasiProcedurali(): void;

    public function modificaIterFaseRichiesta(array $options = []): Response;

    public function hasSezioneRichiestaVisibile(): bool;

    public function validaInPresentazioneDomanda(): EsitoValidazione;

    public function validaInSaldo(): EsitoValidazione;
}