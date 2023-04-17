SET foreign_key_checks = 0;
/*
-- Query: SELECT * FROM `sfinge2020-dcannistraro`.processi
-- Date: 2016-02-23 11:47
*/
INSERT INTO `processi` (`id`,`attivo`,`codice`,`descrizione`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (1,1,'protocollazione_domande_contributo','Cron che serve per la protocollazione delle domande di richiesta di contributo',NULL,NOW(),NULL,NULL,NULL);
INSERT INTO `processi` (`id`,`attivo`,`codice`,`descrizione`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (2,1,'protocollazione_comunicazioni','Cron che serve per la protocollazione delle comunicazione in particolare le richieste di rendiconto',NULL,NOW(),NULL,NULL,NULL);

/*
-- Query: SELECT * FROM `sfinge2020-dcannistraro`.istanze_processi
-- Date: 2016-02-23 11:50
*/
INSERT INTO `istanze_processi` 
(`id`,`processo_id`,`data_avvio`,`data_fine`,`elementi_elaborati`,`stato`,`esito`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (1,1,'2015-11-27 04:31:19','2015-11-27 04:31:19',NULL,NULL,NULL,NULL,NOW(),NULL,NULL,NULL);
INSERT INTO `istanze_processi` 
(`id`,`processo_id`,`data_avvio`,`data_fine`,`elementi_elaborati`,`stato`,`esito`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (2,1,'2015-11-30 12:24:21','2015-11-30 12:24:21',NULL,NULL,NULL,NULL,NOW(),NULL,NULL,NULL);

INSERT INTO `istanze_processi` (
`id`,`processo_id`,`data_avvio`,`data_fine`,`elementi_elaborati`,`stato`,`esito`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (3,2,'2015-12-17 08:00:00','2015-12-17 08:00:00',NULL,NULL,NULL,NULL,NOW(),NULL,NULL,NULL);
INSERT INTO `istanze_processi` 
(`id`,`processo_id`,`data_avvio`,`data_fine`,`elementi_elaborati`,`stato`,`esito`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (4,2,'2015-12-16 12:05:55',NULL,NULL,NULL,NULL,NULL,NOW(),NULL,NULL,NULL);

/*
-- Query: SELECT * FROM `sfinge2020-dcannistraro`.richieste_protocollo
-- Date: 2016-02-23 11:52
*/

-- PROCESSO: 1 
INSERT INTO `richieste_protocollo` 
(`id`,`processo_id`,`istanza_processo_id`,`procedura_id`,`richiesta_id`,`tipo`,`data_creazione_richiesta`,`data_richiesta`,`oggetto`,`stato`,`fase`,`esito_fase`,`fascicolo`,`anno_pg`,`data_pg`,`num_pg`,`oggetto_pg`,`registro_pg`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (142,1,NULL,1,1,'FINANZIAMENTO','2015-10-05 10:45:40','2015-10-05 10:45:40','Domanda di contributo n.8237 Istituto Ortopedico Rizzoli 00302030374','PRONTO_PER_PROTOCOLLAZIONE',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NOW(),NULL,NULL,NULL);
INSERT INTO `richieste_protocollo` 
(`id`,`processo_id`,`istanza_processo_id`,`procedura_id`,`richiesta_id`,`tipo`,`data_creazione_richiesta`,`data_richiesta`,`oggetto`,`stato`,`fase`,`esito_fase`,`fascicolo`,`anno_pg`,`data_pg`,`num_pg`,`oggetto_pg`,`registro_pg`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (144,1,NULL,1,1,'FINANZIAMENTO','2015-10-05 11:08:19','2015-10-05 11:08:19','Domanda di contributo n.8135 RE:Lab S.r.l. 02131390359','PRONTO_PER_PROTOCOLLAZIONE',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NOW(),NULL,NULL,NULL);

-- PROCESSO: 2
INSERT INTO `richieste_protocollo` 
(`id`,`processo_id`,`istanza_processo_id`,`procedura_id`,`richiesta_id`,`tipo`,`data_creazione_richiesta`,`data_richiesta`,`oggetto`,`stato`,`fase`,`esito_fase`,`fascicolo`,`anno_pg`,`data_pg`,`num_pg`,`oggetto_pg`,`registro_pg`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (264,2,4,1,1,'FINANZIAMENTO','2015-11-16 09:45:31','2015-11-16 09:45:31','Comunicazione di variazione del progetto PG/2015/199819 MODELLERIA BRAMBILLA S.P.A. 01763310354','IN_LAVORAZIONE',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NOW(),NULL,NULL,NULL);
INSERT INTO `richieste_protocollo` 
(`id`,`processo_id`,`istanza_processo_id`,`procedura_id`,`richiesta_id`,`tipo`,`data_creazione_richiesta`,`data_richiesta`,`oggetto`,`stato`,`fase`,`esito_fase`,`fascicolo`,`anno_pg`,`data_pg`,`num_pg`,`oggetto_pg`,`registro_pg`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (265,2,NULL,1,1,'FINANZIAMENTO','2015-11-16 10:20:47','2015-11-16 10:20:47','Comunicazione di variazione del progetto PG/2015/198285 UNIONCOOP - UNIONE COOPERATIVE SERVIZI DI ASSISTENZA - SOCIETA\' COOPERATIVA O IN FORMA ABBREVIATA UN 00323070359','PRONTO_PER_PROTOCOLLAZIONE',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NOW(),NULL,NULL,NULL);

/*
-- Query: SELECT * FROM `sfinge2020-dcannistraro`.richieste_protocollo
-- Date: 2016-02-23 11:52
*/

-- PROCESSO: 1 
INSERT INTO `richieste_protocollo` 
(`id`,`processo_id`,`istanza_processo_id`,`procedura_id`,`richiesta_id`,`tipo`,`data_creazione_richiesta`,`data_richiesta`,`oggetto`,`stato`,`fase`,`esito_fase`,`fascicolo`,`anno_pg`,`data_pg`,`num_pg`,`oggetto_pg`,`registro_pg`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (142,1,NULL,1,1,'FINANZIAMENTO','2015-10-05 10:45:40','2015-10-05 10:45:40','Domanda di contributo n.8237 Istituto Ortopedico Rizzoli 00302030374','PRONTO_PER_PROTOCOLLAZIONE',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NOW(),NULL,NULL,NULL);
INSERT INTO `richieste_protocollo` 
(`id`,`processo_id`,`istanza_processo_id`,`procedura_id`,`richiesta_id`,`tipo`,`data_creazione_richiesta`,`data_richiesta`,`oggetto`,`stato`,`fase`,`esito_fase`,`fascicolo`,`anno_pg`,`data_pg`,`num_pg`,`oggetto_pg`,`registro_pg`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (144,1,NULL,1,1,'FINANZIAMENTO','2015-10-05 11:08:19','2015-10-05 11:08:19','Domanda di contributo n.8135 RE:Lab S.r.l. 02131390359','PRONTO_PER_PROTOCOLLAZIONE',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NOW(),NULL,NULL,NULL);

-- PROCESSO: 2
INSERT INTO `richieste_protocollo` 
(`id`,`processo_id`,`istanza_processo_id`,`procedura_id`,`richiesta_id`,`tipo`,`data_creazione_richiesta`,`data_richiesta`,`oggetto`,`stato`,`fase`,`esito_fase`,`fascicolo`,`anno_pg`,`data_pg`,`num_pg`,`oggetto_pg`,`registro_pg`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (264,2,4,1,1,'FINANZIAMENTO','2015-11-16 09:45:31','2015-11-16 09:45:31','Comunicazione di variazione del progetto PG/2015/199819 MODELLERIA BRAMBILLA S.P.A. 01763310354','IN_LAVORAZIONE',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NOW(),NULL,NULL,NULL);
INSERT INTO `richieste_protocollo` 
(`id`,`processo_id`,`istanza_processo_id`,`procedura_id`,`richiesta_id`,`tipo`,`data_creazione_richiesta`,`data_richiesta`,`oggetto`,`stato`,`fase`,`esito_fase`,`fascicolo`,`anno_pg`,`data_pg`,`num_pg`,`oggetto_pg`,`registro_pg`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (265,2,NULL,1,1,'FINANZIAMENTO','2015-11-16 10:20:47','2015-11-16 10:20:47','Comunicazione di variazione del progetto PG/2015/198285 UNIONCOOP - UNIONE COOPERATIVE SERVIZI DI ASSISTENZA - SOCIETA\' COOPERATIVA O IN FORMA ABBREVIATA UN 00323070359','PRONTO_PER_PROTOCOLLAZIONE',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NOW(),NULL,NULL,NULL);


/*
-- Query: SELECT * FROM `sfinge2020-dcannistraro`.richieste_protocollo_documenti
-- Date: 2016-02-23 11:55
*/

INSERT INTO `richieste_protocollo_documenti` 
(`id`,`richiesta_protocollo_id`,`tabella_documento`,`tabella_documento_id`,`path`,`idDocument`,`esito`,`principale`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (10,142,'pre_aziende_allegati',NULL,'/Users/dcannistraro/Sites/richfin/bando_373/kp_richiesta_finanziamento_8237/29_richiesta_finanziamento__8237_1560.p7m',NULL,0,1,NULL,NOW(),NULL,NULL,NULL);
INSERT INTO `richieste_protocollo_documenti` 
(`id`,`richiesta_protocollo_id`,`tabella_documento`,`tabella_documento_id`,`path`,`idDocument`,`esito`,`principale`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (12,142,'pre_aziende_allegati',NULL,'/Users/dcannistraro/Sites/richfin/bando_373/kp_richiesta_finanziamento_8237/24_dichiarazione_sostitutiva_8518_.pdf',NULL,0,0,NULL,NOW(),NULL,NULL,NULL);
INSERT INTO `richieste_protocollo_documenti` 
(`id`,`richiesta_protocollo_id`,`tabella_documento`,`tabella_documento_id`,`path`,`idDocument`,`esito`,`principale`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (14,142,'pre_aziende_allegati',NULL,'/Users/dcannistraro/Sites/richfin/bando_373/kp_richiesta_finanziamento_8237/24_dichiarazione_sostitutiva_8595_.pdf',NULL,0,0,NULL,NOW(),NULL,NULL,NULL);
INSERT INTO `richieste_protocollo_documenti` 
(`id`,`richiesta_protocollo_id`,`tabella_documento`,`tabella_documento_id`,`path`,`idDocument`,`esito`,`principale`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (20,144,'pre_aziende_allegati',NULL,'/Users/dcannistraro/Sites/_richfin/bando_373/kp_richiesta_finanziamento_8135/29_richiesta_finanziamento__8135_1562.p7m',NULL,0,1,NULL,NOW(),NULL,NULL,NULL);
INSERT INTO `richieste_protocollo_documenti` 
(`id`,`richiesta_protocollo_id`,`tabella_documento`,`tabella_documento_id`,`path`,`idDocument`,`esito`,`principale`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (30,264,'geco_variazioni_progetto',NULL,'/Users/dcannistraro/Sites/richfin/bando_360/6224/carta_identita_variazioni_progetto45f31d16b1058d586fc3be7207b58053.p7m',NULL,0,1,NULL,NOW(),NULL,NULL,NULL);
INSERT INTO `richieste_protocollo_documenti` 
(`id`,`richiesta_protocollo_id`,`tabella_documento`,`tabella_documento_id`,`path`,`idDocument`,`esito`,`principale`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (31,265,'geco_variazioni_progetto',NULL,'/Users/dcannistraro/Sites/richfin/bando_360/6699/variazioni_progetto_firmato_2ac2406e835bd49c70469acae337d292.p7m',NULL,0,1,NULL,NOW(),NULL,NULL,NULL);
INSERT INTO `richieste_protocollo_documenti` 
(`id`,`richiesta_protocollo_id`,`tabella_documento`,`tabella_documento_id`,`path`,`idDocument`,`esito`,`principale`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
VALUES (32,265,'geco_variazioni_progetto',NULL,'/Users/dcannistraro/Sites/richfin/bando_360/6699/carta_identita_variazioni_progetto2ac2406e835bd49c70469acae337d292.pdf',NULL,0,0,NULL,NOW(),NULL,NULL,NULL);

SET foreign_key_checks = 1;
