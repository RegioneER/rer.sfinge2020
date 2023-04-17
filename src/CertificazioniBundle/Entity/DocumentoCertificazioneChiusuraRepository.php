<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DocumentoCertificazioneChiusuraRepository extends EntityRepository {

    public function findDocumentiCaricati($id_chiusura) {

        $dql = "SELECT dc FROM CertificazioniBundle:DocumentoCertificazioneChiusura dc 
							JOIN dc.documento_file doc
							JOIN doc.tipologia_documento tipo
							WHERE dc.chiusura = :id_chiusura
                                                        AND tipo.codice <> 'DOC_VALIDA_CHIU'";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_chiusura", $id_chiusura);

        $res = $q->getResult();

        return $res;
    }
    
     public function findDocumentiCaricatiCertificatore($id_chiusura) {

        $dql = "SELECT dc FROM CertificazioniBundle:DocumentoCertificazioneChiusura dc 
							JOIN dc.documento_file doc
							JOIN doc.tipologia_documento tipo
							WHERE dc.chiusura = :id_chiusura
                                                        AND tipo.codice = 'DOC_VALIDA_CHIU'";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_chiusura", $id_chiusura);

        $res = $q->getResult();

        return $res;
    }

}
