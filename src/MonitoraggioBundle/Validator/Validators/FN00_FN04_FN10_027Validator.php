<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN00_FN04_FN10_027Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('FN00', 'FN04', 'FN10')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $dqlEconomie = 'select sum(economie.importo) from MonitoraggioBundle:FN10Economie economie where economie.cod_locale_progetto = :cod_locale_progetto and economie.flg_cancellazione is null ';
        $res = $this->em
        ->createQuery($dqlEconomie)
        ->setParameter('cod_locale_progetto', $protocollo)
        ->setMaxResults(1)
        ->getOneOrNullResult();
        $economie = $res[1];

        $dqlFinanziamenti = 'select sum(finanziamento.importo) from MonitoraggioBundle:FN00Finanziamento finanziamento'.
        ' join finanziamento.tc33_fonte_finanziaria tc33_fonte_finanziaria '
        .'where finanziamento.cod_locale_progetto = :cod_locale_progetto and finanziamento.flg_cancellazione is null '
        . 'and tc33_fonte_finanziaria.cod_fonte not in (:finanziamenti)'
        
        ;
        $res = $this->em
        ->createQuery($dqlFinanziamenti)
        ->setParameter('cod_locale_progetto', $protocollo)
        ->setParameter('finanziamenti', array(
            'PRD', 'RDR'
        ))
        ->setMaxResults(1)
        ->getOneOrNullResult();
        $finanziamenti = $res[1];


        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN04Impegni impegni '
            . 'where impegni.cod_locale_progetto = :protocollo and impegni.flg_cancellazione is null '
            . "and impegni.tipologia_impegno in (:impegni) "
            . 'group by impegni.cod_locale_progetto '
            . 'having sum( impegni.importo_impegno  ) > ( :contributi)';
        $res = $this->em
            ->createQuery($dql)
            ->setParameter('protocollo', $protocollo)
            ->setParameter('impegni', array(
            'I', 'I-TR'
        ))
        
        ->setParameter('contributi',$finanziamenti - $economie)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!\is_null($res)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}