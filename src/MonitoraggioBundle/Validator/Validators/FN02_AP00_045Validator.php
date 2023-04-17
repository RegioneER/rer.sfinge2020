<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN02_AP00_045Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if ( !\in_array($value->getTavolaProtocollo(), array('FN02', 'AP00')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN02QuadroEconomico quadro '

            . 'join quadro.tc37_voce_spesa tc37_voce_spesa '

            . 'left join MonitoraggioBundle:AP00AnagraficaProgetti anagrafica '
            . 'with anagrafica.cod_locale_progetto = quadro.cod_locale_progetto '
            . 'and anagrafica.flg_cancellazione is null '

            . 'left join anagrafica.tc5_tipo_operazione tc5_tipo_operazione '
            .' with tc5_tipo_operazione.codice_natura_cup = tc37_voce_spesa.codice_natura_cup '

            . 'where quadro.cod_locale_progetto = :cod_locale_progetto '
            . 'and quadro.flg_cancellazione is null '
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