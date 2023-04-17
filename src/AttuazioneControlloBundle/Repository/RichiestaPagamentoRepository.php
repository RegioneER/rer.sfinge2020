<?php

namespace AttuazioneControlloBundle\Repository;

use Doctrine\ORM\EntityRepository;

class RichiestaPagamentoRepository extends EntityRepository
{
    public function findAllPagamenti(\RichiesteBundle\Entity\Richiesta $richiesta, $filtro)
    {
        $dql = 'select pagamenti '
            . 'from AttuazioneControlloBundle:RichiestaPagamento pagamenti '
            . 'join pagamenti.richiesta richiesta '
            . 'left join pagamenti.pagamenti_ammessi pagamenti_ammessi '
            . 'where richiesta = :richiesta '
            . 'and pagamenti.tipologia_pagamento like :tipologia ';

        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameters(array(
            'richiesta' => $richiesta,
            'tipologia' => $filtro . '%',
        ))
            ->getResult();

    }
}
