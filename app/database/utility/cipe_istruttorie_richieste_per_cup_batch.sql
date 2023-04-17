INSERT INTO `istruttorie_richieste` 
(`richiesta_id`,`fase_id`,`esito_id`,`cup_natura_id`,`cup_tipologia_id`,`cup_settore_id`,`cup_sottosettore_id`,`cup_categoria_id`,`costo_ammesso`,`contributo_ammesso`,`richiedi_cup`,`validazione`,`codice_cup`,`note`,`data_verbalizzazione`,`data_cancellazione`,`data_creazione`,`data_modifica`,`creato_da`,`modificato_da`) 
SELECT r.id, 1,1,5,44,8,37,532,132750.00,66375.00,0,NULL,NULL,NULL,'2016-04-29',NULL,'2016-04-29 10:51:13','2016-04-29 10:54:02','TRDNTN83M12C421H','TRDNTN83M12C421H'
FROM `richieste` r WHERE r.stato_id = 5 AND r.procedura_id IN (3,4);


UPDATE `istruttorie_richieste` SET richiedi_cup=1;

INSERT INTO `istruttorie_cup_tipi_copertura_richieste` (`istruttoriarichiesta_id`, `cuptipocoperturafinanziaria_id`)
SELECT i.id, 7 FROM `istruttorie_richieste` i JOIN `richieste` r ON r.id = i.richiesta_id WHERE r.procedura_id = 3;


 