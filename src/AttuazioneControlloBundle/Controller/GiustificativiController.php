<?php

namespace AttuazioneControlloBundle\Controller;

use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use Gdbnet\FatturaElettronica\FatturaElettronica;
use Gdbnet\FatturaElettronica\FatturaElettronicaXmlReader;
use Gdbnet\FatturaElettronica\XmlValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use BaseBundle\Annotation\ControlloAccesso;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use AttuazioneControlloBundle\Entity\Pagamento;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/beneficiario/giustificativi")
 */
class GiustificativiController extends \BaseBundle\Controller\BaseController {

    /**
     * @Route("/{id_pagamento}/elenco", name="elenco_giustificativi")
     * @PaginaInfo(titolo="Elenco giustificativi",sottoTitolo="Elenco dei giustificativi definiti per un pagamento")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function elencoGiustificativiAction($id_pagamento) {
        // se definito in query string si mette in sessione e filtra per proponente
        $id_proponente = $this->getCurrentRequest()->get('proponente');
        if (is_numeric($id_proponente)) {
            $this->getSession()->set('elencoGiustificativiProponente', $id_proponente);
        }
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->elencoGiustificativi($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/{id_contratto}/elenco", name="elenco_giustificativi_contratto")
     * @PaginaInfo(titolo="Elenco giustificativi",sottoTitolo="Elenco dei giustificativi definiti per un pagamento")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function elencoGiustificativiContrattoAction($id_pagamento, $id_contratto) {
        // se definito in query string si mette in sessione e filtra per proponente
        $id_proponente = $this->getCurrentRequest()->get('proponente');
        if (is_numeric($id_proponente)) {
            $this->getSession()->set('elencoGiustificativiProponente', $id_proponente);
        }
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->elencoGiustificativiContratto($id_contratto, $id_pagamento);
    }
    
    /**
     * @Route("/{id_pagamento}/avanzamento", name="avanzamento")
     * @PaginaInfo(titolo="Piano costi",sottoTitolo="Riepilogo piano costi")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function avanzamentoAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->avanzamento($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/avanzamento_multi/{id_proponente}/{tipo}", name="avanzamento_multi", defaults={"tipo" = "SINGOLO"})
     * @PaginaInfo(titolo="Piano costi",sottoTitolo="Riepilogo piano costi")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function avanzamentoMultiAction($id_pagamento, $id_proponente, $tipo) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->avanzamento($id_pagamento, $id_proponente, $tipo);
    }

    /**
     * @Route("/{id_pagamento}/aggiungi", name="aggiungi_giustificativo")
     * @PaginaInfo(titolo="Creazione giustificativo",sottoTitolo="pagina di creazione di un giustificativo")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function aggiungiGiustificativoAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->aggiungiGiustificativo($id_pagamento);
    }

    /**
     * @Route("/leggi_fattura_elettronica", name="leggi_fattura_elettronica")
     *
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     *
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function leggiFatturaElettronicaAction(Request $request) {
        if ($request->isXMLHttpRequest()) {
            /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
            $file = $request->files->get('fatturaElettronica');
            $mime = $file->getClientMimeType();
            $name = $file->getClientOriginalName();
            $basename = explode('.', $name);
            $est = end($basename);
            if ($est != 'xml' && $est != 'XML' && $est != 'p7m' && $est != 'P7M') {
                return new Response('Estensione file NON valida, ammesse solo .xml e .p7m', 500);
            }
            if (!in_array($mime, array('application/pkcs7-mime', 'application/pkcs7', 'application/x-pkcs7-mime', 'application/binary', 'application/octet-stream', 'application/xml', 'text/xml', 'text/plain'))) {
                return new Response('Formato mimetype NON valido: ' . $mime, 500);
            }
            if (in_array($mime, array('application/pkcs7-mime', 'application/pkcs7', 'application/binary', 'application/octet-stream'))) {
                /* @var $serviceFD \DocumentoBundle\Component\GestioneFirmaDigitale */
                $serviceDoc = $this->get("documenti");
                $serviceDoc->initGestioneFirmaDigitale();
                $serviceFD = $serviceDoc->getGestioneFirmaDigitale();
                $serviceFD->loadDocumentFromPath($file->getRealPath());
                $serviceFD->estraiContenutoDocumentoInterno();
                $extFile = $serviceFD->getContenutoDocumentoInterno();
                $xml = FatturaElettronicaXmlReader::clearSignature($extFile);
            } else {
                $xml = FatturaElettronicaXmlReader::clearSignature(file_get_contents($file->getPathname()));
            }
            $validator = new XmlValidator();

            if (!$validator->validate($xml)) {
                $errori = $validator->getErrors();
                $erroriFlat = array();
                foreach ($errori as $e) {
                    $erroriFlat[] = $e->message;
                }
                return new Response('Fattura NON Valida: ' . implode(', ', $erroriFlat), 500);
            }

            $reader = new FatturaElettronicaXmlReader();

            /** @var FatturaElettronica $fatturaElettronica */
            $fatturaElettronica = $reader->decodeXml($xml);

            /*
             * La fattura elettronica dovrebbe prevedere la presenza di uno dei due campi tra piva o cf per il 
             * cedente prestatore quindi prima valuto la presenza del cf altrimenti prendo la piva con il formato
             * paese + codice
             */
            if (!is_null($fatturaElettronica->getHeader()->getCedentePrestatore()->getCodiceFiscale())) {
                $cf = $fatturaElettronica->getHeader()->getCedentePrestatore()->getCodiceFiscale();
            } else {
                $cf_part1 = $fatturaElettronica->getHeader()->getCedentePrestatore()->getIdFiscaleIVA()->getIdPaese();
                $cf_part2 = $fatturaElettronica->getHeader()->getCedentePrestatore()->getIdFiscaleIVA()->getIdCodice();
                $cf = $cf_part1 . $cf_part2;
            }

            /*
             * La denominazione può provenire o dal campo denominazione dell'anagrafica o come concatezione di nome e cognome del cedente prestatore
             */
            if (is_null($fatturaElettronica->getHeader()->getCedentePrestatore()->getAnagrafica()->getDenominazione())) {
                $anagrafica = $fatturaElettronica->getHeader()->getCedentePrestatore()->getAnagrafica();
                $denominazione = $anagrafica->getNome() . ' ' . $anagrafica->getCognome();
            } else {
                $denominazione = $fatturaElettronica->getHeader()->getCedentePrestatore()->getAnagrafica()->getDenominazione();
            }

            return new JsonResponse([
                'denominazione' => $denominazione,
                'codiceFiscale' => $cf,
                'numeroFattura' => $fatturaElettronica->getNumeroFattura(),
                'dataFattura' => date('d/m/Y', strtotime($fatturaElettronica->getDataFattura())),
                'importo' => number_format($fatturaElettronica->getImportoTotaleDocumento(), 2, ",", "."),
                'descrizione' => implode(PHP_EOL, $fatturaElettronica->getDescrizione()),
            ]);
        }

        return new Response('This is not ajax!', 400);
    }

    /**
     * @Route("/{id_giustificativo}/elimina", name="elimina_giustificativo")
     * @ControlloAccesso(contesto="giustificativo", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_giustificativo"}, azione=\AttuazioneControlloBundle\Security\GiustificativoVoter::WRITE)  
     */
    public function eliminaGiustificativoAction($id_giustificativo) {
        return $this->get("gestore_giustificativi")->getGestore()->eliminaGiustificativo($id_giustificativo);
    }

    /**
     * @Route("/{id_giustificativo}/{id_documento_giustificativo}/elimina_documento_giustificativo", name="elimina_documento_giustificativo")
     * @ControlloAccesso(contesto="giustificativo", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_giustificativo"}, azione=\AttuazioneControlloBundle\Security\GiustificativoVoter::WRITE)  
     */
    public function eliminaDocumentoGiustificativoAction($id_giustificativo, $id_documento_giustificativo) {
        return $this->get("gestore_giustificativi")->getGestore()->eliminaDocumentoGiustificativo($id_documento_giustificativo, $id_giustificativo);
    }

    /**
     * @Route("/{id_giustificativo}/dettaglio", name="dettaglio_giustificativo")
     * @PaginaInfo(titolo="Dettaglio giustificativo",sottoTitolo="pagina di riepilogo del giustificativo")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="giustificativo", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_giustificativo"}, azione=\AttuazioneControlloBundle\Security\GiustificativoVoter::WRITE)  
     */
    public function dettaglioGiustificativoAction($id_giustificativo) {
        return $this->get("gestore_giustificativi")->getGestore()->dettaglioGiustificativo($id_giustificativo);
    }

    /**
     * @Route("/{id_giustificativo}/modifica", name="modifica_giustificativo")
     * @PaginaInfo(titolo="Modifica giustificativo",sottoTitolo="pagina di modifica del giustificativo")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="giustificativo", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_giustificativo"}, azione=\AttuazioneControlloBundle\Security\GiustificativoVoter::WRITE)  
     * @ParamConverter("giustificativo", options={"mapping": {"id_giustificativo"   : "id"}})
     */
    public function modificaGiustificativoAction(GiustificativoPagamento $giustificativo) {
        return $this->get("gestore_giustificativi")
                        ->getGestore($giustificativo->getPagamento()->getProcedura())
                        ->modificaGiustificativo($giustificativo->getId());
    }

    /**
     * @Route("/{id_giustificativo}/elenco_documenti/{id_pagamento_rif}", name="elenco_documenti_giustificativo", defaults={"id_pagamento_rif" = "0"})
     * @PaginaInfo(titolo="Elenco documenti giustificativo",sottoTitolo="pagina di gestione dei documenti del giustificativo")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="giustificativo", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_giustificativo"}, azione=\AttuazioneControlloBundle\Security\GiustificativoVoter::WRITE)  
     */
    public function elencoDocumentiGiustificativoAction($id_giustificativo, $id_pagamento_rif) {
        $pagamento_rif = ($id_pagamento_rif === 0) ? null : $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento_rif);
        return $this->get("gestore_giustificativi")->getGestore()->elencoDocumenti($id_giustificativo, array(), $pagamento_rif);
    }

    /**
     * @Route("/{id_giustificativo}/elimina_documento_giustificativo_2/{id_documento_giustificativo}", name="elimina_documento_giustificativo_2")
     * @ControlloAccesso(contesto="giustificativo", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_giustificativo"}, azione=\AttuazioneControlloBundle\Security\GiustificativoVoter::WRITE)  
     */
    public function eliminaDocumentoGiustificativo2Action($id_documento_giustificativo, $id_giustificativo) {
        $this->get('base')->checkCsrf('token');
        return $this->get("gestore_giustificativi")->getGestore()->eliminaDocumentoGiustificativo2($id_documento_giustificativo);
    }

    /**
     * @Route("/{id_pagamento}/elenco_amministrativi", name="elenco_amministrativi")
     * @PaginaInfo(titolo="Elenco documenti giustificativo",sottoTitolo="pagina di gestione dei documenti del giustificativo")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function elencoDocumentiAmministrativiAction($id_pagamento) {

        // se definito in query string si mette in sessione e filtra per proponente
        $id_proponente = $this->getCurrentRequest()->get('proponente');
        if (is_numeric($id_proponente)) {
            $this->getSession()->set('elencoDocumentiAmministrativiProponente', $id_proponente);
        }

        return $this->get("gestore_giustificativi")->getGestore()->elencoDocumentiAmministrativi($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/gestione_documenti_personale", name="gestione_documenti_personale")
     * @PaginaInfo(titolo="Gestione documenti personale",sottoTitolo="pagina di gestione dei documenti del personale")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function gestioneDocumentiPersonaleAction($id_pagamento) {

        // se definito in query string si mette in sessione e filtra per proponente
        $id_proponente = $this->getCurrentRequest()->get('proponente');
        if (is_numeric($id_proponente)) {
            $this->getSession()->set('gestioneDocumentiPersonaleProponente', $id_proponente);
        }

        return $this->get("gestore_giustificativi")->getGestore()->gestioneDocumentiPersonale($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/elimina_documento_personale/{id_documento_personale}", name="elimina_documento_personale_pagamento")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function eliminaDocumentoPersonaleAction($id_documento_personale, $id_pagamento) {
        $this->get('base')->checkCsrf('token');
        return $this->get("gestore_giustificativi")->getGestore()->eliminaDocumentoPersonale($id_documento_personale);
    }

    /**
     * @Route("/{id_pagamento}/documenti_amministrativi_consulenze/{id_amministrativo}", name="documenti_amministrativi_consulenze")
     * @PaginaInfo(titolo="Documenti amministrativi voce",sottoTitolo="gestione dei documenti amministrativi di una voce")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     * @ParamConverter("pagamento", options={"mapping": {"id_pagamento"   : "id"}})
     */
    public function documentiAmministrativiConsulenzeAction(Pagamento $pagamento, $id_amministrativo): Response {
        $procedura = $pagamento->getProcedura();
        $service = $this->get("gestore_giustificativi")->getGestore($procedura);

        return $service->compilaDocumentiAmministrativi5($id_amministrativo, $pagamento->getId(), 'CONSULENZE');
    }

    /**
     * @Route("/{id_pagamento}/documenti_amministrativi_brevetti/{id_amministrativo}", name="documenti_amministrativi_brevetti")
     * @PaginaInfo(titolo="Documenti amministrativi voce",sottoTitolo="gestione dei documenti amministrativi di una voce")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function documentiAmministrativiBrevettiAction($id_pagamento, $id_amministrativo) {
        return $this->get("gestore_giustificativi")->getGestore()->compilaDocumentiAmministrativi5($id_amministrativo, $id_pagamento, 'BREVETTI');
    }

    /**
     * @Route("/{id_pagamento}/elimina_documento_amministrativo/{id_documento_amministrativo}", name="elimina_documento_amministrativo")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function eliminaDocumentoAmministrativoAction($id_documento_amministrativo, $id_pagamento) {
        $this->get('base')->checkCsrf('token');
        return $this->get("gestore_giustificativi")->getGestore()->eliminaDocumentoAmministrativo($id_documento_amministrativo, $id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/elimina_documento_amministrativo_voce6/{id_documento_amministrativo}", name="elimina_documento_amministrativo6")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function eliminaDocumentoAmministrativo6Action($id_documento_amministrativo, $id_pagamento) {
        $this->get('base')->checkCsrf('token');
        return $this->get("gestore_giustificativi")->getGestore()->eliminaDocumentoAmministrativo6($id_documento_amministrativo, $id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/elenco_contratti", name="elenco_contratti")
     * @PaginaInfo(titolo="Elenco contratti",sottoTitolo="Elenco dei contratti di consulenza associati al pagamento")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function elencoContrattiAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->elencoContratti($id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/aggiungi_contratto", name="aggiungi_contratto")
     * @PaginaInfo(titolo="Aggiungi contratto",sottoTitolo="pagina di creazione del contratto")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function aggiungiContrattoAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->aggiungiContratto($id_pagamento);
    }

    /**
     * @Route("/{id_contratto}/modifica_contratto", name="modifica_contratto")
     * @PaginaInfo(titolo="Modifica contratto",sottoTitolo="pagina di modifica del contratto")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="contratto", classe="AttuazioneControlloBundle:Contratto", opzioni={"id" = "id_contratto"}, azione=\AttuazioneControlloBundle\Security\ContrattoVoter::WRITE)  
     */
    public function modificaContrattoAction($id_contratto) {
        return $this->get("gestore_giustificativi")->getGestore()->modificaContratto($id_contratto);
    }

    /**
     * @Route("/{id_contratto}/elimina_contratto", name="elimina_contratto")
     * @ControlloAccesso(contesto="contratto", classe="AttuazioneControlloBundle:Contratto", opzioni={"id" = "id_contratto"}, azione=\AttuazioneControlloBundle\Security\ContrattoVoter::WRITE)  
     */
    public function eliminaContrattoAction($id_contratto) {
        return $this->get("gestore_giustificativi")->getGestore()->eliminaContratto($id_contratto);
    }

    /**
     * @Route("/contratto/{contratto_id}/dati_contratto", name="dati_contratto_ajax")
     * @ControlloAccesso(contesto="contratto", classe="AttuazioneControlloBundle:Contratto", opzioni={"id" = "contratto_id"}, azione=\AttuazioneControlloBundle\Security\ContrattoVoter::WRITE)  
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
     * @Route("/{id_giustificativo}/documenti_amministrativi_prototipo", name="documenti_amministrativi_prototipo")
     * @PaginaInfo(titolo="Documenti amministrativi voce",sottoTitolo="gestione dei documenti amministrativi di una voce")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="giustificativo", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_giustificativo"}, azione=\AttuazioneControlloBundle\Security\GiustificativoVoter::WRITE)  
     */
    public function documentiAmministrativiPrototipoAction($id_giustificativo) {
        return $this->get("gestore_giustificativi")->getGestore()->compilaDocumentiAmministrativiPrototipo($id_giustificativo);
    }

    /**
     * @Route("/{id_pagamento}/elimina_documento_amministrativo_prototipo/{id_documento_prototipo}", name="elimina_documento_amministrativo_prototipo")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function eliminaDocumentoAmministrativoPrototipoAction($id_documento_prototipo, $id_pagamento) {
        $this->get('base')->checkCsrf('token');
        return $this->get("gestore_giustificativi")->getGestore()->eliminaDocumentoAmministrativoPrototipo($id_documento_prototipo, $id_pagamento);
    }

    /**
     * @Route("/{id_pagamento}/avanzamento_rendicontazione", name="avanzamento_rendicontazione_beneficiario")
     * @PaginaInfo(titolo="Piano costi",sottoTitolo="Avanzamento piano costi")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     * rendicontazione standard..per il moemnto l'ho aggiunta per evitare conflitti e bordelli con il pregresso
     * anche perchè implementa una logica diversa che unifica singolo e multi proponente
     */
    public function avanzamentoPianoCostiAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->avanzamentoPianoCosti($id_pagamento);
    }

    /**
     * @Route("/{id_giustificativo}/aggiungi_documento_giustificativo", name="aggiungi_documento_giustificativo")
     * @PaginaInfo(titolo="Caricamento documento giustificativo",sottoTitolo="pagina di caricamento di un documento per il giustificativo")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="giustificativo", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_giustificativo"}, azione=\AttuazioneControlloBundle\Security\GiustificativoVoter::WRITE)  
     */
    public function aggiungiDocumentoGiustificativoAction($id_giustificativo) {
        return $this->get("gestore_giustificativi")->getGestore()->aggiungiDocumentoGiustificativo($id_giustificativo);
    }

    /**
     * @Route("/{id_pagamento}/documenti_amministrativi_voce6", name="documenti_amministrativi_voce6")
     * @PaginaInfo(titolo="Documenti amministrativi voce",sottoTitolo="gestione dei documenti amministrativi di una voce")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE) 
     */
    public function documentiAmministrativiVoce6Action($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->compilaDocumentiAmministrativi6($id_pagamento);
    }

    /**
     * @Route("/{id_contratto}/visualizza_contratto", name="visualizza_contratto")
     * @PaginaInfo(titolo="Visualizzazione contratto",sottoTitolo="pagina di visualizzazione del contratto")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Contratto", opzioni={"id" = "id_contratto"})
     */
    public function visualizzaContrattoAction($id_contratto) {
        $contratto = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Contratto")->find($id_contratto);
        $pagamento = $contratto->getPagamento();
        return $this->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->visualizzaContratto($id_contratto);
    }

    /**
     * @Route("/{id_pagamento}/{id_contratto}/elenco_documenti_contratto", name="elenco_documenti_contratto")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Elenco Documenti")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     */
    public function elencoDocumentiContrattoAction($id_pagamento, $id_contratto) {
        $contratto = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Contratto")->find($id_contratto);
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        return $this->get("gestore_giustificativi")->getGestore($pagamento->getProcedura())->elencoDocumentiContratto($contratto->getId(), $pagamento->getId());
    }

}
