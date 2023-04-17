SET @fase_1_id = (SELECT MAX(id) + 1 FROM `istruttorie_fasi`);
INSERT INTO `istruttorie_fasi` (`id`,`procedura_id`,`step`) VALUES (@fase_1_id,2,1);

SET @checklist_1_id = (SELECT MAX(id) + 1 FROM `istruttorie_checklist`);
INSERT INTO `istruttorie_checklist` (`id`,`fase_id`,`codice`,`nome`,`ruolo`,`molteplicita`,`proponente`) 
VALUES (@checklist_1_id, @fase_1_id,'checklist_formale_2_ambiente','Verifica formale','ROLE_ISTRUTTORE',1,0);

SET @fase_2_id = @fase_1_id + 1;
INSERT INTO `istruttorie_fasi` (`id`,`procedura_id`,`step`) VALUES (@fase_2_id,2,2);

SET @checklist_2_id = @checklist_1_id + 1;
INSERT INTO `istruttorie_checklist` (`id`,`fase_id`,`codice`,`nome`,`ruolo`,`molteplicita`,`proponente`) 
VALUES (@checklist_2_id, @fase_2_id,'checklist_sostanziale_2_ambiente','Valutazione sostanziale','ROLE_VALUTATORE',1,0);

SET @fase_3_id = @fase_2_id + 1;
INSERT INTO `istruttorie_fasi` (`id`,`procedura_id`,`step`) VALUES (@fase_3_id,2,3);

SET @checklist_3_id = @checklist_2_id + 1;
INSERT INTO `istruttorie_checklist` (`id`,`fase_id`,`codice`,`nome`,`ruolo`,`molteplicita`,`proponente`) 
VALUES (@checklist_3_id, @fase_3_id,'griglia_2_ambiente','Valutazione','ROLE_VALUTATORE',1,0);

SET @sezione_checklist_1_id = (SELECT MAX(id) + 1 FROM `istruttorie_sezioni_checklist`);
SET @sezione_checklist_2_id = @sezione_checklist_1_id + 1;
SET @sezione_checklist_3_id = @sezione_checklist_2_id + 1;
SET @sezione_checklist_4_id = @sezione_checklist_3_id + 1;

