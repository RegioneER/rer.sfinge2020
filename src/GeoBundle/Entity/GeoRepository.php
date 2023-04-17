<?php

namespace GeoBundle\Entity;
use Doctrine\ORM\EntityRepository;



abstract class GeoRepository extends EntityRepository {

    abstract protected function getGeoTable();

    public function getList($from, $to, $orderBy = null, $asc = false) {
        $q = $this->getEntityManager()
                ->createQuery('SELECT g FROM GeoBundle:' . $this->getGeoTable() . ' g WHERE CURRENT_DATE() >= g.data_istituzione AND d.data_destituzione IS NULL ORDER BY d.' . ($orderBy ? : 'denominazione') . ($asc ? ' ASC' : ' DESC'));
        $q->setMaxResults($to - $from + 1);
        $q->setFirstResult($from);
        return $q->getResult();
    }

    public function getHistoricList($date, $from, $to, $orderBy = null, $asc = false) {
        $q = $this->getEntityManager()
                ->createQuery('SELECT g FROM GeoBundle:' . $this->getGeoTable() . ' g WHERE (? >= g.data_istituzione AND d.data_destituzione IS NULL) OR (? BETWEEN g.data_istituzione AND d.data_destituzione) ORDER BY d.' . ($orderBy ? : 'denominazione') . ($asc ? ' ASC' : ' DESC'));
        $q->setMaxResults($to - $from + 1);
        $q->setFirstResult($from);
        return $q->getResult();
    }

}

?>
