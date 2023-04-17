<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Description of TC3ResponsabileProceduraRepository
 *
 * @author lfontana
 */
class TC16LocalizzazioneGeograficaRepository extends EntityRepository {

    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC16 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC16LocalizzazioneGeografica e '
                . "where e.codice_regione like :codice_regione "
                . "and coalesce(e.descrizione_regione, '') like :descrizione_regione "
                . "and coalesce(e.codice_provincia, '') like :codice_provincia "
                . "and coalesce(e.descrizione_provincia, '') like :descrizione_provincia "
                . "and coalesce(e.codice_comune, '') like :codice_comune "
                . "and coalesce(e.descrizione_comune, '') like :descrizione_comune "
                . "and coalesce(e.nuts_1, '') like :nuts_1 "
                . "and coalesce(e.nuts_2, '') like :nuts_2 "
                . "and coalesce(e.nuts_3, '') like :nuts_3 "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':codice_regione', '%' . $ricerca->getCodiceRegione() . '%');
        $q->setParameter(':descrizione_regione', '%' . $ricerca->getDescrizioneRegione() . '%');
        $q->setParameter(':codice_provincia', '%' . $ricerca->getCodiceProvincia() . '%');
        $q->setParameter(':descrizione_provincia', '%' . $ricerca->getDescrizioneProvincia() . '%');
        $q->setParameter(':codice_comune', '%' . $ricerca->getCodiceComune() . '%');
        $q->setParameter(':descrizione_comune', '%' . $ricerca->getDescrizioneComune() . '%');
        $q->setParameter(':nuts_1', '%' . $ricerca->getNuts1() . '%');
        $q->setParameter(':nuts_2', '%' . $ricerca->getNuts2() . '%');
        $q->setParameter(':nuts_3', '%' . $ricerca->getNuts3() . '%');

        return $q;
    }

    public function findAllProvincie() {
        return $this->getEntityManager()
            ->createQuery(
                "select distinct new MonitoraggioBundle\Form\Entity\Provincia(e.codice_provincia, e.descrizione_provincia, e.descrizione_regione) "
                . "from MonitoraggioBundle:TC16LocalizzazioneGeografica e")
            ->getResult();
    }
    public function findAllRegioni() {
        return $this->getEntityManager()
            ->createQuery(
                "select distinct new MonitoraggioBundle\Form\Entity\Regione(e.codice_regione, e.descrizione_regione) "
                . "from MonitoraggioBundle:TC16LocalizzazioneGeografica e")
            ->getResult();
    }
    public function findProvincieByCod($codice){
        return $this->getEntityManager()
            ->createQuery(
                "select distinct new MonitoraggioBundle\Form\Entity\Provincia(e.codice_provincia, e.descrizione_provincia, e.descrizione_regione) "
                . "from MonitoraggioBundle:TC16LocalizzazioneGeografica e where e.codice_provincia = coalesce(:codice, e.codice_provincia) ")
            ->setParameter("codice", $codice)
            ->getResult();
    }
    
    public function ajaxRequest($params){
        $isNotNull = ($params? 1: 0);
        $q = $this->getEntityManager()->createQueryBuilder('u');
        $q->select(array('u.id','u.descrizione_comune value'));
        $q->from('MonitoraggioBundle:TC16LocalizzazioneGeografica', 'u');
        $q->where($q->expr()->eq(1, ':exists'));       
        
        /*if ($isNotNull) {
            foreach ($params as $key => $tabella) {
                $q->andWhere($q->expr()->like('u.codice_provincia', '?'.$key));
                $q->setParameter($key, '%'. $tabella .'%');
            }
        }
         *  */
        $q->andWhere( $q->expr()->like('u.codice_provincia', ':param'));
            
        
        $q->setParameter('exists', $isNotNull );
        $q->setParameter('param', $params);
        return $q;
    }

}
