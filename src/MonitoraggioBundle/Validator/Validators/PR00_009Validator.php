<?php

namespace MonitoraggioBundle\Validator\Validators;

use Symfony\Component\Validator\Constraint;


class PR00_009Validator extends AbstractValidator {
    public function validate($value, Constraint $constraint) {
        if ('PR00' != $value->getTavolaProtocollo() || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }

//        $fasi_tot = array(
//            "0101" => "01",	//Stipula Contratto
//            "0102" => "01",   //Esecuzione Fornitura
//            "0201" => "02",   //Stipula Contratto
//            "0202" => "02",   //Esecuzione Fornitura
//            "0301" => "03",   //Studio di fattibilità
//            "0302" => "03",   //Progettazione Preliminare
//            "0303" => "03",	//Progettazione Definitiva
//            "0304" => "03",	//Progettazione Esecutiva
//            "0305" => "03",	//Stipula Contratto
//            "0306" => "03",	//Esecuzione  Lavori
//            "0307" => "03",	//Collaudo
//            "0601" => "06",	//Attribuzione finanziamento
//            "0602" => "06",	//Esecuzione  investimenti/attività
//            "0701" => "07",	//Attribuzione finanziamento
//            "0702" => "07",	//Esecuzione  investimenti
//            "0801" => "08",	//Attribuzione finanziamento
//            "0802" => "08",	//Esecuzione  investimenti
//        );

        $a = 1;

        $dql = 'select tc46_fase.cod_fase , tc46_fase.descrizione_fase '
                . 'from MonitoraggioBundle:TC46FaseProcedurale tc46_fase '
                . 'where tc46_fase not in (select IDENTITY(pr00.tc46_fase_procedurale) from MonitoraggioBundle:PR00IterProgetto pr00 '
                    . 'join pr00.monitoraggio_configurazione_esportazioni_tavola tavola '
                    . 'where tavola = :tavola '
                    . 'and pr00.flg_cancellazione is null) '
                . ' and tc46_fase.codice_natura_cup in (select tc5.codice_natura_cup  from MonitoraggioBundle:AP00AnagraficaProgetti ap00 '
                    . 'join ap00.tc5_tipo_operazione tc5 '
                    . 'where ap00.cod_locale_progetto = (:cod_locale_progetto) '
                    . 'and ap00.flg_cancellazione is null) '
                . "and tc46_fase.cod_fase not in ('0301','0302','0303','0304','0307')";

        $res = $this->em
            ->createQuery($dql)
            ->setParameter('tavola', $value)
            ->setParameter('cod_locale_progetto', $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo())
            ->getResult();

        if (count($res) > 0) {
            foreach ($res as $key => $value) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ codice }}', $value['cod_fase'])
                    ->setParameter('{{ descrizione }}', $value['descrizione_fase'])
                    ->addViolation();
            }
        }
    }
}
