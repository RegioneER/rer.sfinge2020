INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Mariarosaria','Avino ','VNAMRS86T57L245A','0515270000','MAVINO@INVITALIA.IT','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'VNAMRS86T57L245A');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'VNAMRS86T57L245A','VNAMRS86T57L245A','MAVINO@INVITALIA.IT','MAVINO@INVITALIA.IT', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Gabriele','Barone ','BRNGRL82A17H501S','0515270000','gbbarone@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'BRNGRL82A17H501S');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'BRNGRL82A17H501S','BRNGRL82A17H501S','gbbarone@invitalia.it','gbbarone@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Francesco','Barretta','BRRFNC84H16H703J','0515270000','fbarretta@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'BRRFNC84H16H703J');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'BRRFNC84H16H703J','BRRFNC84H16H703J','fbarretta@invitalia.it','fbarretta@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Alessandro','Brancaleoni ','BRNLSN78A08C980U','0515270000','abrancaleoni@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'BRNLSN78A08C980U');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'BRNLSN78A08C980U','BRNLSN78A08C980U','abrancaleoni@invitalia.it','abrancaleoni@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Silvia','Bressan','BRSSLV83B49H620O','0515270000','sbressan@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'BRSSLV83B49H620O');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'BRSSLV83B49H620O','BRSSLV83B49H620O','sbressan@invitalia.it','sbressan@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Raffaele','Cartocci ','CRTRFL83C17C469E','0515270000','rcartocci@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'CRTRFL83C17C469E');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'CRTRFL83C17C469E','CRTRFL83C17C469E','rcartocci@invitalia.it','rcartocci@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Francesco','Cavaliere Sgroi','CVLFNC88C03A773X','0515270000','fcavalieresgroi@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'CVLFNC88C03A773X');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'CVLFNC88C03A773X','CVLFNC88C03A773X','fcavalieresgroi@invitalia.it','fcavalieresgroi@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Giulia','Cavallari','CVLGLI83A57L424U','0515270000','gcavallari@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'CVLGLI83A57L424U');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'CVLGLI83A57L424U','CVLGLI83A57L424U','gcavallari@invitalia.it','gcavallari@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Giuseppe','Cavallaro ','CVLGPP86B22G273H','0515270000','gcavallaro@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'CVLGPP86B22G273H');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'CVLGPP86B22G273H','CVLGPP86B22G273H','gcavallaro@invitalia.it','gcavallaro@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Marco','D''Angelo','DNGMRC83S11H269B','0515270000','mrdangelo@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'DNGMRC83S11H269B');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'DNGMRC83S11H269B','DNGMRC83S11H269B','mrdangelo@invitalia.it','mrdangelo@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Tania','Daporto','DPRTNA90M46D705E','0515270000','tdaporto@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'DPRTNA90M46D705E');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'DPRTNA90M46D705E','DPRTNA90M46D705E','tdaporto@invitalia.it','tdaporto@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Marco','D''ascanio ','DSCMRC77D30A515K','0515270000','mdascanio@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'DSCMRC77D30A515K');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'DSCMRC77D30A515K','DSCMRC77D30A515K','mdascanio@invitalia.it','mdascanio@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Carla Maria Costanza','Di Martino ','DMRCLM87P47F899T','0515270000','cdimartino@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'DMRCLM87P47F899T');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'DMRCLM87P47F899T','DMRCLM87P47F899T','cdimartino@invitalia.it','cdimartino@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Giovanna','Elia ','LEIGNN83P67F839G','0515270000','gelia@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'LEIGNN83P67F839G');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'LEIGNN83P67F839G','LEIGNN83P67F839G','gelia@invitalia.it','gelia@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Maria Ludovica','Eusepi ','SPEMLD86C43H501M','0515270000','meusepi@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'SPEMLD86C43H501M');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'SPEMLD86C43H501M','SPEMLD86C43H501M','meusepi@invitalia.it','meusepi@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Benedetta','Ferri','FRRBDT90P57D548U','0515270000','bferri@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'FRRBDT90P57D548U');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'FRRBDT90P57D548U','FRRBDT90P57D548U','bferri@invitalia.it','bferri@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Andrea','Franceschi','FRNNDR82T19A944W','0515270000','afranceschi@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'FRNNDR82T19A944W');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'FRNNDR82T19A944W','FRNNDR82T19A944W','afranceschi@invitalia.it','afranceschi@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Martina','Giannerini','GNNMTN81R55A944L','0515270000','MGIANNERINI@INVITA-LIA.IT','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'GNNMTN81R55A944L');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'GNNMTN81R55A944L','GNNMTN81R55A944L','MGIANNERINI@INVITA-LIA.IT','MGIANNERINI@INVITA-LIA.IT', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Flavio','Giordano','GRDFLV87L13G273C','0515270000','fgiordano@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'GRDFLV87L13G273C');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'GRDFLV87L13G273C','GRDFLV87L13G273C','fgiordano@invitalia.it','fgiordano@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Vincenzo','Golisciano','GLSVCN88D04G793D','0515270000','vgolisciano@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'GLSVCN88D04G793D');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'GLSVCN88D04G793D','GLSVCN88D04G793D','vgolisciano@invitalia.it','vgolisciano@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Vincenzo','Granato','GRNVCN81P05A783D','0515270000','vgranato@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'GRNVCN81P05A783D');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'GRNVCN81P05A783D','GRNVCN81P05A783D','vgranato@invitalia.it','vgranato@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Giorgia','Gubinelli','GBNGRG88A68D451W','0515270000','ggubinelli@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'GBNGRG88A68D451W');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'GBNGRG88A68D451W','GBNGRG88A68D451W','ggubinelli@invitalia.it','ggubinelli@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Federica','Guggi','GGGFRC72B45D548W','0515270000','fguggi@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'GGGFRC72B45D548W');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'GGGFRC72B45D548W','GGGFRC72B45D548W','fguggi@invitalia.it','fguggi@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Andrea','Gurrieri','GRRNDR77C01L682P','0515270000','agurrieri@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'GRRNDR77C01L682P');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'GRRNDR77C01L682P','GRRNDR77C01L682P','agurrieri@invitalia.it','agurrieri@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Simula Juan Manuel','Herrera ','HRRJMN66H24H501V','0515270000','jherrerasimula@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'HRRJMN66H24H501V');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'HRRJMN66H24H501V','HRRJMN66H24H501V','jherrerasimula@invitalia.it','jherrerasimula@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Vittorio','Imperatori','MPRVTR86B20H534X','0515270000','vimperatori@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'MPRVTR86B20H534X');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'MPRVTR86B20H534X','MPRVTR86B20H534X','vimperatori@invitalia.it','vimperatori@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Nicoletta','Pandolfo','PNDNLT68P42L736K','0515270000','npandolfo@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'PNDNLT68P42L736K');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'PNDNLT68P42L736K','PNDNLT68P42L736K','npandolfo@invitalia.it','npandolfo@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Italia','Pascone','PSCTLI78M42H926Y','0515270000','IPASCONE@INVITA-LIA.IT','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'PSCTLI78M42H926Y');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'PSCTLI78M42H926Y','PSCTLI78M42H926Y','IPASCONE@INVITA-LIA.IT','IPASCONE@INVITA-LIA.IT', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Federica','Pennacchini ','PNNFRC82C62H294O','0515270000','fpennacchini@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'PNNFRC82C62H294O');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'PNNFRC82C62H294O','PNNFRC82C62H294O','fpennacchini@invitalia.it','fpennacchini@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Arcangelo','Petracca ','PTRRNG83P06I119N','0515270000','apetracca@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'PTRRNG83P06I119N');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'PTRRNG83P06I119N','PTRRNG83P06I119N','apetracca@invitalia.it','apetracca@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Gina','Renzullo','RNZGNI82D64H985O','0515270000','grenzullo@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'RNZGNI82D64H985O');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'RNZGNI82D64H985O','RNZGNI82D64H985O','grenzullo@invitalia.it','grenzullo@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Mario','Rizzato','RZZMRA82R26H501M','0515270000','mrizzato@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'RZZMRA82R26H501M');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'RZZMRA82R26H501M','RZZMRA82R26H501M','mrizzato@invitalia.it','mrizzato@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Sabrina','Rotondo','RTNSRN82H42C034S','0515270000','srotondo@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'RTNSRN82H42C034S');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'RTNSRN82H42C034S','RTNSRN82H42C034S','srotondo@invitalia.it','srotondo@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Massimo','Ruffini','RFFMSM55C15H199G','0515270000','mruffini@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'RFFMSM55C15H199G');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'RFFMSM55C15H199G','RFFMSM55C15H199G','mruffini@invitalia.it','mruffini@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Eolia','Ruggiero ','RGGLEO67S45Z614I','0515270000','eruggiero@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'RGGLEO67S45Z614I');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'RGGLEO67S45Z614I','RGGLEO67S45Z614I','eruggiero@invitalia.it','eruggiero@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Armando','Russo','RSSRND70A11D708G','0515270000','Istruttoriricostruzione10@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'RSSRND70A11D708G');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'RSSRND70A11D708G','RSSRND70A11D708G','Istruttoriricostruzione10@invitalia.it','Istruttoriricostruzione10@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Simone','Sbaraglia','SBRSMN86P14A944M','0515270000','ssbaraglia@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'SBRSMN86P14A944M');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'SBRSMN86P14A944M','SBRSMN86P14A944M','ssbaraglia@invitalia.it','ssbaraglia@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('ARIANNA','SIANO ','SNIRNN91A69I438B','0515270000','asiano@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'SNIRNN91A69I438B');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'SNIRNN91A69I438B','SNIRNN91A69I438B','asiano@invitalia.it','asiano@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Francesca Nina','Siciliano','SCLFNC83E52D086J','0515270000','fsiciliano@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'SCLFNC83E52D086J');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'SCLFNC83E52D086J','SCLFNC83E52D086J','fsiciliano@invitalia.it','fsiciliano@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Piergiorgio','Sonnessa','SNNPGR78R07F104A','0515270000','psonnessa@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'SNNPGR78R07F104A');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'SNNPGR78R07F104A','SNNPGR78R07F104A','psonnessa@invitalia.it','psonnessa@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Francesca Giovanna','Spinelli','SPNFNC85L61H703J','0515270000','fspinelli@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'SPNFNC85L61H703J');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'SPNFNC85L61H703J','SPNFNC85L61H703J','fspinelli@invitalia.it','fspinelli@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Serena','Spinelli ','SPNSRN89E61D761Z','0515270000','sspinelli@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'SPNSRN89E61D761Z');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'SPNSRN89E61D761Z','SPNSRN89E61D761Z','sspinelli@invitalia.it','sspinelli@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Victor','Trentini','TRNVTR82M01A944U','0515270000','vtrentini@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'TRNVTR82M01A944U');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'TRNVTR82M01A944U','TRNVTR82M01A944U','vtrentini@invitalia.it','vtrentini@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Dario','Valvo','VLVDRA89A11H163O','0515270000','dvalvo@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'VLVDRA89A11H163O');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'VLVDRA89A11H163O','VLVDRA89A11H163O','dvalvo@invitalia.it','dvalvo@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Rossella','Volpe','VLPRSL83P59C265L','0515270000','rvolpe@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'VLPRSL83P59C265L');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'VLPRSL83P59C265L','VLPRSL83P59C265L','rvolpe@invitalia.it','rvolpe@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Pietro','Zappoli','ZPPPTR69T17L762A','0515270000','pzappoli@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'ZPPPTR69T17L762A');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'ZPPPTR69T17L762A','ZPPPTR69T17L762A','pzappoli@invitalia.it','pzappoli@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Giuseppe','Lorusso ','LRSGPP66A09H501Q','0515270000','glorusso@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'LRSGPP66A09H501Q');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'LRSGPP66A09H501Q','LRSGPP66A09H501Q','glorusso@invitalia.it','glorusso@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Vito','Maiorano','MRNVTI70D21H501O','0515270000','vmaiorano@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'MRNVTI70D21H501O');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'MRNVTI70D21H501O','MRNVTI70D21H501O','vmaiorano@invitalia.it','vmaiorano@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES (' Fabio','Molinari','MLNFBA65M04H501R','0515270000','fmolinari@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'MLNFBA65M04H501R');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'MLNFBA65M04H501R','MLNFBA65M04H501R','fmolinari@invitalia.it','fmolinari@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Cristina','Paglia ','PGLCST68H58I838D','0515270000','cpaglia@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'PGLCST68H58I838D');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'PGLCST68H58I838D','PGLCST68H58I838D','cpaglia@invitalia.it','cpaglia@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');
INSERT INTO `persone` ( `nome`, `cognome`, `codice_fiscale`, `telefono_principale`, `email_principale`,`creato_da`, `modificato_da`)
VALUES ('Alessandro','Palmitelli ','PLMLSN69R11F839T','0515270000','apalmitelli@invitalia.it','BNCSFN71R11A944T', 'BNCSFN71R11A944T');	set @id_persona = (select id from persone where codice_fiscale = 'PLMLSN69R11F839T');	INSERT INTO `utenti` ( `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `password`, `roles`, `dati_persona_inseriti`, `cambio_password`, `creato_da`)
VALUES ( @id_persona,'PLMLSN69R11F839T','PLMLSN69R11F839T','apalmitelli@invitalia.it','apalmitelli@invitalia.it', 1, 'password', 'a:4:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";i:2;s:19:\"ROLE_ISTRUTTORE_ATC\";i:3;s:25:\"ROLE_ISTRUTTORE_INVITALIA\";}', 1, 1, 'BNCSFN71R11A944T');


INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11995,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11994,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11993,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11992,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11991,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11990,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11989,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11988,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11987,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11986,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11985,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11984,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11983,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11982,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11981,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11980,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11979,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11978,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11977,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11976,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11975,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11974,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11973,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11972,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11971,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11970,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11969,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11968,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11967,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11966,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11965,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11964,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11963,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11962,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11961,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11960,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11959,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11958,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11957,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11956,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11955,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11954,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11953,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11952,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11951,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11950,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11949,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11948,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11947,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11946,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11945,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');

INSERT INTO `permessi_procedura` ( `utente_id`, `procedura_id`, `solo_lettura`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
 (11940,95, 0, NOW(), NOW(), 'BNCSFN71R11A944T', 'BNCSFN71R11A944T');
