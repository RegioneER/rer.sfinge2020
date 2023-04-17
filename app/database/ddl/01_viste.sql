-- vista AP00
CREATE
OR REPLACE VIEW vista_ap00 AS
SELECT
    richiesta.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richiesta.id
    ) AS cod_locale_progetto,
    richiesta.titolo AS titolo_progetto,
    richiesta.abstract AS sintesi_prg,
    richiesta.mon_tipo_operazione AS tc5_tipo_operazione_id,
    istruttoria.codice_cup AS cup,
    richiesta.mon_tipo_aiuto AS tc6_tipo_aiuto_id,
    DATE(istruttoria.data_avvio_progetto) AS data_inizio,
    DATE(istruttoria.data_termine_progetto) AS data_fine_prevista,
    DATE(atc.data_termine_effettivo) AS data_fine_effettiva,
    richiesta.mon_tipo_tipo_procedura_att_orig_id AS tc48_tipo_procedura_attivazione_originaria_id
FROM
    richieste richiesta
    INNER JOIN richieste_protocollo protocollo ON richiesta.id = protocollo.richiesta_id
    AND protocollo.tipo = 'FINANZIAMENTO'
    AND (protocollo.data_cancellazione IS NULL)
    INNER JOIN attuazione_controllo_richieste atc ON richiesta.id = atc.richiesta_id
    AND (atc.data_cancellazione IS NULL)
    INNER JOIN istruttorie_richieste istruttoria ON richiesta.id = istruttoria.richiesta_id
    AND (istruttoria.data_cancellazione IS NULL)
WHERE
    richiesta.flag_por = 1
    AND richiesta.data_cancellazione IS NULL;
    
-- vista AP01
CREATE OR REPLACE VIEW vista_ap01 AS
SELECT
    richieste.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richieste.id
    ) AS cod_locale_progetto,
    tc1.id AS tc1_procedura_attivazione_id
FROM
    richieste
    INNER JOIN richieste_protocollo protocollo ON richieste.id = protocollo.richiesta_id
    AND protocollo.tipo = 'FINANZIAMENTO'
    AND protocollo.data_cancellazione IS NULL
    INNER JOIN attuazione_controllo_richieste atc ON richieste.id = atc.richiesta_id
    AND (atc.data_cancellazione IS NULL)
    INNER JOIN istruttorie_richieste istruttoria ON richieste.id = istruttoria.richiesta_id
    AND istruttoria.data_cancellazione IS NULL
    INNER JOIN procedure_operative procedura ON richieste.procedura_id = procedura.id
    AND procedura.tipo IN (
        'BANDO',
        'MANIFESTAZIONE_INTERESSE',
        'ASSISTENZA_TECNICA',
        'INGEGNERIA_FINANZIARIA',
        'ACQUISIZIONI',
        'PROCEDURA_PA'
    )
    AND procedura.data_cancellazione IS NULL
    LEFT JOIN tc1_procedura_attivazione tc1 ON procedura.mon_proc_att_id = tc1.id
    AND tc1.data_cancellazione IS NULL
WHERE
    richieste.flag_por = 1
    AND richieste.data_cancellazione IS NULL;

-- vista AP02
    CREATE
    OR REPLACE VIEW vista_ap02 AS
SELECT
    richiesta.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richiesta.id
    ) AS cod_locale_progetto,
    richiesta.mon_progetto_complesso_id AS tc7_progetto_complesso_id,
    richiesta.mon_grande_progetto_id AS tc8_grande_progetto_id,
    CASE richiesta.mon_generatore_entrate WHEN 1 THEN 'S' ELSE 'N' END AS generatore_entrate,
    richiesta.mon_liv_istituzione_str_fin_id AS tc9_tipo_livello_istituzione_id,
    CASE richiesta.mon_fondo_di_fondi WHEN 1 THEN 'S' ELSE 'N' END AS fondo_di_fondi,
    richiesta.mon_gruppo_vulnerabile_id AS tc13_gruppo_vulnerabile_progetto_id,
    richiesta.mon_tipo_localizzazione_id AS tc10_tipo_localizzazione_id
FROM
    richieste richiesta
    INNER JOIN richieste_protocollo protocollo ON richiesta.id = protocollo.richiesta_id
    AND protocollo.tipo = 'FINANZIAMENTO'
    AND (protocollo.data_cancellazione IS NULL)
    INNER JOIN attuazione_controllo_richieste atc ON richiesta.id = atc.richiesta_id
    AND (atc.data_cancellazione IS NULL)
    INNER JOIN istruttorie_richieste istruttoria ON richiesta.id = istruttoria.richiesta_id
    AND (istruttoria.data_cancellazione IS NULL)
WHERE
    (richiesta.flag_por = 1)
    AND (richiesta.data_cancellazione IS NULL);
    
