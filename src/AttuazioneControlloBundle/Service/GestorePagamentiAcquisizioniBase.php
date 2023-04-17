<?php

namespace AttuazioneControlloBundle\Service;

use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\Economia;

class GestorePagamentiAcquisizioniBase extends GestorePagamentiProcedureParticolariBase {
    
    public function getRuolo() {
        return "ROLE_GESTIONE_ACQUISIZIONI";
    }

    /**
     * @param \AttuazioneControlloBundle\Entity\Pagamento $pagamento
     *
     * Metodo richiamato per popolare automaticamente le economie per le ASS. TECNICHE
     */
    protected function popolaEconomieRER(Pagamento $pagamento){

        $em = $this->getEm();

        // Valore ritornato dalla funzione
        $economie = array();

        $richiesta = $pagamento->getRichiesta();


        // TC33/FONTE FINANZIARIA
        $fonteUE = $em->getRepository("MonitoraggioBundle:TC33FonteFinanziaria")->findBy(array("cod_fondo" => "ERDF"));
        $fonteStato = $em->getRepository("MonitoraggioBundle:TC33FonteFinanziaria")->findBy(array("cod_fondo" => "FDR"));
        $fonteRegione = $em->getRepository("MonitoraggioBundle:TC33FonteFinanziaria")->findBy(array("cod_fondo" => "FPREG"));

        // IPOTIZZATO che ASS TECNICA abbia 1 PIANO COSTO con 1 sola VOCE PIANO COSTO
        $vociPianoCosto = $richiesta->getVociPianoCosto();
        $costoAmmesso = $vociPianoCosto[0]->getImportoAnno1();  // Chiedere conferma...


        // I RENDICONTATI

        // PAGAMENTI PRECEDENTI + ATTUALE
        $pagamentiPrecedenti = $richiesta->getAttuazioneControllo()->getPagamenti();

        $contributoPagato = 0.00; // il totale pagato da UE-Stato-Regione // CONTRIBUTO EROGATO A SALDO
        $rendicontatoAmmesso = 0.00;	// quanto ha rendicontato il beneficiario


        foreach($pagamentiPrecedenti as $pagamentoPrecedente){

            // Sommo gli importi dei pagamenti COPERTI DA MANDATO
            if(!is_null($pagamentoPrecedente->getMandatoPagamento())){
                $contributoPagato += $pagamentoPrecedente->getMandatoPagamento()->getImportoPagato();  // Contributo erogato a SALDO
            }

            $rendicontatoAmmesso += $pagamentoPrecedente->getRendicontatoAmmesso();		// Somma degli IMPORTI APPROVATI dei GIUSTIFICATIVI
        }

        // GLI IMPORTI DELLE ECONOMIE
        $importoEconomiaTotale = $costoAmmesso - $rendicontatoAmmesso; // economia totale(UE-Stato-Regione)

        // Siamo nel CASO 2 dell'EXCEL - Parte PUBBLICA
        if ($importoEconomiaTotale > 0){

            // Creazione ECONOMIE
            $economiaUE = new Economia();
            $economiaStato = new Economia();
            $economiaRegione = new Economia();


            $importoEconomiaUE = round($importoEconomiaTotale * 50 / 100, 2);
            $importoEconomiaStato = round($importoEconomiaTotale * 35 / 100, 2);
            $importoEconomiaRegione = $importoEconomiaTotale - ($importoEconomiaUE + $importoEconomiaStato);

            // SETTO GLI IMPORTI ALLE 4 ECONOMIE
            $economiaUE->setImporto($importoEconomiaUE);
            $economiaStato->setImporto($importoEconomiaStato);
            $economiaRegione->setImporto($importoEconomiaRegione);

            // FONTE
            $economiaUE->setTc33FonteFinanziaria($fonteUE[0]);
            $economiaStato->setTc33FonteFinanziaria($fonteStato[0]);
            $economiaRegione->setTc33FonteFinanziaria($fonteRegione[0]);

            // RICHIESTA
            $economiaUE->setRichiesta($richiesta);
            $economiaStato->setRichiesta($richiesta);
            $economiaRegione->setRichiesta($richiesta);


            // IMPOSTO il valore di ritorno
            $economie[] = $economiaUE;
            $economie[] = $economiaStato;
            $economie[] = $economiaRegione;

            /*
             * TODO: Rivedere l'aggiornamento degli IMPEGNI (Inserimento DISIMPEGNO!! Non decrementare l'IMPEGNO!!!)
             */

            // TODO: è corretto prendere la causale "02 - Minori spese realizzate" ???
            $causaleDisimpegno = $em->getRepository("MonitoraggioBundle:TC38CausaleDisimpegno")->findOneBy(array("causale_disimpegno" => "02"));

            // DESTINAZIONE
            $richiestaDisimpegno = new \AttuazioneControlloBundle\Entity\RichiestaImpegni();
            $richiestaDisimpegno->setRichiesta($richiesta);
            $richiestaDisimpegno->setTc38CausaleDisimpegno($causaleDisimpegno);
            $richiestaDisimpegno->setTipologiaImpegno("D"); // D = DISIMPEGNO
            $richiestaDisimpegno->setImportoImpegno($importoEconomiaTotale);

            $disimpegno = new \AttuazioneControlloBundle\Entity\ImpegniAmmessi();
            $disimpegno->setRichiestaImpegni($richiestaDisimpegno);
            $disimpegno->setTc38CausaleDisimpegnoAmm($causaleDisimpegno);
            $disimpegno->setTipologiaImpAmm("D");
            $disimpegno->setImportoImpAmm($importoEconomiaTotale);

            // se per la fn01 esiste un solo livello gerarchico, inserisco anche la IMPEGNI_AMMESSI ?? e richieste_livelli_gerarchici
            // Se il cod_programma/liv_gerarchico è uno (…della RICHIESTA ???…), il record viene creato automaticamente con gli stessi dati dell'IMPEGNO ???

            // GESTIONE equivalente ai PAGAMENTI;
            // In ATTESA di sapere quale inserire...
            $richiestaProgrammi = $richiesta->getMonProgrammi();

            // 1 PROGRAMMA
            if(count($richiestaProgrammi) == 1)
            {
                $livelliGerarchici = $richiestaProgrammi[0]->getMonLivelliGerarchici();

                // 1 LIVELLO GERARCHICO
                if(count($livelliGerarchici) == 1){

                    $disimpegno->setRichiestaLivelloGerarchico($livelliGerarchici[0]);

                }

            }else{

                // Recupero un valore di appoggio per evitare che schianti in quanto obbligatorio....
                // In ATTESA di sapere quale inserire...
                $livelloGerarchico = $em->getRepository("MonitoraggioBundle:TC36LivelloGerarchico")->findBy(array("cod_liv_gerarchico" => "2014IT16RFOP008_8"));

                $disimpegno->setRichiestaLivelloGerarchico($livelloGerarchico[0]);
            }

            $richiestaDisimpegno->addMonImpegniAmmessi($disimpegno);

        }

        return $economie;

    }



}
