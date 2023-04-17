select 
po.asse_id as asse,
r.id as id_richiesta,
concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg) as protocollo_domanda,
p.id as id_pagamento,
concat(pp.registro_pg, '/', pp.anno_pg, '/', pp.num_pg) as protocollo_pagamento,
(
	select replace(replace(valore, '\n', ' '), '\r', '') 
	from valutazioni_elementi_checklist_pagamenti as vecp 
	join elementi_checklist_pagamenti as ecp
	on ecp.id = vecp.`elemento_id`
	where ecp.codice = 'A1' and vecp.`valutazione_checklist_id` = vpcp.id
) as tipo_appalto,
(
	select replace(replace(valore, '\n', ' '), '\r', '')  
	from valutazioni_elementi_checklist_pagamenti as vecp 
	join elementi_checklist_pagamenti as ecp
	on ecp.id = vecp.`elemento_id`
	where ecp.codice = 'A2' and vecp.`valutazione_checklist_id` = vpcp.id
) as criterio_aggiudicazione,
(
	select replace(replace(valore, '\n', ' '), '\r', '') 
	from valutazioni_elementi_checklist_pagamenti as vecp 
	join elementi_checklist_pagamenti as ecp
	on ecp.id = vecp.`elemento_id`
	where ecp.codice = 'A3' and vecp.`valutazione_checklist_id` = vpcp.id
) as cig,
(
	select replace(replace(valore, '\n', ' '), '\r', '')  
	from valutazioni_elementi_checklist_pagamenti as vecp 
	join elementi_checklist_pagamenti as ecp
	on ecp.id = vecp.`elemento_id`
	where ecp.codice = 'A4' and vecp.`valutazione_checklist_id` = vpcp.id
) as contraente,
(
	select replace(valore, '.', ',') 
	from valutazioni_elementi_checklist_pagamenti as vecp 
	join elementi_checklist_pagamenti as ecp
	on ecp.id = vecp.`elemento_id`
	where ecp.codice = 'A7' and vecp.`valutazione_checklist_id` = vpcp.id
) as importo_contratto,
(
	select replace(replace(valore, '\n', ' '), '\r', '')  
	from valutazioni_elementi_checklist_pagamenti as vecp 
	join elementi_checklist_pagamenti as ecp
	on ecp.id = vecp.`elemento_id`
	where ecp.codice = 'A8' and vecp.`valutazione_checklist_id` = vpcp.id
) as estremi_giustificativi,
(
	select replace(valore, '.', ',') 
	from valutazioni_elementi_checklist_pagamenti as vecp 
	join elementi_checklist_pagamenti as ecp
	on ecp.id = vecp.`elemento_id`
	where ecp.codice = 'A9' and vecp.`valutazione_checklist_id` = vpcp.id
) as importo_spese_ammissibili

from  valutazioni_checklist_pagamenti as vpcp


join checklist_pagamenti as cp
on cp.id = vpcp.`checklist_id`
and cp.`tipologia` = 'APPALTI_PUBBLICI'

join pagamenti as p
on p.id = vpcp.pagamento_id
and p.data_cancellazione is null

join `attuazione_controllo_richieste` as atc
on atc.id = p.`attuazione_controllo_richiesta_id`
and atc.data_cancellazione is null

join richieste as r
on r.id = atc.richiesta_id
and r.data_cancellazione is null

join procedure_operative as po
on po.id = r.procedura_id

join richieste_protocollo as rp
on r.id = rp.richiesta_id
and rp.data_cancellazione is null
and rp.tipo = "FINANZIAMENTO"

join richieste_protocollo as pp
on p.id = pp.pagamento_id
and pp.data_cancellazione is null
and pp.tipo = "PAGAMENTO"

where po.asse_id in (4,5,6)
and vpcp.data_cancellazione is null
