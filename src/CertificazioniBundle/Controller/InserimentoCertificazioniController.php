<?php

namespace CertificazioniBundle\Controller;

use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use CertificazioniBundle\Entity\StatoCertificazione;
use DocumentoBundle\Entity\DocumentoFile;
use CertificazioniBundle\Entity\DocumentoCertificazione;
use CertificazioniBundle\Entity\DocumentoCertificazionePagamento;
use DocumentoBundle\Component\ResponseException;

/**
 * @Route("/inserimento")
 */
class InserimentoCertificazioniController extends BaseController {

    /**
     * @Route("/aggiungi_certificazione", name="aggiungi_certificazione")
     * @PaginaInfo(titolo="Aggiungi certificazione", sottoTitolo="pagina per l'aggiunta di una certificazione")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Aggiungi certificazione")})
     */
    public function aggiungiCerticazioneAction() {
        $em = $this->getEm();
        $certificazioni = $em->getRepository("CertificazioniBundle\Entity\Certificazione")->findBy(array("stato" => array(
                $em->getRepository("CertificazioniBundle\Entity\StatoCertificazione")->findOneBy(array("codice" => StatoCertificazione::CERT_INSERITA)),
                $em->getRepository("CertificazioniBundle\Entity\StatoCertificazione")->findOneBy(array("codice" => StatoCertificazione::CERT_PREVALIDATA)),
                $em->getRepository("CertificazioniBundle\Entity\StatoCertificazione")->findOneBy(array("codice" => StatoCertificazione::CERT_VALIDATA)),
                $em->getRepository("CertificazioniBundle\Entity\StatoCertificazione")->findOneBy(array("codice" => StatoCertificazione::CERT_INVIATA))
        )));

        if (count($certificazioni) > 0) {
            $this->addFlash("error", "Impossibile aggiungere una nuova certificazione se quella in corso non è stata ancora approvata");
            return $this->redirectToRoute("elenco_certificazioni");
        }

        $certificazione = new \CertificazioniBundle\Entity\Certificazione();

        $options = array();
        $options["url_indietro"] = $this->generateUrl("elenco_certificazioni");

        $form = $this->createForm("CertificazioniBundle\Form\CertificazioneType", $certificazione, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $em->beginTransaction();
                    $em->persist($certificazione);
                    $em->flush();
                    $this->container->get("sfinge.stati")->avanzaStato($certificazione, StatoCertificazione::CERT_INSERITA);
                    $em->flush();
                    $em->commit();
                    $this->addFlash("success", "La certificazione è stata correttamente salvata");
                    return $this->redirectToRoute("elenco_certificazioni");
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();

        return $this->render("CertificazioniBundle:Certificazioni:aggiungiCertificazione.html.twig", $dati);
    }

    /**
     * @Route("/{id_certificazione}/elimina", name="elimina_certificazione")  
     */
    public function eliminaCertificazioneAction($id_certificazione) {
        $this->get('base')->checkCsrf('token');

        $em = $this->getEm();
        $certificazione = $em->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);

        if (!$certificazione->isEliminabile()) {
            $this->addFlash("error", "L'operazione non è compatibile con lo stato della certificazione");
            return $this->redirectToRoute("elenco_certificazioni");
        }

        try {
            $em->beginTransaction();
            foreach ($certificazione->getPagamenti() as $certPag) {
                $em->remove($certPag);
                $em->flush();
            }
            $em->remove($certificazione);
            $em->flush();
            $em->commit();
            $this->addFlash("success", "La certificazione è stata correttamente eliminata");
        } catch (ResponseException $e) {
            $this->addFlash("error", "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza");
        }

        return $this->redirectToRoute("elenco_certificazioni");
    }

    /**
     * @Route("/{id_certificazione}/associa_pagamenti_pulisci", name="associa_pagamenti_pulisci")
     */
    public function associaPagamentiPulisciAction($id_certificazione) {
        $this->get("ricerca")->pulisci(new \CertificazioniBundle\Form\Entity\RicercaPagamentiDaCertificare());
        return $this->redirectToRoute("associa_pagamenti_certificazione", array("id_certificazione" => $id_certificazione));
    }

