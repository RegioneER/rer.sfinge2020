<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use AttuazioneControlloBundle\Entity\DocumentoVariazione;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;

trait GestoreVariazioneWrapperTrait {
    /**
     * @var IgestoreVariazioni
     */
    protected $base;

    public function datiGeneraliVariazione(): Response {
        return $this->base->datiGeneraliVariazione();
    }

    public function gestioneDocumentiVariazione(): Response {
        return $this->base->gestioneDocumentiVariazione();
    }

    public function eliminaDocumentoVariazione(DocumentoVariazione $documento): Response {
        return $this->base->eliminaDocumentoVariazione($documento);
    }

    public function validaDatiGenerali(): EsitoValidazione {
        return $this->base->validaDatiGenerali();
    }

    public function validaDocumenti(): EsitoValidazione {
        return $this->base->validaDocumenti();
    }

    public function isVariazioneBloccata(): bool {
        return $this->base->isVariazioneBloccata();
    }

    public function dammiVociMenuElencoVariazioni(): array {
        return $this->base->dammiVociMenuElencoVariazioni();
    }

    public function eliminaVariazione(): Response {
        return $this->base->eliminaVariazione();
    }

    public function caricaVariazioneFirmata(): Response {
        return $this->base->caricaVariazioneFirmata();
    }

    public function scaricaDomanda(): Response {
        return $this->base->scaricaDomanda();
    }

    public function scaricaVariazioneFirmata(): Response {
        return $this->base->scaricaVariazioneFirmata();
    }

    public function invalidaVariazione(): Response {
        return $this->base->invalidaVariazione();
    }

    public function modificaFirmatario(): Response {
        return $this->base->modificaFirmatario();
    }

    public function validaVariazioneInviabile(): EsitoValidazione {
        return $this->base->validaVariazioneInviabile();
    }
}