-- vista AP03
    
CREATE OR REPLACE VIEW vista_ap03 AS
SELECT
    programma.richiesta_id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richieste.id
    ) AS cod_locale_progetto,
    programma.programma_id AS tc4_programma_id,
    tc12.tipo_classificazione_id AS tc11_tipo_classificazione_id,
    tc12.id AS tc12_classificazione_id
FROM
    richieste_programmi_classificazioni classificazione
    INNER JOIN richieste_programmi programma ON classificazione.richiesta_programma_id = programma.id
    AND (programma.data_cancellazione IS NULL)
    INNER JOIN richieste richieste ON programma.richiesta_id = richieste.id
    AND (richieste.data_cancellazione IS NULL)
    INNER JOIN richieste_protocollo protocollo ON richieste.id = protocollo.richiesta_id
    AND protocollo.tipo IN (
        'FINANZIAMENTO',
        'INTEGRAZIONE',
        'RISPOSTA_INTEGRAZIONE',
        'INTEGRAZIONE_PAGAMENTO',
        'RISPOSTA_INTEGRAZIONE_PAGAMENTO',
        'ESITO_ISTRUTTORIA',
        'RISPOSTA_ESITO_ISTRUTTORIA',
        'ESITO_ISTRUTTORIA_PAGAMENTO',
        'RICHIESTA_CHIARIMENTI',
        'RISPOSTA_RICHIESTA_CHIARIMENTI',
        'COMUNICAZIONE_PROGETTO',
        'RISPOSTA_COMUNICAZIONE_PROGETTO'
    )
    AND (protocollo.data_cancellazione IS NULL)
    AND (protocollo.tipo IN ('FINANZIAMENTO'))
    INNER JOIN attuazione_controllo_richieste a7_ ON richieste.id = a7_.richiesta_id
    AND (a7_.data_cancellazione IS NULL)
    INNER JOIN istruttorie_richieste i8_ ON richieste.id = i8_.richiesta_id
    AND (i8_.data_cancellazione IS NULL)
    INNER JOIN tc12_classificazione tc12 ON classificazione.classificazione_id = tc12.id
    AND tc12.tipo IN ('GENERICA', 'AZIONE', 'OBIETTIVO')
WHERE
    richieste.flag_por = 1
    AND classificazione.data_cancellazione IS NULL;
    
-- AP04
CREATE OR REPLACE VIEW vista_ap04 AS
SELECT
    richieste.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richieste.id
    ) AS cod_locale_progetto,
    rp.programma_id AS tc4_programma_id,
    rp.stato AS stato,
    tc14.specifica_stato AS tc14_specifica_stato_id
FROM
    richieste_programmi rp
    INNER JOIN richieste richieste ON rp.richiesta_id = richieste.id
    AND richieste.data_cancellazione IS NULL
    INNER JOIN richieste_protocollo protocollo ON richieste.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN attuazione_controllo_richieste atc ON richieste.id = atc.richiesta_id
    AND atc.data_cancellazione IS NULL
    INNER JOIN istruttorie_richieste istruttoria ON richieste.id = istruttoria.richiesta_id
    AND istruttoria.data_cancellazione IS NULL
    LEFT JOIN tc14_specifica_stato tc14 ON rp.specifica_stato_id = tc14.id
    AND tc14.data_cancellazione IS NULL
WHERE
    richieste.flag_por = 1
    AND rp.data_cancellazione IS NULL;


-- AP05
    CREATE OR REPLACE VIEW vista_ap05 AS
SELECT
    richieste.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richieste.id
    ) AS cod_locale_progetto,
    tc15.id AS struttura_attuativa_id
FROM
    richieste
    INNER JOIN richieste_protocollo protocollo ON richieste.id = protocollo.richiesta_id
    AND protocollo.tipo = 'FINANZIAMENTO'
    AND protocollo.data_cancellazione IS NULL
    INNER JOIN attuazione_controllo_richieste atc ON richieste.id = atc.richiesta_id
    AND (atc.data_cancellazione IS NULL)
    INNER JOIN istruttorie_richieste istruttoria ON richieste.id = istruttoria.richiesta_id
    AND istruttoria.data_cancellazione IS NULL
    INNER JOIN tc15_strumento_attuativo tc15 ON tc15.cod_stru_att = '01'
WHERE
    richieste.flag_por = 1
    AND richieste.data_cancellazione IS NULL;
-- AP06
CREATE OR REPLACE VIEW vista_ap06 AS
SELECT
    richieste.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richieste.id
    ) AS cod_locale_progetto,
    localizzazione.localizzazione_id AS tc16_localizzazione_geografica_id,
    localizzazione.indirizzo AS indirizzo,
    localizzazione.cap AS cod_cap
