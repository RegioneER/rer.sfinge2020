<?php
namespace IstruttorieBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use IstruttorieBundle\Form\Entity\RicercaPropostaImpegno;

class PropostaImpegnoRepository extends EntityRepository
{
    /**
     * @param RicercaPropostaImpegno $ricercaPropostaImpegno
     * @return Query
     */
    public function getProposteImpegnoByProcedura(RicercaPropostaImpegno $ricercaPropostaImpegno): Query
    {
        $dql = 'SELECT propostaImpegno FROM IstruttorieBundle:PropostaImpegno propostaImpegno';
        $procedura = $ricercaPropostaImpegno->getProcedura();

        $q = $this->getEntityManager()->createQuery();
        if ($procedura) {
            $dql .= ' WHERE propostaImpegno.procedura = :procedura_id';
            $q->setParameter('procedura_id', $procedura);
        }

        $q->setDQL($dql);
        return $q;
    }
}
