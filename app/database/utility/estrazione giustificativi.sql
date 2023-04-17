select
	r.id as id_richiesta, 
    g.id as id_giustificativo,
	CONCAT(p.registro_pg,'/',p.`anno_pg`,'/', p.`num_pg`) as protocollo,
    case when g.importo_approvato is null then 'incompleta' else 'completa' end as istruttoria_imputazione_spesa,
	case when iop.id is not null then iop.stato_valutazione else 'incompleta' end as istruttoria_generale,
    g.denominazione_fornitore,
    g.codice_fiscale_fornitore,
    g.numero_giustificativo,
	DATE_FORMAT(g.data_giustificativo, '%d/%m/%Y') as data_giustificativo,
	g.importo_giustificativo,
	g.importo_richiesto,
	g.importo_approvato as importo_ammesso,
    SUM(coalesce(v.importo,0) - coalesce(v.importo_approvato,0)),
	group_concat(pc.titolo  SEPARATOR ' - ') as voci_costo 
	
from `attuazione_controllo_richieste` as atc
join richieste as r
on r.id = atc.richiesta_id
and r.data_cancellazione is null

left join `richieste_protocollo` as p
on p.`richiesta_id` = r.id
and p.data_cancellazione is null

join pagamenti as pag
on pag.attuazione_controllo_richiesta_id = atc.id
and pag.data_cancellazione is null

join giustificativi_pagamenti as g
on g.pagamento_id = pag.id
and g.data_cancellazione is null

left join istruttorie_oggetti_pagamento as iop
on iop.id = g.istruttoria_oggetto_pagamento_id

left join voci_piano_costo_giustificativi as v
on v.giustificativo_pagamento_id = g.id
and v.data_cancellazione is null

left join voci_piani_costo as vpc 
on v.voce_piano_costo_id = vpc.id
and v.data_cancellazione is null

left join piani_costo as pc
on pc.id = vpc.piano_costo_id
and pc.data_cancellazione is null

where r.procedura_id = 60
and atc.data_cancellazione is null

group by 
    r.id ASC,
     g.id ASC,
    p.registro_pg ASC,
    p.`anno_pg` ASC,
    p.`num_pg` ASC,
    g.importo_approvato ASC, 
    iop.id ASC, 
    iop.stato_valutazione ASC,
    g.denominazione_fornitore ASC,
    g.codice_fiscale_fornitore ASC,
    g.numero_giustificativo ASC,
    g.data_giustificativo ASC,
    g.importo_giustificativo ASC,
    g.importo_richiesto ASC,
    g.importo_approvato ASC 
;