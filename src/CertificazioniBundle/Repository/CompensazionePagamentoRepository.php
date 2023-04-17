<?php

namespace CertificazioniBundle\Repository;

use Doctrine\ORM\EntityRepository;


class CompensazionePagamentoRepository extends EntityRepository {
	
	public function findCompensazioniPagamento($id_pagamento) {
		$dql = "SELECT cpag 
            FROM CertificazioniBundle:CompensazionePagamento cpag
            JOIN cpag.pagamento pag
			WHERE pag.id = :id_pagamento ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("id_pagamento", $id_pagamento);

        $res = $q->getResult();

        return $res;
	}
	
	public function getPagamentiCompensati($ricerca) {

		$dql = "SELECT certpag "
				. "FROM CertificazioniBundle:CompensazionePagamento certpag "
				. "JOIN certpag.pagamento pag "
				. "JOIN pag.attuazione_controllo_richiesta ac "
				. "JOIN ac.richiesta rich "
				. "JOIN rich.istruttoria ist "
				. "JOIN rich.proponenti prop "
				. "JOIN prop.soggetto sogg "
				. "JOIN rich.procedura proc "
				. "JOIN proc.asse asse "
				. "WHERE certpag.importo <> 0 ";
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
			$q->setParameter("beneficiario", "%" . $ricerca->getBeneficiario() ."%");
		}
		
		if (!is_null($ricerca->getCup())) {
			$dql .= " AND (ac.cup LIKE :cup OR ist.codice_cup LIKE :cup) ";
			$q->setParameter("cup", "%" . $ricerca->getCup() ."%");
		}

		$q->setDQL($dql);
		return $q;
	}
	
}
