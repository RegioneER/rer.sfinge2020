<?php


namespace MonitoraggioBundle\Repository;
use Doctrine\ORM\EntityRepository;
use CipeBundle\Entity\Classificazioni\CupNatura;
use MonitoraggioBundle\Entity\TC46FaseProcedurale;

class TC46FaseProceduraleRepository extends EntityRepository{

     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC46 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC46FaseProcedurale  e '
                . "where e.cod_fase like :cod_fase "
                . "and coalesce(e.descrizione_fase, '') like :descrizione_fase "
                . "and coalesce(e.codice_natura_cup, '') like :codice_natura_cup "
                . "and coalesce(e.descrizione_natura_cup, '') like :descrizione_natura_cup "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_fase', '%'.$ricerca->getCodFase().'%' );
        $q->setParameter(':descrizione_fase', '%'.$ricerca->getDescrizioneFase().'%' );
        $q->setParameter(':codice_natura_cup', '%'.$ricerca->getCodiceNaturaCup().'%' );
        $q->setParameter(':descrizione_natura_cup', '%'.$ricerca->getDescrizioneNaturaCup().'%' );


        return $q;
    }

    /**
     * @param CupNatura $natura
     * @param string $fase
     * @return TC46FaseProcedurale
     */
    public function getFaseProcedurale(CupNatura $natura, $fase){
        $codicenatura = $natura->getCodice();
        return $this->createQueryBuilder('fase')
        ->where('fase.cod_fase = :fase')
        ->setParameter('fase', ($codicenatura . $fase))
        ->getQuery()
        ->getOneOrNullResult();
    }
}
