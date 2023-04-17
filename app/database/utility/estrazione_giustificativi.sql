select 
	po.id as id_procedura,
	po.`titolo` as titolo_procedura,
	r.id as richiesta_id,
	coalesce(concat(pro.registro_pg, '/', pro.anno_pg, '/', pro.num_pg), r.id) as protocollo,
	p.id as pag_id,
	gp.id as giustificato_id,
	gp.denominazione_fornitore,
	gp.`codice_fiscale_fornitore`,
	gp.`data_giustificativo`,
	gp.`numero_giustificativo`	
from `giustificativi_pagamenti` as gp
join istruttorie_oggetti_pagamento as igp
on igp.id = gp.istruttoria_oggetto_pagamento_id and data_cancellazione is null and stato_valutazione = 'Completa'
join pagamenti as p
on p.id = gp.`pagamento_id` and p.data_cancellazione is null and p.stato_id = 10
join `attuazione_controllo_richieste` as atc
on atc.id = p.`attuazione_controllo_richiesta_id` and atc.data_cancellazione is null
join richieste as r
on r.id = atc.`richiesta_id` and r.data_cancellazione is null
join procedure_operative as po
on po.id =r.`procedura_id`
join `richieste_protocollo` as pro
on pro.richiesta_id = r.id and pro.tipo = 'FINANZIAMENTO' and pro.data_cancellazione is null
where gp.data_cancellazione is null;
