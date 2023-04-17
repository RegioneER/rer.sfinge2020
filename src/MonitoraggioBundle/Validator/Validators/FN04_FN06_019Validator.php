<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN04_FN06_019Validator extends AbstractValidator
{
    public function validate($value,  \Symfony\Component\Validator\Constraint $constraint)
    {
       
        if ( !\in_array($value->getTavolaProtocollo(), array('FN04','FN06')) || !$this->checkDuplicateError($value,$constraint) ) {
            return;
        }

        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $dql = 'select sum(fn06.importo_pag * '
            . "case fn06.tipologia_pag when 'P' then 1 when 'R' then -1 else 0 end ) pagamenti, "
            . "sum( fn06.importo_pag * case fn06.tipologia_pag when 'P-TR' then 1 when 'R-TR' then -1 else 0 end ) trasferimenti "
            . 'from MonitoraggioBundle:FN06Pagamenti fn06 '
            . 'where fn06.flg_cancellazione is null and fn06.cod_locale_progetto = :cod_locale_progetto ';

        $pagamenti = $this->em
            ->createQuery($dql)
            ->setParameter('cod_locale_progetto', $protocollo)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (is_null($pagamenti) ) {
            return;
        }
        
        $dql = 'select sum(fn04.importo_impegno * '
        . "case fn04.tipologia_impegno when 'I' then 1 when 'D' then -1 else 0 end ) impegni, "
        . "sum( fn04.importo_impegno * case fn04.tipologia_impegno when 'I-TR' then 1 when 'D-TR' then -1 else 0 end ) trasferimenti "
        . 'from MonitoraggioBundle:FN04Impegni fn04 '
        . 'where fn04.cod_locale_progetto = :cod_locale_progetto and fn04.flg_cancellazione is null ';

        $impegni = $this->em
        ->createQuery($dql)
        ->setParameter('cod_locale_progetto', $protocollo)
        ->setMaxResults(1)
        ->getOneOrNullResult();
        
        if (!is_null($impegni) && ($impegni['impegni'] > 0 || $impegni['trasferimenti'] > 0 ) && 
        ($pagamenti['pagamenti'] > 0 || $pagamenti['trasferimenti'] > 0) ) {
            return;
        }

        
        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ protocollo_richiesta }}', $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo())
            ->addViolation();
    
    }
}