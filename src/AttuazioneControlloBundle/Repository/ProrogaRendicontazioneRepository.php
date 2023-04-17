<?php

namespace AttuazioneControlloBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AttuazioneControlloBundle\Form\Entity\RicercaProrogaRendicontazione;
use Doctrine\ORM\Query;

class ProrogaRendicontazioneRepository extends EntityRepository {

    public function getElencoProroghe(RicercaProrogaRendicontazione $ricerca): Query
    {
        $qb = $this->createQueryBuilder('pr');
        $expr = $qb->expr();
        $qb->join('pr.attuazione_controllo_richiesta', 'atc')
        ->join('atc.richiesta', 'r')
        ->join('r.procedura', 'procedura')
        ->join('r.richieste_protocollo', 'protocollo')
        ->where(
            $expr->eq('r.id', 'COALESCE(:id_operazione, r.id)'),
            $expr->eq('procedura', 'COALESCE(:procedura, procedura)'),
            $expr->like("COALESCE(CONCAT(protocollo.registro_pg,'/',protocollo.anno_pg,'/', protocollo.num_pg),r.id)", ':protocollo')
        )
        ->orderBy('pr.id', 'desc')
        ->setParameter('id_operazione', $ricerca->id_operazione)
        ->setParameter('procedura', $ricerca->procedura)
        ->setParameter('protocollo', "%$ricerca->protocollo%")

        ;

        return $qb->getQuery();
    }
}
