<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN07_053Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if ($value->getTavolaProtocollo() != 'FN07' || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();


        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN07PagamentiAmmessi pagamenti '

            . 'where pagamenti.cod_locale_progetto = :protocollo '
            . 'and pagamenti.flg_cancellazione is null '
            . 'group by pagamenti.tc36_livello_gerarchico '
            . "having sum( case pagamenti.tipologia_pag_amm when 'P' then 1 when 'R' then -1 else 0 end * pagamenti.importo_pag_amm ) < 0 "
            . "or sum( case pagamenti.tipologia_pag_amm when 'P-TR' then 1 when 'R-TR' then -1 else 0 end * pagamenti.importo_pag_amm ) < 0 ";
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