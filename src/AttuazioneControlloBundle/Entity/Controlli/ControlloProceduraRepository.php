<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use Doctrine\ORM\EntityRepository;

class ControlloProceduraRepository extends EntityRepository
{

    public function cercaControlliProcedure($ricercaControlli) {

		$dql = "SELECT controlli 
                FROM AttuazioneControlloBundle:Controlli\ControlloProcedura controlli 
                JOIN controlli.procedura procedura 
				JOIN procedura.richieste richieste 
				LEFT JOIN richieste.attuazione_controllo atc 
				LEFT JOIN atc.pagamenti pagamenti 
				JOIN richieste.controlli controlliRich 
				JOIN procedura.asse asse 
				JOIN procedura.azioni azione 
				JOIN procedura.atto at 
				WHERE 
				procedura.id = coalesce(:procedura, procedura.id) 
				AND asse.id = coalesce(:asse, asse.id)
				AND azione.id = coalesce(:azione, azione.id)
				AND at.id = coalesce(:at, at.id)
				ORDER BY controlli.id ASC
				";
		
		$q = $this->getEntityManager()->createQuery($dql);
		$q->setParameter("asse", $ricercaControlli->getAsse());
		$q->setParameter("procedura", $ricercaControlli->getProcedura());
		$q->setParameter("azione", $ricercaControlli->getAzione());
		$q->setParameter("at", $ricercaControlli->getAtto());

		return $q;
	}
	
	public function getImpreseCampionate($procedura) {
		$dql = "SELECT count(controlli) as num_campionate "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controlli "
				. "JOIN controlli.richiesta rich "
				. "JOIN rich.procedura proc "
				. "WHERE proc.id = $procedura";
		
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		
		$res = $q->getSingleScalarResult();
		
		return $res; 
		
	}
	
	public function getImpreseControllate($procedura) {
		$dql = "SELECT count(controlli) as num_campionate "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controlli "
				. "JOIN controlli.richiesta rich "
				. "JOIN rich.procedura proc "
				. "WHERE proc.id  = $procedura AND controlli.esito is not null";
		
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		
		$res = $q->getSingleScalarResult();
		
		return $res; 
		
	}
	
	public function getSpesaControllata($procedura) {
		$dql = "SELECT SUM(coalesce(pag.importo_rendicontato_ammesso,0)) as importo "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controlli "
				. "JOIN controlli.richiesta rich "
				. "JOIN rich.attuazione_controllo atc "
				. "JOIN atc.pagamenti pag "
				. "JOIN rich.procedura proc "
				. "WHERE proc.id  = $procedura AND controlli.esito IS NOT NULL ";
		
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		
		$res = $q->getSingleScalarResult();
		
		return $res; 
		
	}
	
	public function getSpesaIrregolare($procedura) {
		$dql = "SELECT SUM(coalesce(pag.importo_rendicontato_ammesso,0) - coalesce(pag.importo_rendicontato_ammesso_post_controllo, pag.importo_rendicontato_ammesso)) as importo "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controlli "
				. "JOIN controlli.richiesta rich "
				. "JOIN rich.attuazione_controllo atc "
				. "JOIN atc.pagamenti pag "
				. "JOIN rich.procedura proc "
				. "WHERE proc.id  = $procedura AND controlli.esito IS NOT NULL ";
		
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		
		$res = $q->getSingleScalarResult();
		
		return $res; 
		
	}
	
	public function getSpesaDecertificazioni($procedura) {
		$dql = "SELECT SUM(coalesce(pag.importo_decertificato,0)) as importo "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controlli "
				. "JOIN controlli.richiesta rich "
				. "JOIN rich.attuazione_controllo atc "
				. "JOIN atc.pagamenti pag "
				. "JOIN rich.procedura proc "
				. "WHERE proc.id  = $procedura";
		
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		
		$res = $q->getSingleScalarResult();
		
		return $res; 
		
	}
	
