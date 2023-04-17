<?php

namespace AttuazioneControlloBundle\Service;

use BaseBundle\Exception\SfingeException;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;
use DocumentoBundle\Entity\DocumentoFile;
use IstruttorieBundle\Entity\DocumentoIstruttoria;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\IndicatoreOutput;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use MonitoraggioBundle\Form\IndicatoreOutputType;
use DocumentoBundle\Form\Type\DocumentoFileSimpleType;
use BaseBundle\Form\SalvaIndietroType;
use BaseBundle\Form\SalvaType;
use AttuazioneControlloBundle\Entity\DocumentoImpegno;
use AttuazioneControlloBundle\Form\DocumentoImpegnoType;
use AttuazioneControlloBundle\Form\ImpegnoType;
use AttuazioneControlloBundle\Form\ProgettoProceduraAggiudicazioneType;
use AttuazioneControlloBundle\Form\ProceduraAggiudicazioneBeneficiarioType;
use CipeBundle\Entity\Classificazioni\CupNatura;
use AttuazioneControlloBundle\Form\RichiestaFaseProceduraleType;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Entity\ProceduraAggiudicazione;
use DocumentoBundle\Entity\TipologiaDocumento;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use MonitoraggioBundle\Service\GestoreIndicatoreService;

class GestoreRichiesteATCBase extends AGestoreRichiesteATC {

