<?php
namespace MonitoraggioBundle\Validator\Validators;



class AP06_003Validator extends AbstractValidator
{
    public function validate($value,  \Symfony\Component\Validator\Constraint $constraint)
    {
       
        if ( $value->getTavolaProtocollo() != 'AP06' || !$this->checkDuplicateError($value,$constraint) ) {
            return;
        }
       $dql = 'select 1 risultato '
           . 'from MonitoraggioBundle:AP06LocalizzazioneGeografica c '
           . 'join c.monitoraggio_configurazione_esportazioni_tavola tavola '
           . 'where tavola = :tavola ';

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