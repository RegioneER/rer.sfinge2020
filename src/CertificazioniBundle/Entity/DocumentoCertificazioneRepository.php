<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * DocumentoCertificazioneRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DocumentoCertificazioneRepository extends EntityRepository{	
	
	public function findDocumentiCaricati($id_certificazione, $tipo_doc)
	{	
		
		$dql = "SELECT dc FROM CertificazioniBundle:DocumentoCertificazione dc 
							JOIN dc.documento_file doc
							JOIN doc.tipologia_documento tipo
							WHERE dc.certificazione = :id_certificazione
							AND tipo.codice = :codice";

		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_certificazione", $id_certificazione);
		$q->setParameter("codice", $tipo_doc);
		
		$res = $q->getResult();
		
		return $res;
	}	
	
	public function findDocumentiCaricatiAgrea($id_certificazione)
	{	
		
		$dql = "SELECT dc FROM CertificazioniBundle:DocumentoCertificazione dc 
							JOIN dc.documento_file doc
							JOIN doc.tipologia_documento tipo
							WHERE dc.certificazione = :id_certificazione
							AND tipo.codice IN ('CHECKLIST_CERT','RELAZIONE_CERT','DOMANDA_PAG_CERT', 'ALTRO_CERT')";

		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_certificazione", $id_certificazione);
		
		$res = $q->getResult();
		
		return $res;
	}
	
}