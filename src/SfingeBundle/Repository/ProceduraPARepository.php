<?php

namespace SfingeBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\AbstractQuery;

class ProceduraPARepository extends EntityRepository
{
    public function getProcedureVisibiliPA($utente)
	{

		$dql = "SELECT proc FROM SfingeBundle:ProceduraPA proc "
				. "LEFT JOIN proc.stato_procedura proc_s "
				. "JOIN proc.asse asse "
				. "WHERE 1=1 ";

		$q = $this->getEntityManager()->createQuery();

		if (!$utente->hasRole("ROLE_SUPER_ADMIN") && !$utente->hasRole("ROLE_GESTIONE_INGEGNERIA_FINANZIARIA") && !$utente->hasRole("ROLE_GESTIONE_ASSISTENZA_TECNICA")) {

			if (!$utente->hasRole("ROLE_ADMIN_PA")) {
					$dql .= " AND ( ";
					$dql .= "proc.id in (select proc3.id from SfingeBundle:PermessiProcedura proc2 join proc2.procedura proc3 where proc2.utente={$utente->getId()}) ";
					$dql .= "OR proc.asse in (select asse3.id from SfingeBundle:PermessiAsse asse2 join asse2.asse asse3 where asse2.utente={$utente->getId()}))";
			}
		}

		$q->setDQL($dql);

		return $q->getResult();
	}
}
