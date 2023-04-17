<?php

namespace DocumentoBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RichiesteBundle\Entity\Bando98\OggettoLegge14;
use AttuazioneControlloBundle\Entity\Contratto;
use SfingeBundle\Entity\Procedura;

class TipologiaDocumentoRepository extends EntityRepository {

	public function ricercaDocumentiRichiesta($id_richiesta, $id_procedura, $solo_obbligatori, $obbligatori = array()) {

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
							WHERE (t.procedura = :id_procedura
							OR t.codice IN 
(SELECT 'VIDEO_DI_PRESENTAZIONE' AS codice FROM SfingeBundle\Entity\Procedura p
WHERE p.id = :id_procedura AND p.sezione_video = 1)
							)
							
							AND 
							
							
							t.tipologia LIKE :tipologia AND t NOT IN 
						  (SELECT t1 FROM RichiesteBundle\Entity\DocumentoRichiesta r
							JOIN r.documento_file d
							JOIN d.tipologia_documento t1
							WHERE r.richiesta = :id_richiesta)";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
			$dql .= " AND (t.obbligatorio = :solo_obbligatori";

			if (count($obbligatori) > 0) {
				$dql .= " OR t.codice IN ('" . implode("','", $obbligatori) . "')";
			}

			$dql .= ")";
			$q->setParameter(":solo_obbligatori", $solo_obbligatori);
		}

		$dql .= " ORDER BY t.obbligatorio DESC";

