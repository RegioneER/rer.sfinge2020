<?php

namespace SoggettoBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class AllegatiAzioniAutoritaUrbaneRepository extends EntityRepository {

	public function findDocumentiCaricati($id_azione_au)
	{	
		
		$dql = "SELECT aaau FROM SoggettoBundle:AllegatiAzioniAutoritaUrbane aaau 
							JOIN aaau.documento_file doc
							WHERE aaau.azione_autorita_urbana = :id_azione_au";

		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_azione_au", $id_azione_au);
		
		$res = $q->getResult();
		
		return $res;
	}	
	
}
