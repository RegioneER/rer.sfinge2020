<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FN06 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice pagamento',
            'Tipologia',
            'Data',
            'Importo',
            'Causale',
            'Note',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'E' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
        ];
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
                fn06.cod_locale_progetto,
				fn06.cod_pagamento,
				fn06.tipologia_pag,
				fn06.data_pagamento,
				fn06.importo_pag,
				tc39.causale_pagamento,
				fn06.note_pag

			FROM MonitoraggioBundle:VistaFN06 fn06
            INNER JOIN fn06.tc39_causale_pagamento tc39
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
