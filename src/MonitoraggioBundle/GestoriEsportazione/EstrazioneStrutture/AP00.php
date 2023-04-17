<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class AP00 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Titolo progetto',
            'Sintesi progetto',
            'Tipo operazione',
            'CUP',
            'Tipo aiuto',
            'Data inizio',
            'Data fine prevista',
            'Data fine effettiva',
            'Tipo procedura attivazione originaria',
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
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'J' => NumberFormat::FORMAT_TEXT,
        ];
    }

    protected function normalizeResult($riga): array {
        $res = \array_map(function ($chiave, $valore) {
            return ('tipo_operazione' == $chiave && !\is_null($valore)) ?
                new StringWrapper($valore) :
                $valore;
        }, array_keys($riga), $riga);

        return parent::normalizeResult($res);
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				ap00.cod_locale_progetto,
				ap00.titolo_progetto,
				ap00.sintesi_prg,
				tc5.tipo_operazione as tipo_operazione,
				ap00.cup,
				tc6.tipo_aiuto as tipo_aiuto,
				ap00.data_inizio,
				ap00.data_fine_prevista,
				ap00.data_fine_effettiva,
                tc48.tip_proc_att_orig as CODICE_PROC_ATT_ORIG

			FROM MonitoraggioBundle:VistaAP00 ap00
			LEFT JOIN ap00.tc5_tipo_operazione as tc5
			LEFT JOIN ap00.tc6_tipo_aiuto as tc6
			LEFT JOIN ap00.tc48_tipo_procedura_attivazione_originaria as tc48
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
