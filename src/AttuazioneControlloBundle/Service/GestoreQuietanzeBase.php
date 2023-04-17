<?php
namespace AttuazioneControlloBundle\Service;

use DateTime;
use AttuazioneControlloBundle\Entity\StatoPagamento;
use DocumentoBundle\Component\ResponseException;
use AttuazioneControlloBundle\Entity\QuietanzaGiustificativo;

class GestoreQuietanzeBase extends AGestoreQuietanze {

    public function aggiungiQuietanza($id_giustificativo) {
        $em = $this->getEm();
        $giustificativo = $em->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);
        $pagamento = $giustificativo->getPagamento();
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        if ($pagamento->isRichiestaDisabilitata()) {
            $this->addErrorRedirect('Il pagamento è disabilitato', "dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo));
        }

        $quietanza = new \AttuazioneControlloBundle\Entity\QuietanzaGiustificativo();
        $quietanza->setGiustificativoPagamento($giustificativo);

        $dati = array();
        $dati["documento_caricato"] = false;

        $tipologia_documento = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array("codice" => "QUIETANZA"));
        $documento = new \DocumentoBundle\Entity\DocumentoFile();
        $documento->setTipologiaDocumento($tipologia_documento);
        $quietanza->setDocumentoQuietanza($documento);

        $options = array();
        $options["url_indietro"] = $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo));
        $options["tipologie_quietanza"] = $this->getTipologieQuietanza($richiesta);
        $options["documento_caricato"] = $dati["documento_caricato"];

        $form = $this->createForm("AttuazioneControlloBundle\Form\QuietanzaType", $quietanza, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $dateProgetto = $this->getDateProgetto($pagamento);
            $istruttoria = $pagamento->getRichiesta()->getIstruttoria();
            // Se in fase di passaggio in ATC è stato stabilito che la data di avvio progetto è vincolante, non deve essere possibile
            // inserire una data giustificativo precedente alla data di avvio progetto
            if (!is_null($dateProgetto->dataAvvioProgetto) && $istruttoria->isDataInizioVincolante() && $quietanza->getDataQuietanza() < $dateProgetto->dataAvvioProgetto) {
                $messaggioErroreDataAvvioProgetto = $this->getMessaggioErroreDataAvvioProgetto($dateProgetto->dataAvvioProgetto);
                $form->get("data_quietanza")->addError(new \Symfony\Component\Form\FormError($messaggioErroreDataAvvioProgetto));
            }

            if (!is_null($dateProgetto->dataTermineProgetto) && $quietanza->getDataQuietanza() > $dateProgetto->dataTermineProgetto) {
                $messaggioErroreDataTermineProgetto = $this->getMessaggioErroreDataTermineProgetto($dateProgetto->dataTermineProgetto);
                $form->get("data_quietanza")->addError(new \Symfony\Component\Form\FormError($messaggioErroreDataTermineProgetto));
            }
            if ($form->isValid()) {
                try {
                    $this->container->get("documenti")->carica($documento);
                    $em->persist($quietanza);
                    $em->flush();
                    return $this->addSuccesRedirect("La quietanza è stata correttamente aggiunta", "dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati["form"] = $form->createView();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio giustificativo", $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo)));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Aggiungi quietanza");

        return $this->render("AttuazioneControlloBundle:Quietanze:aggiungiQuietanza.html.twig", $dati);
    }

    public function modificaQuietanza($id_quietanza) {
        $em = $this->getEm();
        /** @var QuietanzaGiustificativo $quietanza */
        $quietanza = $em->getRepository("AttuazioneControlloBundle\Entity\QuietanzaGiustificativo")->find($id_quietanza);
        $giustificativo = $quietanza->getGiustificativoPagamento();
        $pagamento = $giustificativo->getPagamento();
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $dati = array();
        $dati["documento_caricato"] = true;
        if (is_null($quietanza->getDocumentoQuietanza())) {
            $dati["documento_caricato"] = false;
            $tipologia_documento = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array("codice" => "QUIETANZA"));
            $documento = new \DocumentoBundle\Entity\DocumentoFile();
            $documento->setTipologiaDocumento($tipologia_documento);
            $quietanza->setDocumentoQuietanza($documento);
            $path = null;
        } else {
            $documentoFile = $quietanza->getDocumentoQuietanza();
            $path = $this->container->get("funzioni_utili")->encid($documentoFile->getPath() . $documentoFile->getNome());
        }

        $options = array();
        $options['disabled'] = $pagamento->isRichiestaDisabilitata() || !$giustificativo->isModificabileIntegrazione() || $giustificativo->getGiustificativoOrigine();
        $options["url_indietro"] = $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId()));
        $options["tipologie_quietanza"] = $this->getTipologieQuietanza($richiesta);
        $options["documento_caricato"] = $dati["documento_caricato"];

        $form = $this->createForm("AttuazioneControlloBundle\Form\QuietanzaType", $quietanza, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $dateProgetto = $this->getDateProgetto($pagamento);
            $istruttoria = $pagamento->getRichiesta()->getIstruttoria();
            // Se in fase di passaggio in ATC è stato stabilito che la data di avvio progetto è vincolante, non deve essere possibile
            // inserire una data giustificativo precedente alla data di avvio progetto
            if (!is_null($dateProgetto->dataAvvioProgetto) && $istruttoria->isDataInizioVincolante() && $quietanza->getDataQuietanza() < $dateProgetto->dataAvvioProgetto) {
                $messaggioErroreDataAvvioProgetto = $this->getMessaggioErroreDataAvvioProgetto($dateProgetto->dataAvvioProgetto);
                $form->get("data_quietanza")->addError(new \Symfony\Component\Form\FormError($messaggioErroreDataAvvioProgetto));
            }

            if (!is_null($dateProgetto->dataTermineProgetto) && $quietanza->getDataQuietanza() > $dateProgetto->dataTermineProgetto) {
                $messaggioErroreDataTermineProgetto = $this->getMessaggioErroreDataTermineProgetto($dateProgetto->dataTermineProgetto);
                $form->get("data_quietanza")->addError(new \Symfony\Component\Form\FormError($messaggioErroreDataTermineProgetto));
            }

            if ($form->isValid()) {
                try {
                    if ($dati["documento_caricato"] == false) {
                        $this->container->get("documenti")->carica($documento);
                    }
                    $em->persist($quietanza);
                    $em->flush();
                    return $this->addSuccesRedirect("La quietanza è stata correttamente modificata", "dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati["form"] = $form->createView();
        $dati["quietanza"] = $quietanza;
        $dati["path"] = $path;

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio giustificativo", $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Modifica quietanza");

        return $this->render("AttuazioneControlloBundle:Quietanze:modificaQuietanza.html.twig", $dati);
    }

    public function dettaglioQuietanza($id_quietanza) {
        
    }

    public function eliminaQuietanza($id_quietanza) {
        $em = $this->getEm();
        $quietanza = $em->getRepository("AttuazioneControlloBundle\Entity\QuietanzaGiustificativo")->find($id_quietanza);
        $giustificativo = $quietanza->getGiustificativoPagamento();
        $pagamento = $giustificativo->getPagamento();

        if (in_array($pagamento->getStato(), array(StatoPagamento::PAG_INVIATO_PA, StatoPagamento::PAG_PROTOCOLLATO))) {
            return $this->addErrorRedirect("L'operazione non è compatibile con lo stato del pagamento.", "dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId()));
        }

        try {
            $em->remove($quietanza);
            $em->flush();
            return $this->addSuccesRedirect("La quietanza è stata correttamente eliminata", "dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId()));
        } catch (ResponseException $e) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId()));
        }
    }

    public function eliminaDocumentoQuietanza($id_documento_quietanza, $id_quietanza) {
        $em = $this->getEm();
        $documento = $em->getRepository("DocumentoBundle\Entity\DocumentoFile")->find($id_documento_quietanza);
        /** @var QuietanzaGiustificativo $giustificativo */
        $giustificativo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\QuietanzaGiustificativo")->find($id_quietanza);
        $pagamento = $giustificativo->getGiustificativoPagamento()->getPagamento();
        try {
            $em->remove($documento);
            $giustificativo->setDocumentoQuietanza(null);
            $em->flush();
            return $this->addSuccesRedirect("Il documento è stato correttamente eliminato", "modifica_quietanza", array("id_quietanza" => $id_quietanza));
        } catch (ResponseException $e) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "gestione_documenti_pagamento", array("id_pagamento" => $pagamento->getId()));
        }
    }

    public function getTipologieQuietanza($richiesta) {
        $procedura = $richiesta->getProcedura();
        $tipologie_quietanze_procedura = $procedura->getTipologieQuietanze();
        if (count($tipologie_quietanze_procedura) == 0) {
            return $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\TipologiaQuietanza")->findAll();
        } else {
            return $tipologie_quietanze_procedura;
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

    protected function modificaQuietanzaConScadenza($id_quietanza, $scadenzaString, $twig = null) {
        $em = $this->getEm();
        $quietanza = $em->getRepository("AttuazioneControlloBundle\Entity\QuietanzaGiustificativo")->find($id_quietanza);
        $giustificativo = $quietanza->getGiustificativoPagamento();
        $pagamento = $giustificativo->getPagamento();
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $scadenza = new \DateTime($scadenzaString);

        $dati = array();
        $dati["documento_caricato"] = true;
        if (is_null($quietanza->getDocumentoQuietanza())) {
            $dati["documento_caricato"] = false;
            $tipologia_documento = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array("codice" => "QUIETANZA"));
            $documento = new \DocumentoBundle\Entity\DocumentoFile();
            $documento->setTipologiaDocumento($tipologia_documento);
            $quietanza->setDocumentoQuietanza($documento);
            $path = null;
        } else {
            $documentoFile = $quietanza->getDocumentoQuietanza();
            $path = $this->container->get("funzioni_utili")->encid($documentoFile->getPath() . $documentoFile->getNome());
        }

        $options = array();
        $options['disabled'] = $pagamento->isRichiestaDisabilitata() || !$giustificativo->isModificabileIntegrazione();
        $options["url_indietro"] = $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId()));
        $options["tipologie_quietanza"] = $this->getTipologieQuietanza($richiesta);
        $options["documento_caricato"] = $dati["documento_caricato"];

        $form = $this->createForm("AttuazioneControlloBundle\Form\QuietanzaType", $quietanza, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $dateProgetto = $this->getDateProgetto($pagamento);
            $istruttoria = $pagamento->getRichiesta()->getIstruttoria();

            if (!is_null($dateProgetto->dataAvvioProgetto) && $istruttoria->isDataInizioVincolante() && $quietanza->getDataQuietanza() < $dateProgetto->dataAvvioProgetto) {
                $form->get("data_quietanza")->addError(new \Symfony\Component\Form\FormError("Non è possibile inserire una data precedente alla data di avvio progetto"));
            }

            //Bando personalizzita con date oltre la data fine di progetto
            if (!is_null($dateProgetto->dataTermineProgetto) && $quietanza->getDataQuietanza() > $scadenza) {
                $form->get("data_quietanza")->addError(new \Symfony\Component\Form\FormError("Non è possibile inserire una data successiva al " . $scadenza->format('d/m/Y')));
            }

            if ($form->isValid()) {
                try {
                    if ($dati["documento_caricato"] == false) {
                        $this->container->get("documenti")->carica($documento);
                    }
                    $em->persist($quietanza);
                    $em->flush();
                    return $this->addSuccesRedirect("La quietanza è stata correttamente modificata", "dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati["form"] = $form->createView();
        $dati["quietanza"] = $quietanza;
        $dati["path"] = $path;

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio giustificativo", $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $giustificativo->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Modifica quietanza");

        if (!is_null($twig)) {
            return $this->render($twig, $dati);
        }
        return $this->render("AttuazioneControlloBundle:Quietanze:modificaQuietanza.html.twig", $dati);
    }

    protected function aggiungiQuietanzaConScadenza($id_giustificativo, $scadenzaString, $twig = null) {
        $em = $this->getEm();
        $giustificativo = $em->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);
        $pagamento = $giustificativo->getPagamento();
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $scadenza = new \DateTime($scadenzaString);

        if ($pagamento->isRichiestaDisabilitata()) {
            $this->addErrorRedirect('Il pagamento è disabilitato', "dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo));
        }

        $quietanza = new \AttuazioneControlloBundle\Entity\QuietanzaGiustificativo();
        $quietanza->setGiustificativoPagamento($giustificativo);

        $dati = array();
        $dati["documento_caricato"] = false;

        $tipologia_documento = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array("codice" => "QUIETANZA"));
        $documento = new \DocumentoBundle\Entity\DocumentoFile();
        $documento->setTipologiaDocumento($tipologia_documento);
        $quietanza->setDocumentoQuietanza($documento);

        $options = array();
        $options["url_indietro"] = $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo));
        $options["tipologie_quietanza"] = $this->getTipologieQuietanza($richiesta);
        $options["documento_caricato"] = $dati["documento_caricato"];

        $form = $this->createForm("AttuazioneControlloBundle\Form\QuietanzaType", $quietanza, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $dateProgetto = $this->getDateProgetto($pagamento);
            $istruttoria = $pagamento->getRichiesta()->getIstruttoria();
            // Se in fase di passaggio in ATC è stato stabilito che la data di avvio progetto è vincolante, non deve essere possibile
            // inserire una data giustificativo precedente alla data di avvio progetto
            if (!is_null($dateProgetto->dataAvvioProgetto) && $istruttoria->isDataInizioVincolante() && $quietanza->getDataQuietanza() < $dateProgetto->dataAvvioProgetto) {
                $form->get("data_quietanza")->addError(new \Symfony\Component\Form\FormError("Non è possibile inserire una data precedente alla data di avvio progetto"));
            }

            if (!is_null($dateProgetto->dataTermineProgetto) && $quietanza->getDataQuietanza() > $scadenza) {
                $form->get("data_quietanza")->addError(new \Symfony\Component\Form\FormError("Non è possibile inserire una data successiva al " . $scadenza->format('d/m/Y')));
            }
            if ($form->isValid()) {
                try {
                    $this->container->get("documenti")->carica($documento);
                    $em->persist($quietanza);
                    $em->flush();
                    return $this->addSuccesRedirect("La quietanza è stata correttamente aggiunta", "dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati["form"] = $form->createView();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio giustificativo", $this->generateUrl("dettaglio_giustificativo", array("id_giustificativo" => $id_giustificativo)));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Aggiungi quietanza");

        if (!is_null($twig)) {
            return $this->render($twig, $dati);
        }
        return $this->render("AttuazioneControlloBundle:Quietanze:aggiungiQuietanza.html.twig", $dati);
    }

    public function getDataTermineRendicontazione($pagamento): ?\DateTime {
        if (!\is_null($pagamento->data_fine_rendicontazione_forzata)) {
            return $pagamento->data_fine_rendicontazione_forzata;
        }

        $proroga = $pagamento->getProrogaRendicontazione();
        if ($proroga) {
            $dataScadenzaDate = clone $proroga->getDataScadenza();
            $dataScadenzaDate->modify('+23 hours')->modify('+59 minutes')->modify('+59 seconds');
            return $dataScadenzaDate;
        }

        $modalitaPagamentoProcedura = $pagamento->getModalitaPagamentoProcedura();
        if (\is_null($modalitaPagamentoProcedura)) {
            throw new \Exception('Modalita pagamento per la procedura non definito');
        }
        return $modalitaPagamentoProcedura->getDataFineRendicontazione();
    }

}
