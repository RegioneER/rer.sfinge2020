<?php

namespace AttuazioneControlloBundle\Service\Istruttoria;

use AttuazioneControlloBundle\Entity\Finanziamento;
use RichiesteBundle\Entity\VoceFaseProcedurale;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\Pagamento;
use CipeBundle\Entity\Classificazioni\CupNatura;
use AttuazioneControlloBundle\Entity\RichiestaPagamento;
use AttuazioneControlloBundle\Entity\MandatoPagamento;
use MonitoraggioBundle\Entity\TC39CausalePagamento;
use MonitoraggioBundle\Entity\RichiestaPianoCosti;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Entity\DocumentoFile;
use AttuazioneControlloBundle\Entity\Economia;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use ProtocollazioneBundle\Entity\RichiestaProtocollo;
use DocumentoBundle\Component\ResponseException;
use AttuazioneControlloBundle\Entity\RichiestaStatoAttuazioneProgetto;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;

class GestoreEsitoPagamentoBase extends \BaseBundle\Service\BaseService {

    /**
     * Schedula l'invio di una email tramite egrammata creando un oggetto EmailProtocollo associato alla richiesta protocollo
     * 
     * N.B. Ogni classe figlia di RichiestaProtocollo per cui viene scedulata un invio email DEVE implementare la EmailSendableInterface
     * @param RichiestaProtocollo $richiestaProtocollo
     * @return boolean
     */
    protected function schedulaEmailProtocollo($richiestaProtocollo) {
        /* @var $egrammataService \ProtocollazioneBundle\Service\EGrammataWsService */
        $egrammataService = $this->container->get('egrammata_ws');
        return $egrammataService->creaEmailProtocollo($richiestaProtocollo);
    }

    public function isEsitoFinalePositivoEmettibile($pagamento) {
        $esito = new EsitoValidazione();
        $esito->setEsito(true);

        $this->controlliGiustificativi($pagamento, $esito);

        return $esito;
    }

    protected function controlliChecklist($pagamento, $esito) {

        // tutte le ValutazionChecklist create devono essere state validate
        $valutazioniChecklist = $pagamento->getValutazioniChecklist();
        foreach ($valutazioniChecklist as $valutazione) {
            if (!$valutazione->getValidata()) {
                $esito->setEsito(false);
                $esito->addMessaggio("Checklist non validata");
                break;
            }
        }

        // deve esistere almeno la valutazione di una checklist ( per costruzione almeno deve esserci la checklist PRINCIPALE)
        if (count($valutazioniChecklist) == 0) {
            $esito->setEsito(false);
            $esito->addMessaggio("Nessuna checklist validata");
        }
    }

    // per inviare l'esito deve essere completa l'istruttoria
    // e devono essere validate tutte le checklist (sia come liquidabili o meno)
    public function verificaEsitoFinaleEmettibile($pagamento) {

        $gestoreIstruttoriaPagamenti = $this->container->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura());
        $options = array('controllo_per_esito' => true);
        $esito = $gestoreIstruttoriaPagamenti->controllaValiditaIstruttoriaPagamento($pagamento, $options);

        $this->controlliChecklist($pagamento, $esito);

