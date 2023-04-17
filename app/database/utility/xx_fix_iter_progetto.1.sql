-- INSERT INTO iter_progetto(richiesta_id, fase_procedurale_id, data_inizio_prevista, data_inizio_effettiva, data_fine_prevista, data_fine_effettiva, data_cancellazione, data_creazione, data_modifica, creato_da, modificato_da )
select
    atc.richiesta_id,
    tc.id,
    CONVERT(
        case when voci.id is null then i.data_creazione else voci.data_avvio_prevista end,
        DATE
    ),
    -- data inizio prevista
    CONVERT(
        case when voci.id is null then i.data_creazione else voci.data_avvio_effettivo end,
        DATE
    ),
    -- data inizio effettiva
    CONVERT(
        case when voci.id is null then i.data_creazione else voci.data_conclusione_prevista end,
        DATE
    ),
    -- data fine previsto
    CONVERT(
        case when voci.id is null then i.data_creazione else voci.data_conclusione_effettiva end,
        DATE
    ),
    -- data fine effettivo
    NULL,
    atc.data_creazione,
    atc.data_modifica,
    NULL,
    NULL
from
    attuazione_controllo_richieste atc
    inner join richieste ric on ric.id = atc.richiesta_id
    inner join proponenti pro on pro.richiesta_id = ric.id
    inner join soggetti sog on sog.id = pro.soggetto_id
    inner join istruttorie_richieste i on i.richiesta_id = atc.richiesta_id
    inner join cup_nature nat on i.cup_natura_id = nat.id
    inner join tc46_fase_procedurale tc on tc.codice_natura_cup = nat.codice
    and tc.descrizione_fase in(
        'Stipula Contratto',
        'Attribuzione finanziamento'
    )
    left join fasi_procedurali as f on f.fase_natura_id = nat.id
    and f.ordinamento = 1
    and f.procedura_id = ric.procedura_id
    left join voci_fase_procedurale as voci on voci.richiesta_id = ric.id
    and voci.data_cancellazione is null
    and f.id = voci.fase_procedurale_id
    left join iter_progetto as iter on iter.richiesta_id = ric.id
    and iter.data_cancellazione is null
    and iter.fase_procedurale_id = tc.id
where
    pro.mandatario = 1
    and pro.data_cancellazione is NULL
    and ric.data_cancellazione is NULL
    and sog.data_cancellazione is NULL
    and atc.data_cancellazione is NULL
    and ric.stato_id in(4, 5)
    and iter.id is null;