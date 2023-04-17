<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class FN02 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Voce spesa',
            'Importo',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
        ];
    }

    protected function normalizeResult($riga): array {
        $res = \array_map(function ($chiave, $valore) {
            return (\in_array($chiave, [
                    'voce_spesa',
                ]) && !\is_null($valore)) ?
                    new StringWrapper($valore) :
                       $valore;
        }, array_keys($riga), $riga);

        return parent::normalizeResult($res);
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT 
            fn02.cod_locale_progetto,
            tc37.voce_spesa AS voce_spesa,
            fn02.importo
        FROM MonitoraggioBundle:VistaFN02 AS fn02
        INNER JOIN fn02.tc37_voce_spesa AS tc37
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
