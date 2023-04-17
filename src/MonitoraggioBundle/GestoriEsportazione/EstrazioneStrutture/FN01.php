<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class FN01 extends AEstrattoreStruttura {
    protected function getFirstLine(): array {
        return [
            'Codice locale progetto',
            'Codice programma',
            'Codice livello gerarchico',
            'Importo ammesso',
        ];
    }

    protected function getColumnsNumberFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
        ];
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				COALESCE(
					CONCAT(protocollo.registro_pg, '/', protocollo.anno_pg, '/', protocollo.num_pg), 
					richiesta.id
				) as cod_locale_progetto,
				tc4.cod_programma as cod_programma,
				tc36.cod_liv_gerarchico as codice_livello_gerarchico,
				livello.importo_costo_ammesso as importo

			FROM AttuazioneControlloBundle:RichiestaLivelloGerarchico livello
			INNER JOIN livello.richiesta_programma as programma
			INNER JOIN programma.richiesta as richiesta
			INNER JOIN richiesta.richieste_protocollo as protocollo WITH protocollo INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento
			INNER JOIN richiesta.attuazione_controllo as atc
			INNER JOIN richiesta.istruttoria as istruttoria
			
			LEFT JOIN livello.tc36_livello_gerarchico as tc36
			LEFT JOIN programma.tc4_programma as tc4

			WHERE richiesta.flag_por = 1
            and livello.importo_costo_ammesso is not null
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
