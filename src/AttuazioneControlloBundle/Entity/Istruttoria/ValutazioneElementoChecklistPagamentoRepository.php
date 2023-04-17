<?php

/**
 * @author gdisparti
 */

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\EntityRepository;
use AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamento;

class ValutazioneElementoChecklistPagamentoRepository extends EntityRepository {

	/**
	 * 
	 * @param ValutazioneChecklistPagamento $valutazioneChecklist
	 * @param string $codice
	 * @return ValutazioneElementoChecklistPagamento
	 */
	public function getValutazioneElementoByCodice($valutazioneChecklist, $codice){
		
		$dql = "SELECT ve FROM AttuazioneControlloBundle:Istruttoria\ValutazioneElementoChecklistPagamento ve "
				. "JOIN ve.valutazione_checklist vc "
				. "JOIN ve.elemento e "
				. "WHERE vc.id = {$valutazioneChecklist->getId()} AND e.codice = '{$codice}'";
				
		$em = $this->getEntityManager();
		$query = $em->createQuery($dql);
		$result = $query->getOneOrNullResult();
		
		return $result;		
	}
	
}
