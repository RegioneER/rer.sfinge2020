<?php

namespace CertificazioniBundle\Repository;

use Doctrine\ORM\EntityRepository;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Model\SpesaCertificata;
use CertificazioniBundle\Entity\StatoCertificazione;

class CertificazionePagamentoRepository extends EntityRepository {

    public function getCertificazioniPagamentiAsse($id_certificazione, $id_asse) {
        $dql = "SELECT cp  
            FROM CertificazioniBundle:CertificazionePagamento cp
            JOIN cp.certificazione cert
			JOIN cp.pagamento pag
            JOIN pag.attuazione_controllo_richiesta ac 
            JOIN ac.richiesta rich 
			JOIN pag.stato s 
			JOIN rich.procedura proc 
            JOIN proc.asse asse 
			WHERE asse.id = :id_asse AND cert.id = :id_certificazione ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_asse", $id_asse);
        $q->setParameter("id_certificazione", $id_certificazione);

        $res = $q->getResult();

        return $res;
    }

    public function getAssiCertificazioneUtente($id_certificazione, $id_utente = null) {
        $dql = "SELECT asse  
            FROM SfingeBundle:Asse asse ";

        if (!is_null($id_utente)) {
            $dql .= "JOIN asse.permessi perm
                JOIN perm.utente u ";
        }

        $dql .= "JOIN SfingeBundle:Procedura proc WITH proc.asse = asse
            JOIN RichiesteBundle:Richiesta rich WITH rich.procedura = proc
            JOIN rich.attuazione_controllo ac
            JOIN ac.pagamenti pag
            JOIN CertificazioniBundle:CertificazionePagamento cp WITH cp.pagamento = pag
            JOIN cp.certificazione cert
            LEFT JOIN CertificazioniBundle:CertificazioneAsse ca WITH ca.certificazione = :id_certificazione AND ca.asse = asse
			WHERE cert.id = :id_certificazione ";

        if (!is_null($id_utente)) {
            $dql .= " AND u.id = :id_utente ";
        }

        $dql .= "AND ca.id is null
            GROUP BY asse.id ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        if (!is_null($id_utente)) {
            $q->setParameter("id_utente", $id_utente);
        }
        $q->setParameter("id_certificazione", $id_certificazione);
        $res = $q->getResult();

        return $res;
    }

    public function getAssiCertificazioneUtenteCompleta($id_certificazione, $id_utente = null) {
        $dql = "SELECT asse  
            FROM SfingeBundle:Asse asse 
            INNER JOIN CertificazioniBundle:Certificazione cert with cert = :id_certificazione
            JOIN asse.permessi perm
            JOIN perm.utente u
            LEFT JOIN CertificazioniBundle:CertificazioneAsse ca WITH ca.asse = asse and ca.certificazione = cert
            WHERE ca.id is NULL AND asse.id <> 8 
            AND u.id = coalesce(:id_utente, u.id)
            GROUP BY asse ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_utente", $id_utente);
        $q->setParameter("id_certificazione", $id_certificazione);
        $res = $q->getResult();

        return $res;
    }

    public function calcolaAssiCertificazione($id_certificazione) {
        $dql = "SELECT asse, SUM(coalesce(cp.importo, 0) - coalesce(cp.importo_taglio,0))  
            FROM SfingeBundle:Asse asse
            JOIN SfingeBundle:Procedura proc WITH proc.asse = asse
            JOIN RichiesteBundle:Richiesta rich WITH rich.procedura = proc
            JOIN rich.attuazione_controllo ac
            JOIN ac.pagamenti pag
            JOIN CertificazioniBundle:CertificazionePagamento cp WITH cp.pagamento = pag
            JOIN cp.certificazione cert
			WHERE cert.id = :id_certificazione
            GROUP BY asse.id ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_certificazione", $id_certificazione);

        $res = $q->getResult();

        return $res;
    }

    /**
     * @param Richiesta $richiesta
     * @return SpesaCertificata[]
     */
    public function findAllSpeseCertificate(Richiesta $richiesta) {
        $dql = "SELECT certificazionePagamento
                FROM CertificazioniBundle:CertificazionePagamento certificazionePagamento
                JOIN certificazionePagamento.certificazione certificazione
                JOIN certificazione.stato stato_certificazione
                JOIN certificazionePagamento.pagamento pagamento
                JOIN pagamento.attuazione_controllo_richiesta atc
                JOIN atc.richiesta richiesta
                WHERE richiesta = :richiesta
                    AND stato_certificazione.codice = :approvata";

        $certificazioni = $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('richiesta', $richiesta)
                ->setParameter('approvata', StatoCertificazione::CERT_APPROVATA)
                ->getResult();

        $speseCertificate = \array_map(function ($certificazionePagamento) {
            return new SpesaCertificata($certificazionePagamento);
        }, $certificazioni);

        return $speseCertificate;
    }

    public function findDecertificazioniPagamento($id_pagamento) {
        $dql = "SELECT cpag 
            FROM CertificazioniBundle:CertificazionePagamento cpag
            JOIN cpag.pagamento pag
            JOIN cpag.certificazione cert
			WHERE pag.id = :id_pagamento and cpag.importo < 0 ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_pagamento", $id_pagamento);

        $res = $q->getResult();

        return $res;
    }

