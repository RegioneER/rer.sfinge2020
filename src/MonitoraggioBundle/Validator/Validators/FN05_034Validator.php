<?php

namespace MonitoraggioBundle\Validator\Validators;

class FN05_034Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {
        if ('FN05' != $value->getTavolaProtocollo() || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN05ImpegniAmmessi impegni_ammessi '
            . 'join impegni_ammessi.monitoraggio_configurazione_esportazioni_tavola tavola '
            . 'where tavola = :tavola '
            . 'and impegni_ammessi.flg_cancellazione is null '
            . 'and impegni_ammessi.data_imp_amm > CURRENT_TIMESTAMP() ';
        $res = $this->em
            ->createQuery($dql)
            ->setParameter('tavola', $value)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!\is_null($res)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
