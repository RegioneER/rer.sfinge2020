select    
		distinct(po.id),
        po.titolo as titolo_procedura,
        assi.titolo as asse,              
   		(
   			select group_concat(azioni.codice SEPARATOR ', ') from azioni 
   			join procedure_operative_azioni poa on azioni.id = poa.azione_id
   			join procedure_operative po1 on poa.procedura_id = po1.id 
   			where po1.id = po.id
   		) as azioni,
        coalesce(DATE_FORMAT(po.data_pubblicazione, '%d/%m/%Y'),'-') as pubblicazione,
        coalesce(DATE_FORMAT(po.data_ora_fine_presentazione, '%d/%m/%Y'), '-') as scadenza,
        coalesce(( 
       		select count(r.id) from richieste r join procedure_operative po1 on r.procedura_id = po1.id  join istruttorie_richieste i on i.richiesta_id = r.id 
       		where i.`concessione` = 1 and po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null
       	),0) AS finanziati,
       	coalesce((
       		select count(r.id) from richieste r 
			join procedure_operative po1 on r.procedura_id = po1.id  
			join attuazione_controllo_richieste atc on atc.richiesta_id = r.id
			join pagamenti as saldo
        	on saldo.attuazione_controllo_richiesta_id = atc.id and saldo.data_cancellazione is null and saldo.stato_id = 10 and saldo.modalita_pagamento_id in (3, 4)
        	left join mandati_pagamenti as mandato on mandato.id = saldo.mandato_pagamento_id and mandato.data_cancellazione is null 
			where po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null
       	),0)  AS conclusi,
       	coalesce((
       		select count(prop.id) from richieste r 
        	join proponenti prop on prop.richiesta_id = r.id and prop.data_cancellazione is null
        	join procedure_operative po1 on r.procedura_id = po1.id
        	join istruttorie_richieste i on i.richiesta_id = r.id 
        	where po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null and i.concessione = 1 
       	),0) as proponenti,
        coalesce(( 
       		select sum(coalesce(i.costo_ammesso, 0)) from richieste r join procedure_operative po1 on r.procedura_id = po1.id  join istruttorie_richieste i on i.richiesta_id = r.id 
       		where i.concessione = 1 and po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null
       	),0) AS costo,
       	coalesce(( 
       		select sum(coalesce(i.contributo_ammesso, 0)) from richieste r join procedure_operative po1 on r.procedura_id = po1.id  join istruttorie_richieste i on i.richiesta_id = r.id 
       		where i.`concessione` = 1 and po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null
       	),0) AS contributo,
       	coalesce((
       		select sum(mandato.importo_pagato) from richieste r 
			join procedure_operative po1 on r.procedura_id = po1.id  
			join attuazione_controllo_richieste atc on atc.richiesta_id = r.id
			join pagamenti as pag
        	on pag.attuazione_controllo_richiesta_id = atc.id and pag.data_cancellazione is null
        	left join mandati_pagamenti as mandato on mandato.id = pag.mandato_pagamento_id and mandato.data_cancellazione is null 
			where po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null
       	),0) AS erogato,
       	coalesce((
       		select sum(cast( ind.valore_realizzato as UNSIGNED)) from richieste r 
			join procedure_operative po1 on r.procedura_id = po1.id 
        	left join indicatori_output as ind
        	on ind.richiesta_id=r.id
        	and ind.data_cancellazione is null
        	and ind.indicatore_id in (8,28)
        	where po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null
       	),'-') as occupati     	      	
        from procedure_operative as po
        join procedure_operative_azioni as poa on poa.procedura_id = po.id and po.data_cancellazione is null
        
        join assi on assi.id = po.asse_id and assi.id <> 8
        order by po.id
        
        
		