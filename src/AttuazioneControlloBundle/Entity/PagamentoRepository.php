<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use RichiesteBundle\Entity\Richiesta;

class PagamentoRepository extends EntityRepository {

    public function getPagamentiInIstruttoria($ricercaIstruttoriaPagamenti) {
        // Ho creato queste variabili in modo di non dover scrivere gli stessi vincoli in più punti.
        $query_revoca = " (re.id IS NOT NULL AND re.atto_revoca IS NOT NULL) ";
        $query_progetto_concluso = " ((SELECT COUNT(pagamenti_sub.id) 
						 FROM AttuazioneControlloBundle:Pagamento as pagamenti_sub 
						 JOIN pagamenti_sub.modalita_pagamento AS modalita_pagamento_sub
						 JOIN pagamenti_sub.mandato_pagamento AS mandati_pagamento_sub
						 WHERE pagamenti_sub.attuazione_controllo_richiesta = pag.attuazione_controllo_richiesta AND pagamenti_sub.esito_istruttoria IS NOT NULL 
						 AND modalita_pagamento_sub.codice IN ('SALDO_FINALE', 'UNICA_SOLUZIONE')) > 0) ";

        $giorni_default = Pagamento::GIORNI_RISPOSTA_INTEGRAZIONE_DEFAULT;
        // Pensavo che con questo campo (giorniPerRispostaComunicazioni) non avrei dovuto interagire a livello di query.
        // Purtroppo mi sono accorto solamente dopo alcuni test che era necessario farlo.
        // Si poteva comunque fare un po' meglio se fosse stato possibile usare SUBSTRING_INDEX, ma con DOCTRINE non si può usare.
        $query_giorni = "SUBSTRING(
						SUBSTRING(
							SUBSTRING(
								REPLACE(REPLACE(REPLACE(rconf.giorniPerRispostaComunicazioni, 'i:', ''), '{', ''), '}', ''),
								LOCATE(
									mod.codice, REPLACE(REPLACE(REPLACE(rconf.giorniPerRispostaComunicazioni, 'i:', ''), '{', ''), '}', '')
								)
							),
							INSTR(
								SUBSTRING(
									REPLACE(REPLACE(REPLACE(rconf.giorniPerRispostaComunicazioni, 'i:', ''), '{', ''), '}', ''),
									LOCATE(
										mod.codice, REPLACE(REPLACE(REPLACE(rconf.giorniPerRispostaComunicazioni, 'i:', ''), '{', ''), '}', '')
									)
								), ';') + 1
						)
						,
						1,
						LOCATE(';',
							SUBSTRING(
								SUBSTRING(
									REPLACE(REPLACE(REPLACE(rconf.giorniPerRispostaComunicazioni, 'i:', ''), '{', ''), '}', ''),
									LOCATE(
										mod.codice, REPLACE(REPLACE(REPLACE(rconf.giorniPerRispostaComunicazioni, 'i:', ''), '{', ''), '}', '')
									)
								),
								INSTR(
									SUBSTRING(
										REPLACE(REPLACE(REPLACE(rconf.giorniPerRispostaComunicazioni, 'i:', ''), '{', ''), '}', ''),
										LOCATE(
											mod.codice, REPLACE(REPLACE(REPLACE(rconf.giorniPerRispostaComunicazioni, 'i:', ''), '{', ''), '}', '')
										)
									), ';') + 1
							)
						) - 1
					)";

        $query_non_integrato = " pag.esito_istruttoria IS NULL AND (s.codice = 'PAG_INVIATO_PA' OR s.codice = 'PAG_PROTOCOLLATO') AND ip.id IS NOT NULL AND ip.stato IN (29,30) AND (ipr.id IS NULL OR ipr.stato < 29) AND DATEDIFF(NOW(), COALESCE(rp3.data_pg, NOW())) > 
				  (CASE 
					  WHEN rich.procedura = 7 AND mod.codice = 'SAL'
						THEN 30
					  WHEN ip.giorni_per_risposta IS NOT NULL
						THEN ip.giorni_per_risposta
					  WHEN 
					  REGEXP(
					  $query_giorni
						, '^[0-9]+$') = 1 
					  
					  THEN $query_giorni
					 
					  ELSE 
						$giorni_default
				  END) ";

        $query_ammesso = " pag.esito_istruttoria IS NOT NULL AND pag.esito_istruttoria = 1 AND stcer.codice IS NULL AND pag.mandato_pagamento IS NULL ";
        $query_pagato = " pag.esito_istruttoria IS NOT NULL AND pag.mandato_pagamento IS NOT NULL AND stcer.codice IS NULL ";

        $dql = "SELECT concat(pers.nome, ' ',pers.cognome) as assegnatario, pag.id as id_pagamento, pag.esito_istruttoria, "
            . "concat(asse.titolo, ': ', asse.descrizione) as asse_procedura, proc.titolo as titolo_procedura, sogg.denominazione as soggetto, "
            . "CASE WHEN rp.num_pg IS NOT NULL "
            . "THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg) "
            . "ELSE '-' END AS protocollo_richiesta, "
            . "CASE WHEN rp2.num_pg IS NOT NULL "
            . "THEN concat(rp2.registro_pg, '/', rp2.anno_pg, '/', rp2.num_pg) "
            . "ELSE '-' END AS protocollo_pagamento, "

            //Pagamento inviato, senza nessuna richiesta di integrazione associata (Richiesto di visualizzarlo solo quando il pag è protocollato)
            . "CASE WHEN pag.esito_istruttoria IS NULL AND s.codice = 'PAG_PROTOCOLLATO' AND ip.id IS NULL AND vcpag_loco.id IS NULL "
            . "THEN 'In istruttoria' "
            //Pagamento inviato, senza esito, con una richiesta di integrazione inserita ancora da inviare al beneficiario
            . "WHEN pag.esito_istruttoria IS NULL AND (s.codice = 'PAG_INVIATO_PA' OR s.codice = 'PAG_PROTOCOLLATO') AND ip.id IS NOT NULL AND ip.stato = 26 "
            . "THEN 'Integrazione da inviare' "
            //Pagamento inviato, senza esito, con una richiesta di integrazione a cui il beneficiario non ha risposto entro i termini
            . "WHEN $query_non_integrato "
            . "THEN 'Non integrato' "
            //Pagamento inviato, senza esito, con una richiesta di integrazione a cui il benef deve rispondere
            . "WHEN pag.esito_istruttoria IS NULL AND (s.codice = 'PAG_INVIATO_PA' OR s.codice = 'PAG_PROTOCOLLATO') AND ip.id IS NOT NULL AND ip.stato IN (29,30) AND (ipr.id IS NULL OR ipr.stato < 29) "
            . "THEN 'In integrazione' "
            //Pagamento inviato, senza esito, con richiesta di integrazione a cui il benef ha risposto, e senza controlli in loco in corso
            . "WHEN pag.esito_istruttoria IS NULL AND (s.codice = 'PAG_INVIATO_PA' OR s.codice = 'PAG_PROTOCOLLATO') AND ip.id IS NOT NULL AND ipr.id IS NOT NULL AND (ipr.stato = 29 OR ipr.stato = 30) AND vcpag_loco.id IS NULL "
            . "THEN 'Integrato' "
            //Pagamento inviato, con la seconda CL dei controlli in loco presente ma NON validata (questa non potrebbe esistere se la prima CL non fosse validata).
            //NOTA: In questo modo, potrebbero non uscire i pagamenti campionati (count(pag.campioni > 0) MA per i quali non è ancora stata attivata la CL.
            //		Mi pare un bordello farlo, e cmq non è tra i requisiti dato che parlano solo di Checklist
            . "WHEN pag.esito_istruttoria IS NULL AND vcpag_loco.id IS NOT NULL AND vcpag_loco.validata != 1 "
            . "THEN 'Al controllo' "
            //Pagamento con esito istruttorio, con mandato di pagamento, non in certificazione e non certificato
            . "WHEN $query_pagato "
            . "THEN 'Pagato' "
            // Tutti i pagamenti con Esito Istruttoria positivo - Coincide con la validazione "liquidabile" della checklist
            . "WHEN $query_ammesso "
            . "THEN 'Ammesso' "
            // Tutti i pagamenti con Esito Istruttoria negativo - Coincide con la validazione "Non liquidabile" della checklist
            . "WHEN pag.esito_istruttoria IS NOT NULL AND pag.esito_istruttoria = 0 "
            . "THEN 'Non ammesso' "
            . "WHEN pag.esito_istruttoria IS NOT NULL AND pag.esito_istruttoria = 1 AND stcer.codice IS NOT NULL AND stcer.codice != 'CERT_APPROVATA' "
            . "THEN 'In certificazione' "
            . "WHEN pag.esito_istruttoria IS NOT NULL AND pag.esito_istruttoria = 1 AND stcer.codice IS NOT NULL AND stcer.codice  = 'CERT_APPROVATA' "
            . "THEN 'Certificato' "
            . "ELSE '-' END AS descrizione_esito, "
            . "CASE WHEN $query_revoca "
            . "THEN 'Revocato' "
            // Faccio una sub-select perchè devo testare se per il progetto (non a livello di singolo pagamento) è presente
            // il mandato di pagamento per il saldo oppure per l'unica soluzione.
            . "WHEN $query_progetto_concluso "
            . "THEN 'Concluso' "
            . "ELSE 'In attuazione' "
            . "END AS esito_progetto, "
            . "CASE WHEN pag.data_invio IS NOT NULL "
            . "THEN DATE_DIFF(CURRENT_DATE(), pag.data_invio) "
            . "ELSE '-' END AS giorni_istruttoria, "
            . "pag.data_invio as dataInvio, "
            . "CASE WHEN mod.codice = 'SAL' "
            . "THEN 'SAL' "
            . "ELSE mod.descrizione END AS descrizione, "
            . "rich.id as id_richiesta, "
            . "man.importo_pagato as importo_mandato, "
            . "pag.data_inizio_rendicontazione, "
            . "proc.id as procedura_id, "
            . "ep.stato as stato_pec_integrazione, "
            . "MAX(rp2.data_pg) as data_protocollo_pag, "
            . "rp3.data_pg data_protocollo_int, "
            . "rp4.data_pg data_protocollo_risp_int, "
            . "man.data_mandato, "
            . "CASE WHEN proc INSTANCE OF SfingeBundle:AssistenzaTecnica "
            . "THEN 'true' "
            . "WHEN  proc INSTANCE OF SfingeBundle:IngegneriaFinanziaria "
            . "THEN 'true' "
            . "WHEN  proc INSTANCE OF SfingeBundle:Acquisizioni "
            . "THEN 'true' "
            . "ELSE 'false' END AS procedura_particolare "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta ac "
            . "JOIN ac.richiesta rich "
            . "JOIN pag.stato s "
            . "JOIN rich.procedura proc "
            . "LEFT JOIN pag.modalita_pagamento mod "
            . "LEFT JOIN pag.certificazioni cp "
            . "LEFT JOIN cp.certificazione cer "
            . "LEFT JOIN cer.stato stcer "
            . "LEFT JOIN rich.istruttoria i "
            . "LEFT JOIN rich.proponenti prop "
            . "LEFT JOIN rich.richieste_protocollo rp "
            . "LEFT JOIN pag.richieste_protocollo rp2 "
            . "LEFT JOIN prop.soggetto_version sv "
            . "LEFT JOIN prop.soggetto sogg "
            . "LEFT JOIN proc.stato_procedura proc_s "
            . "JOIN proc.asse asse "
            . "LEFT JOIN pag.assegnamenti_istruttoria ai WITH ai.attivo = 1 "
            . "LEFT JOIN ai.istruttore istru "
            . "LEFT JOIN istru.persona pers "
            . "LEFT JOIN pag.mandato_pagamento man "
            . "LEFT JOIN pag.integrazioni ip "
            . "LEFT JOIN ip.risposta ipr "
            . "LEFT JOIN pag.valutazioni_checklist vcpag_loco WITH vcpag_loco.checklist = 11 " //Per sapere se il progetto ha la CL dei controlli in loco
            . "LEFT JOIN ip.richieste_protocollo rp3 " //Richieste protocollo dell'integrazione
            . "LEFT JOIN ipr.richieste_protocollo rp4 " //Risposta protocollo dell'integrazione
            . "LEFT JOIN rp3.emailProtocollo ep " //Email_Protocollo dell'integrazione
            . "LEFT JOIN ac.revoca re " //Per sapere se il progetto è in revoca
            . "LEFT JOIN proc.rendicontazioneProceduraConfig rconf " //Per sapere se il progetto è in revoca
            . "WHERE (s.codice = 'PAG_PROTOCOLLATO' OR s.codice = 'PAG_INVIATO_PA') AND rp INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento "
        ;

        $q = $this->getEntityManager()->createQuery();

        if (!is_null($ricercaIstruttoriaPagamenti->getIstruttoreCorrente())) {
            $dql .= " AND ai.istruttore = :istruttore AND ai.attivo = 1";
            $q->setParameter("istruttore", $ricercaIstruttoriaPagamenti->getIstruttoreCorrente()->getId());
        }

        if (!is_null($ricercaIstruttoriaPagamenti->getProcedura())) {
            $dql .= " AND proc.id = :procedura ";
            $q->setParameter("procedura", $ricercaIstruttoriaPagamenti->getProcedura()->getId());
        }

        if (!is_null($ricercaIstruttoriaPagamenti->getUtente())) {
            if ($ricercaIstruttoriaPagamenti->getUtente()->isInvitalia() == true) {
                $dql .= " AND proc.id IN (95, 121, 132, 167) ";
            } elseif ($ricercaIstruttoriaPagamenti->getUtente()->isOperatoreCogea() == true) {
                $dql .= " AND proc.id IN (2,5,58,64,67,70,72,75,77,81,83,107,110,111,112,116,128,140,142,161) ";
            }
        }



        if (!is_null($ricercaIstruttoriaPagamenti->getAsse())) {
            $dql .= " AND asse.id = :asse ";
            $q->setParameter("asse", $ricercaIstruttoriaPagamenti->getAsse());
        }

        if (!is_null($ricercaIstruttoriaPagamenti->getIdRichiesta())) {
            $dql .= " AND rich.id = :rich ";
            $q->setParameter("rich", $ricercaIstruttoriaPagamenti->getIdRichiesta());
        }

        if (!is_null($ricercaIstruttoriaPagamenti->getStatoIstruttoria())) {
            if ($ricercaIstruttoriaPagamenti->getStatoIstruttoria() == 'COMPLETA') {
                $dql .= " AND pag.esito_istruttoria IS NOT NULL ";
            } elseif ($ricercaIstruttoriaPagamenti->getStatoIstruttoria() == 'NON COMPLETA') {
                //Un progetto NON è completo se non ha l'esito (cioè deve essere ancora istruito, oppure ha i controlli in loco in corso - In entrambi i casi avrà l'esito a NULL)
                $dql .= " AND pag.esito_istruttoria IS NULL ";
            }
        }

        if (!is_null($ricercaIstruttoriaPagamenti->getStatoPagamento())) {
            if ($ricercaIstruttoriaPagamenti->getStatoPagamento() == 'IN ISTRUTTORIA') {
                $dql .= " AND pag.esito_istruttoria IS NULL AND ip.id IS NULL AND vcpag_loco.id IS NULL ";
            } elseif ($ricercaIstruttoriaPagamenti->getStatoPagamento() == 'INT DA INVIARE') {
                $dql .= " AND pag.esito_istruttoria IS NULL AND ip.id IS NOT NULL AND ip.stato = 26 ";
            } elseif ($ricercaIstruttoriaPagamenti->getStatoPagamento() == 'IN INTEGRAZIONE') {
                $dql .= " AND (pag.esito_istruttoria IS NULL AND ip.id IS NOT NULL AND ip.stato IN (29,30) AND (ipr.id IS NULL OR ipr.stato < 29)) AND NOT ($query_non_integrato) ";
            } elseif ($ricercaIstruttoriaPagamenti->getStatoPagamento() == 'INTEGRATO') {
                $dql .= " AND pag.esito_istruttoria IS NULL AND ip.id IS NOT NULL AND ipr.id IS NOT NULL AND (ipr.stato = 29 OR ipr.stato = 30) AND vcpag_loco.id IS NULL";
            } elseif ($ricercaIstruttoriaPagamenti->getStatoPagamento() == 'NON INTEGRATO') {
                $dql .= " AND $query_non_integrato ";
            } elseif ($ricercaIstruttoriaPagamenti->getStatoPagamento() == 'AL CONTROLLO') {
                $dql .= " AND pag.esito_istruttoria IS NULL AND vcpag_loco.id IS NOT NULL AND vcpag_loco.validata != 1";
            } elseif ($ricercaIstruttoriaPagamenti->getStatoPagamento() == 'AMMESSO') {
                $dql .= " AND ($query_ammesso) ";
            } elseif ($ricercaIstruttoriaPagamenti->getStatoPagamento() == 'PAGATO') {
                $dql .= " AND ($query_pagato) ";
            } elseif ($ricercaIstruttoriaPagamenti->getStatoPagamento() == 'NON_AMMESSO') {
                $dql .= " AND pag.esito_istruttoria IS NOT NULL AND  pag.esito_istruttoria = 0 ";
            } elseif ($ricercaIstruttoriaPagamenti->getStatoPagamento() == 'CERTIFICAZIONE') {
                $dql .= " AND pag.esito_istruttoria IS NOT NULL AND  pag.esito_istruttoria = 1 AND stcer.codice IS NOT NULL AND stcer.codice != 'CERT_APPROVATA' ";
            } elseif ($ricercaIstruttoriaPagamenti->getStatoPagamento() == 'CERTIFICATO') {
                $dql .= " AND pag.esito_istruttoria IS NOT NULL AND pag.esito_istruttoria = 1 AND stcer.codice IS NOT NULL AND stcer.codice  = 'CERT_APPROVATA' ";
            }
        }

        if (!is_null($ricercaIstruttoriaPagamenti->getEsitoProgetto())) {
            if ($ricercaIstruttoriaPagamenti->getEsitoProgetto() == 'REVOCATO') {
                $dql .= ' AND ' . $query_revoca;
            } elseif ($ricercaIstruttoriaPagamenti->getEsitoProgetto() == 'CONCLUSO') {
                // Lo str_replace è necessario altrimenti viene restituito l'errore che l'alias esiste già.
                $dql .= ' AND (' . str_replace('_sub', '_sub_where', $query_progetto_concluso) . ' AND NOT ' . $query_revoca . ') ';
            } elseif ($ricercaIstruttoriaPagamenti->getEsitoProgetto() == 'IN ATTUAZIONE') {
                // Lo str_replace è necessario altrimenti viene restituito l'errore che l'alias esiste già.
                $dql .= ' AND (NOT (' . $query_revoca . ') AND NOT (' . str_replace('_sub', '_sub_where', $query_progetto_concluso) . ')) ';
            }
        }

        if (!is_null($ricercaIstruttoriaPagamenti->getCodiceFiscale())) {
            $dql .= " AND sogg.codice_fiscale LIKE :cf ";
            $q->setParameter("cf", "%" . $ricercaIstruttoriaPagamenti->getCodiceFiscale() . "%");
        }

        if (!is_null($ricercaIstruttoriaPagamenti->getDenominazione())) {
            $dql .= " AND sogg.denominazione LIKE :denominazione ";
            $q->setParameter("denominazione", "%" . $ricercaIstruttoriaPagamenti->getDenominazione() . "%");
        }
        //Effettuo la ricerca sia sul protocollo della richiesta, che sul protocollo del pagamento
        if (!is_null($ricercaIstruttoriaPagamenti->getProtocollo())) {
            $dql .= "AND (CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo) OR  (CONCAT(rp2.registro_pg, '/' , rp2.anno_pg , '/' , rp2.num_pg) LIKE :protocollo) ";
            $q->setParameter("protocollo", "%" . $ricercaIstruttoriaPagamenti->getProtocollo() . "%");
        }

        if (!is_null($ricercaIstruttoriaPagamenti->getAssegnato())) {
            $dql .= $ricercaIstruttoriaPagamenti->getAssegnato() ? " AND istru.id IS NOT NULL " : " AND istru.id IS  NULL ";
        }

        if (!is_null($ricercaIstruttoriaPagamenti->getCertificazione())) {
            $dql .= " AND cer.id = :certificazione ";
            $q->setParameter("certificazione", $ricercaIstruttoriaPagamenti->getCertificazione());
        }

        if (!is_null($ricercaIstruttoriaPagamenti->getFinestraTemporale())) {
            $dql .= " AND rich.finestra_temporale = :finestra ";
            $q->setParameter("finestra", $ricercaIstruttoriaPagamenti->getFinestraTemporale());
        }

        $dql .= " GROUP BY pag.id ";
        $dql .= " ORDER BY pag.data_invio DESC ";

        $q->setDQL($dql);

        return $q;
    }

    public function getPagamentiDaCertificare($ricerca) {
        $dql = "SELECT pag 
				FROM AttuazioneControlloBundle:Pagamento pag 
				JOIN pag.attuazione_controllo_richiesta ac 
				LEFT JOIN pag.mandato_pagamento mp 
				LEFT JOIN pag.valutazioni_checklist cl 
				LEFT JOIN pag.certificazioni certPag 
				JOIN ac.richiesta rich 
				JOIN rich.proponenti prop 
				JOIN rich.istruttoria ist 
				JOIN prop.soggetto sogg 
				JOIN pag.stato s 
				JOIN pag.modalita_pagamento mod 
				JOIN rich.procedura proc 
				JOIN proc.asse asse 
				WHERE 
				(
					(
						(
							pag.importo_certificato is null 
							or pag.importo_certificato < mp.importo_pagato
						) 
						AND proc.id <> 8 
						AND mp.importo_pagato IS NOT NULL
					) 
					OR 
					(
					pag.importo_certificato is null  
					AND (proc.id = 8 OR ist.tipologia_soggetto = :pubblico)
					AND (
						cl.validata = 1 OR 
							(
								mod.codice = 'ANTICIPO' 
								AND mp.importo_pagato IS NOT NULL
							)
						)
					)
				) 
				AND (certPag.importo_taglio IS NULL OR certPag.importo_taglio - certPag.importo = 0)";

        $q = $this->getEntityManager()->createQuery();
        $q->setParameter('pubblico', IstruttoriaRichiesta::PUBBLICO);

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

        if (!is_null($ricerca->getBeneficiario())) {
            $dql .= " AND (sogg.denominazione LIKE :beneficiario OR sogg.acronimo_laboratorio LIKE :beneficiario) ";
            $q->setParameter("beneficiario", "%" . $ricerca->getBeneficiario() . "%");
        }

        if (!is_null($ricerca->getCup())) {
            $dql .= " AND (ac.cup LIKE :cup OR ist.codice_cup LIKE :cup) ";
            $q->setParameter("cup", "%" . $ricerca->getCup() . "%");
        }

        $dql .= " GROUP BY pag.id ";

        $q->setDQL($dql);

        return $q;
    }

    public function getPagamentiCertificati($ricerca) {
        $dql = "SELECT certpag "
            . "FROM CertificazioniBundle:CertificazionePagamento certpag "
            . "JOIN certpag.pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta ac "
            . "JOIN ac.richiesta rich "
            . "JOIN rich.istruttoria ist "
            . "JOIN rich.proponenti prop "
            . "JOIN prop.soggetto sogg "
            . "JOIN pag.stato s "
            . "JOIN rich.procedura proc "
            . "JOIN proc.asse asse "
            . "JOIN certpag.certificazione cert "
            . "WHERE cert.id = " . $ricerca->getCertificazione()->getId();
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
            $q->setParameter("beneficiario", "%" . $ricerca->getBeneficiario() . "%");
        }

        if (!is_null($ricerca->getCup())) {
            $dql .= " AND (ac.cup LIKE :cup OR ist.codice_cup LIKE :cup) ";
            $q->setParameter("cup", "%" . $ricerca->getCup() . "%");
        }

        $dql .= " GROUP BY pag.id ";

        $q->setDQL($dql);
        return $q;
    }

    public function getPagamentiInUniverso(\AuditBundle\Form\Entity\RicercaUniversoPagamenti $ricercaUniverso) {

        $dql = "LEFT JOIN pag.attuazione_controllo_richiesta atc "
            . "LEFT JOIN atc.richiesta rich "
            . "LEFT JOIN pag.certificazioni cert "
            . "LEFT JOIN cert.certificazione c "
            . "JOIN rich.stato s "
            . "JOIN rich.procedura proc "
            . "LEFT JOIN rich.istruttoria i "
            . "LEFT JOIN rich.proponenti prop "
            . "LEFT JOIN pag.richieste_protocollo rp "
            . "LEFT JOIN prop.soggetto_version sv "
            . "LEFT JOIN prop.soggetto sogg "
            . "LEFT JOIN proc.stato_procedura proc_s "
            . "JOIN proc.asse asse "
            . "WHERE 1=1 "
        ;

        $q = $this->getEntityManager()->createQuery();

        if (!is_null($ricercaUniverso->getTotaleCertificato())) {
            $q2 = $this->getEntityManager()->createQuery();
            $dql .= " GROUP BY pag.id";
            $dql .= " HAVING (SUM(COALESCE(cert.importo, 0))-SUM(COALESCE(cert.importo_taglio, 0))) {$ricercaUniverso->getTotaleCertificato()}";
            $dql .= " ORDER BY pag.data_invio DESC ";
            $q2->setDQL("SELECT count(pag) FROM AttuazioneControlloBundle:Pagamento pag " . $dql);
            $q2_sql = "SELECT COUNT(*) as conteggio FROM (" . $q2->getSQL() . ") temp";
        }

        if (!is_null($ricercaUniverso->getProcedura())) {
            $dql .= " AND proc.id = :procedura ";
            $q->setParameter("procedura", $ricercaUniverso->getProcedura()->getId());
        }

        if (!is_null($ricercaUniverso->getId())) {
            $dql .= " AND pag.id = :id_pagamento ";
            $q->setParameter("id_pagamento", $ricercaUniverso->getId());
        }

        if (!is_null($ricercaUniverso->getAsse())) {
            $dql .= " AND asse.id = :asse ";
            $q->setParameter("asse", $ricercaUniverso->getAsse());
        }

        if (!is_null($ricercaUniverso->getCodiceFiscale())) {
            $dql .= " AND sogg.codice_fiscale LIKE :cf ";
            $q->setParameter("cf", "%" . $ricercaUniverso->getCodiceFiscale() . "%");
        }

        if (!is_null($ricercaUniverso->getDenominazione())) {
            $dql .= " AND sogg.denominazione LIKE :denominazione ";
            $q->setParameter("denominazione", "%" . $ricercaUniverso->getDenominazione() . "%");
        }

        if (!is_null($ricercaUniverso->getProtocollo())) {
            $dql .= "AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo ";
            $q->setParameter("protocollo", "%" . $ricercaUniverso->getProtocollo() . "%");
        }

        if (!is_null($ricercaUniverso->getTitoloProgetto())) {
            $dql .= " AND rich.titolo LIKE :titolo ";
            $q->setParameter("titolo", "%" . $ricercaUniverso->getTitoloProgetto() . "%");
        }

        if (!is_null($ricercaUniverso->getFase())) {
            switch ($ricercaUniverso->getFase()) {
                case "PRESENTAZIONE":
                    $dql .= " AND i.id IS NOT NULL ";
                    break;
                case "ISTRUTTORIA":
                    $dql .= " AND i.esito IS NOT NULL ";
                    break;
                case "ATTUAZIONE":
                    $dql .= " AND atc.id IS NOT NULL ";
                    break;
                case "CERTIFICAZIONE":
                    $dql .= " AND pag.importo_certificato IS NOT NULL ";
                    break;
            }
        }

        if (!is_null($ricercaUniverso->getCertificazione())) {
            $certificazioni = $ricercaUniverso->getCertificazione();
            $certificazioni_ids = array();
            foreach ($certificazioni as $certificazione) {
                $certificazioni_ids[] = $certificazione->getId();
            }
            if (count($certificazioni_ids)) {
                $dql .= " AND cert.certificazione IN ('" . implode("','", $certificazioni_ids) . "') ";
            }
        }



        if (is_null($ricercaUniverso->getTotaleCertificato())) {
            $dql .= " GROUP BY pag.id";
            $dql .= " ORDER BY pag.data_invio DESC ";
        }

        $q->setDQL("SELECT pag FROM AttuazioneControlloBundle:Pagamento pag " . $dql);

        if (!is_null($ricercaUniverso->getTotaleCertificato())) {
            $count_result = $this->getEntityManager()->getConnection()->fetchAssoc($q2_sql);
            $q->setHint('knp_paginator.count', $count_result["conteggio"]);
        }

        return $q;
    }

    public function getIstruttoriPagamenti() {
        $dql = "SELECT u FROM SfingeBundle:Utente u 
				WHERE u.roles LIKE :ruolo";

        $q = $this->getEntityManager()->createQuery();
        $q->setParameter(":ruolo", "%ISTRUTTORE_ATC%");
        $q->setDQL($dql);

        return $q->getResult();
    }

    public function getSupervisoriAtc() {
        $dql = "SELECT u FROM SfingeBundle:Utente u 
				WHERE u.roles LIKE :ruolo";

        $q = $this->getEntityManager()->createQuery();
        $q->setParameter(":ruolo", "%ISTRUTTORE_SUPERVISORE_ATC%");
        $q->setDQL($dql);

        return $q->getResult();
    }

    public function getSchema31() {
        $dql = "SELECT u FROM SfingeBundle:Utente u 
				WHERE u.username IN ('DMCVCN81A15G273T','PTRLCU85L21D612Q')";

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        return $q->getResult();
    }

    public function getPagamentiIstruttoreCount($istruttore, $completata) {
        $dql = "SELECT count(pag)
                FROM AttuazioneControlloBundle:Pagamento pag 
                JOIN pag.assegnamenti_istruttoria ai
                
				WHERE pag.esito_istruttoria is " . ($completata ? "not null" : "null") . " AND ai.attivo = 1 AND ai.istruttore = " . $istruttore->getId();

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        return $q->getSingleScalarResult();
    }

    public function getPagamentiInviati($procedura = null, $finestraTemporale = null) {

        $dql = "SELECT pag "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta ac "
            . "JOIN ac.richiesta r "
            . "JOIN pag.stato s "
            . "WHERE (s.codice = 'PAG_PROTOCOLLATO' OR s.codice = 'PAG_INVIATO_PA') ";

        if (!is_null($procedura)) {
            $dql .= "AND r.procedura = {$procedura} ";
        }

        if (!is_null($finestraTemporale)) {
            $dql .= "AND r.finestra_temporale = {$finestraTemporale} ";
        }

        $dql .= "ORDER BY r.id";

        $q = $this->getEntityManager()->createQuery($dql);

        return $q->getResult();
    }

    public function getPagamentiInviatiPerQuestionarioRsi() {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());

        $array_campi_formulario = [
            'campo_personale_1', 'campo_personale_2', 'campo_personale_3', 'campo_personale_4', 'campo_personale_5', 'campo_personale_6',
            'campo_1_1_1', 'campo_1_1_2', 'campo_1_1_3', 'campo_1_1_4', 'campo_1_1_5', 'campo_1_1_6', 'campo_1_1_7', 'campo_1_1_8', 'campo_1_1_9', 'campo_1_1_10', 'campo_1_1_11', 'campo_1_1_12', 'campo_1_2_1',
            'campo_1_2_2', 'campo_1_2_3', 'campo_1_2_4', 'campo_1_2_5', 'campo_1_2_6', 'campo_1_2_7', 'campo_1_2_8',
            'campo_2_1_1', 'campo_2_1_2', 'campo_2_1_3', 'campo_2_1_4', 'campo_2_1_5', 'campo_2_1_6', 'campo_2_1_7', 'campo_2_1_8',
            'campo_3_1_1', 'campo_3_1_2', 'campo_3_1_3', 'campo_3_1_4', 'campo_3_1_5', 'campo_3_1_6', 'campo_3_1_7', 'campo_3_1_8', 'campo_3_1_9', 'campo_3_1_10', 'campo_3_1_11', 'campo_3_1_12', 'campo_3_1_13', 'campo_3_1_14', 'campo_3_1_15', 'campo_3_1_16', 'campo_3_1_17', 'campo_3_1_18',
            'campo_4_1_1', 'campo_4_1_2', 'campo_4_1_3', 'campo_4_1_4', 'campo_4_1_5', 'campo_4_1_6', 'campo_4_1_7', 'campo_4_1_8', 'campo_4_1_9', 'campo_4_1_10', 'campo_4_1_11', 'campo_4_1_12', 'campo_4_1_13', 'campo_4_1_14', 'campo_4_1_15', 'campo_4_1_16', 'campo_4_1_17', 'campo_4_1_18',
            'campo_4_1_19', 'campo_4_1_20', 'campo_4_1_21', 'campo_4_1_22', 'campo_4_1_23', 'campo_4_1_24', 'campo_4_1_25', 'campo_4_1_26', 'campo_4_1_27', 'campo_4_1_28', 'campo_4_1_29', 'campo_4_1_30', 'campo_4_1_31', 'campo_4_2_1',
            'campo_4_2_2', 'campo_4_2_3', 'campo_4_2_4', 'campo_4_2_5', 'campo_4_2_6', 'campo_4_2_7', 'campo_4_2_8', 'campo_4_3_1',
            'campo_4_3_2', 'campo_4_3_3', 'campo_4_3_4', 'campo_4_3_5', 'campo_4_3_6', 'campo_5_1_1',
            'campo_5_1_2', 'campo_5_1_3', 'campo_5_1_4', 'campo_5_1_5', 'campo_5_1_6', 'campo_5_1_7', 'campo_5_1_8', 'campo_5_1_9', 'campo_5_1_10', 'campo_5_1_11',
            'campo_5_2_1', 'campo_5_2_2', 'campo_5_2_3', 'campo_5_2_4', 'campo_5_2_5', 'campo_5_2_6',
            'campo_5_3_1', 'campo_5_3_2', 'campo_5_3_3', 'campo_5_3_4', 'campo_5_3_5', 'campo_5_3_6', 'campo_5_3_7', 'campo_5_3_8', 'campo_5_3_9', 'campo_5_3_10', 'campo_5_3_11', 'campo_5_3_12', 'campo_5_3_13', 'campo_5_3_14', 'campo_5_3_15', 'campo_5_3_16',
            'campo_5_4_1', 'campo_5_4_2',
            'campo_5_5_1', 'campo_5_5_2',
            'campo_5_6_1', 'campo_5_6_2', 'campo_6_1_1', 'campo_6_1_2',
        ];

        $array_campi = [
            'pagamento_id', 'data_invio_pagamento', 'protocollo', 'titolo_procedura', 'richiesta_id', 'data_invio', 'nome_fascicolo',
            'istanza_fascicolo_id', 'denominazione', 'codice_fiscale', 'partita_iva', 'email', 'tel', 'codice_ateco',
            'questionario_compilato', 'count_sedi_intervento', 'comune', 'provincia', 'via', 'numero',
        ];

        $array_campi = array_merge($array_campi, $array_campi_formulario);

        foreach ($array_campi_formulario as $campo) {
            $campi_formulario[] = "
            
            IF(@questionario_compilato = 'Proseguire senza compilare il questionario', '-',
            COALESCE(
                (
                SELECT GROUP_CONCAT(COALESCE(icc.valoreRaw, ic.valoreRaw) SEPARATOR '#')
                FROM fascicoli_istanze_fascicoli AS f 

                JOIN fascicoli_istanze_pagine AS ip ON (f.indice_id = ip.id)
                LEFT JOIN fascicoli_istanze_frammenti AS fi ON (fi.istanzaPagina_id = ip.id)
                LEFT JOIN fascicoli_istanze_pagine AS ipp ON (ipp.istanzaFrammentoContenitore_id = fi.id)
                LEFT JOIN fascicoli_istanze_frammenti AS fii ON (fii.istanzaPagina_id = ipp.id)

                LEFT JOIN fascicoli_istanze_pagine AS ippp ON (ippp.istanzaFrammentoContenitore_id = fii.id)
                LEFT JOIN fascicoli_istanze_frammenti AS fiii ON (fiii.istanzaPagina_id = ippp.id)
            
                LEFT JOIN fascicoli_istanze_campi AS ic ON (ic.istanzaFrammento_id = fii.id AND ic.data_cancellazione IS NULL)
                LEFT JOIN fascicoli_campi AS fc ON (fc.id = ic.campo_id)
            
                LEFT JOIN fascicoli_istanze_campi AS icc ON (icc.istanzaFrammento_id = fiii.id AND icc.data_cancellazione IS NULL)
                LEFT JOIN fascicoli_campi AS fcc ON (fcc.id = icc.campo_id)
            
                WHERE f.id = pagamento.istanza_fascicolo_id AND (fc.alias = '$campo' OR fcc.alias = '$campo')
                )
            , '-')
            ) AS $campo ";
        }

        $campi_formulario_sql = implode(', ', $campi_formulario);

        foreach ($array_campi as $campo) {
            $rsm->addScalarResult($campo, $campo);
        }

        $sql = "
            SELECT 
                pagamento.id AS 'pagamento_id',
                DATE_FORMAT(pagamento.data_invio, '%d-%m-%Y %H:%i:%s') AS 'data_invio_pagamento',
                COALESCE(
                    (
                    SELECT CONCAT(richiesta_protocollo.registro_pg, '/', richiesta_protocollo.anno_pg, '/', richiesta_protocollo.num_pg)
                        FROM richieste_protocollo AS richiesta_protocollo
                        WHERE richiesta.id = richiesta_protocollo.richiesta_id AND richiesta_protocollo.tipo = 'FINANZIAMENTO' 
                          AND richiesta_protocollo.stato = 'POST_PROTOCOLLAZIONE' AND richiesta_protocollo.data_cancellazione IS NULL
                        ORDER BY richiesta_protocollo.id DESC
                        LIMIT 0,1
                    ),
                '-') AS protocollo,
                bando.titolo AS 'titolo_procedura',
                richiesta.id AS 'richiesta_id',
                richiesta.data_invio,
                indice.alias AS 'nome_fascicolo',
                istanza_fascicolo.id AS 'istanza_fascicolo_id',
                   
                soggetto.denominazione,
                soggetto.codice_fiscale,
                soggetto.partita_iva,
                COALESCE(soggetto.email, '-') AS email,
                COALESCE(soggetto.tel, '-') AS tel,
                COALESCE(CONCAT(ateco.codice, ' ', ateco.descrizione), '-') AS codice_ateco,

                /** Se non sono presenti sedi metto di default 1 */
                @count_sedi_intervento := (SELECT IF(COUNT(id) = 0, 1, COUNT(id)) FROM sedi_operative AS sede WHERE sede.proponente_id = proponente.id AND data_cancellazione IS NULL) AS count_sedi_intervento,
                   
                @questionario_compilato :=
                      IF(indice.alias = 'principi_rsi_20191204', 'Compilare il questionario sul profilo di sostenibilita dell’impresa',
                          COALESCE(
                            (
                            SELECT ic.valoreRaw
                            FROM fascicoli_istanze_fascicoli AS f 
                        
                            JOIN fascicoli_istanze_pagine AS ip ON (f.indice_id = ip.id)
                            LEFT JOIN fascicoli_istanze_frammenti AS fi ON (fi.istanzaPagina_id = ip.id)
                            LEFT JOIN fascicoli_istanze_pagine AS ipp ON (ipp.istanzaFrammentoContenitore_id = fi.id)
                            LEFT JOIN fascicoli_istanze_frammenti AS fii ON (fii.istanzaPagina_id = ipp.id)
                            
                            LEFT JOIN fascicoli_istanze_pagine AS ippp ON (ippp.istanzaFrammentoContenitore_id = fii.id)
                            LEFT JOIN fascicoli_istanze_frammenti AS fiii ON (fiii.istanzaPagina_id = ippp.id)
                            
                            LEFT JOIN fascicoli_istanze_campi AS ic ON (ic.istanzaFrammento_id = fii.id)
                            LEFT JOIN fascicoli_campi AS fc ON (fc.id = ic.campo_id)
                            
                            WHERE f.id = pagamento.istanza_fascicolo_id AND fc.alias = 'campo_0'
                            )
                         , 'Proseguire senza compilare il questionario'
                         )
                    ) AS questionario_compilato,    

                COALESCE(
                    CASE
                        WHEN (COALESCE(proponente.sede_legale_come_operativa, 0) = 0 AND @count_sedi_intervento > 1) THEN
                            (
                                SELECT comune.denominazione
                                FROM sedi_operative AS sede_operativa 
                                JOIN sedi AS sede ON (sede_operativa.sede_id = sede.id AND sede.data_cancellazione IS NULL)
                                JOIN indirizzi AS indirizzo ON (sede.indirizzo_id = indirizzo.id)
                                JOIN geo_comuni AS comune ON (indirizzo.comune_id = comune.id)
                                WHERE sede_operativa.proponente_id = proponente.id AND sede_operativa.data_cancellazione IS NULL
                                LIMIT 0,1
                            )
                        ELSE
                            (
                                SELECT comune.denominazione
                                FROM geo_comuni AS comune
                                WHERE comune.id = soggetto.comune_id
                                LIMIT 0,1
                            )
                    END,
                    '-') AS comune,
                 
                COALESCE(
                    CASE
                        WHEN (COALESCE(proponente.sede_legale_come_operativa, 0) = 0 AND @count_sedi_intervento > 1) THEN
                            (
                                SELECT COALESCE(provincia.denominazione, indirizzo.provinciaEstera)
                                FROM sedi_operative AS sede_operativa 
                                JOIN sedi AS sede ON (sede_operativa.sede_id = sede.id AND sede.data_cancellazione IS NULL)
                                JOIN indirizzi AS indirizzo ON (sede.indirizzo_id = indirizzo.id)
                                JOIN geo_comuni AS comune ON (indirizzo.comune_id = comune.id)
                                LEFT JOIN geo_province AS provincia ON (comune.provincia_id = provincia.id)
                                WHERE sede_operativa.proponente_id = proponente.id AND sede_operativa.data_cancellazione IS NULL
                                LIMIT 0,1
                            )
                        ELSE
                            (
                                SELECT COALESCE(provincia.denominazione, soggetto.provinciaEstera)
                                FROM geo_comuni AS comune
                                LEFT JOIN geo_province AS provincia ON (comune.provincia_id = provincia.id)
                                WHERE comune.id = soggetto.comune_id
                                LIMIT 0,1
                            )
                    END,
                '-') AS provincia,
                   
                COALESCE(
                    CASE
                        WHEN (COALESCE(proponente.sede_legale_come_operativa, 0) = 0 AND @count_sedi_intervento > 1) THEN
                            (
                                SELECT indirizzo.via
                                FROM sedi_operative AS sede_operativa 
                                JOIN sedi AS sede ON (sede_operativa.sede_id = sede.id AND sede.data_cancellazione IS NULL)
                                JOIN indirizzi AS indirizzo ON (sede.indirizzo_id = indirizzo.id)
                                WHERE sede_operativa.proponente_id = proponente.id AND sede_operativa.data_cancellazione IS NULL
                                LIMIT 0,1
                            )
                        ELSE
                            (
                                soggetto.via
                            )
                    END,
                '-') AS via,
                
                COALESCE(
                   CASE
                    WHEN (COALESCE(proponente.sede_legale_come_operativa, 0) = 0 AND @count_sedi_intervento > 1) THEN
                        (
                            SELECT indirizzo.numero_civico
                            FROM sedi_operative AS sede_operativa 
                            JOIN sedi AS sede ON (sede_operativa.sede_id = sede.id AND sede.data_cancellazione IS NULL)
                            JOIN indirizzi AS indirizzo ON (sede.indirizzo_id = indirizzo.id)
                            WHERE sede_operativa.proponente_id = proponente.id AND sede_operativa.data_cancellazione IS NULL
                            LIMIT 0,1
                        )
                    ELSE
                        (
                            soggetto.civico
                        )
                    END,
                '-') AS numero, $campi_formulario_sql 
                
            FROM pagamenti AS pagamento
            JOIN stati AS stato ON (pagamento.stato_id = stato.id AND (stato.codice = 'PAG_PROTOCOLLATO' OR stato.codice = 'PAG_INVIATO_PA'))
            JOIN attuazione_controllo_richieste AS ac ON (pagamento.attuazione_controllo_richiesta_id = ac.id AND ac.data_cancellazione IS NULL)
            JOIN fascicoli_istanze_fascicoli AS istanza_fascicolo ON (pagamento.istanza_fascicolo_id = istanza_fascicolo.id)
            JOIN fascicoli_fascicoli AS fascicolo ON (istanza_fascicolo.fascicolo_id = fascicolo.id)
            JOIN fascicoli_pagine AS indice ON (fascicolo.indice_id = indice.id)
            JOIN richieste AS richiesta ON (ac.richiesta_id = richiesta.id AND richiesta.data_cancellazione IS NULL)
            JOIN procedure_operative AS bando ON (richiesta.procedura_id = bando.id AND bando.id NOT IN (7, 8, 32))
            JOIN rendicontazione_procedure_config AS rendicontazione_config ON (bando.id = rendicontazione_config.procedura_id)
            JOIN proponenti AS proponente ON (richiesta.id = proponente.richiesta_id AND proponente.data_cancellazione IS NULL AND proponente.mandatario = 1)
            JOIN soggetti AS soggetto ON (proponente.soggetto_id = soggetto.id AND soggetto.data_cancellazione IS NULL)
            LEFT JOIN ateco2007 AS ateco ON (soggetto.codice_ateco_id = ateco.id)
            
            WHERE pagamento.data_cancellazione IS NULL AND rendicontazione_config.sezione_rsi = 1 AND (indice.alias = 'principi_rsi_20191204' OR indice.alias = 'principi_rsi_20190220') 
            ORDER BY richiesta.data_invio ASC";
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        return $query->getResult();
    }

    public function getPagamentiInviatiPerQuestionarioRsiPerSettore($settore) {
        if ($settore == 'imprese_manifatturiere') {
            $settore = 'principi_rsi_imprese_manifatturiere';
        } else {
            $settore = 'principi_rsi_imprese_di_servizi';
        }

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());

        $array_campi_formulario = [
            'campo_caratterizzazione_azienda_1', 'campo_caratterizzazione_azienda_2',
            'campo_personale_1', 'campo_personale_2', 'campo_personale_3', 'campo_personale_4', 'campo_personale_5', 'campo_personale_6',
            'campo_1_1_1', 'campo_1_1_2', 'campo_1_1_3', 'campo_1_1_4', 'campo_1_1_5', 'campo_1_1_6', 'campo_1_1_7', 'campo_1_1_8', 'campo_1_1_9', 'campo_1_1_10', 'campo_1_1_11', 'campo_1_1_12', 'campo_1_1_13', 'campo_1_1_14', 'campo_1_1_15', 'campo_1_1_16',
            'campo_1_2_1', 'campo_1_2_2', 'campo_1_2_3', 'campo_1_2_4', 'campo_1_2_5', 'campo_1_2_6',
            'campo_2_1_1', 'campo_2_1_2', 'campo_2_1_3', 'campo_2_1_4', 'campo_2_1_5', 'campo_2_1_6', 'campo_2_1_7', 'campo_2_1_8', 'campo_2_1_9', 'campo_2_1_10', 'campo_2_1_11', 'campo_2_1_12', 'campo_2_1_13',
            'campo_3_1_1', 'campo_3_1_2', 'campo_3_1_3', 'campo_3_1_4', 'campo_3_1_5', 'campo_3_1_6', 'campo_3_1_7', 'campo_3_1_8', 'campo_3_1_9', 'campo_3_1_10', 'campo_3_1_11', 'campo_3_1_12', 'campo_3_1_13', 'campo_3_1_14', 'campo_3_1_15', 'campo_3_1_16', 'campo_3_1_17', 'campo_3_1_18',
            'campo_4_1_1', 'campo_4_1_2', 'campo_4_1_3', 'campo_4_1_4', 'campo_4_1_5', 'campo_4_1_6', 'campo_4_1_7', 'campo_4_1_8', 'campo_4_1_9', 'campo_4_1_10', 'campo_4_1_11', 'campo_4_1_12', 'campo_4_1_13',
            'campo_4_2_1', 'campo_4_2_2', 'campo_4_2_3', 'campo_4_2_4', 'campo_4_2_5', 'campo_4_2_6', 'campo_4_2_7',
            'campo_4_3_1', 'campo_4_3_2', 'campo_4_3_3', 'campo_4_3_4', 'campo_4_3_5', 'campo_4_3_6', 'campo_4_3_7', 'campo_4_3_8', 'campo_4_3_9', 'campo_4_3_10', 'campo_4_3_11',
            'campo_4_4_1', 'campo_4_4_2', 'campo_4_4_3', 'campo_4_4_4', 'campo_4_4_5', 'campo_4_4_6', 'campo_4_4_7',
            'campo_5_1_1', 'campo_5_1_2', 'campo_5_1_3', 'campo_5_1_4', 'campo_5_1_5', 'campo_5_1_6', 'campo_5_1_7', 'campo_5_1_8', 'campo_5_1_9', 'campo_5_1_10',
            'campo_6_1_1', 'campo_6_1_2', 'campo_6_1_3', 'campo_6_1_4', 'campo_6_1_5', 'campo_6_1_6',
            'campo_7_1_1', 'campo_7_1_2', 'campo_7_1_3', 'campo_7_1_4', 'campo_7_1_5', 'campo_7_1_6', 'campo_7_1_7', 'campo_7_1_8', 'campo_7_1_9', 'campo_7_1_10', 'campo_7_1_11', 'campo_7_1_12',
            'campo_8_1_1', 'campo_8_1_2',
            'campo_8_2_1',
            'campo_8_3_1', 'campo_8_3_2', 'campo_8_3_3', 'campo_8_3_4', 'campo_8_3_5', 'campo_8_3_6', 'campo_8_3_7', 'campo_8_3_8',
            'campo_8_4_1', 'campo_8_4_2', 'campo_8_4_3', 'campo_8_4_4', 'campo_8_4_5', 'campo_8_4_6', 'campo_8_4_7', 'campo_8_4_8',
            'campo_9_1_1', 'campo_9_1_2',
        ];

        $array_campi = [
            'pagamento_id', 'data_invio_pagamento', 'protocollo', 'titolo_procedura', 'richiesta_id', 'data_invio', 'nome_fascicolo',
            'istanza_fascicolo_id', 'denominazione', 'codice_fiscale', 'partita_iva', 'email', 'tel', 'codice_ateco',
            'count_sedi_intervento', 'count_sedi_intervento_reale', 'comune', 'provincia', 'via', 'numero',
        ];

        $array_campi = array_merge($array_campi, $array_campi_formulario);

        foreach ($array_campi_formulario as $campo) {
            $campi_formulario[] = "
            
            COALESCE(
                (
                SELECT GROUP_CONCAT(COALESCE(icc.valoreRaw, ic.valoreRaw) SEPARATOR '#')
                FROM fascicoli_istanze_fascicoli AS f 

                JOIN fascicoli_istanze_pagine AS ip ON (f.indice_id = ip.id)
                LEFT JOIN fascicoli_istanze_frammenti AS fi ON (fi.istanzaPagina_id = ip.id)
                LEFT JOIN fascicoli_istanze_pagine AS ipp ON (ipp.istanzaFrammentoContenitore_id = fi.id)
                LEFT JOIN fascicoli_istanze_frammenti AS fii ON (fii.istanzaPagina_id = ipp.id)

                LEFT JOIN fascicoli_istanze_pagine AS ippp ON (ippp.istanzaFrammentoContenitore_id = fii.id)
                LEFT JOIN fascicoli_istanze_frammenti AS fiii ON (fiii.istanzaPagina_id = ippp.id)
            
                LEFT JOIN fascicoli_istanze_campi AS ic ON (ic.istanzaFrammento_id = fii.id AND ic.data_cancellazione IS NULL)
                LEFT JOIN fascicoli_campi AS fc ON (fc.id = ic.campo_id)
            
                LEFT JOIN fascicoli_istanze_campi AS icc ON (icc.istanzaFrammento_id = fiii.id AND icc.data_cancellazione IS NULL)
                LEFT JOIN fascicoli_campi AS fcc ON (fcc.id = icc.campo_id)
            
                WHERE f.id = pagamento.istanza_fascicolo_id AND (fc.alias = '$campo' OR fcc.alias = '$campo')
                )
            , '-') AS $campo ";
        }

        $campi_formulario_sql = implode(', ', $campi_formulario);

        foreach ($array_campi as $campo ) {
            $rsm->addScalarResult($campo, $campo);
        }

        $sql = "
            SELECT 
                pagamento.id AS 'pagamento_id',
                DATE_FORMAT(pagamento.data_invio, '%d-%m-%Y %H:%i:%s') AS 'data_invio_pagamento',
                COALESCE(
                    (
                    SELECT CONCAT(richiesta_protocollo.registro_pg, '/', richiesta_protocollo.anno_pg, '/', richiesta_protocollo.num_pg)
                        FROM richieste_protocollo AS richiesta_protocollo
                        WHERE richiesta.id = richiesta_protocollo.richiesta_id AND richiesta_protocollo.tipo = 'FINANZIAMENTO' 
                          AND richiesta_protocollo.stato = 'POST_PROTOCOLLAZIONE' AND richiesta_protocollo.data_cancellazione IS NULL
                        ORDER BY richiesta_protocollo.id DESC
                        LIMIT 0,1
                    ),
                '-') AS protocollo,
                bando.titolo AS 'titolo_procedura',
                richiesta.id AS 'richiesta_id',
                richiesta.data_invio,
                indice.alias AS 'nome_fascicolo',
                istanza_fascicolo.id AS 'istanza_fascicolo_id',
                   
                soggetto.denominazione,
                soggetto.codice_fiscale,
                soggetto.partita_iva,
                COALESCE(soggetto.email, '-') AS email,
                COALESCE(soggetto.tel, '-') AS tel,
                COALESCE(CONCAT(ateco.codice, ' ', ateco.descrizione), '-') AS codice_ateco,

                /** Se non sono presenti sedi metto di default 1 */
                @count_sedi_intervento := (SELECT IF(COUNT(id) = 0, 1, COUNT(id)) FROM sedi_operative AS sede WHERE sede.proponente_id = proponente.id AND data_cancellazione IS NULL) AS count_sedi_intervento,
                
                /** Questa variabile viene utilizzata per decidere quale sede mostrare */   
                @count_sedi_intervento_reale := COALESCE((SELECT COUNT(id) FROM sedi_operative AS sede WHERE sede.proponente_id = proponente.id AND data_cancellazione IS NULL), 0) AS count_sedi_intervento_reale,
                
                COALESCE(
                    CASE
                        WHEN (COALESCE(proponente.sede_legale_come_operativa, 0) = 0 AND @count_sedi_intervento_reale > 0) THEN
                            (
                                SELECT comune.denominazione
                                FROM sedi_operative AS sede_operativa 
                                JOIN sedi AS sede ON (sede_operativa.sede_id = sede.id AND sede.data_cancellazione IS NULL)
                                JOIN indirizzi AS indirizzo ON (sede.indirizzo_id = indirizzo.id)
                                JOIN geo_comuni AS comune ON (indirizzo.comune_id = comune.id)
                                WHERE sede_operativa.proponente_id = proponente.id AND sede_operativa.data_cancellazione IS NULL
                                LIMIT 0,1
                            )
                        ELSE
                            (
                                SELECT comune.denominazione
                                FROM geo_comuni AS comune
                                WHERE comune.id = soggetto.comune_id
                                LIMIT 0,1
                            )
                    END,
                    '-') AS comune,
                 
                COALESCE(
                    CASE
                        WHEN (COALESCE(proponente.sede_legale_come_operativa, 0) = 0 AND @count_sedi_intervento_reale > 0) THEN
                            (
                                SELECT COALESCE(provincia.denominazione, indirizzo.provinciaEstera)
                                FROM sedi_operative AS sede_operativa 
                                JOIN sedi AS sede ON (sede_operativa.sede_id = sede.id AND sede.data_cancellazione IS NULL)
                                JOIN indirizzi AS indirizzo ON (sede.indirizzo_id = indirizzo.id)
                                JOIN geo_comuni AS comune ON (indirizzo.comune_id = comune.id)
                                LEFT JOIN geo_province AS provincia ON (comune.provincia_id = provincia.id)
                                WHERE sede_operativa.proponente_id = proponente.id AND sede_operativa.data_cancellazione IS NULL
                                LIMIT 0,1
                            )
                        ELSE
                            (
                                SELECT COALESCE(provincia.denominazione, soggetto.provinciaEstera)
                                FROM geo_comuni AS comune
                                LEFT JOIN geo_province AS provincia ON (comune.provincia_id = provincia.id)
                                WHERE comune.id = soggetto.comune_id
                                LIMIT 0,1
                            )
                    END,
                '-') AS provincia,
                   
                COALESCE(
                    CASE
                        WHEN (COALESCE(proponente.sede_legale_come_operativa, 0) = 0 AND @count_sedi_intervento_reale > 0) THEN
                            (
                                SELECT indirizzo.via
                                FROM sedi_operative AS sede_operativa 
                                JOIN sedi AS sede ON (sede_operativa.sede_id = sede.id AND sede.data_cancellazione IS NULL)
                                JOIN indirizzi AS indirizzo ON (sede.indirizzo_id = indirizzo.id)
                                WHERE sede_operativa.proponente_id = proponente.id AND sede_operativa.data_cancellazione IS NULL
                                LIMIT 0,1
                            )
                        ELSE
                            (
                                soggetto.via
                            )
                    END,
                '-') AS via,
                
                COALESCE(
                   CASE
                    WHEN (COALESCE(proponente.sede_legale_come_operativa, 0) = 0 AND @count_sedi_intervento_reale > 0) THEN
                        (
                            SELECT indirizzo.numero_civico
                            FROM sedi_operative AS sede_operativa 
                            JOIN sedi AS sede ON (sede_operativa.sede_id = sede.id AND sede.data_cancellazione IS NULL)
                            JOIN indirizzi AS indirizzo ON (sede.indirizzo_id = indirizzo.id)
                            WHERE sede_operativa.proponente_id = proponente.id AND sede_operativa.data_cancellazione IS NULL
                            LIMIT 0,1
                        )
                    ELSE
                        (
                            soggetto.civico
                        )
                    END,
                '-') AS numero, $campi_formulario_sql 
                
            FROM pagamenti AS pagamento
            JOIN stati AS stato ON (pagamento.stato_id = stato.id AND (stato.codice = 'PAG_PROTOCOLLATO' OR stato.codice = 'PAG_INVIATO_PA'))
            JOIN attuazione_controllo_richieste AS ac ON (pagamento.attuazione_controllo_richiesta_id = ac.id AND ac.data_cancellazione IS NULL)
            JOIN fascicoli_istanze_fascicoli AS istanza_fascicolo ON (pagamento.istanza_fascicolo_id = istanza_fascicolo.id)
            JOIN fascicoli_fascicoli AS fascicolo ON (istanza_fascicolo.fascicolo_id = fascicolo.id)
            JOIN fascicoli_pagine AS indice ON (fascicolo.indice_id = indice.id)
            JOIN richieste AS richiesta ON (ac.richiesta_id = richiesta.id AND richiesta.data_cancellazione IS NULL)
            JOIN procedure_operative AS bando ON (richiesta.procedura_id = bando.id)
            JOIN rendicontazione_procedure_config AS rendicontazione_config ON (bando.id = rendicontazione_config.procedura_id)
            JOIN proponenti AS proponente ON (richiesta.id = proponente.richiesta_id AND proponente.data_cancellazione IS NULL AND proponente.mandatario = 1)
            JOIN soggetti AS soggetto ON (proponente.soggetto_id = soggetto.id AND soggetto.data_cancellazione IS NULL)
            LEFT JOIN ateco2007 AS ateco ON (soggetto.codice_ateco_id = ateco.id)
            
            WHERE pagamento.data_cancellazione IS NULL AND rendicontazione_config.sezione_rsi = 1 AND indice.alias = '$settore' 
            ORDER BY richiesta.data_invio ASC";
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }
    public function getPagamentiInviatiNoFascicolo($procedura = null, $finestraTemporale = null) {

        $dql = "SELECT pag "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta ac "
            . "JOIN ac.richiesta r "
            . "JOIN pag.stato s "
            . "WHERE (s.codice = 'PAG_PROTOCOLLATO' OR s.codice = 'PAG_INVIATO_PA') ";

        if (!is_null($procedura)) {
            $dql .= "AND r.procedura = {$procedura} ";
        }

        if (!is_null($finestraTemporale)) {
            $dql .= "AND r.finestra_temporale = {$finestraTemporale} ";
        }

        $q = $this->getEntityManager()->createQuery($dql);

        return $q->getResult();
    }

    /**
     * @param Pagamento $pagamento
     * @return Pagamento|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findPagamentoPrimoSal(Pagamento $pagamento) {
        $dql = 'select pagamento '
            . 'from AttuazioneControlloBundle:Pagamento pagamento '
            . 'join pagamento.modalita_pagamento modalita_pagamento '
            . 'where pagamento.attuazione_controllo_richiesta = :atc '
            . 'and modalita_pagamento.codice = :SAL ';
        return $this->getEntityManager()->createQuery($dql)
                ->setParameter('atc', $pagamento->getAttuazioneControlloRichiesta())
                ->setParameter('SAL', ModalitaPagamento::PRIMO_SAL)
                ->getOneOrNullResult();
    }

    /**
     * @param Pagamento $pagamento
     * @return Pagamento|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findPagamentoSecondoSal(Pagamento $pagamento) {
        $dql = 'select pagamento '
            . 'from AttuazioneControlloBundle:Pagamento pagamento '
            . 'join pagamento.modalita_pagamento modalita_pagamento '
            . 'where pagamento.attuazione_controllo_richiesta = :atc '
            . 'and modalita_pagamento.codice = :SAL ';
        return $this->getEntityManager()->createQuery($dql)
                ->setParameter('atc', $pagamento->getAttuazioneControlloRichiesta())
                ->setParameter('SAL', ModalitaPagamento::SECONDO_SAL)
                ->getOneOrNullResult();
    }

    /**
     * Recupera i PAGAMENTI coperti da MANDATO, escluso l'ANTICIPO
     * Ritorna un array di Object (ANNO, IMPORTO) ordinato per ANNO
     *
     */
    public function getPagamentiPDC($idRichiesta): array {

        $dql = "SELECT DATE_FORMAT(pag.data_invio, '%Y') as anno, SUM(COALESCE(pag.importo_richiesto, 0)) as importo 
			FROM AttuazioneControlloBundle:Pagamento pag 
			INNER JOIN pag.attuazione_controllo_richiesta ac 
			INNER JOIN ac.richiesta r 
			INNER JOIN pag.mandato_pagamento m 
			INNER JOIN pag.modalita_pagamento mp 
			WHERE r.id = :idRichiesta 
				AND mp.id <> 1
				AND pag.data_invio IS NOT NULL
			GROUP BY anno 
			ORDER BY anno ";

        $q = $this->getEntityManager()->createQuery($dql);

        $q->setParameter('idRichiesta', $idRichiesta);

        return $q->getResult();
    }

    /**
     * @param integer $id_pagamento
     * @return Richiesta|null
     */
    public function getRichiesta($id_pagamento) {
        $dql = 'select richiesta from RichiesteBundle:Richiesta richiesta '
            . ' join richiesta.attuazione_controllo atc '
            . 'join atc.pagamenti pagamenti '
            . 'where pagamenti.id = :id_pagamento ';
        return $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('id_pagamento', $id_pagamento)
                ->getOneOrNullResult();
    }

    /**
     * @param integer $id_pagamento
     * @return array
     */
    public function getImpegni($id_pagamento) {
        $dql = 'select impegni, tc38 '
            . 'from AttuazioneControlloBundle:RichiestaImpegni impegni '
            . 'join impegni.richiesta richiesta '
            . 'join richiesta.attuazione_controllo atc '
            . 'join atc.pagamenti pagamenti '
            . 'left join impegni.tc38_causale_disimpegno tc38 '
            . 'where pagamenti.id = :id_pagamento ';
        return $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('id_pagamento', $id_pagamento)
                ->getResult();
    }

    public function getPagamentiConMandatoSenzaRendAmmByProcedura($procedura) {

        $dql = "SELECT pag "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta ac "
            . "JOIN ac.richiesta r "
            . "JOIN pag.stato s "
            . "WHERE s.codice = 'PAG_PROTOCOLLATO' AND pag.mandato_pagamento IS NOT NULL AND pag.importo_rendicontato_ammesso IS NULL ";

        $dql .= "AND r.procedura = :procedura ";

        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":procedura", $procedura);

        return $q->getResult();
    }

    public function getPagamentiConMandatoSenzaRendProcedura7($procedura = 7) {

        $dql = "SELECT pag "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta ac "
            . "JOIN ac.richiesta r "
            . "JOIN pag.stato s "
            . "WHERE s.codice = 'PAG_PROTOCOLLATO' AND pag.mandato_pagamento IS NOT NULL AND pag.importo_rendicontato_ammesso IS NULL ";

        $dql .= "AND r.procedura = :procedura ";
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":procedura", $procedura);
        $res1 = $q->getResult();

        $dql = "SELECT pag "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta ac "
            . "JOIN ac.richiesta r "
            . "JOIN pag.stato s "
            . "WHERE s.codice = 'PAG_PROTOCOLLATO' AND pag.importo_rendicontato IS NULL ";

        $dql .= "AND r.procedura = :procedura ";
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":procedura", $procedura);
        $res2 = $q->getResult();

        return array_unique(array_merge($res1, $res2), SORT_REGULAR);
        ;
    }

    public function getPagamentiConMandatoSenzaRendProcedura8($procedura = 8) {

        $dql = "SELECT pag "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta ac "
            . "JOIN ac.richiesta r "
            . "JOIN pag.stato s "
            . "WHERE s.codice = 'PAG_PROTOCOLLATO' AND pag.mandato_pagamento IS NOT NULL AND pag.importo_rendicontato_ammesso IS NULL ";

        $dql .= "AND r.procedura = :procedura ";
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":procedura", $procedura);
        $res1 = $q->getResult();

        $dql = "SELECT pag "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta ac "
            . "JOIN ac.richiesta r "
            . "JOIN pag.stato s "
            . "WHERE s.codice = 'PAG_PROTOCOLLATO' AND pag.importo_rendicontato IS NULL ";

        $dql .= "AND r.procedura = :procedura ";
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":procedura", $procedura);
        $res2 = $q->getResult();

        return array_unique(array_merge($res1, $res2), SORT_REGULAR);
        ;
    }

    public function getPagamentiSenzaRendByProcedura($procedura) {

        $dql = "SELECT pag "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta ac "
            . "JOIN ac.richiesta r "
            . "JOIN pag.stato s "
            . "WHERE s.codice = 'PAG_PROTOCOLLATO' AND pag.importo_rendicontato IS NULL ";

        $dql .= "AND r.procedura = :procedura ";

        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":procedura", $procedura);

        return $q->getResult();
    }

    public function getPagamentiIstruitiByProcedura($procedura) {

        $dql = "SELECT pag "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta atc "
            . "JOIN atc.richiesta r "
            . "JOIN pag.stato s "
            . "WHERE s.codice = 'PAG_PROTOCOLLATO' AND pag.importo_rendicontato IS NULL ";

        $dql .= "AND r.procedura = :procedura ";

        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":procedura", $procedura);

        return $q->getResult();
    }

    /**
     *
     * @return \AttuazioneControlloBundle\Entity\ProceduraAggiudicazione[]
     */
    public function getProcedureAggiudicazione(Pagamento $pagamento) {
        $dql = 'select proceduraAggiudicazione '
            . 'from AttuazioneControlloBundle:ProceduraAggiudicazione proceduraAggiudicazione '
            . 'join proceduraAggiudicazione.richiesta richiesta '
            . 'join richiesta.attuazione_controllo atc '
            . 'join atc.pagamenti pagamenti '
            . 'where pagamenti = :pagamento';

        return $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('pagamento', $pagamento)
                ->getResult();
    }

    /**
     * @param Pagamento $pagamento
     * @return Pagamento|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findPagamentoSal(Pagamento $pagamento, $codice) {
        $dql = 'select pagamento '
            . 'from AttuazioneControlloBundle:Pagamento pagamento '
            . 'join pagamento.modalita_pagamento modalita_pagamento '
            . 'where pagamento.attuazione_controllo_richiesta = :atc '
            . 'and modalita_pagamento.codice = :SAL ';
        return $this->getEntityManager()->createQuery($dql)
                ->setParameter('atc', $pagamento->getAttuazioneControlloRichiesta())
                ->setParameter('SAL', $codice)
                ->getOneOrNullResult();
    }

    public function getPagamentiByProcedura($procedura) {

        $dql = "SELECT pag "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta atc "
            . "JOIN atc.richiesta r "
            . "JOIN pag.stato s "
            . "WHERE s.codice = 'PAG_PROTOCOLLATO' ";

        $dql .= "AND r.procedura = :procedura ";

        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":procedura", $procedura);

        return $q->getResult();
    }

    public function getPagamentiByProceduraOggettoRichiestaFiere($procedura, $codice) {

        $dql = "SELECT pag "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta atc "
            . "JOIN atc.richiesta r "
            . "JOIN r.oggetti_richiesta ogg "
            . "JOIN RichiesteBundle\Entity\Bando28\OggettoFiere fiere with ogg = fiere "
            . "JOIN pag.stato s "
            . "WHERE s.codice = 'PAG_PROTOCOLLATO' AND ogg INSTANCE OF RichiesteBundle\Entity\Bando28\OggettoFiere AND fiere.tipologia = '$codice' ";

        $dql .= "AND r.procedura = :procedura ";

        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":procedura", $procedura);

        return $q->getResult();
    }

    public function getPagamentiConMandato() {

        $dql = "SELECT concat(asse.codice, '-',asse.descrizione) as asse_pag, "
            . "proc.titolo as proc_pag, "
            . "CASE WHEN rp.num_pg IS NOT NULL "
            . "THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg) "
            . "ELSE '-' END AS protocollo, "
            . "sogg.denominazione as ben_pag, "
            . "mand.importo_pagato as mand_pag, "
            . "DATE_FORMAT(mand.data_mandato, '%d/%m/%Y') as datamand_pag "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta ac "
            . "JOIN ac.richiesta r "
            . "LEFT JOIN r.richieste_protocollo rp "
            . "JOIN r.proponenti prop "
            . "JOIN r.procedura proc "
            . "JOIN proc.asse asse "
            . "JOIN prop.soggetto sogg "
            . "JOIN pag.stato s "
            . "JOIN pag.mandato_pagamento mand "
            . "WHERE s.codice in ('PAG_PROTOCOLLATO', 'PAG_INVIATO_PA') and prop.mandatario = 1 AND rp INSTANCE OF ProtocollazioneBundle:RichiestaProtocolloFinanziamento ";

        $q = $this->getEntityManager()->createQuery($dql);

        return $q->getResult();
    }

    public function getEstrazioneAudit($id_procedura): array {

        $dql = "SELECT "
            . "pag.id as id_pagamento, "
            . "DATE_FORMAT(pag.data_invio, '%d-%m-%Y') as data_invio, "
            . "rich.id as id_operazione, "
//Pagamento inviato e in istruttoria a prescindere da integrazioni o altro
            . "CASE WHEN pag.esito_istruttoria IS NULL AND s.codice = 'PAG_PROTOCOLLATO' AND vcpag_loco.id IS NULL "
            . "THEN 'In istruttoria' "
//pagamento al controllo in loco
            . "WHEN pag.esito_istruttoria IS NULL AND vcpag_loco.id IS NOT NULL AND vcpag_loco.validata <> 1 "
            . "THEN 'Al controllo' "
//Pagamento con esito istruttorio, con mandato di pagamento, non in certificazione e non certificato
            . "WHEN  pag.esito_istruttoria IS NOT NULL AND pag.mandato_pagamento IS NOT NULL AND stcer.codice IS NULL "
            . "THEN 'Pagato' "
// Tutti i pagamenti con Esito Istruttoria positivo - Coincide con la validazione "liquidabile" della checklist
            . "WHEN  pag.esito_istruttoria IS NOT NULL AND pag.esito_istruttoria = 1 AND stcer.codice IS NULL AND pag.mandato_pagamento IS NULL "
            . "THEN 'Ammesso' "
// Tutti i pagamenti con Esito Istruttoria negativo - Coincide con la validazione "Non liquidabile" della checklist
            . "WHEN pag.esito_istruttoria IS NOT NULL AND pag.esito_istruttoria = 0 "
            . "THEN 'Non ammesso' "
            . "WHEN pag.esito_istruttoria IS NOT NULL AND pag.esito_istruttoria = 1 AND stcer.codice IS NOT NULL AND stcer.codice <> 'CERT_APPROVATA' "
            . "THEN 'In certificazione' "
            . "WHEN pag.esito_istruttoria IS NOT NULL AND pag.esito_istruttoria = 1 AND stcer.codice IS NOT NULL AND stcer.codice  = 'CERT_APPROVATA' "
            . "THEN 'Certificato' "
            . "ELSE '-' END AS stato_pagamento, "
            . "CASE WHEN mod.codice = 'SAL' "
            . "THEN 'SAL' "
            . "ELSE mod.descrizione END AS causale, "
            . "man.importo_pagato as importo_mandato, "
            . "man.data_mandato, "
            . "coalesce(cp.importo,'-') as importo_proposto, "
            . "coalesce(cp.importo_taglio, '-') as taglio_ada, "
            . "coalesce(concat(cer.numero,'.',cer.anno_contabile), '-') as dpi, "
            . "coalesce(pag.importo_richiesto,'-') as importo_richiesto "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta ac "
            . "JOIN ac.richiesta rich "
            . "JOIN rich.procedura proc "
            . "JOIN proc.asse asse "
            . "JOIN pag.stato s "
            . "JOIN pag.modalita_pagamento mod "
            . "LEFT JOIN pag.certificazioni cp "
            . "LEFT JOIN cp.certificazione cer "
            . "LEFT JOIN cer.stato stcer "
            . "JOIN rich.istruttoria i "
            . "LEFT JOIN pag.mandato_pagamento man "
            . "LEFT JOIN pag.valutazioni_checklist vcpag_loco WITH vcpag_loco.checklist = 11 " //Per sapere se il progetto ha la CL dei controlli in loco
            . "WHERE (s.codice = 'PAG_PROTOCOLLATO' OR s.codice = 'PAG_INVIATO_PA') AND asse.id <> 8 "
        ;

        if ($id_procedura != 'all') {
            $dql .= " AND proc.id = {$id_procedura} ";
        }

        $dql .= " ORDER BY pag.id ";

        $q = $this->getEntityManager()->createQuery($dql);
        $sql = $q->getSQL();
        return $q->getResult();
    }
    
    public function getPagamentiInviatiGlobali() {

        $dql = "SELECT pag "
            . "FROM AttuazioneControlloBundle:Pagamento pag "
            . "JOIN pag.attuazione_controllo_richiesta ac "
            . "JOIN ac.richiesta r "
            . "JOIN pag.stato s "
            . "WHERE (s.codice = 'PAG_PROTOCOLLATO' OR s.codice = 'PAG_INVIATO_PA') "
            ." AND pag.data_invio >= :data ";

        $dql .= "ORDER BY r.id";

        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameter(":data", new \DateTime("2022-01-01"),\Doctrine\DBAL\Types\Type::DATETIME);
        
        return $q->getResult();
    }

}
