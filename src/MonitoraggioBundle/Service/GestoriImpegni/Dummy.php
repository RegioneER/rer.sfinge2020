<?php

namespace MonitoraggioBundle\Service\GestoriImpegni;

use AttuazioneControlloBundle\Entity\Revoche\Revoca;
use MonitoraggioBundle\Service\AGestoreImpegni;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class Dummy extends AGestoreImpegni {
    public function aggiornaImpegniASaldo(): void {
    }

    public function impegnoNuovoProgetto(): void {
    }

    public function mostraSezionePagamento(): bool {
        return false;
    }

    public function aggiornaRevoca(Revoca $revoca): void {
    }

    public function rimuoviImpegniRevoca(Revoca $revoca): void {
    }

    public function validaImpegniBeneficiario(): ConstraintViolationListInterface {
        return new ConstraintViolationList();
    }
}
