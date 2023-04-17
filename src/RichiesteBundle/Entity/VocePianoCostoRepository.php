<?php

namespace RichiesteBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RichiesteBundle\Entity\Proponente;

class VocePianoCostoRepository extends EntityRepository {
	
	private function getWhereConditionSezione($id_sezione, $alias_sezione_piano_costo = 'sez'){
		
		if(is_array($id_sezione)){
			$where_sezione = "";
			foreach ($id_sezione as $id) {
				$where_sezione .= "$alias_sezione_piano_costo.id = $id OR ";
			}
			// O rimuovo l'ultimo OR o aggiungo la seguente condizione farlocca
			$where_sezione .= "true = true";
		} else {
			$where_sezione = "$alias_sezione_piano_costo.id = $id_sezione";
		}
		
		return '(' . $where_sezione . ')';
		
	}
	
	public function getVoceDaProponenteSezioneCodice($id_proponente, $id_sezione, $codice) {
		
		$dql = "SELECT voce FROM RichiesteBundle:VocePianoCosto voce "
				. "JOIN voce.proponente prop "
				. "JOIN voce.piano_costo piano "
				. "JOIN piano.sezione_piano_costo sez "
				. "WHERE prop.id = $id_proponente AND {$this->getWhereConditionSezione($id_sezione)} AND piano.codice = '$codice'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleResult();
	}
	
	public function getVoceDaProponenteCodiceSezioneCodice($id_proponente, $codice_sezione, $codice) {

		$dql = "SELECT voce FROM RichiesteBundle:VocePianoCosto voce "
				. "JOIN voce.proponente prop "
				. "JOIN voce.piano_costo piano "
				. "JOIN piano.sezione_piano_costo sez "
				. "WHERE prop.id = $id_proponente AND sez.codice = '$codice_sezione' AND piano.codice = '$codice'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getOneOrNullResult();
	}
	
	public function getCostoTotaleRichiesta($id_richiesta) {

		$dql = "SELECT SUM(COALESCE(voce.importo_anno_1,0)) + "
				. "SUM(COALESCE(voce.importo_anno_2,0)) + "
				. "SUM(COALESCE(voce.importo_anno_3,0)) + "
				. "SUM(COALESCE(voce.importo_anno_4,0)) + "
				. "SUM(COALESCE(voce.importo_anno_5,0)) + "
				. "SUM(COALESCE(voce.importo_anno_6,0)) + "
				. "SUM(COALESCE(voce.importo_anno_7,0)) FROM RichiesteBundle:VocePianoCosto voce "
				. "JOIN voce.richiesta rich "
				. "JOIN voce.piano_costo piano "
				. "WHERE rich.id = $id_richiesta AND piano.codice = 'TOT'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleScalarResult();
	}
        
        public function getCostoTotaleRichiestaCodiceVoce($id_richiesta, $codice) {

		$dql = "SELECT SUM(COALESCE(voce.importo_anno_1,0)) + "
				. "SUM(COALESCE(voce.importo_anno_2,0)) + "
				. "SUM(COALESCE(voce.importo_anno_3,0)) + "
				. "SUM(COALESCE(voce.importo_anno_4,0)) + "
				. "SUM(COALESCE(voce.importo_anno_5,0)) + "
				. "SUM(COALESCE(voce.importo_anno_6,0)) + "
				. "SUM(COALESCE(voce.importo_anno_7,0)) FROM RichiesteBundle:VocePianoCosto voce "
				. "JOIN voce.richiesta rich "
				. "JOIN voce.piano_costo piano "
				. "WHERE rich.id = $id_richiesta AND piano.codice = '$codice'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleScalarResult();
	}
        
        public function getCostoTotaleProponente($id_proponente) {

		$dql = "SELECT SUM(COALESCE(voce.importo_anno_1,0)) + "
				. "SUM(COALESCE(voce.importo_anno_2,0)) + "
				. "SUM(COALESCE(voce.importo_anno_3,0)) + "
				. "SUM(COALESCE(voce.importo_anno_4,0)) + "
				. "SUM(COALESCE(voce.importo_anno_5,0)) + "
				. "SUM(COALESCE(voce.importo_anno_6,0)) + "
				. "SUM(COALESCE(voce.importo_anno_7,0)) FROM RichiesteBundle:VocePianoCosto voce "
				. "JOIN voce.proponente prop "
				. "JOIN voce.piano_costo piano "
				. "WHERE prop.id = $id_proponente AND piano.codice = 'TOT'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleScalarResult();
	}
    
