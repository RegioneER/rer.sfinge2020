<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class AP04 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice procedura attivazione',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
        ];
    }

    protected function normalizeResult($riga): array {
        $res = \array_map(function ($chiave, $valore) {
            return (!\is_null($valore)) ?
                new StringWrapper($valore) :
                $valore;
        }, array_keys($riga), $riga);

        return parent::normalizeResult($res);
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				AP04.cod_locale_progetto as cod_locale_progetto,
				tc4.cod_programma as cod_programma,
                AP04.stato as stato,
                tc14.specifica_stato as specifica_stato
            
            FROM MonitoraggioBundle:VistaAP04 AP04

            INNER JOIN AP04.tc4_programma as tc4
            LEFT JOIN AP04.tc14_specifica_stato as tc14
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
