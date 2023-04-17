<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\EntityRepository;
use SfingeBundle\Form\Entity\RicercaAtto;

class AttoRepository extends EntityRepository {
	
	 public function cercaAtto(RicercaAtto $dati){
        
        $dql = "SELECT a FROM SfingeBundle:Atto a WHERE 1=1 ";
        $q = $this->getEntityManager()->createQuery();

         if ($dati->getNumero() != "") {
            $dql .= " AND a.numero LIKE :numero";
            $q->setParameter(":numero", "%" . $dati->getNumero() . "%");
        }

        if ($dati->getTitolo() != "") {
            $dql .= " AND a.titolo LIKE :titolo";
            $q->setParameter(":titolo", "%" . $dati->getTitolo() . "%");
        }

        $q->setDQL($dql);
        return $q;
    }

}