FROM
    localizzazione_geografica localizzazione
    INNER JOIN richieste richieste ON localizzazione.richiesta_id = richieste.id
    AND richieste.data_cancellazione IS NULL
    INNER JOIN richieste_protocollo protocollo ON richieste.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN attuazione_controllo_richieste atc ON richieste.id = atc.richiesta_id
    AND atc.data_cancellazione IS NULL
    INNER JOIN istruttorie_richieste istruttoria ON richieste.id = istruttoria.richiesta_id
    AND istruttoria.data_cancellazione IS NULL
    INNER JOIN tc16_localizzazione_geografica tc16 ON localizzazione.localizzazione_id = tc16.id
    AND tc16.data_cancellazione IS NULL
WHERE
    richieste.flag_por = 1
    AND localizzazione.data_cancellazione IS NULL;


-- vista SC00

CREATE OR REPLACE VIEW vista_sc00 AS
SELECT
    richieste.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richieste.id
    ) AS cod_locale_progetto,
    collegati.ruolo_sog_id AS tc24_ruolo_soggetto_id,
    forme.tc25_forma_giuridica_id AS tc25_forma_giuridica_id,
    tc26.id AS tc26_ateco_id,
    soggetti.codice_fiscale AS codice_fiscale,
    CASE soggetti.tipo WHEN 'AZIENDA' THEN 'N' ELSE 'S' END AS flag_soggetto_pubblico,
    collegati.cod_uni_ipa AS cod_uni_ipa,
    soggetti.denominazione AS denominazione_sog,
    collegati.note AS note
FROM
    soggetti_collegati collegati
    INNER JOIN richieste richieste ON collegati.richiesta_id = richieste.id
    AND richieste.data_cancellazione IS NULL
    INNER JOIN richieste_protocollo protocollo ON richieste.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN attuazione_controllo_richieste atc ON richieste.id = atc.richiesta_id
    AND atc.data_cancellazione IS NULL
    INNER JOIN istruttorie_richieste istruttoria ON richieste.id = istruttoria.richiesta_id
    AND istruttoria.data_cancellazione IS NULL
    INNER JOIN soggetti ON soggetti.id = collegati.soggetto_id
    INNER JOIN forme_giuridiche AS forme ON forme.id = soggetti.forma_giuridica_id
    LEFT JOIN ateco2007 ON ateco2007.id = soggetti.codice_ateco_id
    LEFT JOIN tc26_ateco AS tc26 on tc26.cod_ateco_anno = CONCAT(ateco2007.codice, 'codice_2007')
WHERE
    richieste.flag_por = 1
    AND collegati.data_cancellazione IS NULL;

-- FN00
CREATE OR REPLACE VIEW vista_fn00 AS
SELECT
    richiesta.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richiesta.id
    ) AS cod_locale_progetto,
    finanziamenti.fondo_id AS tc33_fonte_finanziaria_id,
    finanziamenti.norma_id AS tc35_norma_id,
    finanziamenti.delibera_cipe_id AS tc34_delibera_cipe_id,
    finanziamenti.tc16_localizzazione_geografica_id AS tc16_localizzazione_geografica_id,
    COALESCE(s6_.codice_fiscale, '99999') AS cf_cofinanz,
    finanziamenti.importo AS importo
FROM
    finanziamenti
    INNER JOIN richieste richiesta ON finanziamenti.richiesta_id = richiesta.id
    AND richiesta.data_cancellazione IS NULL
    INNER JOIN richieste_protocollo protocollo ON richiesta.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN attuazione_controllo_richieste atc ON richiesta.id = atc.richiesta_id
    AND atc.data_cancellazione IS NULL  
    LEFT JOIN soggetti s6_ ON finanziamenti.cofinanziatore_id = s6_.id
    AND s6_.tipo IN ('SOGGETTO', 'AZIENDA', 'COMUNE', 'OOII')
    AND s6_.data_cancellazione IS NULL
WHERE
    richiesta.flag_por = 1
    AND finanziamenti.data_cancellazione IS NULL
    AND finanziamenti.fondo_id is not null
    and finanziamenti.norma_id is not null
    AND finanziamenti.delibera_cipe_id is not null;
    
-- Vista FN01
CREATE OR REPLACE VIEW vista_fn01 AS
SELECT
    richiesta.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richiesta.id
    ) AS cod_locale_progetto,
    rp.programma_id AS tc4_programma_id,
    rlg.tc36_livello_gerarchico_id AS tc36_livello_gerarchico_id,
    rlg.importo_costo_ammesso AS importo_ammesso
