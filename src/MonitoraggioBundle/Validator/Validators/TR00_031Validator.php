<?php
namespace MonitoraggioBundle\Validator\Validators;



class TR00_031Validator extends AbstractValidator
{
    public function validate($value,  \Symfony\Component\Validator\Constraint $constraint)
    {
       
        if ( $value->getTavolaProtocollo() != 'TR00' || !$this->checkDuplicateError($value,$constraint) ) {
            return;
        }
      
        //$protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getTrasferimento()->getProtocollo();
        
        
                $dql = 'select 1 '
                    . 'from MonitoraggioBundle:TR00Trasferimenti trasferimenti '
                    .'join trasferimenti.monitoraggio_configurazione_esportazioni_tavola tavola '
                    . 'where trasferimenti.data_trasferimento > CURRENT_TIMESTAMP() '
                    . 'and trasferimenti.flg_cancellazione is null '
                    .'and tavola = :tavola';


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