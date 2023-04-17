

SET @id_procedura = 7;

/* DEBUG: per vedere i risultati 
SELECT
fvinc.id fascicoli_vincoli_id,
fc.id fascicoli_campi_id,
ffram2.id fascicoli_frammenti_id,
fpag2.id fascicoli_pagine_id,
ff.id fascicoli_fascicoli_id,
fproc.id fascicoli_procedure_id
FROM fascicoli_procedure fproc
JOIN fascicoli_fascicoli ff ON fproc.fascicolo_id = ff.id
JOIN fascicoli_pagine fpag1 ON ff.indice_id = fpag1.id
LEFT JOIN fascicoli_frammenti ffram ON ffram.pagina_id = fpag1.id
LEFT JOIN fascicoli_pagine fpag2 ON ffram.id = fpag2.frammentoContenitore_id OR (fpag2.frammentoContenitore_id IS NULL AND ff.indice_id = fpag2.id)
LEFT JOIN fascicoli_frammenti ffram2 ON ffram2.pagina_id = fpag2.id
LEFT JOIN fascicoli_campi fc ON fc.frammento_id = ffram2.id
LEFT JOIN fascicoli_vincoli fvinc ON fvinc.campo_id = fc.id
 WHERE fproc.procedura_id = @id_procedura;
*/

SELECT
 @fascicoli_vincoli_id   := GROUP_CONCAT(DISTINCT IFNULL(fvinc.id , -1)),
 @fascicoli_campi_id     := GROUP_CONCAT(DISTINCT IFNULL(fc.id    , -1)),
 @fascicoli_frammenti_id := GROUP_CONCAT(DISTINCT IFNULL(ffram2.id, -1)),
 @fascicoli_pagine_id    := GROUP_CONCAT(DISTINCT IFNULL(fpag2.id , -1)),
 @fascicoli_fascicoli_id := GROUP_CONCAT(DISTINCT IFNULL(ff.id    , -1)),
 @fascicoli_procedure_id := GROUP_CONCAT(DISTINCT IFNULL(fproc.id , -1))
FROM fascicoli_procedure_rendiconti fproc
JOIN fascicoli_fascicoli ff ON fproc.fascicolo_id = ff.id
JOIN fascicoli_pagine fpag1 ON ff.indice_id = fpag1.id
LEFT JOIN fascicoli_frammenti ffram ON ffram.pagina_id = fpag1.id
LEFT JOIN fascicoli_pagine fpag2 ON ffram.id = fpag2.frammentoContenitore_id OR (fpag2.frammentoContenitore_id IS NULL AND ff.indice_id = fpag2.id)
LEFT JOIN fascicoli_frammenti ffram2 ON ffram2.pagina_id = fpag2.id
LEFT JOIN fascicoli_campi fc ON fc.frammento_id = ffram2.id
LEFT JOIN fascicoli_vincoli fvinc ON fvinc.campo_id = fc.id
WHERE fproc.procedura_id = @id_procedura;

SELECT @fascicoli_istanze_fascicoli_id := GROUP_CONCAT(DISTINCT IFNULL(id , -1)) FROM fascicoli_istanze_fascicoli WHERE fascicolo_id = @fascicoli_fascicoli_id;

SELECT '/* SELEZIONARE TUTTI I SEGUENTI RISULTATI E LANCIARLI SULLA SHELL, IN CASO DI SUCCESS COMMITTARE */'
UNION
SELECT CONCAT('/* PROCEDURA ID: ', @id_procedura,  '*/')
UNION
SELECT 'BEGIN;' 
UNION 
SELECT 'SET FOREIGN_KEY_CHECKS=0;' 
UNION 
SELECT  CONCAT('DELETE FROM fascicoli_vincoli WHERE id IN (', @fascicoli_vincoli_id, ');')
UNION 
SELECT  CONCAT('DELETE FROM fascicoli_istanze_campi WHERE campo_id IN (', @fascicoli_campi_id, ');')
UNION 
SELECT  CONCAT('DELETE FROM fascicoli_campi WHERE id IN (', @fascicoli_campi_id, ');')
UNION 
SELECT  CONCAT('DELETE FROM fascicoli_istanze_frammenti WHERE frammento_id IN (', @fascicoli_frammenti_id, ');')
UNION 
SELECT  CONCAT('DELETE FROM fascicoli_frammenti WHERE id IN (', @fascicoli_frammenti_id, ');')
UNION
SELECT  CONCAT('DELETE FROM fascicoli_istanze_pagine WHERE pagina_id IN (', @fascicoli_pagine_id, ');')
UNION 
SELECT  CONCAT('DELETE FROM fascicoli_pagine WHERE id IN (', @fascicoli_pagine_id, ');')
UNION
SELECT  CONCAT('DELETE FROM fascicoli_istanze_fascicoli WHERE fascicolo_id IN (', @fascicoli_fascicoli_id, ');')
UNION 
SELECT  CONCAT('DELETE FROM fascicoli_fascicoli WHERE id IN (', @fascicoli_fascicoli_id, ');')
UNION
SELECT  CONCAT('DELETE FROM fascicoli_procedure_rendiconti WHERE id IN (', @fascicoli_procedure_id, ');')
UNION
SELECT  CONCAT('UPDATE pagamenti SET istanza_fascicolo_id = NULL WHERE istanza_fascicolo_id IN (', @fascicoli_istanze_fascicoli_id, ');')
UNION
SELECT 'SET FOREIGN_KEY_CHECKS=1;' 
 

