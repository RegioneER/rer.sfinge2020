<?php

namespace GeoBundle\Entity;


class GeoStatoRepository extends GeoRepository {

    protected function getGeoTable() {
        return 'GeoStato';
    }

	public function statiList() {
        $q = $this->getEntityManager()
			->createQuery('SELECT s FROM GeoBundle:GeoStato s ORDER BY s.id ASC');
        return $q->getResult();
    }

    /**
     * @param $codice
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function ricercaByCodiceParziale($codice)
    {
        $q = $this->getEntityManager()
            ->createQuery("SELECT s FROM GeoBundle:GeoComune c 
                JOIN GeoBundle\Entity\GeoProvincia p WITH c.provincia = p.id
                JOIN GeoBundle\Entity\GeoRegione r WITH p.regione = r.id
                JOIN GeoBundle\Entity\GeoStato s WITH r.stato = s.id
                WHERE c.codice_completo LIKE :codice ORDER BY c.codice_completo")
            ->setParameter('codice', '%' . $codice . '%')
            ->setMaxResults(1)
            ->getOneOrNullResult();
        return $q;
    }

    /*
     * JOIN GeoBundle:GeoProvincia p WITH p.id = c
                JOIN GeoBundle:GeoRegione r WITH r.id = p
                JOIN GeoBundle:GeoStato s WITH s.id = r
     */

}

?>
