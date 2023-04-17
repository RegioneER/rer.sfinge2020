insert into indicatori_output(richiesta_id, indicatore_id)
select r.id, ia.`indicatore_output_id` from richieste as r
join procedure_operative as po
on po.id = r.procedura_id
join `procedure_operative_azioni` as pa
on pa.procedura_id = r.procedura_id
join indicatori_output_azioni as ia
on ia.`azione_id` = pa.`azione_id`
and ia.`asse_id` = po.`asse_id`
left join `indicatori_output` as i
on i.`richiesta_id` = r.id and ia.`indicatore_output_id` = i.`indicatore_id` and i.data_cancellazione is null

where 
i.id is null and
r.stato_id is not null
and r.data_cancellazione is null;