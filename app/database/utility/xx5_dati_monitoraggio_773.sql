
SET @id_frammento = NULL;

INSERT INTO `fascicoli_pagine` (`titolo`, `maxMolteplicita`, `ordinamento`, `alias`, `callback`, `frammentoContenitore_id`, `callbackPresenza`, `minMolteplicita`) VALUES ('Dati monitoraggio', 1, 0, 'dati_monitoraggio_773', NULL, @id_frammento, NULL, 1);
	


SET @id_pagina = (SELECT MAX(id) FROM fascicoli_pagine WHERE alias='dati_monitoraggio_773');
INSERT INTO `fascicoli_fascicoli` (`indice_id`, `template`) VALUES (@id_pagina, '::base_ente.html.twig');
SET @id_fascicolo = LAST_INSERT_ID();	
	
SET @id_pagina = (SELECT MAX(id) FROM fascicoli_pagine WHERE alias='dati_monitoraggio_773');
INSERT INTO `fascicoli_frammenti` (`pagina_id`, `titolo`, `action`, `ordinamento`, `alias`, `tipoFrammento_id`, `callbackPresenza`) VALUES (@id_pagina, NULL, NULL, 1, 'indice_773', 4, NULL);


SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='indice_773');

INSERT INTO `fascicoli_pagine` (`titolo`, `maxMolteplicita`, `ordinamento`, `alias`, `callback`, `frammentoContenitore_id`, `callbackPresenza`, `minMolteplicita`) VALUES ('Appendice A', 1, 1, 'appendice_a_773', NULL, @id_frammento, NULL, 1);
	

SET @id_pagina = (SELECT MAX(id) FROM fascicoli_pagine WHERE alias='appendice_a_773');
INSERT INTO `fascicoli_frammenti` (`pagina_id`, `titolo`, `action`, `ordinamento`, `alias`, `tipoFrammento_id`, `callbackPresenza`) VALUES (@id_pagina, 'Appendice A', NULL, 1, 'appendice_a_773_fr', 1, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Numero occupati', 1, NULL, 'N;', NULL, NULL, NULL, 1, 'numero_occupati_773', 4, NULL, NULL, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Numero occupati laureati', 1, NULL, 'N;', NULL, NULL, NULL, 2, 'numero_occupati_773_laureati', 4, NULL, NULL, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Numero occupati R&S design', 1, NULL, 'N;', NULL, NULL, NULL, 3, 'numero_occupati_773_rs', 4, NULL, NULL, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Fatturato', 1, NULL, 'N;', NULL, NULL, NULL, 4, 'fatturato_773', 8, NULL, 2, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Valore aggiunto', 1, NULL, 'N;', NULL, NULL, NULL, 5, 'valore_aggiunto_773', 8, NULL, 2, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Esportazioni', 1, NULL, 'N;', NULL, NULL, NULL, 6, 'esportazioni_773', 8, NULL, 2, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Invenstimenti produttivi all\'estero', 1, NULL, 'N;', NULL, NULL, NULL, 7, 'investimenti_estero_773', 8, NULL, 2, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Spesa in R&S', 1, NULL, 'N;', NULL, NULL, NULL, 8, 'spesa_rs_773', 8, NULL, 2, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Brevetti (Dato cumulativo)', 1, NULL, 'N;', NULL, NULL, NULL, 9, 'brevetti_773', 2, NULL, NULL, NULL, 4);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Collaborazioni di ricerca', 1, NULL, 'N;', NULL, NULL, NULL, 10, 'collaborazioni_ricerca_773', 2, NULL, NULL, NULL, 4);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Reti partenership strategiche (non commerciali)', 1, NULL, 'N;', NULL, NULL, NULL, 11, 'reti_strategiche_773', 2, NULL, NULL, NULL, 4);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Certificazioni e standard', 1, NULL, 'N;', NULL, NULL, NULL, 12, 'cert_standard_773', 2, NULL, NULL, NULL, 4);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Prodotti principali', 1, NULL, 'N;', NULL, NULL, NULL, 13, 'prodotti_proncipali_773', 2, NULL, NULL, NULL, 4);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Tecnologie chiave principalemente utilizzate', 1, NULL, 'N;', NULL, NULL, NULL, 14, 'tecnologie_chiave_773', 2, NULL, NULL, NULL, 4);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Principali servizi collegati al prodotto', 1, NULL, 'N;', NULL, NULL, NULL, 15, 'principali_servizi_773', 2, NULL, NULL, NULL, 4);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_a_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Investimenti legati alla sostenibilità, salute e sicurezza', 1, NULL, 'N;', NULL, NULL, NULL, 16, 'salute_sicurezza_773', 2, NULL, NULL, NULL, 4);



SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='indice_773');

INSERT INTO `fascicoli_pagine` (`titolo`, `maxMolteplicita`, `ordinamento`, `alias`, `callback`, `frammentoContenitore_id`, `callbackPresenza`, `minMolteplicita`) VALUES ('Appendice B', 1, 2, 'appendice_b_773', NULL, @id_frammento, 'hasAppendiceBVisibile', 0);
	

SET @id_pagina = (SELECT MAX(id) FROM fascicoli_pagine WHERE alias='appendice_b_773');
INSERT INTO `fascicoli_frammenti` (`pagina_id`, `titolo`, `action`, `ordinamento`, `alias`, `tipoFrammento_id`, `callbackPresenza`) VALUES (@id_pagina, '1) Il progetto è orientato a (una sola risposta):', NULL, 1, 'appendice_b_773_fr', 1, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='appendice_b_773_fr');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'opzioni', 1, NULL, 'a:3:{i:0;s:46:"a) un prodotto destinato al consumatore finale";i:1;s:77:"b) un prodotto destinato ad altre imprese (prodotto intermedio della filiera)";i:2;s:49:"c) rendere più efficiente il processo produttivo";}', 0, 0, NULL, 1, 'orientato_773', 7, NULL, NULL, NULL, NULL);


SET @id_pagina = (SELECT MAX(id) FROM fascicoli_pagine WHERE alias='appendice_b_773');
INSERT INTO `fascicoli_frammenti` (`pagina_id`, `titolo`, `action`, `ordinamento`, `alias`, `tipoFrammento_id`, `callbackPresenza`) VALUES (@id_pagina, '2) Il progetto porta vantaggi in termini di (possibili più risposte)', NULL, 2, 'vantaggi_773', 1, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='vantaggi_773');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'opzioni', 1, NULL, 'a:9:{i:0;s:95:"1. Ideazione di un nuovo prodotto (smaterializzazione, uso condiviso, integrazione di funzioni)";i:1;s:53:"2. RIduzione del consumo di materiale e altre risorse";i:2;s:45:"3. Efficienza energetica del processo/filiera";i:3;s:62:"4. Selezione di materiali con altri a minor impatto ambientale";i:4;s:96:"5. Riduzioni impatti ambientali nel processo di produzione/filiera (aria, acqua, rifiuti, etc..)";i:5;s:66:"6. Ottimizzazione della fase di distribuzione del prodotto/filiera";i:6;s:66:"7. Riduzione impatti ambientali durante la fase d\'uso del prodotto";i:7;s:50:"8. Incremento del tempo di vita utile del prodotto";i:8;s:69:"9. Ottimizzazione della gestione dei rifiuti a fine vita del prodotto";}', 0, 1, NULL, 1, 'vantaggi_opzioni_773', 7, NULL, NULL, NULL, NULL);


SET @id_pagina = (SELECT MAX(id) FROM fascicoli_pagine WHERE alias='appendice_b_773');
INSERT INTO `fascicoli_frammenti` (`pagina_id`, `titolo`, `action`, `ordinamento`, `alias`, `tipoFrammento_id`, `callbackPresenza`) VALUES (@id_pagina, '3) La caratteristica ambientale viene considerata strategica per la penetrazione nel mercato ?', NULL, 3, 'strategica_773', 1, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='strategica_773');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'opzioni', 1, NULL, 'a:2:{i:0;s:2:"SI";i:1;s:2:"NO";}', 0, 0, NULL, 1, 'opzioni_strategica_773', 7, NULL, NULL, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='strategica_773');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'In che modo la si intende valorizzare ?', 0, NULL, 'N;', NULL, NULL, NULL, 2, 'modo_valorozzazione_773', 2, NULL, NULL, NULL, 4);


