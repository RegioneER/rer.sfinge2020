<?php

namespace AttuazioneControlloBundle\Controller\Istruttoria;

use AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaComunicazionePagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\RispostaComunicazionePagamento;
use AttuazioneControlloBundle\Entity\Pagamento;
use BaseBundle\Controller\BaseController;
use BaseBundle\Entity\StatoComunicazionePagamento;
use BaseBundle\Exception\SfingeException;
use DocumentoBundle\Entity\TipologiaDocumento;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;

/**
 * @Route("/beneficiario/pagamenti/comunicazioni_pagamenti")
 */
class RispostaComunicazionePagamentoController extends BaseController {

    /**
     * @Route("/elenco_comunicazioni_pagamento/{id}", name="elenco_comunicazioni_pagamento")
     * Template("AttuazioneControlloBundle:RispostaComunicazionePagamento:elencoComunicazioniPagamento.html.twig")
     * @PaginaInfo(titolo="Elenco comunicazioni pagamento",sottoTitolo="mostra l'elenco delle comunicazioni del pagamento")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * 
     * @param Pagamento $pagamento
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function elencoComunicazioniPagamentiAction(Pagamento $pagamento) {
        $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
        $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->find($soggettoSession->getId());
        
        if (is_null($soggetto)) {
            return $this->addErrorRedirect("Soggetto non valido.", "home");
        }

        $comunicazioniProtocollate = array();
        foreach ($pagamento->getComunicazioni() as $comunicazione) {
            if ($comunicazione->getStato() == 'COM_PAG_PROTOCOLLATA') {
                $comunicazioniProtocollate[] = $comunicazione;
            }
        }
        $view = $this->renderView("AttuazioneControlloBundle:RispostaComunicazionePagamento:elencoComunicazioniPagamento.html.twig",
            array("comunicazioni" => $comunicazioniProtocollate));
        return new \Symfony\Component\HttpFoundation\Response($view);
    }

    /**
     * @Route("/{id}/dettaglio", name="dettaglio_comunicazione_pagamento")
     * @PaginaInfo(titolo="Comunicazione pagamento",sottoTitolo="pagina di dettaglio per una comunicazione di pagamento")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Dettaglio comunicazione")})
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"})
     * @ControlloAccesso(contesto="comunicazione_pagamento", classe="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"}, azione=\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::WRITE)
     * 
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function dettaglioComunicazioniPagamentiAction(ComunicazionePagamento $comunicazionePagamento) {
        $risposta = $comunicazionePagamento->getRisposta();
        if (is_null($risposta) || is_null($risposta->getFirmatario())) {
            return $this->redirectToRoute("risposta_comunicazione_pagamento_firmatario", array("id" => $comunicazionePagamento->getId()));
        }

        $this->getSession()->set("gestore_comunicazione_pagamento_bundle", "AttuazioneControlloBundle");
        $gestore = $this->get("gestore_comunicazione_pagamento")->getGestore($this->getSession()->get("gestore_comunicazione_pagamento_bundle"));

        $dati = array();
        $dati["comunicazione_pagamento"] = $comunicazionePagamento;
        $dati["azioni_ammesse"] = $gestore->calcolaAzioniAmmesse($comunicazionePagamento->getRisposta());
        $dati["avanzamenti"] = $gestore->gestioneBarraAvanzamento($comunicazionePagamento->getRisposta());

        return $this->render('AttuazioneControlloBundle:RispostaComunicazionePagamento:dettaglioComunicazionePagamento.html.twig', $dati);
    }

    /**
     * @Route("/{id}/scelta_firmatario", name="risposta_comunicazione_pagamento_firmatario")
     * @PaginaInfo(titolo="Scelta firmatario", sottoTitolo="pagina per scegliere il firmatario della comunicazione")
     * @Breadcrumb(elementi={
     *      @ElementoBreadcrumb(testo="Dettaglio comunicazione pagamento", route="dettaglio_comunicazione_pagamento", parametri={"id"}),
     *      @ElementoBreadcrumb(testo="Scelta firmatario")
     * })
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"})
     * @ControlloAccesso(contesto="comunicazione_pagamento", classe="AttuazioneControlloBundle:Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"}, azione=\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::WRITE)
     * 
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function sceltaFirmatarioComunicazioniPagamentiAction(ComunicazionePagamento $comunicazionePagamento) {
        try {
            $this->getSession()->set("gestore_comunicazione_pagamento_bundle", "AttuazioneControlloBundle");
            $gestore = $this->get("gestore_comunicazione_pagamento")->getGestore($this->getSession()->get("gestore_comunicazione_pagamento_bundle"));

            $richiesta = $comunicazionePagamento->getPagamento()->getRichiesta();
            $opzioni = array("form_options" => array());
            $opzioni["form_options"]["url_indietro"] = $this->generateUrl("dettaglio_comunicazione_pagamento", array("id" => $comunicazionePagamento->getId()));
            $opzioni["form_options"]["firmatabili"] = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->getFirmatariAmmissibili($richiesta->getSoggetto());

            $response = $gestore->sceltaFirmatario($comunicazionePagamento, $opzioni);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "dettaglio_comunicazione_pagamento", array("id" => $comunicazionePagamento->getId()));
        } catch (\Exception $e) {
            return $this->addErrorRedirect("Errore generico", "dettaglio_comunicazione_pagamento", array("id" => $comunicazionePagamento->getId()));
        }
    }

    /**
     * @Route("/{id}/nota_risposta", name="nota_risposta_comunicazione_pagamento")
     * @PaginaInfo(titolo="Nota risposta comunicazione pagamento")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @Breadcrumb(elementi={
     *     @ElementoBreadcrumb(testo="Dettaglio comunicazione pagamento", route="dettaglio_comunicazione_pagamento", parametri={"id"}),
     *     @ElementoBreadcrumb(testo="Nota risposta")
     * })
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"})
     * @ControlloAccesso(contesto="comunicazione_pagamento", classe="AttuazioneControlloBundle:Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"}, azione=\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::WRITE)
     * 
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return mixed
     * @throws SfingeException
     */
    public function notaRispostaComunicazioniPagamentiAction(ComunicazionePagamento $comunicazionePagamento) {
        $this->getSession()->set("gestore_comunicazione_pagamento_bundle", "AttuazioneControlloBundle");
        $gestore = $this->get("gestore_comunicazione_pagamento")->getGestore($this->getSession()->get("gestore_comunicazione_pagamento_bundle"));

        $opzioni = array("form_options" => array());
        $opzioni["form_options"]["url_indietro"] = $this->generateUrl("dettaglio_comunicazione_pagamento", array("id" => $comunicazionePagamento->getId()));
        return $gestore->notaRispostaComunicazionePagamento($comunicazionePagamento, $opzioni)->getResponse();
    }

