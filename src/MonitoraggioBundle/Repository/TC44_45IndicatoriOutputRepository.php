<?php

namespace MonitoraggioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use MonitoraggioBundle\Entity\TC44IndicatoriOutputComuni;
use MonitoraggioBundle\Entity\TC45IndicatoriOutputProgramma;

/**
 * @author vbusccemi
 */
class TC44_45IndicatoriOutputRepository extends EntityRepository{
    // public function getIndicatoriVisibili(): QueryBuilder {
    //     $qb = $this->createQueryBuilder('tc');
    //     $expr = $qb->expr();
    //     $qb->leftJoin('tc.')
    //     $qb->where(
    //         $expr->orX(
    //             $expr->isInstanceOf('tc', TC44IndicatoriOutputComuni::class),
    //             $expr->andX(
    //                 $expr->isInstanceOf('tc', TC45IndicatoriOutputProgramma::class),
    //                 $expr->
    //             )
    //         )
    //     );
    //     return $qb;
    // }
}
