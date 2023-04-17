<?php

namespace MonitoraggioBundle\Validator\Validators;

use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function checkDuplicateError($value, $constraint)
    {
        $dql = "select 1 risultato 
            from MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneErrore e
            join e.monitoraggio_configurazione_esportazione_tavole monitoraggio_configurazione_esportazione_tavole
            where monitoraggio_configurazione_esportazione_tavole = :monitoraggio_configurazione_esportazione_tavole and e.codice_errore_igrue = :codice_errore_igrue
        ";

        $res = $this->em
            ->createQuery($dql)
            ->setParameter('monitoraggio_configurazione_esportazione_tavole', $value)
            ->setParameter('codice_errore_igrue', $constraint->payload['codice_igrue'])
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!\is_null($res)) {
            return false;
        }

        return true;
    }
}