FROM
    richieste_livelli_gerarchici rlg
    INNER JOIN richieste_programmi rp ON rlg.richiesta_programma_id = rp.id
    AND (rp.data_cancellazione IS NULL)
    INNER JOIN richieste richiesta ON rp.richiesta_id = richiesta.id
    AND (richiesta.data_cancellazione IS NULL)
    INNER JOIN richieste_protocollo protocollo ON richiesta.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN attuazione_controllo_richieste a6_ ON richiesta.id = a6_.richiesta_id
    AND a6_.data_cancellazione IS NULL
    INNER JOIN istruttorie_richieste i7_ ON richiesta.id = i7_.richiesta_id
    AND i7_.data_cancellazione IS NULL    
WHERE
        richiesta.flag_por = 1
        AND rlg.importo_costo_ammesso IS NOT NULL
        AND rlg.data_cancellazione IS NULL
        AND rp.programma_id is not null
        and rlg.tc36_livello_gerarchico_id is not null;

-- IN00

CREATE OR REPLACE VIEW vista_in00 AS
SELECT
    richiesta.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richiesta.id
    ) AS cod_locale_progetto,
    i.indicatore_id AS indicatore_id

FROM richieste AS richiesta
    INNER JOIN richieste_protocollo protocollo ON richiesta.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN attuazione_controllo_richieste a6_ ON richiesta.id = a6_.richiesta_id
    AND a6_.data_cancellazione IS NULL
    INNER JOIN istruttorie_richieste i7_ ON richiesta.id = i7_.richiesta_id
    AND i7_.data_cancellazione IS NULL 
    INNER JOIN  indicatori_risultato AS i
    on i.data_cancellazione is null  and i.richiesta_id = richiesta.id
WHERE
        richiesta.flag_por = 1
        AND richiesta.data_cancellazione IS NULL;

-- IN01
CREATE OR REPLACE VIEW vista_in01 AS
SELECT
    richiesta.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richiesta.id
    ) AS cod_locale_progetto,
    i.indicatore_id,
    i.val_programmato AS val_programmato,
    i.valore_realizzato AS valore_realizzato
FROM
    indicatori_output i
    INNER JOIN richieste richiesta ON i.richiesta_id = richiesta.id
    AND richiesta.data_cancellazione IS NULL
    INNER JOIN richieste_protocollo protocollo ON richiesta.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN attuazione_controllo_richieste a4_ ON richiesta.id = a4_.richiesta_id
    AND a4_.data_cancellazione IS NULL
WHERE
    richiesta.flag_por = 1
    AND i.data_cancellazione IS NULL;

-- PR00
CREATE OR REPLACE VIEW vista_pr00 AS
SELECT
    richiesta.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richiesta.id
    ) AS cod_locale_progetto,
    iter.fase_procedurale_id AS tc46_fase_procedurale_id,
    iter.data_inizio_prevista AS data_inizio_prevista,
    iter.data_inizio_effettiva AS data_inizio_effettiva,
    iter.data_fine_prevista AS data_fine_prevista,
    iter.data_fine_effettiva AS data_fine_effettiva
FROM
    iter_progetto iter
    INNER JOIN richieste richiesta ON iter.richiesta_id = richiesta.id
    AND richiesta.data_cancellazione IS NULL
    INNER JOIN richieste_protocollo protocollo ON richiesta.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN attuazione_controllo_richieste a4_ ON richiesta.id = a4_.richiesta_id
    AND a4_.data_cancellazione IS NULL
    INNER JOIN istruttorie_richieste i5_ ON richiesta.id = i5_.richiesta_id
    AND i5_.data_cancellazione IS NULL
WHERE
    richiesta.flag_por = 1
    AND iter.data_cancellazione IS NULL;

-- PR01
CREATE OR REPLACE VIEW vista_pr01 AS
SELECT
    richiesta.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richiesta.id
    ) AS cod_locale_progetto,
    stato.stato_progetto_id AS tc47_stato_progetto_id,
    stato.data_riferimento
FROM
    richiesta_stato_attuazione_progetto stato
    INNER JOIN richieste richiesta ON stato.richiesta_id = richiesta.id
    AND richiesta.data_cancellazione IS NULL
    INNER JOIN richieste_protocollo protocollo ON richiesta.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN attuazione_controllo_richieste a4_ ON richiesta.id = a4_.richiesta_id
    AND a4_.data_cancellazione IS NULL
    INNER JOIN istruttorie_richieste i5_ ON richiesta.id = i5_.richiesta_id
    AND i5_.data_cancellazione IS NULL
    
WHERE
    richiesta.flag_por = 1
    AND stato.data_cancellazione IS NULL;


