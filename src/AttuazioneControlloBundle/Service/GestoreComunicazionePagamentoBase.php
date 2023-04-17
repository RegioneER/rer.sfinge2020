<?php

namespace AttuazioneControlloBundle\Service;

use AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaComunicazionePagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\RispostaComunicazionePagamento;
use BaseBundle\Entity\StatoComunicazionePagamento;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Service\GestoreResponse;
use BaseBundle\Exception\SfingeException;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;

class GestoreComunicazionePagamentoBase extends \BaseBundle\Service\BaseService {

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
    }

    /**
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @throws \Exception
     */
    public function calcolaAzioniAmmesse(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        throw new \Exception("Deve essere implementato nella classe derivata");
    }

    public function isBeneficiario() {
        return $this->isGranted("ROLE_UTENTE");
    }

    /**
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @return EsitoValidazione
     */
    public function validaNotaRisposta(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        $esito = new EsitoValidazione(true);

        if (is_null($rispostaComunicazionePagamento) || is_null($rispostaComunicazionePagamento->getTesto())) {
            $esito->setEsito(false);
            $esito->addMessaggio('Nota di risposta non fornita.');
            $esito->addMessaggioSezione('Nota di risposta non fornita.');
        }

        return $esito;
    }

    /**
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @return array
     */
    public function gestioneBarraAvanzamento(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        $statoRisposta = $rispostaComunicazionePagamento->getStato()->getCodice();
        $arrayStati = array('Inserita' => true, 'Validata' => false, 'Firmata' => false, 'Inviata' => false);

        switch ($statoRisposta) {
            case StatoComunicazionePagamento::COM_PAG_PROTOCOLLATA:
            case StatoComunicazionePagamento::COM_PAG_INVIATA_PA:
                $arrayStati['Validata'] = true;
                $arrayStati['Firmata'] = true;
                $arrayStati['Inviata'] = true;
                break;
            case StatoComunicazionePagamento::COM_PAG_FIRMATA:
                $arrayStati['Validata'] = true;
                $arrayStati['Firmata'] = true;
                break;
            case StatoComunicazionePagamento::COM_PAG_VALIDATA:
                $arrayStati['Validata'] = true;
                break;
        }

        return $arrayStati;
    }

    /**
     * @param ComunicazionePagamento $comunicazionePagamento
     * @param $opzioni
     * @return GestoreResponse
     * @throws SfingeException
     */
    public function notaRispostaComunicazionePagamento(ComunicazionePagamento $comunicazionePagamento, $opzioni) {
        $form_options["disabled"] = $this->isComunicazionePagamentoDisabilitata($comunicazionePagamento);
        $form_options = array_merge($form_options, $opzioni["form_options"]);
        $form = $this->createForm("AttuazioneControlloBundle\Form\ComunicazionePagamento\NotaRispostaComunicazionePagamentoType", $comunicazionePagamento->getRisposta(), $form_options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->flush();
                    $this->addFlash("success", "Nota risposta comunicazione pagamento salvata correttamente.");
                    return new GestoreResponse($this->redirect($form_options["url_indietro"]));
                } catch (\Exception $e) {
                    throw new SfingeException("Nota risposta comunicazione pagamento non salvata.");
                }
            }
        }

        $dati = array("form" => $form->createView());
        $response = $this->render("AttuazioneControlloBundle:RispostaComunicazionePagamento:notaRispostaComunicazionePagamento.html.twig", $dati);

        return new GestoreResponse($response);
    }

    /**
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return bool
     */
    public function isComunicazionePagamentoDisabilitata(ComunicazionePagamento $comunicazionePagamento) {
        if (!$this->isBeneficiario()) {
            return true;
        }

        $risposta = $comunicazionePagamento->getRisposta();
        if (is_null($risposta)) {
            return false;
        }

        $stato = $risposta->getStato()->getCodice();
        if ($stato != StatoComunicazionePagamento::COM_PAG_INSERITA) {
            return true;
        }

        return false;
    }

    /**
     * @param ComunicazionePagamento $comunicazionePagamento
     * @param array $opzioni
     * @param null $proponente
     * @return GestoreResponse
     */
    public function elencoDocumenti(ComunicazionePagamento $comunicazionePagamento, array $opzioni = array(), $proponente = null) {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documentoComunicazionePagamento = new DocumentoRispostaComunicazionePagamento();
        $documentoFile = new DocumentoFile();
        $documentoComunicazionePagamento->setDocumentoFile($documentoFile);
        $documentoComunicazionePagamento->setRispostaComunicazionePagamento($comunicazionePagamento->getRisposta());

        $documentiCaricati = $em->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaComunicazionePagamento")->findBy(array(
            "risposta_comunicazione_pagamento" => $comunicazionePagamento->getRisposta(),
            "proponente" => $proponente
        ));

        $listaTipi = $this->getTipiDocumenti();

        if (count($listaTipi) > 0 && !$this->isComunicazionePagamentoDisabilitata($comunicazionePagamento)) {
            $opzioni_form["lista_tipi"] = $listaTipi;
            $opzioni_form["url_indietro"] = $opzioni["url_indietro"];
            $form = $this->createForm('AttuazioneControlloBundle\Form\ComunicazionePagamento\DocumentoRispostaComunicazionePagamentoType',
                    $documentoComunicazionePagamento, $opzioni_form);

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {
                        $this->container->get("documenti")->carica($documentoFile);
                        $em->persist($documentoComunicazionePagamento);
                        $em->flush();
                        $this->addFlash("success", "Documento caricato correttamente.");
                        return new GestoreResponse($this->redirect($opzioni["url_corrente"]));
                    } catch (\Exception $e) {
                        $this->addFlash('error', $e->getMessage());
                    }
                }
            }
            $form_view = $form->createView();
        } else {
            $form_view = null;
        }

        $dati = array(
            "documenti" => $documentiCaricati,
            "proponente" => $proponente,
            "form" => $form_view,
            "route_cancellazione_documento" => $opzioni["route_cancellazione_documento"],
            "url_indietro" => $opzioni["url_indietro"],
            "is_richiesta_disabilitata" => $this->isComunicazionePagamentoDisabilitata($comunicazionePagamento),
            "documenti_richiesti" => $listaTipi
        );

        $response = $this->render("AttuazioneControlloBundle:RispostaComunicazionePagamento:elencoDocumentiComunicazionePagamento.html.twig", $dati);
        return new GestoreResponse($response);
    }

    /**
     * @return mixed
     */
    public function getTipiDocumenti() {
        return $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findBy(['codice' => TipologiaDocumento::COMUNICAZIONE_PAGAMENTO_RISPOSTA_ALLEGATO]);
    }

    /**
     * @param ComunicazionePagamento $comunicazionePagamento
     * @param array $opzioni
     * @return GestoreResponse
     * @throws SfingeException
     */
    public function sceltaFirmatario(ComunicazionePagamento $comunicazionePagamento, $opzioni = array()) {
        $request = $this->getCurrentRequest();
        $form_options["disabled"] = $this->isComunicazionePagamentoDisabilitata($comunicazionePagamento);
        $form_options = array_merge($form_options, $opzioni["form_options"]);

        $form = $this->createForm("AttuazioneControlloBundle\Form\ComunicazionePagamento\SceltaFirmatarioComunicazionePagamentoType", $comunicazionePagamento->getRisposta(), $form_options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->flush();
                    $this->addFlash("success", "Firmatario della comunicazione impostato correttamente.");
                    return new GestoreResponse($this->redirect($form_options["url_indietro"]));
                } catch (\Exception $e) {
                    throw new SfingeException("Firmatario non impostato.");
                }
            }
        }

        $dati = array("firmatario" => $comunicazionePagamento->getRisposta()->getFirmatario(), "form" => $form->createView());
        $response = $this->render("AttuazioneControlloBundle:RispostaComunicazionePagamento:sceltaFirmatario.html.twig", $dati);

        return new GestoreResponse($response);
    }

    /**
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @param array $opzioni
     * @return GestoreResponse
     * @throws SfingeException
     */
    public function validaRispostaComunicazionePagamento(RispostaComunicazionePagamento $rispostaComunicazionePagamento, array $opzioni = array()) {
        if ($rispostaComunicazionePagamento->getStato()->uguale(StatoComunicazionePagamento::COM_PAG_INSERITA)) {
            $esitoValidazione = $this->controllaValiditaComunicazionePagamento($rispostaComunicazionePagamento);

            if ($esitoValidazione->getEsito()) {
                $this->getEm()->beginTransaction();
                if (!is_null($rispostaComunicazionePagamento->getDocumentoRisposta())) {
                    $this->container->get("documenti")->cancella($rispostaComunicazionePagamento->getDocumentoRisposta(), 0);
                }

                // Genero il nuovo pdf
                $pdf = $this->generaPdf($rispostaComunicazionePagamento);

                // Lo persisto
                $tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findOneBy(['codice' => TipologiaDocumento::COMUNICAZIONE_PAGAMENTO_RISPOSTA]);
                $documentoRisposta = $this->container->get("documenti")->caricaDaByteArray($pdf, $this->getNomePdfRispostaComunicazionePagamento($rispostaComunicazionePagamento) . ".pdf", $tipoDocumento, false);

                // Associo il documento alla richiesta
                $rispostaComunicazionePagamento->setDocumentoRisposta($documentoRisposta);
                $this->getEm()->persist($rispostaComunicazionePagamento);
                $this->getEm()->flush();
                $this->container->get("sfinge.stati")->avanzaStato($rispostaComunicazionePagamento, StatoComunicazionePagamento::COM_PAG_VALIDATA);
                $this->getEm()->flush();
                $this->getEm()->commit();
                $this->addFlash("success", "Risposta comunicazione di pagamento validata.");
                return new GestoreResponse($this->redirect($opzioni['url_indietro']));
            } else {
                throw new SfingeException("La comunicazione non è validabile");
            }
        } else {
            throw new SfingeException("La comunicazione non è validabile");
        }
    }

    public function controllaValiditaComunicazionePagamento(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        $esito = new EsitoValidazione(true);

        $esitoValidaNota = $this->validaNotaRisposta($rispostaComunicazionePagamento);
        if (!$esitoValidaNota->getEsito()) {
            $esito->setEsito(false);
            $esito->setMessaggio($esitoValidaNota->getMessaggi());
            $esito->setMessaggiSezione($esitoValidaNota->getMessaggiSezione());
        }

        return $esito;
    }

    /**
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @param array $opzioni
     * @return GestoreResponse
     * @throws SfingeException
     */
    public function invalidaComunicazionePagamento(RispostaComunicazionePagamento $rispostaComunicazionePagamento, $opzioni = array()) {
        if ($rispostaComunicazionePagamento->getStato()->uguale(StatoComunicazionePagamento::COM_PAG_VALIDATA) || $rispostaComunicazionePagamento->getStato()->uguale(StatoComunicazionePagamento::COM_PAG_FIRMATA)) {
            $this->container->get("sfinge.stati")->avanzaStato($rispostaComunicazionePagamento, StatoComunicazionePagamento::COM_PAG_INSERITA, true);
            $this->addFlash("success", "Risposta comunicazione di pagamento invalidata.");
            return new GestoreResponse($this->redirect($opzioni['url_indietro']));
        }
        throw new SfingeException("Stato non valido per effettuare l'invalidazione");
    }

    /**
     * @param DocumentoRispostaComunicazionePagamento $documentoRispostaComunicazionePagamento
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function eliminaDocumento(DocumentoRispostaComunicazionePagamento $documentoRispostaComunicazionePagamento, array $opzioni = array()) {
        try {
            $em = $this->getEm();
            $this->container->get("documenti")->cancella($documentoRispostaComunicazionePagamento->getDocumentoFile(), 0);
            $em->remove($documentoRispostaComunicazionePagamento);
            $em->flush();
            $this->addFlash("success", "Documento eliminato correttamente");
        } catch (\Exception $ex) {
            $this->addFlash('error', "Errore nell'eliminazione del documento");
        }

        return new GestoreResponse($this->redirect($opzioni["url_indietro"]));
    }

    public function generaPdf(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        return $this->generaPdfComunicazionePagamento($rispostaComunicazionePagamento,
                        "@AttuazioneControllo/RispostaComunicazionePagamento/pdfRispostaComunicazionePagamento.html.twig", false,
                        false);
    }

    /**
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @param $twig
     * @param bool $facsimile
     * @param bool $download
     * @return mixed
     * @throws SfingeException
     */
    protected function generaPdfComunicazionePagamento(RispostaComunicazionePagamento $rispostaComunicazionePagamento, $twig, $facsimile = true, $download = true) {
        if (!$rispostaComunicazionePagamento->getStato()->uguale(StatoComunicazionePagamento::COM_PAG_INSERITA)) {
            throw new SfingeException("Impossibile generare il pdf della comunicazione nello stato in cui si trova.");
        }

        $pdf = $this->container->get("pdf");

        $dati['rispostaComunicazionePagamento'] = $rispostaComunicazionePagamento;
        $dati['richiesta'] = $rispostaComunicazionePagamento->getRichiesta();
        $dati['facsimile'] = $facsimile;
        $isFsc = $this->container->get("gestore_richieste")->getGestore($rispostaComunicazionePagamento->getRichiesta()->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;

        $pdf->load($twig, $dati);

        if ($download) {
            return $pdf->download($this->getNomePdfRispostaComunicazionePagamento($rispostaComunicazionePagamento));
        } else {
            return $pdf->binaryData();
        }
    }

    /**
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @return string
     * @throws \Exception
     */
    protected function getNomePdfRispostaComunicazionePagamento(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        $date = new \DateTime();
        $data = $date->format('d-m-Y');
        return "Risposta comunicazione pagamento " . $rispostaComunicazionePagamento->getId() . " " . $data;
    }

    /**
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @param array $opzioni
     * @return GestoreResponse
     * @throws SfingeException
     */
    public function inviaRispostaComunicazionePagamento(RispostaComunicazionePagamento $rispostaComunicazionePagamento, $opzioni = array()) {
        if ($rispostaComunicazionePagamento->getStato()->uguale(StatoComunicazionePagamento::COM_PAG_FIRMATA)) {
            try {
                // Avvio la transazione
                $this->getEm()->beginTransaction();
                $rispostaComunicazionePagamento->setData(new \DateTime());
                $this->container->get("sfinge.stati")->avanzaStato($rispostaComunicazionePagamento, StatoComunicazionePagamento::COM_PAG_INVIATA_PA);
                $this->getEm()->flush();


                /* Popolamento tabelle protocollazione
                 * - richieste_protocollo
                 * - richieste_protocollo_documenti
                 */
                if ($this->container->getParameter("stacca_protocollo_al_volo")) {
                    $this->container->get("docerinitprotocollazione")->setTabProtocollazioneRispostaComunicazionePagamento($rispostaComunicazionePagamento);
                }
                $this->getEm()->flush();
                $this->getEm()->commit();
            } catch (\Exception $e) {
                // Effettuo il rollback
                $this->getEm()->rollback();
                throw new SfingeException("Errore nell'invio della risposta della comunicazione pagamento.");
            }

            return new GestoreResponse($this->redirect($opzioni['url_indietro']));
        }

        throw new SfingeException("Stato non valido per effettuare l'invio.");
    }

}
