<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FN03 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Anno',
            'Importo realizzato',
            'Importo da realizzare',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
            'D' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
        ];
    }

    protected function configura(): void {
        $this->strictCompareNullCell = true;
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT 
            fn03.cod_locale_progetto,
            fn03.anno_piano,
            fn03.imp_realizzato,
            fn03.imp_da_realizzare
        FROM MonitoraggioBundle:VistaFN03 AS fn03
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
