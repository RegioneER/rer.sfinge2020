<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RichiesteBundle\Form\Entity\RicercaBandoManifestazione;
use SfingeBundle\Form\Entity\RicercaProcedura;
use UtenteBundle\Form\Entity\RicercaUtenti;
use Doctrine\ORM\Query;

class ProceduraRepository extends EntityRepository {

	public function cercaProcedure(RicercaProcedura $dati): Query {
		$utente = $dati->getUtente();
		$dql = "SELECT p.id, 
			  a.numero as numero_atto, 
			  concat(ax.codice, ': ', ax.descrizione) as asse,
			  CASE 
			  WHEN p INSTANCE OF SfingeBundle:ManifestazioneInteresse
			  THEN 'Manifestazione di interesse'
			  WHEN p INSTANCE OF SfingeBundle:Bando OR p INSTANCE OF SfingeBundle:ProceduraPA
			  THEN 'Bando'
			  WHEN p INSTANCE OF SfingeBundle:IngegneriaFinanziaria
			  THEN 'Ingegneria finanziaria'
			  WHEN p INSTANCE OF SfingeBundle:AssistenzaTecnica
			  THEN 'Assistenza tecnica'
			  WHEN p INSTANCE OF SfingeBundle:Acquisizioni
			  THEN 'Acquisizione'
			  ELSE '-' 
			  END AS tipologia_procedura,
			  p.titolo as titolo, 
			  am.descrizione as amministrazione_emittente,
			  concat(pers.cognome, ' ', pers.nome) as responsabile,
			  COALESCE(p.fondo, '-') as fondo
			  FROM SfingeBundle:Procedura p
              LEFT JOIN p.atto a
              LEFT JOIN p.responsabile u
			  LEFT JOIN u.persona pers
              LEFT JOIN p.asse ax
              LEFT JOIN p.amministrazione_emittente am
              WHERE 1=1 
              AND p.titolo LIKE :titolo_procedura 
              AND COALESCE(a.numero, '') LIKE :numero_atto 
              AND u.id = COALESCE(:responsabile, u) 
              AND ax = COALESCE(:asse, ax) 
              AND am = COALESCE(:amministrazione, am) 
              AND p.anno_programmazione = COALESCE(:anno, p.anno_programmazione ) 
              AND (:tipo is null or p INSTANCE OF :tipo)";

		if ($dati->getAdmin() == false) {
			$dql .= "AND (
				p.id in (select proc3.id from SfingeBundle:PermessiProcedura proc2 join proc2.procedura proc3 where proc2.utente={$utente->getId()})
				OR 
				p.asse in (select asse3.id from SfingeBundle:PermessiAsse asse2 join asse2.asse asse3 where asse2.utente={$utente->getId()})
			  )";
		}

		$dql .= "ORDER BY p.id ASC";

		$q = $this->getEntityManager()->createQuery($dql);

		$q->setParameter("titolo_procedura", "%" . $dati->getTitolo() . "%");
		$q->setParameter("numero_atto", "%" . $dati->getAtto() . "%");
		$q->setParameter("responsabile", is_null($dati->getResponsabile()) ? null : $dati->getResponsabile()->getId());
		$q->setParameter("asse", $dati->getAsse());
		$q->setParameter("amministrazione", $dati->getAmministrazioneEmittente());
		$q->setParameter("anno", $dati->getAnnoProgrammazione());

		switch ($dati->getTipo()) {
			case 'BANDO':
				$q->setParameter("tipo", $this->getEntityManager()->getClassMetadata('SfingeBundle\Entity\Bando'));
				break;
			case 'MANIFESTAZIONE_INTERESSE':
				$q->setParameter("tipo", $this->getEntityManager()->getClassMetadata('SfingeBundle\Entity\ManifestazioneInteresse'));
				break;
			case 'ASSISTENZA_TECNICA':
				$q->setParameter("tipo", $this->getEntityManager()->getClassMetadata('SfingeBundle\Entity\AssistenzaTecnica'));
				break;
			default:
				$q->setParameter("tipo", null);
				break;
		}

		$q->setDQL($dql);

