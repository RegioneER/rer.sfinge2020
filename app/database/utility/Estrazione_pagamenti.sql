select r.id,
	concat(proto_richiesta.`registro_pg`, '/', proto_richiesta.`anno_pg`, '/', proto_richiesta.`num_pg`) as protocollo,
	REPLACE(REPLACE(coalesce(r.titolo, ''), '\r', ''), '\n', '') as titolo_progetto,
	modalita.`descrizione` as tipo_pagamento,
	stato_pag.descrizione as stato_pagamento,
	p.id as id_pagamento,
	p.importo_rendicontato,
	p.importo_rendicontato_ammesso,
	mandati.`numero_mandato`,
	date_format(mandati.`data_mandato`, '%d/%m/%Y') as data_mandato,
	mandati.`importo_pagato`, -- importo_pagato
	mandati.`quota_fesr`,-- quota_fesr
	mandati.`quota_regione`,-- quota_regione
	mandati.`quota_stato`,-- quota_stato
	procedura.id as procedura_id,
	procedura.`titolo` as titolo_procedura,
	coalesce(date_format(p.`data_istruttoria`, '%d/%m/%Y'), '') as data_istruttoria,	-- data_istruttoria
	coalesce(eips.descrizione ,'') as stato_istruttoria,
	CASE WHEN p.data_conclusione_progetto IS NULL THEN '' ELSE DATE_FORMAT(p.data_conclusione_progetto, '%d/%m/%Y') END as data_conclusione_progetto, -- data_conclusione_progetto
	coalesce(date_format(p.`data_fine_rendicontazione`, '%d/%m/%Y'), '') as data_fine_rendicontazione, -- data_fine_rendicontazione
	coalesce(date_format(p.`data_invio`, '%d/%m/%Y'), '') as data_invio, -- data_invio
	coalesce(i.tipologia_soggetto, '') as tipologia_soggetto,
	coalesce(date_format(vcp.`data_validazione`, '%d/%m/%Y'), '') as data_validazione_checklist,
	coalesce(rp.`codice`, '') as codice_pagamento,
	coalesce(rp.`importo`, '') as importo_pag_monitoraggio,
	coalesce(p.`importo_certificato`, '') as importo_certificato,
	coalesce(certificazioni.`anno`, '') as anno_certificazione,
	coalesce(certificazioni.numero, '') as numero_certificazione
from pagamenti as p

join `modalita_pagamento` as modalita
on modalita.id = p.`modalita_pagamento_id`

join stati as stato_pag
on stato_pag.id = p.`stato_id`

left join `mandati_pagamenti` as mandati
on mandati.id = p.`mandato_pagamento_id`
and mandati.data_cancellazione is null

join `attuazione_controllo_richieste` as atc
on atc.id = p.`attuazione_controllo_richiesta_id` 
and atc.data_cancellazione is null

join richieste as r
on r.id = atc.richiesta_id 
and r.data_cancellazione is null

join `procedure_operative` as procedura
on procedura.id = r.procedura_id

join `istruttorie_richieste` as i
on i.`richiesta_id` = r.id
and i.data_cancellazione is null

join `richieste_protocollo` as proto_richiesta
on proto_richiesta.`richiesta_id` = r.id 
and proto_richiesta.tipo = 'FINANZIAMENTO' 
and proto_richiesta.data_cancellazione is null

join esiti_istruttoria_pagamento as eip
on eip.pagamento_id = p.id
and eip.data_cancellazione is null
and eip.stato_id in(37,38)

join stati as eips
on eips.id = eip.stato_id

left join valutazioni_checklist_pagamenti as vcp
on vcp.pagamento_id = p.id
and vcp.data_cancellazione is null

left join checklist_pagamenti as cp
on cp.id = vcp.checklist_id

left join richieste_pagamenti as rp
on rp.`pagamento_id` = p.id
and rp.data_cancellazione is null

left join `certificazioni_pagamenti` as cert_pag
on cert_pag.pagamento_id = p.id

left join certificazioni
on certificazioni.id = cert_pag.certificazione_id

where p.data_cancellazione is null and stato_pag.id in (9,10)
and coalesce(cp.tipologia, '') in ('', 'PRINCIPALE')
and p.esito_istruttoria = 1
;


