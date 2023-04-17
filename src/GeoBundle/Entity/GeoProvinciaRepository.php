<?php

namespace GeoBundle\Entity;

use Doctrine\ORM\QueryBuilder;

class GeoProvinciaRepository extends GeoRepository {

    protected function getGeoTable() {
        return 'GeoProvincia';
    }

    public function provinceList($id_regione = null, $stato_cessazione = null) {
        $qb = $this->createQueryBuilder('p');

        $method = "where";

        if ($id_regione) {
            $qb->where('p.regione = :regione_id')
                    ->setParameter('regione_id', $id_regione);

            $method = "andWhere";
        }

        if ($stato_cessazione == "cessate") {
            $qb->$method('p.cessata = :stato_cessazione')
                    ->setParameter('stato_cessazione', 1);
        } elseif ($stato_cessazione == "non-cessate") {
            $qb->$method('p.cessata = :stato_cessazione')
                    ->setParameter('stato_cessazione', 0);
        }

        $qb->orderBy('p.denominazione', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function provinceListQb(?GeoRegione $regione, bool $cessata = false): QueryBuilder
    {
        $qb = $this->createQueryBuilder('province')
        ->join('province.regione', 'regioni')
        ->where(
            'regioni = coalesce(:regione, regioni)',
            'province.cessata in (0, :cessata)'
        )
        ->orderBy('province.denominazione', 'ASC')
        ->setParameter('cessata', $cessata)
        ->setParameter('regione', $regione);

        return $qb;
    }

    /**
     * @param $provincia
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function ricercaDaSiglaProvinciaAdrier($provincia)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->where('p.sigla_automobilistica = :sigla')
            ->setParameter('sigla', $provincia)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function ricercaDaIstatComune($istat)
    {

    }

}

?>
