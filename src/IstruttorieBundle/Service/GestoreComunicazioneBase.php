<?php

namespace IstruttorieBundle\Service;

use SfingeBundle\Entity\Procedura;
use Symfony\Component\DependencyInjection\ContainerInterface;
use BaseBundle\Entity\StatoIntegrazione;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Service\GestoreResponse;
use BaseBundle\Exception\SfingeException;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use BaseBundle\Entity\StatoComunicazioneEsitoIstruttoria;
use DocumentoBundle\Component\ResponseException;

/**
 * Description of GestoreIntegrazioneBase
 *
 * @author aturdo
 */
class GestoreComunicazioneBase extends \BaseBundle\Service\BaseService {

    /**
     * metodo che torna un array con in chiave la label da mostrare nel link e il link a cui andare
     * @param $integrazione
     * @return array
     */
    public function calcolaAzioniAmmesse($comunicazione) {
        throw new \Exception("Deve essere implementato nella classe derivata");
    }

    public function isBeneficiario() {
        return $this->isGranted("ROLE_UTENTE");
    }

    public function validaNotaRisposta($comunicazione) {
        $esito = new EsitoValidazione(true);
        // $documenti_obbligatori = $this->getTipiDocumenti($id_richiesta, 1);

        if (is_null($comunicazione) || is_null($comunicazione->getTesto())) {
            $esito->setEsito(false);
            $esito->addMessaggio('Nota di risposta non fornita');
            $esito->addMessaggioSezione('Nota di risposta non fornita');
        }

        return $esito;
    }

    public function gestioneBarraAvanzamento($comunicazione) {
        $statoRichiesta = $comunicazione->getStato()->getCodice();
        $arrayStati = array('Inserita' => true, 'Validata' => false, 'Firmata' => false, 'Inviata' => false);
        /** @var Procedura $richiesta */
        $procedura = $comunicazione->getProcedura();

        switch ($statoRichiesta) {
            case StatoComunicazioneEsitoIstruttoria::ESI_PROTOCOLLATA:
            case StatoComunicazioneEsitoIstruttoria::ESI_INVIATA_PA:
                $arrayStati['Inviata'] = true;
            case StatoComunicazioneEsitoIstruttoria::ESI_FIRMATA:
                $arrayStati['Firmata'] = true;
            case StatoComunicazioneEsitoIstruttoria::ESI_VALIDATA:
                $arrayStati['Validata'] = true;
        }

        if (!$procedura->isRichiestaFirmaDigitaleStepSuccessivi()) {
            unset($arrayStati['Firmata']);
        }

        return $arrayStati;
    }

