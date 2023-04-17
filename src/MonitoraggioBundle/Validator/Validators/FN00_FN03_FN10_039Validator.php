<?php

namespace MonitoraggioBundle\Validator\Validators;

use Symfony\Component\Validator\Constraint;

class FN00_FN03_FN10_039Validator extends AbstractValidator {
    public function validate($value, Constraint $constraint) {
        if (!\in_array($value->getTavolaProtocollo(), ['FN00', 'FN03', 'FN10']) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $dql = "SELECT sum(economie.importo) 
            FROM MonitoraggioBundle:FN10Economie economie 
            WHERE economie.cod_locale_progetto = :cod_locale_progetto 
            AND economie.flg_cancellazione IS NULL 
        ";
        $economie = $this->em
            ->createQuery($dql)
            ->setParameter('cod_locale_progetto', $protocollo)
            ->getSingleScalarResult();

        $dql = "SELECT sum(finanziamento.importo) 
            FROM MonitoraggioBundle:FN00Finanziamento finanziamento 
            WHERE finanziamento.cod_locale_progetto = :protocollo 
            AND finanziamento.flg_cancellazione IS NULL 
        ";
        $finanziamento = $this->em
            ->createQuery($dql)
            ->setParameter('protocollo', $protocollo)
            ->getSingleScalarResult();

        $dql = "SELECT 1 
            FROM MonitoraggioBundle:FN03PianoCosti pianoCosti 
            WHERE pianoCosti.cod_locale_progetto = :cod_locale_progetto 
            AND pianoCosti.flg_cancellazione IS NULL 
            AND pianoCosti.imp_realizzato + pianoCosti.imp_da_realizzare <> :importi
        ";
        $res = $this->em
            ->createQuery($dql)
            ->setParameter('cod_locale_progetto', $protocollo)
            ->setParameter('importi', $economie + $finanziamento)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!\is_null($res)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
