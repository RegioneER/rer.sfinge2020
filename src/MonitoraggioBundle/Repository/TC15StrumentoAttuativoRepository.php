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
class TC15StrumentoAttuativoRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC15 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC15StrumentoAttuativo e '
                . "where e.cod_stru_att like :cod_stru_att "
                . "and coalesce(e.desc_strumento_attuativo, '') like :desc_strumento_attuativo "
                . "and coalesce(e.denom_resp_stru_att, '') like :denom_resp_stru_att "
                . "and coalesce(e.data_approv_stru_att, '9999-12-31') = coalesce(:data_approv_stru_att, e.data_approv_stru_att, '9999-12-31') "
                . "and coalesce(e.cod_tip_stru_att, '') like :cod_tip_stru_att "
                . "and coalesce(e.desc_tip_stru_att, '') like :desc_tip_stru_att "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_stru_att', '%'.$ricerca->getCodStruAtt().'%' );
        $q->setParameter(':desc_strumento_attuativo', '%'.$ricerca->getDescStrumentoAttuativo().'%' );
        $q->setParameter(':denom_resp_stru_att', '%'.$ricerca->getDenomRespStruAtt().'%' );
        $q->setParameter(':data_approv_stru_att', $ricerca->getDataApprovStruAtt() );
        $q->setParameter(':cod_tip_stru_att', '%'.$ricerca->getCodTipStruAtt().'%' );
        $q->setParameter(':desc_tip_stru_att', '%'.$ricerca->getDescTipStruAtt().'%' );

        return $q;
    }
}
