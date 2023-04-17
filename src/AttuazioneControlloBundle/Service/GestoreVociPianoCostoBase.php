<?php

namespace AttuazioneControlloBundle\Service;

use AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo;
use BaseBundle\Exception\SfingeException;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\StatoPagamento;
use DocumentoBundle\Component\ResponseException;

class GestoreVociPianoCostoBase extends AGestoreVociPianoCosto {

    public function getVociPianoCosto($proponente, $pagamento) {
        $voci = array();
        $hasPianoCostiMultiSezioni = $proponente->getRichiesta()->getProcedura()->hasPianoCostiMultiSezione();
        foreach ($proponente->getVociPianoCosto() as $voce) {
            $pianoCosto = $voce->getPianoCosto();
            if ($pianoCosto->getCodice() != 'TOT' && !$pianoCosto->isVoceSpesaGenerale()) {

                // se il piano costi è multi sezione generiamo la struttura dati per la OptionGroup che avrà il nome delle diverse sezioni
                // altrimenti mettiamo una select classica
                if ($hasPianoCostiMultiSezioni) {
                    $sezione = $pianoCosto->getSezionePianoCosto();
                    $sezioneString = $sezione->getTitoloSezione();
                    if (!array_key_exists($sezioneString, $voci)) {
                        $voci[$sezioneString] = array();
                    }
                    $voci[$sezioneString][] = $voce;
                } else {
                    $voci[] = $voce;
                }
            }
        }

        return $voci;
    }

    // rendicontazione standard..
    // TODO.. da ripensare ed inserire dentro l'aggiungi e modifica..non è detto che serva
    public function verificaImportoDaApprovato($voce_piano, $modifica = false) {
        $giustificativo = $voce_piano->getGiustificativoPagamento();
        $pagamento = $giustificativo->getPagamento();
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $annualitaArray = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($richiesta->getMandatario()->getId());

        $esito = new \stdClass();
        $esito->esito = true;
        $em = $this->getEm();
        $voce_istruttoria = $voce_piano->getVocePianoCostoIstruttoria();
        $annualita = $voce_piano->getAnnualita();
        $voci_presenti = $em->getRepository("AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo")->findBy(array("voce_piano_costo_istruttoria" => $voce_istruttoria, "annualita" => $annualita));
        $importoVoci = 0.00;
        // quali voci_presenti tenere in considerazione?
        // escludere i pagamenti con esito negativo e quelli cancellati...
        // ma questo controllo non doveva essere disabilitato???
        foreach ($voci_presenti as $voce) {
            try {
                // escludere i pagamenti con esito negativo
                $pagamentoV = $voce->getGiustificativoPagamento()->getPagamento();
                if (!is_null($pagamentoV->getEsitoIstruttoria()) && !$pagamentoV->getEsitoIstruttoria()) {
                    continue;
                }

                $importoVoci += $voce->getImporto();
            } catch (\Exception $e) {
                
            }
        }
        if ($modifica == true) {
            $importoVociEattuale = $importoVoci;
        } else {
            $importoVociEattuale = $importoVoci + $voce_piano->getImporto();
        }
        if ($pagamento->isAssistenzaTecnica() == true || $pagamento->isAcquisizioni()) {
            $importoAmmesso = $voce_piano->getImporto();
        } else {
            $funzione = "getImportoAmmissibileAnno" . $annualita;
            $importoAmmesso = $voce_istruttoria->$funzione();
        }

        return $esito;
    }

    public function validaGenerale() {
        $esito = new \stdClass();
        $esito->esito = true;
        $esito->message = '';
        return $esito;
    }

    public function aggiungiVocePianoCosto($id_giustificativo, $options = array()) {

        $em = $this->getEm();

        $giustificativo = $em->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);
        $pagamento = $giustificativo->getPagamento();
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $proponente = $giustificativo->getProponente();

