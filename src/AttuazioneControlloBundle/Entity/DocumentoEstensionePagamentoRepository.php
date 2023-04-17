<?php


namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DocumentoEstensionePagamentoRepository extends EntityRepository {
	
	public function findDocumentiPersonaliCaricati($id_estensione_pagamento)
	{	
		
		$dql = "SELECT dp  FROM AttuazioneControlloBundle:DocumentoEstensionePagamento dp 
							WHERE dp.estensione_pagamento = :id_estensione_pagamento
							AND dp.tipo = 'personale'
							ORDER BY dp.proponente ";

		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_estensione_pagamento", $id_estensione_pagamento);
		
		$res = $q->getResult();
		
		return $res;
	}	
	
	public function findDocumentiGeneraliCaricati($id_estensione_pagamento)
	{	
		
		$dql = "SELECT dp  FROM AttuazioneControlloBundle:DocumentoEstensionePagamento dp 
							WHERE dp.estensione_pagamento = :id_estensione_pagamento
							AND dp.tipo = 'generale'
							ORDER BY dp.proponente ";

		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_estensione_pagamento", $id_estensione_pagamento);
		
		$res = $q->getResult();
		
		return $res;
	}
}
