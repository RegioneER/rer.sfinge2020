<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * CertificazioneChiusuraRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RegistroDebitoriRepository extends EntityRepository {

	public function getImportiIrregolariPerAsse($asse, $codice_irregolarita = null) {
		$dql = "SELECT rev.id FROM AttuazioneControlloBundle:Revoche\Revoca rev "
				. "JOIN rev.attuazione_controllo_richiesta atc "
				. "JOIN rev.atto_revoca atto "
				. "JOIN atto.tipo_motivazione mot "
				. "JOIN atc.pagamenti p "
				. "JOIN atc.richiesta rich "
				. "JOIN p.certificazioni cp "
				. "JOIN cp.certificazione c "
				. "JOIN rich.procedura proc "
				. "JOIN proc.asse ax ";

		if (!is_null($codice_irregolarita)) {
			$dql .= "JOIN rev.tipo_irregolarita irr ";
		}

		$dql .= "WHERE ax.codice = '$asse' AND mot.codice = '3' ";

		if (!is_null($codice_irregolarita)) {
			$dql .= "AND irr.codice = '$codice_irregolarita' ";
		}

		$dql .= "GROUP BY rev.id  ";

		$dqlFinale = "SELECT SUM(rev2.contributo) FROM AttuazioneControlloBundle:Revoche\Revoca rev2  WHERE rev2.id IN (" . $dql . ")";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dqlFinale);

		$importo_totale = $q->getSingleScalarResult();
		return $importo_totale;
	}

	public function ricercaProgettiIrregolariPerAsse($ricercaDebitori) {
		/*$dql = "SELECT rich FROM RichiesteBundle:Richiesta rich "
				. "JOIN rich.attuazione_controllo atc "
				. "JOIN rich.istruttoria i "
				. "JOIN rich.proponenti prop "
				. "JOIN prop.soggetto s "
				. "JOIN atc.pagamenti p "
				. "JOIN atc.revoca rev "
				. "JOIN rev.atto_revoca atto "
				. "JOIN atto.tipo_motivazione mot "
				. "JOIN p.certificazioni cp "
				. "JOIN cp.certificazione c "
				. "JOIN rich.procedura proc "
				. "JOIN proc.asse ax ";
		*/
		$dql = "SELECT rev FROM AttuazioneControlloBundle:Revoche\Revoca rev "
				. "JOIN rev.attuazione_controllo_richiesta atc "
				. "JOIN atc.richiesta rich "
				. "JOIN rich.istruttoria i "
				. "JOIN rich.proponenti prop "
				. "JOIN prop.soggetto s "
				. "JOIN atc.pagamenti p "
				. "JOIN rev.atto_revoca atto "
				. "JOIN atto.tipo_motivazione mot "
				. "JOIN p.certificazioni cp "
				. "JOIN cp.certificazione c "
				. "JOIN rich.procedura proc "
				. "JOIN proc.asse ax ";

		$dql .= "WHERE ax.codice = :asse  AND mot.codice = '3' ";

		$q = $this->getEntityManager()->createQuery();

		$q->setParameter("asse", $ricercaDebitori->getAsse());

		if (!is_null($ricercaDebitori->getBeneficiario())) {
			$dql .= " AND s.denominazione LIKE :denominazione ";
			$q->setParameter("denominazione", "%" . $ricercaDebitori->getBeneficiario() . "%");
		}

		if (!is_null($ricercaDebitori->getCup())) {
			$dql .= " AND (i.codice_cup LIKE :cup OR atc.cup LIKE :cup)";
			$q->setParameter("cup", "%" . $ricercaDebitori->getCup() . "%");
		}

		//$dql .= "GROUP BY rich.id  ";

		$q->setDQL($dql);

		return $q;
	}
	
	public function getImportiOlafPerAsse($asse) {
		$dql = "SELECT rev.id FROM CertificazioniBundle:RegistroDebitori reg "
				. "JOIN reg.richiesta rich "
				. "JOIN rich.attuazione_controllo atc "
				. "JOIN atc.revoca rev "
				. "JOIN rev.atto_revoca atto "
				. "JOIN atto.tipo_motivazione mot "
				. "JOIN rich.procedura proc "
				. "JOIN proc.asse ax ";


		$dql .= "WHERE ax.codice = '$asse' AND reg.olaf = 1 AND mot.codice = '3' ";

		$dql .= "GROUP BY rev.id  ";

		$dqlFinale = "SELECT SUM(rev2.contributo) FROM AttuazioneControlloBundle:Revoche\Revoca rev2  WHERE rev2.id IN (" . $dql . ")";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dqlFinale);

		$importo_totale = $q->getSingleScalarResult();
		return $importo_totale;
	}
	
	public function ricercaProgettiOlafPerAsse($ricercaDebitori) {
		
		$dql = "SELECT rev FROM AttuazioneControlloBundle:Revoche\Revoca rev "
				. "JOIN rev.attuazione_controllo_richiesta atc "
				. "JOIN atc.richiesta rich "
				. "JOIN rich.registro reg "
				. "JOIN rich.istruttoria i "
				. "JOIN rich.proponenti prop "
				. "JOIN prop.soggetto s "
				. "JOIN atc.pagamenti p "
				. "JOIN rev.atto_revoca atto "
				. "JOIN atto.tipo_motivazione mot "
				. "JOIN p.certificazioni cp "
				. "JOIN cp.certificazione c "
				. "JOIN rich.procedura proc "
				. "JOIN proc.asse ax ";

		$dql .= "WHERE ax.codice = :asse AND reg.olaf = 1 AND mot.codice = '3' ";

		$q = $this->getEntityManager()->createQuery();

		$q->setParameter("asse", $ricercaDebitori->getAsse());

		if (!is_null($ricercaDebitori->getBeneficiario())) {
			$dql .= " AND s.denominazione LIKE :denominazione ";
			$q->setParameter("denominazione", "%" . $ricercaDebitori->getBeneficiario() . "%");
		}

		if (!is_null($ricercaDebitori->getCup())) {
			$dql .= " AND (i.codice_cup LIKE :cup OR atc.cup LIKE :cup)";
			$q->setParameter("cup", "%" . $ricercaDebitori->getCup() . "%");
		}

		$q->setDQL($dql);

		return $q;
	}

}