    public function getPagamentiDecertificati($ricerca) {

        $dql = "SELECT certpag "
                . "FROM CertificazioniBundle:CertificazionePagamento certpag "
                . "JOIN certpag.pagamento pag "
                . "JOIN pag.attuazione_controllo_richiesta ac "
                . "JOIN ac.richiesta rich "
                . "JOIN rich.istruttoria ist "
                . "JOIN rich.proponenti prop "
                . "JOIN prop.soggetto sogg "
                . "JOIN rich.procedura proc "
                . "JOIN proc.asse asse "
                . "JOIN certpag.certificazione cert "
                . "WHERE certpag.importo < 0 ";
        ;

        $q = $this->getEntityManager()->createQuery();

        if (!is_null($ricerca->getCertificazione())) {
            $dql .= " AND cert.id = = :certificazione ";
            $q->setParameter("certificazione", $ricerca->getCertificazione()->getId());
        }

        if (!is_null($ricerca->getProcedura())) {
            $dql .= " AND proc.id = :procedura ";
            $q->setParameter("procedura", $ricerca->getProcedura());
        }

        if (!is_null($ricerca->getAsse())) {
            $dql .= " AND asse.id = :asse ";
            $q->setParameter("asse", $ricerca->getAsse());
        }

        if (!is_null($ricerca->getIdPagamento())) {
            $dql .= " AND pag.id = :id_pagamento ";
            $q->setParameter("id_pagamento", $ricerca->getIdPagamento());
        }

        if (!is_null($ricerca->getIdOperazione())) {
            $dql .= " AND rich.id = :id_operazione ";
            $q->setParameter("id_operazione", $ricerca->getIdOperazione());
        }

        if (!is_null($ricerca->getBeneficiario())) {
            $dql .= " AND (sogg.denominazione LIKE :beneficiario OR sogg.acronimo_laboratorio LIKE :beneficiario) ";
            $q->setParameter("beneficiario", "%" . $ricerca->getBeneficiario() . "%");
        }

        if (!is_null($ricerca->getCup())) {
            $dql .= " AND (ac.cup LIKE :cup OR ist.codice_cup LIKE :cup) ";
            $q->setParameter("cup", "%" . $ricerca->getCup() . "%");
        }

        $q->setDQL($dql);
        return $q;
    }

    public function getAnniContabili() {

        $dql = "SELECT distinct cert.anno_contabile
            FROM CertificazioniBundle:Certificazione cert
			WHERE cert.anno_contabile <> 0 ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        $res = $q->getResult();
        $array = array();
        foreach ($res as $ris) {
            $array[] = $ris['anno_contabile'];
        }

        return $array;
    }

    public function getRevocheDecertificatiChiusureConti($ricerca) {

        $dql = "SELECT rev "
                . "FROM AttuazioneControlloBundle:Revoche\Revoca rev "
                . "JOIN rev.attuazione_controllo_richiesta ac "
                . "JOIN ac.richiesta rich "
                . "JOIN rich.istruttoria ist "
                . "JOIN rich.proponenti prop "
                . "JOIN prop.soggetto sogg "
                . "JOIN rich.procedura proc "
                . "JOIN proc.asse asse "
                . "JOIN rev.chiusura ch "
                . "WHERE rev.invio_conti = 1 ";
        ;

        $q = $this->getEntityManager()->createQuery();

        if (!is_null($ricerca->getProcedura())) {
            $dql .= " AND proc.id = :procedura ";
            $q->setParameter("procedura", $ricerca->getProcedura());
        }

        if (!is_null($ricerca->getAsse())) {
            $dql .= " AND asse.id = :asse ";
            $q->setParameter("asse", $ricerca->getAsse());
        }

        if (!is_null($ricerca->getIdOperazione())) {
            $dql .= " AND rich.id = :id_operazione ";
            $q->setParameter("id_operazione", $ricerca->getIdOperazione());
        }

        if (!is_null($ricerca->getBeneficiario())) {
            $dql .= " AND (sogg.denominazione LIKE :beneficiario OR sogg.acronimo_laboratorio LIKE :beneficiario) ";
            $q->setParameter("beneficiario", "%" . $ricerca->getBeneficiario() . "%");
        }

        if (!is_null($ricerca->getCup())) {
            $dql .= " AND (ac.cup LIKE :cup OR ist.codice_cup LIKE :cup) ";
            $q->setParameter("cup", "%" . $ricerca->getCup() . "%");
        }

        $q->setDQL($dql);
        return $q;
    }

    public function getCertificazioniPagamenti($id_pagamento) {
        $dql = "SELECT cp  
            FROM CertificazioniBundle:CertificazionePagamento cp
            JOIN cp.certificazione cert
			JOIN cp.pagamento pag
			WHERE pag.id = :id_pagamento";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_pagamento", $id_pagamento);

        $res = $q->getResult();

        return $res;
    }

    public function getDecertificazioniPagamentiProcedura($id_procedura) {
        $dql = "SELECT cp as certificazione_p, "
                . "pag.id as id_pagamento, "
                . "rich.id as id_operazione, "
                . "cp.importo as importo_decertificato, "
                . "concat(cert.anno_contabile,'.',cert.numero) as certificazione, "
                . "cp.ritiro as ritiro, "
                . "cp.recupero as recupero, "
                . "cp.articolo_137 as articolo, "
                . "cp.segnalazione_ada as ada, "
                . "cp.nota_decertificazione as nota "
                . "FROM CertificazioniBundle:CertificazionePagamento cp "
                . "JOIN cp.certificazione cert "
                . "JOIN cp.pagamento pag "
                . "JOIN pag.attuazione_controllo_richiesta ac "
                . "JOIN ac.richiesta rich "
                . "JOIN rich.procedura proc "
                . "WHERE cp.importo < 0 ";

        if ($id_procedura != 'all') {
            $dql .= " AND proc.id = {$id_procedura} ";
        }

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $res = $q->getResult();

        return $res;
    }

}
