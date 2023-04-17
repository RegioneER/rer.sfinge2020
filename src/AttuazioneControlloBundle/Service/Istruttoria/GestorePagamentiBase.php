<?php

namespace AttuazioneControlloBundle\Service\Istruttoria;

use AttuazioneControlloBundle\Entity\Istruttoria\AllegatoComunicazionePagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaComunicazionePagamento;
use AttuazioneControlloBundle\Form\Istruttoria\AllegatoComunicazionePagamentoType;
use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\StatoPagamento;
use DocumentoBundle\Entity\TipologiaDocumento;
use CipeBundle\Entity\Classificazioni\CupNatura;
use RichiesteBundle\Service\GestoreResponse;
use DocumentoBundle\Entity\DocumentoFile;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\RichiestaPagamento;
use AttuazioneControlloBundle\Entity\PagamentoAmmesso;
use RichiesteBundle\Entity\Richiesta;
use ProtocollazioneBundle\Entity\RichiestaProtocollo;
use AttuazioneControlloBundle\Entity\PagamentiPercettoriGiustificativo;
use MonitoraggioBundle\Entity\TC40TipoPercettore;
use AttuazioneControlloBundle\Entity\Economia;
use MonitoraggioBundle\Entity\RichiestaPianoCosti;
use AttuazioneControlloBundle\Entity\RichiestaStatoAttuazioneProgetto;
use RichiesteBundle\Entity\IndicatoreOutput;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AttuazioneControlloBundle\Form\Istruttoria\IndicatoreOutputType;
use BaseBundle\Entity\StatoEsitoIstruttoriaPagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti;
use AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AttuazioneControlloBundle\Entity\Istruttoria\AllegatoRichiestaChiarimento;
use AttuazioneControlloBundle\Form\Istruttoria\RichiestaChiarimentiType;
use DocumentoBundle\Service\DocumentiService;
use BaseBundle\Entity\StatoRichiestaChiarimenti;
use AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento;
use AttuazioneControlloBundle\Form\Istruttoria\AllegatoRichiestaChiarimentoType;
use DocumentoBundle\Component\ResponseException;
use PaginaBundle\Services\Pagina;
use AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\StoricoAzioniValutazioneChecklistPagamento;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamentoRepository;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;

class GestorePagamentiBase extends AGestorePagamenti {

    public function riepilogoPagamento($pagamento) {

        $incrementoOccAltri = $this->verificaTuttiIncrementiOccupazionali($pagamento);

        $arrayDocumentoEsito = $this->getEm()->getRepository("SfingeBundle:ParametroSistema")->findOneByCodice('ARRAY_DOCUMENTO_ESITO_PAG');
        
        $dati = [
            "pagamento" => $pagamento,
            "menu" => "riepilogo",
            "proroga_pendente" => false,
            'path_scarica_richiesta_istruttoria' => $this->generateUrl("scarica_pagamento_istruttoria", ["id_pagamento" => $pagamento->getId()]),
            'path_scarica_pagamento_firmato_istruttoria' => $this->generateUrl("scarica_pagamento_firmato_istruttoria", ["id_pagamento" => $pagamento->getId()]),
            'rendicontazioneProceduraConfig' => $this->getRendicontazioneProceduraConfig($pagamento->getProcedura()),
            'incrementoOccAltri' => $incrementoOccAltri,
            'arrayDocumentoEsito' => explode(',', $arrayDocumentoEsito->getValore())
        ];

        $proroghe = $pagamento->getAttuazioneControlloRichiesta()->getProroghe();
        $ultimaProroga = $proroghe->last();
        if ($ultimaProroga != false && $ultimaProroga->isStatoFinale() && $ultimaProroga->getGestita() == 0) {
            $dati["proroga_pendente"] = true;
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento");
        $this->container->get("session")->set(\BaseBundle\Controller\BaseController::SESSIONE_SOGGETTO_ISTRUENDO, $pagamento->getRichiesta()->getMandatario()->getSoggetto());

        // gli anticipi sono abbastanza diversi dalle altre modalità, per cui dobbiamo necessariamente trattarli a parte
        $twig = "AttuazioneControlloBundle:Istruttoria/Pagamenti:riepilogoPagamento.html.twig";
        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            $twig = "AttuazioneControlloBundle:Istruttoria/Pagamenti:riepilogoPagamentoAnticipo.html.twig";
        }

        return $this->render($twig, $dati);
    }

