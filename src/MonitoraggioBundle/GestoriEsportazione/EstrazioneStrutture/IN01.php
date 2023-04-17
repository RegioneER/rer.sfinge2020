<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class IN01 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Tipo indicatore output',
            'Codice indicatore',
            'Valore programmato',
            'Valore realizzato',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				in01.cod_locale_progetto,
				case when tc44_45 INSTANCE OF MonitoraggioBundle:TC44IndicatoriOutputComuni then 'COM' else 'DPR' end as tipo,
				tc44_45.cod_indicatore as codice,
				in01.val_programmato as val_programmato,
				in01.valore_realizzato as valore_realizzato

			FROM MonitoraggioBundle:VistaIN01 as in01
			INNER JOIN in01.indicatore as tc44_45
		";

        return $this->getEm()->createQuery($query)->getResult();
    }

    protected function configura(): void {
        $this->strictCompareNullCell = true;
    }
}
