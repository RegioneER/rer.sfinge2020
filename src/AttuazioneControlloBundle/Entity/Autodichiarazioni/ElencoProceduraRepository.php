<?php

namespace AttuazioneControlloBundle\Entity\Autodichiarazioni;

use Doctrine\ORM\EntityRepository;


class ElencoProceduraRepository extends EntityRepository {

	public function getElenchiProceduraByPagamento($pagamento) {
		
		$procedura = $pagamento->getProcedura();
		$modalitaPagamento = $pagamento->getModalitaPagamento();
				
		$dql = "SELECT ep FROM AttuazioneControlloBundle:Autodichiarazioni\ElencoProcedura ep "
				. "JOIN ep.elenco e "
				. "JOIN e.elencoAutodichiarazioni ea "
				. "JOIN ea.autodichiarazione a "
				. "WHERE ep.procedura = :procedura ";			
       
		$whereModalitaPagamento = "AND ep.modalitaPagamento = :modalitaPagamento ";
		
		$em = $this->getEntityManager();
		
		/**
		 * cerco prima il caso specifico, ovvero vedo se sono state definite a db autodichiarazioni per la specifica modalita pagamento
		 * se non trovo risultati eseguo la stessa query escludendo la clausola relativa alla modalita pagamento, ovvero cerco
		 * autodichiarazioni valide per qualsiasi modalita pagamento della specifica procedura
		 */
        $querySpecifica = $em->createQuery(); 
		$querySpecifica->setDQL($dql . $whereModalitaPagamento);

		$querySpecifica->setParameter('procedura', $procedura->getId());
		$querySpecifica->setParameter('modalitaPagamento', $modalitaPagamento->getId());
		
		$res = $querySpecifica->getResult();
		if(count($res) > 0){
			return $res;
		}
		
		$whereModalitaPagamento = "AND ep.modalitaPagamento IS NULL ";
		
		$queryGenerica = $em->createQuery();
		$queryGenerica->setDQL($dql . $whereModalitaPagamento);
		
		$queryGenerica->setParameter('procedura', $procedura->getId());
		
		$res = $queryGenerica->getResult();
		if(count($res) > 0){
			return $res;
		}
		
		throw new \Exception('Non sono state definite autodichiarazioni per la procedura ' . $procedura->getId());       
    }   
 

}
