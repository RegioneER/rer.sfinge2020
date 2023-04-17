<?php


namespace RichiesteBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DocumentoRichiestaRepository extends EntityRepository {
	
	public function findDocumentiCaricati($id_richiesta)
	{	
		
		$dql = "SELECT dr  FROM RichiesteBundle:DocumentoRichiesta dr 
							JOIN dr.documento_file doc
							WHERE dr.richiesta = :id_richiesta";

		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_richiesta", $id_richiesta);
		
		$res = $q->getResult();
		
		return $res;
	}	
	
	public function findDocumentiAvvioCaricati($id_richiesta)
	{	
		
		$dql = "SELECT dr  FROM RichiesteBundle:DocumentoRichiesta dr 
							JOIN dr.documento_file doc
							JOIN doc.tipologia_documento tipo
							WHERE dr.richiesta = :id_richiesta AND tipo.codice = 'AVVIO_PROGETTO' ";

		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_richiesta", $id_richiesta);
		
		$res = $q->getResult();
		
		return $res;
	}	
}
