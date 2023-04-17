<?php


namespace RichiesteBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DocumentoProponenteRepository extends EntityRepository {
	
	public function findDocumentiCaricati($id_proponente)
	{	
		$dql = "SELECT dp  FROM RichiesteBundle:DocumentoProponente dp 
							JOIN dp.documento_file doc
							WHERE dp.proponente = :id_proponente ";

		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_proponente", $id_proponente);
		
		$res = $q->getResult();
		
		return $res;
	}	
}
