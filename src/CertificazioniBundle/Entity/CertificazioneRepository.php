<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * CertificazioneRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CertificazioneRepository extends EntityRepository {

	public function findPrecedentiCertificazioniInviate($certificazione) {
		if (!is_null($certificazione->getDataPropostaAdg())) {
			$data = $certificazione->getDataPropostaAdg()->format('Y-m-d h:i:s');
		}
		else {
			$dataObj = new \DateTime();
			$data = $dataObj->format('Y-m-d h:i:s');
		}
		$dql = "SELECT c FROM CertificazioniBundle:Certificazione c "
				. "JOIN c.stato s "
				. "WHERE s.codice in ('CERT_INVIATA', 'CERT_APPROVATA') "
				. "AND c.id != {$certificazione->getId()} "
				. "AND c.data_proposta_adg < '{$data}' "
				. "ORDER BY c.data_proposta_adg ASC";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		$res = $q->getResult();

		return $res;
	}

	public function getCertificazioniSenzaChiusura($id_chiusura) {
		$dql = "SELECT c FROM CertificazioniBundle:Certificazione c "
				. "JOIN c.stato s "
				. "LEFT JOIN c.chiusura ch "
				. "WHERE s.codice = 'CERT_APPROVATA' AND (ch.id is null OR ch.id = $id_chiusura ) "
				. "ORDER BY c.data_proposta_adg ASC ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		$res = $q->getResult();

		return $res;
	}

	public function getCertificazioniApprovate() {
		$dql = "SELECT c FROM CertificazioniBundle:Certificazione c "
				. "JOIN c.stato s "
				. "LEFT JOIN c.chiusura ch "
				. "WHERE s.codice = 'CERT_APPROVATA' "
				. "ORDER BY c.data_proposta_adg ASC ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		$res = $q->getResult();

		return $res;
	}

	public function getImportiCertificatiPerAsse($id_certificazione, $asse) {
		$dql = "SELECT SUM(cp.importo - coalesce(cp.importo_taglio, 0)) FROM AttuazioneControlloBundle:Pagamento p "
				. "JOIN p.certificazioni cp "
				. "JOIN cp.certificazione c "
				. "JOIN p.attuazione_controllo_richiesta atc "
				. "JOIN atc.richiesta rich "
				. "JOIN rich.procedura proc "
				. "JOIN proc.asse ax "
				. "WHERE c.id = $id_certificazione AND ax.codice = '$asse' ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		$res = $q->getScalarResult();
		$importo_totale = $res[0][1];
		return $importo_totale;
	}

	public function getImportiCertificatiPerAsseAnnoContabile($id_certificazione, $asse, $anno_contabile) {
		$dql = "SELECT SUM(cp.importo - coalesce(cp.importo_taglio, 0)) FROM AttuazioneControlloBundle:Pagamento p "
				. "JOIN p.certificazioni cp "
				. "JOIN cp.certificazione c "
				. "JOIN p.attuazione_controllo_richiesta atc "
				. "JOIN atc.richiesta rich "
				. "JOIN rich.procedura proc "
				. "JOIN proc.asse ax "
				. "WHERE c.id = $id_certificazione AND ax.codice = '$asse' AND c.anno_contabile = $anno_contabile";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		$res = $q->getScalarResult();
		$importo_totale = $res[0][1];
		return $importo_totale;
	}

	public function getImportiCertificatiPerAsseStrumentiFinanziari($id_certificazione, $asse) {
		$dql = "SELECT SUM(cp.importo - coalesce(cp.importo_taglio, 0)) FROM AttuazioneControlloBundle:Pagamento p "
				. "JOIN p.certificazioni cp "
				. "JOIN cp.certificazione c "
				. "JOIN p.attuazione_controllo_richiesta atc "
				. "JOIN atc.richiesta rich "
				. "JOIN rich.procedura proc "
				. "JOIN proc.asse ax "
				. "WHERE c.id = $id_certificazione AND ax.codice = '$asse' AND cp.strumento_finanziario = 1";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		$res = $q->getScalarResult();
		$importo_totale = $res[0][1];
		return $importo_totale;
	}

	public function getImportiCertificatiPerAsseAiutiStato($id_certificazione, $asse) {
		$dql = "SELECT SUM(cp.importo - coalesce(cp.importo_taglio, 0)) FROM AttuazioneControlloBundle:Pagamento p "
				. "JOIN p.certificazioni cp "
				. "JOIN cp.certificazione c "
				. "JOIN p.attuazione_controllo_richiesta atc "
				. "JOIN atc.richiesta rich "
				. "JOIN rich.procedura proc "
				. "JOIN proc.asse ax "
				. "WHERE c.id = $id_certificazione AND ax.codice = '$asse' AND cp.aiuto_di_stato = 1";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		$res = $q->getScalarResult();
		$importo_totale = $res[0][1];
		return $importo_totale;
	}

	public function getPagamentiCertificati($id_certificazione) {

		$dql = "SELECT "
				. "rich.id as id_operazione, "
				. "pag.id as id_pagamento, "
				. "mod.descrizione as causale, "
				. "sogg.denominazione as beneficiario_soggetto, "
				. "certpag.importo as importo_proposto, "
				. "proc.titolo as titolo_procedura, "
				. "rich.titolo as titolo_progetto, "
				. "asse.titolo as asse_prioritario, "
				. "CASE WHEN rich.aiuto_stato_progetto IS NOT NULL "
				. "THEN rich.aiuto_stato_progetto "
				. "ELSE proc.aiuto_stato END as aiuto_di_stato, "
				. "certpag.aiuto_di_stato as anticipi_aiuto_di_stato, "
				. "certpag.strumento_finanziario as strumento_finanziario, "
				. "pag.importo_certificato as importo_certificato, "
				. "pag.importo_rendicontato as importo_rendicontato, "
				. "pag.importo_rendicontato_ammesso as importo_rendicontato_ammesso, "
				. "mp.numero_mandato as numero_mandato_pagamento, "
				. "mp.data_mandato as data_mandato_pagamento, "
				. "al.numero as numero_atto_liquidazione, "
				. "al.data as data_atto_liquidazione, "
				. "controlli.id as controllo, "
				. "coalesce(esito_ctrl.descrizione, '-') as esito_controllo, "
                        	. "controlli2.id as controllo2, "
				. "coalesce(esito_ctrl2.descrizione, '-') as esito_controllo2, "
                                . "ist.codice_cup as cup "
				. "FROM CertificazioniBundle:CertificazionePagamento certpag "
				. "JOIN certpag.pagamento pag "
				. "LEFT JOIN pag.mandato_pagamento mp "
				. "LEFT JOIN mp.atto_liquidazione al "
				. "JOIN pag.modalita_pagamento mod "
				. "JOIN pag.attuazione_controllo_richiesta ac "
				. "JOIN ac.richiesta rich "
                                . "JOIN rich.istruttoria ist "
				. "JOIN rich.proponenti prop "
				. "JOIN prop.soggetto sogg "
				. "JOIN pag.stato s "
				. "JOIN rich.procedura proc "
				. "LEFT JOIN rich.controlli controlli WITH controlli.tipologia = 'STANDARD'"
                                . "LEFT JOIN rich.controlli controlli2 WITH controlli2.tipologia = 'PUNTUALE'"
				. "JOIN proc.asse asse "
				. "JOIN certpag.certificazione cert "
				. "LEFT JOIN controlli.esito esito_ctrl "
                                . "LEFT JOIN controlli2.esito esito_ctrl2 "
				. "WHERE cert.id = " . $id_certificazione;

		$dql .= " GROUP BY pag.id ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		return $q->getResult();
	}
    
    public function getSommaImportiStrumenti($id_asse) {
        $dql = "SELECT SUM(coalesce(perc.importo, 0)) FROM AttuazioneControlloBundle:PagamentiPercettori perc "
				. "JOIN perc.pagamento richP "
                . "JOIN richP.richiesta rich "
				. "JOIN rich.procedura proc "
				. "JOIN proc.asse ax "
                . "WHERE ax.id = $id_asse AND proc INSTANCE OF SfingeBundle:IngegneriaFinanziaria ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		$res = $q->getScalarResult();
		$importo_totale = $res[0][1];
        return $importo_totale;
        
    }

}
