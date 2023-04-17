<?php

namespace AttuazioneControlloBundle\Service;

use BaseBundle\Exception\SfingeException;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\StatoPagamento;
use DocumentoBundle\Entity\TipologiaDocumento;
use RichiesteBundle\Service\GestoreResponse;
use RichiesteBundle\Ricerche\RicercaPersonaOperatore;
use AttuazioneControlloBundle\Entity\OperatoreCcPagamento;
use MonitoraggioBundle\Entity\RichiestaPianoCosti;
use MonitoraggioBundle\Entity\TC40TipoPercettore;
use AttuazioneControlloBundle\Entity\PagamentiPercettoriGiustificativo;
use AttuazioneControlloBundle\Entity\PagamentoAmmesso;
use AttuazioneControlloBundle\Entity\RichiestaPagamento;
use AttuazioneControlloBundle\Entity\Pagamento;
use DocumentoBundle\Component\ResponseException;
use AttuazioneControlloBundle\Entity\MandatoPagamento;
use SfingeBundle\Entity\IngegneriaFinanziaria;
use RichiesteBundle\Entity\Richiesta;

class GestorePagamentiProcedureParticolariBase extends GestorePagamentiBase {

    public function aggiungiPagamento($id_richiesta) {
        $em = $this->getEm();
        $richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);

