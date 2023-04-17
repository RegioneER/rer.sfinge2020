<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN04_FN05_025Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('FN04', 'FN05')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();


        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN04Impegni impegni '
            . 'left join MonitoraggioBundle:FN05ImpegniAmmessi ammessi  '
            . 'with ammessi.cod_locale_progetto = impegni.cod_locale_progetto '
            . 'and impegni.cod_impegno = ammessi.cod_impegno '
            . 'and impegni.data_impegno = ammessi.data_impegno '
            . "and ammessi.tipologia_imp_amm in ('I','I-TR') "
            . 'where impegni.cod_locale_progetto = :protocollo and impegni.flg_cancellazione is null '
            . "and impegni.tipologia_impegno in ('I','I-TR') "
            . 'group by impegni.cod_locale_progetto, impegni.cod_impegno,  impegni.data_impegno '
            . "having sum( impegni.importo_impegno  ) < sum(ammessi.importo_imp_amm  ) ";
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