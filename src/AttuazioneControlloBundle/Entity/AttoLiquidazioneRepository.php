<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AttoLiquidazioneRepository extends EntityRepository {
	
	 public function cercaAttoLiquidazione(\AttuazioneControlloBundle\Form\Entity\RicercaAttoLiquidazione $dati){
        
        $dql = "SELECT a FROM AttuazioneControlloBundle:AttoLiquidazione a WHERE 1=1 ";
        $q = $this->getEntityManager()->createQuery();

         if ($dati->getNumero() != "") {
            $dql .= " AND a.numero LIKE :numero";
            $q->setParameter(":numero", "%" . $dati->getNumero() . "%");
        }

        if ($dati->getDescrizione() != "") {
            $dql .= " AND a.descrizione LIKE :descrizione";
            $q->setParameter(":descrizione", "%" . $dati->getDescrizione() . "%");
        }
        
        if ($dati->getAsse() != "" && !is_null($dati->getAsse())) {
            $dql .= " AND a.asse = :asse";
            $q->setParameter(":asse",  $dati->getAsse());
        }

        if ($dati->getDataAttoDa() != "" && !is_null($dati->getDataAttoDa())) {
            $dql .= " AND a.data >= :data_atto_da";
            $q->setParameter(":data_atto_da",  $dati->getDataAttoDa());
        }

        if ($dati->getDataAttoA() != "" && !is_null($dati->getDataAttoA())) {
            $dql .= " AND a.data <= :data_atto_a";
            $q->setParameter(":data_atto_a",  $dati->getDataAttoA());
        }

        $dql .= " ORDER BY a.data DESC ";

         $q->setDQL($dql);
        return $q;
    }

}
