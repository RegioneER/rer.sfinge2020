set @id_richiesta_protocollo = xxx;

UPDATE richieste_protocollo
SET istanza_processo_id = NULL, stato = "PRONTO_PER_PROTOCOLLAZIONE", fase = NULL, esito_fase = NULL, fascicolo = NULL, 
anno_pg = NULL, data_pg = NULL, num_pg = NULL, registro_pg = NULL, data_modifica = NULL, modificato_da = NULL
WHERE id = @id_richiesta_protocollo;

update richieste_protocollo_documenti
set idDocument = NULL, esito = 0, data_modifica = NULL, modificato_da = NULL where richiesta_protocollo_id = @id_richiesta_protocollo;
