<?php

namespace SoggettoBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use RichiesteBundle\Entity\Richiesta;
use RuntimeException;

class SedeRepository extends EntityRepository {
    public function supportsClass($class) {
        return 'SoggettoBundle\Entity\Sede' === $class;
    }

    public function isSedeAzienda($id_soggetto, $id_sede) {
        $query = "SELECT sa
				  FROM SoggettoBundle:Sede sa				  
                                  WHERE sa.soggetto = :idSoggetto
                                  AND sa.id = :idSede";

        $parametri = [];
        $parametri["idSoggetto"] = $id_soggetto;
        $parametri["idSede"] = $id_sede;

        $q = $this->getEntityManager()->createQuery($query)->setParameters($parametri);

        $a = $q->getResult();

        return count($a) > 0;
    }

	/**
	 * Prende le sedi che non sono associate come sedi operative nel progetto
	 * @param Richiesta $richiesta 
	 * @return QueryBuilder 
	 * @throws InvalidArgumentException 
	 * @throws RuntimeException 
	 */
    public function getNuoveSediNonAssociate(Richiesta $richiesta): QueryBuilder {
        $qb = $this->createQueryBuilder('sede');
        return $qb
                ->join('sede.soggetto', 'soggetto')
                ->join('soggetto.proponenti', 'proponente', 'WITH', 'proponente.mandatario = 1')
                ->join('proponente.richiesta', 'richiesta')
                ->where(
                    'richiesta = :richiesta',
                    'sede.data_cessazione is null',
                    'sede not in (
                        SELECT s 
                        FROM SoggettoBundle:Sede s
                        JOIN s.sedeOperativa so
                        JOIN so.proponente p WITH p.mandatario = 1
                        JOIN p.richiesta r
                        WHERE r = :richiesta
                    )'
                )
                ->setParameter('richiesta', $richiesta);

        return $qb;
    }
}
