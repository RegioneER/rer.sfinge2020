<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN07_AP04_022Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('FN07', 'AP04')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }

        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN07PagamentiAmmessi fn07 '
            . 'join fn07.tc4_programma fn07_tc4 '
            . 'left join MonitoraggioBundle:AP04Programma ap04  with fn07.cod_locale_progetto = ap04.cod_locale_progetto and ap04.stato = 1 '
            . 'left join ap04.tc4_programma tc4_programma with fn07_tc4 = tc4_programma '
            . 'where  fn07.cod_locale_progetto = :protocollo and tc4_programma.id is null';

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