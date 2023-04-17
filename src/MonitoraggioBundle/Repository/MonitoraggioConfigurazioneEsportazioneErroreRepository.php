<?php

namespace MonitoraggioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione;
use MonitoraggioBundle\Form\Entity\RicercaTavolaEsportata;
use Doctrine\ORM\Query;

class MonitoraggioConfigurazioneEsportazioneErroreRepository extends EntityRepository {
    public function findAllErrori(MonitoraggioConfigurazioneEsportazione $configurazione): array {
        return $this->getAllErroriQuery($configurazione)
            ->getResult();
    }

    public function getAllErroriQuery(MonitoraggioConfigurazioneEsportazione $configurazione): Query {
        $dql = 'select tavola.tavola_protocollo struttura, errore.errore errore_label, errore.errore_da_igrue igrueError '
            . 'from MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneErrore errore '
            . 'join errore.monitoraggio_configurazione_esportazione configurazione '
            . 'left join errore.monitoraggio_configurazione_esportazione_tavole tavola '
            . 'where configurazione = :configurazione ';

        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('configurazione', $configurazione);
    }

    public function ricercaErrori(RicercaTavolaEsportata $ricerca): Query {
        $q = $this->createQueryBuilder('errori');
        $expr = $q->expr();
        $q->select('errori, tavola, configurazione')
            ->join('errori.monitoraggio_configurazione_esportazione_tavole', 'tavola')
            ->join('tavola.monitoraggio_configurazione_esportazione', 'configurazione')
            ->join('configurazione.monitoraggio_esportazione', 'esportazione')
            ->where($expr->eq('esportazione', 'coalesce( :esportazione, esportazione )'));

        $strutture = MonitoraggioEsportazioneRepository::GetAllStrutture();
        if ($ricerca->getStruttura() && \in_array($ricerca->getStruttura(), $strutture)) {
            $innerQueryDQL = $this->getEntityManager()
                ->createQueryBuilder('tavolaStruttura')
                ->select('tavolaStruttura.id')
                    ->from('MonitoraggioBundle:' . $ricerca->getStruttura(), 'struttura')
                    ->join('struttura.monitoraggio_configurazione_esportazioni_tavola', 'tavolaStruttura')
                    ->where(
                        $expr->eq(
                            'coalesce(struttura.progressivo_puc, 0)',
                            'coalesce( :progressivo, struttura.progressivo_puc, 0)'
                    ))
                    ->getDQL();
            $q->setParameter('progressivo', $ricerca->getProgressivo())
                ->andWhere(
                    $expr->in(
                        'tavola.id',
                        $innerQueryDQL
            ));
        }

        return $this->getEntityManager()->createQuery($q)->setParameter('esportazione', $ricerca->getEsportazione());
    }
}
