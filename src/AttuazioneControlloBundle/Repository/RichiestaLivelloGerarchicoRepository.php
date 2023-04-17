<?php

namespace AttuazioneControlloBundle\Repository;

use Doctrine\ORM\EntityRepository;

class RichiestaLivelloGerarchicoRepository extends EntityRepository {
    /**
     * funzione che dato un livello gerarchico(TC36) e una Richiesta recupera le righe in richieste_livelli_gerarchici
     */
    public function getRichiestaLivelloGerarchicoByTc36Richiesta($livelloGerarchicoId, $richiestaId) {
        $dql = "SELECT rlg "
                . "FROM AttuazioneControlloBundle:RichiestaLivelloGerarchico rlg "
                . "JOIN rlg.tc36_livello_gerarchico tc36 "
                . "JOIN rlg.richiesta_programma rp "
                . "JOIN rp.tc4_programma tc4 "
                . "JOIN rp.richiesta r "
                . "WHERE "
                . "tc36.id = :livelloGerarchicoId "
                . "AND r.id = :richiestaId "
                . "AND tc4.cod_programma = :codiceProgramma ";

        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":livelloGerarchicoId", $livelloGerarchicoId);
        $q->setParameter(":richiestaId", $richiestaId);
        $q->setParameter(":codiceProgramma", "2014IT16RFOP008");

        $result = $q->getSingleResult();
        return $result;
    }
}
