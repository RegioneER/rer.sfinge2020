<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN06_FN08_049Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('FN06', 'FN08')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();


        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN08Percettori percettori '

            . 'left join MonitoraggioBundle:FN06Pagamenti pagamenti '
            . 'with percettori.cod_locale_progetto = pagamenti.cod_locale_progetto '
            . 'and pagamenti.data_pagamento = percettori.data_pagamento '
            . 'and pagamenti.cod_pagamento = percettori.cod_pagamento '
            . 'and pagamenti.tipologia_pag = percettori.tipologia_pag '
            . 'and pagamenti.flg_cancellazione is null '

            . 'where percettori.cod_locale_progetto = :protocollo '
            . 'and percettori.flg_cancellazione is null '
            . 'and pagamenti.id is null ';
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