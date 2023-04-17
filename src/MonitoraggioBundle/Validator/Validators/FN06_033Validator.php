<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN06_033Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if ($value->getTavolaProtocollo() != 'FN06' || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN06Pagamenti pagamenti '
            . 'join pagamenti.monitoraggio_configurazione_esportazioni_tavola tavola '
            . 'where tavola = :tavola '
            . 'and pagamenti.flg_cancellazione is null '
            . 'and pagamenti.data_pagamento > CURRENT_TIMESTAMP() ';
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