select 
atc.richiesta_id,
concat(pro.`registro_pg`, '/', pro.`anno_pg`, '/', pro.`num_pg`) as protocollo,
ar.`numero` as numero_atto_revoca,
ar.`descrizione` as atto_revoca,
tr.`descrizione` as tipo_revoca,
m.descrizione as motivazione_revoca,
r.`contributo` as contributo_revoca,
r.`con_ritiro`,
r.`con_recupero`,
r.`invio_conti`,
r.`taglio_ada`


 from revoche as r
join attuazione_controllo_richieste as atc
on r.`attuazione_controllo_richiesta_id`  = atc.id
join richieste_protocollo as pro
on pro.richiesta_id = atc.richiesta_id and pro.tipo = 'FINANZIAMENTO' and pro.data_cancellazione is null
join `atti_revoche` as ar
on ar.id = r.`atto_revoca_id`
join `tipi_revoche` as tr
on tr.id = ar.`tipo_id`
join `tipi_motivazione_revoche` as m
on m.id = ar.`tipo_motivazione_id`

where r.data_cancellazione is null