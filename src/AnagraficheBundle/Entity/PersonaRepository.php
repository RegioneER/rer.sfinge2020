<?php

namespace AnagraficheBundle\Entity;

use Doctrine\ORM\EntityRepository;
use SoggettoBundle\Entity\StatoIncarico;
use SoggettoBundle\Form\Entity\RicercaIncaricati;
use SoggettoBundle\Form\Entity\RicercaPersonaIncaricabile;
use AnagraficheBundle\Form\Entity\RicercaPersonaAdmin;
use AnagraficheBundle\Form\Entity\RicercaPersone;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Entity\TipoIncarico;
use Doctrine\ORM\QueryBuilder;

class PersonaRepository extends EntityRepository {

    public function cercaIncaricati(RicercaIncaricati $dati) {
        $dql = "SELECT ip from SoggettoBundle:IncaricoPersona ip
				JOIN ip.incaricato p
				JOIN ip.soggetto s
                JOIN ip.tipo_incarico tipo
				WHERE 1=1 ";

        $q = $this->getEntityManager()->createQuery();

        if (!\is_null($dati->getSoggettoId())) {
            $dql .= " AND s.id = :soggettoId ";
            $q->setParameter("soggettoId", $dati->getSoggettoId());
        }


        if (!\is_null($dati->getStatoIncarico())) {
            $dql .= " AND ip.stato = :statoIncarico";
            $q->setParameter("statoIncarico", $dati->getStatoIncarico());
        }

        if (!\is_null($dati->getIncarico())) {
            $dql .= " AND ip.tipo_incarico = :incarico";
            $q->setParameter("incarico", $dati->getIncarico());
        }

        if ($dati->getCodiceFiscale() != "") {
            $dql .= " AND p.codice_fiscale = :codice_fiscale";
            $q->setParameter(":codice_fiscale", $dati->getCodiceFiscale());
        }

        if ($dati->getNome() != "") {
            $dql .= " AND p.nome LIKE :nome";
            $q->setParameter(":nome", "%" . $dati->getNome() . "%");
        }

        if ($dati->getCognome() != "") {
            $dql .= " AND p.cognome LIKE :cognome";
            $q->setParameter(":cognome", "%" . $dati->getCognome() . "%");
        }

        if ($dati->getEmail() != "") {
            $dql .= " AND p.email_principale = :email_principale";
            $q->setParameter(":email_principale", $dati->getEmail());
        }

        if ($dati->getDenominazione() != "") {
            $dql .= " AND s.denominazione LIKE :denominazione";
            $q->setParameter(":denominazione", "%" . $dati->getDenominazione() . "%");
        }

        $q->setDQL($dql);

        return $q;
    }

    public function cercaDelegatoPresente(RicercaPersonaIncaricabile $dati) {
         $q = $this->getEntityManager()->createQuery();
        $dql = "SELECT ip from SoggettoBundle:IncaricoPersona ip
				JOIN ip.incaricato p
				JOIN ip.soggetto s
                                JOIN ip.tipo_incarico tipo
                                JOIN ip.stato si
                                WHERE p.cognome LIKE :cognome 
                                AND p.codice_fiscale = :codice_fiscale 
                                AND s.id=:soggettoId 
                                AND tipo.id= :tipoIncaricoId 
                                AND si.codice=:statoAttivo";

        $q->setParameter("soggettoId", $dati->getSoggettoId());
        $q->setParameter("tipoIncaricoId", $dati->getTipoIncarico()->getId());
        $q->setParameter("statoAttivo", StatoIncarico::ATTIVO);

        $q->setParameter("cognome", "%" . $dati->getCognome() . "%");
        $q->setParameter("codice_fiscale", $dati->getCodiceFiscale());

        $q->setDQL($dql);
        $a = $q->getSQL();
        return $q->getResult();
    }

