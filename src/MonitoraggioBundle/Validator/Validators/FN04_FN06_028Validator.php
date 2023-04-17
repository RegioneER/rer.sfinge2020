<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN04_FN06_028Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('FN04', 'FN06')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN04Impegni impegni '
            . ',MonitoraggioBundle:FN06Pagamenti pagamenti '
            . 'where impegni.cod_locale_progetto = :protocollo '
            . 'and impegni.flg_cancellazione is null '
            . "and impegni.tipologia_impegno in (:impegni) "
            . ' and pagamenti.cod_locale_progetto = :protocollo '
            . 'and pagamenti.tipologia_pag in (:pagamenti) '
            . 'and pagamenti.flg_cancellazione is null '
            . 'group by impegni.cod_locale_progetto, pagamenti.cod_locale_progetto '
            . 'having sum( impegni.importo_impegno  ) >  sum(pagamenti.importo_pag)';
        $res = $this->em
            ->createQuery($dql)
            ->setParameter('protocollo', $protocollo)
            ->setParameter('pagamenti', array('P','P-TR'))
            ->setParameter('impegni', array(
            'I', 'I-TR'
        ))
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!\is_null($res)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}