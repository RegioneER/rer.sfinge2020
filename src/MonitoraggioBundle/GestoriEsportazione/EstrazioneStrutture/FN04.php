<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FN04 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',	// A
            'Codice impegno',			// B
            'Tipologia impegno',		// C
            'Data impegno',				// D
            'Importo impegno',			// E
            'Causale disimpegno',		// F
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
        ];
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				impegno.cod_locale_progetto,
				impegno.cod_impegno,
				impegno.tipologia_impegno,
				impegno.data_impegno as data_impegno,
				impegno.importo_impegno as importo_impegno,
				tc38.causale_disimpegno

			FROM MonitoraggioBundle:VistaFN04 impegno
			LEFT JOIN impegno.tc38_causale_disimpegno as tc38
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
