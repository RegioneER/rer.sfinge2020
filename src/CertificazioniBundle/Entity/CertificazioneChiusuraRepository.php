<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CertificazioneChiusuraRepository extends EntityRepository {

    public function getChiusureLavorabili() {
        $dql = "SELECT ch FROM CertificazioniBundle:CertificazioneChiusura ch "
                . "JOIN ch.stato s "
                . "WHERE s.codice IN ('CHI_LAVORAZIONE','CHI_BLOCCATA') ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getOneOrNullResult();
        return $res;
    }

    public function getImportiCertificatiPerAsse($id_chiusura, $asse) {
        $dql = "SELECT SUM(cp.importo - coalesce(cp.importo_taglio, 0)) FROM AttuazioneControlloBundle:Pagamento p 
			JOIN p.certificazioni cp
			JOIN cp.certificazione c
			JOIN c.chiusura ch
			JOIN p.attuazione_controllo_richiesta atc
			JOIN atc.richiesta rich
			JOIN rich.procedura proc
			JOIN proc.asse ax
			WHERE ch.id = :chiusura AND ax.codice = :asse ";

        $q = $this
                ->getEntityManager()
                ->createQuery($dql)
                ->setParameter('chiusura', $id_chiusura)
                ->setParameter('asse', $asse);

        $res = $q->getScalarResult();
        $importo_totale = $res[0][1];
        return $importo_totale;
    }

    public function getImportiCertificatiPerAsseStrumentiFinanziari($id_chiusura, $asse) {
        $dql = "SELECT SUM(cp.importo - coalesce(cp.importo_taglio, 0)) FROM AttuazioneControlloBundle:Pagamento p "
                . "JOIN p.certificazioni cp "
                . "JOIN cp.certificazione c "
                . "JOIN c.chiusura ch "
                . "JOIN p.attuazione_controllo_richiesta atc "
                . "JOIN atc.richiesta rich "
                . "JOIN rich.procedura proc "
                . "JOIN proc.asse ax "
                . "WHERE ch.id = $id_chiusura AND ax.codice = '$asse' AND cp.strumento_finanziario = 1";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getScalarResult();
        $importo_totale = $res[0][1];
        return $importo_totale;
    }

    public function getImportiCertificatiPerAsseAiutiStato($id_chiusura, $asse) {
        $dql = "SELECT SUM(cp.importo - coalesce(cp.importo_taglio, 0)) FROM AttuazioneControlloBundle:Pagamento p "
                . "JOIN p.certificazioni cp "
                . "JOIN cp.certificazione c "
                . "JOIN c.chiusura ch "
                . "JOIN p.attuazione_controllo_richiesta atc "
                . "JOIN atc.richiesta rich "
                . "JOIN rich.procedura proc "
                . "JOIN proc.asse ax "
                . "WHERE ch.id = $id_chiusura AND ax.codice = '$asse' AND cp.aiuto_di_stato = 1";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getScalarResult();
        $importo_totale = $res[0][1];
        return $importo_totale;
    }

    public function getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, $asse, $codice_irregolarita = null) {

        $dql = "SELECT cp.id FROM CertificazioniBundle:CertificazionePagamento cp "
                . "JOIN cp.pagamento p "
                . "JOIN cp.certificazione c "
                . "JOIN c.chiusura ch "
                . "JOIN p.attuazione_controllo_richiesta atc "
                . "JOIN atc.richiesta rich "
                . "JOIN rich.procedura proc "
                . "LEFT JOIN atc.revoca rev "
                . "JOIN proc.asse ax ";

        if (!is_null($codice_irregolarita)) {
            $dql .= "JOIN rev.tipo_irregolarita irr ";
        }

        $dql .= "WHERE cp.importo < 0 AND ch.id = $id_chiusura AND ax.codice = '$asse' AND cp.recupero = 1 AND cp.irregolarita = 1 ";

        if (!is_null($codice_irregolarita)) {
            $dql .= "AND irr.codice = '$codice_irregolarita' ";
        }

        $dql .= "GROUP BY cp.id  ";

        $dqlFinale = "SELECT ABS(SUM(cp2.importo_irregolare)) FROM CertificazioniBundle:CertificazionePagamento cp2  WHERE cp2.id IN (" . $dql . ")";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dqlFinale);

        $importo_totale = $q->getSingleScalarResult();
        return $importo_totale;
    }

    public function getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, $asse, $codice_irregolarita = null) {

        $dql = "SELECT cp.id FROM CertificazioniBundle:CertificazionePagamento cp "
                . "JOIN cp.pagamento p "
                . "JOIN cp.certificazione c "
                . "JOIN c.chiusura ch "
                . "JOIN p.attuazione_controllo_richiesta atc "
                . "JOIN atc.richiesta rich "
                . "JOIN rich.procedura proc "
                . "LEFT JOIN atc.revoca rev "
                . "JOIN proc.asse ax ";

        if (!is_null($codice_irregolarita)) {
            $dql .= "JOIN rev.tipo_irregolarita irr ";
        }

        $dql .= "WHERE cp.importo < 0 AND ch.id = $id_chiusura AND ax.codice = '$asse' AND cp.ritiro = 1 AND cp.irregolarita = 1 ";

        if (!is_null($codice_irregolarita)) {
            $dql .= "AND irr.codice = '$codice_irregolarita' ";
        }

        $dql .= "GROUP BY cp.id  ";

        $dqlFinale = "SELECT ABS(SUM(cp2.importo_irregolare)) FROM CertificazioniBundle:CertificazionePagamento cp2  WHERE cp2.id IN (" . $dql . ")";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dqlFinale);

        $importo_totale = $q->getSingleScalarResult();
        return $importo_totale;
    }

    public function getImportiCertificatiPerAsseRevoche($id_chiusura, $asse, $codice_irregolarita = array()) {
        $dql = "SELECT rev.id FROM AttuazioneControlloBundle:Revoche\Revoca rev "
                . "JOIN rev.attuazione_controllo_richiesta atc "
                . "JOIN atc.pagamenti p "
                . "JOIN atc.richiesta rich "
                . "JOIN p.certificazioni cp "
                . "JOIN cp.certificazione c "
                . "JOIN c.chiusura ch "
                . "JOIN rich.procedura proc "
                . "JOIN proc.asse ax ";

        if (count($codice_irregolarita) > 0) {
            $dql .= "JOIN rev.tipo_irregolarita irr ";
        }

        $dql .= "WHERE ch.id = $id_chiusura AND ax.codice = '$asse'";

        if (!is_null($codice_irregolarita)) {
            $dql .= "AND irr.codice IN (" . implode(',', $codice_irregolarita) . ") ";
        }

        $dql .= "GROUP BY rev.id  ";

        $dqlFinale = "SELECT SUM(rev2.contributo) FROM AttuazioneControlloBundle:Revoche\Revoca rev2  WHERE rev2.id IN (" . $dql . ")";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dqlFinale);

        $importo_totale = $q->getSingleScalarResult();
        return $importo_totale;
    }

    public function getRevocheInvioContiAsse($id_chiusura, $asse, $taglio_ada = false, $art137 = false) {
        $dql = "SELECT SUM(rev.contributo_ada) FROM AttuazioneControlloBundle:Revoche\Revoca rev "
                . "JOIN rev.chiusura ch "
                . "JOIN rev.attuazione_controllo_richiesta atc "
                . "JOIN atc.richiesta rich "
                . "JOIN rich.procedura proc "
                . "JOIN proc.asse ax "
                . "WHERE ch.id = $id_chiusura AND ax.codice = '$asse' AND rev.invio_conti = 1 ";

        if ($taglio_ada == true) {
            $dql .= 'AND rev.taglio_ada = 1 ';
        }

        if ($art137 == true) {
            $dql .= 'AND rev.articolo_137 = 1 ';
        }

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getScalarResult();
        $importo_totale = $res[0][1];
        return $importo_totale;
    }

    public function getRevocheInvioContiAsse137($id_chiusura, $asse, $taglio_ada = false, $art137 = false) {
        $dql = "SELECT SUM(rev.contributo) FROM AttuazioneControlloBundle:Revoche\Revoca rev "
                . "JOIN rev.chiusura ch "
                . "JOIN rev.attuazione_controllo_richiesta atc "
                . "JOIN atc.richiesta rich "
                . "JOIN rich.procedura proc "
                . "JOIN proc.asse ax "
                . "WHERE ch.id = $id_chiusura AND ax.codice = '$asse' AND rev.invio_conti = 1 ";

        if ($taglio_ada == true) {
            $dql .= 'AND rev.taglio_ada = 1 ';
        }

        if ($art137 == true) {
            $dql .= 'AND rev.articolo_137 = 1 ';
        }

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getScalarResult();
        $importo_totale = $res[0][1];
        return $importo_totale;
    }

    public function getImportiDecertificatiPrecedente($id_chiusura, $maxAnnoCont, $tipo, $anno) {

        $dql = "SELECT cp.id FROM CertificazioniBundle:CertificazionePagamento cp "
                . "JOIN cp.pagamento p "
                . "JOIN cp.certificazione c "
                . "JOIN c.chiusura ch ";

        $dql .= "WHERE cp.importo < 0 AND ch.id <= $id_chiusura "
                . "AND cp.anno_contabile_precedente is not null "
                . "AND cp.anno_contabile_precedente = {$anno}";

        if ($tipo == 'RIT') {
            $dql .= " AND cp.ritiro = 1 ";
        } elseif ($tipo == 'REC') {
            $dql .= " AND cp.recupero = 1 ";
        }

        $dql .= "GROUP BY cp.id  ";

        $dqlFinale = "SELECT  cp2.anno_contabile_precedente as anno, '{$tipo}' as tipo, ABS(SUM(cp2.importo_irregolare)) as importo, cp2.segnalazione_ada "
                . "FROM CertificazioniBundle:CertificazionePagamento cp2  "
                . "JOIN cp2.certificazione cer "
                . "WHERE cp2.id IN (" . $dql . ") AND cer.anno_contabile = {$maxAnnoCont} ";
        $dqlFinale .= "GROUP BY cp2.anno_contabile_precedente, cp2.segnalazione_ada ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dqlFinale);

        $importo_totale = $q->getResult();
        return $importo_totale;
    }

    public function getMaxAnnoContabileDaChiusura($id_chiusura) {

        $dql = "SELECT MAX(cert.anno_contabile) "
                . "FROM CertificazioniBundle:Certificazione cert "
                . "JOIN cert.chiusura ch "
                . "WHERE ch.id = {$id_chiusura} ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getSingleScalarResult();

        return $res;
    }

    public function getMaxAnnoCertificazioneDaChiusura($id_chiusura) {

        $dql = "SELECT MAX(cert.anno) "
                . "FROM CertificazioniBundle:Certificazione cert "
                . "JOIN cert.chiusura ch "
                . "WHERE ch.id = {$id_chiusura} ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getSingleScalarResult();

        return $res;
    }

    public function getMinAnnoCertificazioneDaChiusura($id_chiusura) {

        $dql = "SELECT MIN(cert.anno) "
                . "FROM CertificazioniBundle:Certificazione cert "
                . "JOIN cert.chiusura ch "
                . "WHERE ch.id = {$id_chiusura} ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getSingleScalarResult();

        return $res;
    }

    public function getRevocheInvioContiAsseApp1($id_chiusura, $asse, $taglio_ada = false, $art137 = false) {
        $dql = "SELECT SUM(rev.contributo) FROM AttuazioneControlloBundle:Revoche\Revoca rev "
                . "JOIN rev.chiusura ch "
                . "JOIN rev.attuazione_controllo_richiesta atc "
                . "JOIN atc.richiesta rich "
                . "JOIN rich.procedura proc "
                . "JOIN proc.asse ax "
                . "WHERE ch.id = $id_chiusura AND ax.codice = '$asse' AND rev.invio_conti = 1 ";

        if ($taglio_ada == true) {
            $dql .= 'AND rev.taglio_ada = 1 ';
        }

        if ($art137 == true) {
            $dql .= 'AND rev.articolo_137 = 1 ';
        }

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getScalarResult();
        $importo_totale = $res[0][1];
        return $importo_totale;
    }

    public function getImportiCompensati($id_chiusura, $tipo, $asse) {

        $dql = "SELECT SUM(cp.importo_compensazione) FROM CertificazioniBundle:CompensazionePagamento cp "
                . "JOIN cp.pagamento p "
                . "JOIN p.attuazione_controllo_richiesta atc "
                . "JOIN atc.richiesta rich "
                . "JOIN rich.procedura proc "
                . "JOIN proc.asse ax "
                . "JOIN cp.chiusura ch ";

        $dql .= "WHERE cp.importo_compensazione <> 0 AND ch.id = $id_chiusura AND ax.codice = '$asse' ";

        if ($tipo == 'RIT') {
            $dql .= " AND cp.ritiro = 1 ";
        } elseif ($tipo == 'REC') {
            $dql .= " AND cp.recupero = 1 ";
        }

        $dql .= "GROUP BY ax.id  ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getOneOrNullResult();
        $importo_totale = $res[1];
        return $importo_totale;
    }

    public function getImportiCompensatiPrecedente($id_chiusura, $maxAnnoCont, $tipo) {

        $dql = "SELECT cp.anno_contabile as anno, '{$tipo}_CMP' as tipo, ABS(SUM(cp.importo_compensazione)) as importo "
                . "FROM CertificazioniBundle:CompensazionePagamento cp  "
                . "JOIN cp.pagamento p "
                . "JOIN cp.chiusura ch ";

        $dql .= "WHERE cp.importo_compensazione <> 0 "
                . "AND ch.id = $id_chiusura "
                . "AND cp.anno_contabile is not null "
                . "AND cp.anno_contabile <= {$maxAnnoCont} ";

        if ($tipo == 'RIT') {
            $dql .= " AND cp.ritiro = 1 ";
        } elseif ($tipo == 'REC') {
            $dql .= " AND cp.recupero = 1 ";
        }

        $dql .= "GROUP BY cp.anno_contabile  ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $importo_totale = $q->getResult();
        return $importo_totale;
    }
    
     public function getImportiCompensatiPrecedenteAda($id_chiusura, $maxAnnoCont, $tipo) {

        $dql = "SELECT cp.anno_contabile as anno, '{$tipo}_CMP_ADA' as tipo, ABS(SUM(cp.importo_compensazione)) as importo, coalesce(cp.taglio_ada,0) as ada "
                . "FROM CertificazioniBundle:CompensazionePagamento cp  "
                . "JOIN cp.pagamento p "
                . "JOIN cp.chiusura ch ";

        $dql .= "WHERE cp.importo_compensazione <> 0 "
                . "AND cp.taglio_ada = 1 "
                . "AND ch.id = $id_chiusura "
                . "AND cp.anno_contabile is not null "
                . "AND cp.anno_contabile <= {$maxAnnoCont} ";

        if ($tipo == 'RIT') {
            $dql .= " AND cp.ritiro = 1 ";
        } elseif ($tipo == 'REC') {
            $dql .= " AND cp.recupero = 1 ";
        }

        $dql .= "GROUP BY cp.anno_contabile  ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $importo_totale = $q->getResult();
        return $importo_totale;
    }

}
