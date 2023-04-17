<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\EntityRepository;
use SfingeBundle\Form\Entity\RicercaPermessiProcedura;

class PermessiProceduraRepository extends EntityRepository {
	
    public function cercaPermessiProcedura(RicercaPermessiProcedura $dati)
	{	

        $dql = "SELECT p FROM SfingeBundle:PermessiProcedura p WHERE 1=1 ";
		$q = $this->getEntityManager()->createQuery();

		if (!\is_null($dati->getUtente())) {
            $dql .= " AND p.utente = :utente_id ";
            $q->setParameter(":utente_id", $dati->getUtente()->getId());
        }

        if (!\is_null($dati->getProcedura())) {
            $dql .= " AND p.procedura = :procedura_id ";
            $q->setParameter(":procedura_id", $dati->getProcedura()->getId());
        }

        $q->setDQL($dql);
		$res = $q->getResult();
		
		return $res;
	}	

}
