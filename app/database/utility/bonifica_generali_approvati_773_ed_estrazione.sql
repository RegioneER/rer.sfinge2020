##estrazione bando 7
Select  
#pag.id,
CASE WHEN rp.num_pg IS NOT NULL 
THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg)
ELSE '-' END AS protocollo,		
sogg.denominazione as beneficiario,
man.importo_pagato as 'importo liquidato',

CASE WHEN (coalesce(og380.maggiorazione_10_perc,0) = 1 AND coalesce(estpag773.rinuncia_maggiorazione,0) = 0) 
THEN round(sum((select sum(coalesce(vg.importo_approvato,0) - coalesce(vg.importo_non_ammesso_per_superamento_massimali,0)) from 
voci_piano_costo_giustificativi vg join voci_piani_costo vpc on vpc.id = vg.voce_piano_costo_id and vpc.piano_costo_id IN (51,52,53,54,55,56)
where vg.giustificativo_pagamento_id = giust.id and giust.data_cancellazione is null and vg.data_cancellazione is null))*0.55,2)

ELSE round(sum((select sum(coalesce(vg.importo_approvato,0) - coalesce(vg.importo_non_ammesso_per_superamento_massimali,0)) from 
voci_piano_costo_giustificativi vg join voci_piani_costo vpc on vpc.id = vg.voce_piano_costo_id and vpc.piano_costo_id IN (51,52,53,54,55,56)
where vg.giustificativo_pagamento_id = giust.id and giust.data_cancellazione is null and vg.data_cancellazione is null))*0.45,2) END as 'importo ri ', 

CASE WHEN (coalesce(og380.maggiorazione_10_perc,0) = 1 AND coalesce(estpag773.rinuncia_maggiorazione,0) = 0) 
THEN round(sum((select sum(coalesce(vg.importo_approvato,0) - coalesce(vg.importo_non_ammesso_per_superamento_massimali,0)) from 
voci_piano_costo_giustificativi vg join voci_piani_costo vpc on vpc.id = vg.voce_piano_costo_id and vpc.piano_costo_id IN (57,58,59,60,61,62,63)
where vg.giustificativo_pagamento_id = giust.id and giust.data_cancellazione is null and vg.data_cancellazione is null))*0.30,2) 

ELSE round(sum((select sum(coalesce(vg.importo_approvato,0) - coalesce(vg.importo_non_ammesso_per_superamento_massimali,0)) from 
voci_piano_costo_giustificativi vg join voci_piani_costo vpc on vpc.id = vg.voce_piano_costo_id and vpc.piano_costo_id IN (57,58,59,60,61,62,63)
where vg.giustificativo_pagamento_id = giust.id and giust.data_cancellazione is null and vg.data_cancellazione is null))*0.20,2) END as 'importo ss '

from pagamenti pag
join attuazione_controllo_richieste atc on atc.id = pag.attuazione_controllo_richiesta_id
join richieste rich on rich.id = atc.richiesta_id
join giustificativi_pagamenti giust on giust.pagamento_id = pag.id
join mandati_pagamenti man on man.id = pag.mandato_pagamento_id
join richieste_protocollo rp on rp.pagamento_id = pag.id
join proponenti prop on prop.richiesta_id = rich.id
join soggetti sogg on sogg.id = prop.soggetto_id
join oggetti_richiesta og on og.richiesta_id = rich.id
join oggetti_importazione380 og380 on og380.id = og.id
join estensioni_pagamenti estpag on pag.estensione_id = estpag.id
join estensioni_pagamenti_bando_7 estpag773 on estpag773.id = estpag.id
where rich.procedura_id = 7 and pag.stato_id = 10 and man.data_mandato > '2017-01-01 00:00:00' and man.data_mandato < '2017-12-31 00:00:00' group by pag.id;

## bonifica RI
update voci_piano_costo_giustificativi as vpcg
join voci_piani_costo vpc on vpc.id = vpcg.voce_piano_costo_id
and vpc.piano_costo_id = 56
inner join giustificativi_pagamenti as gp on gp.id = vpcg.giustificativo_pagamento_id and gp.data_cancellazione is null
inner join pagamenti as p on p.id = gp.pagamento_id and p.data_cancellazione is null
inner join attuazione_controllo_richieste as atc on atc.id = p.attuazione_controllo_richiesta_id and atc.data_cancellazione is null
inner join richieste as r on r.id = atc.richiesta_id and r.data_cancellazione is null 
set vpcg.importo_approvato =
(select round(sum(coalesce(v.importo_approvato,0) - coalesce(v.importo_non_ammesso_per_superamento_massimali,0)) * 0.15, 2)
from (select * from voci_piano_costo_giustificativi) v
inner join voci_piani_costo ivpc on ivpc.id = v.voce_piano_costo_id and ivpc.piano_costo_id in(51,52,53) and ivpc.data_cancellazione is null
inner join giustificativi_pagamenti as gp2 on gp2.id = v.giustificativo_pagamento_id and gp2.data_cancellazione is null
where p.id = gp2.pagamento_id and v.data_cancellazione is null)
where vpcg.data_cancellazione is null and r.procedura_id = 7 and p.stato_id = 10;

