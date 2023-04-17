-- integrazione livelli gerarchici associati ad obiettivi specifici
insert into richieste_livelli_gerarchici(richiesta_programma_id, classificazione_id)
select distinct rp.id, o.`livello_gerarchico_id` 
from richieste as r

join attuazione_controllo_richieste as atc
on atc.richiesta_id = r.id and atc.data_cancellazione is null

join `procedure_operative_azioni` as pa
on pa.`procedura_id` = r.`procedura_id`

join azioni as a
on a.id = pa.azione_id

join `obiettivi_specifici` as o
on o.id = a.`obiettivo_specifico_id`
and o.`livello_gerarchico_id` is not null

join richieste_programmi as rp
on rp.richiesta_id = r.id and rp.data_cancellazione is null

left join `richieste_livelli_gerarchici` as rl
on rl.`tc36_livello_gerarchico_id` = o.`livello_gerarchico_id`
and rl.`richiesta_programma_id` = rp.id 
and rl.data_cancellazione is null

where rl.id is null and r.data_cancellazione is null

UNION
-- integrazione livelli gerarchici associati ad assi

select distinct rp.id, a.`livello_gerachico_id` 
from richieste as r

join attuazione_controllo_richieste as atc
on atc.richiesta_id = r.id and atc.data_cancellazione is null

join `procedure_operative` as po
on po.id = r.`procedura_id`

join assi a
on a.id = po.asse_id

join richieste_programmi as rp
on rp.richiesta_id = r.id and rp.data_cancellazione is null

left join `richieste_livelli_gerarchici` as rl
on rl.`tc36_livello_gerarchico_id` = a.`livello_gerachico_id`
and rl.`richiesta_programma_id` = rp.id 
and rl.data_cancellazione is null

where rl.id is null and r.data_cancellazione is null;