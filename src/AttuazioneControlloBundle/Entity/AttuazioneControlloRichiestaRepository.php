<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AttuazioneControlloRichiestaRepository extends EntityRepository {
    /**
     * @return AttuazioneControlloRichiesta[]
     */
    public function cercaProtocollo(?string $protocollo): array {
        $qb = $this->createQueryBuilder('atc');
        $expr = $qb->expr();
        $qb
        ->join('atc.richiesta', 'r')
        ->join('r.richieste_protocollo', 'richieste_protocollo')
        ->where(
            $expr->like("CONCAT(richieste_protocollo.registro_pg, '/', richieste_protocollo.anno_pg, '/', richieste_protocollo.num_pg)", ':protocollo')
        )
        ->setParameter('protocollo', "%$protocollo%");

        return $qb->getQuery()->getResult();
    }
}
