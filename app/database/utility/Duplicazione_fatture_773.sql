select id from voci_piano_costo_giustificativi where `giustificativo_pagamento_id` in (SELECT id FROM `giustificativi_pagamenti` WHERE `pagamento_id` = '739' and `tipologia_giustificativo_id` = 4);

update voci_piano_costo_giustificativi set importo = importo * 2, importo_approvato = importo_approvato * 2 where id in (21110,21111,21129,21130,
21147,21148,21151,21152,21242,21243,21248,21249,21258,21259,21265,21266,21287,21288,21289,21290,21293,21294,
21300,21301,21305,21306,21316,21317,21324,21325,21327,21328,21329,21330,21351,21352,21353,21354,21357,21358,21359,21360,
21363,21364,21371,21372,21427,21428,21433,21434,21441,21442,21450,21451,21454,21455,21460,21461,21466,21467,22696,22697);

INSERT INTO `contratti` ( `tipologia_spesa_id`, `pagamento_id`, `tipologia_fornitore_id`, `data_inizio`, `descrizione`, `fornitore`, `numero`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `titolo_brevetto`, `importo_contratto_complessivo`, `alta_tecnologia`, `referente`, `attivita`, `numero_domanda_brevetto`, `data_domanda_brevetto`, `stato_brevetto`, `ambito_brevetto`, `proponente_id`, `gestione_ipr_brevetto`, `importo_eleggibilita`, `importo_eleggibilita_istruttoria`, `contratto_clonato_id`)
VALUES
	( 2, 739, 5, '2017-02-01', 'CONSULENZA PROGETTAZIONE ELETTRICA CABINA DI COLLAUDO', 'STUDIO INGEGNERIA MAGRINELLI ANGELO', '170201', NULL, '2017-05-10 15:22:17', '2017-05-10 15:22:17', 'GLLLCU59H06A944Y', 'GLLLCU59H06A944Y', NULL, 9500.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

SET @contr_cabina = last_insert_id();

INSERT INTO `contratti` ( `tipologia_spesa_id`, `pagamento_id`, `tipologia_fornitore_id`, `data_inizio`, `descrizione`, `fornitore`, `numero`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `titolo_brevetto`, `importo_contratto_complessivo`, `alta_tecnologia`, `referente`, `attivita`, `numero_domanda_brevetto`, `data_domanda_brevetto`, `stato_brevetto`, `ambito_brevetto`, `proponente_id`, `gestione_ipr_brevetto`, `importo_eleggibilita`, `importo_eleggibilita_istruttoria`, `contratto_clonato_id`)
VALUES
	( 2, 739, 5, '2016-09-14', 'PROGETTAZIONE ESECUTIVA CAMERA CLIMATICA CON STRUTTURE DI SOSTEGNO, STESURA LOGICA DI FUNZIONAMENTO E CONTROLLO.', 'DEL ZOPPO ANTONIO', '20160914', NULL, '2017-05-10 15:28:46', '2017-05-10 15:28:46', 'GLLLCU59H06A944Y', 'GLLLCU59H06A944Y', NULL, 34500.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

SET @contr_zoppo = last_insert_id();


##FATTURA GAMMA##

INSERT INTO `estensioni_giustificativi` ( `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `tipo`, `ricercatore_id`)
VALUES
	( NULL, '2018-01-10 16:10:40', '2018-01-15 15:27:51', 'GLLLCU59H06A944Y', 'GLLLCU59H06A944Y', 'BANDO_7', NULL);

SET @estensione = last_insert_id();

INSERT INTO `estensioni_giustificativi_bando_7` (`id`, `nome`, `cognome`, `data_assunzione`, `attivita`, `data_inizio`, 
`data_fine`, `costo_orario`, `numero_ore_ri`, `numero_ore_ss`, `tipologia_fornitore_id`, `cespite_pronto_uso`, `importo_bene`, 
`coefficiente_ammortamento`, `giorni_utilizzo`, `percentuale_uso`, `quota_netta`, `tipologia_spesa_id`, `data_consegna_bene`, 
`totale_importi_quietanzati`, `numero_prima_fattura`, `data_prima_fattura`, `numero_ultima_fattura`, `data_ultima_fattura`, 
`numero_rate_rendicontate`, `data_primo_bonifico`, `data_ultimo_bonifico`, `tipologia_contratto`, `mansione`, `referente`, 
`alta_tecnologia`, `descrizione_attrezzatura`, `giustificazione_attrezzatura`, `descrizione_contratto`, `titolo_brevetto`, 
`data_inizio_contratto`, `numero_domanda_brevetto`, `data_domanda_brevetto`, `stato_brevetto`, `ambito_brevetto`, `numero_ore_totale`)
VALUES
	(@estensione, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 5, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
 NULL, NULL, NULL, NULL, 'GAMMA PREFABBRICATI SRL', NULL, NULL, NULL, NULL, 
NULL, NULL, NULL, NULL, NULL, NULL, NULL);


INSERT INTO `giustificativi_pagamenti` ( `pagamento_id`, `documento_giustificativo_id`, `integrazione_di_id`, `denominazione_fornitore`, 
`codice_fiscale_fornitore`, `descrizione_giustificativo`, `numero_giustificativo`, `data_giustificativo`, `importo_imponibile_giustificativo`, 
`importo_iva_giustificativo`, `importo_giustificativo`, `importo_richiesto`, `importo_approvato`, `nota_beneficiario`, `integrazione`, 
`nota_integrazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `tipologia_giustificativo_id`, 
`estensione_id`, `proponente_id`, `contratto_id`, `istruttoria_oggetto_pagamento_id`)
VALUES
	( 739, NULL, NULL, 'GAMMA PREFABBRICATI SRL', NULL, 'CABINE ELETTRICHE PREFABBRICATE', '54', '2017-06-27', 
NULL, NULL, 3870.00, NULL, NULL, NULL, NULL, NULL, NULL, '2018-01-10 16:09:00', '2018-02-01 14:21:05', 'GLLLCU59H06A944Y', 'FRSRRT74T51D862D', 
6, @estensione, NULL, @contr_cabina, NULL);

SET @giust = last_insert_id();

INSERT INTO `voci_piano_costo_giustificativi` (`giustificativo_pagamento_id`, `voce_piano_costo_id`, `voce_piano_costo_istruttoria_id`, `importo`, `importo_approvato`, `annualita`, `nota`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `importo_non_ammesso_per_superamento_massimali`, `nota_superamento_massimali`, `spesa_soggetta_limite_30`)
VALUES
	( @giust, 17164, 8326, 3870.00, 0.00, '1', NULL, NULL, '2018-01-10 16:10:40', '2018-02-01 14:19:46', 'GLLLCU59H06A944Y', 'FRSRRT74T51D862D', NULL, NULL, NULL),
	( @giust, 17158, 8320, 0.00, 0.00, '1', NULL, NULL, '2018-01-10 16:10:40', '2018-02-01 14:19:59', 'GLLLCU59H06A944Y', 'FRSRRT74T51D862D', NULL, NULL, NULL);


##04 ZOPPO##

INSERT INTO `estensioni_giustificativi` ( `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `tipo`, `ricercatore_id`)
VALUES
	( NULL, '2017-05-10 17:41:15', '2017-05-15 10:55:07', 'GLLLCU59H06A944Y', 'GLLLCU59H06A944Y', 'BANDO_7', NULL);

SET @estensione = last_insert_id();

INSERT INTO `estensioni_giustificativi_bando_7` (`id`, `nome`, `cognome`, `data_assunzione`, `attivita`, `data_inizio`, `data_fine`, 
`costo_orario`, `numero_ore_ri`, `numero_ore_ss`, `tipologia_fornitore_id`, `cespite_pronto_uso`, `importo_bene`, `coefficiente_ammortamento`, 
`giorni_utilizzo`, `percentuale_uso`, `quota_netta`, `tipologia_spesa_id`, `data_consegna_bene`, `totale_importi_quietanzati`, 
`numero_prima_fattura`, `data_prima_fattura`, `numero_ultima_fattura`, `data_ultima_fattura`, `numero_rate_rendicontate`, `data_primo_bonifico`, 
`data_ultimo_bonifico`, `tipologia_contratto`, `mansione`, `referente`, `alta_tecnologia`, `descrizione_attrezzatura`, `giustificazione_attrezzatura`, 
`descrizione_contratto`, `titolo_brevetto`, `data_inizio_contratto`, `numero_domanda_brevetto`, `data_domanda_brevetto`, `stato_brevetto`, 
`ambito_brevetto`, `numero_ore_totale`)
VALUES
	(@estensione, NULL, NULL, NULL, 'Progettazione esecutiva della struttura e impianti idraulici di compensazione della nuova camera di prova',
 NULL, NULL, NULL, 0.00, 0.00, 5, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 
NULL, 'DEL ZOPPO ANTONIO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);


INSERT INTO `giustificativi_pagamenti` ( `pagamento_id`, `documento_giustificativo_id`, `integrazione_di_id`, 
`denominazione_fornitore`, `codice_fiscale_fornitore`, `descrizione_giustificativo`, `numero_giustificativo`, 
`data_giustificativo`, `importo_imponibile_giustificativo`, `importo_iva_giustificativo`, `importo_giustificativo`,
 `importo_richiesto`, `importo_approvato`, `nota_beneficiario`, `integrazione`, `nota_integrazione`, `data_cancellazione`, 
`data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `tipologia_giustificativo_id`, `estensione_id`,
 `proponente_id`, `contratto_id`, `istruttoria_oggetto_pagamento_id`)
VALUES
	( 739, NULL, NULL, 'DEL ZOPPO ANTONIO', NULL, 'PROGETTAZIONE CABINA COLLAUDO - ACCONTO', '04', '2017-01-10', 
	NULL, NULL, 11419.20, NULL, NULL, NULL, NULL, NULL, NULL, '2017-05-10 17:39:43', '2017-07-04 10:53:25', 'GLLLCU59H06A944Y', 'FRSRRT74T51D862D'
	, 6, @estensione, NULL, @contr_zoppo, NULL);

SET @giust = last_insert_id();

INSERT INTO `voci_piano_costo_giustificativi` ( `giustificativo_pagamento_id`, `voce_piano_costo_id`, `voce_piano_costo_istruttoria_id`, `importo`, `importo_approvato`, `annualita`, `nota`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `importo_non_ammesso_per_superamento_massimali`, `nota_superamento_massimali`, `spesa_soggetta_limite_30`)
VALUES
	( @giust, 17164, 8326, 360.00, 360.00, '1', NULL, NULL, '2017-05-10 17:41:15', '2017-07-04 10:52:02', 'GLLLCU59H06A944Y', 'FRSRRT74T51D862D', NULL, NULL, NULL),
	( @giust, 17158, 8320, 0.00, 0.00, '1', NULL, NULL, '2017-05-10 17:41:15', '2017-07-04 11:00:44', 'GLLLCU59H06A944Y', 'FRSRRT74T51D862D', NULL, NULL, NULL);

##06 ZOPPO##

INSERT INTO `estensioni_giustificativi` ( `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `tipo`, `ricercatore_id`)
VALUES
	( NULL, '2017-05-10 17:42:52', '2017-05-15 10:55:32', 'GLLLCU59H06A944Y', 'GLLLCU59H06A944Y', 'BANDO_7', NULL);

SET @estensione = last_insert_id();

INSERT INTO `estensioni_giustificativi_bando_7` (`id`, `nome`, `cognome`, `data_assunzione`, `attivita`, `data_inizio`, `data_fine`, `costo_orario`, `numero_ore_ri`, `numero_ore_ss`, `tipologia_fornitore_id`, `cespite_pronto_uso`, `importo_bene`, `coefficiente_ammortamento`, `giorni_utilizzo`, `percentuale_uso`, `quota_netta`, `tipologia_spesa_id`, `data_consegna_bene`, `totale_importi_quietanzati`, `numero_prima_fattura`, `data_prima_fattura`, `numero_ultima_fattura`, `data_ultima_fattura`, `numero_rate_rendicontate`, `data_primo_bonifico`, `data_ultimo_bonifico`, `tipologia_contratto`, `mansione`, `referente`, `alta_tecnologia`, `descrizione_attrezzatura`, `giustificazione_attrezzatura`, `descrizione_contratto`, `titolo_brevetto`, `data_inizio_contratto`, `numero_domanda_brevetto`, `data_domanda_brevetto`, `stato_brevetto`, `ambito_brevetto`, `numero_ore_totale`)
VALUES
	(@estensione, NULL, NULL, NULL, 'Progettazione esecutiva della struttura e impianti idraulici di compensazione della nuova camera di prova', NULL, NULL, NULL, 0.00, 0.00, 5, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DEL ZOPPO ANTONIO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO `giustificativi_pagamenti` ( `pagamento_id`, `documento_giustificativo_id`, `integrazione_di_id`, `denominazione_fornitore`, `codice_fiscale_fornitore`, `descrizione_giustificativo`, `numero_giustificativo`, `data_giustificativo`, `importo_imponibile_giustificativo`, `importo_iva_giustificativo`, `importo_giustificativo`, `importo_richiesto`, `importo_approvato`, `nota_beneficiario`, `integrazione`, `nota_integrazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `tipologia_giustificativo_id`, `estensione_id`, `proponente_id`, `contratto_id`, `istruttoria_oggetto_pagamento_id`)
VALUES
	( 739, NULL, NULL, 'DEL ZOPPO ANTONIO', NULL, 'PROGETTAZIONE CABINA COLLAUDO - PRIMA RATA', '06', '2017-03-01', NULL, NULL, 10150.40, NULL, NULL, NULL, NULL, NULL, NULL, '2017-05-10 17:41:26', '2017-07-04 11:10:54', 'GLLLCU59H06A944Y', 'FRSRRT74T51D862D', 6, @estensione, NULL, @contr_zoppo, NULL);

SET @giust = last_insert_id();

INSERT INTO `voci_piano_costo_giustificativi` ( `giustificativo_pagamento_id`, `voce_piano_costo_id`, `voce_piano_costo_istruttoria_id`, `importo`, `importo_approvato`, `annualita`, `nota`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `importo_non_ammesso_per_superamento_massimali`, `nota_superamento_massimali`, `spesa_soggetta_limite_30`)
VALUES
	( @giust, 17164, 8326, 320.00, 320.00, '1', NULL, NULL, '2017-05-10 17:42:52', '2017-07-04 11:10:37', 'GLLLCU59H06A944Y', 'FRSRRT74T51D862D', NULL, NULL, NULL),
	( @giust, 17158, 8320, 0.00, 0.00, '1', NULL, NULL, '2017-05-10 17:42:52', '2017-07-04 11:10:47', 'GLLLCU59H06A944Y', 'FRSRRT74T51D862D', NULL, NULL, NULL);


##MAGRINELLI##

INSERT INTO `estensioni_giustificativi` ( `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `tipo`, `ricercatore_id`)
VALUES
	( NULL, '2017-05-10 17:46:15', '2017-05-15 10:54:42', 'GLLLCU59H06A944Y', 'GLLLCU59H06A944Y', 'BANDO_7', NULL);

SET @estensione = last_insert_id();

INSERT INTO `estensioni_giustificativi_bando_7` (`id`, `nome`, `cognome`, `data_assunzione`, `attivita`, `data_inizio`, `data_fine`, `costo_orario`, `numero_ore_ri`, `numero_ore_ss`, `tipologia_fornitore_id`, `cespite_pronto_uso`, `importo_bene`, `coefficiente_ammortamento`, `giorni_utilizzo`, `percentuale_uso`, `quota_netta`, `tipologia_spesa_id`, `data_consegna_bene`, `totale_importi_quietanzati`, `numero_prima_fattura`, `data_prima_fattura`, `numero_ultima_fattura`, `data_ultima_fattura`, `numero_rate_rendicontate`, `data_primo_bonifico`, `data_ultimo_bonifico`, `tipologia_contratto`, `mansione`, `referente`, `alta_tecnologia`, `descrizione_attrezzatura`, `giustificazione_attrezzatura`, `descrizione_contratto`, `titolo_brevetto`, `data_inizio_contratto`, `numero_domanda_brevetto`, `data_domanda_brevetto`, `stato_brevetto`, `ambito_brevetto`, `numero_ore_totale`)
VALUES
	(@estensione, NULL, NULL, NULL, 'Progettazione esecutiva degli impianti elettrici a servizio della nuova camera di prova', NULL, NULL, NULL, 0.00, 0.00, 5, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'MAGRINELLI ANGELO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO `giustificativi_pagamenti` ( `pagamento_id`, `documento_giustificativo_id`, `integrazione_di_id`, `denominazione_fornitore`, `codice_fiscale_fornitore`, `descrizione_giustificativo`, `numero_giustificativo`, `data_giustificativo`, `importo_imponibile_giustificativo`, `importo_iva_giustificativo`, `importo_giustificativo`, `importo_richiesto`, `importo_approvato`, `nota_beneficiario`, `integrazione`, `nota_integrazione`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `tipologia_giustificativo_id`, `estensione_id`, `proponente_id`, `contratto_id`, `istruttoria_oggetto_pagamento_id`)
VALUES
	( 739, NULL, NULL, 'STUDIO INGEGNERIA MAGRINELLI ANGELO', NULL, 'PROGETTAZIONE ELETTRICA CABINA COLLAUDO- ACCONTO', '02', '2017-03-09', NULL, NULL, 10248.00, NULL, NULL, NULL, NULL, NULL, NULL, '2017-05-10 17:45:06', '2017-07-04 11:15:49', 'GLLLCU59H06A944Y', 'FRSRRT74T51D862D', 6, @estension, NULL, @contr_cabina, NULL);

SET @giust = last_insert_id();

INSERT INTO `voci_piano_costo_giustificativi` ( `giustificativo_pagamento_id`, `voce_piano_costo_id`, `voce_piano_costo_istruttoria_id`, `importo`, `importo_approvato`, `annualita`, `nota`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `importo_non_ammesso_per_superamento_massimali`, `nota_superamento_massimali`, `spesa_soggetta_limite_30`)
VALUES
	( @giust, 17164, 8326, 400.00, 400.00, '1', NULL, NULL, '2017-05-10 17:46:15', '2017-07-04 11:15:23', 'GLLLCU59H06A944Y', 'FRSRRT74T51D862D', NULL, NULL, NULL),
	( @giust, 17158, 8320, 0.00, 0.00, '1', NULL, NULL, '2017-05-10 17:46:15', '2017-07-04 11:15:40', 'GLLLCU59H06A944Y', 'FRSRRT74T51D862D', NULL, NULL, NULL);
