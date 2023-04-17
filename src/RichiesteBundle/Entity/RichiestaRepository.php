<?php

namespace RichiesteBundle\Entity;

use DateTime;
use Doctrine\ORM\EntityRepository;
use BaseBundle\Entity\StatoRichiesta;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use GeoBundle\Entity\GeoComune;
use RichiesteBundle\Form\Entity\RicercaRichiesta;
use RichiesteBundle\Form\Entity\RicercaRichiestaProceduraPA;
use IstruttorieBundle\Form\Entity\RicercaIstruttoria;
use SfingeBundle\Entity\Procedura;
use Doctrine\ORM\QueryBuilder;
use MonitoraggioBundle\Form\Entity\RicercaProgetto;
use Doctrine\ORM\Query;

class RichiestaRepository extends EntityRepository {

	public function getRichiesteDaSoggettoCompleta($id_soggetto) {

		$dql = "SELECT DISTINCT rich FROM RichiesteBundle:Richiesta rich "
				. "LEFT JOIN rich.oggetti_richiesta orich "
				. "LEFT JOIN rich.proponenti prop1 "
				. "LEFT JOIN orich.proponenti prop2 "
				. "LEFT JOIN prop1.soggetto sogg1 "
				. "LEFT JOIN prop2.soggetto sogg2 "
				. "WHERE (sogg1.id = $id_soggetto OR sogg2.id = $id_soggetto) ";
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function getRichiesteDaSoggetto($id_soggetto, $id_procedura = null, $finestraTemporale = null) {

		$dql = "SELECT rich FROM RichiesteBundle:Richiesta rich "
				. "JOIN rich.proponenti prop "
				. "JOIN prop.soggetto sogg "
                . "JOIN rich.procedura proc "
				. "WHERE sogg.id = $id_soggetto AND (
					proc INSTANCE OF SfingeBundle:Bando OR 
					proc INSTANCE OF SfingeBundle:ManifestazioneInteresse OR
					proc INSTANCE OF SfingeBundle:ProceduraPA
				)";

		if (!is_null($id_procedura)) {
			$dql .= " AND proc.id = $id_procedura";
		}

		if (!is_null($finestraTemporale)) {
			$dql .= " AND rich.finestra_temporale = $finestraTemporale";
		}

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
	
	public function getRichiesteInviateDaSoggetto($id_soggetto, $id_procedura = null, $finestraTemporale = null) {

		$dql = "SELECT rich FROM RichiesteBundle:Richiesta rich "
				. "JOIN rich.proponenti prop "
				. "JOIN rich.stato s "
				. "JOIN prop.soggetto sogg "
				. "WHERE sogg.id = $id_soggetto AND s.codice IN ('PRE_INVIATA_PA','PRE_PROTOCOLLATA')";

		if (!is_null($id_procedura)) {
			$dql .= " AND rich.procedura = $id_procedura";
		}

		if (!is_null($finestraTemporale)) {
			$dql .= " AND rich.finestra_temporale = $finestraTemporale";
		}

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function countRichiesteInviateDaSoggetto(Richiesta $richiesta): int{
		return $this->createQueryBuilder('richiesta')
			->select('count(richiesta)')
			->join('richiesta.stato', 'stato')
			->join('richiesta.procedura', 'procedura')
			->join('richiesta.proponenti', 'mandatario', 'with', 'mandatario.mandatario = 1')
			->join('mandatario.soggetto', 'soggetto')
			->where(
				'procedura = :procedura',
				'soggetto = :soggetto',
				'stato.codice in (:stati)'
			)
			->setParameter('procedura', $richiesta->getProcedura())
			->setParameter('soggetto', $richiesta->getSoggetto())
			->setParameter('stati',[
				StatoRichiesta::PRE_PROTOCOLLATA,
				StatoRichiesta::PRE_INVIATA_PA,
			])
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * @param Procedura $procedura
	 * @param null $finestra_temporale
	 * @return int
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function countRichiesteProtocollateProcedura(Procedura $procedura, $finestra_temporale = null): int{
		$qb = $this->createQueryBuilder('richiesta');
		$expr = $qb->expr();

		$qb->select('count(richiesta.id)')
			->join('richiesta.procedura', 'procedura')
			->join('richiesta.stato', 'stato')
			->where(
				$expr->in('stato.codice', ':stati'),
				$expr->eq('procedura.id', ':id_procedura')
			)
			->setParameter('id_procedura', $procedura->getId())
			->setParameter('stati', [
				StatoRichiesta::PRE_PROTOCOLLATA,
			]);
		
		if ($finestra_temporale) {
			$qb->andWhere('richiesta.finestra_temporale = :idFinestra');
			$qb->setParameter('idFinestra', $finestra_temporale);
		}
		
		return $qb->getQuery()
			->getSingleScalarResult();
	}

	public function getProponentiOggettoRichiesta($id_richiesta) {

		$dql = "SELECT prop FROM RichiesteBundle:Proponente prop "
				. "JOIN prop.oggetto_richiesta orich "
				. "JOIN orich.richiesta rich "
				. "JOIN prop.soggetto sogg "
				. "WHERE rich.id = $id_richiesta";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function getProponentiRichiesta($id_richiesta) {

		$dql = "SELECT prop FROM RichiesteBundle:Proponente prop "
				. "JOIN prop.richiesta rich "
				. "JOIN prop.soggetto sogg "
				. "WHERE rich.id = $id_richiesta";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function getMandatarioRichiesta($id_richiesta) {

		$dql = "SELECT prop FROM RichiesteBundle:Proponente prop "
				. "JOIN prop.richiesta rich "
				. "JOIN prop.soggetto sogg "
				. "WHERE rich.id = $id_richiesta AND prop.mandatario = 1";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleResult();
	}

	public function getMandatarioRichiestaAt($id_richiesta) {

		$dql = "SELECT prop FROM RichiesteBundle:Proponente prop "
				. "JOIN prop.richiesta rich "
				. "JOIN prop.soggetto sogg "
				. "WHERE rich.id = $id_richiesta AND prop.mandatario = 1";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function getRichiesteVisibiliPA(\RichiesteBundle\Form\Entity\RicercaRichiesta $dati) {

		$dql = "SELECT rich.id, proc.titolo, proc.id as id_procedura, s.denominazione, rich.data_invio, ric_s.descrizione as stato, "
				. "CASE WHEN rp.num_pg IS NOT NULL "
				. "THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg) "
				. "ELSE '-' END AS protocollo "
				. "FROM RichiesteBundle:Richiesta rich "
				. "LEFT JOIN rich.richieste_protocollo rp "
				. "LEFT JOIN rich.proponenti prop "
				. "LEFT JOIN prop.soggetto s "
				. "JOIN rich.procedura proc "
				. "LEFT JOIN proc.stato_procedura proc_s "
				. "JOIN proc.asse asse "
				. "LEFT JOIN rich.stato ric_s "
				. "WHERE prop.mandatario = 1 AND rp INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento ";

		//nascondiamo la procedura ICT 360		
		$dql .= " AND proc.id <> 9";


		$q = $this->getEntityManager()->createQuery();

		$utente = $dati->getUtente();

		if (!$utente->hasRole("ROLE_SUPER_ADMIN")) {

			$dql .= " AND (proc_s.codice='CONCLUSO' OR proc.visibile_in_corso = 1) AND ric_s.codice IN ('PRE_INVIATA_PA','PRE_PROTOCOLLATA') ";

			if (!$utente->hasRole("ROLE_ADMIN_PA")) {
				$dql .= " AND ( ";
				$dql .= "proc.id in (select proc3.id from SfingeBundle:PermessiProcedura proc2 join proc2.procedura proc3 where proc2.utente={$utente->getId()}) ";
				$dql .= "OR proc.asse in (select asse3.id from SfingeBundle:PermessiAsse asse2 join asse2.asse asse3 where asse2.utente={$utente->getId()}))";
			}
		}

		if ($dati->getStato() != "") {
			$dql .= " AND rich.stato = :stato";
			$q->setParameter("stato", $dati->getStato()->getId());
		}

		if ($dati->getProcedura() != "") {
			$dql .= " AND rich.procedura = :procedura";
			$q->setParameter("procedura", $dati->getProcedura()->getId());
		}

		if ($dati->getTitoloProgetto() != "") {
			$dql .= " AND rich.titolo LIKE :titoloProgetto";
			$q->setParameter("titoloProgetto", "%" . $dati->getTitoloProgetto() . "%");
		}

		if ($dati->getProtocollo() != "") {
			$dql .= " AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo ";
			$q->setParameter("protocollo", "%" . $dati->getProtocollo() . "%");
		}

		if ($dati->getRagioneSocialeProponente() != "") {
			$dql .= " AND s.denominazione LIKE :denominazione";
			$q->setParameter("denominazione", "%" . $dati->getRagioneSocialeProponente() . "%");
		}

		if ($dati->getCodiceFiscaleProponente() != "") {
			$dql .= " AND s.codice_fiscale LIKE :codice_fiscale";
			$q->setParameter("codice_fiscale", "%" . $dati->getCodiceFiscaleProponente() . "%");
		}

		if ($dati->getFinestraTemporale() != "") {
			$dql .= " AND rich.finestra_temporale = :finestra";
			$q->setParameter("finestra", $dati->getFinestraTemporale());
		}
		
		if (!is_null($dati->getId())) {
			$dql .= " AND rich.id = :id_rich ";
			$q->setParameter("id_rich", $dati->getId());
		}
        
        if (!is_null($dati->getUtente())) {
            if( $dati->getUtente()->isInvitalia() == true ) {
                $dql .= " AND proc.id IN (95, 121, 132, 167) ";
            }
		}
		
		
		$dql .= " GROUP BY rich.id ";

		$dql .= " ORDER BY rich.data_invio DESC ";

		$q->setDQL($dql);

		return $q;
	}

	public function getRichiesteAt(\RichiesteBundle\Form\Entity\RicercaRichiesta $dati) {
		return $this->getRichiestePP($dati, 'AssistenzaTecnica');
	}

	public function getRichiesteIngIf(\RichiesteBundle\Form\Entity\RicercaRichiesta $dati) {
		return $this->getRichiestePP($dati, 'IngegneriaFinanziaria');
	}
	
	public function getRichiesteAcquisizione(\RichiesteBundle\Form\Entity\RicercaRichiesta $dati) {
		return $this->getRichiestePP($dati, 'Acquisizioni');
	}

	public function getRichiestePP(\RichiesteBundle\Form\Entity\RicercaRichiesta $dati, $tipoProceduraParticolare) {

		$dql = "SELECT rich FROM RichiesteBundle:Richiesta rich "
				. "LEFT JOIN rich.richieste_protocollo rp "
				. "LEFT JOIN rich.proponenti prop "
				. "LEFT JOIN prop.soggetto s "
				. "JOIN rich.procedura proc "
				. "LEFT JOIN proc.stato_procedura proc_s "
				. "JOIN proc.asse asse "
				. "LEFT JOIN rich.stato ric_s "
				. "WHERE proc INSTANCE OF SfingeBundle:$tipoProceduraParticolare";

		$q = $this->getEntityManager()->createQuery();

		$utente = $dati->getUtente();
		if (!$utente->isAbilitatoStrumentiFinanziari()) {

			$dql .= " AND (proc_s.codice='CONCLUSO' OR proc.visibile_in_corso = 1) AND ric_s.codice IN ('PRE_INVIATA_PA','PRE_PROTOCOLLATA') ";

			if (!$utente->hasRole("ROLE_ADMIN_PA")) {
				$dql .= " AND ( ";
				$dql .= "proc.id in (select proc3.id from SfingeBundle:PermessiProcedura proc2 join proc2.procedura proc3 where proc2.utente={$utente->getId()}) ";
				$dql .= "OR proc.asse in (select asse3.id from SfingeBundle:PermessiAsse asse2 join asse2.asse asse3 where asse2.utente={$utente->getId()}))";
			}
		}

		if ($dati->getStato() != "") {
			$dql .= " AND rich.stato = :stato";
			$q->setParameter("stato", $dati->getStato()->getId());
		}

		if ($dati->getProcedura() != "") {
			$dql .= " AND rich.procedura = :procedura";
			$q->setParameter("procedura", $dati->getProcedura()->getId());
		}

		if ($dati->getTitoloProgetto() != "") {
			$dql .= " AND rich.titolo LIKE :titoloProgetto";
			$q->setParameter("titoloProgetto", "%" . $dati->getTitoloProgetto() . "%");
		}

		if ($dati->getProtocollo() != "") {
			$dql .= " AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo ";
			$q->setParameter("protocollo", "%" . $dati->getProtocollo() . "%");
		}

		if ($dati->getRagioneSocialeProponente() != "") {
			$dql .= " AND s.denominazione LIKE :denominazione";
			$q->setParameter("denominazione", "%" . $dati->getRagioneSocialeProponente() . "%");
		}

		if ($dati->getCodiceFiscaleProponente() != "") {
			$dql .= " AND s.codice_fiscale LIKE :codice_fiscale";
			$q->setParameter("codice_fiscale", "%" . $dati->getCodiceFiscaleProponente() . "%");
		}

		$dql .= " GROUP BY rich.id ";

		$dql .= " ORDER BY rich.id DESC ";

		$q->setDQL($dql);
		return $q;
	}

	public function getTotaliPianiCosto($Richiesta_id, $Proponente_id = null) {
		$dql = "SELECT "
				. "(vpc.importo_anno_1) + "
				. "(vpc.importo_anno_2) + "
				. "(vpc.importo_anno_3) + "
				. "(vpc.importo_anno_4) + "
				. "(vpc.importo_anno_5) + "
				. "(vpc.importo_anno_6) + "
				. "(vpc.importo_anno_7) "
				. "FROM RichiesteBundle:Richiesta rich "
				. "JOIN voci_piano_costo vpc "
				. "JOIN piano_costo pc "
				. "WHERE rich.id = $Richiesta_id "
				. "AND pc.codice='TOT' "
		;
		if (!\is_null($Proponente_id)) {
			$dql .= " AND vpc.proponente = $Proponente_id";
		}
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function getRichiesteSenzaVersions() {

		$dql = "SELECT rich FROM RichiesteBundle:Richiesta rich "
				. "LEFT JOIN rich.stato s "
				. "LEFT JOIN rich.proponenti prop "
				. "LEFT JOIN prop.sedi sed "
				. "WHERE (prop.soggetto_version IS NULL or sed.sede_version IS NULL) AND s.codice = 'PRE_INVIATA_PA'";


		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function getRichiesteInIstruttoria(RicercaIstruttoria $ricercaIstruttoria) {

		$dql = "SELECT rich.id, rich.data_invio, proc.titolo, sv.denominazione, "
				. "CASE WHEN rp.num_pg IS NOT NULL "
				. "THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg) "
				. "ELSE '-' END AS protocollo, "
				. "CASE WHEN i.id IS NULL OR e.id IS NULL "
				. "THEN '-' "
				. "ELSE e.descrizione END AS esito, "
				. "CASE WHEN prgh.gestita = 0 AND stprgh.codice = 'PROROGA_PROTOCOLLATA' "
				. "THEN '1' "
				. "ELSE '0' END AS proroga_in_attesa, "
                . "CONCAT(asse.titolo, ': ', asse.descrizione) as asse_procedura, proc.titolo as titolo_procedura, "
                . "CAST(CASE WHEN istRich.esito IS NOT NULL "
                . "THEN istRich.data_verbalizzazione "
                . "ELSE :null END AS DATE) AS data_verbalizzazione, "
                . "CASE WHEN rich.data_invio IS NOT NULL AND istRich.esito IS NULL "
                . "THEN DATE_DIFF(CURRENT_DATE(), rich.data_invio) "
                . "WHEN rich.data_invio IS NOT NULL AND istRich.esito IS NOT NULL "
                . "THEN DATE_DIFF(istRich.data_verbalizzazione, rich.data_invio) "
                . "ELSE '-' END AS giorni_istruttoria, "
                . "CONCAT(persIstruttore.nome, ' ', persIstruttore.cognome) AS assegnatario "
            
				. "FROM RichiesteBundle:Richiesta rich "
				. "JOIN rich.stato s "
				. "JOIN rich.procedura proc "
				. "LEFT JOIN rich.istruttoria i "
				. "LEFT JOIN i.esito e "
				. "LEFT JOIN rich.proponenti prop "
				. "LEFT JOIN rich.richieste_protocollo rp "
				. "LEFT JOIN prop.soggetto sv "
				. "LEFT JOIN proc.stato_procedura proc_s "
				. "LEFT JOIN rich.attuazione_controllo atc "
				. "LEFT JOIN atc.proroghe prgh "
				. "LEFT JOIN prgh.stato stprgh "
                . "LEFT JOIN rich.istruttoria istRich "
                . "LEFT JOIN rich.assegnamenti_istruttoria assIstruttore WITH assIstruttore.attivo = 1 "
                . "LEFT JOIN assIstruttore.istruttore istruttore "
                . "LEFT JOIN istruttore.persona persIstruttore "
				. "JOIN proc.asse asse "
				. "WHERE s.codice = 'PRE_PROTOCOLLATA' AND prop.mandatario = 1 AND rp INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento"
		;

		$q = $this->getEntityManager()->createQuery();

		$utente = $ricercaIstruttoria->getUtente();
		if (!is_null($utente)) {

			if (!$utente->hasRole("ROLE_SUPER_ADMIN")) {

				$dql .= " AND (proc_s.codice='CONCLUSO' OR proc.visibile_in_corso = 1) AND s.codice IN ('PRE_INVIATA_PA','PRE_PROTOCOLLATA') ";

				if (!$utente->hasRole("ROLE_ADMIN_PA")) {
					$dql .= " AND ( ";
					$dql .= "proc.id in (select proc3.id from SfingeBundle:PermessiProcedura proc2 join proc2.procedura proc3 where proc2.utente={$utente->getId()}) ";
					$dql .= "OR proc.asse in (select asse3.id from SfingeBundle:PermessiAsse asse2 join asse2.asse asse3 where asse2.utente={$utente->getId()}))";
				}
			}
		}

		if (!is_null($ricercaIstruttoria->getProcedura())) {
			$dql .= " AND proc.id = :procedura ";
			$q->setParameter("procedura", $ricercaIstruttoria->getProcedura());
		}
		
		if (!is_null($ricercaIstruttoria->getId())) {
			$dql .= " AND rich.id = :id_rich ";
			$q->setParameter("id_rich", $ricercaIstruttoria->getId());
		}

		if (!is_null($ricercaIstruttoria->getCompletata())) {
			if ($ricercaIstruttoria->getCompletata()) {
				$dql .= " AND i.esito is not null ";
			} else {
				$dql .= " AND i.esito is null ";
			}
		}

		if (!is_null($ricercaIstruttoria->getCodiceFiscale())) {
			$dql .= " AND sv.codice_fiscale LIKE :cf ";
			$q->setParameter("cf", "%" . $ricercaIstruttoria->getCodiceFiscale() . "%");
		}

		if (!is_null($ricercaIstruttoria->getDenominazione())) {
			$dql .= " AND sv.denominazione LIKE :denominazione ";
			$q->setParameter("denominazione", "%" . $ricercaIstruttoria->getDenominazione() . "%");
			
		}

		if (!is_null($ricercaIstruttoria->getProtocollo())) {
			$dql .= " AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo ";
			$q->setParameter("protocollo", "%" . $ricercaIstruttoria->getProtocollo() . "%");
		}

		if (!\is_null($ricercaIstruttoria->getCup())) {
			$dql .= " AND i.richiedi_cup = 1 AND i.codice_cup IS NULL";
		}

		if (!is_null($ricercaIstruttoria->getFinestraTemporale())) {
			$dql .= " AND rich.finestra_temporale = :finestraTemporale";
			$q->setParameter("finestraTemporale", $ricercaIstruttoria->getFinestraTemporale());
		}

		if (!is_null($ricercaIstruttoria->getProrogaGestita())) {
			if ($ricercaIstruttoria->getProrogaGestita()) {
				$dql .= " AND prgh.gestita = 1 ";
			} else {
				$dql .= " AND prgh.gestita = 0 AND stprgh.codice = 'PROROGA_PROTOCOLLATA'";
			}
		}

        if (!is_null($ricercaIstruttoria->getIstruttoreCorrente())) {
            $dql .= " AND assIstruttore.istruttore = :istruttore AND assIstruttore.attivo = 1";
            $q->setParameter("istruttore", $ricercaIstruttoria->getIstruttoreCorrente()->getId());
        }
        
        if (!is_null($ricercaIstruttoria->getUtente())) {
            if( $ricercaIstruttoria->getUtente()->isInvitalia() == true ) {
                $dql .= " AND proc.id IN (95, 121, 132, 167) ";
            }
		}

        $q->setParameter("null", 'NULL');

		$dql .= " GROUP BY rich.id ";

		$dql .= " ORDER BY rich.data_invio DESC ";


		$q->setDQL($dql);
		return $q;
	}

	public function getRichiesteInIstruttoriaCipe(RicercaIstruttoria $ricercaIstruttoria) {

		$dql = "SELECT i "
				. "FROM IstruttorieBundle:IstruttoriaRichiesta i "
				. "JOIN i.richiesta rich "
				. "JOIN rich.stato s "
				. "JOIN rich.procedura proc "
				. "LEFT JOIN rich.proponenti prop "
				. "LEFT JOIN rich.richieste_protocollo rp "
				. "LEFT JOIN prop.soggetto_version sv "
				. "LEFT JOIN proc.stato_procedura proc_s "
				. "JOIN proc.asse asse "
				. "WHERE s.codice = 'PRE_PROTOCOLLATA' and i.richiedi_cup = 1 "
		;

		$q = $this->getEntityManager()->createQuery();

		$utente = $ricercaIstruttoria->getUtente();
		if (!is_null($utente)) {

			if (!$utente->hasRole("ROLE_SUPER_ADMIN")) {

				$dql .= " AND (proc_s.codice='CONCLUSO' OR proc.visibile_in_corso = 1) AND s.codice IN ('PRE_INVIATA_PA','PRE_PROTOCOLLATA') ";

				if (!$utente->hasRole("ROLE_ADMIN_PA")) {
					$dql .= " AND ((perm_p.id is not null AND perm_p.utente={$utente->getId()}) OR (perm_a.id is not null AND perm_a.utente={$utente->getId()}))";
				}
			}
		}

		if (!is_null($ricercaIstruttoria->getProcedura())) {
			$dql .= " AND proc.id = :procedura ";
			$q->setParameter("procedura", $ricercaIstruttoria->getProcedura());
		}

		if (!is_null($ricercaIstruttoria->getCompletata())) {
			if ($ricercaIstruttoria->getCompletata()) {
				$dql .= " AND i.esito is not null ";
			} else {
				$dql .= " AND i.esito is null ";
			}
		}

		if (!is_null($ricercaIstruttoria->getCodiceFiscale())) {
			$dql .= " AND sv.codice_fiscale LIKE :cf ";
			$q->setParameter("cf", "%" . $ricercaIstruttoria->getCodiceFiscale() . "%");
		}

		if (!is_null($ricercaIstruttoria->getDenominazione())) {
			$dql .= " AND sv.denominazione LIKE :denominazione ";
			$q->setParameter("denominazione", "%" . $ricercaIstruttoria->getDenominazione() . "%");
		}

		if (!is_null($ricercaIstruttoria->getProtocollo())) {
			$dql .= "AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo ";
			$q->setParameter("protocollo", "%" . $ricercaIstruttoria->getProtocollo() . "%");
		}

		if (!\is_null($ricercaIstruttoria->getCup())) {
			$dql .= "AND i.codice_cup IS NULL AND (i.UltimaRichiestaCupBatch IS NULL OR ( i.UltimaRichiestaCupBatch IS NOT NULL AND i.UltimaRichiestaCupBatchScarto IS NOT NULL ) )";
		}

		$q->setDQL($dql);

		$sql = $q->getSQL();
		return $q;
	}

	public function getRichiesteInviatePA() {

		$query = $this->getEntityManager()
				->createQuery(
						'SELECT r.id
				  FROM RichiesteBundle:Richiesta r
				  JOIN r.stato s WITH s.codice = :value
				  JOIN r.procedura p 
				  WHERE r.id NOT IN 
					 (SELECT rr.id
					  FROM ProtocollazioneBundle:RichiestaProtocolloFinanziamento rpf
					  JOIN rpf.richiesta rr)
				  ORDER BY r.data_invio ASC'
				)
				->setParameter('value', StatoRichiesta::PRE_INVIATA_PA);

		$result = $query->getResult();

		return $result;
	}

	public function getCostoAmmessoTotaleRichiesta($id_richiesta) {

		$dql = "SELECT SUM(COALESCE(vocepc.importo_ammissibile_anno_1,0)) + "
				. "SUM(COALESCE(vocepc.importo_ammissibile_anno_2,0)) + "
				. "SUM(COALESCE(vocepc.importo_ammissibile_anno_3,0)) + "
				. "SUM(COALESCE(vocepc.importo_ammissibile_anno_4,0)) + "
				. "SUM(COALESCE(vocepc.importo_ammissibile_anno_5,0)) + "
				. "SUM(COALESCE(vocepc.importo_ammissibile_anno_6,0)) + "
				. "SUM(COALESCE(vocepc.importo_ammissibile_anno_7,0)) FROM IstruttorieBundle:IstruttoriaVocePianoCosto vocepc "
				. "JOIN vocepc.voce_piano_costo voce "
				. "JOIN voce.richiesta rich "
				. "JOIN voce.piano_costo piano "
				. "WHERE rich.id = $id_richiesta AND piano.codice = 'TOT'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleScalarResult();
	}

	public function getTaglioTotaleRichiesta($id_richiesta) {

		$dql = "SELECT SUM(COALESCE(vocepc.taglio_anno_1,0)) + "
				. "SUM(COALESCE(vocepc.taglio_anno_2,0)) + "
				. "SUM(COALESCE(vocepc.taglio_anno_3,0)) + "
				. "SUM(COALESCE(vocepc.taglio_anno_4,0)) + "
				. "SUM(COALESCE(vocepc.taglio_anno_5,0)) + "
				. "SUM(COALESCE(vocepc.taglio_anno_6,0)) + "
				. "SUM(COALESCE(vocepc.taglio_anno_7,0)) FROM IstruttorieBundle:IstruttoriaVocePianoCosto vocepc"
				. "JOIN vocepc.voce_piano_costo voce"
				. "JOIN voce.richiesta rich "
				. "JOIN voce.piano_costo piano "
				. "WHERE rich.id = $id_richiesta AND piano.codice = 'TOT'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleScalarResult();
	}

	public function getTotaliRichiesta($id_richiesta) {

		$dql = "SELECT "
				. "SUM(COALESCE(voce.importo_anno_1,0)) as presentato_1, "
				. "SUM(COALESCE(voce.importo_anno_2,0)) as presentato_2, "
				. "SUM(COALESCE(voce.importo_anno_3,0)) as presentato_3, "
				. "SUM(COALESCE(voce.importo_anno_4,0)) as presentato_4, "
				. "SUM(COALESCE(voce.importo_anno_5,0)) as presentato_5, "
				. "SUM(COALESCE(voce.importo_anno_6,0)) as presentato_6, "
				. "SUM(COALESCE(voce.importo_anno_7,0)) as presentato_7, "
				. "SUM(COALESCE(istr.taglio_anno_1,0)) as taglio_1, "
				. "SUM(COALESCE(istr.taglio_anno_2,0)) as taglio_2, "
				. "SUM(COALESCE(istr.taglio_anno_3,0)) as taglio_3, "
				. "SUM(COALESCE(istr.taglio_anno_4,0)) as taglio_4, "
				. "SUM(COALESCE(istr.taglio_anno_5,0)) as taglio_5, "
				. "SUM(COALESCE(istr.taglio_anno_6,0)) as taglio_6, "
				. "SUM(COALESCE(istr.taglio_anno_7,0)) as taglio_7, "
				. "SUM(COALESCE(istr.importo_ammissibile_anno_1,0)) as ammissibile_1, "
				. "SUM(COALESCE(istr.importo_ammissibile_anno_2,0)) as ammissibile_2, "
				. "SUM(COALESCE(istr.importo_ammissibile_anno_3,0)) as ammissibile_3, "
				. "SUM(COALESCE(istr.importo_ammissibile_anno_4,0)) as ammissibile_4, "
				. "SUM(COALESCE(istr.importo_ammissibile_anno_5,0)) as ammissibile_5, "
				. "SUM(COALESCE(istr.importo_ammissibile_anno_6,0)) as ammissibile_6, "
				. "SUM(COALESCE(istr.importo_ammissibile_anno_7,0)) as ammissibile_7 "
				. "FROM RichiesteBundle:VocePianoCosto voce "
				. "LEFT JOIN voce.istruttoria istr "
				. "JOIN voce.richiesta rich "
				. "JOIN voce.piano_costo piano "
				. "WHERE rich.id = $id_richiesta AND piano.codice = 'TOT' "
				. "GROUP BY rich.id";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		$result = $q->getResult();

		if (count($result)) {
            return $result[0];
        }
		
		$result = [
		    'presentato_1' => 0,
		    'presentato_2' => 0,
		    'presentato_3' => 0,
		    'presentato_4' => 0,
		    'presentato_5' => 0,
		    'presentato_6' => 0,
		    'presentato_7' => 0,
		    'taglio_1' => 0,
		    'taglio_2' => 0,
		    'taglio_3' => 0,
		    'taglio_4' => 0,
		    'taglio_5' => 0,
		    'taglio_6' => 0,
		    'taglio_7' => 0,
		    'ammissibile_1' => 0,
		    'ammissibile_2' => 0,
		    'ammissibile_3' => 0,
		    'ammissibile_4' => 0,
		    'ammissibile_5' => 0,
		    'ammissibile_6' => 0,
		    'ammissibile_7' => 0,
        ];
		
		return $result;
	}

	public function getRichiesteDaSoggettoInGestione($id_soggetto) {

		$dql = "SELECT rich FROM RichiesteBundle:Richiesta rich "
				. "JOIN rich.proponenti prop "
                . "JOIN rich.procedura proc "
				. "JOIN prop.soggetto sogg "
				. "JOIN rich.attuazione_controllo att "
				. "WHERE sogg.id = $id_soggetto AND prop.mandatario = 1 AND (
					proc INSTANCE OF SfingeBundle:Bando OR 
					proc INSTANCE OF SfingeBundle:ManifestazioneInteresse OR
					proc INSTANCE OF SfingeBundle:ProceduraPA
				)";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function getRichiesteInTracciatoSenzaCup() {
		$dql = "SELECT r  "
				. "FROM RichiesteBundle:Richiesta r "
				. "JOIN r.istruttoria ir "
				. "WHERE ir.UltimaRichiestaCupBatch IS NOT NULL AND ir.codice_cup IS NULL AND ir.richiedi_cup =1";
		$query = $this->getEntityManager()->createQuery($dql);
		$results = $query->getResult();

		return $results;
	}

	public function getRichiesteInAttuazione(\AttuazioneControlloBundle\Form\Entity\RicercaAttuazione $ricercaAttuazione) {

		$dql = "SELECT rich as richiesta, rich.id, rich.data_invio, proc.titolo, sv.denominazione, "
				. "CASE WHEN rp.num_pg IS NOT NULL "
				. "THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg) "
				. "ELSE '-' END AS protocollo "
				. "FROM RichiesteBundle:Richiesta rich "
				. "JOIN rich.attuazione_controllo atc "
				. "JOIN rich.stato s "
				. "JOIN rich.procedura proc "
                                . "LEFT JOIN atc.pagamenti pag "
                                . "LEFT JOIN pag.stato s2 WITH s2.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                                . "LEFT JOIN pag.modalita_pagamento mod "
				. "LEFT JOIN rich.istruttoria i "
				. "LEFT JOIN rich.proponenti prop "
				. "LEFT JOIN rich.richieste_protocollo rp "
				. "LEFT JOIN prop.soggetto sv "
				. "LEFT JOIN proc.stato_procedura proc_s "
				. "JOIN proc.asse asse "
				. "LEFT JOIN atc.revoca rev "
                                . "LEFT JOIN rev.atto_revoca atrev "
                                . "LEFT JOIN atrev.tipo tr "
				. "WHERE 1=1 AND rp INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento "
		;             

		$q = $this->getEntityManager()->createQuery();

		if (!is_null($ricercaAttuazione->getProcedura())) {
			$dql .= " AND proc.id = :procedura ";
			$q->setParameter("procedura", $ricercaAttuazione->getProcedura());
		}
        
        if (!is_null($ricercaAttuazione->getFinestraTemporale())) {
			$dql .= " AND rich.finestra_temporale = :finestraTemporale ";
			$q->setParameter("finestraTemporale", $ricercaAttuazione->getFinestraTemporale());
		}

		if (!is_null($ricercaAttuazione->getId())) {
			$dql .= " AND rich.id = :id_richiesta ";
			$q->setParameter("id_richiesta", $ricercaAttuazione->getId());
		}

		if (!is_null($ricercaAttuazione->getAsse())) {
			$dql .= " AND asse.id = :asse ";
			$q->setParameter("asse", $ricercaAttuazione->getAsse());
		}

		if (!is_null($ricercaAttuazione->getCodiceFiscale())) {
			$dql .= " AND sv.codice_fiscale LIKE :cf ";
			$q->setParameter("cf", "%" . $ricercaAttuazione->getCodiceFiscale() . "%");
		}

		if (!is_null($ricercaAttuazione->getDenominazione())) {
			$dql .= " AND sv.denominazione LIKE :denominazione ";
			$q->setParameter("denominazione", "%" . $ricercaAttuazione->getDenominazione() . "%");
		}

		if (!is_null($ricercaAttuazione->getProtocollo())) {
			$dql .= "AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo ";
			$q->setParameter("protocollo", "%" . $ricercaAttuazione->getProtocollo() . "%");
		}

		if (!is_null($ricercaAttuazione->getCup())) {
			$dql .= " AND (atc.cup LIKE :cup OR i.codice_cup LIKE :cup) ";
			$q->setParameter("cup", "%" . $ricercaAttuazione->getCup() . "%");
		}
                
                if (!is_null($ricercaAttuazione->getModalitaPagamento())) {
                        if($ricercaAttuazione->getModalitaPagamento() == 'REVOCATO') {
                            $dql .= " AND rev.id IS NOT NULL AND atrev.id IS NOT NULL AND tr.codice = 'TOT' ";
                        }elseif($ricercaAttuazione->getModalitaPagamento() == 'NO_PAGAMENTO') {
                            $dql .= " AND pag.id is null ";
                        }else {
                            $dql .= " AND mod.codice = '{$ricercaAttuazione->getModalitaPagamento()} '
                                      AND mod.ordine_cronologico = (
                                            SELECT MAX(modalita_pagamento_sub2.ordine_cronologico) 
                                            FROM AttuazioneControlloBundle:Pagamento as pagamenti_sub2 
                                            JOIN pagamenti_sub2.modalita_pagamento AS modalita_pagamento_sub2
                                            JOIN pagamenti_sub2.stato s3 WITH s3.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') 
                                            WHERE pagamenti_sub2.attuazione_controllo_richiesta = pag.attuazione_controllo_richiesta) ";
                        }
		}
        
        if (!is_null($ricercaAttuazione->getUtente())) {
            if( $ricercaAttuazione->getUtente()->isValutatoreFesr() == true ) {
                $dql .= " AND proc.id IN (4, 27, 7, 8, 6, 5, 58, 107, 3, 96, 24, 62, 28, 104, 69, 111, 64, 67, 72, 77, 2, 70, 83, 110, 161, 75, 79, 112, 116, 128, 142) ";
            }
            if( $ricercaAttuazione->getUtente()->isInvitalia() == true ) {
                $dql .= " AND proc.id IN (95, 121, 132, 167) ";
            }
            if( $ricercaAttuazione->getUtente()->isOperatoreCogea() == true ) {
                $dql .= " AND proc.id IN (2,5,58,64,67,70,72,75,77,81,83,107,110,111,112,116,128,140,142,161) ";
            }
		}
        
        if (!is_null($ricercaAttuazione->getUtente())) {
            if( $ricercaAttuazione->getUtente()->isConsulenteFesr() == true )
			$dql .= " AND rich.id IN (201,542,915,3203,4932,5195,5420,5442,5443,5444)";
		}

		$dql .= "GROUP BY rich.id";

		$dql .= " ORDER BY rich.data_invio DESC ";

		$q->setDQL($dql);


		return $q;
	}
    
    public function getRichiesteInAttuazioneVolantino() {

		$dql = "SELECT sv.denominazione as soggetto , sv.cap as cap, s.denominazione as stato, com.denominazione as comune, prov.denominazione as provincia, "
                . "sv.codice_fiscale, sv.partita_iva, sv.email, sv.email_pec, a.codice as asse_prc, proc.titolo as procedura, "
                . "CASE WHEN rp.num_pg IS NOT NULL "
                . "THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg) "
                . "ELSE '-' END AS protocollo, "
                . "CONCAT(amm.numero, '-', amm.titolo) as amministrativo, "
                . "CONCAT(conn.numero, '-', conn.titolo) as conncessione, "
                . "CASE WHEN modconn.id IS NOT NULL "
                . "THEN CONCAT(modconn.numero, '-', modconn.titolo) "
                . "ELSE '-' END AS mod_concessione,"
                . "ist.costo_ammesso as investimento_ammesso, "
                . "ist.contributo_ammesso as contributo_concesso, "
                . "DATE_FORMAT(ist.data_contributo, '%d/%m/%Y') as data_contributo_concesso, "
                . "COALESCE(atc.cup, ist.codice_cup, '') as cup, "

                // Ho provato a fare la query "dinamica" provando a restituire una sub-query nel THEN
                // ma con DQL non me lo faceva fare.
                // Non te ne fare un cruccio...ahahaah ho aggiunto anche i sal 9 e 10
                . "CASE "
                . "WHEN (revoca.id IS NOT NULL AND atto_revoca.id IS NOT NULL AND tipo_atto_revoca.codice = 'TOT') "
                . "THEN 'Revoca totale' "

                . "WHEN (SELECT COUNT(pagamento.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento "
                . "JOIN pagamento.stato AS stato_pagamento WITH stato_pagamento.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "WHERE pagamento.attuazione_controllo_richiesta = atc.id) = 0 "
                . "THEN 'Nessun pagamento' "

                . "WHEN (SELECT COUNT(pagamento1.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento1 "
                . "JOIN pagamento1.stato AS stato_pagamento1 WITH stato_pagamento1.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento1.modalita_pagamento AS modalita_pagamento1 "
                . "WHERE pagamento1.attuazione_controllo_richiesta = atc.id AND modalita_pagamento1.codice = 'SALDO_FINALE') > 0 "
                . "THEN 'Saldo finale' "

                . "WHEN (SELECT COUNT(pagamento2.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento2 "
                . "JOIN pagamento2.stato AS stato_pagamento2 WITH stato_pagamento2.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento2.modalita_pagamento AS modalita_pagamento2 "
                . "WHERE pagamento2.attuazione_controllo_richiesta = atc.id AND modalita_pagamento2.codice = 'UNICA_SOLUZIONE') > 0 "
                . "THEN 'Unica soluzione' "

                . "WHEN (SELECT COUNT(pagamento14.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento14 "
                . "JOIN pagamento14.stato AS stato_pagamento14 WITH stato_pagamento14.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento14.modalita_pagamento AS modalita_pagamento14 "
                . "WHERE pagamento14.attuazione_controllo_richiesta = atc.id AND modalita_pagamento14.codice = 'DECIMO_SAL') > 0 "
                . "THEN '10° Sal' "        
                        
                . "WHEN (SELECT COUNT(pagamento15.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento15 "
                . "JOIN pagamento15.stato AS stato_pagamento15 WITH stato_pagamento15.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento15.modalita_pagamento AS modalita_pagamento15 "
                . "WHERE pagamento15.attuazione_controllo_richiesta = atc.id AND modalita_pagamento15.codice = 'NONO_SAL') > 0 "
                . "THEN '9° Sal' "
                        
                . "WHEN (SELECT COUNT(pagamento3.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento3 "
                . "JOIN pagamento3.stato AS stato_pagamento3 WITH stato_pagamento3.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento3.modalita_pagamento AS modalita_pagamento3 "
                . "WHERE pagamento3.attuazione_controllo_richiesta = atc.id AND modalita_pagamento3.codice = 'OTTAVO_SAL') > 0 "
                . "THEN '8° Sal' "

                . "WHEN (SELECT COUNT(pagamento4.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento4 "
                . "JOIN pagamento4.stato AS stato_pagamento4 WITH stato_pagamento4.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento4.modalita_pagamento AS modalita_pagamento4 "
                . "WHERE pagamento4.attuazione_controllo_richiesta = atc.id AND modalita_pagamento4.codice = 'SETTIMO_SAL') > 0 "
                . "THEN '7° Sal' "

                . "WHEN (SELECT COUNT(pagamento5.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento5 "
                . "JOIN pagamento5.stato AS stato_pagamento5 WITH stato_pagamento5.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento5.modalita_pagamento AS modalita_pagamento5 "
                . "WHERE pagamento5.attuazione_controllo_richiesta = atc.id AND modalita_pagamento5.codice = 'SESTO_SAL') > 0 "
                . "THEN '6° Sal' "

                . "WHEN (SELECT COUNT(pagamento6.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento6 "
                . "JOIN pagamento6.stato AS stato_pagamento6 WITH stato_pagamento6.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento6.modalita_pagamento AS modalita_pagamento6 "
                . "WHERE pagamento6.attuazione_controllo_richiesta = atc.id AND modalita_pagamento6.codice = 'QUINTO_SAL') > 0 "
                . "THEN '5° Sal' "

                . "WHEN (SELECT COUNT(pagamento7.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento7 "
                . "JOIN pagamento7.stato AS stato_pagamento7 WITH stato_pagamento7.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento7.modalita_pagamento AS modalita_pagamento7 "
                . "WHERE pagamento7.attuazione_controllo_richiesta = atc.id AND modalita_pagamento7.codice = 'QUARTO_SAL') > 0 "
                . "THEN '4° Sal' "

                . "WHEN (SELECT COUNT(pagamento8.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento8 "
                . "JOIN pagamento8.stato AS stato_pagamento8 WITH stato_pagamento8.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento8.modalita_pagamento AS modalita_pagamento8 "
                . "WHERE pagamento8.attuazione_controllo_richiesta = atc.id AND modalita_pagamento8.codice = 'TERZO_SAL') > 0 "
                . "THEN '3° Sal' "

                . "WHEN (SELECT COUNT(pagamento9.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento9 "
                . "JOIN pagamento9.stato AS stato_pagamento9 WITH stato_pagamento9.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento9.modalita_pagamento AS modalita_pagamento9 "
                . "WHERE pagamento9.attuazione_controllo_richiesta = atc.id AND modalita_pagamento9.codice = 'SECONDO_SAL') > 0 "
                . "THEN '2° Sal' "

                . "WHEN (SELECT COUNT(pagamento10.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento10 "
                . "JOIN pagamento10.stato AS stato_pagamento10 WITH stato_pagamento10.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento10.modalita_pagamento AS modalita_pagamento10 "
                . "WHERE pagamento10.attuazione_controllo_richiesta = atc.id AND modalita_pagamento10.codice = 'PRIMO_SAL') > 0 "
                . "THEN '1° Sal' "

                . "WHEN (SELECT COUNT(pagamento11.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento11 "
                . "JOIN pagamento11.stato AS stato_pagamento11 WITH stato_pagamento11.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento11.modalita_pagamento AS modalita_pagamento11 "
                . "WHERE pagamento11.attuazione_controllo_richiesta = atc.id AND modalita_pagamento11.codice = 'SAL') > 0 "
                . "THEN 'Sal' "

                . "WHEN (SELECT COUNT(pagamento12.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento12 "
                . "JOIN pagamento12.stato AS stato_pagamento12 WITH stato_pagamento12.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento12.modalita_pagamento AS modalita_pagamento12 "
                . "WHERE pagamento12.attuazione_controllo_richiesta = atc.id AND modalita_pagamento12.codice = 'TRASFERIMENTO') > 0 "
                . "THEN 'Trasferimento' "

                . "WHEN (SELECT COUNT(pagamento13.id) "
                . "FROM AttuazioneControlloBundle:Pagamento AS pagamento13 "
                . "JOIN pagamento13.stato AS stato_pagamento13 WITH stato_pagamento13.codice IN ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') "
                . "JOIN pagamento13.modalita_pagamento AS modalita_pagamento13 "
                . "WHERE pagamento13.attuazione_controllo_richiesta = atc.id AND modalita_pagamento13.codice = 'ANTICIPO') > 0 "
                . "THEN 'Anticipo' "

                . "ELSE '-' "
                . "END AS stato_progetto "

                . "FROM RichiesteBundle:Richiesta richiesta "
                . "JOIN richiesta.attuazione_controllo atc "
                . "JOIN richiesta.istruttoria ist "
                . "JOIN ist.atto_ammissibilita_atc amm "
                . "JOIN ist.atto_concessione_atc conn "
                . "LEFT JOIN ist.atto_modifica_concessione_atc modconn "
                . "JOIN richiesta.procedura proc "
                . "JOIN proc.asse a "
                . "JOIN richiesta.proponenti prop "
                . "JOIN richiesta.richieste_protocollo rp "
                . "JOIN prop.soggetto sv "
                . "JOIN sv.stato s "
                . "LEFT JOIN sv.comune com "
                . "LEFT JOIN com.provincia prov "
                . "LEFT JOIN atc.revoca revoca "
                . "LEFT JOIN revoca.atto_revoca atto_revoca "
                . "LEFT JOIN atto_revoca.tipo tipo_atto_revoca "
		. "WHERE prop.mandatario = 1 AND rp INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento "
		;

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	/**
	 * @return Richiesta[]
	 */
	public function getRichiesteInoltrateProcedura($id_procedura, $finestra_temporale = null) {

		$dql = "SELECT rich FROM RichiesteBundle:Richiesta rich "
				. "JOIN rich.stato st "
				. "WHERE st.id IN (4,5)";

		if (!is_null($id_procedura)) {
			$dql .= " AND rich.procedura = $id_procedura";
		}

		if (!is_null($finestra_temporale)) {
			$dql .= " AND rich.finestra_temporale = $finestra_temporale";
		}


		$q = $this->getEntityManager()->createQuery();

		//$q->setMaxResults(1);

		$q->setDQL($dql);

		return $q->getResult();
	}
    
    public function getImportoRichiesteInoltrateProcedura($id_procedura, $finestra_temporale = null) {

		$dql = "SELECT SUM(coalesce(rich.contributo_richiesta,0)) FROM RichiesteBundle:Richiesta rich "
				. "JOIN rich.stato st "
				. "WHERE st.id IN (4,5)";

		if (!is_null($id_procedura)) {
			$dql .= " AND rich.procedura = $id_procedura";
		}

		if (!is_null($finestra_temporale)) {
			$dql .= " AND rich.finestra_temporale = $finestra_temporale";
		}


		$q = $this->getEntityManager()->createQuery();

		//$q->setMaxResults(1);

		$q->setDQL($dql);
        $res = $q->getSingleScalarResult();
		return !is_null($res) ? $res : 0;
	}

    /**
     * @param int $id_procedura
     * @param int $finestra_temporale
     * @return array
     */
    public function getRichiesteInoltrateProceduraConAnnullate(int $id_procedura, int $finestra_temporale = 0) {
        $em = $this->getEntityManager();
        $em->getFilters()->disable('softdeleteable');
        
        $dql = "SELECT rich FROM RichiesteBundle:Richiesta rich "
            . "JOIN rich.stato st "
            . "WHERE st.id IN (4,5)";

        if (!is_null($id_procedura)) {
            $dql .= " AND rich.procedura = $id_procedura";
        }

        if ($finestra_temporale > 0) {
            $dql .= " AND rich.finestra_temporale = $finestra_temporale";
        }

        $q = $em->createQuery();
        $q->setDQL($dql);
        return $q->getResult();
        
    }

    /**
     * @param $id_procedura
     * @param null $finestra_temporale
     * @return array
     */
    public function getTutteRichiesteProcedura($id_procedura, $finestra_temporale = null) {
        $qb    = $this->createQueryBuilder('richiesta');
        $query = $qb->select('richiesta')
            ->addSelect('(SELECT MAX(log1.data) FROM BaseBundle:StatoLog log1 WHERE log1.id_oggetto = richiesta.id AND log1.stato_destinazione = \'PRE_INSERITA\' AND log1.oggetto = \'RichiesteBundle\Entity\Richiesta\') AS log_inserita')->where('richiesta.procedura = :idProcedura')->setParameter('idProcedura', $id_procedura)
            ->addSelect('(SELECT MAX(log2.data) FROM BaseBundle:StatoLog log2 WHERE log2.id_oggetto = richiesta.id AND log2.stato_destinazione = \'PRE_VALIDATA\' AND log2.oggetto = \'RichiesteBundle\Entity\Richiesta\') AS log_validata')->where('richiesta.procedura = :idProcedura')->setParameter('idProcedura', $id_procedura)
            ->addSelect('(SELECT MAX(log3.data) FROM BaseBundle:StatoLog log3 WHERE log3.id_oggetto = richiesta.id AND log3.stato_destinazione = \'PRE_FIRMATA\' AND log3.oggetto = \'RichiesteBundle\Entity\Richiesta\') AS log_firmata')->where('richiesta.procedura = :idProcedura')->setParameter('idProcedura', $id_procedura)
            ->addSelect('(SELECT MAX(log4.data) FROM BaseBundle:StatoLog log4 WHERE log4.id_oggetto = richiesta.id AND log4.stato_destinazione = \'PRE_INVIATA_PA\' AND log4.oggetto = \'RichiesteBundle\Entity\Richiesta\') AS log_inviata')->where('richiesta.procedura = :idProcedura')->setParameter('idProcedura', $id_procedura)
            ->addSelect('(SELECT MAX(log5.data) FROM BaseBundle:StatoLog log5 WHERE log5.id_oggetto = richiesta.id AND log5.stato_destinazione = \'PRE_PROTOCOLLATA\' AND log5.oggetto = \'RichiesteBundle\Entity\Richiesta\') AS log_protocollata')->where('richiesta.procedura = :idProcedura')->setParameter('idProcedura', $id_procedura)
        ;

        $qb->where('richiesta.procedura = :idProcedura');
        $qb->setParameter('idProcedura', $id_procedura);
        
        if ($finestra_temporale) {
            $qb->andWhere('richiesta.finestra_temporale = :idFinestra');
            $qb->setParameter('idFinestra', $finestra_temporale);
        }

        $result = $query->getQuery()->getResult();
        return $result;
    }



    

    /**
     * @param $id_procedura
     * @param null $finestra_temporale
     * @param GeoComune|null $comune
     * @return Richiesta[]
     */
	public function getRichiesteInoltrateProceduraPerComune($id_procedura, $finestra_temporale = null, GeoComune $comune = null) {
		$dql = "SELECT richiesta FROM RichiesteBundle:Richiesta richiesta "
			. "JOIN richiesta.stato st "
			. "JOIN richiesta.proponenti proponente "
			. "JOIN proponente.sedi sede_operativa "
			. "JOIN sede_operativa.sede sede "
			. "JOIN sede.indirizzo indirizzo "
			. "WHERE richiesta.procedura = :idProcedura AND st.id IN (:statiProcedura) ";
		
		if (!is_null($comune)) {
			$dql .= " AND indirizzo.comune = " . $comune->getId();
		}

		if (!is_null($finestra_temporale)) {
			$dql .= " AND richiesta.finestra_temporale = $finestra_temporale";
		}

		$q = $this->getEntityManager()->createQuery();
		$q->setParameter("idProcedura", $id_procedura);
		$q->setParameter("statiProcedura", [4,5]);
		$q->setDQL($dql);

		return $q->getResult();
	}
	
	public function getNumeroRichiesteInoltrateProceduraComune(Procedura $procedura, ?int $finestra_temporale = null, ?GeoComune $comune = null): int
	{
		$qb = $this->createQueryBuilder('richiesta');
		$expr = $qb->expr();
		$qb->select($expr->count('richiesta'))
		->innerJoin('richiesta.stato', 'stato')
		->innerJoin('richiesta.proponenti', 'proponente')
		->innerJoin('proponente.sedi', 'sede_operativa')
		->innerJoin('sede_operativa.sede', 'sede')
		->innerJoin('sede.indirizzo', 'indirizzo')
		->innerJoin('indirizzo.comune', 'comune')
		->where(
			$expr->eq('richiesta.procedura', ':procedura'),
			$expr->in('stato.codice', ':stati'),
			$expr->eq('comune', 'coalesce(:comune, comune)'),
			$expr->eq('coalesce(richiesta.finestra_temporale, 0)', 'coalesce(:finestra, richiesta.finestra_temporale, 0)')
		)
		->setParameter('procedura', $procedura)
		->setParameter('stati', [StatoRichiesta::PRE_PROTOCOLLATA, StatoRichiesta::PRE_INVIATA_PA])
		->setParameter('comune', $comune)
		->setParameter('finestra', $finestra_temporale);

		return $qb->getQuery()->getSingleScalarResult();
	}

	/**
	 * @param Procedura $procedura
	 * @param null $finestra_temporale
	 * @param GeoComune|null $comune
	 * @return bool
     */
	/*public function isRichiestaBandoCentriStoriciInviabile(Procedura $procedura, $finestra_temporale = null, GeoComune $comune = null): bool
    {
		$objCentriStorici = new OggettoCentriStorici();
		$vincoliComuni = $objCentriStorici->getVincoliComuni();
		
		if (!empty($comune->getId())) {
			// Comuni con limiti
			if (isset($vincoliComuni[$comune->getId()])) {
				$dql = "SELECT richiesta FROM RichiesteBundle:Richiesta richiesta "
					. "JOIN richiesta.stato st "
					. "JOIN richiesta.proponenti proponente "
					. "JOIN proponente.sedi sede_operativa "
					. "JOIN sede_operativa.sede sede "
					. "JOIN sede.indirizzo indirizzo "
					. "WHERE richiesta.procedura = :idProcedura AND st.id IN (:statiProcedura) ";

				if (!is_null($comune)) {
					$dql .= " AND indirizzo.comune = " . $comune->getId();
				}

				if (!is_null($finestra_temporale)) {
					$dql .= " AND richiesta.finestra_temporale = $finestra_temporale";
				}

				$q = $this->getEntityManager()->createQuery();
				$q->setParameter("idProcedura", $procedura->getId());
				$q->setParameter("statiProcedura", [4,5]);
				$q->setDQL($dql);

				if (count($q->getResult()) >= $objCentriStorici->getNrRichiestaPerComune($comune->getId())) {
					return false;
				}
			} else {
				// Altri comuni
				$array_id_comuni_con_limiti = array_keys($vincoliComuni);
				$dql = "SELECT richiesta FROM RichiesteBundle:Richiesta richiesta "
					. "JOIN richiesta.stato st "
					. "JOIN richiesta.proponenti proponente "
					. "JOIN proponente.sedi sede_operativa "
					. "JOIN sede_operativa.sede sede "
					. "JOIN sede.indirizzo indirizzo "
					. "WHERE richiesta.procedura = :idProcedura AND st.id IN (:statiProcedura) ";

				if (!is_null($comune)) {
					$dql .= " AND indirizzo.comune NOT IN (" . implode(',', $array_id_comuni_con_limiti) . ') ';
				}

				if (!is_null($finestra_temporale)) {
					$dql .= " AND richiesta.finestra_temporale = $finestra_temporale";
				}

				$q = $this->getEntityManager()->createQuery();
				$q->setParameter("idProcedura", $procedura->getId());
				$q->setParameter("statiProcedura", [4,5]);
				$q->setDQL($dql);
				
				// Numero di domande presentabili per gli altri comuni
				$nr_domande_presentabili_altri_comuni = $procedura->getNumeroMassimoRichiesteProcedura();
				// Vado a sottrarre i posti riservati per i comuni con limiti
				foreach ($vincoliComuni as $id_comune => $comune) {
					$nr_domande_presentabili_altri_comuni -= $comune['nrMassimoRichieste'];
				}
				
				if (count($q->getResult()) >= $nr_domande_presentabili_altri_comuni) {
					return false;
				}
			}
		} else {
			$richieste = $this->getRichiesteInoltrateProcedura($procedura->getId(), $finestra_temporale);
			
			if (count($richieste) >= $procedura->getNumeroMassimoRichiesteProcedura()) {
				return false;
			}
		}

		return true;
	}*/

    /**
     * @param Procedura $procedura
     * @param null $finestra_temporale
     * @param GeoComune|null $comune
     * @return bool
     */
    public function isRichiestaBandoCentriStoriciInviabile(Procedura $procedura, $finestra_temporale = null, GeoComune $comune = null): bool
    {
        // Metodo temporaneo per riapertura IV finestra bando Centri Storici
        // Non può essere presentata nessuna richiesta di contributo del comune di Ferrara
        if (!empty($comune->getId()) && $comune->getId() == 4030) {
            return false;
        }

        return true;
    }

	public function getRichiesteInoltrateInDate($id_procedura, $data_inizio, $data_fine) {

		$dql = "SELECT rich FROM RichiesteBundle:Richiesta rich "
				. "JOIN rich.stato st "
				. "WHERE st.id IN (4,5)";

		if (!is_null($id_procedura)) {
			$dql .= " AND rich.procedura = $id_procedura";
		}

		if (!is_null($data_inizio)) {
			$dql .= " AND rich.data_invio >= '" . $data_inizio->format('Y-m-d H:i:s') . "' ";
		}

		if (!is_null($data_fine)) {
			$dql .= " AND rich.data_invio <= '" . $data_fine->format('Y-m-d H:i:s') . "' ";
		}

		$dql .= " ORDER BY rich.data_invio ASC ";

		$q = $this->getEntityManager()->createQuery();

		//$q->setMaxResults(1);
		//$a = $q->getSQL();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function getCountRichiesteInoltrateProcedura($id_procedura, $finestra_temporale = null) {

		$dql = "SELECT count(rich.id) FROM RichiesteBundle:Richiesta rich "
				. "JOIN rich.stato st "
				. "WHERE st.id IN (4,5)";

		if (!is_null($id_procedura)) {
			$dql .= " AND rich.procedura = $id_procedura";
		}

		if (!is_null($finestra_temporale)) {
			$dql .= " AND rich.finestra_temporale = $finestra_temporale";
		}


		$q = $this->getEntityManager()->createQuery();

		$q->setDQL($dql);

		return $q->getSingleScalarResult();
	}

	public function getRichiesteInUniverso(\AuditBundle\Form\Entity\RicercaUniverso $ricercaUniverso) {

		if ($ricercaUniverso->getSezione() == 'SISTEMA') {
			$leftCampione = " LEFT JOIN rich.audit_campioni camp "
					. " LEFT JOIN camp.audit_requisito req "
					. " LEFT JOIN req.audit_organismo org ";
		}

		if ($ricercaUniverso->getSezione() == 'OPERAZIONE') {
			$leftCampione = " LEFT JOIN rich.audit_campioni_operazioni camp " . " LEFT JOIN camp.audit_operazione ope ";
		}

		$dql = "LEFT JOIN rich.attuazione_controllo atc "
				. "LEFT JOIN atc.pagamenti pag "
				. "LEFT JOIN pag.certificazioni cert "
				. "LEFT JOIN cert.certificazione c "
				. "JOIN rich.stato s "
				. "JOIN rich.procedura proc "
				. "JOIN rich.stato stato "
				. "LEFT JOIN rich.istruttoria i "
				. "LEFT JOIN rich.proponenti prop "
				. "LEFT JOIN rich.richieste_protocollo rp "
				. $leftCampione
				. "LEFT JOIN prop.soggetto_version sv "
				. "LEFT JOIN proc.stato_procedura proc_s "
				. "JOIN proc.asse asse "
				. "WHERE 1=1 AND stato.codice IN ('PRE_PROTOCOLLATA', 'PRE_INVIATA_PA') ";

		$q = $this->getEntityManager()->createQuery();
		$q2 = $this->getEntityManager()->createQuery();
		$params = array();

		if (!is_null($ricercaUniverso->getProcedura())) {
			$dql .= " AND proc.id = :procedura ";
			$q->setParameter("procedura", $ricercaUniverso->getProcedura()->getId());
			$params[] = $ricercaUniverso->getProcedura()->getId();
		}

		if (!is_null($ricercaUniverso->getId())) {
			$dql .= " AND rich.id = :id_richiesta ";
			$q->setParameter("id_richiesta", $ricercaUniverso->getId());
			$params[] = $ricercaUniverso->getId();
		}

		if (!is_null($ricercaUniverso->getAsse())) {
			$dql .= " AND asse.id = :asse ";
			$q->setParameter("asse", $ricercaUniverso->getAsse());
			$params[] = $ricercaUniverso->getAsse();
		}

		if (!is_null($ricercaUniverso->getCodiceFiscale())) {
			$dql .= " AND sv.codice_fiscale LIKE :cf ";
			$q->setParameter("cf", "%" . $ricercaUniverso->getCodiceFiscale() . "%");
			$params[] = "%" . $ricercaUniverso->getCodiceFiscale() . "%";
		}

		if (!is_null($ricercaUniverso->getDenominazione())) {
			$dql .= " AND sv.denominazione LIKE :denominazione ";
			$q->setParameter("denominazione", "%" . $ricercaUniverso->getDenominazione() . "%");
			$params[] = "%" . $ricercaUniverso->getDenominazione() . "%";
		}

		if (!is_null($ricercaUniverso->getProtocollo())) {
			$dql .= "AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo ";
			$q->setParameter("protocollo", "%" . $ricercaUniverso->getProtocollo() . "%");
			$params[] = "%" . $ricercaUniverso->getProtocollo() . "%";
		}

		if (!is_null($ricercaUniverso->getTitoloProgetto())) {
			$dql .= " AND rich.titolo LIKE :titolo ";
			$q->setParameter("titolo", "%" . $ricercaUniverso->getTitoloProgetto() . "%");
			$params[] = "%" . $ricercaUniverso->getTitoloProgetto() . "%";
		}

		if (!is_null($ricercaUniverso->getSelezionato())) {
			if ($ricercaUniverso->getSelezionato() == 'SI') {
				$dql .= " AND camp.richiesta IS NOT NULL ";
				if ($ricercaUniverso->getSezione() == 'OPERAZIONE') {
					$dql .= " AND ope.id = " . $ricercaUniverso->getAuditOperazione() . " ";
				}
				if ($ricercaUniverso->getSezione() == 'SISTEMA') {
					$dql .= " AND org.id = " . $ricercaUniverso->getAuditOrganismo() . " ";
					$dql .= " AND req.id = " . $ricercaUniverso->getAuditRequisito() . " ";
				}
			}
			if ($ricercaUniverso->getSelezionato() == 'NO') {
				$dql .= " AND camp.richiesta IS NULL ";
			}
		}

		if (!is_null($ricercaUniverso->getFase())) {
			switch ($ricercaUniverso->getFase()) {
				case "PRESENTAZIONE":
					$dql .= " AND i.id IS NOT NULL ";
					break;
				case "ISTRUTTORIA":
					$dql .= " AND i.esito IS NOT NULL ";
					break;
				case "ATTUAZIONE":
					$dql .= " AND atc.id IS NOT NULL ";
					break;
				case "CERTIFICAZIONE":
					$dql .= " AND pag.importo_certificato IS NOT NULL ";
					break;
			}
		}

		if (!is_null($ricercaUniverso->getCertificazione())) {
			$certificazioni = $ricercaUniverso->getCertificazione();
			$certificazioni_ids = array();
			foreach ($certificazioni as $certificazione) {
				$certificazioni_ids[] = $certificazione->getId();
			}
			if (count($certificazioni_ids)) {
				$dql .= " AND cert.certificazione IN ('" . implode("','", $certificazioni_ids) . "') ";
			}
		}

		if (!is_null($ricercaUniverso->getTotaleCertificato())) {
			$dql .= " GROUP BY rich.id";
			$dql .= " HAVING (SUM(COALESCE(cert.importo, 0))-SUM(COALESCE(cert.importo_taglio, 0))) {$ricercaUniverso->getTotaleCertificato()}";
			$dql .= " ORDER BY rich.data_invio DESC ";
			$q2->setDQL("SELECT count(rich) FROM RichiesteBundle:Richiesta rich " . $dql);
			$q2_sql = "SELECT COUNT(*) as conteggio FROM (" . $q2->getSQL() . ") temp";
		}

		if (is_null($ricercaUniverso->getTotaleCertificato())) {
			$dql .= " GROUP BY rich.id";
			$dql .= " ORDER BY rich.data_invio DESC ";
		}

		$q->setDQL("SELECT rich FROM RichiesteBundle:Richiesta rich " . $dql);

		if (!is_null($ricercaUniverso->getTotaleCertificato())) {
			$count_result = $this->getEntityManager()->getConnection()->fetchAssoc($q2_sql, $params);
			$q->setHint('knp_paginator.count', $count_result["conteggio"]);
		}

		$sql = $q->getSQL();
		return $q;
	}


	public function getRichiesteConPagamento() {

		$dql = "SELECT atc FROM AttuazioneControlloBundle:AttuazioneControlloRichiesta atc "
				. "JOIN atc.richiesta rich "
				. "JOIN rich.procedura proc "
				. "JOIN atc.pagamenti pag "
				. "WHERE proc.id not in (7,8,9,32)";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function getRichiesteByattoId($id_atto) {
		$dql = "SELECT rich FROM RichiesteBundle:Richiesta rich "
				. "JOIN rich.atti atti "
				. "WHERE atti = $id_atto";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function getProgetti(RicercaProgetto $ricerca): Query {
		$qb = $this->getProgettiQb($ricerca)
		->select('richiesta,  istruttoria, nucleo, richieste_protocollo, procedura, attuazione_controllo, proponenti')
		->orderBy('richiesta.id', 'asc')
		;
		return $qb->getQuery();
	}

	protected function getProgettiQb(RicercaProgetto $ricerca): QueryBuilder{
		$qb = $this->createQueryBuilder('richiesta');
		$expr = $qb->expr();
		$qb
		->join('richiesta.procedura', 'procedura')
		->join('procedura.asse', 'asse')
		->join('richiesta.proponenti', 'proponenti')
		->join('richiesta.richieste_protocollo', 'richieste_protocollo')
		->join('proponenti.soggetto',' soggetto')
		->join('richiesta.istruttoria', 'istruttoria')
		->join('richiesta.attuazione_controllo', 'attuazione_controllo')
		->leftJoin('istruttoria.nucleoIstruttoria', 'nucleo')
		->where(
			$expr->eq('proponenti.mandatario', 1),
			$expr->eq('asse', 'COALESCE(:asse, asse)'),
			$expr->eq('procedura', 'COALESCE(:procedura, procedura)'),
			$expr->like("COALESCE(CONCAT(richieste_protocollo.registro_pg, '/', richieste_protocollo.anno_pg, '/', richieste_protocollo.num_pg), richiesta.id)", ':codice_locale_progetto'),
			$expr->like("COALESCE(soggetto.denominazione, '')", ':beneficiario'),
			$expr->like("COALESCE(soggetto.denominazione, '')", ':codice_fiscale_beneficiario'),
			$expr->like("COALESCE(istruttoria.codice_cup , '')", ':codice_cup')

		)
		->setParameter(':asse', $ricerca->getAsse())
		->setParameter(':procedura', $ricerca->getProcedura())
		->setParameter(':codice_locale_progetto', '%' . $ricerca->getCodiceLocaleProgetto() . '%')
		->setParameter(':beneficiario', '%' . $ricerca->getBeneficiario() . '%')
		->setParameter(':codice_fiscale_beneficiario', '%' . $ricerca->getCodiceFiscaleBeneficiario() . '%')
		->setParameter(':codice_cup', '%' . $ricerca->getCodiceCup() . '%')
		;

		return $qb;
	}

	/**
	 * @param string $protocollo
	 * @return Richiesta|null
	 */
	public function findOneByProtocollo($protocollo) {
		$dql = 'select richiesta '
				. 'from RichiesteBundle:Richiesta richiesta '
				. 'join richiesta.richieste_protocollo protocollo '
				. "where concat(protocollo.registro_pg, '/', protocollo.anno_pg, '/', protocollo.num_pg) = :protocollo";

		return $this->getEntityManager()
						->createQuery($dql)
						->setParameter('protocollo', $protocollo)
						->getOneOrNullResult();
	}

	public function getRichiesteCtrlLoco($id_procedura) {

		$dql = "SELECT rich, atc, i "
				. "FROM RichiesteBundle:Richiesta rich "
				. "JOIN rich.attuazione_controllo atc "
				. "JOIN rich.stato s "
				. "JOIN rich.procedura proc "
				. "LEFT JOIN rich.istruttoria i "
				. "LEFT JOIN rich.proponenti prop "
				. "LEFT JOIN rich.richieste_protocollo rp "
				. "LEFT JOIN prop.soggetto_version sv "
				. "LEFT JOIN proc.stato_procedura proc_s "
				. "JOIN proc.asse asse "
				. "WHERE proc.id = :procedura AND rp INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento AND s.codice = 'PRE_PROTOCOLLATA' "
		;

		$q = $this->getEntityManager()->createQuery();

		$q->setParameter("procedura", $id_procedura);

		$dql .= "GROUP BY rich.id";

		$q->setDQL($dql);


		return $q->getResult();
	}

	/**
	 * @param RicercaRichiesta $ricerca
	 * @return \Doctrine\ORM\Query
	 */
	public function getQueryRichiesteProcedurePA(RicercaRichiesta $dati) {

		$dql = "SELECT rich FROM RichiesteBundle:Richiesta rich
				LEFT JOIN rich.richieste_protocollo rp 
				LEFT JOIN rich.proponenti prop 
				LEFT JOIN prop.soggetto s 
				JOIN rich.procedura proc 
				LEFT JOIN proc.stato_procedura proc_s 
				JOIN proc.asse asse 
				LEFT JOIN rich.stato ric_s 
				WHERE proc INSTANCE OF SfingeBundle:ProceduraPA
				AND proc = COALESCE(:procedura, proc)
				AND COALESCE(ric_s, '') = COALESCE(ric_s, :stato, '')
				AND COALESCE(rich.titolo, '') LIKE :titoloProgetto
				AND COALESCE(CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg), '') LIKE :protocollo
				AND COALESCE(s.denominazione, '') LIKE :denominazione
				AND COALESCE(s.codice_fiscale, '') LIKE :codice_fiscale
				";

		$q = $this->getEntityManager()->createQuery();

		$q->setParameter("procedura", $dati->getProcedura());
		$q->setParameter("stato", $dati->getStato());
		$q->setParameter("titoloProgetto", "%" . $dati->getTitoloProgetto() . "%");
		$q->setParameter("protocollo", "%" . $dati->getProtocollo() . "%");
		$q->setParameter("denominazione", "%" . $dati->getRagioneSocialeProponente() . "%");
		$q->setParameter("codice_fiscale", "%" . $dati->getCodiceFiscaleProponente() . "%");

		$utente = $dati->getUtente();
		if (!$utente->hasRole("ROLE_SUPER_ADMIN") && !$utente->hasRole("ROLE_GESTIONE_ASSISTENZA_TECNICA") && !$utente->hasRole("ROLE_GESTIONE_INGEGNERIA_FINANZIARIA")) {

			if (!$utente->hasRole("ROLE_ADMIN_PA")) {
				$dql .= " AND ( ";
				$dql .= "proc.id in (select proc3.id from SfingeBundle:PermessiProcedura proc2 join proc2.procedura proc3 where proc2.utente={$utente->getId()}) ";
				$dql .= "OR proc.asse in (select asse3.id from SfingeBundle:PermessiAsse asse2 join asse2.asse asse3 where asse2.utente={$utente->getId()}))";
			}
		} 
		

		$dql .= " GROUP BY rich.id 
					 ORDER BY rich.data_invio DESC ";

		$q->setDQL($dql);
		return $q;
	}

	/**
	 * @param Richiesta[] $richieste
	 * @return boolean
	 */
	public function hasRichiesteAmmesseInIstruttoria(\Traversable $richieste) {
		$dql = "SELECT 1
			FROM RichiesteBundle:Richiesta richiesta
			LEFT JOIN richiesta.istruttoria istruttoria 
			LEFT JOIN istruttoria.esito esito
			WHERE
			istruttoria is null
			or esito is null
			or esito.codice = 'AMMESSO' 
			or esito.codice = 'SOSPESO";

		return true == $this->getEntityManager()
						->createQuery($dql)
						->setMaxResults(1)
						->getOneOrNullResult();
	}

	public function iterateAllRichiesteApprovate(): \Doctrine\ORM\Internal\Hydration\IterableResult {
		return $this->createQueryBuilder('r')
			->join('r.attuazione_controllo','atc')
			->where('atc.data_cancellazione is null')
			->getQuery()
			->iterate();
	}
	
	public function getImpegni($id_richiesta)
	{
		$dql = 'select impegni, tc38 '
		.'from AttuazioneControlloBundle:RichiestaImpegni impegni '
		.'join impegni.richiesta richiesta '
		.'left join impegni.tc38_causale_disimpegno tc38 '
		.'where richiesta.id = :id_richiesta ';
		return $this->getEntityManager()
		->createQuery($dql)
		->setParameter('id_richiesta', $id_richiesta)
		->getResult();
	}

	public function getSoggettoPartecipanteProcedura($soggetto_id, $procedura_id)
	{
		$dql = "SELECT richiesta
		from RichiesteBundle:Richiesta as richiesta 
		inner join richiesta.procedura as procedura
		inner join richiesta.proponenti as proponenti with proponenti.mandatario = 1
		inner join proponenti.soggetto as soggetti
		where 
			procedura.id = :id_procedura
			and soggetti.id = :id_soggetto

		";
		return $this->getEntityManager()->createQuery($dql)
		->setParameter('id_soggetto', $soggetto_id)
		->setParameter('id_procedura', $procedura_id)
		->getResult();
	}

	public function getQueryBuilderRichiesteProtocollate(Procedura $procedura): QueryBuilder {
		$q = $this->createQueryBuilder('r');
		$expr = $q->expr();
		return $q->join('r.procedura', 'procedura')
		->join('r.stato', 'stato')
		->where(
			$expr->eq('procedura', ':procedura'),
			$expr->eq('stato.codice', ':stato')
		)
		->setParameter(':stato', StatoRichiesta::PRE_PROTOCOLLATA)
		->setParameter(':procedura', $procedura);
	}
        
        public function getQueryBuilderRichiesteProtocollateAtc(Procedura $procedura): QueryBuilder {
		$q = $this->createQueryBuilder('r');
		$expr = $q->expr();
		return $q->join('r.procedura', 'procedura')
		->join('r.stato', 'stato')
                ->join('r.attuazione_controllo', 'attuazione_controllo')
		->where(
			$expr->eq('procedura', ':procedura'),
			$expr->eq('stato.codice', ':stato')
		)
		->setParameter(':stato', StatoRichiesta::PRE_PROTOCOLLATA)
		->setParameter(':procedura', $procedura);
	}
        
    public function getRichiesteProtocollateAtcTipologia98(Procedura $procedura, $tipologia) {
        $dql = "SELECT richiesta " .
                "FROM RichiesteBundle:Richiesta richiesta " .
                "JOIN richiesta.attuazione_controllo atc " .
                "JOIN richiesta.oggetti_richiesta oggetto " .
                "JOIN RichiesteBundle:Bando98\OggettoLegge14 oggetto_14 WITH oggetto = oggetto_14 " .
                "WHERE richiesta.procedura = :procedura_id AND oggetto_14.tipologiaProgetto = :tipologia ";

        $q = $this->getEntityManager()->createQuery();
        $q->setParameter("procedura_id", 98);
        $q->setParameter("tipologia", $tipologia);

        $q->setDQL($dql);
        return $q->getResult();
    }

    public function findByProtocollo(?string $protocollo): ?Richiesta{
		if(ctype_digit($protocollo)){
			return $this->find($protocollo);
		}

		$qb = $this->createQueryBuilder('r');
		$expr = $qb->expr();
		$query = $qb
			->innerJoin('r.richieste_protocollo', 'p')
			->where(
				$expr->eq(
					new \Doctrine\ORM\Query\Expr\Func('CONCAT', [
						'p.registro_pg', 
						$expr->literal('/'),
						'p.anno_pg', 
						$expr->literal('/'),
						'p.num_pg'
					]), 
					':protocollo'
				)
			)
			->setParameter('protocollo', $protocollo)
			->getQuery();
		return $query->getOneOrNullResult();
	}

	public function getEstrazioneCompletaBase(Procedura $procedura): array {
		$dql = "SELECT r.id, 
					concat(p.registro_pg, '/', p.anno_pg, '/', p.num_pg) as protocollo,
					r.titolo,
					r.abstract,
					r.data_invio,
					s.denominazione,
					s.partita_iva,
					s.codice_fiscale,
					CONCAT(s.via, ', ', s.civico, comune.denominazione, ' ', s.cap, ' (', provincia.sigla_automobilistica, ')'),
						SUM(COALESCE(v.importo_anno_1, 0)) +
						SUM(COALESCE(v.importo_anno_2, 0)) +
						SUM(COALESCE(v.importo_anno_3, 0)) +
						SUM(COALESCE(v.importo_anno_4, 0)) +
						SUM(COALESCE(v.importo_anno_5, 0)) +
						SUM(COALESCE(v.importo_anno_6, 0)) +
						SUM(COALESCE(v.importo_anno_7, 0)),
                    s.email,
                    s.email_pec,
                    esito.descrizione,
                    istRic.contributo_ammesso,
                    CASE WHEN atc.id IS NOT NULL
				    THEN 'Si'
                    ELSE 'No' END

				FROM RichiesteBundle:Richiesta r
				INNER JOIN r.richieste_protocollo p WITH p INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento
				INNER JOIN r.proponenti proponenti WITH proponenti.mandatario = 1
				INNER JOIN proponenti.soggetto s
				INNER JOIN r.stato stato
				LEFT JOIN s.comune comune
				LEFT JOIN comune.provincia provincia
				INNER JOIN r.voci_piano_costo v
				INNER JOIN v.piano_costo piano_costo 
				INNER JOIN piano_costo.tipo_voce_spesa tipo_voce_spesa WITH tipo_voce_spesa.codice <> 'TOTALE'
                LEFT JOIN r.istruttoria istRic
                LEFT JOIN istRic.esito esito
                LEFT JOIN r.attuazione_controllo atc

				WHERE stato.id in (4,5) AND r.procedura = :procedura

				GROUP BY r.id,
					p.registro_pg, p.anno_pg, p.num_pg, r.titolo, 
					r.abstract, 
					r.data_invio, 
					s.denominazione,
					s.partita_iva,
					s.codice_fiscale,
					s.via, s.civico, comune.denominazione, s.cap, provincia.sigla_automobilistica
				
			";

		return $this->getEntityManager()->createQuery($dql)
		->setParameter('procedura', $procedura)
		->getResult();
	}

    public function getIstruttoriRichieste() {
        $dql = "SELECT u FROM SfingeBundle:Utente u 
				WHERE u.roles LIKE :ruolo";

        $q = $this->getEntityManager()->createQuery();
        // Ho aggiunto anche gli apici doppi perchè non usandoli 
        // venivano restituiti anche gli utenti con i seguenti ruoli: ROLE_ISTRUTTORE_ATC e ROLE_ISTRUTTORE_CONTROLLI
        $q->setParameter(":ruolo", "%\"ROLE_ISTRUTTORE\"%");
        $q->setDQL($dql);

        return $q->getResult();
    }

    public function getRichiesteIstruttoreCount($istruttore, $completata) {
        $dql = "SELECT count(ric)
                FROM RichiesteBundle:Richiesta ric 
                JOIN ric.istruttoria istRic 
                JOIN ric.assegnamenti_istruttoria ai
				WHERE istRic.esito is ".($completata ? "not null" : "null")." AND ai.attivo = 1 AND ai.istruttore = ".$istruttore->getId();

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        return $q->getSingleScalarResult();
	
	}
	public function getReportIgrue(RicercaProgetto $ricerca): Query	{
		$controlli = array_map( function(string $controllo):string{
			return "controllo_igrue(richiesta.id, '$controllo') as c$controllo";
		},[
			'001',
			'002',
			'003',
			'004',
			'005',
			'006',
			'007',
			'008',
			'009',
		]);
		$select = "COALESCE(CONCAT(richieste_protocollo.registro_pg, '/', richieste_protocollo.anno_pg, '/', richieste_protocollo.num_pg), richiesta.id) as protocollo," .
			\implode(', ', $controlli);
		$qb = $this->getProgettiQb($ricerca);
		$qb->select($select);
		return $qb->getQuery();
	}
    
    public function getEstrazioneAudit($id_procedura): array {
        $dql = "SELECT "
                . "r.id as id_operazione, "
                . "concat(p.registro_pg, '/', p.anno_pg, '/', p.num_pg) as protocollo, "
                . "istRic.codice_cup, "
                . "s.denominazione, "
                . "proc.titolo as titolo_procedura ,"
                . "concat(asse.codice,'-',asse.descrizione) as asse_completo, "
                . "r.titolo, "
                . "r.abstract, "
                . "istRic.contributo_ammesso, "
                . "DATE_FORMAT(istRic.data_termine_progetto,'%d-%m-%Y') as data_termine, "
                . "coalesce(controlli.id) as controllo_loco "
                . "FROM RichiesteBundle:Richiesta r "
                . "JOIN r.richieste_protocollo p WITH p INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento "
                . "JOIN r.proponenti proponenti WITH proponenti.mandatario = 1 "
                . "JOIN proponenti.soggetto s "
                . "JOIN r.procedura proc "
                . "JOIN proc.asse asse "
                . "LEFT JOIN r.istruttoria istRic "
                . "LEFT JOIN r.controlli controlli "
                . "WHERE asse.id <> 8 ";

        if ($id_procedura != 'all') {
            $dql .= " AND proc.id = {$id_procedura} ";
        }

        $dql .= " GROUP BY r.id ";

        $q = $this->getEntityManager()->createQuery($dql);
//        $sql = $q->getSQL();
        return $q->getResult();
    }
    
    public function getEstrazioneAuditProcedura($id_procedura): array {
        $dql = "SELECT "
                . "r as richiesta, "
                . "r.id as id_operazione, "
                . "concat(p.registro_pg, '/', p.anno_pg, '/', p.num_pg) as protocollo, "
                . "istRic.codice_cup, "
                . "s.denominazione, "
                . "proc.titolo as titolo_procedura ,"
                . "concat(asse.codice,'-',asse.descrizione) as asse_completo, "
                . "r.titolo, "
                . "r.abstract, "
                . "istRic.contributo_ammesso, "
                . "DATE_FORMAT(istRic.data_termine_progetto,'%d-%m-%Y') as data_termine, "
                . "coalesce(controlli.id) as controllo_loco "
                . "FROM RichiesteBundle:Richiesta r "
                . "JOIN r.richieste_protocollo p WITH p INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento "
                . "JOIN r.proponenti proponenti WITH proponenti.mandatario = 1 "
                . "JOIN proponenti.soggetto s "
                . "JOIN r.procedura proc "
                . "JOIN proc.asse asse "
                . "LEFT JOIN r.istruttoria istRic "
                . "LEFT JOIN r.controlli controlli "
                . "WHERE asse.id <> 8 ";

        if ($id_procedura != 'all') {
            $dql .= " AND proc.id = {$id_procedura} ";
        }

        $dql .= " GROUP BY r.id ";

        $q = $this->getEntityManager()->createQuery($dql);
//        $sql = $q->getSQL();
        return $q->getResult();
    }

    /**
     * @param string $codice_fiscale_beneficiario
     * @return bool
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    function isBeneficiarioGiaSelezionato(string $codice_fiscale_beneficiario)
    {
        $dql = "SELECT COUNT(richiesta) " .
            "FROM RichiesteBundle:Richiesta richiesta " .
            "JOIN richiesta.oggetti_richiesta oggetto " .
            "JOIN RichiesteBundle:Bando127\OggettoSanificazione oggetto_sanificazione WITH oggetto = oggetto_sanificazione " .
            "WHERE richiesta.procedura = :procedura_id AND oggetto_sanificazione.codice_fiscale = :codice_fiscale AND oggetto_sanificazione.data_scadenza_richiesta >= :oggi ";

        $q = $this->getEntityManager()->createQuery();
        $q->setParameter("procedura_id", 127);
        $q->setParameter("codice_fiscale", $codice_fiscale_beneficiario);

        $oggi = new DateTime('now');
        $oggi->setTime(23,59,59);
        $q->setParameter("oggi", $oggi);

        $q->setDQL($dql);
        $retVal = $q->getSingleScalarResult();
        if ($retVal > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param string $tipologia_basket
     * @param null $id_sede_operativa_richiesta
     * @return int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getTotaleImportiPrenotatiBandoSanificazione(string $tipologia_basket, $id_sede_operativa_richiesta = null)
    {
        $dql = "SELECT SUM(COALESCE(sede_operativa_richiesta.contributo_sede, 0)) " .
               "FROM RichiesteBundle:Richiesta richiesta " .
               "JOIN richiesta.stato stato " .
               "JOIN richiesta.oggetti_richiesta oggetto " .
               "JOIN RichiesteBundle:Bando127\OggettoSanificazione oggetto_sanificazione WITH oggetto = oggetto_sanificazione " .
               "JOIN richiesta.sedi_operative sede_operativa_richiesta " .
               "WHERE sede_operativa_richiesta.tipologia = '$tipologia_basket' AND richiesta.procedura = 127 
               AND (stato.codice IN ('PRE_INVIATA_PA', 'PRE_PROTOCOLLATA') OR oggetto_sanificazione.data_scadenza_richiesta >= NOW())";
        
        if ($id_sede_operativa_richiesta) {
            $dql .= " AND sede_operativa_richiesta.id != $id_sede_operativa_richiesta";
        }
        
        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $retVal = $q->getSingleScalarResult();
        return !is_null($retVal) ? $retVal : 0;
    }

    /**
     * @param Procedura $procedura
     * @param null $finestra_temporale
     * @return array|int|string
     */
    public function getRichiesteInviateEProtocollatePerProcedura(Procedura $procedura, $finestra_temporale = null)
    {
        $qb = $this->createQueryBuilder('richiesta');
        $qb->join('richiesta.procedura', 'procedura')
            ->join('richiesta.stato', 'stato')
            ->where(
                'procedura = :procedura',
                'stato.codice in (:stati)'
            );

        $qb->setParameter(':procedura', $procedura)
                ->setParameter('stati', [
                StatoRichiesta::PRE_INVIATA_PA,
                StatoRichiesta::PRE_PROTOCOLLATA,
            ]);

        if ($finestra_temporale) {
            $qb->andWhere('richiesta.finestra_temporale = :idFinestra');
            $qb->setParameter('idFinestra', $finestra_temporale);
        }

        return $qb->getQuery()->getResult();
    }
}
