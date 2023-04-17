<?php
namespace MonitoraggioBundle\Validator\Validators;



class PR01_036Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if ($value->getTavolaProtocollo() != 'PR01' || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $dql = 'select 1 '
            . 'from MonitoraggioBundle:PR01StatoAttuazioneProgetto stato '
            . 'join stato.monitoraggio_configurazione_esportazioni_tavola tavola '
            . 'where tavola = :tavola '
            . 'and stato.flg_cancellazione is null '
            . 'and stato.data_riferimento > CURRENT_TIMESTAMP() ';
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