<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FN07 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice pagamento',
            'Tipologia pagamento',
            'Data pagamento',
            'Codice programma',
            'Codice livello gerarchico',
            'Data pagamento ammesso',
            'Tipologia pagamento ammesso',
            'Causale pagamento ammesso',
            'Importo ammesso',
            'Note',
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
				ammesso.cod_locale_progetto,
				ammesso.cod_pagamento,
				ammesso.tipologia_pag,
				ammesso.data_pagamento,
				tc4.cod_programma as cod_programma,
                tc36.cod_liv_gerarchico as liv_gerarchico,
                ammesso.data_pag_amm,
                ammesso.tipologia_pag_amm,
                tc39.causale_pagamento as causale_pag_amm,
                ammesso.importo_pag_amm,
                ammesso.note_pag

			FROM MonitoraggioBundle:VistaFN07 ammesso         

            INNER JOIN ammesso.tc36_livello_gerarchico as tc36
            INNER JOIN ammesso.tc39_causale_pagamento as tc39
            INNER JOIN ammesso.tc4_programma as tc4
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
