<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use MonitoraggioBundle\Utils\StringWrapper;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PG00 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice procedura aggiudicazione',
            'CIG',
            'Motivo assenza CIG',
            'Descrizione procedura aggiudicazione',
            'Tipo procedura aggiudicazione',
            'Importo procedura aggiudicazione',
            'Data pubblicazione',
            'Importo aggiudicato',
            'Data aggiudicazione',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
            'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'I' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
            'J' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    protected function normalizeResult($riga): array {
        $res = \array_map(function ($chiave, $valore) {
           if($valore && \in_array($chiave, [
                'motivo_assenza_cig',
                'tipo_proc_agg'
           ])){
               return new StringWrapper($valore);
           }
            return $valore;
        }, array_keys($riga), $riga);

        return parent::normalizeResult($res);
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				pg00.cod_locale_progetto,
                pg00.cod_proc_agg,
                pg00.cig,
                tc22.motivo_assenza_cig as motivo_assenza_cig,
                pg00.descr_procedura_agg,
                tc23.tipo_proc_agg,
				pg00.importo_procedura_agg,
                pg00.data_pubblicazione,
                pg00.importo_aggiudicato,
                pg00.data_aggiudicazione


			    FROM MonitoraggioBundle:VistaPG00 pg00
                LEFT JOIN pg00.tc22_motivo_assenza_cig as tc22
                LEFT JOIN pg00.tc23_tipo_procedura_aggiudicazione as tc23

		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
