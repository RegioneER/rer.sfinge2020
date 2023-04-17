<?php

namespace RichiesteBundle\Service;

use DocumentoBundle\Component\ResponseException;
use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Form\ImpegnoType;
use AttuazioneControlloBundle\Form\DocumentoImpegnoType;
use AttuazioneControlloBundle\Entity\DocumentoImpegno;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Entity\DocumentoFile;
use BaseBundle\Form\SalvaIndietroType;
use MonitoraggioBundle\Form\RichiestaIndicatoreOutputType;
use AttuazioneControlloBundle\Form\ProceduraAggiudicazioneBeneficiarioType;
use AttuazioneControlloBundle\Entity\ProceduraAggiudicazione;
use AttuazioneControlloBundle\Form\ProgettoProceduraAggiudicazioneType;
use Symfony\Component\HttpFoundation\Response;
use MonitoraggioBundle\Service\IGestoreIterProgetto;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;

class GestoreProcedureParticolariBase extends AGestoreProcedureParticolari {

    public function getPianiDeiCosti() {
        
    }

    public function nuovaRichiesta($id_richiesta, $opzioni = array()) {
        
    }

    public function dettaglioRichiesta($id_richiesta, $opzioni = array()) {


        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        if (is_null($richiesta)) {
            $this->addErrorRedirect("La richiesta non è stata trovata", "elenco_richieste");
        }

        $dati["richiesta"] = $richiesta;

        $dati["proponenti"] = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getProponentiRichiesta($id_richiesta);
        $dati["mandatario"] = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getMandatarioRichiesta($id_richiesta);
        $dati["piano_costo_attivo"] = $richiesta->getProcedura()->getPianoCostoAttivo();
        $dati["avanzamenti"] = $this->gestioneBarraAvanzamento();

        $response = $this->render("RichiesteBundle:ProcedureParticolari:mainRichiestaPP.html.twig", $dati);

        return new GestoreResponse($response);
    }

