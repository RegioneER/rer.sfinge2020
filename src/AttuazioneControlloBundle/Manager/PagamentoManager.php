<?php

namespace AttuazioneControlloBundle\Manager;

use Doctrine\ORM\EntityManager;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use MonitoraggioBundle\Model\SpesaCertificata;

class PagamentoManager
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * AttivitaGiornataCalendarioManager constructor.
     *
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param SlidingPagination $slidingPagination
     * @return SlidingPagination
     */
    public function aggiornaSlidingPaginationConCertificazione(SlidingPagination $slidingPagination)
    {
        $slidingPaginationRetVal = clone $slidingPagination;
        foreach ($slidingPagination as $key => $item) {
            $pagamento = $this->manager->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($item['id_pagamento']);
            //con le cancellazioni da problemi
            //$certificazioniPagamenti = $pagamento->getCertificazioni();
            //Proviamo con chiamata diretta a repository
            $certificazioniPagamenti = $this->manager->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->getCertificazioniPagamenti($pagamento->getId());

            $certificazioni = array();
            $arrayCertificazioniPagamento = array();
            foreach ($certificazioniPagamenti as $certificazionePagamento) {
                $arrayCertificazioniPagamento[] =  $certificazionePagamento;
                $certificazioni[] = $certificazionePagamento->getCertificazione()->getAnnoContabileNumero();
            }

            $newItem[$key] = $item;
            $newItem[$key]['certificazioni'] = implode(', ', $certificazioni);

            $slidingPaginationRetVal->offsetUnset($key);
            $slidingPaginationRetVal->setItems($newItem);
        }

        return $slidingPaginationRetVal;
    }

    /**
     * @param SlidingPagination $slidingPagination
     * @return SlidingPagination
     */
    public function aggiornaSlidingPaginationConValoreContatore(SlidingPagination $slidingPagination)
    {
        $slidingPaginationRetVal = clone $slidingPagination;
        foreach ($slidingPagination as $key => $item) {
            $pagamento = $this->manager->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($item['id_pagamento']);

            $newItem[$key] = $item;
            $newItem[$key]['contatore'] = $pagamento->getGiorniContatore();


            $slidingPaginationRetVal->offsetUnset($key);
            $slidingPaginationRetVal->setItems($newItem);
        }

        return $slidingPaginationRetVal;
    }
}
