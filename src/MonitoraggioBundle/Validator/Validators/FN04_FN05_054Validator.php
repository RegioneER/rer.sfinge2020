<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN04_FN05_054Validator extends AbstractValidator
{
    public function validate($value,  \Symfony\Component\Validator\Constraint $constraint)
    {
       
        if ( !\in_array($value->getTavolaProtocollo(), array('FN04','FN05')) || !$this->checkDuplicateError($value,$constraint) ) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();
        
        
                $dql = 'select 1 '
                    . 'from MonitoraggioBundle:FN04Impegni impegni '
                    .', MonitoraggioBundle:FN05ImpegniAmmessi ammessi '        

                    . 'where impegni.cod_locale_progetto = :protocollo '
                    .'and ammessi.cod_locale_progetto = :protocollo '
                    . 'and impegni.flg_cancellazione is null '
                    . 'and ammessi.flg_cancellazione is null '

                    . "having sum( case impegni.tipologia_impegno when 'I' then 1 when 'I-TR' then 1 else -1 end * impegni.importo_impegno )  "
                    . "< sum( case ammessi.tipologia_imp_amm when 'I-TR' then 1 when 'I' then 1 else -1 end * ammessi.importo_imp_amm )  ";


                $res = $this->em
                    ->createQuery($dql)
                    ->setParameter('protocollo', $protocollo)
                    ->setMaxResults(1)
                    ->getOneOrNullResult();
        
                if (!\is_null($res)) {
                    $this->context->buildViolation($constraint->message)
                        ->addViolation();
                }
    }
}