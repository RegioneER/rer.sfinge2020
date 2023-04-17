<?php

namespace AttuazioneControlloBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RichiestaImpegniRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RichiestaImpegniRepository extends EntityRepository {

    public function findAllRichiestaImpegni($richiesta, $tipo = "I") {
        $dql = 'select richiesta_impegni '
                . 'from AttuazioneControlloBundle:RichiestaImpegni richiesta_impegni '
                . 'join richiesta_impegni.richiesta richiesta '
                . 'where richiesta = :richiesta '
                . 'and richiesta_impegni.tipologia_impegno like :tipologia_impegno ';
        $em = $this->getEntityManager();
        $query = $em->createQuery($dql);
        $query->setParameter('richiesta', $richiesta);
        $query->setParameter('tipologia_impegno', $tipo . '%');
        return $query->getResult();
    }

}
