<?php

namespace AttuazioneControlloBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MonitoraggioBundle\Repository\TC12ClassificazioneRepository;

class RichiestaProgrammaClassificazioneRepository extends EntityRepository {
    /**
     * @param int $tipoClassificazioneId
     * @param int $richiestaProgrammaId
     */
    public function searchClassificazioni($tipoClassificazioneId, $richiestaProgrammaId): array {
        /** @var \AttuazioneControlloBundle\Entity\RichiestaProgramma $richiestaProgramma */
        $richiestaProgramma = $this->getEntityManager()->getRepository('AttuazioneControlloBundle:RichiestaProgramma')
        ->find($richiestaProgrammaId);
        if (is_null($richiestaProgramma)) {
            throw new \Exception('Associazione richiesta-programma non trovata');
        }

        $tipoClassificazione = $this->getEntityManager()->getRepository('MonitoraggioBundle:TC11TipoClassificazione')
        ->find($tipoClassificazioneId);
        if (is_null($tipoClassificazione)) {
            throw new \Exception('Tipo classificazione non trovato');
        }

        /** @var TC12ClassificazioneRepository $classificazioneRepository */
        $classificazioneRepository = $this->getEntityManager()->getRepository('MonitoraggioBundle:TC12Classificazione');

        $qb = $classificazioneRepository->querySearchValidClassification($richiestaProgramma, $tipoClassificazione);
        $expr = $qb->expr();
        return $qb->select('distinct classificazione.id, classificazione.descrizione as text')
            ->andWhere(
                    $expr->eq('richiesta_programma', ':richiesta_programma'),
                    $expr->eq('tipo', ':tipo')
            )
            ->setParameter('richiesta_programma', $richiestaProgramma)
            ->setParameter('tipo', $tipoClassificazione)
            ->getQuery()
            ->getResult();
    }
}