-- PA00
CREATE OR REPLACE VIEW vista_pa00 AS
SELECT
    procedura.id AS procedura_id,
    attivazione.id AS tc1_cod_proc_att_id,
    procedura.id AS cod_proc_att_locale,
    procedura.mon_cod_aiuto_rna AS cod_aiuto_rna,
    procedura.mon_tipo_procedura_attivazione AS tc2_tipo_procedura_attivazione_id,
    procedura.mon_flag_aiuti AS flag_aiuti,
    procedura.titolo AS descr_procedura_att,
    tae.responsabile_procedura_id AS tc3_responsabile_procedura_id,
    tae.descrizione AS denom_resp_proc,
    procedura.mon_data_avvio_procedura AS data_avvio_procedura,
    procedura.mon_data_fine_procedura AS data_fine_procedura
FROM
    procedure_operative AS procedura
    INNER JOIN  tc1_procedura_attivazione AS attivazione
    on procedura.mon_proc_att_id = attivazione.id
    INNER JOIN tipi_amministrazione_emittente as tae ON tae.id =  procedura.amministrazione_emittente_id
    

WHERE
    procedura.data_cancellazione IS NULL;


-- FN02
CREATE OR REPLACE VIEW vista_fn02 AS
SELECT
    richiesta.id AS richiesta_id,
    tc37.id AS tc37_voce_spesa_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richiesta.id
    ) AS cod_locale_progetto,
    sum(
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_1,
            0
        )
    ) + sum(
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_2,
            0
        )
    ) + sum(
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_3,
            0
        )
    ) + sum(
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_4,
            0
        )
    ) + sum(
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_5,
            0
        )
    ) + sum(
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_6,
            0
        )
    ) + sum(
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_7,
            0
        )
    ) AS importo
FROM
    istruttorie_voci_piani_costo voci_istruttoria
    INNER JOIN voci_piani_costo voci ON voci_istruttoria.voce_piano_costo_id = voci.id
    AND (voci.data_cancellazione IS NULL)
    INNER JOIN piani_costo piano ON voci.piano_costo_id = piano.id
    AND (piano.data_cancellazione IS NULL)
    INNER JOIN tc37_voce_spesa as tc37 on tc37.id = piano.voce_spesa_id
    INNER JOIN richieste richiesta ON voci.richiesta_id = richiesta.id
    AND richiesta.data_cancellazione IS NULL
    AND richiesta.flag_por = 1
    INNER JOIN richieste_protocollo protocollo ON richiesta.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN attuazione_controllo_richieste atc ON richiesta.id = atc.richiesta_id
    AND atc.data_cancellazione IS NULL
WHERE
    voci_istruttoria.data_cancellazione IS NULL
GROUP BY
    tc37.id,
    richiesta.id,
    protocollo.registro_pg,
    protocollo.anno_pg,
    protocollo.num_pg;

-- FN04
CREATE OR REPLACE VIEW vista_fn04 AS
SELECT
    richiesta.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richiesta.id
    ) AS cod_locale_progetto,
    impegni.codice AS cod_impegno,
    impegni.tipologia_impegno AS tipologia_impegno,
    DATE(impegni.data_impegno) AS data_impegno,
    impegni.importo_impegno AS importo_impegno,
    impegni.note_impegno AS note_impegno,
    impegni.causale_disimpegno_id AS tc38_causale_disimpegno_id
FROM
    richieste_impegni impegni
    INNER JOIN richieste richiesta ON impegni.richiesta_id = richiesta.id
    AND richiesta.data_cancellazione IS NULL
    INNER JOIN richieste_protocollo protocollo ON richiesta.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN attuazione_controllo_richieste a4_ ON richiesta.id = a4_.richiesta_id
    AND a4_.data_cancellazione IS NULL
    INNER JOIN istruttorie_richieste i5_ ON richiesta.id = i5_.richiesta_id
    AND i5_.data_cancellazione IS NULL
    
WHERE
    richiesta.flag_por = 1
    AND impegni.data_cancellazione IS NULL;

-- FN06
CREATE OR REPLACE VIEW vista_fn06 AS
SELECT
    richiesta.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richiesta.id
    ) AS cod_locale_progetto,
    COALESCE(psfinge.id, pagamenti.codice) AS cod_pagamento,
    pagamenti.tipologia_pagamento AS tipologia_pag,
    DATE(pagamenti.data_pagamento) AS data_pagamento,
    pagamenti.importo AS importo_pag,
    pagamenti.causale_pagamento_id AS tc39_causale_pagamento_id,
    pagamenti.note AS note_pag
FROM
    richieste_pagamenti pagamenti
    INNER JOIN richieste richiesta ON pagamenti.richiesta_id = richiesta.id
    AND richiesta.data_cancellazione IS NULL
    INNER JOIN richieste_protocollo protocollo ON richiesta.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN attuazione_controllo_richieste a4_ ON richiesta.id = a4_.richiesta_id
    AND a4_.data_cancellazione IS NULL
    INNER JOIN istruttorie_richieste i5_ ON richiesta.id = i5_.richiesta_id
    AND i5_.data_cancellazione IS NULL
    LEFT JOIN pagamenti as psfinge
    on psfinge.id = pagamenti.pagamento_id
    and psfinge.data_cancellazione is null
