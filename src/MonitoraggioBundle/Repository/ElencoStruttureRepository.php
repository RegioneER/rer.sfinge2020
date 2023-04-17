<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 30/06/17
 * Time: 15:30
 */

namespace MonitoraggioBundle\Repository;


use Doctrine\ORM\EntityRepository;


/**
 * Description of ElencoTabelleContestoRepository
 *
 * @author lfontana
 */
class ElencoStruttureRepository extends EntityRepository {

    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\RicercaStruttura $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
            . 'from MonitoraggioBundle:ElencoStruttureProtocollo e '
            . "where e.descrizione like :descrizione "
            . "and e.codice like :codice "
            . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':descrizione', '%'.$ricerca->getDescrizione().'%' );
        $q->setParameter(':codice', '%'.$ricerca->getCodice().'%' );
        return $q;
    }
}
