insert into voci_spesa(richiesta_id,tipo_voce_spesa_id, importo)

select atc.richiesta_id as richiesta_id,
  tc.id as tipo_voce_spesa_id, 
	sum(
		coalesce(ivpc.`importo_ammissibile_anno_1`,0)+
		coalesce(ivpc.`importo_ammissibile_anno_2`,0)+
		coalesce(ivpc.`importo_ammissibile_anno_3`,0)+
		coalesce(ivpc.`importo_ammissibile_anno_4`,0)+
		coalesce(ivpc.`importo_ammissibile_anno_5`,0)+
		coalesce(ivpc.`importo_ammissibile_anno_6`,0)+
		coalesce(ivpc.`importo_ammissibile_anno_7`,0)
		) as importo
from `attuazione_controllo_richieste` as atc

join `voci_piani_costo` as vpc
on vpc.`richiesta_id` = atc.`richiesta_id`
and vpc.data_cancellazione is null

join `piani_costo` as pc
on pc.id = vpc.`piano_costo_id`

join `tc37_voce_spesa` as tc
on tc.id = pc.`voce_spesa_id`

join `istruttorie_voci_piani_costo` as ivpc
on ivpc.`voce_piano_costo_id` = vpc.id 
and ivpc.data_cancellazione is null

left join `voci_spesa` as vs
on vs.`richiesta_id` = atc.`richiesta_id` 
and vs.`tipo_voce_spesa_id` 
and vs.data_cancellazione is null

where atc.data_cancellazione is null 
and vs.id is null

group by atc.richiesta_id, tc.id;