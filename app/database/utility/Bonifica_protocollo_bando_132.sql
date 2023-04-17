
SELECT * FROM `richieste_protocollo` WHERE `procedura_id` = '132' and stato <> 'PRONTO_PER_PROTOCOLLAZIONE'


SELECT concat(richiesta_id,', ') FROM `richieste_protocollo` WHERE `procedura_id` = '132' and stato <> 'PRONTO_PER_PROTOCOLLAZIONE'


UPDATE richieste_protocollo
SET istanza_processo_id = NULL, stato = "PRONTO_PER_PROTOCOLLAZIONE", fase = NULL, esito_fase = NULL, fascicolo = NULL, 
anno_pg = NULL, data_pg = NULL, num_pg = NULL, registro_pg = NULL, data_modifica = NULL, modificato_da = NULL
WHERE id in (60288, 60289, 60290, 
60291, 60292, 60293, 60294, 60295, 60296, 60297, 60298, 60299, 60300, 60301, 60302, 60303, 60304, 60305, 
60306, 60307, 60308, 60309, 60310, 60311, 60312, 60313, 60314, 60315, 60316, 60317, 60318, 60319, 60320);

update richieste_protocollo_documenti
set idDocument = NULL, esito = 0, data_modifica = NULL, modificato_da = NULL where richiesta_protocollo_id = in (60288, 60289, 60290, 
60291, 60292, 60293, 60294, 60295, 60296, 60297, 60298, 60299, 60300, 60301, 60302, 60303, 60304, 60305, 
60306, 60307, 60308, 60309, 60310, 60311, 60312, 60313, 60314, 60315, 60316, 60317, 60318, 60319, 60320);


UPDATE `richieste` SET `stato_id` = '4' WHERE `id` IN (23443, 23373, 23443, 23362, 23358, 23386, 23372, 
23353, 23401, 23367, 23346, 23385, 23361, 23335, 23483, 23450, 23485, 23349, 23337, 23340, 
23486, 23445, 23393, 23489, 23382, 23495, 23486, 23410, 23482, 23481, 23463, 23355, 23413);