<?php

namespace AttuazioneControlloBundle\Service;

use AttuazioneControlloBundle\Entity\DocumentoPagamento;
use AttuazioneControlloBundle\Form\PagamentoType;
use BaseBundle\Exception\SfingeException;
use Exception;
use FascicoloBundle\Entity\IstanzaFascicolo;
use FascicoloBundle\Entity\IstanzaPagina;
use RichiesteBundle\Utility\EsitoValidazione;
use SfingeBundle\Entity\Utente;
use SoggettoBundle\Entity\TipoIncarico;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\StatoPagamento;
use DocumentoBundle\Entity\TipologiaDocumento;
use RichiesteBundle\Service\GestoreResponse;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Entity\ProceduraAggiudicazione;
use DocumentoBundle\Component\ResponseException;
use RichiesteBundle\Entity\IndicatoreOutput;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use MonitoraggioBundle\Form\IndicatoreOutputType;
use DocumentoBundle\Form\Type\DocumentoFileSimpleType;
use DocumentoBundle\Entity\DocumentoFile;
use BaseBundle\Form\SalvaIndietroType;
use AttuazioneControlloBundle\Entity\DocumentoImpegno;
use AttuazioneControlloBundle\Form\DocumentoImpegnoType;
use AttuazioneControlloBundle\Form\ImpegnoType;
use AttuazioneControlloBundle\Form\ProgettoProceduraAggiudicazioneType;
use AttuazioneControlloBundle\Form\ProceduraAggiudicazioneBeneficiarioType;
use CipeBundle\Entity\Classificazioni\CupNatura;
use AttuazioneControlloBundle\Form\RichiestaFaseProceduraleType;
use AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;

class GestorePagamentiBase extends AGestorePagamenti {

    public function elencoPagamenti($id_richiesta) {

        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $protocollo = $richiesta->getProtocollo();

        $dati = array(
            'richiesta' => $richiesta,
            'rendicontazioneProceduraConfig' => $this->getRendicontazioneProceduraConfig($richiesta->getProcedura())
        );

        $this->container->get("pagina")->resettaBreadcrumb();
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti progetto {$protocollo}");

        return $this->render("AttuazioneControlloBundle:Pagamenti:elencoPagamenti.html.twig", $dati);
    }

    /**
     * @param $pagamento
     * @param $tipologiaQuestionarioRsi
     * @return void
     * @throws Exception
     */
    public function aggiungiFascicoloPagamento($pagamento, $tipologiaQuestionarioRsi) {
        $em = $this->getEm();
        $procedura = $pagamento->getProcedura();
        $fascicoli = $procedura->getFascicoliProceduraRendiconto();
        $soggetto = $pagamento->getRichiesta()->getSoggetto();

        // Il questionario RSI va somministrato solamente alle aziende.
        // Andiamo a creare il fascicolo del questionario RSI se tale fascicolo non è ancora presente.
        // Le procedure 8 e 32 sono escluse.
        if (count($fascicoli) == 0 && $pagamento->getAttuazioneControlloRichiesta()->hasQuestionarioRSI()) {
            if ($tipologiaQuestionarioRsi == 'IMPRESE_MANIFATTURIERE') {
                $pagine = $em->getRepository("FascicoloBundle\Entity\Pagina")->getFascicoloAlias('principi_rsi_imprese_manifatturiere');
            } elseif ($tipologiaQuestionarioRsi == 'IMPRESE_DI_SERVIZI') {
                $pagine = $em->getRepository("FascicoloBundle\Entity\Pagina")->getFascicoloAlias('principi_rsi_imprese_di_servizi');
            } else {
                throw new Exception("Questionario non selezionato.");
            }

            $pagina = $pagine[0];
            $fascicolo = $pagina->getFascicolo();
            $istanzaFascicolo = new IstanzaFascicolo();
            $istanzaFascicolo->setFascicolo($fascicolo);
            $indice = new IstanzaPagina();
            $indice->setPagina($fascicolo->getIndice());
            $istanzaFascicolo->setIndice($indice);
            $pagamento->setIstanzaFascicolo($istanzaFascicolo);

            // ci può essere un solo fascicolo per un pagamento
        } elseif (count($fascicoli) > 1) {
            throw new Exception("Non è possibile associare più di un fascicolo ad un pagamento");

            // se è stato esplicitamente definito un fascicolo rendiconto per la procedura prendo quello (dovrebbe servire per gestire il pregresso)
        } elseif (count($fascicoli) == 1) {
            $fascicolo = $fascicoli[0]->getFascicolo();
            $istanzaFascicolo = new IstanzaFascicolo();
            $istanzaFascicolo->setFascicolo($fascicolo);

            $indice = new IstanzaPagina();
            $indice->setPagina($fascicolo->getIndice());
            $istanzaFascicolo->setIndice($indice);

            $pagamento->setIstanzaFascicolo($istanzaFascicolo);
        }
    }

    /**
     * @param Pagamento $pagamentoAttuale
     */
    public function aggiungiGiustificativiConImportiDaRipresentare(Pagamento $pagamentoAttuale) {
        $pagamentoPrecedente = $pagamentoAttuale->getPagamentoPrecedente($pagamentoAttuale);
        if ($pagamentoPrecedente) {
            $pagamentoPrecedente->creaGiustificativiConImportiDaRipresentare($pagamentoAttuale);
        }
    }
    
     /**
     * @param Pagamento $pagamentoAttuale
     * Funzione ridefinita per essere usata da command che aggiorna 
     * i giustificativi ripresentati in un pagamento già creato
     */
    public function aggiornaGiustificativiConImportiDaRipresentare(Pagamento $pagamentoAttuale) {
        $pagamentoPrecedente = $pagamentoAttuale->getPagamentoPrecedente($pagamentoAttuale);
        $em = $this->getEm();
        $esclusi = array();
        $giustificativiRiportati = $em->getRepository(\AttuazioneControlloBundle\Entity\GiustificativoPagamento::class)->getGiustificativiByPagamentoRinviati($pagamentoAttuale->getId());
        foreach ($giustificativiRiportati as $giustificativo) {
            $esclusi[] = $giustificativo->getGiustificativoOrigine()->getId();
        }
        if ($pagamentoPrecedente) {
            $pagamentoPrecedente->aggiornaGiustificativiConImportiDaRipresentare($pagamentoAttuale, $esclusi);
        }
    }

    public function aggiungiObiettiviRealizzativi($richiesta, $pagamento) {
        $orps = array();
        foreach ($richiesta->getObiettiviRealizzativi() as $or) {
            $orp = new \AttuazioneControlloBundle\Entity\ObiettivoRealizzativoPagamento();
            $orp->setObiettivoRealizzativo($or);
            $orp->setPagamento($pagamento);
            $orps[] = $orp;
        }
        $pagamento->setObiettiviRealizzativi($orps);
    }

    public function calcolaImportoRichiestoIniziale($pagamento) {
        $richiesta = $pagamento->getRichiesta();
        $modalita_pagamento = $pagamento->getModalitaPagamento();
        $modalita_pagamento_procedura = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura")->findOneBy(array("procedura" => $richiesta->getProcedura(), "modalita_pagamento" => $modalita_pagamento));

        if (!$modalita_pagamento->getRichiedeGiustificativi() && !is_null($modalita_pagamento_procedura) && !is_null($modalita_pagamento_procedura->getPercentualeContributo())) {
            $pagamento->setImportoRichiesto($modalita_pagamento_procedura->getPercentualeContributo() * $richiesta->getIstruttoria()->getContributoAmmesso() / 100);
        }

        return null;
    }

