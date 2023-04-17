<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class SC00 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice ruolo soggetto',
            'Codice fiscale',
            'Flag soggetto pubblico',
            'Codice UNI IPA',
            'Denominazione soggetto',
            'Forma giuridica',
            'Settore attività economica',
            'Note',
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
            'I' => NumberFormat::FORMAT_TEXT,
        ];
    }

    protected function normalizeResult($riga): array {
        $res = \array_map(function ($valore) {
            return \is_null($valore) ? null : new StringWrapper($valore);
        }, $riga);

        return $res;
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				sc00.cod_locale_progetto,
				tc24.cod_ruolo_sog,
				sc00.codice_fiscale,
				sc00.flag_soggetto_pubblico,
				sc00.cod_uni_ipa,
				sc00.denominazione_sog,
                tc25.forma_giuridica,
                tc26.cod_ateco_anno,
                sc00.note

			FROM MonitoraggioBundle:VistaSC00 sc00
			LEFT JOIN sc00.tc24_ruolo_soggetto as tc24
			LEFT JOIN sc00.tc25_forma_giuridica as tc25
			LEFT JOIN sc00.tc26_ateco as tc26
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
