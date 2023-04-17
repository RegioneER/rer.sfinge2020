<?php

namespace AttuazioneControlloBundle\Controller\Istruttoria;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use BaseBundle\Annotation\ControlloAccesso;
use AttuazioneControlloBundle\Entity\Pagamento;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use AttuazioneControlloBundle\Service\Istruttoria\IGestoreGiustificativi;
use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/istruttoria/giustificativi")
 */
class GiustificativiController extends \BaseBundle\Controller\BaseController {

    /**
     * @Route("/{id_pagamento}/avanzamento_istruttoria", name="avanzamento_istruttoria")
     * @PaginaInfo(titolo="Piano costi",sottoTitolo="Riepilogo piano costi")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"})  
     */
    public function avanzamentoAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->avanzamento($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/avanzamento_multi_istruttoria/{id_proponente}/{tipo}", name="avanzamento_multi_istruttoria", defaults={"tipo" = "SINGOLO"})
     * @PaginaInfo(titolo="Piano costi",sottoTitolo="Riepilogo piano costi")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"})  
     */
    public function avanzamentoMultiAction($id_pagamento, $id_proponente, $tipo) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->avanzamento($id_pagamento, $id_proponente, $tipo);
    }

    /**
     * @Route("/{id_pagamento}/elenco_contratti_istruttoria", name="elenco_contratti_istruttoria")
     * @PaginaInfo(titolo="Elenco contratti",sottoTitolo="Elenco dei contratti di consulenza associati al pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"})  
     */
    public function elencoContrattiAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->elencoContratti($id_pagamento);
    }

    /**
     * @Route("/{id_contratto}/visualizza_contratto_istruttoria", name="visualizza_contratto_istruttoria")
     * @PaginaInfo(titolo="Visualizzazione contratto",sottoTitolo="pagina di visualizzazione del contratto")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Contratto", opzioni={"id" = "id_contratto"})
     */
    public function visualizzaContrattoAction($id_contratto) {
        $contratto = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Contratto")->find($id_contratto);
        $pagamento = $contratto->getPagamento();
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->visualizzaContratto($id_contratto);
    }

    /**
     * @Route("/{id_pagamento}/elenco_giustificativi", name="elenco_giustificativi_istruttoria")
     * @PaginaInfo(titolo="Giustificativi del pagamento in istruttoria",sottoTitolo="elenco dei giustificativi del pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function elencoGiustificativiAction($id_pagamento) {
        // se definito in query string si mette in sessione e filtra per proponente
        $id_proponente = $this->getCurrentRequest()->get('proponente');
        if (is_numeric($id_proponente)) {
            $this->getSession()->set('elencoGiustificativiProponente', $id_proponente);
        }

        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->elencoGiustificativi($id_pagamento);
    }

    /**
     * @Route("/{id_giustificativo}/istruttoria_giustificativo/{id_pagamento_rif}", name="istruttoria_giustificativo_pagamento", defaults={"id_pagamento_rif" = "0"})
     * @PaginaInfo(titolo="Istruttoria giustificativo",sottoTitolo="permette di effettuare l'istruttoria di un giustificativo")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function istruttoriaGiustificativoAction($id_giustificativo, $id_pagamento_rif) {
        $giustificativo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);
        $pagamento_rif = ($id_pagamento_rif === 0) ? null : $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento_rif);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($giustificativo->getPagamento()->getProcedura())->istruttoriaGiustificativo($giustificativo, $pagamento_rif);
    }

    /**
     * @Route("/{id_pagamento}/elenco_amministrativi_istruttoria", name="elenco_amministrativi_istruttoria")
     * @PaginaInfo(titolo="Elenco documenti giustificativo",sottoTitolo="pagina di gestione dei documenti del giustificativo")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_giustificativo"})
     */
    public function elencoDocumentiAmministrativiAction($id_pagamento) {

        // se definito in query string si mette in sessione e filtra per proponente
        $id_proponente = $this->getCurrentRequest()->get('proponente');
        if (is_numeric($id_proponente)) {
            $this->getSession()->set('elencoDocumentiAmministrativiProponente', $id_proponente);
        }

        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->elencoDocumentiAmministrativi($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_documenti_personale_istruttoria", name="gestione_documenti_personale_istruttoria")
     * @PaginaInfo(titolo="Gestione documenti personale",sottoTitolo="pagina di gestione dei documenti del personale")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
     */
    public function gestioneDocumentiPersonaleAction($id_pagamento) {

        // se definito in query string si mette in sessione e filtra per proponente
        $id_proponente = $this->getCurrentRequest()->get('proponente');
        if (is_numeric($id_proponente)) {
            $this->getSession()->set('gestioneDocumentiPersonaleProponente', $id_proponente);
        }

        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->gestioneDocumentiPersonale($id_pagamento);
    }

    /**
     * @Route("/{id_vpc_giustificativo}/dettaglio_imputazione_istruttoria/{id_pagamento_rif}", name="dettaglio_imputazione_istruttoria", defaults={"id_pagamento_rif" = "0"})
     * @PaginaInfo(titolo="Istruttoria dettaglio imputazione",sottoTitolo="pagina di istruttoria del dettaglio imputazione")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
     */
    public function istruttoriaDettaglioImputazioneAction($id_vpc_giustificativo, $id_pagamento_rif) {
        $voceGiustificativo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo")->find($id_vpc_giustificativo);
        $giustificativo = $voceGiustificativo->getGiustificativoPagamento();
        $pagamento = $giustificativo->getPagamento();
        $pagamento_rif = ($id_pagamento_rif === 0) ? null : $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento_rif);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->istruttoriaDettaglioImputazione($id_vpc_giustificativo, $pagamento_rif);
    }

    /**
     * @Route("/{id_pagamento}/istruttoria_documento_giustificativo/{id_documento_giustificativo}", name="istruttoria_documento_giustificativo")
     * @PaginaInfo(titolo="Istruttoria dettaglio imputazione",sottoTitolo="pagina di istruttoria del documento giustificativo")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
     */
    public function istruttoriaDocumentoGiustificativo($id_pagamento, $id_documento_giustificativo) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->istruttoriaDocumentoGiustificativo($id_documento_giustificativo);
    }

    /**
     * @Route("/{id_pagamento}/documenti_amministrativi_brevetti_istruttoria/{id_amministrativo}", name="documenti_amministrativi_brevetti_istruttoria")
     * @PaginaInfo(titolo="Documenti amministrativi voce",sottoTitolo="gestione dei documenti amministrativi di una voce")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_pagamento"})
     */
    public function documentiAmministrativiBrevettiAction($id_pagamento, $id_amministrativo) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->istruttoriaDocumentiAmministrativi5($id_amministrativo, $id_pagamento, 'BREVETTI');
    }

    /**
     * @Route("/{id_pagamento}/documenti_amministrativi_consulenze_istruttoria/{id_amministrativo}", name="documenti_amministrativi_consulenze_istruttoria")
     * @PaginaInfo(titolo="Documenti amministrativi voce",sottoTitolo="gestione dei documenti amministrativi di una voce")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_pagamento"})
     */
    public function documentiAmministrativiConsulenzeAction($id_pagamento, $id_amministrativo) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->istruttoriaDocumentiAmministrativi5($id_amministrativo, $id_pagamento, 'CONSULENZE');
    }

    /**
     * @Route("/{id_pagamento}/single_doc_ammin_brevetti_istruttoria/{id_documento_amministrativo}", name="single_doc_ammin_brevetti_istruttoria")
     * @PaginaInfo(titolo="Documenti amministrativi voce",sottoTitolo="gestione dei documenti amministrativi di una voce")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_pagamento"})
     */
    public function istruttoriaSingoloDocAmmBrevettiAction($id_pagamento, $id_documento_amministrativo) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->istruttoriaSingoloDocumentoAmministrativo($id_documento_amministrativo, $id_pagamento, 'BREVETTI');
    }

    /**
     * @Route("/{id_pagamento}/single_doc_ammin_consulenze_istruttoria/{id_documento_amministrativo}", name="single_doc_ammin_consulenze_istruttoria")
     * @PaginaInfo(titolo="Documenti amministrativi voce",sottoTitolo="gestione dei documenti amministrativi di una voce")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_pagamento"})
     */
    public function istruttoriaSingoloDocAmmConsulAction($id_pagamento, $id_documento_amministrativo) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->istruttoriaSingoloDocumentoAmministrativo($id_documento_amministrativo, $id_pagamento, 'CONSULENZE');
    }

    /**
     * @Route("/{id_pagamento}/istruttoria_documenti_amministrativi_voce6", name="istruttoria_documenti_amministrativi_voce6")
     * @PaginaInfo(titolo="Documenti amministrativi voce",sottoTitolo="gestione dei documenti amministrativi di una voce")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_pagamento"})
     */
    public function documentiAmministrativiVoce6Action($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->istruttoriaDocumentiAmministrativi6($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/istruttoria_documento_amministrativo_voce6/{id_documento_amministrativo}", name="istruttoria_documento_amministrativo_voce6")
     * @PaginaInfo(titolo="Documenti amministrativi voce",sottoTitolo="gestione dei documenti amministrativi di una voce")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_pagamento"})
     */
    public function istruttoriaDocumentoAmministrativoVoce6Action($id_pagamento, $id_documento_amministrativo) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->istruttoriaSingoloDocumentoAmministrativo6($id_documento_amministrativo, $id_pagamento, 'CONSULENZE');
    }

    /**
     * @Route("/{id_pagamento}/istruttoria_documento_personale_pagamento/{id_documento_personale}", name="istruttoria_documento_personale_pagamento")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"})
     */
    public function istruttoriaDocumentoPersonaleAction($id_documento_personale, $id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->istruttoriaDocumentoPersonale($id_documento_personale);
    }

    /**
     * @Route("/contratto/{contratto_id}/dati_contratto", name="dati_contratto_ajax")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Contratto", opzioni={"id" = "contratto_id"})
     */
    public function contrattiByIdAction($contratto_id) {
        $em = $this->get('doctrine.orm.entity_manager');
        $r = $em->getRepository('AttuazioneControlloBundle\Entity\Contratto');
        $contratto = $r->find($contratto_id);
        $dati = array();
        $dati['tipologia_spesa'] = $contratto->getTipologiaSpesa()->getDescrizione();
        $dati['denominazione_fornitore'] = $contratto->getFornitore();
        $dati['tipologia_fornitore'] = $contratto->getTipologiaFornitore()->getDescrizione();

        $json = json_encode($dati);

        return new \Symfony\Component\HttpFoundation\JsonResponse($dati);
    }

    /**
     * @Route("/{id_pagamento}/istruttoria_documenti_amm/{id_giustificativo}", name="istruttoria_documenti_amm")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
     */
    public function istruttoriaDocumentiAmministrativi($id_pagamento, $id_giustificativo) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->istruttoriaDocumentiAmministrativi($id_giustificativo);
    }

    /**
     * @Route("/{id_pagamento}/istruttoria_documento_prototipo/{id_documento_prototipo}", name="istruttoria_documento_prototipo")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"})
     */
    public function istruttoriaDocumentoPrototipoAction($id_documento_prototipo, $id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->istruttoriaDocumentoPrototipo($id_documento_prototipo);
    }

    /**
     * @Route("/{id_quietanza}/istruttoria_dettaglio_quietanza", name="istruttoria_dettaglio_quietanza")
     * ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:QuietanzaGiustificativo", opzioni={"id" = "id_quietanza"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function dettaglioQuietanzaAction($id_quietanza) {
        $quietanza = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\QuietanzaGiustificativo")->find($id_quietanza);
        $richiesta = $quietanza->getGiustificativoPagamento()->getPagamento()->getAttuazioneControlloRichiesta()->getRichiesta();
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($richiesta->getProcedura())->dettaglioQuietanza($quietanza);
    }

    /**
     * @Route("/{id_pagamento}/avanzamento_rendicontazione_istruttoria", name="avanzamento_rendicontazione_istruttoria")
     * @PaginaInfo(titolo="Avanzamento piano costi",sottoTitolo="Avanzamento piano costi")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}) 
     * rendicontazione standard 
     */
    public function avanzamentoPianoCostiAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->avanzamentoPianoCosti($pagamento);
    }

    /**
     * @Route("/{id_pagamento}/esporta_giustificativi", name="esporta_giustificativi")
     */
    public function esportaGiustificativiAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->esportaGiustificativi($pagamento);
    }

    /**
     * @Route("/{id_giustificativo}/modifica_voci_imputazione", name="modifica_voci_imputazione_giustificativo")
     * @ParamConverter("giustificativo", options={"mapping": {"id_giustificativo"   : "id"}})
     * * @PaginaInfo(titolo="Modifica voci giustificativi",sottoTitolo="Modifica delle voci di spesa dei giustificativi per il proponente")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     */
    public function modificaVociImputazioneAction(GiustificativoPagamento $giustificativo): Response {
        $pagamento = $giustificativo->getPagamento();
        /** @var IGestoreGiustificativi $service  */
        $service = $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura());

        return $service->modificaVociImputazione($giustificativo);
    }

    /**
     * @Route("/{id_pagamento}/{id_contratto}/elenco_documenti_contratto_istruttoria", name="elenco_documenti_contratto_istruttoria")
     * @PaginaInfo(titolo="Elenco Documenti")
     * ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function elencoDocumentiContrattoAction($id_pagamento, $id_contratto) {
        $contratto = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Contratto")->find($id_contratto);
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->istruttoriaDocumentiContratto($contratto->getId(), $pagamento->getId());
    }
    
    /**
     * @Route("/{id_pagamento}/documento_contratto_istruttoria/{id_documento_contratto}", name="documento_contratto_istruttoria")
     * @PaginaInfo(titolo="Documenti contratto voce",sottoTitolo="gestione dei documenti contratto")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @Method({"GET", "POST"})
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_pagamento"})
     */
    public function istruttoriaSingoloDocContrattoAction($id_pagamento, $id_documento_contratto) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->istruttoriaSingoloDocumentoContratto($id_documento_contratto, $id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/elenco_giustificativi_contratto/{id_contratto}", name="elenco_giustificativi_contratto_istruttoria")
     * @PaginaInfo(titolo="Giustificativi del pagamento in istruttoria",sottoTitolo="elenco dei giustificativi del pagamento")
     * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function elencoGiustificativiContrattoAction($id_pagamento, $id_contratto) {
        // se definito in query string si mette in sessione e filtra per proponente
        $id_proponente = $this->getCurrentRequest()->get('proponente');
        if (is_numeric($id_proponente)) {
            $this->getSession()->set('elencoGiustificativiProponente', $id_proponente);
        }

        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $contratto = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Contratto")->find($id_contratto);
        return $this->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura())->elencoGiustificativiContratto($id_pagamento, $id_contratto);
    }
}