WHERE
    richiesta.flag_por = 1
    AND pagamenti.data_cancellazione IS NULL;


-- FN07
CREATE OR REPLACE VIEW vista_fn07 AS
SELECT
    richiesta.id as richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richiesta.id
    ) AS cod_locale_progetto,
    pagamento.codice AS cod_pagamento,
    pagamento.tipologia_pagamento AS tipologia_pag,
    pagamento.data_pagamento AS data_pagamento,
    rp.programma_id AS tc4_programma_id,
    rlg.tc36_livello_gerarchico_id AS tc36_livello_gerarchico_id,
    ammesso.data_pagamento AS data_pag_amm,
    ammesso.tipologia_pagamento AS tipologia_pag_amm,
    ammesso.causale_id AS tc39_causale_pagamento_id,
    ammesso.importo AS importo_pag_amm,
    ammesso.note AS note_pag
FROM
    pagamenti_ammessi ammesso
    INNER JOIN richieste_pagamenti pagamento ON ammesso.richiesta_pagamento_id = pagamento.id
    AND pagamento.data_cancellazione IS NULL
    INNER JOIN richieste richiesta ON pagamento.richiesta_id = richiesta.id
    AND richiesta.data_cancellazione IS NULL
    INNER JOIN richieste_protocollo protocollo ON richiesta.id = protocollo.richiesta_id

    AND protocollo.tipo = 'FINANZIAMENTO'
    AND protocollo.data_cancellazione IS NULL
    INNER JOIN attuazione_controllo_richieste atc ON richiesta.id = atc.richiesta_id
    AND atc.data_cancellazione IS NULL
    INNER JOIN richieste_livelli_gerarchici rlg ON ammesso.livello_gerarchico_id = rlg.id
    AND rlg.data_cancellazione IS NULL
    AND rlg.tc36_livello_gerarchico_id IS NOT NULL
    INNER JOIN richieste_programmi rp ON rlg.richiesta_programma_id = rp.id
    AND rp.data_cancellazione IS NULL
    AND rp.programma_id IS NOT NULL

WHERE
    richiesta.flag_por = 1
    AND ammesso.data_cancellazione IS NULL
    AND ammesso.causale_id IS NOT NULL;

-- FN05
CREATE OR REPLACE VIEW vista_fn05 AS
SELECT
    richieste.id as richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richieste.id
    ) AS cod_locale_progetto,
    impegno.codice AS cod_impegno,
    impegno.tipologia_impegno AS tipologia_impegno,
    DATE(impegno.data_impegno) AS data_impegno,
    rp.programma_id AS tc4_programma_id,
    rlg.tc36_livello_gerarchico_id AS tc36_livello_gerarchico_id,
    DATE(ammesso.data_imp_amm) AS data_imp_amm,
    ammesso.tipologia_imp_amm AS tipologia_imp_amm,
    ammesso.causale_disimpegno_amm_id AS tc38_causale_disimpegno_amm_id,
    ammesso.importo_imp_amm AS importo_imp_amm,
    ammesso.note_imp AS note_imp
FROM
    richieste_impegni_ammessi ammesso
    INNER JOIN richieste_impegni impegno ON ammesso.richiesta_impegni_id = impegno.id
    AND impegno.data_cancellazione IS NULL
    INNER JOIN richieste ON impegno.richiesta_id = richieste.id
    AND richieste.data_cancellazione IS NULL
    INNER JOIN attuazione_controllo_richieste atc ON richieste.id = atc.richiesta_id
    AND atc.data_cancellazione IS NULL
    INNER JOIN richieste_protocollo protocollo ON richieste.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN richieste_livelli_gerarchici rlg ON ammesso.richiesta_livello_gerarchico_id = rlg.id
    AND rlg.data_cancellazione IS NULL
    AND rlg.tc36_livello_gerarchico_id IS NOT NULL
    INNER JOIN richieste_programmi rp ON rlg.richiesta_programma_id = rp.id
    AND rp.data_cancellazione IS NULL
    AND rp.programma_id IS NOT NULL

WHERE
    richieste.flag_por = 1
    AND ammesso.data_cancellazione is null;



-- PAGAMENTI ANNI