################### versione lite per controllo invio ###################
select r.id,
	concat(proto_richiesta.`registro_pg`, '/', proto_richiesta.`anno_pg`, '/', proto_richiesta.`num_pg`) as 'protocollo richiesta',
	REPLACE(REPLACE(coalesce(r.titolo, ''), '\r', ''), '\n', '') as titolo_progetto,
	modalita.`descrizione` as tipo_pagamento,
	p.id as id_pagamento,
	p.importo_rendicontato,
	p.importo_rendicontato_ammesso,
	stato_pag.descrizione
	
from pagamenti as p

join `modalita_pagamento` as modalita
on modalita.id = p.`modalita_pagamento_id`

join `attuazione_controllo_richieste` as atc
on atc.id = p.`attuazione_controllo_richiesta_id` 
and atc.data_cancellazione is null

join richieste as r
on r.id = atc.richiesta_id 
and r.data_cancellazione is null

join `procedure_operative` as procedura
on procedura.id = r.procedura_id

join `richieste_protocollo` as proto_richiesta
on proto_richiesta.`richiesta_id` = r.id 
and proto_richiesta.tipo = 'FINANZIAMENTO' 
and proto_richiesta.data_cancellazione is null

join stati as stato_pag
on stato_pag.id = p.`stato_id`

where p.data_cancellazione is null and procedura.id = @id_procedure

;
################### versione lite per controllo invio ###################

################### versione CHk 773 per controllo invio ###################
select r.id,
	p.id as id_pagamento,
	p.importo_rendicontato,
	p.importo_rendicontato_ammesso,
	REPLACE(REPLACE(coalesce(valEl.valore, ''), '\r', ''), '\n', '') as note,
	
from pagamenti as p

join `modalita_pagamento` as modalita
on modalita.id = p.`modalita_pagamento_id`

join `attuazione_controllo_richieste` as atc
on atc.id = p.`attuazione_controllo_richiesta_id` 
and atc.data_cancellazione is null

join richieste as r
on r.id = atc.richiesta_id 
and r.data_cancellazione is null

join proponenti as prop
on prop.richiesta_id = r.id 
and r.data_cancellazione is null

join valutazioni_checklist_pagamenti as val
on val.pagamento_id = p.id 
and val.data_cancellazione is null

join valutazioni_elementi_checklist_pagamenti as valEl
on valEl.valutazione_checklist_id = val.id 
and val.data_cancellazione is null and elemento_id = 255

join `procedure_operative` as procedura
on procedura.id = r.procedura_id

join `richieste_protocollo` as proto_richiesta
on proto_richiesta.`richiesta_id` = r.id 
and proto_richiesta.tipo = 'FINANZIAMENTO' 
and proto_richiesta.data_cancellazione is null

join stati as stato_pag
on stato_pag.id = p.`stato_id`

where p.data_cancellazione is null and procedura.id = @id_procedure and modalita.id = 3
################### versione CHk 773 per controllo invio ###################


################## controllo progetti per verifica fesr di rosa e stefano ###############
### foglio 1 pagati #####

select r.id,
	concat(proto_richiesta.`registro_pg`, '/', proto_richiesta.`anno_pg`, '/', proto_richiesta.`num_pg`) as protocollo,
	REPLACE(REPLACE(coalesce(r.titolo, ''), '\r', ''), '\n', '') as titolo_progetto,
	modalita.`descrizione` as tipo_pagamento,
	stato_pag.descrizione as stato_pagamento,
	p.id as id_pagamento,
	p.importo_rendicontato,
	p.importo_rendicontato_ammesso,
	mandati.`numero_mandato`,
	date_format(mandati.`data_mandato`, '%d/%m/%Y') as data_mandato,
	mandati.`importo_pagato`, -- importo_pagato
	mandati.`quota_fesr`,-- quota_fesr
	mandati.`quota_regione`,-- quota_regione
	mandati.`quota_stato`,-- quota_stato
	procedura.id as procedura_id,
	procedura.`titolo` as titolo_procedura,
	asse.`codice` as asse_s,
	CASE WHEN p.data_conclusione_progetto IS NULL THEN '' ELSE DATE_FORMAT(p.data_conclusione_progetto, '%d/%m/%Y') END as data_conclusione_progetto, -- data_conclusione_progetto
	coalesce(date_format(p.`data_fine_rendicontazione`, '%d/%m/%Y'), '') as data_fine_rendicontazione, -- data_fine_rendicontazione
	coalesce(date_format(p.`data_invio`, '%d/%m/%Y'), '') as data_invio, -- data_invio
	coalesce(i.tipologia_soggetto, '') as tipologia_soggetto,
	coalesce(date_format(vcp.`data_validazione`, '%d/%m/%Y'), '') as data_validazione_checklist,
	coalesce(rp.`codice`, '') as codice_pagamento,
	coalesce(rp.`importo`, '') as importo_pag_monitoraggio,
	coalesce(p.`importo_certificato`, '') as importo_certificato,
	coalesce(certificazioni.`anno`, '') as anno_certificazione,
	coalesce(certificazioni.numero, '') as numero_certificazione
