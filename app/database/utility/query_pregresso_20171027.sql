-- AP04 
-- richieste_programmi - per ogni richiesta 1 riga
INSERT INTO richieste_programmi (programma_id, specifica_stato_id, richiesta_id, stato, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da) 
select 
	(select id from tc4_programma where data_cancellazione is NULL and cod_programma = '2014IT16RFOP008'),
	NULL, 
	atc.richiesta_id, 
	1,  
	NULL,
	atc.data_creazione, 
	atc.data_modifica, 
	NULL, 
	NULL
	from attuazione_controllo_richieste atc 
	join richieste ric on ric.id = atc.richiesta_id
	join proponenti pro on pro.richiesta_id = ric.id
	join soggetti sog on sog.id = pro.soggetto_id
	where 
	pro.mandatario = 1
	and pro.data_cancellazione is NULL
	and ric.data_cancellazione is NULL
	and sog.data_cancellazione is NULL
	and atc.data_cancellazione is NULL
	and ric.stato_id in(4, 5);

/***************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************/

-- AP05 
-- richiesta_strumento_attuativo - per ogni richiesta inserire 1 riga
INSERT INTO richiesta_strumento_attuativo (stru_att_id, richiesta_id, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da) 
select 
	1, 
	atc.richiesta_id,
	NULL, 
	atc.data_creazione, 
	atc.data_modifica,
	NULL, 
	NULL
	from attuazione_controllo_richieste atc 
	join richieste ric on ric.id = atc.richiesta_id
	join proponenti pro on pro.richiesta_id = ric.id
	join soggetti sog on sog.id = pro.soggetto_id
	where pro.mandatario = 1
	and pro.data_cancellazione is NULL
	and ric.data_cancellazione is NULL
	and sog.data_cancellazione is NULL
	and atc.data_cancellazione is NULL
	and ric.stato_id in(4, 5);	

/***************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************/

-- AP06
-- localizzazione_geografica - per ogni richiesta inserire 1 riga

