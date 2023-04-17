INSERT INTO `richieste_programmi` ( `programma_id`, `specifica_stato_id`, `richiesta_id`, `stato`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
	( 122, NULL, @id_richiesta, '1', NULL,NOW(), NULL, NULL, NULL);

