DROP FUNCTION IF EXISTS controllo_igrue;
DELIMITER $$
CREATE FUNCTION controllo_igrue (richiesta_id BIGINT, codice CHAR(4)) RETURNS SMALLINT
BEGIN
	DECLARE res SMALLINT DEFAULT 0;
    IF codice = '001' THEN
        SELECT COUNT(*) > 0 INTO @res
        FROM vista_ap03 ap03
        INNER JOIN tc11_tipo_classificazione as tc11
        ON tc11.id = ap03.tc11_tipo_classificazione_id
        WHERE ap03.richiesta_id = richiesta_id
            AND tc11.tipo_class = 'RA';
       ELSEIF codice = '002' THEN
        SELECT count(*) > 0 INTO @res
        FROM vista_ap05 
        WHERE richiesta_id = richiesta_id;
    ELSEIF codice = '003' THEN
        SELECT count(*) > 0 INTO @res
        FROM vista_ap06 ap06
        WHERE ap06.richiesta_id = richiesta_id;
    ELSEIF codice = '004' THEN
        SELECT count(*) > 0 INTO @res
        FROM vista_sc00 sc00_1
        INNER JOIN tc24_ruolo_soggetto as tc24_1
        ON sc00_1.tc24_ruolo_soggetto_id = tc24_1.id
        , vista_sc00 sc00_2
        INNER JOIN tc24_ruolo_soggetto as tc24_2
        ON sc00_2.tc24_ruolo_soggetto_id = tc24_2.id
        WHERE sc00_1.richiesta_id = richiesta_id
            AND sc00_2.richiesta_id = richiesta_id
            AND tc24_1.cod_ruolo_sog = '1'
            AND tc24_2.cod_ruolo_sog = '2';
    ELSEIF codice = '005' THEN
        SELECT count(*) > 0 INTO @res
        FROM vista_fn00 fn00
        WHERE fn00.richiesta_id = richiesta_id;
    ELSEIF codice = '006' THEN
        SELECT count(*) > 0 INTO @res
        FROM vista_fn01 fn01
        WHERE fn01.richiesta_id = richiesta_id;
    ELSEIF codice = '007' THEN
        SELECT count(*) > 0 INTO @res
        FROM vista_in00 in00
        WHERE in00.richiesta_id = richiesta_id;
    ELSEIF codice = '008' THEN
        SELECT count(*) > 0 INTO @res
        FROM vista_in01 in01
        INNER JOIN tc44_45__indicatori_output as com 
            on com.id = in01.indicatore_id 
            and com.tipo = 'COMUNI'
            and data_cancellazione is null
        WHERE in01.richiesta_id = richiesta_id
            AND in01.val_programmato > 0;
    ELSEIF codice = '009' THEN
        SELECT count(*) > 0 INTO @res
        FROM vista_pr00 pr00
        INNER JOIN tc46_fase_procedurale AS tc 
        on tc.id = pr00.tc46_fase_procedurale_id AND tc.cod_fase in (
            '0101',
            '0102',
            '0201',
            '0202',
            '0305',
            '0306',
            '0601',
            '0602',
            '0701',
            '0702',
            '0801',
            '0802'
            )
        WHERE pr00.richiesta_id = richiesta_id
            and pr00.data_inizio_prevista is not null
            and pr00.data_fine_prevista is not null
        ;
    ELSEIF codice = '010' THEN
        SELECT count(*) > 0 INTO @res
        FROM vista_pr01 pr01       
        WHERE pr01.richiesta_id = richiesta_id;
    ELSEIF codice = '013' THEN
        SELECT CASE WHEN tc5.tipo_operazione = 5 THEN 
                    CASE WHEN ap01.richiesta_id IS NULL 
                        THEN 0
                        ELSE 1 
                    END
                ELSE  
                CASE WHEN ap01.richiesta_id IS NOT NULL AND pa00.procedura_id IS NULL 
                    THEN 0
                    ELSE 1
                END
            END
             INTO @res
        FROM vista_ap00 ap00
        INNER JOIN tc5_tipo_operazione as tc5
        ON tc5.id = ap00.tc5_tipo_operazione_id 
        LEFT JOIN vista_ap01 ap01
        on ap00.richiesta_id = ap01.richiesta_id
         LEFT JOIN vista_pa00 as pa00
        on pa00.tc1_cod_proc_att_id = ap01.tc1_procedura_attivazione_id 
        WHERE ap00.richiesta_id = richiesta_id;
    ELSEIF codice = '014' THEN
        SELECT CASE WHEN ap04.stato = 0 OR tc4.fondo NOT LIKE '%ERDF%' THEN 1
            ELSE
                COUNT(DISTINCT tc11.id) > 4
            END INTO @res
        FROM vista_ap04 AS ap04
        INNER JOIN tc4_programma tc4
        ON tc4.id = ap04.tc4_programma_id
        LEFT JOIN vista_ap03 as ap03
        ON ap03.tc4_programma_id = tc4.id
        AND ap03.richiesta_id = ap04.richiesta_id
        left join tc11_tipo_classificazione as tc11
        on tc11.id = ap03.tc11_tipo_classificazione_id
        AND tc11.tipo_class IN ('CI', 'FF', 'TT', 'MET', 'AE')
        WHERE ap04.richiesta_id = richiesta_id
        
        GROUP BY ap04.richiesta_id, ap04.stato, tc4.fondo;
    ELSEIF codice = '015' THEN
        SELECT CASE WHEN ap04.stato = 0 OR tc4.fondo NOT LIKE '%ESF%' THEN 1
            ELSE
                COUNT(DISTINCT tc11.id) > 5
            END INTO @res
        FROM vista_ap04 AS ap04
        INNER JOIN tc4_programma tc4
        ON tc4.id = ap04.tc4_programma_id
        LEFT JOIN vista_ap03 as ap03
        ON ap03.tc4_programma_id = tc4.id
        AND ap03.richiesta_id = ap04.richiesta_id
        left join tc11_tipo_classificazione as tc11
        on tc11.id = ap03.tc11_tipo_classificazione_id
        AND tc11.tipo_class IN ('CI', 'FF', 'TT', 'MET', 'AE', 'DTS')
        WHERE ap04.richiesta_id = richiesta_id
        
        GROUP BY ap04.richiesta_id, ap04.stato, tc4.fondo;
     ELSEIF codice = '016' THEN
        SELECT CASE WHEN ap04.stato = 0 OR tc4.fondo NOT LIKE '%YEI%' THEN 1
            ELSE
                COUNT(DISTINCT tc11.id) > 5
            END INTO @res
        FROM vista_ap04 AS ap04
        INNER JOIN tc4_programma tc4
        ON tc4.id = ap04.tc4_programma_id
        LEFT JOIN vista_ap03 as ap03
        ON ap03.tc4_programma_id = tc4.id
        AND ap03.richiesta_id = ap04.richiesta_id
        left join tc11_tipo_classificazione as tc11
        on tc11.id = ap03.tc11_tipo_classificazione_id
        AND tc11.tipo_class IN ('CI', 'FF', 'TT', 'MET', 'AE', 'DTS')
        WHERE ap04.richiesta_id = richiesta_id
        
        GROUP BY ap04.richiesta_id, ap04.stato, tc4.fondo;
    ELSEIF codice = '017' THEN
        SELECT CASE WHEN ap04.stato = 0 OR tc4.fondo NOT LIKE '%EAFRD%' THEN 1
            ELSE
                COUNT(DISTINCT tc11.id) > 0
            END INTO @res
        FROM vista_ap04 AS ap04
        INNER JOIN tc4_programma tc4
        ON tc4.id = ap04.tc4_programma_id
        LEFT JOIN vista_ap03 as ap03
        ON ap03.tc4_programma_id = tc4.id
        AND ap03.richiesta_id = ap04.richiesta_id
        left join tc11_tipo_classificazione as tc11
        on tc11.id = ap03.tc11_tipo_classificazione_id
        AND tc11.tipo_class IN ('TI')
        WHERE ap04.richiesta_id = richiesta_id
        
        GROUP BY ap04.richiesta_id, ap04.stato, tc4.fondo;
    ELSEIF codice = '018' THEN
        SELECT CASE WHEN tc5.codice_natura_cup IN('03', '07')
            THEN count(fn02.richiesta_id) > 0
            ELSE
                1
            END INTO @res
        FROM vista_ap00 AS ap00
        INNER JOIN tc5_tipo_operazione AS tc5
        ON tc5.id = ap00.tc5_tipo_operazione_id
        LEFT JOIN vista_fn02 AS fn02
        on fn02.richiesta_id = ap00.richiesta_id
        WHERE ap00.richiesta_id = richiesta_id
        GROUP BY tc5.codice_natura_cup;
    ELSEIF codice = '019' THEN
        SELECT (SUM(COALESCE(fn04.importo_impegno, 0) * 
            CASE fn04.tipologia_impegno 
                WHEN 'D-TR' THEN -1
                WHEN 'D' THEN -1
                ELSE 1
            END
        ) > 0) + (
        	SUM(COALESCE(fn06.importo_pag, 0) * 
            CASE fn06.tipologia_pag 
                WHEN 'R-TR' THEN -1
                WHEN 'R' THEN -1
                ELSE 1
            END
        ) <= 0
        ) > 0 INTO @res
        FROM vista_fn04 AS fn04,
             vista_fn06 AS fn06
        WHERE fn04.richiesta_id = richiesta_id
            AND fn06.richiesta_id = fn04.richiesta_id
        GROUP BY fn04.richiesta_id, fn06.richiesta_id;
    ELSEIF codice = '020' THEN
       SELECT COUNT(fn01.tc4_programma_id) = 0 INTO @res
        FROM vista_ap04 AS ap04
        LEFT JOIN vista_fn01 AS fn01
        ON fn01.richiesta_id = ap04.richiesta_id
        AND fn01.tc4_programma_id = ap04.tc4_programma_id
        AND fn01.importo_ammesso is null
        WHERE ap04.richiesta_id = richiesta_id
        AND ap04.stato = 1
        AND fn01.tc4_programma_id IS NULL;
    ELSEIF codice = '021' THEN
        SELECT COUNT(*) = 0 OR COUNT(IF(ap04.richiesta_id IS NULL,1,NULL)) INTO @res
        FROM vista_fn05 AS fn05
        LEFT JOIN vista_ap04 AS ap04
        ON fn05.richiesta_id = ap04.richiesta_id
        AND fn05.tc4_programma_id = ap04.tc4_programma_id
        AND COALESCE(ap04.stato, 0) = 0
        WHERE fn05.richiesta_id = richiesta_id;
    ELSEIF codice = '022' THEN
        SELECT COUNT(*) = 0 OR COUNT(IF(ap04.richiesta_id IS NULL,1,NULL)) INTO @res
        FROM vista_fn07 AS fn07
        LEFT JOIN vista_ap04 AS ap04
        ON fn07.richiesta_id = ap04.richiesta_id
        AND fn07.tc4_programma_id = ap04.tc4_programma_id
        AND COALESCE(ap04.stato, 0) = 0
        WHERE fn07.richiesta_id = richiesta_id;
    ELSEIF codice = '025' THEN
      SELECT MIN(COALESCE(t.res,1)) INTO @res
        FROM 
            (SELECT COALESCE(
                    fn04.importo_impegno *
                    CASE fn04.tipologia_impegno
                    WHEN 'I' THEN 1
                    WHEN 'I-TR' THEN 1
                    ELSE -1 END
                ,0) >= SUM(COALESCE(
                    fn05.importo_imp_amm *
                    CASE fn05.tipologia_imp_amm
                    WHEN 'I' THEN 1
                    WHEN 'I-TR' THEN 1
                    ELSE -1 END
                ,0)) res
            FROM vista_fn04 AS fn04
            LEFT JOIN vista_fn05 AS fn05
            ON fn04.richiesta_id = fn05.richiesta_id
            AND fn04.cod_impegno = fn05.cod_impegno
            AND fn04.data_impegno = fn05.data_impegno
            WHERE fn04.richiesta_id = richiesta_id) t;
    ELSEIF codice = '026' THEN
        SELECT MIN(COALESCE(t.res,1)) INTO @res
        FROM 
            (SELECT SUM(COALESCE(
                    fn06.importo_pag *
                    CASE fn06.tipologia_pag
                    WHEN 'P' THEN 1
                    WHEN 'P-TR' THEN 1
                    ELSE -1 END
                ,0)) >= SUM(COALESCE(
                    fn07.importo_pag_amm *
                    CASE fn07.tipologia_pag_amm
                    WHEN 'P' THEN 1
                    WHEN 'P-TR' THEN 1
                    ELSE -1 END
                ,0)) as res
            FROM vista_fn06 AS fn06
            LEFT JOIN vista_fn07 AS fn07
            ON fn06.richiesta_id = fn07.richiesta_id
            AND fn06.cod_pagamento = fn07.cod_pagamento
            AND fn06.data_pagamento = fn07.data_pagamento

            WHERE fn06.richiesta_id = richiesta_id
            ) t;
    ELSEIF codice = '028' THEN
      SELECT MIN(COALESCE(t.res,1)) INTO @res
        FROM 
            (SELECT COALESCE(
                    fn04.importo_impegno *
                    CASE fn04.tipologia_impegno
                    WHEN 'I' THEN 1
                    WHEN 'I-TR' THEN 1
                    ELSE -1 END
                ,0) >= SUM(COALESCE(
                    fn06.importo_pag *
                    CASE fn06.tipologia_pag
                    WHEN 'P' THEN 1
                    WHEN 'P-TR' THEN 1
                    ELSE -1 END
                ,0)) res
            FROM vista_fn04 AS fn04
            LEFT JOIN vista_fn06 AS fn06
            ON fn06.richiesta_id = fn04.richiesta_id
            WHERE fn04.richiesta_id = richiesta_id) t;
    ELSEIF codice = '029' THEN
      SELECT COALESCE(MIN(COALESCE(t.res,1)),1) INTO @res
        FROM 
            (SELECT fn01.importo_ammesso >= SUM(COALESCE(
                    fn05.importo_imp_amm *
                    CASE fn05.tipologia_imp_amm
                    WHEN 'I' THEN 1
                    WHEN 'I-TR' THEN 1
                    ELSE -1 END
                ,0)) res
            FROM vista_fn01 AS fn01
            LEFT JOIN vista_fn05 AS fn05
            ON fn01.richiesta_id = fn05.richiesta_id
            AND fn01.tc36_livello_gerarchico_id = fn05.tc36_livello_gerarchico_id
            WHERE fn01.richiesta_id = richiesta_id
            GROUP BY fn01.richiesta_id, fn01.tc36_livello_gerarchico_id
        ) t;
    ELSEIF codice = '030' THEN
      SELECT COALESCE(MIN(COALESCE(t.res,1)),1) INTO @res
        FROM(
            SELECT SUM(COALESCE(
                    fn05.importo_imp_amm *
                    CASE fn05.tipologia_imp_amm
                    WHEN 'I' THEN 1
                    WHEN 'I-TR' THEN 1
                    ELSE -1 END
                ,0)) >= SUM(COALESCE(
                    fn07.importo_pag_amm *
                    CASE fn07.tipologia_pag_amm
                    WHEN 'P' THEN 1
                    WHEN 'P-TR' THEN 1
                    ELSE -1 END
                ,0)) as res
            FROM vista_fn05 AS fn05, vista_fn07 as fn07
                WHERE fn05.richiesta_id = richiesta_id
                and fn07.richiesta_id = richiesta_id
                and coalesce(fn05.tc36_livello_gerarchico_id, fn07.tc36_livello_gerarchico_id) = 
                    coalesce(fn07.tc36_livello_gerarchico_id, fn05.tc36_livello_gerarchico_id)
                GROUP BY fn05.richiesta_id, fn05.tc36_livello_gerarchico_id
        ) t;
    ELSEIF codice = '032' THEN
        SELECT COUNT(fn04.richiesta_id) = 0 INTO @res
        FROM vista_fn04 AS fn04
        WHERE fn04.richiesta_id = richiesta_id and fn04.data_impegno > NOW();
    ELSEIF codice = '033' THEN
        SELECT COUNT(fn06.richiesta_id) = 0 INTO @res
        FROM vista_fn06 AS fn06
        WHERE fn06.richiesta_id = richiesta_id and fn06.data_pagamento > NOW();
    ELSEIF codice = '034' THEN
        SELECT COUNT(fn05.richiesta_id) = 0 INTO @res
        FROM vista_fn05 AS fn05
        WHERE fn05.richiesta_id = richiesta_id and fn05.data_imp_amm > NOW();
    ELSEIF codice = '035' THEN
        SELECT COUNT(fn07.richiesta_id) = 0 INTO @res
        FROM vista_fn07 AS fn07
        WHERE fn07.richiesta_id = richiesta_id and fn07.data_pag_amm > NOW();
    ELSEIF codice = '036' THEN
        SELECT COUNT(pr01.richiesta_id) = 0 INTO @res
        FROM vista_pr01 AS pr01
        WHERE pr01.richiesta_id = richiesta_id and pr01.data_riferimento > NOW();
    ELSEIF codice = '037' THEN
        SELECT COUNT(fn08.richiesta_id) = 0 INTO @res
        FROM vista_fn08 AS fn08
        LEFT JOIN vista_fn06 AS fn06
        ON fn08.richiesta_id = fn06.richiesta_id 
        AND fn08.cod_pagamento = fn06.cod_pagamento
        AND fn08.data_pagamento = fn06.data_pagamento
        WHERE fn08.richiesta_id = 1 
        GROUP BY fn08.richiesta_id, fn08.cod_pagamento, fn08.data_pagamento, fn08.importo
        HAVING COALESCE(fn08.importo, 0) > SUM(
                COALESCE(fn06.importo_pag, 0) * 
                CASE fn06.tipologia_pag
                    WHEN 'P' THEN 1 
                    WHEN 'P-TR' THEN 1 
                    ELSE -1 
                END
            );
    ELSEIF codice = '040' THEN
        SELECT COUNT(pr00.richiesta_id) = 0 INTO @res
        FROM vista_pr00 AS pr00
        INNER JOIN  tc46_fase_procedurale as tc46
        ON tc46.id = pr00.tc46_fase_procedurale_id
        AND tc46.cod_fase IN (
            '0102',
            '0202',
            '0306',
            '0602',
            '0702',
            '0802'
        )
        INNER JOIN vista_in01 AS in01
        ON in01.richiesta_id = pr00.richiesta_id
        WHERE pr00.richiesta_id = richiesta_id 
        and pr00.data_fine_effettiva is not null
        AND (
            coalesce(in01.val_programmato, in01.valore_realizzato) IS NULL 
            OR coalesce(in01.val_programmato, in01.valore_realizzato) < 0
        );
    ELSEIF codice = '041' THEN
        SELECT count(*) = 0 INTO @res
        FROM vista_pr00 AS pr00
        INNER JOIN  tc46_fase_procedurale as tc46
        ON tc46.id = pr00.tc46_fase_procedurale_id
        AND tc46.cod_fase IN (
            '0102',
            '0202',
            '0302', 
            '0303', 
            '0304', 
            '0305', 
            '0306',
            '0307',
            '0602',
            '0702',
            '0802'
        )
        INNER JOIN (
            SELECT p2.richiesta_id as richiesta_id, tc46_2.cod_fase as cod_fase
            FROM vista_pr00 AS p2
            INNER JOIN  tc46_fase_procedurale as tc46_2
            ON tc46_2.id = p2.tc46_fase_procedurale_id
            AND tc46_2.cod_fase IN (
                '0102',
                '0202',
                '0302', 
                '0303', 
                '0304', 
                '0305', 
                '0306',
                '0307',
                '0602',
                '0702',
                '0802'
            )
            WHERE p2.data_inizio_effettiva IS NULL OR p2.data_fine_effettiva IS NULL
            GROUP BY p2.richiesta_id, tc46_2.cod_fase
        ) as res
        ON res.richiesta_id = pr00.richiesta_id
        AND tc46.cod_fase > res.cod_fase

        WHERE pr00.richiesta_id = richiesta_id 
        and pr00.data_fine_effettiva is not null;
    ELSEIF codice = '042' THEN
    SELECT COUNT(pr00.richiesta_id) = 0 INTO @res
        FROM vista_pr00 AS pr00        
        WHERE pr00.richiesta_id = richiesta_id 
        and pr00.data_fine_effettiva is not null
        AND pr00.data_inizio_effettiva IS NULL;
    ELSEIF codice = '043' THEN
        SELECT COUNT(in00.richiesta_id) = 0 INTO @res
        FROM vista_in00 AS in00
        INNER JOIN tc42_43__indicatori_risultato as tc42
        ON in00.indicatore_id = tc42.id
        and tc42.tipo = 'PROGRAMMA'
        LEFT JOIN vista_ap04 as ap04
        on ap04.richiesta_id = in00.richiesta_id and COALESCE(ap04.stato, 0) = 1
        AND ap04.tc4_programma_id = tc42.programma_id
        WHERE in00.richiesta_id = richiesta_id 
        AND ap04.richiesta_id IS NULL;
    ELSEIF codice = '044' THEN
        SELECT COUNT(in01.richiesta_id) = 0 INTO @res
        FROM vista_in01 AS in01
        INNER JOIN tc44_45__indicatori_output as tc44
        ON in01.indicatore_id = tc44.id
        and tc44.tipo = 'PROGRAMMA'
        LEFT JOIN vista_ap04 as ap04
        on ap04.richiesta_id = in01.richiesta_id 
        AND ap04.tc4_programma_id = tc44.programma_id
        AND COALESCE(ap04.stato, 0) = 1
        WHERE in01.richiesta_id = richiesta_id 
        AND ap04.richiesta_id IS NULL;
    ELSEIF codice = '045' THEN
        SELECT COUNT(fn02.richiesta_id) = 0 INTO @res
        FROM vista_fn02 AS fn02

        INNER JOIN tc37_voce_spesa as tc37
        ON fn02.tc37_voce_spesa_id = tc37.id
        
        LEFT JOIN vista_ap00 as ap00
        on ap00.richiesta_id = fn02.richiesta_id 
        
        LEFT JOIN tc5_tipo_operazione as tc5
        ON tc5.id = ap00.tc5_tipo_operazione_id
        AND tc5.codice_natura_cup = tc37.codice_natura_cup
        
        WHERE fn02.richiesta_id = richiesta_id 
        AND tc5.id IS NULL;
    ELSEIF codice = '046' THEN
        SELECT COUNT(pr00.richiesta_id) = 0 INTO @res
        FROM vista_pr00 AS pr00

        INNER JOIN tc46_fase_procedurale as tc46
        ON pr00.tc46_fase_procedurale_id = tc46.id
        
        LEFT JOIN vista_ap00 as ap00
        on ap00.richiesta_id = pr00.richiesta_id 
        
        LEFT JOIN tc5_tipo_operazione as tc5
        ON tc5.id = ap00.tc5_tipo_operazione_id
        AND tc5.codice_natura_cup = tc46.codice_natura_cup
        
        WHERE pr00.richiesta_id = richiesta_id 
        AND tc5.id IS NULL;
     ELSEIF codice = '047' THEN
        SELECT COUNT(fn05.richiesta_id) = 0 INTO @res
        FROM vista_fn05 AS fn05

        LEFT JOIN vista_fn04 as fn04
        ON fn05.richiesta_id = fn04.richiesta_id
        AND fn05.cod_impegno = fn04.cod_impegno
        AND fn05.tipologia_impegno = fn04.tipologia_impegno
        AND fn05.data_impegno = fn04.data_impegno
        
        WHERE fn05.richiesta_id = richiesta_id
        AND fn04.richiesta_id IS NULL;
    ELSEIF codice = '048' THEN
        SELECT COUNT(fn07.richiesta_id) = 0 INTO @res
        FROM vista_fn07 AS fn07

        LEFT JOIN vista_fn06 as fn06
        ON fn07.richiesta_id = fn06.richiesta_id
        AND fn07.cod_pagamento = fn06.cod_pagamento
        AND fn07.tipologia_pag = fn06.tipologia_pag
        AND fn07.data_pagamento = fn06.data_pagamento
        
        WHERE fn07.richiesta_id = richiesta_id
        AND fn06.richiesta_id IS NULL;
    ELSEIF codice = '049' THEN
        SELECT COUNT(fn08.richiesta_id) = 0 INTO @res
        FROM vista_fn08 AS fn08

        LEFT JOIN vista_fn06 as fn06
        ON fn08.richiesta_id = fn06.richiesta_id
        AND fn08.cod_pagamento = fn06.cod_pagamento
        AND fn08.tipologia_pag = fn06.tipologia_pag
        AND fn08.data_pagamento = fn06.data_pagamento
        
        WHERE fn08.richiesta_id = richiesta_id
        AND fn06.richiesta_id IS NULL;
    ELSEIF codice = '050' THEN
        SELECT SUM(
                COALESCE(
                    fn04.importo_impegno *
                    CASE fn04.tipologia_impegno
                    WHEN 'I' THEN 1
                    WHEN 'D' THEN -1
                    ELSE 0 END
                ,0)
        ) >= 0 OR SUM(
                COALESCE(
                    fn04.importo_impegno *
                    CASE fn04.tipologia_impegno
                    WHEN 'I-TR' THEN 1
                    WHEN 'D-TR' THEN -1
                    ELSE 0 END
                ,0)
        ) >= 0 INTO @res
        FROM vista_fn04 AS fn04

        WHERE fn04.richiesta_id = richiesta_id;
    ELSEIF codice = '051' THEN
        SELECT count(fn05.richiesta_id) = 0 INTO @res
        FROM vista_fn05 AS fn05
        WHERE fn05.richiesta_id = richiesta_id
        GROUP BY fn05.tc36_livello_gerarchico_id
        HAVING SUM(
                COALESCE(
                    fn05.importo_imp_amm *
                    CASE fn05.tipologia_imp_amm
                    WHEN 'I' THEN 1
                    WHEN 'D' THEN -1
                    ELSE 0 END
                ,0)
        ) < 0 AND SUM(
                COALESCE(
                    fn05.importo_imp_amm *
                    CASE fn05.tipologia_imp_amm
                    WHEN 'I-TR' THEN 1
                    WHEN 'D-TR' THEN -1
                    ELSE 0 END
                ,0)
        ) < 0;
    ELSEIF codice = '052' THEN
        SELECT SUM(
                COALESCE(
                    fn06.importo_pag *
                    CASE fn06.tipologia_pag
                    WHEN 'P' THEN 1
                    WHEN 'R' THEN -1
                    ELSE 0 END
                ,0)
        ) >= 0 OR SUM(
                COALESCE(
                    fn06.importo_pag *
                    CASE fn06.tipologia_pag
                    WHEN 'P-TR' THEN 1
                    WHEN 'R-TR' THEN -1
                    ELSE 0 END
                ,0)
        ) >= 0 INTO @res
        FROM vista_fn06 AS fn06

        WHERE fn06.richiesta_id = richiesta_id;
    ELSEIF codice = '053' THEN
        SELECT count(fn07.richiesta_id) = 0 INTO @res
        FROM vista_fn07 AS fn07
        WHERE fn07.richiesta_id = richiesta_id
        GROUP BY fn07.tc36_livello_gerarchico_id
        HAVING SUM(
                COALESCE(
                    fn07.importo_pag_amm *
                    CASE fn07.tipologia_pag_amm
                    WHEN 'P' THEN 1
                    WHEN 'R' THEN -1
                    ELSE 0 END
                ,0)
        ) < 0 AND SUM(
                COALESCE(
                    fn07.importo_pag_amm *
                    CASE fn07.tipologia_pag_amm
                    WHEN 'P-TR' THEN 1
                    WHEN 'R-TR' THEN -1
                    ELSE 0 END
                ,0)
        ) < 0;
    ELSEIF codice = '054' THEN
        SELECT count(fn05.richiesta_id) = 0 INTO @res
        FROM vista_fn05 AS fn05
        
        INNER JOIN vista_fn04 AS fn04
        ON fn04.richiesta_id = fn05.richiesta_id
        AND fn05.cod_impegno = fn04.cod_impegno
        AND fn05.tipologia_impegno = fn04.tipologia_impegno
        AND fn05.data_impegno = fn04.data_impegno

        WHERE fn05.richiesta_id = richiesta_id
        HAVING SUM(
                COALESCE(
                    fn05.importo_imp_amm *
                    CASE fn05.tipologia_imp_amm
                    WHEN 'I' THEN 1
                    WHEN 'I-TR' THEN 1
                    ELSE -1 END
                ,0)
        ) > SUM(
                COALESCE(
                    fn04.importo_impegno *
                    CASE fn04.tipologia_impegno
                    WHEN 'I' THEN 1
                    WHEN 'I-TR' THEN 1
                    ELSE -1 END
                ,0)
        );
    ELSEIF codice = '055' THEN
        SELECT count(fn07.richiesta_id) = 0 INTO @res
        FROM vista_fn07 AS fn07
        
        INNER JOIN vista_fn06 AS fn06
        ON fn06.richiesta_id = fn07.richiesta_id
        AND fn07.cod_pagamento = fn06.cod_pagamento
        AND fn07.tipologia_pag = fn06.tipologia_pag
        AND fn07.data_pagamento = fn06.data_pagamento

        WHERE fn07.richiesta_id = richiesta_id
        HAVING SUM(
                COALESCE(
                    fn07.importo_pag_amm *
                    CASE fn07.tipologia_pag_amm
                    WHEN 'P' THEN 1
                    WHEN 'R-TR' THEN 1
                    ELSE -1 END
                ,0)
        ) > SUM(
                COALESCE(
                    fn06.importo_pag *
                    CASE fn06.tipologia_pag
                    WHEN 'P' THEN 1
                    WHEN 'R-TR' THEN 1
                    ELSE -1 END
                ,0)
        );
    ELSE
        set @res = 0;
    END IF;



RETURN @res;

END$$
DELIMITER ;
