select
coalesce(tc4.`cod_programma` , '') as programma,
assi.descrizione as asse,
group_concat(azioni.codice SEPARATOR ' - ') as codici_azioni,
group_concat(azioni.descrizione SEPARATOR ' - ') as descrizioni_azioni,
atti.numero,
po.titolo,
'NON Presente negli atti' as anno_atto,
case when i.`esito_id` is null then 'non ancora valutato' else 
	case i.`esito_id` when 2 then 'non ammesso'
		when 3 then 'sospeso'
		else case concessione when 0 then 'non finanziato'
			when 1 then case atti_revoche.tipo_id
				when 1 then 'Revoca totale'
			--	when 2 then 
				when 3 then 'Rinucia'
				else case when mandato.id is null then 'Finanziato'
					else 'Concluso'
				end
			END
		end
	end
end as stato_intervento,
r.id as id_richiesta,
coalesce(concat(pr.registro_pg, '/', pr.anno_pg, '/', pr.num_pg),r.id) as protocollo,
coalesce(i.codice_cup, '') as CUP,
coalesce(DATE_FORMAT(i.`data_avvio_progetto`, '%d/%m/%Y'), '') as data_inizio_prevista, 
coalesce(DATE_FORMAT(i.`data_termine_progetto`, '%d/%m/%Y'), '') as data_fine_prevista,
coalesce(DATE_FORMAT(saldo.data_invio, '%d/%m/%Y'), '') as data_ricezione_rendicontazione,
coalesce(DATE_FORMAT(mandato.`data_mandato`, '%d/%m/%Y'), '') as data_erogazione,

replace(replace(r.`titolo`, '\n', ' '), '\r', '') as titolo,
replace(replace(r.`abstract`, '\n', ' '), '\r', '') as abstract,
coalesce(sp.`descrizione`, '') as ambito_specializzazione,
coalesce(ot.descrizione, '') as orientamento_tematico,
cast( brevetti.`val_programmato` as UNSIGNED) as brevetti_previsti,
cast( brevetti.valore_realizzato as UNSIGNED) as brevetti_effettivi,

format(coalesce(i.costo_ammesso),2, 'it_IT') as investimento_approvato, -- variazioni.costo_ammesso,
format(coalesce(i.contributo_ammesso),2, 'it_IT') as contributo_ammesso,
format(sum(coalesce(pagamenti.importo_pagamento, 0)),2, 'it_IT' ) as investimento_effettivo,
format(sum(coalesce(mandati.`importo_pagato`, 0)),2, 'it_IT' ) as contributo_erogato,

cast( ricercatori.`val_programmato` as UNSIGNED) as ricercatori_previsti,
cast( ricercatori.valore_realizzato as UNSIGNED) as ricercatori_effettivi

from richieste as r

join richieste_protocollo as pr
on pr.richiesta_id = r.id 
and pr.data_cancellazione is null
and pr.tipo = 'FINANZIAMENTO'

join procedure_operative as po
on po.id = r.procedura_id

join `istruttorie_richieste` as i
on i.richiesta_id = r.id
and i.data_cancellazione is null

join proponenti as mandatario
on mandatario.richiesta_id = r.id
and mandatario.mandatario = 1
and mandatario.data_cancellazione is null

join assi 
on assi.id = po.`asse_id`
and assi.id in (1,3)

join atti
on atti.id =po.`atto_id`

join `procedure_operative_azioni` as poa
on poa.procedura_id = po.id

join azioni
on azioni.id = poa.`azione_id`

left join attuazione_controllo_richieste as atc 
on atc.richiesta_id = r.id
and atc.data_cancellazione is null

left join programmi_procedure_operative as ppo
on ppo.`procedura_id` = po.id
and ppo.data_cancellazione is null

left join `tc4_programma` as tc4
on tc4.id = ppo.`programma_id`


left join `priorita_proponenti` as pp
on pp.`proponente_id` = mandatario.id

left join orientamenti_tematici as ot
on ot.id = pp.`orientamento_tematico_id`

left join `sistemi_produttivi` as sp
on sp.id = ot.`sistemaProduttivo_id`

left join pagamenti as saldo
on saldo.`attuazione_controllo_richiesta_id` = atc.id
and saldo.data_cancellazione is null
and saldo.stato_id = 10
and saldo.`modalita_pagamento_id` in (3, 4)

left join mandati_pagamenti as mandato
on mandato.id = saldo.`mandato_pagamento_id`
and mandato.data_cancellazione is null
left join revoche
on revoche.`attuazione_controllo_richiesta_id` = atc.id
and revoche.`data_cancellazione` is null
left join atti_revoche
on atti_revoche.id = revoche.`atto_revoca_id`

left join `indicatori_output` as brevetti
on brevetti.`richiesta_id`=r.id
and brevetti.data_cancellazione is null
and brevetti.indicatore_id = 266

left join variazioni_richieste as variazioni
on variazioni.`attuazione_controllo_richiesta_id` = atc.id
and variazioni.data_cancellazione is null
and variazioni.`esito_istruttoria` = 1
and variazioni.`data_invio`  IN (
	(select MAX(v1.data_invio) 
	from variazioni_richieste v1
	where v1.attuazione_controllo_richiesta_id = atc.id
	and v1.data_cancellazione is null
	and v1.esito_istruttoria = 1	) 
)

left join `indicatori_output` as ricercatori
on ricercatori.`richiesta_id`=r.id
and ricercatori.data_cancellazione is null
and ricercatori.indicatore_id = 28

left join pagamenti
on pagamenti.`attuazione_controllo_richiesta_id` = atc.id
and pagamenti.data_cancellazione is null
and pagamenti.mandato_pagamento_id is not null

left join `mandati_pagamenti` as mandati
on mandati.id = pagamenti.mandato_pagamento_id
and mandati.data_cancellazione is null
group by r.id;

select 
coalesce(concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg), r.id) as protocollo,
c.denominazione as comune,
p.denominazione as provincia,
c.codice_completo as codice_istat

 from `sedi_operative` as so
join proponenti as pro
on pro.id = so.`proponente_id`
and pro.data_cancellazione is null
join richieste as r
on r.id = pro.`richiesta_id`
join procedure_operative as po
on po.id = r.procedura_id
and po.asse_id in (1,3)
join richieste_protocollo as rp
on rp.id = r.id and rp.tipo = 'FINANZIAMENTO'
and rp.data_cancellazione is null
join sedi
on sedi.id = so.sede_id
and sedi.data_cancellazione is null
join indirizzi
on indirizzi.id = sedi.`indirizzo_id`

join `geo_comuni` as c
on c.id = indirizzi.`comune_id`
join `geo_province` as p
on p.id = c.`provincia_id`

where so.data_cancellazione is null;

select 
coalesce(concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg), r.id) as protocollo,
s.denominazione,
s.codice_fiscale,
a.codice as codice_ateco,
a.`descrizione` as descrizione_ateco,
case pro.mandatario when 1 then 'capofila' else 'partner' end as ruolo_proponente

 from  proponenti as pro
join richieste as r
on r.id = pro.`richiesta_id`
join procedure_operative as po
on po.id = r.procedura_id
and po.asse_id in (1,3)
join richieste_protocollo as rp
on rp.id = r.id and rp.tipo = 'FINANZIAMENTO'
and rp.data_cancellazione is null
join soggetti as s
on s.id = pro.`soggetto_id`
left join `ateco2007` a
on a.id = s.`codice_ateco_id`

where pro.data_cancellazione is null