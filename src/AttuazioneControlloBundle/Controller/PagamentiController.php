<?php

namespace AttuazioneControlloBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use BaseBundle\Annotation\ControlloAccesso;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AttuazioneControlloBundle\Entity\StatoPagamento;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use BaseBundle\Exception\SfingeException;
use AttuazioneControlloBundle\Service\IGestorePagamenti;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AttuazioneControlloBundle\Entity\Pagamento;
use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Entity\ProceduraAggiudicazione;

/**
 * @Route("/beneficiario/pagamenti")
 */
class PagamentiController extends \BaseBundle\Controller\BaseController {
    /**
     * @Route("/{id_richiesta}/elenco_pagamenti", name="elenco_pagamenti")
     * @PaginaInfo(titolo="Elenco pagamenti progetto", sottoTitolo="mostra l'elenco dei pagamenti richieste per un progetto")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_gestione_beneficiario"),
     * @ElementoBreadcrumb(testo="Elenco pagamenti progetto")})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     */
    public function elencoPagamentiAction($id_richiesta) {
        $this->getSession()->set("id_richiesta", $id_richiesta);
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        return $this->getGestorePagamenti($richiesta->getProcedura())->elencoPagamenti($id_richiesta);
    }

    protected function getGestorePagamenti(?Procedura $procedura = null): IGestorePagamenti {
        return $this->get("gestore_pagamenti")->getGestore($procedura);
    }

