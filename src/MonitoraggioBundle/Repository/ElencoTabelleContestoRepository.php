<?php


namespace MonitoraggioBundle\Repository;


use Doctrine\ORM\EntityRepository;


/**
 * Description of ElencoTabelleContestoRepository
 *
 * @author lfontana
 */
class ElencoTabelleContestoRepository extends EntityRepository {
    
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\Elenco $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:ElencoTabelleContesto e '
                . "where e.descrizione like :descrizione "
                . "and e.codice like :codice "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':descrizione', '%'.$ricerca->getDescrizione().'%' );
        $q->setParameter(':codice', '%'.$ricerca->getCodice().'%' );
        return $q;
    }
}
