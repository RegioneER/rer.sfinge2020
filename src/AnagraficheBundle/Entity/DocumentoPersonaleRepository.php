<?php

namespace AnagraficheBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DocumentoPersonaleRepository extends EntityRepository
{
    /**
     * @param int $id_personale
     * @return array
     */
    public function findDocumentiCaricati(int $id_personale)
    {
        $dql = "SELECT dr 
                FROM AnagraficheBundle:DocumentoPersonale dr 
                JOIN dr.documento_file doc
                WHERE dr.personale = :id_personale";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_personale", $id_personale);
        
        $res = $q->getResult();
        return $res;
    }

    /**
     * @param Personale $personale
     * @param string $tipologia
     * @param string $codice
     * @return array
     */
    public function findDocumentoPerPersonaECodice(Personale $personale, string $tipologia, string $codice)
    {
        $dql = "SELECT dp  
                FROM AnagraficheBundle:DocumentoPersonale dp 
                JOIN dp.documento_file doc
                JOIN doc.tipologia_documento tipologiaDoc
                WHERE dp.personale = :id_personale AND tipologiaDoc.tipologia = :tipologia AND tipologiaDoc.codice = :codice";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_personale", $personale->getId());
        $q->setParameter("tipologia", $tipologia);
        $q->setParameter("codice", $codice);

        $res = $q->getResult();
        return $res;
    }
}
