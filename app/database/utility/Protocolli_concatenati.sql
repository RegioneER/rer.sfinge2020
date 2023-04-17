select id as id, concat(registro_pg, '/',anno_pg,'/',num_pg) as protocollo, richiesta_protocollo_pagamento_id as padre 
from richieste_protocollo where pagamento_id = @id_pagamento;