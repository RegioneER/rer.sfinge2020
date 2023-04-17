<?php

namespace MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture;

use MonitoraggioBundle\GestoriEsportazione\AEstrattoreStruttura;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use MonitoraggioBundle\Utils\StringWrapper;

class AP03 extends AEstrattoreStruttura {
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
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
        ];
    }

    protected function normalizeResult($riga): array {
        $res = \array_map(function ($chiave, $valore) {
            return ('class' == $chiave && !\is_null($valore)) ?
                new StringWrapper($valore) :
                $valore;
        }, array_keys($riga), $riga);

        return parent::normalizeResult($res);
    }

    protected function getQueryResult(): iterable {
        $query = "SELECT
				COALESCE(
					CONCAT(protocollo.registro_pg, '/', protocollo.anno_pg, '/', protocollo.num_pg), 
					richiesta.id
				) as cod_locale_progetto,
				tc4.cod_programma as cod_programma,
                tc11.tipo_class as tipo_class,
                tc12.codice as class

			FROM AttuazioneControlloBundle:RichiestaProgrammaClassificazione as classificazione
            INNER JOIN classificazione.richiesta_programma as programma
            INNER JOIN programma.richiesta as richiesta
			INNER JOIN richiesta.richieste_protocollo as protocollo WITH protocollo INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento
			INNER JOIN richiesta.attuazione_controllo as atc
			INNER JOIN richiesta.istruttoria as istruttoria
            INNER JOIN classificazione.classificazione as tc12
            INNER JOIN tc12.tipo_classificazione as tc11
            INNER JOIN programma.tc4_programma as tc4
			WHERE richiesta.flag_por = 1
		";

        return $this->getEm()->createQuery($query)->getResult();
    }
}