        if (!$this->isUtenteAbilitato()) {
            $this->addError("Utente non abilitato all'operazione");
            return $this->redirect($this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $id_richiesta)));
        }

        $pagamento = new \AttuazioneControlloBundle\Entity\Pagamento();
        $pagamento->setAttuazioneControlloRichiesta($richiesta->getAttuazioneControllo());

        $options = array();
        $options["url_indietro"] = $this->generateUrlByTipoProcedura("elenco_pagamenti", $richiesta->getProcedura(), array("id_richiesta" => $id_richiesta));
        $options["modalita_pagamento"] = $this->getModalitaPagamento();

        $form = $this->createForm("AttuazioneControlloBundle\Form\PagamentoProceduraParticolareType", $pagamento, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if (is_null($pagamento->getModalitaPagamento())) {
                return $this->addErrorRedirectByTipoProcedura("Selezionare una modalità di pagamento", "aggiungi_pagamento", $pagamento->getProcedura(), array("id_richiesta" => $id_richiesta));
            }

            if ($pagamento->getModalitaPagamento()->getUnico() && ($richiesta->getAttuazioneControllo()->hasPagamentoUnicoApprovato() || $richiesta->getAttuazioneControllo()->hasPagamentoSaldoApprovato())) {
                $form->get("modalita_pagamento")->addError(new \Symfony\Component\Form\FormError("È già stato approvato un pagamento per la modalità specificata, e non è possibile inserirne ulteriori"));
            }

            if ($richiesta->getAttuazioneControllo()->hasPagamentoSaldoApprovato()) {
                $form->get("modalita_pagamento")->addError(new \Symfony\Component\Form\FormError("È già stato approvato un saldo, e non è possibile inserire ulteriori pagamenti"));
            }

            if ($form->isValid()) {
                $this->calcolaImportoRichiestoIniziale($pagamento);

                try {
                    $em->beginTransaction();

                    //$this->aggiungiFascicoloPagamento($pagamento);
                    $pagamento->setAbilitaRendicontazioneChiusa(false);

                    $em->persist($pagamento);
                    // errore perchè il pagamento non è flushato, forse meglio fare una transazione
                    $em->flush();
                    $this->container->get("sfinge.stati")->avanzaStato($pagamento, "PAG_INSERITO");
                    $em->flush();
                    $em->commit();
                    return $this->addSuccesRedirectByTipoProcedura("Il pagamento è stato correttamente aggiunto", "elenco_pagamenti", $richiesta->getProcedura(), array("id_richiesta" => $id_richiesta));
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();
        $dati["richiesta"] = $richiesta;

        return $this->render("AttuazioneControlloBundle:Pagamenti:aggiungiPagamento.html.twig", $dati);
    }

    public function inviaPagamento($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getRichiesta();
        if ($pagamento->getStato()->uguale(StatoPagamento::PAG_INSERITO)) {
            try {
                //$this->getEm()->beginTransaction();
                $pagamento->setDataInvio(new \DateTime());
                /*
                 * Popolamento tabelle protocollazione
                 */
                if ($this->container->getParameter("stacca_protocollo_al_volo")) {
                    //stacca protocollo
                    $this->container->get("docerinitprotocollazione")->setTabProtocollazionePagamento($pagamento);
                }

                foreach ($pagamento->getGiustificativi() as $giustificativo) {
                    $giustificativo->calcolaImportoAmmesso();
                    $Istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
                    $Istruttoria->setStatoValutazione(\AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento::COMPLETA);
                    $giustificativo->setIstruttoriaOggettoPagamento($Istruttoria);
                    //$this->getEm()->flush();
                }

                //Imposto ad ammissibile eventuali ck presenti per il pagamento
                $checklists = $pagamento->getValutazioniChecklist();
                if (count($checklists) > 0) {
                    foreach ($checklists as $checklist) {
                        $checklist->setAmmissibile(true);
                    }
                }

                $pagamento->setEsitoIstruttoria(true);
                $this->container->get("sfinge.stati")->avanzaStato($pagamento, StatoPagamento::PAG_INVIATO_PA);
                $this->getEm()->persist($pagamento);
                $this->getEm()->flush();
                //$this->getEm()->commit();
            } catch (\Exception $e) {
                //$this->getEm()->rollback();
                $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
            }
            return new GestoreResponse($this->addSuccesRedirectByTipoProcedura("Pagamento inviato correttamente", "elenco_pagamenti", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
        }
        throw new SfingeException("Stato non valido per effettuare la validazione");
    }

    public function gestioneBarraAvanzamento($pagamento) {
        $statoRichiesta = $pagamento->getStato()->getCodice();
        $arrayStati = array('Inserito' => true, 'Completato' => false);

        switch ($statoRichiesta) {
            case 'PAG_INVIATO_PA':
                $arrayStati['Completato'] = true;
        }

        return $arrayStati;
    }

    public function dammiVociMenuElencoPagamenti($id_pagamento) {
        $csrfTokenManager = $this->container->get("security.csrf.token_manager");
        $token = $csrfTokenManager->getToken("token")->getValue();
        $vociMenu = array();

        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        //$id_richiesta = $pagamento->getRichiesta()->getId();

        if (!is_null($pagamento->getStato())) {
            $stato = $pagamento->getStato()->getCodice();
            $esitoValidazione = $this->controllaValiditaPagamento($id_pagamento);

            if ($stato == StatoPagamento::PAG_INSERITO && $esitoValidazione->getEsito() == true) {
                $voceMenu["label"] = "Completa pagamento";
                $voceMenu["path"] = $this->generateUrlByTipoProcedura("invia_pagamento", $pagamento->getProcedura(), array("id_pagamento" => $id_pagamento, "_token" => $token));
                $voceMenu["attr"] = "data-confirm=\"Continuando non sarà più possibile modificare il pagamento nemmeno dall'assistenza tecnica. Si intende procedere comunque?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
                $vociMenu[] = $voceMenu;
            }
        }

        return $vociMenu;
    }

    public function getModalitaPagamento($richiesta = null) {

        $modalita_pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\ModalitaPagamento")->findByCodice(array('SAL', 'UNICA_SOLUZIONE', 'ANTICIPO', 'SALDO_FINALE'));
        return $modalita_pagamento;
    }

    public function datiGeneraliPagamento($id_pagamento, $formType = NULL) {

        $options = array();
        $em = $this->getEm();
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $options["tipologia"] = $pagamento->getModalitaPagamento()->getCodice();
        $options["disabled"] = $pagamento->isRichiestaDisabilitata() || !$this->isUtenteAbilitato();
        $options["url_indietro"] = $this->generateUrlByTipoProcedura("elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId()));

        $form = $this->createForm("AttuazioneControlloBundle\Form\DatiGeneraliPagamentoPPType", $pagamento, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $em->flush();
                    return $this->addSuccesRedirectByTipoProcedura("Dati correttamente salvati", "elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrlByTipoProcedura("elenco_richieste", $pagamento->getProcedura()));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrlByTipoProcedura("elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dati generali pagamento");

        $options["form"] = $form->createView();
        $options["pagamento"] = $pagamento;
        $options["richiesta"] = $richiesta;
        return $this->render("AttuazioneControlloBundle:Pagamenti:datiGeneraliPPT.html.twig", $options);
    }

    public function elencoPagamenti($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);

        $dati = array("richiesta" => $richiesta);
        return $this->render("AttuazioneControlloBundle:Pagamenti:elencoPagamentiPP.html.twig", $dati);
    }

    public function dettaglioPagamento($id_pagamento, $twig = null) {
        $this->getSession()->set("id_pagamento", $id_pagamento);
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $dati = array("pagamento" => $pagamento);
        $dati["avanzamenti"] = $this->gestioneBarraAvanzamento($pagamento);
        $dati["richiesta"] = $richiesta;

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrlByTipoProcedura("elenco_richieste", $pagamento->getProcedura()));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrlByTipoProcedura("elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento");

        return $this->render("AttuazioneControlloBundle:Pagamenti:dettaglioPagamento.html.twig", $dati);
    }

    public function validaGiustificativiPP($pagamento) {
        $esito = new EsitoValidazione(true);
        $giustificativi = $pagamento->getGiustificativi();

        //Verifico la presenza di giustificativi
        if (count($giustificativi) == 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Non sono presenti giustificativi per il pagamento: " . $pagamento->getId());
            return $esito;
        }

        $importoTotale = 0.00;

        foreach ($giustificativi as $giustificativo) {
            $importoTotale += $giustificativo->getImportoGiustificativo();
        }

        foreach ($giustificativi as $giustificativo) {
            $esitoGiustificativi = $this->container->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->validaGiustificativo($giustificativo);
            if ($esitoGiustificativi->getEsito() == false) {
                $esito->setEsito(false);
                $errori = $esitoGiustificativi->getMessaggiSezione();
                foreach ($errori as $errore) {
                    $esito->addMessaggioSezione($errore);
                }
            }
        }
        return $esito;
    }

    public function validaDatiGenerali($pagamento) {

        $esito = new EsitoValidazione(true);
        $esito->setSezione("Dati generali");

        $importo = $pagamento->getImportoRichiesto();

        if ((is_null($importo) || $importo == 0.00) && $pagamento->getModalitaPagamento()->getCodice() != 'ANTICIPO') {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Il campo Importo non è compilato per il pagamento: " . $pagamento->getId());
        }

        return $esito;
    }

    public function validaMandato($pagamento) {

        $esito = new EsitoValidazione(true);
        $esito->setSezione("Mandato");

        $mandato = $pagamento->getMandatoPagamento();

        if (is_null($mandato)) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Nessun mandato definito per il pagamento: " . $pagamento->getId());
        }

        return $esito;
    }

    public function controllaValiditaPagamento($id_pagamento, $opzioni = array()) {

        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $esito = true;

        $esitiSezioni = array();
        $esitiSezioni[] = $this->validaDatiGenerali($pagamento);
        $esitiSezioni[] = $this->validaMandato($pagamento);
        if ($pagamento->getModalitaPagamento()->getRichiedeGiustificativi()) {
            $esitiSezioni[] = $this->validaGiustificativiPP($pagamento);
        }

        $esitiSezioni[] = $this->validaImpegni($pagamento);
        $esitiSezioni[] = $this->validaMonitoraggioIndicatori($pagamento);
        $esitiSezioni[] = $this->validaMonitoraggioFasiProcedurali($pagamento);
        $esitiSezioni[] = $this->validaProceduraAggiudicazione($pagamento);

        $messaggi = array();
        $messaggiSezione = array();
        foreach ($esitiSezioni as $esitoSezione) {
            $esito &= $esitoSezione->getEsito();
            $messaggi = array_merge_recursive($messaggi, $esitoSezione->getMessaggi());
            $messaggiSezione = array_merge_recursive($messaggiSezione, $esitoSezione->getMessaggiSezione());
        }

        return new EsitoValidazione($esito, $messaggi, $messaggiSezione);
    }

    public function validaPagamenti($richiesta) {
        $esito = true;
        $esitoClass = new EsitoValidazione(true);
        if (count($richiesta->getAttuazioneControllo()->getPagamenti()) == 0) {
            $esitoClass->setEsito(false);
            $esitoClass->setSezione("Pagamenti");
            $esitoClass->addMessaggioSezione("nessun pagamento inserito");
            return $esitoClass;
        }

        $esitiSezioni = array();
        $messaggi = array();
        $messaggiSezione = array();
        foreach ($richiesta->getAttuazioneControllo()->getPagamenti() as $pagamento) {
            $esitiSezioni[] = $this->controllaValiditaPagamento($pagamento->getId());
        }

        foreach ($esitiSezioni as $esitoSezione) {
            $esito &= $esitoSezione->getEsito();
            $messaggiTemp = array_merge_recursive($messaggi, $esitoSezione->getMessaggi());
            $messaggiSezioneTemp = array_merge_recursive($messaggiSezione, $esitoSezione->getMessaggiSezione());
        }

        $messaggi = $this->popolaMessErrore($messaggiTemp, $messaggi);
        $messaggiSezione = $this->popolaMessErrore($messaggiSezioneTemp, $messaggiSezione);

        $esitoClass->setEsito($esito);
        $esitoClass->setMessaggiSezione($messaggiSezione);
        $esitoClass->setMessaggio($messaggi);

        return $esitoClass;
    }

    public function eliminaPagamento($id_pagamento) {
        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $id_richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta()->getId();

        if (in_array($pagamento->getStato(), array(StatoPagamento::PAG_INVIATO_PA, StatoPagamento::PAG_PROTOCOLLATO))) {
            return $this->addErrorRedirect("L'operazione non è compatibile con lo stato del pagamento.", "elenco_pagamenti", array("id_richiesta" => $id_richiesta));
        }

        try {
            $em->beginTransaction();
            $pagamento->setIntegrazioneDi(null);

            foreach ($pagamento->getGiustificativi() as $giustificativo) {
                $giustificativo->setIntegrazioneDi(null);
            }

            foreach ($pagamento->getDocumentiPagamento() as $documento_pagamento) {
                $documento_pagamento->setIntegrazioneDi(null);
            }

            $em->flush();
            $em->remove($pagamento);
            $em->flush();
            $em->commit();
            return $this->addSuccesRedirectByTipoProcedura("Il pagamento è stato correttamente eliminato", "elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $id_richiesta));
        } catch (ResponseException $e) {
            $em->rollback();
            $msg = "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.";
            return $this->addErrorRedirect($msg, "elenco_pagamenti", array("id_richiesta" => $id_richiesta));
        }
    }

    /**
     * @param Pagamento $pagamento
     */
    public function mandato($pagamento) {
        $richiesta = $pagamento->getRichiesta();
        $procedura = $richiesta->getProcedura();
        $options = array();
        $options["url_indietro"] = $this->generateUrlByTipoProcedura('elenco_pagamenti', $procedura, array("id_richiesta" => $richiesta->getId()));
        $options["disabled"] = !$this->isGranted($this->getRuolo()) || $pagamento->isPagamentoDisabilitato();

        if (is_null($pagamento->getMandatoPagamento())) {
            $mandato = new \AttuazioneControlloBundle\Entity\MandatoPagamento();
            $pagamento->setMandatoPagamento($mandato);
        } else {
            $mandato = $pagamento->getMandatoPagamento();
        }

        $form = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\MandatoPagamentoType", $mandato, $options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    /*                     * *****************************
                     * ******** MONITORAGGIO ***********
                     * ******************************** */


                    // DESTINAZIONE
                    // Popolamento Automatico FN06 - Pagamenti (solo per BENEFICIARIO PUBBLICO RER)
                    $richiestaPagamento = $this->popolaPagamentoRER($pagamento);

                    // In caso di SALDO / UNICO generare il tracciato per le ECONOMIE
                    if ($pagamento->getModalitaPagamento()->getCodice() == "SALDO_FINALE" || $pagamento->getModalitaPagamento()->getCodice() == "UNICA_SOLUZIONE") {

                        // Implementati nelle rispettive SOTTO CLASSI
                        $economie = $this->popolaEconomieRER($pagamento);

                        foreach ($economie as $economia) {
                            $em->persist($economia);
                        }

                        $this->aggiornaFinanziamento($richiesta);

                        //TODO: Implementare il popolamento automatico dello STATO FINALE dell'ITER di PROGETTO
                        // Valuta una variabile inesistente e richiama una funzione inesistente quindi commento che è meglio
                        /* if (!is_null($natura)) {
                          $richiesta = $pagamento->getRichiesta();
                          $this->popolaStatoFinaleAttuazioneProgettoRER($richiesta, $natura . $mandato);
                          } */
                    }

                    $richiesta = $this->aggiornaPianoCosti($pagamento);
                    $em->persist($richiestaPagamento);
                    $em->persist($richiesta);


                    /*                     * ********************************
                     * ******* FINE MONITORAGGIO *******
                     * ******************************** */


                    $em->persist($mandato);
                    $em->flush();
                    $this->addFlash('success', "Salvataggio effettuato correttamente");

                    return $this->redirect($options["url_indietro"]);
                } catch (\Exception $e) {
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $dati["form"] = $form->createView();
        $dati["menu"] = "mandato";
        $dati["pagamento"] = $pagamento;
        $dati["no_tab"] = true;

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrlByTipoProcedura("elenco_richieste", $pagamento->getProcedura()));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrlByTipoProcedura("elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Mandato pagamento");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:mandato.html.twig", $dati);
    }

    protected function aggiornaFinanziamento(Richiesta $richiesta): void {
        /** @var \MonitoraggioBundle\Service\gestoriFinanziamento\Privato $gestoreFinanziamento */
        $gestoreFinanziamentoService = $this->container->get('monitoraggio.gestore_finanziamento');
        $gestoreFinanziamento = $gestoreFinanziamentoService->getGestore($richiesta);
        $gestoreFinanziamento->aggiornaFinanziamento();
        $gestoreFinanziamento->persistFinanziamenti();
    }

    protected function inserisciValoriMonitoraggio(Pagamento $pagamento): void {
        $richiesta = $pagamento->getRichiesta();
        $procedura = $richiesta->getProcedura();
        $richiestaPagamento = new RichiestaPagamento($pagamento,
                $procedura instanceof IngegneriaFinanziaria ?
                RichiestaPagamento::PAGAMENTO_TRASFERIMENTO :
                RichiestaPagamento::PAGAMENTO
        );
        $richiestaPagamento->setImporto($pagamento->getImportoRichiesto());
        $richiesta->addMonRichiestePagamento($richiestaPagamento);

        $this->getEm()->persist($richiestaPagamento);
    }

    public function validaChecklist($valutazione_checklist) {

        $esito = new EsitoValidazione(true);

        foreach ($valutazione_checklist->getValutazioniElementi() as $valutazione) {
            if (is_null($valutazione->getValore())) {
                $esito->setEsito(false);
                $esito->addMessaggio("La checklist non è completa");
            }
        }



        return $esito;
    }

    public function inizializzaIstruttoriaPagamento($pagamento, $procedura = null) {

        if ($procedura->isAssistenzaTecnica()) {
            $tipo = $procedura->getTipoAssistenzaTecnica();
        } elseif ($procedura->isIngegneriaFinanziaria()) {
            $tipo = $procedura->getTipoIngegneriaFinanziaria();
        } elseif ($procedura->isAcquisizioni()) {
            $checklists = $pagamento->getProcedura()->getChecklistPagamento();
        }
        if (!$procedura->isAcquisizioni() && !is_null($tipo) && $tipo->getChecklistPagamento()) {
            $checklists = $tipo->getChecklistPagamento();
        }

        if (!$procedura->isAcquisizioni()) {
            if (!is_null($checklists)) {
                $valutazione = new \AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento();
                $valutazione->setValidata(false);
                $valutazione->setChecklist($checklists);
                $valutazione->setPagamento($pagamento);
                $pagamento->addValutazioneChecklist($valutazione);
                foreach ($checklists->getSezioni() as $sezione) {
                    foreach ($sezione->getElementi() as $elemento) {
                        $valutazione_elemento = new \AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamento();
                        $valutazione_elemento->setElemento($elemento);
                        $valutazione->addValutazioneElemento($valutazione_elemento);
                    }
                }
            }
        } else {
            if (count($checklists) > 0) {
                foreach ($checklists as $checklist) {
                    $valutazione = new \AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento();
                    $valutazione->setValidata(false);
                    $valutazione->setChecklist($checklist);
                    $valutazione->setPagamento($pagamento);
                    $pagamento->addValutazioneChecklist($valutazione);
                    foreach ($checklist->getSezioni() as $sezione) {
                        foreach ($sezione->getElementi() as $elemento) {
                            $valutazione_elemento = new \AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamento();
                            $valutazione_elemento->setElemento($elemento);
                            $valutazione->addValutazioneElemento($valutazione_elemento);
                        }
                    }
                }
            }
        }
    }

    public function valutaChecklist($pagamento, $extra = array()) {

        $richiesta = $pagamento->getRichiesta();
        $procedura = $richiesta->getProcedura();

        if (
                count($pagamento->getValutazioniChecklist()) == 0 &&
                ( $procedura->isIngegneriaFinanziaria() || $procedura->isAcquisizioni() ||
                ($procedura->isAssistenzaTecnica() && $procedura->getTipoAssistenzaTecnica()->getCodice() != '4') )
        ) {
            $this->inizializzaIstruttoriaPagamento($pagamento, $procedura);
        }

        $valutazioni_checklist = $pagamento->getValutazioniChecklist();
        $valutazione_checklist = $valutazioni_checklist[0];
        if (is_null($valutazione_checklist)) {
            $redirect_url = $this->generateUrlByTipoProcedura('elenco_pagamenti', $procedura, array("id_richiesta" => $richiesta->getId()));
            $this->addError('Attenzione, nessuna checklist definita per questa procedura');
            return $this->redirect($redirect_url);
        }
        $checklist = $valutazione_checklist->getChecklist();
        $options = array();
        $statoPagamento = $pagamento->getStato()->getCodice();
        $options["url_indietro"] = $this->generateUrlByTipoProcedura('elenco_pagamenti', $procedura, array("id_richiesta" => $richiesta->getId()));
        $options["disabled"] = !$this->isGranted($this->getRuolo()) || $valutazione_checklist->getValidata();
        $options["invalida"] = $this->isGranted($this->getRuolo()) && $valutazione_checklist->getValidata() && $statoPagamento != 'PAG_INVIATO_PA';

        $form = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\ValutazioneChecklistPagamentoType", $valutazione_checklist, $options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);


            if ($form->get("pulsanti")->get("pulsante_valida")->isClicked()) {

                $validazione = $this->validaChecklist($valutazione_checklist);

                if (!$validazione->getEsito()) {
                    $form->addError(new \Symfony\Component\Form\FormError("Dati non completi"));
                }
            }

            if ($form->isValid()) {
                $request_data = $request->request->get($form->getName());

                foreach ($form->get("valutazioni_elementi")->getIterator() as $child) {
                    $valutazione_elemento = $child->getData();
                    $elemento = $valutazione_elemento->getElemento();

                    switch ($elemento->getTipo()) {
                        case "choice":
                            $choices = $elemento->getChoices();
                            $valutazione_elemento->setValoreRaw(is_null($valutazione_elemento->getValore()) ? null : $choices[$valutazione_elemento->getValore()]);
                            break;
                        case "date":
                            $valore = $valutazione_elemento->getValore();
                            if (!is_null($valore)) {
                                $valutazione_elemento->setValore($valore->format('Y-m-d'));
                                $valutazione_elemento->setValoreRaw($valore->format('d/m/Y'));
                            }
                            break;
                        case "datetime":
                            $valore = $valutazione_elemento->getValore();
                            if (!is_null($valore)) {
                                $valutazione_elemento->setValoreRaw($valore->format('d/m/Y H:i'));
                                $valutazione_elemento->setValore($valore->format('Y-m-d H:i'));
                            }
                            break;
                        default:
                            $valutazione_elemento->setValoreRaw($valutazione_elemento->getValore());
                    }
                }


                // VALIDAZIONE CHECKLIST
                if ($form->get("pulsanti")->get("pulsante_valida")->isClicked()) {
                    $valutazione_checklist->setValidata(true);
                    $valutazione_checklist->setValutatore($this->getUser());
                    $valutazione_checklist->setDataValidazione(new \DateTime());
                    // $valutazione_checklist->setAmmissibile($this->isAmmissibile($valutazione_checklist));
                    // $this->operazioniValidazione($valutazione_checklist);

                    $messaggio = "Valutazione validata";
                    $redirect_url = $this->generateUrlByTipoProcedura('valuta_checklist_istruttoria_pagamenti', $procedura, array('id_pagamento' => $pagamento->getId()));
                } else {
                    if (isset($request_data["pulsanti"]["pulsante_invalida"])) {
                        $valutazione_checklist->setValidata(false);
                        $valutazione_checklist->setValutatore(null);
                        $valutazione_checklist->setDataValidazione(null);
                        // $valutazione_checklist->setAmmissibile(null);

                        $messaggio = "Valutazione invalidata";
                        $redirect_url = $this->generateUrlByTipoProcedura('valuta_checklist_istruttoria_pagamenti', $procedura, array('id_pagamento' => $pagamento->getId()));
                    } else {
                        $messaggio = "Modifiche salvate correttamente";
                        $redirect_url = $this->generateUrlByTipoProcedura('elenco_pagamenti', $procedura, array("id_richiesta" => $richiesta->getId()));
                    }
                }

                $em = $this->getEm();
                try {
                    $em->flush();
                    $this->addFlash('success', $messaggio);

                    return $this->redirect($redirect_url);
                } catch (\Exception $e) {
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $dati["form"] = $form->createView();
        $dati["pagamento"] = $pagamento;
        $dati["valutazione_checklist"] = $valutazione_checklist;
        $dati["no_tab"] = true;

        if (isset($extra["twig_data"])) {
            $dati = array_merge($dati, $extra["twig_data"]);
        }

        //aggiungo il titolo della pagina e le info della breadcrumb
        $this->container->get("pagina")->setTitolo($checklist->getNome());

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrlByTipoProcedura("elenco_richieste", $pagamento->getProcedura()));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrlByTipoProcedura("elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb($checklist->getNome());


        $twig = isset($extra["twig"]) ? $extra["twig"] : "AttuazioneControlloBundle:Istruttoria/Pagamenti:checklistPagamento.html.twig";

        return $this->render($twig, $dati);
    }

    public function generateUrlByTipoProcedura($route, $procedura, $params = array()) {
        if ($procedura->isAssistenzaTecnica() == true) {
            $route = $route . "_at";
        }

        if ($procedura->isIngegneriaFinanziaria() == true) {
            $route = $route . "_ing_fin";
        }

        if ($procedura->isAcquisizioni() == true) {
            $route = $route . "_acquisizioni";
        }

        return $this->generateUrl($route, $params);
    }

    public function addSuccesRedirectByTipoProcedura($msg, $route, $procedura, $params = array()) {
        if ($procedura->isAssistenzaTecnica() == true) {
            $route = $route . "_at";
        }

        if ($procedura->isIngegneriaFinanziaria() == true) {
            $route = $route . "_ing_fin";
        }

        if ($procedura->isAcquisizioni() == true) {
            $route = $route . "_acquisizioni";
        }
        return $this->addSuccesRedirect($msg, $route, $params);
    }

    public function addErrorRedirectByTipoProcedura($msg, $route, $procedura, $params = array()) {
        if ($procedura->isAssistenzaTecnica() == true) {
            $route = $route . "_at";
        }

        if ($procedura->isIngegneriaFinanziaria() == true) {
            $route = $route . "_ing_fin";
        }
        if ($procedura->isAcquisizioni() == true) {
            $route = $route . "_acquisizioni";
        }
        return $this->addErrorRedirect($msg, $route, $params);
    }

    public function completaPagamento($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getRichiesta();

        if (!$pagamento->getStato()->uguale(StatoPagamento::PAG_INSERITO)) {
            $this->addFlash("error", "Stato non valido per effettuare l'operazione");
            return $this->redirectToRoute("elenco_pagamenti_at", array("id_richiesta" => $richiesta->getId()));
        }

        $esitoValidazione = $this->controllaValiditaPagamento($id_pagamento);
        if (!$esitoValidazione->getEsito()) {
            $error_msg = "Non è possibile completare il pagamento per i seguenti motivi: <br><br> ";
            foreach ($esitoValidazione->getMessaggiSezione() as $msg) {
                $error_msg .= implode($msg, '<br>');
            }
            $this->addFlash("error", $error_msg);
            return $this->redirectToRoute("elenco_pagamenti_at", array("id_richiesta" => $richiesta->getId()));
        }

        try {
            $this->getEm()->beginTransaction();
            $pagamento->setDataInvio(new \DateTime());
            /*
             * Popolamento tabelle protocollazione
             */
            if ($this->container->getParameter("stacca_protocollo_al_volo")) {
                //stacca protocollo
                $this->container->get("docerinitprotocollazione")->setTabProtocollazionePagamento($pagamento);
            }

            foreach ($pagamento->getGiustificativi() as $giustificativo) {
                $giustificativo->calcolaImportoAmmesso();
                $this->getEm()->flush();
            }

            $this->container->get('monitoraggio.gestore_finanziamento')
                    ->getGestore($richiesta)
                    ->aggiornaFinanziamento();
            if ($pagamento->isUltimoPagamento()) {
                /** @var \MonitoraggioBundle\Service\IGestoreImpegni $impegniService */
                $impegniService = $this->container->get('monitoraggio.impegni')->getGestore($richiesta);
                $impegniService->aggiornaImpegniASaldo();
            }


            $pagamento->setEsitoIstruttoria(true);
            $this->container->get("sfinge.stati")->avanzaStato($pagamento, StatoPagamento::PAG_INVIATO_PA);

            $this->inserisciValoriMonitoraggio($pagamento);


            $this->getEm()->persist($pagamento);
            $this->getEm()->flush();
            $this->getEm()->commit();
            $this->addFlash("success", "Pagamento correttamente completato");
        } catch (\Exception $e) {
            $this->getEm()->rollback();
            $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
        }

        return $this->redirectToRoute("elenco_pagamenti_at", array("id_richiesta" => $richiesta->getId()));
    }

    /**
     * @param \AttuazioneControlloBundle\Entity\Pagamento $pagamento
     *
     * Metodo richiamato contestualmente al salvataggio del MANDATO DI PAGAMENTO (per i soli BENEFICIARI PRIVATI)
     * serve per popolare automaticamente le info sul pagamento utili ai fini del monitoraggio
     */
    protected function popolaPagamentoRER(Pagamento $pagamento) {


        /*


          "La particolarità degli strumenti finanziari è che per  Impegni e Pagamenti bisogna sempre tracciare un doppio flusso:

          a) gli impegni e pagamenti della Regione verso il Gestore del Fondo
          (non è beneficiario, ma attuatore, poiché il Beneficiario resta la Regione),
          segnalati con Tipologia Impegno e Tipologia Pagamento rispettivamente I-TR e P-TR;

          b) gli impegni e i pagamenti del soggetto gestore verso terzi, oggetto delle rendicontazioni,
          segnalati con tipologia impegno I e pagamento P.

          Si ricorda, inoltre, che per i soli Pagamenti, emessi dal soggetto gestore verso i singoli beneficiari (imprese finanziate)
          vanno raccolti i dati nella struttura FN08 Percettori"
         */

        /* TODO: Inserire caso A) ??? Gestire PERCETTORI caso B) ???  Solo per ING. FINANZIARIA ??? */


        $em = $this->getEm();

        // DESTINAZIONE
        $richiestaPagamentoMon = new RichiestaPagamento($pagamento);

        // TIPOLOGIA PAGAMENTO
        // VALORI AMMESSI: "P":"Pagamento", "R":"Rettifica", "P-TR":"Pagamento per trasferimento" (SF),"R-TR":"Rettifica per trasferimento" (SF)
        // DEFAULT imposto la TIPOLOGIA a "P"
        $richiestaPagamentoMon->setTipologiaPagamento("P");


        // SOGGETTO PUBBLICO RER	--> importo pagato (MANDATO PAGAMENTO)
        $richiestaPagamentoMon->setImporto($pagamento->getMandatoPagamento()->getImportoPagato());
        $richiestaPagamentoMon->setDataPagamento($pagamento->getMandatoPagamento()->getDataMandato());
        $richiestaPagamentoMon->setNote($pagamento->getNotaIntegrazione());

        // ---------------- // // ----------------// ----------------// ----------------
        // se per la fn01 esiste un solo livello gerarchico, inserisci anche la pagamenti_ammessi e richieste_livelli_gerarchici
        // Se il cod_programma/liv_gerarchico è uno (…della RICHIESTA ???…), il record viene creato automaticamente con gli stessi dati del pagamento

        $richiestaProgrammi = $pagamento->getRichiesta()->getMonProgrammi();

        // 1 RICHIESTA --> Inserisco PAGAMENTI AMMESSI
        if (count($richiestaProgrammi) == 1) {
            $livelliGerarchici = $richiestaProgrammi[0]->getMonLivelliGerarchici();

            // 1 LIVELLO GERARCHICO
            if (count($livelliGerarchici) == 1) {

                // PAGAMENTI AMMESSI
                $pagamentoAmmessoMon = new PagamentoAmmesso($richiestaPagamentoMon, $livelliGerarchici[0]);

                // AttuazioneControlloBundle\Entity\RichiestaPagamento
                $pagamentoAmmessoMon->setRichiestaPagamento($richiestaPagamentoMon);

                // AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico
                $pagamentoAmmessoMon->setLivelloGerarchico($livelliGerarchici[0]);  // VEDIAMO...Come da FN01....
                // DATA PAGAMENTO
                $pagamentoAmmessoMon->setDataPagamento($richiestaPagamentoMon->getDataPagamento());  // Come da FN06 (OK)
                // CAUSALE PAGAMENTO
                $pagamentoAmmessoMon->setCausale($richiestaPagamentoMon->getCausalePagamento()); // Come da FN06 (OK)
                // IMPORTO
                $pagamentoAmmessoMon->setImporto($richiestaPagamentoMon->getImporto());  // Come da FN06 (OK)

                $richiestaPagamentoMon->addPagamentiAmmessi($pagamentoAmmessoMon);
            }
        }

        // PERCETTORI (solo per Opere e Lavori Pubblici)
        $natura = $pagamento->getRichiesta()->getIstruttoria()->getCupNatura();

        if (!is_null($natura->getCodice()) && $natura->getCodice() == "03") {  // Opere e Lavori Pubblici
            $giustificativi = $pagamento->getGiustificativi();

            foreach ($giustificativi as $giustificativo) {

                // DESTINAZIONE
                $pagamentoPercettoreGiustificativo = new PagamentiPercettoriGiustificativo();

                // GIUSTIFICATIVO --> ID del giustificativo
                $pagamentoPercettoreGiustificativo->setGiustificativoPagamento($giustificativo);

                // RICHIESTA PAGAMENTO
                $pagamentoPercettoreGiustificativo->setPagamento($richiestaPagamentoMon);

                // TIPO PERCETTORE -> TC40TipoPercettore; Manca l'ASSOCIAZIONE, metto 'Capofila di un RTI' come APPOGGIO
                $tipoPercettore = new TC40TipoPercettore();
                $tipoPercettore = $em->getRepository("MonitoraggioBundle\Entity\TC40TipoPercettore")->findBy(array("descrizione_tipo_percettore" => "Capofila di un RTI"));
                $pagamentoPercettoreGiustificativo->setTipoPercettore($tipoPercettore[0]);

                // IMPORTO
                $pagamentoPercettoreGiustificativo->setImporto($giustificativo->getImportoAmmesso());

                // Associo il PERCETTORE
                $richiestaPagamentoMon->addPercettori($pagamentoPercettoreGiustificativo);
            }
        }

        return $richiestaPagamentoMon;
    }

    /**
     * @param \AttuazioneControlloBundle\Entity\Pagamento $pagamento
     *
     * Metodo richiamato contestualmente al salvataggio del mandato di pagamento
     * serve per aggiornare il PDC con gli importi realizzati per l'anno di riferimento (DATA INVIO PAGAMENTO)
     */
    protected function aggiornaPianoCosti($pagamento) {

        $em = $this->getEm();

        $richiesta = $pagamento->getRichiesta();
        $idRichiesta = $richiesta->getId();
        $totalePagamentiPrecedenti = 0.00;
        $pagamentoAttualeAggiunto = false;
        $totaleImporto = 0.00;
        $annoAttualePresente = false;
        $costoAmmesso = $richiesta->getIstruttoria()->getCostoAmmesso();
        $mandato = $pagamento->getMandatoPagamento();
        $dataInvio = $pagamento->getDataInvio() ?: $mandato->getDataMandato();
        $annoInvio = $dataInvio->format('Y');

        // Recupero i PAGAMENTI coperti da MANDATO (il PAGAMENTO ATTUALE non ha MANDATO)
        $listaPagamentiPDC = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->getPagamentiPDC($idRichiesta);

        // Aggiorno l'IMPORTO dell'ANNO del PAGAMENTO;
        // Recupero la SOMMA degli IMPORTI da sottrarre al COSTO AMMESSO
        // Il pagamento attuale è SENZA MANDATO quindi non compare....
        foreach ($listaPagamentiPDC as $pagamentoPCD) {

            // DESTINAZIONE
            $pianoCosti = new RichiestaPianoCosti();

            // ID RICHIESTA
            $pianoCosti->setRichiesta($richiesta);

            $totalePagamentiPrecedenti += $pagamentoPCD["importo"];

            // ANNO del PAGAMENTO, aggiungo l'importo dell'ANNO ATTUALE
            // Piccola modifica perchè la data invio potrebbe non esserci
            if ($pagamentoPCD["anno"] == $annoInvio) {
                $pagamentoPCD["importo"] += $pagamento->getImportoRichiesto();
                //break;

                $pagamentoAttualeAggiunto = true;
            }

            $pianoCosti->setAnnoPiano($pagamentoPCD["anno"]);
            $pianoCosti->setImportoRealizzato($pagamentoPCD["importo"]);

            // Se ANNO ATTUALE (quindi l'ULTIMO) calcolo il DA REALIZZARE, altrimenti è 0 per gli ANNI PRECEDENTI
            if ($pagamentoPCD["anno"] == date("Y")) {

                $annoAttualePresente = true;

                $totaleImporto = $totalePagamentiPrecedenti + $pagamento->getImportoRichiesto();
                $pianoCosti->setImportoDaRealizzare($costoAmmesso - $totaleImporto);
            } else
                $pianoCosti->setImportoDaRealizzare(0.00);

            // associo il PIANO COSTI alla richiesta
            $richiesta->addMonPianoCosti($pianoCosti);
        }

        // Se l'ANNO ATTUALE non è presente in nessun pagamento, aggiungo il RECORD
        if (!$annoAttualePresente) {

            // DESTINAZIONE
            $pianoCosti = new RichiestaPianoCosti();

            // ID RICHIESTA
            $pianoCosti->setRichiesta($richiesta);

            $pianoCosti->setAnnoPiano(date("Y"));

            if (!$pagamentoAttualeAggiunto) {
                $pianoCosti->setImportoRealizzato($pagamento->getImportoRichiesto());
            } else {
                $pianoCosti->setImportoRealizzato(0.00);
            }

            $totaleImporto = $totalePagamentiPrecedenti + $pagamento->getImportoRichiesto();

            $pianoCosti->setImportoDaRealizzare($costoAmmesso - $totaleImporto);

            // associo il PIANO COSTI alla richiesta
            $richiesta->addMonPianoCosti($pianoCosti);
        }

        return $richiesta;
    }

    public function elencoDocumentiCaricati($id_pagamento, $opzioni = array()) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documento = new \AttuazioneControlloBundle\Entity\DocumentoPagamento();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();

        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        $documenti_caricati = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoPagamento")->findDocumentiCaricati($id_pagamento, array('PAGAMENTI_PP'));

        $listaTipi = $this->getTipiDocumentiCaricabili($pagamento, 0);

        if (count($listaTipi) > 0) {

            $opzioni_form["lista_tipi"] = $listaTipi;
            $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
            $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Salva'));

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {

                        $this->container->get("documenti")->carica($documento_file, 0, null);

                        $documento->setDocumentoFile($documento_file);
                        $documento->setPagamento($pagamento);
                        $em->persist($documento);

                        $em->flush();
                        return new GestoreResponse($this->addSuccesRedirectByTipoProcedura("Documento caricato correttamente", "elenco_documenti_caricati_pag", $pagamento->getProcedura(), array("id_pagamento" => $pagamento->getId(), "id_richiesta" => $pagamento->getRichiesta()->getId())));
                    } catch (ResponseException $e) {
                        $this->addFlash('error', $e->getMessage());
                    }
                }
            }
            $form_view = $form->createView();
        } else {
            $form_view = null;
        }


        $dati = array("documenti" => $documenti_caricati, "form" => $form_view, "pagamento" => $pagamento);
        $response = $this->render("AttuazioneControlloBundle:Pagamenti:elencoDocumentiCaricatiPP.html.twig", $dati);
        return new GestoreResponse($response, "AttuazioneControlloBundle:Pagamenti:elencoDocumentiCaricatiPP.html.twig", $dati);
    }

    public function eliminaDocumentoPagamento($id_documento_pagamento) {
        $em = $this->getEm();
        $documento_pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoPagamento")->find($id_documento_pagamento);
        $pagamento = $documento_pagamento->getPagamento();

        $redirect_route = "elenco_documenti_caricati_pag";

        if ($pagamento->isRichiestaDisabilitata() || !$this->isUtenteAbilitato()) {
            return $this->addErrorRedirectByTipoProcedura("L'operazione non è compatibile con lo stato del pagamento.", $redirect_route, $pagamento->getProcedura(), array("id_pagamento" => $pagamento->getId(), "id_richiesta" => $pagamento->getRichiesta()->getId()));
        }

        try {
            $em->remove($documento_pagamento);
            $documento_pagamento->setIntegrazioneDi(null);
            $em->flush();
            return $this->addSuccesRedirectByTipoProcedura("Il documento è stato correttamente eliminato", $redirect_route, $pagamento->getProcedura(), array("id_pagamento" => $pagamento->getId(), "id_richiesta" => $pagamento->getRichiesta()->getId()));
        } catch (ResponseException $e) {
            return $this->addErrorRedirectByTipoProcedura("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", $redirect_route, $pagamento->getProcedura(), array("id_pagamento" => $pagamento->getId(), "id_richiesta" => $pagamento->getRichiesta()->getId()));
        }
    }

    public function getTipiDocumentiCaricabili($pagamento, $solo_obbligatori = false) {
        $res = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByCodice('PAGAMENTI_PP');
        return $res;
    }

    public function isUtenteAbilitato() {

        if ($this->getUser()->isAbilitatoStrumentiFinanziariScrittura()) {
            return true;
        }
        return false;
    }

    protected function popolaMessErrore($in, $out) {
        foreach ($in as $mess) {
            if (count($mess) > 0) {
                foreach ($mess as $m) {
                    $out[] = $m;
                }
            }
        }
        return $out;
    }

}
