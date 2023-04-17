<?php
namespace MonitoraggioBundle\Validator\Validators;



class AP03_001Validator extends AbstractValidator
{
    public function validate($value,  \Symfony\Component\Validator\Constraint $constraint)
    {
       
        if ( $value->getTavolaProtocollo() != 'AP03' || !$this->checkDuplicateError($value,$constraint) ) {
            return;
        }
      
        $dql = 'select 1 risultato '
            . 'from MonitoraggioBundle:AP03Classificazioni c '
            . 'join c.classificazione classificazione '
            . 'join classificazione.tipo_classificazione tipo_classificazione '
            . 'join c.monitoraggio_configurazione_esportazioni_tavola tavola '
            . 'where tavola = :tavola '
            . 'and tipo_classificazione.tipo_class = :RA and c.flg_cancellazione is null ';

        $res = $this->em
            ->createQuery($dql)
            ->setParameter('tavola', $value)
            ->setParameter('RA', 'RA')
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (is_null($res) || $res['risultato'] != 1) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}