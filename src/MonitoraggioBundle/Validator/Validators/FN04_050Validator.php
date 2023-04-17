<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN04_050Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if ($value->getTavolaProtocollo() != 'FN04' || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();


        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN04Impegni impegni '


            . 'where impegni.cod_locale_progetto = :protocollo '
            . 'and impegni.flg_cancellazione is null '
            . "having sum( case impegni.tipologia_impegno when 'I' then 1 when 'D' then -1 else 0 end * impegni.importo_impegno ) < 0 "
            . "or sum( case impegni.tipologia_impegno when 'I-TR' then 1 when 'D-TR' then -1 else 0 end * impegni.importo_impegno ) < 0 ";
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