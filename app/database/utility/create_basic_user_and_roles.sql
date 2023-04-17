-- indirizzi
INSERT INTO `indirizzi` (`id`, `stato_id`, `comune_id`, `via`, `numero_civico`, `cap`, `localita`, `note`, `provinciaEstera`, `comuneEstero`)
VALUES
	(1, 1, 4384, 'via verdi', '20', '50018', NULL, NULL, NULL, NULL),
	(2, 1, 4384, 'via rossi', '201', '50018', NULL, NULL, NULL, NULL),
	(3, 1, 4384, 'via bianchi', '18', '50018', NULL, NULL, NULL, NULL),
	(4, 1, 4384, 'via neri', '13', '50018', NULL, NULL, NULL, NULL),
	(5, 1, 4384, 'via viola', '16', '50018', NULL, NULL, NULL, NULL),
	(6, 1, 4477, 'Via Nino Bixio', '43', '52021', 'Caldoro', NULL, NULL, NULL),
	(7, 1, 3937, 'via Russi', '132/A', '65435', 'Mirandola Bassa', NULL, NULL, NULL),
	(8, 1, 4479, 'Via Nino Bixio', '666', '12345', NULL, NULL, NULL, NULL),
	(9, 1, 4474, 'Piazza Adua', '23415', '43434', 'Pive', NULL, NULL, NULL),
	(10, 1, 4267, 'Piazza Alberti', '1', '44444', NULL, NULL, NULL, NULL),
	(11, 6, NULL, 'Via Nino Bixio', '4', '00000', 'Localx', NULL, 'Prov di Cipro', 'Cipro Città'),
	(12, 1, 4421, 'via scopetone', '12', '00032', NULL, NULL, NULL, NULL);


-- Documenti
INSERT INTO `documenti` (`id`, `tipologia_documento_id`, `nome_originale`, `nome`, `md5`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `tipo`, `mime_type`, `path`, `file_size`, `cf_firmatario`)
VALUES
	(1, 3, 'attonomina.pdf.p7m', '_14550026489882_xxxxxx.pdf.p7m', '71b10f1a11c618934bee4ccd06148b92', NULL, '2016-02-09 08:24:08', '2016-02-09 08:24:08', 'utente', 'utente', 'FILE', 'application/octet-stream', '/Users/mauromanetti/Sites/sfinge2020new/doc/', 23477, 'MNTMRA69S29F656H'),
	(2, 2, 'ci.pdf.p7m', '_1455002648992_xxxx.pdf.p7m', '63296f691342ac1ee3ed4f6a052e1473', NULL, '2016-02-09 08:24:08', '2016-02-09 08:24:08', 'utente', 'utente', 'FILE', 'application/octet-stream', '/Users/mauromanetti/Sites/sfinge2020new/doc/', 987442, 'MNTMRA69S29F656H'),
	(3, 4, 'delega.pdf.p7m', '_14550033602757_xxxxx.pdf.p7m', '63296f691342ac1ee3ed4f6a052e1473', NULL, '2016-02-09 08:36:00', '2016-02-09 08:36:00', 'utente', 'utente', 'FILE', 'application/octet-stream', '/Users/mauromanetti/Sites/sfinge2020new/doc/', 987442, 'MNTMRA69S29F656H'),
	(4, 2, 'ci.pdf.p7m', '_14550033602799_xxxxx.p7m', 'aa71176a2a00bed804ab61acc56335f9', NULL, '2016-02-09 08:36:00', '2016-02-09 08:36:00', 'utente', 'utente', 'FILE', 'application/octet-stream', '/Users/mauromanetti/Sites/sfinge2020new/doc/', 7559, 'MNTMRA69S29F656H'),
	(5, 3, 'attonomina.pdf.p7m', '_14550144238166_doc_rerfp.pdf.p7m', 'c948eb4a888d75c61759ce43070e8aa8', NULL, '2016-02-09 11:40:23', '2016-02-09 11:40:23', 'utente', 'utente', 'FILE', 'application/octet-stream', '/Users/mauromanetti/Sites/sfinge2020new/doc/', 128169, 'MNTRRA69S29F656I'),
	(6, 2, 'old.doc_infocamere.pdf.p7m', '_14550144238189_cccc.pdf.p7m', '6173950d776bc944ee2d3ffd57550560', NULL, '2016-02-09 11:40:23', '2016-02-09 11:40:23', 'utente', 'utente', 'FILE', 'application/octet-stream', '/Users/mauromanetti/Sites/sfinge2020new/doc/', 4053, 'MNTRRA69S29F656I'),
	(7, 4, 'delega.pdf.p7m', '_14550147451578_yyyyyy.pdf.p7m', '6c2bed62a521fa73e986e21799d7e790', NULL, '2016-02-09 11:45:45', '2016-02-09 11:45:45', 'utente', 'utente', 'FILE', 'application/octet-stream', '/Users/mauromanetti/Sites/sfinge2020new/doc/', 4284, 'MNTRRA69S29F656I'),
	(8, 2, 'ci.pdf.p7m', '_14550147451618_Passaporto.pdf.p7m', '70d295c4f437a2ac3dcdc59b6018f698', NULL, '2016-02-09 11:45:45', '2016-02-09 11:45:45', 'utente', 'utente', 'FILE', 'application/octet-stream', '/Users/mauromanetti/Sites/sfinge2020new/doc/', 1193803, 'MNTRRA69S29F656I');

