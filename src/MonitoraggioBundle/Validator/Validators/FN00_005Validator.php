<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN00_005Validator extends AbstractValidator
{
    public function validate($value,  \Symfony\Component\Validator\Constraint $constraint)
    {
       
        if ( $value->getTavolaProtocollo() != 'FN00' || !$this->checkDuplicateError($value,$constraint) ) {
            return;
        }
      //TODO
       $dql = 'select 1 risultato '
           . 'from MonitoraggioBundle:FN00Finanziamento c '
           . 'join c.monitoraggio_configurazione_esportazioni_tavola tavola '
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