<?php

namespace RichiesteBundle\Entity\Autodichiarazioni;

use Doctrine\ORM\EntityRepository;
use Exception;
use RichiesteBundle\Entity\Richiesta;


class ElencoProceduraRichiestaRepository extends EntityRepository
{
    /**
     * @param Richiesta $richiesta
     * @return array
     * @throws Exception
     */
    public function getElenchiProceduraByRichiesta($richiesta) {
        $procedura = $richiesta->getProcedura();
        $dql = "SELECT ep FROM RichiesteBundle:Autodichiarazioni\ElencoProceduraRichiesta ep "
                . "JOIN ep.elenco e "
                . "JOIN e.elencoAutodichiarazioni ea "
                . "JOIN ea.autodichiarazione a "
                . "WHERE ep.procedura = :procedura ";
            
        $em = $this->getEntityManager();

        $query = $em->createQuery(); 
        $query->setDQL($dql);

        $query->setParameter('procedura', $procedura->getId());
        
        $res = $query->getResult();
        if (count($res) > 0) {
            return $res;
        }
        
        throw new Exception('Non sono state definite autodichiarazioni per la procedura ' . $procedura->getId());       
    }

    /**
     * @param $etichetta
     * @return array
     * @throws Exception
     */
    public function getElenchiProceduraByEtichetta($etichetta) {
        $dql = "SELECT ep FROM RichiesteBundle:Autodichiarazioni\ElencoProceduraRichiesta ep "
            . "JOIN ep.elenco e "
            . "JOIN e.elencoAutodichiarazioni ea "
            . "JOIN ea.autodichiarazione a "
            . "WHERE e.etichetta = :etichetta ";

        $em = $this->getEntityManager();
        $query = $em->createQuery();
        $query->setDQL($dql);
        $query->setParameter('etichetta', $etichetta);

        $res = $query->getResult();
        if (count($res) > 0) {
            return $res;
        }

        throw new Exception('Non sono state definite autodichiarazioni per l\'etichetta ' . $etichetta);
    }
}
