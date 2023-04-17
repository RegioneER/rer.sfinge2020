<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class FN00 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice fondo',
            'Codice norma',
            'Codice delibera CIPE',
            'Codice localizzazione',
            'CF cofinanziatore',
            'Importo',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
        ];
    }

    protected function configura(): void {
        $this->strictCompareNullCell = true;
    }

    protected function normalizeResult($riga): array {
        $res = \array_map(function ($chiave, $valore) {
            return (\in_array($chiave, [
                'cf_cofinanz',
                'cod_localizzazione',
            ]) && !\is_null($valore)) ?
                    new StringWrapper($valore) :
                       $valore;
        }, array_keys($riga), $riga);

        return parent::normalizeResult($res);
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				fn00.cod_locale_progetto,
				tc33.cod_fondo,
				tc35.cod_norma,
				tc34.cod_del_cipe,
				COALESCE(CONCAT(tc16.codice_regione, tc16.codice_provincia, tc16.codice_comune), '999999999') as cod_localizzazione,
				COALESCE(fn00.cf_cofinanz, '99999'),
				fn00.importo
			FROM MonitoraggioBundle:VistaFN00 fn00
			
			LEFT JOIN fn00.tc33_fonte_finanziaria as tc33
			LEFT JOIN fn00.tc34_delibera_cipe as tc34
			LEFT JOIN fn00.tc35_norma as tc35
			LEFT JOIN fn00.tc16_localizzazione_geografica as tc16
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