    /**
     * @Route("/elenco_documenti_comunicazione_pagamento/{id}", name="risposta_comunicazione_pagamento_elenco_documenti")
     * @PaginaInfo(titolo="Elenco Documenti", sottoTitolo="carica i documenti richiesti")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @Breadcrumb(elementi={
     *      @ElementoBreadcrumb(testo="Dettaglio comunicazione pagamento", route="dettaglio_comunicazione_pagamento", parametri={"id"}),
     *      @ElementoBreadcrumb(testo="Documenti in comunicazione pagamento")
     * })
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"})
     * @ControlloAccesso(contesto="comunicazione_pagamento", classe="AttuazioneControlloBundle:Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"}, azione=\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::WRITE)
     * 
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function elencoDocumentiComunicazioniPagamentiAction(ComunicazionePagamento $comunicazionePagamento) {
        if (is_null($comunicazionePagamento->getRisposta()->getFirmatario())) {
            return $this->redirectToRoute("risposta_comunicazione_pagamento_firmatario", array("id" => $comunicazionePagamento->getId()));
        }

        $this->getSession()->set("gestore_comunicazione_pagamento_bundle", "AttuazioneControlloBundle");
        $gestore = $this->get("gestore_comunicazione_pagamento")->getGestore($this->getSession()->get("gestore_comunicazione_pagamento_bundle"));

        $opzioni = array();
        $opzioni["url_corrente"] = $this->generateUrl("risposta_comunicazione_pagamento_elenco_documenti", array("id" => $comunicazionePagamento->getId()));
        $opzioni["url_indietro"] = $this->generateUrl("dettaglio_comunicazione_pagamento", array("id" => $comunicazionePagamento->getId()));
        $opzioni["route_cancellazione_documento"] = "risposta_comunicazione_pagamento_elimina_documento";

        $response = $gestore->elencoDocumenti($comunicazionePagamento, $opzioni);
        return $response->getResponse();
    }

    /**
     * @Route("/elimina_documento/{id}", name="risposta_comunicazione_pagamento_elimina_documento")
     * 
     * @param DocumentoRispostaComunicazionePagamento $documentoRispostaComunicazionePagamento
     * @return mixed
     * @throws \Exception
     */
    public function eliminaDocumentoAction(DocumentoRispostaComunicazionePagamento $documentoRispostaComunicazionePagamento) {
        $this->get('base')->checkCsrf('token');
        $comunicazionePagamento = $documentoRispostaComunicazionePagamento->getRispostaComunicazionePagamento()->getComunicazione();

        $contestoSoggetto = $this->get('contesto')->getContestoRisorsa($comunicazionePagamento, "soggetto");
        $contestoRichiesta = $this->get('contesto')->getContestoRisorsa($comunicazionePagamento, "comunicazione");
        
        $accessoConsentitoS = $this->isGranted(\SoggettoBundle\Security\SoggettoVoter::ALL, $contestoSoggetto);
        $accessoConsentitoR = $this->isGranted(\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::WRITE, $contestoRichiesta);
        if (!$accessoConsentitoS && !$accessoConsentitoR) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Accesso non consentito al documento risposta comunicazione pagamento di richiesta di chiarimenti');
        }

