<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use AttuazioneControlloBundle\Entity\DocumentoVariazione;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;

interface IGestoreVariazioni {
    public function datiGeneraliVariazione(): Response;

    public function eliminaDocumentoVariazione(DocumentoVariazione $documento): Response;

    public function validaDatiGenerali(): EsitoValidazione;

    public function validaDocumenti(): EsitoValidazione;

    public function isVariazioneBloccata(): bool;

    public function eliminaVariazione(): Response;

    public function caricaVariazioneFirmata(): Response;

    public function scaricaDomanda(): Response;

    public function scaricaVariazioneFirmata(): Response;

    public function invalidaVariazione(): Response;

    public function validaVariazioneInviabile(): EsitoValidazione;

    public function modificaFirmatario(): Response;
}
