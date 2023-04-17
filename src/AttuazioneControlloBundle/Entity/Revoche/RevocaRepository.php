<?php

namespace AttuazioneControlloBundle\Entity\Revoche;

use Doctrine\ORM\EntityRepository;
use CertificazioniBundle\Entity\Certificazione;
use CertificazioniBundle\Entity\StatoChiusuraCertificazione;
use CertificazioniBundle\Entity\StatoCertificazione;

class RevocaRepository extends EntityRepository {
    
    /**
     * @param bool $inConti = true
     */
    public function iterateAuditInvioConti( $invioConti = true ){
        $dql = 'select distinct revoche, atc, richiesta, istruttoria,atto_revoca '
        .'from AttuazioneControlloBundle:Revoche\Revoca revoche '
        .'join revoche.attuazione_controllo_richiesta atc '
        .'join atc.richiesta richiesta '
        .'join richiesta.istruttoria istruttoria '
        .'left join revoche.atto_revoca atto_revoca '
        // .'join richiesta.richieste_protocollo protocollo '
        //.'join richiesta.proponenti proponenti '
        // .'join proponenti.soggetto soggetti '

        .'where revoche.invio_conti = :invioConti ';

        return $this->getEntityManager()
        ->createQuery($dql)
            ->setParameter('invioConti', $invioConti)
            ->iterate();
    }

