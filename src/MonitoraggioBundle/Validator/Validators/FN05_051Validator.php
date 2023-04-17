<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN05_051Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if ($value->getTavolaProtocollo() != 'FN05' || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();


        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN05ImpegniAmmessi impegni '

            . 'where impegni.cod_locale_progetto = :protocollo '
            . 'and impegni.flg_cancellazione is null '
            .'group by impegni.tc36_livello_gerarchico '
            . "having sum( case impegni.tipologia_imp_amm when 'I' then 1 when 'D' then -1 else 0 end * impegni.importo_imp_amm ) < 0 "
            . "or sum( case impegni.tipologia_imp_amm when 'I-TR' then 1 when 'D-TR' then -1 else 0 end * impegni.importo_imp_amm ) < 0 ";
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