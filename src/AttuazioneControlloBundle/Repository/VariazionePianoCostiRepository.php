<?php

namespace AttuazioneControlloBundle\Repository;

use AttuazioneControlloBundle\Entity\VariazioneRichiestaRepository;
use RichiesteBundle\Entity\SezionePianoCosto;
use SfingeBundle\Entity\Procedura;
use AttuazioneControlloBundle\Entity\VariazioneRichiesta;

class VariazionePianoCostiRepository extends VariazioneRichiestaRepository {
    public function getTotaliVariazione($variazione) {
        $dql = "SELECT 
                SUM(COALESCE(voce.importo_variazione_anno_1,0)) as variato_1, 
                SUM(COALESCE(voce.importo_variazione_anno_2,0)) as variato_2, 
                SUM(COALESCE(voce.importo_variazione_anno_3,0)) as variato_3, 
                SUM(COALESCE(voce.importo_variazione_anno_4,0)) as variato_4, 
                SUM(COALESCE(voce.importo_variazione_anno_5,0)) as variato_5, 
                SUM(COALESCE(voce.importo_variazione_anno_6,0)) as variato_6, 
                SUM(COALESCE(voce.importo_variazione_anno_7,0)) as variato_7, 
                SUM(COALESCE(voce.importo_approvato_anno_1,0)) as approvato_1, 
                SUM(COALESCE(voce.importo_approvato_anno_2,0)) as approvato_2, 
                SUM(COALESCE(voce.importo_approvato_anno_3,0)) as approvato_3, 
                SUM(COALESCE(voce.importo_approvato_anno_4,0)) as approvato_4, 
                SUM(COALESCE(voce.importo_approvato_anno_5,0)) as approvato_5, 
                SUM(COALESCE(voce.importo_approvato_anno_6,0)) as approvato_6, 
                SUM(COALESCE(voce.importo_approvato_anno_7,0)) as approvato_7 

                FROM AttuazioneControlloBundle:VariazioneVocePianoCosto voce 
                INNER JOIN voce.variazione var 
                INNER JOIN var.attuazione_controllo_richiesta atc 
                INNER JOIN atc.richiesta rich 
                INNER JOIN voce.voce_piano_costo vpc 
                INNER JOIN vpc.piano_costo piano 
                WHERE var = :variazione AND piano.codice = 'TOT' 
                GROUP BY var.id";

        $q = $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('variazione', $variazione);

        $result = $q->getOneOrNullResult();

        return $result;
    }
    
    public function getTotaliVariazioneCodiceVoce($variazione, $codice) {
        $dql = "SELECT 
                SUM(COALESCE(voce.importo_variazione_anno_1,0)) as variato_1, 
                SUM(COALESCE(voce.importo_variazione_anno_2,0)) as variato_2, 
                SUM(COALESCE(voce.importo_variazione_anno_3,0)) as variato_3, 
                SUM(COALESCE(voce.importo_variazione_anno_4,0)) as variato_4, 
                SUM(COALESCE(voce.importo_variazione_anno_5,0)) as variato_5, 
                SUM(COALESCE(voce.importo_variazione_anno_6,0)) as variato_6, 
                SUM(COALESCE(voce.importo_variazione_anno_7,0)) as variato_7, 
                SUM(COALESCE(voce.importo_approvato_anno_1,0)) as approvato_1, 
                SUM(COALESCE(voce.importo_approvato_anno_2,0)) as approvato_2, 
                SUM(COALESCE(voce.importo_approvato_anno_3,0)) as approvato_3, 
                SUM(COALESCE(voce.importo_approvato_anno_4,0)) as approvato_4, 
                SUM(COALESCE(voce.importo_approvato_anno_5,0)) as approvato_5, 
                SUM(COALESCE(voce.importo_approvato_anno_6,0)) as approvato_6, 
                SUM(COALESCE(voce.importo_approvato_anno_7,0)) as approvato_7 

                FROM AttuazioneControlloBundle:VariazioneVocePianoCosto voce 
                INNER JOIN voce.variazione var 
                INNER JOIN var.attuazione_controllo_richiesta atc 
                INNER JOIN atc.richiesta rich 
                INNER JOIN voce.voce_piano_costo vpc 
                INNER JOIN vpc.piano_costo piano 
                WHERE var = :variazione AND piano.codice = '$codice' 
                GROUP BY var.id";

        $q = $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('variazione', $variazione);

        $result = $q->getOneOrNullResult();

        return $result;
    }

    public function getCostiVariazione(VariazioneRichiesta $variazione) {
        $dql = "SELECT 
                SUM( COALESCE(voci_variazione.importo_approvato_anno_1, 0)) +
                SUM( COALESCE(voci_variazione.importo_approvato_anno_2, 0)) +
                SUM( COALESCE(voci_variazione.importo_approvato_anno_3, 0)) +
                SUM( COALESCE(voci_variazione.importo_approvato_anno_4, 0)) +
                SUM( COALESCE(voci_variazione.importo_approvato_anno_5, 0)) +
                SUM( COALESCE(voci_variazione.importo_approvato_anno_6, 0)) +
                SUM( COALESCE(voci_variazione.importo_approvato_anno_7, 0)) as importo_approvato,
                SUM( COALESCE(voci_variazione.importo_variazione_anno_1, 0)) as importo_variazione_anno_1,
                SUM( COALESCE(voci_variazione.importo_variazione_anno_2, 0)) as importo_variazione_anno_2,
                SUM( COALESCE(voci_variazione.importo_approvato_anno_1, 0)) as importo_variazione_approvato_anno_1,
                SUM( COALESCE(voci_variazione.importo_approvato_anno_2, 0)) as importo_variazione_approvato_anno_2

            FROM AttuazioneControlloBundle:VariazioneRichiesta variazione 
            INNER JOIN variazione.voci_piano_costo voci_variazione
            INNER JOIN voci_variazione.voce_piano_costo voce_piano
            INNER JOIN voce_piano.piano_costo piano_costo
            WHERE variazione = :variazione
                AND piano_costo.codice <> 'TOT'
        ";

        return $this
                    ->getEntityManager()
                    ->createQuery($dql)
                    ->setMaxResults(1)
                    ->setParameter('variazione', $variazione)
                    ->getOneOrNullResult();
    }

    public function getVociVariazioneAggregate(Procedura $procedura, ?SezionePianoCosto $sezione, string $anno): array {
        $dql = "SELECT pc.titolo, 
                SUM(
                COALESCE(ipvc.importo_ammissibile_anno_$anno, 0)
                ) AS costo_ammesso,
                SUM(
                COALESCE(vpca.importo_approvato_anno_$anno, 0)
                ) AS importo_approvato
                
            FROM AttuazioneControlloBundle:VariazioneRichiesta AS variazione
            INNER JOIN variazione.attuazione_controllo_richiesta AS atc
            INNER JOIN atc.richiesta AS richiesta
            INNER JOIN richiesta.procedura AS procedura
            INNER JOIN variazione.voci_piano_costo AS vpca
            INNER JOIN vpca.voce_piano_costo AS vpc
            INNER JOIN vpc.istruttoria AS ipvc
            INNER JOIN vpc.piano_costo AS pc
            INNER JOIN pc.sezione_piano_costo as spc
            WHERE spc = COALESCE(:spc, spc)
            GROUP BY pc.titolo, pc.ordinamento
            ORDER BY pc.ordinamento
        ";

        return $this->getEntityManager()
                    ->createQuery($dql)
                    ->setParameter('spc', $sezione)
                    ->getResult();
    }
}
