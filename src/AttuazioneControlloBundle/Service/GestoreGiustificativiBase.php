<?php

namespace AttuazioneControlloBundle\Service;

use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use BaseBundle\Exception\SfingeException;
use DateTime;
use RichiesteBundle\Utility\EsitoValidazione;
use AttuazioneControlloBundle\Entity\StatoPagamento;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo;
use Doctrine\Common\Collections\Collection;
use DocumentoBundle\Component\ResponseException;
use RichiesteBundle\Entity\VocePianoCosto;

class GestoreGiustificativiBase extends AGestoreGiustificativi {

    public function aggiungiGiustificativo($id_pagamento) {

        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getRichiesta();
        $procedura = $richiesta->getProcedura();

        if ($pagamento->isRichiestaDisabilitata()) {
            return $this->addErrorRedirect("Il pagamento è disabilitato", "elenco_giustificativi", array('id_pagamento' => $id_pagamento));
        }

        $giustificativo = new \AttuazioneControlloBundle\Entity\GiustificativoPagamento();
        $giustificativo->setPagamento($pagamento);

        $dati = array();

        $dati["documento_caricato"] = false;
        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($procedura);

        if ($rendicontazioneProceduraConfig->hasSpesePersonale() == true) {
            $tipologia_documento = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array("codice" => "GIUSTIFICATIVO_CON_SP"));
        } else {
            $tipologia_documento = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array("codice" => "GIUSTIFICATIVO"));
        }
        $documento = new \DocumentoBundle\Entity\DocumentoFile();
        $documento->setTipologiaDocumento($tipologia_documento);
        $giustificativo->setDocumentoGiustificativo($documento);

        if ($rendicontazioneProceduraConfig->getRendicontazioneMultiProponente() || $richiesta->getProcedura()->isMultiPianoCosto()) {
            $proponenti = $richiesta->getProponenti();
            $dati["proponenti"] = $proponenti;

            // se ce n'è solo uno los ettiamo di default
            if (count($proponenti) == 1) {
                $giustificativo->setProponente($proponenti[0]);
            }
        }

        /**
         * se sono definite tipologie specifiche per la procedura prendo quelle, altrimento fetcho il set standard
         */
        $dati["tipologieGiustificativo"] = $em->getRepository("AttuazioneControlloBundle\Entity\TipologiaGiustificativo")->getTipologieGiustificativo($procedura);
        $dati['validation_groups'] = $this->getValidationGroupsFormGiustificativo();
        $dati["disabled"] = $pagamento->isRichiestaDisabilitata() || !$giustificativo->isModificabileIntegrazione();
        if ($rendicontazioneProceduraConfig->getSezioneContratti()) {
            $dati["url_indietro"] = $this->generateUrl("elenco_giustificativi_contratto", array("id_contratto" => $giustificativo->getContratto()->getId(), "id_pagamento" => $pagamento->getId()));
        } else {
            $dati["url_indietro"] = $this->generateUrl("elenco_giustificativi", array("id_pagamento" => $pagamento->getId()));
        }
        $dati["spese_personale"] = $rendicontazioneProceduraConfig->hasSpesePersonale();

        $form = $this->createForm("AttuazioneControlloBundle\Form\GiustificativoType", $giustificativo, $dati);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $istruttoria = $pagamento->getRichiesta()->getIstruttoria();

            $dateProgetto = $this->getDateProgetto($pagamento);

            // Se in fase di passaggio in ATC è stato stabilito che la data di avvio progetto è vincolante, non deve essere possibile
            // inserire una data giustificativo precedente alla data di avvio progetto
            if (!is_null($dateProgetto->dataAvvioProgetto) && $istruttoria->isDataInizioVincolante() && $giustificativo->getDataGiustificativo() < $dateProgetto->dataAvvioProgetto) {
                $messaggioErroreDataAvvioProgetto = $this->getMessaggioErroreDataAvvioProgetto($dateProgetto->dataAvvioProgetto);
                $form->get("data_giustificativo")->addError(new \Symfony\Component\Form\FormError($messaggioErroreDataAvvioProgetto));
            }

            if (!is_null($dateProgetto->dataTermineProgetto) && $giustificativo->getDataGiustificativo() > $dateProgetto->dataTermineProgetto) {
                $messaggioErroreDataTermineProgetto = $this->getMessaggioErroreDataTermineProgetto($dateProgetto->dataTermineProgetto);
                $form->get("data_giustificativo")->addError(new \Symfony\Component\Form\FormError($messaggioErroreDataTermineProgetto));
            }

            // per le spese di personale documento e cf sono opzionali
            $tipologia = $giustificativo->getTipologiaGiustificativo();
            if (is_null($tipologia) || !$tipologia->isTipologiaSpesePersonale()) {
                $docGiust = $giustificativo->getDocumentoGiustificativo();
                if (is_null($docGiust) || is_null($docGiust->getFile())) {
                    $form->get("documento_giustificativo")->get("file")->addError(new \Symfony\Component\Form\FormError("Nessun documento selezionato"));
                } /*elseif (!$giustificativo->getTipologiaGiustificativo()->isTipologiaFatturaElettronica() && $docGiust->isFileXml()) {
                    $form->get("documento_giustificativo")->get("file")->addError(new \Symfony\Component\Form\FormError("Formato del file non corretto per la tipologia di documento selezionata."));
                }*/

                $codiceFiscale = $giustificativo->getCodiceFiscaleFornitore();
                if (empty($codiceFiscale)) {
                    $form->get("codice_fiscale_fornitore")->addError(new \Symfony\Component\Form\FormError("Questo valore non dovrebbe essere nullo."));
                }
            }

            if ($form->isValid()) {
                try {
                    //$giustificativo->calcolaImportoRichiesto();
                    // l'importo richiesto viene poi alimentato dalla imputazioni che seguiranno
                    $giustificativo->setImportoRichiesto(0.00);

                    // nei casi di spese personale è stato richiesto di rendere opzionale il documento
                    $file = $giustificativo->getDocumentoGiustificativo()->getFile();
                    if (!is_null($file)) {
                        $this->container->get("documenti")->carica($documento);
                    } else {
                        $giustificativo->setDocumentoGiustificativo(null);
                    }
                    $em->persist($giustificativo);
                    $em->flush();
                    return $this->addSuccesRedirect("Il giustificativo è stato correttamente aggiunto", "dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati["form"] = $form->createView();
        $dati["hasPesonale"] = $rendicontazioneProceduraConfig->hasSpesePersonale();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        if ($rendicontazioneProceduraConfig->getSezioneContratti()) {
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco Contratti", $this->generateUrl("elenco_contratti", array("id_pagamento" => $id_pagamento)));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi contratto", $this->generateUrl("elenco_giustificativi_contratto", array("id_contratto" => $giustificativo->getContratto()->getId(), "id_pagamento" => $pagamento->getId())));
        } else {
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi", array("id_pagamento" => $pagamento->getId())));
        }
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Aggiungi giustificativo");

        return $this->render("AttuazioneControlloBundle:Giustificativi:aggiungiGiustificativo.html.twig", $dati);
    }

    protected function getValidationGroupsFormGiustificativo(): array {
        return ['Default'];
    }

    public function dettaglioGiustificativo($id_giustificativo) {
        $giustificativo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);
        $pagamento = $giustificativo->getPagamento();
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $annualita = $this->container->get("gestore_voci_piano_costo_giustificativo")->getGestore($richiesta->getProcedura())->getAnnualitaRendicontazione($richiesta);

        $dati = array("giustificativo" => $giustificativo, "annualita" => $annualita, "is_modifica_disabilitata" => false);
        $dati["is_modifica_disabilitata"] = $pagamento->isRichiestaDisabilitata() || !$giustificativo->isModificabileIntegrazione() || $giustificativo->getGiustificativoOrigine();

        $rendicontazioneproceduraConfig = $this->getRendicontazioneProceduraConfig($pagamento->getProcedura());
        $dati['avvisoSezioneGiustificativo'] = $rendicontazioneproceduraConfig->getAvvisoSezioneGiustificativo();
        $dati["rendicontazioneProceduraConfig"] = $rendicontazioneproceduraConfig;

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio giustificativo");

        return $this->render("AttuazioneControlloBundle:Giustificativi:dettaglioGiustificativo.html.twig", $dati);
    }

    public function eliminaGiustificativo($id_giustificativo) {
        $em = $this->getEm();
        /** @var GiustificativoPagamento $giustificativo */
        $giustificativo = $em->getRepository(GiustificativoPagamento::class)->find($id_giustificativo);
        $pagamento = $giustificativo->getPagamento();

        if (!$giustificativo->isModificabileIntegrazione()) {
            return $this->addErrorRedirect("Il giustificativo non è eliminabile perchè non in integrazione", "elenco_giustificativi", array("id_pagamento" => $pagamento->getId()));
        }

        if (\in_array($pagamento->getStato(), array(StatoPagamento::PAG_INVIATO_PA, StatoPagamento::PAG_PROTOCOLLATO))) {
            return $this->addErrorRedirect("L'operazione non è compatibile con lo stato del pagamento.", "elenco_giustificativi", array("id_pagamento" => $pagamento->getId()));
        }

        try {
            // Elimino documenti associati x integrità
            foreach ($giustificativo->getDocumentiGiustificativo() as $documento) {
                $giustificativo->removeDocumentiGiustificativo($documento);
                $em->remove($documento);
            }

            // Elimino le voci piano costo per ricalcolo spese
            foreach ($giustificativo->getVociPianoCosto() as $voce) {
                $giustificativo->removeVociPianoCosto($voce);
                $em->remove($voce);
            }
            $giustificativo->setIntegrazioneDi(null);

            $pagamento->removeGiustificativi($giustificativo);
            $em->remove($giustificativo);
            $em->flush();

            // se elimino un giustificativo di riflesso elimino tutte le eventuali imputazioni collegate
            // quindi devo effetturare il ricalcolo in cascata
            $pagamento->calcolaImportoRichiesto();


            $proponente = $giustificativo->getProponente();
            $esitoGenerali = $this->container->get("gestore_voci_piano_costo_giustificativo")->getGestore($pagamento->getProcedura())->gestioneGiustificativoSpeseGenerali($pagamento, $proponente);
            if ($esitoGenerali == false) {
                throw new \Exception('Errore nel calcolo delle spese generali');
            }

            $em->flush();

            $this->addSuccess("Il giustificativo è stato correttamente eliminato");
        } catch (\Exception $e) {
            $this->container->get('logger')->error($e->getTraceAsString());
            $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
        }

        return $this->redirectToRoute("elenco_giustificativi", array("id_pagamento" => $pagamento->getId()));
    }

    public function modificaGiustificativo($id_giustificativo) {

        $em = $this->getEm();

        $giustificativo = $em->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);
        $pagamento = $giustificativo->getPagamento();
        $richiesta = $pagamento->getRichiesta();
        $procedura = $richiesta->getProcedura();

        $proponenteOld = $giustificativo->getProponente();

        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($procedura);
        $dati = array();

        $dati["documento_caricato"] = true;
        if (is_null($giustificativo->getDocumentoGiustificativo())) {
            $dati["documento_caricato"] = false;
            if ($rendicontazioneProceduraConfig->hasSpesePersonale() == true) {
                $tipologia_documento = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array("codice" => "GIUSTIFICATIVO_CON_SP"));
            } else {
                $tipologia_documento = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array("codice" => "GIUSTIFICATIVO"));
            }
            $documento = new \DocumentoBundle\Entity\DocumentoFile();
            $documento->setTipologiaDocumento($tipologia_documento);
            $giustificativo->setDocumentoGiustificativo($documento);
            $path = null;
        } else {
            $documentoFile = $giustificativo->getDocumentoGiustificativo();
            $path = $this->container->get("funzioni_utili")->encid($documentoFile->getPath() . $documentoFile->getNome());
        }

        if ($rendicontazioneProceduraConfig->getRendicontazioneMultiProponente() || $richiesta->getProcedura()->isMultiPianoCosto()) {
            $dati["proponenti"] = $richiesta->getProponenti();
        }

        /**
         * se sono definite tipologie specifiche per la procedura prendo quelle, altrimento fetcho il set standard
         */
        $dati["tipologieGiustificativo"] = $em->getRepository("AttuazioneControlloBundle\Entity\TipologiaGiustificativo")->getTipologieGiustificativo($procedura);
        $dati['validation_groups'] = $this->getValidationGroupsFormGiustificativo();
        $dati["disabled"] = $pagamento->isRichiestaDisabilitata() || !$giustificativo->isModificabileIntegrazione();
        if ($rendicontazioneProceduraConfig->getSezioneContratti()) {
            $dati["url_indietro"] = $this->generateUrl("elenco_giustificativi_contratto", array("id_contratto" => $giustificativo->getContratto()->getId(), "id_pagamento" => $pagamento->getId()));
        } else {
            $dati["url_indietro"] = $this->generateUrl("elenco_giustificativi", array("id_pagamento" => $pagamento->getId()));
        }
        $dati["spese_personale"] = $rendicontazioneProceduraConfig->hasSpesePersonale();

        $form = $this->createForm("AttuazioneControlloBundle\Form\GiustificativoType", $giustificativo, $dati);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $istruttoria = $pagamento->getRichiesta()->getIstruttoria();

            $dateProgetto = $this->getDateProgetto($pagamento);

            // Se in fase di passaggio in ATC è stato stabilito che la data di avvio progetto è vincolante, non deve essere possibile
            // inserire una data giustificativo precedente alla data di avvio progetto
            if (!is_null($dateProgetto->dataAvvioProgetto) && $istruttoria->isDataInizioVincolante() && $giustificativo->getDataGiustificativo() < $dateProgetto->dataAvvioProgetto) {
                $messaggioErroreDataAvvioProgetto = $this->getMessaggioErroreDataAvvioProgetto($dateProgetto->dataAvvioProgetto);
                $form->get("data_giustificativo")->addError(new \Symfony\Component\Form\FormError($messaggioErroreDataAvvioProgetto));
            }

            if (!is_null($dateProgetto->dataTermineProgetto) && $giustificativo->getDataGiustificativo() > $dateProgetto->dataTermineProgetto) {
                $messaggioErroreDataTermineProgetto = $this->getMessaggioErroreDataTermineProgetto($dateProgetto->dataTermineProgetto);
                $form->get("data_giustificativo")->addError(new \Symfony\Component\Form\FormError($messaggioErroreDataTermineProgetto));
            }

            // per le spese di personale documento e cf sono opzionali
            $tipologia = $giustificativo->getTipologiaGiustificativo();
            if (is_null($tipologia) || !$tipologia->isTipologiaSpesePersonale()) {
                $docGiust = $giustificativo->getDocumentoGiustificativo();
                if (!$dati["documento_caricato"] && (is_null($docGiust) || is_null($docGiust->getFile()))) {
                    $form->get("documento_giustificativo")->get("file")->addError(new \Symfony\Component\Form\FormError("Nessun documento selezionato"));
                }
                /* elseif(!$giustificativo->getTipologiaGiustificativo()->isTipologiaFatturaElettronica() && $docGiust->isFileXml()) {
                  $form->get("documento_giustificativo")->get("file")->addError(new FormError("Formato del file non corretto per la tipologia di documento selezionata."));
                  } */

                $codiceFiscale = $giustificativo->getCodiceFiscaleFornitore();
                if (empty($codiceFiscale)) {
                    $form->get("codice_fiscale_fornitore")->addError(new \Symfony\Component\Form\FormError("Questo valore non dovrebbe essere nullo."));
                }
            }

            if ($form->isValid()) {
                try {
                    if ($dati["documento_caricato"] == false) {

                        // nei casi di spese personale è stato richiesto di rendere opzionale il documento
                        $file = $giustificativo->getDocumentoGiustificativo()->getFile();
                        if (!is_null($file)) {
                            $this->container->get("documenti")->carica($documento);
                        } else {
                            $giustificativo->setDocumentoGiustificativo(null);
                        }
                    }

                    /**
                     * nel caso del calcolo automatico delle spese generali presenti in caso di rendicontazione multiproponente e multipianocosto
                     * succede che in fase di imputazione viene creato automaticamente un giustificativo fittizio per ogni proponente al quale viene
                     * associata la vocespesagiustificativo relativa alle spese generali.
                     * Può succedere una cosa strana: ovvero l'utente crea un giustificativo associandolo ad uno dei proponenti, crea le relative imputazioni,
                     * il sistema calcola in automatico le spese generali per quel proponente; a questo punto l'utente dice 
                     * può pensare di aver sbagliato, questo giustificativo andava associato ad un altro proponente".
                     * Per cui bisogna ricalcolare le spese generali per il vecchio proponente e per il nuovo proponente
                     * 
                     */
                    $proponenteNew = $giustificativo->getProponente();
                    if ($rendicontazioneProceduraConfig->getRendicontazioneMultiProponente() && $procedura->getMultiPianoCosto()) {

                        // con questa condizione restringiamo il ricalcolo solo nel caso di cambio proponente
                        $this->riassociaVociPianoCostoGiustificativo($giustificativo);
                        if (!is_null($proponenteOld) && ($proponenteOld != $proponenteNew)) {
                            $gestoreVociPianoCostoGiustificativo = $this->container->get("gestore_voci_piano_costo_giustificativo")->getGestore($pagamento->getProcedura());

                            $esitoGeneraliProponenteOld = $gestoreVociPianoCostoGiustificativo->gestioneGiustificativoSpeseGenerali($pagamento, $proponenteOld);
                            if ($esitoGeneraliProponenteOld == false) {
                                throw new \Exception('Errore nel calcolo delle spese generali');
                            }

                            $esitoGeneraliProponenteNew = $gestoreVociPianoCostoGiustificativo->gestioneGiustificativoSpeseGenerali($pagamento, $proponenteNew);
                            if ($esitoGeneraliProponenteNew == false) {
                                throw new \Exception('Errore nel calcolo delle spese generali');
                            }
                        }
                    }

                    //$giustificativo->calcolaImportoRichiesto();

                    $em->flush();

                    return $this->addSuccesRedirect("Il giustificativo è stato salvato correttamente", "elenco_giustificativi", array("id_pagamento" => $pagamento->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati["form"] = $form->createView();
        $dati["giustificativo"] = $giustificativo;
        $dati["path"] = $path;
        $dati["is_saldo"] = $pagamento->getModalitaPagamento()->getCodice() == ModalitaPagamento::SALDO_FINALE;
        $dati["hasPesonale"] = $rendicontazioneProceduraConfig->hasSpesePersonale();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        if ($rendicontazioneProceduraConfig->getSezioneContratti()) {
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco Contratti", $this->generateUrl("elenco_contratti", array("id_pagamento" => $pagamento->getId())));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi contratto", $this->generateUrl("elenco_giustificativi_contratto", array("id_contratto" => $giustificativo->getContratto()->getId(), "id_pagamento" => $pagamento->getId())));
        } else {
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi", array("id_pagamento" => $pagamento->getId())));
        }
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Modifica giustificativo");

        return $this->render("AttuazioneControlloBundle:Giustificativi:modificaGiustificativo.html.twig", $dati);
    }

    protected function riassociaVociPianoCostoGiustificativo(GiustificativoPagamento $giustificativo): void {
        $proponenteGiustificativo = $giustificativo->getProponente();
        /** @var Collection|VocePianoCostoGiustificativo[] $vociDisassociate */
        $vociDisassociate = $giustificativo->getVociPianoCosto()->filter(Function(VocePianoCostoGiustificativo $voce) use($proponenteGiustificativo) {
            $proponenteVocePianoCosto = $voce->getVocePianoCosto()->getProponente();
            return $proponenteGiustificativo != $proponenteVocePianoCosto;
        });

        foreach ($vociDisassociate as $voce) {
            $piano = $voce->getVocePianoCosto()->getPianoCosto();
            $voceCorretta = $proponenteGiustificativo->getVociPianoCosto()->filter(function(VocePianoCosto $voce) use($piano) {
                        return $voce->getPianoCosto() == $piano;
                    })->first();
            if (false === $voceCorretta) {
                throw new SfingeException('Impossibile trovare voce piano costo corretta');
            }
            $voce->setVocePianoCosto($voceCorretta);
            $voce->setVocePianoCostoIstruttoria($voceCorretta->getIstruttoria());
        }
    }

    public function elencoGiustificativi($id_pagamento) {
        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getRichiesta();

        $dati = array();
        $dati["pagamento"] = $pagamento;
        $dati["is_aggiungi_disabilitato"] = $pagamento->isRichiestaDisabilitata();

        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($richiesta->getProcedura());
        $dati["rendicontazioneProceduraConfig"] = $rendicontazioneProceduraConfig;
        $dati['giustificativi'] = $em->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->getGiustificativiByPagamento($id_pagamento);

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi");

        return $this->render("AttuazioneControlloBundle:Giustificativi:elencoGiustificativi.html.twig", $dati);
    }

    public function elencoGiustificativiContratto($id_contratto, $id_pagamento) {
        $em = $this->getEm();
        $contratto = $em->getRepository("AttuazioneControlloBundle\Entity\Contratto")->find($id_contratto);
        $pagamento = $contratto->getPagamento();
        $richiesta = $pagamento->getRichiesta();

        $dati = array();
        $dati["contratto"] = $contratto;
        $dati["pagamento"] = $pagamento;
        $dati["is_aggiungi_disabilitato"] = $pagamento->isRichiestaDisabilitata();

        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($richiesta->getProcedura());
        $dati["rendicontazioneProceduraConfig"] = $rendicontazioneProceduraConfig;
        $dati['giustificativi'] = $em->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->getGiustificativiByContratto($id_contratto);

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $id_pagamento)));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco Contratti", $this->generateUrl("elenco_contratti", array("id_pagamento" => $id_pagamento)));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi");

        return $this->render("AttuazioneControlloBundle:Giustificativi:elencoGiustificativi.html.twig", $dati);
    }

    // elimina il documento principale del giustificativo (dalla relazione DocumentoGiustificativo)
    public function eliminaDocumentoGiustificativo($id_documento_giustificativo, $id_giustificativo) {

        $em = $this->getEm();

        $documento = $em->getRepository("DocumentoBundle\Entity\DocumentoFile")->find($id_documento_giustificativo);
        $giustificativo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);
        $pagamento = $giustificativo->getPagamento();

        if (!$giustificativo->isModificabileIntegrazione()) {
            return $this->addErrorRedirect("Il documento non è eliminabile perchè il giustificativo non è in integrazione", "modifica_giustificativo", array("id_giustificativo" => $id_giustificativo));
        }

        if ($pagamento->isPagamentoDisabilitato()) {
            return $this->addErrorRedirect("L'operazione non è compatibile con lo stato del pagamento.", "modifica_giustificativo", array("id_giustificativo" => $id_giustificativo));
        }

        try {
            $giustificativo->setDocumentoGiustificativo(null);
            $em->remove($documento);
            $em->flush();
            return $this->addSuccesRedirect("Il documento è stato correttamente eliminato", "modifica_giustificativo", array("id_giustificativo" => $id_giustificativo));
        } catch (ResponseException $e) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "modifica_giustificativo", array("id_giustificativo" => $id_giustificativo));
        }
    }

    public function validaGiustificativo($giustificativo) {
        $esito = new EsitoValidazione(true);

        $esitoQuietanze = $this->validaQuietanzeGiustificativo($giustificativo);
        if ($esitoQuietanze->getEsito() == false) {
            $esito->setEsito(false);
            $errori = $esitoQuietanze->getMessaggiSezione();
            foreach ($errori as $errore) {
                $esito->addMessaggioSezione($errore);
            }
        }

        $esitoVoci = $this->validaVociSpesaGiustificativo($giustificativo);
        if ($esitoVoci->getEsito() == false) {
            $esito->setEsito(false);
            $errori = $esitoVoci->getMessaggiSezione();
            foreach ($errori as $errore) {
                $esito->addMessaggioSezione($errore);
            }
        }

        // per i giustificativi di tipo spese personale è stata rilassata l'obbligatorietà del caricamento del pdf relativo al giustificativo
        if (is_null($giustificativo->getDocumentoGiustificativo()) && !$giustificativo->getTipologiaGiustificativo()->isTipologiaSpesePersonale()) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione('Non è stato caricato il documento per il giustificativo ' . $giustificativo->getNumeroGiustificativo());
        }

        $esitoDocumenti = $this->validaDocumenti($giustificativo);
        if ($esitoDocumenti->getEsito() == false) {
            $esito->setEsito(false);
            $errori = $esitoDocumenti->getMessaggi();
            $esito->addMessaggioSezione('Non sono stati caricati tutti i documenti richiesti per il giustificativo ' . $giustificativo->getNumeroGiustificativo());
            foreach ($errori as $errore) {
                $esito->addMessaggio($errore);
            }
        }

        return $esito;
    }

    public function validaQuietanzeGiustificativo($giustificativo) {
        $esito = new EsitoValidazione(true);

        $quietanze = $giustificativo->getQuietanze();
        //Verifico la presenza di quitanze nel pagamento
        if (count($quietanze) == 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Non sono state inserite quietanze per il giustificativo " . $giustificativo->getNumeroGiustificativo());
            return $esito;
        }

//		$importoTotale = 0.00;
//
//		foreach ($quietanze as $quietanza) {
//			$importoTotale += $quietanza->getImporto();
//		}
//
//		if ($importoTotale != $giustificativo->getImportoGiustificativo()) {
//			$esito->setEsito(false);
//			$esito->addMessaggioSezione("L'importo delle quietanze è diverso dall'importo del giustificativo " . $giustificativo->getNumeroGiustificativo());
//		}

        foreach ($quietanze as $quietanza) {
            if (is_null($quietanza->getDocumentoQuietanza())) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione("Per una o più quietanze non è stato caricato il documento");
                break;
            }
        }

        return $esito;
    }

    public function validaVociSpesaGiustificativo($giustificativo) {
        $esito = new EsitoValidazione(true);

        $voci = $giustificativo->getVociPianoCosto();
        //Verifico la presenza di voci spesa nel pagamento
        if (count($voci) == 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Non sono state imputate voci spesa per il giustificativo " . $giustificativo->getNumeroGiustificativo());
        }

        $totaleGiustificativo = $giustificativo->getImportoGiustificativo();
        $tt = $giustificativo->getImportoRichiesto();
        $totaleImputato = 0.00;
        foreach ($voci as $voce) {
            $totaleImputato += $voce->getImporto();
        }

        if (round($totaleImputato, 2) > round($totaleGiustificativo, 2)) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("E' stato richiesto un importo superiore a quello totale del giustificativo " . $giustificativo->getNumeroGiustificativo());
        }

        return $esito;
    }

    public function elencoDocumenti($id_giustificativo, $opzioni = array(), $pagamento_rif = null) {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();
        $giustificativo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);
        $pagamento = $giustificativo->getPagamento();

        $documento_giustificativo = new \AttuazioneControlloBundle\Entity\DocumentoGiustificativo();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();

        $documenti_caricati = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoGiustificativo")->findBy(array("giustificativo_pagamento" => $id_giustificativo));

        $richiesta = $giustificativo->getPagamento()->getRichiesta();
        $listaTipi = $this->getTipiDocumenti($giustificativo, 0);

        $codiceGiustificativo = $giustificativo->getTipologiaGiustificativo()->getCodice();
        if ($codiceGiustificativo == '2' || $codiceGiustificativo = '3') {
            if (!is_null($giustificativo->getEstensione()) && !is_null($giustificativo->getEstensione()->getRicercatore()) && $giustificativo->getEstensione()->getRicercatore()->getRendicontataInSal()) {
                // se la persona è stata già rendicontata la dichiarazione del costo orario non è obbligatorio:
                foreach ($listaTipi as $index => $l) {
                    if ($l->getCodice() == 'DICH_COSTO_ORARIO') {
                        $listaTipi[$index]->setObbligatorio(false);
                    }
                }
            }
        }

        if (count($listaTipi) > 0 && !$pagamento->isRichiestaDisabilitata()) {

            $opzioni_form["lista_tipi"] = $listaTipi;
            $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
            $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Salva'));

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {

                        $this->container->get("documenti")->carica($documento_file, 0, $richiesta);

                        $documento_giustificativo->setDocumentoFile($documento_file);
                        $documento_giustificativo->setGiustificativoPagamento($giustificativo);
                        $em->persist($documento_giustificativo);

                        $em->flush();
                        return $this->addSuccesRedirect("Documento caricato correttamente", "elenco_documenti_giustificativo", is_null($pagamento_rif) ? array("id_giustificativo" => $id_giustificativo) : array("id_giustificativo" => $id_giustificativo, 'id_pagamento_rif' => $pagamento_rif->getId()));
                    } catch (\Exception $e) {
                        return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "elenco_documenti_giustificativo", is_null($pagamento_rif) ? array("id_giustificativo" => $id_giustificativo) : array("id_giustificativo" => $id_giustificativo, 'id_pagamento_rif' => $pagamento_rif->getId()));
                    }
                }
            }
            $form_view = $form->createView();
        } else {
            $form_view = null;
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => is_null($pagamento_rif) ? $pagamento->getId() : $pagamento_rif->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi", array("id_pagamento" => is_null($pagamento_rif) ? $pagamento->getId() : $pagamento_rif->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco documenti");

        $nascondi_elimina = false;
        if (!is_null($pagamento_rif) && $giustificativo->getPagamento()->getId() != $pagamento_rif->getId()) {
            $di_cui = $em->getRepository("AttuazioneControlloBundle\Entity\DiCui")->findBy(array("pagamento_provenienza" => $giustificativo->getPagamento(), "pagamento_destinazione" => $pagamento_rif));
            foreach ($di_cui as $dc) {
                if ($dc->getVocePianoCostoGiustificativo()->getGiustificativoPagamento()->getId() == $giustificativo->getId()) {
                    $nascondi_elimina = true;
                }
            }
        }

        if (!is_null($pagamento_rif) && ($pagamento_rif->getModalitaPagamento()->getCodice() == ModalitaPagamento::SALDO_FINALE)) {
            foreach ($giustificativo->getVociPianoCosto() as $vocePianoCosto) {
                if (!is_null($vocePianoCosto->getNotaSuperamentoMassimali())) {
                    $nascondi_elimina = true;
                }
            }
        }

        $dati = array(
            "documenti" => $documenti_caricati,
            "giustificativo" => $giustificativo,
            "id_pagamento" => is_null($pagamento_rif) ? $giustificativo->getPagamento()->getId() : $pagamento_rif->getId(),
            "nascondi_elimina" => $nascondi_elimina,
            "form" => $form_view,
            "is_saldo" => ($pagamento->getModalitaPagamento()->getCodice() == ModalitaPagamento::SALDO_FINALE),
            'is_richiesta_disabilitata' => false,
            "is_ben_scorr_saldo" => $this->container->get("gestore_pagamenti")->getGestore($pagamento->getRichiesta()->getProcedura())->isBeneficiarioScorrimento((is_null($pagamento_rif) ? $giustificativo->getPagamento() : $pagamento_rif), ModalitaPagamento::SALDO_FINALE, null),
        );
        return $this->render("AttuazioneControlloBundle:Giustificativi:elencoDocumenti.html.twig", $dati);
    }

    // RENDICONTAZIONE STANDARD
    public function getTipiDocumenti($giustificativo, $solo_obbligatori, $isValidazione = false) {
        $richiesta = $giustificativo->getPagamento()->getRichiesta();
        $procedura = $richiesta->getProcedura();
        $res = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaTipiDocumentiGiustificativoStandard($giustificativo, $procedura->getId(), $solo_obbligatori);

        return $res;
    }

    // elimina il documento indicato facente parte della collection del giustificativo (dalla relazione DocumentiGiustificativo)
    public function eliminaDocumentoGiustificativo2($id_documento_giustificativo) {

        $redirectAction = 'dettaglio_giustificativo';

        $em = $this->getEm();

        $documento = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoGiustificativo")->find($id_documento_giustificativo);
        $giustificativo = $documento->getGiustificativoPagamento();
        $pagamento = $giustificativo->getPagamento();

        $id_giustificativo = $giustificativo->getId();
        if (!$giustificativo->isModificabileIntegrazione()) {
            return $this->addErrorRedirect("Il documento non è eliminabile perchè il giustificativo non è in integrazione", $redirectAction, array("id_giustificativo" => $id_giustificativo));
        }

        if ($pagamento->isPagamentoDisabilitato()) {
            return $this->addErrorRedirect("L'operazione non è compatibile con lo stato del pagamento.", $redirectAction, array("id_giustificativo" => $id_giustificativo));
        }

        try {
            $em->remove($documento->getDocumentoFile());
            $em->remove($documento);
            $em->flush();

            return $this->addSuccesRedirect("Documento caricato eliminato", $redirectAction, array("id_giustificativo" => $giustificativo->getId()));
        } catch (ResponseException $e) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "elenco_documenti_giustificativo", array("id_giustificativo" => $id_giustificativo));
        }
    }

    // RENDICONTAZIONE STANDARD!!!
    // per bandi 7 e 8 è stata spostata nei gestori specifici
    public function validaDocumenti($giustificativo, $opzioni = array()) {
        $esito = new EsitoValidazione(true);

        $pagamento = $giustificativo->getPagamento();

        $documenti_obbligatori = $this->getTipiDocumenti($giustificativo, true, true);

        foreach ($documenti_obbligatori as $documento) {
            $esito->addMessaggio('Caricare il documento ' . $documento->getDescrizione());
        }

        if (count($documenti_obbligatori) > 0) {
            $esito->setEsito(false);
        }

        return $esito;
    }

    public function gestioneDocumentiPersonale($id_pagamento, $opzioni = array()) {
        throw new \Exception("Implementato soltanto per dei bandi specifici");
    }

    public function documentiAmministrativiVoce($id_giustificativo) {
        throw new \Exception("Implementato soltanto per dei bandi specifici");
    }

    // avanzamento rendicontazione standard
    public function avanzamentoPianoCosti($id_pagamento) {

        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getRichiesta();
        $proponente = null;
        $dati = array();

        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($richiesta->getProcedura());

        if ($rendicontazioneProceduraConfig->getRendicontazioneMultiProponente()) {
            //$proponenti = $richiesta->getProponenti();
            // dava errore..ho fatto repository che si tira gli eventuali proponenti associati alle voci del piano costi
            //altrimenti nel caso di multiproponenza non multipianocosto scazzava (giustamente perchè non è definito un pc per tutti i proponenti)
            $proponenti = $this->getEm()->getRepository('RichiesteBundle\Entity\VocePianoCosto')->getProponentiPianoCosti($richiesta->getId());

            // ha senso scegliere solo in caso di multi-proponenza in multi-pianocosto
            // altrimenti il piano costo è unico
            if (count($proponenti) > 1) {
                $formData = new \stdClass();
                $formData->proponente = $richiesta->getMandatario();

                $formBuilder = $this->createFormBuilder($formData);
                $formBuilder->add('proponente', \BaseBundle\Form\CommonType::entity, array(
                    'class' => 'RichiesteBundle:Proponente',
                    'choice_label' => function ($proponente) {
                        return $proponente;
                    },
                    'choices' => $proponenti,
                    'required' => false,
                    'placeholder' => 'Tutti', // con nuovo upgrade della select2 il placeholder non va più  
                ));

                $formBuilder->add('submit', \BaseBundle\Form\CommonType::submit, array('label' => 'vai'));

                $form = $formBuilder->getForm();

                $request = $this->getCurrentRequest();
                if ($request->isMethod('POST')) {
                    $form->handleRequest($request);

                    $proponente = $formData->proponente;
                    // non ha senso calcolare il totale se è multipianocosto
                    // quindi se non seleziona nulla rimappiamo sul mandatario
                    // commento perché non ha senso sto codice in quanto se proponente è null o è un sigolo piano costi o è totale
                    /* if($pagamento->getProcedura()->getMultiPianoCosto()){
                      $proponente = $richiesta->getMandatario();
                      } */
                }
            }
        } else {
            $proponente = $richiesta->getMandatario();
        }

        if ($pagamento->isInviatoRegione()) {
            $avanzamento = $this->calcolaAvanzamentoPianoCosti($richiesta, $proponente, $pagamento);
        } else {
            $avanzamento = $this->calcolaAvanzamentoPianoCosti($richiesta, $proponente);
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Avanzamento piano costi");

        $atc = $pagamento->getAttuazioneControlloRichiesta();
        $pagamenti = array();

        foreach ($atc->getPagamenti() as $p) {

            // rimuoviamo eventuale anticipo che non fa testo
            if (!$p->getModalitaPagamento()->isAnticipo()) {
                $pagamenti[] = $p;
            }
        }
        $dati["avanzamento"] = $avanzamento;
        $dati["pagamento"] = $pagamento;
        $dati["richiesta"] = $richiesta;
        $dati["proponente"] = $proponente;
        $dati["pagamenti"] = $pagamenti;

        $dati["rendicontazioneProceduraConfig"] = $rendicontazioneProceduraConfig;

        $dati["menu"] = "rendicontazione";

        if ($pagamento->isInviatoRegione()) {
            $variazione = $pagamento->getAttuazioneControlloRichiesta()->getUltimaVariazionePianoCostiPA($pagamento);
        } else {
            $variazione = $pagamento->getAttuazioneControlloRichiesta()->getUltimaVariazioneApprovata();
        }
        if (!is_null($variazione)) {
            $investimentoTotaleAmmesso = $variazione->getCostoAmmesso();
            $contributoTotaleConcesso = $variazione->getContributoAmmesso();
        } else {
            $istruttoriaRichiesta = $richiesta->getIstruttoria();
            $investimentoTotaleAmmesso = $istruttoriaRichiesta->getCostoAmmesso();
            $contributoTotaleConcesso = $istruttoriaRichiesta->getContributoAmmesso();
        }

        $dati['investimentoTotaleAmmesso'] = $investimentoTotaleAmmesso;
        $dati['contributoTotaleConcesso'] = $contributoTotaleConcesso;

        $totaleRendicontato = 0.0;
        foreach ($pagamenti as $pagamento) {
            $totaleRendicontato += $pagamento->getImportoTotaleRichiesto();
        }

        $dati["importoTotaleRichiesto"] = $totaleRendicontato;


        // il contributo si dovrà calcolare applicando la formula del contributo sull'importo totale richiesto

        if ($rendicontazioneProceduraConfig->getRendicontazioneMultiProponente()) {
            if (isset($form)) {
                $dati["form"] = $form->createView();
            }
        }

        return $this->render("AttuazioneControlloBundle:Giustificativi:avanzamentoPianoCosti.html.twig", $dati);
    }

    protected function getRendicontazioneProceduraConfig($procedura) {

        $rendicontazioneProceduraConfig = $procedura->getRendicontazioneProceduraConfig();
        // fallback..default
        if (is_null($rendicontazioneProceduraConfig)) {
            $rendicontazioneProceduraConfig = new \AttuazioneControlloBundle\Entity\RendicontazioneProceduraConfig();
        }

        return $rendicontazioneProceduraConfig;
    }

    public function aggiungiDocumentoGiustificativo($id_giustificativo) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();
        $giustificativo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);
        $pagamento = $giustificativo->getPagamento();

        $documento_giustificativo = new \AttuazioneControlloBundle\Entity\DocumentoGiustificativo();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();

        $richiesta = $giustificativo->getPagamento()->getRichiesta();

        $listaTipi = $this->getTipiDocumentiGiustificativoCaricabili($giustificativo);

        if (count($listaTipi) > 0 && !$pagamento->isRichiestaDisabilitata()) {

            $opzioni_form["cf_firmatario"] = $pagamento->getFirmatario()->getCodiceFiscale();
            $opzioni_form["lista_tipi"] = $listaTipi;
            $opzioni_form["url"] = $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo));
            $dati['validation_groups'] = $this->getValidationGroupsFormGiustificativo();
            //$form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
            //$form->add('submit', \BaseBundle\Form\CommonType::salva_indietro, array('label' => 'Salva', 'url' => $this->generateUrl('dettaglio_giustificativo', array("id_giustificativo" => $id_giustificativo))));
            $form = $this->createForm('AttuazioneControlloBundle\Form\DocumentoGiustificativoType', $documento_giustificativo, $opzioni_form);

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                $tipologia = $documento_giustificativo->getDocumentoFile()->getTipologiaDocumento();
                if (!is_null($tipologia)) {
                    $codice = strtolower($tipologia->getCodice());
                    $nota = $documento_giustificativo->getNota();
                    // se il codice tipologia inizia esattamente per "altro" (quindi tipologia altro) e non ho specificato una nota segnalo errore
                    if (strpos($codice, 'altro') === 0 && empty($nota)) {
                        $form->get('nota')->addError(new \Symfony\Component\Form\FormError('Occorre inserire un nota che descriva la natura del documento'));
                    }
                }

                if ($form->isValid()) {
                    try {

                        $documento_file = $documento_giustificativo->getDocumentoFile();
                        $this->container->get("documenti")->carica($documento_file, 0, $richiesta);

                        //$documento_giustificativo->setDocumentoFile($documento_file);
                        $documento_giustificativo->setGiustificativoPagamento($giustificativo);
                        $em->persist($documento_giustificativo);

                        $em->flush();
                        return $this->addSuccesRedirect("Documento caricato correttamente", "dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo));
                    } catch (\Exception $e) {
                        return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "aggiungi_documento_giustificativo", array("id_giustificativo" => $id_giustificativo));
                    }
                }
            }
            $form_view = $form->createView();
        } else {
            $form_view = null;
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio giustificativo", $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo)));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Aggiungi documento giustificativo");

        $dati = array('form' => $form_view);

        return $this->render("AttuazioneControlloBundle:Giustificativi:aggiungiDocumentoGiustificativo.html.twig", $dati);
    }

    public function getTipiDocumentiGiustificativoCaricabili($giustificativo, $solo_obbligatori = false) {

        $soloObbligatori = true;
        $res = $this->getTipiDocumenti($giustificativo, $soloObbligatori);
        if (!$solo_obbligatori) {
            $procedura = $giustificativo->getPagamento()->getRichiesta()->getProcedura();

            $tipologie_con_duplicati = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findTipologieDocumentiGiustificativoConDuplicati($procedura->getId());
            $res = array_merge($res, $tipologie_con_duplicati);
        }

        return $res;
    }

    // calcolo standard generico
    protected function calcolaAvanzamentoPianoCosti($richiesta, $proponente, $pagamento = null) {

        $atc = $richiesta->getAttuazioneControllo();
        $pagamenti = $atc->getPagamenti();

        $avanzamentoProponenti = array();
        /**
         * se esiste una variazione approvata leggo il suo piano costi
         * altrimenti devo recuperare quello approvato in istruttoria
         */
        if (!is_null($pagamento)) {
            $variazione = $atc->getUltimaVariazionePianoCostiPA($pagamento);
        } else {
            $variazione = $atc->getUltimaVariazioneApprovata();
        }
        if (!is_null($variazione)) {
            $oggettiVocePianoCosto = $variazione->getVociPianoCosto();
            //getImportoVariazioneAnnox
        } else {
            foreach ($richiesta->getVociPianoCosto() as $vocePianoCosto) {
                $oggettiVocePianoCosto[] = $vocePianoCosto->getIstruttoria();
                //getImportoAmmissibileAnnox
            }
        }

        /**
         * attenzione.. potremmo ciclare a seconda del caso
         * oggetti IstruttoriaVocePianoCosto o VariazioneVocePianoCosto
         */
        foreach ($oggettiVocePianoCosto as $oggettoVocePianoCosto) {

            $vocePianoCosto = $oggettoVocePianoCosto->getVocePianoCosto();

            $proponenteVoce = $vocePianoCosto->getProponente();
            $proponenteId = $proponenteVoce->getId();

            if (!array_key_exists($proponenteId, $avanzamentoProponenti)) {
                $avanzamentoProponenti[$proponenteId] = array();
            }

            $pianoCosto = $vocePianoCosto->getPianoCosto();
            $idSezione = $pianoCosto->getSezionePianoCosto()->getId();
            $titoloSezione = $pianoCosto->getSezionePianoCosto()->getTitoloSezione();

            $importoApprovato = $oggettoVocePianoCosto->sommaImportiAvanzamento();

            // init
            $rendicontatoPagamenti = array();
            foreach ($pagamenti as $pagamento) {

                // per gli anticipi non c'è rendicontato..li escludiamo
                if ($pagamento->getModalitaPagamento()->isAnticipo()) {
                    continue;
                }

                $rendicontatoPagamenti[$pagamento->getId()] = array(
                    'pagamento' => $pagamento,
                    'modalitaPagamento' => $pagamento->getModalitaPagamento()->getCodice(),
                    'importoRendicontato' => 0.0,
                    'importoRendicontatoAmmesso' => 0.0
                );
            }

            /**
             * TODO sosituire con chiamata a repository che skippa quelli collegati a pagamenti cancellati
             */
            $vociGiustificativi = $vocePianoCosto->getVociGiustificativi();
            foreach ($vociGiustificativi as $voceGiustificativo) {

                try {
                    $pagamento = $voceGiustificativo->getPagamento();
                    $pagamentoId = $pagamento->getId();

                    /**
                     * succede che $vocePianoCosto->getVociGiustificativi() torna pure oggetti collegati a pagamenti cancellati
                     * siccome io in cima inizializzo l'array con gli id dei pagamenti attivi
                     * per uscirmene velocemente controllo che l'id era stato in precedenza definito..in caso contrario skippo
                     * 
                     * TODO anziche chiamare la getVociGiustificativi è necessario scrivere una funzione di repository che tenga conto 
                     * solo dei pagamenti non cancellati..(avevo provato a farlo dentro la entity vocePianoCosto ma appena chiamo un metodo su un pagamento cancellato
                     * solleva un eccezione entity not found (poichè il pagamento è cancellato logicamente))
                     */
                    if (!array_key_exists($pagamentoId, $rendicontatoPagamenti)) {
                        continue;
                    }

                    $rendicontatoPagamenti[$pagamentoId]['importoRendicontato'] += $voceGiustificativo->getImporto();
                    $rendicontatoPagamenti[$pagamentoId]['importoRendicontatoAmmesso'] += $voceGiustificativo->getImportoApprovato();
                } catch (\Exception $e) {
                    // il try catch serve per gestire la cancellazione logica, se l'oggetto è cancellato a quanto pare viene lanciata un'eccezione
                    // mi attengo al piano e skippo
                }
            }

            ksort($rendicontatoPagamenti);


            /**
             * a questo punto ho creato la mia mega struttura dati indicizzata per proponente, sezione e ordinamento
             * n.b. l'ordinamento serve poi alla ksort per risolvere il troiaio derivato dalle variazioni che scombinano l'ordine del piano costi
             */
            $avanzamentoProponenti[$proponenteId][$idSezione][$pianoCosto->getOrdinamento()] = array(
                'sezione' => $titoloSezione,
                'codice' => $pianoCosto->getCodice(),
                'titolo' => $pianoCosto->getTitolo(),
                'importoApprovato' => $importoApprovato,
                'rendicontatoPagamenti' => $rendicontatoPagamenti
            );
        }

        //se è null vuol dire che ci serve calcolare i totali
        if (is_null($proponente)) {

            $primaIterazione = true;

            // ciclo su tutti i proponenti
            foreach ($avanzamentoProponenti as $avanzamentoProponente) {

                // init..al primo giro ricopio l'avanzamento del primo proponente
                // ai giri successivi inizio le somme degli altri avanzamenti 
                if ($primaIterazione) {
                    $avanzamentoTotale = $avanzamentoProponente;
                    $primaIterazione = false;
                    continue;
                }
                foreach ($avanzamentoProponente as $sezioneId => $sezione) {

                    foreach ($sezione as $ordinamento => $voceSezione) {
                        $avanzamentoTotale[$sezioneId][$ordinamento]['importoApprovato'] += $voceSezione['importoApprovato'];
                        foreach ($voceSezione['rendicontatoPagamenti'] as $pagamentoId => $rendicontatoPagamento) {
                            $avanzamentoTotale[$sezioneId][$ordinamento]['rendicontatoPagamenti'][$pagamentoId]['importoRendicontato'] += $rendicontatoPagamento['importoRendicontato'];
                            $avanzamentoTotale[$sezioneId][$ordinamento]['rendicontatoPagamenti'][$pagamentoId]['importoRendicontatoAmmesso'] += $rendicontatoPagamento['importoRendicontatoAmmesso'];
                        }
                    }
                }
            }

            // avanzamento totale su tutti i proponenti
            $avanzamentoDaMostrare = $avanzamentoTotale;
        } else {
            // avanzamento per il proponente selezionato (o per il mandatario in caso di non multiproponenza)
            $avanzamentoDaMostrare = $avanzamentoProponenti[$proponente->getId()];
        }

        // lo ordino perchè in caso di variazioni esce ordinata a cazzo,
        // in questo modo avremo in successione ogni eventuale sezione con dentro tutte le voci piano costo (di cui il totale alla fine di ogni sezione) 
        // ordino prima rispetto alle sezioni
        ksort($avanzamentoDaMostrare);

        // e poi rispetto alle voci di ogni sezione
        foreach ($avanzamentoDaMostrare as $sezioneId => $sezione) {
            ksort($avanzamentoDaMostrare[$sezioneId]);
        }


//		$totaleRendicontatoPagamenti = array();
//		foreach ($pagamenti as $pagamento){
//			$totaleRendicontatoPagamenti[$pagamento->getId()] = 0.0;
//		}

        foreach ($avanzamentoDaMostrare as $sezioneId => $sezione) {

            $totaleSezioneRendicontatoPagamenti = array();
            $primaIterazione = true;

            foreach ($sezione as $ordinamento => $voceSezione) {

                if ($primaIterazione) {
                    $totaleSezioneRendicontatoPagamenti = $voceSezione['rendicontatoPagamenti'];
                    $primaIterazione = false;
                    continue;
                }

                if ($voceSezione['codice'] != 'TOT') {
                    foreach ($voceSezione['rendicontatoPagamenti'] as $pagamentoId => $rendicontatoPagamento) {
                        $totaleSezioneRendicontatoPagamenti[$pagamentoId]['importoRendicontato'] += $rendicontatoPagamento['importoRendicontato'];
                        $totaleSezioneRendicontatoPagamenti[$pagamentoId]['importoRendicontatoAmmesso'] += $rendicontatoPagamento['importoRendicontatoAmmesso'];
                    }
                } else {
                    $avanzamentoDaMostrare[$sezioneId][$ordinamento]['rendicontatoPagamenti'] = $totaleSezioneRendicontatoPagamenti;
                }
            }
        }

        return $avanzamentoDaMostrare;
    }

    // 773 774
    public function avanzamento($id_pagamento, $id_proponente = null, $tipo = null) {
        throw new \Exception("Implementato soltanto per dei bandi specifici");
    }

    public function calcolaImportiFinali($id_pagamento) {
        throw new \Exception("Implementato soltanto per dei bandi specifici");
    }

    public function popolaDiCui($pagamento_provenienza, $pagamento_destinazione, $pagamento_provenienza_padre = null) {
        // Attraverso questa funzione vengono prese alcune voci di spesa dei giustificativi collegati al pagamento di provenienza
        // e collegati tramite la tabella di_cui al pagamento di destinazione.
        // Tali voci di spesa hanno la caratteristica di avere un taglio sull'importo dovuto al superamento dei massimali
        // TALE IMPORTO SARA' IL NUOVO IMPORTO RICHIESTO SU QUESTA VOCE DI SPESA
        // CASO TIPICO:
        // a SAL viene chiesto 1.000 -> l'istruttore concede 800 e 200 vengono negate per superamento dei massimali
        // queste 200 saranno l'importo richiesto per questa voce di spesa a SALDO
        // quindi in questo caso avremo:
        // - UNA VOCE SPESA CON UN IMPORTO DI 200
        // - PAGAMENTO DI PROVENIENZA : SAL
        // - PAGAMENTO DESTINAZIONE   : SALDO
        $em = $this->getEm();
        foreach ($pagamento_provenienza->getGiustificativi() as $giustificativo) {
            foreach ($giustificativo->getVociPianoCosto() as $vocePianoCosto) {
                if (!is_null($vocePianoCosto->getImportoNonAmmessoPerSuperamentoMassimali()) && ($vocePianoCosto->getImportoNonAmmessoPerSuperamentoMassimali() > 0)) {
                    $criteria = array(
                        "pagamento_provenienza" => $pagamento_provenienza->getId(),
                        "pagamento_destinazione" => $pagamento_destinazione->getId(),
                        "voce_piano_costo_giustificativo" => $vocePianoCosto->getId(),
                    );
                    $di_cui = $em->getRepository("AttuazioneControlloBundle\Entity\DiCui")->findOneBy($criteria);
                    if (is_null($di_cui)) {
                        $di_cui = new \AttuazioneControlloBundle\Entity\DiCui();
                        $di_cui->setPagamentoDestinazione($pagamento_destinazione);
                        $di_cui->setPagamentoProvenienza($pagamento_provenienza);
                        $di_cui->setVocePianoCostoGiustificativo($vocePianoCosto);

                        // SE IL PAGAMENTO DI PROVENIENZA E' STATO A SUA VOLTA DESTINAZIONE PER LA STESSA VOCE,
                        // COME NUOVO IMPORTO PRENDIAMO QUEL VALORE DI SUPERAMENTO MASSIMALI
                        // 
                        // VICEVERSA PRENDIAMO QUELLO "ORIGINALE":
                        // Un caso di questo scenario è quello in cui ci sono più SAL
                        // prov: SAL1 -> dest: SAL2
                        // prov: SAL2 -> dest: SALDO in questo caso sul SALDO ribaltiamo quello che proveniva dal SAL2
                        $criteria = array(
                            "pagamento_destinazione" => $pagamento_provenienza->getId(),
                            "voce_piano_costo_giustificativo" => $vocePianoCosto->getId(),
                        );
                        $di_cui_precedenti = $em->getRepository("AttuazioneControlloBundle\Entity\DiCui")->findOneBy($criteria);

                        $nuovo_importo = !is_null($di_cui_precedenti) && !is_null($di_cui_precedenti->getImportoNonAmmessoPerSuperamentoMassimali() && ($di_cui_precedenti->getImportoNonAmmessoPerSuperamentoMassimali() > 0)) ?
                                $di_cui_precedenti->getImportoNonAmmessoPerSuperamentoMassimali() :
                                $vocePianoCosto->getImportoNonAmmessoPerSuperamentoMassimali();

                        $di_cui->setImporto($nuovo_importo);

                        $em->persist($di_cui);
                        $em->flush();
                    }
                }
            }
        }
        if (!is_null($pagamento_provenienza_padre)) {
            $criteria = array("pagamento_provenienza" => $pagamento_provenienza_padre->getId(),
                "pagamento_destinazione" => $pagamento_provenienza->getId());
            $di_cui_precedenti = $em->getRepository("AttuazioneControlloBundle\Entity\DiCui")->findBy($criteria);
            foreach ($di_cui_precedenti as $dc) {
                $criteria = array(
                    "pagamento_provenienza" => $pagamento_provenienza->getId(),
                    "pagamento_destinazione" => $pagamento_destinazione->getId(),
                    "voce_piano_costo_giustificativo" => $dc->getVocePianoCostoGiustificativo());
                $di_cui = $em->getRepository("AttuazioneControlloBundle\Entity\DiCui")->findOneBy($criteria);
                if (is_null($di_cui) && !is_null($dc->getImportoNonAmmessoPerSuperamentoMassimali()) && ($dc->getImportoNonAmmessoPerSuperamentoMassimali() > 0)) {
                    $di_cui = new \AttuazioneControlloBundle\Entity\DiCui();
                    $di_cui->setPagamentoDestinazione($pagamento_destinazione);
                    $di_cui->setPagamentoProvenienza($pagamento_provenienza);
                    $di_cui->setVocePianoCostoGiustificativo($dc->getVocePianoCostoGiustificativo());
                    $di_cui->setImporto($dc->getImportoNonAmmessoPerSuperamentoMassimali());
                    $em->persist($di_cui);
                    $em->flush();
                }
            }
        }
    }

    /**
     * Si guarda se ci sono proroghe approvate
     * altrimenti si leggono da istruttoria richiesta (che sarebbero le date impostate nel passaggio in ATC)
     */
    protected function getDateProgetto($pagamento) {

        $istruttoria = $pagamento->getRichiesta()->getIstruttoria();

        if (!is_null($pagamento->getAttuazioneControlloRichiesta()->getUltimaProrogaAvvioApprovata())) {
            $dataAvvioProgetto = $pagamento->getAttuazioneControlloRichiesta()->getUltimaProrogaAvvioApprovata()->getDataAvvioApprovata();
        } else {
            $dataAvvioProgetto = $istruttoria->getDataAvvioProgetto();
        }

       if (!is_null($pagamento->getAttuazioneControlloRichiesta()->getUltimaProrogaFineApprovata())) {
            if ($pagamento->getAttuazioneControlloRichiesta()->getUltimaProrogaFineApprovata()->getDataFineApprovata() < $istruttoria->getDataTermineProgetto()) {
                $dataTermineProgetto = $istruttoria->getDataTermineProgetto();
            } else {
                $dataTermineProgetto = $pagamento->getAttuazioneControlloRichiesta()->getUltimaProrogaFineApprovata()->getDataFineApprovata();
            }
        } else {
            $dataTermineProgetto = $istruttoria->getDataTermineProgetto();
        }
        
        $date = new \stdClass();
        $date->dataAvvioProgetto = $dataAvvioProgetto;
        $date->dataTermineProgetto = $dataTermineProgetto;

        return $date;
    }

    /**
     * @param DateTime $dataAvvioProgetto
     * @return string
     */
    protected function getMessaggioErroreDataAvvioProgetto(DateTime $dataAvvioProgetto): string
    {
        $dataMinima = "(data minima " . $dataAvvioProgetto->format('d/m/Y') . ")";
        return "Non è possibile inserire una data precedente alla data di avvio progetto " . $dataMinima;
    }

    /**
     * @param DateTime $dataTermineProgetto
     * @return string
     */
    protected function getMessaggioErroreDataTermineProgetto(DateTime $dataTermineProgetto): string
    {
        $dataMassima = "(data massima " . $dataTermineProgetto->format('d/m/Y') . ")";
        return "Non è possibile inserire una data successiva alla data di fine progetto " . $dataMassima;
    }

    public function elencoContratti($id_pagamento) {

        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        if (is_null($pagamento)) {
            throw new \Symfony\Component\Routing\Exception\ResourceNotFoundException('Pagamento non trovato');
        }

        $dati = array("pagamento" => $pagamento);
        $dati["menu"] = "contratti";
        $dati["richiesta"] = $pagamento->getRichiesta();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco contratti");

        return $this->render("AttuazioneControlloBundle:Contratti:elencoContratti.html.twig", $dati);
    }

    public function visualizzaContratto($id_contratto) {

        $em = $this->getEm();
        $contratto = $em->getRepository("AttuazioneControlloBundle\Entity\Contratto")->find($id_contratto);
        if (is_null($contratto)) {
            throw new \Symfony\Component\Routing\Exception\ResourceNotFoundException('Pagamento non trovato');
        }

        $pagamento = $contratto->getPagamento();

        $tipologieFornitore = $em->getRepository("AttuazioneControlloBundle\Entity\TipologiaFornitore")->findByCodice(array('RI', 'UN', 'LAB', 'CO'));

        $dati = array();
        $dati["url_indietro"] = $this->generateUrl("elenco_contratti", array("id_pagamento" => $pagamento->getId()));
        $dati["disabled"] = true;
        $dati["tipologieFornitore"] = $tipologieFornitore;
        $form = $this->createForm("AttuazioneControlloBundle\Form\ContrattoType", $contratto, $dati);

        $dati["form"] = $form->createView();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco contratti", $this->generateUrl("elenco_contratti", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Visualizza contratto");

        return $this->render("AttuazioneControlloBundle:Contratti:contratto.html.twig", $dati);
    }

    public function validaContratto($contratto) {
        $esito = new EsitoValidazione(true);
        $giustificativi = $contratto->getGiustificativiPagamento();

        //Verifico la presenza di giustificativi
        if (count($giustificativi) == 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Per il contratto ".$contratto->getNumero()." non sono presenti giustificativi");            
            return $esito;
        }

        //Probabile refuso da togliere
        /* $importoTotale = 0.00;

          foreach ($giustificativi as $giustificativo) {
          $importoTotale += $giustificativo->getImportoGiustificativo();
          } */

        foreach ($giustificativi as $giustificativo) {
            $tipologia = $giustificativo->getTipologiaGiustificativo();
            if (!is_null($tipologia) && $tipologia->isInvisibile() == true) {
                continue;
            }
            $esitoGiustificativi = $this->container->get("gestore_giustificativi")->getGestore($contratto->getPagamento()->getProcedura())->validaGiustificativo($giustificativo);
            if ($esitoGiustificativi->getEsito() == false) {
                $esito->setEsito(false);
                $errori = $esitoGiustificativi->getMessaggiSezione();
                foreach ($errori as $errore) {
                    $esito->addMessaggio($errore);
                }
            }
        }

        if (!$esito->getEsito()) {
            $esito->addMessaggioSezione("Uno o più contratti sono incompleti o non validi");
        }

        return $esito;
    }

    public function elencoDocumentiContratto($id_contratto, $id_pagamento, $opzioni = array()) {

        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        $documenti_caricati = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoContratto")->findDocumentiCaricati($id_contratto);

        $dati = array("documenti" => $documenti_caricati);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco contratti", $this->generateUrl("elenco_contratti", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Documenti");
        return $this->render("AttuazioneControlloBundle:Contratti:elencoDocumenti.html.twig", $dati);
    }

}