    public function generaPianoDeiCosti($id_proponente, $opzioni = array()) {

        $em = $this->getEm();
        $proponente = $em->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
        $richiesta = $proponente->getRichiesta();

        if ($richiesta->isAssistenzaTecnica()) {
            $vocePiano = $em->getRepository("RichiesteBundle:PianoCosto")->getVociAssTecnica();
        }

        if ($richiesta->isIngegneriaFinanziaria()) {
            $vocePiano = $em->getRepository("RichiesteBundle:PianoCosto")->getVociIngFinanziaria();
        }

        /* if ($richiesta->isAcquisizioni()) {
          $vocePiano = $em->getRepository("RichiesteBundle:PianoCosto")->getVociAcquisizioni();
          } */

        try {
            $voce = new \RichiesteBundle\Entity\VocePianoCosto();
            $voce->setPianoCosto($vocePiano);
            $voce->setProponente($proponente);
            $voce->setRichiesta($richiesta);

            if ($richiesta->isIngegneriaFinanziaria()) {
                $voce->setImportoAnno1($richiesta->getProcedura()->getRisorseDisponibili());
            }
            $em->persist($voce);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function aggiornaPianoDeiCosti($id_proponente, $opzioni = array(), $twig = null, $opzioni_twig = array()) {

        $proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
        $richiesta = $proponente->getRichiesta();
        $id_richiesta = $richiesta->getId();

        $request = $this->getCurrentRequest();
        $anno = $richiesta->getProcedura()->getAnnoProgrammazione();

        $opzioni['url_indietro'] = $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array('id_richiesta' => $id_richiesta));
        $opzioni['modalita_finanziamento_attiva'] = $richiesta->getProcedura()->getModalitaFinanziamentoAttiva();
        $opzioni['annualita'] = 1;
        $opzioni['labels_anno'] = array('importo_anno_1' => $anno);
        $opzioni['disabled'] = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitataPP($richiesta);
        $opzioni['abilita_contr_impe'] = $richiesta->isAssistenzaTecnica() || $richiesta->isAcquisizioni();

        if (\is_null($twig)) {
            $twig = "RichiesteBundle:Richieste:pianoCosto.html.twig";
        }

        $istruttoria = $richiesta->getIstruttoria();
        if (!\is_null($istruttoria->getContributoAmmesso())) {
            $proponente->setContributo($istruttoria->getContributoAmmesso());
        }

        if (!\is_null($istruttoria->getImpegnoAmmesso())) {
            $proponente->setImpegno($istruttoria->getImpegnoAmmesso());
        }

        $form = $this->createForm("RichiesteBundle\Form\PianoCostiBaseType", $proponente, $opzioni);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEm();
            try {
                $em->beginTransaction();

                $em->persist($proponente);
                if ($opzioni['abilita_contr_impe'] == true) {
                    $istruttoria->setContributoAmmesso($proponente->getContributo());
                    $istruttoria->setImpegnoAmmesso($proponente->getImpegno());
                    $em->persist($istruttoria);
                    /*
                     * I set dei dati impegno la faccio solo la prima volta poi saranno editabili
                     */
                    if ($richiesta->getMonImpegni()->count() == 0) {
                        $this->setDatiImpegni($proponente, $richiesta);
                        $em->persist($richiesta);
                    }
                }

                $em->flush();
                $em->commit();
                $this->addFlash('success', "Modifiche salvate correttamente");

                return new GestoreResponse($this->redirect($this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $id_richiesta))));
            } catch (\Exception $e) {
                $em->rollback();
                $this->addFlash('error', "Errore nel salvataggio delle informazioni");
            }
        } else {
            if ($form->getErrors()->count() != $form->getErrors(true)->count()) {
                $error = new \Symfony\Component\Form\FormError("Sono presenti valori non corretti o non validi. È ammesso soltanto il separatore dei decimali.");
                $form->addError($error);
            }
        }

        if (!\array_key_exists("onKeyUp", $opzioni_twig)) {
            $dati["onKeyUp"] = "calcolaTotaleSezione";
        }


        $dati["form"] = $form->createView();
        $dati["annualita"] = $opzioni['annualita'];
        $dati["complessivo"] = false;
        $dati["abilita_contr_impe"] = $opzioni['abilita_contr_impe'];
        $dati = array_merge($dati, $opzioni_twig);
        //aggiungo il titolo della pagina e le info della breadcrumb
        $this->container->get("pagina")->setTitolo("Piano costi");
        $this->container->get("pagina")->setSottoTitolo("pagina per la compilazione del piano costi della domanda");
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richieste", $this->generateUrlByTipoProcedura("elenco_richieste", $richiesta->getProcedura()));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $id_richiesta)));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Piano costi");

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response, $twig, $dati);
    }

    public function isRichiestaDisabilitata($id_richiesta = null) {
        if (!$this->isUtenteAbilitato()) {
            return true;
        }

        $em = $this->getEm();
        if (is_null($id_richiesta)) {
            $id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->get("id_richiesta");
        }
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        $stato = $richiesta->getStato()->getCodice();
        if ($stato != \BaseBundle\Entity\StatoRichiesta::PRE_INSERITA) {
            return true;
        }
        return false;
    }

    public function isRichiestaDisabilitataMon($id_richiesta = null) {
        if (!$this->isUtenteAbilitato()) {
            return true;
        }
        return false;
    }

    public function isUtenteAbilitato() {

        if ($this->getUser()->isAbilitatoStrumentiFinanziariScrittura()) {
            return true;
        }
        return false;
    }

    public function validaDatiProgetto($id_richiesta, $opzioni = array()) {
        $em = $this->getEm();
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        $istruttoria = $richiesta->getIstruttoria();
        $esito = new EsitoValidazione(true);
        $esito->setSezione("Dati generali");

        if ($richiesta->isIngegneriaFinanziaria() == false) {
            if (is_null($istruttoria->getDataAvvioProgetto()) || is_null($istruttoria->getDataTermineProgetto())) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione("Sezione incompleta");
            }
        }
        return $esito;
    }

    public function gestioneDatiProgetto($id_richiesta, $opzioni = array()) {
        $em = $this->getEm();
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $istruttoria = $richiesta->getIstruttoria();
        $request = $this->getCurrentRequest();
        if (is_null($richiesta)) {
            $this->addErrorRedirectByTipoProcedura("La richiesta non è stata trovata", "elenco_richieste", $richiesta->getProcedura());
        }
        $opzioni['url_indietro'] = $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId()));
        $opzioni["disabled"] = $this->isRichiestaDisabilitata();

        if ($richiesta->isAssistenzaTecnica() == true || $richiesta->isAcquisizioni() == true) {
            if (!is_null($istruttoria->getDataAvvioProgetto())) {
                $richiesta->setDataInizioProgetto($istruttoria->getDataAvvioProgetto());
            }
            if (!is_null($istruttoria->getDataTermineProgetto())) {
                $richiesta->setDataFineProgetto($istruttoria->getDataTermineProgetto());
            }
        }

        if ($richiesta->isAssistenzaTecnica() == true) {
            $form = $this->createForm("RichiesteBundle\Form\AssistenzaTecnica\DatiProgettoType", $richiesta, $opzioni);
        } elseif ($richiesta->isIngegneriaFinanziaria() == true) {
            $form = $this->createForm("RichiesteBundle\Form\IngegneriaFinanziaria\DatiProgettoType", $richiesta, $opzioni);
        } elseif ($richiesta->isAcquisizioni() == true) {
            $form = $this->createForm("RichiesteBundle\Form\Acquisizioni\DatiProgettoType", $richiesta, $opzioni);
        } else {
            $form = $this->createForm("RichiesteBundle\Form\DatiProgettoType", $richiesta, $opzioni);
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEm();
                try {

                    $em->beginTransaction();
                    if ($richiesta->isAssistenzaTecnica() == true || $richiesta->isAcquisizioni() == true) {
                        $istruttoria->setDataAvvioProgetto($richiesta->getDataInizioProgetto());
                        $istruttoria->setDataTermineProgetto($richiesta->getDataFineProgetto());
                        $em->persist($istruttoria);
                    }
                    $em->persist($richiesta);

                    $em->flush();
                    $em->commit();

                    return new GestoreResponse($this->addSuccesRedirectByTipoProcedura("Dati del progetto modificati correttamente", "dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId()))
                    );
                } catch (\Exception $e) {
                    $em->rollback();
                    throw new SfingeException("Dati del progetto non modificati");
                }
            }
        }

        $dati = array("id_richiesta" => $richiesta->getId(), "form" => $form->createView());

        $response = $this->render("RichiesteBundle:ProcedureParticolari:datiProgetto.html.twig", $dati);

        return new GestoreResponse($response, "RichiesteBundle:ProcedureParticolari:datiProgetto.html.twig", $dati);
    }

    public function gestioneBarraAvanzamento() {
        $richiesta = $this->getRichiesta();
        $statoRichiesta = $richiesta->getStato()->getCodice();
        $arrayStati = array('Inserita' => true, 'Completata' => false);

        switch ($statoRichiesta) {
            case 'PRE_INVIATA_PA':
                $arrayStati['Completata'] = true;
        }
        return $arrayStati;
    }

    public function dammiVociMenuElencoRichiesteProcedureParticolari($id_richiesta) {
        $csrfTokenManager = $this->container->get("security.csrf.token_manager");
        $token = $csrfTokenManager->getToken("token")->getValue();
        $this->container->get("request_stack")->getCurrentRequest()->attributes->set("id_richiesta", $id_richiesta);
        $vociMenu = array();
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        if (!is_null($richiesta->getStato())) {
            $stato = $richiesta->getStato()->getCodice();
            $esitoValidazione = $this->controllaValiditaRichiesta($id_richiesta);
            if ($stato == \BaseBundle\Entity\StatoRichiesta::PRE_INSERITA && $this->isUtenteAbilitato() && $esitoValidazione->getEsito() == true) {
                $voceMenu["label"] = "Completa";
                $voceMenu["path"] = $this->generateUrlByTipoProcedura("completa_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $id_richiesta, "_token" => $token));
                $vociMenu[] = $voceMenu;
            } else {
                $voceMenu["label"] = "Visualizza";
                $voceMenu["path"] = $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $id_richiesta));
                $vociMenu[] = $voceMenu;
            }
        }

        return $vociMenu;
    }

    public function completaRichiesta($id_richiesta, $opzioni = array()) {
        /** @var Richiesta $richiesta */
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        if ($richiesta->getStato()->uguale(\BaseBundle\Entity\StatoRichiesta::PRE_INSERITA)) {
            try {

                $proponenti = $richiesta->getProponenti();
                $proponente = $proponenti[0];

                $this->getEm()->beginTransaction();
                $this->creaOggettiVersions($richiesta);
                $richiesta->setDataInvio(new \DateTime());

                $voci_piano_costo = $proponente->getVociPianoCosto();
                $voce_piano_costo = $voci_piano_costo[0]->getImportoAnno1();

                //Se non esiste la voce di istruttoria la creo e imposto i valori come approvati
                if (is_null($voci_piano_costo[0]->getIstruttoria())) {
                    $istruttoria_voce = new \IstruttorieBundle\Entity\IstruttoriaVocePianoCosto();
                    $istruttoria_voce->setTaglioAnno1(0.00);
                    $istruttoria_voce->setImportoAmmissibileAnno1($voci_piano_costo[0]->getImportoAnno1());
                    $istruttoria_voce->setVocePianoCosto($voci_piano_costo[0]);
                    $this->getEm()->persist($istruttoria_voce);
                }
                $richiesta->setContributoRichiesta($voce_piano_costo);

                $this->container->get("sfinge.stati")->avanzaStato($richiesta, \BaseBundle\Entity\StatoRichiesta::PRE_INVIATA_PA);

                /** monitoraggio */
                $istruttoria = $richiesta->getIstruttoria();
                $richiesta->setMonTipoOperazione($istruttoria->getCupTipologia()->getTc5TipoOperazione());

                $this->getEm()->flush();

                foreach ($richiesta->getAttuazioneControllo()->getPagamenti() as $pagamento) {
                    if ($pagamento->getStato()->getCodice() != 'PAG_INVIATO_PA') {
                        $this->container->get("gestore_pagamenti")->getGestore()->inviaPagamento($pagamento->getId());
                    }
                }

                $this->getEm()->commit();
            } catch (\Exception $e) {
                $this->getEm()->rollback();
                throw new \BaseBundle\Exception\SfingeException("Errore, contattare l'assistenza tecnica");
            }

            return new GestoreResponse($this->addSuccesRedirectByTipoProcedura("Richiesta completata correttamente", "dettaglio_richiesta", $richiesta->getProcedura(), array('id_richiesta' => $id_richiesta)));
        }
        throw new \BaseBundle\Exception\SfingeException("Stato non valido per effettuare la validazione");
    }

    public function creaOggettiVersions($richiesta) {
        $versioning = $this->container->get("soggetto.versioning");
        foreach ($richiesta->getProponenti() as $proponente) {
            if (is_null($proponente->getSoggettoVersion())) {
                $soggetto_version = $versioning->creaSoggettoVersion($proponente->getSoggetto());
                $proponente->setSoggettoVersion($soggetto_version);
            }

            foreach ($proponente->getSedi() as $sedeOperativa) {
                if (is_null($sedeOperativa->getSedeVersion())) {
                    $sede_version = $versioning->creaSedeVersion($sedeOperativa->getSede());
                    $sedeOperativa->setSedeVersion($sede_version);
                }
            }
        }
    }

    public function generateUrlByTipoProcedura($route, $procedura, $params = array()) {
        $newRoute = $this->getRouteNameByTipoProcedura($route);

        return $this->generateUrl($newRoute, $params);
    }

    public function addSuccesRedirectByTipoProcedura($msg, $route, $procedura, $params = array()) {
        $newRoute = $this->getRouteNameByTipoProcedura($route);
        return $this->addSuccesRedirect($msg, $newRoute, $params);
    }

    public function addErrorRedirectByTipoProcedura($msg, $route, $procedura, $params = array()) {
        $newRoute = $this->getRouteNameByTipoProcedura($route);

        return $this->addErrorRedirect($msg, $newRoute, $params);
    }

    protected function getRouteNameByTipoProcedura(string $route): string {
        throw new \LogicException('Metodo non implementato');
    }

    protected function getUrlDettaglio(Richiesta $richiesta): string {
        $routeName = $this->getRouteNameByTipoProcedura('dettaglio_richiesta');

        return $this->generateUrl($routeName, ["id_richiesta" => $richiesta->getId()]);
    }

    public function validaProponenti($id_richiesta, $opzioni = array()) {
        $esito = new EsitoValidazione(true);
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        foreach ($richiesta->getProponenti() as $proponente) {
            $esitoProponente = $this->validaProponente($proponente->getId());
            if (!$esitoProponente->getEsito()) {
                if (count($esitoProponente->getMessaggi()) > 0) {
                    $esito->addMessaggioSezione("I dati inseriti per il proponente " . $proponente->getSoggetto() . " non sono completi. Selezionare la voce 'Visualizza' dal menu 'Azioni'");
                } else {
                    $esito->addMessaggioSezione('Uno o più proponenti non sono correttamente inseriti');
                }
                $esito->setEsito(false);
                break;
            }
        }
        return $esito;
    }

    public function validaProponente($id_proponente, $opzioni = array()) {
        $esito = new EsitoValidazione(true);
        $proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);

        if (count($proponente->getReferenti()) < 1) {
            $esito->setEsito(false);
            $esito->addMessaggio("Per il proponente " . $proponente->getSoggetto()->getDenominazione() . " occorre indicare il responsabile di progetto ");
        }

        return $esito;
    }

    public function controllaValiditaRichiesta($id_richiesta, $opzioni = array()) {

        //viene anche usato nell'elenco richieste quindi inietto il parametro id_richiesta
        $this->container->get("request_stack")->getCurrentRequest()->attributes->set("id_richiesta", $id_richiesta);
        $richiesta = $this->getRichiesta();

        $esitiSezioni = array();
        $esitiSezioni[] = $this->validaDatiProgetto($id_richiesta);
        $esitiSezioni[] = $this->container->get("gestore_proponenti")->getGestore($richiesta->getProcedura())->validaProponenti($id_richiesta);
        $esitiSezioni[] = $this->validaDatiProtocollo($id_richiesta);
        $esitiSezioni[] = $this->container->get("gestore_pagamenti")->getGestore($richiesta->getProcedura())->validaPagamenti($richiesta);
        if ($richiesta->getProcedura()->getPianoCostoAttivo()) {
            $esitiSezioni[] = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->validaPianoDeiCosti($id_richiesta);
        }
        if ($richiesta->getProcedura()->getFasiProcedurali()) {
            $esitiSezioni[] = $this->container->get("gestore_fase_procedurale")->getGestore($richiesta->getProcedura())->validaFaseProceduraleRichiesta($id_richiesta);
        }
        if ($richiesta->isAssistenzaTecnica() == true || $richiesta->isAcquisizioni() == true) {
            $esitiSezioni[] = $this->validaImpegni($id_richiesta);
            $esitiSezioni[] = $this->validaMonitoraggioIndicatori($id_richiesta);
            $esitiSezioni[] = $this->validaDatiCup($id_richiesta);
        }

        $esito = true;
        $messaggi = array();
        $messaggiSezione = array();
        foreach ($esitiSezioni as $esitoSezione) {
            $esito &= $esitoSezione->getEsito();
            $messaggi = array_merge_recursive($messaggi, $esitoSezione->getMessaggi());
            $messaggiSezione = array_merge_recursive($messaggiSezione, $esitoSezione->getMessaggiSezione());
        }


        $esito = new EsitoValidazione($esito, $messaggi, $messaggiSezione);
        return $esito;
    }

    public function datiProtocollo($id_richiesta) {
        $em = $this->getEm();
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $request = $this->getCurrentRequest();
        if (is_null($richiesta)) {
            return new GestoreResponse($this->addErrorRedirectByTipoProcedura("La richiesta non è stata trovata", "elenco_richieste", $richiesta->getProcedura()));
        }

        $opzioni['url_indietro'] = $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId()));
        $opzioni["disabled"] = $this->isRichiestaDisabilitata();

        $richieste_protocollo = $richiesta->getRichiesteProtocollo();
        $richiesta_protocollo = $richieste_protocollo[0];

        $form = $this->createForm("RichiesteBundle\Form\DatiProtocolloType", $richiesta_protocollo, $opzioni);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $richiesta_protocollo_verifica = $em->getRepository("ProtocollazioneBundle:RichiestaProtocolloFinanziamento")->findOneBy(array("registro_pg" => $richiesta_protocollo->getRegistroPg(), "anno_pg" => $richiesta_protocollo->getAnnoPg(), "num_pg" => $richiesta_protocollo->getNumPg()));
                if (count($richiesta_protocollo_verifica) > 0 && ($richiesta_protocollo->getId() != $richiesta_protocollo_verifica->getId())) {
                    return new GestoreResponse($this->addErrorRedirectByTipoProcedura("Il protocollo inserito è già presente a sistema  ", "dati_protocollo", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
                }
                $em = $this->getEm();
                try {

                    $em->beginTransaction();

                    $em->persist($richiesta_protocollo);

                    $em->flush();
                    $em->commit();

                    return new GestoreResponse($this->addSuccesRedirectByTipoProcedura("Dati del protocollo modificati correttamente", "dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId()))
                    );
                } catch (\Exception $e) {
                    $em->rollback();
                    throw new SfingeException("Dati del protocollo non modificati");
                }
            }
        }

        $dati = array("id_richiesta" => $richiesta->getId(), "form" => $form->createView());

        $response = $this->render("RichiesteBundle:Richieste:datiProtocollo.html.twig", $dati);

        return new GestoreResponse($response, "RichiesteBundle:Richieste:datiProtocollo.html.twig", $dati);
    }

    public function isRichiestaDisabilitataPP($richiesta) {
        if (!$this->isUtenteAbilitato()) {
            return true;
        }

        $stato = $richiesta->getStato()->getCodice();
        if ($stato != \BaseBundle\Entity\StatoRichiesta::PRE_INSERITA) {
            return true;
        }

        return false;
    }

    public function validaDatiProtocollo($id_richiesta, $opzioni = array()) {
        //i dati del form sono statici tra i vari bandi pertanto controllo solo che siano valorizzati
        $esito = new EsitoValidazione(true);
        $esito->setSezione("Dati protocollo");

        $em = $this->getEm();
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        $richieste_protocollo = $richiesta->getRichiesteProtocollo();
        $richiesta_protocollo = $richieste_protocollo[0];

        $registro_pg = $richiesta_protocollo->getRegistroPg();
        if (empty($registro_pg)) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Il registro non è valorizzato");
        }

        $anno_pg = $richiesta_protocollo->getAnnoPg();
        if (empty($anno_pg)) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("L'anno non è valorizzato");
        }

        $num_pg = $richiesta_protocollo->getNumPg();
        if (empty($num_pg)) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Il numero protocollo non è valorizzato");
        }

        return $esito;
    }

    public function elencoDocumenti($id_richiesta, $opzioni = array()) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documento_richiesta = new \RichiesteBundle\Entity\DocumentoRichiesta();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();

        $documenti_caricati = $em->getRepository("RichiesteBundle\Entity\DocumentoRichiesta")->findDocumentiCaricati($id_richiesta);

        $richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $listaTipi = $this->getTipiDocumenti($id_richiesta, 0);

        // chiedono di poter aggiungere documenti anche se la richiesta è inviata.
        // Questo perchè anche quando la richiesta è inviata è ancora possibile aggiungere pagamenti (ad esempio SAL o saldo)
        // e devono essere quindi aggiunti documenti riferiti al nuovo pagamento
        //$allowUpload = false;
        /* foreach ($richiesta->getAttuazioneControllo()->getPagamenti() as $pagamento){
          if(!$pagamento->isInviato()){
          $allowUpload = true;
          break;
          }
          } */
        $allowUpload = true;
        if (count($listaTipi) > 0 && (!$this->isRichiestaDisabilitata() || $allowUpload)) {

            $opzioni_form["lista_tipi"] = $listaTipi;
            $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
            $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Salva'));

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {

                        $this->container->get("documenti")->carica($documento_file, 0, $richiesta);

                        $documento_richiesta->setDocumentoFile($documento_file);
                        $documento_richiesta->setRichiesta($richiesta);
                        $em->persist($documento_richiesta);

                        $em->flush();
                        return new GestoreResponse($this->addSuccesRedirectByTipoProcedura("Documento caricato correttamente", "elenco_documenti_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
                    } catch (ResponseException $e) {
                        $this->addFlash('error', $e->getMessage());
                    }
                }
            }
            $form_view = $form->createView();
        } else {
            $form_view = null;
        }

        $dati = array("documenti" => $documenti_caricati, "richiesta" => $richiesta, "form" => $form_view, 'is_richiesta_disabilitata' => $this->isRichiestaDisabilitata());
        $response = $this->render("RichiesteBundle:ProcedureParticolari:elencoDocumentiRichiestaPP.html.twig", $dati);
        return new GestoreResponse($response, "RichiesteBundle:ProcedureParticolari:elencoDocumentiRichiestaPP.html.twig", $dati);
    }

    public function eliminaDocumentoRichiesta($id_documento_richiesta, $opzioni = array()) {
        $em = $this->getEm();
        $documento = $em->getRepository("RichiesteBundle\Entity\DocumentoRichiesta")->find($id_documento_richiesta);
        $id_richiesta = $documento->getRichiesta()->getId();
        try {
            $em->remove($documento->getDocumentoFile());
            $em->remove($documento);
            $em->flush();
            return new GestoreResponse($this->addSuccesRedirectByTipoProcedura("Documento eliminato correttamente", "elenco_documenti_richiesta", $documento->getRichiesta()->getProcedura(), array("id_richiesta" => $id_richiesta))
            );
        } catch (ResponseException $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    public function validaDocumenti($id_richiesta, $opzioni = array()) {
        $esito = new EsitoValidazione(true);
        $documenti_obbligatori = $this->getTipiDocumenti($id_richiesta, 1);

        foreach ($documenti_obbligatori as $documento) {
            $esito->addMessaggio('Caricare il documento ' . $documento->getDescrizione());
        }

        if (count($documenti_obbligatori) > 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Caricare tutti gli allegati previsti dalla procedura");
        }

        return $esito;
    }

    public function validaDatiTrasferimentoFondo($id_richiesta, $opzioni = array()) {
        $esito = new EsitoValidazione(true);

        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        if ($richiesta->getAtti()->isEmpty()) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Non è stato selezionato l'atto di trasferimento del fondo, se l'elenco è vuoto occorre crearlo nella sezione atti amministrativi");
        }

        return $esito;
    }

    /**
     * @param Richiesta $richiesta
     */
    public function gestioneIndicatori($richiesta) {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $opzioni_form = array(
            'to_richiesta' => false,
            'to_beneficiario' => false,
            'validation_groups' => array('presentazione_beneficiario'),
            'disabled' => $this->isRichiestaDisabilitataMon(),
        );
        $indicatori = $richiesta->getMonIndicatoreOutput();
        foreach ($indicatori as $indicatore) {
            if ($indicatore->getIndicatore()->isAutomatico()) {
                $indicatori->removeElement($indicatore);
            }
        }
        $form = $this->createForm(RichiestaIndicatoreOutputType::class, $richiesta, $opzioni_form);
        $form->add('submit', SalvaIndietroType::class, [
            'label' => false,
            'url' => $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array('id_richiesta' => $richiesta->getId())),
            'disabled' => $this->isRichiestaDisabilitataMon(),
        ]);

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

        $dati = array("richiesta" => $richiesta, "form" => $form->createView());
        return $this->render("RichiesteBundle:ProcedureParticolari:monitoraggioIndicatori.html.twig", $dati);
    }

    /**
     * @param Richiesta $richiesta
     * 
     * @return EsitoValidazione
     */
    public function validaMonitoraggioIndicatori($id_richiesta) {
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        $validator = $this->container->get('validator');/** @var \Symfony\Component\Validator\Validator\ValidatorInterface $validator */
        $esito = new EsitoValidazione(true);
        //Verifica che tutti gli indicatori abbiano valori diversi da NULL
        foreach ($richiesta->getMonIndicatoreOutput() as $indicatore) {
            /** @var \RichiesteBundle\Entity\IndicatoreOutput $indicatore */
            if ($indicatore->getIndicatore()->isAutomatico()) {
                continue;
            }
            /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $errors */
            $errors = $validator->validate($indicatore, NULL, array('rendicontazione_beneficiario'));
            if (\count($errors)) {
                $esito->setEsito(false);
                foreach ($errors as $error) { /** @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
                    $esito->addMessaggio($error->getMessage());
                }
            }
        }

        if (!$esito->getEsito()) {
            $esito->addMessaggioSezione('Sezione incompleta');
        }

        return $esito;
    }

    public function datiCup($id_richiesta) {
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        if (\is_null($richiesta)) {
            throw new SfingeException('Risorsa non trovata');
        }

        $istruttoria = $richiesta->getIstruttoria();
        if (\is_null($istruttoria)) {
            throw new SfingeException('Risorsa non trovata');
        }

        $esisteCup = false; //! \is_null($istruttoria->getCodiceCup());

        $options = [
            'url_indietro' => $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array('id_richiesta' => $richiesta->getId())),
            // richiesto di sbloccare sezione anche in presenza di richiesta integrazione
            'selezioni' => $this->getSelezioniCup($id_richiesta, $esisteCup),
            'required_all' => false,
            'user' => $this->getUser(),
        ];
        $form = $this->createForm(\IstruttorieBundle\Form\DatiCupType::class, $istruttoria, $options);

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEm();
            $gestore_istruttoria = $this->container->get('gestore_istruttoria')->getGestore($richiesta->getProcedura());
            $gestore_istruttoria->creaLogIstruttoria($istruttoria, 'dati_cup');


            try {
                $em->flush();
                $this->addFlash('success', 'Dati cup salvati correttamente');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Errore nel salvataggio delle informazioni');
            }
        }

        $twig = 'RichiesteBundle:ProcedureParticolari:datiCup.html.twig';

        $dati = array();
        $dati['istruttoria'] = $istruttoria;
        $dati['menu'] = 'cup';
        $dati['esiste_cup'] = $esisteCup;
        $dati['form'] = $form->createView();

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response, $twig, $dati);
    }

    public function getSelezioniCup($id_richiesta, $esisteCup) {

        $selezioni = array();
        $selezioni["cup_natura"] = array();
        $selezioni["cup_tipologia"] = array();
        $selezioni["cup_settore"] = array();
        $selezioni["cup_sottosettore"] = array();
        $selezioni["cup_categoria"] = array();
        $selezioni["cup_tipi_copertura_finanziaria"] = array();

        if ($esisteCup) {
            // Recuperare le info da DB
            $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
            if (is_null($richiesta)) {
                throw new SfingeException('Risorsa non trovata');
            }

            $istruttoriaRichiesta = $richiesta->getIstruttoria();

            $tipiCoperturaFinanziaria = $istruttoriaRichiesta->getCupTipiCoperturaFinanziaria();

            array_push($selezioni["cup_natura"], $istruttoriaRichiesta->getCupNatura());
            array_push($selezioni["cup_tipologia"], $istruttoriaRichiesta->getCupTipologia());
            array_push($selezioni["cup_settore"], $istruttoriaRichiesta->getCupSettore());
            array_push($selezioni["cup_sottosettore"], $istruttoriaRichiesta->getCupSottosettore());
            array_push($selezioni["cup_categoria"], $istruttoriaRichiesta->getCupCategoria());

            foreach ($tipiCoperturaFinanziaria as $tipoCoperturaFinanziaria) {
                array_push($selezioni["cup_tipi_copertura_finanziaria"], $tipoCoperturaFinanziaria);
            }
        } else {

            $nature = $this->getEm()->getRepository("CipeBundle\Entity\Classificazioni\CupNatura")->findAll();
            $cup_tipi_copertura_finanziaria = $this->getEm()->getRepository("CipeBundle\Entity\Classificazioni\CupTipoCoperturaFinanziaria")->findAll();

            $selezioni = array();
            $selezioni["cup_natura"] = $nature;
            $selezioni["cup_tipologia"] = array();
            $selezioni["cup_settore"] = array();
            $selezioni["cup_sottosettore"] = array();
            $selezioni["cup_categoria"] = array();
            $selezioni["cup_tipi_copertura_finanziaria"] = $cup_tipi_copertura_finanziaria;
        }

        return $selezioni;
    }

    /**
     * @param int $id_richiesta
     * 
     * @return EsitoValidazione
     */
    public function validaDatiCup($id_richiesta) {
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        $validator = $this->container->get('validator');/** @var \Symfony\Component\Validator\Validator\ValidatorInterface $validator */
        $esito = new EsitoValidazione(true);
        $istruttoria = $richiesta->getIstruttoria();
        //Verifica che tutti gli indicatori abbiano valori diversi da NULL
        /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $errors */
        $errors = $validator->validate($istruttoria, NULL, array('procedure_particolari'));
        if (\count($errors)) {
            $esito->setEsito(false);
            foreach ($errors as $error) { /** @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
                $esito->addMessaggio($error->getMessage());
            }
        }

        if (!$esito->getEsito()) {
            $esito->addMessaggioSezione('Sezione incompleta');
        }

        return $esito;
    }

    /** Visualizza elenco di impegni/disimpegni
     * @param Richiesta $richiesta
     */
    public function gestioneImpegni(Richiesta $richiesta) {
        $em = $this->getEm();
        $impegni = $em->getRepository('RichiesteBundle:Richiesta')->getImpegni($richiesta->getId());

        $dati = array(
            "richiesta" => $richiesta,
            "impegni" => $impegni,
            'is_richiesta_disabilitata' => $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isRichiestaDisabilitataPP($richiesta)
        );
        return $this->render("RichiesteBundle:Richieste:monitoraggioElencoImpegni.html.twig", $dati);
    }

    /**
     * Form di modifica degli impegni legati alla richiesta
     * @param Richiesta $richiesta
     * @param RichiestaImpegni|null $impegno se NULL inserisce nuovo impegno legato alla richiesta del pagamento
     */
    public function gestioneFormImpegno(Richiesta $richiesta, RichiestaImpegni $impegno = null) {
        $em = $this->getEm();
        $nuovoImpegno = \is_null($impegno);
        if ($nuovoImpegno) {
            $impegno = new RichiestaImpegni($richiesta);
            $richiesta->addMonImpegni($impegno);
        }

        $form = $this->createForm(ImpegnoType::class, $impegno, array(
            'url_indietro' => $this->generateUrlByTipoProcedura("gestione_impegni", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())),
            'disabled' => $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitataPP($richiesta),
        ));
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $connection = $em->getConnection();
            try {
                if ($impegno->getMonImpegniAmmessi()->isEmpty()) {
                    /** @var RichiestaProgramma $programma */
                    $programma = $richiesta->getMonProgrammi()->first();
                    if ($programma === false) {
                        throw new SfingeException('Programma non definto per il progetto.');
                    }
                    $livelloGerarchico = $programma->getMonLivelliGerarchici()->first();
                    if ($programma === false) {
                        throw new SfingeException('Livello gerarchico non definto per il progetto.');
                    }
                    $livelloGerarchico = $programma->getMonLivelliGerarchici()->first();
                    $ammesso = new ImpegniAmmessi($impegno, $livelloGerarchico);
                    $impegno->addMonImpegniAmmessi($ammesso);
                } else {
                    /** @var ImpegniAmmessi $ammesso */
                    $ammesso = $impegno->getMonImpegniAmmessi()->first();
                    $ammesso->setImportoImpAmm($impegno->getImportoImpegno());
                    $ammesso->setDataImpAmm($impegno->getDataImpegno());
                    $ammesso->setTipologiaImpAmm($impegno->getTipologiaImpegno());
                    $ammesso->setTc38CausaleDisimpegnoAmm($impegno->getTc38CausaleDisimpegno());
                }

                $connection->beginTransaction();
                $em->persist($impegno);
                $em->flush();
                $connection->commit();
                $this->addFlash('success', 'Dati salvati correttamente');
            } catch (SfingeException $e) {
                if ($connection->isTransactionActive()) {
                    $connection->rollBack();
                }
                $this->container->get('logger')->error($e->getTraceAsString());
                $this->addError($e->getMessage());
            } catch (\Exception $e) {
                throw $e;
                $this->container->get('logger')->error($e->getMessage());
                if ($connection->isTransactionActive()) {
                    $connection->rollBack();
                }
                $this->addError('Errore durante il salvataggio dei dati');
            }
        }

        $tipologiaDocumento = $em->getRepository('DocumentoBundle:TipologiaDocumento')->findOneByCodice(TipologiaDocumento::DOC_IMPEGNO);
        $file = new DocumentoFile($tipologiaDocumento);
        $nuovoDocumento = new DocumentoImpegno($impegno, $file);
        $formDoc = $this->createForm(DocumentoImpegnoType::class, $nuovoDocumento, []);
        $formDoc->handleRequest($request);
        if ($formDoc->isSubmitted() && $formDoc->isValid()) {
            try {
                $file = $nuovoDocumento->getDocumento();
                $this->container->get('documenti')->carica($file);
                $em->persist($nuovoDocumento);
                $em->flush();
                $this->addSuccess('Dati salvati correttamente');
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage(), ['id_impegno' => $impegno->getId()]);
                $this->addError('Errore durante il salvataggio dei dati');
            }
        }

        $renderViewData = array(
            'form' => $form->createView(),
            'formDoc' => $formDoc->createView(),
            'is_richiesta_disabilitata' => $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isRichiestaDisabilitataPP($richiesta),
            'impegno_preesistente' => !$nuovoImpegno,
        );
        return $this->render('RichiesteBundle:Richieste:richiestaImpegno.html.twig', $renderViewData);
    }

    /**
     * @param int $id_richiesta
     * @return EsitoValidazione
     */
    public function validaImpegni($id_richiesta) {
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        $validator = $this->container->get('validator');/** @var \Symfony\Component\Validator\Validator\ValidatorInterface $validator */
        $esito = new EsitoValidazione(true);
        if (is_null($richiesta->getIstruttoria()->getCupNatura())) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione('Sezione incompleta');
            $esito->addMessaggio('Inoltre è necessario compilare i dati cup per la definizione degli impegni');
            return $esito;
        }
        if (!\in_array($richiesta->getIstruttoria()->getCupNatura()->getCodice(), array('06', '07'))) {
            //Verifica che tutte le fasi procedurali abbiano date effettive diverse da NULL
            foreach ($richiesta->getMonImpegni() as $impegno) { /** @var \AttuazioneControlloBundle\Entity\RichiestaImpegni $impegno */
                $errors = $validator->validate($impegno, NULL, array('Default'));/** @var \Symfony\Component\Validator\ConstraintViolationListInterface $errors */
                if (\count($errors)) {
                    $esito->setEsito(false);
                    foreach ($errors as $error) { /** @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
                        $esito->addMessaggio($error->getMessage());
                    }
                }
            }
        }

        if (!$esito->getEsito()) {
            $esito->addMessaggioSezione('Sezione incompleta');
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

    protected function setDatiImpegni($proponente, $richiesta) {

        $gestoreIstruttoria = $this->container->get("gestore_istruttoria")->getGestore();
        $programma = $gestoreIstruttoria->creaProgramma($richiesta);
        $richiesta->addMonProgrammi($programma);

        $classificazioni = $gestoreIstruttoria->creaClassificazioni($programma);
        foreach ($classificazioni as $classificazione) {
            $programma->addClassificazioni($classificazione);
        }

        $livelloGerarchicoPerAsse = $gestoreIstruttoria->creaLivelloGerarchicoPerAsse($programma);

        $impegno = $gestoreIstruttoria->creaImpegno($richiesta);
        $richiesta->addMonImpegni($impegno);

        $impegnoAmmesso = $gestoreIstruttoria->creaImpegnoAmmesso($impegno, $livelloGerarchicoPerAsse);
        $impegno->addMonImpegniAmmessi($impegnoAmmesso);
        $programma->addMonLivelliGerarchici($livelloGerarchicoPerAsse);
    }

    public function gestioneProceduraAggiudicazione(Richiesta $richiesta): Response {
        $em = $this->getEm();
        $procedureAggiudicazione = $richiesta->getMonProcedureAggiudicazione();
        $atc = $richiesta->getAttuazioneControllo();

        $form = $this->createForm(ProgettoProceduraAggiudicazioneType::class, $atc, [
            // 'url_indietro' => $this->generateUrl('dettaglio_pagamento', ['id_pagamento' => $pagamento->getId()]),
            'disabled' => $this->isRichiestaDisabilitataMon($richiesta->getId()),
        ]);
        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($atc->getProcedureAggiudicazione() === false) {
                    $procedureDaEliminare = $procedureAggiudicazione->filter(function(ProceduraAggiudicazione $p) {
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
            'richiesta' => $richiesta,
            'is_richiesta_disabilitata' => $this->isRichiestaDisabilitata($richiesta->getId()),
            'procedureAggiudicazione' => $procedureAggiudicazione,
        );
        return $this->render('RichiesteBundle:ProcedureParticolari:proceduraAggiudicazione.html.twig', $mv);
    }

    /**
     * @param Richiesta $richiesta
     * @return EsitoValidazione
     */
    public function validaProceduraAggiudicazione(Richiesta $richiesta): EsitoValidazione {
        $istruttoria = $richiesta->getIstruttoria();
        $naturaCup = $istruttoria->getCupNatura();
        $esito = new EsitoValidazione(true);
        if (is_null($istruttoria->getCupNatura())) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione('Sezione incompleta');
            $esito->addMessaggio('Inoltre è necessario compilare i dati cup per la definizione degli impegni');
            return $esito;
        }
        if ($naturaCup->getCodice() == \CipeBundle\Entity\Classificazioni\CupNatura::CONCESSIONE_INCENTIVI_ATTIVITA_PRODUTTIVE) {
            return $esito;
        }

        $validator = $this->container->get('validator');/** @var \Symfony\Component\Validator\Validator\ValidatorInterface $validator */
        $atc = $richiesta->getAttuazioneControllo();
        if ($atc->getProcedureAggiudicazione() == true) {
            //Verifica che tutte le fasi procedurali abbiano date effettive diverse da NULL
            foreach ($richiesta->getMonProcedureAggiudicazione() as $pg) { /** @var \AttuazioneControlloBundle\Entity\RichiestaImpegni $impegno */
                $errors = $validator->validate($pg, NULL, array('Default'));/** @var \Symfony\Component\Validator\ConstraintViolationListInterface $errors */
                if ($errors->count() > 0) {
                    $esito->setEsito(false);
                    foreach ($errors as $error) { /** @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
                        $esito->addMessaggio($error->getMessage());
                    }
                }
            }
            if ($richiesta->getMonProcedureAggiudicazione()->count() == 0) {
                $esito->setEsito(false);
            }
        }
        if (\is_null($atc->getProcedureAggiudicazione())) {
            $esito->setEsito(false);
        }

        if (!$esito->getEsito()) {
            $esito->addMessaggioSezione('Sezione incompleta');
        }

        return $esito;
    }

    public function gestioneModificaProceduraAggiudicazione(Richiesta $richiesta, $id_procedura_aggiudicazione) {
        $gara = NULL;
        $em = $this->getEm();
        if (\is_null($id_procedura_aggiudicazione)) {
            $gara = new ProceduraAggiudicazione($richiesta);
        } else {
            $gara = $em->getRepository('AttuazioneControlloBundle:ProceduraAggiudicazione')->findOneById($id_procedura_aggiudicazione);/** @var ProceduraAggiudicazione $gara */
            if ($gara->getRichiesta() != $richiesta) {
                //Stanno forzando gli ID dell'URL: presento come se non avessero inserito l'ID della procedura
                $gara = new ProceduraAggiudicazione($richiesta);
            }
        }
        $form = $this->createForm(ProceduraAggiudicazioneBeneficiarioType::class, $gara, array(
                    'url_indietro' => $this->generateUrlByTipoProcedura("gestione_procedura_aggiudicazione", $richiesta->getProcedura(), array('id_richiesta' => $richiesta->getId())),
                    'disabled' => $this->isRichiestaDisabilitata($richiesta->getId()),
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
            'is_richiesta_disabilitata' => $this->isRichiestaDisabilitata($richiesta->getId()),
        );
        return $this->render('AttuazioneControlloBundle:Pagamenti:modificaProceduraAggiudicazione.html.twig', $mv);
    }

    public function gestioneEliminaProceduraAggiudicazione(Richiesta $richiesta, $id_procedura_aggiudicazione) {
        if ($this->isRichiestaDisabilitata($richiesta->getId())) {
            throw new SfingeException('Non è possibile cancellare una procedura di cancellazione di una richiesta validato');
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

        return $this->redirect($this->generateUrlByTipoProcedura("gestione_procedura_aggiudicazione", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
    }

    public function gestioneFasiProcedurali(Richiesta $richiesta): Response {
        $gestoreIterProgetto = $this->getGestoreIterProgetto($richiesta);
        $indietro = $this->getUrlDettaglio($richiesta);

        return $gestoreIterProgetto->modificaIterFaseRichiesta([
                    'form_options' => ['indietro' => $indietro,],
                    'redirect_on_success' => $indietro,
        ]);
    }

    protected function getGestoreIterProgetto($richiesta): IGestoreIterProgetto {
        return $this->container->get('monitoraggio.iter_progetto')->getIstanza($richiesta);
    }

    public function hasIncrementoDaOggetto($richiesta) {
        return false;
    }

    public function hasIncrementoDaFascicolo($proponente) {
        return false;
    }

    public function hasIncrementoDaOccupazioneProponente($proponente) {
        return false;
    }

    public function hasIncrementoDaRisorse($richiesta) {
        return false;
    }

}
