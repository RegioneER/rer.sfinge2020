<?php

namespace IstruttorieBundle\Service;

use SfingeBundle\Entity\Procedura;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Service\GestoreResponse;
use BaseBundle\Exception\SfingeException;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use BaseBundle\Entity\StatoComunicazioneProgetto;
use DocumentoBundle\Component\ResponseException;
use Symfony\Component\HttpFoundation\Response;
use ProtocollazioneBundle\Entity\RichiestaProtocollo;

/**
 * Description of GestoreComunicazioneProgettoBase
 *
 * @author aturdo
 */
class GestoreComunicazioneProgettoBase extends \BaseBundle\Service\BaseService {

    public function calcolaAzioniAmmesse($risposta_comunicazione_istruttoria) {
        $csrfTokenManager = $this->container->get("security.csrf.token_manager");
        $token = $csrfTokenManager->getToken("token")->getValue();

        $vociMenu = array();
        $comunicazione = $risposta_comunicazione_istruttoria->getComunicazione();
        $stato = $risposta_comunicazione_istruttoria->getStato()->getCodice();

        if ($stato == StatoComunicazioneProgetto::COM_INSERITA && $this->isBeneficiario()) {
            if ($comunicazione->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
                // firmatario
                $voceMenu["label"] = "Firmatario";
                $voceMenu["path"] = $this->generateUrl("risposta_comunicazione_progetto_firmatario", array("id_comunicazione" => $comunicazione->getId()));
                $vociMenu[] = $voceMenu;
            }

            // validazione
            $esitoValidazione = $this->controllaValiditaRisposta($risposta_comunicazione_istruttoria);

            if ($esitoValidazione->getEsito()) {
                $voceMenu["label"] = "Valida";
                $voceMenu["path"] = $this->generateUrl("valida_comunicazione_progetto_risposta", array("id_comunicazione_risposta" => $risposta_comunicazione_istruttoria->getId(), "_token" => $token));
                $vociMenu[] = $voceMenu;
            }
        }

        // scarica pdf domanda
        if ($stato != StatoComunicazioneProgetto::COM_INSERITA) {
            $voceMenu["label"] = "Scarica risposta";
            $voceMenu["path"] = $this->generateUrl("scarica_comunicazione_progetto_risposta", array("id_comunicazione_risposta" => $risposta_comunicazione_istruttoria->getId()));
            $vociMenu[] = $voceMenu;
        }

        //carica richiesta firmata
        if ($stato == StatoComunicazioneProgetto::COM_VALIDATA && $this->isBeneficiario() && $comunicazione->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
            $voceMenu["label"] = "Carica risposta firmata";
            $voceMenu["path"] = $this->generateUrl("carica_comunicazione_risposta_progetto_firmata", array("id_comunicazione_risposta" => $risposta_comunicazione_istruttoria->getId(), "id_comunicazione" => $comunicazione->getId()));
            $vociMenu[] = $voceMenu;
        }

        if (!($stato == StatoComunicazioneProgetto::COM_INSERITA || $stato == StatoComunicazioneProgetto::COM_VALIDATA)
            && $comunicazione->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
            $voceMenu["label"] = "Scarica risposta firmata";
            $voceMenu["path"] = $this->generateUrl("scarica_comunicazione_risposta_progetto_firmata", array("id_comunicazione_risposta" => $risposta_comunicazione_istruttoria->getId()));
            $vociMenu[] = $voceMenu;
        }

        //invio alla pa
        if ($stato == StatoComunicazioneProgetto::COM_FIRMATA && $this->isBeneficiario()) {
            $voceMenu["label"] = "Invia risposta";
            $voceMenu["path"] = $this->generateUrl("invia_risposta_comunicazione_progetto", array("id_comunicazione_risposta" => $risposta_comunicazione_istruttoria->getId(), "_token" => $token));
            $voceMenu["attr"] = "data-confirm=\"Continuando non sarà più possibile modificare la comunicazione nemmeno dall'assistenza tecnica. Si intende procedere comunque?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
            $vociMenu[] = $voceMenu;
        }

        //invalidazione
        if (($stato == StatoComunicazioneProgetto::COM_VALIDATA || $stato == StatoComunicazioneProgetto::COM_FIRMATA) && $this->isBeneficiario()) {
            $voceMenu["label"] = "Invalida";
            $voceMenu["path"] = $this->generateUrl("invalida_comunicazione_risposta_progetto", array("id_comunicazione_risposta" => $risposta_comunicazione_istruttoria->getId(), "_token" => $token));
            $voceMenu["attr"] = "data-confirm=\"Confermi l'invalidazione della risposta?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
            $vociMenu[] = $voceMenu;
        }

        return $vociMenu;
    }