    /**
     * @param Revoca $revoca
     * @return Certificazione
     */
    public function findCertificazioneRevoca( Revoca $revoca){
        $dql = 'select certificazioni '
        .'from CertificazioniBundle:Certificazione certificazioni '
        .'join certificazioni.chiusura chiusura '
        .'join chiusura.revoche_invio_conti revoche '
        .'where revoche = :revoca '
        .'order by certificazioni.data_creazione ';
        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('revoca', $revoca)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function iterateAuditRevocheConRecupero(){
        $dql = 'select distinct recuperi, revoche, atc, richiesta, istruttoria, atto_revoca, procedura '
        .'from AttuazioneControlloBundle:Revoche\Recupero recuperi '
        .'join recuperi.revoca revoche '
        .'join revoche.attuazione_controllo_richiesta atc '
        .'join atc.richiesta richiesta '
        .'join richiesta.istruttoria istruttoria '
        .'left join revoche.atto_revoca atto_revoca '
        .'join richiesta.procedura procedura '
        ;      
        return $this->getEntityManager()
            ->createQuery($dql)
            ->iterate();        
    }

    public function iteratePagamentiCertificatiConChiusura($codiceStatoChiusura = StatoChiusuraCertificazione::CHI_APPROVATA )
    {
        $dql = 'select distinct pagamenti, atc, richiesta, istruttoria '
        .'from AttuazioneControlloBundle:Pagamento pagamenti '
        .'join pagamenti.certificazioni pag_cert '
        .'join pag_cert.certificazione certificazioni '
        .'join certificazioni.chiusura chiusura '
        .'join chiusura.stato stato_chiusura '
        .'join pagamenti.attuazione_controllo_richiesta atc '
        .'join atc.richiesta richiesta '
        .'join richiesta.istruttoria istruttoria '
        .'where stato_chiusura.codice = :codiceChiusura '
        ;
        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('codiceChiusura',$codiceStatoChiusura)
            ->iterate();      
    }
	
	public function iteratePagamentiCertificati($codiceStatoCertificazione = StatoCertificazione::CERT_APPROVATA )
    {
        $dql = 'select distinct pagamenti, atc, richiesta, istruttoria '
        .'from AttuazioneControlloBundle:Pagamento pagamenti '
        .'join pagamenti.certificazioni pag_cert '
        .'join pag_cert.certificazione certificazioni '
        .'join certificazioni.stato stato_certificazione '
        .'join pagamenti.attuazione_controllo_richiesta atc '
        .'join atc.richiesta richiesta '
        .'join richiesta.istruttoria istruttoria '
        .'where stato_certificazione.codice = :codiceCertificazione '
        ;
        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('codiceCertificazione',$codiceStatoCertificazione)
            ->iterate();      
    }
	
    /**
     * @param bool $inConti = true
     */
    public function iterateCertAgreaInvioConti($codiceStatoRec){
        $dql = 'select revoche '
        .'from AttuazioneControlloBundle:Revoche\Revoca revoche '
        .'left join revoche.recuperi recuperi '
        .'join revoche.attuazione_controllo_richiesta atc '
        .'join atc.richiesta richiesta '
		.'join atc.pagamenti pag '
		.'left join pag.certificazioni cert '
        .'join richiesta.istruttoria istruttoria '
        .'left join revoche.atto_revoca atto_revoca '
        .'join richiesta.procedura procedura '
		.'left join recuperi.tipo_fase_recupero fase '
		.'where fase.codice = :fase_rec OR revoche.invio_conti = 1 '
        ;      
		
		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("fase_rec", $codiceStatoRec);
		$res = $q->getResult();
		
		return $res;
    }	
    
    public function iterateCertAgreaUniverso(){
        $dql = 'select revoche '
        .'from AttuazioneControlloBundle:Revoche\Revoca revoche '
        .'left join revoche.recuperi recuperi '
        .'join revoche.attuazione_controllo_richiesta atc '
        .'join atc.richiesta richiesta '
		.'left join atc.pagamenti pag '
		.'left join pag.certificazioni cert '
        .'join richiesta.istruttoria istruttoria '
        .'left join revoche.atto_revoca atto_revoca '
        .'join richiesta.procedura procedura '
		.'left join recuperi.tipo_fase_recupero fase '
		.'where 1 = 1 '
        ;      
		
		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        //$a = $q->getSQL();
		$res = $q->getResult();
		
		return $res;
    }	
	
    public function iterateCertAgreaRevocheConRecupero($codiceStatoRec){
        $dql = 'select revoche '
        .'from AttuazioneControlloBundle:Revoche\Revoca revoche '
        .'left join revoche.recuperi recuperi '
        .'join revoche.attuazione_controllo_richiesta atc '
        .'join atc.richiesta richiesta '
		.'join atc.pagamenti pag '
		.'left join pag.certificazioni cert '
        .'join richiesta.istruttoria istruttoria '
        .'left join revoche.atto_revoca atto_revoca '
        .'join richiesta.procedura procedura '
		.'join recuperi.tipo_fase_recupero fase '
		.'where fase.codice = :fase_rec OR revoche.invio_conti = 1 '
        ;      
		
		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("fase_rec", $codiceStatoRec);
		$res = $q->getResult();
		
		return $res;
    
    }
	
    public function iteratePagamentiCertAgreaCertificati($codiceStatoCer)
    {
        $dql = "select "
		."pagamenti.id as id_pagamento, "
		."pagamenti.importo_certificato as importo_certificato, "
		."mandato.importo_pagato as importo_pagato, "
		."SUM(coalesce(cert_pag.importo, 0)) as importo_proposto, "
		."cert.anno_contabile as anno_contabile, "
		."cert.numero as numero, "
		."soggetto.denominazione as denominazione, "
		."asse.titolo as titolo_asse, "
		."richiesta.id as id_richiesta, "
                ."istruttoria.codice_cup as cup_richiesta, "
		."CASE WHEN rp.num_pg IS NOT NULL "
		."THEN concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg) "
		."ELSE '-' END AS protocollo_richiesta, "
		."CASE WHEN rp2.num_pg IS NOT NULL "
		."THEN concat(rp2.registro_pg, '/', rp2.anno_pg, '/', rp2.num_pg) "
		."ELSE '-' END AS protocollo_pagamento, "
		."richiesta.titolo as titolo, "
		."procedura.titolo as titolo_procedura, "
		."modalita_pagamento.descrizione as mod_pagamento "
        ."from AttuazioneControlloBundle:Pagamento pagamenti "
		."join pagamenti.attuazione_controllo_richiesta atc "
		."join pagamenti.modalita_pagamento modalita_pagamento "
        ."join atc.richiesta richiesta "
		."join pagamenti.certificazioni cert_pag "
		."join cert_pag.certificazione cert "
		."join cert.stato stato "
        ."join richiesta.istruttoria istruttoria "
		."join richiesta.proponenti proponenti with proponenti.mandatario = 1 "
		."join proponenti.soggetto soggetto "
        ."join richiesta.procedura procedura "
		."join procedura.asse asse "
		."JOIN richiesta.richieste_protocollo rp "
		."LEFT JOIN pagamenti.richieste_protocollo rp2 "
		."JOIN pagamenti.mandato_pagamento mandato "
		."where stato.codice = :stato_cer AND rp2.richiesta_protocollo_pagamento_precedente is null "
		."GROUP BY pagamenti.id "
        ;      
		
		$q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);
        $q->setParameter("stato_cer", $codiceStatoCer);
		
		$res = $q->getResult();
		
		return $res;
    }	
	
}
