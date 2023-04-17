<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use UtenteBundle\Form\Entity\RicercaUtenti;

class UtenteRepository extends EntityRepository {
	
	public function cercaUtenteByEmail($email) {
		$dql = "SELECT u FROM SfingeBundle:Utente u WHERE u.enabled = true";
		$q = $this->getEntityManager()->createQuery();
		$dql .= " AND u.email = :email";
		$q->setParameter(":email", $email);
		$q->setDQL($dql);

		return $q->getResult();
	}
	
	public function cercaUtenti(RicercaUtenti $dati) {
		$dql = "SELECT u FROM SfingeBundle:Utente u "
				. "LEFT JOIN u.persona p";

		$q = $this->getEntityManager()->createQuery();
		// if ($dati->getFiltroAttivo() == true) {
			$dql .= " WHERE (1 = 1 ";
			if ($dati->getUsername() != "") {
				$dql .= " AND u.username LIKE :username";
				$q->setParameter(":username", $dati->getUsername());
			}
			if ($dati->getEmail() != "") {
				$dql .= " AND u.email LIKE :email";
				$q->setParameter(":email", "%" . $dati->getEmail() . "%");
			}
			if ($dati->getRuoli() != "") {
				$dql .= " AND u.roles LIKE :ruolo";
				$q->setParameter(":ruolo", "%" . $dati->getRuoli() . "%");
			}
			if (!is_null($dati->getAttivo())) {
				$dql .= " AND u.enabled = :attivo";
				$q->setParameter(":attivo",  $dati->getAttivo() );
			}
			if ($dati->getId() != "") {
				$dql .= " AND u.id = :id";
				$q->setParameter(":id",  $dati->getId() );
			}
			if ($dati->getIdPersona() != "") {
				$dql .= " AND p.id LIKE :id_persona";
				$q->setParameter(":id_persona", "%" . $dati->getIdPersona() . "%");
			}
			if ($dati->getRuoliEsclusi() != "") {
				$ruoli = $dati->getRuoliEsclusi();

				if (!is_array($ruoli)) {
					$ruoli = array($ruoli);
				}
				
				$pieces = array();
				foreach ($ruoli as $key => $ruolo) {
					$pieces[] = "u.roles NOT LIKE :ruolo$key";
					$q->setParameter(":ruolo$key", "%" ."\"$ruolo\"". "%");
				}
				
				$dql .= " AND (". implode(" AND ", $pieces).")";	
				
			}			
			$dql .= ")";
			
			$dql .= " ORDER BY p.cognome";
		// }
		$q->setDQL($dql);

		return $q->getResult();
	}
	
	public function cercaIstruttoriAtc() {
		$dql = "SELECT NEW UtentePersonaDTO(u.id, p.nome, p.cognome) FROM SfingeBundle:Utente u "
				. "LEFT JOIN u.persona p "
				. "WHERE u.roles LIKE '%ROLE_ISTRUTTORE_ATC%' ";

		$q = $this->getEntityManager()->createQuery();
			
		$dql .= " ORDER BY p.cognome";

		$q->setDQL($dql);

		return $q->getResult();
	}
	
	public function cercaUtentiPaDTO() {
		$dql = "SELECT NEW UtentePersonaDTO(u.id, p.nome, p.cognome) FROM SfingeBundle:Utente u "
				. "JOIN u.persona p "
				. "WHERE u.roles LIKE '%ROLE_UTENTE_PA%'";

		$q = $this->getEntityManager()->createQuery();
			
		$dql .= " ORDER BY p.cognome";

		$q->setDQL($dql);

		return $q->getResult();
	}

	public function cercaUtentiByPersonaId() {
		$dql = "SELECT u FROM SfingeBundle:Utente u WHERE u.persona IS NULL";
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	// $admin è true se devo cercare anche l'admin. Per cercare gli utenti che possono creare le procedure, non ho bisogno degli admin.
	public function cercaUtentiPA($ritornaAdmin = false){
		$dql = "SELECT u FROM SfingeBundle:Utente u
				WHERE u.roles like '%ROLE_UTENTE_PA%' OR u.roles like '%ROLE_MANAGER_PA%' ";
		if($ritornaAdmin){
			$dql .= " OR u.roles like '%ROLE_ADMIN_PA%' ";
		}
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function cercaUtentiPAQb(bool $ritornaAdmin = false): QueryBuilder {
		$qb = $this->createQueryBuilder('u');
		$expr = $qb->expr();
		$qb->where(
			$expr->orX(
				$expr->like("u.roles", "'%ROLE_UTENTE_PA%'"),
				$expr->like("u.roles", "'%ROLE_MANAGER_PA%'"),
				$expr->andX(
					$expr->like("u.roles", "'%ROLE_ADMIN_PA%'"),
					$expr->eq(1, ':ritorna_admin')
				)
			)
		)
		->setParameter('ritorna_admin', $ritornaAdmin);

		return $qb;
	}
	
	public function cercaUtentiAssociabiliProcedureAssi(){
		$dql = "SELECT u FROM SfingeBundle:Utente u JOIN u.persona p
				WHERE u.roles like '%ROLE_UTENTE_PA%' OR u.roles like '%ROLE_MANAGER_PA%' OR u.roles like '%ROLE_ISTRUTTORE%' OR u.roles like '%ROLE_VALUTATORE%' ";
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}	

	public function getUtenteByUsername($username) {
		$q = $this->getEntityManager()->createQuery();
		$dql = "SELECT u FROM SfingeBundle:Utente u ";
		$dql .= " WHERE u.username = :username";
		
		$q = $this->getEntityManager()->createQuery();
		$q->setParameter(":username", $username);

		$q->setDQL($dql);
		return $q->getSingleResult();
	}
	
	public function cercaUtentiByPersonaCf($codice_fiscale) {
		$dql = "SELECT u FROM SfingeBundle:Utente u "
				. " JOIN u.persona p "
				. " WHERE p.codice_fiscale = '$codice_fiscale' ";
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
	
	public function findPersonaByUsername($codice_fiscale) {
		$dql = "SELECT p.nome, p.cognome FROM SfingeBundle:Utente u "
				. " JOIN u.persona p "
				. " WHERE u.username = '$codice_fiscale' ";
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
		
}
