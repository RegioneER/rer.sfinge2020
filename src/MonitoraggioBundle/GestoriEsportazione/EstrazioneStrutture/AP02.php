<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class AP02 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice progetto complesso',
            'Grande progetto',
            'Generatore entrate',
            'Tipo livello istituzione',
            'Fondo di fondi',
            'Tipo localizzazione',
            'Codice gruppo vulnerabile',
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
        ];
    }

    protected function normalizeResult($riga): array {
        $res = \array_map(function ($valore) {
            return (!\is_null($valore)) ? new StringWrapper($valore) : $valore;
        }, $riga);

        return parent::normalizeResult($res);
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				ap02.cod_locale_progetto,
				tc7.cod_prg_complesso as cod_prg_complesso,
                tc8.grande_progetto as grande_progetto,
                ap02.generatore_entrate as generatore_entrate,
                tc9.liv_istituzione_str_fin as liv_istituzione_str_fin,
                ap02.fondo_di_fondi as fondo_di_fondi,
                tc10.tipo_localizzazione as tipo_localizzazione,
                tc13.cod_vulnerabili as cod_vulnerabili

			FROM MonitoraggioBundle:VistaAP02 ap02

            LEFT JOIN ap02.tc7_progetto_complesso as tc7
            LEFT JOIN ap02.tc8_grande_progetto as tc8
            LEFT JOIN ap02.tc9_tipo_livello_istituzione as tc9
            LEFT JOIN ap02.tc10_tipo_localizzazione as tc10
            LEFT JOIN ap02.tc13_gruppo_vulnerabile_progetto as tc13
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
