<?php

/**
 * @author gdisparti
 */

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\EntityRepository;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento;

class ValutazioneChecklistPagamentoRepository extends EntityRepository {

	/**
	 * 
	 * @param Pagamento $pagamento
	 * @param string $tipologia
	 * @return ValutazioneChecklistPagamento[]
	 */
	public function getValutazioneChecklistByPagamento($pagamento, $tipologia = null){
		
		$where = '';
		if(!is_null($tipologia)){
			$where = "AND cp.tipologia = '{$tipologia}'";
		}
		
		$dql = "SELECT vc FROM AttuazioneControlloBundle:Istruttoria\ValutazioneChecklistPagamento vc "
				. "JOIN vc.checklist cp "
				. "WHERE vc.pagamento = {$pagamento->getId()} {$where}";
				
		$em = $this->getEntityManager();
		$query = $em->createQuery($dql);
		
		try{
			$result = $query->getSingleResult();
		}catch(\Doctrine\ORM\NoResultException $e){ // mi interessa tenere solo la NonUniqueResultException
			$result = null;
		}
		
		return $result;		
	}
	
	// prendiamo l'elenco di tutte le valutazioni checklist che non siano di tipo appalti
	public function getValutazioniChecklistGenericheByPagamento($pagamento){
		
		$dql = "SELECT vc FROM AttuazioneControlloBundle:Istruttoria\ValutazioneChecklistPagamento vc "
				. "JOIN vc.checklist cp "
				. "WHERE vc.pagamento = {$pagamento->getId()} AND cp.tipologia != :tipologiaAppalti";
				
		$em = $this->getEntityManager();
		
		$query = $em->createQuery($dql);		
		$query->setParameter('tipologiaAppalti', ChecklistPagamento::TIPOLOGIA_APPALTI_PUBBLICI);

		$result = $query->getResult();
				
		return $result;		
	}
	
	// prendiamo l'elenco di tutte le valutazioni checklist di tipo appalti
	public function getValutazioniChecklistAppaltiByPagamento($pagamento){
		
		$dql = "SELECT vc FROM AttuazioneControlloBundle:Istruttoria\ValutazioneChecklistPagamento vc "
				. "JOIN vc.checklist cp "
				. "WHERE vc.pagamento = {$pagamento->getId()} AND cp.tipologia = :tipologiaAppalti";
				
		$em = $this->getEntityManager();
		
		$query = $em->createQuery($dql);		
		$query->setParameter('tipologiaAppalti', ChecklistPagamento::TIPOLOGIA_APPALTI_PUBBLICI);

		$result = $query->getResult();
				
		return $result;		
	}

	public function getValutazioniIstanziate(Pagamento $pagamento, string $tipologia): array
	{
		$dql = "SELECT vc
				FROM AttuazioneControlloBundle:Istruttoria\ValutazioneChecklistPagamento vc
				INNER JOIN vc.checklist AS checklist
				INNER JOIN vc.pagamento as pagamento
				WHERE pagamento = :pagamento and checklist.tipologia = :tipologia
				";
		$res = $this->getEntityManager()
		->createQuery($dql)
		->setParameter('pagamento', $pagamento)
		->setParameter('tipologia', $tipologia)
		->getResult();

		return $res;
	}
}
