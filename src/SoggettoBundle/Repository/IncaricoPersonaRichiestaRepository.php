<?php

namespace SoggettoBundle\Repository;

use AnagraficheBundle\Entity\Persona;
use Doctrine\ORM\EntityRepository;
use RichiesteBundle\Entity\Richiesta;
use SoggettoBundle\Entity\StatoIncarico;

class IncaricoPersonaRichiestaRepository extends EntityRepository {
    public function getRichiesteIncaricato(Richiesta $richiesta, Persona $persona): array {
        $dql = "SELECT rich.id from RichiesteBundle:Richiesta rich
                JOIN rich.incarichi_richiesta inch_rich
                JOIN inch_rich.incarico_persona inch
                JOIN inch.incaricato pers
                JOIN inch.stato si
                WHERE si.codice = :statoAttivo AND rich.id = :id_richiesta AND pers.id = :id_persona";

        $q = $this->getEntityManager()->createQuery($dql)
        ->setParameter("statoAttivo", StatoIncarico::ATTIVO)
        ->setParameter("id_richiesta", $richiesta->getId())
        ->setParameter("id_persona", $persona->getId());
        
        $res = $q->getResult();


        $arrayRes = \array_map(function(array $record){
            return $record['id'];
        }, $res);

        return $arrayRes;
    }
}
