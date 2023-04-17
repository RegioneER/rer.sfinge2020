<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;
use Doctrine\ORM\EntityRepository;
/**
 * Description of TC9TipoLivelloIstituzioneRepository
 *
 * @author lfontana
 */
class TC9TipoLivelloIstituzioneRepository extends EntityRepository{
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC9 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC9TipoLivelloIstituzione e '
                . "where e.liv_istituzione_str_fin like :liv_istituzione_str_fin "
                    . "and coalesce(e.descrizione_livello_istituzione, '') like :descrizione_livello_istituzione "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':liv_istituzione_str_fin', '%'.$ricerca->getLivIstituzioneStrFin().'%' );
        $q->setParameter(':descrizione_livello_istituzione', '%'.$ricerca->getDescrizioneLivelloIstituzione(). '%' );

        return $q;
    }
}