    /**
     * @Route("/{id_certificazione}/associa_pagamenti/{sort}/{direction}/{page}", defaults={"sort" = "pag.id", "direction" = "asc", "page" = "1"}, name="associa_pagamenti_certificazione")
     * @PaginaInfo(titolo="Associazione pagamenti certificazione",sottoTitolo="permette di associare dei pagamenti alla certificazione")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Associazione pagamenti certificazione")})
     */
    public function associaPagamentiAction($id_certificazione) {

        $datiRicerca = new \CertificazioniBundle\Form\Entity\RicercaPagamentiDaCertificare();
        $datiRicerca->setConsentiRicercaVuota(false);

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        $em = $this->getEm();
        $certificazione = $em->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);

        $options = array();
        $options["url_indietro"] = $this->generateUrl("elenco_certificazioni");

        $certificazioni_pagamenti_indicizzati = array();
        if (!is_null($certificazione->getPagamenti())) {
            foreach ($certificazione->getPagamenti() as $certificazione_pagamento) {
                $certificazioni_pagamenti_indicizzati[$certificazione_pagamento->getPagamento()->getId()] = $certificazione_pagamento;
            }
        }

        foreach ($risultato["risultato"] as $pagamento) {
            $certificazione_pagamento = new \CertificazioniBundle\Entity\CertificazionePagamento();
            /*
             * In caso di pubblici non è detto che ci sia il mandato ma il pagamento è
             * comunque certificabile con il valore inserito in chcklist.
             * Ovviamente se la checklist non c'è il pagamento va proposto
             */
            $pagamento->setImportoErogabileChecklist($this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->getValoreFromChecklist($pagamento, 'CONTRIBUTO_EROGABILE'));

            $certificazione_pagamento->setPagamento($pagamento);

            if (isset($certificazioni_pagamenti_indicizzati[$pagamento->getId()])) {
                $certificazione_pagamento->setSelezionato(true);
                $certificazione_pagamento->setImporto($certificazioni_pagamenti_indicizzati[$pagamento->getId()]->getImporto());
                if ($certificazioni_pagamenti_indicizzati[$pagamento->getId()]->getAiutoDiStato()) {
                    $certificazione_pagamento->setAiutoDiStato(true);
                }
                if ($certificazioni_pagamenti_indicizzati[$pagamento->getId()]->getStrumentoFinanziario()) {
                    $certificazione_pagamento->setStrumentoFinanziario(true);
                }
            }

            $certificazione->addPagamentoEsteso($certificazione_pagamento);
        }

        $form = $this->createForm("CertificazioniBundle\Form\AssociazioneCertificazionePagamentiType", $certificazione, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            foreach ($form->get("pagamenti_estesi")->all() as $form_pagamento) {
                $certificazione_pagamento_esteso = $form_pagamento->getData();
                if ($certificazione_pagamento_esteso->getSelezionato()) {
                    $a = $certificazione_pagamento_esteso->getImporto();
                    $c = $certificazione_pagamento_esteso->getPagamento()->getImportoCertificato();
                    if (!is_null($certificazione_pagamento_esteso->getPagamento()) && !is_null($certificazione_pagamento_esteso->getPagamento()->getMandatoPagamento())) {
                        $b = $certificazione_pagamento_esteso->getPagamento()->getMandatoPagamento()->getImportoPagato();
                        if ($a + $c > $b) {
                            $form_pagamento->get("importo")->addError(new \Symfony\Component\Form\FormError("Importo maggiore di quello ancora certificabile"));
                        }
                    }
                }
            }

            if ($form->isValid()) {

                foreach ($form->get("pagamenti_estesi")->all() as $form_pagamento) {
                    $certificazione_pagamento_esteso = $form_pagamento->getData();

                    if (isset($certificazioni_pagamenti_indicizzati[$certificazione_pagamento_esteso->getPagamento()->getId()])) {
                        $certificazione_pagamento_nuovo = $certificazioni_pagamenti_indicizzati[$certificazione_pagamento_esteso->getPagamento()->getId()];
                    } else {
                        $certificazione_pagamento_nuovo = new \CertificazioniBundle\Entity\CertificazionePagamento();
                    }

                    if ($certificazione_pagamento_esteso->getSelezionato()) {
                        $certificazione_pagamento_nuovo->setPagamento($certificazione_pagamento_esteso->getPagamento());
                        $certificazione_pagamento_nuovo->setImporto($certificazione_pagamento_esteso->getImporto());
                        $certificazione_pagamento_nuovo->setAiutoDiStato($certificazione_pagamento_esteso->getAiutoDiStato());
                        $certificazione_pagamento_nuovo->setStrumentoFinanziario($certificazione_pagamento_esteso->getStrumentoFinanziario());
                        $certificazione->addPagamento($certificazione_pagamento_nuovo);
                    } else {
                        if (!is_null($certificazione_pagamento_nuovo->getId())) {
                            $em->remove($certificazione_pagamento_nuovo);
                        }
                    }
                }

                $em = $this->getEm();
                try {
                    $em->flush();
                    $this->addFlash("success", "La certificazione è stata correttamente salvata");
                    return $this->redirectToRoute("elenco_certificazioni");
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza. " . $e->getMessage());
                }
            }
        }

