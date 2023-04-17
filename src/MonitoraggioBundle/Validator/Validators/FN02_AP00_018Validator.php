<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN02_AP00_018Validator extends AbstractValidator
{
    protected static $NATURE = array(
        '03',
        '07',
    );

    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if ( !\in_array($value->getTavolaProtocollo(), array('FN02', 'AP00')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $dql = '';

        switch ($value->getTavolaProtocollo()) {
            case 'AP00':
            $dql = 'select 1 '
                . 'from MonitoraggioBundle:AP00AnagraficaProgetti ap00 '
                . 'join ap00.monitoraggio_configurazione_esportazioni_tavola tavola '
                . 'join ap00.tc5_tipo_operazione tc5_tipo_operazione '
                . 'left join MonitoraggioBundle:FN02QuadroEconomico fn02 with fn02.cod_locale_progetto = ap00.cod_locale_progetto '
                . 'where tavola = :tavola '
                . 'and (tc5_tipo_operazione.codice_natura_cup not in (:natura) or fn02.id is not null or ap00.flg_cancellazione is null ) ';
                break;

                case 'FN02':
                $dql = 'select 1 '
                . 'from  MonitoraggioBundle:FN02QuadroEconomico fn02  '
                . 'join fn02.monitoraggio_configurazione_esportazioni_tavola tavola '
                . 'left join MonitoraggioBundle:AP00AnagraficaProgetti ap00 with fn02.cod_locale_progetto = ap00.cod_locale_progetto '
                . 'join ap00.tc5_tipo_operazione tc5_tipo_operazione '
                . 'where tavola = :tavola '
                . 'and (tc5_tipo_operazione.codice_natura_cup not in (:natura) or fn02.id is not null or ap00.flg_cancellazione is null ) ';
            
                break;            
            default:
                return;
        }



        $res = $this->em
            ->createQuery($dql)
            ->setParameter('tavola', $value)
            ->setParameter('natura', self::$NATURE)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (is_null($res) ) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ protocollo_richiesta }}', $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo())
                ->addViolation();
        }
    }
}