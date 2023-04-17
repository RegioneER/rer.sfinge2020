<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class AP06 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice regione',
            'Codice provincia',
            'Codice comune',
            'Indirizzo',
            'Codice CAP',
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
        $query = "SELECT ap06.cod_locale_progetto as cod_locale_progetto,
				tc16.codice_regione as regione,
				tc16.codice_provincia as provincia,
				tc16.codice_comune as comune,
                ap06.indirizzo as indirizzo,
                ap06.cod_cap as cap
			FROM MonitoraggioBundle:VistaAP06 as ap06
            INNER JOIN ap06.tc16_localizzazione_geografica as tc16
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
