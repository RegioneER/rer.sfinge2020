<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\EntityRepository;

class FrammentoRepository extends EntityRepository {

	public function getFrammentiPagina($id_pagina) {

		$query = "SELECT f
                FROM FascicoloBundle:Frammento f 
				JOIN f.pagina p WHERE p.id = {$id_pagina} ORDER BY f.id";

		$q = $this->getEntityManager()->createQuery($query);
		
		return $q->getResult();
	}
	
	public function getOrdinamentoMaggiore($id_pagina) {

		$query = "SELECT MAX(f.ordinamento)
                FROM FascicoloBundle:Frammento f 
				JOIN f.pagina p WHERE p.id = {$id_pagina} ";

		$q = $this->getEntityManager()->createQuery($query);
		
		try{
			return $q->getSingleScalarResult();
		} catch (\Exception $ex) {
			return 0;
		}		
	}
	
	public function getSottoPagineOrdinate($id_frammento) {

		$query = "SELECT p
                FROM FascicoloBundle:Pagina p
				JOIN p.frammentoContenitore f WHERE f.id= {$id_frammento} ORDER BY p.ordinamento DESC";

		$q = $this->getEntityManager()->createQuery($query);
		
		return $q->getResult();
	}
	
	public function getFrammentoOrdinamento($id_pagina,$ordinamento) {

		$query = "SELECT f
                FROM FascicoloBundle:Frammento f 
				JOIN f.pagina p WHERE p.id = {$id_pagina} AND f.ordinamento = {$ordinamento}";

		$q = $this->getEntityManager()->createQuery($query);
		
		try{
			$result = $q->getSingleResult();
			return $result;
		} catch (\Exception $ex) {
			return null;
		}		
	}

}
