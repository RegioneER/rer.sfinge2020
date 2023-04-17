<?php


namespace RichiesteBundle\Entity;

use Doctrine\ORM\EntityRepository;
use SfingeBundle\Entity\Procedura;

class PianoCostoRepository extends EntityRepository {
	
	public function getVociDaProcedura($id_procedura) {

		$dql = "SELECT piano FROM RichiesteBundle:PianoCosto piano "
				. "JOIN piano.procedura proc "
				. "WHERE proc.id = $id_procedura";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

	public function getDistinctVociDaProcedura($id_procedura){
		$dql = "SELECT DISTINCT piano FROM RichiesteBundle:PianoCosto piano "
			. "JOIN piano.procedura proc "
			. "WHERE proc.id = $id_procedura "
			. "GROUP BY piano.identificativo_pdf ORDER BY piano.ordinamento ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
	
	public function getDistinctVociDaProceduraCodiceSezione($id_procedura, $codiceSezione){
		$dql = "SELECT DISTINCT piano FROM RichiesteBundle:PianoCosto piano "
			. "JOIN piano.procedura proc "
			. "JOIN piano.sezione_piano_costo spc "
			. "WHERE proc.id = $id_procedura AND spc.codice = '$codiceSezione' " 
			. "GROUP BY piano.identificativo_pdf";

		$q = $this->getEntityManager()->createQuery();

		$q->setDQL($dql);

		return $q->getResult();
	}

	public function getSezioniDaProcedura($id_procedura){
		$dql = "SELECT DISTINCT spc FROM RichiesteBundle:SezionePianoCosto spc "
			. "JOIN spc.piani_costo piano "
			. "JOIN piano.procedura proc "
			. "WHERE proc.id = $id_procedura";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
	
	public function getSezioniDaProceduraCodice($id_procedura, $codice) {
		$dql = "SELECT DISTINCT spc FROM RichiesteBundle:SezionePianoCosto spc "
			. "JOIN spc.piani_costo piano "
			. "JOIN piano.procedura proc "
			. "WHERE proc.id = $id_procedura AND spc.codice = '$codice'" ;

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
	
	public function getVociDaProceduraSezione($id_procedura, $sezione) {

		$dql = "SELECT piano FROM RichiesteBundle:PianoCosto piano "
				. "JOIN piano.procedura proc "
				. "JOIN piano.sezione_piano_costo spc "
				. "WHERE proc.id = $id_procedura AND spc.codice = '$sezione'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
	
	public function getVociAssTecnica() {

		$dql = "SELECT piano FROM RichiesteBundle:PianoCosto piano "
				. "JOIN piano.sezione_piano_costo spc "
				. "WHERE spc.codice = 'ASS_TECNICA'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleResult();
	}
	
	public function getVociIngFinanziaria() {

		$dql = "SELECT piano FROM RichiesteBundle:PianoCosto piano "
				. "JOIN piano.sezione_piano_costo spc "
				. "WHERE spc.codice = 'ING_FIN'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleResult();
	}
	
	public function getVociAcquisizioni(?Procedura $procedura = null) {
		if(\is_null($procedura)){
			$dql = "SELECT piano FROM RichiesteBundle:PianoCosto piano "
					. "JOIN piano.sezione_piano_costo spc "
					. "WHERE spc.codice = 'ACQUISIZIONI'";
	
			$q = $this->getEntityManager()->createQuery();
			$q->setDQL($dql);
	
			return $q->getResult();
		}

		return $this->getVociDaProcedura($procedura->getId());
	}
	
	public function getVoceDaProceduraSezioneCodice($id_procedura, $sezione, $codice) {

		$dql = "SELECT piano FROM RichiesteBundle:PianoCosto piano "
				. "JOIN piano.procedura proc "
				. "JOIN piano.sezione_piano_costo spc "
				. "WHERE proc.id = $id_procedura AND spc.codice = '$sezione' AND piano.codice = '$codice' ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleResult();
	}
	
	public function getVociDaProceduraSenzaTotale($id_procedura) {

		$dql = "SELECT piano FROM RichiesteBundle:PianoCosto piano "
				. "JOIN piano.procedura proc "
				. "JOIN piano.tipo_voce_spesa tipo "
				. "WHERE proc.id = {$id_procedura} AND tipo.codice <> 'TOTALE' "
				. "ORDER BY piano.ordinamento ASC ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
        
        public function getVociDaProceduraInterventi180($id_procedura) {

		$dql = "SELECT piano FROM RichiesteBundle:PianoCosto piano "
				. "JOIN piano.procedura proc "
				. "JOIN piano.tipo_voce_spesa tipo "
				. "WHERE proc.id = {$id_procedura} AND tipo.codice <> 'TOTALE' AND tipo.codice <> 'PROTOTIPI' AND tipo.codice <> 'SPES_GEN_ACC' "
				. "ORDER BY piano.ordinamento ASC ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

    /**
     * @param int $id_procedura
     * @param string $codiceSezione
     * @return array
     */
    public function getDistinctVociDaProceduraCodiceSezioneLike($id_procedura, $codiceSezione){
        $dql = "SELECT DISTINCT piano " 
            . "FROM RichiesteBundle:PianoCosto piano "
            . "JOIN piano.procedura procedura "
            . "JOIN piano.sezione_piano_costo sezione "
            . "WHERE procedura.id = $id_procedura AND sezione.codice LIKE '$codiceSezione%' "
            . "GROUP BY piano.identificativo_pdf";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        return $q->getResult();
    }

    /**
     * @param $id_procedura
     * @param $codice_sezione
     * @param array $sezioni_da_escludere
     * @return array
     */
    public function getCodiciVociDaSezione($id_procedura, $codice_sezione, $sezioni_da_escludere = []) {

        $dql = "SELECT DISTINCT piano.codice "
            . "FROM RichiesteBundle:PianoCosto piano "
            . "JOIN piano.procedura procedura "
            . "JOIN piano.sezione_piano_costo sezione "
            . "WHERE procedura.id = :id_procedura AND sezione.codice = :codice_sezione ";
        
        foreach ($sezioni_da_escludere as $sezione) {
            $dql .= " AND piano.codice <> '$sezione'";
        }

        $q = $this->getEntityManager()->createQuery();
        $q->setParameter('id_procedura', $id_procedura);
        $q->setParameter('codice_sezione', $codice_sezione);
        $q->setDQL($dql);
        
        $retVal = [];
        foreach ($q->getResult() as $result) {
            $retVal[$result['codice']] = $result['codice'];
        }
        
        return $retVal;
    }
}
