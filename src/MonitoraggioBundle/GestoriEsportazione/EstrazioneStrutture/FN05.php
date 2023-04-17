<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;

class FN05 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',	// A
            'Codice impegno',			// B
            'Tipologia impegno',		// C
            'Data impegno',				// D
            'Codice programma',			// E
            'Codice livello gerarchico', // F
            'Data impegno ammesso',		// G
            'Tipologia impegno ammesso', // H
            'Causale dismpegno ammesso', // I
            'Importo impegno ammesso',	// J
            'Note impegno ammesso',		// K
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
            'K' => NumberFormat::FORMAT_TEXT,
        ];
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				fn05.cod_locale_progetto,
				fn05.cod_impegno,
				fn05.tipologia_impegno,
				fn05.data_impegno,
				tc4.cod_programma as codice_programma,
				tc36.cod_liv_gerarchico as codice_livello,
				fn05.data_imp_amm,
				fn05.tipologia_imp_amm as tipologia_amm_H,
				tc38.causale_disimpegno as causale_amm_I,
				fn05.importo_imp_amm as importo_amm_J,
				fn05.note_imp as note_K
				
			FROM MonitoraggioBundle:VistaFN05 as fn05
			INNER JOIN fn05.tc36_livello_gerarchico as tc36
			INNER JOIN fn05.tc4_programma tc4
			LEFT JOIN fn05.tc38_causale_disimpegno_amm as tc38
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