## bonifica SS
update voci_piano_costo_giustificativi as vpcg
join voci_piani_costo vpc on vpc.id = vpcg.voce_piano_costo_id and vpc.piano_costo_id = 63
inner join giustificativi_pagamenti as gp on gp.id = vpcg.giustificativo_pagamento_id and gp.data_cancellazione is null
inner join pagamenti as p on p.id = gp.pagamento_id and p.data_cancellazione is null
inner join attuazione_controllo_richieste as atc on atc.id = p.attuazione_controllo_richiesta_id and atc.data_cancellazione is null
inner join richieste as r on r.id = atc.richiesta_id and r.data_cancellazione is null 
set vpcg.importo_approvato =
(select round(sum(coalesce(v.importo_approvato,0) - coalesce(v.importo_non_ammesso_per_superamento_massimali,0)) * 0.15, 2)
from (select * from voci_piano_costo_giustificativi) v
inner join voci_piani_costo ivpc on ivpc.id = v.voce_piano_costo_id and ivpc.piano_costo_id in(57,58,59) and ivpc.data_cancellazione is null
inner join giustificativi_pagamenti as gp2 on gp2.id = v.giustificativo_pagamento_id and gp2.data_cancellazione is null
where p.id = gp2.pagamento_id and v.data_cancellazione is null)
where vpcg.data_cancellazione is null and r.procedura_id = 7 and p.stato_id = 10;

## estrazione bando 3

Select  
pag.id,
CASE WHEN rp.num_pg IS NOT NULL 
THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg)
ELSE '-' END AS protocollo,		
sogg.denominazione as beneficiario,
man.importo_pagato as 'importo liquidato ', 

round(sum((select sum(coalesce(vg.importo_approvato,0) - coalesce(vg.importo_non_ammesso_per_superamento_massimali,0)) from 
voci_piano_costo_giustificativi vg join voci_piani_costo vpc on vpc.id = vg.voce_piano_costo_id and vpc.piano_costo_id IN (15,16,17,19,21,22)
where vg.giustificativo_pagamento_id = giust.id and giust.data_cancellazione is null and vg.data_cancellazione is null))*0.50,2) as importo_1,

round(sum((select sum(coalesce(vg.importo_approvato,0) - coalesce(vg.importo_non_ammesso_per_superamento_massimali,0)) from 
voci_piano_costo_giustificativi vg join voci_piani_costo vpc on vpc.id = vg.voce_piano_costo_id and vpc.piano_costo_id IN (18,20,23,24)
where vg.giustificativo_pagamento_id = giust.id and giust.data_cancellazione is null and vg.data_cancellazione is null))*0.50,2) as importo_2,

round(sum((select sum(coalesce(vg.importo_approvato,0) - coalesce(vg.importo_non_ammesso_per_superamento_massimali,0)) from 
voci_piano_costo_giustificativi vg join voci_piani_costo vpc on vpc.id = vg.voce_piano_costo_id and vpc.piano_costo_id IN (25)
where vg.giustificativo_pagamento_id = giust.id and giust.data_cancellazione is null and vg.data_cancellazione is null)),2) as importo_3

from pagamenti pag
join attuazione_controllo_richieste atc on atc.id = pag.attuazione_controllo_richiesta_id
join richieste rich on rich.id = atc.richiesta_id
join giustificativi_pagamenti giust on giust.pagamento_id = pag.id
join mandati_pagamenti man on man.id = pag.mandato_pagamento_id
join richieste_protocollo rp on rp.pagamento_id = pag.id
join proponenti prop on prop.richiesta_id = rich.id
join soggetti sogg on sogg.id = prop.soggetto_id
join oggetti_richiesta og on og.richiesta_id = rich.id
where rich.procedura_id = 3 and pag.stato_id = 10 and man.data_mandato > '2017-01-01 00:00:00' and man.data_mandato < '2017-12-31 00:00:00' group by pag.id

