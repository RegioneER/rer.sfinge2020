<?php
namespace MonitoraggioBundle\Validator\Validators;



class AP04_IN00_043Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('AP04', 'IN00')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $dql = 'select 1 '
            . 'from MonitoraggioBundle:IN00IndicatoriRisultato risultato '

            . 'join risultato.indicatore_id indicatore_generico '

            . 'join MonitoraggioBundle:TC43IndicatoriRisultatoProgramma indicatore with indicatore = indicatore_generico '

            . 'left join MonitoraggioBundle:AP04Programma programma '
            . 'with programma.cod_locale_progetto = risultato.cod_locale_progetto '
            . 'and programma.stato = 1 '
            . 'and programma.tc4_programma = indicatore.programma '

            . 'where risultato.cod_locale_progetto = :cod_locale_progetto '
            . 'and risultato.flg_cancellazione is null '
            .'and programma.id is null ';
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