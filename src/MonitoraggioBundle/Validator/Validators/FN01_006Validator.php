<?php

namespace MonitoraggioBundle\Validator\Validators;

class FN01_006Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {
        if ('FN01' != $value->getTavolaProtocollo() || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $dql = 'select 1 risultato '
           . 'from MonitoraggioBundle:FN01CostoAmmesso c '
           . 'join c.monitoraggio_configurazione_esportazioni_tavola tavola '
           . 'where tavola = :tavola '
           . 'and c.flg_cancellazione is null ';

        $res = $this->em
           ->createQuery($dql)
           ->setParameter('tavola', $value)
           ->setMaxResults(1)
           ->getOneOrNullResult();

        if (is_null($res) || 1 != $res['risultato']) {
            $this->context->buildViolation($constraint->message)
               ->addViolation();
        }
    }
}
