<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ManualeRepository extends EntityRepository {

    public function cercaManuale($is_utente) {

        $dql = "SELECT man FROM SfingeBundle:Manuale man 
			    JOIN man.documento_file doc
			    JOIN doc.tipologia_documento t
			    WHERE 1=1 ";

        if ($is_utente) {
            $dql .= " AND (t.tipologia LIKE 'manuale_generico' OR t.tipologia LIKE 'manuale_ben') ";
        }
        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $manuali = $q->getResult();

        return $manuali;
    }

}
