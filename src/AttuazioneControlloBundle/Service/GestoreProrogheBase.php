<?php

namespace AttuazioneControlloBundle\Service;

use BaseBundle\Exception\SfingeException;
use AttuazioneControlloBundle\Entity\Proroga;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\StatoProroga;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Component\ResponseException;

class GestoreProrogheBase extends AGestoreProroghe {

    public function aggiungiProroga($id_richiesta) {
        if (!$this->isProrogaAggiungibile($id_richiesta)) {
            return $this->redirectToRoute("elenco_proroghe", array("id_richiesta" => $id_richiesta));
        }

        $em = $this->getEm();
        $richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);

        $proroga = new \AttuazioneControlloBundle\Entity\Proroga();
        $proroga->setAttuazioneControlloRichiesta($richiesta->getAttuazioneControllo());

        $options = array();
        $options["url_indietro"] = $this->generateUrl("elenco_proroghe", array("id_richiesta" => $id_richiesta));
        $options["firmatabili"] = $em->getRepository("SoggettoBundle:Soggetto")->getFirmatariAmmissibili($richiesta->getSoggetto());

        $form = $this->createForm("AttuazioneControlloBundle\Form\CreazioneProrogaType", $proroga, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($proroga->getTipoProroga() == 'PROROGA_AVVIO' && is_null($proroga->getDataAvvioProgetto())) {
                $form->addError(new \Symfony\Component\Form\FormError("In caso di proroga avvio progetto è necessario indicare la nuova data di avvio"));
            }
            if ($proroga->getTipoProroga() == 'PROROGA_FINE' && is_null($proroga->getDataFineProgetto())) {
                $form->addError(new \Symfony\Component\Form\FormError("In caso di proroga fine progetto è necessario indicare la nuova data di fine"));
            }

            if ($form->isValid()) {
                try {
                    $em->beginTransaction();
                    $proroga->setGestita(false);
                    $em->persist($proroga);
                    // errore perchè il pagamento non è flushato, forse meglio fare una transazione
                    $em->flush();
                    $this->container->get("sfinge.stati")->avanzaStato($proroga, "PROROGA_INSERITA");
                    $em->flush();
                    $em->commit();
                    return $this->addSuccesRedirect("La proroga è stata creata correttamente", "dettaglio_proroga", array("id_proroga" => $proroga->getId()));
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza. " . $e->getMessage());
                }
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();

        return $this->render("AttuazioneControlloBundle:Proroghe:aggiungiProroga.html.twig", $dati);
    }

    public function dettaglioProroga($id_proroga) {
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);
        $richiesta = $proroga->getRichiesta();

        $dati["proroga"] = $proroga;
        $dati["richiesta"] = $richiesta;
        $dati["avanzamenti"] = $this->gestioneBarraAvanzamento($proroga);

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco proroghe", $this->generateUrl("elenco_proroghe", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio proroga");