    public function cercaIncaricabili(RicercaPersonaIncaricabile $dati) {
        $q = $this->getEntityManager()->createQuery();
        $dql = "SELECT p FROM AnagraficheBundle:Persona p ";
        //se il tipo di incarico prevede un ruolo applicativo impongo il vincolo
        if ($dati->getTipoIncarico()->hasRuoloApplicativo()) {
            $dql .= " JOIN SfingeBundle:Utente u WITH u.persona = p ";
        }
        $dql .= "
				WHERE  p.cognome LIKE :cognome AND p.codice_fiscale = :codice_fiscale";
        if ($dati->getNome() != "") {
            $dql .= " AND p.nome LIKE :nome ";
            $q->setParameter("nome", "%" . $dati->getNome() . "%");
        }

        $dql .= "	AND p.id NOT IN (
					SELECT pi.id FROM AnagraficheBundle:Persona pi
					JOIN pi.incarichi_persone ip
					JOIN ip.soggetto s
					JOIN ip.tipo_incarico ti
					JOIN ip.stato si
					 WHERE s.id=:soggettoId AND ti.id= :tipoIncaricoId AND si.codice=:statoAttivo
				)";

        $q->setParameter("soggettoId", $dati->getSoggettoId());
        $q->setParameter("tipoIncaricoId", $dati->getTipoIncarico()->getId());
        $q->setParameter("statoAttivo", StatoIncarico::ATTIVO);

