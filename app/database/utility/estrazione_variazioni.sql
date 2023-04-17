select 
concat(pro.`registro_pg`, '/', pro.`anno_pg`, '/', pro.`num_pg`) as 'protocollo richiesta',
concat(pro2.`registro_pg`, '/', pro2.`anno_pg`, '/', pro2.`num_pg`) as 'protocollo variazione',
DATE_FORMAT(var.data_invio,"%d/%m/%Y") as 'data invio variazione',
sogg.denominazione as beneficiario,
ist.costo_ammesso as "importo ammesso",
var.costo_ammesso  as "importo ammesso variazione",
CASE 
when var.esito_istruttoria is null then 'non gestita'
when var.esito_istruttoria = 1 then 'ammessa'
when var.esito_istruttoria = 0 then 'non ammessa'
END as 'esito'
from variazioni_richieste as var
join attuazione_controllo_richieste atc on var.`attuazione_controllo_richiesta_id`  = atc.id
join richieste r on r.id = atc.richiesta_id
join richieste_protocollo pro on r.id = pro.richiesta_id and pro.tipo = 'FINANZIAMENTO' and pro.data_cancellazione is null
join richieste_protocollo pro2 on var.id = pro2.variazione_id and pro2.tipo = 'VARIAZIONE' and pro2.data_cancellazione is null
join proponenti prop on prop.richiesta_id = r.id
join soggetti sogg on sogg.id = prop.soggetto_id
join istruttorie_richieste ist on ist.richiesta_id = r.id
where r.data_cancellazione is null and r.procedura_id = 69 and var.data_cancellazione is null and var.`data_invio` is not nullprocedura_id = 69 and var.data_cancellazione is null and var.`data_invio` is not null