<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class AP05 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice dello Strumento Attuativo',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
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
				AP05.cod_locale_progetto,
				tc15.cod_stru_att
            
            FROM MonitoraggioBundle:VistaAP05 AP05
            INNER JOIN AP05.struttura_attuativa as tc15
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
