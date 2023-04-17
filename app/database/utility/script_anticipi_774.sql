SET @atc_id = (SELECT ar.id FROM `richieste_protocollo` rp 
JOIN `attuazione_controllo_richieste` ar ON ar.`richiesta_id` = rp.`richiesta_id` 
JOIN `pagamenti` p ON p.`attuazione_controllo_richiesta_id` = ar.id
where rp.`num_pg` = 730232 AND rp.`anno_pg` = 2015 AND p.data_cancellazione IS NULL); SET @data_invio = '2016-12-31';
SET @importo_richiesto = 250000;INSERT INTO `pagamenti` ( `attuazione_controllo_richiesta_id`, `documento_pagamento_id`, `documento_pagamento_firmato_id`, `firmatario_id`, `stato_id`, `modalita_pagamento_id`, `istanza_fascicolo_id`, `documento_integrazione_id`, `integrazione_di_id`, `mandato_pagamento_id`, `data_invio`, `banca`, `intestatario`, `agenzia`, `iban`, `importo_pagamento`, `esito_istruttoria`, `nota_integrazione`, `integrazione_sostanziale`, `importo_richiesto`, `data_fideiussione`, `importo_certificato`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`  )VALUES
 (@atc_id, NULL, NULL, NULL, 10, 1, NULL, NULL, NULL, NULL, @data_invio, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, @importo_richiesto, NULL,NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T');SET @pagamento_id = (SELECT p.`id` FROM `pagamenti` p WHERE p.`attuazione_controllo_richiesta_id` = @atc_id AND p.`stato_id` = 10 AND p.`modalita_pagamento_id` = 1 AND p.`importo_richiesto` = @importo_richiesto AND p.`creato_da` = 'DMCVCN81A15G273T');SET @data_pg = '2016-11-11';INSERT INTO `richieste_protocollo` (`processo_id`, `istanza_processo_id`, `procedura_id`, `richiesta_id`, `tipo`, `data_creazione_richiesta`, `data_invio_PA`, `oggetto`, `stato`, `fase`, `esito_fase`, `fascicolo`, `anno_pg`, `data_pg`, `num_pg`,`oggetto_pg`,`registro_pg`, `anno_pg_validazione`, `registro_pg_validazione`, `num_pg_validazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `pagamento_id`, `variazione_id`, `proroga_id`, `integrazione_id`,`risposta_integrazione_id`, `integrazione_pagamento_id`, `risposta_integrazione_pagamento_id`, `comunicazione_esito_id`, `risposta_comunicazione_id`, `esito_istruttoria_pagamento_id`, `richiesta_chiarimenti_id`, `risposta_richiesta_chiarimenti_id`)
