<?php
namespace MonitoraggioBundle\Validator\Validators;



class PR00_042Validator extends AbstractValidator
{
    public function validate($value,  \Symfony\Component\Validator\Constraint $constraint)
    {
       
        if ( $value->getTavolaProtocollo() != 'PR00' || !$this->checkDuplicateError($value,$constraint) ) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();
        
                $dql = 'select 1 '
                    . 'from MonitoraggioBundle:PR00IterProgetto iter '
                    . 'where iter.cod_locale_progetto = :cod_locale_progetto '
                    . 'and iter.flg_cancellazione is null '
                    . 'and iter.data_fine_effettiva is not null '
                    . 'and iter.data_inizio_effettiva is null ';
                $res = $this->em
                    ->createQuery($dql)
                    ->setParameter('cod_locale_progetto', $protocollo)
                    ->setMaxResults(1)
                    ->getOneOrNullResult();
        
                    if (!\is_null($res)) {
                    $this->context->buildViolation($constraint->message)
                        ->addViolation();
                }
    }
}