        $dati = array('risultati' => $risultato["risultato"], "formRicerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]);
        $dati["form"] = $form->createView();
        $dati["certificazione"] = $certificazione;
        $dati["importi_asse"] = $this->riepilogoPagamentiPerAsse($id_certificazione);

        return $this->render("CertificazioniBundle:Certificazioni:associaPagamenti.html.twig", $dati);
    }

    /**
     * @Route("/{id_documento}/cancella", name="cancella_documento_certificazione")
     * @Template("CertificazioniBundle:Certificazioni:dettaglioCertificazione.html.twig")
     * @PaginaInfo(titolo="Dettaglio certificazione", sottoTitolo="pagina di dettaglio della certificazione")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Dettaglio certificazione")})
     */
    public function cancellaDocumentoCertificazioneAction($id_documento) {

        $em = $this->getEm();
        $documento = $em->getRepository("CertificazioniBundle\Entity\DocumentoCertificazione")->find($id_documento);
        $id_certificazione = $documento->getCertificazione()->getId();
        try {
            $em->remove($documento->getDocumentoFile());
            $em->remove($documento);
            $em->flush();
            return $this->addSuccessRedirect("Documento eliminato correttamente", "dettaglio_certificazione", array("id_certificazione" => $id_certificazione));
        } catch (ResponseException $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    /**
     * @Route("/{id_certificazione}/{id_pagamenti_certificati}/carica_cl", name="carica_cl")
     * @PaginaInfo(titolo="Carica Check List",sottoTitolo="carica checklist per il pagamento certificato")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Elenco pagamenti", route="elenco_pagamenti_certificati", parametri={"id_certificazione"}),
     *                       @ElementoBreadcrumb(testo="Carica Check List")})
     */
    public function caricaCLAction($id_certificazione, $id_pagamenti_certificati) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documento_certificazione_pagamento = new DocumentoCertificazionePagamento();
        $documento_file = new DocumentoFile();

        $certificazione_pagamento = $em->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->find($id_pagamenti_certificati);

        $documenti_caricati_certificati_pagamento = $em->getRepository("CertificazioniBundle\Entity\DocumentoCertificazionePagamento")->findDocumentiCaricati($id_pagamenti_certificati);


        // Form per caricamento documento certificatore agrea	
        $tipo_doc_checklist = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array('codice' => "CHECKLIST_CERT_PAG"));

        $form_upload_checklist = $this->createForm("DocumentoBundle\Form\Type\DocumentoFileType", $documento_file, array('tipo' => $tipo_doc_checklist)
        );
        $form_upload_checklist->add("submit", "Symfony\Component\Form\Extension\Core\Type\SubmitType", array("label" => "Salva"));

        $form_upload_checklist_view = $form_upload_checklist->createView();



        if ($request->isMethod("POST")) {

            $form_upload_checklist->handleRequest($request);
            if ($form_upload_checklist->isValid()) {
                try {

                    $this->container->get("documenti")->carica($documento_file, 0);

                    $documento_certificazione_pagamento->setDocumentoFile($documento_file);
                    $documento_certificazione_pagamento->setCertificazionePagamento($certificazione_pagamento);
                    $em->persist($documento_certificazione_pagamento);

                    $em->flush();

                    return $this->addSuccessRedirect("Documento caricato correttamente", "carica_cl", array('id_certificazione' => $id_certificazione,
                                'id_pagamenti_certificati' => $id_pagamenti_certificati)
                    );
                } catch (ResponseException $e) {
                    $this->addFlash("error", $e->getMessage());
                }
            }
        }

        $dati = array(
            'id_certificazione' => $id_certificazione,
            'documenti_cert_pagamenti' => $documenti_caricati_certificati_pagamento,
            'form_upload_checklist_view' => $form_upload_checklist_view,
        );

        return $this->render("CertificazioniBundle:Certificazioni:caricaCheckListCartPag.html.twig", $dati);
    }

