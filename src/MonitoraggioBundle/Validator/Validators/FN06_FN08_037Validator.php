<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN06_FN08_037Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('FN06', 'FN08')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN08Percettori percettori '
            . ',MonitoraggioBundle:FN06Pagamenti pagamenti '
            . 'where percettori.cod_locale_progetto = :protocollo '
            . 'and percettori.flg_cancellazione is null '
            . 'and pagamenti.cod_locale_progetto = percettori.cod_locale_progetto '
            . 'and pagamenti.cod_pagamento = percettori.cod_pagamento '
            . 'and pagamenti.data_pagamento = percettori.data_pagamento '
            . 'and pagamenti.tipologia_pag in (:pagamenti) '
            . 'and pagamenti.flg_cancellazione is null '
            . 'group by pagamenti.data_pagamento '
            . 'having sum( percettori.importo  ) >  sum(pagamenti.importo_pag)';
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