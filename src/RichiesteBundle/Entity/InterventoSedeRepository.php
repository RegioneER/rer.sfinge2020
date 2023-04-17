<?php

namespace RichiesteBundle\Entity;

use Doctrine\ORM\EntityRepository;


class InterventoSedeRepository extends EntityRepository
{
	public function getInterventiDaProponenteVoce($proponente, $voce_piano, $sezione = null) {
		$dql = "SELECT int.descrizione, int.costo, int.annualita, int.ulteriore_descrizione FROM RichiesteBundle:InterventoSede int "
				. "JOIN int.piano_costo piano "
                                . "JOIN piano.sezione_piano_costo sezione "
				. "JOIN int.sede_operativa sede "
				. "JOIN sede.proponente prop "
				. "WHERE prop.id = {$proponente->getId()} AND piano.codice = '$voce_piano'";
                
                if(!is_null($sezione)) {
                    $dql .= " AND sezione.codice = '$sezione'";
                }
                                

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
    
    public function getInterventiDaProponenteVoceSoloDescrizione($proponente, $voce_piano) {
		$dql = "SELECT int.descrizione FROM RichiesteBundle:InterventoSede int "
				. "JOIN int.piano_costo piano "
				. "JOIN int.sede_operativa sede "
				. "JOIN sede.proponente prop "
				. "WHERE prop.id = {$proponente->getId()} AND piano.codice = '$voce_piano'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
	
	public function getInterventiDaProponente($proponente) {
		$dql = "SELECT piano.id, concat(piano.codice,') ',piano.titolo) as titolo, "
				. "SUM ( CASE WHEN int.annualita = '2018' THEN int.costo ELSE 0 END) as importo_2018, "
				. "SUM ( CASE WHEN int.annualita = '2019' THEN int.costo ELSE 0 END) as importo_2019 "
				. "FROM RichiesteBundle:InterventoSede int "
				. "JOIN int.piano_costo piano "
				. "JOIN int.sede_operativa sede "
				. "JOIN sede.proponente prop "
				. "WHERE prop.id = {$proponente->getId()} "
				. "GROUP BY piano.id ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

    public function getInterventiDaProponenteSenzaAnnualita($proponente) {
        $dql = "SELECT piano.id, concat(piano.codice,') ',piano.titolo) as titolo, "
            . "SUM (COALESCE(int.costo, 0)) as importo "
            . "FROM RichiesteBundle:InterventoSede int "
            . "JOIN int.piano_costo piano "
            . "JOIN int.sede_operativa sede "
            . "JOIN sede.proponente prop "
            . "WHERE prop.id = {$proponente->getId()} "
            . "GROUP BY piano.id ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        return $q->getResult();
    }
    
    public function getInterventiDaProponenteSenzaAnnualitaSezione($proponente) {
        $dql = "SELECT piano.id, concat(piano.codice,') ',piano.titolo) as titolo, sezione.codice as sezione_codice, "
            . "SUM (COALESCE(int.costo, 0)) as importo "
            . "FROM RichiesteBundle:InterventoSede int "
            . "JOIN int.piano_costo piano "
            . "JOIN piano.sezione_piano_costo sezione "
            . "JOIN int.sede_operativa sede "
            . "JOIN sede.proponente prop "
            . "WHERE prop.id = {$proponente->getId()} "
            . "GROUP BY piano.id ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        return $q->getResult();
    }
    
    public function getInterventiDaRichiestaVoce($richiesta, $voce_piano) {
		$dql = "SELECT int.descrizione, int.costo, int.annualita FROM RichiesteBundle:InterventoSede int "
				. "JOIN int.piano_costo piano "
				. "JOIN int.richiesta rich "
				. "WHERE rich.id = {$richiesta->getId()} AND piano.codice = '$voce_piano'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
	
	public function getInterventiDaRichiesta($richiesta) {
		$dql = "SELECT piano.id, concat(piano.codice,') ',piano.titolo) as titolo, "
				. "SUM (coalesce(int.costo)) as importo "
				. "FROM RichiesteBundle:InterventoSede int "
				. "JOIN int.piano_costo piano "
				. "JOIN int.richiesta rich "
				. "WHERE rich.id = {$richiesta->getId()} "
				. "GROUP BY piano.id ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
}