		return $q;
	}

	public function cercaBandiManifestazioni(RicercaBandoManifestazione $dati) {

		// TODO: aggiungere alla query lo stato della Procedura(bandi creati, ma non pubblicati non devono essere visualizzabili: possiamo utilizzare la data pubblicazione BUR?)
		$dql = "SELECT b FROM SfingeBundle:Bando b
              LEFT JOIN b.atto a
              LEFT JOIN b.responsabile u
              LEFT JOIN b.asse ax
              LEFT JOIN b.amministrazione_emittente am
			  LEFT JOIN b.stato_procedura sp
			  WHERE 1=1 AND sp.codice IN ('IN_CORSO', 'CONCLUSO') 
			  AND b not instance of SfingeBundle:ProceduraPA ";

		$q = $this->getEntityManager()->createQuery();

		if (!\is_null($dati->getStato())) {
			$stato = $dati->getStato();
			$dql .= " AND (";
			if ($stato == 'APERTO') {
				$dql .= " (b.data_ora_inizio_presentazione <= CURRENT_TIMESTAMP()  ";
				$dql .= " AND b.data_ora_fine_creazione >= CURRENT_TIMESTAMP()  )";
			} else if ($stato == 'CHIUSO') {
				$dql .= " (b.data_ora_inizio_presentazione >= CURRENT_TIMESTAMP() ";
				$dql .= " OR b.data_ora_fine_creazione <= CURRENT_TIMESTAMP()) ";
			}
			$dql .= ") ";
		}

		if (!\is_null($dati->getTitolo())) {
			$dql .= " AND b.titolo LIKE :titolo_procedura ";
			$q->setParameter(":titolo_procedura", "%" . $dati->getTitolo() . "%");
		}

		if (!\is_null($dati->getAtto())) {
			$dql .= " AND coalesce(a.numero, '') LIKE :numero_atto ";
			$q->setParameter(":numero_atto", "%" . $dati->getAtto() . "%");
		}

		if (!\is_null($dati->getTipo())) {
			if ($dati->getTipo() == 'BANDO') {
				$dql .= " AND b INSTANCE OF SfingeBundle:Bando";
			}
			if ($dati->getTipo() == 'MANIFESTAZIONE_INTERESSE') {
				$dql .= " AND b INSTANCE OF SfingeBundle:ManifestazioneInteresse";
			}
		} else {
			$dql .= " AND (b INSTANCE OF SfingeBundle:ManifestazioneInteresse OR b INSTANCE OF SfingeBundle:Bando) ";
		}

		if (!\is_null($dati->getAsse())) {
			$dql .= " AND ax.id = :asse_id ";
			$q->setParameter(":asse_id", $dati->getAsse()->getId());
		}

		if (!\is_null($dati->getStatoProcedura())) {
			$dql .= " AND sp.codice = :stato_procedura ";
			$q->setParameter(":stato_procedura", $dati->getStatoProcedura());
		}

		$q->setDQL($dql);

		return $q;
	}

	public function cercaBandiParticolari(\RichiesteBundle\Form\Entity\RicercaBandoParticolare $dati) {
		$dql = "SELECT b FROM SfingeBundle:Procedura b
              LEFT JOIN b.atto a
              LEFT JOIN b.responsabile u
              LEFT JOIN b.asse ax
              LEFT JOIN b.amministrazione_emittente am
			  LEFT JOIN b.stato_procedura sp
              WHERE 1=1";

		$q = $this->getEntityManager()->createQuery();

		if (!\is_null($dati->getTitolo())) {
			$dql .= " AND b.titolo LIKE :titolo_procedura ";
			$q->setParameter(":titolo_procedura", "%" . $dati->getTitolo() . "%");
		}

		if (!\is_null($dati->getAtto())) {
			$dql .= " AND coalesce(a.numero, '') LIKE :numero_atto ";
			$q->setParameter(":numero_atto", "%" . $dati->getAtto() . "%");
		}

		if (!\is_null($dati->getTipo())) {
			if ($dati->getTipo() == 'ASSISTENZA_TECNICA') {
				$dql .= " AND b INSTANCE OF SfingeBundle:AssistenzaTecnica";
			}

			if ($dati->getTipo() == 'INGEGNERIA_FINANZIARIA') {
				$dql .= " AND b INSTANCE OF SfingeBundle:IngegneriaFinanziaria";
			}
		}

		if (!\is_null($dati->getAsse())) {
			$dql .= " AND ax.id = :asse_id ";
			$q->setParameter(":asse_id", $dati->getAsse()->getId());
		}

		if (!\is_null($dati->getStatoProcedura())) {
			$dql .= " AND sp.codice = :stato_procedura ";
			$q->setParameter(":stato_procedura", $dati->getStatoProcedura());
		}

		$q->setDQL($dql);
		return $q;
	}

	public function getProcedureVisibiliPA($utente) {

		$dql = "SELECT proc FROM SfingeBundle:Procedura proc "
				. "LEFT JOIN proc.stato_procedura proc_s "
				. "JOIN proc.asse asse "
				. "WHERE 1=1 ";

		$q = $this->getEntityManager()->createQuery();

		if (!$utente->hasRole("ROLE_SUPER_ADMIN") && !$utente->hasRole("ROLE_GESTIONE_INGEGNERIA_FINANZIARIA") && !$utente->hasRole("ROLE_GESTIONE_ASSISTENZA_TECNICA")) {

			$dql .= " AND (proc_s.codice='CONCLUSO' OR proc.visibile_in_corso = 1) ";

			if (!$utente->hasRole("ROLE_ADMIN_PA")) {
				$dql .= " AND ( ";
				$dql .= "proc.id in (select proc3.id from SfingeBundle:PermessiProcedura proc2 join proc2.procedura proc3 where proc2.utente={$utente->getId()}) ";
				$dql .= "OR proc.asse in (select asse3.id from SfingeBundle:PermessiAsse asse2 join asse2.asse asse3 where asse2.utente={$utente->getId()}))";
			}
		}

		$q->setDQL($dql);

		return $q->getResult();
	}

	public function getProcedureAt() {
		$dql = "SELECT proc FROM SfingeBundle:Procedura proc "
				. "LEFT JOIN proc.stato_procedura proc_s "
				. "JOIN proc.asse asse "
				. "WHERE proc INSTANCE OF SfingeBundle:AssistenzaTecnica ";

		$q = $this->getEntityManager()->createQuery();

		$q->setDQL($dql);
		return $q->getResult();
	}

	public function getProcedureIngFin() {
		$dql = "SELECT proc FROM SfingeBundle:Procedura proc "
				. "LEFT JOIN proc.stato_procedura proc_s "
				. "JOIN proc.asse asse "
				. "WHERE proc INSTANCE OF SfingeBundle:IngegneriaFinanziaria ";

		$q = $this->getEntityManager()->createQuery();

		$q->setDQL($dql);
		return $q->getResult();
	}

	public function findAllMonitoraggioSearch(\MonitoraggioBundle\Form\Entity\RicercaProcedura $ricerca): Query {
		$qb = $this->createQueryBuilder('procedura');
		$expr = $qb->expr();
		$qb->innerJoin('procedura.asse', 'asse')
		->leftJoin('procedura.atto', 'atto')
		->where(
			$expr->like('procedura.titolo', ':titolo'),
			$expr->eq('asse','COALESCE(:asse, asse)'),
			$expr->eq("COALESCE(atto, '')","COALESCE(:atto, atto, '')"),
			$expr->eq('procedura.fondo', $expr->literal('FESR')),
			$expr->orX(
				$expr->isNull(':tipo'),
				$expr->isInstanceOf('procedura', ':tipo')
			)
		)
		->setParameter('titolo', '%' . $ricerca->getTitolo() . '%')
		->setParameter('asse', $ricerca->getAsse())
		->setParameter('atto', $ricerca->getNumeroProceduraAttivazione())
		->setParameter('tipo', $ricerca->getTipo());

		$query = $qb->getQuery();

		return $query;
	}

	
	public function getElencoProcedurePA($utente)
	{

		$dql = "SELECT proc FROM SfingeBundle:ProceduraPA proc "
				. "LEFT JOIN proc.stato_procedura proc_s "
				. "JOIN proc.asse asse "
				. "WHERE proc INSTANCE OF SfingeBundle:ProceduraPA" 
				." AND (proc.data_ora_inizio_presentazione <= CURRENT_TIMESTAMP() AND proc.data_ora_fine_creazione >= CURRENT_TIMESTAMP()) ";

		if (!$utente->hasRole("ROLE_SUPER_ADMIN") && !$utente->hasRole("ROLE_GESTIONE_INGEGNERIA_FINANZIARIA") && !$utente->hasRole("ROLE_GESTIONE_ASSISTENZA_TECNICA")) {

			$dql .= " AND (proc_s.codice='CONCLUSO' OR proc.visibile_in_corso = 1) ";

			if (!$utente->hasRole("ROLE_ADMIN_PA")) {
					$dql .= " AND ( ";
					$dql .= "proc.id in (select proc3.id from SfingeBundle:PermessiProcedura proc2 join proc2.procedura proc3 where proc2.utente={$utente->getId()}) ";
					$dql .= "OR proc.asse in (select asse3.id from SfingeBundle:PermessiAsse asse2 join asse2.asse asse3 where asse2.utente={$utente->getId()}))";
			}
		}
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		
		return $q->getResult();
	}
    
    public function getProcedurePOR() {
		$dql = "SELECT proc FROM SfingeBundle:Procedura proc "
				. "JOIN proc.asse asse "
				. "WHERE asse.id <> 8 ";

		$q = $this->getEntityManager()->createQuery();

		$q->setDQL($dql);
		return $q->getResult();
	}
}
