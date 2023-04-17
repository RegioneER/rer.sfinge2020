<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class PR00 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice fase',
            'Data inizio prevista',
            'Data inizio effettiva',
            'Data fine prevista',
            'Data fine effettiva',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    protected function normalizeResult($riga): array {
        $res = \array_map(function ($chiave, $valore) {
            return ('cod_fase' == $chiave && !\is_null($valore)) ?
                new StringWrapper($valore) :
                $valore;
        }, array_keys($riga), $riga);

        return parent::normalizeResult($res);
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				iter.cod_locale_progetto,
				tc46.cod_fase as cod_fase,
                iter.data_inizio_prevista,
                iter.data_inizio_effettiva,
                iter.data_fine_prevista,
                iter.data_fine_effettiva

			FROM MonitoraggioBundle:VistaPR00 as iter 
			INNER JOIN iter.tc46_fase_procedurale as tc46
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
