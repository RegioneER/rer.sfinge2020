<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN05_FN07_030Validator extends AbstractValidator
{
    public function validate($value,  \Symfony\Component\Validator\Constraint $constraint)
    {
       
        if ( !\in_array($value->getTavolaProtocollo(), array('FN05','FN07')) || !$this->checkDuplicateError($value,$constraint) ) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();
        
                $dql = 'select 1 '
                    . 'from MonitoraggioBundle:FN07PagamentiAmmessi costo '
                    .'join costo.tc36_livello_gerarchico cos_liv '
                    . ',MonitoraggioBundle:FN05ImpegniAmmessi ammessi '
                    . 'join ammessi.tc36_livello_gerarchico amm_liv '
                    . 'where costo.cod_locale_progetto = :protocollo '
                    . 'and costo.tipologia_pag_amm in (:pagamenti) '
                    . 'and costo.flg_cancellazione is null '
                    . 'and ammessi.cod_locale_progetto = costo.cod_locale_progetto '
                    . 'and ammessi.tipologia_imp_amm in (:impegni) '
                    . 'and ammessi.flg_cancellazione is null '
                    . 'and cos_liv = amm_liv '
                    . 'group by cos_liv.id, amm_liv.id '
                    . 'having sum( costo.importo_pag_amm  ) >  sum(ammessi.importo_imp_amm)';
                $res = $this->em
                    ->createQuery($dql)
                    ->setParameter('protocollo', $protocollo)
                    ->setParameter('impegni', array(
                    'I', 'I-TR'
                ))
                ->setParameter('pagamenti', array(
                    'P', 'P-TR'
                ))
                    ->setMaxResults(1)
                    ->getOneOrNullResult();
        
                if (!\is_null($res)) {
                    $this->context->buildViolation($constraint->message)
                        ->addViolation();
                }
    }
}