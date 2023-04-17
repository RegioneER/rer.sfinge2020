<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class FN08 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice pagamento',
            'Tipologia',
            'Data pagamento',
            'Codice fiscale',
            'Flag soggetto pubblico',
            'Tipo percettore',
            'Importo',
        ];
    }

    protected function normalizeResult($riga): array {
        $res = \array_map(function ($chiave, $valore) {
            return ('codice_fiscale' == $chiave && !\is_null($valore)) ?
                new StringWrapper($valore) :
                $valore;
        }, array_keys($riga), $riga);

        return parent::normalizeResult($res);
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_GENERAL,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
        ];
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				fn08.cod_locale_progetto,
				fn08.cod_pagamento,
				fn08.tipologia_pag,
				fn08.data_pagamento,
				fn08.codice_fiscale AS codice_fiscale,
                fn08.flag_soggetto_pubblico AS soggetto_pubblico,
                tc40.tipo_percettore AS tipo_percettore,
                fn08.importo AS importo


			FROM MonitoraggioBundle:VistaFN08 AS fn08
            LEFT JOIN fn08.tc40_tipo_percettore AS tc40
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
