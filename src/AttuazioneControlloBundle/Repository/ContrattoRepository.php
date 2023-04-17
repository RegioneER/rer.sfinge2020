<?php

namespace AttuazioneControlloBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AttuazioneControlloBundle\Entity\Contratto;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;

class ContrattoRepository extends EntityRepository {

    /**
     * @param int $id_procedura
     * @return Contratto[]
     */
    public function getContrattiConPagamentoErrato($id_procedura) {
        return $this->createQueryBuilder('contratto')
                ->select(array('contratto', 'pagamento', 'estensione', 'durc', 'modalitaSecondoSal', 'atc'))
                ->join('contratto.pagamento', 'pagamento')
                ->leftJoin('pagamento.durc', 'durc')
                ->leftJoin('pagamento.estensione', 'estensione')
                ->join('pagamento.modalita_pagamento', 'modalitaSecondoSal')
                ->join('pagamento.attuazione_controllo_richiesta', 'atc')
                ->join('atc.richiesta', 'richiesta')
                ->join('richiesta.procedura', 'procedura')
                ->from('AttuazioneControlloBundle:Pagamento', 'primoSal')
                ->join('primoSal.modalita_pagamento', 'modalita')
                ->join('primoSal.attuazione_controllo_richiesta', 'atcPrimoSal')
                ->where("modalita.codice = :primoSal 
                and atcPrimoSal = atc 
                and modalitaSecondoSal.codice = :secondoSal
                and contratto.data_creazione < pagamento.data_creazione
                and procedura.id = :procedura
            ")
                ->setParameter('procedura', $id_procedura)
                ->setParameter('primoSal', ModalitaPagamento::PRIMO_SAL)
                ->setParameter('secondoSal', ModalitaPagamento::SECONDO_SAL)
                ->getQuery()
                ->getResult();
    }
    
    public function getEstrazioneAudit($procedura) {
        
        $dql = "SELECT "
            . "pag.id as id_pagamento, "
            . "ts.descrizione as tipologia_contratto, "
            . "c.fornitore as fornitore, "
            . "tf.descrizione as tipologia_fornitore, "
            . "ta.descrizione as stazione_appaltante, "
            . "c.altro_stazione_appaltante as altro_stazione_appaltante, "
            . "c.beneficiario, "
            . "c.fornitore, "
            . "c.piattaforma_committenza, "
            . "c.numero as numero_contratto, "
            . "c.descrizione as descrizione_contratto, "
            . "c.importo_contratto_complessivo, "
            . "DATE_FORMAT(c.dataInizio, '%d/%m/%Y') as data_contratto, "
            . "c.provvedimento_avvio_procedimento as provvediamento, "
            . "c.tipologia_atto_aggiudicazione as tipologia_atto, "
            . "c.num_atto_aggiudicazione as numero_atto, "
            . "DATE_FORMAT(c.data_atto_aggiudicazione, '%d/%m/%Y') as data_atto "
                . "FROM AttuazioneControlloBundle:Contratto c "
                . "JOIN c.pagamento pag "
                . "JOIN c.tipologiaSpesa ts "
                . "JOIN c.tipologiaFornitore tf "
                . "JOIN c.tipologia_stazione_appaltante ta "
                . "JOIN pag.attuazione_controllo_richiesta atc "
                . "JOIN atc.richiesta rich "
                . "JOIN rich.procedura p "
                . "WHERE p.id = {$procedura} "
        ;

        $q = $this->getEntityManager()->createQuery();
        $q->setDQL($dql);

        return $q->getResult();
    }

}
