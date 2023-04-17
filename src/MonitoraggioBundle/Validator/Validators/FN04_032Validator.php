<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN04_032Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if ($value->getTavolaProtocollo() != 'FN04' || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN04Impegni impegni '
            .'join impegni.monitoraggio_configurazione_esportazioni_tavola tavola '
            . 'where tavola = :tavola '
            . 'and impegni.flg_cancellazione is null '
            .'and impegni.data_impegno > CURRENT_TIMESTAMP() ';
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