CREATE OR REPLACE VIEW vista_pagamenti_anni AS
select 
            atc.richiesta_id,
            YEAR(
                CASE istruttoria.tipologia_soggetto 
                    WHEN 'PUBBLICO' THEN COALESCE(m.data_mandato, p.data_invio)
                    ELSE m.data_mandato
                END
            ) AS anno,
            SUM( 
                COALESCE(
                    p.importo_rendicontato_ammesso,
                    0
                )
            ) as importo
        FROM attuazione_controllo_richieste as atc
        INNER JOIN istruttorie_richieste as istruttoria
        on istruttoria.richiesta_id = atc.richiesta_id
        AND istruttoria.data_cancellazione is null
        INNER JOIN pagamenti as p
        on atc.id = p.attuazione_controllo_richiesta_id
        and p.data_cancellazione is null
        and p.stato_id = 10    
        and p.esito_istruttoria = 1
        LEFT JOIN mandati_pagamenti as m
        ON m.id = p.mandato_pagamento_id and m.data_cancellazione is null
        WHERE istruttoria.tipologia_soggetto = 'PUBBLICO' OR m.id IS NOT NULL
       GROUP BY richiesta_id, anno;


-- IMPORTO AMMESSO PROGETTO
CREATE OR REPLACE VIEW vista_importi_ammessi_progetti AS
SELECT 
    atc2.richiesta_id,
    sum(
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_1,
            0
        )
    +
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_2,
            0
        )
    +
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_3,
            0
        )
    +
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_4,
            0
        )
    +
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_5,
            0
        )
    +
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_6,
            0
        )
    +
        COALESCE(
            voci_istruttoria.importo_ammissibile_anno_7,
            0
        )
    ) AS importo
FROM attuazione_controllo_richieste as atc2
INNER JOIN voci_piani_costo as voci
ON voci.data_cancellazione IS NULL
AND voci.richiesta_id = atc2.richiesta_id
INNER JOIN piani_costo piano
on piano.id = voci.piano_costo_id
INNER JOIN tipi_voce_spesa as tvs
on tvs.id = piano.tipo_voce_id
INNER JOIN istruttorie_voci_piani_costo voci_istruttoria
ON voci_istruttoria.voce_piano_costo_id = voci.id
AND voci_istruttoria.data_cancellazione IS NULL

WHERE
    atc2.data_cancellazione is null
    AND tvs.codice <> 'TOTALE'
GROUP BY atc2.richiesta_id;



-- FN03
CREATE OR REPLACE VIEW vista_fn03 AS
select 
atc.richiesta_id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        atc.richiesta_id
    ) AS cod_locale_progetto,
    anni.anno AS anno_piano,
    SUM(COALESCE(CASE WHEN pagamenti_anno.anno <= anni.anno THEN pagamenti_anno.importo ELSE 0 END,0)) AS imp_realizzato,
    CASE anni.anno WHEN YEAR(NOW()) THEN ammessi.importo - SUM(COALESCE(pagamenti_anno.importo, 0)) ELSE 0 END as imp_da_realizzare
FROM attuazione_controllo_richieste as atc
INNER JOIN richieste as r
on r.id = atc.richiesta_id
AND r.flag_por = 1
INNER JOIN richieste_protocollo as protocollo
ON protocollo.richiesta_id = atc.richiesta_id
and protocollo.tipo = 'FINANZIAMENTO'
and protocollo.data_cancellazione is null
INNER JOIN anni
on anni.anno  BETWEEN
        COALESCE((
            SELECT YEAR(MIN(CASE istr.tipologia_soggetto 
                WHEN 'PUBBLICO' THEN pag.data_invio
                ELSE mp.data_mandato
                END
            )) 
            FROM attuazione_controllo_richieste as atc2
            INNER JOIN pagamenti as pag
            ON pag.attuazione_controllo_richiesta_id = atc2.id
            AND pag.esito_istruttoria = 1
            AND pag.stato_id = 10
            AND pag.data_cancellazione is null
            INNER JOIN istruttorie_richieste as istr
            on istr.richiesta_id =  atc2.richiesta_id
            AND istr.data_cancellazione is null
            LEFT JOIN mandati_pagamenti as mp
            on mp.id = pag.mandato_pagamento_id
            WHERE  atc2.id = atc.id
        ), 
        protocollo.anno_pg)
        AND YEAR(NOW())

LEFT JOIN vista_pagamenti_anni as pagamenti_anno
ON pagamenti_anno.richiesta_id = atc.richiesta_id
LEFT JOIN vista_importi_ammessi_progetti as ammessi
ON ammessi.richiesta_id = atc.richiesta_id

GROUP BY atc.richiesta_id, anni.anno
HAVING anni.anno <=  MAX(pagamenti_anno.anno);

-- FN08
CREATE OR REPLACE VIEW vista_fn08 AS
SELECT
    richiesta.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        richiesta.id
    ) AS cod_locale_progetto,
    pagamenti.codice AS cod_pagamento,
    pagamenti.tipologia_pagamento AS tipologia_pag,
    DATE(pagamenti.data_pagamento) AS data_pagamento,
    percettori.tipo_percettore_id AS tc40_tipo_percettore_id,
    percettori.codice_fiscale as codice_fiscale,
    CASE percettori.soggetto_pubblico WHEN 1 THEN 'S' ELSE 'N' END AS flag_soggetto_pubblico,
    percettori.importo AS importo
