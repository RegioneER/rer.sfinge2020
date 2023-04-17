<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use Doctrine\ORM\EntityRepository;

class ControlloCampioneRepository extends EntityRepository {

    public function getImpreseCampionate($procedura) {
        $dql = "SELECT count(controlli) as num_campionate "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controlli "
                . "JOIN controlli.richiesta rich "
                . "JOIN controlli.campione cmp "
                . "WHERE cmp.id = $procedura ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getSingleScalarResult();

        return $res;
    }

    public function getImpreseControllate($procedura) {
        $dql = "SELECT count(controlli) as num_campionate "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controlli "
                . "JOIN controlli.richiesta rich "
                . "JOIN controlli.campione cmp "
                . "WHERE cmp.id = $procedura AND controlli.esito is not null";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getSingleScalarResult();

        return $res;
    }

    public function getCampioniConClAmmesseSenzaEsito($procedura) {

        $dql = "SELECT count(controlli) as num_campionate "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controlli "
                . "JOIN controlli.richiesta rich "
                . "JOIN controlli.campione cmp "
                . "WHERE cmp.id = $procedura AND controlli.esito is null";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getSingleScalarResult();

        return $res;
    }

    public function getCampioniByDate($campione) {
        $dataInizio = $campione->getDataInizio()->format('Y-m-d H:i:s');
        $dataFine = $campione->getDataTermine()->format('Y-m-d H:i:s');
        $dql = "SELECT rich.id as num_campionate "
                . "FROM RichiesteBundle:Richiesta rich "
                . "JOIN rich.attuazione_controllo atc "
                . "JOIN atc.pagamenti pag "
                . "JOIN pag.modalita_pagamento mod "
                . "JOIN pag.mandato_pagamento man "
                . "JOIN rich.proponenti prop "
                . "JOIN rich.procedura proc "
                . "LEFT JOIN prop.sedi seop "
                . "LEFT JOIN seop.sede se "
                . "LEFT JOIN se.indirizzo adr "
                . "LEFT JOIN adr.comune com "
                . "LEFT JOIN com.provincia prov "
                . "JOIN rich.richieste_protocollo rp "
                . "JOIN prop.soggetto sogg "
                . "JOIN sogg.comune com2 "
                . "JOIN com2.provincia prov2 "
                . "JOIN proc.asse asse "
                . "WHERE mod.codice IN ('SALDO_FINALE','UNICA_SOLUZIONE') AND man.data_mandato < '" . $dataFine . "' AND man.data_mandato > '" . $dataInizio . "' ";

        $dql .= " GROUP BY rich.id ORDER BY rich.id ASC ";
        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getResult();

        return count($res);
    }

    public function getCampioniByArrayId($campione) {
        $pre = $campione->getPreCampione();
        $preString = implode(',', $pre);
        $dql = "SELECT rich.id as num_campionate "
                . "FROM RichiesteBundle:Richiesta rich "
                . "JOIN rich.attuazione_controllo atc "
                . "JOIN atc.pagamenti pag "
                . "JOIN pag.modalita_pagamento mod "
                . "JOIN pag.mandato_pagamento man "
                . "JOIN rich.proponenti prop "
                . "JOIN rich.procedura proc "
                . "LEFT JOIN prop.sedi seop "
                . "LEFT JOIN seop.sede se "
                . "LEFT JOIN se.indirizzo adr "
                . "LEFT JOIN adr.comune com "
                . "LEFT JOIN com.provincia prov "
                . "JOIN rich.richieste_protocollo rp "
                . "JOIN prop.soggetto sogg "
                . "JOIN sogg.comune com2 "
                . "JOIN com2.provincia prov2 "
                . "JOIN proc.asse asse "
                . "WHERE mod.codice IN ('SALDO_FINALE','UNICA_SOLUZIONE') AND rich.id IN ($preString) ";

        $dql .= " GROUP BY rich.id ORDER BY rich.id ASC ";
        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getResult();

        return count($res);
    }

