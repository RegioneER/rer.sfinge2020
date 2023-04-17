# bando Valorizzazione delle risorse  artistiche, culturali e ambientali - procedure_operative id:2
# misura 6.6.1
INSERT INTO `procedure_dati_cup`
(`id`, `natura_id`, `settore_id`, `tipologia_id`, `sotto_settore_id`, `categoria_id`, `procedura_id`, `codici_tipologia_cop_finanz`) 
VALUES (1, 4, 2, 31, 9, 45, 2, '001,002,004,005,006,007');
# misura 6.6.2
# INSERT INTO `procedure_dati_cup`
# (`id`, `natura_id`, `settore_id`, `tipologia_id`, `sotto_settore_id`, `categoria_id`, `procedura_id`) 
# VALUES (1, 4, 5, 31, 17, 89, 2);

# bando Progetti di promozione dell'export per imprese non esportatrici - procedure operative id: 3
INSERT INTO `procedure_dati_cup`
(`id`, `natura_id`, `settore_id`, `tipologia_id`, `sotto_settore_id`, `categoria_id`, `procedura_id`, `codici_tipologia_cop_finanz`) 
VALUES (2, 5, 9, 44, 39, 225, 3, '002,007');

# bando Sostegno alla creazione e al consolidamento di start up innovative - procedure operative id: 4
INSERT INTO `procedure_dati_cup`
(`id`, `natura_id`, `settore_id`, `tipologia_id`, `sotto_settore_id`, `categoria_id`, `procedura_id`, `codici_tipologia_cop_finanz`) 
VALUES (3, 5, 8, 44, 37, 201, 4, '002,007');


INSERT INTO `istruttorie_richieste` (`richiesta_id`, `data_creazione`, `costo_ammesso`, `contributo_ammesso`) 
SELECT r.id, CURRENT_TIMESTAMP(), 10000.23, 0 FROM `richieste` r WHERE r.stato_id = 5
; 


UPDATE `istruttorie_richieste` ist SET ist.richiedi_cup=1;
UPDATE `istruttorie_richieste` ist SET ist.richiedi_cup=0 WHERE ist.richiesta_id IN 
(SELECT id FROM richieste WHERE procedura_id = 1);