    public function isBeneficiario() {
        return $this->isGranted("ROLE_UTENTE");
    }

    public function validaNotaRisposta($comunicazione) {
        $esito = new EsitoValidazione(true);
        // $documenti_obbligatori = $this->getTipiDocumenti($id_richiesta, 1);

        if (is_null($comunicazione) || is_null($comunicazione->getTesto())) {
            $esito->setEsito(false);
            $esito->addMessaggio('Nota di risposta non fornita');
            $esito->addMessaggioSezione('Nota di risposta non fornita');
        }

        return $esito;
    }

    public function gestioneBarraAvanzamento($comunicazione) {
        /** @var Procedura $richiesta */
        $procedura = $comunicazione->getProcedura();

        $statoRichiesta = $comunicazione->getStato()->getCodice();
        $arrayStati = array('Inserita' => true, 'Validata' => false, 'Firmata' => false, 'Inviata' => false);

        switch ($statoRichiesta) {
            case StatoComunicazioneProgetto::COM_PROTOCOLLATA:
            case StatoComunicazioneProgetto::COM_INVIATA_PA:
                $arrayStati['Inviata'] = true;
            case StatoComunicazioneProgetto::COM_FIRMATA:
                $arrayStati['Firmata'] = true;
            case StatoComunicazioneProgetto::COM_VALIDATA:
                $arrayStati['Validata'] = true;
        }

        if (!$procedura->isRichiestaFirmaDigitaleStepSuccessivi()) {
            unset($arrayStati['Firmata']);
        }

        return $arrayStati;
    }

