<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CampoRepository extends EntityRepository {

	public function getCampiFrammento($id_frammento) {

		$query = "SELECT c
                FROM FascicoloBundle:Campo c
				JOIN c.frammento f WHERE f.id = {$id_frammento} ORDER BY f.id";

		$q = $this->getEntityManager()->createQuery($query);
		
		return $q->getResult();
	}
	
	public function getOrdinamentoMaggiore($id_frammento) {

		$query = "SELECT MAX(c.ordinamento)
                FROM FascicoloBundle:Campo c
				JOIN c.frammento f WHERE f.id = {$id_frammento}";

		$q = $this->getEntityManager()->createQuery($query);
		
		try {
			return $q->getSingleScalarResult();
		} catch (\Exception $ex) {
			return 0;
		}
	}
	
	public function getCampoOrdinamento($id_frammento,$ordinamento) {

		$query = "SELECT c
                FROM FascicoloBundle:Campo c 
				JOIN c.frammento f WHERE f.id = {$id_frammento} AND c.ordinamento = {$ordinamento}";

		$q = $this->getEntityManager()->createQuery($query);
		
		try{
			$result = $q->getSingleResult();
			return $result;
		} catch (\Exception $ex) {
			return null;
		}		
	}

}
