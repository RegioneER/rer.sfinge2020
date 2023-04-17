<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ImportoIrapRepository extends EntityRepository
{
    /**
     * @param string $codice_fiscale
     * @return array
     */
    public function getImportoIrapImportato(string $codice_fiscale)
    {
        $dql = "SELECT importo "
            . "FROM IstruttorieBundle:ImportoIrap importo "
            . "WHERE importo.codice_fiscale = :codice_fiscale";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("codice_fiscale", $codice_fiscale);
        return $q->getResult();
    }
}
