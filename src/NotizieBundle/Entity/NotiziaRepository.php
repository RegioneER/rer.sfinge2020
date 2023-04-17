<?php

namespace NotizieBundle\Entity;

use Doctrine\ORM\EntityRepository;
use NotizieBundle\Form\RicercaNotizieType;
use NotizieBundle\Form\Entity\RicercaNotiziaAdmin;

class NotiziaRepository extends EntityRepository {

	public function getNotizieRuoli ($ruoli, array $idProcedureOperative = NULL){
		$dql = "SELECT n FROM NotizieBundle:Notizia n WHERE n.dataInizioVisualizzazione <= CURRENT_TIMESTAMP() AND n.dataFineVisualizzazione >= CURRENT_TIMESTAMP()";
		
		$first=true;
		foreach ($ruoli as $ruolo) {
			if($first){
				$dql = $dql." AND (n.visibilita LIKE '%\"$ruolo\"%' ";
				$first = false;
			}else{
				$dql = $dql." OR n.visibilita LIKE '%\"$ruolo\"%' ";
			}

		}
		if(!$first){
			$dql .=")";
		}
        //Commento perchè non può funzionare tenuto conto che in fase di login non si sa chi sia il soggetto 
        //e in ogni caso non ha senso filtrare per bando essendo notizie per tutti
		//Se ci sono notizie relative alle richieste create dall'utente, le mostro
		/*if(!is_null($idProcedureOperative) && !empty($idProcedureOperative)){
			$procedure = implode(",", $idProcedureOperative);
			$dql = $dql." AND (n.procedura is NULL OR n.procedura IN ($procedure)) ";
		}*/
		$dql .=" ORDER BY n.dataInserimento DESC ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function cercaNotizie(RicercaNotiziaAdmin $dati) {
		$dql = "SELECT n from NotizieBundle:Notizia n where 1=1";

		$q = $this->getEntityManager()->createQuery();

		if (!\is_null($dati->getTitolo())) {
			$dql .= " AND n.titolo LIKE :titolo ";
			$q->setParameter(":titolo", "%" . $dati->getTitolo() . "%");
		}

		if (!\is_null($dati->getTesto())) {
			$dql .= " AND n.testo LIKE :testo ";
			$q->setParameter(":testo", "%" . $dati->getTesto() . "%");
		}

		if (!\is_null($dati->getVisibilita())) {
			$dql .= " AND n.visibilita LIKE :visibilita ";
			$q->setParameter(":visibilita", "%" . $dati->getVisibilita() . "%");
		}

		$dql .= " ORDER BY n.dataFineVisualizzazione DESC";

		$q->setDQL($dql);

		return $q;

	}
}