<?php

namespace MonitoraggioBundle\Service;

use AttuazioneControlloBundle\Entity\Revoche\Revoca;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface IGestoreImpegni {
    /**
     * Rivede le cifre degli impegni e inserisce eventualmente i disimpegni
     */
    public function aggiornaImpegniASaldo(): void;

    /**
     * Inserisce l'impegno per un progetto che ha superato l'istruttoria
     */
    public function impegnoNuovoProgetto(): void;

    public function aggiornaRevoca(Revoca $revoca): void;

    public function rimuoviImpegniRevoca(Revoca $revoca): void;

    public function mostraSezionePagamento(): bool;

    public function validaImpegniBeneficiario(): ConstraintViolationListInterface;
}
