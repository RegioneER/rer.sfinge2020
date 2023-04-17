<?php

namespace GeoBundle\Entity;


class GeoRegioneRepository extends GeoRepository {

    protected function getGeoTable() {
        return 'GeoRegione';
    }
    
    public function regioniList() {
        $q = $this->getEntityManager()
			->createQuery('SELECT r FROM GeoBundle:GeoRegione r ORDER BY r.denominazione ASC');
        return $q->getResult();
    }

}

?>