    /**
     * @Route("/cancella_doc_cert_pag/{id_documento}", name="cancella_documento_certificazione_pagamento")
     * @PaginaInfo(titolo="Carica Check List",sottoTitolo="carica checklist per il pagamento certificato")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     */
    public function cancellaDocumentoCertificazionePagamentoAction($id_documento) {

        $em = $this->getEm();
        $documento = $em->getRepository("CertificazioniBundle\Entity\DocumentoCertificazionePagamento")->find($id_documento);

        $certificazione_pagamento = $documento->getCertificazionePagamento();

        $id_pagamenti_certificati = $certificazione_pagamento->getId();

        $certificazione = $certificazione_pagamento->getCertificazione();

        $id_certificazione = $certificazione->getId();

        try {
            $em->remove($documento->getDocumentoFile());
            $em->remove($documento);
            $em->flush();
            return $this->addSuccessRedirect("Documento eliminato correttamente", "carica_cl", array('id_certificazione' => $id_certificazione,
                        'id_pagamenti_certificati' => $id_pagamenti_certificati)
            );
        } catch (ResponseException $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    /**
     * @Route("/{id_certificazione}/prevalida", name="prevalida_certificazione")  
     */
    public function prevalidaCertificazioneAction($id_certificazione) {
        $this->get('base')->checkCsrf('token');

        $em = $this->getEm();
        $certificazione = $em->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);

        if (!$certificazione->isPrevalidabile()) {
            $this->addFlash("error", "L'operazione non è compatibile con lo stato della certificazione");
            return $this->redirectToRoute("elenco_certificazioni");
        }

        try {
            $this->container->get("sfinge.stati")->avanzaStato($certificazione, StatoCertificazione::CERT_PREVALIDATA);
            $em->flush();
            $this->addFlash("success", "La certificazione è stata correttamente prevalidata");
        } catch (ResponseException $e) {
            $this->addFlash("error", "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza");
        }

        return $this->redirectToRoute("elenco_certificazioni");
    }

    /**
     * @Route("/{id_certificazione}/invia", name="invia_certificazione")  
     */
    public function inviaCertificazioneAction($id_certificazione) {
        $this->get('base')->checkCsrf('token');

        $em = $this->getEm();
        $certificazione = $em->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);

        if (!$certificazione->isInviabile()) {
            $this->addFlash("error", "L'operazione non è compatibile con lo stato della certificazione");
            return $this->redirectToRoute("elenco_certificazioni");
        }

        try {
            $certificazione->setDataPropostaAdg(new \DateTime());
            $this->container->get("sfinge.stati")->avanzaStato($certificazione, StatoCertificazione::CERT_INVIATA);
            $em->flush();
            $this->addFlash("success", "La certificazione è stata correttamente inviata");
        } catch (ResponseException $e) {
            $this->addFlash("error", "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza");
        }

        return $this->redirectToRoute("elenco_certificazioni");
    }

    /**
     * @Route("/{id_pagamento}/decertificazioni", name="decertificazioni_pagamento")
     * @PaginaInfo(titolo="Decertificazioni pagamento",sottoTitolo="Elenco decertificazioni un pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco pagamenti", route="elenco_istruttoria_pagamenti"),
     *                       @ElementoBreadcrumb(testo="Decertificazione")})
     */
    public function decertificazioniPagamentoAction($id_pagamento) {
        $em = $this->getEm();
        $decertificazioni = $em->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->findDecertificazioniPagamento($id_pagamento);
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $dati['decertificazioni'] = $decertificazioni;
        $dati["pagamento"] = $pagamento;
        return $this->render("CertificazioniBundle:Certificazioni:decertificazioniPagamento.html.twig", $dati);
    }

