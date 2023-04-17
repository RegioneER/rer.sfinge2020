select 
CONCAT(pro.registro_pg,'/',pro.`anno_pg`,'/', pro.`num_pg`) as 'Codice progetto', 
atti.numero as 'Codice bando', 
sogg.denominazione, 
IF(rich.femminile<1, "NO", "SI") as 'femminile', 
IF(rich.giovanile<1, "NO", "SI") as 'giovanile'
from richieste rich
join proponenti prop on prop.richiesta_id = rich.id
join soggetti sogg on sogg.id = prop.soggetto_id
join procedure_operative as proc on proc.id = rich.procedura_id
join atti on atti.id = proc.atto_id
join richieste_protocollo as pro on pro.richiesta_id = rich.id and pro.data_cancellazione is null and pro.tipo = 'FINANZIAMENTO'
where rich.procedura_id in (6,3) and rich.stato_id = 5 and prop.mandatario = 1
UNION 
select 
CONCAT(pro.registro_pg,'/',pro.`anno_pg`,'/', pro.`num_pg`) as 'Codice progetto', 
atti.numero as 'Codice bando', 
sogg.denominazione, 
IF(prop.impresa_femminile<1, "NO", "SI") as 'femminile', 
IF(prop.impresa_giovanile<1, "NO", "SI") as 'giovanile'
from richieste rich
join proponenti prop on prop.richiesta_id = rich.id
join soggetti sogg on sogg.id = prop.soggetto_id
join procedure_operative as proc on proc.id = rich.procedura_id
join atti on atti.id = proc.atto_id
join richieste_protocollo as pro on pro.richiesta_id = rich.id and pro.data_cancellazione is null and pro.tipo = 'FINANZIAMENTO'
where rich.procedura_id in (28,104,69) and rich.stato_id = 5 and prop.mandatario = 1;