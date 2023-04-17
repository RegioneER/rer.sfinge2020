<?php
namespace MonitoraggioBundle\Validator\Validators;



class PR00_AP00_046Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('PR00', 'AP00')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $dql = 'select 1 '
            . 'from MonitoraggioBundle:PR00IterProgetto iter '

            .'join iter.tc46_fase_procedurale fase '

            . 'left join MonitoraggioBundle:AP00AnagraficaProgetti anagrafica '
            . 'with anagrafica.cod_locale_progetto = iter.cod_locale_progetto '
            . 'and anagrafica.flg_cancellazione is null '

            . 'left join anagrafica.tc5_tipo_operazione tc5_tipo_operazione '
            . ' with tc5_tipo_operazione.codice_natura_cup = fase.codice_natura_cup '

            . 'where iter.cod_locale_progetto = :cod_locale_progetto '
            . 'and iter.flg_cancellazione is null '
            . 'and tc5_tipo_operazione.id is null ';
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