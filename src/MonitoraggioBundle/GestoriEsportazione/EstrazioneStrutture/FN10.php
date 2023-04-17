<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class FN10 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice fondo',
            'Importo',
        ];
    }
    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
        ];
	}
	
	protected function getQueryResult(): iterable {
		$query = "SELECT
				COALESCE(
					CONCAT(protocollo.registro_pg, '/', protocollo.anno_pg, '/', protocollo.num_pg), 
					richiesta.id
				) as cod_locale_progetto,
				tc33.cod_fondo as cod_fondo,
				economia.importo as importo

			FROM AttuazioneControlloBundle:Economia as economia
			INNER JOIN economia.richiesta as richiesta
			INNER JOIN richiesta.richieste_protocollo as protocollo WITH protocollo INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento
			INNER JOIN richiesta.attuazione_controllo as atc
			INNER JOIN economia.tc33_fonte_finanziaria as tc33
			WHERE richiesta.flag_por = 1
		";
		
		return $this->getEm()->createQuery($query)->getResult();
	}
}