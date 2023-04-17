<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class PR01 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice sttao progetto',
            'Data riferimento',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    protected function normalizeResult($riga): array {
        $res = \array_map(function ($chiave, $valore) {
            return ('stato_progetto' == $chiave && !\is_null($valore)) ?
                new StringWrapper($valore) :
                $valore;
        }, array_keys($riga), $riga);

        return parent::normalizeResult($res);
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				stato.cod_locale_progetto,
				tc47.stato_progetto as stato_progetto,
                stato.data_riferimento

			FROM MonitoraggioBundle:VistaPR01 as stato 
			INNER JOIN stato.tc47_stato_progetto as tc47
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