    public function documentiPagamento($pagamento) {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $tipo = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneByCodice("ISTRUTTORIA_PAGAMENTO");
        $documento_file = new DocumentoFile();
        $documento_file->setTipologiaDocumento($tipo);
        /*
         * $options = array("disabled" => $this->isDisabled($pagamento));
         * riga commentata come da mantis 0049385, pare che sti doc li devono caricare quando gli pare
         * anche ad istruttoria conclusa e mandato emesso non che condivida ma ok se la richiesta viene da loro
         */
        $options = array("disabled" => false, 'opzionale' => false);
        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileSimpleType', $documento_file, $options);
        $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $this->container->get("documenti")->carica($documento_file, 0, $pagamento->getRichiesta());

                    $pagamento->addDocumentoIstruttoria($documento_file);

                    $em->flush();
                    $this->addFlash('success', "Documento caricato correttamente");
                    return $this->redirectToRoute("documenti_istruttoria_pagamenti", array("id_pagamento" => $pagamento->getId()));
                } catch (\Exception $e) {
                    $this->container->get("logger")->error($e->getMessage());
                    $this->addFlash('error', "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
                }
            }
        }

        $dati = array("pagamento" => $pagamento, "menu" => "documenti", "form" => $form->createView());

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Documenti pagamento");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:documentiPagamento.html.twig", $dati);
    }

    public function eliminaDocumentoIstruttoriaPagamento($pagamento, $id_documento_istruttoria) {

        if ($this->isGranted("ROLE_OPERATORE_COGEA")) {
            $this->addFlash('error', "Azione non permessa per il ruolo.");
            return $this->addErrorRedirect("Azione non ammessa", "documenti_progetto_istruttoria", array("id_pagamento" => $pagamento->getId()));
        }

        if ($this->isDisabled($pagamento)) {
            return $this->addErrorRedirect("Azione non ammessa", "documenti_progetto_istruttoria", array("id_pagamento" => $pagamento->getId()));
        }

        $em = $this->getEm();
        $documentoIstruttoria = $em->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaPagamento")->find($id_documento_istruttoria);
        try {
            if ($pagamento->removeDocumentoIstruttoria($documentoIstruttoria)) {
                $em->remove($documentoIstruttoria);
                $em->flush();
                return $this->addSuccesRedirect("Documento eliminato correttamente", "documenti_progetto_istruttoria", array("id_pagamento" => $pagamento->getId()));
            } else {
                return $this->addErrorRedirect("Documento non trovato o non collegato al pagamento", "documenti_progetto_istruttoria", array("id_pagamento" => $pagamento->getId()));
            }
        } catch (\Exception $e) {
            $this->container->get("logger")->error($e->getMessage());
            $this->addFlash('error', "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
        }
    }

    public function isDisabled($pagamento) {
        // se l'istruttoria è conclusa il pagamento è disabilitato a prescindere
        if ($pagamento->isIstruttoriaConclusa()) {
            return true;
        } else {
            // se sei una utenza in sola visualizzazione come certificatori o controllori loco deve essere tutto disabilitato
            if ($this->isGranted("ROLE_PAGAMENTI_READONLY") || $this->isGranted("ROLE_OPERATORE_COGEA")) {
                return true;
            }
            // altrimenti se sono il supervisore faccio quello che cazzo mi pare
            elseif ($this->isGranted("ROLE_ISTRUTTORE_SUPERVISORE_ATC")) {
                return false;
                // se sono un comune sciacquapalle, devo essere anche l'assegnatario dell'istruttoria per poterla lavorare
            } else {
                return is_null($pagamento->getAssegnamentoIstruttoriaAttivo()) || $this->getUser()->getId() != $pagamento->getAssegnamentoIstruttoriaAttivo()->getIstruttore()->getId();
            }
        }
    }

    /**
     * quando viene invocato verifica se sono state instanziate le valutazioni di tutte le ChecklistPagamento definite per la procedura
     * (solo quelle che devono essere create in automatico)
     * attenzione che non flusha
     * @param Pagamento $pagamento
     */
    public function inizializzaIstruttoriaPagamento($pagamento) {

        $em = $this->getEm();
        /** @var ValutazioneChecklistPagamentoRepository $valutazioneChecklistRepository */
        $valutazioneChecklistRepository = $em->getRepository('AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento');
        $atc = $pagamento->getAttuazioneControlloRichiesta();

        $isAnticipo = $pagamento->getModalitaPagamento()->isAnticipo();

        $needChecklistControlli = $atc->getRichiesta()->hasControlliProgetto();

        $valutazioneChecklistPrincipale = $valutazioneChecklistRepository->getValutazioneChecklistByPagamento($pagamento, \AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento::TIPOLOGIA_PRINCIPALE);

        // per gli anticipi si segue un comportamento diverso, ovvero ad oggi si fetcha direttamente la checklist relativa agli anticipi
        // che è unica per tutti i bandi
        if ($isAnticipo) {
            $checklistAnticipi = $em->getRepository('AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento')->findOneBy(array('codice' => 'CL_ANTICIPI'));
            $checklistPreviste = array($checklistAnticipi);
        } else {
            /**
             * Per costruzione dovranno essere sempre presenti almeno due checklist, una di tipo principale ed una di tipo post controllo in loco
             * L'associazione tra checklist e procedura si fa nell'apposita tabella di relazione
             */
            $checklistPreviste = $this->getChecklistPreviste($pagamento);
        }
        /** @var \AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento $checklistPrevista */
        foreach ($checklistPreviste as $checklistPrevista) {

            // gdsiparti
            // ad oggi non si sa molto a riguardo, non si sa se sarà solo questa la checklist post controlli in loco o ce ne saranno diverse
            // il concetto è che le checklist relative al post controllo in loco vanno istanziate solo se effettivamente
            // risulta a sistema un controllo in loco e solo se siamo in un pagamento finale (SALDO o UNICA SOLUZIONE)
            // ..quindi bisogna fare questa valutazione prima di istanziare
            if ($checklistPrevista->isTipologiaPostControlloLoco() && (!$needChecklistControlli || !$pagamento->getModalitaPagamento()->isPagamentoFinale())) {
                //skip
                continue;
            }

            // le checklist appalti pubblici (dove previste) si istanziano manualmente al bisogno tramite apposito bottone
            if ($checklistPrevista->isTipologiaAppaltiPubblici()) {
                //skip
                continue;
            }

            // se non è ancora stata istanziata la checklistPrevista la istanzio
            $valutazioneChecklist = $valutazioneChecklistRepository->findOneBy(array('pagamento' => $pagamento, 'checklist' => $checklistPrevista));
            if (!\is_null($valutazioneChecklist)) {
                continue;
            }
            // se si tratta di checklist post controllo in loco..va istanziata solo dopo che è stata validata la principale
            // e solo se la principale dice che il pagamento è ammissibile
            if ($checklistPrevista->isTipologiaPostControlloLoco()) {
                if (is_null($valutazioneChecklistPrincipale) || !$valutazioneChecklistPrincipale->isAmmissibile()) {
                    //skip
                    continue;
                }
            }
            if (\count($valutazioneChecklistRepository->getValutazioniIstanziate($pagamento, $checklistPrevista->getTipologia())) > 0) {
                continue;
            }

            $this->istanziaStrutturaChecklist($checklistPrevista, $pagamento);
        }
    }

    /**
     * 
     * @param \AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento $checklist
     * @param Pagamento $pagamento
     */
    protected function istanziaStrutturaChecklist($checklist, $pagamento) {

        $valutazione = new ValutazioneChecklistPagamento();
        $valutazione->setValidata(false);
        $valutazione->setChecklist($checklist);

        foreach ($checklist->getSezioni() as $sezione) {
            foreach ($sezione->getElementi() as $elemento) {
                $valutazione_elemento = new ValutazioneElementoChecklistPagamento();
                $valutazione_elemento->setElemento($elemento);
                $valutazione->addValutazioneElemento($valutazione_elemento);
            }
        }

        $pagamento->addValutazioneChecklist($valutazione);
    }

    /**
     * @param ValutazioneChecklistPagamento $valutazione_checklist
     * @param $extra
     */
    public function valutaChecklist($valutazione_checklist, $extra = array()) {

        $pagamento = $valutazione_checklist->getPagamento();
        $atc = $pagamento->getAttuazioneControlloRichiesta();
        $checklist = $valutazione_checklist->getChecklist();

        $indietro = $this->generateUrl('checklist_generale', array("id_pagamento" => $pagamento->getId()));

        $isChecklistValidata = $valutazione_checklist->isValidata();
        $hasControlloProgetto = $atc->getRichiesta()->hasControlliProgetto();
        $serveEMancaDocumentoAppalto = $checklist->isTipologiaAppaltiPubblici() && (count($valutazione_checklist->getDocumentiChecklist()) == 0);

        $options = array();
        $options["url_indietro"] = $indietro;
        $options["action"] = $this->generateUrl("valuta_checklist_istruttoria_pagamenti", array("id_valutazione_checklist" => $valutazione_checklist->getId()));

        // disablita solo i campi, una volta validata la checklist o se il pagamento è disabilitato
        $options["fields_disabled"] = $this->isDisabled($pagamento) || $isChecklistValidata;

        /*
         * abilito i pulsanti..logica piuttosto lineare..posso validare se non ho ancora validato ed invalidare se ho già validato
         * a meno che il pagamento sia disabilitato..in quel caso non si può più toccare nulla 
         */


        // può eventualmente invalidare solo il supervisore
        $options["enable_invalida"] = !$this->isDisabled($pagamento) && $this->isGranted("ROLE_ISTRUTTORE_SUPERVISORE_ATC") && $isChecklistValidata;

        $options['mostra_salva'] = !$this->isDisabled($pagamento) && !$isChecklistValidata;

        // per le checklist che impattano sulla liquidabilita del pagamento
        if ($checklist->isChecklistDiLiquidabilita()) {
            $options['enable_valida'] = false;
            $options['enable_valida_non_liq'] = !$this->isDisabled($pagamento) && !$isChecklistValidata;

            // il bottone valida liquidabile controllo deve comparire solo nei saldi e ovviamente solo se è previsto un controllo in loco
            if ($checklist->isTipologiaPrincipale() && $hasControlloProgetto && $pagamento->getModalitaPagamento()->isPagamentoFinale()) {
                $options['enable_valida_liq'] = false;
                $options['enable_valida_liq_controllo'] = !$this->isDisabled($pagamento) && !$isChecklistValidata;
            } else {
                $options['enable_valida_liq_controllo'] = false;
                $options['enable_valida_liq'] = !$this->isDisabled($pagamento) && !$isChecklistValidata;
            }

            // per tutte le altre checklist di carattere generico mostriamo il classico valida
            // il cui click non impatta sull'esito del pagamento
        } else {
            $options['enable_valida_liq'] = false;
            $options['enable_valida_liq_controllo'] = false;
            $options['enable_valida_non_liq'] = false;

            if ($serveEMancaDocumentoAppalto) {
                $options['enable_valida'] = false;
            } else {
                $options['enable_valida'] = !$this->isDisabled($pagamento) && !$isChecklistValidata;
            }
        }

        $form = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\ValutazioneChecklistPagamentoStandardType", $valutazione_checklist, $options);

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            if ($form->isValid()) {

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
                $pulsanti = $form->get("pulsanti");
                $pulsanteValida = $pulsanti->has("pulsante_valida") ? $pulsanti->get("pulsante_valida") : null;
                $isClickedValida = $pulsanteValida ? $pulsanteValida->isClicked() : false;

                $pulsanteValidaLiquidabile = $pulsanti->has("pulsante_valida_liq") ? $pulsanti->get("pulsante_valida_liq") : null;
                $isClickedValidaLiquidabile = $pulsanteValidaLiquidabile ? $pulsanteValidaLiquidabile->isClicked() : false;

                $pulsanteValidaLiquidabileControllo = $pulsanti->has("pulsante_valida_liq_controllo") ? $pulsanti->get("pulsante_valida_liq_controllo") : null;
                $isClickedValidaLiquidabileControllo = $pulsanteValidaLiquidabileControllo ? $pulsanteValidaLiquidabileControllo->isClicked() : false;

                $pulsanteValidaNonLiquidabile = $pulsanti->has("pulsante_valida_non_liq") ? $pulsanti->get("pulsante_valida_non_liq") : null;
                $isClickedValidaNonLiquidabile = $pulsanteValidaNonLiquidabile ? $pulsanteValidaNonLiquidabile->isClicked() : false;

                $pulsanteInvalida = $pulsanti->has("pulsante_invalida") ? $pulsanti->get("pulsante_invalida") : null;
                $isClickedInvalida = $pulsanteInvalida ? $pulsanteInvalida->isClicked() : false;

                $pulsanteSalva = $pulsanti->has("pulsante_submit") ? $pulsanti->get("pulsante_submit") : null;
                $isClickedSalva = $pulsanteSalva ? $pulsanteSalva->isClicked() : false;

                // casi di validazione
                $isValidazione = $isClickedValida || $isClickedValidaLiquidabile || $isClickedValidaLiquidabileControllo || $isClickedValidaNonLiquidabile;

                $redirect_url = $this->generateUrl('valuta_checklist_istruttoria_pagamenti', array('id_valutazione_checklist' => $valutazione_checklist->getId()));

                $evento = null;
                $notaInvalidazione = null;
                $storicoAzione = new StoricoAzioniValutazioneChecklistPagamento();

                $em = $this->getEm();

                $rendicontatoAmmessoENonAmmesso = $em->getRepository('AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo')->getRendicontatoAmmessoENonAmmesso($pagamento);

                if ($isValidazione) {

                    /**
                     * è importante che torni indietro, perchè nell'action di elenco checklist viene chiamata la inizializzaIstruttoria
                     * che istanzia altre eventuali checklist in cascata (ad esempio quella dei post controlli )
                     */
                    $redirect_url = $this->generateUrl('checklist_generale', array("id_pagamento" => $pagamento->getId()));

                    $speseAmmesse = 0.0;
                    $contributoErogabile = 0.0;

                    $esitoValidazioneForm = true;

                    // Fase di validazione fatta qui, perchè deve essere possibile salvare temporaneamente dati incompleti 
                    foreach ($form->get("valutazioni_elementi")->getIterator() as $name => $child) {

                        $valutazioneElemento = $child->getData();
                        $elemento = $valutazioneElemento->getElemento();

                        if (is_null($valutazioneElemento->getValore()) && ($elemento->getOpzionale() != true)) {
                            $form->get("valutazioni_elementi")->get($name)->get('valore')->addError(new \Symfony\Component\Form\FormError("Campo obbligatorio"));
                            $esitoValidazioneForm = false;
                        }

                        if ($elemento->getCodice() == 'CAMPIONATO_CONTROLLO_LOCO' && $valutazioneElemento->getValore() == '0' && !$hasControlloProgetto) {
                            $form->get("valutazioni_elementi")->get($name)->get('valore')->addError(new \Symfony\Component\Form\FormError("Attenzione, nessun controllo in loco previsto a sistema per il progetto"));
                            $esitoValidazioneForm = false;
                        }

                        if ($elemento->getCodice() == 'CAMPIONATO_CONTROLLO_LOCO' && $valutazioneElemento->getValore() != '0' && $hasControlloProgetto) {
                            $form->get("valutazioni_elementi")->get($name)->get('valore')->addError(new \Symfony\Component\Form\FormError("Attenzione, è stato previsto a sistema un controllo in loco per questo progetto"));
                            $esitoValidazioneForm = false;
                        }

                        if ($elemento->getCodice() == 'SPESE_AMMESSE') {
                            $speseAmmesse = $valutazioneElemento->getValore();
                        }

                        if ($elemento->getCodice() == 'MOTIVO_SCOSTAMENTO') {
                            $motivoScostamento = $valutazioneElemento->getValore();
                            $needMotivazione = abs($rendicontatoAmmessoENonAmmesso['rendicontato'] - $rendicontatoAmmessoENonAmmesso['rendicontato_ammesso']) > 0.1;
                            if (empty($motivoScostamento) && $needMotivazione) {
                                $form->get("valutazioni_elementi")->get($name)->get('valore')->addError(new \Symfony\Component\Form\FormError("Campo obbligatorio in caso di spesa non ammessa"));
                                $esitoValidazioneForm = false;
                            }
                        }

                        if ($elemento->getCodice() == 'CONTRIBUTO_EROGABILE') {
                            $contributoErogabile = $valutazioneElemento->getValore();
                        }
                    }

                    if (!$esitoValidazioneForm) {
                        $form->addError(new \Symfony\Component\Form\FormError("Dati non completi o errati"));
                    } else {
                        $valutazione_checklist->setValidata(true);
                        $valutazione_checklist->setValutatore($this->getUser());
                        $valutazione_checklist->setDataValidazione(new \DateTime());

                        /**
                         * si chiede di tracciare l'importo ammesso relativo al pre-controlli in loco
                         * e quello relativo al post controlli in loco.
                         * Considerato che il rendicontato ammesso può essere modificato dall'istruttore dopo i controlli in loco,
                         * l'unico modo (almeno per il momento) è quello di salvarsi il valore inserito nella checklist
                         * in corrispondenza del campo spese ammesse
                         */
                        if ($checklist->isTipologiaPrincipale()) {
                            $pagamento->setImportoRendicontatoAmmesso($speseAmmesse);
                        } elseif ($checklist->isTipologiaPostControlloLoco()) {
                            $pagamento->setImportoRendicontatoAmmessoPostControllo($speseAmmesse);
                        }

                        /**
                         * qui in pratica si delineano due scenari
                         * 1) stiamo validando una checklist generica che non impatta sulla liquidabilità del pagamento.
                         * 
                         * 2) stiamo validando una checklist che impatta sulla liquidabilita del pagamento (PRINCIPALE o POST_CONTROLLO_LOCO)
                         *  2.1 - Non sono previsti controlli in loco ed il progetto è ammissibile 						 
                         *  2.2 - Il progetto non è ammissibile
                         *  2.3 - Sono previsti dei controlli in loco ed il progetto è ammissibile
                         * 
                         * Nel caso 2.3 non possiamo dire niente sull'esito del'istruttoria, poichè si aspetta il resconto dei controlli in loco
                         * per cui si imposta a NULL e si rimanda all'esito della successiva checklist relativa al post controllo in loco
                         */
                        if ($isClickedValida) {
                            $evento = StoricoAzioniValutazioneChecklistPagamento::EVENTO_VALIDATA;

                            // solo per le checklist appalti
                            if ($serveEMancaDocumentoAppalto) {
                                $this->addError('Attenzione, occorre aggiungere il documento relativo alla checklist procedurale prima di poter validare');
                                return $this->redirect($this->generateUrl('valuta_checklist_istruttoria_pagamenti', array("id_valutazione_checklist" => $valutazione_checklist->getId())));
                            }

                            if ($checklist->isTipologiaAnticipi()) {
                                $pagamento->setEsitoIstruttoria(true);
                                $valutazione_checklist->setAmmissibile(true);
                            }
                            /*
                             * Setto la data di prima validazione dell ck per controllo di efficacia
                             * della variazione in caso di bandi con gestione particolare delle variazioni.
                             * Setto la data a prescindere che il bando si aa gestione particolare o meno
                             * in quanto mi sembra una logica più conservativa
                             */
                            if (is_null($pagamento->getDataPrimaValidazioneck()) && $checklist->isTipologiaPrincipale()) {
                                $pagamento->setDataPrimaValidazioneck(new \DateTime());
                            }
                        } elseif ($isClickedValidaLiquidabile) {
                            $valutazione_checklist->setAmmissibile(true);
                            $pagamento->setEsitoIstruttoria(true);
                            /*
                             * Setto la data di prima validazione dell ck per controllo di efficacia
                             * della variazione in caso di bandi con gestione particolare delle variazioni.
                             * Setto la data a prescindere che il bando si aa gestione particolare o meno
                             * in quanto mi sembra una logica più conservativa
                             */
                            if (is_null($pagamento->getDataPrimaValidazioneck()) && $checklist->isTipologiaPrincipale()) {
                                $pagamento->setDataPrimaValidazioneck(new \DateTime());
                            }

                            if ($pagamento->getRichiesta()->getFlagPor()) {
                                $this->aggiornaInformazioniMonitoraggio($pagamento, $contributoErogabile);
                            }

                            $evento = StoricoAzioniValutazioneChecklistPagamento::EVENTO_VALIDATA_LIQUIDABILE;
                        } elseif ($isClickedValidaNonLiquidabile) {
                            $valutazione_checklist->setAmmissibile(false);
                            $pagamento->setEsitoIstruttoria(false);

                            $evento = StoricoAzioniValutazioneChecklistPagamento::EVENTO_VALIDATA_NON_LIQUIDABILE;
                            // mi dicono che in questo caso andrà inviato un esito negativo al beneficiario
                            // che avrà un tempo x per rispondere e contestare
                            // a sua volta dopo l'eventuale contestazione il prgetto va in revoca oppure si riapre
                        } elseif ($isClickedValidaLiquidabileControllo) {

                            // in questo caso non si può dire niente, perchè si aspetta l'esito dei controlli
                            // e poi andrà validata la seconda checklist relativa ai controlli
                            $valutazione_checklist->setAmmissibile(true);
                            $pagamento->setEsitoIstruttoria(null);

                            /*
                             * Setto la data di prima validazione dell ck per controllo di efficacia
                             * della variazione in caso di bandi con gestione particolare delle variazioni.
                             * Setto la data a prescindere che il bando si aa gestione particolare o meno
                             * in quanto mi sembra una logica più conservativa
                             */
                            if (is_null($pagamento->getDataPrimaValidazioneck()) && $checklist->isTipologiaPrincipale()) {
                                $pagamento->setDataPrimaValidazioneck(new \DateTime());
                            }

                            $evento = StoricoAzioniValutazioneChecklistPagamento::EVENTO_VALIDATA_LIQUIDABILE_CONTROLLI;
                            // qui andrebbe avviato il controllo in loco
                            // agendo sull'oggetto ControlloProgetto
                            //vedremo...al momento si avvia sto cazzo
                        }

                        $messaggio = "Checklist validata";
                    }
                } else {
                    // giusto per fare chiarezza esplicito tutte le casistiche rimanenti che non ricadono in quelle
                    // legate ai bottoni di validazione..
                    // qui rimangono da gestire il click su salva e su invalida

                    if ($isClickedInvalida) {

                        /**
                         * chiedono che nel caso in cui siano presenti dei controlli in loco e siano validate entrambe le checklist
                         * prima di poter invalidare la principale, deve essere invalidata quella dei conntrolli in loco
                         */
                        if ($hasControlloProgetto && $checklist->isTipologiaPrincipale()) {
                            $tipologiaChecklistControlli = \AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento::TIPOLOGIA_POST_CONTROLLO_LOCO;
                            $valutazioneChecklistRepository = $em->getRepository('AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento');
                            $valutazioneChecklistControlli = $valutazioneChecklistRepository->getValutazioneChecklistByPagamento($pagamento, $tipologiaChecklistControlli);
                            if (!is_null($valutazioneChecklistControlli) && $valutazioneChecklistControlli->isValidata()) {
                                $this->addWarning('Attenzione, occorre invalidare prima la checklist relativa ai controlli in loco');
                                return $this->redirect($redirect_url);
                            }
                        }

                        // se viene invalidata una delle due tipologie di checklist 
                        // questi valori non sono più attendibili e vanno messi a null, in attesa della prossima validazione
                        if ($checklist->isTipologiaPrincipale()) {
                            $pagamento->setImportoRendicontatoAmmesso(null);
                        } elseif ($checklist->isTipologiaPostControlloLoco()) {
                            $pagamento->setImportoRendicontatoAmmessoPostControllo(null);
                        }

                        $valutazione_checklist->setValidata(false);
                        $valutazione_checklist->setValutatore(null);
                        $valutazione_checklist->setDataValidazione(null);
                        $valutazione_checklist->setAmmissibile(null);

                        if ($checklist->isChecklistDiLiquidabilita()) {
                            $pagamento->setEsitoIstruttoria(null);
                            $this->invalidaPagamentoMonitoraggio($pagamento);
                        } elseif ($checklist->isTipologiaAnticipi()) {
                            $pagamento->setEsitoIstruttoria(null);
                        }

                        $evento = StoricoAzioniValutazioneChecklistPagamento::EVENTO_INVALIDATA;
                        $notaInvalidazione = $valutazione_checklist->notaInvalidazione;

                        $messaggio = "Checklist invalidata";
                    } elseif ($isClickedSalva) {

                        $evento = StoricoAzioniValutazioneChecklistPagamento::EVENTO_SALVATA;

                        $messaggio = "Modifiche salvate correttamente";
                    }
                }


                // in caso di bottoni di validazione, posso flushare solo se il form è valido
                if (($isValidazione && $esitoValidazioneForm == true) || $isClickedSalva || $isClickedInvalida) {
                    try {

                        $storicoAzione->setValutatore($this->getUser());
                        $storicoAzione->setData(new \DateTime());
                        $storicoAzione->setEvento($evento);
                        $storicoAzione->setNota($notaInvalidazione);
                        $valutazione_checklist->addStoricoAzione($storicoAzione);

                        $em->flush();
                        $this->addFlash('success', $messaggio);

                        return $this->redirect($redirect_url);
                    } catch (\Exception $e) {
                        throw $e;
                        $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                    }
                }
            } else {// fine isValid
                $form->addError(new \Symfony\Component\Form\FormError("Dati non completi o errati"));
            }
        } // fine POST

        $twig = $extra["twig"] ?? "AttuazioneControlloBundle:Istruttoria/Pagamenti:checklistPagamentoStandard.html.twig";

        $validataCome = '';
        if ($valutazione_checklist->getValidata()) {
            $esito = $pagamento->getEsitoIstruttoria();
            if ($esito === true) {
                $validataCome = 'come liquidabile';
            } elseif ($esito === false) {
                $validataCome = 'come non liquidabile';
            } elseif (is_null($esito) && $valutazione_checklist->getChecklist()->getTipologia() == 'APPALTI_PUBBLICI') {
                $validataCome = '';
            } elseif (is_null($esito) && $valutazione_checklist->getChecklist()->getTipologia() != 'APPALTI_PUBBLICI') {
                $validataCome = 'come liquidabile per controllo in loco';
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();
        $dati["pagamento"] = $pagamento;
        $dati["valutazione_checklist"] = $valutazione_checklist;
        $dati["no_tab"] = true;
        $dati["mostra_riepilogo_progetto"] = true;
        $dati["pagamento"] = $pagamento;
        $dati["validata_come"] = $validataCome;
        $dati["enable_carica_documenti"] = !$options["fields_disabled"];

        if (isset($extra['dati_twig'])) {
            $dati = array_merge($dati, $extra['dati_twig']);
        }
        /** @var Pagina $paginaService */
        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $paginaService->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $paginaService->aggiungiElementoBreadcrumb("Elenco checklist", $indietro);
        $paginaService->aggiungiElementoBreadcrumb($checklist->getNome());

        return $this->render($twig, $dati);
    }

    protected function aggiornaInformazioniMonitoraggio(Pagamento $pagamento, $contributoErogabile) {
        $richiesta = $pagamento->getRichiesta();
        if (!$richiesta->getMonPrgPubblico() || !$richiesta->getFlagPor()) {
            return;
        }

        $this->creaPagamentoMonitoraggioPubblico($pagamento, $contributoErogabile);

        $richiesta = $pagamento->getRichiesta();
        $this->container->get('monitoraggio.gestore_finanziamento')
            ->getGestore($richiesta)
            ->aggiornaFinanziamento();
        if ($pagamento->isUltimoPagamento()) {
            /** @var \MonitoraggioBundle\Service\IGestoreImpegni $impegniService */
            $impegniService = $this->container->get('monitoraggio.impegni')->getGestore($richiesta);
            $impegniService->aggiornaImpegniASaldo();
        }
    }

    protected function creaPagamentoMonitoraggioPubblico(Pagamento $pagamento, $importo) {
        $em = $this->getEm();
        $richiesta = $pagamento->getRichiesta();

        //il pagamento monitoraggio va creato solo per i pubblici
        // per i privati viene creato contestualmente al mandato
        if (!$richiesta->getMonPrgPubblico()) {
            return;
        }

        $pagamentoMonitoraggio = new RichiestaPagamento($pagamento, RichiestaPagamento::PAGAMENTO);
        $pagamentoMonitoraggio->setImporto($importo);
        $richiesta->addMonRichiestePagamento($pagamentoMonitoraggio);

        $istruttoria = $richiesta->getIstruttoria();
        if ($istruttoria->getCupNatura()->getCodice() == '03') {
            $this->aggiornaPercettoriPagamento($pagamentoMonitoraggio);
        }

        try {
            $livelloGerarchico = $this->getRichiestaLivelloGerarchico($richiesta);
            $pagamentoAmmesso = new PagamentoAmmesso($pagamentoMonitoraggio, $livelloGerarchico);
            $pagamentoMonitoraggio->addPagamentiAmmessi($pagamentoAmmesso);
        } catch (\Exception $e) {
            $this->container->get('logger')->error($e->getMessage());
        }
        $em->persist($pagamentoMonitoraggio);
    }

    /**
     * @throws \Exception
     * @return \AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico
     */
    protected function getRichiestaLivelloGerarchico(Richiesta $richiesta) {
        // $asse = $richiesta->getProcedura()->getAsse();
        $em = $this->getEm();
        /** @var RichiestaProgramma $richiestaProgramma */
        $richiestaProgramma = $richiesta->getMonProgrammi()->first();
        if (!$richiestaProgramma) {
            throw new SfingeException('Nessun programma asssociato alla richiesta', array('richiesta' => $richiesta));
        }
        $livelliGerarchiciObiettivo = $richiestaProgramma->getRichiesta()->getProcedura()->getLivelliGerarchici();
        /** @var TC36LivelloGerarchico */
        $primoLivelloGerarchico = $livelliGerarchiciObiettivo->first();
        /** @var RichiestaLivelloGerarchico|bool $res */
        $res = $richiestaProgramma->getMonLivelliGerarchici()->filter(function (RichiestaLivelloGerarchico $rl) use ($livelliGerarchiciObiettivo) {
                return $livelliGerarchiciObiettivo->contains($rl->getTc36LivelloGerarchico());
            })->first();
        if ($res === false) {
            $res = new RichiestaLivelloGerarchico($richiestaProgramma, $primoLivelloGerarchico);
            $richiestaProgramma->addMonLivelliGerarchici($res);
            $em->persist($res);
        }
        return $res;
    }

    protected function aggiornaPercettoriPagamento(RichiestaPagamento $pagamentoMonitoraggio): void {
        $pagamento = $pagamentoMonitoraggio->getPagamenti();
        $giustificativi = $pagamento->getGiustificativi();
        foreach ($giustificativi as $giustificativo) {
            $percettore = $this->prendeOIstanziaPercettore($pagamentoMonitoraggio, $giustificativo);
            $percettore->aggiornaDaGiustificativo();
            $pagamentoMonitoraggio->addPercettori($percettore);
        }
    }

    protected function prendeOIstanziaPercettore(RichiestaPagamento $pagamentoMonitoraggio, GiustificativoPagamento $giustificativo): PagamentiPercettoriGiustificativo {
        $percettore = $giustificativo->getPagamentiPercettori()->first();

        if (false === $percettore) {
            $percettore = new PagamentiPercettoriGiustificativo($pagamentoMonitoraggio);
            $percettore->setGiustificativoPagamento($giustificativo);
            $giustificativo->addPagamentiPercettori($percettore);

            $em = $this->getEm();
            $tipo = $em->getRepository(TC40TipoPercettore::class)->findOneBy([
                'tipo_percettore' => TC40TipoPercettore::IMPRESE,
            ]);
            $percettore->setTipoPercettore($tipo)
                ->setSoggettoPubblico(false);

            $em->persist($percettore);
        }

        return $percettore;
    }

    protected function invalidaPagamentoMonitoraggio(Pagamento $pagamento): void {
        if (!$pagamento->getRichiesta()->getFlagPor()) {
            return;
        }
        $em = $this->getEm();
        /** @var \AttuazioneControlloBundle\Entity\RichiestaPagamento[] $pagamentiMonitoraggio */
        $pagamentiMonitoraggio = $em
            ->getRepository('AttuazioneControlloBundle:RichiestaPagamento')
            ->findBy(['pagamento' => $pagamento]);

        foreach ($pagamentiMonitoraggio as $pag) {
            foreach ($pag->getPagamentiAmmessi() as $ammesso) {
                $em->remove($ammesso);
            }
            $this->rimuoviPercettoriPagamento($pag);
            $em->remove($pag);
        }
    }

    protected function rimuoviPercettoriPagamento(RichiestaPagamento $pagamento): void {
        $em = $this->getEm();
        $percettori = $pagamento->getPercettori();
        foreach ($percettori as $percettore) {
            $pagamento->removePercettori($percettore);
            $em->remove($percettore);
        }
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

    protected function controlliGiustificativi($pagamento, $esito) {

        foreach ($pagamento->getGiustificativi() as $giustificativo) {
            foreach ($giustificativo->getVociPianoCosto() as $voce) {
                if (is_null($voce->getImportoApprovato())) {
                    $esito->setEsito(false);
                    $esito->addMessaggio("Prima di dare esito positivo è necessario specificare gli importi approvati nei giustificativi");
                    break;
                }
            }
        }
    }

    public function richiediIntegrazione($pagamento) {
        $options = array();
        $options["url_indietro"] = $this->generateUrl('esito_finale_istruttoria_pagamenti', array("id_pagamento" => $pagamento->getId()));
        // $options["action"] = $this->generateUrl("richiedi_integrazione_pagamento", array("id_pagamento" => $pagamento->getId()));
        $options["disabled"] = $this->isDisabled($pagamento) || $pagamento->hasIntegrazione();

        $form = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\RichiestaIntegrazionePagamentoType", $pagamento, $options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if (!$pagamento->hasIntegrazione()) {
                $this->addFlash("error", "La richiesta di integrazione è vuota");
            } elseif ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $this->generaPdfIntegrazione($pagamento);

                    if ($this->container->getParameter("stacca_protocollo_al_volo")) {
                        $this->container->get("docerinitprotocollazione")->setTabProtocollazioneIntegrazionePagamento($pagamento);
                    }
                    $em->flush();
                    $this->addFlash('success', "Salvataggio effettuato correttamente");

                    return $this->redirect($this->generateUrl('esito_finale_istruttoria_pagamenti', array("id_pagamento" => $pagamento->getId())));
                } catch (\Exception $e) {
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $dati["form"] = $form->createView();
        $dati["menu"] = "esito";

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Esito finale", $this->generateUrl("esito_finale_istruttoria_pagamenti", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Richiesta integrazione");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:richiediIntegrazione.html.twig", $dati);
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

    public function generaPdfIntegrazione($pagamento) {
        $pdf = $this->container->get("pdf");

        $dati['pagamento'] = $pagamento;
        $twig = "AttuazioneControlloBundle:Istruttoria\Pagamenti:pdfIntegrazione.html.twig";
        $pdf->load($twig, $dati);

        $data = $pdf->binaryData();

        $tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice("RICHIESTA_INTEGRAZIONE_PAGAMENTO");
        $data_corrente = new \DateTime();
        $documentoRichiesta = $this->container->get("documenti")->caricaDaByteArray($data, "Integrazione_pagamento_{$pagamento->getId()}_{$data_corrente->format('Y-m-d')}.pdf", $tipoDocumento);

        $pagamento->setDocumentoIntegrazione($documentoRichiesta);
    }

    /**
     * Questa viene utilizzata solo per le procedure particolari
     */
    public function mandato($pagamento) {
        $options = array();
        $options["url_indietro"] = $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId()));
        if ($this->isGranted("ROLE_SUPER_ADMIN")) {
            $options["disabled"] = false;
        } else {
            $options["disabled"] = !$this->isGranted("ROLE_ISTRUTTORE_SUPERVISORE_ATC") || !is_null($pagamento->getMandatoPagamento());
        }
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
                    $em->persist($mandato);

                    $em->flush();
                    $this->addFlash('success', "Salvataggio effettuato correttamente");

                    return $this->redirect($this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
                } catch (\Exception $e) {
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $dati["form"] = $form->createView();
        $dati["menu"] = "mandato";
        $dati["pagamento"] = $pagamento;
        $dati["no_tab"] = true;

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Mandato pagamento");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:mandato.html.twig", $dati);
    }

    // non usato dalla rendicontazione standard..quello è in gestoregiustificativi
    public function avanzamentoPianoCosti($richiesta, $proponente, $pagamento_rif, $anno) {
        $avanzamentoCompleto = $this->calcolaAvanzamentoPianoCosti($richiesta, $proponente, $pagamento_rif, $anno);
        $annualita = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($richiesta->getMandatario()->getId());

        $dati = array(
            "richiesta" => $richiesta,
            "pagamento" => $pagamento_rif,
            "avanzamento" => $avanzamentoCompleto['avanzamento'],
            "pagamenti" => $avanzamentoCompleto['pagamenti'],
            "anno" => is_null($anno) ? "Totali" : "Annualità " . $annualita[$anno],
            "menu" => "piano_costi");

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento_rif->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Avanzamento piano costi");

        return $this->render("AttuazioneControlloBundle:Istruttoria:Pagamenti/avanzamentoPianoCosti.html.twig", $dati);
    }

    public function getPagamentiDaAvanzamento($richiesta, $pagamento_rif) {
        $pagamenti = array();
        $pagamenti[] = $pagamento_rif;
        $dataRiferimento = $pagamento_rif->getDataInvio();
        $id_rif = $pagamento_rif->getId();

        foreach ($richiesta->getAttuazioneControllo()->getPagamenti() as $pagamento_ind) {
            $id_ind = $pagamento_ind->getId();
            $data_ind = $pagamento_ind->getDataInvio();
            if (($id_ind != $id_rif) && ($data_ind <= $dataRiferimento) && $pagamento_ind->getEsitoIstruttoria() == true) {
                $pagamenti[] = $pagamento_ind;
            }
        }

        return $pagamenti;
    }

    public function gestioneBarraAvanzamento($pagamento) {
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

        return $arrayStati;
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

    public function getTipiDocumentiObbligatori($pagamento) {
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $procedura_id = $richiesta->getProcedura()->getId();
        $res = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaDocumentiPagamento($pagamento->getId(), $procedura_id, true);
        return $res;
    }

    public function eliminaDocumentoPagamento($id_documento_pagamento) {
        $em = $this->getEm();
        $documento_pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoPagamento")->find($id_documento_pagamento);
        $pagamento = $documento_pagamento->getPagamento();

        if ($this->isGranted("ROLE_OPERATORE_COGEA")) {
            $this->addFlash('error', "Azione non permessa per il ruolo.");
            return $this->addErrorRedirect("Azione non ammessa", "documenti_progetto_istruttoria", array("id_pagamento" => $pagamento->getId()));
        }

        if (!$documento_pagamento->isModificabileIntegrazione()) {
            return $this->addErrorRedirect("Il documento non è eliminabile perchè non in integrazione", "gestione_documenti_pagamento", array("id_pagamento" => $pagamento->getId()));
        }

        if ($pagamento->isRichiestaDisabilitata()) {
            return $this->addErrorRedirect("L'operazione non è compatibile con lo stato del pagamento.", "gestione_documenti_pagamento", array("id_pagamento" => $pagamento->getId()));
        }

        try {
            $em->remove($documento_pagamento);
            $documento_pagamento->setIntegrazioneDi(null);
            $em->flush();
            return $this->addSuccesRedirect("Il documento è stato correttamente eliminato", "gestione_documenti_pagamento", array("id_pagamento" => $pagamento->getId()));
        } catch (ResponseException $e) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "gestione_documenti_pagamento", array("id_pagamento" => $pagamento->getId()));
        }
    }

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

    public function riepilogoRichiestaChiarimenti($pagamento) {

        $indietro = $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId()));

        $dati = array();
        $dati["menu"] = "chiarimenti";
        $dati["pagamento"] = $pagamento;
        $dati["indietro"] = $indietro;

        $twig = "AttuazioneControlloBundle:RichiestaChiarimenti:riepilogoRichiesteChiarimenti.html.twig";

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Richiesta di chiarimenti");

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    /**
     * @param Pagamento $pagamento
     * @param array $opzioni
     */
    public function creaRichiestaChiarimenti($pagamento, $opzioni = array()) {
        $indietro = $this->generateUrl('richiesta_chiarimento_pagamento', array("id_pagamento" => $pagamento->getId()));
        if ($this->isGranted("ROLE_PAGAMENTI_READONLY") || $this->isGranted("ROLE_OPERATORE_COGEA")) {
            $this->addFlash('error', "Azione non permessa per il ruolo");
            return new GestoreResponse($this->redirect($indietro));
        }
        if ($this->isChiarimentoGestibile($pagamento) == false) {
            $this->addFlash('error', "Attenzione! Prima di inviare una richiesta di chiarimenti è necessario attendere la risposta del beneficiario ad una integrazione o la scadenza dei giorni previsti per la risposta");
            return new GestoreResponse($this->redirect($indietro));
        }

        $richiesta_chiarimenti = new RichiestaChiarimento();
        $richiesta_chiarimenti->setPagamento($pagamento);

        $protocollo_richiesta = $pagamento->getProtocollo();
        $protocollo_data = $pagamento->getDataProtocollo();

        $risposta_ultima_integrazione = $pagamento->getIntegrazioni()->last()->getRisposta();

        $protocollo_risposta_ultima_integrazione = $risposta_ultima_integrazione->getProtocolloRispostaIntegrazione();

        $data_protocollo_risposta_ultima_integrazione = '-';
        if (!\is_null($risposta_ultima_integrazione->getDataProtocolloRispostaIntegrazione())) {
            $data_protocollo_risposta_ultima_integrazione = date_format($risposta_ultima_integrazione->getDataProtocolloRispostaIntegrazione(), "d/m/Y");
        }


        $procedura = $pagamento->getProcedura();
        $rup = $procedura->getNomeCognomeRup();
        // testo di default eventualmente ridefinito con l'opzione 
        // ho diversificato perchè la gestione degli anticipi è legata alla fase di istruttoria/valutazione..altro responsabile
        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            $testoEmail = "Richiesta di chiarimenti trasmissione rendicontazione $protocollo_data $protocollo_richiesta."
                . "\n\n"
                . "In fase di istruttoria della documentazione inviata con Prot. $protocollo_risposta_ultima_integrazione "
                . "del $data_protocollo_risposta_ultima_integrazione, è emersa la necessità di alcuni chiarimenti."
                . " Le informazioni richieste nell'allegato dovranno essere caricate su Sfinge entro 7 giorni dal ricevimento della presente comunicazione."
                . "Questa comunicazione non interrompe il procedimento e l'istruttoria verrà comunque portata a conclusione decorso inutilmente tale termine."
                . "Si precisa che non verranno acquisiti documenti inviati in altra forma (PEC, email)."
                . "\n\n"
                . "Cordiali saluti."
                . "\n\n"
                . "Il responsabile del procedimento"
                . "\n"
                . $rup;
        } else {
            $testoEmail = "Richiesta di chiarimenti trasmissione rendicontazione $protocollo_data $protocollo_richiesta."
                . "\n\n"
                . "In fase di istruttoria della documentazione inviata con Prot. $protocollo_risposta_ultima_integrazione "
                . "del $data_protocollo_risposta_ultima_integrazione, è emersa la necessità di alcuni chiarimenti."
                . " Le informazioni richieste nell'allegato dovranno essere caricate su Sfinge entro 7 giorni dal ricevimento della presente comunicazione."
                . "Questa comunicazione non interrompe il procedimento e l'istruttoria verrà comunque portata a conclusione decorso inutilmente tale termine."
                . "Si precisa che non verranno acquisiti documenti inviati in altra forma (PEC, email)."
                . "\n\n"
                . "Cordiali saluti."
                . "\n\n"
                . "Il responsabile del procedimento"
                . "\n"
                . $rup;
        }

        if (\array_key_exists('testo_email', $opzioni)) {
            $testoEmail = $opzioni['testo_email'];
        }

        $richiesta_chiarimenti->setTestoEmail($testoEmail);
        $richiesta_chiarimenti->setTesto("Per chiarimenti si prega di contattare ");
        $richiesta_chiarimenti->setData(new \DateTime());

        $em = $this->getEm();
        $em->persist($richiesta_chiarimenti);

        $risposta = new RispostaRichiestaChiarimenti();

        $risposta->setRichiestaChiarimenti($richiesta_chiarimenti);

        $this->container->get("sfinge.stati")->avanzaStato($richiesta_chiarimenti, StatoRichiestaChiarimenti::RICH_CHIAR_INSERITA);
        $this->container->get("sfinge.stati")->avanzaStato($risposta, StatoRichiestaChiarimenti::RICH_CHIAR_INSERITA);

        return $this->gestioneRichiestaChiarimenti($richiesta_chiarimenti, [
                'twig_options' => [
                    "menu" => "crea_richiesta_chiarimenti",
                ]
        ]);
    }

    public function gestioneRichiestaChiarimenti(RichiestaChiarimento $richiesta_chiarimenti, array $opzioni = []) {
        $request = $this->getCurrentRequest();
        $em = $this->getEm();

        $pagamento = $richiesta_chiarimenti->getPagamento();

        $indietro = $this->generateUrl('richiesta_chiarimento_pagamento', array("id_pagamento" => $pagamento->getId()));
        $disabilita = $this->isGranted("ROLE_PAGAMENTI_READONLY") || $this->isGranted("ROLE_OPERATORE_COGEA") ||
            $richiesta_chiarimenti->getStato() == 'RICH_CHIAR_PROTOCOLLATA' ||
            $richiesta_chiarimenti->getStato() == 'RICH_CHIAR_INVIATA_PA';
        $form_options = array(
            "url_indietro" => $indietro,
            'disabled' => $disabilita
        );
        $form = $this->createForm(RichiestaChiarimentiType::class, $richiesta_chiarimenti, $form_options);
        $form->handleRequest($request);

        $tipologiaAllegato = $em->getRepository("DocumentoBundle:TipologiaDocumento")->findOneBy([
            'codice' => TipologiaDocumento::ALLEGATO_RICHIESTA_CHIARIMENTI
        ]);
        if (\is_null($tipologiaAllegato)) {
            throw new SfingeException("Tipologia documento non trovata");
        }

        $documento = new DocumentoFile($tipologiaAllegato);
        $allegato = new AllegatoRichiestaChiarimento($richiesta_chiarimenti, $documento);

        $formAllegati = $this->createForm(AllegatoRichiestaChiarimentoType::class, $allegato, ['disabled' => $disabilita]);
        $formAllegati->add('submit', SubmitType::class, [
                'label' => 'Carica',
            ])
            ->handleRequest($request);
        if ($formAllegati->isSubmitted() && $formAllegati->isValid()) {
            $richiesta_chiarimenti->addAllegati($allegato);
            try {
                /** @var DocumentiService $docService */
                $docService = $this->container->get('documenti');
                $file = $allegato->getDocumento();
                $docService->carica($file);
                $em->flush();
            } catch (\Exception $e) {
                throw $e;
                $this->addFlash('error', "Errore nel salvataggio delle informazioni");
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->beginTransaction();

                $documentoChiarimenti = $this->creaAllegatoChiarimenti($pagamento);
                $richiesta_chiarimenti->setDocumento($documentoChiarimenti);

                $this->azioniAggiuntiveSalvataggioRichiestaChiarimenti($richiesta_chiarimenti);

                if ($form->get("pulsanti")->get("pulsante_invio")->isClicked()) {
                    $this->container->get("sfinge.stati")->avanzaStato($richiesta_chiarimenti, StatoRichiestaChiarimenti::RICH_CHIAR_INVIATA_PA);
                    if ($this->container->getParameter("stacca_protocollo_al_volo")) {

                        $richiestaProtocollo = $this->container->get("docerinitprotocollazione")->setTabProtocollazioneRichiestaChiarimenti($pagamento, $richiesta_chiarimenti);

                        if (!$this->schedulaEmailProtocollo($richiestaProtocollo)) {
                            throw new \Exception('Errore durante la schedulazione dell\'EmailProtocollo');
                        }
                    }
                    $em->flush();
                    $em->commit();
                    $this->addFlash('success', "Richiesta di chiarimenti inviata con successo");
                } else {
                    $em->flush();
                    $em->commit();
                    $this->addFlash('success', "Richiesta di chiarimenti salvata con successo");
                }
            } catch (\Exception $e) {
                if ($em->getConnection()->isTransactionActive()) {
                    $em->rollback();
                }
                $this->addFlash('error', "Errore nel salvataggio delle informazioni");
            }

            return new GestoreResponse($this->redirect($indietro));
        }

        $dati = [
            "menu" => "gestione_richiesta_chiarimenti",
            "form" => $form->createView(),
            'form_allegati' => $formAllegati->createView(),
            "pagamento" => $pagamento,
        ];
        if (isset($opzioni['twig_options'])) {
            $dati = \array_merge($dati, $opzioni['twig_options']);
        }
        $twig = "AttuazioneControlloBundle:RichiestaChiarimenti:creaRichiestaChiarimenti.html.twig";

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Comunicazioni", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Gestione richieste chiarimenti");

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    protected function azioniAggiuntiveSalvataggioRichiestaChiarimenti(RichiestaChiarimento $richiesta_chiarimenti) {
        
    }

    public function eliminaAllegatoRichiestaChiarimento(AllegatoRichiestaChiarimento $allegato): void {
        $em = $this->getEm();
        $richiesta_chiarimento = $allegato->getRichiestaChiarimento();
        $allegato->getRichiestaChiarimento()->removeAllegati($allegato);
        $em->remove($allegato);
        $em->flush();
    }

    public function istruttoriaRichiestaChiarimenti($richiesta_chiarimenti) {

        $pagamento = $richiesta_chiarimenti->getPagamento();
        $indietro = $this->generateUrl('richiesta_chiarimento_pagamento', array("id_pagamento" => $pagamento->getId()));

        $istruttoria = $richiesta_chiarimenti->getIstruttoriaOggettoPagamento();

        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $richiesta_chiarimenti->setIstruttoriaOggettoPagamento($istruttoria);
        }

        $dati_form_istruttoria = array('url_indietro' => $indietro, 'nascondi_integrazione' => true);
        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $dati_form_istruttoria);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form_istruttoria->handleRequest($request);
            if ($form_istruttoria->isValid()) {
                try {
                    $em = $this->getEm();
                    $em->persist($richiesta_chiarimenti);
                    $em->flush();
                    return $this->addSuccesRedirect("Istruttoria richiesta di chiarimenti salvata correttamente", 'istruttoria_richiesta_chiarimenti', array("id_richiesta_chiarimenti" => $richiesta_chiarimenti->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Richiesta chiarimenti", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Istruttoria richieste chiarimenti");

        $dati = array();
        $dati["pagamento"] = $pagamento;
        $dati["richiesta_chiarimento"] = $richiesta_chiarimenti;
        $dati["indietro"] = $indietro;
        $dati["form_istruttoria"] = $form_istruttoria->createView();

        return $this->render("AttuazioneControlloBundle:RichiestaChiarimenti:istruttoriaRichiestaChiarimenti.html.twig", $dati);
    }

    public function istruttoriaDocumentoRichChiar($richiesta_chiarimenti, $id_documento_rich_chiar) {

        $em = $this->getEm();
        $documento = $em->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaRichiestaChiarimenti")->find($id_documento_rich_chiar);

        $istruttoria = $documento->getIstruttoriaOggettoPagamento();
        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $documento->setIstruttoriaOggettoPagamento($istruttoria);
        }

        $indietro = $this->generateUrl('istruttoria_richiesta_chiarimenti', array("id_richiesta_chiarimenti" => $richiesta_chiarimenti->getId()));

        $dati_form_istruttoria = array('url_indietro' => $indietro, 'nascondi_integrazione' => true);
        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $dati_form_istruttoria);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form_istruttoria->handleRequest($request);
            if ($form_istruttoria->isValid()) {
                try {
                    $em->persist($documento);
                    $em->flush();
                    return $this->addSuccesRedirect("Istruttoria documento salvata correttamente", 'istruttoria_richiesta_chiarimenti', array("id_richiesta_chiarimenti" => $richiesta_chiarimenti->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $pagamento = $richiesta_chiarimenti->getPagamento();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Richiesta chiarimenti", $this->generateUrl('richiesta_chiarimento_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Istruttoria richieste chiarimenti", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Istruttoria documento");

        $dati["form_istruttoria"] = $form_istruttoria->createView();
        $dati["titolo_sezione"] = "Documento " . $documento->getDocumentoFile()->getNomeOriginale();

        return $this->render("AttuazioneControlloBundle:Istruttoria\bando_7:pannelloIstruttoriaGenerale.html.twig", $dati);
    }

    /**
     * @param Pagamento $pagamento
     * @param array $opzioni
     * @return GestoreResponse
     * @throws \Exception
     */
    public function creaIntegrazione($pagamento, $opzioni = array()) {

        $em = $this->getEm();
        $protocollo_richiesta = $pagamento->getProtocollo();
        $protocollo_data = $pagamento->getDataProtocollo();

        $indietro = $this->generateUrl('integrazione_pagamento', array("id_pagamento" => $pagamento->getId()));
        if ($this->isGranted("ROLE_PAGAMENTI_READONLY") || $this->isGranted("ROLE_OPERATORE_COGEA")) {
            $this->addFlash('error', "Azione non permessa per il ruolo");
            return new GestoreResponse($this->redirect($indietro));
        }

        // Controllo se è previsto un numero di giorni di default per rispondere alla comunicazione
        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($pagamento->getRichiesta()->getProcedura());
        $giorniPerRispostaComunicazioniArray = $rendicontazioneProceduraConfig->getGiorniPerRispostaComunicazioni();
        $giorniPerRispostaComunicazioniConfig = isset($giorniPerRispostaComunicazioniArray[$pagamento->getModalitaPagamento()->getCodice()]) ? $giorniPerRispostaComunicazioniArray[$pagamento->getModalitaPagamento()->getCodice()] : null;

        $procedura = $pagamento->getProcedura();
        $rup = $procedura->getNomeCognomeRup();
        // testo di default eventualmente ridefinito con l'opzione
        // ho diversificato perchè la gestione degli anticipi è legata alla fase di istruttoria/valutazione..altro responsabile
        $giorniPerRispostaComunicazioni = $giorniPerRispostaComunicazioniConfig ? $giorniPerRispostaComunicazioniConfig : $pagamento::GIORNI_RISPOSTA_INTEGRAZIONE_DEFAULT;
        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            $testoEmail = "Richiesta di integrazione documentale e interruzione del procedimento trasmissione rendicontazione $protocollo_data $protocollo_richiesta."
                . "\n\n"
                . "In riferimento alla Vs. presentazione di rendicontazione di anticipo, relativa al progetto finanziato dal bando in oggetto, si richiedono le integrazioni elencate in allegato, da inviare tramite il sistema Sfinge2020."
                . "La presente vale come comunicazione di interruzione del procedimento secondo le previsioni dell'art.132 del Regolamento UE n.1303/2013."
                . "Il Beneficiario è tenuto ad integrare la sopra elencata documentazione entro il termine di $giorniPerRispostaComunicazioni giorni, calcolati a partire dalla data di ricevimento della presente richiesta."
                . "In caso di mancato invio si procederà alla valutazione della rendicontazione di anticipo sulla base della sola documentazione già inviata in allegato alla stessa."
                . "\n\n"
                . "Cordiali saluti."
                . "\n\n"
                . "Il dirigente responsabile"
                . "\n"
                . $rup;
        } else {
            $testoEmail = "Richiesta di integrazione documentale e interruzione del procedimento trasmissione rendicontazione $protocollo_data $protocollo_richiesta."
                . "\n\n"
                . "In riferimento alla Vs. presentazione di rendicontazione, per le spese relative al progetto finanziato dal bando in oggetto, si richiedono le integrazioni elencate in allegato da inviare tramite il sistema Sfinge2020."
                . "La presente vale come comunicazione di interruzione del procedimento secondo le previsioni dell'art.132 del Regolamento UE n.1303/2013."
                . "Il Beneficiario è tenuto ad integrare la sopra elencata documentazione entro il termine di $giorniPerRispostaComunicazioni giorni, calcolati a partire dalla data di ricevimento della presente richiesta."
                . "In caso di mancato invio nei termini si procederà alla valutazione della rendicontazione con la sola documentazione già inviata con la trasmissione della rendicontazione."
                . "\n\n"
                . "Cordiali saluti."
                . "\n\n"
                . "Il responsabile del procedimento"
                . "\n"
                . $rup;
        }

        if (array_key_exists('testo_email', $opzioni)) {
            $testoEmail = $opzioni['testo_email'];
        }

        $integrazioni = $pagamento->getIntegrazioni();
        if (count($integrazioni) > 0) {
            $integrazione_pagamento = $integrazioni->last();
        } else {
            $integrazione_pagamento = new IntegrazionePagamento();
            $integrazione_pagamento->setPagamento($pagamento);
            $integrazione_pagamento->setTestoEmail($testoEmail);
            $integrazione_pagamento->setTesto("Per chiarimenti si prega di contattare");
            $integrazione_pagamento->setData(new \DateTime());
            $integrazione_pagamento->setGiorniPerRisposta($giorniPerRispostaComunicazioni);

            $risposta = new \AttuazioneControlloBundle\Entity\Istruttoria\RispostaIntegrazionePagamento();
            $risposta->setIntegrazione($integrazione_pagamento);
            $this->container->get("sfinge.stati")->avanzaStato($integrazione_pagamento, \BaseBundle\Entity\StatoIntegrazione::INT_INSERITA);
            $this->container->get("sfinge.stati")->avanzaStato($risposta, \BaseBundle\Entity\StatoIntegrazione::INT_INSERITA);
            try {
                $em->persist($integrazione_pagamento);
                $em->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', "Errore nel salvataggio delle informazioni");
            }
        }

        $form_options = [
            'url_indietro' => $indietro,
            'mostra_giorni_per_risposta' => isset($giorniPerRispostaComunicazioni) ? true : false
        ];

        $form = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IntegrazioneType", $integrazione_pagamento, $form_options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                try {
                    $em->beginTransaction();
                    $documentoIntegrazione = $this->creaAllegatoIntegrazione($pagamento);
                    $integrazione_pagamento->setDocumento($documentoIntegrazione);

                    $em->flush();

                    if ($form->get("pulsanti")->get("pulsante_invio")->isClicked()) {

                        $esitoPag = $this->controllaValiditaIstruttoriaPagamento($pagamento);
                        if ($esitoPag->getEsito() == false) {
                            return new GestoreResponse($this->addErrorRedirect("Non è possibile inviare un'integrazione se non sono state istruite tutte le sezioni", "crea_integrazione_pagamento", array("id_pagamento" => $pagamento->getId())));
                        }

                        $this->container->get("sfinge.stati")->avanzaStato($integrazione_pagamento, \BaseBundle\Entity\StatoIntegrazione::INT_INVIATA_PA);
                        $em->flush();
                        if ($this->container->getParameter("stacca_protocollo_al_volo")) {

                            $richiestaProtocollo = $this->container->get("docerinitprotocollazione")->setTabProtocollazioneIntegrazionePagamento($pagamento, $integrazione_pagamento);
                            /**
                             * schedulo un invio email per protocollazione in uscita tramite egrammata
                             * l'email verrà mandata solo dopo che lo stato della richiestaProtocollo evolverà in POST_PROTOCOLLAZIONE
                             * Questo blocco andrà riportato negli eventuali GestoriIstruttoriaBando scritti ad hoc che faranno 
                             * l'overwrite del metodo creaIntegrazione 
                             */
                            /*                             * ************************************************************************ */
                            if (!$this->schedulaEmailProtocollo($richiestaProtocollo)) {
                                throw new \Exception('Errore durante la schedulazione dell\'EmailProtocollo');
                            }
                            /*                             * ************************************************************************ */
                        }
                        $em->commit();
                        $this->addFlash('success', "Integrazione inviata con successo");
                    } else {
                        $em->commit();
                        $this->addFlash('success', "Integrazione salvata con successo");
                    }
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }

                return new GestoreResponse($this->redirect($indietro));
            }
        }

        $dati = array();
        $dati["menu"] = "crea_integrazione";
        $dati["form"] = $form->createView();
        $dati["pagamento"] = $pagamento;
        $dati["integrazione"] = $integrazione_pagamento;
        $dati["giorni_risposta_default"] = $giorniPerRispostaComunicazioni;
        $dati["mostra_giorni_per_risposta"] = $form_options['mostra_giorni_per_risposta'];

        $twig = "AttuazioneControlloBundle:Integrazione:creaIntegrazione.html.twig";

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Integrazione", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Crea integrazione");

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    // :O WTF perchè l'invio è getsito anche qui?
    public function gestioneRichiestaIntegrazione($integrazione) {

        $pagamento = $integrazione->getPagamento();
        if ($this->isGranted("ROLE_PAGAMENTI_READONLY") || $this->isGranted("ROLE_OPERATORE_COGEA")) {
            $disabilita = true;
        } else {
            $disabilita = false;
        }
        $indietro = $this->generateUrl('integrazione_pagamento', array("id_pagamento" => $pagamento->getId()));

        // Controllo se è previsto un numero di giorni di default per rispondere alla comunicazione per mostrarlo nella form
        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($pagamento->getRichiesta()->getProcedura());
        $giorniPerRispostaComunicazioniArray = $rendicontazioneProceduraConfig->getGiorniPerRispostaComunicazioni();
        $giorniPerRispostaComunicazioniConfig = isset($giorniPerRispostaComunicazioniArray[$pagamento->getModalitaPagamento()->getCodice()]) ? $giorniPerRispostaComunicazioniArray[$pagamento->getModalitaPagamento()->getCodice()] : null;

        // testo di default eventualmente ridefinito con l'opzione 
        // ho diversificato perché la gestione degli anticipi è legata alla fase di istruttoria/valutazione..altro responsabile
        $giorniPerRispostaComunicazioni = $giorniPerRispostaComunicazioniConfig ?: $pagamento::GIORNI_RISPOSTA_INTEGRAZIONE_DEFAULT;
        $form_options = [
            'url_indietro' => $indietro,
            'disabled' => (($integrazione->getStato() == 'INT_PROTOCOLLATA') || ($integrazione->getStato() == 'INT_INVIATA_PA') || $disabilita == true),
            'mostra_giorni_per_risposta' => isset($giorniPerRispostaComunicazioniConfig) ? true : false
        ];

        $form = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IntegrazioneType", $integrazione, $form_options);

        $request = $this->getCurrentRequest();

        $em = $this->getEm();
        $em->persist($integrazione);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                try {
                    $em->beginTransaction();

                    $documentoIntegrazione = $this->creaAllegatoIntegrazione($pagamento);
                    $integrazione->setDocumento($documentoIntegrazione);

                    $em->flush();

                    if ($form->get("pulsanti")->get("pulsante_invio")->isClicked()) {

                        $esitoPag = $this->controllaValiditaIstruttoriaPagamento($pagamento);
                        if ($esitoPag->getEsito() == false) {
                            return new GestoreResponse($this->addErrorRedirect("Non è possibile inviare un'integrazione se non sono state istruite tutte le sezioni", "gestione_richiesta_integrazione", array("id_integrazione" => $integrazione->getId())));
                        }

                        $this->container->get("sfinge.stati")->avanzaStato($integrazione, \BaseBundle\Entity\StatoIntegrazione::INT_INVIATA_PA);
                        $em->flush();
                        if ($this->container->getParameter("stacca_protocollo_al_volo")) {

                            $richiestaProtocollo = $this->container->get("docerinitprotocollazione")->setTabProtocollazioneIntegrazionePagamento($pagamento, $integrazione);
                            /**
                             * schedulo un invio email per protocollazione in uscita tramite egrammata
                             * l'email verrà mandata solo dopo che lo stato della richiestaProtocollo evolverà in POST_PROTOCOLLAZIONE
                             * Questo blocco andrà riportato negli eventuali GestoriIstruttoriaBando scritti ad hoc che faranno 
                             * l'overwrite del metodo creaIntegrazione 
                             */
                            /*                             * ********************************************************************** * */
                            if (!$this->schedulaEmailProtocollo($richiestaProtocollo)) {
                                throw new \Exception('Errore durante la schedulazione dell\'EmailProtocollo');
                            }
                            /*                             * ********************************************************************** * */
                        }
                        $em->commit();
                        $this->addFlash('success', "Integrazione inviata con successo");
                    } else {
                        $em->commit();
                        $this->addFlash('success', "Integrazione salvata con successo");
                    }
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }

                return new GestoreResponse($this->redirect($indietro));
            }
        }

        $dati = [];
        $dati["menu"] = "crea_integrazione";
        $dati["form"] = $form->createView();
        $dati["pagamento"] = $pagamento;
        $dati["giorni_risposta_default"] = $giorniPerRispostaComunicazioni;
        $dati["mostra_giorni_per_risposta"] = $form_options['mostra_giorni_per_risposta'];

        $twig = "AttuazioneControlloBundle:Integrazione:creaIntegrazione.html.twig";

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Comunicazioni", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Gestione integrazione");

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    public function riepilogoIntegrazione($pagamento) {

        $indietro = $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId()));

        $dati = array();
        $dati["menu"] = "integrazione";
        $dati["pagamento"] = $pagamento;
        $dati["indietro"] = $indietro;

        $twig = "AttuazioneControlloBundle:Integrazione:riepilogoIntegrazione.html.twig";

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Integrazione");

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    public function istruttoriaIntegrazione($integrazione) {

        $pagamento = $integrazione->getPagamento();
        $indietro = $this->generateUrl('integrazione_pagamento', array("id_pagamento" => $pagamento->getId()));

        $istruttoria = $integrazione->getIstruttoriaOggettoPagamento();

        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $integrazione->setIstruttoriaOggettoPagamento($istruttoria);
        }

        $dati_form_istruttoria = array('url_indietro' => $indietro, 'nascondi_integrazione' => true);
        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $dati_form_istruttoria);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form_istruttoria->handleRequest($request);
            if ($form_istruttoria->isValid()) {
                try {
                    $em = $this->getEm();
                    $em->persist($integrazione);
                    $em->flush();
                    return $this->addSuccesRedirect("Istruttoria Integrazione salvata correttamente", 'istruttoria_integrazione', array("id_integrazione" => $integrazione->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Integrazione", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Istruttoria integrazione");

        $dati = array();
        $dati["pagamento"] = $pagamento;
        $dati["integrazione"] = $integrazione;
        $dati["indietro"] = $indietro;
        $dati["form_istruttoria"] = $form_istruttoria->createView();

        return $this->render("AttuazioneControlloBundle:Integrazione:istruttoriaIntegrazione.html.twig", $dati);
    }

    public function istruttoriaDocumentoIntegrazione($integrazione, $id_documento_integrazione) {

        $em = $this->getEm();
        $documento = $em->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaIntegrazionePagamento")->find($id_documento_integrazione);

        $istruttoria = $documento->getIstruttoriaOggettoPagamento();
        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $documento->setIstruttoriaOggettoPagamento($istruttoria);
        }

        $indietro = $this->generateUrl('istruttoria_integrazione', array("id_integrazione" => $integrazione->getId()));

        $dati_form_istruttoria = array('url_indietro' => $indietro, 'nascondi_integrazione' => true);
        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $dati_form_istruttoria);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form_istruttoria->handleRequest($request);
            if ($form_istruttoria->isValid()) {
                try {
                    $em->persist($documento);
                    $em->flush();
                    return $this->addSuccesRedirect("Istruttoria documento salvata correttamente", 'istruttoria_integrazione', array("id_integrazione" => $integrazione->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $pagamento = $integrazione->getPagamento();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Integrazione", $this->generateUrl('integrazione_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Istruttoria integrazione", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Istruttoria documento");

        $dati["form_istruttoria"] = $form_istruttoria->createView();
        $dati["titolo_sezione"] = "Documento " . $documento->getDocumentoFile()->getNomeOriginale();

        return $this->render("AttuazioneControlloBundle:Istruttoria:pannelloIstruttoriaGenerale.html.twig", $dati);
    }

    public function checklistGenerale($pagamento) {

        $indietro = $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId()));

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco checklist");

        try {
            $this->inizializzaIstruttoriaPagamento($pagamento);
            $this->getEm()->flush();
        } catch (\Exception $e) {
            $this->addFlash("error", "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
            throw $e;
            return $this->redirectToRoute("elenco_istruttoria_pagamenti");
        }

        $enableChecklistAppalti = false;

        $checklistPreviste = $this->getChecklistPreviste($pagamento);
        foreach ($checklistPreviste as $checklistPrevista) {
            if ($checklistPrevista->isTipologiaAppaltiPubblici()) {
                $enableChecklistAppalti = true;
                break;
            }
        }

        $em = $this->getEm();

        $rep = $em->getRepository('AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento');
        $rep2 = $em->getRepository('AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamento');

        $valutazioniChecklistGeneriche = $rep->getValutazioniChecklistGenericheByPagamento($pagamento);

        // sono definite solo per i pubblici
        $valutazioniChecklistAppalti = $rep->getValutazioniChecklistAppaltiByPagamento($pagamento);

        $datiValutazioniChecklistAppalti = array();
        foreach ($valutazioniChecklistAppalti as $valutazioneChecklistAppalti) {
            $a1 = $rep2->getValutazioneElementoByCodice($valutazioneChecklistAppalti, 'A1');
            $a2 = $rep2->getValutazioneElementoByCodice($valutazioneChecklistAppalti, 'A2');
            $a3 = $rep2->getValutazioneElementoByCodice($valutazioneChecklistAppalti, 'A3');
            $a4 = $rep2->getValutazioneElementoByCodice($valutazioneChecklistAppalti, 'A4');
            $a7 = $rep2->getValutazioneElementoByCodice($valutazioneChecklistAppalti, 'A7');
            $a8 = $rep2->getValutazioneElementoByCodice($valutazioneChecklistAppalti, 'A8');
            $a9 = $rep2->getValutazioneElementoByCodice($valutazioneChecklistAppalti, 'A9');

            $datiValutazioniChecklistAppalti[] = array(
                'tipoAppalto' => $a1->getValore(),
                'criterioAggiudicazione' => $a2->getValore(),
                'cig' => $a3->getValore(),
                'contraente' => $a4->getValore(),
                'importoContratto' => $a7->getValore(),
                'estremiGiustificativi' => $a8->getValore(),
                'importoSpeseAmmissibili' => $a9->getValore(),
                'valutazioneChecklist' => $valutazioneChecklistAppalti
            );
        }

        $dati['valutazioniChecklistGeneriche'] = $valutazioniChecklistGeneriche;
        $dati['datiValutazioniChecklistAppalti'] = $datiValutazioniChecklistAppalti;
        $dati["indietro"] = $indietro;
        $dati["pagamento"] = $pagamento;
        $dati["enable_checklist_appalti"] = $enableChecklistAppalti;
        $dati["pagamento_disabled"] = $this->isDisabled($pagamento);

        return $this->render("AttuazioneControlloBundle:Istruttoria\Checklist:elencoChecklist.html.twig", $dati);
    }

    protected function getNomePdfIntegrazione($pagamento) {
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $date = new \DateTime();
        $data = $date->format('d-m-Y');
        return 'Richiesta di integrazione ' . $richiesta->getMandatario() . ' Pagamento ' . $pagamento->getId() . ' ' . $data;
    }

    public function pdfIntegrazioneIstruttoria($pagamento, $download = true) {

        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($pagamento->getRichiesta()->getProcedura());

        $twig = "@AttuazioneControllo/Pdf/Istruttoria/richiesta_integrazione.html.twig";

        $dati = $this->datiPdfIntegrazione($pagamento);
        $dati["titolo_pdf"] = 'RICHIESTA DI INTEGRAZIONE';
        $dati["rendicontazioneProceduraConfig"] = $rendicontazioneProceduraConfig;

        $isFsc = $this->container->get("gestore_richieste")->getGestore($pagamento->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;

        $pdf = $this->container->get("pdf");
        $pdf->load($twig, $dati);

        //return $this->render($twig,$dati);

        if ($download) {
            $nome_file = $this->getNomePdfIntegrazione($pagamento);
            $pdf->download($nome_file);
            return new Response();
        } else {
            return $pdf->binaryData();
        }
    }

    protected function creaAllegatoIntegrazione($pagamento) {
        $tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice(TipologiaDocumento::RICHIESTA_INTEGRAZIONE_PAGAMENTO);
        $documentoIntegrazione = $this->container->get("documenti")->caricaDaByteArray($this->pdfIntegrazioneIstruttoria($pagamento, false), $this->getNomePdfIntegrazione($pagamento) . ".pdf", $tipoDocumento, false);

        return $documentoIntegrazione;
    }

    protected function getNomePdfChiarimenti($pagamento) {
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $date = new \DateTime();
        $data = $date->format('d-m-Y');
        return 'Richiesta di chiarimenti ' . $richiesta->getMandatario() . ' Pagamento ' . $pagamento->getId() . ' ' . $data;
    }

    public function pdfChiarimentiIstruttoria($pagamento, $download = true) {

        $twig = "@AttuazioneControllo/Pdf/Istruttoria/richiesta_chiarimenti.html.twig";
        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($pagamento->getRichiesta()->getProcedura());

        $dati = $this->datiPdfChiarimenti($pagamento);
        $dati["titolo_pdf"] = 'RICHIESTA DI CHIARIMENTI';
        $dati["rendicontazioneProceduraConfig"] = $rendicontazioneProceduraConfig;

        $isFsc = $this->container->get("gestore_richieste")->getGestore($pagamento->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;

        $pdf = $this->container->get("pdf");
        $pdf->load($twig, $dati);

        //return $this->render($twig,$dati);

        if ($download) {
            $nome_file = $this->getNomePdfChiarimenti($pagamento);
            $pdf->download($nome_file);
            return new Response();
        } else {
            return $pdf->binaryData();
        }
    }

    protected function creaAllegatoChiarimenti($pagamento) {
        $tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice(TipologiaDocumento::RICHIESTA_CHIARIMENTI);
        $documentoChiarimenti = $this->container->get("documenti")->caricaDaByteArray($this->pdfChiarimentiIstruttoria($pagamento, false), $this->getNomePdfChiarimenti($pagamento) . ".pdf", $tipoDocumento, false);

        return $documentoChiarimenti;
    }

    protected function datiPdfIntegrazione($pagamento, $facsimile = false) {

        $integrazioni = $pagamento->getIntegrazioni();
        $integrazione = $integrazioni->last();

        // recupero i dati flaggati come da integrare dalle varie sezioni
        $datiDaIntegrare = $this->recuperaDatiSezioniDaIntegrare($pagamento);
        $datiDaIntegrare['nota_integrazione'] = null;

        if ($integrazione) {
            $testo = $integrazione->getTesto();
            if (!empty($testo)) {
                $datiDaIntegrare['nota_integrazione'] = $testo;
            }
        }

        $datiPdf = $this->datiPdf($pagamento, $facsimile = false);

        return array_merge(array('dati_integrazione' => $datiDaIntegrare), $datiPdf);
    }

    protected function datiPdfChiarimenti($pagamento, $facsimile = false) {

        $chiarimenti = $pagamento->getRichiesteChiarimenti();
        $chiarimento = $chiarimenti->last();

        $datiDaIntegrare = $this->recuperaDatiSezioniDaIntegrare($pagamento);

        $datiDaIntegrare['nota_integrazione'] = null;
        if ($chiarimento) {
            $testo = $chiarimento->getTesto();
            if (!empty($testo)) {
                $datiDaIntegrare['nota_integrazione'] = $testo;
            }
        }

        $datiPdf = $this->datiPdf($pagamento, $facsimile = false);

        return array_merge(array('dati_integrazione' => $datiDaIntegrare), $datiPdf);
    }

    /**
     *  al momento visto che sembra essere uguale sia per chiarimenti che per integrazione 
     *  definiamo questo e la richiamiamo
     *  se in futuro dovesse essere necessario diversificare basta fornire direttamente
     *  le due implementazioni di datiPdfIntegrazione e datiPdfChiarimenti
     */
    protected function datiPdf($pagamento, $facsimile = false) {
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $dati = array();
        $dati["pagamento"] = $pagamento;
        $dati["procedura"] = $richiesta->getProcedura();
        $dati["richiesta"] = $richiesta;
        $dati["capofila"] = $richiesta->getMandatario();
        $dati['facsimile'] = $facsimile;

        //da definire..ancora non si sa cosa metterci

        return $dati;
    }

    public function cancellaRichiestaIntegrazione($integrazione) {

        $id_pagamento = $integrazione->getPagamento()->getId();

        if ($integrazione->getStato() != \BaseBundle\Entity\StatoIntegrazione::INT_INSERITA) {
            return $this->addErrorRedirect("Lo stato dell'integrazione non ne permette la cancellazione", "integrazione_pagamento", array("id_pagamento" => $id_pagamento));
        }

        $em = $this->getEm();

        try {
            $em->beginTransaction();
            $em->remove($integrazione);
            $em->remove($integrazione->getRisposta());
            $em->flush();
            $em->commit();
        } catch (\Exception $ex) {
            $em->rollback();
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "integrazione_pagamento", array("id_pagamento" => $id_pagamento));
        }
        return $this->addSuccesRedirect("Integrazione pagamento eliminata con successo", "integrazione_pagamento", array("id_pagamento" => $id_pagamento));
    }

    public function cancellaRichiestaChiarimenti($richiesta_chiarimenti) {

        $id_pagamento = $richiesta_chiarimenti->getPagamento()->getId();

        if ($richiesta_chiarimenti->getStato() != StatoRichiestaChiarimenti::RICH_CHIAR_INSERITA) {
            return $this->addErrorRedirect("Lo stato della richiesta di chiarimenti non ne permette la cancellazione", "richiesta_chiarimento_pagamento", array("id_pagamento" => $id_pagamento));
        }

        $em = $this->getEm();

        try {
            $em->beginTransaction();
            $em->remove($richiesta_chiarimenti);
            $em->remove($richiesta_chiarimenti->getRisposta());
            $em->flush();
            $em->commit();
        } catch (\Exception $ex) {
            $em->rollback();
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "richiesta_chiarimento_pagamento", array("id_pagamento" => $id_pagamento));
        }
        return $this->addSuccesRedirect("Richiesta di chiarimenti eliminata con successo", "richiesta_chiarimento_pagamento", array("id_pagamento" => $id_pagamento));
    }

    public function dateProgettoPagamento($pagamento) {

        $options = array();

        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $options["tipologia"] = $pagamento->getModalitaPagamento()->getCodice();
        $options["disabled"] = true;
        $options["url_indietro"] = $this->generateUrl("riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));

        $dateProgetto = $this->getDateProgetto($pagamento);

        $pagamento->data_avvio_progetto = $dateProgetto->dataAvvioProgetto;
        $pagamento->data_termine_progetto = $dateProgetto->dataTermineProgetto;

        $form = $this->createForm("AttuazioneControlloBundle\Form\DateProgettoPagamentoStandardType", $pagamento, $options);

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl("riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Date progetto");

        $options["form"] = $form->createView();
        $options["pagamento"] = $pagamento;
        $options["richiesta"] = $richiesta;

        return $this->render("AttuazioneControlloBundle:Pagamenti:dateProgettoPagamento.html.twig", $options);
    }

    public function datiBancariPagamento($pagamento) {

        $em = $this->getEm();

        $richiesta = $pagamento->getRichiesta();
        $proponenti = $richiesta->getProponenti();
        $datiBancariProponenti = array();

        foreach ($proponenti as $proponente) {
            $datiBancari = $proponente->getDatiBancari()->first();
            if ($datiBancari) {
                $datiBancariProponenti[] = $datiBancari;
            }
        }

        $urlIndietro = $this->generateUrl("riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $urlIndietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dati bancari");

        /*         * * ISTRUTTORIA ** */

        $istruttoria = $pagamento->getIstruttoriaDatiBancari();
        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $pagamento->setIstruttoriaDatiBancari($istruttoria);
        }

        $options = array('url_indietro' => $urlIndietro, 'disabled' => $this->isDisabled($pagamento));

        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $options);
        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form_istruttoria->handleRequest($request);
            if ($istruttoria->isIntegrazione() && (is_null($istruttoria->getNotaIntegrazione()) || $istruttoria->getNotaIntegrazione() == '')) {
                $form_istruttoria->get('nota_integrazione')->addError(new \Symfony\Component\Form\FormError('Il campo note è obbligatorio in caso di integrazione'));
            }
            if ($form_istruttoria->isValid()) {
                try {
                    $em = $this->getEm();
                    $em->persist($pagamento);
                    $em->flush();
                    return $this->addSuccesRedirect("Istruttoria salvata correttamente", 'riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        /*         * * FINE ISTRUTTORIA ** */

        $dati = array();
        $dati["form_istruttoria"] = $form_istruttoria->createView();
        $dati["istruttoria"] = true;
        $dati["pagamento"] = $pagamento;
        $dati["richiesta"] = $richiesta;
        $dati['datiBancariProponenti'] = $datiBancariProponenti;

        return $this->render("AttuazioneControlloBundle:Istruttoria\Pagamenti:datiBancariPagamento.html.twig", $dati);
    }

    public function validaGiustificativiIstruttoria($pagamento) {

        $esito = new EsitoValidazione(true);

        $almenoUnaIntegrazione = false;

        foreach ($pagamento->getGiustificativi() as $giustificativo) {
            $tipologia = $giustificativo->getTipologiaGiustificativo();
            if (!is_null($tipologia) && $tipologia->isInvisibile() == true) {
                continue;
            }
            $istruttoria = $giustificativo->getIstruttoriaOggettoPagamento();

            if (is_null($giustificativo->getImportoApprovato()) || is_null($istruttoria) || ($istruttoria->isIncompleta())) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione("E' necessario istruire tutti i giustificativi");
                // torniamo un solo messaggio 
                return $esito;
            }

            $almenoUnaIntegrazione = $almenoUnaIntegrazione || (!is_null($istruttoria) && $istruttoria->isIntegrazione());
        }

        /**
         * mi accodo a quanto fatto per la gestione dell'icona blu anziche la verde in caso di integrazione
         * vedi views/Pagamenti/mostraValidazioneInLineAttuazione.html.twig
         */
        if ($almenoUnaIntegrazione) {
            $esito->setMessaggio('Integrazione');
        }

        return $esito;
    }

    public function validaContrattiIstruttoria($pagamento) {

        $esito = new EsitoValidazione(true);

        $almenoUnaIntegrazione = false;

        foreach ($pagamento->getContratti() as $contratto) {

            $istruttoria = $contratto->getIstruttoriaOggettoPagamento();

            if (is_null($istruttoria) || ($istruttoria->isIncompleta())) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione("E' necessario istruire tutti i contratti");
                // torniamo un solo messaggio 
                return $esito;
            }

            foreach ($contratto->getDocumentiContratto() as $doc) {
                $istruttoriaDoc = $doc->getIstruttoriaOggettoPagamento();

                if (is_null($istruttoriaDoc) || ($istruttoriaDoc->isIncompleta())) {
                    $esito->setEsito(false);
                    $esito->addMessaggioSezione("E' necessario istruire tutti i documenti dei contratti");
                    // torniamo un solo messaggio 
                    return $esito;
                }
            }

            $almenoUnaIntegrazione = $almenoUnaIntegrazione || (!is_null($istruttoria) && $istruttoria->isIntegrazione());
        }

        $esitoGiustificativi = $this->validaGiustificativiIstruttoria($pagamento);
        if ($esitoGiustificativi->getEsito() == false) {
            $almenoUnaIntegrazione = true;
        }

        /**
         * mi accodo a quanto fatto per la gestione dell'icona blu anziche la verde in caso di integrazione
         * vedi views/Pagamenti/mostraValidazioneInLineAttuazione.html.twig
         */
        if ($almenoUnaIntegrazione) {
            $esito->setMessaggio('Integrazione');
        }

        return $esito;
    }

    public function validaIstruttoriaDatibancari($pagamento) {

        $esito = new EsitoValidazione(true);

        $istr = $pagamento->getIstruttoriaDatiBancari();
        if (is_null($istr) || ($istr->isIncompleta())) {
            $esito->setEsito(false);

            $esito->addMessaggioSezione("Istruttoria dati bancari incompleta");
        }

        /**
         * mi accodo a quanto fatto per la gestione dell'icona blu anziche la verde in caso di integrazione
         * vedi views/Pagamenti/mostraValidazioneInLineAttuazione.html.twig
         */
        if (!is_null($istr) && $istr->isIntegrazione()) {
            $esito->setMessaggio('Integrazione');
        }

        return $esito;
    }

    public function calcolaAvanzamentoPianoCosti($richiesta, $proponente, $pagamento_rif, $annualita = null) {

        //commento per via della nuova gestione delle variaizoni
        //$ultima_variazione = $richiesta->getAttuazioneControllo()->getUltimaVariazioneApprovata();

        $ultima_variazione = $richiesta->getAttuazioneControllo()->getUltimaVariazionePianoCostiPA($pagamento_rif);
        $avanzamento = array();
        $totali = array();

        $dataRiferimento = $pagamento_rif->getDataInvio();

        $pagamenti = $this->getPagamentiDaAvanzamento($richiesta, $pagamento_rif);

        $voci = is_null($proponente) ? $richiesta->getVociPianoCosto() : $proponente->getVociPianoCosto();
        foreach ($voci as $voce) {

            $sezione = $voce->getPianoCosto()->getSezionePianoCosto();

            if (!isset($avanzamento[$sezione->getId()])) {
                $avanzamento[$sezione->getId()] = array("sezione" => $sezione, "voci" => array());
            }

            if (!isset($totali[$sezione->getId()])) {
                $totali[$sezione->getId()] = array("rendicontato" => 0, "pagato" => 0);
            }

            if ($richiesta->isProceduraParticolare() == false) {
                if (!is_null($annualita)) {
                    $importo_ammesso = $voce->getIstruttoria()->{"getImportoAmmissibileAnno" . $annualita}();
                } else {
                    $importo_ammesso = $voce->getIstruttoria()->sommaImporti();
                }
            } else {
                $importo_ammesso = $voce->{"getImportoAnno1" }();
            }

            if (!is_null($ultima_variazione) && !$ultima_variazione->getIgnoraVariazione()) {
                $variazione_voce = $ultima_variazione->getVariazioneVocePianoCosto($voce);
                if (!is_null($annualita)) {
                    $importo_variato = $variazione_voce->{"getImportoVariazioneAnno" . $annualita}();
                } else {
                    $importo_variato = $variazione_voce->sommaImporti();
                }
            } else {
                $importo_variato = $importo_ammesso;
            }

            $importo_rendicontato = 0;
            $importo_rendicontato_ammesso = 0;

            foreach ($voce->getVociGiustificativi() as $voce_giustificativo) {
                // il try catch serve per gestire la cancellazione logica, se l'oggetto è cancellato viene lanciata un'eccezione
                try {
                    $pagamento = $voce_giustificativo->getGiustificativoPagamento()->getPagamento();
                    // Aggiungo controllo sulla data per l'avanzamento del piano costi
                    if ($pagamento->getDataInvio() <= $dataRiferimento) {
                        if (!is_null($pagamento->getEsitoIstruttoria()) && !$pagamento->getEsitoIstruttoria()) {
                            continue;
                        }

                        if ($pagamento->getStato()->getCodice() == "PAG_PROTOCOLLATO" && ($voce_giustificativo->getAnnualita() == $annualita || is_null($annualita))) {
                            $importo_rendicontato += $voce_giustificativo->getImporto();
                            if (!is_null($pagamento->getEsitoIstruttoria()) && $pagamento->getEsitoIstruttoria()) {
                                $importo_rendicontato_ammesso += $voce_giustificativo->getImportoApprovato();
                            }
                        }

                        if ($pagamento->getStato()->getCodice() == "PAG_INVIATO_PA" && $pagamento->isProceduraParticolare() == true) {
                            $importo_rendicontato += $voce_giustificativo->getImporto();
                            $importo_rendicontato_ammesso += $voce_giustificativo->getImportoApprovato();
                        }
                    }
                } catch (\Exception $e) {
                    
                }
            }

            if ($voce->getPianoCosto()->getCodice() != "TOT") {
                $totali[$sezione->getId()]["rendicontato"] += $importo_rendicontato;
                $totali[$sezione->getId()]["pagato"] += $importo_rendicontato_ammesso;
            } else {
                $importo_rendicontato = $totali[$sezione->getId()]["rendicontato"];
                $importo_rendicontato_ammesso = $totali[$sezione->getId()]["pagato"];
            }

            $avanzamento[$sezione->getId()]["voci"][] = array("voce" => $voce,
                "ammesso" => $importo_ammesso,
                "variato" => $importo_variato,
                "rendicontato" => $importo_rendicontato,
                "pagato" => $importo_rendicontato_ammesso);
        }

        $avanzamentoCompleto = array();
        $avanzamentoCompleto['avanzamento'] = $avanzamento;
        $avanzamentoCompleto['pagamenti'] = $pagamenti;

        return $avanzamentoCompleto;
    }

    public function riepilogoAnticipiPagamento($pagamento, $pagamento_anticipo) {

        $anticipi = $pagamento_anticipo->getRipartizioniImportiPagamento();
        $indietro = $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId()));

        $dati = array("pagamento" => $pagamento, "pagamento_anticipo" => $pagamento_anticipo, "menu" => "anticipi", "anticipi" => $anticipi, "indietro" => $indietro);

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo anticipi pagamento");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:riepilogoAnticipiPagamento.html.twig", $dati);
    }

    public function gestisciAnticipoPagamento($pagamento, $pagamento_anticipo, $anticipo) {

        $em = $this->getEm();
        $indietro = $this->generateUrl('riepilogo_anticipi_pagamento', array("id_pagamento" => $pagamento->getId()));
        $proponenti = $anticipo->getPagamento()->getRichiesta()->getProponenti();

        if ($this->isGranted("ROLE_PAGAMENTI_READONLY") || $this->isGranted("ROLE_OPERATORE_COGEA")) {
            $disabilita = true;
        } else {
            $disabilita = false;
        }

        $dati_form_anticipo = array('url_indietro' => $indietro, "proponenti" => $proponenti, 'disabled' => $disabilita);
        $form_anticipo = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\AnticipoPagamentoType", $anticipo, $dati_form_anticipo);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form_anticipo->handleRequest($request);
            if ($form_anticipo->isValid()) {
                try {
                    $anticipo_verifica = $em->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RipartizioneImportiPagamento")->findOneBy(array("proponente" => $anticipo->getProponente(), "pagamento" => $anticipo->getPagamento()));
                    if (!is_null($anticipo_verifica) && $anticipo->getId() != $anticipo_verifica->getId()) {
                        return $this->addWarningRedirect("Esiste già un anticipo per il proponente: " . $anticipo->getProponente(), 'riepilogo_anticipi_pagamento', array("id_pagamento" => $pagamento->getId()));
                    }
                    $em->persist($anticipo);
                    $em->flush();
                    return $this->addSuccesRedirect("Anticipo pagamento salvato correttamente", 'riepilogo_anticipi_pagamento', array("id_pagamento" => $pagamento->getId()));
                } catch (\Exception $e) {
                    return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", 'riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId()));
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo anticipi", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Anticipo pagamento");

        $dati["form"] = $form_anticipo->createView();
        $dati["pagamento"] = $pagamento;
        $dati["pagamento_anticipo"] = $pagamento_anticipo;

        return $this->render("AttuazioneControlloBundle:Istruttoria\Pagamenti:anticipoPagamento.html.twig", $dati);
    }

    /**
     * @param Pagamento $pagamento
     */
    public function creaAnticipoPagamento($pagamento, $pagamento_anticipo, $opzioni = array()) {

        $proponenti = $pagamento->getRichiesta()->getProponenti();

        if ($this->isGranted("ROLE_PAGAMENTI_READONLY") || $this->isGranted("ROLE_OPERATORE_COGEA")) {
            return $this->addErrorRedirect("Azione non permessa per il ruolo", 'riepilogo_anticipi_pagamento', array("id_pagamento" => $pagamento->getId()));
        }

        $anticipo = new \AttuazioneControlloBundle\Entity\Istruttoria\RipartizioneImportiPagamento();
        $anticipo->setPagamento($pagamento_anticipo);

        $em = $this->getEm();
        $em->persist($anticipo);

        $indietro = $this->generateUrl('riepilogo_anticipi_pagamento', array("id_pagamento" => $pagamento->getId()));

        if ($this->isGranted("ROLE_PAGAMENTI_READONLY") || $this->isGranted("ROLE_OPERATORE_COGEA")) {
            
        }

        $form_options = array(
            "url_indietro" => $indietro,
            "proponenti" => $proponenti,
            'disabled' => $pagamento->isPagamentoDisabilitato(),
        );

        $form = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\AnticipoPagamentoType", $anticipo, $form_options);

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($request->isSubmitted() && $form->isValid()) {
            try {
                $anticipo_verifica = $em->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RipartizioneImportiPagamento")->findOneBy(array("proponente" => $anticipo->getProponente(), "pagamento" => $pagamento_anticipo));
                if (!is_null($anticipo_verifica)) {
                    return $this->addWarningRedirect("Esiste già un anticipo per il proponente: " . $anticipo->getProponente(), 'riepilogo_anticipi_pagamento', array("id_pagamento" => $pagamento->getId()));
                }
                $em->persist($anticipo);
                $em->flush();
                return $this->addSuccesRedirect("Anticipo pagamento salvato correttamente", 'riepilogo_anticipi_pagamento', array("id_pagamento" => $pagamento->getId()));
            } catch (\Exception $e) {
                return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", 'riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId()));
            }
        }

        $dati = array();
        $dati["menu"] = "crea_anticipo";
        $dati["form"] = $form->createView();
        $dati["pagamento"] = $pagamento;
        $dati["pagamento_anticipo"] = $pagamento_anticipo;

        $twig = "AttuazioneControlloBundle:Istruttoria\Pagamenti:anticipoPagamento.html.twig";

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo anticipi", $indietro);

        return $this->render($twig, $dati);
    }

    /**
     * @param \AttuazioneControlloBundle\Entity\Pagamento $pagamento
     *
     * Metodo richiamato contestualmente al salvataggio della CHECKLIST del PAGAMENTO (per i soli BENEFICIARI PUBBLICI)
     * serve per popolare automaticamente le economie; utile ai fii del monitoraggio
     */
    protected function popolaEconomiePubblico(Pagamento $pagamento) {

        $em = $this->getEm();

        // Valore ritornato dalla funzione
        $economie = array();

        $richiesta = $pagamento->getRichiesta();
        $istruttoriaRichiesta = $richiesta->getIstruttoria();

        // TC33/FONTE FINANZIARIA
        $fonteAltroPubblico = $em->getRepository("MonitoraggioBundle:TC33FonteFinanziaria")->findBy(array("cod_fondo" => "ALTRO_NAZ"));
        $fonteUE = $em->getRepository("MonitoraggioBundle:TC33FonteFinanziaria")->findBy(array("cod_fondo" => "ERDF"));
        $fonteStato = $em->getRepository("MonitoraggioBundle:TC33FonteFinanziaria")->findBy(array("cod_fondo" => "FDR"));
        $fonteRegione = $em->getRepository("MonitoraggioBundle:TC33FonteFinanziaria")->findBy(array("cod_fondo" => "FPREG"));

        // I TOTALI
        $costoAmmesso = $istruttoriaRichiesta->getCostoAmmesso(); // Il totale ammesso - COSTO TOTALE DEL PROGETTO
        $contributoConcesso = $istruttoriaRichiesta->getContributoAmmesso(); // Il massimo contributo erogabile
        // I RENDICONTATI
        // PAGAMENTI PRECEDENTI + ATTUALE
        $pagamentiPrecedenti = $richiesta->getAttuazioneControllo()->getPagamenti();

        $contributoPagato = 0.00; // il totale pagato da UE-Stato-Regione // CONTRIBUTO EROGATO A SALDO
        $rendicontatoAmmesso = 0.00; // quanto ha rendicontato il beneficiario


        foreach ($pagamentiPrecedenti as $pagamentoPrecedente) {

            // Sommo gli importi dei pagamenti COPERTI DA MANDATO
            if (!is_null($pagamentoPrecedente->getMandatoPagamento())) {
                $contributoPagato += $pagamentoPrecedente->getMandatoPagamento()->getImportoPagato();  // Contributo erogato a SALDO
            }

            $rendicontatoAmmesso += $pagamentoPrecedente->getRendicontatoAmmesso();  // Somma degli IMPORTI APPROVATI dei GIUSTIFICATIVI
        }

        // GLI IMPORTI DELLE ECONOMIE
        $importoEconomiaTotale = $costoAmmesso - $rendicontatoAmmesso; // economia totale(privato + UE-Stato-Regione)
        // Siamo nel CASO 1 dell'EXCEL
        if ($importoEconomiaTotale > 0) {

            // Creazione ECONOMIE
            $economiaAltroPubblico = new Economia();
            $economiaUE = new Economia();
            $economiaStato = new Economia();
            $economiaRegione = new Economia();

            $importoEconomiaQuote = $contributoConcesso - $contributoPagato; // economia che avanza dai contributi UE-Stato-Regione // ECONOMIA DI CONTRIBUTO
            $importoEconomiaAltroPubblico = $importoEconomiaTotale - $importoEconomiaQuote;

            $importoEconomiaUE = round($importoEconomiaQuote * 50 / 100, 2);
            $importoEconomiaStato = round($importoEconomiaQuote * 35 / 100, 2);
            $importoEconomiaRegione = $importoEconomiaQuote - ($importoEconomiaUE + $importoEconomiaStato);

            // SETTO GLI IMPORTI ALLE 4 ECONOMIE
            $economiaAltroPubblico->setImporto($importoEconomiaAltroPubblico);
            $economiaUE->setImporto($importoEconomiaUE);
            $economiaStato->setImporto($importoEconomiaStato);
            $economiaRegione->setImporto($importoEconomiaRegione);

            // FONTE
            $economiaAltroPubblico->setTc33FonteFinanziaria($fonteAltroPubblico[0]);
            $economiaUE->setTc33FonteFinanziaria($fonteUE[0]);
            $economiaStato->setTc33FonteFinanziaria($fonteStato[0]);
            $economiaRegione->setTc33FonteFinanziaria($fonteRegione[0]);

            // RICHIESTA
            $economiaAltroPubblico->setRichiesta($richiesta);
            $economiaUE->setRichiesta($richiesta);
            $economiaStato->setRichiesta($richiesta);
            $economiaRegione->setRichiesta($richiesta);

            // IMPOSTO il valore di ritorno
            $economie[] = $economiaAltroPubblico;
            $economie[] = $economiaUE;
            $economie[] = $economiaStato;
            $economie[] = $economiaRegione;
        }

        return $economie;
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

            $pianoCosti->setAnnoPiano(\intval(date("Y")));

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

    /**
     * @param Richiesta $richiesta
     *
     * Metodo richiamato contestualmente al passaggio della richiesta in istruttoria, tramite il pulsante VALIDA
     * serve per popolare automaticamente lo STATO ATTUAZIONE PROGETTO; utile ai fine del monitoraggio
     */
    protected function popolaStatoFinaleAttuazioneProgettoPubblico(Richiesta $richiesta, CupNatura $natura, $pagamento) {

        // DESTINAZIONE
        $statoAttuazioneProgettoMon = new RichiestaStatoAttuazioneProgetto();
        $em = $this->getEm();
        $dataRiferimento = new \DateTime();

        $statoAttuazioneProgettoMon->setRichiesta($richiesta);

        if ($natura->getCodice() == '03') { // REALIZZAZIONE DI LAVORI PUBBLICI (OPERE ED IMPIATISTICA
            // TODO: IN ESERCIZIO; data collaudo - FINE EFFETTIVA
            $vociFasiProcedurali = $richiesta->getVociFaseProcedurale();

            foreach ($vociFasiProcedurali as $voceFaseProcedurale) {
                if ($voceFaseProcedurale->getFaseProcedurale()->getFaseNatura()->getCodice() == '0307') {  // COLLAUDO
                    $dataRiferimento = $voceFaseProcedurale->getDataFineEffettiva();
                    break;
                }
            }


            // STATO PROGETTO - TC47StatoProgetto - STATO FINALE
            $statoFinaleProgetto = $em->getRepository("MonitoraggioBundle\Entity\TC47StatoProgetto")->findBy(array("descr_stato_prg" => "In esercizio"));
        } else {

            // TODO: CONCLUSO; → Pagamento a SALDO - DATA INVIO
            // STATO PROGETTO - TC47StatoProgetto - STATO FINALE
            $dataRiferimento = $pagamento->getDataInvio();
            $statoFinaleProgetto = $em->getRepository("MonitoraggioBundle\Entity\TC47StatoProgetto")->findBy(array("descr_stato_prg" => "Concluso"));
        }

        $statoAttuazioneProgettoMon->setDataRiferimento($dataRiferimento);

        $statoAttuazioneProgettoMon->setStatoProgetto($statoFinaleProgetto[0]);

        // setto la DESTINAZIONE nella richiesta
        $richiesta->addMonStatoProgetti($statoAttuazioneProgettoMon);
    }

    public function relazioneFinale($id_pagamento) {

        $em = $this->getEm();

        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        $url_indietro = $this->generateUrl("riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $url_indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Relazione finale a saldo");

        /*         * * ISTRUTTORIA ** */

        $istruttoria = $pagamento->getIstruttoriaRelazioneFinaleSaldo();

        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $pagamento->setIstruttoriaRelazioneFinaleSaldo($istruttoria);
        }
        $dati_form_istruttoria = array('url_indietro' => $url_indietro);

        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $dati_form_istruttoria);
        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form_istruttoria->handleRequest($request);
            if ($form_istruttoria->isValid()) {
                try {
                    $em = $this->getEm();
                    $em->persist($pagamento);
                    $em->flush();
                    return $this->addSuccesRedirect("Istruttoria sulla relazione finale a saldo salvata correttamente", 'relazione_finale_istruttoria', array("id_pagamento" => $pagamento->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        /*         * * FINE ISTRUTTORIA ** */
        $dati = array(
            "pagamento" => $pagamento,
            "form" => null,
            'is_richiesta_disabilitata' => $pagamento->isRichiestaDisabilitata(),
            'form_istruttoria' => $form_istruttoria->createView(),
            'istruttoria' => true,
        );

        return $this->render("AttuazioneControlloBundle:Pagamenti:relazioneFinale.html.twig", $dati);
    }

    public function getRendicontazioneProceduraConfig($procedura) {

        $rendicontazioneProceduraConfig = $procedura->getRendicontazioneProceduraConfig();
        // fallback..default
        if (is_null($rendicontazioneProceduraConfig)) {
            $rendicontazioneProceduraConfig = new \AttuazioneControlloBundle\Entity\RendicontazioneProceduraConfig();
        }

        return $rendicontazioneProceduraConfig;
    }

    public function gestioneDocumentiProgetto($pagamento) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documentiPagamento = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoPagamento")->findDocumentiPagamento($pagamento->getId(), false);

        /*         * * ISTRUTTORIA ** */
        $istruttoria = $pagamento->getIstruttoriaDocumentiProgetto();
        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $pagamento->setIstruttoriaDocumentiProgetto($istruttoria);
        }

        $dati_form_istruttoria = array('url_indietro' => $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $dati_form_istruttoria['disabled'] = $this->isDisabled($pagamento);

        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $dati_form_istruttoria);

        if ($request->isMethod('POST')) {
            $form_istruttoria->handleRequest($request);
            if ($istruttoria->isIntegrazione() && (is_null($istruttoria->getNotaIntegrazione()) || $istruttoria->getNotaIntegrazione() == '')) {
                $form_istruttoria->get('nota_integrazione')->addError(new \Symfony\Component\Form\FormError('Il campo note è obbligatorio in caso di integrazione'));
            }

            if ($istruttoria->isCompleta()) {
                foreach ($documentiPagamento as $documentoPagamento) {
                    $istruttoriaDocumento = $documentoPagamento->getIstruttoriaOggettoPagamento();
                    if (is_null($istruttoriaDocumento) || !$istruttoriaDocumento->isCompleta()) {
                        $form_istruttoria->get('stato_valutazione')->addError(new \Symfony\Component\Form\FormError('Per essere etichettata come completa, devono prima essere state eseguite le istruttorie su tutti i documenti ed etichettate come complete'));
                        break;
                    }
                }
            }

            if ($form_istruttoria->isValid()) {
                try {
                    $em = $this->getEm();
                    $em->persist($pagamento);
                    $em->flush();
                    return $this->addSuccesRedirect("Istruttoria salvata correttamente", 'documenti_progetto_istruttoria', array("id_pagamento" => $pagamento->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        /*         * * FINE ISTRUTTORIA ** */

        $dati = array(
            "pagamento" => $pagamento,
            "menu" => "documenti",
            "form_istruttoria" => $form_istruttoria->createView(),
            'istruttoria' => true,
            "documentiProgetto" => $documentiPagamento,
            "is_disabled" => $this->isDisabled($pagamento)
        );

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Documenti progetto");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:documentiProgetto.html.twig", $dati);
    }

    public function getTipiDocumentiIstruttoriaPagamento($pagamento) {
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $procedura_id = $richiesta->getProcedura()->getId();
        $res = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findTipologieDocumentiIstruttoriaPagamento($pagamento->getId(), $procedura_id, true);
        return $res;
    }

    public function aggiungiDocumentoIstruttoriaPagamento($pagamento) {

        /* deve essere semre possibile caricare documenti a supporto
          Una volta validato il mandato di pagamento .. la pratica e l'istruttoria della liquidazione viene giustamente bloccata ,
          ma come era stato detto diverse volte, abbiamo spesso la necessità ( o per errore o per dimenticanza o per altro)
          di dover caricare altri documenti, anche successivi al mandato,  nella sezione "documenti di progetto"  nella :
          Documentazione caricata a supporto dell'istruttoria
          ma una volta inserito il mandato il pulsante "aggiungi documento istruttoria" sparisce ..
          SI PUO' MANTENERE IL PULSANTE DI CARICAMENTO SEMPRE ATTIVO ( ALMENO PER L'INSERIMENTO), BLOCCANDO TUTTO IL RESTO?
          oggi ci troviamo a dover caricare dei documenti per audit
         */
        /* if($this->isDisabled($pagamento)){
          return $this->addErrorRedirect("Azione non ammessa", "documenti_progetto_istruttoria", array("id_pagamento" => $pagamento->getId()));
          } */

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documentoIstruttoriaPagamento = new \AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaPagamento();

        // todo forse occurre gestire il disabled..vediamo più in là..blocco al mandato
        $options = array("disabled" => false);
        $options['documento_caricato'] = false;

        $listaTipi = $this->getTipiDocumentiIstruttoriaPagamento($pagamento);

        // todo capire se esiste una condizione per cui non deve essere possibile caricare documenti
        if (count($listaTipi) > 0) {

            $options["lista_tipi"] = $listaTipi;
            $options["url"] = $this->generateUrl('documenti_progetto_istruttoria', array("id_pagamento" => $pagamento->getId()));
            //$options["disabled"] = $this->isDisabled($pagamento);

            $form = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\DocumentoIstruttoriaPagamentoType', $documentoIstruttoriaPagamento, $options);

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    try {
                        $documentoFile = $documentoIstruttoriaPagamento->getDocumentoFile();
                        $this->container->get("documenti")->carica($documentoFile, 0, $pagamento->getRichiesta());

                        $pagamento->addDocumentoIstruttoria($documentoIstruttoriaPagamento);

                        $em->flush();

                        $this->addFlash('success', "Documento caricato correttamente");
                        return $this->redirectToRoute("documenti_progetto_istruttoria", array("id_pagamento" => $pagamento->getId()));
                    } catch (\Exception $e) {
                        $this->container->get("logger")->error($e->getMessage());
                        $this->addFlash('error', "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
                    }
                }
            }
        }

        $dati = array("form" => $form->createView());

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Documenti progetto", $this->generateUrl('documenti_progetto_istruttoria', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Aggiungi documento istruttoria");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:aggiungiDocumentoIstruttoria.html.twig", $dati);
    }

    public function gestioneDurc($pagamento) {

        $richiesta = $pagamento->getRichiesta();
        $dati = array();
        $dati["url_indietro"] = $this->generateUrl("riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));
        $dati["soggetto"] = $richiesta->getMandatario()->getSoggetto();
        $dati["proponenti"] = array();
        if (count($richiesta->getProponenti()) > 1) {
            foreach ($richiesta->getProponenti() as $proponente) {
                if ($proponente->getMandatario() == false) {
                    $dati["proponenti"][] = $proponente->getSoggetto();
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $dati["url_indietro"]);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Proponenti");

        return $this->render("AttuazioneControlloBundle:Istruttoria\Pagamenti:gestioneDurc.html.twig", $dati);
    }

    public function validaDatiDurc($pagamento) {

        $esito = new EsitoValidazione(true);
        return $esito;
    }

    public function gestioneAntimafiaPagamento($pagamento) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documentiAntimafia = $em->getRepository('AttuazioneControlloBundle\Entity\DocumentoPagamento')->findDocumentiAntimafia($pagamento->getId());

        /*         * * ISTRUTTORIA ** */
        $istruttoria = $pagamento->getIstruttoriaAntimafia();
        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $pagamento->setIstruttoriaAntimafia($istruttoria);
        }
        $dati_form_istruttoria = array('url_indietro' => $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $dati_form_istruttoria['disabled'] = $this->isDisabled($pagamento);

        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $dati_form_istruttoria);

        if ($request->isMethod('POST')) {
            $form_istruttoria->handleRequest($request);
            if ($istruttoria->isIntegrazione() && (is_null($istruttoria->getNotaIntegrazione()) || $istruttoria->getNotaIntegrazione() == '')) {
                $form_istruttoria->get('nota_integrazione')->addError(new \Symfony\Component\Form\FormError('Il campo note è obbligatorio in caso di integrazione'));
            }

            if ($form_istruttoria->isValid()) {
                try {
                    $em = $this->getEm();
                    $em->persist($istruttoria);
                    $em->flush();

                    return $this->addSuccesRedirect("Istruttoria salvata correttamente", 'gestione_antimafia_istruttoria', array("id_pagamento" => $pagamento->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Antimafia/casellario");

        $dati = array(
            "pagamento" => $pagamento,
            'documentiAntimafia' => $documentiAntimafia,
            "istruttoria" => true,
            "form_istruttoria" => $form_istruttoria->createView()
        );

        return $this->render("AttuazioneControlloBundle:Istruttoria\Pagamenti:gestioneAntimafia.html.twig", $dati);
    }

    public function validaAntimafiaCasellario($pagamento) {

        $esito = new EsitoValidazione(true);

        $rendicontazioneproceduraConfig = $this->getRendicontazioneProceduraConfig($pagamento->getProcedura());
        if ($rendicontazioneproceduraConfig->getSezioneAntimafia() && !$pagamento->getModalitaPagamento()->isAnticipo() && $pagamento->isAntimafiaRichiesta()) {

            $istruttoria = $pagamento->getIstruttoriaAntimafia();
            if (is_null($istruttoria) || ($istruttoria->isIncompleta())) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione("Istruttoria antimafia incompleta");
            }

            /**
             * mi accodo a quanto fatto per la gestione dell'icona blu anziche la verde in caso di integrazione
             * vedi views/Pagamenti/mostraValidazioneInLineAttuazione.html.twig
             */
            if (!is_null($istruttoria) && $istruttoria->isIntegrazione()) {
                $esito->setMessaggio('Integrazione');
            }
        }

        return $esito;
    }

    public function validaDocumentiProgetto($pagamento) {

        $esito = new EsitoValidazione(true);

        $istruttoria = $pagamento->getIstruttoriaDocumentiProgetto();
        if (is_null($istruttoria) || ($istruttoria->isIncompleta())) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Istruttoria documenti progetto incompleta");
        }

        /**
         * mi accodo a quanto fatto per la gestione dell'icona blu anziche la verde in caso di integrazione
         * vedi views/Pagamenti/mostraValidazioneInLineAttuazione.html.twig
         */
        if (!is_null($istruttoria) && $istruttoria->isIntegrazione()) {
            $esito->setMessaggio('Integrazione');
        }

        return $esito;
    }

    /**
     * @param Pagamento $pagamento
     */
    public function controllaValiditaIstruttoriaPagamento($pagamento, $opzioni = array()) {

        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($pagamento->getRichiesta()->getProcedura());

        $esitiSezioni = array();
        $esitiSezioni[] = $this->validaIstruttoriaDatibancari($pagamento);
        $esitiSezioni[] = $this->validaDatiDurc($pagamento);
        $esitiSezioni[] = $this->validaDocumentiProgetto($pagamento);

        // questo controllo va fatto solo per l'invio dell'esito
        if ($pagamento->getModalitaPagamento()->getRichiedeGiustificativi() && array_key_exists('controllo_per_esito', $opzioni)) {
            $esitiSezioni[] = $this->validaAvanzamentoPianoCosti($pagamento);
        }

        if ($rendicontazioneProceduraConfig->getSezioneAntimafia()) {
            $esitiSezioni[] = $this->validaAntimafiaCasellario($pagamento);
        }

        //TODO
        if ($rendicontazioneProceduraConfig->getSezioneAtti()) {
            //$esitiSezioni[] = $this->validaAtti($pagamento);
        }

        //TODO
        if ($rendicontazioneProceduraConfig->getSezioneContratti()) {
            //$esitiSezioni[] = $this->validaContratti($pagamento);
        }

        //TODO
        if ($rendicontazioneProceduraConfig->getSezionePersonale()) {
            //$esitiSezioni[] = $this->validaPersonale($pagamento);
        }

        // per l'invio dell'integrazione non serve aver istruito anche l'imputazione della spesa,
        // viene valutata solo nell'emissione dell'esito
        if ($pagamento->getModalitaPagamento()->getRichiedeGiustificativi() && array_key_exists('controllo_per_esito', $opzioni)) {
            $esitiSezioni[] = $this->validaGiustificativiIstruttoria($pagamento);
        }

        //Solo per progetti POR
        if ($pagamento->getRichiesta()->getFlagPor()) {
            $esitiSezioni[] = $this->validaIndicatoriOutput($pagamento);
        }


        $esito = true;
        $messaggi = array();
        $messaggiSezione = array();
        foreach ($esitiSezioni as $esitoSezione) {
            $esito &= $esitoSezione->getEsito();
            $msg = $esitoSezione->getMessaggi();
            $msgSezione = $esitoSezione->getMessaggiSezione();

            /**
             * questi if vanno messi perchè per gestire il triplo pallino colorato nelle sezioni
             * da qualche parte si chiama un setMessaggio(string) e si infogna tutto..genius
             */
            if (is_array($msg)) {
                $messaggi = array_merge_recursive($messaggi, $msg);
            }
            if (is_array($msgSezione)) {
                $messaggiSezione = array_merge_recursive($messaggi, $msgSezione);
            }
        }

        return new EsitoValidazione($esito, $messaggi, $messaggiSezione);
    }

    public function istruttoriaSingoloDocumentoPagamento($id_pagamento, $id_documento_pagamento) {

        $em = $this->getEm();
        $documento = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoPagamento")->find($id_documento_pagamento);
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        $istruttoria = $documento->getIstruttoriaOggettoPagamento();
        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $documento->setIstruttoriaOggettoPagamento($istruttoria);
        }

        $indietro = $this->generateUrl('documenti_progetto_istruttoria', array("id_pagamento" => $id_pagamento));

        $dati_form_istruttoria = array('url_indietro' => $indietro);
        $dati_form_istruttoria['disabled'] = $this->isDisabled($pagamento);

        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $dati_form_istruttoria);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form_istruttoria->handleRequest($request);
            if ($form_istruttoria->isValid()) {
                try {
                    $em->persist($documento);
                    $em->flush();
                    return $this->addSuccesRedirect("Istruttoria documento salvata correttamente", 'documenti_progetto_istruttoria', array("id_pagamento" => $id_pagamento));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $id_pagamento)));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Documenti progetto", $this->generateUrl("documenti_progetto_istruttoria", array("id_pagamento" => $id_pagamento)));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Istruttoria documento");

        $dati["form_istruttoria"] = $form_istruttoria->createView();
        $dati["titolo_sezione"] = "Documento " . $documento->getDocumentoFile()->getNomeOriginale();

        return $this->render("AttuazioneControlloBundle:Istruttoria:pannelloIstruttoriaGenerale.html.twig", $dati);
    }

    protected function recuperaDatiSezioniDaIntegrare($pagamento, $facsimile = false) {

        $datiIntegrazione = array(
            'dati_bancari' => null,
            'dati_durc' => null,
            'antimafia_casellario' => null,
            'incremento_occupazionale' => null,
            'documenti_progetto_generale' => null,
            'documenti_progetto_singoli' => array(),
            'giustificativi' => array(),
            'contratti' => null
        );

        $datiBancari = $pagamento->getIstruttoriaDatiBancari();
        if ($datiBancari && $datiBancari->isIntegrazione()) {
            $datiIntegrazione['dati_bancari'] = $datiBancari->getNotaIntegrazione();
        }

        $datiDurc = $pagamento->getDurc();
        $istruttoriaDurc = $datiDurc ? $datiDurc->getIstruttoriaOggettoPagamento() : null;
        if ($istruttoriaDurc && $istruttoriaDurc->isIntegrazione()) {
            $datiIntegrazione['dati_durc'] = $istruttoriaDurc->getNotaIntegrazione();
        }

        $antimafia = $pagamento->getIstruttoriaAntimafia();
        if ($antimafia && $antimafia->isIntegrazione()) {
            $datiIntegrazione['antimafia_casellario'] = $antimafia->getNotaIntegrazione();
        }

        $incrementoOccupazionale = $pagamento->getIstruttoriaIncrementoOccupazionale();
        if ($incrementoOccupazionale && $incrementoOccupazionale->isIntegrazione()) {
            $datiIntegrazione['incremento_occupazionale'] = $incrementoOccupazionale->getNotaIntegrazione();
        }

        $documenti = $pagamento->getIstruttoriaDocumentiProgetto();
        if ($documenti && $documenti->isIntegrazione()) {
            $datiIntegrazione['documenti_progetto_generale'] = $documenti->getNotaIntegrazione();
        }

        $em = $this->getEm();
        $documentiProgetto = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoPagamento")->findDocumentiPagamento($pagamento->getId(), false);
        foreach ($documentiProgetto as $documentoProgetto) {
            $istruttoriaDocumentoProgetto = $documentoProgetto->getIstruttoriaOggettoPagamento();
            if ($istruttoriaDocumentoProgetto && $istruttoriaDocumentoProgetto->isIntegrazione()) {
                $documentoFile = $documentoProgetto->getDocumentoFile();
                $fileName = $documentoFile->getNomeOriginale();
                $tipo = $documentoFile->getTipologiaDocumento()->getDescrizione();

                $datiIntegrazione['documenti_progetto_singoli'][] = array('filename' => $fileName, 'tipo' => $tipo, 'nota' => $istruttoriaDocumentoProgetto->getNotaIntegrazione());
            }
        }

        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($pagamento->getProcedura());
        $isRendicontazioneMultiProponente = $rendicontazioneProceduraConfig->getRendicontazioneMultiProponente();

        $giustificativi = $pagamento->getGiustificativi();
        foreach ($giustificativi as $giustificativo) {
            $istruttoria = $giustificativo->getIstruttoriaOggettoPagamento();
            if ($istruttoria && $istruttoria->isIntegrazione()) {
                $nota = $istruttoria->getNotaIntegrazione();
                $numero = !is_null($giustificativo->getNumeroGiustificativo()) ? $giustificativo->getNumeroGiustificativo() : '-';
                $data = !is_null($giustificativo->getDataGiustificativo()) ? date_format($giustificativo->getDataGiustificativo(), "d/m/Y") : '-';
                $fornitore = $giustificativo->getDenominazioneFornitore();

                $datiGiustificativo = array(
                    'numero' => $numero,
                    'data' => $data,
                    'fornitore' => $fornitore,
                    'nota' => $nota
                );

                if ($isRendicontazioneMultiProponente) {
                    $proponente = $giustificativo->getProponente();
                    $datiGiustificativo['proponente'] = !is_null($proponente) ? $proponente->getSoggetto()->getDenominazione() : '-';
                }

                $datiIntegrazione['giustificativi'][] = $datiGiustificativo;
            }
        }
        if ($rendicontazioneProceduraConfig->getSezioneContratti() == true) {
            $contratti = $pagamento->getContratti();
            foreach ($contratti as $contratto) {
                $istruttoriaContratto = $contratto->getIstruttoriaOggettoPagamento();
                if ($istruttoriaContratto && $istruttoriaContratto->isIntegrazione()) {
                    $numero = $contratto->getNumero();
                    $fornitore = $contratto->getFornitore();
                    $nota = $istruttoriaContratto->getNotaIntegrazione();
                    $datiIntegrazione['contratto_singoli'][] = array('numero' => $numero, 'fornitore' => $fornitore, 'nota' => $nota);
                }
                foreach ($contratto->getDocumentiContratto() as $documento) {
                    $istruttoria = $documento->getIstruttoriaOggettoPagamento();
                    if ($istruttoria && $istruttoria->isIntegrazione()) {
                        $documentoFile = $documento->getDocumentoFile();
                        $fileName = $documentoFile->getNomeOriginale();
                        $tipo = $documentoFile->getTipologiaDocumento()->getDescrizione();
                        $datiIntegrazione['documenti_contratto_singoli'][] = array('filename' => $fileName, 'tipo' => $tipo, 'nota' => $istruttoria->getNotaIntegrazione(), 'numero_contratto' => $contratto->getNumero());
                    }
                }
            }
        }

        return $datiIntegrazione;
    }

    public function modificaDocumentoIstruttoriaPagamento($pagamento, $documentoIstruttoriaPagamentoId) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        if ($this->isGranted("ROLE_OPERATORE_COGEA")) {
            $this->addFlash('error', "Azione non permessa per il ruolo.");
            return $this->addErrorRedirect("Azione non ammessa", "documenti_progetto_istruttoria", array("id_pagamento" => $pagamento->getId()));
        }

        $documentoIstruttoriaPagamento = $em->getRepository('AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaPagamento')->find($documentoIstruttoriaPagamentoId);

        // todo forse occurre gestire il disabled..vediamo più in là..blocco al mandato
        $options = array("disabled" => false);

        $listaTipi = $this->getTipiDocumentiIstruttoriaPagamento($pagamento);

        $documentoFile = $documentoIstruttoriaPagamento->getDocumentoFile();
        if (is_null($documentoFile)) {
            $options["documento_caricato"] = false;
            $path = null;
        } else {
            $options["documento_caricato"] = $documentoFile;
            $path = $this->container->get("funzioni_utili")->encid($documentoFile->getPath() . $documentoFile->getNome());
        }

        // todo capire se esiste una condizione per cui non deve essere possibile caricare documenti
        if (count($listaTipi) > 0) {

            $options["lista_tipi"] = $listaTipi;
            $options["url"] = $this->generateUrl('documenti_progetto_istruttoria', array("id_pagamento" => $pagamento->getId()));
            $options["disabled"] = $this->isDisabled($pagamento);

            $form = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\DocumentoIstruttoriaPagamentoType', $documentoIstruttoriaPagamento, $options);

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    try {

                        // sto volontariamente escludendo la possibilità di aggionrare il documento
                        // se vogliono cambiare il documento cancellano il documento istruttoria e lo ricreano

                        $em->flush();

                        $this->addFlash('success', "Documento caricato correttamente");
                        return $this->redirectToRoute("documenti_progetto_istruttoria", array("id_pagamento" => $pagamento->getId()));
                    } catch (\Exception $e) {
                        $this->container->get("logger")->error($e->getMessage());
                        $this->addFlash('error', "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
                    }
                }
            }
        }

        $dati = array("form" => $form->createView());
        $dati["documento_caricato"] = $options["documento_caricato"];
        $dati["path"] = $path;

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Documenti progetto", $this->generateUrl('documenti_progetto_istruttoria', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Modifica documento istruttoria");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:modificaDocumentoIstruttoria.html.twig", $dati);
    }

    /**
     * Si guarda se ci sono proroghe approvate
     * altrimenti si leggono da istruttoria richiesta (che sarebbero le date impostate nel passaggio in ATC)
     */
    private function getDateProgetto($pagamento) {

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
     * dobbiamo leggere questo valore dalle checklist
     * però: 
     * 1)se il progetto è campionato per il controllo in loco, il valore va letto nella checklist post controllo in loco
     * ma solo se siamo in uno step di pagamento finale (perchè i controlli si è assunto che vengono verbalizzati solo a SALDO)
     * altrimenti leggo da quella principale
     * 2) se il progetto non è campionato per i controlli in loco allora leggo direttamente il valore della checklist principale
     * 
     * @param Pagamento $pagamento
     */
    public function getValoreFromChecklist($pagamento, $codiceElementoChecklist) {

        $richiesta = $pagamento->getRichiesta();
        $isCampionatoPerControlloInLoco = $richiesta->hasControlliProgetto();
        $isPagamentoFinale = $pagamento->getModalitaPagamento()->isPagamentoFinale();

        $em = $this->getEm();
        $valutazioneChecklistRepository = $em->getRepository('AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento');
        $valutazioneElementoChecklistRepository = $em->getRepository('AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamento');

        if ($pagamento->isAnticipo()) {
            $tipologiaChecklist = \AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento::TIPOLOGIA_ANTICIPI;
        } else {
            $tipologiaChecklist = \AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento::TIPOLOGIA_PRINCIPALE;
        }
        if ($isCampionatoPerControlloInLoco && $isPagamentoFinale) {
            $tipologiaChecklist = \AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento::TIPOLOGIA_POST_CONTROLLO_LOCO;
        }

        $valutazioneChecklist = $valutazioneChecklistRepository->getValutazioneChecklistByPagamento($pagamento, $tipologiaChecklist);
        if (is_null($valutazioneChecklist) || !$valutazioneChecklist->isValidata()) {
            return null;
        }

        $elementoValutazioneChecklist = $valutazioneElementoChecklistRepository->getValutazioneElementoByCodice($valutazioneChecklist, $codiceElementoChecklist);

        if (is_null($elementoValutazioneChecklist)) {
            return null;
        } else {
            return $elementoValutazioneChecklist->getValore();
        }
    }

    public function getAmmissibilitaChecklist($pagamento) {
        $richiesta = $pagamento->getRichiesta();
        $isCampionatoPerControlloInLoco = $richiesta->hasControlliProgetto();
        $isPagamentoFinale = $pagamento->getModalitaPagamento()->isPagamentoFinale();

        $em = $this->getEm();
        $valutazioneChecklistRepository = $em->getRepository('AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento');
        //$valutazioneElementoChecklistRepository = $em->getRepository('AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamento');

        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            $tipologiaChecklist = \AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento::TIPOLOGIA_ANTICIPI;
        } else {
            $tipologiaChecklist = \AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento::TIPOLOGIA_PRINCIPALE;
            if ($isCampionatoPerControlloInLoco && $isPagamentoFinale) {
                $tipologiaChecklist = \AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento::TIPOLOGIA_POST_CONTROLLO_LOCO;
            }
        }

        $valutazioneChecklist = $valutazioneChecklistRepository->getValutazioneChecklistByPagamento($pagamento, $tipologiaChecklist);

        return is_null($valutazioneChecklist) ? false : $valutazioneChecklist->isAmmissibile();
    }

    public function validaAvanzamentoPianoCosti($pagamento) {

        $esito = new EsitoValidazione(true);

        $contributoComplessivoSpettante = $pagamento->getContributoComplessivoSpettante();
        if (is_null($contributoComplessivoSpettante)) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Contributo complessivo spettante non definito");
        }

        return $esito;
    }

    public function aggiungiChecklistAppalti($pagamento) {

        $em = $this->getEm();

        $checklistAppalti = null;
        $checklistPreviste = $this->getChecklistPreviste($pagamento);
        foreach ($checklistPreviste as $checklistPrevista) {
            if ($checklistPrevista->isTipologiaAppaltiPubblici()) {
                $checklistAppalti = $checklistPrevista;
                break;
            }
        }

        if (is_null($checklistAppalti)) {
            $this->addFlash('error', "Nessuna checklist appalti definita per il bando");
            return $this->redirectToRoute("checklist_generale", array("id_pagamento" => $pagamento->getId()));
        }

        if ($this->isDisabled($pagamento)) {
            $this->addFlash('error', "L'istruttoria è conclusa");
            return $this->redirectToRoute("checklist_generale", array("id_pagamento" => $pagamento->getId()));
        }

        $this->istanziaStrutturaChecklist($checklistAppalti, $pagamento);
        try {
            $em->flush();
            $this->addFlash('success', "Checklist creata con successo");
        } catch (\Exception $e) {
            $this->addFlash('error', "Si è verificato un errore");
        }

        return $this->redirectToRoute("checklist_generale", array("id_pagamento" => $pagamento->getId()));
    }

    public function eliminaValutazioneChecklist($valutazioneChecklist) {

        $em = $this->getEm();

        $pagamento = $valutazioneChecklist->getpagamento();

        if ($this->isDisabled($pagamento)) {
            $this->addFlash('error', "L'istruttoria è conclusa");
            return $this->redirectToRoute("checklist_generale", array("id_pagamento" => $pagamento->getId()));
        }

        // ad oggi questa gestione estesa delle checklist è permessa solo per quelle "appalti"
        $checklist = $valutazioneChecklist->getChecklist();
        if (!$checklist->isTipologiaAppaltiPubblici()) {
            $this->addFlash('error', "Si possono eliminare solo checklist appalti");
            return $this->redirectToRoute("checklist_generale", array("id_pagamento" => $pagamento->getId()));
        }

        if ($valutazioneChecklist->isValidata()) {
            $this->addFlash('error', "E' necessario invalidare la checklist prima di poterla eliminare");
            return $this->redirectToRoute("checklist_generale", array("id_pagamento" => $pagamento->getId()));
        }

        try {
            $em->remove($valutazioneChecklist);
            $em->flush();
            $this->addFlash('success', "Checklist eliminata con successo");
        } catch (\Exception $e) {
            $this->addFlash('error', "Si è verificato un errore");
        }

        return $this->redirectToRoute("checklist_generale", array("id_pagamento" => $pagamento->getId()));
    }

    public function aggiungiDocumentoChecklistPagamento($valutazioneChecklistPagamento) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $pagamento = $valutazioneChecklistPagamento->getPagamento();

        if ($this->isDisabled($pagamento) || $valutazioneChecklistPagamento->isValidata()) {
            $this->addFlash('error', "Azione non ammessa");
            return $this->redirectToRoute("valuta_checklist_istruttoria_pagamenti", array("id_valutazione_checklist" => $valutazioneChecklistPagamento->getId()));
        }

        $documentiChecklist = $valutazioneChecklistPagamento->getDocumentiChecklist();
        if (count($documentiChecklist) > 0) {
            $this->addFlash('error', "E' possibile caricare un solo documento");
            return $this->redirectToRoute("valuta_checklist_istruttoria_pagamenti", array("id_valutazione_checklist" => $valutazioneChecklistPagamento->getId()));
        }

        $documentoChecklistPagamento = new \AttuazioneControlloBundle\Entity\Istruttoria\DocumentoChecklistPagamento();
        $valutazioneChecklistPagamento->addDocumentoChecklist($documentoChecklistPagamento);

        // todo forse occurre gestire il disabled..vediamo più in là..blocco al mandato
        $options = array("disabled" => false);
        $options['documento_caricato'] = false;

        $tipoDocumento = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array('tipologia' => 'rendicontazione_documenti_checklist_standard', 'codice' => 'CHECKLIST_PROCEDURALE'));
        $lista_tipi = array($tipoDocumento);
        $options['lista_tipi'] = $lista_tipi;

        $documentoFile = new DocumentoFile();
        $documentoFile->setTipologiaDocumento($tipoDocumento);

        $documentoChecklistPagamento->setDocumentoFile($documentoFile);

        $options["url"] = $this->generateUrl('valuta_checklist_istruttoria_pagamenti', array("id_valutazione_checklist" => $valutazioneChecklistPagamento->getId()));
        $options["disabled"] = $this->isDisabled($pagamento);

        $form = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\DocumentoValutazioneChecklistPagamentoType', $documentoChecklistPagamento, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $documentoFile = $documentoChecklistPagamento->getDocumentoFile();

                    $this->container->get("documenti")->carica($documentoFile, 0, $pagamento->getRichiesta());

                    $em->flush();

                    $this->addFlash('success', "Documento caricato correttamente");
                    return $this->redirectToRoute("valuta_checklist_istruttoria_pagamenti", array("id_valutazione_checklist" => $valutazioneChecklistPagamento->getId()));
                } catch (\Exception $e) {
                    $this->container->get("logger")->error($e->getMessage());
                    $this->addFlash('error', "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
                }
            }
        }

        $dati = array("form" => $form->createView());

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco checklist", $this->generateUrl('checklist_generale', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Valuta checklist", $this->generateUrl('valuta_checklist_istruttoria_pagamenti', array("id_valutazione_checklist" => $valutazioneChecklistPagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Aggiungi documento checklist");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Checklist:aggiungiDocumentoChecklistPagamento.html.twig", $dati);
    }

    public function eliminaDocumentoChecklistPagamento($valutazioneChecklistPagamento, $id_documento_checklist) {

        $em = $this->getEm();

        $pagamento = $valutazioneChecklistPagamento->getPagamento();

        if ($this->isDisabled($pagamento) || $valutazioneChecklistPagamento->isValidata()) {
            $this->addFlash('error', "Azione non ammessa");
            return $this->redirectToRoute("valuta_checklist_istruttoria_pagamenti", array("id_valutazione_checklist" => $valutazioneChecklistPagamento->getId()));
        }

        $documentoChecklist = $em->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\DocumentoChecklistPagamento")->find($id_documento_checklist);
        try {
            if ($valutazioneChecklistPagamento->removeDocumentoChecklist($documentoChecklist)) {
                $em->remove($documentoChecklist);
                $em->flush();
                return $this->addSuccesRedirect("Documento eliminato correttamente", "valuta_checklist_istruttoria_pagamenti", array("id_valutazione_checklist" => $valutazioneChecklistPagamento->getId()));
            } else {
                return $this->addErrorRedirect("Documento non trovato o non collegato alla checklist", "valuta_checklist_istruttoria_pagamenti", array("id_valutazione_checklist" => $valutazioneChecklistPagamento->getId()));
            }
        } catch (\Exception $e) {
            $this->container->get("logger")->error($e->getMessage());
            $this->addFlash('error', "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
        }
    }

    public function validaIndicatoriOutput(Pagamento $pagamento): EsitoValidazione {
        if (!$pagamento->isUltimoPagamento()) {
            return new EsitoValidazione(true);
        }
        $richiesta = $pagamento->getRichiesta();

        /** @var \MonitoraggioBundle\Service\IGestoreIndicatoreOutput $indicatoriService  */
        $indicatoriService = $this->container->get('monitoraggio.indicatori_output')->getGestore($richiesta);
        $valido = $indicatoriService->isRendicontazioneIstruttoriaValida();

        $esito = new EsitoValidazione($valido);
        if (!$valido) {
            $esito->addMessaggioSezione('Validare i valori realizzati', 'Indicatori di output');
        }
        return $esito;
    }

    public function gestioneIndicatoriOutput(Pagamento $pagamento): Response {
        $richiesta = $pagamento->getRichiesta();
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get('validator');
        /** @var \MonitoraggioBundle\Service\IGestoreIndicatoreOutput $indicatoriService  */
        $indicatoriService = $this->container->get('monitoraggio.indicatori_output')->getGestore($richiesta);
        $indicatoriManuali = $indicatoriService->getIndicatoriManuali();
        $indicatori = $indicatoriManuali->map(function (IndicatoreOutput $indicatore) use ($validator) {
            $validationList = $validator->validate($indicatore, null, [
                'Default',
                'rendicontazione_istruttoria'
            ]);
            return [
            'indicatore' => $indicatore,
            'valido' => $validationList->count() == 0,
            ];
        });

        $dati = [
            'indicatori' => $indicatori,
            'pagamento' => $pagamento
        ];

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco indicatori output");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:gestioneIndicatoriOutput.html.twig", $dati);
    }

    public function gestioneIndicatoreOutput(Pagamento $pagamento, IndicatoreOutput $indicatore): Response {
        /** @var \AttuazioneControlloBundle\Entity\Istruttoria\EsitoIstruttoriaPagamento  $esitoIstruttoria */
        $esitoIstruttoria = $pagamento->getEsitiIstruttoriaPagamento()->first();
        $pagamentoNonValidato = $esitoIstruttoria === false || $esitoIstruttoria->getStato() == StatoEsitoIstruttoriaPagamento::ESITO_IP_INSERITA;
        $form = $this->createForm(IndicatoreOutputType::class, $indicatore, [
            'disabled' => $this->isDisabled($pagamento) && !$pagamentoNonValidato,
            'url_indietro' => $this->generateUrl('gestione_indicatori_output_istruttoria', [
                'id_pagamento' => $pagamento->getId()
            ])
        ]);
        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->getEm()->flush($indicatore);
                $this->addSuccess('Dati salvati correttamente');
            } catch (\Exception $e) {
                $this->addError('Errore durante il salvataggio dei dati');
            }
        }
        $dati = [
            'form' => $form->createView(),
            'pagamento' => $pagamento,
            'indicatore' => $indicatore,
        ];

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco indicatori output", $this->generateUrl('gestione_indicatori_output_istruttoria', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio indicatore output");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:indicatoreOutput.html.twig", $dati);
    }

    public function estrazionePagamentiConVoci($procedura) {
        throw new \Exception("Implementare funzionalità nella sottoclasse");
    }

    public function esportaPagamenti($procedura) {
        \ini_set('memory_limit', '512M');
        setlocale(LC_ALL, ['it_IT.UTF-8']);

        /** @var \BaseBundle\Service\SpreadsheetFactory $excelService */
        $excelService = $this->container->get('phpoffice.spreadsheet');
        $phpExcelObject = $excelService->getSpreadSheet();
        $phpExcelObject->getProperties()->setCreator("Sfinge 2014-2020")
            ->setLastModifiedBy("Sfinge 2014-2020")
            ->setTitle("Esportazione pagamenti")
            ->setSubject("")
            ->setDescription("")
            ->setKeywords("")
            ->setCategory("");

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();

        $nomiColonne = array(
            "N. Pagamento",
            "Tipo Pagamento",
            "Data Invio Pagamento",
            "Data Protocollazione Pagamento",
            "Numero Protocollo Pagamento",
            "Id. Progetto",
            "Numero Protocollo Progetto",
            "Ragione Sociale/Denominazione del soggetto",
            "Codice Fiscale",
            "Partita IVA",
            "Titolo Progetto",
            "CUP del progetto",
            "Spesa totale ammessa in concessione",
            "Contributo concesso in fase di concessione",
            "Importo Variato",
            "Spesa totale rendicontata",
            "Contributo richiesto",
            "Spesa ammessa in fase di liquidazione",
            "Contributo spettante in fase di liquidazione ( contributo da check list)",
            "IBAN",
            "Stato della pratica",
            "Protocollo Integrazione",
            "Data invio richiesta di integrazione ",
            "Data invio risposta integrazione beneficiario",
            "Protocollo risposta integrazione beneficiario",
            "Progetto campionato per il controllo in loco",
            "Data esito controllo in loco",
            "Numero atto di liquidazione ",
            "Data atto di liquidazione ",
            "Numero mandato di pagamento ",
            "Data mandato di pagamento ",
            "Istruttore assegnato",
            "Data validazione checklist",
            "Data validazione checklist controlli",
            "Protocollo esito istruttoria",
            "Data esito istruttoria"
        );

        $activeSheet->fromArray($nomiColonne);
        $riga = 1;
        $risultato = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->getPagamentiInviati($procedura->getId());
        /** @var Pagamento[] $risultato */
        foreach ($risultato as $pagamento) {
            $soggetto = $pagamento->getSoggetto();
            $richiesta = $pagamento->getRichiesta();
            $istruttoriaRichiesta = $richiesta->getIstruttoria();
            /** @var IntegrazionePagamento|bool $ultimaIntegrazione */
            $ultimaIntegrazione = $pagamento->getIntegrazioni()->last(); //FALSE se non esiste
            $rispostaUltimaIntegrazione = $ultimaIntegrazione ? $ultimaIntegrazione->getRisposta() : null;
            $ultimoControlloLoco = $richiesta->getControlli()->last(); //FALSE se non esiste
            $mandatoPagamento = $pagamento->getMandatoPagamento();
            $chkP = $pagamento->valutazioneChkPagemento('PRINCIPALE');
            $chkCL = $pagamento->valutazioneChkPagemento('POST_CONTROLLO_LOCO');

            $istruttore = null;
            foreach ($pagamento->getAssegnamentiIstruttoria() as $i) {
                if ($i->getAttivo()) {
                    $istruttore = $i;
                    break;
                }
            }

            $ultimaVariazioneApprovata = $richiesta->getAttuazioneControllo()->getUltimaVariazioneApprovata();

            $esiti = $pagamento->getEsitiIstruttoriaPagamento();
            $esito = !is_null($esiti) && $esiti->last() ? $esiti->last() : null;
            $richieste_protocollo = !is_null($esito) ? $esito->getRichiesteProtocollo() : null;
            $richiesta_protocollo = !is_null($richieste_protocollo) && $richieste_protocollo->last() ? $richieste_protocollo->last() : null;
            $data_protocollo_esito_tmp = !is_null($richiesta_protocollo) ? $richiesta_protocollo->getDataPg() : null;
            $data_protocollo_esito = !is_null($data_protocollo_esito_tmp) ? $data_protocollo_esito_tmp->format('d/m/Y') : '-';
            $numero_protocollo_esito = !is_null($richiesta_protocollo) ? $richiesta_protocollo->getProtocollo() : '-';

            if ($pagamento->isAnticipo()) {
                $erogato = $this->getValoreFromChecklist($pagamento, 'ANTICIPO_EROGATO');
            } else {
                $erogato = $this->getValoreFromChecklist($pagamento, 'CONTRIBUTO_EROGABILE');
            }

            $iban = '';
            if ($pagamento->getRichiesta()->getMandatario()) {
                $iban = !\is_null($pagamento->getRichiesta()->getMandatario()->getDatiBancari()) ? $pagamento->getRichiesta()->getMandatario()->getDatiBancari()->first()->getIban() : '';
            }

            $valori = array(
                $pagamento->getId(), //N. Pagamento
                $pagamento->getModalitaPagamento(), //Tipo Pagamento
                Date::dateTimeToExcel($pagamento->getDataInvio()), //Data Invio Pagamento
                $pagamento->getDataProtocollo(), //Data Protocollazione Pagamento @TODO Da verificare che prenda l'ultimo
                $pagamento->getProtocollo(), //Numero Protocollo Pagamento
                $richiesta->getId(), //ID Richiesta
                is_null($richiesta->getProtocollo()) ? '-' : $richiesta->getProtocollo(), //Numero protocollo Richiesta
                $soggetto->getDenominazione(), //Ragione Sociale/Denominazione del soggetto
                $soggetto->getCodiceFiscale(), //Codice Fiscale
                $soggetto->getPartitaIva(), //Partita IVA
                $richiesta->getTitolo(), //Titolo Progetto
                !\is_null($istruttoriaRichiesta) ? $istruttoriaRichiesta->getCodiceCup() : "-", //CUP del progetto
                \floatval($richiesta->getCostoAmmesso()), //Spesa totale ammessa in concessione
                !\is_null($istruttoriaRichiesta) ? \floatval($istruttoriaRichiesta->getContributoAmmesso()) : "", //Contributo concesso in fase di concessione
                \is_null($ultimaVariazioneApprovata) ? '' : \floatval($ultimaVariazioneApprovata->getContributoAmmesso()), // Importo variato
                !\is_null($pagamento->getImportoRendicontato()) ? \floatval($pagamento->getImportoRendicontato()) : \floatval($pagamento->getRendicontato()), //Spesa totale rendicontata
                \floatval($pagamento->getImportoRichiesto()), //Contributo richiesto in fase di concessione
                \floatval($this->getValoreFromChecklist($pagamento, 'SPESE_AMMESSE')), //Spesa ammessa in fase di liquidazione
                \floatval($erogato), //Spesa erogata in fase di liquidazione
                $iban, //IBAN
                $pagamento->getDescrizioneEsito(), //Stato della pratica
                $ultimaIntegrazione ? $ultimaIntegrazione->getProtocolloIntegrazione() : "-", //Protocollo integrazione
                $ultimaIntegrazione && $ultimaIntegrazione->getDataProtocolloIntegrazione() ? $ultimaIntegrazione->getDataProtocolloIntegrazione()->format('d/m/Y') : '', //Data invio richiesta integrazione
                !is_null($rispostaUltimaIntegrazione) ? (!is_null($rispostaUltimaIntegrazione->getDataProtocolloRispostaIntegrazione()) ? $rispostaUltimaIntegrazione->getDataProtocolloRispostaIntegrazione()->format('d/m/Y') : "-") : "-", //Data risposta integrazione
                !\is_null($rispostaUltimaIntegrazione) ? $rispostaUltimaIntegrazione->getProtocolloRispostaIntegrazione() : "-", //Protocollo risposta integrazione
                $ultimoControlloLoco ? "SI" : "NO", //Progetto campionato per il controllo in loco
                $ultimoControlloLoco ? ($ultimoControlloLoco->getDataValidazione() ? $ultimoControlloLoco->getDataValidazione()->format('d/m/Y') : "-" ) : "-", //Data esito controlli in loco
                $mandatoPagamento ? ( $mandatoPagamento->getAttoLiquidazione() ? $mandatoPagamento->getAttoLiquidazione()->getNumero() : "-" ) : "-", //Numero atto di liquidazione
                $mandatoPagamento ? ( $mandatoPagamento->getAttoLiquidazione() ? $mandatoPagamento->getAttoLiquidazione()->getData()->format('d/m/Y') : "-" ) : "-", //Data atto di liquidazione
                $mandatoPagamento ? $mandatoPagamento->getNumeroMandato() : "-", //Numero mandato di pagamento
                $mandatoPagamento ? $mandatoPagamento->getDataMandato()->format('d/m/Y') : "-", //Data mandato di pagamento
                \is_null($istruttore) ? 'non assegnato' : $istruttore->getIstruttore()->getPersona(), //Istruttore assegnato
                \is_null($chkP) ? "-" : \is_null($chkP->getDataValidazione()) ? '-' : Date::dateTimeToExcel($chkP->getDataValidazione()),
                \is_null($chkCL) ? "-" : \is_null($chkCL->getDataValidazione()) ? '-' : Date::dateTimeToExcel($chkCL->getDataValidazione()),
                $numero_protocollo_esito,
                $data_protocollo_esito
            );

            $riga++;
            $activeSheet->fromArray($valori, null, "A$riga");
        }

        if ($riga > 1) {
            $activeSheet->getStyle("C2:D$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            $activeSheet->getStyle("E2:J$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $activeSheet->getStyle("K2:Q$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            $activeSheet->getStyle("T2:V$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            $activeSheet->getStyle("AD2:AE$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
        }

        $response = $excelService->createResponse($phpExcelObject, 'Esportazione_Pagamenti_' . $procedura->getId() . '.xlsx');
        return $response;
    }

    protected function setFormatoCelle($sheet, $riga, $colonne, $formato) {
        foreach (\str_split($colonne) as $colonna) {
            $sheet->getStyle((\strtoupper($colonna)) . $riga)
                ->getNumberFormat()
                ->setFormatCode($formato);
        }
    }

    protected function aggiornaFinanzimento(Pagamento $pagamento) {
        if ($pagamento->isUltimoPagamento()) {
            return;
        }
        $richiesta = $pagamento->getRichiesta();
        $totFinanziamento = $richiesta->getTotaleFinanziamento();
    }

    /**
     * @param Pagamento $pagamento
     * @return GestoreResponse
     */
    public function riepilogoComunicazionePagamento(Pagamento $pagamento) {
        $indietro = $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId()));

        $dati = array();
        $dati["menu"] = "comunicazioni_pagamento";
        $dati["pagamento"] = $pagamento;
        $dati["indietro"] = $indietro;

        $twig = "AttuazioneControlloBundle:ComunicazionePagamento:riepilogoComunicazionePagamento.html.twig";

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Comunicazioni");

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    /**
     * @param Pagamento $pagamento
     * @param array $opzioni
     * @return GestoreResponse
     * @throws \Exception
     */
    public function creaComunicazionePagamento(Pagamento $pagamento, $opzioni = array()) {
        $em = $this->getEm();
        $indietro = $this->generateUrl('comunicazioni_pagamento', array("id" => $pagamento->getId()));
        if ($this->isGranted("ROLE_PAGAMENTI_READONLY") || $this->isGranted("ROLE_OPERATORE_COGEA")) {
            $this->addFlash('error', "Azione non permessa per il ruolo.");
            return new GestoreResponse($this->redirect($indietro));
        }

        $comunicazione_pagamento = new ComunicazionePagamento();
        $comunicazione_pagamento->setPagamento($pagamento);
        $comunicazione_pagamento->setTestoEmail('');
        $comunicazione_pagamento->setData(new \DateTime());
        $risposta = new \AttuazioneControlloBundle\Entity\Istruttoria\RispostaComunicazionePagamento();
        $risposta->setComunicazione($comunicazione_pagamento);
        $this->container->get("sfinge.stati")->avanzaStato($comunicazione_pagamento, \BaseBundle\Entity\StatoComunicazionePagamento::COM_PAG_INSERITA);
        $this->container->get("sfinge.stati")->avanzaStato($risposta, \BaseBundle\Entity\StatoComunicazionePagamento::COM_PAG_INSERITA);

        try {
            $em->persist($comunicazione_pagamento);
            $em->flush();
        } catch (\Exception $e) {
            $this->addFlash('error', "Errore nel salvataggio delle informazioni.");
        }


        // Faccio il redirect a gestione_comunicazione_pagamento per non scrivere anche qua
        // la logica di salvataggio di dati in POST ed invio comunicazione.
        // In questo modo gestisco questi due aspetti solamente nel gestione_comunicazione_pagamento.
        $gestione_comunicazione_pagamento = $this->generateUrl('gestione_comunicazione_pagamento',
            array("id" => $comunicazione_pagamento->getId()));
        return new GestoreResponse($this->redirect($gestione_comunicazione_pagamento));
    }

    /**
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cancellaComunicazionePagamento(ComunicazionePagamento $comunicazionePagamento) {
        $id_pagamento = $comunicazionePagamento->getPagamento()->getId();

        if ($comunicazionePagamento->getStato() != \BaseBundle\Entity\StatoComunicazionePagamento::COM_PAG_INSERITA) {
            return $this->addErrorRedirect('Lo stato della comuncazione non ne permette la cancellazione.',
                    'comunicazioni_pagamento', array('id' => $id_pagamento));
        }

        $em = $this->getEm();

        try {
            $em->beginTransaction();
            $em->remove($comunicazionePagamento);
            $em->remove($comunicazionePagamento->getRisposta());
            $em->flush();
            $em->commit();
        } catch (\Exception $ex) {
            $em->rollback();
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.",
                    'comunicazioni_pagamento', array('id' => $id_pagamento));
        }

        return $this->addSuccesRedirect("Comuncazione pagamento eliminata con successo.", 'comunicazioni_pagamento',
                array("id" => $id_pagamento));
    }

    /**
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return GestoreResponse
     * @throws SfingeException
     */
    public function gestioneComunicazionePagamento(ComunicazionePagamento $comunicazionePagamento) {
        /** @var $pagamento Pagamento */
        $pagamento = $comunicazionePagamento->getPagamento();
        if ($this->isGranted("ROLE_PAGAMENTI_READONLY") || $this->isGranted("ROLE_OPERATORE_COGEA")) {
            $disabilita = true;
        } else {
            $disabilita = false;
        }

        $request = $this->getCurrentRequest();
        $em = $this->getEm();

        $indietro = $this->generateUrl('comunicazioni_pagamento', array("id" => $pagamento->getId()));
        $form_options = array(
            "url_indietro" => $indietro,
            'disabled' => (($comunicazionePagamento->getStato() == 'COM_PAG_PROTOCOLLATA') || ($comunicazionePagamento->getStato() == 'COM_PAG_INVIATA_PA') || $disabilita == true)
        );

        $form = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\ComunicazionePagamentoType",
            $comunicazionePagamento, $form_options);

        // Form allegati
        $tipologiaAllegato = $em->getRepository("DocumentoBundle:TipologiaDocumento")->findOneBy([
            'codice' => TipologiaDocumento::COMUNICAZIONE_PAGAMENTO_ALLEGATO
        ]);

        if (\is_null($tipologiaAllegato)) {
            throw new SfingeException("Tipologia documento non trovata.");
        }

        $documento = new DocumentoFile($tipologiaAllegato);
        $allegato = new AllegatoComunicazionePagamento($comunicazionePagamento, $documento);

        $formAllegati = $this->createForm(AllegatoComunicazionePagamentoType::class, $allegato, ['disabled' => $disabilita]);
        if (!$form_options['disabled']) {
            $formAllegati->add('submit', SubmitType::class, ['label' => 'Carica',])->handleRequest($request);
        }

        if ($formAllegati->isSubmitted() && $formAllegati->isValid()) {
            $comunicazionePagamento->addAllegati($allegato);
            try {
                /** @var DocumentiService $docService */
                $docService = $this->container->get('documenti');
                $file = $allegato->getDocumento();
                $docService->carica($file);
                $em->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', "Errore nel salvataggio delle informazioni.");
            }
        }
        // Fine form allegati

        $request = $this->getCurrentRequest();

        $em = $this->getEm();
        $em->persist($comunicazionePagamento);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $em->beginTransaction();

                    $documentoIntegrazione = $this->creaAllegatoComunicazionePagamento($comunicazionePagamento);
                    $comunicazionePagamento->setDocumento($documentoIntegrazione);

                    $em->flush();

                    if ($form->get("pulsanti")->get("pulsante_invio")->isClicked()) {
                        $esitoPag = $this->controllaValiditaIstruttoriaPagamento($pagamento);
                        if ($esitoPag->getEsito() == false) {
                            return new GestoreResponse($this->addErrorRedirect("Non è possibile inviare una comunicazione se non sono state istruite tutte le sezioni",
                                    "gestione_comunicazione_pagamento", ["id" => $comunicazionePagamento->getId()]));
                        }

                        $this->container->get("sfinge.stati")->avanzaStato($comunicazionePagamento, \BaseBundle\Entity\StatoComunicazionePagamento::COM_PAG_INVIATA_PA);

                        $em->flush();

                        if ($this->container->getParameter("stacca_protocollo_al_volo")) {
                            $richiestaProtocollo = $this->container->get("docerinitprotocollazione")->setTabProtocollazioneComunicazionePagamento($comunicazionePagamento);
                            /**
                             * Schedulo un invio email per protocollazione in uscita tramite egrammata
                             * l'email verrà mandata solo dopo che lo stato della richiestaProtocollo evolverà in POST_PROTOCOLLAZIONE
                             * Questo blocco andrà riportato negli eventuali GestoriIstruttoriaBando scritti ad hoc che faranno
                             * l'overwrite del metodo creaIntegrazione
                             */
                            /*                             * ********************************************************************** * */
                            if (!$this->schedulaEmailProtocollo($richiestaProtocollo)) {
                                throw new \Exception('Errore durante la schedulazione dell\'EmailProtocollo');
                            }
                            /*                             * ********************************************************************** * */
                        }
                        $em->commit();
                        $this->addFlash('success', "Comunicazione inviata con successo");
                    } else {
                        $em->commit();
                        $this->addFlash('success', "Comunicazione salvata con successo");
                    }
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni.");
                }

                return new GestoreResponse($this->redirect($indietro));
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();
        $dati["form_allegati"] = $formAllegati->createView();
        $dati["comunicazionePagamento"] = $comunicazionePagamento;

        $twig = "AttuazioneControlloBundle:ComunicazionePagamento:creaComunicazionePagamento.html.twig";

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Comunicazioni", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Gestione comunicazione");

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    /**
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return DocumentoFile
     * @throws \Exception
     */
    protected function creaAllegatoComunicazionePagamento(ComunicazionePagamento $comunicazionePagamento) {
        $tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findOneBy(['codice' => TipologiaDocumento::COMUNICAZIONE_PAGAMENTO]);
        $documentoComunicazione = $this->container->get("documenti")->caricaDaByteArray($this->pdfComunicazionePagamento($comunicazionePagamento, false, false), $this->getNomePdfComunicazionePagamento($comunicazionePagamento) . ".pdf", $tipoDocumento, false);
        return $documentoComunicazione;
    }

    /**
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return string
     * @throws \Exception
     */
    protected function getNomePdfComunicazionePagamento(ComunicazionePagamento $comunicazionePagamento) {
        $pagamento = $comunicazionePagamento->getPagamento();
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $date = new \DateTime();
        $data = $date->format('d-m-Y');

        $tipologiaComunicazione = strtolower($comunicazionePagamento->getTipologiaComunicazione()->getDescrizione());
        return 'Comunicazione di ' . $tipologiaComunicazione . ' ' . $richiesta->getMandatario() . ' Pagamento ' . $pagamento->getId() . ' ' . $data;
    }

    /**
     * @param ComunicazionePagamento $comunicazionePagamento
     * @param bool $download
     * @param bool $facsimile
     * @return string|Response
     * @throws \Exception
     */
    public function pdfComunicazionePagamento(ComunicazionePagamento $comunicazionePagamento, $download, $facsimile) {
        $twig = "@AttuazioneControllo/Pdf/Istruttoria/comunicazione_pagamento.html.twig";
        $datiPdf = $this->datiPdf($comunicazionePagamento->getPagamento(), $facsimile);
        $datiPdf['dati']['testo'] = $comunicazionePagamento->getTesto();
        $datiPdf['dati']['tipologia_comunicazione'] = $comunicazionePagamento->getTipologiaComunicazione()->getDescrizione();

        $pdf = $this->container->get("pdf");
        $pdf->load($twig, $datiPdf);

        if ($download) {
            $nomeFile = $this->getNomePdfComunicazionePagamento($comunicazionePagamento);
            $pdf->download($nomeFile);
            return new Response();
        } else {
            return $pdf->binaryData();
        }
    }

    /**
     * @param AllegatoComunicazionePagamento $allegato
     */
    public function eliminaAllegatoComunicazionePagamento(AllegatoComunicazionePagamento $allegato): void {
        $em = $this->getEm();
        $allegato->getComunicazionePagamento()->removeAllegati($allegato);
        $em->remove($allegato);
        $em->flush();
    }

    /**
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return Response
     */
    public function istruttoriaComunicazionePagamento(ComunicazionePagamento $comunicazionePagamento) {
        $pagamento = $comunicazionePagamento->getPagamento();
        $indietro = $this->generateUrl('comunicazioni_pagamento', array("id" => $pagamento->getId()));
        $istruttoria = $comunicazionePagamento->getIstruttoriaOggettoPagamento();

        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $comunicazionePagamento->setIstruttoriaOggettoPagamento($istruttoria);
        }

        $datiFormIstruttoria = array('url_indietro' => $indietro, 'nascondi_integrazione' => true);
        $formIstruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $datiFormIstruttoria);
        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $formIstruttoria->handleRequest($request);
            if ($formIstruttoria->isValid()) {
                try {
                    $em = $this->getEm();
                    $em->persist($comunicazionePagamento);
                    $em->flush();
                    return $this->addSuccesRedirect("Istruttoria comunicazione pagamento salvata correttamente.",
                            'istruttoria_comunicazione_pagamento', array("id" => $comunicazionePagamento->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Comunicazione pagamento", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Istruttoria comunicazione pagamento");

        $dati = array();
        $dati["pagamento"] = $pagamento;
        $dati["comunicazionePagamento"] = $comunicazionePagamento;
        $dati["indietro"] = $indietro;
        $dati["form_istruttoria"] = $formIstruttoria->createView();

        return $this->render("AttuazioneControlloBundle:ComunicazionePagamento:istruttoriaComunicazionePagamento.html.twig", $dati);
    }

    /**
     * @param DocumentoRispostaComunicazionePagamento $documentoRispostaComunicazionePagamento
     * @return Response
     */
    public function istruttoriaDocumentoComunicazionePagamento(DocumentoRispostaComunicazionePagamento $documentoRispostaComunicazionePagamento) {
        $em = $this->getEm();

        $comunicazionePagamento = $documentoRispostaComunicazionePagamento->getRispostaComunicazionePagamento()->getComunicazione();
        $pagamento = $comunicazionePagamento->getPagamento();

        $istruttoria = $documentoRispostaComunicazionePagamento->getIstruttoriaOggettoPagamento();
        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $documentoRispostaComunicazionePagamento->setIstruttoriaOggettoPagamento($istruttoria);
        }

        $indietro = $this->generateUrl('istruttoria_comunicazione_pagamento', array("id" => $comunicazionePagamento->getId()));
        $datiFormIstruttoria = array('url_indietro' => $indietro, 'nascondi_integrazione' => true);
        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $datiFormIstruttoria);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form_istruttoria->handleRequest($request);
            if ($form_istruttoria->isValid()) {
                try {
                    $em->persist($documentoRispostaComunicazionePagamento);
                    $em->flush();
                    return $this->addSuccesRedirect("Istruttoria documento salvata correttamente.", 'istruttoria_comunicazione_pagamento', array("id" => $comunicazionePagamento->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Comunicazione pagamento", $this->generateUrl('comunicazioni_pagamento', array("id" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Istruttoria comunicazione pagamento", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Istruttoria documento");

        $dati["form_istruttoria"] = $form_istruttoria->createView();
        $dati["titolo_sezione"] = "Documento " . $documentoRispostaComunicazionePagamento->getDocumentoFile()->getNomeOriginale();

        return $this->render("AttuazioneControlloBundle:Istruttoria:pannelloIstruttoriaGenerale.html.twig", $dati);
    }

    public function esportaPagamentiMandato() {
        \ini_set('memory_limit', '512M');
        setlocale(LC_ALL, ['it_IT.UTF-8']);

        /** @var \BaseBundle\Service\SpreadsheetFactory $excelService */
        $excelService = $this->container->get('phpoffice.spreadsheet');
        $phpExcelObject = $excelService->getSpreadSheet();
        $phpExcelObject->getProperties()->setCreator("Sfinge 2014-2020")
            ->setLastModifiedBy("Sfinge 2014-2020")
            ->setTitle("Esportazione pagamenti")
            ->setSubject("")
            ->setDescription("")
            ->setKeywords("")
            ->setCategory("");

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();

        $nomiColonne = array(
            "Asse",
            "Bando",
            "Protocollo richiesta",
            "Beneficiario",
            "Importo erogato/liquidato",
            "Data mandato pagamento"
        );

        $activeSheet->fromArray($nomiColonne);
        $riga = 1;
        $risultato = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->getPagamentiConMandato();
        /** @var Pagamento[] $risultato */
        foreach ($risultato as $pagamento) {
            $valori = array(
                $pagamento['asse_pag'],
                $pagamento['proc_pag'],
                $pagamento['protocollo'],
                $pagamento['ben_pag'],
                $pagamento['mand_pag'],
                $pagamento['datamand_pag']
            );

            $riga++;
            $activeSheet->fromArray($valori, null, "A$riga");
        }

        if ($riga > 1) {
            $activeSheet->getStyle("A2:D$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $activeSheet->getStyle("F2:F$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            $activeSheet->getStyle("E2:E$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }

        $response = $excelService->createResponse($phpExcelObject, 'Esportazione_Pagamenti.xlsx');
        return $response;
    }

    public function isChiarimentoGestibile(Pagamento $pagamento) {
        $integrazioni = $pagamento->getIntegrazioni();
        if ($integrazioni->count() == 0) {
            return false;
        } else {
            $ultimaIntegrazione = $integrazioni->last();
            $statoIntegrazione = $ultimaIntegrazione->getStato()->getCodice();
            if ($statoIntegrazione == 'INT_INSERITA') {
                return false;
            }
            if ($statoIntegrazione == 'INT_PROTOCOLLATA' && $ultimaIntegrazione->getRisposta()) {
                $risposta = $ultimaIntegrazione->getRisposta();
                if ($risposta->getStato()->getCodice() == 'INT_PROTOCOLLATA') {
                    return true;
                } else {
                    return $ultimaIntegrazione->isScaduta();
                }
            } else {
                if ($statoIntegrazione == 'INT_PROTOCOLLATA' && !$ultimaIntegrazione->getRisposta()) {
                    return $ultimaIntegrazione->isScaduta();
                }
            }
        }
    }

    public function getChecklistPreviste($pagamento) {
        return $pagamento->getProcedura()->getChecklistPagamento();
    }

    public function verificaTuttiIncrementiOccupazionali($pagamento) {
        $richiesta = $pagamento->getRichiesta();
        $incrementoDaOggetto = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->hasIncrementoDaOggetto($richiesta);
        $incrementoDaFascicolo = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->hasIncrementoDaFascicolo($richiesta->getMandatario());
        $incrementoDaIncrementoOccupazione = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->hasIncrementoDaOccupazioneProponente($richiesta->getMandatario());
        $incrementoDaRisorse = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->hasIncrementoDaRisorse($richiesta);

        return ($incrementoDaOggetto == true || $incrementoDaFascicolo == true || $incrementoDaIncrementoOccupazione == true || $incrementoDaRisorse == true);
    }

    /**
     * @param Pagamento $pagamento
     * @return RedirectResponse
     */
    public function riapriPagamento(Pagamento $pagamento): RedirectResponse {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            return $this->addErrorRedirect("Non sei abilitato ad eseguire l’operazione.", "riepilogo_istruttoria_pagamento", ["id_pagamento" => $pagamento->getId()]);
        } elseif (!$pagamento->isRiapribile()) {
            return $this->addErrorRedirect("Il pagamento non è riapribile.", "riepilogo_istruttoria_pagamento", ["id_pagamento" => $pagamento->getId()]);
        }

        $em = $this->getEm();
        try {
            $em->beginTransaction();

            $statoPagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\StatoPagamento")->findOneByCodice(StatoPagamento::PAG_INSERITO);
            $pagamento->setStato($statoPagamento);
            foreach ($pagamento->getRichiesteProtocollo() as $richiestaProtocollo) {
                $pagamento->removeRichiesteProtocollo($richiestaProtocollo);
                $em->remove($richiestaProtocollo);
            }
            $em->persist($pagamento);
            $em->flush();
            $em->commit();
        } catch (Exception $e) {
            $em->rollback();
            return $this->addErrorRedirect("Errore nella riapertura del pagamento. Si prega di riprovare o contattare l’assistenza.", "riepilogo_istruttoria_pagamento", ["id_pagamento" => $pagamento->getId()]);
        }
        return $this->addSuccesRedirect("Pagamento riaperto con successo.", "elenco_istruttoria_pagamenti");
    }
    
    
    public function esportaPagamentiGlobali() {
        \ini_set('memory_limit', '2G');
        setlocale(LC_ALL, ['it_IT.UTF-8']);

        /** @var \BaseBundle\Service\SpreadsheetFactory $excelService */
        $excelService = $this->container->get('phpoffice.spreadsheet');
        $phpExcelObject = $excelService->getSpreadSheet();
        $phpExcelObject->getProperties()->setCreator("Sfinge 2014-2020")
            ->setLastModifiedBy("Sfinge 2014-2020")
            ->setTitle("Esportazione MASSIVA pagamenti")
            ->setSubject("")
            ->setDescription("")
            ->setKeywords("")
            ->setCategory("");

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();

        $nomiColonne = array(
            "Asse",
            "Procedura",
            "Numero Atto DGR",
            "N. Pagamento",
            "Tipo Pagamento",
            "Data Invio Pagamento",
            "Data Protocollazione Pagamento",
            "Numero Protocollo Pagamento",
            "Id. Progetto",
            "Numero Protocollo Progetto",
            "Ragione Sociale/Denominazione del soggetto",
            "Codice Fiscale",
            "Partita IVA",
            "Titolo Progetto",
            "CUP del progetto",
            "Spesa totale ammessa in concessione",
            "Contributo concesso in fase di concessione",
            "Importo Variato",
            "Spesa totale rendicontata",
            "Contributo richiesto",
            "Spesa ammessa in fase di liquidazione",
            "Contributo spettante in fase di liquidazione ( contributo da check list)",
            "IBAN",
            "Stato della pratica",
            "Protocollo Integrazione",
            "Data invio richiesta di integrazione ",
            "Data invio risposta integrazione beneficiario",
            "Protocollo risposta integrazione beneficiario",
            "Progetto campionato per il controllo in loco",
            "Data esito controllo in loco",
            "Numero atto di liquidazione ",
            "Data atto di liquidazione ",
            "Numero mandato di pagamento ",
            "Data mandato di pagamento ",
            "Istruttore assegnato",
            "Data validazione checklist",
            "Data validazione checklist controlli",
            "Protocollo esito istruttoria",
            "Data esito istruttoria"
        );

        $activeSheet->fromArray($nomiColonne);
        $riga = 1;
        $risultato = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->getPagamentiInviatiGlobali();
        /** @var Pagamento[] $risultato */
        foreach ($risultato as $pagamento) {
            $soggetto = $pagamento->getSoggetto();
            $richiesta = $pagamento->getRichiesta();
            $procedura = $pagamento->getProcedura();
            $istruttoriaRichiesta = $richiesta->getIstruttoria();
            /** @var IntegrazionePagamento|bool $ultimaIntegrazione */
            $ultimaIntegrazione = $pagamento->getIntegrazioni()->last(); //FALSE se non esiste
            $rispostaUltimaIntegrazione = $ultimaIntegrazione ? $ultimaIntegrazione->getRisposta() : null;
            $ultimoControlloLoco = $richiesta->getControlli()->last(); //FALSE se non esiste
            $mandatoPagamento = $pagamento->getMandatoPagamento();
            $chkP = $pagamento->valutazioneChkPagemento('PRINCIPALE');
            $chkCL = $pagamento->valutazioneChkPagemento('POST_CONTROLLO_LOCO');

            $istruttore = null;
            foreach ($pagamento->getAssegnamentiIstruttoria() as $i) {
                if ($i->getAttivo()) {
                    $istruttore = $i;
                    break;
                }
            }

            $ultimaVariazioneApprovata = $richiesta->getAttuazioneControllo()->getUltimaVariazioneApprovata();

            $esiti = $pagamento->getEsitiIstruttoriaPagamento();
            $esito = !is_null($esiti) && $esiti->last() ? $esiti->last() : null;
            $richieste_protocollo = !is_null($esito) ? $esito->getRichiesteProtocollo() : null;
            $richiesta_protocollo = !is_null($richieste_protocollo) && $richieste_protocollo->last() ? $richieste_protocollo->last() : null;
            $data_protocollo_esito_tmp = !is_null($richiesta_protocollo) ? $richiesta_protocollo->getDataPg() : null;
            $data_protocollo_esito = !is_null($data_protocollo_esito_tmp) ? $data_protocollo_esito_tmp->format('d/m/Y') : '-';
            $numero_protocollo_esito = !is_null($richiesta_protocollo) ? $richiesta_protocollo->getProtocollo() : '-';

            if ($pagamento->isAnticipo()) {
                $erogato = $this->getValoreFromChecklist($pagamento, 'ANTICIPO_EROGATO');
            } else {
                $erogato = $this->getValoreFromChecklist($pagamento, 'CONTRIBUTO_EROGABILE');
            }

            $iban = '';
            
            if ($pagamento->getRichiesta()->getMandatario()) {
                if(!\is_null($pagamento->getRichiesta()->getMandatario()->getDatiBancari())){
                    if(!\is_null($pagamento->getRichiesta()->getMandatario()->getDatiBancari()->first()) && $pagamento->getRichiesta()->getMandatario()->getDatiBancari()->first() != false){
                        $iban = $pagamento->getRichiesta()->getMandatario()->getDatiBancari()->first()->getIban();
                    }
                }
                
//                
//                $datiBancari = $pagamento->getRichiesta()->getMandatario()->getDatiBancari();
//                if($pagamento->getRichiesta()->getMandatario()->getDatiBancari()){
//                    if($pagamento->getRichiesta()->getMandatario()->getDatiBancari()->first() == true){
//                        $first = $pagamento->getRichiesta()->getMandatario()->getDatiBancari()->first();
//                        $a = 1;
//                    }elseif($pagamento->getRichiesta()->getMandatario()->getDatiBancari()->first() == false){
//                        $a=2;
//                    }
//                }
//                $iban = !\is_null($pagamento->getRichiesta()->getMandatario()->getDatiBancari()) ? $pagamento->getRichiesta()->getMandatario()->getDatiBancari()->first()->getIban() : '';
            }

            $valori = array(
                $procedura->getAsse()->getTitolo(), // Asse
                $procedura->getTitolo(), //Procedura
                $procedura->getAtto()->getNumero(), //Num.Atto
                $pagamento->getId(), //N. Pagamento
                $pagamento->getModalitaPagamento(), //Tipo Pagamento
                Date::dateTimeToExcel($pagamento->getDataInvio()), //Data Invio Pagamento
                $pagamento->getDataProtocollo(), //Data Protocollazione Pagamento @TODO Da verificare che prenda l'ultimo
                $pagamento->getProtocollo(), //Numero Protocollo Pagamento
                $richiesta->getId(), //ID Richiesta
                is_null($richiesta->getProtocollo()) ? '-' : $richiesta->getProtocollo(), //Numero protocollo Richiesta
                $soggetto->getDenominazione(), //Ragione Sociale/Denominazione del soggetto
                $soggetto->getCodiceFiscale(), //Codice Fiscale
                $soggetto->getPartitaIva(), //Partita IVA
                $richiesta->getTitolo(), //Titolo Progetto
                !\is_null($istruttoriaRichiesta) ? $istruttoriaRichiesta->getCodiceCup() : "-", //CUP del progetto
                \floatval($richiesta->getCostoAmmesso()), //Spesa totale ammessa in concessione
                !\is_null($istruttoriaRichiesta) ? \floatval($istruttoriaRichiesta->getContributoAmmesso()) : "", //Contributo concesso in fase di concessione
                \is_null($ultimaVariazioneApprovata) ? '' : \floatval($ultimaVariazioneApprovata->getContributoAmmesso()), // Importo variato
                !\is_null($pagamento->getImportoRendicontato()) ? \floatval($pagamento->getImportoRendicontato()) : \floatval($pagamento->getRendicontato()), //Spesa totale rendicontata
                \floatval($pagamento->getImportoRichiesto()), //Contributo richiesto in fase di concessione
                \floatval($this->getValoreFromChecklist($pagamento, 'SPESE_AMMESSE')), //Spesa ammessa in fase di liquidazione
                \floatval($erogato), //Spesa erogata in fase di liquidazione
                $iban, //IBAN
                $pagamento->getDescrizioneEsito(), //Stato della pratica
                $ultimaIntegrazione ? $ultimaIntegrazione->getProtocolloIntegrazione() : "-", //Protocollo integrazione
                $ultimaIntegrazione && $ultimaIntegrazione->getDataProtocolloIntegrazione() ? $ultimaIntegrazione->getDataProtocolloIntegrazione()->format('d/m/Y') : '', //Data invio richiesta integrazione
                !is_null($rispostaUltimaIntegrazione) ? (!is_null($rispostaUltimaIntegrazione->getDataProtocolloRispostaIntegrazione()) ? $rispostaUltimaIntegrazione->getDataProtocolloRispostaIntegrazione()->format('d/m/Y') : "-") : "-", //Data risposta integrazione
                !\is_null($rispostaUltimaIntegrazione) ? $rispostaUltimaIntegrazione->getProtocolloRispostaIntegrazione() : "-", //Protocollo risposta integrazione
                $ultimoControlloLoco ? "SI" : "NO", //Progetto campionato per il controllo in loco
                $ultimoControlloLoco ? ($ultimoControlloLoco->getDataValidazione() ? $ultimoControlloLoco->getDataValidazione()->format('d/m/Y') : "-" ) : "-", //Data esito controlli in loco
                $mandatoPagamento ? ( $mandatoPagamento->getAttoLiquidazione() ? $mandatoPagamento->getAttoLiquidazione()->getNumero() : "-" ) : "-", //Numero atto di liquidazione
                $mandatoPagamento ? ( $mandatoPagamento->getAttoLiquidazione() ? $mandatoPagamento->getAttoLiquidazione()->getData()->format('d/m/Y') : "-" ) : "-", //Data atto di liquidazione
                $mandatoPagamento ? $mandatoPagamento->getNumeroMandato() : "-", //Numero mandato di pagamento
                $mandatoPagamento ? $mandatoPagamento->getDataMandato()->format('d/m/Y') : "-", //Data mandato di pagamento
                \is_null($istruttore) ? 'non assegnato' : $istruttore->getIstruttore()->getPersona(), //Istruttore assegnato
                \is_null($chkP) ? "-" : \is_null($chkP->getDataValidazione()) ? '-' : Date::dateTimeToExcel($chkP->getDataValidazione()),
                \is_null($chkCL) ? "-" : \is_null($chkCL->getDataValidazione()) ? '-' : Date::dateTimeToExcel($chkCL->getDataValidazione()),
                $numero_protocollo_esito,
                $data_protocollo_esito
            );

            $riga++;
            $activeSheet->fromArray($valori, null, "A$riga");
        }

        if ($riga > 1) {
            $activeSheet->getStyle("F2:G$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            $activeSheet->getStyle("H2:O$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $activeSheet->getStyle("P2:T$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            $activeSheet->getStyle("Z2:AA$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            $activeSheet->getStyle("AD2:AD$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            $activeSheet->getStyle("AF2:AF$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            $activeSheet->getStyle("AH2:AH$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            $activeSheet->getStyle("AJ2:AK$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            $activeSheet->getStyle("AM2:AM$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
        }

        $response = $excelService->createResponse($phpExcelObject, 'Esportazione_MASSIVA_Pagamenti.xlsx');
        return $response;
    }

}
