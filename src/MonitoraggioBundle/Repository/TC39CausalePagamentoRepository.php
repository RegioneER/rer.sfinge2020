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
class TC39CausalePagamentoRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC39 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC39CausalePagamento  e '
                . "where e.causale_pagamento like :causale_pagamento "
                . "and coalesce(e.descrizione_causale_pagamento, '') like :descrizione_causale_pagamento "
                . "and coalesce(e.tipologia_pagamento, '') like :tipologia_pagamento "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':causale_pagamento', '%'.$ricerca->getCausalePagamento().'%' );
        $q->setParameter(':descrizione_causale_pagamento', '%'.$ricerca->getDescrizioneCausalePagamento().'%' );
        $q->setParameter(':tipologia_pagamento', '%'.$ricerca->getTipologiaPagamento().'%' );


        return $q;
    }
}
