<?php

namespace MonitoraggioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use MonitoraggioBundle\Form\Entity\RicercaAssociazioneAzioniIndicatori;

class IndicatoriOutputAzioniRepository extends EntityRepository {
    public function findAzioni(RicercaAssociazioneAzioniIndicatori $ricerca): QueryBuilder {
        $qb = $this->createQueryBuilder('a');
        $qb->select(['a', 'azione', 'asse', 'indicatoreOutput']);
        $qb->join('a.azione', 'azione');
        $qb->leftJoin('a.asse', 'asse');
        $qb->join('a.indicatoreOutput', 'indicatoreOutput');
        $expr = $qb->expr();
        $qb->where(
            $expr->orX(
                $expr->in('azione', ':azioni'),
                $expr->eq('0', ':countAzioni')
            ),
            $expr->orX(
                $expr->in('asse', ':assi'),
                $expr->eq('0', ':countAssi')
            ),
            $expr->orX(
                $expr->in('indicatoreOutput', ':indicatori'),
                $expr->eq('0', ':countIndicatori')
            )
        );
        $qb->orderBy('a.id', 'desc');
        $qb->setParameters([
            'countAzioni' => \count($ricerca->azioni),
            'countAssi' => \count($ricerca->assi),
            'countIndicatori' => \count($ricerca->indicatori),
            'azioni' => $this->addNullIfEmpty($ricerca->azioni),
            'assi' => $this->addNullIfEmpty($ricerca->assi),
            'indicatori' => $this->addNullIfEmpty($ricerca->indicatori)
        ]);

        return $qb;
    }

    private function addNullIfEmpty(array $array): array {
        if(\count($array)){
            $array[] = null;
        }

        return $array;
    }
}
