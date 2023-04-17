<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\EntityRepository;
use SfingeBundle\Form\Entity\RicercaPermessiAsse;

class PermessiAsseRepository extends EntityRepository {
	
    public function cercaPermessiAsse(RicercaPermessiAsse $dati)
	{	

        $dql = "SELECT p FROM SfingeBundle:PermessiAsse p WHERE 1=1 ";
		$q = $this->getEntityManager()->createQuery();
		
		if (!\is_null($dati->getUtente())) {
            $dql .= " AND p.utente = :utente_id ";
            $q->setParameter(":utente_id", $dati->getUtente()->getId());
        }

        if (!\is_null($dati->getAsse())) {
            $dql .= " AND p.asse = :asse_id ";
            $q->setParameter(":asse_id", $dati->getAsse()->getId());
        }

        $q->setDQL($dql);
		$res = $q->getResult();
		
		return $res;
	}	

}
