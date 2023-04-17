<?php


namespace AttuazioneControlloBundle\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;

class DocumentoPagamentoRepository extends EntityRepository {


	public function findDocumentiCaricati($id_pagamento, array $arrayCodici = [])
	{
		$dql = "SELECT dp  FROM AttuazioneControlloBundle:DocumentoPagamento dp 
				JOIN dp.documento_file doc
				JOIN doc.tipologia_documento tipologia
				WHERE dp.pagamento = :id_pagamento ";

        if (!is_null($arrayCodici)) {
            $dql .= " AND tipologia.codice IN (:codici)";
        }

		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_pagamento", $id_pagamento);

        if (!is_null($arrayCodici)) {
            $q->setParameter('codici', [$arrayCodici], Connection::PARAM_STR_ARRAY);
        }

        $res = $q->getResult();

        return $res;
	}	
	
	public function findDocumentiAntimafia($id_pagamento) {

		$dql = "SELECT dp FROM AttuazioneControlloBundle:DocumentoPagamento dp "
				. "JOIN dp.documento_file df "
				. "JOIN df.tipologia_documento td "
				. "WHERE dp.pagamento = :id_pagamento AND td.tipologia = :tipologia"; 

		$q = $this->getEntityManager()->createQuery($dql);

		$q->setParameter("id_pagamento", $id_pagamento);
		$q->setParameter("tipologia", 'rendicontazione_antimafia_standard');

		$res = $q->getResult();

		return $res;
	}
    
    public function findDocumentiAntimafiaPersonalizzato($id_pagamento, $tipologia) {

		$dql = "SELECT dp FROM AttuazioneControlloBundle:DocumentoPagamento dp "
				. "JOIN dp.documento_file df "
				. "JOIN df.tipologia_documento td "
				. "WHERE dp.pagamento = :id_pagamento AND td.tipologia = :tipologia";

		$q = $this->getEntityManager()->createQuery($dql);

		$q->setParameter("id_pagamento", $id_pagamento);
		$q->setParameter("tipologia", $tipologia);

		$res = $q->getResult();

		return $res;
	}

	public function findDocumentiPagamento($id_pagamento, $escludiVideo = true)
    {
		if ($escludiVideo) {
            $dql = "SELECT dp FROM AttuazioneControlloBundle:DocumentoPagamento dp "
                . "JOIN dp.documento_file df "
                . "JOIN df.tipologia_documento td "
                . "WHERE dp.pagamento = :id_pagamento AND td.tipologia IN (:tipologia) AND td.codice != :codice_video_pagamento "
                . "ORDER BY td.descrizione, dp.data_creazione ASC";
        } else {
            $dql = "SELECT dp FROM AttuazioneControlloBundle:DocumentoPagamento dp "
                . "JOIN dp.documento_file df "
                . "JOIN df.tipologia_documento td "
                . "WHERE dp.pagamento = :id_pagamento AND td.tipologia IN (:tipologia) "
                . "ORDER BY td.descrizione, dp.data_creazione ASC";
        }

		$q = $this->getEntityManager()->createQuery($dql);

		$q->setParameter("id_pagamento", $id_pagamento);
		// aggiunta anche la tipologia "rendicontazione" per renderlo retroattivo con il pregresso
		$q->setParameter('tipologia', array('rendicontazione_documenti_progetto_standard', 'rendicontazione', 'rendicontazione_anticipi_standard'), \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);

        if ($escludiVideo) {
            // non mostro il video del pagamento perché viene mostrato nella sua apposita sezione
            $q->setParameter('codice_video_pagamento', 'VIDEO_PAGAMENTO');
        }

		$res = $q->getResult();
		return $res;
	}
}
