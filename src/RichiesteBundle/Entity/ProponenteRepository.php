<?php


namespace RichiesteBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProponenteRepository extends EntityRepository {
	
	public function getProponenteByAcronimoLaboratorio($richiesta, $acronimo_opzione_1, $acronimo_opzione_2 = '', $acronimo_opzione_3 = ''){
			
		//$acronimo_opzione_1 = str_replace(' ', '', strtolower($acronimo_opzione_1));
		//$acronimo_opzione_2 = str_replace(' ', '', strtolower($acronimo_opzione_2));
		//$acronimo_opzione_3 = str_replace(' ', '', strtolower($acronimo_opzione_3));
		
		$acronimo_opzione_1 = strtolower($acronimo_opzione_1);
		$acronimo_opzione_2 = strtolower($acronimo_opzione_2);
		$acronimo_opzione_3 = strtolower($acronimo_opzione_3);
		
		$richiesta_id = $richiesta->getId();
		
		$dql = "SELECT p FROM RichiesteBundle:Proponente p "
				. "JOIN p.richiesta r "
				. "JOIN p.soggetto s "
				. "WHERE r.id = $richiesta_id " 
				//                                   s.acronimo_laboratorio va anche lui lowerizzato
				. "AND (                             LOWER(s.acronimo_laboratorio) = '$acronimo_opzione_1' "
				. (($acronimo_opzione_2 != '') ? "OR LOWER(s.acronimo_laboratorio) = '$acronimo_opzione_2' " : "")
				. (($acronimo_opzione_3 != '') ? "OR LOWER(s.acronimo_laboratorio) = '$acronimo_opzione_3' " : "")
				. ")";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		//$res = $q->getResult();
		//return $res[0];
		//return $q->getSingleResult();
		return $q->getOneOrNullResult();
		
	}
    
    public function findAllSedeOperativa($proponente){
        $dql = "select sede "
                . "from SoggettoBundle:sede sede "
                . "join sede.sedeOperativa sedeOperativa "
                . "join sedeOperativa.proponente proponente "
                . "where proponente.mandatario = 1 "
                . "and proponente = :proponente ";
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter('proponente', $proponente);
        return $q->getResult();
    }
	
}
