<?php
namespace MonitoraggioBundle\Validator\Validators;



class AP04_IN01_044Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('AP04', 'IN01')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $dql = 'select 1 '
            . 'from MonitoraggioBundle:IN01IndicatoriOutput output '

            . 'join output.indicatore_id indicatore_generico '

            . 'join MonitoraggioBundle:TC45IndicatoriOutputProgramma indicatore with indicatore = indicatore_generico '

            . 'left join MonitoraggioBundle:AP04Programma programma '
            . 'with programma.cod_locale_progetto = output.cod_locale_progetto '
            . 'and programma.stato = 1 '
            . 'and programma.tc4_programma = indicatore.programma '

            . 'where output.cod_locale_progetto = :cod_locale_progetto '
            . 'and output.flg_cancellazione is null '
            . 'and programma.id is null ';
        $res = $this->em
            ->createQuery($dql)
            ->setParameter('cod_locale_progetto', $protocollo)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!\is_null($res)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}