        if ($pagamento->isRichiestaDisabilitata()) {
            $this->addErrorRedirect('Il pagamento è disabilitato', "dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo));
        }

        $voce_piano = new \AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo();
        $giustificativo->addVocePianoCosto($voce_piano);

        if ($richiesta->getProcedura()->isMultiPianoCosto()) {
            $proponenteVoce = $proponente;
        } else {
            $proponenteVoce = $richiesta->getMandatario();
        }

        $options["url_indietro"] = $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo));
        $options["voci_piano_costo"] = $this->getVociPianoCosto($proponenteVoce, $pagamento);
        $options["annualita"] = $this->getAnnualitaRendicontazione($richiesta);

        $form = $this->createForm("AttuazioneControlloBundle\Form\PagamentoVocePianoCostoType", $voce_piano, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $pianoCostoPresentato = $voce_piano->getVocePianoCosto();
                $pianoCostoAmmesso = $pianoCostoPresentato->getIstruttoria();
                $voce_piano->setVocePianoCostoIstruttoria($pianoCostoAmmesso);

                if (count($options["annualita"]) == 1) {
                    $chiavi = array_keys($options["annualita"]);
                    $voce_piano->setAnnualita($chiavi[0]);
                }

                try {
                    $em->beginTransaction();

                    $em->persist($voce_piano);
                    $em->flush();

                    $esitoGenerali = $this->gestioneGiustificativoSpeseGenerali($pagamento, $proponente);
                    if ($esitoGenerali == false) {
                        throw new \Exception('Errore nel calcolo delle spese generali');
                    }

                    $giustificativo->calcolaImportoRichiesto();

                    $em->flush();

                    $em->commit();

                    return $this->addSuccesRedirect("La voce piano costo è stata correttamente aggiunta", "dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo));
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                    $this->container->get('logger')->error($e->getTraceAsString());
                }
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio giustificativo", $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo)));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Aggiungi voce piano costo");

        return $this->render("AttuazioneControlloBundle:VociPianoCosto:aggiungiVocePianoCosto.html.twig", $dati);
    }

    public function modificaVocePianoCosto($id_voce_piano, $options = array()) {
        $em = $this->getEm();
        /** @var VocePianoCostoGiustificativo $voce_piano */
        $voce_piano = $em->getRepository("AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo")->find($id_voce_piano);
        $giustificativo = $voce_piano->getGiustificativoPagamento();
        $pagamento = $giustificativo->getPagamento();
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $proponente = $giustificativo->getProponente();

        $options["url_indietro"] = $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId()));

        if ($richiesta->getProcedura()->isMultiPianoCosto()) {
            $proponenteVoce = $proponente;
        } else {
            $proponenteVoce = $richiesta->getMandatario();
        }

        $options["voci_piano_costo"] = $this->getVociPianoCosto($proponenteVoce, $pagamento);
        $options["annualita"] = $this->getAnnualitaRendicontazione($richiesta);
        $options['disabled'] = $pagamento->isRichiestaDisabilitata() || !$giustificativo->isModificabileIntegrazione() || $giustificativo->getGiustificativoOrigine();

        $form = $this->createForm("AttuazioneControlloBundle\Form\PagamentoVocePianoCostoType", $voce_piano, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $pianoCostoPresentato = $voce_piano->getVocePianoCosto();
                $pianoCostoAmmesso = $pianoCostoPresentato->getIstruttoria();
                $voce_piano->setVocePianoCostoIstruttoria($pianoCostoAmmesso);

                if (count($options["annualita"]) == 1) {
                    $chiavi = array_keys($options["annualita"]);
                    $voce_piano->setAnnualita($chiavi[0]);
                }

                try {
                    $em->beginTransaction();

                    $em->persist($voce_piano);
                    $em->flush();

                    $esitoGenerali = $this->gestioneGiustificativoSpeseGenerali($pagamento, $proponente);
                    if ($esitoGenerali == false) {
                        throw new \Exception('Errore nel calcolo delle spese generali');
                    }

                    $giustificativo->calcolaImportoRichiesto();
                    $em->flush();

                    $em->commit();

                    return $this->addSuccesRedirect("La voce piano costo è stata correttamente modificata", "dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId()));
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio giustificativo", $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Modifica voce piano costo");

        return $this->render("AttuazioneControlloBundle:VociPianoCosto:modificaVocePianoCosto.html.twig", $dati);
    }

    public function eliminaVocePianoCosto($id_voce_costo_giustificativo) {

        $em = $this->getEm();
        $voce_piano = $em->getRepository("AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo")->find($id_voce_costo_giustificativo);
        $giustificativo = $voce_piano->getGiustificativoPagamento();
        $pagamento = $giustificativo->getPagamento();
        $proponente = $giustificativo->getProponente();


        if ($pagamento->isRichiestaDisabilitata()) {
            return $this->addErrorRedirect("L'operazione non è compatibile con lo stato del pagamento.", "dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId()));
        }

        try {
            $em->beginTransaction();
            $giustificativo->removeVocePianoCosto($voce_piano);
            $em->flush();
            $esitoGenerali = $this->gestioneGiustificativoSpeseGenerali($pagamento, $proponente);
            if ($esitoGenerali == false) {
                throw new \Exception('Errore nel calcolo delle spese generali');
            }

            $giustificativo->calcolaImportoRichiesto();
            $em->flush();
            $em->commit();
        } catch (ResponseException $e) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId()));
        }

        return $this->addSuccesRedirect("La voce piano costo è stata correttamente eliminata", "dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId()));
    }

    /**
     * gdisparti
     * per alcuni bandi si è presentato un mismatch
     * pur avendo definito il PC in una sola annualita vogliono rendicontato su tutte le annualita
     * 
     * cerco prima sulla getAnnualitaRendicontazione che risponde ad eventuali casistiche particolari
     * se torna null chiamo la classica getAnnualita che torna quanto definito in prsentazione
     */
    public function getAnnualitaRendicontazione($richiesta) {
        $annualita = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualitaRendicontazione($richiesta->getMandatario()->getId());
        if (is_null($annualita)) {
            $annualita = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($richiesta->getMandatario()->getId());
        }

        return $annualita;
    }

    public function gestioneGiustificativoSpeseGenerali($pagamento, $proponente = null) {

        $procedura = $pagamento->getProcedura();
        $richiesta = $pagamento->getRichiesta();

        /*
         * Controllo se per la procedura sono previste spese generali, se non ci sono ritorno true
         */
        $config = $this->container->get("gestore_pagamenti")->getGestore($procedura)->getRendicontazioneProceduraConfig($procedura);
        if (!$config->hasSpeseGenerali()) {
            return true;
        }

        // per questo bando abbiamo due voci distinte di spese generali
        // il che equivale a dire che abbiamo appuntamento con Rocco Siffredi e Peter North..e non sono di buon umore nè hanno voglia di chiaccherare
        $vociSpeseGenerali = $this->container->get("gestore_piano_costo")->getGestore($procedura)->getVociSpeseGenerali($pagamento);

        /**
         * Bisogna tenere in considerazione che
         * 1) se siamo in singola proponenza, il proponente del giustificativo è NULL e le voci piano costo sono associate al mandatario
         * 2) se siamo in multiproponenza ma singolo piano costi, il proponente del giustificativo è settato(con uno dei proponenti) ma le voce di piano costo sono associate al mandatario
         * 3) se siamo in multiproponenza multipianocosto, il proponente è settato (con uno dei proponenti) e le voci del piano costo sono associate al proponente del giustificativo
         * 
         * per far tornare in conti dobbiamo assumere come proponente il mandatario per i casi 1 e 3
         * e tenere quello settato nel giustificativo
         */
        if (is_null($proponente)) {//1
            $proponenteVocePianoCosto = $richiesta->getMandatario();
        } else {
            if ($procedura->getMultiPianoCosto()) {//2
                $proponenteVocePianoCosto = $proponente;
            } else {//3
                $proponenteVocePianoCosto = $richiesta->getMandatario();
            }
        }

        foreach ($vociSpeseGenerali as $voce) {

            $codiceSezione = $voce->getSezionePianoCosto()->getCodice();

            // recupero se già esiste l'eventuale giustificativo relativo alle spese generali creato in automatico
            $giustificativoGenerali = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo")->getGiustificativoDaVoce($pagamento, $voce->getCodice(), $codiceSezione, $proponenteVocePianoCosto);

            // recupero la voce piano costo relativa alle spese generali
            $vocePianoGenerali = $this->getEm()->getRepository("RichiesteBundle\Entity\VocePianoCosto")->getVoceDaProponenteCodiceSezioneCodiceRichiesta($richiesta->getId(), $codiceSezione, $voce->getCodice(), $proponenteVocePianoCosto);

            // se non esite (primo giro) creo automaticamente un giustificativo che verrà imputato sulla voce spesa relativa alle spese generali
            if (count($giustificativoGenerali) == 0) {
                $tipologia = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\TipologiaGiustificativo")->findOneByCodice('TIPOLOGIA_SPESE_GENERALI_NASCOSTA');
                $giustificativoGenerali = new \AttuazioneControlloBundle\Entity\GiustificativoPagamento();
                $giustificativoGenerali->setPagamento($pagamento);
                $giustificativoGenerali->setTipologiaGiustificativo($tipologia);
                $giustificativoGenerali->setProponente($proponenteVocePianoCosto);
                $giustificativoGenerali->setDescrizioneGiustificativo("Giustificativo automatico spese generali [{$codiceSezione}]");
            } else {
                $giustificativoGenerali = $giustificativoGenerali[0];
            }

            // se non esiste (primo giro) creo anche la voce piano costo giustificativo, ovvero l'imputazione sulle spese generali
            if (count($giustificativoGenerali->getVociPianoCosto()) == 0) {
                $vocePCGiustificativoVoceGenerali = new \AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo();
                $vocePCGiustificativoVoceGenerali->setVocePianoCosto($vocePianoGenerali);
                $giustificativoGenerali->addVocePianoCosto($vocePCGiustificativoVoceGenerali);
            }

            // calcolo l'importo sul rendicontato (fino a questo momento) applicando la formula definita per lo specifico bando 
            // la necessità di passare il proponente deriva dal fatto che in caso di multipianocosto bisogna creare l'imputazione delle spese generali
            // sullo specifico piano costi associato al proponente specificato..altrimenti non funziona un gazzu
            $importi = $this->container->get("gestore_piano_costo")->getGestore($procedura)->calcolaImportoSpeseGenerali($pagamento, $proponenteVocePianoCosto, $codiceSezione);

            // creo un'imputazione automatica pari all'importo calcolato al passo precedente
            $vocePCGiustificativoVoceGenerali = $giustificativoGenerali->getVociPianoCosto()->first();
            $giustificativoGenerali->setImportoGiustificativo($importi['importo']);
            $giustificativoGenerali->setImportoRichiesto($importi['importo']);
            $vocePCGiustificativoVoceGenerali->setImporto($importi['importo']);
            if ($pagamento->isInItruttoria()) {
                $vocePCGiustificativoVoceGenerali->setImportoApprovato($importi['importo_approvato']);
            }
            try {
                $em = $this->getEm();
                $em->persist($giustificativoGenerali);
                $em->persist($vocePCGiustificativoVoceGenerali);
            } catch (\Exception $e) {
                $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                return false;
            }
        }

        return true;
    }

}