INSERT INTO `istruttorie_sezioni_checklist` (`id`, `checklist_id`, `descrizione`, `ordinamento`, `commento`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
	(@sezione_checklist_1_id, @checklist_1_id, 'Verifica formale', 1, 1, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_2_id, @checklist_2_id, 'Valutazione risondenza sostanziale', 1, 1, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_3_id, @checklist_3_id, 'Valutazione (Punteggio minimo 75)', 1, 1, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_4_id, @checklist_3_id, 'Valutazione di priorità', 1, 1, NULL, NULL, NULL, NULL, NULL);

INSERT INTO `istruttorie_elementi_checklist` (`sezione_checklist_id`, `descrizione`, `note`, `tipo`, `choices`, `punteggio_minimo_ammissibilita`, `punteggio_massimo`, `significativo`, `lunghezza_massima`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
	(@sezione_checklist_1_id, 'Trattasi di progetto integrato (domande soggetti associati)', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'Progetto integrato: Numero associati non superiore a 5', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'Progetto integrato: spesa minima per partecipante non inferiore a 200.000 euro', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'I soggetti proponenti sono soggetti beneficiari previsti dal bando', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'Progetto integrato: convenzione sottoscritta digitalmente da ciascun legale rappresentante dei soggetti associati', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'Domanda/e inviata/e entro i termini e secondo le modalità previste dal bando', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'Assolvimento imposta di bollo', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'Domanda firmata digitalmente dal legale rappresentante o delegato; in caso di progetto integrato domande firmate digitalmente dal legale rappresentante o delegato del soggetto capofila ', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'Soggetto proponente è proprietario del bene/attrattore; in caso di progetto integrato verifica per ciascun soggetto associato', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'In caso di soggetto proponente non proprietario del bene/attrattore: copia atto attestante la disponibilità del bene oggetto di qualificazione per un periodo non inferiore a 20 anni a decorrere dalla data di presentazione della domanda; in caso di progetto integrato verifica per ciascun soggetto associato', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, '"(In caso di domanda presentata da società di capitali a prevalente capitale pubblico)Documentazione comprovante la procedura  
		di selezione del socio privato e  contestuale affidamento dell'avvio, esecuzione e/o gestione del progetto candidato a finanziamento oppure 
		lo Statuto o l\'accordo da cui si evince che i soci privati, non beneficiano, direttamente o indirettamente, dei proventi derivanti dalla gestione 
		economica del progetto candidato a finanziamento né siano coinvolti nella sua realizzazione"', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'Atto di approvazione del progetto preliminare o progettazione successiva; in caso di progetto integrato atto di approvazione progetto preliminare di ciascun soggetto associato', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'Allegato 3, nel caso in cui, in fase di presentazione della domanda, siano previste e quantificabili “Entrate nette” ex art. 61 del Reg. (UE) 1303/20; in caso di progetto integrato verifica per ciascun soggetto associato', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'Allegato 4, nel caso in cui, in fase di presentazione della domanda, siano previste e quantificabili “Entrate nette” ex art. 65, comma 8 del Reg. (UE) 1303/2013; in caso di progetto integrato verifica per ciascun soggetto associato', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'Costo complessivo non inferiore a 1 milione di euro (che dovrà risultare comunque ammissibile)', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_1_id, 'La domanda è ammissibile sotto il profilo formale; in caso di progetto integrato l\'ammissibilità è riferita per ciascuna domanda di ciascun soggetto associato', NULL, 'choice', 'a:3:{i:0;s:2:\"Si\";i:1;s:2:\"No\";i:2;s:3:\"N/P\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),

	(@sezione_checklist_2_id, 'Coerenza con strategia, contenuti ed obiettivi del POR', NULL, 'choice', 'a:2:{i:0;s:2:\"Si\";i:1;s:2:\"No\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_2_id, 'Coerenza con gli orientamenti strategici perseguiti dalle politiche regionali con particolare riferimento alle linee di indirizzo del Piano di Azione Ambientale della Regione Emilia Romagna, con le politiche per il turismo sostenibile e l’attrattività territoriale e con la Comunicazione della Commissione Europea Strategia Europea per una maggiore crescita e occupazione nel turismo costiero e marittimo', NULL, 'choice', 'a:2:{i:0;s:2:\"Si\";i:1;s:2:\"No\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_2_id, 'Collocazione degli attrattori del patrimonio naturale nelle aree dell\'Appennino, del Delta del PO e del Distretto turistico della Costa; in caso di progetto integrato la valutazione è riferita per ciascun soggetto associato', NULL, 'choice', 'a:2:{i:0;s:2:\"Si\";i:1;s:2:\"No\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_2_id, 'Coerenza con le categorie di operazione associate alla procedura di attuazione;  in caso di progetto integrato la valutazione è riferita per ciascun soggetto associato', NULL, 'choice', 'a:2:{i:0;s:2:\"Si\";i:1;s:2:\"No\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_2_id, 'La domanda è rispondente sotto il profilo sostanziale; in caso di progetto integrato la rispondenza è riferita per ciascuna domanda di ciascun soggetto associato', NULL, 'choice', 'a:2:{i:0;s:2:\"Si\";i:1;s:2:\"No\";}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),

	(@sezione_checklist_3_id, 'A.1 Analisi della domanda potenziale (MAX 5)', NULL, 'integer', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_3_id, 'A.2 Capacità di attivare integrazioni e sinergie con il sistema economico e di incidere sulla qualificazione del sistema territoriale (MAX 10)', NULL, 'integer', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_3_id, 'A.3 Sostenibilità gestionale e finanziaria nell’arco temporale del Programma Operativo; in caso di progetto integrato il punteggio assegnato deriva dalla MEDIA delle valutazioni degli interventi dei singoli soggetti associati (MAX 10)', NULL, 'integer', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_3_id, 'A.4 Accessibilità e fruibilità dei luoghi proposti con particolare riferimento a soluzioni attente ai temi della disabilità (MAX 10)', NULL, 'integer', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_3_id, 'A.5 Impatto sull\'innovatività del prodotto turistico (MAX 15)', NULL, 'integer', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_3_id, 'A.6 Minimizzazione dei costi ambientali indotti dalla possibile pressione turistica conseguente agli interventi di valorizzazione ambientale (MAX 5)', NULL, 'integer', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_3_id, 'B. Qualità economico-finanziaria del progetto in termini di congruenza dei costi e del valore complessivo del progetto rispetto agli obiettivi e alle attività previste; in caso di progetto integrato il punteggio assegnato deriva dalla MEDIA delle valutazioni degli interventi dei singoli soggetti associati (MAX 10)', NULL, 'integer', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_3_id, 'C. Capacità di integrazione degli interventi proposti nella filiera turistica regionale anche con riferimento al sistema dei servizi e della commercializzazione (MAX 15)', NULL, 'integer', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_3_id, 'D. Concentrazione delle risorse su poli e reti di eccellenza (MAX 10)', NULL, 'integer', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_3_id, 'E. Utilizzo di tecnologie digitali (MAX 10)', NULL, 'integer', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),

	(@sezione_checklist_4_id, 'Stato di avanzamento della progettualità degli interventi (cantierabilità) (MAX 8)', NULL, 'integer', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_4_id, 'Integrazione con altri interventi previsti nello stesso ambito territoriale (MAX 2)', NULL, 'integer', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
	(@sezione_checklist_4_id, 'Rilevanza dell\'intervento rispetto ai temi dell\'innovazione sociale (MAX 2)', NULL, 'integer', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL);
