<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN01_AP04_020Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('FN01', 'AP04')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();
        
        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN01CostoAmmesso fn01 '
            . 'join fn01.tc4_programma fn01_tc4 '
            . 'left join MonitoraggioBundle:AP04Programma ap04  with fn01.cod_locale_progetto = ap04.cod_locale_progetto and ap04.stato = 1 '
            . 'left join ap04.tc4_programma tc4_programma with fn01_tc4 = tc4_programma '
            . 'where fn01.cod_locale_progetto = :protocollo and tc4_programma.id is null';

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