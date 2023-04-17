<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class ControlloProgettoRepository extends EntityRepository {

    /**
     * @todo: da completare, filtrare solo per quelle in istruttoria
     */
    public function getControlli($ricercaControlli) {

        $dql = "SELECT controllo.id, proc.titolo, sogg.denominazione, esito.descrizione as esito_ctrl, controllo.data_validazione, controllo as controlloObj, "
                . "CASE WHEN rp.num_pg IS NOT NULL "
                . "THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg) "
                . "ELSE '-' END AS protocollo,  "
                . "at.numero as atto_num,  "
                . "concat(adr.via,' ',adr.numero_civico,' ',com.denominazione, '(', prov.sigla_automobilistica, ')') AS sede_intervento, "
                . "concat(sogg.via,' ',sogg.civico,' ',com2.denominazione, '(', prov2.sigla_automobilistica, ')') AS sede_legale, "
                . "dim.codice as pmi "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controllo "
                . "JOIN controllo.richiesta rich "
                . "JOIN rich.procedura proc "
                . "JOIN proc.atto at "
                . "LEFT JOIN rich.attuazione_controllo atc "
                . "LEFT JOIN atc.pagamenti pag "
                . "LEFT JOIN rich.proponenti prop "
                . "LEFT JOIN prop.sedi seop "
                . "LEFT JOIN seop.sede se "
                . "LEFT JOIN se.indirizzo adr "
                . "LEFT JOIN adr.comune com "
                . "LEFT JOIN com.provincia prov "
                . "LEFT JOIN rich.richieste_protocollo rp "
                . "LEFT JOIN prop.soggetto sogg "
                . "LEFT JOIN sogg.dimensione_impresa dim "
                . "JOIN sogg.comune com2 "
                . "JOIN com2.provincia prov2 "
                . "LEFT JOIN controllo.esito esito "
                . "JOIN proc.asse asse "
                . "WHERE 1=1 "
        ;

        $q = $this->getEntityManager()->createQuery();

        $utente = $ricercaControlli->getUtente();
        if (!is_null($utente)) {

            if (!$utente->hasRole("ROLE_SUPER_ADMIN")) {
                $dql .= " AND ( ";
                $dql .= "proc.id in (select proc3.id from SfingeBundle:PermessiProcedura proc2 join proc2.procedura proc3 where proc2.utente={$utente->getId()}) ";
                $dql .= "OR proc.asse in (select asse3.id from SfingeBundle:PermessiAsse asse2 join asse2.asse asse3 where asse2.utente={$utente->getId()}))";
            }
        }
        
        if(!is_null($ricercaControlli->getTipoControllo()) && $ricercaControlli->getTipoControllo() == 'STABILITA_PUNTUALE') {
            $dql .= " AND controllo.tipologia IN ('STABILITA', 'PUNTUALE') ";
        }else {
            $dql .= " AND controllo.tipologia IN ('STANDARD') ";
        }
        
        if (!is_null($ricercaControlli->getCampione())) {
            $dql .= " AND controllo.campione = :campione ";
            $q->setParameter(":campione", $ricercaControlli->getCampione());
        }

        if (!is_null($ricercaControlli->getUtente())) {

            if ($ricercaControlli->getUtente()->isInvitalia() == true)
                $dql .= " AND proc.id IN (95, 121, 132, 167) ";
        }

        if (!is_null($ricercaControlli->getProcedura())) {
            $dql .= " AND proc.id = :procedura ";
            $q->setParameter("procedura", $ricercaControlli->getProcedura());
        }

        if (!is_null($ricercaControlli->getComune())) {
            $dql .= " AND (com.id = :comune OR com2.id = :comune )";
            $q->setParameter("comune", $ricercaControlli->getComune());
        }

        if (!is_null($ricercaControlli->getAtto())) {
            $dql .= " AND at.id = :at ";
            $q->setParameter("at", $ricercaControlli->getAtto());
        }

        if (!is_null($ricercaControlli->getCompletata())) {
            if ($ricercaControlli->getCompletata()) {
                $dql .= " AND esito.id is not null ";
            } else {
                $dql .= " AND esito.id is null ";
            }
        }

        if (!is_null($ricercaControlli->getCodiceFiscale())) {
            $dql .= " AND sogg.codice_fiscale LIKE :cf ";
            $q->setParameter("cf", "%" . $ricercaControlli->getCodiceFiscale() . "%");
        }

        if (!is_null($ricercaControlli->getDenominazione())) {
            $dql .= " AND sogg.denominazione LIKE :denominazione ";
            $q->setParameter("denominazione", "%" . $ricercaControlli->getDenominazione() . "%");
        }

        if (!is_null($ricercaControlli->getProtocollo())) {
            $dql .= "AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo ";
            $q->setParameter("protocollo", "%" . $ricercaControlli->getProtocollo() . "%");
        }

        $dql .= " GROUP BY controllo.id ORDER BY controllo.id ASC ";

        $q->setDQL($dql);
        $sql = $q->getSQL();


        return $q;
    }

    public function estrazioneControlli() {

        $dql = "SELECT "
                . "rich.id as id_richiesta, "
                . "CASE WHEN rp.num_pg IS NOT NULL "
                . "THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg) "
                . "ELSE '-' END AS protocollo,  "
                . "rich.titolo, "
                . "stato.descrizione as stato_richiesta, "
                . "sogg.denominazione, "
                . "coalesce(controllo.data_inizio_controlli, '-') as data_controllo, "
                . "coalesce(chk.data_validazione, '-') as data_vali_chk, "
                . "esito.descrizione as esito_cl, "
                . "coalesce(controllo.data_validazione, '-') as data_validazione, "
                . "controllo.id as id_controllo, "
                . "max(pag.id) as id_pagamento, "
                . "istr.codice_cup, "
                . "rev.id as id_revoca "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controllo "
                . "JOIN controllo.richiesta rich "
                . "LEFT JOIN controllo.esito esito "
                . "LEFT JOIN rich.attuazione_controllo atc "
                . "LEFT JOIN atc.pagamenti pag "
                . "LEFT JOIN atc.revoca rev "
                . "LEFT JOIN pag.modalita_pagamento modalita "
                . "JOIN rich.procedura proc "
                . "JOIN rich.proponenti prop "
                . "JOIN rich.richieste_protocollo rp "
                . "JOIN rich.stato stato "
                . "LEFT JOIN rich.istruttoria istr "
                . "JOIN prop.soggetto sogg "
                . "LEFT JOIN controllo.valutazioni_checklist chk "
                . "WHERE rp INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento AND prop.mandatario = 1 "
        ;

        $q = $this->getEntityManager()->createQuery();

        $dql .= "GROUP BY controllo.id ORDER BY rich.id ASC ";

        $q->setDQL($dql);


        $res = $q->getResult();

        return $res;
    }

    public function estrazioneControlliPagamenti() {

        $dql = "SELECT "
                . "pag as pagamento, "
                . "pag.id as id_pagamento, "
                . "proc.titolo as titolo, "
                . "istr.codice_cup, "
                . "sogg.denominazione, "
                . "CASE WHEN rp.num_pg IS NOT NULL "
                . "THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg) "
                . "ELSE '-' END AS protocollo,  "
                . "CASE WHEN rp2.num_pg IS NOT NULL "
                . "THEN concat(rp2.registro_pg, '/', rp2.anno_pg, '/', rp2.num_pg) "
                . "ELSE '-' END AS protocollo_pag,  "
                . "modalita.descrizione as modalita_pagamento, "
                . "coalesce(controlli.id, 0) as campionato, "
                . "coalesce(pag.data_invio, '-') as data_invio, "
                . "rev.id as id_revoca "
                . "FROM AttuazioneControlloBundle:Pagamento pag "
                . "JOIN pag.attuazione_controllo_richiesta atc "
                . "LEFT JOIN atc.revoca rev "
                . "JOIN atc.richiesta rich "
                . "LEFT JOIN rich.controlli controlli "
                . "JOIN pag.modalita_pagamento modalita "
                . "JOIN rich.procedura proc "
                . "JOIN rich.richieste_protocollo rp "
                . "JOIN pag.richieste_protocollo rp2 "
                . "JOIN rich.stato stato "
                . "JOIN rich.istruttoria istr "
                . "JOIN rich.proponenti prop "
                . "JOIN prop.soggetto sogg "
                . "WHERE modalita.codice in ('SALDO_FINALE', 'UNICA_SOLUZIONE') AND rp INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento AND prop.mandatario = 1 "
        ;

        $q = $this->getEntityManager()->createQuery();

        $dql .= "GROUP BY pag.id ORDER BY proc.titolo ASC ";

        $q->setDQL($dql);


        $res = $q->getResult();

        return $res;
    }

    public function estrazioneControlliRichieste() {

        $dql = "SELECT "
                . "rich.id as id_richiesta, "
                . "pag.id as id_pagamento, "
                . "CASE WHEN rp.num_pg IS NOT NULL "
                . "THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg) "
                . "ELSE '-' END AS protocollo,  "
                . "CASE WHEN rp2.num_pg IS NOT NULL "
                . "THEN concat(rp2.registro_pg, '/', rp2.anno_pg, '/', rp2.num_pg) "
                . "ELSE '-' END AS protocollo_pag,  "
                . "istr.codice_cup, "
                . "istr.contributo_ammesso, "
                . "sogg.denominazione, "
                . "proc.titolo as titolo, "
                . "asse.titolo as asse_titolo, "
                . "modalita.descrizione as modalita_pagamento, "
                . "stato_pag.descrizione as stato_pag_desc, "
                . "pag.importo_richiesto, "
                . "mandato.importo_pagato, "
                . "pag.importo_rendicontato, "
                . "pag.importo_rendicontato_ammesso, "
                . "coalesce(pag.data_invio, '-') as data_invio, "
                . "rev.id as id_revoca "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controllo "
                . "JOIN controllo.richiesta rich "
                . "JOIN rich.attuazione_controllo atc "
                . "LEFT JOIN atc.revoca rev "
                . "JOIN atc.pagamenti pag "
                . "JOIN pag.modalita_pagamento modalita "
                . "JOIN pag.stato stato_pag "
                . "LEFT JOIN pag.mandato_pagamento mandato "
                . "JOIN rich.procedura proc "
                . "JOIN rich.richieste_protocollo rp "
                . "JOIN pag.richieste_protocollo rp2 "
                . "JOIN rich.istruttoria istr "
                . "JOIN rich.proponenti prop "
                . "JOIN prop.soggetto sogg "
                . "JOIN proc.asse asse "
                . "WHERE rp INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento AND prop.mandatario = 1 "
        ;

        $q = $this->getEntityManager()->createQuery();

        $dql .= "ORDER BY pag.id ASC ";

        $q->setDQL($dql);


        $res = $q->getResult();

        return $res;
    }

    public function estrazioenUniversoProgetti() {

        $sql = "select
            rich.id as id_progetto,
            coalesce(concat(prot.registro_pg, '/',prot.`anno_pg`,'/',prot.`num_pg`), rich.id) as protocollo,
            rich.titolo as titolo_progetto,
            istr.codice_cup as cup,
            assi.titolo,
            proc.titolo as titolo_procedura,
            ap.numero as numero_atto,
            sogg.denominazione as Beneficiario,
            istr.`costo_ammesso` as costo_ammesso,
            istr.`contributo_ammesso` as Contributo_ammesso,
            (
            select m.`descrizione`
            from pagamenti as p
            join richieste_protocollo as rpp
            on rpp.`pagamento_id` = p.id
            and rpp.data_cancellazione is null
            left join `modalita_pagamento` as m
            on m.id = p.modalita_pagamento_id
            where p.`attuazione_controllo_richiesta_id` = atc.id
            and p.data_cancellazione is NULL
            order by m.ordine_cronologico
            limit 1
            ) as modalita_ultimo_pagamento
            from richieste rich
            join `procedure_operative` proc on proc.id = rich.procedura_id
            join atti as ap
            on ap.id = proc.`atto_id`
            join `istruttorie_richieste` istr on istr.`richiesta_id` = rich.id
            left join `attuazione_controllo_richieste` atc on atc.`richiesta_id` = rich.id
            join `proponenti` prop on prop.richiesta_id = rich.id and prop.`data_cancellazione` is null and prop.mandatario = 1
            join soggetti sogg on sogg.id = prop.soggetto_id
            join procedura_tipoaiuto mm on mm.procedura_id = proc.id
            join tipi_aiuti tpa on tpa.id = mm.`tipoaiuto_id`
            join `richieste_protocollo` prot on prot.`richiesta_id` = rich.id
            join assi on assi.id = proc.asse_id
            where prot.`tipo` = 'FINANZIAMENTO'
            and rich.`data_cancellazione` is null
            and istr.`esito_id` = 1";

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getElementiCheckList($id_controllo, $id_checklist) {
        $dql = "SELECT ec.id as id, 
                       ec.descrizione as elemento,
                       ve.valore_raw as valore,
                       ve.commento as note ,
                       ve.documenti_text as note_doc ,
                       ve.collocazione as note_coll ,
                       ve.collocazione_ben as note_coll_ben ,
                       sc.descrizione as sezione, 
                       ec.note as note_elemento, 
                       ec.codice, ec.specifica, 
                       ec.procedure
                     FROM AttuazioneControlloBundle:Controlli\ControlloProgetto ir
                     JOIN ir.valutazioni_checklist vc
                     JOIN vc.checklist c
                     JOIN vc.valutazioni_elementi ve
                     JOIN ve.elemento ec
                     JOIN ec.sezione_checklist sc
                     WHERE ir.id = $id_controllo AND c.id = $id_checklist 
                     ORDER BY sc.ordinamento";
        $query = $this->getEntityManager()->createQuery();
        $query->setDQL($dql);

        return $query->getResult();
    }

    public function estrazioneControlliRichiesteAudit($id_procedura) {

        $dql = "SELECT "
                . "rich.id as id_operazione, "
                . "controllo.data_inizio_controlli as data_controllo, "
                . "controllo.note as note_controllo, "
                . "controllo as ctr, "
                . "controllo.spese_ammesse as ammesse, "
                . "controllo.spese_rivalutazione as rivalutare, "
                . "controllo.spese_non_ammissibili non_ammesse, "
                . "es.descrizione as esito, "
                . "controllo.data_validazione as data_val "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controllo "
                . "JOIN controllo.richiesta rich "
                . "JOIN controllo.esito es "
                . "JOIN rich.procedura proc "
                . "JOIN proc.asse asse "
                . "WHERE asse.id <> 8 ";
        ;

        if ($id_procedura != 'all') {
            $query .= " AND proc.id = {$id_procedura} ";
        }

        $q = $this->getEntityManager()->createQuery();

        $dql .= "ORDER BY rich.id ASC ";

        $q->setDQL($dql);


        $res = $q->getResult();

        return $res;
    }

    public function estrazioneUniversoStabilita() {

        $sql = "select
            rich.id as id_progetto,
            coalesce(concat(prot.registro_pg, '/',prot.`anno_pg`,'/',prot.`num_pg`), rich.id) as protocollo,
            rich.titolo as titolo_progetto,
            istr.codice_cup as cup,
            assi.titolo,
            proc.titolo as titolo_procedura,
            ap.numero as numero_atto,
            sogg.denominazione as Beneficiario,
            istr.costo_ammesso as costo_ammesso,
            istr.contributo_ammesso as Contributo_ammesso,
            CASE WHEN dimProp.id IS NOT NULL 
                THEN dimProp.codice 
                WHEN dimProp.id IS NULL AND dimSogg.id IS NOT NULL  
                THEN dimSogg.codice 
                ELSE '-' END AS pmi,  
            man.data_mandato as data_liquidazione,
            m.descrizione as modalita_ultimo_pagamento
            from richieste rich
            join procedure_operative proc on proc.id = rich.procedura_id
            join atti as ap on ap.id = proc.atto_id
            join istruttorie_richieste istr on istr.richiesta_id = rich.id
            join attuazione_controllo_richieste atc on atc.richiesta_id = rich.id
            join proponenti prop on prop.richiesta_id = rich.id and prop.data_cancellazione is null and prop.mandatario = 1
            join soggetti sogg on sogg.id = prop.soggetto_id
            LEFT JOIN dimensioni_imprese dimProp on prop.dimensione_impresa_id = dimProp.id
            LEFT JOIN dimensioni_imprese dimSogg on sogg.dimensione_impresa_id = dimSogg.id
            join procedura_tipoaiuto mm on mm.procedura_id = proc.id
            join tipi_aiuti tpa on tpa.id = mm.tipoaiuto_id
            join richieste_protocollo prot on prot.richiesta_id = rich.id
            join assi on assi.id = proc.asse_id
            join pagamenti pag on pag.attuazione_controllo_richiesta_id = atc.id
            join modalita_pagamento m on pag.modalita_pagamento_id = m.id
            join mandati_pagamenti man on pag.mandato_pagamento_id = man.id
            where prot.tipo = 'FINANZIAMENTO' 
            and m.codice in ('SALDO_FINALE','UNICA_SOLUZIONE')
            and rich.data_cancellazione is null
            group by rich.id ";

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getControlliCampione($campione) {

        $dql = "SELECT controllo.id, proc.titolo, sogg.denominazione, esito.descrizione as esito_ctrl, controllo.data_validazione, controllo as controlloObj, "
                . "CASE WHEN rp.num_pg IS NOT NULL "
                . "THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg) "
                . "ELSE '-' END AS protocollo,  "
                . "CASE WHEN dimProp.id IS NOT NULL "
                . "THEN dimProp.codice "
                . "WHEN dimProp.id IS NULL AND dimSogg.id IS NOT NULL " 
                . "THEN dimSogg.codice "
                . "ELSE '-' END AS pmi,  "
                . "at.numero as atto_num,  "
                . "concat(adr.via,' ',adr.numero_civico,' ',com.denominazione, '(', prov.sigla_automobilistica, ')') AS sede_intervento, "
                . "concat(sogg.via,' ',sogg.civico,' ',com2.denominazione, '(', prov2.sigla_automobilistica, ')') AS sede_legale "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controllo "
                . "JOIN controllo.richiesta rich "
                . "JOIN rich.procedura proc "
                . "JOIN proc.atto at "
                . "LEFT JOIN rich.attuazione_controllo atc "
                . "LEFT JOIN atc.pagamenti pag "
                . "LEFT JOIN rich.proponenti prop "
                . "LEFT JOIN prop.sedi seop "
                . "LEFT JOIN seop.sede se "
                . "LEFT JOIN se.indirizzo adr "
                . "LEFT JOIN adr.comune com "
                . "LEFT JOIN com.provincia prov "
                . "LEFT JOIN rich.richieste_protocollo rp WITH rp instance of ProtocollazioneBundle:RichiestaProtocolloFinanziamento "
                . "LEFT JOIN prop.soggetto sogg "
                . "LEFT JOIN prop.dimensione_impresa dimProp "
                . "LEFT JOIN sogg.dimensione_impresa dimSogg "
                . "JOIN sogg.comune com2 "
                . "JOIN com2.provincia prov2 "
                . "LEFT JOIN controllo.esito esito "
                . "JOIN proc.asse asse "
                . "JOIN controllo.campione cmp "
                . "WHERE cmp.id = $campione";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getResult();

        return $res;
    }

}
