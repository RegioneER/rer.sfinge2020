<?php

namespace GeoBundle\Entity;
use Doctrine\ORM\EntityRepository;



class GeoAreaGeopoliticaRepository extends EntityRepository {

    public function getList($from, $to, $orderBy = null, $asc = false) {
        $q = $this->getEntityManager()
                ->createQuery('SELECT g FROM GeoBundle:GeoAreaGeopolitica g ORDER BY d.' . ($orderBy ? : 'denominazione') . ($asc ? ' ASC' : ' DESC'));
        $q->setMaxResults($to - $from + 1);
        $q->setFirstResult($from);
        return $q->getResult();
    }


}

?>
