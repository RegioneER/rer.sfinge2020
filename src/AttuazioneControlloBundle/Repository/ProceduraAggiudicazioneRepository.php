<?php

namespace AttuazioneControlloBundle\Repository;

use Doctrine\ORM\EntityRepository;
use RichiesteBundle\Entity\Richiesta;

class ProceduraAggiudicazioneRepository extends EntityRepository {
    
    public function getProcedureAggiudicazione(\MonitoraggioBundle\Form\Entity\RicercaProceduraAggiudicazione $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = "SELECT p 
                from AttuazioneControlloBundle:ProceduraAggiudicazione p 
                inner join p.richiesta richiesta  
                left join p.motivo_assenza_cig motivo_assenza_cig 
                left join p.tipo_procedura_aggiudicazione tipo_procedura_aggiudicazione 
                where 
                p.id = coalesce(:codice ,p.id) 
                and richiesta = :richiesta
                and coalesce(p.cig, '') like :cig 
                and coalesce(motivo_assenza_cig,0) = coalesce(:motivo_assenza_cig, motivo_assenza_cig, 0) 
                and coalesce(p.descrizione_procedura_aggiudicazione, '') like :descrizione_procedura_aggiudicazione 
                and coalesce(tipo_procedura_aggiudicazione,0) = coalesce(:tipo_procedura_aggiudicazione, tipo_procedura_aggiudicazione, 0) 
                and coalesce(p.importo_procedura_aggiudicazione,'-') = coalesce(:importo_procedura_aggiudicazione, p.importo_procedura_aggiudicazione, '-') 
                and coalesce(p.importo_aggiudicato, '-') = coalesce(:importo_aggiudicato, p.importo_aggiudicato ,'-') 
                order by p.id asc";
        $q->setDQL($query);
        $q->setParameter(':richiesta', $ricerca->getRichiesta() );
        $q->setParameter(':codice', $ricerca->getCodice() );
        $q->setParameter(':cig', '%'.$ricerca->getCig().'%' );
        $q->setParameter(':motivo_assenza_cig', $ricerca->getMotivoAssenzaCig() );
        $q->setParameter(':descrizione_procedura_aggiudicazione', '%'.$ricerca->getDescrizioneProceduraAggiudicazione().'%' );
        $q->setParameter(':tipo_procedura_aggiudicazione', $ricerca->getTipoProceduraAggiudicazione() );
        $q->setParameter(':importo_procedura_aggiudicazione', $ricerca->getImportoProceduraAggiudicazione() );
        $q->setParameter(':importo_aggiudicato', $ricerca->getImportoAggiudicato() );

        return $q;
    }

    public function getNuovoProgressivo(Richiesta $richiesta): int
    {
        $dql = "SELECT COUNT(p) + 1 
        FROM AttuazioneControlloBundle:ProceduraAggiudicazione p
        INNER JOIN p.richiesta richiesta 
        WHERE richiesta = :richiesta";
        $res = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('richiesta', $richiesta)
            ->getSingleScalarResult();

        return (int)$res;
    }
    
    public function getProcedureAggiudicazioneAudit($id_procedura){
       
        $query = "SELECT "
                . "richiesta.id as id_operazione, "
                . "p.id as codice_procedura, "
                . "p.cig as cig, "
                . "motivo.desc_motivo_assenza_cig as senza_cig, "
                . "p.descrizione_procedura_aggiudicazione as descrizione, "
                . "tipo_procedura_aggiudicazione.descrizione_tipologia_procedura_aggiudicazione tipo, "
                . "p.importo_procedura_aggiudicazione as importo, "
                . "DATE_FORMAT(p.data_aggiudicazione, '%d-%m-%Y') as data, "
                . "p.importo_aggiudicato as importo_aggiudicato, "
                . "DATE_FORMAT(p.data_pubblicazione, '%d-%m-%Y') as data_p " 
                ."from AttuazioneControlloBundle:ProceduraAggiudicazione p " 
                ."join p.richiesta richiesta "
                ."join richiesta.procedura proc "
                . "JOIN proc.asse asse "
                ."left join p.motivo_assenza_cig motivo " 
                ."left join p.tipo_procedura_aggiudicazione tipo_procedura_aggiudicazione "
                . "WHERE asse.id <> 8 ";
        
        if ($id_procedura != 'all') {
            $query .= " AND proc.id = {$id_procedura} ";
        }
        
        $query .= "order by p.id asc";
        
        $q = $this->getEntityManager()->createQuery(); 
		$q->setDQL($query);

		return $q->getResult();  
        

    }
}