    public function aggiungiPagamento($id_richiesta) {
        $em = $this->getEm();
        $richiesta = $em->getRepository(Richiesta::class)->find($id_richiesta);

        if ($richiesta->getAttuazioneControllo()->isRevocatoTotale()) {
            return $this->addErrorRedirect("IMPOSSIBILE AGGIUNGERE UN PAGAMENTO PERCHÉ IL PROGETTO È STATO OGGETTO DI REVOCA TOTALE. SI PREGA DI CONTATTARE L'AMMINISTRAZIONE PER CHIARIMENTI.", "elenco_pagamenti", array("id_richiesta" => $richiesta->getId()));
        }

        if ($richiesta->getAttuazioneControllo()->hasPagamentoPendente()) {
            return $this->addErrorRedirect("È già presente un pagamento non inviato alla PA o non ancora valutato", "elenco_pagamenti", array("id_richiesta" => $richiesta->getId()));
        }

        $attiva = $richiesta->getProcedura()->getRendicontazioneAttiva();
        if (!$attiva) {
            return $this->addErrorRedirect("Impossibile inviare una nuova trasmissione rendicontazione: la rendicontazione non risulta attiva.", "elenco_pagamenti", array("id_richiesta" => $richiesta->getId()));
        }
        $atc = $richiesta->getAttuazioneControllo();
        $pagamento = new Pagamento($atc);
        $pagamento->setAbilitaRendicontazioneChiusa(false);

        $options = array();
        $options["url_indietro"] = $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $id_richiesta));
        $options["modalita_pagamento"] = $this->getModalitaPagamento($richiesta);
        $options["firmatabili"] = $em->getRepository("SoggettoBundle:Soggetto")->getFirmatariAmmissibili($richiesta->getSoggetto());

        $form = $this->createForm(PagamentoType::class, $pagamento, $options);

        $request = $this->getCurrentRequest();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $modalitaPagamento = $pagamento->getModalitaPagamento();

            if (!is_null($modalitaPagamento) && $modalitaPagamento->getUnico() && $richiesta->getAttuazioneControllo()->hasPagamentoApprovatoConModalita($modalitaPagamento->getCodice())) {
                $form->get("modalita_pagamento")->addError(new \Symfony\Component\Form\FormError("È già stato approvato un pagamento per la modalità specificata, e non è possibile inserirne ulteriori"));
            }

            if (!is_null($modalitaPagamento) && !$this->isRendicontazioneAttivaPerModalitaPagamento($pagamento)) {
                $form->get("modalita_pagamento")->addError(new \Symfony\Component\Form\FormError("la rendicontazione non risulta essere attiva per la modalita pagamento selezionata"));
            }

            if ($form->isValid()) {
                // Creo gli eventuali giustificativi con gli importi da presentare nelle rendicontazioni successive
                $this->aggiungiGiustificativiConImportiDaRipresentare($pagamento);

                $this->calcolaImportoRichiestoIniziale($pagamento);
                try {
                    $em->beginTransaction();

                    //$this->aggiungiFascicoloPagamento($pagamento);

                    $em->persist($pagamento);
                    // errore perchè il pagamento non è flushato, forse meglio fare una transazione
                    $em->flush();
                    $this->container->get("sfinge.stati")->avanzaStato($pagamento, "PAG_INSERITO");
                    //aggiorno le spese generali dopo ripresentati
                    $this->gestioneGiustificativoSpeseGenerali($pagamento);
                    $em->flush();
                    $em->commit();
                    return $this->addSuccesRedirect("Il pagamento è stato correttamente aggiunto", "elenco_pagamenti", array("id_richiesta" => $id_richiesta));
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

    public function dettaglioPagamento($id_pagamento, $twig = null) {
        $this->getSession()->set("id_pagamento", $id_pagamento);
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $isRichiestaFirmaDigitale = $pagamento->getProcedura()->getRendicontazioneProceduraConfig()->isRichiestaFirmaDigitale();
        $isUtenteAbilitatoPagamenti = true;
        if (!$isRichiestaFirmaDigitale) {
            $isUtenteAbilitatoPagamenti = $this->isUtenteAbilitatoPagamenti($pagamento, $this->getUser());
        }

        if ($this->isRendicontazioneScaduta($id_pagamento) == true && !$pagamento->isInviato()) {
            $this->addError("La presentazione delle domande di pagamento per questa fase di rendicontazione è scaduta e non sarà possibile inviare il pagamento");
        }

        // segnaliamo un warning per i pagamenti inviati prima della rendicontazione standard
        // che chiaramente avranno le nuove sezioni aggiunte incomplete
        $dataInvio = $pagamento->getDataInvio();
        $dataLimite = new \DateTime('2018-01-01');
        if ($pagamento->isInviato() && !is_null($dataInvio) && $dataInvio < $dataLimite) {
            $this->addWarning("Non si tenga conto di eventuali sezioni incomplete per pagamenti già inviati");
        }

        $incrementoOccAltri = $this->verificaTuttiIncrementiOccupazionali($pagamento);

        $dati = array("pagamento" => $pagamento);
        $dati["avanzamenti"] = $this->gestioneBarraAvanzamento($pagamento);
        $dati["richiesta"] = $richiesta;
        $dati["variazione_pendente"] = false;
        $dati["proroga_pendente"] = false;
        $dati["oggetti_richiesta"] = $richiesta->getOggettiRichiesta();
        $dati['rendicontazioneProceduraConfig'] = $this->getRendicontazioneProceduraConfig($richiesta->getProcedura());
        $dati['pagamentoInviabile'] = $this->isPagamentoInviabile($pagamento);
        $dati['incrementoOccAltri'] = $incrementoOccAltri;
        $dati['isUtenteAbilitatoPagamenti'] = $isUtenteAbilitatoPagamenti;

        $variazioni = $pagamento->getAttuazioneControlloRichiesta()->getVariazioni();
        $ultimaVariazione = $variazioni->last();
        if ($ultimaVariazione != false && $ultimaVariazione->isStatoFinale() && is_null($ultimaVariazione->getEsitoIstruttoria())) {
            $dati["variazione_pendente"] = true;
        }

        $proroghe = $pagamento->getAttuazioneControlloRichiesta()->getProroghe();
        $ultimaProroga = $proroghe->last();
        if ($ultimaProroga != false && $ultimaProroga->isStatoFinale() && $ultimaProroga->getGestita() == 0) {
            $dati["proroga_pendente"] = true;
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento");

        // gli anticipi sono abbastanza diversi dalle altre modalità, per cui dobbiamo necessariamente trattarli a parte
        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            $twig = "AttuazioneControlloBundle:Pagamenti:dettaglioPagamentoAnticipo.html.twig";
        }

        if (is_null($twig)) {
            $twig = "AttuazioneControlloBundle:Pagamenti:dettaglioPagamento.html.twig";
        }

        return $this->render($twig, $dati);
    }

    public function eliminaPagamento($id_pagamento) {
        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $id_richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta()->getId();

        if (in_array($pagamento->getStato()->getCodice(), array(StatoPagamento::PAG_INVIATO_PA, StatoPagamento::PAG_PROTOCOLLATO))) {
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
            return $this->addSuccesRedirect("Il pagamento è stato correttamente eliminato", "elenco_pagamenti", array("id_richiesta" => $id_richiesta));
        } catch (ResponseException $e) {
            $em->rollback();
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "elenco_pagamenti", array("id_richiesta" => $id_richiesta));
        }
    }

    /**
     * @param Richiesta $richiesta
     * @return ModalitaPagamento[]
     */
    public function getModalitaPagamento($richiesta) {
        /** @var ModalitaPagamentoProcedura[] $modalita_pagamento_procedura */
        $modalita_pagamento_procedura = $richiesta->getProcedura()->getModalitaPagamento()->toArray();
        /** @var ModalitaPagamentoProcedura[] $modalitaPagamentoProceduraAttivi */
        $modalitaPagamentoProceduraAttivi = \array_filter($modalita_pagamento_procedura,
            function (ModalitaPagamentoProcedura $modalitaProcedura) use ($richiesta) {
                $now = new \DateTime();
                $inIntervalloRendicontazione = $now >= $modalitaProcedura->getDataInizioRendicontazione() && $now < $modalitaProcedura->getDataFineRendicontazione();
                $isScorrimento = $richiesta->getAbilitaScorrimento();
                $finestraTemporaleModalitaPagamento = $modalitaProcedura->getFinestraTemporale() ?: $richiesta->getFinestraTemporale() ?: 0;
                $finestraTemporaleRichiesta = $richiesta->getFinestraTemporale() ?: 0;
                return ($inIntervalloRendicontazione || $isScorrimento) && (
                $finestraTemporaleModalitaPagamento == $finestraTemporaleRichiesta
                );
            });

        /** @var ModalitaPagamento[] $modalitaPagamentoAttivi */
        $modalitaPagamentoAttivi = \array_map(function (ModalitaPagamentoProcedura $modalitaProcedura) {
            return $modalitaProcedura->getModalitaPagamento();
        }, $modalitaPagamentoProceduraAttivi);

        /** @var ModalitaPagamento[] $modalitaPagamentoNonPresentati */
        $modalitaPagamentoNonPresentati = \array_filter($modalitaPagamentoAttivi, function (ModalitaPagamento $modalita) use ($richiesta) {
            $atc = $richiesta->getAttuazioneControllo();

            return !$atc->hasPagamentoApprovatoConModalita($modalita->getCodice());
        });

        /** @var float $ordineCronologicoMinimo */
        $ordineCronologicoMinimo = \array_reduce($modalitaPagamentoNonPresentati, function (float $ordinePrecedente, ModalitaPagamento $modalita) {
            $ordine = $modalita->getOrdineCronologico();
            return ($ordine ?? \INF) < $ordinePrecedente ? $ordine : $ordinePrecedente;
        }, \INF);

        $modalitaPagamentoPresentabili = \array_filter($modalitaPagamentoNonPresentati, function (ModalitaPagamento $modalita) use ($ordineCronologicoMinimo) {
            $hasOrdineMinimo = $modalita->getOrdineCronologico() == $ordineCronologicoMinimo;
            $codice = $modalita->getCodice();

            $isModalitaSemprePresentabile = \in_array($codice, [
                ModalitaPagamento::UNICA_SOLUZIONE,
                ModalitaPagamento::SALDO_FINALE,
            ]);

            return $hasOrdineMinimo || $isModalitaSemprePresentabile;
        });

        if (\count($modalitaPagamentoPresentabili) == 0) {
            return $this->getModalitaFinanziamentoProgoga($richiesta);
        }

        return $modalitaPagamentoPresentabili;
    }

    protected function getModalitaFinanziamentoProgoga(Richiesta $richiesta): array {
        /** @var ModalitaPagamentoProcedura[] $modalita_pagamento_procedura */
        $modalita_pagamento_procedura = $richiesta->getProcedura()->getModalitaPagamento()->toArray();
        /** @var ModalitaPagamentoProcedura[] $modalitaPagamentoProceduraAttivi */
        $modalitaPagamentoProceduraAttivi = \array_filter($modalita_pagamento_procedura,
            function (ModalitaPagamentoProcedura $modalitaProcedura) use ($richiesta) {
                $modalita = $modalitaProcedura->getModalitaPagamento();

                return $richiesta->getAttuazioneControllo()->hasProrogaRendicontazione($modalita);
            });
        /** @var ModalitaPagamento[] $modalitaConProroga */
        $modalitaConProroga = \array_map(function (ModalitaPagamentoProcedura $mpr) {
            return $mpr->getModalitaPagamento();
        }, $modalitaPagamentoProceduraAttivi);

        /** @var float $ordineCronologicoMinimo */
        $ordineCronologicoMinimo = \array_reduce($modalitaConProroga, function (float $ordinePrecedente, ModalitaPagamento $modalita) {
            $ordine = $modalita->getOrdineCronologico();
            return ($ordine ?? \INF) < $ordinePrecedente ? $ordine : $ordinePrecedente;
        }, \INF);

        /** @var ModalitaPagamento[] $modalitaPagamentoPresentabili */
        $modalitaPagamentoPresentabili = \array_filter($modalitaConProroga, function (ModalitaPagamento $modalita) use ($ordineCronologicoMinimo) {
            return $modalita->getOrdineCronologico() == $ordineCronologicoMinimo;
        });

        return $modalitaPagamentoPresentabili;
    }

    public function validaPagamento($id_pagamento) {
        ini_set('memory_limit', '512M');
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getRichiesta();

        // Faccio il controllo nel caso in cui il bando preveda la validazione solamente da parte del legale rappresentante o del delegato.
        if (!$pagamento->getProcedura()->getRendicontazioneProceduraConfig()->isRichiestaFirmaDigitale()) {
            if (!$this->isUtenteAbilitatoPagamenti($pagamento, $this->getUser())) {
                return new GestoreResponse($this->addErrorRedirect("Impossibile procedere, solamente il legale rappresentante o un suo delegato possono effettuare la validazione", "dettaglio_pagamento", array('id_pagamento' => $id_pagamento)));
            }
        }

        if (!$pagamento->isValidabile()) {
            return new GestoreResponse($this->addErrorRedirect("Stato non valido per effettuare la validazione", "dettaglio_pagamento", array('id_pagamento' => $id_pagamento)));
        }

        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            $esitoValidazione = $this->controllaValiditaAnticipoPagamento($id_pagamento);
        } else {
            $esitoValidazione = $this->controllaValiditaPagamento($id_pagamento);
        }

        if (!$esitoValidazione->getEsito()) {
            return new GestoreResponse($this->addErrorRedirect("Pagamento non validabile", "dettaglio_pagamento", array('id_pagamento' => $id_pagamento)));
        }

        if (!is_null($pagamento->getDocumentoPagamento())) {
            $this->container->get("documenti")->cancella($pagamento->getDocumentoPagamento(), 1);
        }

        //genero il nuovo pdf
        $pdf = $this->generaPdf($id_pagamento, false, false);

        //avanzo lo stato del pagamento
        $this->container->get("sfinge.stati")->avanzaStato($pagamento, StatoPagamento::PAG_VALIDATO);
        $em = $this->getEm();

        //lo persisto
        $tipoDocumento = $em->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice(TipologiaDocumento::PAGAMENTO_CONTRIBUTO);
        $documentoPagamento = $this->container->get("documenti")->caricaDaByteArray($pdf, $this->getNomePdfPagamento($pagamento) . ".pdf", $tipoDocumento, false, $richiesta);

        //associo il documento al pagamento
        $pagamento->setDocumentoPagamento($documentoPagamento);
        $em->persist($pagamento);

        try {
            $em->flush();
        } catch (\Exception $e) {
            return new GestoreResponse($this->addErrorRedirect("Si è verificato un errore durante il salvataggio delle informazioni", "dettaglio_pagamento", array('id_pagamento' => $id_pagamento)));
        }

        return new GestoreResponse($this->addSuccesRedirect("Pagamento validato", "dettaglio_pagamento", array('id_pagamento' => $id_pagamento)));
    }

    public function invalidaPagamento($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        if ($pagamento->getStato()->uguale(StatoPagamento::PAG_VALIDATO) ||
            $pagamento->getStato()->uguale(StatoPagamento::PAG_FIRMATO)) {
            $this->container->get("sfinge.stati")->avanzaStato($pagamento, StatoPagamento::PAG_INSERITO, true);
            return new GestoreResponse($this->addSuccesRedirect("Pagamento invalidato", "dettaglio_pagamento", array('id_pagamento' => $id_pagamento)));
        }
        throw new SfingeException("Stato non valido per effettuare la validazione");
    }

    public function inviaPagamento($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        if ($this->isRendicontazioneScaduta($id_pagamento) == true) {
            throw new SfingeException("La rendicontazione è chiusa e non è possibile inviare il pagamento");
        }

        // Faccio il controllo nel caso in cui il bando preveda l'invio solamente da parte del legale rappresentante o del delegato.
        if (!$pagamento->getProcedura()->getRendicontazioneProceduraConfig()->isRichiestaFirmaDigitale()) {
            if (!$this->isUtenteAbilitatoPagamenti($pagamento, $this->getUser())) {
                throw new SfingeException("Impossibile procedere, solamente il legale rappresentante o un suo delegato possono inviare il pagamento");
            }
        }

        $controlloModalita = $this->isPagamentoInviabile($pagamento);
        if ($pagamento->getStato()->uguale(StatoPagamento::PAG_FIRMATO) && $pagamento->isInoltrabile() && $controlloModalita->inviabile == true) {
            $pagamento->setDataInvio(new \DateTime());
            /*
             * Popolamento tabelle protocollazione
             */
            if ($this->container->getParameter("stacca_protocollo_al_volo")) {
                //stacca protocollo
                //$this->container->get("docerinitprotocollazione")->setTabProtocollazionePagamento($pagamento);
                $this->container->get("docerinitprotocollazione")->setTabProtocollazioneLottiPagamento($pagamento);
            }

            if ($pagamento->getAttuazioneControlloRichiesta()->getRichiesta()->getProcedura()->getAsse()->getCodice() == 'A7' || $pagamento->getAttuazioneControlloRichiesta()->getRichiesta()->getProcedura()->isIngegneriaFinanziaria()) {
                $this->container->get("sfinge.stati")->avanzaStato($pagamento, StatoPagamento::PAG_PROTOCOLLATO);
            } else {
                $this->container->get("sfinge.stati")->avanzaStato($pagamento, StatoPagamento::PAG_INVIATO_PA);
            }

            $this->getEm()->persist($pagamento);
            $this->getEm()->flush();

            return new GestoreResponse($this->addSuccesRedirect("Pagamento inviato correttamente", "dettaglio_pagamento", array('id_pagamento' => $id_pagamento)));
        }
        throw new SfingeException("Stato non valido per effettuare l''inoltro o inoltro non abilitato");
    }

    /**
     * @param Pagamento $pagamento
     * @return array
     */
    public function gestioneBarraAvanzamento($pagamento)
    {
        $statoRichiesta = $pagamento->getStato()->getCodice();
        $arrayStati = array('Inserito' => true, 'Validato' => false, 'Firmato' => false, 'Inviato' => false);

        switch ($statoRichiesta) {
            case 'PAG_PROTOCOLLATO':
            case 'PAG_INVIATO_PA':
                $arrayStati['Inviato'] = true;
            case 'PAG_FIRMATO':
                $arrayStati['Firmato'] = true;
            case 'PAG_VALIDATO':
                $arrayStati['Validato'] = true;
        }

        if (!$pagamento->getProcedura()->getRendicontazioneProceduraConfig()->isRichiestaFirmaDigitale()) {
            unset($arrayStati['Firmato']);
        }

        return $arrayStati;
    }

    /** sezione documenti progetto* */
    // ATTENZIONE RENDICONTAZIONE STANDARD!!!
    // per il bando 7 è stata spostata nel gestore pagamento specifico
    // UN FATE BUIDDIELLU
    public function gestioneDocumentiPagamento($id_pagamento) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documentoFile = new \DocumentoBundle\Entity\DocumentoFile();
        $documentoPagamento = new \AttuazioneControlloBundle\Entity\DocumentoPagamento();

        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        /**
         * metto questo controllo..per stare tranquilli
         * perchè i documenti dei pagamenti di tipo anticipo vanno gestiti dalla funzione ad hoc
         */
        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            return $this->gestioneDocumentiAnticipoPagamento($id_pagamento);
        }

        $listaTipi = $this->getTipiDocumentiPagamentoCaricabili($pagamento, false);
        if (count($listaTipi) > 0 && !$pagamento->isRichiestaDisabilitata()) {

            $opzioni_form["lista_tipi"] = $listaTipi;
            $opzioni_form["cf_firmatario"] = $pagamento->getFirmatario()->getCodiceFiscale();
            $form = $this->createForm('AttuazioneControlloBundle\Form\DocumentoPagamentoType', $documentoPagamento, $opzioni_form);
            //$form->add('submit', \BaseBundle\Form\CommonType::submit, array('label' => 'Carica'));

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                $tipologia = $documentoPagamento->getDocumentoFile()->getTipologiaDocumento();
                if (!is_null($tipologia)) {
                    $codice = strtolower($tipologia->getCodice());
                    $nota = $documentoPagamento->getNota();
                    // se il codice tipologia inizia esattamente per "altro" (quindi tipologia altro) e non ho specificato una nota segnalo errore
                    if (strpos($codice, 'altro') === 0 && empty($nota)) {
                        $form->get('nota')->addError(new \Symfony\Component\Form\FormError('Occorre inserire un nota che descriva la natura del documento'));
                    }
                }

                if ($form->isValid()) {
                    try {

                        $documentoFile = $documentoPagamento->getDocumentoFile();
                        $this->container->get("documenti")->carica($documentoFile, 0, $richiesta);

                        //$documento_pagamento->setDocumentoFile($documento_file);
                        $documentoPagamento->setPagamento($pagamento);

                        $em->persist($documentoPagamento);

                        $em->flush();
                        return $this->addSuccesRedirect("Il documento è stato correttamente salvato", "gestione_documenti_pagamento", array("id_pagamento" => $id_pagamento));
                    } catch (ResponseException $e) {
                        $this->addFlash('error', $e->getMessage());
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
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Gestione documenti");

        $documentiPagamento = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoPagamento")->findDocumentiPagamento($id_pagamento);

        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($pagamento->getProcedura());
        $linkDocumentiProgetto = $rendicontazioneProceduraConfig->getLinkDocumentiProgetto();
        $avvisoSezioneDocumentiProgetto = $rendicontazioneProceduraConfig->getAvvisoSezioneDocumentiProgetto();

        $dati = array(
            "pagamento" => $pagamento,
            "form" => $form_view,
            'is_richiesta_disabilitata' => $pagamento->isRichiestaDisabilitata(),
            'documentiPagamento' => $documentiPagamento,
            'linkDocumentiProgetto' => $linkDocumentiProgetto,
            'avvisoSezioneDocumentiProgetto' => $avvisoSezioneDocumentiProgetto
        );

        return $this->render("AttuazioneControlloBundle:Pagamenti:gestioneDocumentiPagamento.html.twig", $dati);
    }

    /**
     * @param Pagamento $pagamento
     * @return EsitoValidazione
     */
    public function validaDatiGenerali($pagamento) {
        $esito = new EsitoValidazione(true);
        $esito->setSezione("Dati generali");

        if (is_null($pagamento->getFirmatario())) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione('Non è stato selezionato il firmatario');
        }

        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            $importoRichiesto = $pagamento->getImportoRichiesto();
            if (!($importoRichiesto > 0)) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione('Non è stato inserito l\'importo richiesto per il pagamento');
            }

            $dataFideiussione = $pagamento->getDataFideiussione();
            if (is_null($dataFideiussione)) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione('Non è stata inserita la data di fideiussione ');
            }
        }

        return $esito;
    }

    public function validaDocumenti($pagamento) {

        $esito = new EsitoValidazione(true);

        $tipiDocumentiObbligatoriNonCaricati = $this->getTipiDocumentiPagamentoObbligatoriNonCaricati($pagamento);
        foreach ($tipiDocumentiObbligatoriNonCaricati as $tipoDocumento) {
            $esito->addMessaggio('Caricare il documento ' . $tipoDocumento->getDescrizione());
        }

        if (count($tipiDocumentiObbligatoriNonCaricati) > 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Caricare tutti gli allegati previsti");
        }

        // nel caso siano tutti opzionali ce ne deve essere almeno uno caricato
        if (count($tipiDocumentiObbligatoriNonCaricati) == 0 && count($pagamento->getDocumentiPagamento()) == 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Nessun documento caricato");
        }

        return $esito;
    }

    public function validaDocumentiDichiarazioniRendicontazione($pagamento) {
        $esito = new EsitoValidazione(true);
        $documenti_obbligatori = $this->getTipiDocumentiObbligatoriDichiarazioni($pagamento);

        foreach ($documenti_obbligatori as $documento) {
            $esito->addMessaggio('Caricare il documento ' . $documento->getDescrizione());
        }

        if (count($documenti_obbligatori) > 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Caricare tutti gli allegati previsti dalla procedura");
        }

        return $esito;
    }

    public function validaGiustificativi($pagamento) {
        $esito = new EsitoValidazione(true);
        $giustificativi = $pagamento->getGiustificativi();

        //Verifico la presenza di giustificativi visibili e non di spese generali se
        //no la validazione li conta e non funziona come dovrebbe
        $giustificativiVisibili = $pagamento->getGiustigicativiVisibili();
        if (count($giustificativiVisibili) == 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Non sono presenti giustificativi");
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
            $esitoGiustificativi = $this->container->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->validaGiustificativo($giustificativo);
            if ($esitoGiustificativi->getEsito() == false) {
                $esito->setEsito(false);
                $errori = $esitoGiustificativi->getMessaggiSezione();
                foreach ($errori as $errore) {
                    $esito->addMessaggio($errore);
                }
            }
        }

        if (!$esito->getEsito()) {
            $esito->addMessaggioSezione("Uno o più giustificativi sono incompleti o non validi");
        }

        return $esito;
    }

    public function getTipiDocumentiCaricabili($pagamento, $solo_obbligatori = false) {
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $procedura_id = $richiesta->getProcedura()->getId();
        $res = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaDocumentiPagamento($pagamento->getId(), $procedura_id, false);
        if (!$solo_obbligatori) {
            $tipologie_con_duplicati = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array("abilita_duplicati" => 1, "procedura" => $richiesta->getProcedura(), "tipologia" => 'rendicontazione'));
            $res = array_merge($res, $tipologie_con_duplicati);
        }

        return $res;
    }

    public function getTipiDocumentiCaricabiliRendicontazione($pagamento, $solo_obbligatori = false) {
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $procedura_id = $richiesta->getProcedura()->getId();
        $res = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaDocumentiPagamento($pagamento->getId(), $procedura_id, false);
        if ($solo_obbligatori) {
            $tipologie_con_duplicati = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array("abilita_duplicati" => 1, "procedura" => $richiesta->getProcedura(), "tipologia" => 'rendicontazione_dichiarazioni'));
            $res = array_merge($res, $tipologie_con_duplicati);
        }

        return $res;
    }

    public function getTipiDocumentiPagamentoObbligatoriNonCaricati($pagamento) {
        $soloObbligatori = true;
        $procedura = $pagamento->getRichiesta()->getProcedura();
        $tipiDocumenti = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaTipiDocumentiPagamentoStandard($pagamento->getId(), $procedura->getId(), $soloObbligatori);
        return $tipiDocumenti;
    }

    public function getTipiDocumentiObbligatoriDichiarazioni($pagamento) {
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $procedura_id = $richiesta->getProcedura()->getId();
        $res = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaDocumentiPagamentoTipologia($pagamento->getId(), $procedura_id, false, 'rendicontazione_dichiarazioni');
        return $res;
    }

    public function eliminaDocumentoPagamento($id_documento_pagamento) {
        $em = $this->getEm();
        $documento_pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoPagamento")->find($id_documento_pagamento);
        $pagamento = $documento_pagamento->getPagamento();
        $redirect_route = 'gestione_documenti_pagamento';
        $tipologia = $documento_pagamento->getDocumentoFile()->getTipologiaDocumento()->getTipologia();
        $codice = $documento_pagamento->getDocumentoFile()->getTipologiaDocumento()->getCodice();
        if ($tipologia == 'rendicontazione_antimafia_standard' || $tipologia == 'rendicontazione_antimafia_' . $pagamento->getProcedura()->getId()) {
            $redirect_route = 'gestione_antimafia';
        }

        if ($codice == 'VIDEO_PAGAMENTO') {
            $redirect_route = 'gestione_documenti_dropzone_pagamento';
        }

        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            $redirect_route = 'gestione_documenti_anticipo_pagamento';
        }



        if (!$documento_pagamento->isModificabileIntegrazione()) {
            return $this->addErrorRedirect("Il documento non è eliminabile perché non in integrazione", $redirect_route, array("id_pagamento" => $pagamento->getId()));
        }

        if ($pagamento->isRichiestaDisabilitata()) {
            return $this->addErrorRedirect("L'operazione non è compatibile con lo stato del pagamento.", $redirect_route, array("id_pagamento" => $pagamento->getId()));
        }

        try {
            $em->remove($documento_pagamento);
            $documento_pagamento->setIntegrazioneDi(null);
            $em->flush();
            return $this->addSuccesRedirect("Il documento è stato correttamente eliminato", $redirect_route, array("id_pagamento" => $pagamento->getId()));
        } catch (ResponseException $e) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", $redirect_route, array("id_pagamento" => $pagamento->getId()));
        }
    }

    public function datiGeneraliPagamento($id_pagamento, $formType = NULL) {

        $options = array();
        $em = $this->getEm();
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();

        $options["tipologia"] = $pagamento->getModalitaPagamento()->getCodice();
        $options["disabled"] = $isRichiestaDisabilitata;
        $options["url_indietro"] = $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $id_pagamento));
        $options["firmatabili"] = $em->getRepository("SoggettoBundle:Soggetto")->getFirmatariAmmissibili($richiesta->getSoggetto());

        if (is_null($formType)) {
            $formType = "AttuazioneControlloBundle\Form\DatiGeneraliPagamentoType";
            if ($pagamento->getModalitaPagamento()->isAnticipo()) {
                $formType = "AttuazioneControlloBundle\Form\DatiGeneraliAnticipoPagamentoType";
            }
        }

        $form = $this->createForm($formType, $pagamento, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {

            if ($isRichiestaDisabilitata) {
                return $this->addErrorRedirect("Le modifiche sul pagamento sono disabilitate", "dettaglio_pagamento", array("id_pagamento" => $id_pagamento));
            }

            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $em->flush();
                    return $this->addSuccesRedirect("Dati correttamente salvati", "dettaglio_pagamento", array("id_pagamento" => $id_pagamento));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dati generali pagamento");

        $options["form"] = $form->createView();
        $options["pagamento"] = $pagamento;
        $options["richiesta"] = $richiesta;

        return $this->render("AttuazioneControlloBundle:Pagamenti:datiGenerali.html.twig", $options);
    }

    // Da non confondere l'attributo alias del Fascicolo con l'alias della Pagina: in questa funzione viene individuato un fascicolo in base all'alias della su pagina/indice
    public function getFascicoloByAlias($fascicoli, $alias) {
        foreach ($fascicoli as $fascicolo) {
            $pagina = $fascicolo->getIndice();
            $aliasPagina = $pagina->getAlias();
            if ($aliasPagina == $alias) {
                return $fascicolo;
            }
        }
        return false;
    }

    public function getFascicoli() {
        $fascicoli = array();
        foreach ($this->getProcedura()->getFascicoliProceduraRendiconto() as $fascioloProcedura) {
            $fascicoli[] = $fascioloProcedura->getFascicolo();
        }
        return $fascicoli;
    }

    public function controllaValiditaPagamento($id_pagamento, $opzioni = array()) {
        /** @var Pagamento $pagamento */
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($pagamento->getRichiesta()->getProcedura());

        $esitiSezioni = array();
        $esitiSezioni[] = $this->validaDatiGenerali($pagamento);
        $esitiSezioni[] = $this->validaDateProgetto($pagamento);
        $esitiSezioni[] = $this->validaDatiBancari($pagamento);
        $esitiSezioni[] = $this->validaDocumenti($pagamento);
        $esitiSezioni[] = $this->validaAutodichiarazioniAutorizzazioni($pagamento);

        if ($rendicontazioneProceduraConfig->getSezioneDurc()) {
            $esitiSezioni[] = $this->validaDurc($pagamento);
        }

        if ($rendicontazioneProceduraConfig->getSezioneAntimafia()) {
            $esitiSezioni[] = $this->validaAntimafia($pagamento);
        }

        if ($rendicontazioneProceduraConfig->getSezioneAtti()) {
            $esitiSezioni[] = $this->validaAtti($pagamento);
        }

        if ($rendicontazioneProceduraConfig->getSezioneContratti()) {
            $esitiSezioni[] = $this->validaContratti($pagamento);
        }

        if ($rendicontazioneProceduraConfig->getSezionePersonale()) {
            $esitiSezioni[] = $this->validaPersonale($pagamento);
        }

        if ($pagamento->getModalitaPagamento()->getRichiedeGiustificativi()) {
            $esitiSezioni[] = $this->validaGiustificativi($pagamento);
        }

        if ($rendicontazioneProceduraConfig->getSezioneRSI() == true) {
            $esitiSezioni[] = $this->validaQuestionario($pagamento);
        }

        if ($rendicontazioneProceduraConfig->getSezioneVideo() == true) {
            $esitiSezioni[] = $this->validaDocumentiDropzone($pagamento);
        }

        // Controlli monitoraggio solo se POR
        $richiesta = $pagamento->getRichiesta();
        if ($richiesta->getFlagPor()) {
            $esitiSezioni[] = $this->validaImpegni($pagamento);
            $esitiSezioni[] = $this->validaMonitoraggioIndicatori($pagamento);
            $esitiSezioni[] = $this->validaMonitoraggioFasiProcedurali($pagamento);
            $esitiSezioni[] = $this->validaProceduraAggiudicazione($pagamento);
        }

        $esito = true;
        $messaggi = array();
        $messaggiSezione = array();
        foreach ($esitiSezioni as $esitoSezione) {
            $esito &= $esitoSezione->getEsito();
            $messaggi = array_merge_recursive($messaggi, $esitoSezione->getMessaggi());
            $messaggiSezione = array_merge_recursive($messaggi, $esitoSezione->getMessaggiSezione());
        }

        return new EsitoValidazione($esito, $messaggi, $messaggiSezione);
    }

    public function dammiVociMenuElencoPagamenti($id_pagamento) {
        $csrfTokenManager = $this->container->get("security.csrf.token_manager");
        $token = $csrfTokenManager->getToken("token")->getValue();
        $vociMenu = array();

        /** @var Pagamento $pagamento */
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getRichiesta();
        $id_procedura = $richiesta->getProcedura()->getId();

        if (!is_null($pagamento->getStato())) {
            $stato = $pagamento->getStato()->getCodice();
            if ($stato == StatoPagamento::PAG_INSERITO) {
                $voceMenu["label"] = "Compila";
                $voceMenu["path"] = $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $id_pagamento));
                $vociMenu[] = $voceMenu;

                $voceMenu["label"] = "Genera pdf";
                $voceMenu["path"] = $this->generateUrl("genera_pdf_pagamento", array("id_pagamento" => $id_pagamento));
                $vociMenu[] = $voceMenu;

                //validazione
                if ($pagamento->getModalitaPagamento()->isAnticipo()) {
                    $esitoValidazione = $this->controllaValiditaAnticipoPagamento($id_pagamento);
                } else {
                    $esitoValidazione = $this->controllaValiditaPagamento($id_pagamento);
                }

                //aggiungo if su procedura e multiproponente fino a che non ci danno ok per il pdf dell 773
                if ($esitoValidazione->getEsito()) {
                    $voceMenu["label"] = "Valida";
                    $voceMenu["path"] = $this->generateUrl("valida_pagamento", array("id_pagamento" => $id_pagamento, "_token" => $token));
                    $vociMenu[] = $voceMenu;
                }
            } else {
                $voceMenu["label"] = "Visualizza";
                $voceMenu["path"] = $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $id_pagamento));
                $vociMenu[] = $voceMenu;
            }

            //scarica pdf domanda
            if ($stato != StatoPagamento::PAG_INSERITO) {
                $voceMenu["label"] = "Scarica richiesta";
                $voceMenu["path"] = $this->generateUrl("scarica_pagamento", array("id_pagamento" => $id_pagamento));
                $vociMenu[] = $voceMenu;
            }

            //carica pagamento firmato
            if ($stato == StatoPagamento::PAG_VALIDATO && $pagamento->getProcedura()->getRendicontazioneProceduraConfig()->isRichiestaFirmaDigitale()) {
                $voceMenu["label"] = "Carica domanda firmata";
                $voceMenu["path"] = $this->generateUrl("carica_pagamento_firmato", array("id_pagamento" => $id_pagamento));
                $vociMenu[] = $voceMenu;
            }

            if (!($stato == StatoPagamento::PAG_INSERITO || $stato == StatoPagamento::PAG_VALIDATO)
                && $pagamento->getProcedura()->getRendicontazioneProceduraConfig()->isRichiestaFirmaDigitale()) {
                $voceMenu["label"] = "Scarica pagamento firmato";
                $voceMenu["path"] = $this->generateUrl("scarica_pagamento_firmato", array("id_pagamento" => $id_pagamento));
                $vociMenu[] = $voceMenu;
            }

            // sposto qui (vincolandola) questa parte che era stata a posta per il 773 e che dava errore poichè non tutti gli oggetti richiesta hanno il metodo getTipologia....
            $oggettiRichiesta = $richiesta->getOggettiRichiesta();
            $tipologia_richiesta = null;
            if ($id_procedura == 7) {
                $tipologia_richiesta = is_null($oggettiRichiesta) || (count($oggettiRichiesta) != 0) ? "A" : $oggettiRichiesta[0]->getTipologia();
            }

            $controlloModalita = $this->isPagamentoInviabile($pagamento);
            if ($stato == StatoPagamento::PAG_FIRMATO && $pagamento->isInoltrabile() && $controlloModalita->inviabile == true) {
                $voceMenu["label"] = "Invia pagamento";
                $voceMenu["path"] = $this->generateUrl("invia_pagamento", array("id_pagamento" => $id_pagamento, "_token" => $token));
                $voceMenu["attr"] = "data-confirm=\"Continuando non sarà più possibile modificare il pagamento nemmeno dall'assistenza tecnica. Si intende procedere comunque?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
                $vociMenu[] = $voceMenu;
            }

            //invalidazione
            if (($stato == StatoPagamento::PAG_VALIDATO || $stato == StatoPagamento::PAG_FIRMATO)) {
                $voceMenu["label"] = "Invalida";
                $voceMenu["path"] = $this->generateUrl("invalida_pagamento", array("id_pagamento" => $id_pagamento, "_token" => $token));
                $voceMenu["attr"] = "data-confirm=\"Confermi l'invalidazione del pagamento?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
                $vociMenu[] = $voceMenu;
            }
        }

        return $vociMenu;
    }

    public function elencoDocumentiCaricati($id_pagamento, $opzioni = array()) {

        $em = $this->getEm();

        $documenti_caricati = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoPagamento")->findDocumentiCaricati($id_pagamento);
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        $domanda = $pagamento->getDocumentoPagamentoFirmato();

        $dati = array("documenti" => $documenti_caricati, "domanda" => $domanda);
        $response = $this->render("AttuazioneControlloBundle:Pagamenti:elencoDocumentiCaricati.html.twig", $dati);
        return new GestoreResponse($response, "AttuazioneControlloBundle:Pagamenti:elencoDocumentiCaricati.html.twig", $dati);
    }

    public function recuperaPagamento($id_pagamento) {
        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        if (!$pagamento->hasIntegrazione() || !is_null($pagamento->getIntegratoDa())) {
            return $this->addErrorRedirect("L'operazione non è compatibile con lo stato del pagamento.", "elenco_pagamenti", array("id_richiesta" => $richiesta->getId()));
        }

        $pagamento_recuperato = clone $pagamento;
        $pagamento_recuperato->setIntegrazioneDi($pagamento);

        foreach ($pagamento_recuperato->getGiustificativi() as $giustificativo) {
            if (!is_null($giustificativo->getDocumentoGiustificativo())) {
                $contenuto = $this->container->get("documenti")->recuperaContenutoDaId($giustificativo->getDocumentoGiustificativo()->getId());
                $tipologia_documento = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array("codice" => "GIUSTIFICATIVO"));
                $documento = $this->container->get("documenti")->caricaDaByteArray($contenuto, $giustificativo->getDocumentoGiustificativo()->getNomeOriginale(), $tipologia_documento, false, $richiesta);
                $giustificativo->setDocumentoGiustificativo($documento);
            }

            foreach ($giustificativo->getQuietanze() as $quietanza) {
                if (!is_null($quietanza->getDocumentoQuietanza())) {
                    $contenuto = $this->container->get("documenti")->recuperaContenutoDaId($quietanza->getDocumentoQuietanza());
                    $tipologia_documento = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array("codice" => "QUIETANZA"));
                    $documento = $this->container->get("documenti")->caricaDaByteArray($contenuto, $quietanza->getDocumentoQuietanza()->getNomeOriginale(), $tipologia_documento, false, $richiesta);
                    $quietanza->setDocumentoQuietanza($documento);
                }
            }
        }

        foreach ($pagamento_recuperato->getDocumentiPagamento() as $documento_pagamento) {
            if (!is_null($giustificativo->getDocumentoGiustificativo())) {
                $contenuto = $this->container->get("documenti")->recuperaContenutoDaId($documento_pagamento->getDocumentoFile()->getId());
                $documento = $this->container->get("documenti")->caricaDaByteArray($contenuto, $documento_pagamento->getDocumentoFile()->getNomeOriginale(), $documento_pagamento->getDocumentoFile()->getTipologiaDocumento(), false, $richiesta);
                $documento_pagamento->setDocumentoFile($documento);
            }
        }

        $this->container->get("sfinge.stati")->avanzaStato($pagamento_recuperato, "PAG_INSERITO");

        try {
            $em->persist($pagamento_recuperato);
            $em->flush();
            return $this->addSuccesRedirect("È stato creato un nuovo pagamento che è possibile modificare e reinviare", "dettaglio_pagamento", array("id_pagamento" => $pagamento_recuperato->getId()));
        } catch (ResponseException $e) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "elenco_pagamenti", array("id_richiesta" => $richiesta->getId()));
        }
    }

    public function visualizzaIntegrazione($id_pagamento) {
        $this->getSession()->set("id_pagamento", $id_pagamento);
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $dati = array("pagamento" => $pagamento->getIntegrazioneDi());
        $dati["avanzamenti"] = $this->gestioneBarraAvanzamento($pagamento);

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettagli integrazione");

        return $this->render("AttuazioneControlloBundle:Pagamenti:visualizzaIntegrazione.html.twig", $dati);
    }

    public function isBeneficiarioScorrimento($pagamento, $modalita_scorrimento, $richiesta) {
        //TO DO: da implementare in modo generico		
    }

    // ATTENZIONE RENDICONTAZIONE STANDARD!!!
    // per i bandi 7 e 8 è stata spostata nei gestori pagamento specifici
    public function dateProgettoPagamento($pagamento) {

        $options = array();
        $em = $this->getEm();

        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $options["tipologia"] = $pagamento->getModalitaPagamento()->getCodice();
        $options["disabled"] = $pagamento->isRichiestaDisabilitata();
        $options["url_indietro"] = $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId()));

        $dateProgetto = $this->getDateProgetto($pagamento);

        $pagamento->data_avvio_progetto = $dateProgetto->dataAvvioProgetto;
        $pagamento->data_termine_progetto = $dateProgetto->dataTermineProgetto;

        $form = $this->createForm("AttuazioneControlloBundle\Form\DateProgettoPagamentoStandardType", $pagamento, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $esito = $this->validaDateProgetto($pagamento);

            if ($esito->getEsito() == false) {
                foreach ($esito->getMessaggiSezione() as $messaggio) {
                    $form->addError(new \Symfony\Component\Form\FormError($messaggio));
                }
            }

            if ($form->isValid()) {
                try {
                    $em->flush();
                    return $this->addSuccesRedirect("Dati correttamente salvati", "dettaglio_pagamento", array("id_pagamento" => $pagamento->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Date progetto");

        $options["form"] = $form->createView();

        return $this->render("AttuazioneControlloBundle:Pagamenti:dateProgettoPagamento.html.twig", $options);
    }

    public function isRendicontazioneScaduta($id_pagamento) {

        $em = $this->getEm();
        /** @var Pagamento $pagamento */
        $pagamento = $em->getRepository("AttuazioneControlloBundle:Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getRichiesta();
        $dataFineRendicontazione = $pagamento->getDataTermineRendicontazione();
        // per gestire casi pregressi in cui ci si è dimenticati di settare l'informazion..blocchiamo
        if (is_null($dataFineRendicontazione)) {
            return true;
        }

        $now = new \DateTime();

        /**
         * dobbiamo permettere di rendicontare se non è scaduta la data impostata per quella ModalitaPagamentoProcedura, oppure se 
         * è stato abilitato il flag mafioso sul pagamento o anche se è stato abilitato lo scorrimento sulla richiesta
         * per cui la rendicontazione è scaduta se ho superato la dataFinerendicontazione e se non è abilitato nessuno tra i due flag
         */
        if ($now > $dataFineRendicontazione && !($pagamento->getAbilitaRendicontazioneChiusa() || $richiesta->getAbilitaScorrimento())) {
            return true;
        }

        return false;
    }

    public function validaDateProgetto($pagamento) {

        $esito = new EsitoValidazione(true);

        // questa data va inserita obbligatoriamente solo nei casi di SAL
        if ($pagamento->needDataFineRendicontazioneSal()) {
            $dataFineRendicontazioneSal = $pagamento->getDataFineRendicontazione();
            $istruttoria = $pagamento->getRichiesta()->getIstruttoria();

            if (is_null($dataFineRendicontazioneSal)) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione('Deve essere specificata la data di fine rendicontazione SAL');
            }

            $dateProgetto = $this->getDateProgetto($pagamento);

            // se non sono presenti le date..(casi pregressi) non posso fare il controllo
            // eventualmente vedremo di importarci i dati oppure li facciamo inserire all'istruttore solo se sono nulli
            if ($dateProgetto->dataAvvioProgetto && $dateProgetto->dataTermineProgetto) {
                if ($dataFineRendicontazioneSal < $dateProgetto->dataAvvioProgetto || $dataFineRendicontazioneSal > $dateProgetto->dataTermineProgetto) {
                    $esito->setEsito(false);
                    $esito->addMessaggioSezione('La data deve essere compresa tra la data avvio e la data termine progetto');
                }
            }
        }

        return $esito;
    }

    public function gestioneDichiarazioniAltriProponenti($id_pagamento) {
        throw new SfingeException("Deve essere implementato nella classe derivata");
    }

    public function gestioneDocumentiAmministrativiGenerali($id_pagamento, $id_proponente) {
        throw new SfingeException("Deve essere implementato nella classe derivata");
    }

    // ESITO ISTRUTTORIA FINALE

    public function isDisabled($pagamento) {
        return !$pagamento->isModificabile() || is_null($pagamento->getAssegnamentoIstruttoriaAttivo()) || ($this->getUser()->getId() != $pagamento->getAssegnamentoIstruttoriaAttivo()->getIstruttore()->getId() && !$this->isGranted("ROLE_ISTRUTTORE_SUPERVISORE_ATC"));
    }

    protected function controlliChecklist($pagamento, $esito) {

        foreach ($pagamento->getValutazioniChecklist() as $valutazione) {
            if (!$valutazione->getValidata()) {
                $esito->setEsito(false);
                $esito->addMessaggio("Checklist non completata o validata");
                break;
            }
        }
    }

    public function verificaEsitoFinaleEmettibile($pagamento) {
        $esito = new \RichiesteBundle\Utility\EsitoValidazione();
        $esito->setEsito(true);

        $this->controlliChecklist($pagamento, $esito);

        return $esito;
    }

    public function esitoFinale($pagamento) {

        $verifica = $this->verificaEsitoFinaleEmettibile($pagamento);
        if (!$verifica->getEsito()) {
            foreach ($verifica->getMessaggi() as $messaggio) {
                $this->addFlash('error', $messaggio);
            }
            return $this->redirect($this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        }

        $options = array();
        $options["url_indietro"] = $this->generateUrl('esito_finale_istruttoria_pagamenti', array("id_pagamento" => $pagamento->getId()));
        $options["disabled"] = $this->isDisabled($pagamento) || !is_null($pagamento->getEsitoIstruttoria());

        $form = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\EsitoPagamentoType", $pagamento, $options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                if ($pagamento->getEsitoIstruttoria()) {
                    $esito = $this->isEsitoFinalePositivoEmettibile($pagamento);
                    if (!$esito->getEsito()) {
                        foreach ($esito->getMessaggi() as $messaggio) {
                            $this->addFlash('error', $messaggio);
                        }
                        return $this->redirect($this->generateUrl('esito_finale_istruttoria_pagamenti', array('id_pagamento' => $pagamento->getId())));
                    }
                }

                $em = $this->getEm();
                try {
                    $em->flush();
                    $this->addFlash('success', "Esito finale istruttoria pagamento salvato correttamente");

                    $this->invioEmailEsitoPagamento($pagamento);

                    if ($pagamento->getEsitoIstruttoria()) {
                        return $this->redirect($this->generateUrl('elenco_istruttoria_pagamenti'));
                    } else {
                        return $this->redirect($this->generateUrl('esito_finale_istruttoria_pagamenti', array('id_pagamento' => $pagamento->getId())));
                    }
                } catch (\Exception $e) {
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $twig = "AttuazioneControlloBundle:Istruttoria\Pagamenti:esitoFinale.html.twig";

        $dati = array();
        $dati["pagamento"] = $pagamento;
        $dati["menu"] = "esito";
        $dati["form"] = $form->createView();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Esito finale istruttoria pagamento");

        return $this->render($twig, $dati);
    }

    public function datiBancariPagamento($pagamento) {

        $em = $this->getEm();

        $richiesta = $pagamento->getRichiesta();
        $proponenti = $richiesta->getProponenti();
        $datiBancariProponenti = array();

        $datiBancariMancanti = array();

        foreach ($proponenti as $proponente) {

            // dobbiamo escludere gli eventuali proponenti (non mandatari) che hanno il proponenteProfessionista(bando professionisti)
            // questo perchè pur essendoci più proponenti, rappresentano in realtà gli associati studi o associazioni di professionisti 
            // per cui intuisco interessi soltanto l'iban del mandatario
            $proponenteProfessionista = $proponente->getProfessionisti();
            if (count($proponenteProfessionista) > 0 && !$proponente->isMandatario()) {
                //skip
                continue;
            }
            $datiBancari = $proponente->getDatiBancari()->first();

            if ($datiBancari) {
                $datiBancariProponenti[] = $datiBancari;
            } else {
                $datoBancario = new \AttuazioneControlloBundle\Entity\DatiBancari();
                $datoBancario->setProponente($proponente);
                $datiBancariMancanti[] = $datoBancario;
            }
        }

        $dati = array('datiBancariProponenti' => $datiBancariProponenti, 'pagamento' => $pagamento);

        $isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();

        /**
         * i dati bancari vengono richiesti in fase di accettazione contributo.
         * per evitare di essere sommersi di richieste di dati bancari mancanti relativi al pregresso, facciamo così..
         * se non sono definiti per uno o più proponenti
         * allora mostriamo il form e glieli facciamo inserire qui.
         * Se invece sono già presenti li visualizziamo e basta
         * 
         */
        if (count($datiBancariMancanti) > 0 && !$isRichiestaDisabilitata) {

            $atc = $pagamento->getAttuazioneControlloRichiesta();
            $atc->setDatiBancariProponenti($datiBancariMancanti);

            $request = $this->getCurrentRequest();

            $opzioni = array();
            $opzioni["label_pulsante"] = 'Salva';
            $opzioni["url_indietro"] = $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId()));

            $form = $this->createForm("AttuazioneControlloBundle\Form\DatiBancariProponentiType", $atc, $opzioni);

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                if ($form->isValid()) {

                    foreach ($atc->getDatiBancariProponenti() as $datiBancariProponente) {
                        $em->persist($datiBancariProponente);
                    }

                    try {
                        $em->flush();
                        return $this->addSuccesRedirect("Dati inseriti correttamente", "dati_bancari_pagamento", array("id_pagamento" => $pagamento->getId()));
                    } catch (\Exception $e) {
                        $this->addError("Errore nel salvataggio dei dati. Si prega di riprovare o contattare l'assistenza");
                    }
                }
            }

            $dati['form'] = $form->createView();
        }


        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dati bancari");

        $twig = "AttuazioneControlloBundle:Pagamenti:datiBancari.html.twig";

        return $this->render($twig, $dati);
    }

    public function validaDatiBancari($pagamento) {

        $esito = new EsitoValidazione(true);
        $esito->setSezione("Dati bancari");

        $proponenti = $pagamento->getRichiesta()->getProponenti();
        foreach ($proponenti as $proponente) {

            // dobbiamo escludere gli eventuali proponenti (non mandatari) che hanno il proponenteProfessionista(bando professionisti)
            // questo perchè pur essendoci più proponenti, rappresentano in realtà gli associati studi o associazioni di professionisti 
            // per cui intuisco interessi soltanto l'iban del mandatario
            $proponenteProfessionista = $proponente->getProfessionisti();
            if (count($proponenteProfessionista) > 0 && !$proponente->isMandatario()) {
                //skip
                continue;
            }

            $datiBancari = $proponente->getDatiBancari()->first();
            if (!$datiBancari) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione('Dati bancari mancanti');

                return $esito;
            }
        }


        return $esito;
    }

    public function gestioneDurc($pagamento) {

        $richiesta = $pagamento->getRichiesta();
        $soggettoMandatario = $richiesta->getMandatario()->getSoggetto();

        $dati["url_indietro"] = $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId()));

        // prendo i dati degli altri eventuali proponenti da mostrare
        $dati["proponenti"] = array();
        if (count($richiesta->getProponenti()) > 1) {
            foreach ($richiesta->getProponenti() as $proponente) {
                if ($proponente->getMandatario()) {
                    continue;
                }

                $dati["proponenti"][] = $proponente->getSoggetto();
            }
        }
        $dati["soggetto"] = $soggettoMandatario;

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Proponenti");

        return $this->render("AttuazioneControlloBundle:Pagamenti:gestioneDurc.html.twig", $dati);
    }

    private function setDatiDurc($soggetto, $durc) {

        $durc->setDatiVariati(false);
        $durc->setEmailPec($soggetto->getEmailPec());
        if (!is_null($soggetto->getImpresaIscrittaInps())) {
            $durc->setImpresaIscrittaInps(true);
        }
        $durc->setMatricolaInps($soggetto->getMatricolaInps());
        $durc->setImpresaIscrittaInpsDi($soggetto->getImpresaIscrittaInps());

        $durc->setImpresaIscrittaInail($soggetto->getImpresaIscrittaInail());
        $durc->setNumeroCodiceDittaImpresaAssicurata($soggetto->getNumeroCodiceDittaImpresaAssicurata());
        $durc->setImpresaIscrittaInailDi($soggetto->getImpresaIscrittaInailDi());
        $durc->setCcnl($soggetto->getCcnl());
    }

    public function gestioneAntimafiaPagamento($id_pagamento) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documentoPagamento = new \AttuazioneControlloBundle\Entity\DocumentoPagamento();
        $documentoFile = new \DocumentoBundle\Entity\DocumentoFile();

        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getRichiesta();

        // i documenti antimafia sono gestiti come DocumentoPagamento con TipologiaDocumento avente attributo tipologia = rendicontazione_antimafia_standard
        $listaTipi = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByTipologia('rendicontazione_antimafia_standard');

        if (!$pagamento->isRichiestaDisabilitata()) {

            $opzioni_form["lista_tipi"] = $listaTipi;
            $opzioni_form["cf_firmatario"] = $pagamento->getFirmatario()->getCodiceFiscale();
            $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documentoFile, $opzioni_form);
            $form->add('submit', \BaseBundle\Form\CommonType::salva_indietro, array(
                'url' => $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())),
                'label_salva' => 'Carica'
            ));

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {

                        $this->container->get("documenti")->carica($documentoFile, 0, $richiesta);

                        $documentoPagamento->setDocumentoFile($documentoFile);
                        $documentoPagamento->setPagamento($pagamento);
                        $em->persist($documentoPagamento);

                        $em->flush();
                        return $this->addSuccesRedirect("Il documento è stato correttamente salvato", "gestione_antimafia", array("id_pagamento" => $id_pagamento));
                    } catch (ResponseException $e) {
                        $this->addFlash('error', $e->getMessage());
                    }
                }
            }
            $form_view = $form->createView();
        } else {
            $form_view = null;
        }

        $documentiAntimafia = $em->getRepository('AttuazioneControlloBundle\Entity\DocumentoPagamento')->findDocumentiAntimafia($id_pagamento);

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Gestione antimafia");

        $dati = array(
            "pagamento" => $pagamento,
            "form" => $form_view,
            'is_richiesta_disabilitata' => $pagamento->isRichiestaDisabilitata(),
            'documentiAntimafia' => $documentiAntimafia
        );

        return $this->render("AttuazioneControlloBundle:Pagamenti:gestioneAntimafia.html.twig", $dati);
    }

    public function getRendicontazioneProceduraConfig($procedura) {

        $rendicontazioneProceduraConfig = $procedura->getRendicontazioneProceduraConfig();
        // fallback..default
        if (is_null($rendicontazioneProceduraConfig)) {
            $rendicontazioneProceduraConfig = new \AttuazioneControlloBundle\Entity\RendicontazioneProceduraConfig();
        }

        return $rendicontazioneProceduraConfig;
    }

    public function validaDurc($pagamento) {
        $esito = new EsitoValidazione(true);
        return $esito;
    }

    public function validaAntimafia($pagamento) {
        $esito = new EsitoValidazione(true);

        if (!$pagamento->isAntimafiaRichiesta()) {
            return $esito;
        }
        $documentiAntimafia = $documentiAntimafia = $this->getEm()->getRepository('AttuazioneControlloBundle\Entity\DocumentoPagamento')->findDocumentiAntimafia($pagamento->getId());
        // al momento verifichiamo che ce ne sia almeno uno..
        if (count($documentiAntimafia) == 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Caricare la documentazione antimafia prevista");
        }

        return $esito;
    }

    /**
     * @param $pagamento
     * @return EsitoValidazione
     */
    public function validaQuestionario($pagamento): EsitoValidazione {
        $esito = new EsitoValidazione(true);

        if (!$pagamento->getAttuazioneControlloRichiesta()->hasQuestionarioRSI()) {
            return $esito;
        }

        if (is_null($pagamento->getIstanzaFascicolo())) {
            $esito->setEsito(false);
            $esito->addMessaggio('Il questionario non è compilato in tutte le sue sezioni');
            $esito->addMessaggioSezione('Il questionario non è compilato in tutte le sue sezioni');
            return $esito;
        }

        $validitaQuestionario = $this->container->get("fascicolo.istanza")->validaIstanzaPagina($pagamento->getIstanzaFascicolo()->getIndice());
        if (!$validitaQuestionario->getEsito()) {
            $esito->setEsito(false);
            $esito->addMessaggio('Il questionario non è compilato in tutte le sue sezioni');
            $esito->addMessaggioSezione('Il questionario non è compilato in tutte le sue sezioni');
        }

        return $esito;
    }

    public function validaAutodichiarazioniAutorizzazioni($pagamento) {
        $esito = new EsitoValidazione(true);
        if (!$pagamento->getAccettazioneAutodichiarazioniAutorizzazioni()) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione('Accettazione obbligatoria');
        }

        return $esito;
    }

    public function validaAtti($pagamento) {
        $esito = new EsitoValidazione(true);

        // todo eventually..

        return $esito;
    }

    public function validaContratti($pagamento) {
        $esito = new EsitoValidazione(true);
        $contratti = $pagamento->getContratti();

        //Verifico la presenza di contratti
        if (count($contratti) == 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Non sono presenti contratti");
            return $esito;
        }

        foreach ($contratti as $contratto) {
            $esitoContratti = $this->container->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->validaContratto($contratto);
            if ($esitoContratti->getEsito() == false) {
                $esito->setEsito(false);
                $errori = $esitoContratti->getMessaggiSezione();
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

    public function validaPersonale($pagamento) {
        $esito = new EsitoValidazione(true);

        // todo eventually..

        return $esito;
    }

    public function getTipiDocumentiPagamentoCaricabili($pagamento, $solo_obbligatori = false) {

        $res = $this->getTipiDocumentiPagamentoObbligatoriNonCaricati($pagamento);
        if (!$solo_obbligatori) {

            $procedura = $pagamento->getRichiesta()->getProcedura();

            $tipologie_con_duplicati = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findTipologieDocumentiPagamentoConDuplicati($procedura->getId());
            $res = array_merge($res, $tipologie_con_duplicati);
        }

        return $res;
    }

    public function gestioneAutodichiarazioniAutorizzazioni($id_pagamento, $opzioni = array()) {

        $em = $this->getEm();

        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getRichiesta();

        $formData = new \stdClass();
        $formData->accettazione = $pagamento->getAccettazioneAutodichiarazioniAutorizzazioni();

        $label = 'Dichiaro di aver preso visione e di accettare integralmente le clausole riportate in questa sezione';
        if (array_key_exists('label', $opzioni)) {
            $label = $opzioni['label'];
        }

        $options = array();
        $options['disabled'] = $pagamento->isRichiestaDisabilitata();

        $formBuilder = $this->createFormBuilder($formData, $options);
        $formBuilder->add('accettazione', \BaseBundle\Form\CommonType::checkbox, array(
            'required' => true,
            'label' => $label
        ));
        $formBuilder->add('submit', \BaseBundle\Form\CommonType::salva_indietro, array(
            'url' => $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId()))
        ));

        $form = $formBuilder->getForm();

        $elenchiProcedura = $em->getRepository('AttuazioneControlloBundle\Entity\Autodichiarazioni\ElencoProcedura')->getElenchiProceduraByPagamento($pagamento);

        $dati = array('form' => $form->createView(), 'elenchiProcedura' => $elenchiProcedura);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $accettazione = $formData->accettazione;

            if (!$accettazione) {
                return $this->addErrorRedirect('Attenzione, l\'accettazione è obbligatoria', 'gestione_autodichiarazioni_autorizzazioni', array("id_pagamento" => $id_pagamento));
            }

            if ($form->isValid()) {
                try {
                    $pagamento->setAccettazioneAutodichiarazioniAutorizzazioni($accettazione);
                    $em->flush();
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }

                return $this->addSuccesRedirect("Dati correttamente salvati", "dettaglio_pagamento", array("id_pagamento" => $id_pagamento));
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Autodichiarazioni");

        $twig = "AttuazioneControlloBundle:Pagamenti:autodichiarazioniAutorizzazioni.html.twig";
        if (array_key_exists('twig', $opzioni)) {
            $twig = $opzioni['twig'];
        }

        foreach ($opzioni as $key => $value) {
            $dati[$key] = $value;
        }

        return $this->render($twig, $dati);
    }

    // dispatcher..vanno eventualmente ridefinite solo le funzione specifiche per modalità pagamento
    public function generaPdf($id_pagamento, $facsimile = true, $download = true) {
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        $em = $this->getEm();

        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $modalitaPagamento = $pagamento->getModalitaPagamento();
        $opzioni = array();

        /**
         * è stato chiesto di leggere, anche in caso di variazione, sempre i due valori dalla concessione
         */
        $istruttoriaRichiesta = $pagamento->getRichiesta()->getIstruttoria();
        $costoTotaleAmmesso = $istruttoriaRichiesta->getCostoAmmesso();
        $contributoTotaleAmmesso = $istruttoriaRichiesta->getContributoAmmesso();

        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            $elenchiProcedura = array();
        } else {
            $elenchiProcedura = $this->getEm()->getRepository('AttuazioneControlloBundle\Entity\Autodichiarazioni\ElencoProcedura')->getElenchiProceduraByPagamento($pagamento);
        }
        $sezioneAllega = $this->getEm()->getRepository('AttuazioneControlloBundle\Entity\Sezioni\SezioneAllega')->getSezioneAllegaByPagamento($pagamento);

        $datiSede = new \stdClass();
        $datiSede->comune = null;
        $datiSede->provincia = null;
        $datiSede->via = null;
        $datiSede->numero = null;

        /**
         * concordato: 
         * se esiste la sede intervento(Intervento) stampo i dati di quella, altrimenti stampo la sede operativa (SedeOperativa),
         * altrimenti stampo i dati della sede legale del mandatario
         * va valutato anche il flag $mandatario->getSedeLegaleComeOperativa()..se true vuol dire che la sede operativa è la sede legale  
         */
        $mandatario = $pagamento->getRichiesta()->getMandatario();

        $sediIntervento = $mandatario->getInterventi();
        $sediOperative = $mandatario->getSedi();

        if (count($sediIntervento) > 0) {
            $sedeIntervento = $sediIntervento->first();
            $indirizzo = $sedeIntervento->getIndirizzo();
            $comune = $indirizzo->getComune();

            $datiSede->comune = $comune ? $comune->getDenominazione() : $indirizzo->getComuneEstero();
            $datiSede->provincia = $comune ? $comune->getProvincia()->getDenominazione() : $indirizzo->getProvinciaEstera();
            $datiSede->via = $indirizzo->getVia();
            $datiSede->numero = $indirizzo->getNumeroCivico();
        } elseif (!$mandatario->getSedeLegaleComeOperativa() && count($sediOperative) > 0) {
            $sedeOperativa = $sediOperative->first();
            $indirizzo = $sedeOperativa->getSede()->getIndirizzo();
            $comune = $indirizzo->getComune();

            $datiSede->comune = $comune ? $comune->getDenominazione() : $indirizzo->getComuneEstero();
            $datiSede->provincia = $comune ? $comune->getProvincia()->getDenominazione() : $indirizzo->getProvinciaEstera();
            $datiSede->via = $indirizzo->getVia();
            $datiSede->numero = $indirizzo->getNumeroCivico();
        }
        /**
         * Si aggiunge ora anche la sede del bando 5 in quanto gestita come sede in gestione progetti
         */ elseif ($pagamento->getProcedura()->getId() == 5) {
            $oggetti_richiesta = $pagamento->getRichiesta()->getOggettiRichiesta();
            $oggetto = $oggetti_richiesta->first();
            $elencoEdifici = $oggetto->getIndirizziCatastali();
            $intervento = $elencoEdifici->first();
            if ($intervento != false) {
                $comune = $intervento->getComune();
                $datiSede->comune = $comune ? $comune->getDenominazione() : '-';
                $datiSede->provincia = $comune ? $comune->getProvincia()->getDenominazione() : '-';
                $datiSede->via = $intervento->getVia();
                $datiSede->numero = $intervento->getNumeroCivico();
            } else {
                $soggetto = $mandatario->getSoggetto();
                $comune = $soggetto->getComune();

                $datiSede->comune = $comune ? $comune->getDenominazione() : $soggetto->getComuneEstero();
                $datiSede->provincia = $comune ? $comune->getProvincia()->getDenominazione() : $soggetto->getProvinciaEstera();
                $datiSede->via = $soggetto->getVia();
                $datiSede->numero = $soggetto->getCivico();
            }
        } else {
            $soggetto = $mandatario->getSoggetto();
            $comune = $soggetto->getComune();

            $datiSede->comune = $comune ? $comune->getDenominazione() : $soggetto->getComuneEstero();
            $datiSede->provincia = $comune ? $comune->getProvincia()->getDenominazione() : $soggetto->getProvinciaEstera();
            $datiSede->via = $soggetto->getVia();
            $datiSede->numero = $soggetto->getCivico();
        }

        $incrementoOccAltri = $this->verificaTuttiIncrementiOccupazionali($pagamento);
        /**
         * dati comuni alle modalita pagamento passati al twig pdf
         * eventuali dati specifici di una modalita pagamento vanno aggiunti nella sotto procedura relativa
         */
        $opzioni['dati_twig'] = array(
            'costoTotaleAmmesso' => $costoTotaleAmmesso,
            'contributoTotaleAmmesso' => $contributoTotaleAmmesso,
            'elenchiProcedura' => $elenchiProcedura,
            'rendicontazioneProceduraConfig' => $this->getRendicontazioneProceduraConfig($pagamento->getProcedura()),
            'sezioneAllega' => count($sezioneAllega) > 0 ? $sezioneAllega[0] : null,
            'datiSede' => $datiSede,
            'giustificativi' => $em->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->getGiustificativiByPagamentoPerPdfDomandaPagamento($id_pagamento),
            'incrementoOccAltri' => $incrementoOccAltri
        );

        if ($modalitaPagamento->isAnticipo()) {
            return $this->generaPdfAnticipo($pagamento, $facsimile, $download, $opzioni);
            //accomuniamo tutti i pafgamenti intermedi sal sal1 sal2 etc..
            // se dovesse servire diversificare (ma non penso) vanno gestite le casistiche specifiche
        } elseif ($modalitaPagamento->isPagamentoIntermedio()) {
            return $this->generaPdfSal($pagamento, $facsimile, $download, $opzioni);
        } elseif ($modalitaPagamento->isSaldo()) {
            return $this->generaPdfSaldo($pagamento, $facsimile, $download, $opzioni);
        } elseif ($modalitaPagamento->isUnicaSoluzione()) {
            return $this->generaPdfUnicaSoluzione($pagamento, $facsimile, $download, $opzioni);
        }

        throw new \Exception('modalità pagamento non gestita');
    }

    protected function generaPdfAnticipo($pagamento, $facsimile = true, $download = true, $opzioni = array()) {

        $dati = array();
        // al momento assumiamo che l'importo dell'anticipo sia settato dentro importoRichiesto di Pagamento
        $dati["importo_rendicontato"] = $pagamento->getImportoRichiesto();

        $dati = array_merge($dati, $opzioni['dati_twig']);

        $twig = "@AttuazioneControllo/Pdf/pdf_anticipo.html.twig";
        if (array_key_exists('twig', $opzioni)) {
            $twig = $opzioni['twig'];
        }

        return $this->generaPdfPagamento($pagamento, $twig, $dati, $facsimile, $download);
    }

    protected function generaPdfSal($pagamento, $facsimile = true, $download = true, $opzioni = array()) {

        $dati = array();

        $dati = array_merge($dati, $opzioni['dati_twig']);

        $gestoreRichieste = $this->container->get("gestore_richieste")->getGestore($pagamento->getRichiesta()->getProcedura());

        if ($gestoreRichieste->hasNuovoModuloPagamento() == true) {
            $twig = "@AttuazioneControllo/Pdf/pdf_sal_new.html.twig";
        } else {
            $twig = "@AttuazioneControllo/Pdf/pdf_sal.html.twig";
        }
        if (array_key_exists('twig', $opzioni)) {
            $twig = $opzioni['twig'];
        }

        return $this->generaPdfPagamento($pagamento, $twig, $dati, $facsimile, $download);
    }

    protected function generaPdfSaldo($pagamento, $facsimile = true, $download = true, $opzioni = array()) {

        $dati = array();
        $dati['rendicontazioneProceduraConfig'] = $this->getRendicontazioneProceduraConfig($pagamento->getRichiesta()->getProcedura());

        $dati = array_merge($dati, $opzioni['dati_twig']);

        $gestoreRichieste = $this->container->get("gestore_richieste")->getGestore($pagamento->getRichiesta()->getProcedura());

        if ($gestoreRichieste->hasNuovoModuloPagamento() == true) {
            $twig = "@AttuazioneControllo/Pdf/pdf_saldo_new.html.twig";
        } else {
            $twig = "@AttuazioneControllo/Pdf/pdf_saldo.html.twig";
        }

        if (array_key_exists('twig', $opzioni)) {
            $twig = $opzioni['twig'];
        }

        return $this->generaPdfPagamento($pagamento, $twig, $dati, $facsimile, $download);
    }

    protected function generaPdfUnicaSoluzione($pagamento, $facsimile = true, $download = true, $opzioni = array()) {

        $dati = array();
        $dati['rendicontazioneProceduraConfig'] = $this->getRendicontazioneProceduraConfig($pagamento->getRichiesta()->getProcedura());

        $dati = array_merge($dati, $opzioni['dati_twig']);

        $gestoreRichieste = $this->container->get("gestore_richieste")->getGestore($pagamento->getRichiesta()->getProcedura());

        if ($gestoreRichieste->hasNuovoModuloPagamento() == true) {
            $twig = "@AttuazioneControllo/Pdf/pdf_unica_soluzione_new.html.twig";
        } else {
            $twig = "@AttuazioneControllo/Pdf/pdf_unica_soluzione.html.twig";
        }

        if (array_key_exists('twig', $opzioni)) {
            $twig = $opzioni['twig'];
        }

        foreach ($opzioni as $key => $value) {
            $dati[$key] = $value;
        }

        return $this->generaPdfPagamento($pagamento, $twig, $dati, $facsimile, $download);
    }

    /**
     * Viene invocato nell'aggiungi pagamento e mi dice se posso creare un pagamento per una specifica modalità pagamento.
     * Dentro la aggiungi pagamento viene sempre valutato (a parte) se la rendicontazione è attiva per l'intera procedura..
     * quindi comanda overall il flag su procedura
     */
    public function isRendicontazioneAttivaPerModalitaPagamento(Pagamento $pagamento) {
        return $pagamento->isRendicontazioneAttiva();
    }

    public function relazioneFinale($id_pagamento) {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documento_pagamento = new \AttuazioneControlloBundle\Entity\DocumentoPagamento();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();

        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $listaTipi = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array('codice' => TipologiaDocumento::RELAZIONE_FINALE_A_SALDO));

        if ($this->validaRelazioneFinale($pagamento)->getEsito()) {
            foreach ($listaTipi as $index => $d) {
                if (($d->getCodice() == TipologiaDocumento::RELAZIONE_FINALE_A_SALDO) && ($d->getPrefix() == 'relazione_finale')) {
                    unset($listaTipi[$index]);
                }
            }
        }

        if (count($listaTipi) > 0 && !$pagamento->isRichiestaDisabilitata()) {

            $opzioni_form["lista_tipi"] = $listaTipi;
            $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
            $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {

                        $this->container->get("documenti")->carica($documento_file, 0, $richiesta);

                        $documento_pagamento->setDocumentoFile($documento_file);
                        $documento_pagamento->setPagamento($pagamento);
                        $em->persist($documento_pagamento);

                        $em->flush();
                        return $this->addSuccesRedirect("Il documento è stato correttamente salvato", "relazione_finale", array("id_pagamento" => $id_pagamento));
                    } catch (ResponseException $e) {
                        $this->addFlash('error', $e->getMessage());
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
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Relazione finale");

        $dati = array("pagamento" => $pagamento, "form" => $form_view, 'is_richiesta_disabilitata' => $pagamento->isRichiestaDisabilitata());

        $dati['isBeneficiarioScorrimento'] = $this->isBeneficiarioScorrimento($pagamento, ModalitaPagamento::SAL, null);
        $dati['istruttoria'] = false;

        return $this->render("AttuazioneControlloBundle:Pagamenti:relazioneFinale.html.twig", $dati);
    }

    public function validaRelazioneFinale($pagamento) {

        $esito = new EsitoValidazione(true);

        // questa sezione è solo per le domande di SALDO
        if ($pagamento->getModalitaPagamento()->getCodice() != ModalitaPagamento::SALDO_FINALE) {
            return $esito;
        }

        foreach ($pagamento->getDocumentiPagamento() as $d) {
            $tipo_docu = $d->getDocumentoFile()->getTipologiaDocumento();
            if (($tipo_docu->getCodice() == TipologiaDocumento::RELAZIONE_FINALE_A_SALDO) && ($tipo_docu->getPrefix() == 'relazione_finale')) {
                return $esito;
            }
        }

        $esito->setEsito(false);
        $esito->addMessaggioSezione('Caricare la relazione finale a saldo');
        return $esito;
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

    public function gestioneIndicatori(Pagamento $pagamento): Response {
        $richiesta = $pagamento->getRichiesta();
        /** @var \MonitoraggioBundle\Service\IGestoreIndicatoreOutput $indicatoriService  */
        $indicatoriService = $this->container->get('monitoraggio.indicatori_output')->getGestore($richiesta);
        $indicatori = $indicatoriService->getIndicatoriManuali();

        /** @var ValidatorInterface $validator */
        $validator = $this->container->get('validator');
        $mv = $indicatori->map(function (IndicatoreOutput $indicatore) use ($validator) {
            return [
            'value' => $indicatore,
            'validation' => $validator->validate($indicatore, null, ['rendicontazione_beneficiario'])
            ];
        });
        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $paginaService->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $paginaService->aggiungiElementoBreadcrumb("Indicatori di output");

        $dati = array("pagamento" => $pagamento, "indicatori" => $mv);
        return $this->render("AttuazioneControlloBundle:Pagamenti:monitoraggioIndicatori.html.twig", $dati);
    }

    public function gestioneSingoloIndicatore(Pagamento $pagamento, IndicatoreOutput $indicatore): Response {
        //Indicatore
        $disabled = $pagamento->isRichiestaDisabilitata();
        $formIndicatore = $this->createForm(IndicatoreOutputType::class, $indicatore, [
            'to_beneficiario' => true,
            'disabled' => $disabled,
        ]);
        $request = $this->getCurrentRequest();
        $formIndicatore->add('submit', SalvaIndietroType::class, [
            'url' => false,
            'disabled' => $disabled,
        ]);

        $formIndicatore->handleRequest($request);
        $em = $this->getEm();
        if ($formIndicatore->isSubmitted() && $formIndicatore->isValid()) {
            try {
                $em->flush($indicatore);
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage(), ['id_indicatore' => $indicatore->getId()]);
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }

        //Documenti associati
        $tipologiaDocumento = $em->getRepository('DocumentoBundle:TipologiaDocumento')->findOneBy([
            'codice' => 'INDICATORE_OUTPUT',
        ]);
        $nuovoDocumento = new DocumentoFile();
        $nuovoDocumento->setTipologiaDocumento($tipologiaDocumento);

        $formDocumento = $this->createForm(DocumentoFileSimpleType::class, $nuovoDocumento, []);

        $indietro = $this->generateUrl('gestione_monitoraggio_indicatori', [
            'id_pagamento' => $pagamento->getId()
        ]);
        $formDocumento->add('submit', SalvaIndietroType::class, [
            'url' => $indietro,
            'disabled' => $disabled,
            'label_salva' => 'Carica',
        ]);
        $formDocumento->handleRequest($request);
        if ($formDocumento->isSubmitted() && $formDocumento->isValid()) {
            try {
                $this->container->get("documenti")->carica($nuovoDocumento);
                $indicatore->addDocumenti($nuovoDocumento);
                $em->flush();
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage(), ['id_indicatore' => $indicatore->getId()]);
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }
        $richiesta = $pagamento->getRichiesta();
        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $paginaService->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $paginaService->aggiungiElementoBreadcrumb("Indicatori di output", $this->generateUrl("gestione_monitoraggio_indicatori", ['id_pagamento' => $pagamento->getId()]));
        $paginaService->aggiungiElementoBreadcrumb("Gestione indicatore");

        $dati = [
            'form_indicatore' => $formIndicatore->createView(),
            'form_documento' => $formDocumento->createView(),
            'pagamento' => $pagamento,
        ];
        return $this->render("AttuazioneControlloBundle:Pagamenti:monitoraggioSingoloIndicatore.html.twig", $dati);
    }

    public function eliminaDocumentoIndicatoreOutput(Pagamento $pagamento, IndicatoreOutput $indicatore, DocumentoFile $documento): Response {
        $em = $this->getEm();
        if ($pagamento->isRichiestaDisabilitata()) {
            throw new SfingeException('Modifica trasmissione rendicontazione disabilitata');
        }
        try {
            $documentoDaEliminare = $indicatore->removeDocumenti($documento);
            $em->remove($documentoDaEliminare);
            $em->flush();
            $this->addFlash('success', "Documento rimosso correttamente");
        } catch (\Exception $e) {
            $this->addError("Errore durante il salvataggio delle informazioni");
        }
        return $this->redirectToRoute("gestione_monitoraggio_singolo_indicatore", [
                'id_pagamento' => $pagamento->getId(),
                'id_indicatore' => $indicatore->getId(),
        ]);
    }

    /**
     * @return EsitoValidazione
     */
    public function validaMonitoraggioIndicatori(Pagamento $pagamento) {
        $esito = new EsitoValidazione(true);

        if ($pagamento->isUltimoPagamento() || $pagamento->getProcedura()->getId() == 140) {
            $richiesta = $pagamento->getRichiesta();
            /** @var \MonitoraggioBundle\Service\IGestoreIndicatoreOutput $indicatoriService  */
            $indicatoriService = $this->container->get('monitoraggio.indicatori_output')->getGestore($richiesta);
            $valido = $indicatoriService->isRendicontazioneBeneficiarioValida();
            $esito->setEsito($valido);
        }

        if (!$esito->getEsito()) {
            $esito->addMessaggioSezione('Sezione incompleta');
        }

        return $esito;
    }

    public function gestioneFasiProcedurali(Pagamento $pagamento) {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $IS = $pagamento->isUltimoPagamento();
        $form = $this->createForm(RichiestaFaseProceduraleType::class, $richiesta, array(
            'url_indietro' => $this->generateUrl('dettaglio_pagamento', array("id_pagamento" => $pagamento->getId())),
            'disabled' => $pagamento->isPagamentoDisabilitato(),
            'validation_groups' => $pagamento->isUltimoPagamento() ?
            ['rendicontazione_iter_progetto_beneficiario_finale', 'rendicontazione_beneficiario'] :
            ['rendicontazione_beneficiario'],
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($richiesta);
                $em->flush();
                $this->addFlash('success', 'Dati salvati correttamente');
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage());
                $this->addError('Errore nel salvataggio dei dati');
            }
        }

        $dati = array("pagamento" => $pagamento, "form" => $form->createView());
        return $this->render("AttuazioneControlloBundle:Pagamenti:monitoraggioFasiProcedurali.html.twig", $dati);
    }

    /**
     * @param Pagamento $pagamento
     * 
     * @return EsitoValidazione
     */
    public function validaMonitoraggioFasiProcedurali(Pagamento $pagamento): EsitoValidazione {
        /** @var \MonitoraggioBundle\Service\IGestoreIterProgetto $iterProgettoService */
        $iterProgettoService = $this->container->get('monitoraggio.iter_progetto')->getIstanza($pagamento->getRichiesta());

        return $iterProgettoService->validaInSaldo();
    }

    /** Visualizza elenco di impegni/disimpegni
     * @param Pagamento $pagamento
     */
    public function gestioneImpegni(Pagamento $pagamento) {
        $em = $this->getEm();
        $impegni = $em->getRepository('AttuazioneControlloBundle:Pagamento')->getImpegni($pagamento->getId());

        $dati = array(
            "pagamento" => $pagamento,
            "impegni" => $impegni
        );
        return $this->render("AttuazioneControlloBundle:Pagamenti:monitoraggioElencoImpegni.html.twig", $dati);
    }

    /**
     * Form di modifica degli impegni legati alla richiesta
     * @param Pagamento $id_impegno
     * @param RichiestaImpegni|null $impegno se NULL inserisce nuovo impegno legato alla richiesta del pagamento
     * @return string
     */
    public function gestioneFormImpegno(Pagamento $pagamento, RichiestaImpegni $impegno = null) {
        $em = $this->getEm();
        $richiesta = $this->getEm()->getRepository('AttuazioneControlloBundle:Pagamento')->getRichiesta($pagamento->getId());
        if (\is_null($impegno)) {
            /** @var Richiesta */
            if (\is_null($richiesta)) {
                throw new SfingeException('Pagamento non trovato');
            }
            $impegno = new RichiestaImpegni($richiesta);
            $em->persist($impegno);
        }

        $form = $this->createForm(ImpegnoType::class, $impegno, array(
            'url_indietro' => $this->generateUrl('gestione_monitoraggio_impegni', array('id_pagamento' => $pagamento->getId())),
            'disabled' => $pagamento->isPagamentoDisabilitato(),
        ));

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($impegno->getMonImpegniAmmessi()->isEmpty()) {
                    /** @var RichiestaProgramma */
                    $richiestaProgramma = $richiesta->getMonProgrammi()->first();
                    /** @var RichiestaLivelloGerarchico */
                    $livelloGerarchico = $richiestaProgramma->getLivelliGerarchiciObiettivoSpecifico()->first();
                    $ammesso = new ImpegniAmmessi($impegno, $livelloGerarchico);
                    $impegno->addMonImpegniAmmessi($ammesso);
                    $livelloGerarchico->addImpegniAmmessi($ammesso);
                    $em->persist($ammesso);
                } else {
                    /** @var ImpegniAmmessi $ammesso */
                    $ammesso = $impegno->getMonImpegniAmmessi()->first();
                    $ammesso->setImportoImpAmm($impegno->getImportoImpegno());
                    $ammesso->setDataImpAmm($impegno->getDataImpegno());
                    $ammesso->setTipologiaImpAmm($impegno->getTipologiaImpegno());
                    $ammesso->setTc38CausaleDisimpegnoAmm($impegno->getTc38CausaleDisimpegno());
                }
                $em->persist($impegno);
                $em->flush();
                $this->addFlash('success', 'Dati salvati correttamente');
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio dei dati');
            }
        }

        $tipologiaDocumento = $em->getRepository('DocumentoBundle:TipologiaDocumento')->findOneByCodice(TipologiaDocumento::DOC_IMPEGNO);
        $file = new DocumentoFile($tipologiaDocumento);
        $nuovoDocumento = new DocumentoImpegno($impegno, $file);
        $formDoc = $this->createForm(DocumentoImpegnoType::class, $nuovoDocumento, [
            'disabled' => $pagamento->isPagamentoDisabilitato(),
        ]);
        $formDoc->handleRequest($request);
        if ($formDoc->isSubmitted() && $formDoc->isValid()) {
            try {
                $file = $nuovoDocumento->getDocumento();
                $this->container->get('documenti')->carica($file);
                $em->persist($nuovoDocumento);
                $em->flush();
                $this->addSuccess('Dati salvati correttamente');
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage(), ['id_impegno' => $impegno->getId(), 'id_pagamento' => $pagamento->getId()]);
                $this->addError('Errore durante il salvataggio dei dati');
            }
        }
        $renderViewData = array(
            'form' => $form->createView(),
            'formDoc' => $formDoc->createView(),
            'pagamento' => $pagamento,
        );
        return $this->render('AttuazioneControlloBundle:Pagamenti:richiestaImpegno.html.twig', $renderViewData);
    }

    public function eliminaDocumentoImpegno(Pagamento $pagamento, DocumentoImpegno $documento): Response {
        $impegno = $documento->getImpegno();
        try {
            $em = $this->getEm();
            $em->remove($documento);
            $em->flush();
            $this->addSuccess('Documento eliminato correttamente');
        } catch (\Exception $e) {
            $this->container->get('logger')->error($e->getMessage(), [
                'method' => 'eliminaDocumentoImpegno',
                'id_impegno' => $impegno->getId(),
                'id_pagamento' => $pagamento->getId()
            ]);
            $this->addError('Errore durante il salvataggio dei dati');
        }
        return $this->redirectToRoute("gestione_modifica_monitoraggio_impegni", [
                'id_pagamento' => $pagamento->getId(),
                'id_impegno' => $impegno->getId(),
        ]);
    }

    /**
     * @param Pagamento $pagamento
     * @return EsitoValidazione
     */
    public function validaImpegni(Pagamento $pagamento) {
        $arrayEsclusi = [71, 137, 120, 162];

        $richiesta = $pagamento->getRichiesta();
        $procedura = $richiesta->getProcedura();
        if (in_array($procedura->getId(), $arrayEsclusi)) {
            return new EsitoValidazione(true);
        }
        /** @var \MonitoraggioBundle\Service\GestoreImpegniService $factory */
        $factory = $this->container->get('monitoraggio.impegni');
        $service = $factory->getGestore($richiesta);

        $violazioni = $service->validaImpegniBeneficiario();
        $esito = new EsitoValidazione($violazioni->count() == 0);

        if (!$esito->getEsito()) {
            $esito->addMessaggioSezione('La somma dei contributi erogati è superiore alla somma degli impegni');
        }

        return $esito;
    }

    /**
     * @param Pagamento $pagamento
     * @param integer $id_impegno
     * 
     * @return string
     */
    public function eliminaImpegno(Pagamento $pagamento, $id_impegno) {
        if ($pagamento->isPagamentoDisabilitato()) {
            throw new SfingeException('Non è possibile eliminare un impegno di un pagamento validato');
        }
        try {
            $em = $this->getEm();
            $impegno = $em->getRepository('AttuazioneControlloBundle:RichiestaImpegni')->findOneById($id_impegno);
            if (\is_null($impegno)) {
                throw new SfingeException('Impegno non trovato');
            }
            $em->remove($impegno);
            $em->flush();
            $this->addFlash('success', 'Impegno rimosso con successo');
        } catch (\Exception $e) {
            $this->container->get('logger')->error($e->getMessage());
            $this->addError("Errore durante la rimozione dell'impegno");
        }
        return $this->redirectToRoute('gestione_monitoraggio_impegni', array('id_pagamento' => $pagamento->getId()));
    }

    public function gestioneProceduraAggiudicazione(Pagamento $pagamento): Response {
        $em = $this->getEm();
        $pagamentoRepository = $em->getRepository('AttuazioneControlloBundle:Pagamento');/** var \AttuazioneControlloBundle\Entity\PagamentoRepository $pagamentoRepository */
        $procedureAggiudicazione = $pagamento->getRichiesta()->getMonProcedureAggiudicazione();
        $atc = $pagamento->getAttuazioneControlloRichiesta();

        $form = $this->createForm(ProgettoProceduraAggiudicazioneType::class, $atc, [
            // 'url_indietro' => $this->generateUrl('dettaglio_pagamento', ['id_pagamento' => $pagamento->getId()]),
            'disabled' => $pagamento->isPagamentoDisabilitato(),
        ]);
        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($atc->getProcedureAggiudicazione() === false) {
                    $procedureDaEliminare = $procedureAggiudicazione->filter(function (ProceduraAggiudicazione $p) {
                        return !$p->isValidato();
                    });
                    foreach ($procedureAggiudicazione as $gara) {
                        $em->remove($gara);
                    }
                }
                $em->flush();
                $this->addFlash('success', ' Dati salvati correttamente');
            } catch (\Exception $e) {
                $this->addError('Errore durante il salvataggio dei dati');
            }
        }
        $mv = array(
            'form' => $form->createView(),
            'pagamento' => $pagamento,
            'procedureAggiudicazione' => $procedureAggiudicazione,
        );
        return $this->render('AttuazioneControlloBundle:Pagamenti:proceduraAggiudicazione.html.twig', $mv);
    }

    /**
     * @param Pagamento $pagamento
     * @return EsitoValidazione
     */
    public function validaProceduraAggiudicazione(Pagamento $pagamento): EsitoValidazione {
        //escludiamo la natura non prevista
        $istruttoria = $pagamento->getRichiesta()->getIstruttoria();
        $naturaCup = $istruttoria->getCupNatura();
        if ($naturaCup->getCodice() == CupNatura::CONCESSIONE_INCENTIVI_ATTIVITA_PRODUTTIVE) {
            return new EsitoValidazione(true);
        }
        //escludiamo le anomalie della stronzate fatte
        $arrayEsclusi = [71, 137];
        $richiesta = $pagamento->getRichiesta();
        $procedura = $richiesta->getProcedura();
        if (in_array($procedura->getId(), $arrayEsclusi)) {
            return new EsitoValidazione(true);
        }

        $validator = $this->container->get('validator');/** @var \Symfony\Component\Validator\Validator\ValidatorInterface $validator */
        $esito = new EsitoValidazione(true);
        $atc = $pagamento->getAttuazioneControlloRichiesta();
        if ($pagamento->isPrimoPagamento() && $atc->getProcedureAggiudicazione() == true) {
            //Verifica che tutte le fasi procedurali abbiano date effettive diverse da NULL
            foreach ($pagamento->getAttuazioneControlloRichiesta()->getRichiesta()->getMonProcedureAggiudicazione() as $pg) { /** @var \AttuazioneControlloBundle\Entity\RichiestaImpegni $impegno */
                $errors = $validator->validate($pg, NULL, array('Default'));/** @var \Symfony\Component\Validator\ConstraintViolationListInterface $errors */
                if ($errors->count() > 0) {
                    $esito->setEsito(false);
                    foreach ($errors as $error) { /** @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
                        $esito->addMessaggio($error->getMessage());
                    }
                }
                if ($pagamento->getAttuazioneControlloRichiesta()->getRichiesta()->getMonProcedureAggiudicazione()->isEmpty()) {
                    $esito->setEsito(false);
                }
            }
            if (\is_null($atc->getProcedureAggiudicazione())) {
                $esito->setEsito(false);
            }
        }

        if (!$esito->getEsito()) {
            $esito->addMessaggioSezione('Sezione incompleta');
        }

        return $esito;
    }

    public function gestioneModificaProceduraAggiudicazione(Pagamento $pagamento, $id_procedura_aggiudicazione) {
        $gara = NULL;
        $em = $this->getEm();
        /** @var \AttuazioneControlloBundle\Repository\ProceduraAggiudicazioneRepository $procedureRepo */
        $procedureRepo = $em->getRepository('AttuazioneControlloBundle:ProceduraAggiudicazione');
        $pagamento = $em->getRepository('AttuazioneControlloBundle:Pagamento')->find($pagamento->getId());/** @var Pagamento $pagamento */
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $progressivo = $procedureRepo->getNuovoProgressivo($richiesta);
        if (\is_null($id_procedura_aggiudicazione)) {
            $gara = new ProceduraAggiudicazione($richiesta, $progressivo);
        } else {
            $gara = $procedureRepo->findOneById($id_procedura_aggiudicazione);/** @var ProceduraAggiudicazione $gara */
            if ($gara->getRichiesta() != $richiesta) {
                //Stanno forzando gli ID dell'URL: presento come se non avessero inserito l'ID della procedura
                $gara = new ProceduraAggiudicazione($richiesta, $progressivo);
            }
        }
        $form = $this->createForm(ProceduraAggiudicazioneBeneficiarioType::class, $gara, array(
                'url_indietro' => $this->generateUrl("gestione_monitoraggio_procedura_aggiudicazione", array("id_pagamento" => $pagamento->getId())),
                'disabled' => $pagamento->isPagamentoDisabilitato(),
            ))
            ->remove('id');
        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($gara);
                $em->flush();
                $this->addFlash('success', 'Informazioni salvate correttamente');
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage());
                $this->addError('Errore nel salvataggio dei dati');
            }
        }
        $mv = array(
            'form' => $form->createView(),
        );
        return $this->render('AttuazioneControlloBundle:Pagamenti:modificaProceduraAggiudicazione.html.twig', $mv);
    }

    /**
     * @param Pagamento $pagamento
     */
    public function gestioneEliminaProceduraAggiudicazione(Pagamento $pagamento, $id_procedura_aggiudicazione) {
        if ($pagamento->isPagamentoDisabilitato()) {
            throw new SfingeException('Non è possibile cancellare una procedura di cancellazione di un pagamento validato');
        }
        $em = $this->getEm();
        $proceduraAggiudicazione = $em->getRepository('AttuazioneControlloBundle:ProceduraAggiudicazione')->findOneById($id_procedura_aggiudicazione);
        if (!\is_null($proceduraAggiudicazione)) {
            try {
                $em->remove($proceduraAggiudicazione);
                $em->flush();
                $this->addFlash('success', 'Eliminazione avvenuta con successo');
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage());
                $this->addError("Errore durante l'eliminazione della procedura di aggiudicazione");
            }
        } else {
            $this->addWarning('Procedura di aggiudicazione non trovata');
        }
        return $this->redirectToRoute('gestione_monitoraggio_procedura_aggiudicazione', array('id_pagamento' => $pagamento->getId()));
    }

    /**
     * sezione AD HOC documenti relativi ai soli anticipi
     */
    public function gestioneDocumentiAnticipoPagamento($id_pagamento) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documentoFile = new \DocumentoBundle\Entity\DocumentoFile();
        $documentoPagamento = new \AttuazioneControlloBundle\Entity\DocumentoPagamento();

        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $procedura = $richiesta->getProcedura();

        $listaTipi = $this->getListaDocumentiAnticipi($procedura);

        if (count($listaTipi) > 0 && !$pagamento->isRichiestaDisabilitata()) {

            $opzioni_form["lista_tipi"] = $listaTipi;
            $opzioni_form["cf_firmatario"] = $pagamento->getFirmatario()->getCodiceFiscale();
            $form = $this->createForm('AttuazioneControlloBundle\Form\DocumentoPagamentoType', $documentoPagamento, $opzioni_form);

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                $tipologia = $documentoPagamento->getDocumentoFile()->getTipologiaDocumento();
                if (!is_null($tipologia)) {
                    $codice = strtolower($tipologia->getCodice());
                    $nota = $documentoPagamento->getNota();
                    // se il codice tipologia inizia esattamente per "altro" (quindi tipologia altro) e non ho specificato una nota segnalo errore
                    if (strpos($codice, 'altro') === 0 && empty($nota)) {
                        $form->get('nota')->addError(new \Symfony\Component\Form\FormError('Occorre inserire un nota che descriva la natura del documento'));
                    }
                }

                if ($form->isValid()) {
                    try {

                        $documentoFile = $documentoPagamento->getDocumentoFile();
                        $this->container->get("documenti")->carica($documentoFile, 0, $richiesta);

                        $documentoPagamento->setPagamento($pagamento);

                        $em->persist($documentoPagamento);

                        $em->flush();
                        return $this->addSuccesRedirect("Il documento è stato correttamente salvato", "gestione_documenti_anticipo_pagamento", array("id_pagamento" => $id_pagamento));
                    } catch (ResponseException $e) {
                        $this->addFlash('error', $e->getMessage());
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
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Gestione documenti");

        $documentiPagamento = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoPagamento")->findDocumentiPagamento($id_pagamento);

        $dati = array(
            "pagamento" => $pagamento,
            "form" => $form_view,
            'is_richiesta_disabilitata' => $pagamento->isRichiestaDisabilitata(),
            'documentiPagamento' => $documentiPagamento,
        );

        return $this->render("AttuazioneControlloBundle:Pagamenti:gestioneDocumentiAnticipoPagamento.html.twig", $dati);
    }

    // per gli anticipi è tutta un'altra storia..lo gestiamo a parte
    public function controllaValiditaAnticipoPagamento($id_pagamento, $opzioni = array()) {

        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        $esitiSezioni = array();
        $esitiSezioni[] = $this->validaDatiGenerali($pagamento);
        $esitiSezioni[] = $this->validaDateProgetto($pagamento);
        $esitiSezioni[] = $this->validaDocumentiAnticipoPagamento($pagamento);

        $esito = true;
        $messaggi = array();
        $messaggiSezione = array();
        foreach ($esitiSezioni as $esitoSezione) {
            $esito &= $esitoSezione->getEsito();
            $messaggi = array_merge_recursive($messaggi, $esitoSezione->getMessaggi());
            $messaggiSezione = array_merge_recursive($messaggi, $esitoSezione->getMessaggiSezione());
        }

        return new EsitoValidazione($esito, $messaggi, $messaggiSezione);
    }

    // implementazione veloce..sorry
    public function validaDocumentiAnticipoPagamento($pagamento) {

        $esito = new EsitoValidazione(true);

        $documentiCaricati = $pagamento->getDocumentiPagamento();

        if (count($documentiCaricati) == 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Caricare tutti gli allegati previsti");
            return $esito;
        }

        // controlliamo che è stata caricata la fideiussione
        foreach ($documentiCaricati as $documentoCaricato) {
            $codice = $documentoCaricato->getDocumentoFile()->getTipologiaDocumento()->getCodice();
            if ($codice == 'fid_ant_std') {
                $esito->setEsito(true);
                break;
            }

            $esito->setEsito(false);
            $esito->addMessaggioSezione("Caricare il documento di fideiussione");
        }

        return $esito;
    }

    public function isPagamentoInviabile(Pagamento $pagamento) {
        $controllo = new \stdClass();

        $modalita = $pagamento->getModalitaPagamentoProcedura();
        $dataOdierna = new \DateTime();

        if (is_null($modalita->getDataInvioAbilitata())) {
            $controllo->inviabile = true;
            $controllo->data = null;
        }

        if (!is_null($modalita->getDataInvioAbilitata()) && $dataOdierna >= $modalita->getDataInvioAbilitata()) {
            $controllo->inviabile = true;
            $controllo->data = null;
        }

        if (!is_null($modalita->getDataInvioAbilitata()) && $dataOdierna < $modalita->getDataInvioAbilitata()) {
            $controllo->inviabile = false;
            $controllo->data = $modalita->getDataInvioAbilitata();
        }

        return $controllo;
    }

    public function getListaDocumentiAnticipi($procedura) {
        $listaTipi = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array('tipologia' => 'rendicontazione_anticipi_standard'));
        $listaTipiPerBando = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array('tipologia' => 'rendicontazione_anticipi_standard', 'procedura' => $procedura));
        $listaTipi = array_merge($listaTipiPerBando, $listaTipi);
        return $listaTipi;
    }

    public function verificaTuttiIncrementiOccupazionali($pagamento) {
        $richiesta = $pagamento->getRichiesta();
        $incrementoDaOggetto = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->hasIncrementoDaOggetto($richiesta);
        $incrementoDaFascicolo = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->hasIncrementoDaFascicolo($richiesta->getMandatario());
        $incrementoDaIncrementoOccupazione = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->hasIncrementoDaOccupazioneProponente($richiesta->getMandatario());
        $incrementoDaRisorse = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->hasIncrementoDaRisorse($richiesta);

        return ($incrementoDaOggetto == true || $incrementoDaFascicolo == true || $incrementoDaIncrementoOccupazione == true || $incrementoDaRisorse == true);
    }

    public function getTempoRendicontazioneRestanteGestore($pagamento) {
        $dataFineRendicontazione = $this->getDataTermineRendicontazione($pagamento);

        $now = new \DateTime();
        $giorniRimanenti = $now->diff($dataFineRendicontazione);
        if ($giorniRimanenti->invert) {
            $tempoRestante = '0';
        } else {
            $labels = array('d' => 'giorni', 'h' => 'ore', 'i' => 'minuti', 's' => 'secondi');
            if ($giorniRimanenti->days == 1) {
                $labels['d'] = 'giorno';
            }
            if ($giorniRimanenti->h == 1) {
                $labels['h'] = 'ora';
            }
            if ($giorniRimanenti->m == 1) {
                $labels['i'] = 'minuto';
            }
            if ($giorniRimanenti->s == 1) {
                $labels['s'] = 'secondo';
            }

            // dal caso più specifico al più generico
            if ($giorniRimanenti->d == 0 && $giorniRimanenti->h == 0) {
                $tempoRestante = $giorniRimanenti->format("%i {$labels['i']} %s {$labels['s']}");
            } elseif ($giorniRimanenti->days == 0) {
                $tempoRestante = $giorniRimanenti->format("%h {$labels['h']} %i {$labels['i']}");
            } else {
                $tempoRestante = $giorniRimanenti->format("%a {$labels['d']} %h {$labels['h']}");
            }
        }

        return $tempoRestante;
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

    public function gestioneGiustificativoSpeseGenerali($pagamento) {
        $richiesta = $pagamento->getRichiesta();
        $proponenti = $richiesta->getProponenti();
        $esitoGenerali = true;
        foreach ($proponenti as $proponente) {
            $esitoGenerali = $this->container->get("gestore_voci_piano_costo_giustificativo")->getGestore($pagamento->getProcedura())->gestioneGiustificativoSpeseGenerali($pagamento, $proponente);
            if ($esitoGenerali == false) {
                return false;
            }
        }
        return $esitoGenerali;
    }

    /**
     * @param $pagamento
     * @return RedirectResponse|Response|null
     */
    public function gestioneQuestionarioRsi($pagamento) {
        $em = $this->getEm();
        $id_pagamento = $pagamento->getId();
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();

        $options["disabled"] = $isRichiestaDisabilitata;
        $options["url_indietro"] = $this->generateUrl("dettaglio_pagamento", ["id_pagamento" => $id_pagamento]);
        $formType = "AttuazioneControlloBundle\Form\SceltaQuestionarioRsiType";

        $form = $this->createForm($formType, null, $options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {

            if ($isRichiestaDisabilitata) {
                return $this->addErrorRedirect("Le modifiche sul pagamento sono disabilitate", "dettaglio_pagamento",
                        ["id_pagamento" => $id_pagamento]);
            }

            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $formTipologiaQuestionarioRsi = $request->request->get('scelta_questionario_rsi');
                    $this->aggiungiFascicoloPagamento($pagamento, $formTipologiaQuestionarioRsi['tipologia_questionario_rsi']);
                    $em->persist($pagamento);
                    $em->flush();

                    return $this->redirect($this->generateUrl('questionario_pagamento',
                                ['id_istanza_pagina' => $pagamento->getIstanzaFascicolo()->getIndice()->getId()]));
                } catch (Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l’assistenza.");
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Scelta questionario RSI");

        $options["form"] = $form->createView();
        return $this->render("AttuazioneControlloBundle:Pagamenti:sceltaQuestionarioRsi.html.twig", $options);
    }

    /**
     * @param Pagamento $pagamento
     * @return Response|null
     */
    public function gestioneDocumentiDropzonePagamento(Pagamento $pagamento): ?Response {
        set_time_limit(0);
        $em = $this->getEm();
        $documentoDropzone = $em->getRepository("DocumentoBundle:TipologiaDocumento")->findOneBy(['codice' => 'VIDEO_PAGAMENTO']);
        $documentiCaricati = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoPagamento")->findDocumentiCaricati($pagamento->getId(), ['VIDEO_PAGAMENTO']);

        $dati = [
            'pagamento' => $pagamento,
            'is_richiesta_disabilitata' => $pagamento->isRichiestaDisabilitata(),
            'documenti_caricati' => $documentiCaricati,
            'dimensione_massima_documento' => $documentoDropzone->getDimensioneMassima(),
            'formato_ammesso_documento' => $documentoDropzone->getMimeAmmessi(),
        ];

        return $this->render("AttuazioneControlloBundle:Pagamenti:elencoDocumentiDropzonePagamento.html.twig", $dati);
    }

    /**
     * @param Request $request
     * @param Pagamento $pagamento
     * @return array|string[]
     */
    public function caricaDocumentoDropzone(Request $request, Pagamento $pagamento): array {
        if ($pagamento->isRichiestaDisabilitata()) {
            return ['status' => 'error', 'info' => 'Il pagamento è disabilitato'];
        }

        set_time_limit(0);
        $em = $this->getEm();
        $documentiCaricati = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoPagamento")->findDocumentiCaricati($pagamento->getId(), ['VIDEO_PAGAMENTO']);

        if (!empty($documentiCaricati)) {
            return ['status' => 'error', 'info' => 'Video di presentazione già caricato'];
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        $fileId = $request->get('dzuuid');
        $chunkIndex = $request->get('dzchunkindex') + 1;

        // Imposto la directory di upload
        $tipologiaDocumento = $em->getRepository('DocumentoBundle:TipologiaDocumento')->findOneByCodice('VIDEO_PAGAMENTO');
        $targetPath = $this->container->get("documenti")->getRealPath($pagamento->getRichiesta(), $tipologiaDocumento->getTipologia());

        $fileName = $fileId . '.' . $chunkIndex;

        if (!$file->move($targetPath, $fileName)) {
            return ['status' => 'error', 'info' => 'Errore nello spostamento dei file'];
        }

        return ['status' => 'success', null];
    }

    /**
     * @param Request $request
     * @param $id_pagamento
     * @return array
     */
    public function concatChunksDocumentoDropzone(Request $request, $id_pagamento): array {
        set_time_limit(0);
        $em = $this->getEm();
        $fileId = $request->get('dzuuid');
        $chunkTotal = $request->get('dztotalchunkcount');
        $filename = $request->get('filename');

        $pagamento = $em->getRepository('AttuazioneControlloBundle:Pagamento')->find($id_pagamento);
        $richiesta = $pagamento->getRichiesta();
        $tipologiaDocumento = $em->getRepository('DocumentoBundle:TipologiaDocumento')->findOneByCodice('VIDEO_PAGAMENTO');

        $prefix = $tipologiaDocumento->getPrefix();
        $path = $this->container->get("documenti")->getRealPath($richiesta);

        $originalFileName = preg_replace("/[^a-zA-Z0-9_. -]{1}/", "_", $filename);
        $nome = str_replace(' ', '_', $prefix . "_" . $this->container->get("documenti")->getMicroTime() . "_" . $originalFileName);
        $destinazione = $path . $nome;

        // Prendo il nome file originale
        $originalFileName = $filename;

        // Imposto la directory di upload
        $targetPath = $this->container->get("documenti")->getRealPath($richiesta, $tipologiaDocumento->getTipologia());

        for ($i = 1; $i <= $chunkTotal; $i++) {
            $temp_file_path = $targetPath . $fileId . '.' . $i;
            $chunk = file_get_contents($temp_file_path);
            file_put_contents($destinazione, $chunk, FILE_APPEND | LOCK_SH);
            unlink($temp_file_path);
        }

        $md5 = md5_file($destinazione);

        // Calcolo le dimensioni
        $fileDimension = filesize($destinazione);

        // Prendo il mimeType
        $fileMimeType = mime_content_type($destinazione);

        $documentoFile = new DocumentoFile();
        $documentoFile->setNomeOriginale($originalFileName);
        $documentoFile->setMimeType($fileMimeType);
        $documentoFile->setFileSize($fileDimension);
        $documentoFile->setMd5($md5);
        $documentoFile->setNome($nome);
        $documentoFile->setPath($targetPath);
        $documentoFile->setTipologiaDocumento($tipologiaDocumento);

        $em->persist($documentoFile);

        $documentoPagamento = new DocumentoPagamento();
        $documentoPagamento->setPagamento($pagamento);
        $documentoPagamento->setDocumentoFile($documentoFile);
        $em->persist($documentoPagamento);
        $em->flush();
        return ['status' => 'success', null, 'uploaded' => true, 'nomeOriginale' => $originalFileName,];
    }

    /**
     * @param Pagamento $pagamento
     * @return EsitoValidazione
     */
    public function validaDocumentiDropzone(Pagamento $pagamento): EsitoValidazione {
        $esito = new EsitoValidazione(true);
        $em = $this->getEm();
        $documentiCaricati = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoPagamento")
            ->findDocumentiCaricati($pagamento->getId(), ['VIDEO_PAGAMENTO']);

        if (empty($documentiCaricati)) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Non è stato caricato il video di presentazione");
            return $esito;
        }

        return $esito;
    }

    /**
     * @param Pagamento $pagamento
     * @param Utente $utenteCorrente
     * @return bool
     */
    public function isUtenteAbilitatoPagamenti(Pagamento $pagamento, Utente $utenteCorrente): bool
    {
        $soggetto = $pagamento->getSoggetto();
        $incarichiAbilitati = [TipoIncarico::LR, TipoIncarico::DELEGATO];
        foreach ($soggetto->getIncarichiPersone() as $incarico) {
            if (in_array($incarico->getTipoIncarico()->getCodice(), $incarichiAbilitati)
                && $incarico->getIncaricato()->getCodiceFiscale() === $utenteCorrente->getUsername()) {
                    return true;
            }
        }

        return false;
    }
}
