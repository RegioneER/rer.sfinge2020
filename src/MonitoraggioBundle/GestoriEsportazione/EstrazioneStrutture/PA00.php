<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class PA00 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice procedura attivazione',
            'Codice procedura attivazione locale',
            'Codice aiuto RNA',
            'Tipo procedura attivazione',
            'Flag aiuti',
            'Descrizione procedura attivazione',
            'Codice tipo responsabile procedura',
            'Denominazione responsabile procedura',
            'Data avvio procedura',
            'Data fine procedura',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'J' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    protected function normalizeResult($riga): array {
        $res = \array_map(function ($chiave, $valore) {
            switch ($chiave) {
                case 'cod_proc_att_locale':
                case 'tip_procedura_att':
                case 'cod_tipo_resp_proc':
                   if(!\is_null($valore)){
                       return new StringWrapper($valore);
                   }

                case 'flag_aiuti':
                   return $valore ? 'Si': 'No';

                default:
                    return $valore;
            }
        }, array_keys($riga), $riga);

        return parent::normalizeResult($res);
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				tc1.cod_proc_att,
				pa00.cod_proc_att_locale,
				pa00.cod_aiuto_rna,
				tc2.tip_procedura_att as tip_procedura_att,
				pa00.flag_aiuti,
				pa00.descr_procedura_att,
                tc3.cod_tipo_resp_proc as cod_tipo_resp_proc,
                pa00.denom_resp_proc,
                pa00.data_avvio_procedura,
                pa00.data_fine_procedura

			FROM MonitoraggioBundle:VistaPA00 pa00
			LEFT JOIN pa00.tc1_cod_proc_att as tc1
			LEFT JOIN pa00.tc2_tipo_procedura_attivazione as tc2
			LEFT JOIN pa00.tc3_responsabile_procedura as tc3
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
