<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN06_052Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if ($value->getTavolaProtocollo() != 'FN06' || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();


        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN06Pagamenti pagamenti '

            . 'where pagamenti.cod_locale_progetto = :protocollo '
            . 'and pagamenti.flg_cancellazione is null '
            . "having sum( case pagamenti.tipologia_pag when 'P' then 1 when 'R' then -1 else 0 end * pagamenti.importo_pag ) < 0 "
            . "or sum( case pagamenti.tipologia_pag when 'P-TR' then 1 when 'R-TR' then -1 else 0 end * pagamenti.importo_pag ) < 0 ";
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