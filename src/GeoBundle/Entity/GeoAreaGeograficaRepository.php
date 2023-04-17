<?php

namespace GeoBundle\Entity;
use Doctrine\ORM\EntityRepository;



class GeoAreaGeograficaRepository extends EntityRepository {

    public function getList($from, $to, $orderBy = null, $asc = false) {
        $q = $this->getEntityManager()
                ->createQuery('SELECT g FROM GeoBundle:GeoAreaGeografica g ORDER BY d.' . ($orderBy ? : 'ripartizione') . ($asc ? ' ASC' : ' DESC'));
        $q->setMaxResults($to - $from + 1);
        $q->setFirstResult($from);
        return $q->getResult();
    }


}

?>