-- persone
INSERT INTO `persone` (`id`, `nazionalita_id`, `stato_nascita_id`, `comune_id`, `luogo_residenza_id`, `nome`, `cognome`, `data_nascita`, `sesso`, `codice_fiscale`, `provincia_estera`, `comune_estero`, `telefono_principale`, `fax_principale`, `email_principale`, `telefono_secondario`, `fax_secondario`, `email_secondario`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `carta_identita_id`)
VALUES
	(1, 1, 1, NULL, 1, 'Mario', 'Verdi', '1970-01-01', 'M', 'MRAVRD70A01D612G', NULL, NULL, '3291234343', NULL, 'superadmin@schema31.it', NULL, NULL, NULL, NULL, '2016-01-25 15:42:27', '2016-01-25 15:42:27', 'superadmin', 'superadmin', NULL),
	(2, 1, 1, NULL, 2, 'Mario', 'Rossi', '1970-01-01', 'M', 'RSSMRA70A01D612W', NULL, NULL, '3233234213', NULL, 'utentepa@schema31.it', NULL, NULL, NULL, NULL, '2016-01-25 13:07:58', '2016-01-25 13:07:58', 'utentepa', 'utentepa', NULL),
	(3, 1, 1, NULL, 3, 'Mario', 'Bianchi', '1970-01-01', 'M', 'MRABCH70A01D612N', NULL, NULL, '3291234341', NULL, 'managerpa@schema31.it', NULL, NULL, NULL, NULL, '2016-01-25 15:44:51', '2016-01-25 15:44:51', 'managerpa', 'managerpa', NULL),
	(4, 1, 1, NULL, 4, 'Mario', 'Neri', '1970-01-01', 'M', 'MRANRE70A01D612Z', NULL, NULL, '3291234345', NULL, 'adminpa@schema31.it', NULL, NULL, NULL, NULL, '2016-01-25 15:46:34', '2016-01-25 15:46:34', 'adminpa', 'adminpa', NULL),
	(5, 1, 1, NULL, 5, 'Mario', 'Viola', '1970-01-01', 'M', 'MRAVLI70A01D612H', NULL, NULL, '3291234349', NULL, 'utente@schema31.it', NULL, NULL, NULL, NULL, '2016-01-25 15:48:38', '2016-01-25 15:48:38', 'utente', 'utente', 4),
    (6, 1, 1, NULL, 6, 'Marco', 'marchi', '1965-02-09', 'M', 'MNTMRA69S29F656H', NULL, NULL, '3282897055', NULL, 'xxxxx@gmail.com', '66666', NULL, 'mmarchi@provaemaui.it', NULL, '2016-02-09 08:22:45', '2016-02-09 08:24:09', 'utente', 'utente',2),
	(7, 1, 1, NULL, 7, 'Franco', 'Franchi', '1976-03-09', 'M', 'FFFMTA69S28F656I', NULL, NULL, '055666665433', NULL, 'test@prova.it', NULL, NULL, NULL, NULL, '2016-02-09 08:35:46', '2016-02-09 08:36:00', 'utente', 'utente',NULL),
	(8, 1, 1, NULL, 8, 'Carlo', 'Cracco', '2016-02-09', 'M', 'CLRCRK69S29F656I', NULL, NULL, '99999999999', NULL, 'ccraccco@provaemail.it', NULL, NULL, NULL, NULL, '2016-02-09 10:55:21', '2016-02-09 10:55:21', 'utente', 'utente',NULL),
	(9, 1, 1, NULL, 10, 'Luca', 'Petruzzi', '2016-02-03', 'M', 'PTRLCU01A01G535A', NULL, NULL, '9999977777', NULL, 'petrux@provaemail.it', NULL, NULL, NULL, NULL, '2016-02-09 11:32:44', '2016-02-09 11:32:44', 'lpetruzzi', 'lpetruzzi',NULL),
	(10, 1, 1, NULL, 11, 'Marco', 'Rossi', '2016-02-09', 'M', 'MNTRRA69S29F656I', NULL, NULL, '8888888888', NULL, 'mroxxxxx@gmail.com', '3282897061222', NULL, NULL, NULL, '2016-02-09 11:39:17', '2016-02-09 11:40:23', 'utente', 'utente',NULL),
	(11, 1, 1, NULL, 12, 'Alex', 'Britt', '2016-02-02', 'M', 'GVBLSS80P08E625Y', NULL, NULL, '6666666777777777', NULL, 'abritti@prova.it', NULL, NULL, NULL, NULL, '2016-02-09 11:56:25', '2016-02-09 11:56:25', 'utente', 'utente',NULL);


