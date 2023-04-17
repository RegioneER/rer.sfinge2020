<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DocumentoContrattoRepository extends EntityRepository {

    public function findDocumentiCaricati($id_contratto) {

        $dql = "SELECT dp  FROM AttuazioneControlloBundle:DocumentoContratto dp 
							JOIN dp.documentoFile doc
							WHERE dp.contratto = :id_contratto";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_contratto", $id_contratto);

        $res = $q->getResult();

        return $res;
    }

}
