INSERT INTO finanziamenti (richiesta_id, fondo_id, norma_id, delibera_cipe_id, localizzazione_geografica_id, cofinanziatore_id, importo, data_cancellazione, data_creazione, data_modifica)

-- FIN PRIVATO
select 
	atc.richiesta_id,
	tc33_fonte_finanziaria.id, -- FONDO
	tc35_norma.id, -- NORMA
	tc34_delibera_cipe.id, -- DELIBERA CIPE
	NULL, 
	NULL,
	(costo_ammesso  - contributo_ammesso), -- CONTRIBUTO PRIVATO
	NULL, 
	atc.data_creazione, 
	atc.data_modifica

from attuazione_controllo_richieste atc

join tc33_fonte_finanziaria
on tc33_fonte_finanziaria.cod_fondo = 'PRT'

join tc34_delibera_cipe 
on tc34_delibera_cipe.cod_del_cipe = '99999'

join tc35_norma on tc35_norma.cod_norma = '99999'

join richieste ric on ric.id = atc.richiesta_id
join procedure_operative as po on po.id = ric.procedura_id
join istruttorie_richieste istr on istr.richiesta_id = atc.richiesta_id
left join finanziamenti as f
on f.richiesta_id = ric.id 
	and f.fondo_id  = tc33_fonte_finanziaria.id
	and f.norma_id  = tc35_norma.id
	and f.delibera_cipe_id = tc34_delibera_cipe.id
where 
 ric.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and costo_ammesso > contributo_ammesso
and (ric.stato_id in(4, 5) or po.tipo = 'ACQUISIZIONI')

AND f.id is null

UNION

-- UNIONE EUROPEA
select 
	atc.richiesta_id,
	tc33_fonte_finanziaria.id, -- FONDO
	tc35_norma.id, -- NORMA
	tc34_delibera_cipe.id, -- DELIBERA CIPE
	NULL, 
	NULL,
	ifnull( 
		(select round(contributo_ammesso * 50 / 100, 2) from istruttorie_richieste where richiesta_id = atc.richiesta_id),
		(select round(importo_anno_1 * 50 / 100, 2) from voci_piani_costo where richiesta_id =  atc.richiesta_id and data_cancellazione IS NULL) -- PER I NON BANDI
	),-- CONTRIBUTO UE
	NULL, 
	atc.data_creazione, 
	atc.data_modifica

from attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
	AND ric.data_cancellazione is NULL

join tc33_fonte_finanziaria
on tc33_fonte_finanziaria.cod_fondo = 'ERDF'

join tc34_delibera_cipe 
on tc34_delibera_cipe.cod_del_cipe = '99999'

join tc35_norma on tc35_norma.cod_norma = '99999'

left join finanziamenti as f
on f.richiesta_id = ric.id 
	and f.fondo_id  = tc33_fonte_finanziaria.id
	and f.norma_id  = tc35_norma.id
	and f.delibera_cipe_id = tc34_delibera_cipe.id

where 
 f.id is null and
 atc.data_cancellazione is NULL


UNION
-- FIN STATO
select 
	atc.richiesta_id,
	tc33_fonte_finanziaria.id, -- FONDO
	tc35_norma.id, -- NORMA
	tc34_delibera_cipe.id, -- DELIBERA CIPE
	NULL, 
	NULL,
	ifnull( 
		(select round(contributo_ammesso * 35 / 100, 2) from istruttorie_richieste where richiesta_id = atc.richiesta_id),
		(select round(importo_anno_1 * 35 / 100, 2) from voci_piani_costo where richiesta_id =  atc.richiesta_id and data_cancellazione IS NULL) -- PER I NON BANDI
	),-- CONTRIBUTO STATO

	NULL, 
	atc.data_creazione, 
	atc.data_modifica

from attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
and ric.data_cancellazione is NULL

join tc33_fonte_finanziaria
on tc33_fonte_finanziaria.cod_fondo = 'FDR'

join tc34_delibera_cipe 
on tc34_delibera_cipe.cod_del_cipe = '99999'

join tc35_norma on tc35_norma.cod_norma = '202'

left join finanziamenti as f
on f.richiesta_id = ric.id 
	and f.fondo_id  = tc33_fonte_finanziaria.id
	and f.norma_id  = tc35_norma.id
	and f.delibera_cipe_id = tc34_delibera_cipe.id

where 
 	f.id is null and
	atc.data_cancellazione is NULL


UNION

-- CONTRIBUTO REGIONE
select 
	atc.richiesta_id,
	(select id from tc33_fonte_finanziaria where cod_fondo = 'FPREG'), -- FONDO
	(select id from tc35_norma where cod_norma = '99999'), -- NORMA
	(select id from tc34_delibera_cipe where cod_del_cipe = '99999'), -- DELIBERA CIPE
	NULL, 
	NULL,
	ifnull( 
		(select contributo_ammesso - round(contributo_ammesso * 50 / 100, 2) - round(contributo_ammesso * 35 / 100, 2) from istruttorie_richieste where richiesta_id = atc.richiesta_id),
		(select importo_anno_1 - round(importo_anno_1 * 50 / 100, 2) - round(importo_anno_1 * 35 / 100, 2) from voci_piani_costo where richiesta_id =  atc.richiesta_id and data_cancellazione IS NULL) -- PER I NON BANDI
	), -- CONTRIBUTO REGIONE
	NULL, 
	atc.data_creazione, 
	atc.data_modifica

from attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join tc33_fonte_finanziaria
on tc33_fonte_finanziaria.cod_fondo = 'FPREG'

join tc34_delibera_cipe 
on tc34_delibera_cipe.cod_del_cipe = '99999'

join tc35_norma on tc35_norma.cod_norma = '99999'

left join finanziamenti as f
on f.richiesta_id = ric.id 
	and f.fondo_id  = tc33_fonte_finanziaria.id
	and f.norma_id  = tc35_norma.id
	and f.delibera_cipe_id = tc34_delibera_cipe.id
where 
 	f.id is null and
	atc.data_cancellazione is NULL;