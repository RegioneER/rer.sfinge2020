select concat(pro.registro_pg, '/', pro.anno_pg, '/', pro.num_pg) as protocollo,
REPLACE(REPLACE( a1.`valore`, '\r', ''), '\n', ' ') as tipo_appalto,
REPLACE(REPLACE( a2.`valore`, '\r', ''), '\n', ' ') as criterio_aggiudicazione,
REPLACE(REPLACE( a3.`valore`, '\r', ''), '\n', ' ') as CIG,
REPLACE(REPLACE( a4.`valore`, '\r', ''), '\n', ' ') as contraente,
REPLACE(REPLACE( a5.`valore`, '\r', ''), '\n', ' ') as piva_contraente,
REPLACE(REPLACE( a6.`valore`, '\r', ''), '\n', ' ') as estremi_contratto,
REPLACE(REPLACE( a7.`valore`, '\r', ''), '\n', ' ') as importo_contratto,
REPLACE(REPLACE( a8.`valore`, '\r', ''), '\n', ' ') as estremi_giustificativi,
REPLACE(REPLACE( a9.`valore`, '\r', ''), '\n', ' ') as spese_sostenute

from pagamenti as p
join valutazioni_checklist_pagamenti as cvp
on p.id = cvp.`pagamento_id` and cvp.`checklist_id` = 14
and cvp.`data_cancellazione` is null


join attuazione_controllo_richieste as atc
on atc.id = p.`attuazione_controllo_richiesta_id`

join richieste as r
on r.id = atc.richiesta_id

join richieste_protocollo as pro
on pro.richiesta_id = r.id and pro.tipo = 'FINANZIAMENTO' and pro.data_cancellazione is null

join procedure_operative as po
on po.id = r.procedura_id

left join `valutazioni_elementi_checklist_pagamenti` as a1
on a1.`elemento_id` = 447 and cvp.id = a1.`valutazione_checklist_id`

left join `valutazioni_elementi_checklist_pagamenti` as a2
on a2.`elemento_id` = 448 and cvp.id = a2.`valutazione_checklist_id`

left join `valutazioni_elementi_checklist_pagamenti` as a3
on a3.`elemento_id` = 449 and cvp.id = a3.`valutazione_checklist_id`

left join `valutazioni_elementi_checklist_pagamenti` as a4
on a4.`elemento_id` = 450 and cvp.id = a4.`valutazione_checklist_id`

left join `valutazioni_elementi_checklist_pagamenti` as a5
on a5.`elemento_id` = 451 and cvp.id = a5.`valutazione_checklist_id`

left join `valutazioni_elementi_checklist_pagamenti` as a6
on a6.`elemento_id` = 452 and cvp.id = a6.`valutazione_checklist_id`

left join `valutazioni_elementi_checklist_pagamenti` as a7
on a7.`elemento_id` = 453 and cvp.id = a7.`valutazione_checklist_id`

left join `valutazioni_elementi_checklist_pagamenti` as a8
on a8.`elemento_id` = 454 and cvp.id = a8.`valutazione_checklist_id`

left join `valutazioni_elementi_checklist_pagamenti` as a9
on a9.`elemento_id` = 455 and cvp.id = a9.`valutazione_checklist_id`

where  po.asse_id = 6;