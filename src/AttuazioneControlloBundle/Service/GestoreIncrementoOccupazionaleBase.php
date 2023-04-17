<?php

namespace AttuazioneControlloBundle\Service;

use AnagraficheBundle\Entity\DocumentoPersonale;
use AnagraficheBundle\Entity\Personale;
use AttuazioneControlloBundle\Entity\DocumentoIncrementoOccupazionale;
use AttuazioneControlloBundle\Entity\IncrementoOccupazionale;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\RendicontazioneProceduraConfig;
use DocumentoBundle\Entity\DocumentoFile;
use Exception;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Service\GestoreResponse;
use RichiesteBundle\Utility\EsitoValidazione;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\HttpFoundation\Response;


class GestoreIncrementoOccupazionaleBase extends AGestoreIncrementoOccupazionale
{
    const ALTRO_DOCUMENTO = 'ALTRO_DOCUMENTO';
    const DM10_INIZIO = 'DM10_INIZIO';
    const DM10_FINE = 'DM10_FINE';
    const F24 = 'F24';
    const MODELLO_UNIFICATO_LAV = 'MODELLO_UNIFICATO_LAV';
    const CONTRATTO_ASSUNZIONE = 'CONTRATTO_ASSUNZIONE';
    const COMUNICAZIONE_CENTRO_IMPIEGO = 'COMUNICAZIONE_CENTRO_IMPIEGO';
    const ALTRO_DOCUMENTO_PERSONALE = 'ALTRO_DOCUMENTO_PERSONALE';
    
    /**
     * @param Procedura $procedura
     * @return RendicontazioneProceduraConfig
     */
    public function getRendicontazioneProceduraConfig(Procedura $procedura)
    {
        $rendicontazioneProceduraConfig = $procedura->getRendicontazioneProceduraConfig();
        if (is_null($rendicontazioneProceduraConfig)) {
            $rendicontazioneProceduraConfig = new RendicontazioneProceduraConfig();
        }

        return $rendicontazioneProceduraConfig;
    }

    /**
     * @param Procedura $procedura
     * @return mixed
     */
    public function getAvvisoSezioneIncrementoOccupazionale(Procedura $procedura)
    {
        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($procedura);
        return $rendicontazioneProceduraConfig->getAvvisoSezioneIncrementoOccupazionale();
    }

    /**
     * @param Procedura $procedura
     * @return mixed
     */
    public function getCaricamentoNuoviDipendenti(Procedura $procedura)
    {
        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($procedura);
        return $rendicontazioneProceduraConfig->getIncrementoOccupazionaleNuoviDipendenti();
    }

    /**
     * @param Procedura $procedura
     * @return array|mixed
     */
    public function getDocumentiObbligatoriIncrementoOccupazionale(Procedura $procedura)
    {
        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($procedura);
        $documentiObbligatori = $rendicontazioneProceduraConfig->getIncrementoOccupazionaleDocumentiObbligatori();

        // Dai documenti obbligatori tolgo il documento ALTRO_DOCUMENTO_PERSONALE perché non obbligatorio.
        if (!empty($documentiObbligatori)) {
            if (($key = array_search(self::ALTRO_DOCUMENTO_PERSONALE, $documentiObbligatori)) !== false) {
                unset($documentiObbligatori[$key]);
            }
        } else {
            $documentiObbligatori = [];
        }
        return $documentiObbligatori;
    }

    /**
     * @param Procedura $procedura
     * @return array|mixed
     */
    public function getDocumentiIncrementoOccupazionale(Procedura $procedura)
    {
        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($procedura);
        $documentiObbligatori = $rendicontazioneProceduraConfig->getIncrementoOccupazionaleDocumentiObbligatori();
        if (empty($documentiObbligatori)) {
            $documentiObbligatori = [];
        }
        return $documentiObbligatori;
    }
    
