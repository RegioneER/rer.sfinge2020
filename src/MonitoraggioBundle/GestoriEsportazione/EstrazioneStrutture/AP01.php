<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AP01 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice procedura attivazione',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
        ];
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				ap01.cod_locale_progetto,
				tc1.cod_proc_att

			    FROM MonitoraggioBundle:VistaAP01 ap01
                LEFT JOIN ap01.tc1_procedura_attivazione as tc1
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