		$q->setDQL($dql);
		$q->setParameter("tipologia", 'richiesta');
		$q->setParameter("id_procedura", $id_procedura);
		$q->setParameter("id_richiesta", $id_richiesta);
		return $q->getResult();
	}

	public function ricercaDocumentiProponente($id_proponente, $id_procedura, $solo_obbligatori) {

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
							WHERE t.procedura = :id_procedura AND t.tipologia LIKE :tipologia AND t NOT IN 
						  (SELECT t1 FROM RichiesteBundle\Entity\DocumentoProponente p
							JOIN p.documento_file d
							JOIN d.tipologia_documento t1
							WHERE p.proponente = :id_proponente)";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
			$dql .= " AND t.obbligatorio = :solo_obbligatori";
			$q->setParameter(":solo_obbligatori", $solo_obbligatori);
		}

		$dql .= " ORDER BY t.obbligatorio DESC";

		$q->setDQL($dql);
		$q->setParameter("tipologia", 'proponente');
		$q->setParameter("id_procedura", $id_procedura);
		$q->setParameter("id_proponente", $id_proponente);
		return $q->getResult();
	}

	public function ricercaTipologieManuali() {
		/* $dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
		  WHERE t.tipologia LIKE :tipologia AND t NOT IN
		  (SELECT t1 FROM SfingeBundle\Entity\Manuale m
		  JOIN DocumentoBundle\Entity\Documento d WITH d = m.documento_file
		  JOIN DocumentoBundle\Entity\TipologiaDocumento t1 WITH t1 = d.tipologia_documento)"; */

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
				WHERE t.tipologia LIKE :tipologia ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		$q->setParameter("tipologia", '%manuale%');
		return $q->getResult();
	}

	public function ricercaDocumentiIntegrazioneRichiesta($integrazione, $proponente = null) {

		$id_integrazione = $integrazione->getId();
		$id_risposta_integrazione = $integrazione->getRisposta()->getId();

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t
							JOIN IstruttorieBundle\Entity\IntegrazioneIstruttoriaDocumento id WITH id.tipologia_documento = t
							WHERE id.integrazione = :id_integrazione AND id.proponente " . (is_null($proponente) ? "is null" : "= :proponente") . " AND (t NOT IN 
						  (SELECT t1 FROM IstruttorieBundle\Entity\DocumentoIntegrazioneIstruttoria r
							JOIN r.documento_file d
							JOIN d.tipologia_documento t1
							WHERE r.risposta_integrazione = :id_risposta_integrazione) OR t.abilita_duplicati = 1)";

		$q = $this->getEntityManager()->createQuery();

		$q->setDQL($dql);
		$q->setParameter("id_integrazione", $id_integrazione);
		$q->setParameter("id_risposta_integrazione", $id_risposta_integrazione);

		if (!is_null($proponente)) {
			$q->setParameter("proponente", $proponente);
		}

		$s = $q->getSQL();
		return $q->getResult();

	}

	public function validaDocumentiIntegrazioneRichiesta($integrazione, $proponente = null) {

		$id_integrazione = $integrazione->getIntegrazione()->getId();
		$id_risposta_integrazione = $integrazione->getId();

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t
							JOIN IstruttorieBundle\Entity\IntegrazioneIstruttoriaDocumento id WITH id.tipologia_documento = t
							WHERE id.integrazione = :id_integrazione AND id.proponente " . (is_null($proponente) ? "is null" : "= :proponente") . " AND (t NOT IN 
						  (SELECT t1 FROM IstruttorieBundle\Entity\DocumentoIntegrazioneIstruttoria r
							JOIN r.documento_file d
							JOIN d.tipologia_documento t1
							WHERE r.risposta_integrazione = :id_risposta_integrazione) )";

		$q = $this->getEntityManager()->createQuery();

		$q->setDQL($dql);
		$q->setParameter("id_integrazione", $id_integrazione);
		$q->setParameter("id_risposta_integrazione", $id_risposta_integrazione);

		if (!is_null($proponente)) {
			$q->setParameter("proponente", $proponente);
		}

		return $q->getResult();
	}
	
	public function ricercaDaCodiceTipologia($codice, $richiesta) {

		$dql = "SELECT doc FROM RichiesteBundle\Entity\DocumentoRichiesta doc
				JOIN doc.richiesta rich
				JOIN doc.documento_file df
				JOIN df.tipologia_documento t
				WHERE t.codice = :codice AND rich.id = :richiesta";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		$q->setParameter("codice", $codice);
		$q->setParameter("richiesta", $richiesta->getId());

		return $q->getResult();
	}

	public function ricercaDocumentiPagamento($id_pagamento, $id_procedura, $solo_obbligatori, $obbligatori = array()) {

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
							WHERE t.procedura = :id_procedura AND t.tipologia LIKE :tipologia AND t NOT IN 
						  (SELECT t1 FROM AttuazioneControlloBundle\Entity\DocumentoPagamento r
							JOIN r.documento_file d
							JOIN d.tipologia_documento t1
							WHERE r.pagamento = :id_pagamento)";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
			$dql .= " AND (t.obbligatorio = :solo_obbligatori";

			if (count($obbligatori) > 0) {
				$dql .= " OR t.codice IN ('" . implode("','", $obbligatori) . "')";
			}

			$dql .= ")";
			$q->setParameter(":solo_obbligatori", $solo_obbligatori);
		}

		$dql .= " ORDER BY t.obbligatorio DESC";

		$q->setDQL($dql);
		$q->setParameter("tipologia", 'rendicontazione');
		$q->setParameter("id_procedura", $id_procedura);
		$q->setParameter("id_pagamento", $id_pagamento);

		return $q->getResult();
	}
	
	public function ricercaDocumentiPagamentoTipologia($id_pagamento, $id_procedura, $solo_obbligatori, $tipologia,  $obbligatori = array()) {

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
							WHERE t.procedura = :id_procedura AND t.tipologia LIKE :tipologia AND t NOT IN 
						  (SELECT t1 FROM AttuazioneControlloBundle\Entity\DocumentoPagamento r
							JOIN r.documento_file d
							JOIN d.tipologia_documento t1
							WHERE r.pagamento = :id_pagamento)";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
			$dql .= " AND (t.obbligatorio = :solo_obbligatori";

			if (count($obbligatori) > 0) {
				$dql .= " OR t.codice IN ('" . implode("','", $obbligatori) . "')";
			}

			$dql .= ")";
			$q->setParameter(":solo_obbligatori", $solo_obbligatori);
		}

		$dql .= " ORDER BY t.obbligatorio DESC";

		$q->setDQL($dql);
		$q->setParameter("tipologia", $tipologia);
		$q->setParameter("id_procedura", $id_procedura);
		$q->setParameter("id_pagamento", $id_pagamento);

		return $q->getResult();
	}

	public function ricercaDocumentiIstruttoria() {

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
				WHERE t.procedura IS NULL AND t.tipologia = :tipologia ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		$q->setParameter("tipologia", 'istruttoria');
		return $q->getResult();
	}

	public function ricercaDocumentiIstruttoriaPagamento() {

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
				WHERE t.tipologia = :tipologia ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		$q->setParameter("tipologia", 'istruttoria_pagamento');
		return $q->getResult();
	}

	public function ricercaDocumentiVariazione($id_variazione, $id_procedura, $solo_obbligatori, $obbligatori = array()) {

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
							WHERE t.procedura = :id_procedura AND t.tipologia LIKE :tipologia AND t NOT IN 
						  (SELECT t1 FROM AttuazioneControlloBundle\Entity\DocumentoVariazione r
							JOIN r.documento_file d
							JOIN d.tipologia_documento t1
							WHERE r.variazione = :id_variazione)";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
			$dql .= " AND (t.obbligatorio = :solo_obbligatori";

			if (count($obbligatori) > 0) {
				$dql .= " OR t.codice IN ('" . implode("','", $obbligatori) . "')";
			}

			$dql .= ")";
			$q->setParameter(":solo_obbligatori", $solo_obbligatori);
		}

		$dql .= " ORDER BY t.obbligatorio DESC";

		$q->setDQL($dql);
		$q->setParameter("tipologia", 'variazione');
		$q->setParameter("id_procedura", $id_procedura);
		$q->setParameter("id_variazione", $id_variazione);

		return $q->getResult();
	}
	
	public function ricercaDocumentiIstruttoriaVariazione() {

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
				WHERE t.tipologia = :tipologia ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		$q->setParameter("tipologia", 'istruttoria_variazione');
		return $q->getResult();
	}

	public function ricercaDocumentiRicercatori($id_personale,$id_procedura,$solo_obbligatori, $obbligatori = array())
	{
		
		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
							WHERE t.procedura = :id_procedura AND t.tipologia LIKE :tipologia AND t NOT IN 
						  (SELECT t1 FROM AnagraficheBundle\Entity\DocumentoPersonale r
							JOIN r.documento_file d
							JOIN d.tipologia_documento t1
							WHERE r.personale = :id_personale)";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
            $dql .= " AND (t.obbligatorio = :solo_obbligatori";
			
			if (count($obbligatori) > 0) {
				$dql .= " OR t.codice IN ('".implode("','", $obbligatori)."')";
			}
			
			$dql .= ")";
            $q->setParameter(":solo_obbligatori", $solo_obbligatori);
        }
		
		$dql .= " ORDER BY t.obbligatorio DESC";

        $q->setDQL($dql);
        $q->setParameter("tipologia", 'ricercatori');
        $q->setParameter("id_procedura", $id_procedura);
        $q->setParameter("id_personale", $id_personale);	
		return $q->getResult();
	}	
    
	public function ricercaDocumentiGiustificativo($giustificativo, $procedura, $solo_obbligatori, $pubblico = null, $isValidazione = false)
	{
        
		// al momento tutti i doc ammettono duplicati, eventuali doc che non ammettono duplicati vanno aggiunti per codice dentro "IN"
		$extra = '';
		if(!$isValidazione){
			$extra = "AND (t1.codice IN ('') OR t1.abilita_duplicati = 0)";
		}        
        
        $extra2 = "";

        if ($pubblico === true) {
            $extra2 .= " AND t.codice NOT IN ('ESTRATTO_CONTO_GIU', 'RIBA', 'BONIFICO_VOCE_6')";  
        }
        
        if ($pubblico === false) {
            $extra2 .= " AND t.codice NOT IN ('MANDATO_QUIETANZIATO_GIU_774', 'SPLIT_PAYMENT_774')";  
        }         
		
		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
							WHERE t.procedura = :id_procedura AND t.tipologia LIKE :tipologia AND t NOT IN 
						  (SELECT t1 FROM AttuazioneControlloBundle\Entity\DocumentoGiustificativo dg
							JOIN dg.documento_file d
							JOIN d.tipologia_documento t1
							WHERE dg.giustificativo_pagamento = :id_giustificativo {$extra}) $extra2";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
            $dql .= " AND (t.obbligatorio = :solo_obbligatori)";
            $q->setParameter(":solo_obbligatori", $solo_obbligatori);
        }
		
		$dql .= " ORDER BY t.obbligatorio DESC";

        $q->setDQL($dql);
        $q->setParameter("tipologia", 'giustificativo');
        $q->setParameter("id_procedura", $procedura->getId());
        $q->setParameter("id_giustificativo", $giustificativo->getId());	
		return $q->getResult();
	}
    
	public function ricercaDocumentiPersonalePagamento($pagamento, $procedura, $solo_obbligatori)
	{
		
		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
							WHERE t.procedura = :id_procedura AND t.tipologia LIKE :tipologia AND t NOT IN 
						  (SELECT t1 FROM AttuazioneControlloBundle\Entity\Pagamento p
                            JOIN p.estensione ep
                            JOIN ep.documenti de
							JOIN de.documento_file d                            
                            JOIN d.tipologia_documento t1                          
							WHERE p = :id_pagamento AND de.tipo =:tipo)";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
            $dql .= " AND (t.obbligatorio = :solo_obbligatori)";
            $q->setParameter(":solo_obbligatori", $solo_obbligatori);
        }
		
		$dql .= " ORDER BY t.obbligatorio DESC";

        $q->setDQL($dql);
        $q->setParameter("tipologia", 'personale_pagamento');
        $q->setParameter("id_procedura", $procedura->getId());
        $q->setParameter("id_pagamento", $pagamento->getId());	
		$q->setParameter("tipo", \AttuazioneControlloBundle\Entity\DocumentoEstensionePagamento::TIPO_DOCUMENTO_PERSONALE);
		return $q->getResult();
	}  
	
	public function ricercaDocumentiAmministrativi(Contratto $amministrativo, Procedura $procedura, ?string $tipo, bool $solo_obbligatori, $abilitaDocScorrimentoSal773 = false): array
	{

		$dql = "SELECT t 
				FROM DocumentoBundle:TipologiaDocumento t
				INNER JOIN t.procedura procedura
				WHERE procedura = :id_procedura 
				AND t.tipologia = :tipologia 
				AND (
					t.obbligatorio = 1 
					OR coalesce(:solo_obbligatori,0) = 0 
				) 
				AND (t.prefix = 'scorr_sal_773' OR coalesce(:scorr_sal_773, 0) = 0)
				AND t NOT IN (
					select t1 
					FROM DocumentoBundle:TipologiaDocumento t1
					inner join DocumentoBundle:Documento d WITH d.tipologia_documento = t1
					inner join AttuazioneControlloBundle:DocumentoContratto dc WITH dc.documentoFile = d
					inner join dc.contratto con
					WHERE con = :id_contratto
					AND coalesce(t1.abilita_duplicati,0) = 0
				) 
				
				ORDER BY t.obbligatorio DESC";

		$q = $this->getEntityManager()->createQuery($dql);

        $q->setParameter("solo_obbligatori", $solo_obbligatori);
		$q->setParameter('scorr_sal_773', $abilitaDocScorrimentoSal773);
		$q->setParameter("tipologia", $tipo == 'BREVETTI' ? 'giustificativo_brevetti' : 'giustificativo_consulenze');
        $q->setParameter("id_procedura", $procedura);
		$q->setParameter("id_contratto", $amministrativo);
		$res =  $q->getResult();

		return $res;
	} 
	
	public function listaDocumentiAmministrativi( $procedura, $tipo, $solo_obbligatori)
	{
		
		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
							WHERE t.procedura = :id_procedura AND t.tipologia LIKE :tipologia";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
            $dql .= " AND (t.obbligatorio = :solo_obbligatori)";
            $q->setParameter(":solo_obbligatori", $solo_obbligatori);
        }
		
		$dql .= " ORDER BY t.obbligatorio DESC";

        $q->setDQL($dql);
		if($tipo == 'BREVETTI') {
			$q->setParameter("tipologia", 'giustificativo_brevetti');
		}
		else {
			$q->setParameter("tipologia", 'giustificativo_consulenze');
		}
        $q->setParameter("id_procedura", $procedura->getId());

		
		$sql = $q->getSql();
		
		return $q->getResult();
	} 
	
	public function ricercaDocumentiPersonalePagamentoBando8($pagamento, $procedura, $solo_obbligatori, $id_proponente, $isValidazione = false, $voci = array(), $pubblico = null)
	{
		// al momento tutti i doc ammettono duplicati, eventuali doc che non ammettono duplicati vanno aggiunti per codice dentro "IN"
		$extra = '';
		if(!$isValidazione){
			$extra = "AND t1.codice IN ('')";
		}
        
        //$extra2 = "AND t.codice NOT IN ('TIMESHEET_CUMULATIVO')";
		$extra2 = "";
        
        if (count(array_intersect($voci, array("1", "3"))) == 0) {
            $extra2 .= " AND t.codice NOT IN ('MANDATO_QUIETANZIATO_CUMULATIVO_774', 'DICHIARAZIONE_PAGAMENTI_DIP_774')";          
        }
        
        if (count(array_intersect($voci, array("2", "4"))) == 0) {
            $extra2 .= " AND t.codice NOT IN ('MANDATO_QUIETANZIATO_774', 'MANDATO_DICHIARAZIONE_VOCE_2')";          
        }

        if ($pubblico === true) {
            $extra2 .= " AND t.codice NOT IN ('BONIFICO_COMULATIVO', 'ESTRATTO_CONTO')";  
        }
        
        if ($pubblico === false) {
            $extra2 .= " AND t.codice NOT IN ('MANDATO_QUIETANZIATO_CUMULATIVO_774', 'DICHIARAZIONE_PAGAMENTI_DIP_774', 'MANDATO_QUIETANZIATO_774', 'MANDATO_DICHIARAZIONE_VOCE_2')";  
        }        
		
		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
							WHERE t.procedura = :id_procedura AND t.tipologia LIKE :tipologia AND t NOT IN 
						  (SELECT t1 FROM AttuazioneControlloBundle\Entity\Pagamento p
                            JOIN p.estensione ep
                            JOIN ep.documenti de
							JOIN de.documento_file d                            
                            JOIN d.tipologia_documento t1 
							JOIN de.proponente pr
							WHERE p = :id_pagamento AND de.tipo=:tipo AND pr = :id_proponente {$extra}) $extra2";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
            $dql .= " AND (t.obbligatorio = :solo_obbligatori)";
            $q->setParameter(":solo_obbligatori", $solo_obbligatori);
        }
		
		$dql .= " ORDER BY t.obbligatorio DESC";

        $q->setDQL($dql);
        $q->setParameter("tipologia", 'personale_pagamento');
        $q->setParameter("id_procedura", $procedura->getId());
        $q->setParameter("id_pagamento", $pagamento->getId());	
		$q->setParameter("id_proponente", $id_proponente);
		$q->setParameter("tipo", \AttuazioneControlloBundle\Entity\DocumentoEstensionePagamento::TIPO_DOCUMENTO_PERSONALE);
		
		return $q->getResult();
	} 
	
	public function ricercaDocumentiGeneraliPagamento($pagamento, $procedura, $id_proponente, $solo_obbligatori, $isValidazione = false)
	{
		// al momento tutti i doc ammettono duplicati, eventuali doc che non ammettono duplicati vanno aggiunti per codice dentro "IN"
		$extra = '';
		if(!$isValidazione){
			$extra = "AND t1.codice IN ('')";
		}
		
		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
							WHERE t.procedura = :id_procedura AND t.tipologia LIKE :tipologia AND t NOT IN 
						  (SELECT t1 FROM AttuazioneControlloBundle\Entity\Pagamento p
                            JOIN p.estensione ep
                            JOIN ep.documenti de
							JOIN de.documento_file d                            
                            JOIN d.tipologia_documento t1 
							JOIN de.proponente pr
							WHERE p = :id_pagamento AND de.tipo=:tipo AND pr = :id_proponente {$extra})";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
            $dql .= " AND (t.obbligatorio = :solo_obbligatori)";
            $q->setParameter(":solo_obbligatori", $solo_obbligatori);
        }
		
		$dql .= " ORDER BY t.obbligatorio DESC";

        $q->setDQL($dql);
        $q->setParameter("tipologia", 'pagamento');
        $q->setParameter("id_procedura", $procedura->getId());
        $q->setParameter("id_pagamento", $pagamento->getId());	
		$q->setParameter("id_proponente", $id_proponente);
		$q->setParameter("tipo", \AttuazioneControlloBundle\Entity\DocumentoEstensionePagamento::TIPO_DOCUMENTO_GENERALE);
		
		return $q->getResult();
	}
	
	/**
	 * predisposta per la rendicontazione standard
	 * 
	 * E' stato definito un set standard di documenti progetto, identificati dalla tipologia 'rendicontazione_documenti_progetto_standard'
	 * aventi procedura_id NULL.
	 * Potrebbe capitare che richiedano l'aggiunta di documenti progetto specifici per bando;
	 * per cui estendiamo il set standard aggiungendo le nuove tipologie avendo l'accortezza di impostare come tipologia sempre 
	 * 'rendicontazione_documenti_progetto_standard' e specificando il procedura_id.
	 */
	public function ricercaTipiDocumentiPagamentoStandard($id_pagamento, $id_procedura, $solo_obbligatori, $obbligatori = array()) {

		// estendo il set standard..per cui prendo sia quelli con procedura_id nulla
		// che quelli con procedura_id voluto		
		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
							WHERE t.tipologia = :tipologia AND (t.procedura IS NULL OR t.procedura = :id_procedura) AND t NOT IN 
						  (SELECT t1 FROM AttuazioneControlloBundle\Entity\DocumentoPagamento r
							JOIN r.documento_file d
							JOIN d.tipologia_documento t1
							WHERE r.pagamento = :id_pagamento)";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
			$dql .= " AND (t.obbligatorio = :solo_obbligatori";

			if (count($obbligatori) > 0) {
				$dql .= " OR t.codice IN ('" . implode("','", $obbligatori) . "')";
			}

			$dql .= ")";
			$q->setParameter(":solo_obbligatori", $solo_obbligatori);
		}

		$dql .= " ORDER BY t.obbligatorio DESC";

		$q->setDQL($dql);
		$q->setParameter("tipologia", 'rendicontazione_documenti_progetto_standard');
		$q->setParameter("id_pagamento", $id_pagamento);
		$q->setParameter("id_procedura", $id_procedura);
	
		return $q->getResult();
	}	
	
	// predisposto per rendicontazione standard
	public function findTipologieDocumentiPagamentoConDuplicati($id_procedura) {

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t "
				. "WHERE t.tipologia = :tipologia AND (t.procedura IS NULL OR t.procedura = :id_procedura) AND t.abilita_duplicati = 1"; 

		$q = $this->getEntityManager()->createQuery();

		$q->setDQL($dql);
		$q->setParameter("tipologia", 'rendicontazione_documenti_progetto_standard');
		$q->setParameter("id_procedura", $id_procedura);
	
		return $q->getResult();
	}
	
	
	/**
	 * predisposta per la rendicontazione standard
	 * 
	 * E' stato definito un set standard di documenti giustificativo, identificati dalla tipologia 'rendicontazione_documenti_giustificativo_standard'
	 * aventi procedura_id NULL.
	 * Potrebbe capitare che richiedano l'aggiunta di documenti giustificativo specifici per bando;
	 * per cui estendiamo il set standard aggiungendo le nuove tipologie avendo l'accortezza di impostare come tipologia sempre 
	 * 'rendicontazione_documenti_giustificativo_standard' e specificando il procedura_id.
	 */
	public function ricercaTipiDocumentiGiustificativoStandard($giustificativo, $id_procedura, $solo_obbligatori) {
		
		// estendo il set standard..per cui prendo sia quelli con procedura_id nulla
		// che quelli con procedura_id voluto
		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t "
				. "WHERE (t.procedura IS NULL OR t.procedura = :id_procedura) AND t.tipologia LIKE :tipologia AND t NOT IN "
				. "(SELECT t1 FROM AttuazioneControlloBundle\Entity\DocumentoGiustificativo dg "
				. "JOIN dg.documento_file d "
				. "JOIN d.tipologia_documento t1 "
				. "WHERE dg.giustificativo_pagamento = :id_giustificativo)";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
			$dql .= " AND (t.obbligatorio = :solo_obbligatori)";
			$q->setParameter(":solo_obbligatori", $solo_obbligatori);
		}

		$dql .= " ORDER BY t.obbligatorio DESC";

		$q->setDQL($dql);
		$q->setParameter("tipologia", 'rendicontazione_documenti_giustificativo_standard');
		$q->setParameter("id_procedura", $id_procedura);
		$q->setParameter("id_giustificativo", $giustificativo->getId());
		
		return $q->getResult();
	}
	
	// predisposto per rendicontazione standard
	public function findTipologieDocumentiGiustificativoConDuplicati($id_procedura) {

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t "
				. "WHERE t.tipologia = :tipologia AND (t.procedura IS NULL OR t.procedura = :id_procedura) AND t.abilita_duplicati = 1"; 

		$q = $this->getEntityManager()->createQuery();

		$q->setDQL($dql);
		$q->setParameter("tipologia", 'rendicontazione_documenti_giustificativo_standard');
		$q->setParameter("id_procedura", $id_procedura);
	
		return $q->getResult();
	}
	
	
	// predisposto per istruttoria rendicontazione standard
	public function findTipologieDocumentiIstruttoriaPagamento($id_procedura) {

		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t "
				. "WHERE t.tipologia = :tipologia AND (t.procedura IS NULL OR t.procedura = :id_procedura) "; 

		$q = $this->getEntityManager()->createQuery();

		$q->setDQL($dql);
		$q->setParameter("tipologia", 'rendicontazione_documenti_istruttoria_standard');
		$q->setParameter("id_procedura", $id_procedura);
	
		return $q->getResult();
	}
	
	/**
	 * predisposta per la rendicontazione standard
	 * 
	 * E' stato definito un set standard di documenti progetto, identificati dalla tipologia 'rendicontazione_documenti_progetto_standard'
	 * aventi procedura_id NULL.
	 * Potrebbe capitare che richiedano l'aggiunta di documenti progetto specifici per bando;
	 * per cui estendiamo il set standard aggiungendo le nuove tipologie avendo l'accortezza di impostare come tipologia sempre 
	 * 'rendicontazione_documenti_progetto_standard' e specificando il procedura_id.
	 */
	public function ricercaTipiDocumentiPagamentoStandardNoRelTec($id_pagamento, $id_procedura, $solo_obbligatori, $obbligatori = array()) {

		// estendo il set standard..per cui prendo sia quelli con procedura_id nulla
		// che quelli con procedura_id voluto		
		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
							WHERE t.tipologia = :tipologia AND t.codice <> 'RELAZIONE_TECNICA_STANDARD' AND (t.procedura IS NULL OR t.procedura = :id_procedura) AND t NOT IN 
						  (SELECT t1 FROM AttuazioneControlloBundle\Entity\DocumentoPagamento r
							JOIN r.documento_file d
							JOIN d.tipologia_documento t1
							WHERE r.pagamento = :id_pagamento)";

		$q = $this->getEntityManager()->createQuery();

		if ($solo_obbligatori == 1) {
			$dql .= " AND (t.obbligatorio = :solo_obbligatori";

			if (count($obbligatori) > 0) {
				$dql .= " OR t.codice IN ('" . implode("','", $obbligatori) . "')";
			}

			$dql .= ")";
			$q->setParameter(":solo_obbligatori", $solo_obbligatori);
		}

		$dql .= " ORDER BY t.obbligatorio DESC";

		$q->setDQL($dql);
		$q->setParameter("tipologia", 'rendicontazione_documenti_progetto_standard');
		$q->setParameter("id_pagamento", $id_pagamento);
		$q->setParameter("id_procedura", $id_procedura);
	
		return $q->getResult();
	}

	public function ricercaDocumentiRichiestaBandoCentriStorici($id_richiesta, $id_procedura, $solo_obbligatori, $oggettoRichiesta, $obbligatori = array(), $gia_caricati = true) {
        $suffix = '';
        $array_procedure = [121, 132];
        if (in_array($id_procedura, $array_procedure)) {
            $suffix = '_' . $id_procedura;
        }
	    $q = $this->getEntityManager()->createQuery();
		$dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
				WHERE t.procedura = :id_procedura AND t.tipologia LIKE :tipologia ";
		
		if ($gia_caricati) {
			$dql .= " AND t NOT IN (SELECT t1 FROM RichiesteBundle\Entity\DocumentoRichiesta r
					JOIN r.documento_file d
					JOIN d.tipologia_documento t1
					WHERE r.richiesta = :id_richiesta) ";
		}
		
		if ($oggettoRichiesta->isImpresa() || $oggettoRichiesta->isNoProfit()) {
			$dql .= " AND (t.codice IN (
				'ACQUISTO_IMMOBILE_CENTRI_STORICI" . $suffix . "',
				'AFFITTO_IMMOBILE_CENTRI_STORICI" . $suffix . "',
				'ALTRO_CONTRATTO_IMMOBILE_CENTRI_STORICI" . $suffix . "',
				'AUTORIZZAZIONE_SUOLO_PUBBLICO_CENTRI_STORICI" . $suffix . "',
				'VISURA_CATASTALE_IMMOBILE_CENTRI_STORICI" . $suffix . "',
				'DICHIARAZIONE_ANTIMAFIA_CENTRI_STORICI" . $suffix . "',
				'DICHIARAZIONE_PRINCIPI_CENTRI_STORICI" . $suffix . "',
				'DICHIARAZIONE_LAVORI_STRUTTURA_CENTRI_STORICI" . $suffix . "'
				)
			) ";
		} elseif ($oggettoRichiesta->isAssociazione()) {
			$dql .= " AND (t.codice IN (
				'ACQUISTO_IMMOBILE_CENTRI_STORICI" . $suffix . "',
				'AFFITTO_IMMOBILE_CENTRI_STORICI" . $suffix . "',
				'ALTRO_CONTRATTO_IMMOBILE_CENTRI_STORICI" . $suffix . "',
				'AUTORIZZAZIONE_SUOLO_PUBBLICO_CENTRI_STORICI" . $suffix . "',
				'VISURA_CATASTALE_IMMOBILE_CENTRI_STORICI" . $suffix . "',
				'DICHIARAZIONE_ANTIMAFIA_CENTRI_STORICI" . $suffix . "',
				'DICHIARAZIONE_PRINCIPI_CENTRI_STORICI" . $suffix . "',
				'DICHIARAZIONE_INIZIO_ATTIVITA_CENTRI_STORICI" . $suffix . /*"',
				'REGOLARITA_CONTRIBUTIVA_CENTRI_STORICI" . $suffix . */"',
				'ATTO_COSTITUTIVO_E_STATUTO_CENTRI_STORICI" . $suffix . "',
				'DICHIARAZIONE_LAVORI_STRUTTURA_CENTRI_STORICI" . $suffix . "'
				)
			) ";
		} elseif ($oggettoRichiesta->isLiberoProfessionista()) {
			$dql .= " AND (t.codice IN (
				'ACQUISTO_IMMOBILE_CENTRI_STORICI" . $suffix . "',
				'AFFITTO_IMMOBILE_CENTRI_STORICI" . $suffix . "',
				'ALTRO_CONTRATTO_IMMOBILE_CENTRI_STORICI" . $suffix . "',
				'AUTORIZZAZIONE_SUOLO_PUBBLICO_CENTRI_STORICI" . $suffix . "',
				'VISURA_CATASTALE_IMMOBILE_CENTRI_STORICI" . $suffix . "',
				'DICHIARAZIONE_ANTIMAFIA_CENTRI_STORICI" . $suffix . "',
				'DICHIARAZIONE_PRINCIPI_CENTRI_STORICI" . $suffix . "',
				'DICHIARAZIONE_INIZIO_ATTIVITA_CENTRI_STORICI" . $suffix . /*"',
				'REGOLARITA_CONTRIBUTIVA_CENTRI_STORICI" . $suffix . */"',
				'DICHIARAZIONE_LAVORI_STRUTTURA_CENTRI_STORICI" . $suffix . "'
				)
			) ";
		}
		
		if ($solo_obbligatori == 1) {
			$dql .= " AND (t.obbligatorio = :solo_obbligatori";
			
			if (count($obbligatori) > 0) {
				$dql .= " OR t.codice IN ('" . implode("','", $obbligatori) . "')";
			}
			
			$dql .= ")";
			$q->setParameter(":solo_obbligatori", $solo_obbligatori);
		}
		
		$dql .= " ORDER BY t.id ASC";
		
		$q->setDQL($dql);
		$q->setParameter("tipologia", 'richiesta');
		$q->setParameter("id_procedura", $id_procedura);
		if ($gia_caricati) {
			$q->setParameter("id_richiesta", $id_richiesta);
		}
		
		return $q->getResult();
	}

    /**
     * @param int $id_richiesta
     * @param int $id_procedura
     * @param bool $solo_obbligatori
     * @param OggettoLegge14 $oggettoRichiesta
     * @param array array $obbligatori
     * @param bool $gia_caricati
     * @return array
     */
    public function ricercaDocumentiRichiestaLegge14(int $id_richiesta, int $id_procedura, bool $solo_obbligatori, OggettoLegge14 $oggettoRichiesta, array $obbligatori = array(), bool $gia_caricati = true) {
        $q = $this->getEntityManager()->createQuery();
        $dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
                WHERE t.procedura = :id_procedura AND t.tipologia LIKE :tipologia ";

        if ($gia_caricati) {
            $dql .= " AND t NOT IN (SELECT t1 FROM RichiesteBundle\Entity\DocumentoRichiesta r
                    JOIN r.documento_file d
                    JOIN d.tipologia_documento t1
                    WHERE r.richiesta = :id_richiesta) ";
        }

        // Prendo i documenti specifici per progetto e quelli generali
        if ($oggettoRichiesta->isTipologiaA()) {
            $dql .= " AND (SUBSTRING(t.codice, 1, 12) = 'LEGGE_14_A__') ";
        } elseif ($oggettoRichiesta->isTipologiaB()) {
            $dql .= " AND (SUBSTRING(t.codice, 1, 12) = 'LEGGE_14_B__') ";
        } elseif ($oggettoRichiesta->isTipologiaC()) {
            $dql .= " AND (SUBSTRING(t.codice, 1, 12) = 'LEGGE_14_C__') ";
        } elseif ($oggettoRichiesta->isTipologiaD()) {
            $dql .= " AND (SUBSTRING(t.codice, 1, 12) = 'LEGGE_14_D__') ";
        } elseif ($oggettoRichiesta->isTipologiaEF()) {
            $dql .= " AND (SUBSTRING(t.codice, 1, 13) = 'LEGGE_14_EF__') ";
        }

        if ($solo_obbligatori == 1) {
            $dql .= " AND (t.obbligatorio = :solo_obbligatori";

            if (count($obbligatori) > 0) {
                $dql .= " OR t.codice IN ('" . implode("','", $obbligatori) . "')";
            }

            $dql .= ")";
            $q->setParameter(":solo_obbligatori", $solo_obbligatori);
        }

        $dql .= " ORDER BY t.id ASC";

        $q->setDQL($dql);
        $q->setParameter("tipologia", 'richiesta');
        $q->setParameter("id_procedura", $id_procedura);
        if ($gia_caricati) {
            $q->setParameter("id_richiesta", $id_richiesta);
        }

        return $q->getResult();
    }

    /**
     * @param int $id_richiesta
     * @param int $id_procedura
     * @param bool $solo_obbligatori
     * @param array array $obbligatori
     * @param bool $gia_caricati
     * @return array
     */
    public function ricercaDocumentiProgrammaLegge14(int $id_richiesta, int $id_procedura, bool $solo_obbligatori, array $obbligatori = array(), bool $gia_caricati = true) {
        $q = $this->getEntityManager()->createQuery();
        $dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
                WHERE t.procedura = :id_procedura AND t.tipologia LIKE :tipologia ";

        if ($gia_caricati) {
            $dql .= " AND t NOT IN (
                SELECT tipologia_documento FROM RichiesteBundle:Bando98\DocumentoProgrammaLegge14 documento
                JOIN documento.documento_file file
                JOIN file.tipologia_documento tipologia_documento
                JOIN documento.programma_legge_14 programma_legge_14
                JOIN programma_legge_14.richieste richiesta
                WHERE richiesta.id = :id_richiesta) ";
        }

        $dql .= " AND SUBSTRING(t.codice, 1, 10) = 'LEGGE_14__' ";

        if ($solo_obbligatori == 1) {
            $dql .= " AND (t.obbligatorio = :solo_obbligatori";

            if (count($obbligatori) > 0) {
                $dql .= " OR t.codice IN ('" . implode("','", $obbligatori) . "')";
            }

            $dql .= ")";
            $q->setParameter(":solo_obbligatori", $solo_obbligatori);
        }

        $dql .= " ORDER BY t.id ASC";

        $q->setDQL($dql);
        $q->setParameter("tipologia", 'richiesta');
        $q->setParameter("id_procedura", $id_procedura);
        if ($gia_caricati) {
            $q->setParameter("id_richiesta", $id_richiesta);
        }

        return $q->getResult();
    }

    /**
     * @param int $procedura_id
     * @return array
     */
    public function ricercaDocumentiIstruttoriaIrap(int $procedura_id)
    {
        $dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
                WHERE t.tipologia = :tipologia ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter('tipologia', 'documenti_amministrativi_pa_istruttoria_bando_' . $procedura_id);
        return $q->getResult();
    }

    /**
     * @param int $procedura_id
     * @return array|int|string
     */
    public function ricercaDocumentiProceduraIstruttoria(int $procedura_id)
    {
        $dql = "SELECT t FROM DocumentoBundle\Entity\TipologiaDocumento t 
				WHERE t.procedura = :procedura_id AND t.tipologia = :tipologia ";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("procedura_id", $procedura_id);
        $q->setParameter("tipologia", 'istruttoria');
        return $q->getResult();
    }
}
