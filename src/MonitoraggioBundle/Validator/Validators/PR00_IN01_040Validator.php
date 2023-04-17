<?php
namespace MonitoraggioBundle\Validator\Validators;



class PR00_IN01_040Validator extends AbstractValidator
{
    public function validate($value,  \Symfony\Component\Validator\Constraint $constraint)
    {
       
        if ( !\in_array($value->getTavolaProtocollo(), array('PR00','IN01')) || !$this->checkDuplicateError($value,$constraint) ) {
            return;
        }

        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $dql = "SELECT 1
            from MonitoraggioBundle:PR00IterProgetto iter
            inner join iter.tc46_fase_procedurale fase
            inner join MonitoraggioBundle:IN01IndicatoriOutput output
            where iter.cod_locale_progetto = :cod_locale_progetto
                and iter.flg_cancellazione is null
                and fase.cod_fase in (:fasi)
                and iter.data_fine_prevista is not null
                and output.valore_realizzato is null
            ";
        $res = $this->em
            ->createQuery($dql)
            ->setParameter('cod_locale_progetto', $protocollo)
            ->setParameter('fasi', array(
                '0102',
                '0202',
                '0306',
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