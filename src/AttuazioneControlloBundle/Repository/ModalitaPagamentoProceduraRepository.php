<?php

namespace AttuazioneControlloBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SfingeBundle\Form\Entity\RicercaModalitaPagamentoProcedura;

class ModalitaPagamentoProceduraRepository extends EntityRepository {
    public function getRicercaModalita(RicercaModalitaPagamentoProcedura $ricerca) {
        $qb = $this->createQueryBuilder('mp')
        ->join('mp.procedura', 'procedura')
        ->join('mp.modalita_pagamento', 'modalita')
        ->where(
            'procedura = coalesce(:procedura, procedura)',
            'modalita = coalesce(:modalita, modalita)',
            'procedura INSTANCE OF SfingeBundle:Bando OR procedura INSTANCE OF SfingeBundle:ProceduraPA'
        )
        ->setParameter('procedura', $ricerca->procedura)
        ->setParameter('modalita', $ricerca->modalita);

        return $qb->getQuery();
    }
}
