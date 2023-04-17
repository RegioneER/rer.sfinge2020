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
class DocumentoCertificazionePagamentoRepository extends EntityRepository{	
	
	public function findDocumentiCaricati($id_pagamento_certificazione)
	{	
		
		$dql = "SELECT dp FROM CertificazioniBundle:DocumentoCertificazionePagamento dp 
							JOIN dp.documento_file doc
							WHERE dp.certificazione_pagamento = :id_pagamento_certificazione";

		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_pagamento_certificazione", $id_pagamento_certificazione);
		
		$res = $q->getResult();
		
		return $res;
	}	
	
}
