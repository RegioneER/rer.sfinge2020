<?php
namespace MonitoraggioBundle\Validator\Validators;



class PR00_041Validator extends AbstractValidator
{
    public function validate($value,  \Symfony\Component\Validator\Constraint $constraint)
    {
       
        if ( $value->getTavolaProtocollo() != 'PR00' || !$this->checkDuplicateError($value,$constraint) ) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();
        
                $dql = 'select 1 '
                    . 'from MonitoraggioBundle:PR00IterProgetto iter '
                    .'join iter.tc46_fase_procedurale fase '
                    .'join MonitoraggioBundle:PR00IterProgetto iter_precedenti '
                    .'with iter_precedenti.cod_locale_progetto = iter.cod_locale_progetto '
                    .'and iter_precedenti.data_fine_effettiva is null '
                    .'and iter_precedenti.data_inizio_effettiva is null '
                    .'and iter.flg_cancellazione is null '
                    .'join iter_precedenti.tc46_fase_procedurale fase_precedente '
                    .'with fase_precedente.codice_natura_cup = fase.codice_natura_cup '
                    .'and fase_precedente.cod_fase < fase.cod_fase '
                    . 'where iter.cod_locale_progetto = :cod_locale_progetto '
                    . 'and iter.flg_cancellazione is null '
                    .'and fase.cod_fase in (:fasi) '
                    . 'and iter.data_fine_effettiva is not null ';
                $res = $this->em
                    ->createQuery($dql)
                    ->setParameter('cod_locale_progetto', $protocollo)
                    ->setParameter('fasi', array(
                        '0102',
                        '0202',
                        '0302',
                        '0303',
                        '0304',
                        '0305',
                        '0306',
                        '0307',
                        '0602',
                        '0702',
                        '0802',
                    ))
                    ->setMaxResults(1)
                    ->getOneOrNullResult();
        
                    if (!\is_null($res)) {
                    $this->context->buildViolation($constraint->message)
                        ->addViolation();
                }
    }
}