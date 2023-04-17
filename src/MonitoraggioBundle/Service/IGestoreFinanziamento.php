<?php

namespace MonitoraggioBundle\Service;

use AttuazioneControlloBundle\Entity\Pagamento;


interface IGestoreFinanziamento
{
	public function aggiornaFinanziamento(bool $force = false): void;
	public function persistFinanziamenti(): void;
}