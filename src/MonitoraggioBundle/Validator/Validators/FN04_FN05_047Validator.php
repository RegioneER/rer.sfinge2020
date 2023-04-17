<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN04_FN05_047Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('FN04', 'FN05')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN05ImpegniAmmessi ammesso '

            . 'left join MonitoraggioBundle:FN04Impegni impegno '
            . 'with impegno.cod_locale_progetto = ammesso.cod_locale_progetto '
            . 'and impegno.cod_impegno = ammesso.cod_impegno '
            . 'and impegno.tipologia_impegno = ammesso.tipologia_impegno '
            . 'and impegno.data_impegno = ammesso.data_impegno '
            . 'and impegno.flg_cancellazione is null '

            . 'where ammesso.cod_locale_progetto = :cod_locale_progetto '
            . 'and ammesso.flg_cancellazione is null '
            . 'and impegno.id is null ';


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