    /**
     * @Route("/{id_pagamento}/decertifica", name="decertifica_pagamento")
     * @PaginaInfo(titolo="Decertifica pagamento",sottoTitolo="permette di decertificare un pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco pagamenti", route="elenco_istruttoria_pagamenti"),
     * 						 @ElementoBreadcrumb(testo="Elenco decertificazioni", route="decertificazioni_pagamento", parametri={"id_pagamento"}),
     *                       @ElementoBreadcrumb(testo="Decertificazione")})
     */
    public function decertificaPagamentoAction($id_pagamento) {
        $return_url = $this->generateUrl("decertificazioni_pagamento", array("id_pagamento" => $id_pagamento));
        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        if (is_null($pagamento->getImportoCertificato())) {
            $this->addFlash("error", "Il pagamento selezionato non è stato ancora certificato");
            return $this->redirect($return_url);
        }

        if (!is_null($pagamento->getImportoDecertificato()) && $pagamento->getImportoDecertificato() >= $pagamento->getImportoCertificato()) {
            $this->addFlash("error", "Per il pagamento selezionato è già stato decerticato un importo pari a quello certificato");
            return $this->redirect($return_url);
        }

        $stato_certificazione = $em->getRepository("CertificazioniBundle\Entity\StatoCertificazione")->findOneBy(array("codice" => StatoCertificazione::CERT_INSERITA));
        $certificazione = $em->getRepository("CertificazioniBundle\Entity\Certificazione")->findOneBy(array("stato" => $stato_certificazione));

        if (is_null($certificazione)) {
            $this->addFlash("error", "Non è presente una certificazione nello stato 'Certificazione inserita' a cui associare la decertificazione");
            return $this->redirect($return_url);
        }

        //$certificazione_pagamento = $em->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->findOneBy(array("certificazione" => $certificazione, "pagamento" => $pagamento));
        $certificazione_pagamento_precedente = $em->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->findOneBy(array("pagamento" => $pagamento));

        if (!is_null($certificazione_pagamento_precedente)) {
            $aiuti = $certificazione_pagamento_precedente->getAiutoDiStato();
            $strumenti = $certificazione_pagamento_precedente->getStrumentoFinanziario();
        } else {
            $aiuti = null;
            $strumenti = null;
        }

        $certificazione_pagamento = new \CertificazioniBundle\Entity\CertificazionePagamento();
        $certificazione_pagamento->setCertificazione($certificazione);
        $certificazione_pagamento->setAiutoDiStato($aiuti);
        $certificazione_pagamento->setStrumentoFinanziario($strumenti);
        $certificazione_pagamento->setPagamento($pagamento);

        $options = array();
        $options["url_indietro"] = $return_url;
        $options["importo_decertificabile"] = $pagamento->getImportoCertificato() - $pagamento->getImportoDecertificato();

        if ($this->hasContributoRevoca($pagamento) != false && is_null($certificazione_pagamento->getImporto())) {
            $certificazione_pagamento->setImporto($this->hasContributoRevoca($pagamento));
        }

        $form = $this->createForm("CertificazioniBundle\Form\DecertificazionePagamentoType", $certificazione_pagamento, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($certificazione_pagamento->getRitiro() == true && $certificazione_pagamento->getRecupero() == true) {
                $form->addError(new \Symfony\Component\Form\FormError("Non è possibile selezionare sia ritiro che recupero"));
            }

            if ($certificazione_pagamento->isIrregolarita() == true && is_null($certificazione_pagamento->getImportoIrregolare())) {
                $form->addError(new \Symfony\Component\Form\FormError("In caso di irregolarità è necessario inserire la quota di decertificazione per irregolarità"));
            }

            if ($form->isValid()) {
                try {
                    $em->persist($certificazione_pagamento);
                    $em->flush();
                    $this->addFlash("success", "La decertificazione è stata correttamente salvata");
                    return $this->redirect($return_url);
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();
        $dati["pagamento"] = $pagamento;

        return $this->render("CertificazioniBundle:Certificazioni:decertificazionePagamento.html.twig", $dati);
    }

    /**
     * @Route("/{id_pagamento}/decertifica_modifica/{id_certificazione_pagamento}", name="decertifica_modifica_pagamento")
     * @PaginaInfo(titolo="Decertifica pagamento",sottoTitolo="permette di decertificare un pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco pagamenti", route="elenco_istruttoria_pagamenti"),
     * 						 @ElementoBreadcrumb(testo="Elenco decertificazioni", route="decertificazioni_pagamento", parametri={"id_pagamento"}),
     *                       @ElementoBreadcrumb(testo="Decertificazione")})
     */
    public function decertificaModificaPagamentoAction($id_pagamento, $id_certificazione_pagamento) {
        $return_url = $this->generateUrl("decertificazioni_pagamento", array("id_pagamento" => $id_pagamento));
        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        $certificazione_pagamento = $em->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->findOneById($id_certificazione_pagamento);

        if (!is_null($pagamento->getImportoDecertificato()) && $pagamento->getImportoDecertificato() >= $pagamento->getImportoCertificato()) {
            $this->addFlash("error", "Per il pagamento selezionato è già stato decerticato un importo pari a quello certificato");
            return $this->redirect($return_url);
        }

        if ($certificazione_pagamento->getCertificazione()->getStato()->getCodice() != 'CERT_INSERITA') {
            $this->addFlash("error", "La certificicazione collegata non è più lavorabile");
            return $this->redirect($return_url);
        }

        $options = array();
        $options["url_indietro"] = $return_url;
        $options["importo_decertificabile"] = $pagamento->getImportoCertificato() - $pagamento->getImportoDecertificato();

        if ($this->hasContributoRevoca($pagamento) != false && is_null($certificazione_pagamento->getImporto())) {
            $certificazione_pagamento->setImporto($this->hasContributoRevoca($pagamento));
        }

        $form = $this->createForm("CertificazioniBundle\Form\DecertificazionePagamentoType", $certificazione_pagamento, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($certificazione_pagamento->getRitiro() == true && $certificazione_pagamento->getRecupero() == true) {
                $form->addError(new \Symfony\Component\Form\FormError("Non è possibile selezionare sia ritiro che recupero"));
            }
            if ($certificazione_pagamento->isIrregolarita() == true && is_null($certificazione_pagamento->getImportoIrregolare())) {
                $form->addError(new \Symfony\Component\Form\FormError("In caso di irregolarità è necessario inserire la quota di decertificazione per irregolarità"));
            }

            if ($form->isValid()) {
                try {
                    $em->persist($certificazione_pagamento);
                    $em->flush();
                    $this->addFlash("success", "La decertificazione è stata correttamente salvata");
                    return $this->redirect($return_url);
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();
        $dati["pagamento"] = $pagamento;

        return $this->render("CertificazioniBundle:Certificazioni:decertificazionePagamento.html.twig", $dati);
    }

    /*
     * Ritorna false se non ha revoca oppure l'importo della revoca o 
     * eventualemnte 0.00 se l'importo è null ma esiste comunque la revoca
     */

    public function hasContributoRevoca($pagamento) {
        if (count($pagamento->getAttuazioneControlloRichiesta()->getRevoca()) > 0) {
            $contributo = 0.00;
            foreach ($pagamento->getAttuazioneControlloRichiesta()->getRevoca() as $revoca) {
                $contributo += $revoca->getContributo();
            }
            return ($contributo * -1);
        } else {
            return false;
        }
    }

    public function riepilogoPagamentiPerAsse($id_certificazione) {
        $em = $this->getEm();

        $res = array();
        $res['A1'] = 0.00;
        $res['A2'] = 0.00;
        $res['A3'] = 0.00;
        $res['A4'] = 0.00;
        $res['A5'] = 0.00;
        $res['A6'] = 0.00;
        $res['A7'] = 0.00;
        $res['TOTALE'] = 0.00;
        $res['A1'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A1');
        $res['A2'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A2');
        $res['A3'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A3');
        $res['A4'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A4');
        $res['A5'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A5');
        $res['A6'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A6');
        $res['A7'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A7');
        $res['TOTALE'] = $res['A1'] + $res['A2'] + $res['A3'] + $res['A4'] + $res['A5'] + $res['A6'] + $res['A7'];
        return $res;
    }

    /**
     * @Route("/{id_pagamento}/compensazioni", name="compensazioni_pagamento")
     * @PaginaInfo(titolo="Decertificazioni pagamento",sottoTitolo="Elenco compensazioni un pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco pagamenti", route="elenco_istruttoria_pagamenti"),
     *                       @ElementoBreadcrumb(testo="Compensazioni")})
     */
    public function compensazioniPagamentoAction($id_pagamento) {
        $em = $this->getEm();
        $compensazioni = $em->getRepository("CertificazioniBundle\Entity\CompensazionePagamento")->findCompensazioniPagamento($id_pagamento);
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $dati['compensazioni'] = $compensazioni;
        $dati["pagamento"] = $pagamento;
        return $this->render("CertificazioniBundle:Certificazioni:compensazioniPagamento.html.twig", $dati);
    }

    /**
     * @Route("/{id_pagamento}/compensazione", name="compensazione_pagamento")
     * @PaginaInfo(titolo="Decertifica pagamento",sottoTitolo="permette una compensazione di un pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco pagamenti", route="elenco_istruttoria_pagamenti"),
     * 						 @ElementoBreadcrumb(testo="Elenco compensazioni", route="compensazioni_pagamento", parametri={"id_pagamento"}),
     *                       @ElementoBreadcrumb(testo="Decertificazione")})
     */
    public function compensazionePagamentoAction($id_pagamento) {
        $return_url = $this->generateUrl("compensazioni_pagamento", array("id_pagamento" => $id_pagamento));
        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $chiusura = $this->getEm()->getRepository("CertificazioniBundle\Entity\CertificazioneChiusura")->getChiusureLavorabili();

        $compensazione_pagamento = new \CertificazioniBundle\Entity\CompensazionePagamento();
        $compensazione_pagamento->setChiusura($chiusura);
        $compensazione_pagamento->setPagamento($pagamento);

        $options = array();
        $options["url_indietro"] = $return_url;

        $form = $this->createForm("CertificazioniBundle\Form\CompensazionePagamentoType", $compensazione_pagamento, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if (is_null($chiusura)) {
                $form->addError(new FormError("Per l'invio nei conti è necessario che sia presente una chiusura lavorabile"));
            }

            if ($form->isValid()) {
                try {
                    $em->persist($compensazione_pagamento);
                    $em->flush();
                    $this->addFlash("success", "La compensazioni è stata correttamente salvata");
                    return $this->redirect($return_url);
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();
        $dati["pagamento"] = $pagamento;

        return $this->render("CertificazioniBundle:Certificazioni:compensazionePagamento.html.twig", $dati);
    }

    /**
     * @Route("/{id_pagamento}/compensazione_modifica/{id_compensazione_pagamento}", name="compensazione_modifica_pagamento")
     * @PaginaInfo(titolo="Decertifica pagamento",sottoTitolo="permette una compensazione di un pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco pagamenti", route="elenco_istruttoria_pagamenti"),
     * 						 @ElementoBreadcrumb(testo="Elenco compensazioni", route="compensazioni_pagamento", parametri={"id_pagamento"}),
     *                       @ElementoBreadcrumb(testo="Decertificazione")})
     */
    public function compensazioneModificaPagamentoAction($id_pagamento, $id_compensazione_pagamento) {
        $return_url = $this->generateUrl("compensazioni_pagamento", array("id_pagamento" => $id_pagamento));
        $em = $this->getEm();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        $compensazione_pagamento = $em->getRepository("CertificazioniBundle\Entity\CompensazionePagamento")->findOneById($id_compensazione_pagamento);

        if (!$compensazione_pagamento->isEliminabile()) {
            $this->addFlash("error", "La chiusura collegata non è più lavorabile");
            return $this->redirect($return_url);
        }

        $options = array();
        $options["url_indietro"] = $return_url;

        $form = $this->createForm("CertificazioniBundle\Form\CompensazionePagamentoType", $compensazione_pagamento, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $em->persist($compensazione_pagamento);
                    $em->flush();
                    $this->addFlash("success", "La compensazione è stata correttamente salvata");
                    return $this->redirect($return_url);
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();
        $dati["pagamento"] = $pagamento;

        return $this->render("CertificazioniBundle:Certificazioni:compensazionePagamento.html.twig", $dati);
    }

    /**
     * @Route("/{id_compensazione_pagamento}/elimina_compensazione", name="elimina_compensazione")  
     */
    public function eliminaCompensazioneAction($id_compensazione_pagamento) {
        $this->get('base')->checkCsrf('token');
        $em = $this->getEm();
        $compensazione_pagamento = $em->getRepository("CertificazioniBundle\Entity\CompensazionePagamento")->findOneById($id_compensazione_pagamento);
        $id_pagamento = $compensazione_pagamento->getPagamento()->getId();
        $return_url = $this->generateUrl("compensazioni_pagamento", array("id_pagamento" => $id_pagamento));


        if (!$compensazione_pagamento->isEliminabile()) {
            $this->addFlash("error", "La chiusura collegata non è più lavorabile");
            return $this->redirect($return_url);
        }

        try {
            $em->remove($compensazione_pagamento);
            $em->flush();
            $this->addFlash("success", "La compensazione è stata correttamente eliminata");
        } catch (ResponseException $e) {
            $this->addFlash("error", "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza");
        }

        return $this->redirect($return_url);
    }

}