SET @id_pagina = (SELECT MAX(id) FROM fascicoli_pagine WHERE alias='appendice_b_773');
INSERT INTO `fascicoli_frammenti` (`pagina_id`, `titolo`, `action`, `ordinamento`, `alias`, `tipoFrammento_id`, `callbackPresenza`) VALUES (@id_pagina, '4) indicare (in una scala da 1 \"poco\" a 3 \"molto\", con NA non applicabile) la possibilità del progetto di incidere sui seguenti paramentri', NULL, 4, 'ambito_impatto_gen_773', 1, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='ambito_impatto_gen_773');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Riduzione consumo di energia', 1, NULL, 'a:4:{i:0;s:2:"NA";i:1;s:1:"1";i:2;s:1:"2";i:3;s:1:"3";}', 0, 0, NULL, 1, 'ambito_impatto_773_1', 7, NULL, NULL, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='ambito_impatto_gen_773');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Riduzione consumo materie prime ed altre risorse (compresa acqua)', 1, NULL, 'a:4:{i:0;s:2:"NA";i:1;s:1:"1";i:2;s:1:"2";i:3;s:1:"3";}', 0, 0, NULL, 2, 'ambito_impatto_773_2', 7, NULL, NULL, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='ambito_impatto_gen_773');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Riduzione impiego sostanze pericolose', 1, NULL, 'a:4:{i:0;s:2:"NA";i:1;s:1:"1";i:2;s:1:"2";i:3;s:1:"3";}', 0, 0, NULL, 3, 'ambito_impatto_773_3', 7, NULL, NULL, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='ambito_impatto_gen_773');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Riduzione emissioni (acqua, aria e suolo)', 1, NULL, 'a:4:{i:0;s:2:"NA";i:1;s:1:"1";i:2;s:1:"2";i:3;s:1:"3";}', 0, 0, NULL, 4, 'ambito_impatto_773_4', 7, NULL, NULL, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='ambito_impatto_gen_773');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Riduzione altre tipologie di emissioni (rumore, radiazioni, campi elettromagnetici, etc...)', 1, NULL, 'a:4:{i:0;s:2:"NA";i:1;s:1:"1";i:2;s:1:"2";i:3;s:1:"3";}', 0, 0, NULL, 5, 'ambito_impatto_773_5', 7, NULL, NULL, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='ambito_impatto_gen_773');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Riduzione rifiuti (quantitativo e/o pericolosità)', 1, NULL, 'a:4:{i:0;s:2:"NA";i:1;s:1:"1";i:2;s:1:"2";i:3;s:1:"3";}', 0, 0, NULL, 6, 'ambito_impatto_773_6', 7, NULL, NULL, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='ambito_impatto_gen_773');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Reimpiego, reciclaggio e recupero di materiali (compresa valorizzazione dei sottoprodotti e degli scarti)', 1, NULL, 'a:4:{i:0;s:2:"NA";i:1;s:1:"1";i:2;s:1:"2";i:3;s:1:"3";}', 0, 0, NULL, 7, 'ambito_impatto_773_7', 7, NULL, NULL, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='ambito_impatto_gen_773');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Altro (specificare)', 0, NULL, 'N;', NULL, NULL, NULL, 8, 'ambito_impatto_773_8', 1, NULL, NULL, NULL, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='ambito_impatto_gen_773');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'Altro (valutazione)', 0, NULL, 'a:4:{i:0;s:2:"NA";i:1;s:1:"1";i:2;s:1:"2";i:3;s:1:"3";}', 0, 0, NULL, 9, 'ambito_impatto_773_9', 7, NULL, NULL, NULL, NULL);


SET @id_pagina = (SELECT MAX(id) FROM fascicoli_pagine WHERE alias='appendice_b_773');
INSERT INTO `fascicoli_frammenti` (`pagina_id`, `titolo`, `action`, `ordinamento`, `alias`, `tipoFrammento_id`, `callbackPresenza`) VALUES (@id_pagina, '5) E\' possibile quantificare il miglioramento ambientale (se si descrivere)', NULL, 5, 'quantificare_773', 1, NULL);

SET @id_frammento = (SELECT MAX(id) FROM fascicoli_frammenti WHERE alias='quantificare_773');
INSERT INTO `fascicoli_campi` (`frammento_id`, `label`, `required`, `evidenziato`, `scelte`, `expanded`, `multiple`, `query`, `ordinamento`, `alias`, `tipoCampo_id`, `callbackPresenza`, `precisione`, `note`, `righeTextArea`) VALUES (@id_frammento, 'descrizione', 0, NULL, 'N;', NULL, NULL, NULL, 1, 'descrizione_773', 2, NULL, NULL, NULL, NULL);

INSERT INTO `fascicoli_procedure_rendiconti` (`procedura_id`, `fascicolo_id`, `data_cancellazione`, `data_creazione`, `data_modifica`, `creato_da`, `modificato_da`)
VALUES
	( 7, @id_fascicolo, NULL, '2016-12-02 14:03:17', '2016-12-02 14:03:17', NULL, NULL);



	
	