    /**
     * @Route("/{id_richiesta}/aggiungi", name="aggiungi_pagamento")
     * @PaginaInfo(titolo="Creazione richiesta pagamento", sottoTitolo="pagina di creazione di un pagamento")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_gestione_beneficiario"),
     *     @ElementoBreadcrumb(testo="Elenco pagamenti richiesti", route="elenco_pagamenti", parametri={"id_richiesta": "id_richiesta"}),
     * @ElementoBreadcrumb(testo="creazione pagamento")})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id": "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     */
    public function aggiungiPagamentoAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        return $this->getGestorePagamenti($richiesta->getProcedura())->aggiungiPagamento($id_richiesta);
    }

    /**
     * @Route("/{id_pagamento}/elimina", name="elimina_pagamento")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function eliminaPagamentoAction($id_pagamento) {
        $this->get('base')->checkCsrf('token');
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->eliminaPagamento($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/dettaglio", name="dettaglio_pagamento")
     * @PaginaInfo(titolo="Dettaglio richiesta pagamento", sottoTitolo="pagina di riepilogo della richiesta di pagamento")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function dettaglioPagamentoAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);

        if (8 == $pagamento->getProcedura()->getId() && "ANTICIPO" == $pagamento->getModalitaPagamento()->getCodice()) {
            return $this->get("gestore_pagamenti")->getGestoreBase()->dettaglioPagamento($id_pagamento);
        }

        return $this->getGestorePagamenti($pagamento->getProcedura())->dettaglioPagamento($id_pagamento);
    }

    protected function getPagamento($id_pagamento): ?Pagamento {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        return $pagamento;
    }

    /**
     * @Route("/{id_pagamento}/gestione_documenti", name="gestione_documenti_pagamento")
     * @PaginaInfo(titolo="Documenti pagamento", sottoTitolo="pagina di gestione dei documenti del pagamento")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestioneDocumentiAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneDocumentiPagamento($id_pagamento);
    }

    /**
     * @Route("/{id_documento_pagamento}/elimina_documento_pagamento", name="elimina_documento_pagamento")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:DocumentoPagamento", opzioni={"id": "id_documento_pagamento"})
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function eliminaDocumentoAction($id_documento_pagamento) {
        return $this->getGestorePagamenti()->eliminaDocumentoPagamento($id_documento_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_documenti_dropzone_pagamento", name="gestione_documenti_dropzone_pagamento")
     * @PaginaInfo(titolo="Video pagamento", sottoTitolo="pagina di gestione del video del pagamento")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestioneDocumentiDropzoneAction($id_pagamento): ?Response
    {
        $pagamento = $this->getPagamento($id_pagamento);
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", ["id_richiesta" => $pagamento->getRichiesta()->getId()]));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", ["id_pagamento" => $pagamento->getId()]));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Gestione documenti");

        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneDocumentiDropzonePagamento($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/carica_documento_dropzone_pagamento", name="carica_documento_dropzone_pagamento")
     * @Method({"POST"})
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     * @param Request $request
     * @param $id_pagamento
     * @return JsonResponse
     */
    public function caricaDocumentoDropzoneAction(Request $request, $id_pagamento): JsonResponse
    {
        $pagamento = $this->getPagamento($id_pagamento);
        $arrayResult = $this->getGestorePagamenti($pagamento->getProcedura())->caricaDocumentoDropzone($request, $pagamento);
        return new JsonResponse($arrayResult);
    }

    /**
     * @Route("/{id_pagamento}/concat_chunks_documento_dropzone_pagamento", name="concat_chunks_documento_dropzone_pagamento")
     * @Method({"POST"})
     */
    public function concatChunksDocumentoDropzoneAction(Request $request, $id_pagamento): JsonResponse
    {
        $pagamento = $this->getPagamento($id_pagamento);
        $arrayResult = $this->getGestorePagamenti($pagamento->getProcedura())->concatChunksDocumentoDropzone($request, $id_pagamento);
        return new JsonResponse($arrayResult);
    }

    /**
     * @Route("/{id_pagamento}/dati_generali", name="dati_generali_pagamento")
     * @PaginaInfo(titolo="Dati generali pagamento", sottoTitolo="dati generali del pagamento")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function datiGeneraliAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->datiGeneraliPagamento($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/valida", name="valida_pagamento")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function validaPagamentoAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        $response = $this->getGestorePagamenti($pagamento->getProcedura())->validaPagamento($id_pagamento);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_pagamento}/invalida", name="invalida_pagamento")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function invalidaPagamentoAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        $response = $this->getGestorePagamenti($pagamento->getProcedura())->invalidaPagamento($id_pagamento);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_pagamento}/invia_pagamento", name="invia_pagamento")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function inviaPagamento($id_pagamento) {
        $this->get('base')->checkCsrf('token');
        $pagamento = $this->getPagamento($id_pagamento);

        try {
            $response = $this->getGestorePagamenti($pagamento->getProcedura())->inviaPagamento($id_pagamento);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "dettaglio_pagamento", ["id_pagamento" => $id_pagamento]);
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "dettaglio_pagamento", ["id_pagamento" => $id_pagamento]);
        }
    }

    /**
     * @Route("/{id_pagamento}/genera_pdf", name="genera_pdf_pagamento")
     * @Method({"GET"})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function generaPdf($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->generaPdf($id_pagamento);
    }

    /**
     * @Route("/questionario/{id_istanza_pagina}/{id_pagina}/{id_istanza_frammento}/{azione}", name="questionario_pagamento", defaults={"id_istanza_pagina": "-", "id_pagina": "-", "id_istanza_frammento": "-", "azione": "modifica"})
     */
    public function questionarioAction(Request $request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione) {
        $em = $this->getEm();

        if ("-" != $id_istanza_pagina) {
            $istanza_pagina = $em->getRepository("FascicoloBundle\Entity\IstanzaPagina")->find($id_istanza_pagina);
        } else {
            $istanza_frammento = $em->getRepository("FascicoloBundle\Entity\IstanzaFrammento")->find($id_istanza_frammento);
            $istanza_pagina = $istanza_frammento->getIstanzaPagina();
        }

        $istanza_fascicolo = $istanza_pagina->getIstanzaFascicolo();
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->findOneBy(["istanza_fascicolo" => $istanza_fascicolo]);

        if (is_null($pagamento)) {
            throw new SfingeException("Pagamento o richiesta non trovati");
        }

        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $id_richiesta = $richiesta->getId();

        if (is_null($richiesta)) {
            throw new SfingeException("Pagamento o richiesta non trovati");
        }

        $isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();
        if (!$this->isUtente() || $isRichiestaDisabilitata) {
            $azione = "visualizza";
        }

        $contestoSoggetto = $this->get('contesto')->getContestoRisorsa($richiesta, "soggetto");
        $accessoConsentito = $this->isGranted(\SoggettoBundle\Security\SoggettoVoter::ALL, $contestoSoggetto);
        
        $contestoRichiesta = $this->get('contesto')->getContestoRisorsa($pagamento, "pagamento");
        $accessoConsentito |= $this->isGranted(\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE, $contestoRichiesta);

        if (!$accessoConsentito) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $this->getSession()->set("fascicolo.route_istanza_pagina", "questionario_pagamento");

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", ["id_richiesta" => $id_richiesta]));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", ["id_pagamento" => $pagamento->getId()]));

        return $this->get("fascicolo.istanza")->istanzaPagina($request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione, $pagamento->getId(), 'PAGAMENTO');
    }

    /**
     * @Route("/{id_pagamento}/scarica_pagamento", name="scarica_pagamento")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function scaricaDomandaAction($id_pagamento) {
        $em = $this->getEm();
        $pagamento = $this->getPagamento($id_pagamento);

        if (is_null($pagamento)) {
            return $this->addErrorRedirect("Pagamento non valida", "dettaglio_pagamento", ["id_pagamento" => $id_pagamento]);
        }

        if (is_null($pagamento->getDocumentoPagamento())) {
            return $this->addErrorRedirect("Nessun documento associato al pagamento", "dettaglio_pagamento", ["id_pagamento" => $id_pagamento]);
        }

        return $this->get("documenti")->scaricaDaId($pagamento->getDocumentoPagamento()->getId());
    }

    /**
     * @Route("/{id_pagamento}/carica_pagamento_firmato", name="carica_pagamento_firmato")
     * @PaginaInfo(titolo="Carica pagamento firmato", sottoTitolo="pagina per caricare il pagamento firmato")
     * @Template("AttuazioneControlloBundle:Pagamenti:caricaPagamentoFirmato.html.twig")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function caricaPagamentoFirmatoAction($id_pagamento) {
        $em = $this->getEm();
        $pagamento = $this->getPagamento($id_pagamento);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        $request = $this->getCurrentRequest();

        $documento_file = new DocumentoFile();

        if (!$pagamento) {
            throw $this->createNotFoundException('Risorsa non trovata');
        }

        try {
            if (!$pagamento->getStato()->uguale(StatoPagamento::PAG_VALIDATO)) {
                throw new SfingeException("Stato non valido per effettuare l'operazione");
            }
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        }

        $opzioni_form["tipo"] = TipologiaDocumento::PAGAMENTO_CONTRIBUTO_FIRMATO;
        $opzioni_form["cf_firmatario"] = $pagamento->getFirmatario()->getCodiceFiscale();
        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
        $form->add("pultanti", "BaseBundle\Form\SalvaIndietroType", ["url" => $this->generateUrl("dettaglio_pagamento", ['id_pagamento' => $id_pagamento])]);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $this->container->get("documenti")->carica($documento_file, 0, $richiesta);
                    $pagamento->setDocumentoPagamentoFirmato($documento_file);
                    $this->get("sfinge.stati")->avanzaStato($pagamento, StatoPagamento::PAG_FIRMATO);
                    $em->persist($pagamento);
                    $em->flush();
                    return $this->addSuccessRedirect("Documento caricato correttamente", "dettaglio_pagamento", ['id_pagamento' => $id_pagamento]);
                } catch (\Exception $e) {
                    //TODO gestire cancellazione del file
                    $this->addFlash('error', "Errore generico");
                }
            }
        }
        $form_view = $form->createView();

        return ["id_pagamento" => $id_pagamento, "form" => $form_view];
    }

    /**
     * @Route("/{id_pagamento}/elenco_documenti_caricati_pag", name="elenco_documenti_caricati_pag")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Elenco Documenti", sottoTitolo="carica i documenti richiesti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function elencoDocumentiCaricatiPagAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        $response = $this->getGestorePagamenti($pagamento->getProcedura())->elencoDocumentiCaricati($id_pagamento);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_pagamento}/scarica_pagamento_firmato", name="scarica_pagamento_firmato")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function scaricaPagamentoFirmatoAction($id_pagamento) {
        $em = $this->getEm();
        $pagamento = $this->getPagamento($id_pagamento);
        if (is_null($pagamento)) {
            return $this->addErrorRedirect("Pagamento non valida", "dettaglio_pagamento", ['id_pagamento' => $id_pagamento]);
        }

        if (is_null($pagamento->getDocumentoPagamentoFirmato())) {
            return $this->addErrorRedirect("Nessun documento associato al pagamento", "dettaglio_pagamento", ['id_pagamento' => $id_pagamento]);
        }

        return $this->get("documenti")->scaricaDaId($pagamento->getDocumentoPagamentoFirmato()->getId());
    }

    /**
     * @Route("/{id_pagamento}/recupera", name="recupera_pagamento")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function recuperaPagamentoAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->recuperaPagamento($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/visualizza_integrazione", name="visualizza_integrazione_pagamento")
     * @PaginaInfo(titolo="Note integrazione pagamento", sottoTitolo="")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function visualizzaIntegrazioneAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->visualizzaIntegrazione($id_pagamento);
    }

    /**
     * @Route("/{id_richiesta}/cerca_operatore/{id_pagamento}/{page}", defaults={"page": 1}, name="cerca_operatore")
     * @PaginaInfo(titolo="Aggiungi operatore", sottoTitolo="pagina per cercare ed aggiungere un nuovo operatore")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_gestione_beneficiario", parametri={"id_richiesta": "id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco pagamenti", route="elenco_pagamenti", parametri={"id_richiesta": "id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettaglio pagamento", route="dettaglio_pagamento", parametri={"id_pagamento": "id_pagamento"}),
     * 				@ElementoBreadcrumb(testo="Dati generali", route="dati_generali_pagamento", parametri={"id_pagamento": "id_pagamento"}),
     * 				@ElementoBreadcrumb(testo="Aggiunta operatore")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function cercaOperatoreAction($id_richiesta, $id_pagamento) {
        try {
            $response = $this->getGestorePagamenti()->cercaOperatore($id_pagamento);
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }

        return $response->getResponse();
    }

    /**
     * @Route("/inserisci_operatore/{id_pagamento}/{persona_id}", name="inserisci_operatore")
     * @Method({"GET", "POST"})
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function inserisciOperatoreAction($id_pagamento, $persona_id) {
        try {
            $response = $this->getGestorePagamenti()->inserisciOperatore($id_pagamento, $persona_id);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "dettaglio_pagamento");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "dettaglio_pagamento");
        }
    }

    /**
     * @Route("/rimuovi_operatore/{id_operatore}", name="rimuovi_operatore")
     */
    public function rimuoviOperatoreAction($id_operatore) {
        $this->get('base')->checkCsrf('token');
        try {
            $response = $this->getGestorePagamenti()->rimuoviOperatore($id_operatore);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_gestione_beneficiario");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_gestione_beneficiario");
        }
    }

    /**
     * @Route("/{id_richiesta}/aggiungi_persona_operatore/{id_pagamento}", name="aggiungi_persona_operatore")
     * @PaginaInfo(titolo="Dettaglio referente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_gestione_beneficiario", parametri={"id_richiesta": "id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco pagamenti", route="elenco_pagamenti", parametri={"id_richiesta": "id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettaglio pagamento", route="dettaglio_pagamento", parametri={"id_pagamento": "id_pagamento"}),
     * 				@ElementoBreadcrumb(testo="Dati generali", route="dati_generali_pagamento", parametri={"id_pagamento": "id_pagamento"}),
     * 				@ElementoBreadcrumb(testo="Aggiunta operatore")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function aggiungiPersonaReferenteAction($id_richiesta, $id_pagamento) {
        $parametriUrl = ["id_pagamento" => $id_pagamento];
        $urlIndietro = $this->generateUrl("dettaglio_pagamento", $parametriUrl);

        return $this->get("inserimento_persona")->inserisciPersona($urlIndietro, "inserisci_operatore", $parametriUrl);
    }

    /**
     * @Route("/{id_pagamento}/date_progetto", name="date_progetto")
     * @PaginaInfo(titolo="Dati generali pagamento", sottoTitolo="date di rendicontazione")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function dateProgettoAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->dateProgettoPagamento($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/dati_bancari_pagamento", name="dati_bancari_pagamento")
     * @PaginaInfo(titolo="Dati bancari pagamento", sottoTitolo="dati bancari")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function datiBancariAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->datiBancariPagamento($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/elenco_ricercatori", name="elenco_ricercatori")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Elenco ricercatori")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function elencoRicercatoriAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->elencoRicercatori($pagamento);
    }

    /**
     * disabilitata perchè si crea contestualmente alle voci di tipo 1
     * @Route("/aggiungi_ricercatore/{id_pagamento}", name="aggiungi_ricercatore")
     * @PaginaInfo(titolo="Dettaglio referente")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function aggiungiRicercatoreAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->aggiungiRicercatore($pagamento);
    }

    /**
     * @Route("/modifica_ricercatore/{id_ricercatore}", name="modifica_ricercatore")
     * @PaginaInfo(titolo="Dettaglio referente")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AnagraficheBundle:Personale", opzioni={"id": "id_ricercatore"})
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function modificaRicercatoreAction($id_ricercatore) {
        $ricercatore = $this->getEm()->getRepository("AnagraficheBundle\Entity\Personale")->find($id_ricercatore);
        return $this->getGestorePagamenti()->modificaRicercatore($ricercatore);
    }

    /**
     * disabilitata perchè si cancella contestualmente quando si cancella la voce 1 a cui è associato
     * @Route("/elimina_ricercatore/{id_ricercatore}", name="elimina_ricercatore")
     * @ControlloAccesso(contesto="soggetto", classe="AnagraficheBundle:Personale", opzioni={"id": "id_ricercatore"})
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function rimuoviRicercatoreAction($id_ricercatore) {
        $this->get('base')->checkCsrf('token');
        $ricercatore = $this->getEm()->getRepository("AnagraficheBundle\Entity\Personale")->find($id_ricercatore);

        $pagamento = $ricercatore->getPagamento();
        try {
            $response = $this->getGestorePagamenti()->rimuoviRicercatore($id_ricercatore);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_ricercatori", ["id_pagamento" => $pagamento->getId()]);
        } catch (\Exception $e) {
            return $this->addErrorRedirect("Errore generico", "elenco_ricercatori", ["id_pagamento" => $pagamento->getId()]);
        }
    }

    /**
     * @Route("/elenco_documenti_ricercatore/{id_ricercatore}", name="elenco_documenti_ricercatore")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Elenco Documenti", sottoTitolo="carica i documenti richiesti")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AnagraficheBundle:Personale", opzioni={"id": "id_ricercatore"})
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function elencoDocumentiRicercatoreAction($id_ricercatore) {
        $response = $this->getGestorePagamenti()->elencoDocumentiCaricati($id_ricercatore);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_ricercatore}/elimina_documento_ricercatore/{id_documento_ricercatore}", name="elimina_documento_ricercatore")
     * @ControlloAccesso(contesto="soggetto", classe="AnagraficheBundle:Personale", opzioni={"id": "id_ricercatore"})
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function eliminaDocumentoRicercatoreAction($id_documento_ricercatore, $id_ricercatore) {
        $this->get('base')->checkCsrf('token');
        $response = $this->getGestorePagamenti()->eliminaDocumentoRicercatore($id_documento_ricercatore, $id_ricercatore);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_pagamento}/accetta_maggiorazione", name="accetta_maggiorazione")
     * @PaginaInfo(titolo="Dati generali pagamento", sottoTitolo="dati bancari")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function maggiorazioneContributoAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->accettaMaggiorazioneContributo($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_durc", name="gestione_durc")
     * @PaginaInfo(titolo="Dati proponenti", sottoTitolo="Dati proponenti")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestioneDurcAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneDurc($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_dichiarazioni_proponenti", name="gestione_dichiarazioni_proponenti")
     * @PaginaInfo(titolo="Documenti proponenti", sottoTitolo="pagina di gestione dei documenti dei proponenti")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestioneDichiarazioniAltriProponentiAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneDichiarazioniAltriProponenti($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/{id_proponente}/documenti_generali_pagamento", name="documenti_generali_pagamento")
     * @PaginaInfo(titolo="Documenti generali", sottoTitolo="documenti generali del pagamento")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function documentiGeneraliPagamentoAction($id_pagamento, $id_proponente) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneDocumentiGeneraliPagamento($id_pagamento, $id_proponente);
    }

    /**
     * @Route("/{id_documento}/{id_proponente}/elimina_documento_generale_pagamento", name="elimina_documento_generale_pagamento")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:DocumentoEstensionePagamento", opzioni={"id": "id_documento"})
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function eliminaDocumentoGeneralePagamentoAction($id_documento, $id_proponente) {
        return $this->getGestorePagamenti()->eliminaDocumentoGeneralePagamento($id_documento, $id_proponente);
    }

    /**
     * @Route("/{id_pagamento}/gestione_antimafia", name="gestione_antimafia")
     * @PaginaInfo(titolo="Gestione antimafia", sottoTitolo="pagina di gestione antimafia")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestioneAntimafiaAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneAntimafiaPagamento($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_autodichiarazioni", name="gestione_autodichiarazioni_autorizzazioni")
     * @PaginaInfo(titolo="Gestione autodichiarazioni", sottoTitolo="pagina di gestione autodichiarazioni")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestioneAutodichiarazioniAutorizzazioniAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneAutodichiarazioniAutorizzazioni($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/relazione_finale", name="relazione_finale")
     * @PaginaInfo(titolo="Relazione Finale", sottoTitolo="pagina di gestione della relazione finale")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function relazioneFinaleAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->relazioneFinale($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_documenti_anticipo", name="gestione_documenti_anticipo_pagamento")
     * @PaginaInfo(titolo="Documenti pagamento", sottoTitolo="pagina di gestione dei documenti del pagamento")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     * questa funzione è dedicata per gli anticipi
     */
    public function gestioneDocumentiAnticipoAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneDocumentiAnticipoPagamento($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_indicatori", name="gestione_monitoraggio_indicatori")
     * @PaginaInfo(titolo="Indicatori di output", sottoTitolo="pagina di gestione degli indicatori di output")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"})
     */
    public function gestioneIndicatoriAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        if (\is_null($pagamento)) {
            throw new SfingeException("Pagamento $id_pagamento non trovato");
        }
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneIndicatori($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_indicatore/{id_indicatore}", name="gestione_monitoraggio_singolo_indicatore")
     * @PaginaInfo(titolo="Indicatori di output", sottoTitolo="pagina di gestione degli indicatori di output")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"})
     */
    public function gestioneSingoloIndicatoreAction($id_pagamento, $id_indicatore) {
        $pagamento = $this->getPagamento($id_pagamento);
        if (\is_null($pagamento)) {
            throw new SfingeException("Pagamento $id_pagamento non trovato");
        }
        /** @var $indicatore */
        $indicatore = $this->getEm()->getRepository('RichiesteBundle:IndicatoreOutput')->find($id_indicatore);
        if (\is_null($indicatore)) {
            throw new SfingeException("Indicatore $id_indicatore non trovato");
        }
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneSingoloIndicatore($pagamento, $indicatore);
    }

    /**
     * @Route("/{id_pagamento}/gestione_fasi_procedurali", name="gestione_monitoraggio_fasi_procedurali")
     * @PaginaInfo(titolo="Fasi procedurali", sottoTitolo="pagina di gestione delle fasi procedurali")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"})
     */
    public function gestioneFasiProceduraliAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        if (\is_null($pagamento)) {
            throw new SfingeException('Pagamento non trovato');
        }
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneFasiProcedurali($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_impegni", name="gestione_monitoraggio_impegni")
     * @PaginaInfo(titolo="Impegni e disimpegni", sottoTitolo="pagina di gestione degli impegni e dei disimpegni")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"})
     */
    public function gestioneImpegniAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        if (\is_null($pagamento)) {
            throw new SfingeException('Pagamento non trovato');
        }
        $richiesta = $pagamento->getRichiesta();

        $paginaService = $this->get('pagina');
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$paginaService->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
		$paginaService->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$paginaService->aggiungiElementoBreadcrumb("Impegni e disimpegni");

        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneImpegni($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/crea_impegno", name="crea_monitoraggio_impegni")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"})
     */
    public function creaImpegniAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        if (\is_null($pagamento)) {
            throw new SfingeException('Pagamento non trovato');
        }
        $richiesta = $pagamento->getRichiesta();
        $impegno = new RichiestaImpegni($richiesta);
        $richiesta->addMonImpegni($impegno);
        $em = $this->getEm();
        try{
            $em->persist($impegno);
            $em->flush($impegno);
        }
        catch(\Exception $e){
            $this->get('logger')->error($e->getMessage());
            $this->addErrorRedirect('Errore durante la creazione dell\'impegno','gestione_monitoraggio_impegni',['id_pagamento' => $id_pagamento]);
        }
        return $this->addSuccessRedirect('Impegno inserito con successo', 'gestione_modifica_monitoraggio_impegni',[
            'id_pagamento' => $id_pagamento,
            'id_impegno' => $impegno->getId(),
        ]);
    }

    /**
     * @Route("/{id_pagamento}/modifica_impegno/{id_impegno}", name="gestione_modifica_monitoraggio_impegni", defaults={"id_impegno": NULL})
     * @PaginaInfo(titolo="Modifica impegno o disimpegno", sottoTitolo="pagina di gestione degli impegni e dei disimpegni")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"})
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:RichiestaImpegni", opzioni={"id": "id_impegno"})
     */
    public function modificaImpegnoAction(string $id_pagamento, ?string $id_impegno):Response {
        $em = $this->getEm();
        $pagamento = $this->getPagamento($id_pagamento);
        if (\is_null($id_impegno)) {
            if($pagamento->isPagamentoDisabilitato()){
                throw new SfingeException("Impossibile creare un impegno per un pagamento disabilitato");
            }
            $impegno = new RichiestaImpegni($pagamento->getRichiesta());
            $em->persist($impegno);
            $em->flush($impegno);
            return $this->redirectToRoute('gestione_modifica_monitoraggio_impegni',[
                'id_pagamento' => $id_pagamento, 
                'id_impegno' => $impegno->getId()
            ]);
        }
        $impegno = $em->getRepository('AttuazioneControlloBundle:RichiestaImpegni')->find($id_impegno);
            if ($pagamento->getSoggetto()->getId() !== $impegno->getSoggetto()->getId()) {
                $impegno = null;
            }
        $richiesta = $pagamento->getRichiesta();

        $paginaService = $this->get('pagina');
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$paginaService->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $richiesta->getId())));
		$paginaService->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
        $paginaService->aggiungiElementoBreadcrumb("Impegni e disimpegni", $this->generateUrl("gestione_monitoraggio_impegni", array("id_pagamento" => $pagamento->getId())));
		$paginaService->aggiungiElementoBreadcrumb("Modifica impegno o disimpegno");
        
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneFormImpegno($pagamento, $impegno);
    }

    /**
     * @Route("/{id_pagamento}/elimina_impegno/{id_impegno}", name="gestione_elimina_monitoraggio_impegni")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:RichiestaImpegni", opzioni={"id": "id_impegno"})
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"})
     */
    public function eliminaImpegnoAction($id_pagamento, $id_impegno) {
        $this->get('base')->checkCsrf('token');
        $pagamento = $this->getPagamento($id_pagamento);
        if (\is_null($pagamento)) {
            throw new SfingeException('Pagamento non trovato');
        }
        return $this->getGestorePagamenti($pagamento->getProcedura())->eliminaImpegno($pagamento, $id_impegno);
    }

    /**
     * @Route("/{id_pagamento}/gestione_procedura_aggiudicazione", name="gestione_monitoraggio_procedura_aggiudicazione")
     * @PaginaInfo(titolo="Procedure di aggiudicazione", sottoTitolo="pagina di gestione degli impegni e dei disimpegni")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"})
     */
    public function gestioneProceduraAggiudicazioneAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        if (\is_null($pagamento)) {
            throw new SfingeException('Pagamento non trovato');
        }
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneProceduraAggiudicazione($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/modifica_procedura_aggiudicazione/{id_procedura_aggiudicazione}", name="gestione_monitoraggio_modifica_procedura_aggiudicazione", defaults={"id_procedura_aggiudicazione": NULL})
     * @PaginaInfo(titolo="Procedure di aggiudicazione", sottoTitolo="Modifica o inserimento delle procedure di aggiudicazione")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"})
     */
    public function gestioneModificaProceduraAggiudicazioneAction($id_pagamento, $id_procedura_aggiudicazione) {
        $pagamento = $this->getPagamento($id_pagamento);
        if (\is_null($pagamento)) {
            throw new SfingeException('Pagamento non trovato');
        }
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneModificaProceduraAggiudicazione($pagamento, $id_procedura_aggiudicazione);
    }

    /**
     * @Route("/{id_pagamento}/crea_procedura_aggiudicazione", name="gestione_monitoraggio_crea_procedura_aggiudicazione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"})
     */
    public function creaProceduraAggiudicazioneAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        if (\is_null($pagamento)) {
            throw new SfingeException('Pagamento non trovato');
        }
        $richiesta = $pagamento->getRichiesta();
        $procedura = new ProceduraAggiudicazione($richiesta);
        $richiesta->addMonProcedureAggiudicazione($procedura);
        try {
            $em = $this->getEm();
            $em->persist($procedura);
            $em->flush();
        } catch (\Exception $ex) {
            $this->get('logger')->error($ex->getMessage());
            return $this->addErrorRedirect(
                "Errore durante la creazione della procedura di aggiudicazione",
                'gestione_monitoraggio_procedura_aggiudicazione',
                ['id_pagamento' => $id_pagamento]
            );    
        }
        return $this->addSuccessRedirect('Procedura di aggiudicazione creata con successo',
            'gestione_monitoraggio_modifica_procedura_aggiudicazione',
            ['id_pagamento' => $id_pagamento, 'id_procedura_aggiudicazione' => $procedura->getId()]
        );
    }

    /**
     * @Route("/{id_pagamento}/elimina_procedura_aggiudicazione/{id_procedura_aggiudicazione}", name="gestione_monitoraggio_elimina_procedura_aggiudicazione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:ProceduraAggiudicazione", opzioni={"id": "id_procedura_aggiudicazione"})
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"})
     */
    public function gestioneEliminaProceduraAggiudicazioneAction($id_pagamento, $id_procedura_aggiudicazione) {
        $this->get('base')->checkCsrf('token');
        $pagamento = $this->getPagamento($id_pagamento);
        if (\is_null($pagamento)) {
            throw new SfingeException('Pagamento non trovato');
        }
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneEliminaProceduraAggiudicazione($pagamento, $id_procedura_aggiudicazione);
    }

    
    /**
     * @Route("/{id_pagamento}/elimina_indicatore/{id_indicatore}/{id_documento}", name="elimina_documento_indicatore")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"})
     * @throws \Exception
     */
    public function eliminaDocumentoIndicatoreOutputAction($id_pagamento, $id_indicatore, $id_documento): Response {
        $this->get('base')->checkCsrf('token');
        $pagamento = $this->getPagamento($id_pagamento);
        if (\is_null($pagamento)) {
            throw new SfingeException('Pagamento non trovato');
        }
        if($pagamento->isRichiestaDisabilitata()){
            throw new \Exception("Impossibile apportare modifiche a pagamento disabilitato", 1);
            
        }
        $indicatore =  $this->getEm()->getRepository('RichiesteBundle:IndicatoreOutput')->find($id_indicatore);
        if (\is_null($indicatore)) {
            throw new SfingeException('Indicatore non trovato');
        }
        $documento = $this->getEm()->getRepository('DocumentoBundle:DocumentoFile')->find($id_documento);
        if (\is_null($documento)) {
            throw new SfingeException('Documento non trovato');
        }
        return $this->getGestorePagamenti($pagamento->getProcedura())->eliminaDocumentoIndicatoreOutput($pagamento, $indicatore, $documento);
    }

    
    /**
     * @Route("/{id_pagamento}/elimina_documento_impegno/{id_documento}", name="elimina_documento_impegno")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"})
     * @ControlloAccesso(contesto="richiesta", classe="AttuazioneControlloBundle:DocumentoImpegno", opzioni={"id": "id_documento"})
     * @throws \Exception
     */
    public function eliminaDocumentoImpegnoAction($id_pagamento, $id_documento): Response {
        $this->get('base')->checkCsrf('token');
        $pagamento = $this->getPagamento($id_pagamento);
        if (\is_null($pagamento)) {
            throw new SfingeException('Pagamento non trovato');
        }
        if($pagamento->isRichiestaDisabilitata()){
            throw new \Exception("Impossibile apportare modifiche a pagamento disabilitato", 1);
            
        }
        $documento = $this->getEm()->getRepository('AttuazioneControlloBundle:DocumentoImpegno')->find($id_documento);
        if (\is_null($documento)) {
            throw new SfingeException('Documento non trovato');
        }
        return $this->getGestorePagamenti($pagamento->getProcedura())->eliminaDocumentoImpegno($pagamento, $documento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_questionario_rsi", name="gestione_questionario_rsi")
     * @PaginaInfo(titolo="Questionario RSI", sottoTitolo="Scelta questionario RSI")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestioneQuestionarioRsiAction($id_pagamento) {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestorePagamenti($pagamento->getProcedura())->gestioneQuestionarioRsi($pagamento);
    }
}
