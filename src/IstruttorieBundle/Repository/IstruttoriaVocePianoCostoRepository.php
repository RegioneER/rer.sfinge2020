<?php

namespace IstruttorieBundle\Repository;

use Doctrine\ORM\EntityRepository;
use RichiesteBundle\Entity\Richiesta;

class IstruttoriaVocePianoCostoRepository extends EntityRepository {
    public function getAmmessoTotaleRichiestaSezione(Richiesta $richiesta, string $codiceSezione): float {
        $dql = "SELECT SUM(COALESCE(voce_istruttoria.importo_ammissibile_anno_1,0)) +
                        SUM(COALESCE(voce_istruttoria.importo_ammissibile_anno_2,0)) + 
                        SUM(COALESCE(voce_istruttoria.importo_ammissibile_anno_3,0)) + 
                        SUM(COALESCE(voce_istruttoria.importo_ammissibile_anno_4,0)) + 
                        SUM(COALESCE(voce_istruttoria.importo_ammissibile_anno_5,0)) + 
                        SUM(COALESCE(voce_istruttoria.importo_ammissibile_anno_6,0)) + 
                        SUM(COALESCE(voce_istruttoria.importo_ammissibile_anno_7,0)) 
        FROM IstruttorieBundle:IstruttoriaVocePianoCosto voce_istruttoria
        INNER JOIN voce_istruttoria.voce_piano_costo voce_piano_costo
        INNER JOIN voce_piano_costo.richiesta richiesta 
        INNER JOIN voce_piano_costo.piano_costo piano 
        INNER JOIN piano.sezione_piano_costo sez 
        WHERE richiesta = :richiesta AND piano.codice = 'TOT' AND sez.codice = :sezione";

        $q = $this->getEntityManager()
                    ->createQuery($dql)
                    ->setParameter('richiesta', $richiesta)
                    ->setParameter('sezione', $codiceSezione);

        return \floatval($q->getSingleScalarResult());
    }
}
