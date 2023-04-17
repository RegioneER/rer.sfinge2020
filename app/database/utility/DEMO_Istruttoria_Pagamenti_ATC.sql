-- Script per la DEMO del 22_11_2016 (Bando turismo)

SET @checklist_1 = (SELECT MAX(id) + 1 FROM `checklist_pagamenti`);

INSERT INTO `checklist_pagamenti` (`id`,`procedura_id`, `codice`, `nome`, `ruolo`)
VALUES
	(@checklist_1, 4, 'a', 'Checklist pagamento', 'ROLE_ISTRUTTORE_ATC'); -- Associo la CL alla procedura 4 (startup)

SET @sezione_checklist_1 = (SELECT MAX(id) + 1 FROM `sezioni_checklist_pagamenti`);
SET @sezione_checklist_2 = @sezione_checklist_1 + 1;
SET @sezione_checklist_3 = @sezione_checklist_2 + 1;

INSERT INTO `sezioni_checklist_pagamenti` (`checklist_id`, `descrizione`, `ordinamento`, `commento`)
VALUES
	(@checklist_1, 'ISTRUTTORIA FORMALE', 1, 1),
	(@checklist_1, 'AMMISSIBILITÀ DELLE SPESE', 2, 1),
	(@checklist_1, 'AVERIFICA DOCUMENTALE', 3, 1);

INSERT INTO `elementi_checklist_pagamenti` (`sezione_checklist_id`, `descrizione`, `note`, `tipo`, `choices`, `punteggio_minimo_ammissibilita`, `punteggio_massimo`, `significativo`, `lunghezza_massima`, `codice`)
VALUES
	(@sezione_checklist_1, 'Ragione sociale coincidente con i dati della domanda e dell\'atto di concessione contributo?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_1, 'Sede legale coincidente con i dati della domanda?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_1, 'Codice fiscale coincidente con i dati della domanda?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_1, 'Partita i.v.a. coincidente con i dati della domanda?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_1, 'Impresa regolare da verifica visura camerale?(impresa attiva, che non si trova in stato di liquidazione (anche volontaria) e che non è soggetta a procedure di fallimento, concordato preventivo, amministrazione controllata o altre procedure concorsuali in corso)', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_1, 'Il legale rappresentante indicato nella dichiarazione sostitutiva di atto di notorietà ed il legale rappresentante indicato nella visura camerale aggiornata, coincidono?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_1, 'Il legale rappresentante indicato nella dichiarazione sostitutiva di atto di notorietà ed il legale rappresentante che ha firmato digitalmente la dichiarazione stessa, coincidono?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_1, 'Nella dichiarazione sostitutiva di atto di notorietà sono state barrate confermate tutte le dichiarazioni/impegni obbligatori previsti?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_1, 'Il soggetto agevolato è iscritto sia all\'INPS che all\'INAIL?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_1, 'Il DURC risulta regolare ed è in corso di validità?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_1, 'L\'impresa beneficiaria ha ottenuto ulteriori contributi de minimis dopo la data di presentazione della domanda alla Regione, nell\'ambito del concetto di impresa unica?? (verificare richiesta di erogazione saldo)', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_1, 'Nel caso di impresa che ha dichiarato, nella richiesta di erogazione saldo, di aver ottenuto ulteriori contributi de minimis, la situazione risulta regolare rispetto al limite ?de minimis? di ?. 200.000,00?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),

	(@sezione_checklist_2, 'Tutta la spesa ammessa è stata sostenuta dal beneficiario nel periodo di eleggibilità del programma POR-FESR?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_2, 'Tutta la spesa ammessa è stata effettivamente sostenuta durante il periodo di eleggibilità del progetto?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_2, 'Tutta la spesa ammessa è fondata su contratti aventi valore legale o accordi e/o documenti?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_2, 'Tutta la spesa ammessa è riferita direttamente al progetto?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_2, 'Le spese ammesse rientrano nelle categorie previste dal bando?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_2, 'Gli importi rendicontati sono al netto di IVA?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_2, 'L\'IVA rappresenta un costo per il beneficiario? (il beneficiario deve presentare, ai fini del riconoscimento dell\'IVA come costo ammissibile, una dichiarazione di indeducibilità della stessa)', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),

	(@sezione_checklist_3, 'E\' stata redatta una relazione sullo stato di avanzamento/finale del progetto?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_3, 'La relazione tecnica finale contiene una descrizione analitica delle attività svolte tramite le consulenze? (solo in caso di progetti con spese rientranti nella categoria C "Consulenze specialistiche")', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_3, 'In caso di variazioni sostanziali intervenute nel corso della realizzazione del progetto è presente la necessaria documentazione a supporto?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_3, 'In materia di ambiente, per la realizzazione di questo progetto erano richieste specifiche	autorizzazioni in conformità con quanto previsto dalla normativa nazionale e comunitaria? (se sì, indicare nei commenti gli estremi delle eventuali autorizzazioni ottenute)', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_3, 'E\' presente altra eventuale documentazione (es. dichiarazioni) se richiesta nel bando di riferimento?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_3, 'Il beneficiario ha presentato le necessarie garanzie bancarie nei casi di anticipo di pagamento?', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_3, 'I documenti di spesa contengono i seguenti elementi: data e numero, oggetto, ammontare, importo iva, numero partita iva/codice fiscale, imposte/ritenute di legge, estremi del soggetto che ha emesso il documento di spesa, estremi del soggetto intestatario del documento di spesa, CUP', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_3, 'Tutti i documenti di spesa sono intestati al beneficiario? (specificare nei commenti eventuali documenti di spesa con diversa intestazione)', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL),
	(@sezione_checklist_3, 'Ciascun documento di spesa è regolarmente quietanzato (con quietanza singola o cumulativa?)', NULL, 'choice', 'a:3:{i:0;s:2:"Si";i:1;s:2:"No";i:2;s:3:"N/A";}', NULL, NULL, 0, NULL, NULL);
