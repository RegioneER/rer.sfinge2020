<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN06_FN07_026Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('FN06', 'FN07')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();


        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN06Pagamenti pagamenti '
            . 'left join MonitoraggioBundle:FN07PagamentiAmmessi ammessi  '
            . 'with ammessi.cod_locale_progetto = pagamenti.cod_locale_progetto '
            . 'and pagamenti.data_pagamento = ammessi.data_pagamento '
            . 'and pagamenti.cod_pagamento = ammessi.cod_pagamento '
            . "and ammessi.tipologia_pag in (:pagamenti) "
            . 'where pagamenti.cod_locale_progetto = :protocollo and pagamenti.flg_cancellazione is null '
            . "and pagamenti.tipologia_pag in (:pagamenti) "
            . 'group by pagamenti.cod_locale_progetto, pagamenti.cod_pagamento,  pagamenti.data_pagamento '
            . "having sum( pagamenti.importo_pag  ) < sum(ammessi.importo_pag_amm  ) ";
        $res = $this->em
            ->createQuery($dql)
            ->setParameter('protocollo', $protocollo)
            ->setParameter('pagamenti', array(
            'P', 'P-TR'
        ))
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!\is_null($res)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}