        $this->getSession()->set("gestore_comunicazione_pagamento_bundle", "AttuazioneControlloBundle");
        $gestore = $this->get("gestore_comunicazione_pagamento")->getGestore($this->getSession()->get("gestore_comunicazione_pagamento_bundle"));

        $response = $gestore->eliminaDocumento($documentoRispostaComunicazionePagamento, array(
            "url_indietro" => $this->generateUrl("risposta_comunicazione_pagamento_elenco_documenti",
                array("id" => $comunicazionePagamento->getId()))
        ));

        return $response->getResponse();
    }

    /**
     * @Route("/valida_risposta_comunicazione_pagamento/{id}", name="valida_risposta_comunicazione_pagamento")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"})
     * @ControlloAccesso(contesto="comunicazione_pagamento", classe="AttuazioneControlloBundle:Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"}, azione=\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::WRITE)
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function validaRispostaComunicazionePagamentoAction(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        $this->get('base')->checkCsrf('token');
        try {
            $this->getSession()->set("gestore_comunicazione_pagamento_bundle", "AttuazioneControlloBundle");
            $gestore = $this->get("gestore_comunicazione_pagamento")->getGestore($this->getSession()->get("gestore_comunicazione_pagamento_bundle"));

            $opzioni["url_indietro"] = $this->generateUrl("dettaglio_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getComunicazione()->getId()));
            $response = $gestore->validaRispostaComunicazionePagamento($rispostaComunicazionePagamento, $opzioni);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "dettaglio_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getComunicazione()->getId()));
        } catch (\Exception $e) {
            return $this->addErrorRedirect("Errore generico", "dettaglio_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getComunicazione()->getId()));
        }
    }

    /**
     *  @Route("/scarica_risposta_comunicazione_pagamento/{id}", name="scarica_risposta_comunicazione_pagamento")
     *  @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"})
     *  @ControlloAccesso(contesto="comunicazione_pagamento", classe="AttuazioneControlloBundle:Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"}, azione=\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::WRITE)
     * 
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function scaricaRispostaComunicazionePagamentoAction(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        if (is_null($rispostaComunicazionePagamento)) {
            return $this->addErrorRedirect("Richiesta non valida", "elenco_richieste");
        }
        
        if (is_null($rispostaComunicazionePagamento->getDocumentoRisposta())) {
            return $this->addErrorRedirect("Nessun documento associato alla risposta",
                "dettaglio_comunicazione_pagamento",
                array("id_integrazione_pagamento" => $rispostaComunicazionePagamento->getComunicazione()->getId()));
        }

        return $this->get("documenti")->scaricaDaId($rispostaComunicazionePagamento->getDocumentoRisposta()->getId());
    }

    /**
     * @Route("/invalida_risposta_comunicazione_pagamento/{id}", name="invalida_risposta_comunicazione_pagamento")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"})
     * @ControlloAccesso(contesto="comunicazione_pagamento", classe="AttuazioneControlloBundle:Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"}, azione=\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::WRITE)
     * 
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function invalidaRispostaComunicazionePagamentoAction(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        $this->get('base')->checkCsrf('token');
        try {
            $this->getSession()->set("gestore_comunicazione_pagamento_bundle", "AttuazioneControlloBundle");
            $gestore = $this->get("gestore_comunicazione_pagamento")->getGestore($this->getSession()->get("gestore_comunicazione_pagamento_bundle"));
            $opzioni["url_indietro"] = $this->generateUrl("dettaglio_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getComunicazione()->getId()));
            $response = $gestore->invalidaComunicazionePagamento($rispostaComunicazionePagamento, $opzioni);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "dettaglio_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getId()));
        } catch (\Exception $e) {
            return $this->addErrorRedirect("Errore generico", "dettaglio_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getId()));
        }
    }

    /**
     * @Route("/carica_risposta_firmata_comunicazione_pagamento/{id}", name="carica_risposta_firmata_comunicazione_pagamento")
     * @Template("AttuazioneControlloBundle:RispostaComunicazionePagamento:caricaRispostaFirmataComunicazionePagamento.html.twig")
     * @PaginaInfo(titolo="Carica risposta comunicazioni pagamento firmata", sottoTitolo="pagina per caricare la risposta firmata alla comunicazione di pagamento")
     * @Breadcrumb(elementi={
     *      @ElementoBreadcrumb(testo="Dettaglio comunicazioni pagamento", route="dettaglio_comunicazione_pagamento", parametri={"id"}),
     *      @ElementoBreadcrumb(testo="Carica risposta")
     * })
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"})
     * @ControlloAccesso(contesto="comunicazione_pagamento", classe="AttuazioneControlloBundle:Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"}, azione=\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::WRITE)
     * 
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function caricaRispostaFirmataComunicazionePagamentoAction(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();
        $documentoFile = new \DocumentoBundle\Entity\DocumentoFile();

        if (!$rispostaComunicazionePagamento) {
            throw $this->createNotFoundException('Risorsa non trovata.');
        }

        try {
            if (!$rispostaComunicazionePagamento->getStato()->uguale(StatoComunicazionePagamento::COM_PAG_VALIDATA)) {
                throw new SfingeException("Stato non valido per effettuare l'operazione.");
            }
        } catch (SfingeException $e) {
            return $this->addErrorRedirect("Errore generico.", "dettaglio_comunicazione_pagamento",
                array("id" => $rispostaComunicazionePagamento->getId()));
        }

        $opzioniForm["tipo"] = TipologiaDocumento::COMUNICAZIONE_PAGAMENTO_RISPOSTA_FIRMATO;
        $opzioniForm["cf_firmatario"] = $rispostaComunicazionePagamento->getFirmatario()->getCodiceFiscale();
        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documentoFile, $opzioniForm);
        $form->add("pulsanti", "BaseBundle\Form\SalvaIndietroType", array(
            "url" => $this->generateUrl("dettaglio_comunicazione_pagamento",
                array("id" => $rispostaComunicazionePagamento->getId()))
        ));
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $this->container->get("documenti")->carica($documentoFile, 0);
                    $rispostaComunicazionePagamento->setDocumentoRispostaFirmato($documentoFile);
                    $this->container->get("sfinge.stati")->avanzaStato($rispostaComunicazionePagamento, StatoComunicazionePagamento::COM_PAG_FIRMATA, true);
                    $em->flush();
                    return $this->addSuccessRedirect("Documento firmato caricato correttamente.", "dettaglio_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getId()));
                } catch (\Exception $e) {
                    $this->addFlash('error', "Errore generico");
                }
            }
        }
        
        $form_view = $form->createView();
        return array("id_integrazione_pagamento" => $rispostaComunicazionePagamento->getComunicazione()->getId(), "form" => $form_view);
    }

    /**
     *  @Route("/scarica_risposta_firmata_comunicazione_pagamento/{id}", name="scarica_risposta_firmata_comunicazione_pagamento")
     *  @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"})
     *  @ControlloAccesso(contesto="comunicazione_pagamento", classe="AttuazioneControlloBundle:Istruttoria\ComunicazionePagamento", opzioni={"id" = "id"}, azione=\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::WRITE)
     * 
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function scaricaRispostaFirmataComunicazionePagamentoAction(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        if (is_null($rispostaComunicazionePagamento)) {
            return $this->addErrorRedirect("Richiesta non valida.", "dettaglio_comunicazione_pagamento",
                array("id" => $rispostaComunicazionePagamento->getComunicazione()->getId()));
        }

        if (is_null($rispostaComunicazionePagamento->getDocumentoRispostaFirmato())) {
            return $this->addErrorRedirect("Nessun documento associato alla risposta.",
                "dettaglio_comunicazione_pagamento",
                array("id" => $rispostaComunicazionePagamento->getComunicazione()->getId()));
        }

        return $this->get("documenti")->scaricaDaId($rispostaComunicazionePagamento->getDocumentoRispostaFirmato()->getId());
    }

    /**
     * @Route("/invia_risposta_comunicazione_pagamento/{id}", name="invia_risposta_comunicazione_pagamento")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", opzioni={"id" = "id_integrazione_pagamento"})
     * @ControlloAccesso(contesto="comunicazione_pagamento", classe="AttuazioneControlloBundle:Istruttoria\ComunicazionePagamento", opzioni={"id" = "id_integrazione_pagamento"}, azione=\AttuazioneControlloBundle\Security\ComunicazionePagamentoVoter::WRITE)
     * 
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @return mixed|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function inviaRispostaComunicazionePagamentoAction(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        $this->get('base')->checkCsrf('token');
        try {
            $this->getSession()->set("gestore_comunicazione_pagamento_bundle", "AttuazioneControlloBundle");
            $gestore = $this->get("gestore_comunicazione_pagamento")->getGestore($this->getSession()->get("gestore_comunicazione_pagamento_bundle"));

            $opzioni["url_indietro"] = $this->generateUrl("dettaglio_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getComunicazione()->getId()));
            $response = $gestore->inviaRispostaComunicazionePagamento($rispostaComunicazionePagamento, $opzioni);
            $this->addFlash("success", "Risposta alla comunicazione di pagamento inviata con successo.");
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "dettaglio_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getComunicazione()->getId()));
        } catch (\Exception $e) {
            return $this->addErrorRedirect("Errore generico.", "dettaglio_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getComunicazione()->getId()));
        }
    }
}
