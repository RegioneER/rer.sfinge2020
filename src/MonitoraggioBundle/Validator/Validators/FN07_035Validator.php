<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN07_035Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if ($value->getTavolaProtocollo() != 'FN07' || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN07PagamentiAmmessi pagamenti_ammessi '
            . 'join pagamenti_ammessi.monitoraggio_configurazione_esportazioni_tavola tavola '
            . 'where tavola = :tavola '
            . 'and pagamenti_ammessi.flg_cancellazione is null '
            . 'and pagamenti_ammessi.data_pag_amm > CURRENT_TIMESTAMP() ';
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