-- utenti
INSERT INTO `utenti` (`id`, `persona_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `locked`, `expired`, `expires_at`, `confirmation_token`, `password_requested_at`, `roles`, `credentials_expired`, `credentials_expire_at`, `dati_persona_inseriti`, `cambio_password`, `creato_da`, `creato_il`)
VALUES
	(1, 1, 'superadmin', 'superadmin', 'superadmin@schema31.it', 'superadmin@schema31.it', 1, '9v7xmz4i8k8c48okkkgwo04c0ggc8wo', 'superadmin{9v7xmz4i8k8c48okkkgwo04c0ggc8wo}', '2016-01-25 15:37:27', 0, 0, NULL, NULL, NULL, 'a:2:{i:0;s:9:\"ROLE_USER\";i:1;s:16:\"ROLE_SUPER_ADMIN\";}', 0, NULL, 1, 1, NULL, '2016-01-25 12:29:20'),
    (2, 2, 'utentepa', 'utentepa', 'utentepa@schema31.it', 'utentepa@schema31.it', 1, '4gquaj3f2hwkww8o08o0kk04c4s80gs', 'utentepa{4gquaj3f2hwkww8o08o0kk04c4s80gs}', '2016-01-25 13:06:20', 0, 0, NULL, NULL, NULL, 'a:2:{i:0;s:9:\"ROLE_USER\";i:1;s:14:\"ROLE_UTENTE_PA\";}', 0, NULL, 1, 1, NULL, '2016-01-25 12:33:14'),
	(3, 3, 'managerpa', 'managerpa', 'managerpa@schema31.it', 'managerpa@schema31.it', 1, 'bcuv1b165008kg4osck8g0ck0k8g0ww', 'managerpa{bcuv1b165008kg4osck8g0ck0k8g0ww}', '2016-01-25 15:43:31', 0, 0, NULL, NULL, NULL, 'a:2:{i:0;s:9:\"ROLE_USER\";i:1;s:15:\"ROLE_MANAGER_PA\";}', 0, NULL, 1, 1, NULL, '2016-01-25 12:29:55'),
	(4, 4, 'adminpa', 'adminpa', 'adminpa@schema31.it', 'adminpa@schema31.it', 1, '3xlkuji2uuyowwgsok8csk8o0gc8ogs', 'adminpa{3xlkuji2uuyowwgsok8csk8o0gc8ogs}', '2016-01-25 15:45:41', 0, 0, NULL, NULL, NULL, 'a:2:{i:0;s:9:\"ROLE_USER\";i:1;s:13:\"ROLE_ADMIN_PA\";}', 0, NULL, 1, 1, NULL, '2016-01-25 15:34:23'),
	(5, 5, 'utente', 'utente', 'utente@schema31.it', 'utente@schema31.it', 1, 'p1e28x6skq8scgg8ssookckowos4o0o', 'utente{p1e28x6skq8scgg8ssookckowos4o0o}', '2016-01-25 15:47:39', 0, 0, NULL, NULL, NULL, 'a:1:{i:0;s:11:\"ROLE_UTENTE\";}', 0, NULL, 1, 1, NULL, '2016-01-25 15:35:12');





-- Soggetti

INSERT INTO `soggetti` (`id`, `codice_ateco_id`, `comune_id`, `forma_giuridica_id`, `tipo_soggetto_id`, `comune_unione_comune_id`, `denominazione`, `partita_iva`, `codice_fiscale`, `data_registrazione`, `data_costituzione`, `sito_web`, `dimensione`, `email`, `tel`, `fax`, `via`, `civico`, `cap`, `localita`, `codice_organismo`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`, `tipo`, `fatturato`, `bilancio`, `ccia`)
VALUES
	(1, NULL, 3968, 44, NULL, NULL, 'Comune di Bologna', '01232710374', '01232710374', '2016-02-08 23:59:26', '2016-02-08 00:00:00', 'http://', NULL, 'comune.bologna@regione-emilia-romagna.it', '90888888', NULL, 'Rosa', '12', '55555', NULL, 20001, NULL, '2016-02-08 23:59:26', '2016-02-09 08:36:00', 'utente', 'utente', 'COMUNE', NULL, NULL, NULL),
	(2, 2, 3899, 10, NULL, NULL, 'Gundam SPA', '01232710376', 'GNTMRA69S29F656I', '2016-02-09 00:03:35', '2016-02-09 00:00:00', 'http://', NULL, 'email@email.it', '98988', NULL, 'Via salti', '123', '65655', NULL, 20002, NULL, '2016-02-09 00:03:35', '2016-02-09 00:03:35', 'utente', 'utente', 'AZIENDA', NULL, NULL, NULL),
	(3, 1, 6912, 10, NULL, NULL, 'Monster SPA', '01232710375', 'MMTMRA69S29F6565', '2016-02-09 11:03:30', '2016-02-09 00:00:00', 'http://', NULL, 'prova@sasa.it', '333333333', NULL, 'Dei Serragli', '34/A', '22222', NULL, 20003, NULL, '2016-02-09 11:03:30', '2016-02-11 16:58:49', 'mmanetti', 'mmanetti', 'AZIENDA', NULL, NULL, NULL),
	(4, NULL, 3968, 44, NULL, 230, 'Comune di Barberino', '01232710379', '01232710375', '2016-02-09 11:18:38', NULL, 'http://', NULL, 'bologna@regione.it', '5454554554544', NULL, 'Roma', '18', '43433', NULL, 20004, NULL, '2016-02-09 11:18:39', '2016-02-09 11:18:39', 'mmanetti', 'mmanetti', 'COMUNE', NULL, NULL, NULL),
	(5, NULL, 5325, 50, NULL, NULL, 'Università di Bologna', '01131710376', '80007010376', '2016-02-09 11:22:50', NULL, 'http://', NULL, 'unibo@email.it', '54545545444', NULL, 'Serra', '242', '54545', 'Budrio', 20005, NULL, '2016-02-09 11:22:50', '2016-02-09 11:22:50', 'mmanetti', 'mmanetti', 'SOGGETTO', NULL, NULL, NULL),
	(6, 152, 3969, 11, NULL, NULL, 'Franchetti SRL', '01235710374', 'GNLMRA69S29F656I', '2016-02-17 20:19:42', '2016-02-17 00:00:00', 'http://www.franchetti.com', 36, 'franchetti@provaemail.it', '0517654443', NULL, 'Piazza della repubblica', '126', '65432', NULL, 20006, NULL, '2016-02-17 20:19:42', '2016-02-17 20:19:42', 'utente', 'utente', 'AZIENDA', 100000.00, 499345.00, '05207420653');