        return $this->render("AttuazioneControlloBundle:Proroghe:dettaglioProroga.html.twig", $dati);
    }

    public function gestioneBarraAvanzamento($proroga) {
        $stato = $proroga->getStato()->getCodice();
        $arrayStati = array('Inserita' => true, 'Validata' => false, 'Firmata' => false, 'Inviata' => false);

        switch ($stato) {
            case 'PROROGA_PROTOCOLLATA':
            case 'PROROGA_INVIATA_PA':
                $arrayStati['Inviata'] = true;
            case 'PROROGA_FIRMATA':
                $arrayStati['Firmata'] = true;
            case 'PROROGA_VALIDATA':
                $arrayStati['Validata'] = true;
        }

        return $arrayStati;
    }

    public function controllaValiditaProroga($id_proroga, $opzioni = array()) {
        $esito = true;
        $messaggi = array();
        $messaggiSezione = array();
        return new EsitoValidazione($esito, $messaggi, $messaggiSezione);
    }

    public function dammiVociMenuElencoRichieste($id_proroga) {
        $csrfTokenManager = $this->container->get("security.csrf.token_manager");
        $token = $csrfTokenManager->getToken("token")->getValue();
        //viene anche usato nell'elenco richieste quindi inietto il parametro id_richiesta
        $this->container->get("request_stack")->getCurrentRequest()->attributes->set("id_proroga", $id_proroga);
        $vociMenu = array();
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle:Proroga")->find($id_proroga);
        $id_richiesta = 201;

        if (!is_null($proroga->getStato())) {
            $stato = $proroga->getStato()->getCodice();
            if ($stato == StatoProroga::PROROGA_INSERITA) {
                $voceMenu["label"] = "Compila";
                $voceMenu["path"] = $this->generateUrl("dettaglio_proroga", array("id_proroga" => $id_proroga));
                $vociMenu[] = $voceMenu;

                $voceMenu["label"] = "Genera pdf";
                $voceMenu["path"] = $this->generateUrl("genera_pdf_proroga", array("id_proroga" => $id_proroga));
                $vociMenu[] = $voceMenu;

                //validazione
                $esitoValidazione = $this->controllaValiditaProroga($id_richiesta);

                if ($esitoValidazione->getEsito()) {
                    $voceMenu["label"] = "Valida";
                    $voceMenu["path"] = $this->generateUrl("valida_proroga", array("id_proroga" => $id_proroga, "_token" => $token));
                    $vociMenu[] = $voceMenu;
                }
            } else {
                $voceMenu["label"] = "Visualizza";
                $voceMenu["path"] = $this->generateUrl("dettaglio_proroga", array("id_proroga" => $id_proroga));
                $vociMenu[] = $voceMenu;
            }

            //scarica pdf domanda
            if ($stato != StatoProroga::PROROGA_INSERITA) {
                $voceMenu["label"] = "Scarica documento";
                $voceMenu["path"] = $this->generateUrl("scarica_proroga", array("id_proroga" => $id_proroga));
                $vociMenu[] = $voceMenu;
            }

            //carica richiesta firmata
            if ($stato == StatoProroga::PROROGA_VALIDATA/* && $this->isBeneficiario() */) {
                $voceMenu["label"] = "Carica documento firmato";
                $voceMenu["path"] = $this->generateUrl("carica_proroga_firmata", array("id_proroga" => $id_proroga));
                $vociMenu[] = $voceMenu;
            }


            if (!($stato == StatoProroga::PROROGA_INSERITA || $stato == StatoProroga::PROROGA_VALIDATA)) {
                $voceMenu["label"] = "Scarica documento firmato";
                $voceMenu["path"] = $this->generateUrl("scarica_proroga_firmata", array("id_proroga" => $id_proroga));
                $vociMenu[] = $voceMenu;
            }

            //invio alla pa
            if ($stato == StatoProroga::PROROGA_FIRMATA/*  && $this->isBeneficiario() */) {
                $voceMenu["label"] = "Invia";
                $voceMenu["path"] = $this->generateUrl("invia_proroga", array("id_proroga" => $id_proroga, "_token" => $token));
                $voceMenu["attr"] = "data-confirm=\"Continuando non sarà più possibile modificare la richiesta di proroga nemmeno dall'assistenza tecnica. Si intende procedere comunque?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
                $vociMenu[] = $voceMenu;
            }

            //invalidazione
            if (($stato == StatoProroga::PROROGA_VALIDATA || $stato == StatoProroga::PROROGA_FIRMATA)/* && $this->isBeneficiario() */) {
                $voceMenu["label"] = "Invalida";
                $voceMenu["path"] = $this->generateUrl("invalida_proroga", array("id_proroga" => $id_proroga, "_token" => $token));
                $voceMenu["attr"] = "data-confirm=\"Confermi l'invalidazione della proroga?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
                $vociMenu[] = $voceMenu;
            }

            //eliminazione
            if (!($stato == StatoProroga::PROROGA_INVIATA_PA || $stato == StatoProroga::PROROGA_PROTOCOLLATA)) {
                $voceMenu["label"] = "Elimina";
                $voceMenu["path"] = $this->generateUrl("elimina_proroga", array("id_proroga" => $id_proroga, "_token" => $token));
                $voceMenu["attr"] = "data-confirm=\"Sei sicuro di voler eliminare la proroga?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
                $vociMenu[] = $voceMenu;
            }
        }

        return $vociMenu;
    }

    public function modificaDatiProroga($id_proroga) {
        $em = $this->getEm();
        $proroga = $em->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);
        $richiesta = $proroga->getRichiesta();

        $options = array();
        $options["url_indietro"] = $this->generateUrl("dettaglio_proroga", array("id_proroga" => $id_proroga));
        $options["firmatabili"] = $em->getRepository("SoggettoBundle:Soggetto")->getFirmatariAmmissibili($richiesta->getSoggetto());
        $options["disabled"] = $proroga->isRichiestaDisabilitata();

        $form = $this->createForm("AttuazioneControlloBundle\Form\CreazioneProrogaType", $proroga, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($proroga->getTipoProroga() == 'PROROGA_AVVIO' && is_null($proroga->getDataAvvioProgetto())) {
                $form->get('data_avvio_progetto')->addError(new \Symfony\Component\Form\FormError("In caso di proroga avvio progetto è necessario indicare la nuova data di avvio"));
            }
            if ($proroga->getTipoProroga() == 'PROROGA_FINE' && is_null($proroga->getDataFineProgetto())) {
                $form->get('data_fine_progetto')->addError(new \Symfony\Component\Form\FormError("In caso di proroga fine progetto è necessario indicare la nuova data di fine"));
            }

            if ($form->isValid()) {
                try {
                    if ($proroga->getTipoProroga() == 'PROROGA_AVVIO') {
                        $proroga->setDataFineProgetto(null);
                    }
                    if ($proroga->getTipoProroga() == 'PROROGA_FINE') {
                        $proroga->setDataAvvioProgetto(null);
                    }
                    $em->flush();
                    return $this->addSuccesRedirect("La proroga è stata modificata correttamente", "dettaglio_proroga", array("id_proroga" => $proroga->getId()));
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza. " . $e->getMessage());
                }
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco proroghe", $this->generateUrl("elenco_proroghe", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio proroga", $this->generateUrl("dettaglio_proroga", array("id_proroga" => $id_proroga)));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dati proroga");

        return $this->render("AttuazioneControlloBundle:Proroghe:modificaDatiProroga.html.twig", $dati);
    }

    public function invalidaProroga($id_proroga) {
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle:Proroga")->find($id_proroga);
//		if ($this->isRichiestaDisabilitataInoltro()) {
//			throw new SfingeException("Il bando è chiuso e la richiesta non è più invalidabile");
//		}
        if ($proroga->getStato()->uguale(StatoProroga::PROROGA_VALIDATA) ||
                $proroga->getStato()->uguale(StatoProroga::PROROGA_FIRMATA)) {
            $this->container->get("sfinge.stati")->avanzaStato($proroga, StatoProroga::PROROGA_INSERITA, true);
            return $this->addSuccesRedirect("Proroga invalidata", "dettaglio_proroga", array("id_proroga" => $proroga->getId()));
        }
        throw new SfingeException("Stato non valido per effettuare la invalidazione");
    }

    public function validaProroga($id_proroga) {
        /* if ($this->isRichiestaDisabilitataInoltro()) {
          throw new SfingeException("Il bando è chiuso e la richiesta non è più validabile");
          } */
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle:Proroga")->find($id_proroga);
        $richiesta = $proroga->getRichiesta();
        if ($proroga->getStato()->uguale(StatoProroga::PROROGA_INSERITA)) {
            $esitoValidazione = $this->controllaValiditaProroga($id_proroga);
            if ($esitoValidazione->getEsito()) {
                //cancello il vecchio documento se esiste
                if (!is_null($proroga->getDocumentoProroga())) {
                    $this->container->get("documenti")->cancella($proroga->getDocumentoProroga(), 1);
                }

                //genero il nuovo pdf
                $pdf = $this->generaPdf($id_proroga, false, false);

                //lo persisto
                $tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice(TipologiaDocumento::RICHIESTA_PROROGA);
                $documentoProroga = $this->container->get("documenti")->caricaDaByteArray($pdf, $this->getNomePdfProroga($proroga) . ".pdf", $tipoDocumento, false, $richiesta);

                //associo il documento alla richiesta
                $proroga->setDocumentoProroga($documentoProroga);

                //avanzo lo stato della richiesta
                $this->container->get("sfinge.stati")->avanzaStato($proroga, StatoProroga::PROROGA_VALIDATA);

                $this->getEm()->flush();
                return $this->addSuccesRedirect("Proroga validata", "dettaglio_proroga", array("id_proroga" => $proroga->getId()));
            } else {
                throw new SfingeException("La proroga non è validabile");
            }
        }
        throw new SfingeException("Stato non valido per effettuare la validazione");
    }

    public function generaPdf($id_proroga, $facsimile = true, $download = true) {
        // throw new SfingeException("Deve essere implementato nella classe derivata");

        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle:Proroga")->find($id_proroga);
        $dati["proroga"] = $proroga;
        $isFsc = $this->container->get("gestore_richieste")->getGestore($proroga->getRichiesta()->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;


        return $this->generaPdfProroga($proroga, "@AttuazioneControllo/Proroghe/pdf.html.twig", $dati, $facsimile, $download);
    }

    public function inviaProroga($id_proroga) {
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle:Proroga")->find($id_proroga);
//		if ($this->isRichiestaDisabilitataInoltro()) {
//			throw new SfingeException("Il bando è chiuso e la richiesta non è più inviabile");
//		}
        if ($proroga->getStato()->uguale(StatoProroga::PROROGA_FIRMATA)) {
            $proroga->setDataInvio(new \DateTime());
            $proroga->setDataAvvioApprovata($proroga->getDataAvvioProgetto());
            $proroga->setDataFineApprovata($proroga->getDataFineProgetto());
            $this->container->get("sfinge.stati")->avanzaStato($proroga, StatoProroga::PROROGA_INVIATA_PA);

            /* Popolamento tabelle protocollazione
             * - richieste_protocollo
             * - richieste_protocollo_documenti
             */
            if ($this->container->getParameter("stacca_protocollo_al_volo")) {
                $this->container->get("docerinitprotocollazione")->setTabProtocollazioneProroga($proroga);
            }

            $this->getEm()->flush();

            return $this->addSuccesRedirect("Richiesta di proroga inviata correttamente", "dettaglio_proroga", array("id_proroga" => $proroga->getId()));
        }
        throw new SfingeException("Stato non valido per effettuare l'invio");
    }

    public function eliminaProroga($id_proroga) {
        $em = $this->getEm();
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle:Proroga")->find($id_proroga);
        $id_richiesta = $proroga->getRichiesta()->getId();

        if (in_array($proroga->getStato(), array(StatoProroga::PROROGA_INVIATA_PA, StatoProroga::PROROGA_PROTOCOLLATA))) {
            return $this->addErrorRedirect("L'operazione non è compatibile con lo stato della proroga.", "elenco_proroghe", array("id_richiesta" => $id_richiesta));
        }

        try {
            $em->remove($proroga);
            $em->flush();
            return $this->addSuccesRedirect("La proroga è stata correttamente eliminata", "elenco_proroghe", array("id_richiesta" => $id_richiesta));
        } catch (ResponseException $e) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "elenco_proroghe", array("id_richiesta" => $id_richiesta));
        }
    }

    public function isProrogaAggiungibile($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);

        foreach ($richiesta->getAttuazioneControllo()->getProroghe() as $proroga) {
            $stato = $proroga->getStato()->getCodice();
            if (!($stato == StatoProroga::PROROGA_INVIATA_PA || $stato == StatoProroga::PROROGA_PROTOCOLLATA)) {
                $this->addError("Impossibile creare una proroga perchè già presente una non inviata alla PA");
                return false;
            }
        }

        return true;
    }

    public function istruttoriaProroga($proroga) {
        $em = $this->getEm();
        $id_richiesta = $proroga->getRichiesta()->getId();
        $options = array();

        $options["url_indietro"] = $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $id_richiesta));
        $options["disabled"] = $proroga->getGestita();
        $options["tipo_proroga"] = $proroga->getTipoProroga();

        $form = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaProrogaType", $proroga, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $proroga->setGestita(true);
                    $proroga->setDataApprovazione(new \DateTime());
                    $em->flush();
                    return $this->addSuccesRedirect("La proroga è stata modificata correttamente", "riepilogo_proroghe", array("id_richiesta" => $id_richiesta));
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza. " . $e->getMessage());
                }
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();
        $dati["attuazione_controllo"] = $proroga->getAttuazioneControlloRichiesta();
        $dati["proroga"] = $proroga;

        return $this->render("AttuazioneControlloBundle:PA/Proroghe:istruttoriaProroga.html.twig", $dati);
    }

    public function riepilogoAtcProroga($proroga) {
        $dati["attuazione_controllo"] = $proroga->getAttuazioneControlloRichiesta();
        $dati["proroga"] = $proroga;
        $dati["menu"] = 'proroghe';

        return $this->render("AttuazioneControlloBundle:PA/Richieste:attuazioneRiepilogoProroga.html.twig", $dati);
    }

    /**
     * @param Proroga $proroga
     */
    public function elencoDocumentiProroga(Proroga $proroga) {
        $em = $this->getEm();
        $nuovoDocumento = new \AttuazioneControlloBundle\Entity\DocumentoProroga($proroga);
        $form = $this->createForm('AttuazioneControlloBundle\Form\DocumentazioneProrogaType', $nuovoDocumento, array(
            'url_indietro' => $this->generateUrl('dettaglio_proroga', array('id_proroga' => $proroga->getId())),
        ));
        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $em->beginTransaction();
                $nuovoDocumento->setDocumento($this->container->get('documenti')->carica($nuovoDocumento->getDocumento(), false, $proroga->getRichiesta()));
                $proroga->addDocumenti($nuovoDocumento);
                $em->persist($proroga);
                $em->flush();
                $em->commit();
                $this->addFlash('success', 'Operazione effettuata con successo');
            } catch (\Exception $e) {
                $em->rollBack();
                $this->container->get('monolog.logger.schema31')->error($e->getMessage(), array(
                    'ID Proroga' => $proroga->getId(),
                ));
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco proroghe", $this->generateUrl("elenco_proroghe", array("id_richiesta" => $proroga->getRichiesta()->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio proroga", $this->generateUrl("dettaglio_proroga", array("id_proroga" => $proroga->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Documenti proroga");

        $dati = array('form' => $form->createView(), 'proroga' => $proroga,);
        return $this->render('AttuazioneControlloBundle:Proroghe:elencoDocumenti.html.twig', $dati);
    }

}
