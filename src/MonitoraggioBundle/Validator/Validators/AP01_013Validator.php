<?php

namespace MonitoraggioBundle\Validator\Validators;

class AP01_013Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {
        if (!\in_array($value->getTavolaProtocollo(), array('AP01', 'AP00')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }

        if ('AP00' == $value->getTavolaProtocollo()) {
            $dql = 'select 1 risultato '
            . 'from MonitoraggioBundle:AP00AnagraficaProgetti c '
            . 'join c.tc48_tipo_procedura_attivazione_originaria tc48_tipo_procedura_attivazione_originaria '
            . 'join c.monitoraggio_configurazione_esportazioni_tavola tavola '
            . 'left join MonitoraggioBundle:AP01AssociazioneProgettiProcedura ap01 with c.cod_locale_progetto =  ap01.cod_locale_progetto and ap01.flg_cancellazione is null '
            . 'where tavola = :tavola '
            . 'and c.flg_cancellazione is NOT null and ap01.id is null and tc48_tipo_procedura_attivazione_originaria.tip_proc_att_orig =  :tipo ';

            $res = $this->em
            ->createQuery($dql)
            ->setParameter('tavola', $value)
            ->setParameter('tipo', 5)
            ->setMaxResults(1)
            ->getOneOrNullResult();

            $errore = !is_null($res) && $res['risultato'];

            if ($errore) {
                $this->context->buildViolation($constraint->message)
                ->addViolation();
            }
        } else {
            $dql = 'select 1 risultato '
            . 'from MonitoraggioBundle:AP01AssociazioneProgettiProcedura c '
            . 'join c.monitoraggio_configurazione_esportazioni_tavola tavola '
            . 'left join c.tc1_procedura_attivazione ap01tc1 '
            . 'join  MonitoraggioBundle:PA00ProcedureAttivazione pa00 with pa00.cod_proc_att = ap01tc1.cod_proc_att '
            . 'where tavola = :tavola '
            . "and c.flg_cancellazione is null and pa00.flg_cancellazione = 'S' ";

            $res = $this->em
            ->createQuery($dql)
            ->setParameter('tavola', $value)
            ->setMaxResults(1)
            ->getOneOrNullResult();

            if (!\is_null($res) ) {
                $this->context->buildViolation($constraint->message)
                ->addViolation();
            }
        }
    }
}
