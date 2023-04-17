<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class IN00 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Tipo indicatore output',
            'Codice indicatore',
        ];
    }
    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
        ];
	}
	
	protected function getQueryResult(): iterable {
		$query = "SELECT
				in00.cod_locale_progetto,
				case when indicatore INSTANCE OF MonitoraggioBundle:TC43IndicatoriRisultatoProgramma then 'DPR' else 'COM' end as tipo,
				indicatore.cod_indicatore as cod_indicatore

			FROM MonitoraggioBundle:VistaIN00 as in00
			INNER JOIN in00.indicatore as indicatore
		";
		
		return $this->getEm()->createQuery($query)->getResult();
	}
}