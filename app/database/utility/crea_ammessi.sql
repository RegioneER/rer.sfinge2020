insert into
    richieste_impegni_ammessi(
        richiesta_impegni_id,
        richiesta_livello_gerarchico_id,
        causale_disimpegno_amm_id,
        data_imp_amm,
        tipologia_imp_amm,
        importo_imp_amm,
        creato_da
    )
select
    ri.id,
    rlg.id,
    ri.causale_disimpegno_id,
    ri.data_impegno,
    ri.`tipologia_impegno`,
    ri.importo_impegno,
    'FNTLRD79M10G273D'
from
    richieste_impegni as ri
    join richieste_programmi as rp on rp.`richiesta_id` = ri.`richiesta_id`
    and rp.data_cancellazione is null
    join richieste_livelli_gerarchici as rlg on rlg.`richiesta_programma_id` = rp.id
    and rlg.data_cancellazione is null
    left join assi on assi.`livello_gerachico_id` = rlg.`tc36_livello_gerarchico_id`
    left join richieste_impegni_ammessi as ia on ia.`richiesta_impegni_id` = ri.id
where
    assi.id is null
    and ia.id is null
    and ri.data_cancellazione is null;

	
insert into
    pagamenti_ammessi(
        richiesta_pagamento_id,
        livello_gerarchico_id,
        causale_id,
        data_pagamento,
        importo,
        tipologia_pagamento,
        creato_da
    )
select
    p.id,
    rlg.id,
    p.causale_pagamento_id,
    p.data_pagamento,
    p.importo,
    p.tipologia_pagamento,
    'FNTLRD79M10GD73D'
from
    richieste_pagamenti as p
    join richieste_programmi as rp on rp.`richiesta_id` = p.`richiesta_id`
    and rp.data_cancellazione is null
    join richieste_livelli_gerarchici as rlg on rlg.`richiesta_programma_id` = rp.id
    and rlg.data_cancellazione is null
    left join assi on assi.`livello_gerachico_id` = rlg.`tc36_livello_gerarchico_id`
    left join `pagamenti_ammessi` as pa on pa.richiesta_pagamento_id = p.id
    and pa.`data_cancellazione` is null
where
    assi.id is null
    and pa.id is null
    and p.data_cancellazione is null;