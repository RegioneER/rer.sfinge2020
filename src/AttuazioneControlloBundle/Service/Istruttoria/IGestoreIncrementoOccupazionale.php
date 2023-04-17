<?php

namespace AttuazioneControlloBundle\Service\Istruttoria;

use AttuazioneControlloBundle\Entity\Pagamento;


interface IGestoreIncrementoOccupazionale
{
    /**
     * @param Pagamento $pagamento
     * @return mixed
     */
    public function dettaglioIncrementoOccupazionale(Pagamento $pagamento, $twig = null);

    /**
     * @param Pagamento $pagamento
     * @return mixed
     */
    public function validaIncrementoOccupazionale(Pagamento $pagamento);
}