FROM
    pagamenti_percettori percettori
    INNER JOIN richieste_pagamenti pagamenti
    ON pagamenti.id = percettori.richiesta_pagamento_id
    AND pagamenti.data_cancellazione IS NULL
    INNER JOIN richieste richiesta ON pagamenti.richiesta_id = richiesta.id
    AND richiesta.data_cancellazione IS NULL
    INNER JOIN richieste_protocollo protocollo ON richiesta.id = protocollo.richiesta_id
    AND protocollo.data_cancellazione IS NULL
    AND protocollo.tipo = 'FINANZIAMENTO'
    INNER JOIN attuazione_controllo_richieste a4_ ON richiesta.id = a4_.richiesta_id
    AND a4_.data_cancellazione IS NULL
    INNER JOIN istruttorie_richieste i5_ ON richiesta.id = i5_.richiesta_id
    AND i5_.data_cancellazione IS NULL
    
WHERE
    richiesta.flag_por = 1
    AND percettori.data_cancellazione IS NULL;

-- PG00

CREATE OR REPLACE VIEW vista_pg00 AS
SELECT
    r.id AS richiesta_id,
    COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        r.id
    ) AS cod_locale_progetto,
    concat(COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        r.id
    ), '_', coalesce(rpa.`progressivo`,1)) as cod_proc_agg,
    rpa.cig,
    rpa.`tc22_motivo_assenza_cig_id`,
    rpa.`descrizione_procedura_aggiudicazione` as descr_procedura_agg,
    rpa.`tc23_tipo_procedura_aggiudicazione_id`,
    rpa.`importo_procedura_aggiudicazione` as importo_procedura_agg,
    rpa.`data_pubblicazione_validato` as data_pubblicazione,
    rpa.`importo_aggiudicato_validato` as importo_aggiudicato,
    rpa.`data_aggiudicazione_validato` as data_aggiudicazione
FROM richiesta_procedura_aggiudicazione as rpa
INNER JOIN richieste AS r
ON r.id = rpa.richiesta_id
inner join richieste_protocollo as protocollo
on protocollo.richiesta_id = r.id
and protocollo.`data_cancellazione` is null
and protocollo.tipo = 'FINANZIAMENTO'

where rpa.data_cancellazione is null AND 
(rpa.`tc22_motivo_assenza_cig_id` is not null OR rpa.cig is not null);


CREATE OR REPLACE VIEW vista_fn09 AS
SELECT
r.id AS richiesta_id,
 COALESCE(
        CONCAT(
            protocollo.registro_pg,
            '/',
            protocollo.anno_pg,
            '/',
            protocollo.num_pg
        ),
        r.id
    ) AS cod_locale_progetto,
CASE c.numero 
    WHEN 'I' THEN CONCAT(1,'/',c.anno)
    WHEN 'II' THEN CONCAT(2,'/',c.anno)
    WHEN 'III' THEN CONCAT(3,'/',c.anno)
    WHEN 'IV' THEN CONCAT(4,'/',c.anno)
    WHEN 'V' THEN CONCAT(5,'/',c.anno)
    WHEN 'VI' THEN CONCAT(6,'/',c.anno)
    WHEN 'VII' THEN CONCAT(7,'/',c.anno)
    WHEN 'VIII' THEN CONCAT(8,'/',c.anno)
    ELSE CONCAT(c.numero,'/',c.anno) END AS cod_pagamento,
c.data_proposta_adg as data_pagamento,
CASE  WHEN cert.importo >= 0 THEN 'C' ELSE 'D' END AS tipologia_pag,
tc.id as tc36_livello_gerarchico_id,
ABS(cert.importo) as importo_totale,
ABS(cert.importo) as importo_spesa_pubblica
FROM certificazioni_pagamenti cert
join certificazioni c on c.id = cert.`certificazione_id`
join pagamenti p on cert.pagamento_id = p.id
join `attuazione_controllo_richieste` as atc on atc.id = p.`attuazione_controllo_richiesta_id`
join richieste r on r.id = atc.`richiesta_id`
join `procedure_operative`pr on pr.id = r.`procedura_id`
join assi on assi.id = pr.asse_id
join tc36_livello_gerarchico tc on assi.livello_gerachico_id = tc.id
join richieste_protocollo as protocollo
on protocollo.richiesta_id = r.id
and protocollo.`data_cancellazione` is null
and protocollo.tipo = 'FINANZIAMENTO'
WHERE
r.flag_por = 1