        return $esito;
    }

    public function esitoFinale($pagamento) {
        $indietro = $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId()));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Mandato pagamento");

        $em = $this->getEm();

        $request = $this->getCurrentRequest();
        $procedura = $pagamento->getProcedura();

        $atto = $procedura->getAtto();
        $numeroAtto = is_null($atto) ? '--' : $atto->getNumero();

        $azioni = $procedura->getAzioni();
        $azioniArray = array();
        foreach ($azioni as $azione) {
            $azioniArray[] = $azione->getCodice();
        }
        $azioniString = implode(' e ', $azioniArray);

        /* Se l'esito non esiste lo creo */
        $esito_istruttoria_pagamento = $pagamento->getEsitiIstruttoriaPagamento();

        $rup = $procedura->getNomeCognomeRup();

        if (count($esito_istruttoria_pagamento) == 0) {
            $esito_istruttoria_pagamento = new \AttuazioneControlloBundle\Entity\Istruttoria\EsitoIstruttoriaPagamento();

            /*             * * TESTO DELL'EMAIL DI DEFAULT ** */
            if ($pagamento->getModalitaPagamento()->isAnticipo()) {
                $testo_default = "Gentilissimi,\n"
                    . "si comunica l'adozione dell'atto di liquidazione di cui in allegato, "
                    . "a valere sul bando POR-FESR 2014-2020 - Azione {$azioniString} "
                    . "approvato con {$numeroAtto} e successive modifiche e integrazioni."
                    . "\n\nCordiali saluti\n\n"
                    . "Il responsabile del procedimento\n"
                    . $rup;
            } else {
                $testo_default = "Gentilissimi,\n"
                    . "si comunica l'adozione dell'atto di liquidazione di cui in allegato e l'emissione dei relativi mandati di pagamento, "
                    . "a valere sul bando POR-FESR 2014-2020 - Azione {$azioniString} "
                    . "approvato con {$numeroAtto} e successive modifiche e integrazioni.\n"
                    . "Si precisa  che le fatture ammesse a contributo non potranno essere utilizzate per l'ottenimento di altri contributi pubblici."
                    . "\n\nCordiali saluti\n\n"
                    . "Il responsabile del procedimento\n"
                    . $rup;
            }

            $esito_istruttoria_pagamento->setTestoEmail($testo_default);
            /*             * ******************************** */

            $esito_istruttoria_pagamento->setPagamento($pagamento);
            $this->container->get("sfinge.stati")->avanzaStato($esito_istruttoria_pagamento, \BaseBundle\Entity\StatoEsitoIstruttoriaPagamento::ESITO_IP_INSERITA);
            $em->persist($esito_istruttoria_pagamento);
            $em->flush();
        } else {
            // se è stato settato $esito_istruttoria_pagamento è una PersistantCollection... per cui:
            $esito_istruttoria_pagamento = $esito_istruttoria_pagamento[0];
        }

        // se l'esito è già stato inviato, mostro schermata di riepilogo
        if ($esito_istruttoria_pagamento->isInviato()) {
            return $this->riepilogoEsitoInviato($pagamento);
        }



        $documento_esito_istruttoria = new \AttuazioneControlloBundle\Entity\Istruttoria\DocumentoEsitoIstruttoria();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();
        $documento_esito_istruttoria->setDocumentoFile($documento_file);
        $documento_esito_istruttoria->setEsitoIstruttoriaPagamento($esito_istruttoria_pagamento);

        $documenti_caricati = $esito_istruttoria_pagamento->getDocumentiEsitoIstruttoria();

        $documenti_check = false;

        // perchè forse in alcuni casi la determina non è prevista
        if (count($documenti_caricati) > 0) {
            $documenti_check = true;
        }


        $listaTipi = $em->getRepository(TipologiaDocumento::class)->findByTipologia('esito_istruttoria_pagamento');

        // Se lo stato è inviato/protocollato
        $disabilita_azioni = ($esito_istruttoria_pagamento->getStato() != \BaseBundle\Entity\StatoEsitoIstruttoriaPagamento::ESITO_IP_INSERITA);

        if ($disabilita_azioni) {
            $msg = $esito_istruttoria_pagamento->getStato()->getDescrizione();
            if (($esito_istruttoria_pagamento->getProtocolloEsitoIstruttoria() != '-') && !is_null($esito_istruttoria_pagamento->getDataProtocolloEsitoIstruttoria())) {
                $msg .= ' [Protocollo N° ' . $esito_istruttoria_pagamento->getProtocolloEsitoIstruttoria() . ' del ' . $esito_istruttoria_pagamento->getDataProtocolloEsitoIstruttoria() . ']';
            }
            $this->addFlash("success", $msg);
        }

        $verifica = $this->verificaEsitoFinaleEmettibile($pagamento);

        if (count($listaTipi) > 0) {

            $opzioni_form_documenti["lista_tipi"] = $listaTipi;
            $opzioni_form_documenti["url_indietro"] = $indietro;
            $opzioni_form_documenti["disabled"] = $disabilita_azioni || !$this->isRuoloAbilitato();

            $form_documenti = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\DocumentiEsitoIstruttoriaPagamentoType', $documento_esito_istruttoria, $opzioni_form_documenti);

            $opzioni_form_esito["url_indietro"] = $indietro;
            $opzioni_form_esito["disabled"] = $disabilita_azioni || !$this->isRuoloAbilitato();
            $opzioni_form_esito["disabled_invio"] = (!$verifica->getEsito() || !$documenti_check || !$this->isRuoloAbilitato());

            $form_esito = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\EsitoIstruttoriaPagamentoStandardType', $esito_istruttoria_pagamento, $opzioni_form_esito);

            if ($request->isMethod('POST')) {

                $form_documenti->handleRequest($request);
                $form_esito->handleRequest($request);

                if ($form_documenti->isSubmitted() && $form_documenti->isValid()) {
                    try {
                        $this->container->get("documenti")->carica($documento_file, 1);
                        $em->persist($documento_esito_istruttoria);
                        $em->flush();
                        $this->addFlash("success", "Documento caricato con successo.");
                        return $this->redirect($this->generateUrl('esito_finale_istruttoria_pagamenti', array("id_pagamento" => $pagamento->getId())));
                    } catch (ResponseException $e) {
                        $this->addFlash('error', "Errore durante il caricamento del documento. Si invita a riprovare. Se il problema persiste contattare l'assistenza");
                    }
                }

                if ($form_esito->isSubmitted() && $form_esito->isValid()) {

                    try {

                        // SALVATAGGIO INFORMAZIONI
                        $em->persist($esito_istruttoria_pagamento);
                        $em->flush();

                        if ($form_esito->get("pulsanti")->get("pulsante_invio")->isClicked() && $verifica->getEsito()) {

                            if (!$verifica->getEsito()) {
                                foreach ($verifica->getMessaggi() as $messaggio) {
                                    $this->addFlash('error', $messaggio);
                                }
                                return $this->redirect($indietro);
                            }
                            //Scommentare le due righe sotto solo in caso di necessità per pagamenti con molte fatture
                            //ini_set("memory_limit","768M");
                            //set_time_limit(300);
                            // INVIO
                            $em->beginTransaction();
                            $this->pdfEsitoIstruttoriaPagamentoAllegato($pagamento, $esito_istruttoria_pagamento);
                            $em->flush();

                            $this->container->get("sfinge.stati")->avanzaStato($esito_istruttoria_pagamento, \BaseBundle\Entity\StatoEsitoIstruttoriaPagamento::ESITO_IP_INVIATA_PA);
                            $em->flush();

                            if ($this->container->getParameter("stacca_protocollo_al_volo")) {

                                $richiestaProtocollo = $this->container->get("docerinitprotocollazione")->setTabProtocollazioneEsitoIstruttoriaPagamento($pagamento, $esito_istruttoria_pagamento);
                                $em->flush();

                                /**
                                 * schedulo un invio email per protocollazione in uscita tramite egrammata
                                 * l'email verrà mandata solo dopo che lo stato della richiestaProtocollo evolverà in POST_PROTOCOLLAZIONE
                                 * Questo blocco andrà riportato negli eventuali GestoriIstruttoriaBando scritti ad hoc che faranno 
                                 * l'overwrite del metodo creaIntegrazione 
                                 */
                                /*                                 * *********************************************************************** * */
                                if (!$this->schedulaEmailProtocollo($richiestaProtocollo)) {
                                    throw new \Exception('Errore durante la schedulazione dell\'EmailProtocollo');
                                }
                                /*                                 * *********************************************************************** * */
                            }

                            $em->commit();
                            $this->addFlash("success", "Esito istruttoria pagamento inviato con successo.");

                            // IN CASO DI INVIO RICREO I FORM DISABILITANDO

                            $disabilita_azioni = ($esito_istruttoria_pagamento->getStato() != \BaseBundle\Entity\StatoEsitoIstruttoriaPagamento::ESITO_IP_INSERITA);

                            $opzioni_form_documenti["lista_tipi"] = $listaTipi;
                            $opzioni_form_documenti["url_indietro"] = $indietro;
                            $opzioni_form_documenti["disabled"] = $disabilita_azioni || !$this->isRuoloAbilitato();
                            $form_documenti = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\DocumentiEsitoIstruttoriaPagamentoType', $documento_esito_istruttoria, $opzioni_form_documenti);

                            $opzioni_form_esito["url_indietro"] = $indietro;
                            $opzioni_form_esito["disabled"] = $disabilita_azioni || !$this->isRuoloAbilitato();
                            ;
                            $form_esito = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\EsitoIstruttoriaPagamentoStandardType', $esito_istruttoria_pagamento, $opzioni_form_esito);
                        }

                        if ($form_esito->get("pulsanti")->get("pulsante_submit")->isClicked()) {
                            // SALVA
                            $this->addFlash("success", "Esito istruttoria pagamento salvato con successo.");
                        }
                    } catch (\DocumentoBundle\Component\ResponseException $e) {
                        if ($form_esito->get("pulsanti")->get("pulsante_invio")->isClicked()) {
                            $em->rollback();
                        }
                        $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                    }
                }
            }

            $form_documenti_view = $form_documenti->createView();
            $form_esito_view = $form_esito->createView();
        } else {
            $form_documenti_view = null;
            $form_esito_view = null;
        }

        if (!$verifica->getEsito() || !$documenti_check) {
            $this->addFlash('warning', 'ATTENZIONE! Sarà possibile inviare l\'esito previa validazione della Checklist e dopo aver caricato la Determinazione Dirigenziale');
        }

        $dati = array(
            "pagamento" => $pagamento,
            "menu" => 'esito',
            "documenti" => $documenti_caricati,
            "form_documenti" => $form_documenti_view,
            "form_esito" => $form_esito_view,
            "route_cancellazione_documento" => 'esito_finale_elimina_doc',
            "url_indietro" => $indietro,
            "disabilita_azioni" => $disabilita_azioni,
            "documenti_richiesti" => $listaTipi,
        );

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Esito finale");

        return $this->render("AttuazioneControlloBundle:Istruttoria\Pagamenti:esitoIstruttoriaPagamento.html.twig", $dati);
    }

    public function eliminaDocumento($id_documento_esito_istruttoria, $pagamento, $opzioni = array()) {
        $em = $this->getEm();
        $documento = $em->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\DocumentoEsitoIstruttoria")->find($id_documento_esito_istruttoria);

        try {
            $this->container->get("documenti")->cancella($documento->getDocumentoFile(), 0);
            $em->remove($documento);
            $em->flush();
            $this->addFlash("success", "Documento eliminato correttamente");
        } catch (ResponseException $e) {
            $this->addFlash('error', "Errore nell'eliminazione del documento");
        }

        return $this->redirect($this->generateUrl('esito_finale_istruttoria_pagamenti', array("id_pagamento" => $pagamento->getId())));
    }

    /**
     * @param Pagamento $pagamento
     */
    public function mandato($pagamento) {

        $indietro = $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId()));

        $gestoreIstruttoriaPagamenti = $this->container->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura());

        $esitoChecklist = $gestoreIstruttoriaPagamenti->getAmmissibilitaChecklist($pagamento);
        if (!$esitoChecklist) {
            $this->addWarning("Non è possibile salvare il mandato se non è stata validata la checklist come liquidabile");
        }

        $options = array();
        $options["url_indietro"] = $indietro;

        if ($this->isGranted("ROLE_SUPER_ADMIN")) {
            $options["disabled"] = false;
        } else {
            $options["disabled"] = !$this->isGranted("ROLE_ISTRUTTORE_SUPERVISORE_ATC") || !is_null($pagamento->getMandatoPagamento()) || !$esitoChecklist;
        }

        if (\is_null($pagamento->getMandatoPagamento())) {
            $mandato = new MandatoPagamento();
            $pagamento->setMandatoPagamento($mandato);
        } else {
            $mandato = $pagamento->getMandatoPagamento();
        }

        $form = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\MandatoPagamentoType", $mandato, $options);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $esitoChecklist) {
            $em = $this->getEm();
            try {
                $isPrivato = !$pagamento->getRichiesta()->getMonPrgPubblico();
                if ($isPrivato) {
                    $richiestaPagamento = $this->popolaPagamentoPrivatoMonitoraggio($mandato);
                    $em->persist($richiestaPagamento);
                }

                if ($pagamento->isUltimoPagamento()) {
                    $this->setIndicatoriProgetto($pagamento);
                    $em->persist($pagamento->getRichiesta());
                    if ($isPrivato) {
                        $economie = $this->popolaEconomiePrivato($pagamento);
                        $this->aggiornaFinanziamento($pagamento->getRichiesta());

                        foreach ($economie as $economia) {
                            $em->persist($economia);
                        }

                        $natura = $pagamento->getRichiesta()->getIstruttoria()->getCupNatura();
                        if (!\is_null($natura)) {
                            $richiesta = $pagamento->getRichiesta();
                            $this->popolaStatoFinaleAttuazioneProgettoPrivato($richiesta, $natura, $mandato);
                        }
                    }
                }

                $em->persist($mandato);
                $em->flush();
                $this->addFlash('success', "Salvataggio effettuato correttamente");

                return $this->redirect($indietro);
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage());
                $this->addFlash('error', "Errore nel salvataggio delle informazioni");
            }
        }

        $dati["form"] = $form->createView();
        $dati["menu"] = "mandato";
        $dati["pagamento"] = $pagamento;
        $dati["no_tab"] = true;

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:mandato.html.twig", $dati);
    }

    protected function popolaPagamentoPrivatoMonitoraggio(MandatoPagamento $mandato): RichiestaPagamento {
        $importo = $mandato->getImportoPagato();
        $pagamento = $mandato->getPagamento();
        $richiesta = $mandato->getRichiesta();
        $causale = $pagamento->getModalitaPagamento()->getCausale();
        /** @var RichiestaPagamento|false */
        $pagamentoEsistente = $richiesta->getMonRichiestePagamento()->filter(function (RichiestaPagamento $p) use ($pagamento) {
                return $p->getPagamenti() == $pagamento && $p->getTipologiaPagamento() == RichiestaPagamento::PAGAMENTO;
            })->last();
        $richiestaPagamento = $pagamentoEsistente ?: new RichiestaPagamento($pagamento);
        $richiestaPagamento->setTipologiaPagamento(RichiestaPagamento::PAGAMENTO)
            ->setCausalePagamento($causale)
            ->setImporto($importo)
            ->setDataPagamento($mandato->getDataMandato())
            ->setNote($pagamento->getNotaIntegrazione());

        $richiesta->addMonRichiestePagamento($richiestaPagamento);

        return $richiestaPagamento;
    }

    /**
     * @param Pagamento $pagamento
     * @return TC39CausalePagamento
     */
    private function getCausalePagamento(Pagamento $pagamento) {
        $modalitaPagamento = $pagamento->getModalitaPagamento();
        $causale = TC39CausalePagamento::CodiceDaMandatoPagamento($modalitaPagamento);
        $r = $this->getEm()->getRepository('MonitoraggioBundle:TC39CausalePagamento');
        return $r->findOneBy(array(
                'causale_pagamento' => $causale
        ));
    }

    public function getNomePdf($pagamento) {
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $modalitaPagamento = $pagamento->getModalitaPagamento();
        $date = new \DateTime();
        $data = $date->format('d-m-Y');
        $nome_file = "Esito rendicontazione {$modalitaPagamento->getCodice()} " . $richiesta->getMandatario() . $pagamento->getId() . " " . $data;
        return $nome_file;
    }

    public function pdfEsitoIstruttoriaPagamento($pagamento) {

        $download = true;

        //Scommentare le due righe sotto solo in caso di necessità per pagamenti con molte fatture
        ini_set("memory_limit", "1.5G");
        set_time_limit(600);

        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            $twig = "@AttuazioneControllo/Pdf/Istruttoria/esito_istruttoria_pagamento_anticipo.html.twig";
            $dati = $this->datiPdfEsitoIstruttoriaPagamentoAnticipo($pagamento);
        } else {
            $twig = "@AttuazioneControllo/Pdf/Istruttoria/esito_istruttoria_pagamento.html.twig";
            $dati = $this->datiPdfEsitoIstruttoriaPagamento($pagamento);
        }

        $dati['facsimile'] = false;
        $pdf = $this->container->get("pdf");
        $pdf->setPageOrientation('landscape');
        $pdf->load($twig, $dati);
        //return $this->render($twig,$dati);

        if ($download) {
            $nome_file = $this->getNomePdf($pagamento);
            $pdf->download($nome_file);
            return new Response();
        } else {
            return $pdf->binaryData();
        }
    }

    public function pdfEsitoIstruttoriaPagamentoHtml($pagamento) {

        $download = true;

        //Scommentare le due righe sotto solo in caso di necessità per pagamenti con molte fatture
        ini_set("memory_limit", "1.5G");
        set_time_limit(600);

        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            $twig = "@AttuazioneControllo/Pdf/Istruttoria/esito_istruttoria_pagamento_anticipo.html.twig";
            $dati = $this->datiPdfEsitoIstruttoriaPagamentoAnticipo($pagamento);
        } else {
            $twig = "@AttuazioneControllo/Pdf/Istruttoria/esito_istruttoria_pagamento.html.twig";
            $dati = $this->datiPdfEsitoIstruttoriaPagamento($pagamento);
        }

        $dati['facsimile'] = false;
        return $this->render($twig, $dati);
    }

    public function pdfEsitoIstruttoriaPagamentoAllegato($pagamento, $esito_istruttoria_pagamento) {

        ini_set("memory_limit", "1.5G");
        set_time_limit(600);

        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            $twig = "@AttuazioneControllo/Pdf/Istruttoria/esito_istruttoria_pagamento_anticipo.html.twig";
            $dati = $this->datiPdfEsitoIstruttoriaPagamentoAnticipo($pagamento);
        } else {
            $twig = "@AttuazioneControllo/Pdf/Istruttoria/esito_istruttoria_pagamento.html.twig";
            $dati = $this->datiPdfEsitoIstruttoriaPagamento($pagamento);
        }

        $dati['facsimile'] = false;
        $pdf = $this->container->get("pdf");
        $pdf->setPageOrientation('landscape');
        $pdf->load($twig, $dati);

        $tipoDocumento = $this->getEm()->getRepository(TipologiaDocumento::class)->findOneByCodice(TipologiaDocumento::ESITO_ISTRUTTORIA_PAGAMENTO_ALTRO);
        $documentoEsito = $this->container->get("documenti")->caricaDaByteArray($pdf->binaryData(), $this->getNomePdf($pagamento) . ".pdf", $tipoDocumento, false);

        //associo il documento alla richiesta
        $esito_istruttoria_pagamento->setDocumento($documentoEsito);
        $this->getEm()->persist($esito_istruttoria_pagamento);
    }

    private function datiPdfEsitoIstruttoriaPagamento($pagamento) {

        $dati = array();

        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $gestoreIstruttoriaPagamenti = $this->container->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura());

        $gestoreIstruttoriaGiustificativi = $this->container->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura());

        $dati["contributoComplessivoSpettante"] = $pagamento->getContributoComplessivoSpettante();

        $contributoErogabile = $gestoreIstruttoriaPagamenti->getValoreFromChecklist($pagamento, 'CONTRIBUTO_EROGABILE');
        $dati["contributoErogabile"] = $contributoErogabile;

        $esitiIstruttoriaPagamento = $pagamento->getEsitiIstruttoriaPagamento();
        $esitoIstruttoriaPagamento = $esitiIstruttoriaPagamento->first();
        $dati["noteAllaLiquidazione"] = $esitoIstruttoriaPagamento ? $esitoIstruttoriaPagamento->getNoteAllaLiquidazione() : null;

        $dati["avanzamento"] = $gestoreIstruttoriaGiustificativi->calcolaAvanzamentoPianoCostiEsito($richiesta, NULL, $pagamento);

        $atc = $pagamento->getAttuazioneControlloRichiesta();
        $pagamenti = $atc->getPagamenti();
        $dati["pagamenti"] = $pagamenti;

        /**
         * il contributo erogato è pari alla somma del contributo erogabile definito nelle checklist degli eventuali pagamenti precedenti
         */
        $contributoErogato = 0.0;
        foreach ($pagamenti as $pagamentoPrecedente) {
            if ($pagamentoPrecedente->getId() != $pagamento->getId()) {
                if ($pagamentoPrecedente->isAnticipo()) {
                    $contributoErogato += (float) $gestoreIstruttoriaPagamenti->getValoreFromChecklist($pagamentoPrecedente, 'ANTICIPO_EROGATO');
                } else {
                    $contributoErogato += (float) $gestoreIstruttoriaPagamenti->getValoreFromChecklist($pagamentoPrecedente, 'CONTRIBUTO_EROGABILE');
                }
            }
        }
        $dati["contributoErogato"] = $contributoErogato;

        if ($pagamento->isInviatoRegione()) {
            $variazione = $pagamento->getAttuazioneControlloRichiesta()->getUltimaVariazionePianoCostiPA($pagamento);
        } else {
            $variazione = $pagamento->getAttuazioneControlloRichiesta()->getUltimaVariazioneApprovata();
        }
        if (!is_null($variazione)) {
            $importoComplessivoAmmesso = $variazione->getCostoAmmesso();
            $contributoComplessivoAmmesso = $variazione->getContributoAmmesso();
        } else {
            $istruttoriaRichiesta = $richiesta->getIstruttoria();
            $importoComplessivoAmmesso = $istruttoriaRichiesta->getCostoAmmesso();
            $contributoComplessivoAmmesso = $istruttoriaRichiesta->getContributoAmmesso();
        }

        $dati['importoComplessivoAmmesso'] = $importoComplessivoAmmesso;
        $dati['contributoComplessivoAmmesso'] = $contributoComplessivoAmmesso;

        $dati["importoRendicontato"] = $pagamento->getImportoTotaleRichiesto();
        $dati["importoRendicontatoAmmesso"] = $pagamento->getImportoTotaleRichiestoAmmesso();
        $dati["importoNonAmmesso"] = $pagamento->calcolaImportoNonAmmesso();

        $procedura = $richiesta->getProcedura();
        $pianiCosto = $procedura->getPianiCosto();

        // imputazioni istruite raggruppate per piano costo
        $vociPianoCostoGiustificativoIstruite = array();
        foreach ($pianiCosto as $pianoCosto) {
            $vociIstruite = $this->getEm()->getRepository('AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo')->getVociPianoCostoGiustificativoIstruite($pagamento, $pianoCosto);
            if (count($vociIstruite) > 0) {
                $vociPianoCostoGiustificativoIstruite[$pianoCosto->getSezionePianoCosto() . ' - ' . $pianoCosto->getTitolo()] = $vociIstruite;
            }
        }

        $dati['vociPianoCostoGiustificativoIstruite'] = $vociPianoCostoGiustificativoIstruite;

        $rendicontazioneProceduraConfig = $this->container->get("gestore_istruttoria_pagamenti")->getGestore($procedura)->getRendicontazioneProceduraConfig($procedura);

        $dati["isRendicontazioneMultiproponente"] = $rendicontazioneProceduraConfig->getRendicontazioneMultiProponente();
        $dati["richiesta"] = $richiesta;
        $dati["pagamento"] = $pagamento;

        $dati["procedura"] = $procedura;
        $dati["capofila"] = $richiesta->getMandatario();
        $dati["rendicontazioneProceduraConfig"] = $rendicontazioneProceduraConfig;

        $isFsc = $this->container->get("gestore_richieste")->getGestore($procedura)->isFsc();
        $dati["is_fsc"] = $isFsc;

        if ($rendicontazioneProceduraConfig->getSezioneContratti() == true) {
            $contratti = $pagamento->getContratti();
            foreach ($contratti as $contratto) {
                $istruttoriaContratto = $contratto->getIstruttoriaOggettoPagamento();
                if ($istruttoriaContratto && $istruttoriaContratto->isIntegrazione()) {
                    $numero = $contratto->getNumero();
                    $fornitore = $contratto->getFornitore();
                    $nota = $istruttoriaContratto->getNotaIntegrazione();
                    $dati['contratto_singoli'][] = array('numero' => $numero, 'fornitore' => $fornitore, 'nota' => $nota);
                }
            }
        }

        return $dati;
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
            if ($pagamentoPCD["anno"] == date("Y", $pagamento->getDataInvio()->getTimestamp())) {
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

            $pianoCosti->setAnnoPiano(\intval(\date("Y")));

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

    public function isRuoloAbilitato() {
        if ($this->isGranted("ROLE_ISTRUTTORE_ATC") || $this->isGranted("ROLE_ISTRUTTORE_SUPERVISORE_ATC")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param \AttuazioneControlloBundle\Entity\Pagamento $pagamento
     *
     * Metodo richiamato per popolare automaticamente le economie per i beneficiari privati
     */
    protected function popolaEconomiePrivato(Pagamento $pagamento) {
        $em = $this->getEm();

        // Valore ritornato dalla funzione
        $economie = array();

        // $richiesta = new Richiesta();

        $richiesta = $pagamento->getRichiesta();
        $istruttoriaRichiesta = $richiesta->getIstruttoria();

        // TC33/FONTE FINANZIARIA
        $tc33Repository = $em->getRepository(TC33FonteFinanziaria::class);
        $fontePrivato = $tc33Repository->findOneBy(array("cod_fondo" => "PRT"));
        $fonteUE = $tc33Repository->findOneBy(array("cod_fondo" => "ERDF"));
        $fonteStato = $tc33Repository->findOneBy(array("cod_fondo" => "FDR"));
        $fonteRegione = $tc33Repository->findOneBy(array("cod_fondo" => "FPREG"));

        // I TOTALI
        $costoAmmesso = $istruttoriaRichiesta->getCostoAmmesso(); // Il totale ammesso - COSTO TOTALE DEL PROGETTO
        $contributoConcesso = $istruttoriaRichiesta->getContributoAmmesso(); // Il massimo contributo erogabile
        // I RENDICONTATI
        // ATTENZIONE! Possiamo usare gli importi del mandato pagamento SOLO per i privati!!
        // Per i pubblici, abbiamo bisogno di un altro giro per recuperare l' importo pagato
        $mandatoPagamento = $pagamento->getMandatoPagamento();

        // PASGAMENTI PRECEDENTI + ATTUALE
        $pagamentiPrecedenti = $richiesta->getAttuazioneControllo()->getPagamenti();

        $contributoPagato = 0.00; // il totale pagato da UE-Stato-Regione // CONTRIBUTO EROGATO A SALDO
        $rendicontatoAmmesso = 0.00; // quanto ha rendicontato il beneficiario


        foreach ($pagamentiPrecedenti as $pagamentoPrecedente) {

            // Sommo gli importi dei pagamenti COPERTI DA MANDATO
            if (!is_null($pagamentoPrecedente->getMandatoPagamento())) {
                $contributoPagato += $pagamentoPrecedente->getMandatoPagamento()->getImportoPagato();
            }

            $rendicontatoAmmesso += $pagamentoPrecedente->getRendicontatoAmmesso();  // Somma degli IMPORTI APPROVATI dei GIUSTIFICATIVI
        }

        // GLI IMPORTI DELLE ECONOMIE
        $importoEconomiaTotale = $costoAmmesso - $rendicontatoAmmesso; // economia totale(privato + UE-Stato-Regione)
        // Siamo nel CASO 1 dell'EXCEL
        if ($importoEconomiaTotale > 0) {

            // Creazione ECONOMIE
            $economiaPrivato = new Economia();
            $economiaUE = new Economia();
            $economiaStato = new Economia();
            $economiaRegione = new Economia();

            $importoEconomiaQuote = $contributoConcesso - $contributoPagato; // economia che avanza dai contributi UE-Stato-Regione // ECONOMIA DI CONTRIBUTO
            $importoEconomiaPrivata = $importoEconomiaTotale - $importoEconomiaQuote;

            $importoEconomiaUE = round($importoEconomiaQuote * Finanziamento::FINANZIAMENTO_UE, 2);
            $importoEconomiaStato = round($importoEconomiaQuote * Finanziamento::FINANZIAMENTO_STATO, 2);
            $importoEconomiaRegione = $importoEconomiaQuote - ($importoEconomiaUE + $importoEconomiaStato);

            // SETTO GLI IMPORTI ALLE 4 ECONOMIE
            $economiaPrivato->setImporto($importoEconomiaPrivata);
            $economiaUE->setImporto($importoEconomiaUE);
            $economiaStato->setImporto($importoEconomiaStato);
            $economiaRegione->setImporto($importoEconomiaRegione);

            // FONTE
            $economiaPrivato->setTc33FonteFinanziaria($fontePrivato);
            $economiaUE->setTc33FonteFinanziaria($fonteUE);
            $economiaStato->setTc33FonteFinanziaria($fonteStato);
            $economiaRegione->setTc33FonteFinanziaria($fonteRegione);

            // RICHIESTA
            $economiaPrivato->setRichiesta($richiesta);
            $economiaUE->setRichiesta($richiesta);
            $economiaStato->setRichiesta($richiesta);
            $economiaRegione->setRichiesta($richiesta);

            // IMPOSTO il valore di ritorno
            $economie[] = $economiaPrivato;
            $economie[] = $economiaUE;
            $economie[] = $economiaStato;
            $economie[] = $economiaRegione;

            /*
             * TODO: Inserire l'aggiornamento degli IMPEGNI (Inserimento DISIMPEGNO!! Non decrementare l'IMPEGNO!!!)
             * TODO: La possibilità di inserire l'IMPEGNO è in fase di sviluppo.....
             */
        }


        /* Qualora a saldo del progetto la differenza tra contributo concesso
         * e contributo erogato sia maggiore della differenza tra finanziamento
         * totale e rendicontato ammesso, allora bisognerà procedere, oltre 
         * all'individuazione delle economie, alla rideterminazione interna 
         * degli importi delle fonti di finanziamento.
         */

        return $economie;
    }

    protected function aggiornaFinanziamento(Richiesta $richiesta): void {
        /** @var \MonitoraggioBundle\Service\gestoriFinanziamento\Privato $gestoreFinanziamento */
        $gestoreFinanziamentoService = $this->container->get('monitoraggio.gestore_finanziamento');
        $gestoreFinanziamento = $gestoreFinanziamentoService->getGestore($richiesta);
        $gestoreFinanziamento->aggiornaFinanziamento();
        $gestoreFinanziamento->persistFinanziamenti();
    }

    /**
     * Metodo richiamato contestualmente all'inserimento del mandato per un pagamento di tipo SALDO, tramite il pulsante VALIDA
     * serve per popolare automaticamente lo STATO FINALE ATTUAZIONE PROGETTO; utile ai fine del monitoraggio
     */
    protected function popolaStatoFinaleAttuazioneProgettoPrivato(Richiesta $richiesta, CupNatura $natura, MandatoPagamento $mandato) {

        // DESTINAZIONE
        $statoAttuazioneProgettoMon = new RichiestaStatoAttuazioneProgetto();
        $em = $this->getEm();

        $dataRiferimento = new \DateTime();

        $statoAttuazioneProgettoMon->setRichiesta($richiesta);

        if ($natura->getCodice() == '03') { // REALIZZAZIONE DI LAVORI PUBBLICI (OPERE ED IMPIATISTICA
            // TODO: IN ESERCIZIO; data collaudo - FINE EFFETTIVA
            $vociFasiProcedurali = $richiesta->getMonIterProgetti();

            foreach ($vociFasiProcedurali as $voceFaseProcedurale) {
                if ($voceFaseProcedurale->getFaseProcedurale()->getCodFase() == '0307') {  // COLLAUDO
                    $dataRiferimento = $voceFaseProcedurale->getDataFineEffettiva();
                    break;
                }
            }

            // STATO PROGETTO - TC47StatoProgetto - STATO FINALE
            $statoFinaleProgetto = $em->getRepository("MonitoraggioBundle\Entity\TC47StatoProgetto")->findBy(array("descr_stato_prg" => "In esercizio"));
        } else {

            // TODO: CONCLUSO; → Pagamento a SALDO - DATA MANDATO
            // STATO PROGETTO - TC47StatoProgetto - STATO FINALE
            $dataRiferimento = $mandato->getDataMandato();
            $statoFinaleProgetto = $em->getRepository("MonitoraggioBundle\Entity\TC47StatoProgetto")->findBy(array("descr_stato_prg" => "Concluso"));
        }

        $statoAttuazioneProgettoMon->setStatoProgetto($statoFinaleProgetto[0]);

        $statoAttuazioneProgettoMon->setDataRiferimento($dataRiferimento);

        // setto la DESTINAZIONE nella richiesta
        $richiesta->addMonStatoProgetti($statoAttuazioneProgettoMon);
    }

    /**
     * una volta inviato l'esito, viene mostrata l schermata di repilogo
     * @param type $pagamento
     * @return type
     */
    public function riepilogoEsitoInviato($pagamento) {

        $indietro = $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId()));

        $dati = array();
        $dati["menu"] = "esito";
        $dati["pagamento"] = $pagamento;
        $dati["indietro"] = $indietro;

        $twig = "AttuazioneControlloBundle:Istruttoria/Pagamenti:riepilogoEsito.html.twig";

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Esito finale");

        $response = $this->render($twig, $dati);

        return $response;
    }

    private function datiPdfEsitoIstruttoriaPagamentoAnticipo($pagamento) {

        $dati = array();

        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $valutazioneElementoChecklistRepository = $this->getEm()->getRepository('AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamento');

        $dati['anticipo_richiesto'] = null;
        $dati['anticipo_erogato'] = null;

        $valutazioniChecklist = $pagamento->getValutazioniChecklist();
        foreach ($valutazioniChecklist as $valutazioneChecklist) {
            if ($valutazioneChecklist->getChecklist()->isTipologiaAnticipi()) {
                $elementoValutazioneChecklist = $valutazioneElementoChecklistRepository->getValutazioneElementoByCodice($valutazioneChecklist, 'ANTICIPO_RICHIESTO');
                $dati['anticipo_richiesto'] = $elementoValutazioneChecklist->getValore();

                $elementoValutazioneChecklist = $valutazioneElementoChecklistRepository->getValutazioneElementoByCodice($valutazioneChecklist, 'ANTICIPO_EROGATO');
                $dati['anticipo_erogato'] = $elementoValutazioneChecklist->getValore();
            }
        }

        $dati["richiesta"] = $richiesta;
        $dati["pagamento"] = $pagamento;
        $dati["procedura"] = $richiesta->getProcedura();
        $dati["capofila"] = $richiesta->getMandatario();

        return $dati;
    }

    protected function setIndicatoriProgetto(Pagamento $pagamento): void {
        /** @var \MonitoraggioBundle\Service\GestoreIndicatoreService $factory */
        $factory = $this->container->get("monitoraggio.indicatori_output");
        $richiesta = $pagamento->getRichiesta();
        $service = $factory->getGestore($richiesta);

        $service->valorizzaIndicatoriAutomatici();
    }

}