from pagamenti as p

join `modalita_pagamento` as modalita
on modalita.id = p.`modalita_pagamento_id`

join stati as stato_pag
on stato_pag.id = p.`stato_id`

left join `mandati_pagamenti` as mandati
on mandati.id = p.`mandato_pagamento_id`
and mandati.data_cancellazione is null

join `attuazione_controllo_richieste` as atc
on atc.id = p.`attuazione_controllo_richiesta_id` 
and atc.data_cancellazione is null

join richieste as r
on r.id = atc.richiesta_id 
and r.data_cancellazione is null

join `procedure_operative` as procedura
on procedura.id = r.procedura_id

join assi as asse
on asse.id = procedura.asse_id

join `istruttorie_richieste` as i
on i.`richiesta_id` = r.id
and i.data_cancellazione is null

join `richieste_protocollo` as proto_richiesta
on proto_richiesta.`richiesta_id` = r.id 
and proto_richiesta.tipo = 'FINANZIAMENTO' 
and proto_richiesta.data_cancellazione is null

left join valutazioni_checklist_pagamenti as vcp
on vcp.pagamento_id = p.id
and vcp.data_cancellazione is null

left join checklist_pagamenti as cp
on cp.id = vcp.checklist_id

left join richieste_pagamenti as rp
on rp.`pagamento_id` = p.id
and rp.data_cancellazione is null

left join `certificazioni_pagamenti` as cert_pag
on cert_pag.pagamento_id = p.id

left join certificazioni
on certificazioni.id = cert_pag.certificazione_id

where p.data_cancellazione is null and stato_pag.id in (9,10)
and coalesce(cp.tipologia, '') in ('', 'PRINCIPALE')
and p.esito_istruttoria = 1 and asse.id <> 8
;

### foglio 2 in gestione #####

select r.id,
	concat(proto_richiesta.`registro_pg`, '/', proto_richiesta.`anno_pg`, '/', proto_richiesta.`num_pg`) as 'protocollo richiesta',
	REPLACE(REPLACE(coalesce(r.titolo, ''), '\r', ''), '\n', '') as titolo_progetto,
	asse.codice as asse_s,
	procedura.titolo as procedura,
	ist.contributo_ammesso,
	p.id as id_pagamento,
	p.importo_rendicontato,
	p.importo_rendicontato_ammesso
	
from richieste as r

join `istruttorie_richieste` ist
on ist.richiesta_id = r.id

left join `attuazione_controllo_richieste` as atc
on atc.richiesta_id = r.id 
and atc.data_cancellazione is null

left join pagamenti as p
on p.attuazione_controllo_richiesta_id = atc.id 
and p.data_cancellazione is null

left join revoche as rev
on rev.attuazione_controllo_richiesta_id = atc.id 
and rev.data_cancellazione is null

left join atti_revoche as atrev
on rev.atto_revoca_id = atrev.id 
and atrev.data_cancellazione is null

join `procedure_operative` as procedura
on procedura.id = r.procedura_id and procedura.id not in (118,125)

join assi as asse
on asse.id = procedura.asse_id

join `richieste_protocollo` as proto_richiesta
on proto_richiesta.`richiesta_id` = r.id 
and proto_richiesta.tipo = 'FINANZIAMENTO' 
and proto_richiesta.data_cancellazione is null

where p.data_cancellazione is null and (p.id is null or p.mandato_pagamento_id is null) and ist.esito_id = 1 and (rev.id is null or atrev.tipo_id <> 1)

############################### fine ####################################