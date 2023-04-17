<?php
namespace MonitoraggioBundle\Validator\Validators;

use Symfony\Component\Validator\Constraint;




class FN06_FN07_048Validator extends AbstractValidator
{
    public function validate($value, Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('FN06', 'FN07')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();


        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN07PagamentiAmmessi ammessi '

            . 'left join MonitoraggioBundle:FN06Pagamenti pagamenti '
            . 'with ammessi.cod_locale_progetto = pagamenti.cod_locale_progetto '
            . 'and pagamenti.data_pagamento = ammessi.data_pagamento '
            . 'and pagamenti.cod_pagamento = ammessi.cod_pagamento '
            . 'and pagamenti.tipologia_pag = ammessi.tipologia_pag '
            . 'and pagamenti.flg_cancellazione is null '

            . 'where ammessi.cod_locale_progetto = :protocollo '
            . 'and ammessi.flg_cancellazione is null '
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