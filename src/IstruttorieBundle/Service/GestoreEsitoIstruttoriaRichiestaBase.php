<?php

namespace IstruttorieBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use BaseBundle\Entity\StatoIntegrazione;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Service\GestoreResponse;
use BaseBundle\Exception\SfingeException;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;

/**
 * Description of GestoreEsitoIstruttoriaRichiestaBase
 *
 * @author gdisparti
 */
class GestoreEsitoIstruttoriaRichiestaBase extends \BaseBundle\Service\BaseService {

    public function emettiEsito($richiestaId) {

        $em = $this->getEm();

        // fetch richiesta

        $richiesta = null; // to fetch

        $request = $this->getCurrentRequest();

        /* Se l'esito non esiste lo creo */
        $esitoIstruttoriaRichiesta = $richiesta->getEsitiIstruttoriaRichiesta();

        if (count($esitoIstruttoriaRichiesta) == 0) {
            $esitoIstruttoriaRichiesta = new \IstruttorieBundle\Entity\EsitoIstruttoriaRichiesta();

            /*             * * TESTO DELL'EMAIL DI DEFAULT ** */
            $testo_default = '';
            $esitoIstruttoriaRichiesta->setTestoEmail($testo_default);
            /*             * ******************************** */

            $esitoIstruttoriaRichiesta->setRichiesta($richiesta);

            // gestire stato???
            //$this->container->get("sfinge.stati")->avanzaStato($esito_istruttoria_pagamento, \BaseBundle\Entity\StatoEsitoIstruttoriaPagamento::ESITO_IP_INSERITA);

            $em->persist($esitoIstruttoriaRichiesta);
            $em->flush();
        } else {
            // se è stato settato $esito_istruttoria_pagamento è una PersistantCollection... per cui:
            $esito_istruttoria_pagamento = $esito_istruttoria_pagamento[0];
        }

        // to do link indietro
        //$indietro = $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId()));

        $verifica = $this->verificaEsitoFinaleEmettibile($pagamento);
        if (!$verifica->getEsito()) {
            foreach ($verifica->getMessaggi() as $messaggio) {
                $this->addFlash('error', $messaggio);
            }
            return $this->redirect($indietro);
        }

        $documentoEsitoIstruttoriaRichiesta = new \AttuazioneControlloBundle\Entity\Istruttoria\DocumentoEsitoIstruttoriaRichiesta();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();
        $documentoEsitoIstruttoriaRichiesta->setDocumentoFile($documento_file);
        $documentoEsitoIstruttoriaRichiesta->setEsitoIstruttoriaRichiesta($esitoIstruttoriaRichiesta);

        $documenti_caricati = $esitoIstruttoriaRichiesta->getDocumentiEsitoIstruttoria();

        $listaTipi = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByTipologia('esito_istruttoria_richiesta');

        // Se lo stato è inviato/protocollato
        $disabilita_azioni = ($esito_istruttoria_pagamento->getStato() != \BaseBundle\Entity\StatoEsitoIstruttoriaPagamento::ESITO_IP_INSERITA);

        if ($disabilita_azioni) {
            $msg = $esito_istruttoria_pagamento->getStato()->getDescrizione();
            if (($esito_istruttoria_pagamento->getProtocolloEsitoIstruttoria() != '-') && !is_null($esito_istruttoria_pagamento->getDataProtocolloEsitoIstruttoria())) {
                $msg .= ' [Protocollo N° ' . $esito_istruttoria_pagamento->getProtocolloEsitoIstruttoria() . ' del ' . $esito_istruttoria_pagamento->getDataProtocolloEsitoIstruttoria() . ']';
            }
            $this->addFlash("success", $msg);
        }

        if (count($listaTipi) > 0) {

            $opzioni_form_documenti["lista_tipi"] = $listaTipi;
            $opzioni_form_documenti["url_indietro"] = $indietro;
            $opzioni_form_documenti["disabled"] = $disabilita_azioni;
            $form_documenti = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\DocumentiEsitoIstruttoriaPagamentoType', $documento_esito_istruttoria, $opzioni_form_documenti);

            $opzioni_form_esito["url_indietro"] = $indietro;
            $opzioni_form_esito["disabled"] = $disabilita_azioni;
            $form_esito = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\EsitoIstruttoriaPagamentoType', $esito_istruttoria_pagamento, $opzioni_form_esito);

            if ($request->isMethod('POST')) {

                $form_documenti->handleRequest($request);
                $form_esito->handleRequest($request);

                if ($form_documenti->isSubmitted() && $form_documenti->isValid()) {
                    try {
                        $this->container->get("documenti")->carica($documento_file);
                        $em->persist($documento_esito_istruttoria);
                        $em->flush();
                        $this->addFlash("success", "Documento caricato con successo.");
                        //return new GestoreResponse($this->redirect($this->generateUrl('esito_finale_istruttoria_pagamenti', array("id_pagamento" => $pagamento->getId()))));
                    } catch (ResponseException $e) {
                        $this->addFlash('error', "Errore durante il caricamento del documento. Si invita a riprovare. Se il problema persiste contattare l'assistenza");
                    }
                }

                if ($form_esito->isSubmitted() && $form_esito->isValid()) {

                    try {

                        // SALVATAGGIO INFORMAZIONI
                        $em->persist($esito_istruttoria_pagamento);
                        $em->flush();

                        if ($form_esito->get("pulsanti")->get("pulsante_invio")->isClicked()) {

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
                            $opzioni_form_documenti["disabled"] = $disabilita_azioni;
                            $form_documenti = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\DocumentiEsitoIstruttoriaPagamentoType', $documento_esito_istruttoria, $opzioni_form_documenti);

                            $opzioni_form_esito["url_indietro"] = $indietro;
                            $opzioni_form_esito["disabled"] = $disabilita_azioni;
                            $form_esito = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\EsitoIstruttoriaPagamentoType', $esito_istruttoria_pagamento, $opzioni_form_esito);
                        }

                        if ($form_esito->get("pulsanti")->get("pulsante_submit")->isClicked()) {
                            // SALVA
                            $this->addFlash("success", "Esito istruttoria pagamento salvato con successo.");
                        }
                    } catch (ResponseException $e) {
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

        $dati = array(
            "pagamento" => $pagamento,
            "menu" => 'esito',
            "documenti" => $documenti_caricati,
            //"proponente" => $proponente,
            "form_documenti" => $form_documenti_view,
            "form_esito" => $form_esito_view,
            "route_cancellazione_documento" => 'esito_finale_elimina_doc',
            "url_indietro" => $indietro,
            "disabilita_azioni" => $disabilita_azioni,
            "documenti_richiesti" => $listaTipi
        );

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Esito finale");

        return $this->render("AttuazioneControlloBundle:Istruttoria\Pagamenti:esitoIstruttoriaPagamento.html.twig", $dati);
        //return new GestoreResponse($response);
    }

    public function isEsitoEmittibile() {
        // definire logica
    }

    public function generaPdf($richiesta) {

        $download = true;

        // to do path
        $twig = "path/esito_istruttoria_richiesta.html.twig";
        $dati = $this->datiPdfEsitoIstruttoriaPagamento($pagamento);
        $dati['facsimile'] = true;
        $isFsc = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;
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

    // to refactor
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

}
