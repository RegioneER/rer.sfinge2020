select atc.richiesta_id as id_richiesta, 
    CONCAT(pro.registro_pg,'/',pro.`anno_pg`,'/', pro.`num_pg`) as protocollo,
    CASE WHEN r.titolo IS NULL THEN '' ELSE REPLACE(REPLACE(r.titolo, '\r', ''), '\n', '') END as titolo_progetto,
    m.descrizione as tipo_pagamento,
    stati.descrizione as stato_pagamento,
    op.id as procedura_id,
    op.titolo as titolo_procedura,
    COALESCE(p.importo_rendicontato, '') as importo_rendicontato,
    COALESCE(p.importo_rendicontato_ammesso, '') as importo_rendicontato_ammesso,
    COALESCE(p.importo_rendicontato_ammesso_post_controllo, '') as importo_rendicontato_ammesso_post_controllo,
    CASE WHEN p.data_istruttoria IS NULL THEN '' ELSE DATE_FORMAT(p.data_istruttoria, '%d/%m/%Y') END as data_istruttoria ,
    CASE WHEN p.data_conclusione_progetto IS NULL THEN '' ELSE DATE_FORMAT(p.data_conclusione_progetto, '%d/%m/%Y') END as data_conclusione_progetto,
    CASE WHEN p.data_inizio_rendicontazione IS NULL THEN '' ELSE DATE_FORMAT(p.data_inizio_rendicontazione, '%d/%m/%Y') END as data_inizio_rendicontazione,
    CASE WHEN p.data_fine_rendicontazione IS NULL THEN '' ELSE DATE_FORMAT(p.data_fine_rendicontazione, '%d/%m/%Y') END as data_fine_rendicontazione,
    CASE WHEN p.data_invio IS NULL THEN '' ELSE DATE_FORMAT(p.data_invio, '%d/%m/%Y') END as data_invio_pagamento

from attuazione_controllo_richieste as atc

inner join pagamenti as p
on p.attuazione_controllo_richiesta_id = atc.id
    and p.data_cancellazione is NULL


inner join modalita_pagamento as m
on m.id = p.modalita_pagamento_id

inner join stati
on stati.id = p.stato_id

left join richieste_protocollo as pro
on pro.richiesta_id = atc.richiesta_id
and pro.data_cancellazione is null
and pro.tipo = 'FINANZIAMENTO'

join richieste as r
on r.id = atc.richiesta_id

inner join procedure_operative as op
on op.id = r.procedura_id

where 
m.codice in (
    'SALDO_FINALE',
    'UNICA_SOLUZIONE'
)
and atc.data_cancellazione is null

group by atc.richiesta_id

--------- solo per open coesione usare la query sotto ------------------

select atc.richiesta_id as id_richiesta, 
    CONCAT(pro.registro_pg,'/',pro.`anno_pg`,'/', pro.`num_pg`) as protocollo,
    CASE WHEN r.titolo IS NULL THEN '' ELSE REPLACE(REPLACE(r.titolo, '\r', ''), '\n', '') END as titolo_progetto,
    CASE WHEN r.abstract IS NULL THEN '' ELSE REPLACE(REPLACE(r.abstract , '\r', ''), '\n', '') END as sintesi_progetto
    
from attuazione_controllo_richieste as atc

left join richieste_protocollo as pro
on pro.richiesta_id = atc.richiesta_id
and pro.data_cancellazione is null
and pro.tipo = 'FINANZIAMENTO'

join richieste as r
on r.id = atc.richiesta_id

inner join procedure_operative as op
on op.id = r.procedura_id

where atc.data_cancellazione is null

group by atc.richiesta_id

------

select r.id as richiesta, sogg.denominazione as beneficiario, ist.contributo_ammesso as contributo, proc.titolo as Procedura from richieste r 
JOIN istruttorie_richieste ist on ist.richiesta_id = r.id
JOIN proponenti pr on pr.richiesta_id = r.id
JOIN soggetti sogg on pr.soggetto_id = sogg.id
JOIN procedure_operative proc on proc.id = r.procedura_id
where ist.esito_id = 1 and pr.mandatario = 1 and proc.asse_id = 6

------

select rich.id as id_richiesta, 
    CONCAT(pro.registro_pg,'/',pro.`anno_pg`,'/', pro.`num_pg`) as protocollo,
    CASE WHEN r.titolo IS NULL THEN '' ELSE REPLACE(REPLACE(r.titolo, '\r', ''), '\n', '') END as titolo_progetto,
    CASE WHEN r.abstract IS NULL THEN '' ELSE REPLACE(REPLACE(r.abstract , '\r', ''), '\n', '') END as sintesi_progetto
    
from richieste as rich

left join richieste_protocollo as pro
on pro.richiesta_id = rich.id
and pro.data_cancellazione is null
and pro.tipo = 'FINANZIAMENTO'

inner join procedure_operative as op
on op.id = rich.procedura_id

group by rich.id