-- PARTE 1: se il proponente ha sede_legale_come_operativa = 1 oppure sede_legale_come_operativa = NULL => prendo la sede dal SOGGETTO
INSERT INTO localizzazione_geografica (richiesta_id, localizzazione_id, indirizzo, cap, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
	atc.richiesta_id,
	(select tc16_localizzazione_geografica_id from geo_comuni join soggetti on geo_comuni.id = soggetti.comune_id where soggetti.id = sog.id), -- attenzione, alcuni comuni non hanno il corrispettivo tc16
	(select concat(via, ", ", civico) from soggetti where id = sog.id),
	(select cap from soggetti where id = sog.id),
	NULL,
	atc.data_creazione, 
	atc.data_modifica, 
	NULL, 
	NULL
	from attuazione_controllo_richieste atc 
	join richieste ric on ric.id = atc.richiesta_id
	join proponenti pro on pro.richiesta_id = ric.id
	join soggetti sog on sog.id = pro.soggetto_id
	join geo_comuni gc on gc.id = sog.comune_id
	where 1=1
	and pro.mandatario = 1
	and (pro.sede_legale_come_operativa is null or pro.sede_legale_come_operativa = 1) 
	and pro.data_cancellazione is NULL
	and ric.data_cancellazione is NULL
	and sog.data_cancellazione is NULL
	and atc.data_cancellazione is NULL
	and gc.tc16_localizzazione_geografica_id is not null
	and ric.stato_id in(4, 5);

-- PARTE 2: se il proponente ha sede_legale_come_operativa = 0 => prendo la sede dall'indirizzo della prima SEDE_OPERATIVA non cancellata
INSERT INTO localizzazione_geografica (richiesta_id, localizzazione_id, indirizzo, cap, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
	atc.richiesta_id,
		(select geo_comuni.tc16_localizzazione_geografica_id 
		from sedi 
		join sedi_operative on sedi.id = sedi_operative.sede_id 
		join indirizzi on sedi.indirizzo_id = indirizzi.id 
		join geo_comuni on geo_comuni.id = indirizzi.comune_id
		where 1 = 1 
		and sedi_operative.proponente_id = pro.id
		and sedi.data_cancellazione is NULL
		and sedi_operative.data_cancellazione is NULL
		order by sedi_operative.id ASC
		limit 1) as TC16, -- TC16_LOCALIZZAZIONE_GEOGRAFICA
		(select concat(via, ", ", civico) from sedi 
		join sedi_operative on sedi.id = sedi_operative.sede_id 
		join indirizzi on sedi.indirizzo_id = indirizzi.id where 1 = 1 
		and sedi_operative.proponente_id = pro.id
		and sedi.data_cancellazione is NULL
		and sedi_operative.data_cancellazione is NULL
		order by sedi_operative.id ASC
		limit 1), -- VIA + CIVICO
		(select cap from sedi 
		join sedi_operative on sedi.id = sedi_operative.sede_id 
		join indirizzi on sedi.indirizzo_id = indirizzi.id where 1 = 1 
		and sedi_operative.proponente_id = pro.id
		and sedi.data_cancellazione is NULL
		and sedi_operative.data_cancellazione is NULL
		order by sedi_operative.id ASC
		limit 1), -- CAP
	NULL,
	atc.data_creazione, 
	atc.data_modifica, 
	NULL, 
	NULL
	from attuazione_controllo_richieste atc 
	join richieste ric on ric.id = atc.richiesta_id
	join proponenti pro on pro.richiesta_id = ric.id
	join soggetti sog on sog.id = pro.soggetto_id
	join geo_comuni gc on gc.id = sog.comune_id
	where 1=1
	and pro.mandatario = 1
	and (pro.sede_legale_come_operativa is not null and pro.sede_legale_come_operativa = 0)
	and pro.data_cancellazione is NULL
	and ric.data_cancellazione is NULL
	and sog.data_cancellazione is NULL
	and atc.data_cancellazione is NULL
	and gc.tc16_localizzazione_geografica_id is not null
	and ric.stato_id in(4, 5);

/***************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************/

-- FN00
-- finanziamenti - per ogni richiesta 3 righe(4 se c'è la parte privata)

-- CONTRIBUTO UE
INSERT INTO finanziamenti (richiesta_id, fondo_id, norma_id, delibera_cipe_id, localizzazione_geografica_id, cofinanziatore_id, importo, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
	atc.richiesta_id,
	(select id from tc33_fonte_finanziaria where cod_fondo = 'ERDF'), -- FONDO
	(select id from tc35_norma where cod_norma = '99999'), -- NORMA
	(select id from tc34_delibera_cipe where cod_del_cipe = '99999'), -- DELIBERA CIPE
	NULL, 
	NULL,
	ifnull( 
		(select round(contributo_ammesso * 50 / 100, 2) from istruttorie_richieste where richiesta_id = atc.richiesta_id),
		(select round(importo_anno_1 * 50 / 100, 2) from voci_piani_costo where richiesta_id =  atc.richiesta_id and data_cancellazione IS NULL) -- PER I NON BANDI
	),-- CONTRIBUTO UE
	NULL, 
	atc.data_creazione, 
	atc.data_modifica,
	NULL, 
	NULL
from attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join proponenti pro on pro.richiesta_id = ric.id
join soggetti sog on sog.id = pro.soggetto_id
where 
pro.mandatario = 1
and pro.data_cancellazione is NULL
and ric.data_cancellazione is NULL
and sog.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5);

-- CONTRIBUTO STATO
INSERT INTO finanziamenti (richiesta_id, fondo_id, norma_id, delibera_cipe_id, localizzazione_geografica_id, cofinanziatore_id, importo, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
	atc.richiesta_id,
	(select id from tc33_fonte_finanziaria where cod_fondo = 'FDR'), -- FONDO
	(select id from tc35_norma where cod_norma = '202'), -- NORMA
	(select id from tc34_delibera_cipe where cod_del_cipe = '99999'), -- DELIBERA CIPE
	NULL, 
	NULL,
	ifnull( 
		(select round(contributo_ammesso * 35 / 100, 2) from istruttorie_richieste where richiesta_id = atc.richiesta_id),
		(select round(importo_anno_1 * 35 / 100, 2) from voci_piani_costo where richiesta_id =  atc.richiesta_id and data_cancellazione IS NULL) -- PER I NON BANDI
	),-- CONTRIBUTO STATO

	NULL, 
	atc.data_creazione, 
	atc.data_modifica,
	NULL, 
	NULL
from attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join proponenti pro on pro.richiesta_id = ric.id
join soggetti sog on sog.id = pro.soggetto_id
where 
pro.mandatario = 1
and pro.data_cancellazione is NULL
and ric.data_cancellazione is NULL
and sog.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5);	

-- CONTRIBUTO REGIONE
INSERT INTO finanziamenti (richiesta_id, fondo_id, norma_id, delibera_cipe_id, localizzazione_geografica_id, cofinanziatore_id, importo, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
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
	atc.data_modifica,
	NULL, 
	NULL
from attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join proponenti pro on pro.richiesta_id = ric.id
join soggetti sog on sog.id = pro.soggetto_id
where 
pro.mandatario = 1
and pro.data_cancellazione is NULL
and ric.data_cancellazione is NULL
and sog.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5);	

-- CONTRIBUTO PRIVATO
INSERT INTO finanziamenti (richiesta_id, fondo_id, norma_id, delibera_cipe_id, localizzazione_geografica_id, cofinanziatore_id, importo, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
	atc.richiesta_id,
	(select id from tc33_fonte_finanziaria where cod_fondo = 'PRT'), -- FONDO
	(select id from tc35_norma where cod_norma = '99999'), -- NORMA
	(select id from tc34_delibera_cipe where cod_del_cipe = '99999'), -- DELIBERA CIPE
	NULL, 
	NULL,
	(costo_ammesso  - contributo_ammesso), -- CONTRIBUTO PRIVATO
	NULL, 
	atc.data_creazione, 
	atc.data_modifica,
	NULL, 
	NULL
from attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join proponenti pro on pro.richiesta_id = ric.id
join soggetti sog on sog.id = pro.soggetto_id
join istruttorie_richieste istr on istr.richiesta_id = atc.richiesta_id
where 
pro.mandatario = 1
and pro.data_cancellazione is NULL
and ric.data_cancellazione is NULL
and sog.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and costo_ammesso > contributo_ammesso
and ric.stato_id in(4, 5);		

/***************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************/

-- FN02 
-- richieste_piano_costi - per ogni richiesta 1 riga

INSERT INTO richieste_piano_costi(richiesta_id, anno_piano, importo_da_realizzare, importo_realizzato, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
	atc.richiesta_id,
	DATE_FORMAT (now(), '%Y'), -- ANNO, PER ORA E' SEMPRE L'ANNO CORRENTE
	ifnull(
		(select costo_ammesso from istruttorie_richieste where richiesta_id =  atc.richiesta_id and data_cancellazione IS NULL), -- IMPORTO DA REALIZZARE BANDI
		(select importo_anno_1 from voci_piani_costo where richiesta_id =  atc.richiesta_id and data_cancellazione IS NULL) -- IMPORTO DA REALIZZARE NON BANDI
	),
	0.00,
	NULL, 
	atc.data_creazione, 
	atc.data_modifica,
	NULL,
	NULL
from attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join proponenti pro on pro.richiesta_id = ric.id
join soggetti sog on sog.id = pro.soggetto_id
where 
pro.mandatario = 1
and pro.data_cancellazione is NULL
and ric.data_cancellazione is NULL
and sog.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5);

/***************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************/
-- FN03 - Inserisce 1 riga per ogni voce spesa di ogno richiesta. SCOPPIA PERCHE' NON MATCHANO TC37 CON I CODICI DI TIPI_VOCE_SPESA!!! 
INSERT INTO voci_spesa (richiesta_id, tipo_voce_spesa_id, importo, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select atc.richiesta_id  as richiesta_id, 
(select a.id 
	from tc37_voce_spesa a
	join tipi_voce_spesa b on a.voce_spesa = b.codice
	join piani_costo c on c.tipo_voce_id = b.id
	join voci_piani_costo d on d.piano_costo_id = c.id
	where d.id = vpc.id) as tc37,
(ifnull(ivpc.importo_ammissibile_anno_1, 0) + ifnull(ivpc.importo_ammissibile_anno_2, 0) + ifnull(ivpc.importo_ammissibile_anno_3, 0) + ifnull(ivpc.importo_ammissibile_anno_4, 0) + ifnull(ivpc.importo_ammissibile_anno_5, 0) + ifnull(ivpc.importo_ammissibile_anno_6, 0) + ifnull(ivpc.importo_ammissibile_anno_7, 0)) as somma_importi,
NULL, 
atc.data_creazione, 
atc.data_modifica,
NULL, 
NULL
from attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join proponenti pro on pro.richiesta_id = ric.id
join soggetti sog on sog.id = pro.soggetto_id
join voci_piani_costo vpc on vpc.richiesta_id = ric.id
join istruttorie_voci_piani_costo ivpc on ivpc.voce_piano_costo_id = vpc.id 
where 
pro.mandatario = 1
and pro.data_cancellazione is NULL
and ric.data_cancellazione is NULL
and sog.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ivpc.data_cancellazione is NULL
and vpc.data_cancellazione is NULL
and ric.stato_id in(4, 5)
order by ric.id ASC;

/***************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************/

-- PR00 
-- iter_progetto - per ogni richiesta 1 riga
INSERT INTO iter_progetto(richiesta_id, fase_procedurale_id, data_inizio_prevista, data_inizio_effettiva, data_fine_prevista, data_fine_effettiva, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da )
select 
	atc.richiesta_id,
	(select a.id 
	from tc46_fase_procedurale a 
	join cup_nature b on a.codice_natura_cup = b.codice
	join istruttorie_richieste c on c.cup_natura_id = b.id 
	where c.richiesta_id = atc.richiesta_id and a.data_cancellazione IS NULL and c.data_cancellazione IS NULL and a.descrizione_fase in('Stipula Contratto', 'Attribuzione finanziamento')),
	now(), now(), now(), now(), -- DATE INIZIO E FINE, PREVISTE E EFFETTIVE, SONO 4 DATE PER ORA TUTTE NOW.
	NULL, 
	atc.data_creazione, 
	atc.data_modifica,
	NULL,
	NULL
from attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join proponenti pro on pro.richiesta_id = ric.id
join soggetti sog on sog.id = pro.soggetto_id
where 
pro.mandatario = 1
and pro.data_cancellazione is NULL
and ric.data_cancellazione is NULL
and sog.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5);

/***************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************/

-- PR01 
-- richiesta_stato_attuazione_progetto - per ogni richiesta 1 riga

INSERT INTO richiesta_stato_attuazione_progetto (richiesta_id, stato_progetto_id, data_riferimento, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
	atc.richiesta_id,
	(select id from tc47_stato_progetto where data_cancellazione is NULL and descr_stato_prg = 'In Corso di esecuzione'),
	now(), -- DATA RIFERIMENTO PER ORA SEMPRE NOW
	NULL, 
	atc.data_creazione, 
	atc.data_modifica,
	NULL, 
	NULL
from attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join proponenti pro on pro.richiesta_id = ric.id
join soggetti sog on sog.id = pro.soggetto_id
where 
pro.mandatario = 1
and pro.data_cancellazione is NULL
and ric.data_cancellazione is NULL
and sog.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5); 

/***************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************/

-- SC00
-- soggetti_collegati - per ogni richiesta inserire 2 righe(una con il soggetto programmatore e una con il beneficiario)

-- PROGRAMMATORE DEL PROGETTO.
INSERT INTO soggetti_collegati (ruolo_sog_id, richiesta_id, soggetto_id, cod_uni_ipa, note, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da) 
select 
	(select id from tc24_ruolo_soggetto where cod_ruolo_sog = 1 and descrizione_ruolo_soggetto = 'Programmatore del progetto' limit 1),
	atc.richiesta_id,
	3438, -- EMILIA ROMAGNA
	NULL, NULL, --  cod_uni_ipa, note
	NULL, 
	atc.data_creazione, 
	atc.data_modifica,
	NULL, 
	NULL
from attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join proponenti pro on pro.richiesta_id = ric.id
join soggetti sog on sog.id = pro.soggetto_id
where 
pro.mandatario = 1
and pro.data_cancellazione is NULL
and ric.data_cancellazione is NULL
and sog.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5);

-- BENEFICIARIO DEL PROGETTO.
INSERT INTO soggetti_collegati (ruolo_sog_id, richiesta_id, soggetto_id, cod_uni_ipa, note, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da) 
select 
	(select id from tc24_ruolo_soggetto where cod_ruolo_sog = 2 and descrizione_ruolo_soggetto = 'Beneficiario del progetto' limit 1),
	atc.richiesta_id,
	sog.id, -- EMILIA ROMAGNA
	NULL, NULL, --  cod_uni_ipa, note
	NULL, 
	atc.data_creazione, 
	atc.data_modifica,
	NULL, 
	NULL
from attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join proponenti pro on pro.richiesta_id = ric.id
join soggetti sog on sog.id = pro.soggetto_id
where 
pro.mandatario = 1
and pro.data_cancellazione is NULL
and ric.data_cancellazione is NULL
and sog.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5);


/***************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************
****************************************************************************************************************************************************************************/

-- FN10
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
-- ECONOMIE PRIVATI - *** CASO 1 ***
-- Per ogni richiesta 4 righe

INSERT INTO economie (richiesta_id, fondo_id, importo, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
atc.richiesta_id,
(select id from tc33_fonte_finanziaria where cod_fondo = 'PRT'),
-- IMPORTO PER I PRIVATI
(ist.costo_ammesso -- COSTO TOTALE DEL PROGETTO / il costo ammesso, quanto poteva spendere il beneficiario al massimo 
- -- MENO
(select sum(importo_approvato) 
	from giustificativi_pagamenti gp 
	join pagamenti p1 on gp.pagamento_id = p1.id 
	where 
	p1.attuazione_controllo_richiesta_id = atc.id
	and gp.data_cancellazione is null
	and p1.data_cancellazione is null
) -- CONTRIBUTO CONCESSO / quanto ha speso il beneficiario, la somma dei pagamenti
- -- MENO	
	-- QUESTA SOTTOQUERY RESTITUISCE LA SOMMA DELLE ECONOMIA UE/STATO/REGIONE
	(	select ist.contributo_ammesso -- CONTRIBUTO CONCESSO / il contributo massimo ottenibile dal beneficiario
		- 
		(select sum(importo_pagato) 
		from mandati_pagamenti mp 
		join pagamenti p2 on mp.id = p2.mandato_pagamento_id 
		where 
		p2.attuazione_controllo_richiesta_id = atc.id
		and mp.data_cancellazione is null
		and p2.data_cancellazione is null
		) -- CONTRIBUTO EROGATO A SALDO / il contributo ottenuto effettivamente dal beneficiario
	)
),
NULL,
man.data_mandato,
man.data_mandato,
NULL,
NULL
from 
attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join istruttorie_richieste ist on ist.richiesta_id = ric.id
join pagamenti pag on pag.attuazione_controllo_richiesta_id = atc.id
join mandati_pagamenti man on man.id = pag.mandato_pagamento_id
where 1=1
and ric.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5)
and pag.modalita_pagamento_id in (3, 4) -- SALDO FINALE oppure UNICA SOLUZIONE
and (ist.costo_ammesso 
	> 
	(select sum(importo_approvato) 
	from giustificativi_pagamenti gp 
	join pagamenti p on gp.pagamento_id = p.id 
	where 
	p.attuazione_controllo_richiesta_id = atc.id
	and p.data_cancellazione is null)
) -- DEVONO ESSERE AVANZATI DEI SOLDI RISPETTO A QUANTO AMMESSO
; 


-- ECONOMIE UE
INSERT INTO economie (richiesta_id, fondo_id, importo, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
atc.richiesta_id,
(select id from tc33_fonte_finanziaria where cod_fondo = 'ERDF'),
round(
	(select ist.contributo_ammesso -- CONTRIBUTO CONCESSO / il contributo massimo ottenibile dal beneficiario
		- 
		(select sum(importo_pagato) 
			from mandati_pagamenti mp 
			join pagamenti p2 on mp.id = p2.mandato_pagamento_id 
			where 
			p2.attuazione_controllo_richiesta_id = atc.id
			and mp.data_cancellazione is null
			and p2.data_cancellazione is null
		) -- CONTRIBUTO EROGATO A SALDO / il contributo ottenuto effettivamente dal beneficiario
	) * 0.5, 2),
NULL,
man.data_mandato,
man.data_mandato,
NULL,
NULL
from 
attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join istruttorie_richieste ist on ist.richiesta_id = ric.id
join pagamenti pag on pag.attuazione_controllo_richiesta_id = atc.id
join mandati_pagamenti man on man.id = pag.mandato_pagamento_id
where 1=1
and ric.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5)
and pag.modalita_pagamento_id in (3, 4) -- SALDO FINALE oppure UNICA SOLUZIONE
and (ist.costo_ammesso 
	> 
	(select sum(importo_approvato) 
	from giustificativi_pagamenti gp 
	join pagamenti p on gp.pagamento_id = p.id 
	where 
	p.attuazione_controllo_richiesta_id = atc.id
	and p.data_cancellazione is null)
) -- DEVONO ESSERE AVANZATI DEI SOLDI RISPETTO A QUANTO AMMESSO
; 

-- ECONOMIE STATO
INSERT INTO economie (richiesta_id, fondo_id, importo, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
atc.richiesta_id,
(select id from tc33_fonte_finanziaria where cod_fondo = 'FDR'),
-- IMPORTO PER UE
round(
	(select ist.contributo_ammesso -- CONTRIBUTO CONCESSO / il contributo massimo ottenibile dal beneficiario
		- 
		(select sum(importo_pagato) 
			from mandati_pagamenti mp 
			join pagamenti p2 on mp.id = p2.mandato_pagamento_id 
			where 
			p2.attuazione_controllo_richiesta_id = atc.id
			and mp.data_cancellazione is null
			and p2.data_cancellazione is null
		) -- CONTRIBUTO EROGATO A SALDO / il contributo ottenuto effettivamente dal beneficiario
	) * 0.35, 2),
NULL,
man.data_mandato,
man.data_mandato,
NULL,
NULL
from 
attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join istruttorie_richieste ist on ist.richiesta_id = ric.id
join pagamenti pag on pag.attuazione_controllo_richiesta_id = atc.id
join mandati_pagamenti man on man.id = pag.mandato_pagamento_id
where 1=1
and ric.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5)
and pag.modalita_pagamento_id in (3, 4) -- SALDO FINALE oppure UNICA SOLUZIONE
and (ist.costo_ammesso 
	> 
	(select sum(importo_approvato) 
	from giustificativi_pagamenti gp 
	join pagamenti p on gp.pagamento_id = p.id 
	where 
	p.attuazione_controllo_richiesta_id = atc.id
	and p.data_cancellazione is null)
) -- DEVONO ESSERE AVANZATI DEI SOLDI RISPETTO A QUANTO AMMESSO
; 


-- ECONOMIE REGIONE
INSERT INTO economie (richiesta_id, fondo_id, importo, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
atc.richiesta_id,
(select id from tc33_fonte_finanziaria where cod_fondo = 'FPREG'),
-- IMPORTO PER REGIONE
(select ist.contributo_ammesso -- CONTRIBUTO CONCESSO / il contributo massimo ottenibile dal beneficiario
		- 
		(select sum(importo_pagato) 
			from mandati_pagamenti mp 
			join pagamenti p2 on mp.id = p2.mandato_pagamento_id 
			where 
			p2.attuazione_controllo_richiesta_id = atc.id
			and mp.data_cancellazione is null
			and p2.data_cancellazione is null
		)) - 
round(
	(select ist.contributo_ammesso
		- 
		(select sum(importo_pagato) 
			from mandati_pagamenti mp 
			join pagamenti p2 on mp.id = p2.mandato_pagamento_id 
			where 
			p2.attuazione_controllo_richiesta_id = atc.id
			and mp.data_cancellazione is null
			and p2.data_cancellazione is null
		) -- CONTRIBUTO EROGATO A SALDO / il contributo ottenuto effettivamente dal beneficiario
	) * 0.5, 2) -
round(
	(select ist.contributo_ammesso
		- 
		(select sum(importo_pagato) 
			from mandati_pagamenti mp 
			join pagamenti p2 on mp.id = p2.mandato_pagamento_id 
			where 
			p2.attuazione_controllo_richiesta_id = atc.id
			and mp.data_cancellazione is null
			and p2.data_cancellazione is null
		) -- CONTRIBUTO EROGATO A SALDO / il contributo ottenuto effettivamente dal beneficiario
	) * 0.35, 2),
NULL,
man.data_mandato,
man.data_mandato,
NULL,
NULL
from 
attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join istruttorie_richieste ist on ist.richiesta_id = ric.id
join pagamenti pag on pag.attuazione_controllo_richiesta_id = atc.id
join mandati_pagamenti man on man.id = pag.mandato_pagamento_id
where 1=1
and ric.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5)
and pag.modalita_pagamento_id in (3, 4) -- SALDO FINALE oppure UNICA SOLUZIONE
and (ist.costo_ammesso 
	> 
	(select sum(importo_approvato) 
	from giustificativi_pagamenti gp 
	join pagamenti p on gp.pagamento_id = p.id 
	where 
	p.attuazione_controllo_richiesta_id = atc.id
	and p.data_cancellazione is null)
) -- DEVONO ESSERE AVANZATI DEI SOLDI RISPETTO A QUANTO AMMESSO
; 

-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
-- ECONOMIE PRIVATI - *** CASO 2 ***
-- Non dovrebbe esserci nulla da fare: le economie non vengono toccate in questo caso: rivedere i finanziamenti.


-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
-- ECONOMIE PUBBLICI - *** CASO 1 ***
-- Per ogni richiesta 4 righe

-- economia "Altro pubblico"
INSERT INTO economie (richiesta_id, fondo_id, importo, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
atc.richiesta_id,
(select id from tc33_fonte_finanziaria where cod_fondo = 'ALTRO_NAZ'),
(ist.costo_ammesso -- COSTO TOTALE DEL PROGETTO / il costo ammesso, quanto poteva spendere il beneficiario al massimo 
- -- MENO
(select sum(importo_approvato) 
	from giustificativi_pagamenti gp 
	join pagamenti p1 on gp.pagamento_id = p1.id 
	where 
	p1.attuazione_controllo_richiesta_id = atc.id
	and gp.data_cancellazione is null
	and p1.data_cancellazione is null
) -- CONTRIBUTO CONCESSO / quanto ha speso il beneficiario, la somma dei pagamenti
- -- MENO	
	-- QUESTA SOTTOQUERY RESTITUISCE LA SOMMA DELLE ECONOMIA UE/STATO/REGIONE
	(	select ist.contributo_ammesso -- CONTRIBUTO CONCESSO / il contributo massimo ottenibile dal beneficiario
		- 
		(select sum(importo_pagato) 
		from mandati_pagamenti mp 
		join pagamenti p2 on mp.id = p2.mandato_pagamento_id 
		where 
		p2.attuazione_controllo_richiesta_id = atc.id
		and mp.data_cancellazione is null
		and p2.data_cancellazione is null
		) -- CONTRIBUTO EROGATO A SALDO / il contributo ottenuto effettivamente dal beneficiario
	)
),
NULL,
vcp.data_validazione,
vcp.data_validazione,
NULL,
NULL
from 
attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join istruttorie_richieste ist on ist.richiesta_id = ric.id
join pagamenti pag on pag.attuazione_controllo_richiesta_id = atc.id
join valutazioni_checklist_pagamenti vcp on vcp.pagamento_id = pag.id
where 1=1
and ric.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5)
and pag.modalita_pagamento_id in (3, 4) -- SALDO FINALE oppure UNICA SOLUZIONE
and vcp.data_validazione is not null
and vcp.validata = 1
and (ist.costo_ammesso 
	> 
	(select sum(importo_approvato) 
	from giustificativi_pagamenti gp 
	join pagamenti p on gp.pagamento_id = p.id 
	where 
	p.attuazione_controllo_richiesta_id = atc.id
	and p.data_cancellazione is null)
) -- DEVONO ESSERE AVANZATI DEI SOLDI RISPETTO A QUANTO AMMESSO
; 

-- Economia UE
INSERT INTO economie (richiesta_id, fondo_id, importo, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
atc.richiesta_id,
(select id from tc33_fonte_finanziaria where cod_fondo = 'ERDF'),
round (   -- QUESTA SOTTOQUERY RESTITUISCE LA SOMMA DELLE ECONOMIA UE/STATO/REGIONE
	(	select ist.contributo_ammesso -- CONTRIBUTO CONCESSO / il contributo massimo ottenibile dal beneficiario
		- 
		(select sum(importo_pagato) 
		from mandati_pagamenti mp 
		join pagamenti p2 on mp.id = p2.mandato_pagamento_id 
		where 
		p2.attuazione_controllo_richiesta_id = atc.id
		and mp.data_cancellazione is null
		and p2.data_cancellazione is null
		) -- CONTRIBUTO EROGATO A SALDO / il contributo ottenuto effettivamente dal beneficiario
	) * 0.5, 2),
NULL,
vcp.data_validazione,
vcp.data_validazione,
NULL,
NULL
from 
attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join istruttorie_richieste ist on ist.richiesta_id = ric.id
join pagamenti pag on pag.attuazione_controllo_richiesta_id = atc.id
join valutazioni_checklist_pagamenti vcp on vcp.pagamento_id = pag.id
where 1=1
and ric.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5)
and pag.modalita_pagamento_id in (3, 4) -- SALDO FINALE oppure UNICA SOLUZIONE
and vcp.data_validazione is not null
and vcp.validata = 1
and (ist.costo_ammesso 
	> 
	(select sum(importo_approvato) 
	from giustificativi_pagamenti gp 
	join pagamenti p on gp.pagamento_id = p.id 
	where 
	p.attuazione_controllo_richiesta_id = atc.id
	and p.data_cancellazione is null)
) -- DEVONO ESSERE AVANZATI DEI SOLDI RISPETTO A QUANTO AMMESSO
; 

-- Economia STATO
INSERT INTO economie (richiesta_id, fondo_id, importo, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
atc.richiesta_id,
(select id from tc33_fonte_finanziaria where cod_fondo = 'FDR'),
round (   -- QUESTA SOTTOQUERY RESTITUISCE LA SOMMA DELLE ECONOMIA UE/STATO/REGIONE
	(	select ist.contributo_ammesso -- CONTRIBUTO CONCESSO / il contributo massimo ottenibile dal beneficiario
		- 
		(select sum(importo_pagato) 
		from mandati_pagamenti mp 
		join pagamenti p2 on mp.id = p2.mandato_pagamento_id 
		where 
		p2.attuazione_controllo_richiesta_id = atc.id
		and mp.data_cancellazione is null
		and p2.data_cancellazione is null
		) -- CONTRIBUTO EROGATO A SALDO / il contributo ottenuto effettivamente dal beneficiario
	) * 0.35, 2),
NULL,
vcp.data_validazione,
vcp.data_validazione,
NULL,
NULL
from 
attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join istruttorie_richieste ist on ist.richiesta_id = ric.id
join pagamenti pag on pag.attuazione_controllo_richiesta_id = atc.id
join valutazioni_checklist_pagamenti vcp on vcp.pagamento_id = pag.id
where 1=1
and ric.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5)
and pag.modalita_pagamento_id in (3, 4) -- SALDO FINALE oppure UNICA SOLUZIONE
and vcp.data_validazione is not null
and vcp.validata = 1
and (ist.costo_ammesso 
	> 
	(select sum(importo_approvato) 
	from giustificativi_pagamenti gp 
	join pagamenti p on gp.pagamento_id = p.id 
	where 
	p.attuazione_controllo_richiesta_id = atc.id
	and p.data_cancellazione is null)
) -- DEVONO ESSERE AVANZATI DEI SOLDI RISPETTO A QUANTO AMMESSO
; 


-- Economia REGIONE
INSERT INTO economie (richiesta_id, fondo_id, importo, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da)
select 
atc.richiesta_id,
(select id from tc33_fonte_finanziaria where cod_fondo = 'FPREG'),
-- IMPORTO: TOTALE ECONOMIA QUOTE - ECONOMIA UE(50%) - ECONOMIA STATO(35%)
(
    (	select ist.contributo_ammesso -- CONTRIBUTO CONCESSO / il contributo massimo ottenibile dal beneficiario
		- 
		(select sum(importo_pagato) 
		from mandati_pagamenti mp 
		join pagamenti p2 on mp.id = p2.mandato_pagamento_id 
		where 
		p2.attuazione_controllo_richiesta_id = atc.id
		and mp.data_cancellazione is null
		and p2.data_cancellazione is null
		) -- CONTRIBUTO EROGATO A SALDO / il contributo ottenuto effettivamente dal beneficiario
	) 
    - 
    round (   -- QUESTA SOTTOQUERY RESTITUISCE LA SOMMA DELLE ECONOMIA UE/STATO/REGIONE
	(	select ist.contributo_ammesso -- CONTRIBUTO CONCESSO / il contributo massimo ottenibile dal beneficiario
		- 
		(select sum(importo_pagato) 
		from mandati_pagamenti mp 
		join pagamenti p2 on mp.id = p2.mandato_pagamento_id 
		where 
		p2.attuazione_controllo_richiesta_id = atc.id
		and mp.data_cancellazione is null
		and p2.data_cancellazione is null
		) -- CONTRIBUTO EROGATO A SALDO / il contributo ottenuto effettivamente dal beneficiario
	) * 0.5, 2)
    -
    round (   -- QUESTA SOTTOQUERY RESTITUISCE LA SOMMA DELLE ECONOMIA UE/STATO/REGIONE
	(	select ist.contributo_ammesso -- CONTRIBUTO CONCESSO / il contributo massimo ottenibile dal beneficiario
		- 
		(select sum(importo_pagato) 
		from mandati_pagamenti mp 
		join pagamenti p2 on mp.id = p2.mandato_pagamento_id 
		where 
		p2.attuazione_controllo_richiesta_id = atc.id
		and mp.data_cancellazione is null
		and p2.data_cancellazione is null
		) -- CONTRIBUTO EROGATO A SALDO / il contributo ottenuto effettivamente dal beneficiario
	) * 0.35, 2)),
NULL,
vcp.data_validazione,
vcp.data_validazione,
NULL,
NULL
from 
attuazione_controllo_richieste atc 
join richieste ric on ric.id = atc.richiesta_id
join istruttorie_richieste ist on ist.richiesta_id = ric.id
join pagamenti pag on pag.attuazione_controllo_richiesta_id = atc.id
join valutazioni_checklist_pagamenti vcp on vcp.pagamento_id = pag.id
where 1=1
and ric.data_cancellazione is NULL
and atc.data_cancellazione is NULL
and ric.stato_id in(4, 5)
and pag.modalita_pagamento_id in (3, 4) -- SALDO FINALE oppure UNICA SOLUZIONE
and vcp.data_validazione is not null
and vcp.validata = 1
and (ist.costo_ammesso 
	> 
	(select sum(importo_approvato) 
	from giustificativi_pagamenti gp 
	join pagamenti p on gp.pagamento_id = p.id 
	where 
	p.attuazione_controllo_richiesta_id = atc.id
	and p.data_cancellazione is null)
) -- DEVONO ESSERE AVANZATI DEI SOLDI RISPETTO A QUANTO AMMESSO
; 