    public function getCostoTotaleRichiestaSezione($id_richiesta, $sezione) {

		$dql = "SELECT SUM(COALESCE(voce.importo_anno_1,0)) + "
				. "SUM(COALESCE(voce.importo_anno_2,0)) + "
				. "SUM(COALESCE(voce.importo_anno_3,0)) + "
				. "SUM(COALESCE(voce.importo_anno_4,0)) + "
				. "SUM(COALESCE(voce.importo_anno_5,0)) + "
				. "SUM(COALESCE(voce.importo_anno_6,0)) + "
				. "SUM(COALESCE(voce.importo_anno_7,0)) FROM RichiesteBundle:VocePianoCosto voce "
				. "JOIN voce.richiesta rich "
				. "JOIN voce.piano_costo piano "
                . "JOIN piano.sezione_piano_costo sez "
				. "WHERE rich.id = $id_richiesta AND piano.codice = 'TOT' AND sez.codice = '$sezione'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleScalarResult();
	}
    
	public function getAvanzamentoPianoCosti($id_richiesta,  $anno, $ultima_variazione_id = null) {

		$dql = "SELECT i.importo_ammissibile_anno_$anno "
                . "FROM RichiesteBundle:VocePianoCosto voce "
                . "JOIN voce.istruttoria i "
				. "JOIN voce.richiesta rich "
				. "JOIN voce.piano_costo piano ";
        
        if (!is_null($ultima_variazione_id)) {
            $dql .= "LEFT JOIN rich.attuazione_controllo ac "
                  . "LEFT JOIN ac.variazioni var "
                  . "LEFT JOIN var.voci_piano_costo vvoce ";
        }
			
        $dql .= "WHERE rich.id = $id_richiesta AND piano.codice != 'TOT'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleScalarResult();
	}    
	
	public function getVoceDaPianoERichiesta($id_piano, $id_richiesta) {

		$dql = "SELECT voce FROM RichiesteBundle:VocePianoCosto voce "
				. "JOIN voce.piano_costo piano "
				. "JOIN voce.richiesta ric "
				. "WHERE  AND piano.id = '$id_piano' AND ric.id = '$id_richiesta'";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleResult();
	}
	public function getVoceDaPianoERichiestaProponente($id_piano, $id_richiesta, $id_proponente = null) {

		$dql = "SELECT voce FROM RichiesteBundle:VocePianoCosto voce "
				. "JOIN voce.piano_costo piano "
				. "JOIN voce.richiesta ric ";

		if (!is_null($id_proponente)) {
			$dql.= "JOIN voce.proponente prop ";
		}

		$dql.= "WHERE piano.id = $id_piano AND ric.id = $id_richiesta ";

		if (!is_null($id_proponente)) {
			$dql.= " AND prop.id = $id_proponente ";
		}

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		return $q->getResult();
	}

	public function getVoceDaProponenteCodiceSezioneCodiceRichiesta($id_richiesta, $codice_sezione, $codice, $proponente = null) {

		$dql = "SELECT voce FROM RichiesteBundle:VocePianoCosto voce "
				. "JOIN voce.richiesta rich ";
		
		if(!is_null($proponente)) {
			$dql .= "JOIN voce.proponente prop ";
		}
		
		$dql .= "JOIN voce.piano_costo piano "
				. "JOIN piano.sezione_piano_costo sez "
				. "WHERE rich.id = $id_richiesta AND sez.codice = '$codice_sezione' AND piano.codice = '$codice' ";
		
		if(!is_null($proponente)) {
			$dql .= "AND prop.id = {$proponente->getId()} ";
		}
		
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getSingleResult();
	}
	
	
	/**
	 * usata nell'avanzamento della rendicontazione standard
	 * tira fuori i proponenti per i quali è stato definito un piano costi
	 * @param int $id_richiesta
	 * @return Proponente[]
	 */
	public function getProponentiPianoCosti($id_richiesta) {

		$dql = "SELECT DISTINCT p FROM RichiesteBundle:Proponente p "
				. "JOIN RichiesteBundle:VocePianoCosto vpc WITH p = vpc.proponente "
				. "WHERE p.richiesta = $id_richiesta";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}
	
