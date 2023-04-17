select 

CONCAT(pro.registro_pg,'/',pro.`anno_pg`,'/', pro.`num_pg`) as protocollo,
p.id as pagamento_id,
m.descrizione as modalita_pagamento,
val.valore_raw as spese_ammesse,
contributo.valore_raw as contributo_totale,
contributo_erogabile.valore_raw as contributo_erogabile

from pagamenti as p

join modalita_pagamento as m
on m.id = p.`modalita_pagamento_id`

join attuazione_controllo_richieste as atc
on atc.id = p.`attuazione_controllo_richiesta_id`
and atc.data_cancellazione is null

join richieste as r
on r.data_cancellazione is null and r.id = atc.`richiesta_id`

join richieste_protocollo as pro
on pro.richiesta_id = atc.richiesta_id
and pro.data_cancellazione is null
and pro.tipo = 'FINANZIAMENTO'

join valutazioni_checklist_pagamenti as cp
on cp.`pagamento_id` = p.id
and cp.data_cancellazione is null
and cp.`validata` = 1

join valutazioni_elementi_checklist_pagamenti as val on  val.valutazione_checklist_id = cp.id
and val.`elemento_id` = 293


join valutazioni_elementi_checklist_pagamenti as contributo on  contributo.valutazione_checklist_id = cp.id
and contributo.`elemento_id` = 294

join valutazioni_elementi_checklist_pagamenti as contributo_erogabile on  contributo_erogabile.valutazione_checklist_id = cp.id
and contributo_erogabile.`elemento_id` = 295

where p.data_cancellazione is null

and r.procedura_id = 8;



update pagamenti
join (
	select p.id, sum(coalesce(vpg.importo, 0)) 
	+ coalesce((
		select sum(coalesce(di_cui.importo,0) * case when vpi.piano_costo_id in(
			65,66,68,71,72,74,77,78,80
		) then 1.25 else 1 end) 

		from di_cui

		join voci_piano_costo_giustificativi as vpgi
		on di_cui.voce_piano_costo_giustificativo_id  = vpgi.id
		
		join voci_piani_costo as vpi
		on vpi.id = vpgi.voce_piano_costo_id

		
		where di_cui.pagamento_destinazione_id = p.id
			and di_cui.data_cancellazione is null
	),0) as importo

	from pagamenti as p

	join `giustificativi_pagamenti` as gp
	on p.id = gp.`pagamento_id`
	and p.data_cancellazione is null

	join `attuazione_controllo_richieste` as atc
	on atc.id = p.`attuazione_controllo_richiesta_id`
	and atc.data_cancellazione is null

	join richieste as r
	on r.id = atc.`richiesta_id`
	and r.data_cancellazione is null

	join `voci_piano_costo_giustificativi` as vpg
	on vpg.`giustificativo_pagamento_id` = gp.id
	and vpg.data_cancellazione is null

	where r.procedura_id = 8 

	group by p.id
) as rendicontati
on pagamenti.id = rendicontati.id
set pagamenti.importo_rendicontato = rendicontati.importo;

select 

CONCAT(pro.registro_pg,'/',pro.`anno_pg`,'/', pro.`num_pg`) as protocollo,
p.id as pagamento_id,
m.descrizione as modalita_pagamento,
val.valore_raw as spese_ammesse,
contributo.valore_raw as contributo_totale,
contributo_erogabile.valore_raw as contributo_erogabile,
sum(coalesce(vpg.importo, 0)) 
	+ coalesce((
		select sum(coalesce(di_cui.importo,0) * case when vpi.piano_costo_id in(
			65,66,68,71,72,74,77,78,80
		) then 1.25 else 1 end) 

		from di_cui

		join voci_piano_costo_giustificativi as vpgi
		on di_cui.voce_piano_costo_giustificativo_id  = vpgi.id
		
		join voci_piani_costo as vpi
		on vpi.id = vpgi.voce_piano_costo_id

		
		where di_cui.pagamento_destinazione_id = p.id
			and di_cui.data_cancellazione is null
	),0) as importo_rendicontato

from pagamenti as p

join modalita_pagamento as m
on m.id = p.`modalita_pagamento_id`

join attuazione_controllo_richieste as atc
on atc.id = p.`attuazione_controllo_richiesta_id`
and atc.data_cancellazione is null

join richieste as r
on r.data_cancellazione is null and r.id = atc.`richiesta_id`

join richieste_protocollo as pro
on pro.richiesta_id = atc.richiesta_id
and pro.data_cancellazione is null
and pro.tipo = 'FINANZIAMENTO'

join valutazioni_checklist_pagamenti as cp
on cp.`pagamento_id` = p.id
and cp.data_cancellazione is null
and cp.`validata` = 1

join valutazioni_elementi_checklist_pagamenti as val on  val.valutazione_checklist_id = cp.id
and val.`elemento_id` = 293


join valutazioni_elementi_checklist_pagamenti as contributo on  contributo.valutazione_checklist_id = cp.id
and contributo.`elemento_id` = 294

join valutazioni_elementi_checklist_pagamenti as contributo_erogabile on  contributo_erogabile.valutazione_checklist_id = cp.id
and contributo_erogabile.`elemento_id` = 295


join  `giustificativi_pagamenti` as gp
on  p.id = gp.`pagamento_id`
	 and gp.data_cancellazione is null
	
join `voci_piano_costo_giustificativi` as vpg
on vpg.`giustificativo_pagamento_id` = gp.id
and vpg.data_cancellazione is null

join voci_piani_costo as vp
on vp.id = vpg.voce_piano_costo_id

where p.data_cancellazione is null

and r.procedura_id = 8


group by
p.id,
pro.registro_pg,
pro.`anno_pg`,
 pro.`num_pg`,
 m.descrizione,
 val.valore_raw,
 contributo.valore_raw,
 contributo_erogabile.valore_raw
 ;
