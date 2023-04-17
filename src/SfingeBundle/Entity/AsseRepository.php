<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\EntityRepository;
use SfingeBundle\Form\Entity\RicercaAtto;

class AsseRepository extends EntityRepository {
	
	 public function getAssi(Utente $utente, $filtro_assi = null){
        
        $dql = "SELECT a "
				. "FROM SfingeBundle:Asse a "
				. "LEFT JOIN a.permessi p "
				. "WHERE 1=1 ";
		
        $q = $this->getEntityManager()->createQuery();

        if (!$utente->hasRole("ROLE_ADMIN_PA") && !$utente->hasRole("ROLE_SUPER_ADMIN")) {
            $dql .= " AND p.id is not null AND p.utente=:utente";
            $q->setParameter(":utente", $utente->getId());
        }
        
        if (!is_null($filtro_assi)) {
            $dql .= " AND a.codice IN ('".  implode("', '", $filtro_assi) . "')";      
        }

		$dql .= " ORDER BY a.id ASC ";
		
        $q->setDQL($dql);
        return $q->getResult();
    }
	
	 public function getAssiConAutoritaUrbane(){
        
        $dql = "SELECT DISTINCT a "
				. "FROM SfingeBundle:Asse a "
				. "JOIN a.autorita_urbana au "
				. "ORDER BY a.id ASC";
		
        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        return $q->getResult();
    }	

}