-- sedi
INSERT INTO `sedi` (`id`, `indirizzo_id`, `soggetto_id`, `ateco_id`, `denominazione`, `numero_rea`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
	(1, 9, 3, 2, 'Sede Operativa Uno', NULL, NULL, '2016-02-09 11:05:40', '2016-02-09 11:05:40', 'utente', 'utente');

-- Incarichi

INSERT INTO `incarico_persona` (`id`, `soggetto_id`, `incaricato_id`, `tipo_incarico_id`, `stato_id`, `documento_nomina_id`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
	(1, 1, 5, 5, 1, NULL, NULL, '2016-02-08 23:59:26', '2016-02-08 23:59:26', 'utente', 'utente'),
	(2, 2, 5, 5, 1, NULL, NULL, '2016-02-09 00:03:35', '2016-02-09 00:03:35', 'utente', 'utente'),
	(3, 1, 6, 2, 1, 1, NULL, '2016-02-09 08:24:08', '2016-02-09 08:25:52', 'utente', 'managerpa'),
	(4, 1, 7, 3, 1, 3, NULL, '2016-02-09 08:36:00', '2016-02-09 08:36:56', 'utente', 'managerpa'),
	(5, 3, 8, 5, 1, NULL, NULL, '2016-02-09 11:03:30', '2016-02-09 11:03:30', 'utente', 'utente'),
	(6, 4, 8, 5, 1, NULL, NULL, '2016-02-09 11:18:39', '2016-02-09 11:18:39', 'utente', 'utente'),
	(7, 5, 8, 5, 1, NULL, NULL, '2016-02-09 11:22:50', '2016-02-09 11:22:50', 'utente', 'utente'),
	(8, 3, 9, 1, 1, NULL, NULL, '2016-02-09 11:33:08', '2016-02-09 11:33:08', 'utente', 'utente'),
	(9, 3, 10, 2, 1, 5, NULL, '2016-02-09 11:40:23', '2016-02-09 11:43:28', 'utente', 'managerpa'),
	(10, 3, 5, 3, 1, 7, NULL, '2016-02-09 11:45:45', '2016-02-09 11:46:16', 'utente', 'managerpa'),
	(11, 3, 11, 4, 1, NULL, NULL, '2016-02-09 12:14:26', '2016-02-09 12:14:26', 'utente', 'utente'),
	(12, 6, 5, 5, 1, NULL, NULL, '2016-02-17 20:19:42', '2016-02-17 20:19:42', 'utente', 'utente');