    public function notaRispostaComunicazione($comunicazione, $opzioni) {

        $form_options["disabled"] = $this->isComunicazioneDisabilitata($comunicazione);

        $form_options = array_merge($form_options, $opzioni["form_options"]);

        $form = $this->createForm("IstruttorieBundle\Form\NotaRispostaComunicazioneType", $comunicazione->getRisposta(), $form_options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->flush();
                    $this->addFlash("success", "Nota risposta integrazione salvata correttamente");
                    return new GestoreResponse($this->redirect($form_options["url_indietro"]));
                } catch (\Exception $e) {
                    throw new SfingeException("Nota risposta integrazione non salvata");
                }
            }
        }

        $dati = array("form" => $form->createView());

        $response = $this->render("IstruttorieBundle:RispostaIntegrazione:notaRisposta.html.twig", $dati);

        return new GestoreResponse($response);
    }

    public function isComunicazioneDisabilitata($comunicazione) {

        if (!$this->isBeneficiario()) {
            return true;
        }
        $risposta = $comunicazione->getRisposta();
        $stato = $risposta->getStato()->getCodice();
        if ($stato != StatoComunicazioneEsitoIstruttoria::ESI_INSERITA) {
            return true;
        }

        return false;
    }

    public function elencoDocumenti($comunicazione, $proponente = null, $opzioni = array()) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documento_comunicazione = new \IstruttorieBundle\Entity\RispostaEsitoIstruttoriaDocumento();
        $documento_file = new DocumentoFile();

        $documenti_caricati = $em->getRepository("IstruttorieBundle\Entity\RispostaEsitoIstruttoriaDocumento")->findBy(array("risposta_comunicazione" => $comunicazione->getRisposta(), "proponente" => $proponente));

        $listaTipi = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByTipologia('comunicazione_esito_risposta');

        if (count($listaTipi) > 0 && !$this->isComunicazioneDisabilitata($comunicazione)) {

            $opzioni_form["lista_tipi"] = $listaTipi;
            $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
            $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Salva'));

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {

                        $this->container->get("documenti")->carica($documento_file);

                        $documento_comunicazione->setDocumentoFile($documento_file);
                        $documento_comunicazione->setRispostaComunicazione($comunicazione->getRisposta());
                        $documento_comunicazione->setProponente($proponente);
                        $em->persist($documento_comunicazione);

                        $em->flush();
                        $this->addFlash("success", "Documento caricato correttamente");
                        return new GestoreResponse($this->redirect($opzioni["url_corrente"]));
                    } catch (ResponseException $e) {
                        $this->addFlash('error', $e->getMessage());
                    }
                }
            }
            $form_view = $form->createView();
        } else {
            $form_view = null;
        }

        $dati = array(
            "documenti" => $documenti_caricati,
            "risposta" => $comunicazione->getRisposta(),
            "proponente" => $proponente,
            "form" => $form_view,
            "route_cancellazione_documento" => $opzioni["route_cancellazione_documento"],
            "url_indietro" => $opzioni["url_indietro"],
            "disabilita_azioni" => $this->isComunicazioneDisabilitata($comunicazione),
        );

        $response = $this->render("IstruttorieBundle:RispostaComunicazione:elencoDocumentiRichiesta.html.twig", $dati);
        return new GestoreResponse($response);
    }

    public function validaDocumenti($comunicazione, $proponente = null) {
        $esito = new EsitoValidazione(true);
        return $esito;
    }

    public function sceltaFirmatario($comunicazione, $opzioni = array()) {

        $request = $this->getCurrentRequest();
        $form_options["disabled"] = $this->isComunicazioneDisabilitata($comunicazione);
        $form_options = array_merge($form_options, $opzioni["form_options"]);

        $form = $this->createForm("IstruttorieBundle\Form\SceltaFirmatarioRispostaComunicazioneType", $comunicazione->getRisposta(), $form_options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->flush();

                    $this->addFlash("success", "Firmatario della risposta impostato correttamente");
                    return new GestoreResponse($this->redirect($form_options["url_indietro"]));
                } catch (\Exception $e) {
                    throw new SfingeException("Firmatario non impostato");
                }
            }
        }

        $dati = array("firmatario" => $comunicazione->getRisposta()->getFirmatario(), "form" => $form->createView());

        $response = $this->render("IstruttorieBundle:RispostaIntegrazione:sceltaFirmatario.html.twig", $dati);

        return new GestoreResponse($response);
    }

    public function validaRispostaComunicazione($comunicazione_risposta, $opzioni = array()) {

        if ($comunicazione_risposta->getStato()->uguale(StatoComunicazioneEsitoIstruttoria::ESI_INSERITA)) {

            $esitoValidazione = $this->controllaValiditaRisposta($comunicazione_risposta);
            if ($esitoValidazione->getEsito()) {
                $this->getEm()->beginTransaction();
                if (!is_null($comunicazione_risposta->getDocumentoRisposta())) {
                    $this->container->get("documenti")->cancella($comunicazione_risposta->getDocumentoRisposta(), 0);
                }

                //genero il nuovo pdf
                $pdf = $this->generaPdf($comunicazione_risposta);

                //lo persisto
                $tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice(TipologiaDocumento::COMUNICAZIONE_ESITO_RISPOSTA);
                $documentoRisposta = $this->container->get("documenti")->caricaDaByteArray($pdf, $this->getNomePdfComunicazioneRisposta($comunicazione_risposta) . ".pdf", $tipoDocumento, false);

                //associo il documento alla richiesta
                $comunicazione_risposta->setDocumentoRisposta($documentoRisposta);
                $this->getEm()->persist($documentoRisposta);
                $this->getEm()->flush();
                $this->container->get("sfinge.stati")->avanzaStato($comunicazione_risposta, StatoComunicazioneEsitoIstruttoria::ESI_VALIDATA);
                $this->getEm()->flush();
                $this->getEm()->commit();
                $this->addFlash("success", "Integrazione validata");
                return new GestoreResponse($this->redirect($opzioni['url_indietro']));
            } else {
                throw new SfingeException("La comunicazione non è validabile");
            }
        } else {
            throw new SfingeException("La comunicazione non è validabile");
        }
    }

    public function controllaValiditaRisposta($comunicazione) {
        $esito = new EsitoValidazione(true);

        $esitoValidaNota = $this->validaNotaRisposta($comunicazione);
        $esitoValidaDocumenti = $this->validaDocumenti($comunicazione);
        if (!$esitoValidaNota->getEsito() || !$esitoValidaDocumenti->getEsito()) {
            $esito->setEsito(false);
            $esito->setMessaggio($esitoValidaNota->getMessaggi());
            $esito->setMessaggiSezione($esitoValidaNota->getMessaggiSezione());
        }

        return $esito;
    }

    public function invalidaRispostaComunicazione($comunicazione_risposta, $opzioni = array()) {

        if ($comunicazione_risposta->getStato()->uguale(StatoComunicazioneEsitoIstruttoria::ESI_VALIDATA) ||
                $comunicazione_risposta->getStato()->uguale(StatoComunicazioneEsitoIstruttoria::ESI_FIRMATA)) {
            $this->container->get("sfinge.stati")->avanzaStato($comunicazione_risposta, StatoComunicazioneEsitoIstruttoria::ESI_INSERITA, true);
            $this->addFlash("success", "Comunicazione invalidata");
            return new GestoreResponse($this->redirect($opzioni['url_indietro']));
        }
        throw new SfingeException("Stato non valido per effettuare l'invalidazione");
    }

    public function eliminaDocumento($id_documento_integrazione, $opzioni = array()) {
        $em = $this->getEm();
        $documento = $em->getRepository("IstruttorieBundle\Entity\RispostaEsitoIstruttoriaDocumento")->find($id_documento_integrazione);

        try {
            $this->container->get("documenti")->cancella($documento->getDocumentoFile(), 0);
            $em->remove($documento);
            $em->flush();
            $this->addFlash("success", "Documento eliminato correttamente");
        } catch (ResponseException $e) {
            $this->addFlash('error', "Errore nell'eliminazione del documento");
        }

        return new GestoreResponse($this->redirect($opzioni["url_indietro"]));
    }

    public function generaPdf($comunicazione_risposta) {
        return $this->generaPdfComunicazioneRisposta($comunicazione_risposta, "@Istruttorie/RispostaComunicazione/pdfRispostaComunicazione.html.twig", array(), false, false);
    }

    protected function generaPdfComunicazioneRisposta($comunicazione_risposta, $twig, $datiAggiuntivi = array(), $facsimile = true, $download = true) {

        if (!$comunicazione_risposta->getStato()->uguale(StatoComunicazioneEsitoIstruttoria::ESI_INSERITA)) {
            throw new SfingeException("Impossibile generare il pdf della richiesta nello stato in cui si trova");
        }

        $pdf = $this->container->get("pdf");

        $dati['comunicazione_risposta'] = $comunicazione_risposta;
        $dati['richiesta'] = $comunicazione_risposta->getRichiesta();
        $dati['facsimile'] = $facsimile;
        $isFsc = $this->container->get("gestore_richieste")->getGestore($comunicazione_risposta->getRichiesta()->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;

        $pdf->load($twig, $dati);

        if ($download) {
            return $pdf->download($this->getNomePdfComunicazioneRisposta($comunicazione_risposta));
        } else {
            return $pdf->binaryData();
        }
    }

    protected function getNomePdfComunicazioneRisposta($comunicazione_risposta) {
        $date = new \DateTime();
        $data = $date->format('d-m-Y');
        return "Risposta comunicazione esito " . $comunicazione_risposta->getId() . " " . $data;
    }

    public function inviaRisposta($comunicazione_risposta, $opzioni = array()) {
        if ($comunicazione_risposta->getStato()->uguale(StatoComunicazioneEsitoIstruttoria::ESI_FIRMATA)) {
            try {
                //Avvio la transazione
                $this->getEm()->beginTransaction();
                $comunicazione_risposta->setData(new \DateTime());
                $this->container->get("sfinge.stati")->avanzaStato($comunicazione_risposta, StatoComunicazioneEsitoIstruttoria::ESI_INVIATA_PA);
                $this->getEm()->flush();


                /* Popolamento tabelle protocollazione
                 * - richieste_protocollo
                 * - richieste_protocollo_documenti
                 */

                if ($this->container->getParameter("stacca_protocollo_al_volo")) {
                    $this->container->get("docerinitprotocollazione")->setTabProtocollazioneRispostaEsitoRichiesta($comunicazione_risposta);
                }
                $this->getEm()->flush();
                $this->getEm()->commit();
            } catch (\Exception $ex) {
                //Effettuo il rollback
                $this->getEm()->rollback();
                throw new SfingeException('Errore nell\'invio della risposta dell\'integrazione');
            }

            return new GestoreResponse($this->redirect($opzioni['url_indietro']));
        }
        throw new SfingeException("Stato non valido per effettuare l'invio");
    }

}