	public function getCampioniConRevoca($procedura) {
		$dql = "SELECT count(controlli) as num_campionate  "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controlli "
				. "JOIN controlli.richiesta rich "
				. "JOIN rich.attuazione_controllo atc "
				. "JOIN atc.revoca rev "
				. "JOIN rev.atto_revoca atto "
				. "JOIN atto.tipo_motivazione tipo "
                . "LEFT JOIN atto.tipo tipo2 "
				. "JOIN atc.pagamenti pag "
				. "JOIN rich.procedura proc "
				. "WHERE proc.id  = $procedura AND (tipo.codice = '1' OR tipo2.codice = 'TOT')";
		
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		
		$res = $q->getSingleScalarResult();
		
		return $res; 
		
	}
	
	public function getSpesaRendicontataAmmessaCampione($procedura) {
		$dql = "SELECT SUM(coalesce(pag.importo_rendicontato_ammesso,0)) as importo "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controlli "
				. "JOIN controlli.richiesta rich "
				. "JOIN rich.attuazione_controllo atc "
				. "JOIN atc.pagamenti pag "
				. "JOIN rich.procedura proc "
				. "WHERE proc.id  = $procedura";
		
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		
		$res = $q->getSingleScalarResult();
		
		return $res; 
		
	}
	
	public function getSpesaRendicontataAmmessa($procedura) {
		$dql = "SELECT SUM(coalesce(pag.importo_rendicontato_ammesso,0)) as importo "
                . "FROM AttuazioneControlloBundle:Pagamento pag "
				. "JOIN pag.attuazione_controllo_richiesta atc "
				. "JOIN atc.richiesta rich "
				. "JOIN rich.procedura proc "
				. "WHERE proc.id  = $procedura";
		
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		
		$res = $q->getSingleScalarResult();
		
		return $res; 
		
	}
    
    public function getCampioniConClRendNonAmmessa($procedura) {
		$dql = "SELECT count(controlli) as num_campionate  "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controlli "
				. "JOIN controlli.richiesta rich "
				. "JOIN rich.attuazione_controllo atc "
				. "JOIN atc.pagamenti pag "
				. "LEFT JOIN pag.valutazioni_checklist vcpag " //Per sapere se il progetto ha la CL
                . "LEFT JOIN vcpag.checklist chk " //Per sapere se il progetto ha la CL
				. "JOIN rich.procedura proc "
                . "LEFT JOIN atc.revoca rev "
				. "LEFT JOIN rev.atto_revoca atto "
				. "LEFT JOIN atto.tipo_motivazione tipo "
                . "LEFT JOIN atto.tipo tipo2 "
				. "WHERE proc.id  = $procedura AND chk.tipologia = 'PRINCIPALE' AND vcpag.ammissibile = 0 AND rev.id is NULL ";
		
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		
		$res = $q->getSingleScalarResult();
		
		return $res; 
		
	}
    
    public function getCampioniConClAmmesseSenzaEsito($procedura) {
        
		$dql = "SELECT count(distinct controlli) as num_campionate  "
                . "FROM AttuazioneControlloBundle:Controlli\ControlloProgetto controlli "
				. "JOIN controlli.richiesta rich "
				. "JOIN rich.attuazione_controllo atc "
				. "JOIN atc.pagamenti pag "
				. "LEFT JOIN pag.valutazioni_checklist vcpag " //Per sapere se il progetto ha la CL
                . "LEFT JOIN vcpag.checklist chk " //Per sapere se il progetto ha la CL
				. "JOIN rich.procedura proc "
                . "LEFT JOIN atc.revoca rev "
				. "LEFT JOIN rev.atto_revoca atto "
				. "LEFT JOIN atto.tipo_motivazione tipo "
                . "LEFT JOIN atto.tipo tipo2 "
				. "WHERE proc.id  = $procedura AND chk.tipologia = 'PRINCIPALE' AND vcpag.ammissibile = 1 AND rev.id is NULL AND controlli.esito is null ";        
		
		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		
		$res = $q->getSingleScalarResult();
		
		return $res; 
		
	}
}
