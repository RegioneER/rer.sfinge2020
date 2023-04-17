<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PaginaRepository extends EntityRepository {

	public function getPagineFascicolo($id_fascicolo) {

		$query = "SELECT p
                FROM FascicoloBundle:Pagina p 
				JOIN p.fascicolo f WHERE f.id = {$id_fascicolo} ORDER BY p.id";

		$q = $this->getEntityManager()->createQuery($query);
		
		return $q->getResult();
	}
	
	public function getOrdinamentoSottoPaginaMaggiore($id_frammento) {

		$query = "SELECT MAX(p.ordinamento)
                FROM FascicoloBundle:Pagina p 
				JOIN p.frammentoContenitore f WHERE f.id = {$id_frammento}";

		$q = $this->getEntityManager()->createQuery($query);
		
		try{
			return $q->getSingleScalarResult();
		} catch (\Exception $ex) {
			return 0;
		}
	}
	
	public function getFrammentiOrdinati($id_pagina) {

		$query = "SELECT f
                FROM FascicoloBundle:Frammento f
				JOIN f.pagina p WHERE p.id= {$id_pagina} ORDER BY f.ordinamento DESC";

		$q = $this->getEntityManager()->createQuery($query);
		
		return $q->getResult();
	}
	
	public function getSottoPaginaOrdinamento($id_frammento,$ordinamento) {

		$query = "SELECT p
                FROM FascicoloBundle:Pagina p 
				JOIN p.frammentoContenitore f WHERE f.id = {$id_frammento} AND p.ordinamento = {$ordinamento}";

		$q = $this->getEntityManager()->createQuery($query);
		
		try{
			$result = $q->getSingleResult();
			return $result;
		} catch (\Exception $ex) {
			return null;
		}	
	}
	
	public function getPagineFascicoloAlias($alias, $fascicolo) {

		$query = "SELECT p
                FROM FascicoloBundle:Pagina p 
				JOIN p.fascicolo f WHERE p.alias = '{$alias}' AND p.frammentoContenitore IS NULL";
				
		if (!is_null($fascicolo->getId())) {
			$query .= " AND f.id != {$fascicolo->getId()}";
		}		

		$q = $this->getEntityManager()->createQuery($query);
		
		return $q->getResult();
	}
	
	public function getFascicoloAlias($alias) {

		$query = "SELECT p
                FROM FascicoloBundle:Pagina p 
				JOIN p.fascicolo f WHERE p.alias = '{$alias}' AND p.frammentoContenitore IS NULL";		

		$q = $this->getEntityManager()->createQuery($query);
		
		return $q->getResult();
	}

    public function getFascicoloPagina($alias, $frammentoContenitoreId)
    {
        $query = "SELECT p
                FROM FascicoloBundle:Pagina p 
				WHERE p.alias = '{$alias}' AND p.frammentoContenitore = {$frammentoContenitoreId}";
        $q = $this->getEntityManager()->createQuery($query);
        return $q->getResult();
    }

}
