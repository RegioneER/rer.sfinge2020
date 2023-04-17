<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\EntityRepository;
use AttuazioneControlloBundle\Entity\StatoVariazione;
use SfingeBundle\Entity\Procedura;

class VocePianoCostoGiustificativoRepository extends EntityRepository {
	
	public function getVociPianoCostoGiustificativoNonAmmesse($pagamento, $pianoCosto){
		
		$dql = "SELECT v, g,tg FROM AttuazioneControlloBundle:VocePianoCostoGiustificativo v "
				. "JOIN v.giustificativo_pagamento g "
				. "LEFT JOIN g.tipologia_giustificativo tg "
				. "JOIN v.voce_piano_costo vpc "
				. "WHERE g.pagamento = {$pagamento->getId()} AND vpc.piano_costo = {$pianoCosto->getId()} "
				. "AND v.importo_approvato IS NOT NULL AND v.importo_approvato != v.importo ";
				
		$query = $this->getEntityManager()->createQuery($dql);
		return $query->getResult();
	}
	
	public function getImportiVociPianoCostoGiustificativo($pagamento, $codiceVoce, $sezioneVoce, $proponente){
		
		$dql = "SELECT SUM(COALESCE(v.importo,0)) as importo, SUM(COALESCE(v.importo_approvato,0)) as importo_approvato "
				. "FROM AttuazioneControlloBundle:VocePianoCostoGiustificativo v "
				. "JOIN v.giustificativo_pagamento g "
				. "LEFT JOIN g.tipologia_giustificativo tg "
				. "JOIN v.voce_piano_costo vpc "
				. "JOIN vpc.piano_costo pc "
				. "JOIN pc.sezione_piano_costo sez "
				. "WHERE g.pagamento = {$pagamento->getId()} AND pc.codice = '$codiceVoce' AND sez.codice = '{$sezioneVoce}' AND vpc.proponente = {$proponente->getId()} ";
				//. "WHERE g.pagamento = {$pagamento->getId()} AND pc.codice = '$codiceVoce' ";
				
		$query = $this->getEntityManager()->createQuery($dql);
		$res = $query->getResult();
		return $res[0];
	}
	
	public function getGiustificativoDaVoce($pagamento, $codiceVoce, $sezioneVoce, $proponente = null){
		
		$dql = "SELECT g "
				. "FROM AttuazioneControlloBundle:GiustificativoPagamento g "
				. "JOIN g.voci_piano_costo v "
				. "JOIN v.voce_piano_costo vpc ";
				
		if(!is_null($proponente)) {
			$dql .= "JOIN vpc.proponente prop ";
		}
		
		$dql .=	  "JOIN vpc.piano_costo pc "
				. "JOIN pc.sezione_piano_costo sez "
				. "WHERE g.pagamento = {$pagamento->getId()} AND pc.codice = '$codiceVoce' AND sez.codice = '$sezioneVoce'";
				
		if(!is_null($proponente)) {
			$dql .= " AND prop.id = {$proponente->getId()} ";
		}
				
		$query = $this->getEntityManager()->createQuery($dql);
		return $query->getResult();
	}
	
	public function getVociPianoCostoGiustificativoIstruite($pagamento, $pianoCosto){
		
		$dql = "SELECT v, g,tg FROM AttuazioneControlloBundle:VocePianoCostoGiustificativo v "
				. "JOIN v.giustificativo_pagamento g "
				. "LEFT JOIN g.tipologia_giustificativo tg "
				. "JOIN v.voce_piano_costo vpc "
				. "WHERE g.pagamento = {$pagamento->getId()} AND vpc.piano_costo = {$pianoCosto->getId()} "
				. "AND v.importo_approvato IS NOT NULL";
				
		$query = $this->getEntityManager()->createQuery($dql);
		return $query->getResult();
	}
	
	public function getRendicontatoAmmessoENonAmmesso($pagamento){
		
		$dql = "SELECT SUM(COALESCE(v.importo,0)) as rendicontato, SUM(COALESCE(v.importo_approvato,0)) as rendicontato_ammesso "
				. "FROM AttuazioneControlloBundle:VocePianoCostoGiustificativo v "
				. "JOIN v.giustificativo_pagamento g "
				. "JOIN v.voce_piano_costo vpc "
				. "WHERE g.pagamento = {$pagamento->getId()} ";
				
		$query = $this->getEntityManager()->createQuery($dql);
		$res = $query->getResult();
		return $res[0];
	}
	
	public function getRendicontatoAmmessoENonAmmessoDaPianoCosto($pagamento, $codice){
		
		$dql = "SELECT SUM(COALESCE(v.importo,0)) as rendicontato, "
				. "SUM(COALESCE(v.importo_approvato,0)) as rendicontato_ammesso, "
				. "SUM(COALESCE(v.importo,0) - COALESCE(v.importo_approvato,0)) as rendicontato_non_ammesso "
				. "FROM AttuazioneControlloBundle:VocePianoCostoGiustificativo v "
				. "JOIN v.giustificativo_pagamento g "
				. "JOIN v.voce_piano_costo vpc "
				. "JOIN vpc.piano_costo pc "
				. "WHERE g.pagamento = {$pagamento->getId()} AND pc.codice = '$codice' ";
				
		$query = $this->getEntityManager()->createQuery($dql);
		$res = $query->getResult();
		return $res[0];
	}
	
}