	public function getTotaliRichiestaSezioneCodice($id_richiesta, $id_sezione) {
		
		$dql = "SELECT concat(piano.codice, ') ', piano.titolo) as titolo, "
				. "sum(voce.importo_anno_1) as importo_anno_1, "
				. "sum(voce.importo_anno_2) as importo_anno_2, "
				. "sum(voce.importo_anno_3) as importo_anno_3, "
				. "sum(voce.importo_anno_4) as importo_anno_4, "
				. "sum(voce.importo_anno_5) as importo_anno_5, "
				. "sum(voce.importo_anno_6) as importo_anno_6, "
				. "sum(voce.importo_anno_7) as importo_anno_7 "
				. "FROM RichiesteBundle:VocePianoCosto voce "
				. "JOIN voce.richiesta rich "
				. "JOIN voce.piano_costo piano "
				. "JOIN piano.sezione_piano_costo sez "
				. "WHERE rich.id = $id_richiesta AND {$this->getWhereConditionSezione($id_sezione)} "
				. "GROUP BY rich.id,  piano.codice ORDER BY piano.codice ASC";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		$deb = $q->getSql();
		
		return $q->getResult();
	}
	
	public function getTotaliAmmessiRichiestaSezioneCodice($id_richiesta, $id_sezione) {
		
		$dql = "SELECT concat(piano.codice, ') ', piano.titolo) as titolo, "
				. "sum(istr.importo_ammissibile_anno_1) as importo_ammissibile_anno_1, "
				. "sum(istr.importo_ammissibile_anno_2) as importo_ammissibile_anno_2, "
				. "sum(istr.importo_ammissibile_anno_3) as importo_ammissibile_anno_3, "
				. "sum(istr.importo_ammissibile_anno_4) as importo_ammissibile_anno_4, "
				. "sum(istr.importo_ammissibile_anno_5) as importo_ammissibile_anno_5, "
				. "sum(istr.importo_ammissibile_anno_6) as importo_ammissibile_anno_6, "
				. "sum(istr.importo_ammissibile_anno_7) as importo_ammissibile_anno_7 "
				. "FROM RichiesteBundle:VocePianoCosto voce "
				. "JOIN voce.richiesta rich "
				. "JOIN voce.piano_costo piano "
				. "JOIN piano.sezione_piano_costo sez "
				. "JOIN voce.istruttoria istr "
				. "WHERE rich.id = $id_richiesta AND {$this->getWhereConditionSezione($id_sezione)} "
				. "GROUP BY rich.id,  piano.codice ORDER BY piano.codice ASC";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		$deb = $q->getSql();
		
		return $q->getResult();
	}

    public function getVoceCodiceRichiesta($id_richiesta, $codice) {
        $dql = "SELECT voce " 
            . "FROM RichiesteBundle:VocePianoCosto voce "
            . "JOIN voce.richiesta richiesta "
            . "JOIN voce.piano_costo piano "
            . "WHERE richiesta.id = $id_richiesta AND piano.identificativo_html = '$codice'";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        return $q->getResult();
    }

    /**
     * @param int $id_richiesta
     * @param array $anni
     * @param string $voce_totale
     * @return mixed
     */
    public function getCostoTotaleRichiestaPerAnno(int $id_richiesta, array $anni, string $voce_totale = 'TOT') {

        $dql = "SELECT ";

        foreach ($anni as $anno) {
            $temp[] = "SUM(COALESCE(voce.importo_anno_$anno, 0)) ";
        }

        $dql .= implode('+ ', $temp);
        
        $dql .= "FROM RichiesteBundle:VocePianoCosto voce "
             . "JOIN voce.richiesta rich "
             . "JOIN voce.piano_costo piano "
             . "WHERE rich.id = $id_richiesta AND piano.codice = '$voce_totale'";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $result = $q->getResult();
        
        return $result[0][1];
    }
}
