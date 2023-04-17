<?php

namespace MonitoraggioBundle\GestoriPianoCosto;

use MonitoraggioBundle\Service\IGestorePianoCosto;
use MonitoraggioBundle\Service\AGestorePianoCosto;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\RichiestaPianoCosti;
use AttuazioneControlloBundle\Entity\StatoPagamento;
use AttuazioneControlloBundle\Entity\Pagamento;
use SfingeBundle\Entity\AssistenzaTecnica;

class PianoCostoGenerico extends AGestorePianoCosto implements IGestorePianoCosto {
    /**
     * @return float importo da realizzare per richiesta
     */
    protected function getPianoCostoTotaleDaRealizzare() {        
        $dql = "SELECT sum( coalesce( ivpc.importo_ammissibile_anno_1, 0)) + 
                sum( coalesce( ivpc.importo_ammissibile_anno_2, 0)) + 
                sum( coalesce( ivpc.importo_ammissibile_anno_3, 0)) + 
                sum( coalesce( ivpc.importo_ammissibile_anno_4, 0)) + 
                sum( coalesce( ivpc.importo_ammissibile_anno_5, 0)) + 
                sum( coalesce( ivpc.importo_ammissibile_anno_6, 0)) + 
                sum( coalesce( ivpc.importo_ammissibile_anno_7, 0)) as da_realizzare 
            from IstruttorieBundle:IstruttoriaVocePianoCosto ivpc
            INNER JOIN ivpc.voce_piano_costo vpc
            INNER JOIN vpc.piano_costo piano_costo
            INNER JOIN piano_costo.tipo_voce_spesa tipo_voce_spesa
            INNER JOIN vpc.richiesta richiesta
            where tipo_voce_spesa.codice <> 'TOTALE' and richiesta = :richiesta 
        ";

        if($this->richiesta->getProcedura() instanceof AssistenzaTecnica){
            $dql = "SELECT 
                    sum( coalesce( vpc.importo_anno_1, 0)) + 
                    sum( coalesce( vpc.importo_anno_2, 0)) + 
                    sum( coalesce( vpc.importo_anno_3, 0)) + 
                    sum( coalesce( vpc.importo_anno_4, 0)) + 
                    sum( coalesce( vpc.importo_anno_5, 0)) + 
                    sum( coalesce( vpc.importo_anno_6, 0)) + 
                    sum( coalesce( vpc.importo_anno_7, 0)) as da_realizzare 
                from RichiesteBundle:VocePianoCosto vpc
                INNER JOIN vpc.piano_costo piano_costo
                INNER JOIN vpc.richiesta richiesta
                where richiesta = :richiesta 
            ";
        }

        $query = $this->container->get('doctrine')->getManager()
            ->createQuery($dql);
        $risultato = $query
            ->setParameter('richiesta', $this->richiesta)
            ->getOneOrNullResult();
        if (is_null($risultato)) {
            throw new \Exception('La query per prendere i totali del piano costo non ha tornato risultati: ' . $query->getSQL());
        }

        return $risultato['da_realizzare'];
    }

    /**
     * @return Pagamento[] elenco ordinato dei pagamenti della richiesta nella forma array(importo, data)
     *                   per trasformare un array in iteratore usare ArrayIterator
     */
    protected function getImportiPagamenti(): array {
        $dql = "SELECT 
                SUM(coalesce(pagamento.importo_rendicontato_ammesso,  0)) importo, 
                YEAR(CASE WHEN COALESCE(istruttoria.tipologia_soggetto, 'PUBBLICO') = 'PUBBLICO' or mandato.id is null
                            THEN pagamento.data_invio 
                            ELSE  mandato.data_mandato
                    END) as anno
            from AttuazioneControlloBundle:Pagamento pagamento
            inner join pagamento.attuazione_controllo_richiesta atcRichiesta
            inner join atcRichiesta.richiesta richiesta
            inner join richiesta.procedura procedura
            inner join richiesta.istruttoria istruttoria
            inner join pagamento.stato stato
            left join pagamento.mandato_pagamento mandato
            where richiesta = :richiesta 
                and (stato.codice = :protocollato
                    OR (
                        stato.codice = :inviato AND (
                            procedura INSTANCE OF SfingeBundle:AssistenzaTecnica
                            OR procedura INSTANCE OF SfingeBundle:Acquisizioni
                            OR procedura INSTANCE OF SfingeBundle:IngegneriaFinanziaria
                        )
                    )
                )
            group by anno
            order by pagamento.data_invio ASC
        ";
        $em = $this->container->get('doctrine')->getManager();

        $res =  $em->createQuery($dql)
             ->setParameter('protocollato', StatoPagamento::PAG_PROTOCOLLATO)
             ->setParameter('inviato', StatoPagamento::PAG_INVIATO_PA)
             ->setParameter('richiesta', $this->richiesta)
             ->getResult();
        
        $vociAnno = [];
        foreach ($res as $voce) {
            $anno =$voce['anno'];
            if(!\array_key_exists($anno, $vociAnno)){
                $vociAnno[$anno] = 0.0;
            }
            $vociAnno[$anno] += $voce['importo'];
        }

        return $vociAnno;
    }

    /**
     * @return RichiestaPianoCosti[]
     */
    public function generaArrayPianoCostoTotaleRealizzato(): iterable {
        $pagamentiAnnui = $this->getImportiPagamenti();
        $annoAttuale = (new \DateTime())->format('Y');
        $res = [];
        $primoAnno = $this->getPrimoAnno();

        $importoDaRealizzare = $this->getPianoCostoTotaleDaRealizzare();
        $importoRealizzato = 0;
        for ($anno = $primoAnno; $anno <= $annoAttuale; ++$anno) {
            $importoPagamento = $pagamentiAnnui[$anno] ?? 0.0;
            $importoDaRealizzare -= $importoPagamento;
         
            $voce_anno_corrente = new RichiestaPianoCosti($this->richiesta);
            $voce_anno_corrente->setImportoDaRealizzare($importoDaRealizzare);
            $voce_anno_corrente->setImportoRealizzato($importoPagamento);
            $voce_anno_corrente->setAnnoPiano($anno);
            $res[$anno] = $voce_anno_corrente;
            if($anno > $primoAnno){
                $res[$anno-1]->setImportoDaRealizzare(0.0);
            }
            //Condizione break for
            if ($importoDaRealizzare <= 0 ) {
                $res[$anno]->setImportoDaRealizzare(0.0);
                $res[$anno]->setImportoRealizzato($importoPagamento + $importoDaRealizzare);
                break; //Esco dal for se non ci sono altri pagamenti e non è necessario realizzare altro
            }
        }

        return $res;
    }

    protected function getPrimoAnno():int{
        $atc = $this->richiesta->getAttuazioneControllo();
        if($atc && $atc->getDataAvvio()){
            return \intval($atc->getDataAvvio()->format('Y'));
        }
        $protocollo = $this->richiesta->getRichiesteProtocollo()->first();
        $anniPianoCosto = \array_values($this->getAnnualita());
        $primoAnno = false === $protocollo || \is_null($protocollo->getAnnoPg()) ?
            $anniPianoCosto[0] ?? null :
            $protocollo->getAnnoPg();
        return $primoAnno;
    }

    public static function arrayKeyCastToString($value) {
        return '"' . $value . '"';
    }

    /**
     * @param string $value Stringa avente primo ed ultimo carattere una carattere separatore
     * @return int valore numerico intero contenuto nella stringa
     */
    public static function arrayKeyCastToInt(string $value): int {
        return \intval(\substr($value, 1, count($value) - 2));
    }
}
