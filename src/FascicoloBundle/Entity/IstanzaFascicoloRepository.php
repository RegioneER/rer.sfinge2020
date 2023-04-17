<?php
namespace FascicoloBundle\Entity;

use Doctrine\ORM\EntityRepository;

class IstanzaFascicoloRepository extends EntityRepository
{
    /**
     * @param $id_bando
     * @return array|int|string
     */
    public function getRichiesteBando($id_bando)
    {
        $query = "SELECT istanzaFascicolo
                FROM FascicoloBundle:IstanzaFascicolo istanzaFascicolo
                JOIN istanzaFascicolo.oggetto_richiesta oggettoRichiesta   
                JOIN oggettoRichiesta.richiesta richiesta
				WHERE richiesta.procedura = :procedura_id";

        $q = $this->getEntityManager()->createQuery($query);
        $q->setParameter("procedura_id", $id_bando);
        return $q->getResult();
	}
}
