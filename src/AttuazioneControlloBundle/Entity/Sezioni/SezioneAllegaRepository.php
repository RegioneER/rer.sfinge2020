<?php

namespace AttuazioneControlloBundle\Entity\Sezioni;

use Doctrine\ORM\EntityRepository;


class SezioneAllegaRepository extends EntityRepository {

	public function getSezioneAllegaByPagamento($pagamento) {
		
		$procedura = $pagamento->getProcedura();
		$modalitaPagamento = $pagamento->getModalitaPagamento();
				
		$dql = "SELECT sa FROM AttuazioneControlloBundle:Sezioni\SezioneAllega sa "
				. "WHERE sa.procedura = :procedura ";			
       
		$whereModalitaPagamento = "AND sa.modalitaPagamento = :modalitaPagamento ";
		
		$em = $this->getEntityManager();
		
		/**
		 * cerco prima il caso specifico, ovvero vedo se è stato definito qualcosa per la specifica modalita pagamento
		 * se non trovo risultati eseguo la stessa query escludendo la clausola relativa alla modalita pagamento
		 */
        $querySpecifica = $em->createQuery(); 
		$querySpecifica->setDQL($dql . $whereModalitaPagamento);

		$querySpecifica->setParameter('procedura', $procedura->getId());
		$querySpecifica->setParameter('modalitaPagamento', $modalitaPagamento->getId());
		
		$res = $querySpecifica->getResult();
		if(count($res) > 0){
			return $res;
		}
		
		$whereModalitaPagamento = "AND sa.modalitaPagamento IS NULL ";
		
		$queryGenerica = $em->createQuery();
		$queryGenerica->setDQL($dql . $whereModalitaPagamento);
		
		$queryGenerica->setParameter('procedura', $procedura->getId());
		
		$res = $queryGenerica->getResult();
		if(count($res) > 0){
			return $res;
		}
		
		throw new \Exception('Non è stata definita la sezione allega del pdf per la procedura ' . $procedura->getId());       
    }   
 

}
