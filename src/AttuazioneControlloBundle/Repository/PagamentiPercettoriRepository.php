<?php

namespace AttuazioneControlloBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PagamentiPercettoriRepository extends EntityRepository 
{
    public function findAllPagamentiPercettori($richiesta) {
        $dql = 'select pagamenti_percettori '
                . 'from AttuazioneControlloBundle:PagamentiPercettori pagamenti_percettori '
                . 'join pagamenti_percettori.pagamento pagamento '
                . 'join pagamento.richiesta richiesta '
                . 'where richiesta = :richiesta ';
        $em = $this->getEntityManager();
        $query = $em->createQuery($dql);
        $query->setParameter('richiesta', $richiesta);
        return $query->getResult();
    }
}