VALUES
 (NULL, NULL, 8, NULL, 'PAGAMENTO', now(), now(), 'Domanda di pagamento Anticipo bando 774', 'POST_PROTOCOLLAZIONE', 0, 1, NULL, '2016', @data_pg, '713324', NULL, 'PG', NULL, NULL, NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T',@pagamento_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
 
 
SET @atc_id = (SELECT ar.id FROM `richieste_protocollo` rp 
JOIN `attuazione_controllo_richieste` ar ON ar.`richiesta_id` = rp.`richiesta_id` 
JOIN `pagamenti` p ON p.`attuazione_controllo_richiesta_id` = ar.id
where rp.`num_pg` = 731626 AND rp.`anno_pg` = 2015 AND p.data_cancellazione IS NULL); SET @data_invio = '2016-12-31';
SET @importo_richiesto = 249090.63;INSERT INTO `pagamenti` ( `attuazione_controllo_richiesta_id`, `documento_pagamento_id`, `documento_pagamento_firmato_id`, `firmatario_id`, `stato_id`, `modalita_pagamento_id`, `istanza_fascicolo_id`, `documento_integrazione_id`, `integrazione_di_id`, `mandato_pagamento_id`, `data_invio`, `banca`, `intestatario`, `agenzia`, `iban`, `importo_pagamento`, `esito_istruttoria`, `nota_integrazione`, `integrazione_sostanziale`, `importo_richiesto`, `data_fideiussione`, `importo_certificato`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`  )VALUES
 (@atc_id, NULL, NULL, NULL, 10, 1, NULL, NULL, NULL, NULL, @data_invio, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, @importo_richiesto, NULL,NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T');SET @pagamento_id = (SELECT p.`id` FROM `pagamenti` p WHERE p.`attuazione_controllo_richiesta_id` = @atc_id AND p.`stato_id` = 10 AND p.`modalita_pagamento_id` = 1 AND p.`importo_richiesto` = @importo_richiesto AND p.`creato_da` = 'DMCVCN81A15G273T');SET @data_pg = '2016-11-11';INSERT INTO `richieste_protocollo` (`processo_id`, `istanza_processo_id`, `procedura_id`, `richiesta_id`, `tipo`, `data_creazione_richiesta`, `data_invio_PA`, `oggetto`, `stato`, `fase`, `esito_fase`, `fascicolo`, `anno_pg`, `data_pg`, `num_pg`,`oggetto_pg`,`registro_pg`, `anno_pg_validazione`, `registro_pg_validazione`, `num_pg_validazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `pagamento_id`, `variazione_id`, `proroga_id`, `integrazione_id`,`risposta_integrazione_id`, `integrazione_pagamento_id`, `risposta_integrazione_pagamento_id`, `comunicazione_esito_id`, `risposta_comunicazione_id`, `esito_istruttoria_pagamento_id`, `richiesta_chiarimenti_id`, `risposta_richiesta_chiarimenti_id`)
VALUES
 (NULL, NULL, 8, NULL, 'PAGAMENTO', now(), now(), 'Domanda di pagamento Anticipo bando 774', 'POST_PROTOCOLLAZIONE', 0, 1, NULL, '2016', @data_pg, '713353', NULL, 'PG', NULL, NULL, NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T',@pagamento_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
 
 
SET @atc_id = (SELECT ar.id FROM `richieste_protocollo` rp 
JOIN `attuazione_controllo_richieste` ar ON ar.`richiesta_id` = rp.`richiesta_id` 
JOIN `pagamenti` p ON p.`attuazione_controllo_richiesta_id` = ar.id
where rp.`num_pg` = 726492 AND rp.`anno_pg` = 2015 AND p.data_cancellazione IS NULL); SET @data_invio = '2016-12-31';
SET @importo_richiesto = 233995.59;INSERT INTO `pagamenti` ( `attuazione_controllo_richiesta_id`, `documento_pagamento_id`, `documento_pagamento_firmato_id`, `firmatario_id`, `stato_id`, `modalita_pagamento_id`, `istanza_fascicolo_id`, `documento_integrazione_id`, `integrazione_di_id`, `mandato_pagamento_id`, `data_invio`, `banca`, `intestatario`, `agenzia`, `iban`, `importo_pagamento`, `esito_istruttoria`, `nota_integrazione`, `integrazione_sostanziale`, `importo_richiesto`, `data_fideiussione`, `importo_certificato`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`  )VALUES
 (@atc_id, NULL, NULL, NULL, 10, 1, NULL, NULL, NULL, NULL, @data_invio, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, @importo_richiesto, NULL,NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T');SET @pagamento_id = (SELECT p.`id` FROM `pagamenti` p WHERE p.`attuazione_controllo_richiesta_id` = @atc_id AND p.`stato_id` = 10 AND p.`modalita_pagamento_id` = 1 AND p.`importo_richiesto` = @importo_richiesto AND p.`creato_da` = 'DMCVCN81A15G273T');SET @data_pg = '2016-11-16';INSERT INTO `richieste_protocollo` (`processo_id`, `istanza_processo_id`, `procedura_id`, `richiesta_id`, `tipo`, `data_creazione_richiesta`, `data_invio_PA`, `oggetto`, `stato`, `fase`, `esito_fase`, `fascicolo`, `anno_pg`, `data_pg`, `num_pg`,`oggetto_pg`,`registro_pg`, `anno_pg_validazione`, `registro_pg_validazione`, `num_pg_validazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `pagamento_id`, `variazione_id`, `proroga_id`, `integrazione_id`,`risposta_integrazione_id`, `integrazione_pagamento_id`, `risposta_integrazione_pagamento_id`, `comunicazione_esito_id`, `risposta_comunicazione_id`, `esito_istruttoria_pagamento_id`, `richiesta_chiarimenti_id`, `risposta_richiesta_chiarimenti_id`)
VALUES
 (NULL, NULL, 8, NULL, 'PAGAMENTO', now(), now(), 'Domanda di pagamento Anticipo bando 774', 'POST_PROTOCOLLAZIONE', 0, 1, NULL, '2016', @data_pg, '720811', NULL, 'PG', NULL, NULL, NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T',@pagamento_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
 
 
SET @atc_id = (SELECT ar.id FROM `richieste_protocollo` rp 
JOIN `attuazione_controllo_richieste` ar ON ar.`richiesta_id` = rp.`richiesta_id` 
JOIN `pagamenti` p ON p.`attuazione_controllo_richiesta_id` = ar.id
where rp.`num_pg` = 737416 AND rp.`anno_pg` = 2015 AND p.data_cancellazione IS NULL); SET @data_invio = '2016-12-31';
SET @importo_richiesto = 163187.5;INSERT INTO `pagamenti` ( `attuazione_controllo_richiesta_id`, `documento_pagamento_id`, `documento_pagamento_firmato_id`, `firmatario_id`, `stato_id`, `modalita_pagamento_id`, `istanza_fascicolo_id`, `documento_integrazione_id`, `integrazione_di_id`, `mandato_pagamento_id`, `data_invio`, `banca`, `intestatario`, `agenzia`, `iban`, `importo_pagamento`, `esito_istruttoria`, `nota_integrazione`, `integrazione_sostanziale`, `importo_richiesto`, `data_fideiussione`, `importo_certificato`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`  )VALUES
 (@atc_id, NULL, NULL, NULL, 10, 1, NULL, NULL, NULL, NULL, @data_invio, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, @importo_richiesto, NULL,NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T');SET @pagamento_id = (SELECT p.`id` FROM `pagamenti` p WHERE p.`attuazione_controllo_richiesta_id` = @atc_id AND p.`stato_id` = 10 AND p.`modalita_pagamento_id` = 1 AND p.`importo_richiesto` = @importo_richiesto AND p.`creato_da` = 'DMCVCN81A15G273T');SET @data_pg = '2016-07-25';INSERT INTO `richieste_protocollo` (`processo_id`, `istanza_processo_id`, `procedura_id`, `richiesta_id`, `tipo`, `data_creazione_richiesta`, `data_invio_PA`, `oggetto`, `stato`, `fase`, `esito_fase`, `fascicolo`, `anno_pg`, `data_pg`, `num_pg`,`oggetto_pg`,`registro_pg`, `anno_pg_validazione`, `registro_pg_validazione`, `num_pg_validazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `pagamento_id`, `variazione_id`, `proroga_id`, `integrazione_id`,`risposta_integrazione_id`, `integrazione_pagamento_id`, `risposta_integrazione_pagamento_id`, `comunicazione_esito_id`, `risposta_comunicazione_id`, `esito_istruttoria_pagamento_id`, `richiesta_chiarimenti_id`, `risposta_richiesta_chiarimenti_id`)
VALUES
 (NULL, NULL, 8, NULL, 'PAGAMENTO', now(), now(), 'Domanda di pagamento Anticipo bando 774', 'POST_PROTOCOLLAZIONE', 0, 1, NULL, '2016', @data_pg, '0548585', NULL, 'PG', NULL, NULL, NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T',@pagamento_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
 
 
SET @atc_id = (SELECT ar.id FROM `richieste_protocollo` rp 
JOIN `attuazione_controllo_richieste` ar ON ar.`richiesta_id` = rp.`richiesta_id` 
JOIN `pagamenti` p ON p.`attuazione_controllo_richiesta_id` = ar.id
where rp.`num_pg` = 731060 AND rp.`anno_pg` = 2015 AND p.data_cancellazione IS NULL); SET @data_invio = '2016-12-31';
SET @importo_richiesto = 191851.91;INSERT INTO `pagamenti` ( `attuazione_controllo_richiesta_id`, `documento_pagamento_id`, `documento_pagamento_firmato_id`, `firmatario_id`, `stato_id`, `modalita_pagamento_id`, `istanza_fascicolo_id`, `documento_integrazione_id`, `integrazione_di_id`, `mandato_pagamento_id`, `data_invio`, `banca`, `intestatario`, `agenzia`, `iban`, `importo_pagamento`, `esito_istruttoria`, `nota_integrazione`, `integrazione_sostanziale`, `importo_richiesto`, `data_fideiussione`, `importo_certificato`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`  )VALUES
 (@atc_id, NULL, NULL, NULL, 10, 1, NULL, NULL, NULL, NULL, @data_invio, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, @importo_richiesto, NULL,NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T');SET @pagamento_id = (SELECT p.`id` FROM `pagamenti` p WHERE p.`attuazione_controllo_richiesta_id` = @atc_id AND p.`stato_id` = 10 AND p.`modalita_pagamento_id` = 1 AND p.`importo_richiesto` = @importo_richiesto AND p.`creato_da` = 'DMCVCN81A15G273T');SET @data_pg = '2016-09-30';INSERT INTO `richieste_protocollo` (`processo_id`, `istanza_processo_id`, `procedura_id`, `richiesta_id`, `tipo`, `data_creazione_richiesta`, `data_invio_PA`, `oggetto`, `stato`, `fase`, `esito_fase`, `fascicolo`, `anno_pg`, `data_pg`, `num_pg`,`oggetto_pg`,`registro_pg`, `anno_pg_validazione`, `registro_pg_validazione`, `num_pg_validazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `pagamento_id`, `variazione_id`, `proroga_id`, `integrazione_id`,`risposta_integrazione_id`, `integrazione_pagamento_id`, `risposta_integrazione_pagamento_id`, `comunicazione_esito_id`, `risposta_comunicazione_id`, `esito_istruttoria_pagamento_id`, `richiesta_chiarimenti_id`, `risposta_richiesta_chiarimenti_id`)
VALUES
 (NULL, NULL, 8, NULL, 'PAGAMENTO', now(), now(), 'Domanda di pagamento Anticipo bando 774', 'POST_PROTOCOLLAZIONE', 0, 1, NULL, '2016', @data_pg, '0641168', NULL, 'PG', NULL, NULL, NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T',@pagamento_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
 
 
SET @atc_id = (SELECT ar.id FROM `richieste_protocollo` rp 
JOIN `attuazione_controllo_richieste` ar ON ar.`richiesta_id` = rp.`richiesta_id` 
JOIN `pagamenti` p ON p.`attuazione_controllo_richiesta_id` = ar.id
where rp.`num_pg` = 726417 AND rp.`anno_pg` = 2015 AND p.data_cancellazione IS NULL); SET @data_invio = '2016-12-31';
SET @importo_richiesto = 153947.15;INSERT INTO `pagamenti` ( `attuazione_controllo_richiesta_id`, `documento_pagamento_id`, `documento_pagamento_firmato_id`, `firmatario_id`, `stato_id`, `modalita_pagamento_id`, `istanza_fascicolo_id`, `documento_integrazione_id`, `integrazione_di_id`, `mandato_pagamento_id`, `data_invio`, `banca`, `intestatario`, `agenzia`, `iban`, `importo_pagamento`, `esito_istruttoria`, `nota_integrazione`, `integrazione_sostanziale`, `importo_richiesto`, `data_fideiussione`, `importo_certificato`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`  )VALUES
 (@atc_id, NULL, NULL, NULL, 10, 1, NULL, NULL, NULL, NULL, @data_invio, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, @importo_richiesto, NULL,NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T');SET @pagamento_id = (SELECT p.`id` FROM `pagamenti` p WHERE p.`attuazione_controllo_richiesta_id` = @atc_id AND p.`stato_id` = 10 AND p.`modalita_pagamento_id` = 1 AND p.`importo_richiesto` = @importo_richiesto AND p.`creato_da` = 'DMCVCN81A15G273T');SET @data_pg = '2016-07-28';INSERT INTO `richieste_protocollo` (`processo_id`, `istanza_processo_id`, `procedura_id`, `richiesta_id`, `tipo`, `data_creazione_richiesta`, `data_invio_PA`, `oggetto`, `stato`, `fase`, `esito_fase`, `fascicolo`, `anno_pg`, `data_pg`, `num_pg`,`oggetto_pg`,`registro_pg`, `anno_pg_validazione`, `registro_pg_validazione`, `num_pg_validazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `pagamento_id`, `variazione_id`, `proroga_id`, `integrazione_id`,`risposta_integrazione_id`, `integrazione_pagamento_id`, `risposta_integrazione_pagamento_id`, `comunicazione_esito_id`, `risposta_comunicazione_id`, `esito_istruttoria_pagamento_id`, `richiesta_chiarimenti_id`, `risposta_richiesta_chiarimenti_id`)
VALUES
 (NULL, NULL, 8, NULL, 'PAGAMENTO', now(), now(), 'Domanda di pagamento Anticipo bando 774', 'POST_PROTOCOLLAZIONE', 0, 1, NULL, '2016', @data_pg, '0555967', NULL, 'PG', NULL, NULL, NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T',@pagamento_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
 
 
SET @atc_id = (SELECT ar.id FROM `richieste_protocollo` rp 
JOIN `attuazione_controllo_richieste` ar ON ar.`richiesta_id` = rp.`richiesta_id` 
JOIN `pagamenti` p ON p.`attuazione_controllo_richiesta_id` = ar.id
where rp.`num_pg` = 731768 AND rp.`anno_pg` = 2015 AND p.data_cancellazione IS NULL); SET @data_invio = '2016-02-28';
SET @importo_richiesto = 249997.2;INSERT INTO `pagamenti` ( `attuazione_controllo_richiesta_id`, `documento_pagamento_id`, `documento_pagamento_firmato_id`, `firmatario_id`, `stato_id`, `modalita_pagamento_id`, `istanza_fascicolo_id`, `documento_integrazione_id`, `integrazione_di_id`, `mandato_pagamento_id`, `data_invio`, `banca`, `intestatario`, `agenzia`, `iban`, `importo_pagamento`, `esito_istruttoria`, `nota_integrazione`, `integrazione_sostanziale`, `importo_richiesto`, `data_fideiussione`, `importo_certificato`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`  )VALUES
 (@atc_id, NULL, NULL, NULL, 10, 1, NULL, NULL, NULL, NULL, @data_invio, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, @importo_richiesto, NULL,NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T');SET @pagamento_id = (SELECT p.`id` FROM `pagamenti` p WHERE p.`attuazione_controllo_richiesta_id` = @atc_id AND p.`stato_id` = 10 AND p.`modalita_pagamento_id` = 1 AND p.`importo_richiesto` = @importo_richiesto AND p.`creato_da` = 'DMCVCN81A15G273T');SET @data_pg = '2016-07-08';INSERT INTO `richieste_protocollo` (`processo_id`, `istanza_processo_id`, `procedura_id`, `richiesta_id`, `tipo`, `data_creazione_richiesta`, `data_invio_PA`, `oggetto`, `stato`, `fase`, `esito_fase`, `fascicolo`, `anno_pg`, `data_pg`, `num_pg`,`oggetto_pg`,`registro_pg`, `anno_pg_validazione`, `registro_pg_validazione`, `num_pg_validazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `pagamento_id`, `variazione_id`, `proroga_id`, `integrazione_id`,`risposta_integrazione_id`, `integrazione_pagamento_id`, `risposta_integrazione_pagamento_id`, `comunicazione_esito_id`, `risposta_comunicazione_id`, `esito_istruttoria_pagamento_id`, `richiesta_chiarimenti_id`, `risposta_richiesta_chiarimenti_id`)
VALUES
 (NULL, NULL, 8, NULL, 'PAGAMENTO', now(), now(), 'Domanda di pagamento Anticipo bando 774', 'POST_PROTOCOLLAZIONE', 0, 1, NULL, '2016', @data_pg, '0517982', NULL, 'PG', NULL, NULL, NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T',@pagamento_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
 
 
SET @atc_id = (SELECT ar.id FROM `richieste_protocollo` rp 
JOIN `attuazione_controllo_richieste` ar ON ar.`richiesta_id` = rp.`richiesta_id` 
JOIN `pagamenti` p ON p.`attuazione_controllo_richiesta_id` = ar.id
where rp.`num_pg` = 731976 AND rp.`anno_pg` = 2015 AND p.data_cancellazione IS NULL); SET @data_invio = '2016-02-28';
SET @importo_richiesto = 249918.31;INSERT INTO `pagamenti` ( `attuazione_controllo_richiesta_id`, `documento_pagamento_id`, `documento_pagamento_firmato_id`, `firmatario_id`, `stato_id`, `modalita_pagamento_id`, `istanza_fascicolo_id`, `documento_integrazione_id`, `integrazione_di_id`, `mandato_pagamento_id`, `data_invio`, `banca`, `intestatario`, `agenzia`, `iban`, `importo_pagamento`, `esito_istruttoria`, `nota_integrazione`, `integrazione_sostanziale`, `importo_richiesto`, `data_fideiussione`, `importo_certificato`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`  )VALUES
 (@atc_id, NULL, NULL, NULL, 10, 1, NULL, NULL, NULL, NULL, @data_invio, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, @importo_richiesto, NULL,NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T');SET @pagamento_id = (SELECT p.`id` FROM `pagamenti` p WHERE p.`attuazione_controllo_richiesta_id` = @atc_id AND p.`stato_id` = 10 AND p.`modalita_pagamento_id` = 1 AND p.`importo_richiesto` = @importo_richiesto AND p.`creato_da` = 'DMCVCN81A15G273T');SET @data_pg = '2016-07-08';INSERT INTO `richieste_protocollo` (`processo_id`, `istanza_processo_id`, `procedura_id`, `richiesta_id`, `tipo`, `data_creazione_richiesta`, `data_invio_PA`, `oggetto`, `stato`, `fase`, `esito_fase`, `fascicolo`, `anno_pg`, `data_pg`, `num_pg`,`oggetto_pg`,`registro_pg`, `anno_pg_validazione`, `registro_pg_validazione`, `num_pg_validazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `pagamento_id`, `variazione_id`, `proroga_id`, `integrazione_id`,`risposta_integrazione_id`, `integrazione_pagamento_id`, `risposta_integrazione_pagamento_id`, `comunicazione_esito_id`, `risposta_comunicazione_id`, `esito_istruttoria_pagamento_id`, `richiesta_chiarimenti_id`, `risposta_richiesta_chiarimenti_id`)
VALUES
 (NULL, NULL, 8, NULL, 'PAGAMENTO', now(), now(), 'Domanda di pagamento Anticipo bando 774', 'POST_PROTOCOLLAZIONE', 0, 1, NULL, '2016', @data_pg, '0517993', NULL, 'PG', NULL, NULL, NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T',@pagamento_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
 
 
SET @atc_id = (SELECT ar.id FROM `richieste_protocollo` rp 
JOIN `attuazione_controllo_richieste` ar ON ar.`richiesta_id` = rp.`richiesta_id` 
JOIN `pagamenti` p ON p.`attuazione_controllo_richiesta_id` = ar.id
where rp.`num_pg` = 730277 AND rp.`anno_pg` = 2015 AND p.data_cancellazione IS NULL); SET @data_invio = '2016-02-28';
SET @importo_richiesto = 249978.22;INSERT INTO `pagamenti` ( `attuazione_controllo_richiesta_id`, `documento_pagamento_id`, `documento_pagamento_firmato_id`, `firmatario_id`, `stato_id`, `modalita_pagamento_id`, `istanza_fascicolo_id`, `documento_integrazione_id`, `integrazione_di_id`, `mandato_pagamento_id`, `data_invio`, `banca`, `intestatario`, `agenzia`, `iban`, `importo_pagamento`, `esito_istruttoria`, `nota_integrazione`, `integrazione_sostanziale`, `importo_richiesto`, `data_fideiussione`, `importo_certificato`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`  )VALUES
 (@atc_id, NULL, NULL, NULL, 10, 1, NULL, NULL, NULL, NULL, @data_invio, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, @importo_richiesto, NULL,NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T');SET @pagamento_id = (SELECT p.`id` FROM `pagamenti` p WHERE p.`attuazione_controllo_richiesta_id` = @atc_id AND p.`stato_id` = 10 AND p.`modalita_pagamento_id` = 1 AND p.`importo_richiesto` = @importo_richiesto AND p.`creato_da` = 'DMCVCN81A15G273T');SET @data_pg = '2016-07-01';INSERT INTO `richieste_protocollo` (`processo_id`, `istanza_processo_id`, `procedura_id`, `richiesta_id`, `tipo`, `data_creazione_richiesta`, `data_invio_PA`, `oggetto`, `stato`, `fase`, `esito_fase`, `fascicolo`, `anno_pg`, `data_pg`, `num_pg`,`oggetto_pg`,`registro_pg`, `anno_pg_validazione`, `registro_pg_validazione`, `num_pg_validazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `pagamento_id`, `variazione_id`, `proroga_id`, `integrazione_id`,`risposta_integrazione_id`, `integrazione_pagamento_id`, `risposta_integrazione_pagamento_id`, `comunicazione_esito_id`, `risposta_comunicazione_id`, `esito_istruttoria_pagamento_id`, `richiesta_chiarimenti_id`, `risposta_richiesta_chiarimenti_id`)
VALUES
 (NULL, NULL, 8, NULL, 'PAGAMENTO', now(), now(), 'Domanda di pagamento Anticipo bando 774', 'POST_PROTOCOLLAZIONE', 0, 1, NULL, '2016', @data_pg, '0507733', NULL, 'PG', NULL, NULL, NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T',@pagamento_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
 
 
SET @atc_id = (SELECT ar.id FROM `richieste_protocollo` rp 
JOIN `attuazione_controllo_richieste` ar ON ar.`richiesta_id` = rp.`richiesta_id` 
JOIN `pagamenti` p ON p.`attuazione_controllo_richiesta_id` = ar.id
where rp.`num_pg` = 731587 AND rp.`anno_pg` = 2015 AND p.data_cancellazione IS NULL); SET @data_invio = '2016-02-28';
SET @importo_richiesto = 235950;INSERT INTO `pagamenti` ( `attuazione_controllo_richiesta_id`, `documento_pagamento_id`, `documento_pagamento_firmato_id`, `firmatario_id`, `stato_id`, `modalita_pagamento_id`, `istanza_fascicolo_id`, `documento_integrazione_id`, `integrazione_di_id`, `mandato_pagamento_id`, `data_invio`, `banca`, `intestatario`, `agenzia`, `iban`, `importo_pagamento`, `esito_istruttoria`, `nota_integrazione`, `integrazione_sostanziale`, `importo_richiesto`, `data_fideiussione`, `importo_certificato`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`  )VALUES
 (@atc_id, NULL, NULL, NULL, 10, 1, NULL, NULL, NULL, NULL, @data_invio, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, @importo_richiesto, NULL,NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T');SET @pagamento_id = (SELECT p.`id` FROM `pagamenti` p WHERE p.`attuazione_controllo_richiesta_id` = @atc_id AND p.`stato_id` = 10 AND p.`modalita_pagamento_id` = 1 AND p.`importo_richiesto` = @importo_richiesto AND p.`creato_da` = 'DMCVCN81A15G273T');SET @data_pg = '2016-07-07';INSERT INTO `richieste_protocollo` (`processo_id`, `istanza_processo_id`, `procedura_id`, `richiesta_id`, `tipo`, `data_creazione_richiesta`, `data_invio_PA`, `oggetto`, `stato`, `fase`, `esito_fase`, `fascicolo`, `anno_pg`, `data_pg`, `num_pg`,`oggetto_pg`,`registro_pg`, `anno_pg_validazione`, `registro_pg_validazione`, `num_pg_validazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `pagamento_id`, `variazione_id`, `proroga_id`, `integrazione_id`,`risposta_integrazione_id`, `integrazione_pagamento_id`, `risposta_integrazione_pagamento_id`, `comunicazione_esito_id`, `risposta_comunicazione_id`, `esito_istruttoria_pagamento_id`, `richiesta_chiarimenti_id`, `risposta_richiesta_chiarimenti_id`)
VALUES
 (NULL, NULL, 8, NULL, 'PAGAMENTO', now(), now(), 'Domanda di pagamento Anticipo bando 774', 'POST_PROTOCOLLAZIONE', 0, 1, NULL, '2016', @data_pg, '0516943', NULL, 'PG', NULL, NULL, NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T',@pagamento_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
 
 
SET @atc_id = (SELECT ar.id FROM `richieste_protocollo` rp 
JOIN `attuazione_controllo_richieste` ar ON ar.`richiesta_id` = rp.`richiesta_id` 
JOIN `pagamenti` p ON p.`attuazione_controllo_richiesta_id` = ar.id
where rp.`num_pg` = 731607 AND rp.`anno_pg` = 2015 AND p.data_cancellazione IS NULL); SET @data_invio = '2016-02-28';
SET @importo_richiesto = 203462.5;INSERT INTO `pagamenti` ( `attuazione_controllo_richiesta_id`, `documento_pagamento_id`, `documento_pagamento_firmato_id`, `firmatario_id`, `stato_id`, `modalita_pagamento_id`, `istanza_fascicolo_id`, `documento_integrazione_id`, `integrazione_di_id`, `mandato_pagamento_id`, `data_invio`, `banca`, `intestatario`, `agenzia`, `iban`, `importo_pagamento`, `esito_istruttoria`, `nota_integrazione`, `integrazione_sostanziale`, `importo_richiesto`, `data_fideiussione`, `importo_certificato`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`  )VALUES
 (@atc_id, NULL, NULL, NULL, 10, 1, NULL, NULL, NULL, NULL, @data_invio, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, @importo_richiesto, NULL,NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T');SET @pagamento_id = (SELECT p.`id` FROM `pagamenti` p WHERE p.`attuazione_controllo_richiesta_id` = @atc_id AND p.`stato_id` = 10 AND p.`modalita_pagamento_id` = 1 AND p.`importo_richiesto` = @importo_richiesto AND p.`creato_da` = 'DMCVCN81A15G273T');SET @data_pg = '2016-09-08';INSERT INTO `richieste_protocollo` (`processo_id`, `istanza_processo_id`, `procedura_id`, `richiesta_id`, `tipo`, `data_creazione_richiesta`, `data_invio_PA`, `oggetto`, `stato`, `fase`, `esito_fase`, `fascicolo`, `anno_pg`, `data_pg`, `num_pg`,`oggetto_pg`,`registro_pg`, `anno_pg_validazione`, `registro_pg_validazione`, `num_pg_validazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `pagamento_id`, `variazione_id`, `proroga_id`, `integrazione_id`,`risposta_integrazione_id`, `integrazione_pagamento_id`, `risposta_integrazione_pagamento_id`, `comunicazione_esito_id`, `risposta_comunicazione_id`, `esito_istruttoria_pagamento_id`, `richiesta_chiarimenti_id`, `risposta_richiesta_chiarimenti_id`)
VALUES
 (NULL, NULL, 8, NULL, 'PAGAMENTO', now(), now(), 'Domanda di pagamento Anticipo bando 774', 'POST_PROTOCOLLAZIONE', 0, 1, NULL, '2016', @data_pg, '0603969', NULL, 'PG', NULL, NULL, NULL, NULL, now(), now(), 'DMCVCN81A15G273T', 'DMCVCN81A15G273T',@pagamento_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
 