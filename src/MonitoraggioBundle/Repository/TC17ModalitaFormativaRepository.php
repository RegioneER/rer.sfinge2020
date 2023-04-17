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
class TC17ModalitaFormativaRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC17 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC17ModalitaFormativa e '
                . "where e.cod_modalita_formativa like :cod_modalita_formativa "
                . "and coalesce(e.descrizione_modalita_formativa_sottoclasse, '') like :descrizione_modalita_formativa_sottoclasse "
                . "and coalesce(e.descrizione_classe, '') like :descrizione_classe "
                . "and coalesce(e.descrizione_macro_categoria, '') like :descrizione_macro_categoria "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_modalita_formativa', '%'.$ricerca->getCodModalitaFormativa().'%' );
        $q->setParameter(':descrizione_modalita_formativa_sottoclasse', '%'.$ricerca->getDescrizioneModalitaFormativaSottoclasse().'%' );
        $q->setParameter(':descrizione_classe', '%'.$ricerca->getDescrizioneClasse().'%' );
        $q->setParameter(':descrizione_macro_categoria', '%'.$ricerca->getDescrizioneMacroCategoria().'%' );


        return $q;
    }
}
