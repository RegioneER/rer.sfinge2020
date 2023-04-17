-- AP05
    CREATE OR REPLACE VIEW vista_ap05 AS
SELECT
    richieste.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richieste.id
    ) AS cod_locale_progetto,
    tc15.id AS struttura_attuativa_id
FROM
    richieste
    INNER JOIN richieste_protocollo protocollo ON richieste.id = protocollo.richiesta_id
    AND protocollo.tipo = 'FINANZIAMENTO'
    AND protocollo.data_cancellazione IS NULL
    INNER JOIN attuazione_controllo_richieste atc ON richieste.id = atc.richiesta_id
    AND (atc.data_cancellazione IS NULL)
    INNER JOIN istruttorie_richieste istruttoria ON richieste.id = istruttoria.richiesta_id
    AND istruttoria.data_cancellazione IS NULL
    INNER JOIN richiesta_strumento_attuativo rsa on rsa.richiesta_id = richieste.id
    INNER JOIN tc15_strumento_attuativo tc15 ON rsa.stru_att_id = tc15.id
WHERE
    richieste.flag_por = 1
    AND richieste.data_cancellazione IS NULL;