    public function accettaContributo($id_richiesta) {
        $request = $this->getCurrentRequest();
        $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
        $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
        if (is_null($soggetto)) {
            return $this->addErrorRedirect("Soggetto non valido", "home");
        }

        $data_corrente = new \DateTime();

        $em = $this->getEm();

        $richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $attuazioneControllo = $richiesta->getAttuazioneControllo();

        if ($richiesta->getProcedura()->getId() == 7 || $richiesta->getProcedura()->getId() == 8) {
            $label = 'Conferma accettazione contributo';
        } else {
            $label = 'Accetta contributo';
        }

        $opzioni = array();
        $opzioni["label_pulsante"] = $label;
        $opzioni["url_indietro"] = $this->generateUrl("elenco_gestione_beneficiario");

        if (!$attuazioneControllo->isContributoAccettabile()) {
            return $this->addError("Il contributo è già stato accettato oppure è scaduto il termine per l'accettazione", $opzioni["url_indietro"]);
        }

        $datiBancariProponenti = array();
        foreach ($richiesta->getProponenti() as $proponente) {

            // dobbiamo escludere gli eventuali proponenti (non mandatari) che hanno il proponenteProfessionista(bando professionisti)
            // questo perchè pur essendoci più proponenti, rappresentano in realtà gli associati di studi o associazioni di professionisti 
            // per cui intuisco interessi soltanto l'iban del mandatario
            $proponenteProfessionista = $proponente->getProfessionisti();
            if (count($proponenteProfessionista) > 0 && !$proponente->isMandatario()) {
                //skip
                continue;
            }

            $datiBancari = new \AttuazioneControlloBundle\Entity\DatiBancari();
            $datiBancari->setProponente($proponente);

            $datiBancariProponenti[] = $datiBancari;
        }

        $attuazioneControllo->setDatiBancariProponenti($datiBancariProponenti);

        $form = $this->createForm("AttuazioneControlloBundle\Form\AccettazioneContributoType", $attuazioneControllo, $opzioni);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $attuazioneControllo->setContributoAccettato(true);
                $attuazioneControllo->setDataAccettazione($data_corrente);
                $attuazioneControllo->setUtenteAccettazione($this->getUser());

                foreach ($attuazioneControllo->getDatiBancariProponenti() as $datiBancariProponente) {
                    $em->persist($datiBancariProponente);
                }

                // generazione del documento
                // protocollazione

                try {
                    $em->flush();
                    return $this->addSuccesRedirect("Il contributo è stato correttamente accettato", "elenco_gestione_beneficiario");
                } catch (\Exception $e) {
                    $this->addError("Errore nell'accettazione del contributo. Si prega di riprovare o contattare l'assistenza");
                }
            } else {
                $this->addError('Sono presenti degli errori');
            }
        }

        $dati = array("richiesta" => $richiesta, "form" => $form->createView());

        return $this->render("AttuazioneControlloBundle:RichiesteATC:accettaContributo.html.twig", $dati);
    }

    public function riepilogoRichiestaPA($richiesta) {
        $attuazioneControllo = $richiesta->getAttuazioneControllo();

        $dati = array(
            "attuazione_controllo" => $attuazioneControllo,
            "menu" => "riepilogo",
        );
        return $this->render("AttuazioneControlloBundle:PA/Richieste:riepilogoRichiesta.html.twig", $dati);
    }

    public function documentiRichiestaPA($richiesta) {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $attuazioneControllo = $richiesta->getAttuazioneControllo();
        $documenti_caricati = $em->getRepository("RichiesteBundle\Entity\DocumentoRichiesta")->findDocumentiCaricati($richiesta->getId());

        if ($richiesta->getProcedura()->isRichiestaFirmaDigitale() == false) {
            $domanda = $richiesta->getDocumentoRichiesta();
        } else {
            $domanda = $richiesta->getDocumentoRichiestaFirmato();
        }

        $documenti_proponenti = array();

        $documenti_pagamenti = array();

        foreach ($richiesta->getProponenti() as $proponente) {
            $documenti_proponente = $em->getRepository("RichiesteBundle\Entity\DocumentoProponente")->findDocumentiCaricati($proponente->getId());
            if (count($documenti_proponente) > 0) {
                $documenti_proponenti[] = array("proponente" => $proponente, "documenti" => $documenti_proponente);
            }
        }

        if (!is_null($richiesta->getAttuazioneControllo())) {
            foreach ($richiesta->getAttuazioneControllo()->getPagamenti() as $pagamento) {
                if (count($pagamento->getDocumentiPagamento()) > 0) {
                    foreach ($pagamento->getDocumentiPagamento() as $doc) {
                        $documenti_pagamenti[] = $doc;
                    }
                }
            }
        }

        $documenti_istruttoria = $em->getRepository("IstruttorieBundle\Entity\DocumentoIstruttoria")->findByRichiesta($richiesta);

        $tipo = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneByCodice("ATTUAZIONE");
        $documento_file = new DocumentoFile();
        $documento_file->setTipologiaDocumento($tipo);
        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileSimpleType', $documento_file);
        $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

        if ($request->isMethod('POST')) {
            if ($this->isGranted("ROLE_OPERATORE_COGEA")) {
                throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
            }
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $this->container->get("documenti")->carica($documento_file, 0, $richiesta);

                    $attuazioneControllo->addDocumento($documento_file);

                    $em->flush();
                    $this->addFlash('success', "Documento caricato correttamente");
                    return $this->redirectToRoute("documenti_richiesta_attuazione", array("id_richiesta" => $richiesta->getId()));
                } catch (\Exception $e) {
                    $this->container->get("logger")->error($e->getMessage());
                    $this->addFlash('error', "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
                }
            }
        }


        $dati = array(
            "documenti" => $documenti_caricati,
            "documenti_istruttoria" => $documenti_istruttoria,
            "domanda" => $domanda, "form" => $form->createView(),
            "richiesta" => $richiesta,
            "menu" => "documenti",
            "documenti_proponenti" => $documenti_proponenti,
            "documenti_pagamenti" => $documenti_pagamenti,
            "attuazione_controllo" => $attuazioneControllo);

        return $this->render("AttuazioneControlloBundle:PA/Richieste:documentiRichiesta.html.twig", $dati);
    }

    public function documentiRichiestaIstruttoriaPA($richiesta) {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $attuazioneControllo = $richiesta->getAttuazioneControllo();
        $documenti_caricati = $em->getRepository("RichiesteBundle\Entity\DocumentoRichiesta")->findDocumentiCaricati($richiesta->getId());

        $domanda = $richiesta->getDocumentoRichiestaFirmato();

        $documenti_istruttoria = $em->getRepository("IstruttorieBundle\Entity\DocumentoIstruttoria")->findByRichiesta($richiesta);

        $tipo = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneByCodice("ATTUAZIONE");
        $documento_file = new DocumentoFile();
        $documento_file->setTipologiaDocumento($tipo);
        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileSimpleType', $documento_file);
        $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

        if ($request->isMethod('POST')) {
            if ($this->isGranted("ROLE_OPERATORE_COGEA")) {
                throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
            }
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $this->container->get("documenti")->carica($documento_file, 0, $richiesta);

                    $attuazioneControllo->addDocumento($documento_file);

                    $em->flush();
                    $this->addFlash('success', "Documento caricato correttamente");
                    return $this->redirectToRoute("documenti_richiesta_attuazione", array("id_richiesta" => $richiesta->getId()));
                } catch (\Exception $e) {
                    $this->container->get("logger")->error($e->getMessage());
                    $this->addFlash('error', "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
                }
            }
        }


        $dati = array(
            "documenti" => $documenti_caricati,
            "documenti_istruttoria" => $documenti_istruttoria,
            "domanda" => $domanda, "form" => $form->createView(),
            "richiesta" => $richiesta,
            "menu" => "documenti_istruttoria",
            "attuazione_controllo" => $attuazioneControllo);

        return $this->render("AttuazioneControlloBundle:PA/Richieste:documentiIstruttoria.html.twig", $dati);
    }

    public function eliminaDocumentoAttuazione($richiesta, $id_documento) {
        $em = $this->getEm();
        $documento = $em->getRepository("DocumentoBundle\Entity\Documento")->find($id_documento);
        try {
            $richiesta->getAttuazioneControllo()->removeDocumento($documento);
            $em->remove($documento);
            $em->flush();
            $this->addFlash('success', "Documento eliminato correttamente");
        } catch (\Exception $e) {
            $this->container->get("logger")->error($e->getMessage());
            $this->addFlash('error', "Documento non trovato o non collegato alla richiesta");
        }

        return $this->redirectToRoute("documenti_richiesta_attuazione", ['id_richiesta' => $richiesta->getId(),]);
    }

    public function riepilogoBeneficiari($richiesta) {

        $dati = array("richiesta" => $richiesta, "menu" => "beneficiari");

        return $this->render("AttuazioneControlloBundle:PA/Richieste:riepilogoBeneficiari.html.twig", $dati);
    }

    public function elencoPagamenti($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);

        $dati = array("richiesta" => $richiesta, "menu" => "pagamenti");
        return $this->render("AttuazioneControlloBundle:PA/Richieste:elencoPagamenti.html.twig", $dati);
    }

    public function dettaglioPagamento($pagamento) {
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $proponente = $richiesta->getMandatario();
        $avanzamento = $this->avanzamentoPianoCostiPagamento($richiesta, $proponente, null, $pagamento->getId());

        $avanzamento["menu"] = "pagamenti";
        $dati = $avanzamento; //array("pagamento" => $pagamento, "menu" => "pagamenti");

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Attuazione progetti", $this->generateUrl("elenco_gestione_pa"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti_attuazione", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento");

        //TODO verificare dove viene renderizzato questo twig e valutare se devono essere aggiunti i warning sulla presenza di variazioni o proroghe pendenti. (Vedi AttuazioneControlloBundle/Service/Istruttoria/GestorePagamentiBase.php)
        return $this->render("AttuazioneControlloBundle:PA/Richieste:dettaglioPagamento.html.twig", $dati);
    }

    public function dettaglioGiustificativo($giustificativo) {

        $em = $this->getEm();

        $pagamento = $giustificativo->getPagamento();
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $annualita = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($richiesta->getMandatario()->getId());

        $dati = array("giustificativo" => $giustificativo, "annualita" => $annualita, "menu" => "pagamenti");

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Attuazione progetti", $this->generateUrl("elenco_gestione_pa"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti_attuazione", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento_attuazione", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio giustificativo");

        $dati["documenti"] = $em->getRepository("AttuazioneControlloBundle\Entity\DocumentoGiustificativo")->findBy(array("giustificativo_pagamento" => $giustificativo->getId()));

        return $this->render("AttuazioneControlloBundle:PA/Richieste:dettaglioGiustificativo.html.twig", $dati);
    }

    public function dettaglioQuietanza($quietanza) {
        $giustificativo = $quietanza->getGiustificativoPagamento();
        $pagamento = $giustificativo->getPagamento();
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $dati = array("quietanza" => $quietanza, "menu" => "pagamenti");

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Attuazione progetti", $this->generateUrl("elenco_gestione_pa"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti_attuazione", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento_attuazione", array("id_pagamento" => $pagamento->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio giustificativo", $this->generateUrl("dettaglio_giustificativo_attuazione", array("id_giustificativo" => $giustificativo->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio quietanza");

        return $this->render("AttuazioneControlloBundle:PA/Richieste:dettaglioQuietanza.html.twig", $dati);
    }

    public function calcolaAvanzamentoPianoCosti($richiesta, $proponente, $annualita = null, $pagamento_rif_id = null) {
        $ultima_variazione = $richiesta->getAttuazioneControllo()->getUltimaVariazioneApprovata();
        $avanzamento = array();
        $totali = array();
        $variazione_presente = false;
        $variazione = null;

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
                    $importo_variato = $variazione_voce->{"getImportoApprovatoAnno" . $annualita}();
                } else {
                    $importo_variato = $variazione_voce->sommaImportiApprovati();
                }
            } else {
                $importo_variato = $importo_ammesso;
            }

            $importo_rendicontato = 0;
            $importo_rendicontato_ammesso = 0;
            $importo_spesa_sup_massimali = 0;
            foreach ($voce->getVociGiustificativi() as $voce_giustificativo) {
                // il try catch serve per gestire la cancellazione logica, se l'oggetto è cancellato viene lanciata un'eccezione
                try {
                    $pagamento = $voce_giustificativo->getGiustificativoPagamento()->getPagamento();
                    if (!is_null($pagamento_rif_id) && $pagamento->getId() > $pagamento_rif_id) {
                        continue;
                    }
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

                    if ($pagamento->getStato()->getCodice() == "PAG_PROTOCOLLATO" && ($voce_giustificativo->getAnnualita() == $annualita || is_null($annualita))) {
                        if (!is_null($voce_giustificativo->getImportoNonAmmessoPerSuperamentoMassimali())) {
                            $importo_spesa_sup_massimali += $voce_giustificativo->getImportoNonAmmessoPerSuperamentoMassimali();
                        }
                    }
                } catch (\Exception $e) {
                    
                }
            }

            if ($importo_spesa_sup_massimali != 0) {
                $importo_rendicontato_ammesso = $importo_rendicontato_ammesso - $importo_spesa_sup_massimali;
            }

            if ($voce->getPianoCosto()->getCodice() != "TOT") {
                $totali[$sezione->getId()]["rendicontato"] += $importo_rendicontato;
                $totali[$sezione->getId()]["pagato"] += $importo_rendicontato_ammesso;
            } else {
                $importo_rendicontato = $totali[$sezione->getId()]["rendicontato"];
                $importo_rendicontato_ammesso = $totali[$sezione->getId()]["pagato"];
            }

            $avanzamento[$sezione->getId()]["voci"][] = array(
                "voce" => $voce,
                "ammesso" => $importo_ammesso,
                "variato" => $importo_variato,
                "rendicontato" => $importo_rendicontato,
                "pagato" => $importo_rendicontato_ammesso
            );
        }

        return $avanzamento;
    }

    public function avanzamentoPianoCosti($richiesta, $proponente, $anno) {
        $avanzamento = $this->calcolaAvanzamentoPianoCosti($richiesta, $proponente, $anno, null);
        $annualita = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($richiesta->getMandatario()->getId());

        $variazione_presente = false;
        $variazione = null;

        $ultima_variazione = $richiesta->getAttuazioneControllo()->getUltimaVariazioneApprovata();
        if (!is_null($ultima_variazione) && !$ultima_variazione->getIgnoraVariazione()) {
            $variazione_presente = true;
            $variazione = $ultima_variazione;
        }
        $dati = array(
            "richiesta" => $richiesta,
            "avanzamento" => $avanzamento,
            "anno" => is_null($anno) ? "Totali" : "Annualità " . $annualita[$anno],
            "menu" => "piano_costi",
            "variazione_presente" => $variazione_presente,
            "variazione" => $variazione
        );

        return $this->render("AttuazioneControlloBundle:PA/Richieste:avanzamentoPianoCosti.html.twig", $dati);
    }

    public function avanzamentoPianoCostiPagamento($richiesta, $proponente, $anno, $pagamento_rif_id) {
        $em = $this->getEm();
        $avanzamento = $this->calcolaAvanzamentoPianoCosti($richiesta, $proponente, $anno, $pagamento_rif_id);
        $annualita = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($richiesta->getMandatario()->getId());
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($pagamento_rif_id);
        $variazione_presente = false;
        $variazione = null;

        $ultima_variazione = $richiesta->getAttuazioneControllo()->getUltimaVariazioneApprovata();
        if (!is_null($ultima_variazione) && !$ultima_variazione->getIgnoraVariazione()) {
            $variazione_presente = true;
            $variazione = $ultima_variazione;
        }
        $dati = array(
            "richiesta" => $richiesta,
            "avanzamento" => $avanzamento,
            "anno" => is_null($anno) ? "Totali" : "Annualità " . $annualita[$anno],
            "menu" => "piano_costi",
            "variazione_presente" => $variazione_presente,
            "variazione" => $variazione,
            "pagamento" => $pagamento
        );

        return $dati;
    }

    public function datiRichiestaPA($richiesta) {

        $attuazioneControllo = $richiesta->getAttuazioneControllo();

        $opzioni = array();
        $opzioni["url_indietro"] = $this->generateUrl("elenco_gestione_beneficiario");

        $dati = array("istruttoria" => $richiesta->getIstruttoria(), 'attuazione_controllo' => $attuazioneControllo, "menu" => "richiesta");

        return $this->render("AttuazioneControlloBundle:PA/Richieste:datiRichiesta.html.twig", $dati);
    }

    protected static function eliminaElementiCollection(
        \RichiesteBundle\Entity\Richiesta $richiesta, ArrayCollection $elementiDb, \Doctrine\ORM\EntityManagerInterface $em) {
        foreach ($elementiDb as $statoDb) {
            if (false === $richiesta->getMonStatoProgetti()->contains($statoDb)) {
                $em->remove($statoDb);
            }
        }
    }

    /**
     * @param Richiesta $richiesta
     * @return array[]
     */
    public function getQuadroEconomico(Richiesta $richiesta) {
        $avanzamento = $this->calcolaAvanzamentoPianoCosti($richiesta, NULL);
        $vociPianoCosto = \array_reduce(
            $avanzamento, function ($carry, $sezione) {
                return \array_merge($carry, $sezione['voci']);
            }, array()
        );
        $quadroEconomico = \array_reduce(
            $vociPianoCosto, '\AttuazioneControlloBundle\Service\GestoreRichiesteATCBase::aggiungiVoceAQuadroEconomico', array()
        );
        ksort($quadroEconomico);

        return $quadroEconomico;
    }

    public static function aggiungiVoceAQuadroEconomico($quadroEconomico, $voceAvanzamento) {
        /** @var \RichiesteBundle\Entity\VocePianoCosto $voce */
        $voce = $voceAvanzamento['voce'];
        $pianoCosto = $voce->getPianoCosto();
        $istanzaVoceSpesa = $pianoCosto->getMonVoceSpesa();
        if (\is_null($istanzaVoceSpesa)) {
            return $quadroEconomico;
        }

        $voceSpesa = (string) $istanzaVoceSpesa;
        $importo = $voceAvanzamento['ammesso'];

        if (\array_key_exists($voceSpesa, $quadroEconomico)) {
            $quadroEconomico[$voceSpesa]['importo'] += $importo;
        } else {
            $voce = array(
                'voce' => $istanzaVoceSpesa,
                'importo' => $importo,
            );
            $quadroEconomico[$voceSpesa] = $voce;
        }

        return $quadroEconomico;
    }

    public function gestioneIndicatori(Richiesta $richiesta): Response {
        /** @var GestoreIndicatoreService $monitoraggioService */
        $monitoraggioService = $this->container->get('monitoraggio.indicatori_output');
        $gestoreIndicatori = $monitoraggioService->getGestore($richiesta);
        $indicatori = $gestoreIndicatori->getIndicatoriManuali();
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
        $paginaService->aggiungiElementoBreadcrumb("Indicatori di output");

        $dati = array("richiesta" => $richiesta, "indicatori" => $mv);
        return $this->render("AttuazioneControlloBundle:RichiesteATC:monitoraggioIndicatori.html.twig", $dati);
    }

    public function gestioneSingoloIndicatore(Richiesta $richiesta, IndicatoreOutput $indicatore): Response {
        //Indicatore
        $formIndicatore = $this->createForm(IndicatoreOutputType::class, $indicatore, [
            'to_beneficiario' => true,
            'disabled' => false,
        ]);
        $request = $this->getCurrentRequest();
        $formIndicatore->add('submit', SalvaIndietroType::class, [
            'url' => false,
            'disabled' => false,
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

        $indietro = $this->generateUrl('gestione_monitoraggio_indicatori_ben', [
            'id_richiesta' => $richiesta->getId()
        ]);
        $formDocumento->add('submit', SalvaIndietroType::class, [
            'url' => $indietro,
            'disabled' => false,
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
        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Indicatori di output", $this->generateUrl("gestione_monitoraggio_indicatori_ben", array('id_richiesta' => $richiesta->getId())));
        $paginaService->aggiungiElementoBreadcrumb("Indicatore");

        $dati = [
            'form_indicatore' => $formIndicatore->createView(),
            'form_documento' => $formDocumento->createView(),
            'richiesta' => $richiesta,
        ];
        return $this->render("AttuazioneControlloBundle:RichiesteATC:monitoraggioSingoloIndicatore.html.twig", $dati);
    }

    public function eliminaDocumentoIndicatoreOutput(Richiesta $richiesta, IndicatoreOutput $indicatore, DocumentoFile $documento) {
        $em = $this->getEm();

        try {
            $documentoDaEliminare = $indicatore->removeDocumenti($documento);
            $em->remove($documentoDaEliminare);
            $em->flush();
            $this->addFlash('success', "Documento rimosso correttamente");
        } catch (\Exception $e) {
            $this->addError("Errore durante il salvataggio delle informazioni");
        }
        return $this->redirectToRoute("gestione_monitoraggio_singolo_indicatore_ben", [
                'id_richiesta' => $richiesta->getId(),
                'id_indicatore' => $indicatore->getId(),
        ]);
    }

    public function gestioneFasiProcedurali(Richiesta $richiesta) {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $form = $this->createForm(RichiestaFaseProceduraleType::class, $richiesta, array(
            'url_indietro' => $this->generateUrl('elenco_gestione_beneficiario'),
            'disabled' => false,
            'to_beneficiario' => true,
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

        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Fasi procedurali");

        $dati = array("richiesta" => $richiesta, "form" => $form->createView());
        return $this->render("AttuazioneControlloBundle:RichiesteATC:monitoraggioFasiProcedurali.html.twig", $dati);
    }

    public function gestioneImpegni(Richiesta $richiesta) {
        $em = $this->getEm();
        $impegni = $richiesta->getMonImpegni();

        $dati = array(
            "richiesta" => $richiesta,
            "impegni" => $impegni
        );
        return $this->render("AttuazioneControlloBundle:RichiesteATC:monitoraggioElencoImpegni.html.twig", $dati);
    }

    public function gestioneFormImpegno(Richiesta $richiesta, RichiestaImpegni $impegno = null) {
        $em = $this->getEm();
        if (\is_null($impegno)) {
            $impegno = new RichiestaImpegni($richiesta);
            $em->persist($impegno);
        }

        $form = $this->createForm(ImpegnoType::class, $impegno, array(
            'url_indietro' => $this->generateUrl('gestione_monitoraggio_impegni_ben', array('id_richiesta' => $richiesta->getId())),
            'disabled' => false,
        ));

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($impegno->getMonImpegniAmmessi()->isEmpty()) {
                    $ammesso = new ImpegniAmmessi($impegno);
                    $impegno->addMonImpegniAmmessi($ammesso);
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
            'disabled' => false,
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
                $this->container->get('logger')->error($e->getMessage(), ['id_impegno' => $impegno->getId(), 'id_richiesta' => $richiesta->getId()]);
                $this->addError('Errore durante il salvataggio dei dati');
            }
        }
        $renderViewData = array(
            'form' => $form->createView(),
            'formDoc' => $formDoc->createView(),
            'richiesta' => $richiesta,
        );
        return $this->render('AttuazioneControlloBundle:RichiesteATC:richiestaImpegno.html.twig', $renderViewData);
    }

    public function eliminaDocumentoImpegno(Richiesta $richiesta, DocumentoImpegno $documento): Response {
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
                'id_richiesta' => $richiesta->getId()
            ]);
            $this->addError('Errore durante il salvataggio dei dati');
        }
        return $this->redirectToRoute("gestione_modifica_monitoraggio_impegni_ben", [
                'id_richiesta' => $richiesta->getId(),
                'id_impegno' => $impegno->getId(),
        ]);
    }

    public function eliminaImpegno(Richiesta $richiesta, $id_impegno) {
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
        return $this->redirectToRoute('gestione_monitoraggio_impegni_ben', array('id_richiesta' => $richiesta->getId()));
    }

    public function gestioneProceduraAggiudicazione(Richiesta $richiesta): Response {
        $em = $this->getEm();
        $procedureAggiudicazione = $richiesta->getMonProcedureAggiudicazione();
        $atc = $richiesta->getAttuazioneControllo();

        $form = $this->createForm(ProgettoProceduraAggiudicazioneType::class, $atc, [
            // 'url_indietro' => $this->generateUrl('dettaglio_pagamento', ['id_pagamento' => $pagamento->getId()]),
            'disabled' => false,
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

        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Procedure aggiudicazione");

        $mv = array(
            'form' => $form->createView(),
            'richiesta' => $richiesta,
            'procedureAggiudicazione' => $procedureAggiudicazione,
        );
        return $this->render('AttuazioneControlloBundle:RichiesteATC:proceduraAggiudicazione.html.twig', $mv);
    }

    public function gestioneModificaProceduraAggiudicazione(Richiesta $richiesta, $id_procedura_aggiudicazione) {
        $gara = NULL;
        $em = $this->getEm();
        /** @var \AttuazioneControlloBundle\Repository\ProceduraAggiudicazioneRepository $procedureRepo */
        $procedureRepo = $em->getRepository('AttuazioneControlloBundle:ProceduraAggiudicazione');

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
                'url_indietro' => $this->generateUrl("gestione_monitoraggio_procedura_aggiudicazione_ben", array("id_richiesta" => $richiesta->getId())),
                'disabled' => false,
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
        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Procedure aggiudicazione", $this->generateUrl("gestione_monitoraggio_procedura_aggiudicazione_ben", array('id_richiesta' => $richiesta->getId())));
        $paginaService->aggiungiElementoBreadcrumb("Procedura");

        $mv = array(
            'form' => $form->createView(),
        );
        return $this->render('AttuazioneControlloBundle:RichiesteATC:modificaProceduraAggiudicazione.html.twig', $mv);
    }

    /**
     * @param Pagamento $pagamento
     */
    public function gestioneEliminaProceduraAggiudicazione(Richiesta $richiesta, $id_procedura_aggiudicazione) {

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
        return $this->redirectToRoute('gestione_monitoraggio_procedura_aggiudicazione_ben', array('id_richiesta' => $richiesta->getId()));
    }

    public function documentiRichiestaAvvio($richiesta) {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documenti_caricati = $em->getRepository("RichiesteBundle\Entity\DocumentoRichiesta")->findDocumentiAvvioCaricati($richiesta->getId());

        $tipo = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneByCodice("AVVIO_PROGETTO");
        $documento_file = new DocumentoFile();
        $documento_file->setTipologiaDocumento($tipo);

        $documento_richiesta = new \RichiesteBundle\Entity\DocumentoRichiesta();
        $documento_richiesta->setDocumentoFile($documento_file);

        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileSimpleType', $documento_file);
        $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $this->container->get("documenti")->carica($documento_file, 0, $richiesta);

                    $documento_richiesta->setDocumentoFile($documento_file);
                    $documento_richiesta->setRichiesta($richiesta);
                    $em->persist($documento_richiesta);

                    $em->flush();

                    $this->addFlash('success', "Documento caricato correttamente");
                    return $this->redirectToRoute("gestione_documenti_avvio", array("id_richiesta" => $richiesta->getId()));
                } catch (\Exception $e) {
                    $this->container->get("logger")->error($e->getMessage());
                    $this->addFlash('error', "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
                }
            }
        }

        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Documenti avvio progetto");

        $dati = array(
            "documenti" => $documenti_caricati,
            "form" => $form->createView(),
            "richiesta" => $richiesta
        );

        return $this->render("AttuazioneControlloBundle:RichiesteATC:documentiAvvio.html.twig", $dati);
    }

    public function gestioneAvvioProgetto($richiesta) {
        $atc = $richiesta->getAttuazioneControllo();
        $disabilita_data = false;
        $opzioni["url_indietro"] = $this->generateUrl("elenco_gestione_beneficiario");
        if (!is_null($atc->getDataAvvioEffettivo())) {
            $disabilita_data = true;
        }
        $formData = $this->createForm(\AttuazioneControlloBundle\Form\DateAvvioProgettoType::class, $atc, [
            'disabled' => $disabilita_data,
            'url_indietro' => $opzioni["url_indietro"],
        ]);

        $request = $this->getCurrentRequest();
        $formData->handleRequest($request);
        $em = $this->getEm();
        if ($formData->isSubmitted() && $formData->isValid()) {
            try {
                $em->flush($atc);
                return $this->redirectToRoute("gestione_documenti_avvio", array("id_richiesta" => $richiesta->getId()));
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage(), ['id' => $atc->getId()]);
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }

        $documenti_caricati = $em->getRepository("RichiesteBundle\Entity\DocumentoRichiesta")->findDocumentiAvvioCaricati($richiesta->getId());

        $tipo = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneByCodice("AVVIO_PROGETTO");
        $documento_file = new DocumentoFile();
        $documento_file->setTipologiaDocumento($tipo);

        $documento_richiesta = new \RichiesteBundle\Entity\DocumentoRichiesta();
        $documento_richiesta->setDocumentoFile($documento_file);

        $formDocumento = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileSimpleType', $documento_file);
        $formDocumento->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));

        $formDocumento->handleRequest($request);
        if ($formDocumento->isSubmitted() && $formDocumento->isValid()) {
            try {
                $this->container->get("documenti")->carica($documento_file, 0, $richiesta);

                $documento_richiesta->setDocumentoFile($documento_file);
                $documento_richiesta->setRichiesta($richiesta);
                $em->persist($documento_richiesta);

                $em->flush();

                $this->addFlash('success', "Documento caricato correttamente");
                return $this->redirectToRoute("gestione_documenti_avvio", array("id_richiesta" => $richiesta->getId()));
            } catch (\Exception $e) {
                $this->container->get("logger")->error($e->getMessage());
                $this->addFlash('error', "Si è verificato un errore a sistema. Si prega di riprovare o contattare l'assistenza");
            }
        }
        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Documenti avvio progetto");

        $dati = array(
            "documenti" => $documenti_caricati,
            "form_documento" => $formDocumento->createView(),
            'form_data' => $formData->createView(),
            "richiesta" => $richiesta
        );

        return $this->render("AttuazioneControlloBundle:RichiesteATC:documentiAvvio.html.twig", $dati);
    }

    public function estraiProgettiUniversoVolantinoAtc() {
        $richieste = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->getRichiesteInAttuazioneVolantino();

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Sfinge 2104-2020")
            ->setLastModifiedBy("Sfinge 2104-2020")
            ->setTitle("Office 2005 XLSX Test Document")
            ->setSubject("Office 2005 XLSX Test Document")
            ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
            ->setKeywords("office 2005 openxml php")
            ->setCategory("Test result file");

        $colonne = [];
        $lettera = 'A';
        while ($lettera !== 'AAAA') {
            $colonne[] = $lettera++;
        }

        $riga = 1;

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();

        $column = 0;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Ragione Sociale Denominazione');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(60);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Codice postale / Postcode');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(25);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Paese / Country');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(15);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Comune / Municipality');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(25);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Provincia / Province');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(20);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'C.F.');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(20);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'P.Iva');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(20);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Mail');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(35);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Pec');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(35);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Asse');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(10);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Procedura');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(70);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Codice Progetto');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(15);
        $column++;
