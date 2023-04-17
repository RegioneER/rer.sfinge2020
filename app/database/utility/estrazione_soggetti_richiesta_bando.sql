set @procedura_id = 123;

select rich.id as 'id progetto', s.descrizione as 'stato progetto' ,
sogg.denominazione as 'Beneficiario',
sogg.partita_iva as 'piva',
sogg.codice_fiscale as 'cf',
sogg.email as 'mail beneficiario',
sogg.tel as 'telefono beneficiario',
sogg.email_pec as 'pec beneficiario' 
from richieste rich 
join proponenti prop on prop.richiesta_id = rich.id
join soggetti sogg on sogg.id = prop.soggetto_id
join stati s on s.id = rich.stato_id
where rich.stato_id is not null and rich.procedura_id = @procedura_id and rich.data_cancellazione is null and sogg.data_cancellazione is null;