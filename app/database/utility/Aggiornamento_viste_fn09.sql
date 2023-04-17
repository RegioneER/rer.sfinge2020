CREATE OR REPLACE VIEW vista_fn09 AS
SELECT
r.id AS richiesta_id,
 COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        r.id
    ) AS cod_locale_progetto,
CASE c.numero 
    WHEN 'I' THEN CONCAT(1,'/',c.anno)
    WHEN 'II' THEN CONCAT(2,'/',c.anno)
    WHEN 'III' THEN CONCAT(3,'/',c.anno)
    WHEN 'IV' THEN CONCAT(4,'/',c.anno)
    WHEN 'V' THEN CONCAT(5,'/',c.anno)
    WHEN 'VI' THEN CONCAT(6,'/',c.anno)
    WHEN 'VII' THEN CONCAT(7,'/',c.anno)
    WHEN 'VIII' THEN CONCAT(8,'/',c.anno)
    ELSE CONCAT(c.numero,'/',c.anno) END AS cod_pagamento,
c.data_approvazione as data_pagamento,
CASE  WHEN cert.importo >= 0 THEN 'C' ELSE 'D' END AS tipologia_pag,
tc.id as tc36_livello_gerarchico_id,
ABS(cert.importo) as importo_totale,
ABS(cert.importo) as importo_spesa_pubblica
FROM certificazioni_pagamenti cert
join certificazioni c on c.id = cert.`certificazione_id`
join pagamenti p on cert.pagamento_id = p.id
join `attuazione_controllo_richieste` as atc on atc.id = p.`attuazione_controllo_richiesta_id`
join richieste r on r.id = atc.`richiesta_id`
join `procedure_operative`pr on pr.id = r.`procedura_id`
join assi on assi.id = pr.asse_id
join tc36_livello_gerarchico tc on assi.livello_gerachico_id = tc.id
join richieste_protocollo as protocollo
on protocollo.richiesta_id = r.id
and protocollo.`data_cancellazione` is null
and protocollo.tipo = 'FINANZIAMENTO'
WHERE
r.flag_por = 1