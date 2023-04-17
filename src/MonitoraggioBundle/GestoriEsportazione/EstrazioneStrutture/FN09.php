<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FN09 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Domanda pagamento',
            'Tipologia importo',
            'Data domanda',
            'Livello gerarchico',
            'Importo spesa totale',
            'Importo spesa pubblica',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
            'G' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
        ];
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
                    fn09.cod_locale_progetto,
                    fn09.cod_pagamento,
                    fn09.tipologia_pag,
                    fn09.data_pagamento,
                    tc36.cod_liv_gerarchico as liv_gerarchico,
                    fn09.importo_totale,
                    fn09.importo_spesa_pubblica
		FROM MonitoraggioBundle:VistaFN09 AS fn09
                INNER JOIN fn09.tc36_livello_gerarchico as tc36";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
