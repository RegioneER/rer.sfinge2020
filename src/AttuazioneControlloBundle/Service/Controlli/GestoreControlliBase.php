<?php

namespace AttuazioneControlloBundle\Service\Controlli;

use BaseBundle\Exception\SfingeException;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\StatoPagamento;
use DocumentoBundle\Entity\TipologiaDocumento;
use RichiesteBundle\Service\GestoreResponse;
use DocumentoBundle\Component\ResponseException;
use Symfony\Component\HttpFoundation\Request;

class GestoreControlliBase extends AGestoreControlli {

    public function riepilogoControllo($controllo) {

        $this->inizializzaControllo($controllo);
        $em = $this->getEm();

        try {
            $em->flush();
        } catch (\Exception $e) {
            $this->addFlash("error", "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
            return $this->redirectToRoute("elenco_controlli");
        }

        $options = array();
        $options["disabled"] = !$this->isGranted("ROLE_ISTRUTTORE_CONTROLLI") || !is_null($controllo->getEsito());
        $options["url_indietro"] = $this->generateUrl('elenco_controlli');

        $form = $this->createForm("AttuazioneControlloBundle\Form\Controlli\DatiControlloType", $controllo, $options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $em->flush();
                    $this->addFlash('success', "Dati controllo salvati correttamente");

                    return $this->redirect($this->generateUrl('riepilogo_controllo', array('id_controllo' => $controllo->getId())));
                } catch (\Exception $e) {
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $dati = array();
        $dati["controllo"] = $controllo;
        $dati["menu"] = "riepilogo";
        $dati['menu_principale'] = 'campioni';
        $dati["form"] = $form->createView();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco controlli", $this->generateUrl("elenco_controlli"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo controllo");

        return $this->render("AttuazioneControlloBundle:Controlli:riepilogoControllo.html.twig", $dati);
    }

    public function documentiControllo($controllo) {
        $request = $this->getCurrentRequest();
        $em = $this->getEm();

        $tipo = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneByCodice("CONTROLLO");

        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();
        $documento_file->setTipologiaDocumento($tipo);
        $documento_controllo = new \AttuazioneControlloBundle\Entity\Controlli\DocumentoControllo();
        $documento_controllo->setDocumentoFile($documento_file);

        $options = array();
        $options["disabled"] = !$this->isGranted("ROLE_ISTRUTTORE_CONTROLLI");

        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileSimpleType', $documento_file, $options);
        $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $controllo->addDocumentoControllo($documento_controllo);

                    $this->container->get("documenti")->carica($documento_file, 0);

                    $em->flush();
                    $this->addFlash('success', "Documento caricato correttamente");
                    return $this->redirectToRoute("documenti_controlli", array("id_controllo" => $controllo->getId()));
                } catch (\Exception $e) {
                    $this->container->get("logger")->error($e->getMessage());
                    $this->addFlash('error', "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
                }
            }
        }