    public function notaRispostaComunicazione($comunicazione, $opzioni) {

        $form_options["disabled"] = $this->isComunicazioneDisabilitata($comunicazione);

        $form_options = array_merge($form_options, $opzioni["form_options"]);

        $form = $this->createForm("IstruttorieBundle\Form\NotaRispostaComunicazioneProgettoType", $comunicazione->getRisposta(), $form_options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->flush();
                    $this->addFlash("success", "Nota risposta integrazione salvata correttamente");
                    return new GestoreResponse($this->redirect($form_options["url_indietro"]));
                } catch (\Exception $e) {
                    throw new SfingeException("Nota risposta integrazione non salvata");
                }
            }
        }

        $dati = array("form" => $form->createView());

        $response = $this->render("IstruttorieBundle:RispostaIntegrazione:notaRisposta.html.twig", $dati);

        return new GestoreResponse($response);
    }

    public function isComunicazioneDisabilitata($comunicazione) {

        if (!$this->isBeneficiario()) {
            return true;
        }
        $risposta = $comunicazione->getRisposta();
        $stato = $risposta->getStato()->getCodice();
        if ($stato != StatoComunicazioneProgetto::COM_INSERITA) {
            return true;
        }

        return false;
    }

    public function elencoDocumenti($comunicazione, $proponente = null, $opzioni = array()) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documento_comunicazione = new \IstruttorieBundle\Entity\RispostaComunicazioneProgettoDocumento();
        $documento_file = new DocumentoFile();

        $documenti_caricati = $em->getRepository("IstruttorieBundle\Entity\RispostaComunicazioneProgettoDocumento")->findBy(array("risposta_comunicazione" => $comunicazione->getRisposta(), "proponente" => $proponente));

        $listaTipi = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByCodice(TipologiaDocumento::COMUNICAZIONE_RISPOSTA_ALLEGATO);

        if (count($listaTipi) > 0) {

            $opzioni_form["lista_tipi"] = $listaTipi;
            $opzioni_form["disabled"] = $this->isComunicazioneDisabilitata($comunicazione);
            $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
            $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Salva'));

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {

                        $this->container->get("documenti")->carica($documento_file);

                        $documento_comunicazione->setDocumentoFile($documento_file);
                        $documento_comunicazione->setRispostaComunicazione($comunicazione->getRisposta());
                        $documento_comunicazione->setProponente($proponente);
                        $em->persist($documento_comunicazione);

                        $em->flush();
                        $this->addFlash("success", "Documento caricato correttamente");
                        return new GestoreResponse($this->redirect($opzioni["url_corrente"]));
                    } catch (ResponseException $e) {
                        $this->addFlash('error', $e->getMessage());
                    }
                }
            }
            $form_view = $form->createView();
        } else {
            $form_view = null;
        }

        $dati = array(
            "documenti" => $documenti_caricati,
            "risposta" => $comunicazione->getRisposta(),
            "proponente" => $proponente,
            "form" => $form_view,
            "route_cancellazione_documento" => $opzioni["route_cancellazione_documento"],
            "url_indietro" => $opzioni["url_indietro"],
            "disabilita_azioni" => $this->isComunicazioneDisabilitata($comunicazione),
        );

        $response = $this->render("IstruttorieBundle:RispostaComunicazione:elencoDocumentiRichiesta.html.twig", $dati);
        return new GestoreResponse($response);
    }

    public function validaDocumenti($comunicazione, $proponente = null) {
        $esito = new EsitoValidazione(true);
        return $esito;
    }

    public function sceltaFirmatario($comunicazione, $opzioni = array()) {

        $request = $this->getCurrentRequest();
        $form_options["disabled"] = $this->isComunicazioneDisabilitata($comunicazione);
        $form_options = array_merge($form_options, $opzioni["form_options"]);

        $form = $this->createForm("IstruttorieBundle\Form\SceltaFirmatarioRispostaComunicazioneProgettoType", $comunicazione->getRisposta(), $form_options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->flush();

                    $this->addFlash("success", "Firmatario della risposta impostato correttamente");
                    return new GestoreResponse($this->redirect($form_options["url_indietro"]));
                } catch (\Exception $e) {
                    throw new SfingeException("Firmatario non impostato");
                }
            }
        }

        $dati = array("firmatario" => $comunicazione->getRisposta()->getFirmatario(), "form" => $form->createView());

        $response = $this->render("IstruttorieBundle:RispostaIntegrazione:sceltaFirmatario.html.twig", $dati);

        return new GestoreResponse($response);
    }

    public function validaRispostaComunicazione($comunicazione_risposta, $opzioni = array()) {

        if ($comunicazione_risposta->getStato()->uguale(StatoComunicazioneProgetto::COM_INSERITA)) {

            $esitoValidazione = $this->controllaValiditaRisposta($comunicazione_risposta);
            if ($esitoValidazione->getEsito()) {
                $this->getEm()->beginTransaction();
                if (!is_null($comunicazione_risposta->getDocumentoRisposta())) {
                    $this->container->get("documenti")->cancella($comunicazione_risposta->getDocumentoRisposta(), 0);
                }

                //genero il nuovo pdf
                $pdf = $this->generaPdf($comunicazione_risposta);

                //lo persisto
                $tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice(TipologiaDocumento::COMUNICAZIONE_PROGETTO_RISPOSTA);
                $documentoRisposta = $this->container->get("documenti")->caricaDaByteArray($pdf, $this->getNomePdfComunicazioneRisposta($comunicazione_risposta) . ".pdf", $tipoDocumento, false);

                //associo il documento alla richiesta
                $comunicazione_risposta->setDocumentoRisposta($documentoRisposta);
                $this->getEm()->persist($documentoRisposta);
                $this->getEm()->flush();
                $this->container->get("sfinge.stati")->avanzaStato($comunicazione_risposta, StatoComunicazioneProgetto::COM_VALIDATA);
                $this->getEm()->flush();
                $this->getEm()->commit();
                $this->addFlash("success", "Comunicazione validata");
                return new GestoreResponse($this->redirect($opzioni['url_indietro']));
            } else {
                throw new SfingeException("La comunicazione non è validabile");
            }
        } else {
            throw new SfingeException("La comunicazione non è validabile");
        }
    }

    public function controllaValiditaRisposta($comunicazione) {
        $esito = new EsitoValidazione(true);

        $esitoValidaNota = $this->validaNotaRisposta($comunicazione);
        $esitoValidaDocumenti = $this->validaDocumenti($comunicazione);
        if (!$esitoValidaNota->getEsito() || !$esitoValidaDocumenti->getEsito()) {
            $esito->setEsito(false);
            $esito->setMessaggio($esitoValidaNota->getMessaggi());
            $esito->setMessaggiSezione($esitoValidaNota->getMessaggiSezione());
        }

        return $esito;
    }

    public function invalidaRispostaComunicazione($comunicazione_risposta, $opzioni = array()) {

        if ($comunicazione_risposta->getStato()->uguale(StatoComunicazioneProgetto::COM_VALIDATA) ||
                $comunicazione_risposta->getStato()->uguale(StatoComunicazioneProgetto::COM_FIRMATA)) {
            $this->container->get("sfinge.stati")->avanzaStato($comunicazione_risposta, StatoComunicazioneProgetto::COM_INSERITA, true);
            $this->addFlash("success", "Comunicazione invalidata");
            return new GestoreResponse($this->redirect($opzioni['url_indietro']));
        }
        throw new SfingeException("Stato non valido per effettuare l'invalidazione");
    }

    public function eliminaDocumentoComunicazioneRichiesta($documento, $opzioni = array()) {
        $em = $this->getEm();
        try {
            $this->container->get("documenti")->cancella($documento->getDocumentoFile(), 0);
            $em->remove($documento);
            $em->flush();
            $this->addFlash("success", "Documento eliminato correttamente");
        } catch (ResponseException $e) {
            $this->addFlash('error', "Errore nell'eliminazione del documento");
        }
        return new GestoreResponse($this->redirect($opzioni["url_indietro"]));
    }

    public function eliminaDocumentoComunicazioneRisposta($id_comunicazione, $opzioni = array()) {
        $em = $this->getEm();
        $documento = $em->getRepository("IstruttorieBundle\Entity\RispostaComunicazioneProgettoDocumento")->find($id_comunicazione);

        try {
            $this->container->get("documenti")->cancella($documento->getDocumentoFile(), 0);
            $em->remove($documento);
            $em->flush();
            $this->addFlash("success", "Documento eliminato correttamente");
        } catch (ResponseException $e) {
            $this->addFlash('error', "Errore nell'eliminazione del documento");
        }

        return new GestoreResponse($this->redirect($opzioni["url_indietro"]));
    }

    public function generaPdf($comunicazione_risposta) {
        return $this->generaPdfComunicazioneRisposta($comunicazione_risposta, "@Istruttorie/RispostaComunicazioneProgetto/pdfRispostaComunicazione.html.twig", array(), false, false);
    }

    protected function generaPdfComunicazioneRisposta($comunicazione_risposta, $twig, $datiAggiuntivi = array(), $facsimile = true, $download = true) {

        if (!$comunicazione_risposta->getStato()->uguale(StatoComunicazioneProgetto::COM_INSERITA)) {
            throw new SfingeException("Impossibile generare il pdf della richiesta nello stato in cui si trova");
        }

        $pdf = $this->container->get("pdf");

        $dati['comunicazione_risposta'] = $comunicazione_risposta;
        $dati['richiesta'] = $comunicazione_risposta->getRichiesta();
        $dati['facsimile'] = $facsimile;
        $isFsc = $this->container->get("gestore_richieste")->getGestore($comunicazione_risposta->getRichiesta()->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;

        $pdf->load($twig, $dati);

        if ($download) {
            return $pdf->download($this->getNomePdfComunicazioneRisposta($comunicazione_risposta));
        } else {
            return $pdf->binaryData();
        }
    }

    protected function getNomePdfComunicazioneRisposta($comunicazione_risposta) {
        $date = new \DateTime();
        $data = $date->format('d-m-Y');
        return "Risposta comunicazione esito " . $comunicazione_risposta->getId() . " " . $data;
    }

    public function inviaRisposta($comunicazione_risposta, $opzioni = array()) {
        if ($comunicazione_risposta->getStato()->uguale(StatoComunicazioneProgetto::COM_FIRMATA)) {
            try {
                //Avvio la transazione
                $this->getEm()->beginTransaction();
                $comunicazione_risposta->setData(new \DateTime());
                $this->container->get("sfinge.stati")->avanzaStato($comunicazione_risposta, StatoComunicazioneProgetto::COM_INVIATA_PA);
                $this->getEm()->flush();


                /* Popolamento tabelle protocollazione
                 * - richieste_protocollo
                 * - richieste_protocollo_documenti
                 */

                if ($this->container->getParameter("stacca_protocollo_al_volo")) {
                    $this->container->get("docerinitprotocollazione")->setTabProtocollazioneRispostaComunicazioneProgetto($comunicazione_risposta);
                }
                $this->getEm()->flush();
                $this->getEm()->commit();
            } catch (\Exception $ex) {
                //Effettuo il rollback
                $this->getEm()->rollback();
                throw new SfingeException('Errore nell\'invio della risposta dell\'integrazione');
            }

            return new GestoreResponse($this->redirect($opzioni['url_indietro']));
        }
        throw new SfingeException("Stato non valido per effettuare l'invio");
    }

    public function creaComunicazioneProgetto($oggetto, $tipo_oggetto) {

        $em = $this->getEm();
        $comunicazione = new \IstruttorieBundle\Entity\ComunicazioneProgetto();
        $procedura = $oggetto->getProcedura();
        $responsabile = $procedura->getResponsabile()->getPersona();
        try {
            if ($tipo_oggetto == 'RICHIESTA') {
                $comunicazione->setRichiesta($oggetto);
            }
            if ($tipo_oggetto == 'VARIAZIONE') {
                $comunicazione->setVariazione($oggetto);
            }
            $comunicazione->setTipoOggetto($tipo_oggetto);
            /*             * * TESTO DELL'EMAIL DI DEFAULT ** */
            $testo_default = 'Con la presente si trasmette la documentazione allegata. '
                    . PHP_EOL . 'Il responsabile del procedimento ' . $responsabile->getNome() . " " . $responsabile->getCognome();
            $comunicazione->setTestoEmail($testo_default);
            $comunicazione->setData(new \DateTime());
            $comunicazione->setRispondibile(false);
            $this->container->get("sfinge.stati")->avanzaStato($comunicazione, \BaseBundle\Entity\StatoComunicazioneProgetto::COM_INSERITA);
            $em->persist($comunicazione);
            $em->flush();
        } catch (\Exception $e) {
            throw new SfingeException($e->getMessage());
        }
        return new GestoreResponse($this->generaUrlDaTipo($oggetto, $tipo_oggetto));
    }

    public function gestioneComunicazioneProgetto($comunicazione) {

        $em = $this->getEm();

        if ($comunicazione->getTipoOggetto() == 'RICHIESTA') {
            $istruttoria = $comunicazione->getRichiesta()->getIstruttoria();
            $indietro = $this->generateUrl("elenco_comunicazioni", array("id_istruttoria" => $istruttoria->getId()));
        }

        if ($comunicazione->getTipoOggetto() == 'VARIAZIONE') {
            $variazione = $comunicazione->getVariazione();
            $indietro = $this->generateUrl("elenco_comunicazioni_variazione", array("id_variazione" => $variazione->getId()));
        }

        $documento_comunicazione = new \IstruttorieBundle\Entity\ComunicazioneProgettoDocumento();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();
        $documento_comunicazione->setDocumentoFile($documento_file);
        $documento_comunicazione->setComunicazione($comunicazione);

        $documenti_caricati = $comunicazione->getDocumentiComunicazione();

        $listaTipi = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByCodice(TipologiaDocumento::COMUNICAZIONE_RICHIESTA_ALLEGATO);

        // Se lo stato è inviato/protocollato
        $disabilita_azioni = ($comunicazione->getStato() != \BaseBundle\Entity\StatoComunicazioneProgetto::COM_INSERITA);

        if ($disabilita_azioni) {
            $msg = $comunicazione->getStato()->getDescrizione();
            if (($comunicazione->getProtocolloComunicazione() != '-') && !is_null($comunicazione->getDataProtocolloComunicazione())) {
                $msg .= ' [Protocollo N° ' . $comunicazione->getProtocolloComunicazione() . ' del ' . date_format($comunicazione->getDataProtocolloComunicazione(), 'd/m/Y') . ']';
            }
            $this->addFlash("success", $msg);
        }

        $request = $this->getCurrentRequest();
        if (count($listaTipi) > 0) {

            $opzioni_form_documenti["lista_tipi"] = $listaTipi;
            $opzioni_form_documenti["url_indietro"] = $indietro;
            $opzioni_form_documenti["disabled"] = $disabilita_azioni;
            $form_documenti = $this->createForm('IstruttorieBundle\Form\ComunicazioneProgettoDocumentoType', $documento_comunicazione, $opzioni_form_documenti);

            $opzioni_form_esito["url_indietro"] = $indietro;
            $opzioni_form_esito["disabled"] = $disabilita_azioni;
            $form_esito = $this->createForm('IstruttorieBundle\Form\ComunicazioneProgettoType', $comunicazione, $opzioni_form_esito);

            if ($request->isMethod('POST')) {

                $form_documenti->handleRequest($request);
                $form_esito->handleRequest($request);

                if ($form_documenti->isSubmitted() && $form_documenti->isValid()) {
                    try {
                        $this->container->get("documenti")->carica($documento_file);
                        $em->persist($documento_comunicazione);
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
                        $em->persist($comunicazione);
                        $em->flush();

                        if ($form_esito->get("pulsanti")->get("pulsante_invio")->isClicked()) {

                            // INVIO

                            $em->beginTransaction();
                            $comunicazione->setDataInvio(new \DateTime());
                            $this->generaPdfComunicazioneProgetto($comunicazione);
                            $em->flush();

                            $this->container->get("sfinge.stati")->avanzaStato($comunicazione, \BaseBundle\Entity\StatoComunicazioneProgetto::COM_INVIATA_PA);
                            $em->flush();

                            if ($this->container->getParameter("stacca_protocollo_al_volo")) {

                                $richiestaProtocollo = $this->container->get("docerinitprotocollazione")->setTabProtocollazioneComunicazioneProgetto($comunicazione);
                                $em->flush();

                                /**
                                 * schedulo un invio email per protocollazione in uscita tramite egrammata
                                 * l'email verrà mandata solo dopo che lo stato della richiestaProtocollo evolverà in POST_PROTOCOLLAZIONE
                                 * Questo blocco andrà riportato negli eventuali GestoriIstruttoriaBando scritti ad hoc che faranno 
                                 * l'overwrite del metodo creaIntegrazione 
                                 */
                                /**                                 * ********************************************************************** * */
                                if (!$this->schedulaEmailProtocollo($richiestaProtocollo)) {
                                    throw new \Exception('Errore durante la schedulazione dell\'EmailProtocollo');
                                }
                                /**                                 * ********************************************************************** * */
                            }

                            $em->commit();
                            $this->addFlash("success", "Comunicazione progetto inviata con successo.");

                            $disabilita_azioni = ($comunicazione->getStato() != \BaseBundle\Entity\StatoComunicazioneProgetto::COM_INSERITA);

                            $opzioni_form_documenti["lista_tipi"] = $listaTipi;
                            $opzioni_form_documenti["url_indietro"] = $indietro;
                            $opzioni_form_documenti["disabled"] = $disabilita_azioni;
                            $form_documenti = $this->createForm('IstruttorieBundle\Form\ComunicazioneProgettoDocumentoType', $documento_comunicazione, $opzioni_form_documenti);

                            $opzioni_form_esito["url_indietro"] = $indietro;
                            $opzioni_form_esito["disabled"] = $disabilita_azioni;
                            $form_esito = $this->createForm('IstruttorieBundle\Form\ComunicazioneProgettoType', $comunicazione, $opzioni_form_esito);
                        }

                        if ($form_esito->get("pulsanti")->get("pulsante_submit")->isClicked()) {
                            // SALVA
                            $this->addFlash("success", "Comunicazione progetto salvata con successo.");
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
            "comunicazione" => $comunicazione,
            "menu" => 'comunicazioni',
            "documenti" => $documenti_caricati,
            "form_documenti" => $form_documenti_view,
            "form_esito" => $form_esito_view,
            "url_indietro" => $indietro,
            "disabilita_azioni" => $disabilita_azioni,
            "documenti_richiesti" => $listaTipi
        );

        if ($comunicazione->getTipoOggetto() == 'RICHIESTA') {
            $dati['istruttoria'] = $istruttoria;
            $response = $this->render("IstruttorieBundle:Istruttoria:comunicazioneProgetto.html.twig", $dati);
        }

        if ($comunicazione->getTipoOggetto() == 'VARIAZIONE') {
            $dati['variazione'] = $variazione;
            $response = $this->render("AttuazioneControlloBundle:Istruttoria:Variazioni/comunicazioneVariazione.html.twig", $dati);
        }

        return new GestoreResponse($response);
    }

    public function generaFacsimileComunicazioneProgetto($comunicazione) {

        $download = true;
        $pdf = $this->container->get("pdf");
        $dati['comunicazione'] = $comunicazione;
        $dati['facsimile'] = true;
        $dati['documenti'] = $comunicazione->getDocumentiComunicazione();
        $isFsc = $this->container->get("gestore_richieste")->getGestore($comunicazione->getRichiesta()->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;
        $twig = "IstruttorieBundle:Istruttoria:pdfComunicazioneProgetto.html.twig";
        $pdf->load($twig, $dati);
        //return $this->render($twig,$dati);

        if ($comunicazione->getTipoOggetto() == 'RICHIESTA') {
            $richiesta = $comunicazione->getRichiesta();
        }
        if ($comunicazione->getTipoOggetto() == 'VARIAZIONE') {
            $richiesta = $comunicazione->getVariazione()->getRichiesta();
        }

        $isFsc = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;

        if ($download) {
            $date = new \DateTime();
            $nome_file = "Comunicazione_progetto_{$richiesta->getId()}_{$date->format('Y-m-d')}.pdf";
            $pdf->download($nome_file);
            return new Response();
        } else {
            return $pdf->binaryData();
        }
    }

    public function generaPdfComunicazioneProgetto($comunicazione) {
        $pdf = $this->container->get("pdf");

        $dati['comunicazione'] = $comunicazione;
        $dati['facsimile'] = false;
        $dati['documenti'] = $comunicazione->getDocumentiComunicazione();
        $isFsc = $this->container->get("gestore_richieste")->getGestore($comunicazione->getRichiesta()->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;

        $twig = "IstruttorieBundle:Istruttoria:pdfComunicazioneProgetto.html.twig";
        $pdf->load($twig, $dati);

        if ($comunicazione->getTipoOggetto() == 'RICHIESTA') {
            $richiesta = $comunicazione->getRichiesta();
        }
        if ($comunicazione->getTipoOggetto() == 'VARIAZIONE') {
            $richiesta = $comunicazione->getVariazione()->getRichiesta();
        }

        $data = $pdf->binaryData();
        $tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice(TipologiaDocumento::COMUNICAZIONE_PROGETTO_RICHIESTA);
        $data_corrente = new \DateTime();
        $documentoRichiesta = $this->container->get("documenti")->caricaDaByteArray($data, "Comunicazione_progetto_{$richiesta->getId()}_{$data_corrente->format('Y-m-d')}.pdf", $tipoDocumento);

        $comunicazione->setDocumento($documentoRichiesta);
    }

    /**
     * Schedula l'invio di una email tramite egrammata creando un oggetto EmailProtocollo associato alla richiesta protocollo.
     *
     * N.B. Ogni classe figlia di RichiestaProtocollo per cui viene scedulata un invio email DEVE implementare la EmailSendableInterface
     *
     * @param RichiestaProtocollo $richiestaProtocollo
     *
     * @return bool
     */
    protected function schedulaEmailProtocollo($richiestaProtocollo) {
        /* @var $egrammataService \ProtocollazioneBundle\Service\EGrammataWsService */
        $egrammataService = $this->container->get('egrammata_ws');

        return $egrammataService->creaEmailProtocollo($richiestaProtocollo);
    }

    protected function generaUrlDaTipo($oggetto, $tipo_oggetto) {
        switch ($tipo_oggetto) {
            case 'RICHIESTA':
                return $this->redirectToRoute("elenco_comunicazioni", array('id_istruttoria' => $oggetto->getIstruttoria()->getId()));
            case 'VARIAZIONE':
                return $this->redirectToRoute("elenco_comunicazioni_variazione", array('id_variazione' => $oggetto->getId()));
        }
    }

}
