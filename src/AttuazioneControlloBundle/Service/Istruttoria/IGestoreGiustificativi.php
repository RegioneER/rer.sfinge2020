<?php

namespace AttuazioneControlloBundle\Service\Istruttoria;

use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\GiustificativoPagamento;

interface IGestoreGiustificativi
{
    public function istruttoriaGiustificativo(GiustificativoPagamento $giustificativo);

    public function elencoGiustificativi($pagamento);

    public function modificaVociImputazione(GiustificativoPagamento $pagamento): Response;
}