    /**
     * @param Pagamento $pagamento
     * @return mixed|Response
     */
    public function dettaglioIncrementoOccupazionale(Pagamento $pagamento, $twig = null)
    {
        $options['disabled'] = $pagamento->isRichiestaDisabilitata();
        $options['url_indietro'] = $this->generateUrl('dettaglio_pagamento', ['id_pagamento' => $pagamento->getId()]);

        $richiesta = $pagamento->getRichiesta();
        $form = $this->createForm('AttuazioneControlloBundle\Form\IncrementoOccupazionale\ConfermaIncrementoOccupazionaleType', $pagamento, $options);

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $incremento_occupazionale_confermato = $form->get('incremento_occupazionale_confermato')->getData();
                    $attuazione = $pagamento->getAttuazioneControlloRichiesta();
                    $attuazione->setIncrementoOccupazionaleConfermato($incremento_occupazionale_confermato);
                    $em->persist($attuazione);

                    if ($incremento_occupazionale_confermato) {
                        foreach ($richiesta->getProponenti() as $proponente) {
                            $criteria = ['pagamento' => $pagamento->getId(), 'proponente' => $proponente->getId()];
                            $incremento_occupazionale = $em->getRepository('AttuazioneControlloBundle\Entity\IncrementoOccupazionale')->findOneBy($criteria);

                            if (is_null($incremento_occupazionale)) {
                                $incremento_occupazionale = new IncrementoOccupazionale();
                                $incremento_occupazionale->setPagamento($pagamento);
                                $incremento_occupazionale->setProponente($proponente);
                                $pagamento->addIncrementoOccupazionale($incremento_occupazionale);
                                $em->persist($pagamento);
                            }
                        }
                    } else {
                        // Rimuovo i record incremento occupazionale
                        foreach ($pagamento->getIncrementoOccupazionale() as $incremento_occupazionale) {
                            $em->remove($incremento_occupazionale);
                        }
                        
                        // Rimuovo gli eventuali record caricati per il personale
                        foreach ($pagamento->getPersonale() as $personale) {
                            foreach ($personale->getDocumentiPersonale() as $documentoPersonale) {
                                $em->remove($documentoPersonale->getDocumentoFile());
                                $em->remove($documentoPersonale);
                            }
                            $em->remove($personale);
                        }
                    }
                    
                    $em->flush();
                    
                    if ($incremento_occupazionale_confermato) {
                        return $this->addSuccesRedirect('Dati salvati correttamente', 'dettaglio_incremento_occupazionale', ['id_pagamento' => $pagamento->getId()]);
                    } else {
                        return $this->addSuccesRedirect('Dati salvati correttamente', 'dettaglio_pagamento', ['id_pagamento' => $pagamento->getId()]);
                    }
                } catch (Exception $e) {
                    $em->rollback();
                    $this->addError('Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l’assistenza.');
                }
            }
        }

        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Elenco progetti', $this->generateUrl('elenco_gestione_beneficiario'));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Elenco pagamenti', $this->generateUrl('elenco_pagamenti', ['id_richiesta' => $richiesta->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Dettaglio pagamento', $this->generateUrl('dettaglio_pagamento', ['id_pagamento' => $pagamento->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Conferma incremento occupazionale');

        $options['form'] = $form->createView();
        $options['pagamento'] = $pagamento;
        $options['dataInizio'] = $pagamento->getAttuazioneControlloRichiesta()->getDataAvvioProgettoConEventualeProroga();
        $options['dataFine'] = $pagamento->getAttuazioneControlloRichiesta()->getDataTermineProgettoConEventualeProroga();
        $options['config'] = $this->getRendicontazioneProceduraConfig($pagamento->getProcedura());
        
        $avviso = $this->getAvvisoSezioneIncrementoOccupazionale($richiesta->getProcedura());
        $caricamentoNuoviDipendenti = $this->getCaricamentoNuoviDipendenti($richiesta->getProcedura());
        $options['caricamentoNuoviDipendenti'] = $caricamentoNuoviDipendenti;
        // Nel caso in cui nell'avviso siano stati messi i placeholder per le date vado a fare un replace.
        $avviso = str_replace('DATA_DA', $options['dataInizio']->format('d/m/Y'), $avviso);
        $avviso = str_replace('DATA_A', $options['dataFine']->format('d/m/Y'), $avviso);
        $options['avviso'] = $avviso;
        
        if(is_null($twig)) {
            $twig = '@AttuazioneControllo/Pagamenti/IncrementoOccupazionale/confermaIncrementoOccupazionale.twig';
        }
        
        return $this->render($twig, $options);
    }

    /**
     * @param Pagamento $pagamento
     * @param Proponente $proponente
     * @param string $tipoDocumento
     * @return mixed|Response
     */
    public function caricaDocumentoIncrementoOccupazionale(Pagamento $pagamento, Proponente $proponente, string $tipoDocumento)
    {
        $em = $this->getEm();
        $documentoFile = new DocumentoFile();
        
        if ($tipoDocumento == 'ALTRO') {
            $options['lista_tipi'] = $em->getRepository('DocumentoBundle\Entity\TipologiaDocumento')->findBy(['tipologia' => 'rendicontazione_incremento_occupazionale_standard', 
                'codice' => [self::ALTRO_DOCUMENTO, self::F24, self::MODELLO_UNIFICATO_LAV]]);
        } else {
            $options['lista_tipi'] = $em->getRepository('DocumentoBundle\Entity\TipologiaDocumento')->findBy(['tipologia' => 'rendicontazione_incremento_occupazionale_standard', 'codice' => $tipoDocumento]);
        }

        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documentoFile, $options);
        $url_indietro = $this->generateUrl('dettaglio_incremento_occupazionale', ['id_pagamento' => $pagamento->getId()]);
        $form->add('pulsanti', 'BaseBundle\Form\SalvaIndietroType', ['url' => $url_indietro]);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $criteria = ['pagamento' => $pagamento->getId(), 'proponente' => $proponente->getId()];
                    $incrementoOccupazionale = $em->getRepository('AttuazioneControlloBundle\Entity\IncrementoOccupazionale')->findOneBy($criteria);
                    $documento = $this->container->get('documenti')->carica($documentoFile, 0);
                    if ($tipoDocumento == self::DM10_INIZIO) {
                        $incrementoOccupazionale->setAllegatoDmA($documentoFile);
                    } elseif ($tipoDocumento == self::DM10_FINE) {
                        $incrementoOccupazionale->setAllegatoDmB($documentoFile);
                    } else {
                        $documentoIncrementoOccupazionale = new DocumentoIncrementoOccupazionale();
                        $documentoIncrementoOccupazionale->setDocumentoFile($documento);
                        $documentoIncrementoOccupazionale->setIncrementoOccupazionale($incrementoOccupazionale);
                        $incrementoOccupazionale->addDocumentiIncrementoOccupazionale($documentoIncrementoOccupazionale);
                    }

                    $em->persist($incrementoOccupazionale);
                    $em->flush();

                    return $this->addSuccesRedirect('Documento caricato correttamente', 'dettaglio_incremento_occupazionale', ['id_pagamento' => $pagamento->getId()]);
                } catch (Exception $e) {
                    return $this->addErrorRedirect('Errore nel salvataggio del documento. Si prega di riprovare o contattare l’assistenza.', 'dettaglio_incremento_occupazionale', ['id_pagamento' => $pagamento->getId()]);
                }
            }
        }

        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Elenco progetti', $this->generateUrl('elenco_gestione_beneficiario'));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Elenco pagamenti', $this->generateUrl('elenco_pagamenti', ['id_richiesta' => $pagamento->getRichiesta()->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Dettaglio pagamento', $this->generateUrl('dettaglio_pagamento', ['id_pagamento' => $pagamento->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Incremento occupazionale', $this->generateUrl('dettaglio_incremento_occupazionale', ['id_pagamento' => $pagamento->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Carica documento');

        $form_view = $form->createView();
        return $this->render('@AttuazioneControllo/Pagamenti/IncrementoOccupazionale/caricaDocumentoIncrementoOccupazionale.html.twig', ['form' => $form_view,]);
    }

    /**
     * @param IncrementoOccupazionale $incrementoOccupazionale
     * @param DocumentoFile $documentoFile
     * @return mixed|GestoreResponse
     */
    public function eliminaDocumentoIncrementoOccupazionaleDM10(IncrementoOccupazionale $incrementoOccupazionale, DocumentoFile $documentoFile)
    {
        try {
            if (!is_null($documentoFile)) {
                if (!is_null($incrementoOccupazionale->getAllegatoDmA()) && $incrementoOccupazionale->getAllegatoDmA()->getId() == $documentoFile->getId()) {
                    $incrementoOccupazionale->setAllegatoDmA(null); 
                } elseif (!is_null($incrementoOccupazionale->getAllegatoDmB()) && $incrementoOccupazionale->getAllegatoDmB()->getId() == $documentoFile->getId()) {
                    $incrementoOccupazionale->setAllegatoDmB(null);
                }

                $em = $this->getEm();
                $em->persist($incrementoOccupazionale);

                $em->remove($documentoFile);
                $em->flush();
            }
            return new GestoreResponse($this->addSuccesRedirect('Documento eliminato correttamente',
                'dettaglio_incremento_occupazionale', ['id_pagamento' => $incrementoOccupazionale->getPagamento()->getId()]));
        } catch (Exception $e) {
            return $this->addErrorRedirect('Errore nell’eliminazione del documento. Si prega di riprovare o contattare l’assistenza.',
                'dettaglio_incremento_occupazionale', ['id_pagamento' => $incrementoOccupazionale->getPagamento()->getId()]);
        }
    }

    /**
     * @param DocumentoIncrementoOccupazionale $documentoIncrementoOccupazionale
     * @return mixed|GestoreResponse
     */
    public function eliminaDocumentoIncrementoOccupazionale(DocumentoIncrementoOccupazionale $documentoIncrementoOccupazionale)
    {
        /** @var IncrementoOccupazionale $incrementoOccupazionale */
        $incrementoOccupazionale = $documentoIncrementoOccupazionale->getIncrementoOccupazionale();
        try {
            if (!is_null($documentoIncrementoOccupazionale->getDocumentoFile())) {
                $em = $this->getEm();
                $em->remove($documentoIncrementoOccupazionale);
                $em->persist($incrementoOccupazionale);
                $em->flush();
            }
            return new GestoreResponse($this->addSuccesRedirect('Documento eliminato correttamente',
                'dettaglio_incremento_occupazionale', ['id_pagamento' => $incrementoOccupazionale->getPagamento()->getId()]));
        } catch (Exception $e) {
            return $this->addErrorRedirect('Errore nell’eliminazione del documento. Si prega di riprovare o contattare l’assistenza.',
                'dettaglio_incremento_occupazionale', ['id_pagamento' => $incrementoOccupazionale->getPagamento()->getId()]);
        }
    }
    
    /**
     * @param Pagamento $pagamento
     * @return mixed|Response
     */
    public function aggiungiNuovoDipendente(Pagamento $pagamento)
    {
        $options['disabled'] = $pagamento->isRichiestaDisabilitata();
        $options['url_indietro'] = $this->generateUrl('dettaglio_incremento_occupazionale', ['id_pagamento' => $pagamento->getId()]);

        $nuovoDipendente = new Personale();
        $richiesta = $pagamento->getRichiesta();
        $form = $this->createForm('AttuazioneControlloBundle\Form\IncrementoOccupazionale\NuovoDipendenteType', $nuovoDipendente, $options);

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $nuovoDipendente->setPagamento($pagamento);
                    $em->persist($nuovoDipendente);
                    $em->flush();
                    return $this->addSuccesRedirect('Dati salvati correttamente', 'modifica_nuovo_dipendente', ['id_nuovo_dipendente' => $nuovoDipendente->getId()]);
                } catch (Exception $e) {
                    $em->rollback();
                    $this->addError('Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l’assistenza.');
                }
            }
        }

        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Elenco progetti', $this->generateUrl('elenco_gestione_beneficiario'));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Elenco pagamenti', $this->generateUrl('elenco_pagamenti', ['id_richiesta' => $richiesta->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Dettaglio pagamento', $this->generateUrl('dettaglio_pagamento', ['id_pagamento' => $pagamento->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Incremento occupazionale', $this->generateUrl('dettaglio_incremento_occupazionale', ['id_pagamento' => $pagamento->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Nuovo dipendente');

        $options['form'] = $form->createView();
        $options['pagamento'] = $pagamento;
        $options['richiesta'] = $richiesta;
        $options['contratto'] = null;
        $options['comunicazione_centro_impiego'] = null;

        return $this->render('@AttuazioneControllo/Pagamenti/IncrementoOccupazionale/nuovoDipendente.html.twig', $options);
    }

    /**
     * @param Personale $personale
     * @return mixed|Response
     */
    public function modificaNuovoDipendente(Personale $personale)
    {
        $pagamento = $personale->getPagamento();
        $options['disabled'] = $pagamento->isRichiestaDisabilitata();
        $options['url_indietro'] = $this->generateUrl('dettaglio_incremento_occupazionale', ['id_pagamento' => $pagamento->getId()]);

        $em = $this->getEm();
        $form = $this->createForm('AttuazioneControlloBundle\Form\IncrementoOccupazionale\NuovoDipendenteType', $personale, $options);
        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $em->persist($personale);
                    $em->flush();
                    return $this->addSuccesRedirect('Dati salvati correttamente', 'dettaglio_incremento_occupazionale', ['id_pagamento' => $pagamento->getId()]);
                } catch (Exception $e) {
                    $em->rollback();
                    $this->addError('Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l’assistenza.');
                }
            }
        }

        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Elenco progetti', $this->generateUrl('elenco_gestione_beneficiario'));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Elenco pagamenti', $this->generateUrl('elenco_pagamenti', ['id_richiesta' => $pagamento->getRichiesta()->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Dettaglio pagamento', $this->generateUrl('dettaglio_pagamento', ['id_pagamento' => $pagamento->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Incremento occupazionale', $this->generateUrl('dettaglio_incremento_occupazionale', ['id_pagamento' => $pagamento->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Modifica nuovo dipendente');

        $options['form'] = $form->createView();
        $options['personale'] = $personale;

        $documentiObbligatori = $this->getDocumentiIncrementoOccupazionale($pagamento->getProcedura());

        $options['contratto'] = null;
        if (in_array(self::CONTRATTO_ASSUNZIONE, $documentiObbligatori)) {
            $options['contratto_da_caricare'] = true;
            $contratto = $em->getRepository('AnagraficheBundle:DocumentoPersonale')->findDocumentoPerPersonaECodice($personale,
                'rendicontazione_incremento_occupazionale_standard', self::CONTRATTO_ASSUNZIONE);
            if ($contratto) {
                $options['contratto'] = $contratto[0];
            }
        } else {
            $options['contratto_da_caricare'] = false;
        }
        
        $options['comunicazione_centro_impiego'] = null;
        if (in_array(self::COMUNICAZIONE_CENTRO_IMPIEGO, $documentiObbligatori)) {
            $options['comunicazione_centro_impiego_da_caricare'] = true;
            $comunicazione_centro_impiego = $em->getRepository('AnagraficheBundle:DocumentoPersonale')->findDocumentoPerPersonaECodice($personale,
                'rendicontazione_incremento_occupazionale_standard', self::COMUNICAZIONE_CENTRO_IMPIEGO);
            if ($comunicazione_centro_impiego) {
                $options['comunicazione_centro_impiego'] = $comunicazione_centro_impiego[0];
            }
        } else {
            $options['comunicazione_centro_impiego_da_caricare'] = false;
        }

        $options['altri_documenti'] = null;
        if (in_array(self::ALTRO_DOCUMENTO_PERSONALE, $documentiObbligatori)) {
            $options['altri_documenti_da_caricare'] = true;
            $altri_documenti = $em->getRepository('AnagraficheBundle:DocumentoPersonale')->findDocumentoPerPersonaECodice($personale,
                'rendicontazione_incremento_occupazionale_standard', self::ALTRO_DOCUMENTO_PERSONALE);
            foreach ($altri_documenti as $altro_documento ) {
                $options['altri_documenti'][] = $altro_documento;
            }
        } else {
            $options['altri_documenti_da_caricare'] = false;
        }
        
        return $this->render('@AttuazioneControllo/Pagamenti/IncrementoOccupazionale/nuovoDipendente.html.twig', $options);
    }

    /**
     * @param Personale $personale
     * @return mixed|GestoreResponse
     * @throws Exception
     */
    public function eliminaNuovoDipendente(Personale $personale)
    {
        $pagamento = $personale->getPagamento();
        if (is_null($personale)) {
            throw new Exception('Il nuovo dipendente indicato non esiste');
        }

        $isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();

        if ($isRichiestaDisabilitata) {
            throw new Exception('Impossibile effettuare questa operazione');
        }
        $em = $this->getEm();
        
        foreach ($personale->getDocumentiPersonale() as $documentoPersonale) {
            $em->remove($documentoPersonale->getDocumentoFile());
            $em->remove($documentoPersonale);
        }
        
        $em->remove($personale);
        $em->flush();
        return new GestoreResponse($this->addSuccesRedirect('Nuovo dipendente rimosso correttamente',
            'dettaglio_incremento_occupazionale', ['id_pagamento' => $pagamento->getId()]));
    }

    /**
     * @param Pagamento $pagamento
     * @param Personale $personale
     * @param string $tipoDocumento
     * @return mixed|Response
     */
    public function caricaDocumentoPersonaleIncrementoOccupazionale(Pagamento $pagamento, Personale $personale, string $tipoDocumento)
    {
        $em = $this->getEm();
        $documentiValidi = $em->getRepository('DocumentoBundle\Entity\TipologiaDocumento')->findBy(['tipologia' => 'rendicontazione_incremento_occupazionale_standard']);

        $tipoDocumentoRichiesto = array_filter(
            $documentiValidi,
            function ($e) use (&$tipoDocumento) {
                return $e->getCodice() == $tipoDocumento;
            }
        );
        
        if (empty($tipoDocumentoRichiesto)) {
            $this->addFlash('error', 'Tipologia di documento non valida.');
            return $this->addErrorRedirect('Tipologia di documento non valida.', 'modifica_nuovo_dipendente', ['id_nuovo_dipendente' => $personale->getId()]);
        }
        
        $documentoFile = new DocumentoFile();
        $options['lista_tipi'] = $em->getRepository('DocumentoBundle\Entity\TipologiaDocumento')->findBy(['tipologia' => 'rendicontazione_incremento_occupazionale_standard', 'codice' => $tipoDocumento]);
        
        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documentoFile, $options);
        $url_indietro = $this->generateUrl('modifica_nuovo_dipendente', ['id_nuovo_dipendente' => $personale->getId()]);
        $form->add('pulsanti', 'BaseBundle\Form\SalvaIndietroType', ['url' => $url_indietro]);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $this->container->get('documenti')->carica($documentoFile, 0);
                    
                    $documentoPersonale = new DocumentoPersonale();
                    $documentoPersonale->setDocumentoFile($documentoFile);
                    $documentoPersonale->setPersonale($personale);
                    
                    $em->persist($documentoPersonale);
                    $em->flush();

                    return $this->addSuccesRedirect('Documento caricato correttamente', 'modifica_nuovo_dipendente', ['id_nuovo_dipendente' => $personale->getId()]);
                } catch (Exception $e) {
                    return $this->addErrorRedirect('Errore nel salvataggio del documento. Si prega di riprovare o contattare l’assistenza.', 'modifica_nuovo_dipendente', ['id_nuovo_dipendente' => $personale->getId()]);
                }
            }
        }
        
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Elenco progetti', $this->generateUrl('elenco_gestione_beneficiario'));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Elenco pagamenti', $this->generateUrl('elenco_pagamenti', ['id_richiesta' => $pagamento->getRichiesta()->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Dettaglio pagamento', $this->generateUrl('dettaglio_pagamento', ['id_pagamento' => $pagamento->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Incremento occupazionale', $this->generateUrl('dettaglio_incremento_occupazionale', ['id_pagamento' => $pagamento->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Nuovo dipendente', $this->generateUrl('modifica_nuovo_dipendente', ['id_nuovo_dipendente' => $personale->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Carica documento dipendente');
        
        $form_view = $form->createView();
        return $this->render('@AttuazioneControllo/Pagamenti/IncrementoOccupazionale/caricaDocumentoIncrementoOccupazionale.html.twig', ['form' => $form_view,]);
    }
    
    /**
     * @param DocumentoPersonale $documentoPersonale
     * @return mixed|GestoreResponse
     */
    public function eliminaDocumentoPersonaleIncrementoOccupazionale(DocumentoPersonale $documentoPersonale)
    {
        try {
            $em = $this->getEm();
            $documentoFile = $documentoPersonale->getDocumentoFile();
            if (!is_null($documentoFile)) {
                $em->remove($documentoFile);
                $em->remove($documentoPersonale);
                $em->flush();
            }
            return new GestoreResponse($this->addSuccesRedirect('Documento eliminato correttamente',
                'modifica_nuovo_dipendente', ['id_nuovo_dipendente' => $documentoPersonale->getPersonale()->getId()]));
        } catch (Exception $e) {
            return $this->addErrorRedirect('Errore nell’eliminazione del documento. Si prega di riprovare o contattare l’assistenza.',
                'modifica_nuovo_dipendente', ['id_nuovo_dipendente' => $documentoPersonale->getPersonale()->getId()]);
        }
    }

    /**
     * @param Pagamento $pagamento
     * @return mixed|EsitoValidazione
     */
    public function validaIncrementoOccupazionale(Pagamento $pagamento)
    {
        $esito = new EsitoValidazione(true);
        // Se è stato scelto di rinunciare restituisco true
        if ($pagamento->getAttuazioneControlloRichiesta()->isIncrementoOccupazionaleConfermato() === false
            && !$pagamento->getIncrementoOccupazionale()->count()) {
            return $esito;
        }

        if ($pagamento->getAttuazioneControlloRichiesta()->isIncrementoOccupazionaleConfermato() === null) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione('Indicare se si intende confermare o meno l’incremento occupazionale indicato in fase di presentazione');
            return $esito;
        }
        
        $richiesta = $pagamento->getRichiesta();
        $em = $this->getEm();

        foreach ($richiesta->getProponenti() as $proponente) {
            $criteria = ['pagamento' => $pagamento->getId(), 'proponente' => $proponente->getId()];
            $incrementoOccupazionale = $em->getRepository('AttuazioneControlloBundle\Entity\IncrementoOccupazionale')->findOneBy($criteria);

            if ($richiesta->getProponenti()->count() > 1) {
                $testoProponente = $proponente->getDenominazione() . ': ';
            } else {
                $testoProponente = '';
            }

            if (is_null($incrementoOccupazionale)) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione('Nessun dato inserito per ' . $proponente->getDenominazione());
                return $esito;
            }

            if (is_null($incrementoOccupazionale->getOccupatiInDataA())) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione($testoProponente . 'Inserire il numero di occupati alla data iniziale');
            }

            if (is_null($incrementoOccupazionale->getOccupatiInDataB())) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione($testoProponente . 'Inserire il numero di occupati alla data finale');
            }

            if ($incrementoOccupazionale->getOccupatiInDataA() > 0 && is_null($incrementoOccupazionale->getAllegatoDmA())) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione($testoProponente . 'Caricare modello DM10 alla data iniziale');
            }

            if ($incrementoOccupazionale->getOccupatiInDataB() > 0 && is_null($incrementoOccupazionale->getAllegatoDmB())) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione($testoProponente . 'Caricare modello DM10 alla data finale');
            }
        }
        
        if ($this->getCaricamentoNuoviDipendenti($richiesta->getProcedura()) == true) {
            if (!$pagamento->getPersonale()->count()) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione('Inserire i nuovi dipendenti');
            } else {
                $documentiObbligatori = $this->getDocumentiObbligatoriIncrementoOccupazionale($richiesta->getProcedura());
                if ($documentiObbligatori) {
                    $arrayEsitoControllo = [];
                    foreach ($pagamento->getPersonale() as $personale) {
                        if ($personale->getDocumentiPersonale()->count() > 0) {
                            foreach ($documentiObbligatori as $documentoObbligatorio) {
                                $arrayEsitoControllo[$personale->getId()]['nome'] = $personale->getNome() . ' ' . $personale->getCognome();
                                $arrayEsitoControllo[$personale->getId()]['documenti'][$documentoObbligatorio] = false;

                                foreach ($personale->getDocumentiPersonale() as $documentoPersonale) {
                                    if ($documentoPersonale->getDocumentoFile()->getTipologiaDocumento()->getCodice() == $documentoObbligatorio) {
                                        $arrayEsitoControllo[$personale->getId()]['documenti'][$documentoObbligatorio] = true;
                                    }
                                }
                            }
                        } else {
                            foreach ($documentiObbligatori as $documentoObbligatorio) {
                                $arrayEsitoControllo[$personale->getId()]['nome'] = $personale->getNome() . ' ' . $personale->getCognome();
                                $arrayEsitoControllo[$personale->getId()]['documenti'][$documentoObbligatorio] = false;
                            }
                        }
                    }

                    foreach ($arrayEsitoControllo as $esitoControlloDocumenti) {
                        $messaggioErrore = [];
                        foreach ($esitoControlloDocumenti['documenti'] as $codiceDocumento => $esitoDocumento) {
                            if (!$esitoDocumento) {
                                $messaggioErrore[] =  $codiceDocumento;
                            }
                        }

                        if ($messaggioErrore) {
                            $esito->setEsito(false);
                            $arrayErrori = [];
                            foreach ($messaggioErrore as $errore) {
                                $descrizioneDocumento = $em->getRepository('DocumentoBundle\Entity\TipologiaDocumento')
                                    ->findOneBy(['tipologia' => 'rendicontazione_incremento_occupazionale_standard','codice' => $errore]);
                               
                                $arrayErrori[] = strtolower($descrizioneDocumento->getDescrizione());
                            }
                            $esito->addMessaggioSezione('Per il nuovo dipendente ' . $esitoControlloDocumenti['nome'] . ' caricare: ' . implode(', ' , $arrayErrori));
                        }
                    }
                }
            }
        }

        return $esito;
    }
}
