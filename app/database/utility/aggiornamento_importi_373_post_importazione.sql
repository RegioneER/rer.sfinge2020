
UPDATE oggetti_importazione373
JOIN (
select oi.id, /*rp.num_pg,*/ sum(p.costo_totale_importato_excel) as sum_costo_totale_importato_excel, sum(p.contributo_importato_excel) as sum_contributo_importato_excel from richieste r
/* join richieste_protocollo rp on rp.richiesta_id = r.id */
join proponenti p on p.richiesta_id = r.id
join oggetti_richiesta o on o.richiesta_id = r.id
join oggetti_importazione373 oi ON oi.id = o.id 
where r.procedura_id = 8
group by  oi.id
)T ON T.id = oggetti_importazione373.id
SET oggetti_importazione373.costo_totale_importato_excel = T.sum_costo_totale_importato_excel, oggetti_importazione373.contributo_importato_excel = T.sum_contributo_importato_excel