//        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Atto ammissibilità');
//        $column++;
//        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Atto di concessione');
//        $column++;
//        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Atto modifica di concessione');
//        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Investimento ammesso');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(20);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Contributo concesso');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(20);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Data del contributo concesso');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(20);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'CUP');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(20);
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Stato progetto');
        $activeSheet->getColumnDimension($colonne[$column])->setWidth(20);

        $column = 0;
        foreach ($richieste as $key => $richiesta) {

            $riga++;
            $phpExcelObject->setActiveSheetIndex(0);

            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['soggetto']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['cap']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['stato']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['comune']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['provincia']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['codice_fiscale']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['partita_iva']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['email']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['email_pec']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['asse_prc']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['procedura']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['protocollo']);
            $column++;
//            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['amministrativo']);
//            $column++;
//            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['conncessione']);
//            $column++;
//            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['mod_concessione']);
//            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['investimento_ammesso']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['contributo_concesso']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['data_contributo_concesso']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['cup']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta['stato_progetto']);

            $column = 0;
        }

        // create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione_volantino.xls');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    public function riepilogoAccettaContributo($id_richiesta) {
        $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
        $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());

        if (is_null($soggetto)) {
            return $this->addErrorRedirect("Soggetto non valido", "home");
        }

        $em = $this->getEm();

        $richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $attuazioneControllo = $richiesta->getAttuazioneControllo();
        $opzioni["url_indietro"] = $this->generateUrl("elenco_gestione_beneficiario");

        $datiBancariProponenti = $richiesta->getMandatario()->getDatiBancari()->last();

        $dati = array("richiesta" => $richiesta,
            "atc" => $attuazioneControllo,
            "datiBancariProponenti" => $datiBancariProponenti,
            "dateProgetto" => $this->getDateProgetto($id_richiesta),
        );

        return $this->render("AttuazioneControlloBundle:RichiesteATC:riepilogoAccettaContributo.html.twig", $dati);
    }

    /**
     * Si guarda se ci sono proroghe approvate
     * altrimenti si leggono da istruttoria richiesta (che sarebbero le date impostate nel passaggio in ATC)
     */
    protected function getDateProgetto($id_richiesta) {
        $em = $this->getEm();
        $richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $istruttoria = $richiesta->getIstruttoria();

        if (!is_null($richiesta->getAttuazioneControllo()->getUltimaProrogaAvvioApprovata())) {
            $dataAvvioProgetto = $richiesta->getAttuazioneControllo()->getUltimaProrogaAvvioApprovata()->getDataAvvioApprovata();
        } else {
            $dataAvvioProgetto = $istruttoria->getDataAvvioProgetto();
        }

        if (!is_null($richiesta->getAttuazioneControllo()->getUltimaProrogaFineApprovata())) {
            $dataTermineProgetto = $richiesta->getAttuazioneControllo()->getUltimaProrogaFineApprovata()->getDataFineApprovata();
        } else {
            $dataTermineProgetto = $istruttoria->getDataTermineProgetto();
        }

        $date = new \stdClass();
        $date->dataAvvioProgetto = $dataAvvioProgetto;
        $date->dataTermineProgetto = $dataTermineProgetto;

        return $date;
    }

}
