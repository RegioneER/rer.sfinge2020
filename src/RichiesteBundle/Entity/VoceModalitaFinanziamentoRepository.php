<?php

namespace RichiesteBundle\Entity;

use Doctrine\ORM\EntityRepository;

class VoceModalitaFinanziamentoRepository extends EntityRepository {
	
		public function getVociDaProponenteRichiesta($id_proponente, $id_richiesta) {

		$dql = "SELECT voce FROM RichiesteBundle:VoceModalitaFinanziamento voce "
				. "JOIN voce.proponente prop "
				. "JOIN voce.richiesta rich "
				. "JOIN voce.modalita_finanziamento mod "
				. "WHERE prop.id = $id_proponente AND rich.id = $id_richiesta";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
}
