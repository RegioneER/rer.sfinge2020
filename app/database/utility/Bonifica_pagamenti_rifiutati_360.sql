# 23 righe

UPDATE pagamenti p
JOIN `attuazione_controllo_richieste` a ON p.`attuazione_controllo_richiesta_id`=a.id
JOIN richieste r ON a.`richiesta_id`=r.id
SET p.`esito_istruttoria`=0
WHERE r.`procedura_id`=9 AND p.data_invio!=a.`data_avvio`