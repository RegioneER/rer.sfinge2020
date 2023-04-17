<?php
namespace MonitoraggioBundle\Validator\Validators;



class IN01_008Validator extends AbstractValidator
{
    public function validate($value,  \Symfony\Component\Validator\Constraint $constraint)
    {
       
        if ( $value->getTavolaProtocollo() != 'IN01' || !$this->checkDuplicateError($value,$constraint) ) {
            return;
        }
       $dql = 'select 1 risultato '
           . 'from MonitoraggioBundle:IN01IndicatoriOutput c '
           . 'join c.monitoraggio_configurazione_esportazioni_tavola tavola '
           . 'join MonitoraggioBundle:TC44IndicatoriOutputComuni tipo with tipo = c.indicatore_id '
           . 'where tavola = :tavola '
           . 'and c.flg_cancellazione is null ';

       $res = $this->em
           ->createQuery($dql)
           ->setParameter('tavola', $value)
           ->setMaxResults(1)
           ->getOneOrNullResult();

       if (is_null($res) || $res['risultato'] != 1) {
           $this->context->buildViolation($constraint->message)
               ->addViolation();
       }
    }
}