<?php

namespace AttuazioneControlloBundle\Service\Istruttoria;

use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo;
use BaseBundle\Exception\SfingeException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use AttuazioneControlloBundle\Form\Entity\ModificaVociImputazioneGiustificativo;
use AttuazioneControlloBundle\Form\Istruttoria\ModificaVociImputazioneGiustificativoType;
use AttuazioneControlloBundle\Service\IGestoreVociPianoCosto;
use AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaPagamento;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\VocePianoCosto;

class GestoreGiustificativiBase extends AGestoreGiustificativi {

    public function elencoGiustificativi($id_pagamento) {

        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getRichiesta();

        $dati = array("pagamento" => $pagamento, "menu" => "giustificativi");
        $dati['giustificativi'] = $em->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->getGiustificativiByPagamento($id_pagamento);
        $dati['ripresentazione_spesa'] = $pagamento->isSpesaRipresentabile();

        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($richiesta->getProcedura());
        $dati["rendicontazioneProceduraConfig"] = $rendicontazioneProceduraConfig;

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl("riepilogo_istruttoria_pagamento", array('id_pagamento' => $id_pagamento)));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:elencoGiustificativi.html.twig", $dati);
    }

    public function elencoGiustificativiContratto($id_pagamento, $id_contratto) {

        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $contratto = $em->getRepository("AttuazioneControlloBundle\Entity\Contratto")->find($id_contratto);
        $richiesta = $pagamento->getRichiesta();

        $dati = array("pagamento" => $pagamento, "contratto" => $contratto, "menu" => "giustificativi");
        $dati['giustificativi'] = $em->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->getGiustificativiByContratto($id_contratto);
        $dati['ripresentazione_spesa'] = $pagamento->isSpesaRipresentabile();

        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($richiesta->getProcedura());
        $dati["rendicontazioneProceduraConfig"] = $rendicontazioneProceduraConfig;

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl("riepilogo_istruttoria_pagamento", array('id_pagamento' => $id_pagamento)));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco contratti", $this->generateUrl("elenco_contratti_istruttoria", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:elencoGiustificativi.html.twig", $dati);
    }

    /**
     * @param GiustificativoPagamento $giustificativo
     * @return RedirectResponse|Response
     * @throws SfingeException
     */
    public function istruttoriaGiustificativo(GiustificativoPagamento $giustificativo) {
        $pagamento = $giustificativo->getPagamento();
        $richiesta = $pagamento->getRichiesta();
        $options = array();
        $options["url_modifica_imputazione"] = $this->generateUrl('modifica_voci_imputazione_giustificativo', array("id_giustificativo" => $giustificativo->getId()));
        $options["action"] = $this->generateUrl("istruttoria_giustificativo_pagamento", array("id_giustificativo" => $giustificativo->getId()));
        $options["disabled"] = $this->isDisabled($pagamento);
        $options['ripresentazione_spesa'] = $giustificativo->getPagamento()->isSpesaRipresentabile();

        $proponente = $giustificativo->getProponente();

        $form = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaGiustificativoPagamentoType", $giustificativo, $options);

        /*         * * ISTRUTTORIA ** */
        $istruttoria = $giustificativo->getIstruttoriaOggettoPagamento();
        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $giustificativo->setIstruttoriaOggettoPagamento($istruttoria);
        }
        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($richiesta->getProcedura());
        if ($rendicontazioneProceduraConfig->getSezioneContratti() == true) {
            $dati_form_istruttoria = array('url_indietro' => $this->generateUrl("elenco_giustificativi_contratto_istruttoria", array("id_pagamento" => $pagamento->getId(), "id_contratto" => $giustificativo->getContratto()->getId())));
        } else {
            $dati_form_istruttoria = array('url_indietro' => $this->generateUrl("elenco_giustificativi_istruttoria", array("id_pagamento" => $pagamento->getId())));
        }
        $dati_form_istruttoria['disabled'] = $this->isDisabled($pagamento);

        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $dati_form_istruttoria);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $form_istruttoria->handleRequest($request);

            // submit form rendicontato approvato
            if ($form->isSubmitted()) {

                foreach ($form->get("voci_piano_costo")->all() as $subform) {
                    /** @var VocePianoCostoGiustificativo $voce_piano_costo_giustificativo */
                    $voce_piano_costo_giustificativo = $subform->getData();

                    $importoRendicontato = $voce_piano_costo_giustificativo->getImporto();
                    $importoRendicontatoApprovato = $voce_piano_costo_giustificativo->getImportoApprovato();
                    $nota = $voce_piano_costo_giustificativo->getNota();

                    if ($importoRendicontato > 0) {
                        if ($importoRendicontatoApprovato > $importoRendicontato) {
                            $subform->get("importo_approvato")->addError(new FormError("L'importo approvato non può essere superiore a quello richiesto"));
                        }

                        if (($importoRendicontatoApprovato != $importoRendicontato) && empty($nota)) {
                            $subform->get("nota")->addError(new FormError("Se l'importo richiesto è diverso da quello approvato è necessario inserire una nota"));
                        }

                        /*if (($importoRendicontatoApprovato == $importoRendicontato) && !empty($nota)) {
                            $subform->get("nota")->addError(new FormError("La nota va inserita solo in caso di importo approvato diverso da quello richiesto"));
                        }*/
                    } else {
                        if ($importoRendicontatoApprovato != 0) {
                            $subform->get("importo_approvato")->addError(new FormError("In caso di importi negativi l'importo approvato può essere solo 0.00 "));
                        }
                    }

                    if ($pagamento->isSpesaRipresentabile()) {
                        $importoRendicontatoApprovatoPagamentoSuccessivo = $voce_piano_costo_giustificativo->getImportoPagamentoSuccessivo();
                        // Controllo che la somma degli importi approvati (importo approvato pagamento corrente e importo approvato pagamento successivo)
                        // non superi l'importo richiesto.
                        $importoApprovatoTotale = bcadd($importoRendicontatoApprovato, $importoRendicontatoApprovatoPagamentoSuccessivo, 2);
                        if (bccomp($importoApprovatoTotale, $importoRendicontato, 2) > 0) {
                            $subform->get("importo_pagamento_successivo")->addError(new FormError("La somma degli importi ammessi non può superare l'importo richiesto"));
                        }
                    }
                }

                if ($form->isValid()) {
                    $em = $this->getEm();

                    try {
                        $em->beginTransaction();
                        $giustificativo->calcolaImportoAmmesso();
                        $em->flush();

                        $esitoGenerali = $this->gestioneGiustificativoSpeseGenerali($pagamento, $proponente);
                        if ($esitoGenerali == false) {
                            throw new \Exception('Errore nel calcolo delle spese generali');
                        }

                        $em->flush();

                        $this->addFlash('success', "Salvataggio effettuato correttamente");
                        $em->commit();

                        return $this->redirect($dati_form_istruttoria['url_indietro']);
                    } catch (\Exception $e) {
                        $em->rollback();
                        $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                    }
                } else {
                    $this->addError('Attenzione, sono presenti degli errori');
                }
            } // fine form rendicontato approvato
            // form istruttoria
            elseif ($form_istruttoria->isSubmitted()) {
                if ($istruttoria->isIntegrazione() && (is_null($istruttoria->getNotaIntegrazione()) || $istruttoria->getNotaIntegrazione() == '')) {
                    $form_istruttoria->get('nota_integrazione')->addError(new \Symfony\Component\Form\FormError('Il campo note è obbligatorio in caso di integrazione'));
                }
                if ($form_istruttoria->isValid()) {
                    try {
                        $em = $this->getEm();
                        $em->persist($pagamento);
                        $em->flush();
                        return $this->addSuccesRedirect("Istruttoria salvata correttamente", 'istruttoria_giustificativo_pagamento', array("id_giustificativo" => $giustificativo->getId()));
                    } catch (\Exception $e) {
                        $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                    }
                }
            }// fine form istruttoria
        } // fine POST

        $dati["annualita"] = $this->container->get("gestore_voci_piano_costo_giustificativo")->getGestore($richiesta->getProcedura())->getAnnualitaRendicontazione($richiesta);
        $dati["form"] = $form->createView();
        $dati["form_istruttoria"] = $form_istruttoria->createView();
        $dati["giustificativo"] = $giustificativo;
        $dati["menu"] = "giustificativi";
        $dati["istruttoria"] = true;
        $dati["ripresentazione_spesa"] = $pagamento->isSpesaRipresentabile();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl("riepilogo_istruttoria_pagamento", array('id_pagamento' => $pagamento->getId())));
        if ($rendicontazioneProceduraConfig->getSezioneContratti() == true) {
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco contratti", $this->generateUrl("elenco_contratti_istruttoria", array("id_pagamento" => $pagamento->getId())));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi_contratto_istruttoria", array("id_pagamento" => $pagamento->getId(), "id_contratto" => $giustificativo->getContratto()->getId())));
        } else {
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi_istruttoria", array("id_pagamento" => $pagamento->getId())));
        }
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Istruttoria giustificativo");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Giustificativi:istruttoriaGiustificativo.html.twig", $dati);
    }

    public function dettaglioQuietanza($quietanza) {
        $giustificativo = $quietanza->getGiustificativoPagamento();
        $pagamento = $giustificativo->getPagamento();

        $dati["menu"] = "giustificativi";
        $dati["quietanza"] = $quietanza;
        $dati["pagamento"] = $pagamento;
        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($pagamento->getRichiesta()->getProcedura());
        $dati["rendicontazioneProceduraConfig"] = $rendicontazioneProceduraConfig;
        
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl("riepilogo_istruttoria_pagamento", array('id_pagamento' => $pagamento->getId())));
        if ($rendicontazioneProceduraConfig->getSezioneContratti() == true) {
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco contratti", $this->generateUrl("elenco_contratti_istruttoria", array("id_pagamento" => $pagamento->getId())));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi_contratto_istruttoria", array("id_pagamento" => $pagamento->getId(), "id_contratto" => $giustificativo->getContratto()->getId())));
        } else {
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrl("elenco_giustificativi_istruttoria", array("id_pagamento" => $pagamento->getId())));
        } 
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Istruttoria giustificativo", $this->generateUrl("istruttoria_giustificativo_pagamento", array("id_giustificativo" => $giustificativo->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio quietanza");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Pagamenti:dettaglioQuietanza.html.twig", $dati);
    }

    public function isDisabled($pagamento) {
        // se l'istruttoria è conclusa il pagamento è disabilitato a prescindere
        if ($pagamento->isIstruttoriaConclusa()) {
            return true;
        } else {
            // se sei una utenza in sola visualizzazione come certificatori o controllori loco deve essere tutto disabilitato
            if ($this->isGranted("ROLE_PAGAMENTI_READONLY")) {
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

    // avanzamento rendicontazione standard
    public function avanzamentoPianoCosti($pagamento) {

        $em = $this->getEm();

        $richiesta = $pagamento->getRichiesta();
        $proponente = $richiesta->getMandatario();

        $dati = array();
        $formContributoData = new \stdClass();
        $formContributoData->contributoComplessivoSpettante = $pagamento->getContributoComplessivoSpettante();
        $formContributoBuilder = $this->createFormBuilder($formContributoData, array('disabled' => $this->isDisabled($pagamento)));
        $formContributoBuilder->add('contributoComplessivoSpettante', \BaseBundle\Form\CommonType::importo, array(
            'required' => false,
            'label' => "Contributo complessivo spettante ({$pagamento->getModalitaPagamento()->getDescrizioneBreve()})"
        ));

        $formContributoBuilder->add('submit_contributo', \BaseBundle\Form\CommonType::submit, array('label' => 'Salva'));
        $formContributo = $formContributoBuilder->getForm();
        $formContributoVisibile = false;

        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($richiesta->getProcedura());
        if ($rendicontazioneProceduraConfig->getRendicontazioneMultiProponente() == false) {
            $formContributoVisibile = true;
        }

        if ($rendicontazioneProceduraConfig->getRendicontazioneMultiProponente()) {

            $proponenti = $em->getRepository('RichiesteBundle\Entity\VocePianoCosto')->getProponentiPianoCosti($richiesta->getId());

            // ha senso scegliere solo in caso di multi-proponenza in multi-pianocosto
            // altrimenti il piano costo è unico
            if (count($proponenti) > 1) {

                $formProponentiData = new \stdClass();
                $formProponentiData->proponente = $richiesta->getMandatario();

                $formProponentiBuilder = $this->createFormBuilder($formProponentiData);
                $formProponentiBuilder->add('proponente', \BaseBundle\Form\CommonType::entity, array(
                    'class' => 'RichiesteBundle:Proponente',
                    'choice_label' => function ($proponente) {
                        return $proponente;
                    },
                    'choices' => $proponenti,
                    'required' => false,
                    'placeholder' => 'Tutti',
                ));

                $formProponentiBuilder->add('submit', \BaseBundle\Form\CommonType::submit, array('label' => 'vai'));
                $formProponenti = $formProponentiBuilder->getForm();
            } else {
                $formContributoVisibile = true;
            }
        }

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {

            /**
             * form relativo all'eventuale selezione di un proponente 
             * il proponente si può selezionare solo se è abilitata la rendicontazione in multiproponenza e siamo in multipianocosto per il bando
             */
            if ($rendicontazioneProceduraConfig->getRendicontazioneMultiProponente()) {
                if (isset($formProponenti)) {
                    $formProponenti->handleRequest($request);

                    if ($formProponenti->isSubmitted() && $formProponenti->get('submit')->isClicked()) {
                        $proponente = $formProponentiData->proponente;
                        //rendiamo visibile il form contributo solo nel casso si selezione "Tutti"
                        //il tutto solo nel caso di RendicontazioneMultiProponente == true altrimenti è sempre visibile o così si spera
                        if (is_null($proponente)) {
                            $formContributoVisibile = true;
                        } else {
                            $formContributoVisibile = false;
                        }
                    } else {
                        // form relativo all'inserimento del contributo complessivo spettante
                        $formContributo->handleRequest($request);
                        if ($formContributo->isSubmitted() && $formContributo->get('submit_contributo')->isClicked()) {
                            if (is_null($formContributoData->contributoComplessivoSpettante)) {
                                $formContributo->get('contributoComplessivoSpettante')->addError(new \Symfony\Component\Form\FormError('Il campo non può essere vuoto'));
                            }
                            if ($formContributo->isValid()) {
                                $pagamento->setContributoComplessivoSpettante($formContributoData->contributoComplessivoSpettante);
                                try {
                                    $em->flush();
                                    $this->addFlash('success', 'Dati salvati con successo');
                                } catch (\Exception $ex) {
                                    $this->addFlash('error', 'Si è verificato un errore durante il salvataggio dei dati');
                                }
                            }
                        }
                    }
                } else {
                    $formContributo->handleRequest($request);
                    if ($formContributo->isSubmitted() && $formContributo->get('submit_contributo')->isClicked()) {
                        if (is_null($formContributoData->contributoComplessivoSpettante)) {
                            $formContributo->get('contributoComplessivoSpettante')->addError(new \Symfony\Component\Form\FormError('Il campo non può essere vuoto'));
                        }
                        if ($formContributo->isValid()) {
                            $pagamento->setContributoComplessivoSpettante($formContributoData->contributoComplessivoSpettante);
                            try {
                                $em->flush();
                                $this->addFlash('success', 'Dati salvati con successo');
                            } catch (\Exception $ex) {
                                $this->addFlash('error', 'Si è verificato un errore durante il salvataggio dei dati');
                            }
                        }
                    }
                }
            } else {
                //DEVO replicare il tutto perchè la multiproponenza può non essere multipianocosto
                //purtroppo non hanno ancora fatto pace con i loro neuroni
                $formContributo->handleRequest($request);
                if ($formContributo->isSubmitted() && $formContributo->get('submit_contributo')->isClicked()) {
                    if (is_null($formContributoData->contributoComplessivoSpettante)) {
                        $formContributo->get('contributoComplessivoSpettante')->addError(new \Symfony\Component\Form\FormError('Il campo non può essere vuoto'));
                    }
                    if ($formContributo->isValid()) {
                        $pagamento->setContributoComplessivoSpettante($formContributoData->contributoComplessivoSpettante);
                        try {
                            $em->flush();
                            $this->addFlash('success', 'Dati salvati con successo');
                        } catch (\Exception $ex) {
                            $this->addFlash('error', 'Si è verificato un errore durante il salvataggio dei dati');
                        }
                    }
                }
            }
        }

        if ($pagamento->isInviatoRegione()) {
            $avanzamento = $this->calcolaAvanzamentoPianoCosti($richiesta, $proponente, $pagamento);
        } else {
            $avanzamento = $this->calcolaAvanzamentoPianoCosti($richiesta, $proponente);
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl('riepilogo_istruttoria_pagamento', array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Avanzamento piano costi");


        $dati["avanzamento"] = $avanzamento;
        $dati["pagamento"] = $pagamento;
        $dati["richiesta"] = $richiesta;
        $dati["proponente"] = $proponente;

        $dati["rendicontazioneProceduraConfig"] = $rendicontazioneProceduraConfig;
        $dati["formContributoVisibile"] = $formContributoVisibile;

        $dati["menu"] = "rendicontazione";


        //commento per testatre la modifica della visibilità delle variazioni
        //$variazione = $pagamento->getAttuazioneControlloRichiesta()->getUltimaVariazioneApprovata();	
        $variazione = $pagamento->getAttuazioneControlloRichiesta()->getUltimaVariazionePianoCostiPA($pagamento);
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
        $totaleRendicontatoAmmesso = 0.0;

        $gestoreIstruttoriaPagamenti = $this->container->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura());

        $atc = $pagamento->getAttuazioneControlloRichiesta();
        $datiPagamenti = array();

        // atto incompiuto
        //$ordinePagamentoDiRiferimento = $pagamento->getModalitaPagamento()->getOrdineCronologico();

        $totaleRendicontatoTot = 0.0;
        $totaleRendicontatoAmmessoTot = 0.0;
        $totaleRendicontatoNonAmmessoTot = 0.0;
        $contributoErogabileTot = 0.0;

        // gli istruttori devono vedere solo cose relative a pagamenti inviati o protocollati
        $pagamentiInviati = array();
        $pagamenti = $atc->getPagamenti();

        // orendiamo solo pagamenti inviati e che non siano degli anticipi
        foreach ($pagamenti as $p) {
            if ($p->isInviato() && !$p->getModalitaPagamento()->isAnticipo()) {
                $pagamentiInviati[] = $p;
            }
        }

        $dati["pagamenti"] = $pagamentiInviati;

        foreach ($pagamentiInviati as $pagamento) {

            $modalitaPagamento = $pagamento->getModalitaPagamento();

            // nel riepilogo mostriamo solo il pagamento corrente e quelli cronologicamente precedenti
            /* al momento lo decommento..devo capire che cosa vogliono
              if($modalitaPagamento->getOrdineCronologico() > $ordinePagamentoDiRiferimento){
              //skip
              continue;
              }
             */
            $totaleRendicontato = $pagamento->getImportoTotaleRichiesto();
            $totaleRendicontatoAmmesso = $pagamento->getImportoTotaleRichiestoAmmesso();
            $totaleRendicontatoNonAmmesso = $pagamento->calcolaImportoNonAmmesso();
            /*
             * In caso di vecchi pagamenti non esiste a cl quindi provo a prendere il valore dal pagamento
             */
            $contributo = $gestoreIstruttoriaPagamenti->getValoreFromChecklist($pagamento, 'CONTRIBUTO_EROGABILE');
            /* if (is_null($contributo)) {
              $contributo = $pagamento->getContributoComplessivoSpettante();
              } */
            $contributoErogabile = (float) $contributo;
            $totaleRendicontatoTot += $totaleRendicontato;
            $totaleRendicontatoAmmessoTot += $totaleRendicontatoAmmesso;
            $totaleRendicontatoNonAmmessoTot += $totaleRendicontatoNonAmmesso;
            $contributoErogabileTot += $contributoErogabile;

            $datiPagamenti[$modalitaPagamento->getDescrizioneBreve()] = array(
                "importoRendicontato" => $totaleRendicontato,
                "importoRendicontatoAmmesso" => $totaleRendicontatoAmmesso,
                "importoRendicontatoNonAmmesso" => $totaleRendicontatoNonAmmesso,
                "contributoErogabile" => $contributoErogabile
            );
        }

        // il totale ha senso solo se ci sono più pagamenti
        if (count($pagamentiInviati) > 1) {
            $datiPagamenti['Totale'] = array(
                "importoRendicontato" => $totaleRendicontatoTot,
                "importoRendicontatoAmmesso" => $totaleRendicontatoAmmessoTot,
                "importoRendicontatoNonAmmesso" => $totaleRendicontatoNonAmmessoTot,
                "contributoErogabile" => $contributoErogabileTot
            );
        }

        $dati["datiPagamenti"] = $datiPagamenti;

        if ($rendicontazioneProceduraConfig->getRendicontazioneMultiProponente()) {
            if (isset($formProponenti)) {
                $dati["formProponenti"] = $formProponenti->createView();
            }
        }

        $dati["formContributo"] = $formContributo->createView();

        return $this->render("AttuazioneControlloBundle:Istruttoria\Giustificativi:avanzamentoPianoCosti.html.twig", $dati);
    }

    // calcolo standard generico
    public function calcolaAvanzamentoPianoCosti(Richiesta $richiesta, ?Proponente $proponente, ?Pagamento $pagamento = null) {

        $atc = $richiesta->getAttuazioneControllo();
        $pagamenti = $atc->getPagamenti();

        $avanzamentoProponenti = array();

        /**
         * se esiste una variazione approvata leggo il suo piano costi
         * altrimenti devo recuperare quello approvato in istruttoria
         */
        if (is_null($pagamento)) {
            $variazione = $atc->getUltimaVariazioneApprovata();
        } else {
            $variazione = $atc->getUltimaVariazionePianoCostiPA($pagamento);
        }
        if (is_null($variazione)) {
            $oggettiVocePianoCosto = $richiesta->getVociPianoCosto()->map(function(VocePianoCosto $voce) {
                return $voce->getIstruttoria();
            });
        } else {
            $oggettiVocePianoCosto = $variazione->getVociPianoCosto();
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
                if (!$pagamento->isInviatoPa() && !$pagamento->isProtocollato()) {
                    // gli istruttori devono vedere solo cose relative a pagamenti inviati o protocollati
                    continue;
                }

                // per gli anticipi non c'è rendicontato..li escludiamo
                if ($pagamento->getModalitaPagamento()->isAnticipo()) {
                    continue;
                }

                $rendicontatoPagamenti[$pagamento->getId()] = array(
                    'modalitaPagamento' => $pagamento->getModalitaPagamento()->getCodice(),
                    'importoRendicontato' => 0.0,
                    'importoRendicontatoAmmesso' => 0.0,
                    'importoRendicontatoNonAmmesso' => 0.0
                );
            }

            // ci deve essere pure il totale su tutti i pagamenti
            $rendicontatoPagamenti['totalePagamenti'] = array(
                'modalitaPagamento' => 'TOTALE',
                'importoRendicontato' => 0.0,
                'importoRendicontatoAmmesso' => 0.0,
                'importoRendicontatoNonAmmesso' => 0.0
            );

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

                    if (!$pagamento->isInviatoPa() && !$pagamento->isProtocollato()) {
                        // gli istruttori devono vedere solo cose relative a pagamenti inviati o protocollati
                        continue;
                    }

                    $importoRendicontato = $voceGiustificativo->getImporto();
                    $importoRendicontatoAmmesso = $voceGiustificativo->getImportoApprovato();
                    $importoRendicontatoNonAmmesso = $voceGiustificativo->calcolaImportoNonAmmesso();

                    $rendicontatoPagamenti[$pagamentoId]['importoRendicontato'] += $importoRendicontato;
                    $rendicontatoPagamenti[$pagamentoId]['importoRendicontatoAmmesso'] += $importoRendicontatoAmmesso;
                    $rendicontatoPagamenti[$pagamentoId]['importoRendicontatoNonAmmesso'] += $importoRendicontatoNonAmmesso;

                    //in base a quanto calcolato per ogni pagamento, calcolo il totalone su tutti i pagamenti
                    $rendicontatoPagamenti['totalePagamenti']['importoRendicontato'] += $importoRendicontato;
                    $rendicontatoPagamenti['totalePagamenti']['importoRendicontatoAmmesso'] += $importoRendicontatoAmmesso;
                    $rendicontatoPagamenti['totalePagamenti']['importoRendicontatoNonAmmesso'] += $importoRendicontatoNonAmmesso;
                } catch (\Exception $e) {
                    // il try catch serve per gestire la cancellazione logica, se l'oggetto è cancellato a quanto pare viene lanciata un'eccezione
                    // mi attengo al piano e skippo
                }
            }


            /**
             * tutto questo perchè ho dovuto aggiungerer la chiave totalePagamenti non prevista..
             * inizialmente c'erano solo gli id dei pagamenti..ma vogliono il totalone
             * per costruzione deve essere ordinato in ordine crescente di idPagamento ed il totalePagamenti in ultima posizione
             * 
             * avrei potuto chiamarla 9999999999totalePagamenti ma mi è sembrato davvero imbarazzante
             */
            uksort($rendicontatoPagamenti, function($a, $b) {
                if (is_string($a)) {
                    return 69;
                } elseif (is_string($b)) {
                    return -69;
                } else {
                    return $a > $b ? 69 : -69;
                }
            });

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

                // init
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
                            $avanzamentoTotale[$sezioneId][$ordinamento]['rendicontatoPagamenti'][$pagamentoId]['importoRendicontatoNonAmmesso'] += $rendicontatoPagamento['importoRendicontatoNonAmmesso'];
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
                        $totaleSezioneRendicontatoPagamenti[$pagamentoId]['importoRendicontatoNonAmmesso'] += $rendicontatoPagamento['importoRendicontatoNonAmmesso'];
                    }
                } else {
                    $avanzamentoDaMostrare[$sezioneId][$ordinamento]['rendicontatoPagamenti'] = $totaleSezioneRendicontatoPagamenti;
                }
            }
        }

        return $avanzamentoDaMostrare;
    }

    protected function getRendicontazioneProceduraConfig($procedura) {

        $rendicontazioneProceduraConfig = $procedura->getRendicontazioneProceduraConfig();
        // fallback..default
        if (is_null($rendicontazioneProceduraConfig)) {
            $rendicontazioneProceduraConfig = new \AttuazioneControlloBundle\Entity\RendicontazioneProceduraConfig();
        }

        return $rendicontazioneProceduraConfig;
    }

    public function gestioneGiustificativoSpeseGenerali($pagamento, $proponente = null) {

        $procedura = $pagamento->getProcedura();
        $richiesta = $pagamento->getRichiesta();

        /*
         * Controllo se per la procedura sono previste spese generali, se non ci sono ritorno true
         */
        $config = $this->getRendicontazioneProceduraConfig($procedura);
        if (!$config->hasSpeseGenerali()) {
            return true;
        }

        // recupero il piano costo relativo alle spese generali
        $vociSpeseGenerali = $this->container->get("gestore_piano_costo")->getGestore($procedura)->getVociSpeseGenerali($pagamento);


        /**
         * Bisogna tenere in considerazione che
         * 1) se siamo in singola proponenza, il proponente del giustificativo è NULL e le voci piano costo sono associate al mandatario
         * 2) se siamo in multiproponenza ma singolo piano costi, il proponente del giustificativo è settato(con uno dei proponenti) ma le voce di piano costo sono associate al mandatario
         * 3) se siamo in multiproponenza multipianocosto, il proponente è settato (con uno dei proponenti) e le voci del piano costo sono associate al proponente del giustificativo
         * 
         * per far tornare in conti dobbiamo assumere come proponente il mandatario per i casi 1 e 3
         * e tenere quello settato nel giustificativo
         */
        if (is_null($proponente)) {//1
            $proponenteVocePianoCosto = $richiesta->getMandatario();
        } else {
            if ($procedura->getMultiPianoCosto()) {//2
                $proponenteVocePianoCosto = $proponente;
            } else {//3
                $proponenteVocePianoCosto = $richiesta->getMandatario();
            }
        }

        foreach ($vociSpeseGenerali as $voce) {

            $codiceSezione = $voce->getSezionePianoCosto()->getCodice();

            // recupero il giustificativo relativo alle spese generali creato in automatico
            $giustificativiGenerali = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo")->getGiustificativoDaVoce($pagamento, $voce->getCodice(), $codiceSezione, $proponenteVocePianoCosto);
            if (count($giustificativiGenerali) == 0) {
                return true;
            }
            $giustificativoGenerali = $giustificativiGenerali[0];

            // calcolo l'importo sul rendicontato (fino a questo momento) applicando la formula definita per lo specifico bando 
            // la necessità di passare il proponente deriva dal fatto che in caso di multipianocosto bisogna creare l'imputazione delle spese generali
            // sullo specifico piano costi associato al proponente specificato..altrimenti non funziona un gazzu
            $importi = $this->container->get("gestore_piano_costo")->getGestore($procedura)->calcolaImportoSpeseGenerali($pagamento, $proponenteVocePianoCosto, $codiceSezione);

            $vocePCGiustificativoVoceGenerali = $giustificativoGenerali->getVociPianoCosto()->first();
            $vocePCGiustificativoVoceGenerali->setImportoApprovato($importi['importo_approvato']);

            $giustificativoGenerali->setImportoApprovato($importi['importo_approvato']);

            try {
                $this->getEm()->persist($vocePCGiustificativoVoceGenerali);
            } catch (\Exception $e) {
                $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                return false;
            }
        }

        return true;
    }

    public function esportaGiustificativi($pagamento) {

        $excelService = $this->container->get('phpoffice.spreadsheet');
        $phpExcelObject = $excelService->getSpreadSheet();
        $phpExcelObject->getProperties()->setCreator("Sfinge 2104-2020")
                ->setLastModifiedBy("Sfinge 2104-2020")
                ->setTitle("Esportazione giustificativi")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();

        //Creo la riga con i nomi delle colonne
        $nomiColonne = array(
            'Voce piano costi',
            'Tipologia giustificativo',
            'Fornitore / Dipendente',
            'Descrizione',
            'Numero fattura',
            'Importo fattura',
            'Data fattura',
            'Importo rendicontato',
            'Importo su cui si chiede il contributo',
            'Importo ammesso',
            'Importo ammesso pagamento successivo',
            'Spesa attribuita (proponente)',
            'Spesa non ammessa',
            'Motivazione di non ammissibilità',
            'Nota alla richiesta di integrazione'
        );

        $activeSheet->fromArray($nomiColonne);
        $riga = 1;

        //Popolo l'estrazione
        $id_pagamento = $pagamento->getId();
        $giustificativi = $this->getEm()->getRepository(GiustificativoPagamento::class)->getGiustificativiByPagamento($id_pagamento);
        /** @var GiustificativoPagamento $g */
        foreach ($giustificativi as $g) {

            $vociPianoCosti = $g->getVociPianoCosto();

            //Per ogni voce di costo associata al giustificativo, genero una nuova riga
            /** @var VocePianoCostoGiustificativo $vocePianoCosti */
            foreach ($vociPianoCosti as $vocePianoCosti) {

                $fornitore_dipendente = $g->getDenominazioneFornitore();
                if (!is_null($fornitore_dipendente)) {
                    $fornitore_dipendente .= $g->getCodiceFiscaleFornitore() ? (' - ' . $g->getCodiceFiscaleFornitore()) : '';
                } elseif (!is_null($g->getEstensione()) && !is_null($g->getEstensione()->getNome())) {
                    $fornitore_dipendente = $g->getEstensione()->getNome() . ' ' . $g->getEstensione()->getCognome();
                } else {
                    $fornitore_dipendente = '-';
                }
                $proponenteGiustificativo = $g->getProponente();

                $valori = array(
                    $vocePianoCosti->getVocePianoCosto()->getPianoCosto()->getTitolo(), //Voce Piano Costi
                    $g->getTipologiaGiustificativo()->getDescrizione(), // Tipologia giustificativo (personale/fattura ecc..)
                    $fornitore_dipendente, //Fornitore / Dipendente
                    !is_null($g->getDescrizioneGiustificativo()) ? $g->getDescrizioneGiustificativo() : '-', //Descrizione
                    !is_null($g->getNumeroGiustificativo()) ? $g->getNumeroGiustificativo() : '-', //Numero fattura
                    !is_null($g->getImportoGiustificativo()) ? \floatval($g->getImportoGiustificativo()) : null, //Importo fattura
                    !is_null($g->getDataGiustificativo()) ? date_format($g->getDataGiustificativo(), 'd/m/Y') : null, //Data fattura
                   
                    \floatval($vocePianoCosti->getImporto()), //Importo rendicontato (sulla singola voce di costo)
                    \floatval($g->getImportoRichiesto()), //Importo su cui si chiede il contributo
                    \floatval($vocePianoCosti->getImportoApprovato()), //Importo ammesso
                    \floatval($vocePianoCosti->getImportoPagamentoSuccessivo()), //Importo ammesso pagamento successivo
                    !is_null($proponenteGiustificativo) ? $proponenteGiustificativo->getSoggetto()->getDenominazione() : null, //'Spesa attribuita (proponente)',
                    \floatval($vocePianoCosti->calcolaImportoNonAmmesso()), //'Spesa non ammessa'
                    !is_null($vocePianoCosti->getNota()) ? $vocePianoCosti->getNota() : '-', //'Motivazione di non ammissibilità',
                    $g->getIstruttoriaOggettoPagamento() ? $g->getIstruttoriaOggettoPagamento()->getNotaIntegrazione() : '-'//'Nota alla richiesta di integrazione'
                );

                $riga++;
                $activeSheet->fromArray($valori, null, "A$riga");
            }
            // Merge celle per giustificativo
            $primaRigaVocePerGiustificativo = $riga - $vociPianoCosti->count() + 1;
            if ($riga > $primaRigaVocePerGiustificativo) {
                $activeSheet->mergeCells("I$primaRigaVocePerGiustificativo:I$riga");
            }
        }

        if ($riga > 1) {
            $activeSheet->getStyle("F2:F$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            $activeSheet->getStyle("H2:K$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            $activeSheet->getStyle("M2:M$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            $activeSheet->getStyle("E2:E$riga")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        }

        $response = $excelService->createResponse($phpExcelObject, 'Esportazione_Giustificativi_Pagamento_' . $pagamento->getId() . '.xlsx');
        return $response;
    }

    protected function setFormatoCelle($sheet, $riga, $colonne, $formato) {
        foreach (\str_split($colonne) as $colonna) {
            $sheet->getStyle((\strtoupper($colonna)) . $riga)
                    ->getNumberFormat()
                    ->setFormatCode($formato);
        }
    }

    public function modificaVociImputazione(GiustificativoPagamento $giustificativo): Response {
        if ($this->isDisabled($giustificativo->getPagamento())) {
            throw new \Exception("non autorizzato");
        }
        $voci = new ModificaVociImputazioneGiustificativo($giustificativo);
        $indietro = $this->generateUrl('istruttoria_giustificativo_pagamento', [
            'id_giustificativo' => $giustificativo->getId(),
        ]);
        $options = [
            'url_indietro' => $indietro,
        ];
        $form = $this->createForm(ModificaVociImputazioneGiustificativoType::class, $voci, $options);
        $form->handleRequest($this->getCurrentRequest());

        if ($giustificativo->getPagamento()->getDocumentiIstruttoria()->filter(function(DocumentoIstruttoriaPagamento $doc) {
                    return $doc->getDocumentoFile()->getTipologiaDocumento()->getCodice() == 'MOD_IMP_FATTURE';
                })->isEmpty()) {
            $form->addError(new FormError("E' necessario allegare documentazione modifica imputazione fatture"));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEm();
            try {
                $em->beginTransaction();
                //Elimino presenti
                foreach ($giustificativo->getVociPianoCosto() as $voce) {
                    $giustificativo->removeVociPianoCosto($voce);
                    $em->remove($voce);
                }

                //Aggiungo nuove
                foreach ($voci->getVoci() as $voce) {
                    $giustificativo->addVociPianoCosto($voce);
                    $em->persist($voce);
                }
                $em->flush();

                /** @var IGestoreVociPianoCosto $gestoreVociGiustificativo */
                $gestoreVociGiustificativo = $this->container->get('gestore_voci_piano_costo_giustificativo')->getGestore($giustificativo->getProcedura());
                $esitoOk = $gestoreVociGiustificativo->gestioneGiustificativoSpeseGenerali($giustificativo->getPagamento(), $giustificativo->getProponente());
                if (!$esitoOk) {
                    throw new SfingeException("Errore nel calcolo delle spese generali");
                }

                //Ricalcolo totale richiesto e dell'ammesso del giustificativo e del suo pagamento
                $giustificativo->calcolaImportoRichiesto();
                $giustificativo->calcolaImportoAmmesso();

                $em->flush();
                $em->commit();
                $this->addSuccess('Operazione effettuata con successo');

                return $this->redirect($indietro);
            } catch (SfingeException $e) {
                $em->rollback();
                $this->addError($e->getMessage());
            } catch (\Exception $e) {
                $em->rollback();
                $this->container->get('logger')->error($e->getTraceAsString());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }
        $mv = [
            'form' => $form->createView(),
            'totaleGiustificativo' => $giustificativo->getTotaleImputato(),
        ];
        return $this->render("AttuazioneControlloBundle:Istruttoria/Giustificativi:modificaVociImputazione.html.twig", $mv);
    }

    public function calcolaAvanzamentoPianoCostiEsito(Richiesta $richiesta, ?Proponente $proponente, ?Pagamento $pagamento = null) {

        $atc = $richiesta->getAttuazioneControllo();
        $pagamenti = $this->getPagamentiDaAvanzamento($richiesta, $pagamento);

        $avanzamentoProponenti = array();

        /**
         * se esiste una variazione approvata leggo il suo piano costi
         * altrimenti devo recuperare quello approvato in istruttoria
         */
        if (is_null($pagamento)) {
            $variazione = $atc->getUltimaVariazioneApprovata();
        } else {
            $variazione = $atc->getUltimaVariazionePianoCostiPA($pagamento);
        }
        if (is_null($variazione)) {
            $oggettiVocePianoCosto = $richiesta->getVociPianoCosto()->map(function(VocePianoCosto $voce) {
                return $voce->getIstruttoria();
            });
        } else {
            $oggettiVocePianoCosto = $variazione->getVociPianoCosto();
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
                if (!$pagamento->isInviatoPa() && !$pagamento->isProtocollato()) {
                    // gli istruttori devono vedere solo cose relative a pagamenti inviati o protocollati
                    continue;
                }

                // per gli anticipi non c'è rendicontato..li escludiamo
                if ($pagamento->getModalitaPagamento()->isAnticipo()) {
                    continue;
                }

                $rendicontatoPagamenti[$pagamento->getId()] = array(
                    'modalitaPagamento' => $pagamento->getModalitaPagamento()->getCodice(),
                    'importoRendicontato' => 0.0,
                    'importoRendicontatoAmmesso' => 0.0,
                    'importoRendicontatoNonAmmesso' => 0.0
                );
            }

            // ci deve essere pure il totale su tutti i pagamenti
            $rendicontatoPagamenti['totalePagamenti'] = array(
                'modalitaPagamento' => 'TOTALE',
                'importoRendicontato' => 0.0,
                'importoRendicontatoAmmesso' => 0.0,
                'importoRendicontatoNonAmmesso' => 0.0
            );

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

                    if (!$pagamento->isInviatoPa() && !$pagamento->isProtocollato()) {
                        // gli istruttori devono vedere solo cose relative a pagamenti inviati o protocollati
                        continue;
                    }

                    $importoRendicontato = $voceGiustificativo->getImporto();
                    $importoRendicontatoAmmesso = $voceGiustificativo->getImportoApprovato();
                    $importoRendicontatoNonAmmesso = $voceGiustificativo->calcolaImportoNonAmmesso();

                    $rendicontatoPagamenti[$pagamentoId]['importoRendicontato'] += $importoRendicontato;
                    $rendicontatoPagamenti[$pagamentoId]['importoRendicontatoAmmesso'] += $importoRendicontatoAmmesso;
                    $rendicontatoPagamenti[$pagamentoId]['importoRendicontatoNonAmmesso'] += $importoRendicontatoNonAmmesso;

                    //in base a quanto calcolato per ogni pagamento, calcolo il totalone su tutti i pagamenti
                    $rendicontatoPagamenti['totalePagamenti']['importoRendicontato'] += $importoRendicontato;
                    $rendicontatoPagamenti['totalePagamenti']['importoRendicontatoAmmesso'] += $importoRendicontatoAmmesso;
                    $rendicontatoPagamenti['totalePagamenti']['importoRendicontatoNonAmmesso'] += $importoRendicontatoNonAmmesso;
                } catch (\Exception $e) {
                    // il try catch serve per gestire la cancellazione logica, se l'oggetto è cancellato a quanto pare viene lanciata un'eccezione
                    // mi attengo al piano e skippo
                }
            }


            /**
             * tutto questo perchè ho dovuto aggiungerer la chiave totalePagamenti non prevista..
             * inizialmente c'erano solo gli id dei pagamenti..ma vogliono il totalone
             * per costruzione deve essere ordinato in ordine crescente di idPagamento ed il totalePagamenti in ultima posizione
             * 
             */
            uksort($rendicontatoPagamenti, function($a, $b) {
                if (is_string($a)) {
                    return 69;
                } elseif (is_string($b)) {
                    return -69;
                } else {
                    return $a > $b ? 69 : -69;
                }
            });

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

                // init
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
                            $avanzamentoTotale[$sezioneId][$ordinamento]['rendicontatoPagamenti'][$pagamentoId]['importoRendicontatoNonAmmesso'] += $rendicontatoPagamento['importoRendicontatoNonAmmesso'];
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
                        $totaleSezioneRendicontatoPagamenti[$pagamentoId]['importoRendicontatoNonAmmesso'] += $rendicontatoPagamento['importoRendicontatoNonAmmesso'];
                    }
                } else {
                    $avanzamentoDaMostrare[$sezioneId][$ordinamento]['rendicontatoPagamenti'] = $totaleSezioneRendicontatoPagamenti;
                }
            }
        }

        return $avanzamentoDaMostrare;
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

    public function elencoContratti($id_pagamento) {

        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        if (is_null($pagamento)) {
            throw new \Symfony\Component\Routing\Exception\ResourceNotFoundException('Pagamento non trovato');
        }

        $dati = array("pagamento" => $pagamento, "istruttoria" => true);
        $dati["menu"] = "contratti";

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl("riepilogo_istruttoria_pagamento", array("id_pagamento" => $id_pagamento)));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco contratti");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Contratti:elencoContratti.html.twig", $dati);
    }

    public function visualizzaContratto($id_contratto) {

        $em = $this->getEm();
        $contratto = $em->getRepository("AttuazioneControlloBundle\Entity\Contratto")->find($id_contratto);
        if (is_null($contratto)) {
            throw new \Symfony\Component\Routing\Exception\ResourceNotFoundException('Pagamento non trovato');
        }

        $pagamento = $contratto->getPagamento();

        $url_indietro = $this->generateUrl("elenco_contratti_istruttoria", array("id_pagamento" => $pagamento->getId()));
        $tipologieFornitore = $em->getRepository("AttuazioneControlloBundle\Entity\TipologiaFornitore")->findByCodice(array('RI', 'UN', 'LAB', 'CO'));

        $dati = array();
        $dati["url_indietro"] = $url_indietro;
        $dati["disabled"] = true;
        $dati["tipologieFornitore"] = $tipologieFornitore;
        $form = $this->createForm("AttuazioneControlloBundle\Form\ContrattoType", $contratto, $dati);
        $dati["form"] = $form->createView();

        if ($this->isGranted("ROLE_PAGAMENTI_READONLY")) {
            $disabilita = true;
        } else {
            $disabilita = false;
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl("riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco contratti", $this->generateUrl("elenco_contratti_istruttoria", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Visualizza contratto");

        return $this->render("AttuazioneControlloBundle:Istruttoria/Contratti:contratto.html.twig", $dati);
    }

    public function istruttoriaDocumentiContratto($id_contratto, $id_pagamento) {

        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle:Pagamento")->find($id_pagamento);
        $contratto = $em->getRepository("AttuazioneControlloBundle\Entity\Contratto")->find($id_contratto);
        if (is_null($pagamento)) {
            throw new SfingeException("Pagamento non trovato");
        }

        if (is_null($contratto)) {
            throw new SfingeException("Oggetto non trovato");
        }

        $richiesta = $pagamento->getRichiesta();
        $type = "AttuazioneControlloBundle\Form\Bando_8\AmministrativiConsulenzeType";

        $visualizzaDati = true;

        $documento_estensione = new \AttuazioneControlloBundle\Entity\DocumentoContratto();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();
        $documento_estensione->setDocumentoFile($documento_file);

        $documenti_caricati = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoContratto")->findBy(array("contratto" => $contratto));

        $opzioni['url_indietro'] = $this->generateUrl("elenco_contratti_istruttoria", array("id_pagamento" => $pagamento->getId()));
        $opzioni["disabled"] = true;

        $listaTipi = $this->getTipiDocumentiContratto();
        $opzioni_form['lista_tipi'] = $listaTipi;

        $form_doc = $this->createForm('AttuazioneControlloBundle\Form\Bando_8\DocumentiAmministrativiType', $documento_estensione, $opzioni_form);

        $form_dati = $this->createForm($type, $contratto, $opzioni);

        $istruttoria = $contratto->getIstruttoriaOggettoPagamento();
        $url_indietro = $this->generateUrl("riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId()));


        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $contratto->setIstruttoriaOggettoPagamento($istruttoria);
        }
        $dati_form_istruttoria = array('url_indietro' => $url_indietro);
        $dati_form_istruttoria["disabled"] = $this->isDisabled($pagamento);
        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $dati_form_istruttoria);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form_istruttoria->handleRequest($request);
            if ($form_istruttoria->isValid()) {
                try {
                    $em = $this->getEm();
                    $em->persist($pagamento);
                    $em->flush();
                    return $this->addSuccesRedirect("Istruttoria documenti prototipo salvata correttamente", 'elenco_contratti_istruttoria', array("id_pagamento" => $pagamento->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        /*         * * FINE ISTRUTTORIA ** */


        $dati = array(
            "id_richiesta" => $richiesta->getId(),
            "pagamento" => $pagamento,
            "form_istruttoria" => $form_istruttoria->createView(),
            "form_dati" => $form_dati->createView(),
            "documenti_caricati" => $documenti_caricati,
            "is_richiesta_disabilitata" => true,
            "visualizza_dati" => $visualizzaDati,
            "istruttoria" => true,
            "menu" => "documenti_amministrativi");

        $dati["form_doc"] = $form_doc->createView();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl("riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco contratti", $this->generateUrl("elenco_contratti_istruttoria", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Documenti");

        $response = $this->render('AttuazioneControlloBundle:Istruttoria/Contratti:elencoDocumenti.html.twig', $dati);

        return $response;
    }

    public function istruttoriaSingoloDocumentoContratto($id_documento_contratto, $id_pagamento) {

        $em = $this->getEm();
        $documento = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoContratto")->find($id_documento_contratto);
        $pagamento = $em->getRepository("AttuazioneControlloBundle:Pagamento")->find($id_pagamento);
        $contratto = $documento->getContratto();

        $istruttoria = $documento->getIstruttoriaOggettoPagamento();
        if (is_null($istruttoria)) {
            $istruttoria = new \AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento();
            $documento->setIstruttoriaOggettoPagamento($istruttoria);
        }

        $indietro = $this->generateUrl('elenco_documenti_contratto_istruttoria', array("id_pagamento" => $id_pagamento, "id_contratto" => $contratto->getId()));

        $dati_form_istruttoria = array('url_indietro' => $indietro);
        $dati_form_istruttoria["disabled"] = $this->isDisabled($pagamento);
        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, $dati_form_istruttoria);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form_istruttoria->handleRequest($request);
            if ($form_istruttoria->isValid()) {
                try {
                    $em->persist($documento);
                    $em->flush();
                    return $this->addSuccesRedirect("Istruttoria documento salvata correttamente", 'elenco_documenti_contratto_istruttoria', array("id_pagamento" => $id_pagamento, "id_contratto" => $contratto->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_istruttoria_pagamenti"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo pagamento", $this->generateUrl("riepilogo_istruttoria_pagamento", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco contratti", $this->generateUrl("elenco_contratti_istruttoria", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Documenti", $indietro);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Istruttoria Documento");

        $dati["form_istruttoria"] = $form_istruttoria->createView();
        $dati["titolo_sezione"] = "Documento contratto " . $documento->getDocumentoFile()->getNomeOriginale();

        return $this->render("AttuazioneControlloBundle:Istruttoria\bando_8:pannelloIstruttoriaGenerale.html.twig", $dati);
    }

    public function getTipiDocumentiContratto() {

        $res = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array('tipologia' => 'rendicontazione_documenti_contratto_standard'));

        return $res;
    }

}