        $q->setParameter("cognome", "%" . $dati->getCognome() . "%");
        $q->setParameter("codice_fiscale", $dati->getCodiceFiscale());
        $q->setDQL($dql);
        return $q->getResult();
    }

    public function cercaSuperAdmin(RicercaPersonaAdmin $dati) {
        $dql = "SELECT DISTINCT p FROM AnagraficheBundle:Persona p
				LEFT JOIN p.incarichi_persone ip
				LEFT JOIN ip.tipo_incarico ti
				LEFT JOIN p.utente u
				LEFT JOIN ip.soggetto s
				WHERE 1=1 ";

        $q = $this->getEntityManager()->createQuery();

        if ($dati->getEmail() != "") {
            $dql .= " AND p.email_principale LIKE :email_principale";
            $q->setParameter(":email_principale", "%" . $dati->getEmail() . "%");
        }

        if ($dati->getUsername() != "") {
            $dql .= " AND u.username LIKE :username";
            $q->setParameter(":username", "%" . $dati->getUsername() . "%");
        }

        if (!is_null($dati->getUtenteRicercante())) {
            if (!$dati->getUtenteRicercante()->hasRole('ROLE_SUPER_ADMIN')) {
                $dql .= " AND (u.roles is null or u.roles not LIKE '%ROLE_SUPER_ADMIN%')";
            }
        }

        if ($dati->getRuolo() != "") {
            $dql .= " AND u.roles LIKE :ruolo";
            $q->setParameter(":ruolo", "%" . $dati->getRuolo() . "%");
        }


        if ($dati->getCodiceFiscale() != "") {
            $dql .= " AND p.codice_fiscale = :codice_fiscale";
            $q->setParameter(":codice_fiscale", $dati->getCodiceFiscale());
        }

        if ($dati->getNome() != "") {
            $dql .= " AND p.nome LIKE :nome";
            $q->setParameter(":nome", "%" . $dati->getNome() . "%");
        }

        if ($dati->getCognome() != "") {
            $dql .= " AND p.cognome LIKE :cognome";
            $q->setParameter(":cognome", "%" . $dati->getCognome() . "%");
        }

        if ($dati->getSoggettoId() != "") {
            $dql .= " AND s.id = :soggetto_id";
            $q->setParameter(":soggetto_id", $dati->getSoggettoId());
        }

        if ($dati->getSoggettoDenominazione() != "") {
            $dql .= " AND s.denominazione LIKE :soggetto_denominazione";
            $q->setParameter(":soggetto_denominazione", "%" . $dati->getSoggettoDenominazione() . "%");
        }

        if ($dati->getSoggettoPiva() != "") {
            $dql .= " AND s.partita_iva = :soggetto_piva";
            $q->setParameter(":soggetto_piva", $dati->getSoggettoPiva());
        }

        if (!is_null($dati->getSoggettoIncarico())) {
            $dql .= " AND ti.codice = :tipo_incarico_codice";
            $q->setParameter(":tipo_incarico_codice", $dati->getSoggettoIncarico()->getCodice());
        }

        $q->setDQL($dql);
        return $q;
    }

    public function cercaPersone(RicercaPersone $dati) {
        $dql = "SELECT p FROM AnagraficheBundle:Persona p WHERE 1=1 ";

        $q = $this->getEntityManager()->createQuery();

        if ($dati->getUtente() != "") {
            $dql .= "AND p.creato_da = :utente ";
            $q->setParameter("utente", $dati->getUtente());
        }

        $and = array();
        if ($dati->getCodiceFiscale() != "") {
            $and[] = " p.codice_fiscale = :codice_fiscale";
            $q->setParameter(":codice_fiscale", $dati->getCodiceFiscale());
        }

        if ($dati->getNome() != "") {
            $and[] = " p.nome LIKE :nome";
            $q->setParameter(":nome", "%" . $dati->getNome() . "%");
        }

        if ($dati->getCognome() != "") {
            $and[] = " p.cognome LIKE :cognome";
            $q->setParameter(":cognome", "%" . $dati->getCognome() . "%");
        }
        if ($dati->getEmailPrincipale() != "") {
            $and[] = " p.email_principale LIKE :email_principale";
            $q->setParameter(":email_principale", "%" . $dati->getEmailPrincipale() . "%");
        }

        foreach ($and as $condizione) {
            $dql .= " AND " . $condizione;
        }

        $q->setDQL($dql);

        return $q->getResult();
    }

    public function cercaPersoneByCf($codice_fiscale) {
        $dql = "SELECT p FROM AnagraficheBundle:Persona p WHERE p.data_cancellazione IS NULL ";
        $dql .= " AND p.codice_fiscale = :codice_fiscale";
        $q = $this->getEntityManager()->createQuery();
        $q->setParameter(":codice_fiscale", $codice_fiscale);
        $q->setDQL($dql);
        return $q->getResult();
    }

    public function cercaPersoneByEmailPrincipale($email) {
        $dql = "SELECT p FROM AnagraficheBundle:Persona p WHERE p.data_cancellazione IS NULL ";
        $dql .= " AND p.email_principale = :email";
        $q = $this->getEntityManager()->createQuery();
        $q->setParameter(":email", $email);
        $q->setDQL($dql);

        return $q->getResult();
    }

    public function cercaPersoneSenzaUtenza() {

        $q = $this->getEntityManager()->createQuery();
        $dql = "SELECT p FROM AnagraficheBundle:Persona p ";
        $dql .= " LEFT JOIN p.utente u ";
        $dql .= " WHERE u.id IS NULL";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        return $q->getResult();
    }

    public function getPersonaByUsername($username) {
        $q = $this->getEntityManager()->createQuery();
        $dql = "SELECT p FROM AnagraficheBundle:Persona p ";
        $dql .= " JOIN p.utente u ";
        $dql .= " WHERE u.username LIKE :username";

        $q = $this->getEntityManager()->createQuery();
        $q->setParameter(":username", "%" . $username . "%");

        $q->setDQL($dql);
        return $q->getResult();
    }

    public function getDirigenti() {
        $dql = "SELECT p FROM AnagraficheBundle:Persona p 
		        JOIN p.utente u 
				WHERE u.roles not like '%ROLE_SUPER_ADMIN%' AND (u.roles like '%ROLE_UTENTE_PA%' OR u.roles like '%ROLE_MANAGER_PA%' OR u.roles like '%ROLE_ADMIN_PA%')";
        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        return $q->getResult();
    }

    public function cercaPersonePagamento($dati) {
        $dql = "SELECT p FROM AnagraficheBundle:Persona p WHERE 1=1 ";

        $q = $this->getEntityManager()->createQuery();

        if ($dati->getUtente() != "") {
            $dql .= "AND p.creato_da = :utente ";
            $q->setParameter("utente", $dati->getUtente());
        }

        $and = array();
        if ($dati->getCodiceFiscale() != "") {
            $and[] = " p.codice_fiscale = :codice_fiscale";
            $q->setParameter(":codice_fiscale", $dati->getCodiceFiscale());
        }

        if ($dati->getNome() != "") {
            $and[] = " p.nome LIKE :nome";
            $q->setParameter(":nome", "%" . $dati->getNome() . "%");
        }

        if ($dati->getCognome() != "") {
            $and[] = " p.cognome LIKE :cognome";
            $q->setParameter(":cognome", "%" . $dati->getCognome() . "%");
        }

        foreach ($and as $condizione) {
            $dql .= " AND " . $condizione;
        }

        $q->setDQL($dql);

        return $q->getResult();
    }

    public function getQueryFirmatariAmmissibili(): QueryBuilder {
        $qb = $this->createQueryBuilder('firmatario');
        $expr = $qb->expr();
        return $qb->innerJoin('firmatario.incarichi_persone', 'incarichi')
                ->innerJoin('incarichi.soggetto', 'soggetto')
                //->innerJoin('soggetto.proponenti', 'proponenti')
                ->innerJoin('incarichi.tipo_incarico', 'tipo_incarico')
                ->innerJoin('incarichi.stato', 'stato_incarico')
                ->where(
                    $expr->in('tipo_incarico.codice', ':legale_rappresentante, :delegato'),
                    $expr->eq('stato_incarico.codice', ':stato_attivo')
                )
                ->setParameter('legale_rappresentante', TipoIncarico::LR)
                ->setParameter('delegato', TipoIncarico::DELEGATO)
                ->setParameter('stato_attivo', StatoIncarico::ATTIVO);
    }

    public function findPersonaByNomeOrCodiceFiscale(string $query): array {
        $qb = $this->createQueryBuilder('p');
        $expr = $qb->expr();
        return $qb
                ->leftJoin('p.utente', 'u')
                ->where(
                    "LENGTH(:query) > 3",
                    $expr->isNotNull('p.codice_fiscale'),
                    $expr->orX(
                        $expr->like('u.roles', $expr->literal('%"ROLE_UTENTE"%')),
                        $expr->isNull('u.id')
                    ),
                    $expr->orX(
                        $expr->eq('p.codice_fiscale', ':query'),
                        $expr->like("CONCAT(p.nome, ' ', p.cognome)", $expr->concat($expr->literal('%'), $expr->concat(":query", $expr->literal("%"))))
                    )
                )
                ->setParameter('query', $query)
                ->getQuery()
                ->getResult();
    }

    public function cercaOperatoriRichiesta(RicercaIncaricati $dati) {
        $dql = "SELECT ip from SoggettoBundle:IncaricoPersonaRichiesta ipr
                JOIN ipr.incarico_persona ip
				JOIN ip.incaricato p
				JOIN ip.soggetto s
                JOIN ip.tipo_incarico tipo
				WHERE tipo.codice = 'OPERATORE_RICHIESTA' ";

        $q = $this->getEntityManager()->createQuery();

        if (!\is_null($dati->getSoggettoId())) {
            $dql .= " AND s.id = :soggettoId ";
            $q->setParameter("soggettoId", $dati->getSoggettoId());
        }

        if ($dati->getCodiceFiscale() != "") {
            $dql .= " AND p.codice_fiscale = :codice_fiscale";
            $q->setParameter(":codice_fiscale", $dati->getCodiceFiscale());
        }

        if ($dati->getNome() != "") {
            $dql .= " AND p.nome LIKE :nome";
            $q->setParameter(":nome", "%" . $dati->getNome() . "%");
        }

        if ($dati->getCognome() != "") {
            $dql .= " AND p.cognome LIKE :cognome";
            $q->setParameter(":cognome", "%" . $dati->getCognome() . "%");
        }

        if ($dati->getEmail() != "") {
            $dql .= " AND p.email_principale = :email_principale";
            $q->setParameter(":email_principale", $dati->getEmail());
        }

        if ($dati->getDenominazione() != "") {
            $dql .= " AND s.denominazione LIKE :denominazione";
            $q->setParameter(":denominazione", "%" . $dati->getDenominazione() . "%");
        }

        $q->setDQL($dql);

        return $q;
    }

}