        $dati = array(
            "controllo" => $controllo,
            "menu" => "documenti",
            'menu_principale' => 'campioni',
            "form" => $form->createView(),
            "disabled" => $options["disabled"]);

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco controlli", $this->generateUrl("elenco_controlli"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Documenti controllo");

        return $this->render("AttuazioneControlloBundle:Controlli:documentiControllo.html.twig", $dati);
    }

    public function inizializzaControllo($controllo) {
        $procedura = $controllo->getRichiesta()->getProcedura();
        if (is_null($controllo->getValutazioniChecklist()) || $controllo->getValutazioniChecklist()->count() == 0) {
            $checklists = $procedura->getChecklistControllo();
            if (count($checklists) == 0) {
                $checklists = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ChecklistControllo")->findByCodice(array('CHECK_DESK_DEFAULT', 'CHECK_SPR_DEFAULT'));
            }
            foreach ($checklists as $checklist) {
                $valutazione = new \AttuazioneControlloBundle\Entity\Controlli\ValutazioneChecklistControllo();
                $valutazione->setValidata(false);
                $valutazione->setChecklist($checklist);
                $valutazione->setControlloProgetto($controllo);

                $controllo->addValutazioneChecklist($valutazione);

                foreach ($checklist->getSezioni() as $sezione) {
                    foreach ($sezione->getElementi() as $elemento) {
                        $valutazione_elemento = new \AttuazioneControlloBundle\Entity\Controlli\ValutazioneElementoChecklistControllo();
                        $valutazione_elemento->setElemento($elemento);
                        $valutazione->addValutazioneElemento($valutazione_elemento);
                    }
                }
            }
        }
    }

    public function valutaChecklist($valutazione_checklist, $extra = array()) {
        $controllo = $valutazione_checklist->getControlloProgetto();
        $checklist = $valutazione_checklist->getChecklist();
        $options = array();
        $options["url_indietro"] = $this->generateUrl('riepilogo_controllo', array("id_controllo" => $controllo->getId()));
        $options["disabled"] = !$this->isGranted("ROLE_ISTRUTTORE_CONTROLLI") || $valutazione_checklist->getValidata();
        $options["invalida"] = $valutazione_checklist->getValidata() && $this->isGranted("ROLE_SUPERVISORE_CONTROLLI") && is_null($controllo->getEsito());
        $options["valida"] = !$valutazione_checklist->getValidata() && $this->isGranted("ROLE_SUPERVISORE_CONTROLLI") && is_null($controllo->getEsito());

        $form = $this->createForm("AttuazioneControlloBundle\Form\Controlli\ValutazioneChecklistControlloType", $valutazione_checklist, $options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($options["valida"] && $form->get("pulsanti")->get("pulsante_valida")->isClicked()) {
                if (!$this->isGranted("ROLE_SUPERVISORE_CONTROLLI")) {
                    throw new \Exception("Operazione non ammessa per l'utente");
                }

                $validazione = $this->validaChecklist($valutazione_checklist);

                if (!$validazione->getEsito()) {
                    $form->addError(new \Symfony\Component\Form\FormError("Dati non completi"));
                }
            }

            if ($form->isValid()) {
                $request_data = $request->request->get($form->getName());

                foreach ($form->get("valutazioni_elementi")->getIterator() as $child) {
                    $valutazione_elemento = $child->getData();
                    $elemento = $valutazione_elemento->getElemento();

                    switch ($elemento->getTipo()) {
                        case "choice":
                            $choices = $elemento->getChoices();
                            $valutazione_elemento->setValoreRaw(is_null($valutazione_elemento->getValore()) ? null : $choices[$valutazione_elemento->getValore()]);
                            break;
                        default:
                            $valutazione_elemento->setValoreRaw($valutazione_elemento->getValore());
                    }
                }

                if ($options["valida"] && $form->get("pulsanti")->get("pulsante_valida")->isClicked()) {
                    $valutazione_checklist->setValidata(true);
                    $valutazione_checklist->setValutatore($this->getUser());
                    $valutazione_checklist->setDataValidazione(new \DateTime());

                    $messaggio = "Valutazione validata";
                    $redirect_url = $this->generateUrl('valuta_checklist_controlli', array('id_valutazione_checklist' => $valutazione_checklist->getId()));
                } else {
                    if (isset($request_data["pulsanti"]["pulsante_invalida"])) {
                        $valutazione_checklist->setValidata(false);
                        $valutazione_checklist->setValutatore(null);
                        $valutazione_checklist->setDataValidazione(null);

                        $messaggio = "Valutazione invalidata";
                        $redirect_url = $this->generateUrl('valuta_checklist_controlli', array('id_valutazione_checklist' => $valutazione_checklist->getId()));
                    } else {
                        $messaggio = "Modifiche salvate correttamente";
                        $redirect_url = $this->generateUrl('valuta_checklist_controlli', array('id_valutazione_checklist' => $valutazione_checklist->getId()));
                    }
                }

                $em = $this->getEm();
                try {
                    $em->flush();
                    $this->addFlash('success', $messaggio);

                    return $this->redirect($redirect_url);
                } catch (\Exception $e) {
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $dati["form"] = $form->createView();
        $dati["controllo"] = $controllo;
        $dati["procedura"] = $controllo->getProcedura()->getId();
        $dati["valutazione_checklist"] = $valutazione_checklist;
        $dati['menu_principale'] = 'campioni';

        if (isset($extra["twig_data"])) {
            $dati = array_merge($dati, $extra["twig_data"]);
        }

        //aggiungo il titolo della pagina e le info della breadcrumb
        $this->container->get("pagina")->setTitolo($checklist->getNome());

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco controlli", $this->generateUrl("elenco_controlli"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb($checklist->getNome());

        $twig = isset($extra["twig"]) ? $extra["twig"] : "AttuazioneControlloBundle:Controlli:checklistControllo.html.twig";

        return $this->render($twig, $dati);
    }

    public function verificaEsitoFinaleEmettibile($controllo) {
        $esito = new \RichiesteBundle\Utility\EsitoValidazione();
        $esito->setEsito(true);

        $this->controlliChecklist($controllo, $esito);
        $this->controlliRiepilogo($controllo, $esito);

        return $esito;
    }

    public function esitoFinale($controllo) {

        $verifica = $this->verificaEsitoFinaleEmettibile($controllo);
        if (!$verifica->getEsito()) {
            foreach ($verifica->getMessaggi() as $messaggio) {
                $this->addFlash('error', $messaggio);
            }
            return $this->redirect($this->generateUrl('riepilogo_controllo', array("id_controllo" => $controllo->getId())));
        }

        $options = array();
        $options["url_indietro"] = $this->generateUrl('esito_finale_controlli', array("id_controllo" => $controllo->getId()));
        $options["disabled"] = !$this->isGranted("ROLE_SUPERVISORE_CONTROLLI") || !is_null($controllo->getEsito());

        $form = $this->createForm("AttuazioneControlloBundle\Form\Controlli\EsitoControlloType", $controllo, $options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($controllo->getDataValidazione() < $controllo->getDataInizioControlli()) {
                $form->get('data_validazione')->addError(new \Symfony\Component\Form\FormError("La data di validazione non può essere precedente alla data del controllo in loco"));
            }

            if ($form->isValid()) {

                if ($controllo->getEsito()) {
                    $esito = $this->isEsitoFinalePositivoEmettibile($controllo);
                    if (!$esito->getEsito()) {
                        foreach ($esito->getMessaggi() as $messaggio) {
                            $this->addFlash('error', $messaggio);
                        }
                        return $this->redirect($this->generateUrl('esito_finale_controlli', array('id_controllo' => $controllo->getId())));
                    }
                }

                $em = $this->getEm();
                try {
                    $em->flush();
                    $this->addFlash('success', "Esito finale controllo salvato correttamente");

                    return $this->redirect($this->generateUrl('elenco_controlli'));
                } catch (\Exception $e) {
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $twig = "AttuazioneControlloBundle:Controlli:esitoFinale.html.twig";

        $dati = array();
        $dati["controllo"] = $controllo;
        $dati["menu"] = "esito";
        $dati['menu_principale'] = 'campioni';
        $dati["form"] = $form->createView();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco controlli", $this->generateUrl("elenco_controlli"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Esito finale controllo");

        return $this->render($twig, $dati);
    }

    public function isEsitoFinalePositivoEmettibile($controllo) {
        $esito = new \RichiesteBundle\Utility\EsitoValidazione();
        $esito->setEsito(true);

        return $esito;
    }

    protected function controlliChecklist($controllo, $esito) {

        foreach ($controllo->getValutazioniChecklist() as $valutazione) {
            if (!$valutazione->getValidata()) {
                $esito->setEsito(false);
                $esito->addMessaggio("Checklist non completata o validata");
                break;
            }
        }
    }

    protected function controlliRiepilogo($controllo, $esito) {
        if (is_null($controllo->getNote()) || is_null($controllo->getDataInizioControlli())) {
            $esito->setEsito(false);
            $esito->addMessaggio("Sezione riepilogo non completa");
        }
    }

    public function validaChecklist($valutazione_checklist) {

        $esito = new EsitoValidazione(true);
        $procedura = $valutazione_checklist->getControlloProgetto()->getProcedura()->getId();
        foreach ($valutazione_checklist->getValutazioniElementi() as $valutazione) {
            $elemento = $valutazione->getElemento();
            /*
             * aggiungo controllo con eccezione per elementi specifici non previsti che non vanno verificati
             * perchè non visibili nel twig
             */
            $specifica = $elemento->isSpecifica();
            $procedure = $elemento->getProcedure();
            if ($specifica == false || ($specifica == true && (in_array($procedura, $procedure)))) {
                if (is_null($valutazione->getValore())) {
                    $esito->setEsito(false);
                    $esito->addMessaggio("La checklist non è completa");
                }
            }
        }

        return $esito;
    }

    public function isEsitoFinaleEmettibile($controllo) {
        
    }

    public function eliminaDocumentoControllo($controllo, $documento_controllo, $verbale) {
        $em = $this->getEm();

        try {
            $em->remove($documento_controllo->getDocumentoFile());
            $em->remove($documento_controllo);
            $em->flush();
            if ($verbale == '1') {
                return $this->addSuccesRedirect("Il documento è stato correttamente eliminato", "valuta_sopralluogo_form", array("id_controllo" => $controllo->getId()));
            } else {
                return $this->addSuccesRedirect("Il documento è stato correttamente eliminato", "documenti_controlli", array("id_controllo" => $controllo->getId()));
            }
        } catch (ResponseException $e) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "documenti_controlli", array("id_controllo" => $controllo->getId()));
        }
    }

    public function valutaSopralluogoForm($controllo) {
        $em = $this->getEm();
        $tipo = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneByCodice("CONTROLLO_VERBALE_SPR");

        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();
        $documento_file->setTipologiaDocumento($tipo);
        $documento_controllo = new \AttuazioneControlloBundle\Entity\Controlli\DocumentoControllo();
        $documento_controllo->setDocumentoFile($documento_file);
        $documento_controllo->setControlloProgetto($controllo);

        $opzioni_form["tipo"] = $tipo;
        $opzioni_form["disabled"] = !$this->isGranted("ROLE_ISTRUTTORE_CONTROLLI") || !is_null($controllo->getEsito());

        $form_doc = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
        $form_doc->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

        $opzioni_form_valutazione = array(
            'url_indietro' => $this->generateUrl('riepilogo_controllo', array("id_controllo" => $controllo->getId())),
            "disabled" => $opzioni_form["disabled"]
        );

        $form = $this->createForm('AttuazioneControlloBundle\Form\Controlli\ValutazioneSopralluogoFormType', $controllo, $opzioni_form_valutazione);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $em->flush();
                    $this->addFlash('success', "Valutazione salvata correttamente");
                    return $this->redirect($this->generateUrl('riepilogo_controllo', array("id_controllo" => $controllo->getId())));
                } catch (\Exception $e) {
                    $this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
                    $this->container->get("logger")->error($e->getMessage());
                }
            }

            $form_doc->handleRequest($request);
            if ($form_doc->isSubmitted() && $form_doc->isValid()) {
                try {

                    $this->container->get("documenti")->carica($documento_file, 0);
                    $em->persist($documento_controllo);

                    $em->flush($documento_controllo);
                    $this->addFlash('success', "Documento salvato correttamente");
                    return $this->redirect($this->generateUrl('valuta_sopralluogo_form', array("id_controllo" => $controllo->getId())));
                } catch (ResponseException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco controlli", $this->generateUrl("elenco_controlli"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Valutazione sopralluogo");

        $dati["menu"] = "sopralluogo";
        $dati['menu_principale'] = 'campioni';
        $dati["controllo"] = $controllo;
        $dati["disabled"] = $opzioni_form["disabled"];
        $dati["form"] = $form->createView();
        $dati["form_doc"] = $form_doc->createView();

        return $this->render("AttuazioneControlloBundle:Controlli:valutazioneSopralluogoForm.html.twig", $dati);
    }

    public function documentiControlloProcedura($controllo) {
        $request = $this->getCurrentRequest();
        $em = $this->getEm();

        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();
        $documento_controllo = new \AttuazioneControlloBundle\Entity\Controlli\DocumentoControlloProcedura();
        $documento_controllo->setDocumentoFile($documento_file);

        $options = array();
        $options["disabled"] = !$this->isGranted("ROLE_ISTRUTTORE_CONTROLLI");
        $options["lista_tipi"] = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByTipologia("supporto_controllo_proc");

        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $options);
        $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $controllo->addDocumentoControllo($documento_controllo);

                    $this->container->get("documenti")->carica($documento_file, 0);

                    $em->flush();
                    $this->addFlash('success', "Documento caricato correttamente");
                    return $this->redirectToRoute("documenti_controllo_procedura", array("id_controllo" => $controllo->getId()));
                } catch (\Exception $e) {
                    $this->container->get("logger")->error($e->getMessage());
                    $this->addFlash('error', "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
                }
            }
        }

        $dati = array(
            "controllo" => $controllo,
            'menu_principale' => 'procedura',
            "form" => $form->createView(),
            "disabled" => $options["disabled"]);

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco procedure", $this->generateUrl("home_controlli"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Documenti controllo");

        return $this->render("AttuazioneControlloBundle:Controlli:documentiControlloProcedura.html.twig", $dati);
    }

    public function eliminaDocumentoControlloProcedura($controllo, $documento_controllo) {
        $em = $this->getEm();
        try {
            $em->remove($documento_controllo->getDocumentoFile());
            $em->remove($documento_controllo);
            $em->flush();
            return $this->addSuccesRedirect("Il documento è stato correttamente eliminato", "documenti_controllo_procedura", array("id_controllo" => $controllo->getId()));
        } catch (ResponseException $e) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "documenti_controllo_procedura", array("id_controllo" => $controllo->getId()));
        }
    }

    public function estraiCampioniLoco() {
        $campioni = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->estrazioneControlli();

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Sfinge 2104-2020")
                ->setLastModifiedBy("Sfinge 2104-2020")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Test result file");

        $riga = 1;

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();

        $column = 0;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'id operazione');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'protocollo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'titolo progetto');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'stato richiesta');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'denominazione soggetto');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'id controllo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'data controllo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'data validazione checklist');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'esito');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'data validazione controllo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'id pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'CUP');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Revocato/ritirato');
        $column++;

        $column = 0;
        foreach ($campioni as $key => $campione) {

            $riga++;
            $phpExcelObject->setActiveSheetIndex(0);

            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['id_richiesta']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['protocollo']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['titolo']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['stato_richiesta']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['denominazione']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['id_controllo']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['data_controllo'] == '-' ? $campione['data_controllo'] : (new \DateTime($campione['data_controllo']))->format('d-m-Y H:i:s'));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['data_vali_chk'] == '-' ? $campione['data_vali_chk'] : (new \DateTime($campione['data_vali_chk']))->format('d-m-Y H:i:s'));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($campione['esito_cl']) ? '-' : ($campione['esito_cl']));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['data_validazione'] == '-' ? $campione['data_validazione'] : (new \DateTime($campione['data_validazione']))->format('d-m-Y H:i:s'));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['id_pagamento']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['codice_cup']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($campione['id_revoca']) ? 'NO' : 'SI');
            $column++;

            $column = 0;
        }

        // create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione_campioni.xls');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    public function estraiPagamentiCampioniLoco() {
        $campioni = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->estrazioneControlliPagamenti();

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Sfinge 2104-2020")
                ->setLastModifiedBy("Sfinge 2104-2020")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Test result file");

        $riga = 1;

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();

        $column = 0;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'titolo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'CUP');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'protocollo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'denominazione');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'modalita pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'campionato controllo in loco');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'id pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'data invio pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'protocollo pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'esito checklist pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'validazione checklist pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'data validazione checklist pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'esito checklist controllo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'validazione checklist controllo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'data validazione checklist controllo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Revocato/ritirato');
        $column++;


        $column = 0;
        foreach ($campioni as $key => $campione) {
            $pagamento = $campione['pagamento'];
            $valutazioneChkPag = $pagamento->valutazioneChkPagemento(\AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento::TIPOLOGIA_PRINCIPALE);
            $valutazioneChkCloco = $pagamento->valutazioneChkPagemento(\AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento::TIPOLOGIA_POST_CONTROLLO_LOCO);
            $riga++;
            $phpExcelObject->setActiveSheetIndex(0);
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['titolo']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['codice_cup']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['protocollo']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['denominazione']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['modalita_pagamento']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['campionato'] != 0 ? 'SI' : 'NO');
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['id_pagamento']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['data_invio'] == '-' ? $campione['data_invio'] : (new \DateTime($campione['data_invio']))->format('d-m-Y H:i:s'));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['protocollo_pag']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($valutazioneChkPag) ? '-' : (is_null($valutazioneChkPag->getAmmissibile()) ? '-' : ( $valutazioneChkPag->isAmmissibile() == true ? 'Ammissibile' : 'Non ammissibile')));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($valutazioneChkPag) ? '-' : ( $valutazioneChkPag->isValidata() == true ? 'SI' : 'NO'));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($valutazioneChkPag) ? '-' : is_null($valutazioneChkPag->getDataValidazione()) ? '-' : $valutazioneChkPag->getDataValidazione()->format('d-m-Y H:i:s'));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($valutazioneChkCloco) ? '-' : (is_null($valutazioneChkCloco->getAmmissibile()) ? '-' : ( $valutazioneChkCloco->isAmmissibile() == true ? 'Ammissibile' : 'Non ammissibile')));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($valutazioneChkCloco) ? '-' : ( $valutazioneChkCloco->isValidata() == true ? 'SI' : 'NO'));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($valutazioneChkCloco) ? '-' : is_null($valutazioneChkCloco->getDataValidazione()) ? '-' : $valutazioneChkCloco->getDataValidazione()->format('d-m-Y H:i:s'));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($campione['id_revoca']) ? 'NO' : 'SI');
            $column++;


            $column = 0;
        }

        // create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione_campioni.xls');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    public function estraiRichiesteCampioniLoco() {
        $campioni = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->estrazioneControlliRichieste();

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Sfinge 2104-2020")
                ->setLastModifiedBy("Sfinge 2104-2020")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Test result file");

        $riga = 1;

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();


        $activeSheet->getStyle('M')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $activeSheet->getStyle('N')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $activeSheet->getStyle('O')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


        $column = 0;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'id richiesta');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'protocollo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'CUP');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'contributo ammesso');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'denominazione');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'titolo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'asse titolo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'id pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'modalita pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'protocollo pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'stato pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'importo richiesto');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'importo pagato');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'importo rendicontato');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'importo rendicontato ammesso');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'data invio');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Revocato/ritirato');
        $column++;


        $column = 0;
        foreach ($campioni as $key => $campione) {

            $riga++;
            $phpExcelObject->setActiveSheetIndex(0);
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['id_richiesta']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['protocollo']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['codice_cup']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, number_format($campione['contributo_ammesso'], 2, ',', ''));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['denominazione']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['titolo']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['asse_titolo']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['id_pagamento']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['modalita_pagamento']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['protocollo_pag']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['stato_pag_desc']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, number_format($campione['importo_richiesto'], 2, ',', '')); //L
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, number_format($campione['importo_pagato'], 2, ',', '')); //M
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, number_format($campione['importo_rendicontato'], 2, ',', '')); //N
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, number_format($campione['importo_rendicontato_ammesso'], 2, ',', '')); //O
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['data_invio'] == '-' ? $campione['data_invio'] : (new \DateTime($campione['data_invio']))->format('d-m-Y H:i:s'));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($campione['id_revoca']) ? 'NO' : 'SI');
            $column++;

            $column = 0;
        }

        // create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione_campioni.xls');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    public function estrazioneGiustificativiProgetto($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->findOneById($id_richiesta);
        $giustificativi = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->estrazioneGiustifictiviRichiesta($id_richiesta);
        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Sfinge 2104-2020")
                ->setLastModifiedBy("Sfinge 2104-2020")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Test result file");

        $riga = 1;

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();

        $column = 0;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Tipologia giustificativo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Fornitore / Dipendente');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Descrizione');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Numero fattura / Scheda costo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Data');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Importo giustificativo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Importo richiesto');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Importo ammesso');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Importo non ammesso');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Note istruttore');
        $column++;

        $activeSheet->getStyle('F')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $activeSheet->getStyle('G')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $activeSheet->getStyle('H')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $activeSheet->getStyle('I')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $activeSheet->getStyle('L')->getAlignment()->setWrapText(true);



        $column = 0;
        foreach ($giustificativi as $key => $giustificativo) {

            //gestione dei risultati per rotture 773 e 774
            //tipologia			
            if (!is_null($giustificativo->getDenominazioneFornitore())) {
                $fornitore = $giustificativo->getDenominazioneFornitore();
                if (!is_null($giustificativo->getTipologiaGiustificativo())) {
                    $tipologia = $giustificativo->getTipologiaGiustificativo()->getDescrizione();
                } else {
                    $fornitore = '-';
                    $tipologia = '-';
                }
                $numero = $giustificativo->getNumeroGiustificativo();
            } else {
                if (!is_null($giustificativo->getEstensione()) && !is_null($giustificativo->getEstensione()->getNome())) {
                    $fornitore = $giustificativo->getEstensione()->getNome() . ' ' . $giustificativo->getEstensione()->getCognome();
                    if (!is_null($giustificativo->getTipologiaGiustificativo())) {
                        $tipologia = $giustificativo->getTipologiaGiustificativo()->getDescrizione();
                    } else {
                        $fornitore = '-';
                        $tipologia = '-';
                    }
                } else {
                    $tipologia = $giustificativo->getTipologiaGiustificativo()->getDescrizione();
                    $fornitore = $giustificativo->getDenominazioneFornitore();
                }
                $numero = '-';
            }

            if (!is_null($giustificativo->getImportoGiustificativo())) {
                $importo = number_format($giustificativo->getImportoGiustificativo(), 2, ',', '.');
            } else {
                $importo = number_format($giustificativo->getTotaleImputato(), 2, ',', '.');
            }

            if (!is_null($giustificativo->getImportoGiustificativo())) {
                $importo_richiesto = number_format($giustificativo->getImportoRichiesto(), 2, ',', '.');
            } else {
                $importo_richiesto = '-';
            }

            $importo_approvato = number_format($giustificativo->getTotaleImputatoApprovato(), 2, ',', '.');
            $importo_non_approvato = number_format($giustificativo->calcolaImportoNonAmmesso(), 2, ',', '.');
            if (!is_null($giustificativo->getDataGiustificativo())) {
                $data = $giustificativo->getDataGiustificativo()->format('d/m/Y');
            } else {
                $data = '-';
            }
            if (!is_null($giustificativo->getDescrizioneGiustificativo())) {
                $descrizione = $giustificativo->getDescrizioneGiustificativo();
            } else {
                $descrizione = '-';
            }

            $note_totale = "";
            foreach ($giustificativo->getVociPianoCosto() as $voceGiust) {
                $note_totale .= ($voceGiust->getNota() . "\n\n");
            }

            $riga++;
            $phpExcelObject->setActiveSheetIndex(0);
            //tipologia
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $tipologia);
            $column++;
            //fornitore
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $fornitore);
            $column++;
            //descrizione
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $descrizione);
            $column++;
            //numero
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $numero);
            $column++;
            //data
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $data);
            $column++;
            //importo
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $importo);
            $column++;
            //importo richiesto
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $importo_richiesto);
            $column++;
            //importo ammesso
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $importo_approvato);
            $column++;
            //importo non ammesso
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $importo_non_approvato);
            $column++;

            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $note_totale);
            $column++;

            $column = 0;
        }

        // create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione_giustificativi_' . str_replace("/", '_', $richiesta->getProtocollo()) . '.xls');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    public function estraiProgettiUniverso() {
        $campioni = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->estrazioenUniversoProgetti();

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Sfinge 2104-2020")
                ->setLastModifiedBy("Sfinge 2104-2020")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Test result file");

        $riga = 1;

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();

        $column = 0;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'id progetto');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'protocollo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'cup');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'titolo progetto');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'asse');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'bando');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'atto');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'beneficiario');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'costo ammesso');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'contributo ammesso');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'pagamento');
        $column++;

        $column = 0;
        foreach ($campioni as $key => $campione) {

            $riga++;
            $phpExcelObject->setActiveSheetIndex(0);

            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['id_progetto']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['protocollo']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['cup']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['titolo_progetto']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['titolo']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['titolo_procedura']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['numero_atto']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['Beneficiario']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['costo_ammesso']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['Contributo_ammesso']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $campione['modalita_ultimo_pagamento']);
            $column++;

            $column = 0;
        }

        // create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione_universo.xls');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    public function verbaleDeskControllo($controllo) {
        $em = $this->getEm();
        $options = array();
        $options["disabled"] = !$this->isGranted("ROLE_ISTRUTTORE_CONTROLLI") || !is_null($controllo->getEsito());
        $options["url_indietro"] = $this->generateUrl('verbale_desk_form', array('id_controllo' => $controllo->getId()));

        $form = $this->createForm("AttuazioneControlloBundle\Form\Controlli\VerbaleDeskControlloType", $controllo, $options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $em->flush();
                    $this->addFlash('success', "Dati controllo salvati correttamente");

                    return $this->redirect($this->generateUrl('riepilogo_controllo', array('id_controllo' => $controllo->getId())));
                } catch (\Exception $e) {
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $dati = array();
        $dati["controllo"] = $controllo;
        $dati["menu"] = "verbaledesk";
        $dati['menu_principale'] = 'campioni';
        $dati["form"] = $form->createView();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco controlli", $this->generateUrl("elenco_controlli"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo controllo");

        return $this->render("AttuazioneControlloBundle:Controlli:verbaleDeskControllo.html.twig", $dati);
    }

    public function verbaleSopralluogoControllo($controllo) {
        $em = $this->getEm();
        $funzioniService = $this->container->get('funzioni_utili');
        $request = $this->getCurrentRequest();
        $data = $funzioniService->getIndirizzoControlliAzienda($request, $controllo->getIndirizzo());


        $options = array();
        $ruoloGranted = $this->isGranted("ROLE_ISTRUTTORE_CONTROLLI");
        $esitoGranted = is_null($controllo->getEsito());
        $options["disabled"] = !$ruoloGranted || !$esitoGranted;
        $options["url_indietro"] = $this->generateUrl('verbale_sopralluogo_form', array('id_controllo' => $controllo->getId()));
        $options["dataIndirizzo"] = $data;
        $form = $this->createForm("AttuazioneControlloBundle\Form\Controlli\VerbaleSopralluogoControlloType", $controllo, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $em->flush();
                    $this->addFlash('success', "Dati controllo salvati correttamente");

                    return $this->redirect($this->generateUrl('riepilogo_controllo', array('id_controllo' => $controllo->getId())));
                } catch (\Exception $e) {
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $dati = array();
        $dati["controllo"] = $controllo;
        $dati["menu"] = "verbalesopralluogo";
        $dati['menu_principale'] = 'campioni';
        $dati["form"] = $form->createView();

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco controlli", $this->generateUrl("elenco_controlli"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo controllo");

        return $this->render("AttuazioneControlloBundle:Controlli:verbaleSopralluogoControllo.html.twig", $dati);
    }

    public function generaVerbaleDeskControllo($controllo) {

        $procedura = $controllo->getRichiesta()->getProcedura();
        $checklists = $procedura->getChecklistControllo();
        if (count($checklists) == 0) {
            $checklist_desk = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ChecklistControllo")->findOneByCodice(array('CHECK_DESK_DEFAULT'));
        } else {
            foreach ($checklists as $checklist) {
                if ($checklist->getNome() == 'Fase Desk') {
                    $codice = $checklist->getCodice();
                }
            }
            $checklist_desk = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ChecklistControllo")->findOneBy(['codice' => $codice]);
        }

        $valutazioni = $controllo->getValutazioniChecklist();
        $valutatore = null;
        foreach ($valutazioni as $valutazione) {
            if ($valutazione->getChecklist()->getNome() == 'Fase Desk') {
                $valutatore = $valutazione->getValutatore();
            }
        }

        $sezioni_checklist_desk = $checklist_desk->getSezioni();
        $elementi_checklist_desk = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->getElementiCheckList($controllo->getId(), $checklist_desk->getId());

        $dati["checklist_desk"] = [];
        foreach ($sezioni_checklist_desk as $sezione) {
            foreach ($elementi_checklist_desk as $elemento) {
                $specifica = $elemento['specifica'];
                $procedure = $elemento['procedure'];
                if ($specifica == false || ($specifica == true && !is_null($procedure) && (in_array($procedura->getId(), $procedure)))) {
                    if ($elemento['sezione'] == $sezione->getDescrizione()) {
                        $dati["checklist_desk"][$sezione->getDescrizione()][$elemento['elemento']]['valore'] = array_key_exists('valore',
                                        $elemento) ? $elemento['valore'] : null;
                        $dati["checklist_desk"][$sezione->getDescrizione()][$elemento['elemento']]['note'] = array_key_exists('note',
                                        $elemento) ? $elemento['note'] : null;
                        $dati["checklist_desk"][$sezione->getDescrizione()][$elemento['elemento']]['note_doc'] = array_key_exists('note_doc',
                                        $elemento) ? $elemento['note_doc'] : null;
                        $dati["checklist_desk"][$sezione->getDescrizione()][$elemento['elemento']]['note_coll'] = array_key_exists('note_coll',
                                        $elemento) ? $elemento['note_coll'] : null;
                    }
                }
            }
        }

        $dati['twig'] = 'AttuazioneControlloBundle:Controlli:pdfVerbaleDeskControllo.html.twig';
        $pdf = $this->container->get('pdf');
        $dati['controllo'] = $controllo;
        $dati['soggetto'] = $controllo->getRichiesta()->getMandatario()->getSoggetto();
        $dati['richiesta'] = $controllo->getRichiesta();
        $dati['facsimile'] = false;
        $dati['validatore'] = $valutatore;
        $dati['procedura'] = $procedura;

        $pdf->load($dati['twig'], $dati);
        //return $this->render($dati['twig'], $dati);

        $date = new \DateTime();
        $data = $date->format('d-m-Y');


        $pdf->download('Verbale_fase_desk_' . $controllo->getRichiesta()->getId() . ' ' . $data);

        return new Response();
    }

    public function generaVerbaleSopralluogoControllo($controllo) {

        $procedura = $controllo->getRichiesta()->getProcedura();
        $checklists = $procedura->getChecklistControllo();
        if (count($checklists) == 0) {
            $checklist_spr = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ChecklistControllo")->findOneByCodice(array('CHECK_SPR_DEFAULT'));
        } else {
            foreach ($checklists as $checklist) {
                if ($checklist->getNome() == 'Fase Sopralluogo') {
                    $codice = $checklist->getCodice();
                }
            }
            $checklist_spr = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ChecklistControllo")->findOneBy(['codice' => $codice]);
        }

        $valutazioni = $controllo->getValutazioniChecklist();
        $valutatore = null;
        foreach ($valutazioni as $valutazione) {
            if ($valutazione->getChecklist()->getNome() == 'Fase Sopralluogo') {
                $valutatore = $valutazione->getValutatore();
            }
        }

        $sezioni_checklist_spr = $checklist_spr->getSezioni();
        $elementi_checklist_spr = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->getElementiCheckList($controllo->getId(), $checklist_spr->getId());

        $dati["checklist_spr"] = [];
        foreach ($sezioni_checklist_spr as $sezione) {
            foreach ($elementi_checklist_spr as $elemento) {
                $specifica = $elemento['specifica'];
                $procedure = $elemento['procedure'];
                if ($specifica == false || ($specifica == true && !is_null($procedure) && (in_array($procedura->getId(), $procedure)))) {
                    if ($elemento['sezione'] == $sezione->getDescrizione()) {
                        $dati["checklist_spr"][$sezione->getDescrizione()][$elemento['elemento']]['valore'] = array_key_exists('valore',
                                        $elemento) ? $elemento['valore'] : null;
                        $dati["checklist_spr"][$sezione->getDescrizione()][$elemento['elemento']]['note'] = array_key_exists('note',
                                        $elemento) ? $elemento['note'] : null;
                        $dati["checklist_spr"][$sezione->getDescrizione()][$elemento['elemento']]['note_coll_ben'] = array_key_exists('note_coll_ben',
                                        $elemento) ? $elemento['note_coll_ben'] : null;
                    }
                }
            }
        }

        $dati['twig'] = 'AttuazioneControlloBundle:Controlli:pdfVerbaleSopralluogoControllo.html.twig';
        $pdf = $this->container->get('pdf');
        $dati['controllo'] = $controllo;
        $dati['soggetto'] = $controllo->getRichiesta()->getMandatario()->getSoggetto();
        $dati['richiesta'] = $controllo->getRichiesta();
        $dati['facsimile'] = false;
        $dati['validatore'] = $valutatore;
        $dati['procedura'] = $procedura;

        $pdf->load($dati['twig'], $dati);
        //return $this->render($dati['twig'], $dati);

        $date = new \DateTime();
        $data = $date->format('d-m-Y');

        $pdf->download('Verbale_fase_sopralluogo_' . $controllo->getRichiesta()->getId() . ' ' . $data);

        return new Response();
    }

    protected function isGranted($attributes, $object = null) {
        $utente = $this->getUser();
        if($utente->haDoppioRuoloInvFesr() == false) {
            parent::isGranted($attributes, $object);
        }
        $ruoli = $utente->getRoles();
        return in_array($attributes, $ruoli);
    }
    

}
