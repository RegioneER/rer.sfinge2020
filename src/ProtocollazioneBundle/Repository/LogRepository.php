<?php
namespace ProtocollazioneBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use ProtocollazioneBundle\Form\Entity\RicercaLog;

class LogRepository extends EntityRepository
{
    /**
     * @param RicercaLog $ricercaLog
     * @return Query
     */
    public function getLogByRichiestaProtocolloId(RicercaLog $ricercaLog): Query
    {
        $dql = 'SELECT log FROM ProtocollazioneBundle:Log log';
        $richiestaProtocolloId = $ricercaLog->getRichiestaProtocolloId();
        $appFunctionTarget = $ricercaLog->getAppFunctionTarget();

        $ultimaSettimana = strtotime("-1 week");
        $ultimaSettimana = date("Y-m-d 00:00:00", $ultimaSettimana);
        $q = $this->getEntityManager()->createQuery();
        if ($richiestaProtocolloId) {
            $dql .= ' WHERE log.richiesta_protocollo_id = :richiesta_protocollo_id AND log.log_time >= :ultima_settimana';
            $q->setParameter('richiesta_protocollo_id', $richiestaProtocolloId);
            $q->setParameter("ultima_settimana", $ultimaSettimana);
        } else {
            $dql .= ' WHERE log.log_time >= :ultima_settimana';
            $q->setParameter("ultima_settimana", $ultimaSettimana);
        }

        if ($appFunctionTarget) {
            if ($richiestaProtocolloId) {
                $dql .= ' AND ';
            } else {
                $dql .= ' WHERE ';
            }
            $dql .= 'log.app_function_target = :app_function_target';
            $q->setParameter('app_function_target', $appFunctionTarget);
        }

        $dql .= ' ORDER BY log.id DESC';

        $q->setDQL($dql);
        return $q;
    }
}
