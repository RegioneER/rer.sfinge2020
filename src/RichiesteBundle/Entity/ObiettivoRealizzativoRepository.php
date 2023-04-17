<?php


namespace RichiesteBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ObiettivoRealizzativoRepository extends EntityRepository {
	
    public function getObiettiviByRichiestaOrdinatiCodice($id_richiesta) {

		$dql = "SELECT ob FROM RichiesteBundle:ObiettivoRealizzativo ob "
				. "JOIN ob.richiesta rich "
				. "WHERE rich.id = $id_richiesta "
                . "ORDER BY ob.codice_or ASC ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
}