    public function getCampioniByDateRicerca($ricercaControlli) {
        $campione = $ricercaControlli->getCampione();
        if ($campione->getTipo() == 'AUTO') {
            $dataInizio = $campione->getDataInizio()->format('Y-m-d H:i:s');
            $dataFine = $campione->getDataTermine()->format('Y-m-d H:i:s');
            $where = "WHERE mod.codice IN ('SALDO_FINALE','UNICA_SOLUZIONE') AND man.data_mandato < '" . $dataFine . "' AND man.data_mandato > '" . $dataInizio . "' ";
        } elseif ($campione->getTipo() == 'FILE') {
            $pre = $campione->getPreCampione();
            $preString = implode(',', $pre);
            $where = "WHERE mod.codice IN ('SALDO_FINALE','UNICA_SOLUZIONE') AND rich.id IN ($preString) ";
        } else {
            $where = "WHERE mod.codice IN ('SALDO_FINALE','UNICA_SOLUZIONE')";
        }
        $dql = "SELECT rich as richiesta "
                . "FROM RichiesteBundle:Richiesta rich "
                . "JOIN rich.attuazione_controllo atc "
                . "JOIN atc.pagamenti pag "
                . "JOIN pag.modalita_pagamento mod "
                . "JOIN pag.mandato_pagamento man "
                . "JOIN rich.proponenti prop "
                . "JOIN rich.procedura proc "
                . "LEFT JOIN prop.sedi seop "
                . "LEFT JOIN seop.sede se "
                . "LEFT JOIN se.indirizzo adr "
                . "LEFT JOIN adr.comune com "
                . "LEFT JOIN com.provincia prov "
                . "JOIN rich.richieste_protocollo rp "
                . "JOIN prop.soggetto sogg "
                . "JOIN sogg.comune com2 "
                . "JOIN com2.provincia prov2 "
                . "JOIN proc.asse asse "
                . $where;

        $q = $this->getEntityManager()->createQuery();

        $utente = $ricercaControlli->getUtente();

        if (!is_null($ricercaControlli->getUtente())) {
            if ($ricercaControlli->getUtente()->isInvitalia() == true)
                $dql .= " AND proc.id IN(95,121,132) ";
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

        $dql .= " GROUP BY rich.id ORDER BY rich.id ASC ";

        $q->setDQL($dql);


        return $q;
    }

    public function estrazioneUniversoStabilitaByCampione($campione) {
        if ($campione->getTipoControllo() == 'STABILITA') {
            $dataInizio = $campione->getDataInizio()->format('Y-m-d H:i:s');
            $dataFine = $campione->getDataTermine()->format('Y-m-d H:i:s');
        }
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
            m.descrizione as modalita_ultimo_pagamento,
            DATE_FORMAT(man.data_mandato, '%d/%m/%Y') as data_pagamento
            from richieste rich
            join procedure_operative proc on proc.id = rich.procedura_id
            join atti as ap on ap.id = proc.atto_id
            join istruttorie_richieste istr on istr.richiesta_id = rich.id
            join attuazione_controllo_richieste atc on atc.richiesta_id = rich.id
            join proponenti prop on prop.richiesta_id = rich.id and prop.data_cancellazione is null and prop.mandatario = 1
            join soggetti sogg on sogg.id = prop.soggetto_id
            join procedura_tipoaiuto mm on mm.procedura_id = proc.id
            join tipi_aiuti tpa on tpa.id = mm.tipoaiuto_id
            join richieste_protocollo prot on prot.richiesta_id = rich.id
            join assi on assi.id = proc.asse_id
            join pagamenti pag on pag.attuazione_controllo_richiesta_id = atc.id
            join modalita_pagamento m on pag.modalita_pagamento_id = m.id
            join mandati_pagamenti man on pag.mandato_pagamento_id = man.id
            where prot.tipo = 'FINANZIAMENTO' 
            and m.codice in ('SALDO_FINALE','UNICA_SOLUZIONE')
            and rich.data_cancellazione is null ";
        
        if ($campione->getTipoControllo() == 'STABILITA') {
            $sql .= " AND man.data_mandato < '" . $dataFine . "' AND man.data_mandato > '" . $dataInizio . "' ";
        }


        $sql .= " GROUP BY rich.id ORDER BY rich.id ASC ";

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}
