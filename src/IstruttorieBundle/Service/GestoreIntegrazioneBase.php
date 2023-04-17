<?php
namespace IstruttorieBundle\Service;

use AnagraficheBundle\Entity\Persona;
use Doctrine\ORM\Query;
use IstruttorieBundle\Entity\DocumentoIntegrazioneIstruttoria;
use IstruttorieBundle\Entity\IntegrazioneIstruttoria;
use IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria;
use IstruttorieBundle\Form\Entity\RicercaIntegrazione;
use PHPExcel_Exception;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use SfingeBundle\Entity\PermessiProcedura;
use SfingeBundle\Entity\Procedura;
use SfingeBundle\Entity\Utente;
use SfingeBundle\Form\Entity\RicercaPermessiProcedura;
use BaseBundle\Entity\StatoIntegrazione;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Service\GestoreResponse;
use BaseBundle\Exception\SfingeException;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Component\ResponseException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Description of GestoreIntegrazioneBase
 *
 * @author aturdo
 */
class GestoreIntegrazioneBase extends \BaseBundle\Service\BaseService {

    /**
     * metodo che torna un array con in chiave la label da mostrare nel link e il link a cui andare
     * @param $integrazione
     * @return array
     */
    public function calcolaAzioniAmmesse($integrazione) {
        throw new \Exception("Deve essere implementato nella classe derivata");
    }

    public function raggruppaDocumentiIntegrazione($integrazione) {
        $gruppi = array();
        foreach ($integrazione->getTipologieDocumenti() as $tipologia_documento) {
            $proponente = $tipologia_documento->getProponente();
            $id = is_null($proponente) ? 0 : $proponente->getId();

            if (!isset($gruppi[$id])) {
                $gruppi[$id] = array("proponente" => $proponente, "docs" => array());
            }
            $gruppi[$id]["docs"][] = $tipologia_documento;
        }

        return $gruppi;
    }

    public function isBeneficiario() {
        return $this->isGranted("ROLE_UTENTE");
    }

    public function validaNotaRisposta($integrazione) {
        $esito = new EsitoValidazione(true);
        // $documenti_obbligatori = $this->getTipiDocumenti($id_richiesta, 1);

        if (is_null($integrazione) || is_null($integrazione->getTesto())) {
            $esito->setEsito(false);
            $esito->addMessaggio('Nota di risposta non fornita');
            $esito->addMessaggioSezione('Nota di risposta non fornita');
        }

        return $esito;
    }

    public function gestioneBarraAvanzamento($integrazione) {
        /** @var Procedura $richiesta */
        $procedura = $integrazione->getProcedura();

        $statoRichiesta = $integrazione->getStato()->getCodice();
        $arrayStati = array('Inserita' => true, 'Validata' => false, 'Firmata' => false, 'Inviata' => false);

        switch ($statoRichiesta) {
            case StatoIntegrazione::INT_PROTOCOLLATA:
            case StatoIntegrazione::INT_INVIATA_PA:
                $arrayStati['Inviata'] = true;
            case StatoIntegrazione::INT_FIRMATA:
                $arrayStati['Firmata'] = true;
            case StatoIntegrazione::INT_VALIDATA:
                $arrayStati['Validata'] = true;
        }

        if (!$procedura->isRichiestaFirmaDigitaleStepSuccessivi()) {
            unset($arrayStati['Firmata']);
        }

        return $arrayStati;
    }

    public function notaRispostaIntegrazione($integrazione, $opzioni) {

        $form_options["disabled"] = $this->isIntegrazioneDisabilitata($integrazione);

        $form_options = array_merge($form_options, $opzioni["form_options"]);

        $form = $this->createForm("IstruttorieBundle\Form\NotaRispostaType", $integrazione->getRisposta(), $form_options);

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

    public function isIntegrazioneDisabilitata($integrazione) {

        if (!$this->isBeneficiario()) {
            return true;
        }
        $risposta = $integrazione->getRisposta();
        $stato = $risposta->getStato()->getCodice();
        if ($stato != StatoIntegrazione::INT_INSERITA) {
            return true;
        }

        return false;
    }

    public function elencoDocumenti($integrazione, $proponente = null, $opzioni = array()) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documento_integrazione = new DocumentoIntegrazioneIstruttoria();
        $documento_file = new DocumentoFile();

        $documenti_caricati = $em->getRepository("IstruttorieBundle\Entity\DocumentoIntegrazioneIstruttoria")->findBy(array("risposta_integrazione" => $integrazione->getRisposta(), "proponente" => $proponente));

        $listaTipi = $this->getTipiDocumenti($integrazione, $proponente);

        if (count($listaTipi) > 0 && !$this->isIntegrazioneDisabilitata($integrazione)) {

            $opzioni_form["lista_tipi"] = $listaTipi;
            $opzioni_form["cf_firmatario"] = $integrazione->getRisposta()->getFirmatario() ? $integrazione->getRisposta()->getFirmatario()->getCodiceFiscale() : '';
            $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
            $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Salva'));

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {

                        $this->container->get("documenti")->carica($documento_file);

                        $documento_integrazione->setDocumentoFile($documento_file);
                        $documento_integrazione->setRispostaIntegrazione($integrazione->getRisposta());
                        $documento_integrazione->setProponente($proponente);
                        $em->persist($documento_integrazione);

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

        $documenti_raggruppati = $this->raggruppaDocumentiIntegrazione($integrazione);

        $dati = array(
            "documenti" => $documenti_caricati,
            "proponente" => $proponente,
            "form" => $form_view,
            "route_cancellazione_documento" => $opzioni["route_cancellazione_documento"],
            "url_indietro" => $opzioni["url_indietro"],
            "is_richiesta_disabilitata" => $this->isIntegrazioneDisabilitata($integrazione),
            "integrazione" => $integrazione,
            "documenti_richiesti" => is_null($proponente) ? $documenti_raggruppati[0]["docs"] : $documenti_raggruppati[$proponente->getId()]["docs"]
        );

        $response = $this->render("IstruttorieBundle:RispostaIntegrazione:elencoDocumentiRichiesta.html.twig", $dati);
        return new GestoreResponse($response);
    }

    public function getTipiDocumenti($integrazione, $proponente = null) {
        return $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaDocumentiIntegrazioneRichiesta($integrazione, $proponente);
    }

    public function getTipiDocumentiValidita($integrazione, $proponente = null) {
        return $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->validaDocumentiIntegrazioneRichiesta($integrazione, $proponente);
    }

    public function validaDocumenti($integrazione, $proponente = null) {
        $esito = new EsitoValidazione(true);
        $documenti_obbligatori = $this->getTipiDocumentiValidita($integrazione, $proponente);

        foreach ($documenti_obbligatori as $documento) {
            $esito->addMessaggio('Caricare il documento ' . $documento->getDescrizione());
        }

        if (count($documenti_obbligatori) > 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Caricare tutti gli allegati richiesti");
        }

        return $esito;
    }

    public function sceltaFirmatario($integrazione, $opzioni = array()) {

        $request = $this->getCurrentRequest();
        $form_options["disabled"] = $this->isIntegrazioneDisabilitata($integrazione);
        $form_options = array_merge($form_options, $opzioni["form_options"]);

        $form = $this->createForm("IstruttorieBundle\Form\SceltaFirmatarioType", $integrazione->getRisposta(), $form_options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->flush();

                    $this->addFlash("success", "Firmatario dell'integrazione impostato correttamente");
                    return new GestoreResponse($this->redirect($form_options["url_indietro"]));
                } catch (\Exception $e) {
                    throw new SfingeException("Firmatario non impostato");
                }
            }
        }

        $dati = array("firmatario" => $integrazione->getRisposta()->getFirmatario(), "form" => $form->createView());

        $response = $this->render("IstruttorieBundle:RispostaIntegrazione:sceltaFirmatario.html.twig", $dati);

        return new GestoreResponse($response);
    }

    public function validaIntegrazione($id_integrazione, $opzioni = array()) {

        $risposta_integrazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria")->find($id_integrazione);
        if ($risposta_integrazione->getStato()->uguale(StatoIntegrazione::INT_INSERITA)) {

            $esitoValidazione = $this->controllaValiditaIntegrazione($risposta_integrazione);
            if ($esitoValidazione->getEsito()) {
                $this->getEm()->beginTransaction();
                if (!is_null($risposta_integrazione->getDocumentoRisposta())) {
                    $this->container->get("documenti")->cancella($risposta_integrazione->getDocumentoRisposta(), 0);
                }

                //genero il nuovo pdf
                $pdf = $this->generaPdf($id_integrazione);

                //lo persisto
                $tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice(TipologiaDocumento::RICHIESTA_INTEGRAZIONE_RISPOSTA);
                $documentoRisposta = $this->container->get("documenti")->caricaDaByteArray($pdf, $this->getNomePdfIntegrazione($risposta_integrazione) . ".pdf", $tipoDocumento, false);

                //associo il documento alla richiesta
                $risposta_integrazione->setDocumentoRisposta($documentoRisposta);
                $this->getEm()->persist($risposta_integrazione);
                $this->getEm()->flush();
                $this->container->get("sfinge.stati")->avanzaStato($risposta_integrazione, StatoIntegrazione::INT_VALIDATA);
                $this->getEm()->flush();
                $this->getEm()->commit();
                $this->addFlash("success", "Risposta validata");
                return new GestoreResponse($this->redirect($opzioni['url_indietro']));
            } else {
                throw new SfingeException("L'integrazione non è validabile");
            }
        } else {
            throw new SfingeException("L'integrazione non è validabile");
        }
    }

    public function controllaValiditaIntegrazione($integrazione) {
        $esito = new EsitoValidazione(true);

        $esitoValidaNota = $this->validaNotaRisposta($integrazione);
        if (!$esitoValidaNota->getEsito()) {
            $esito->setEsito(false);
            $esito->setMessaggio($esitoValidaNota->getMessaggi());
            $esito->setMessaggiSezione($esitoValidaNota->getMessaggiSezione());
        }

        foreach ($integrazione->getDocumenti() as $documento) {
            $proponente = $documento->getProponente();
            $esitoValidaDocumentiProponente = $this->validaDocumenti($integrazione, $proponente);
            if (!$esitoValidaDocumentiProponente) {
                $esito->setEsito(false);
                $esito->setMessaggio($esitoValidaDocumentiProponente->getMessaggi());
                $esito->setMessaggiSezione($esitoValidaDocumentiProponente->getMessaggiSezione());
            }
        }

        $esitoValidaDocumentiRichiesta = $this->validaDocumenti($integrazione);
        if (!$esitoValidaDocumentiRichiesta->getEsito()) {
            $esito->setEsito(false);
            $esito->setMessaggio($esitoValidaDocumentiRichiesta->getMessaggi());
            $esito->setMessaggiSezione($esitoValidaDocumentiRichiesta->getMessaggiSezione());
        }
        return $esito;
    }

    public function invalidaIntegrazione($id_integrazione, $opzioni = array()) {

        $integrazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria")->find($id_integrazione);
        if ($integrazione->getStato()->uguale(StatoIntegrazione::INT_VALIDATA) ||
                $integrazione->getStato()->uguale(StatoIntegrazione::INT_FIRMATA)) {
            $this->container->get("sfinge.stati")->avanzaStato($integrazione, StatoIntegrazione::INT_INSERITA, true);
            $this->addFlash("success", "Risposta invalidata");
            return new GestoreResponse($this->redirect($opzioni['url_indietro']));
        }
        throw new SfingeException("Stato non valido per effettuare l'invalidazione");
    }

    public function eliminaDocumento($id_documento_integrazione, $opzioni = array()) {
        $em = $this->getEm();
        $documento = $em->getRepository("IstruttorieBundle\Entity\DocumentoIntegrazioneIstruttoria")->find($id_documento_integrazione);

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

    public function generaPdf($rispostaIntegrazioneId) {
        return $this->generaPdfIntegrazione($rispostaIntegrazioneId, "@Istruttorie/RispostaIntegrazione/pdfRispostaIntegrazione.html.twig", array(), false, false);
    }

    protected function generaPdfIntegrazione($rispostaIntegrazioneId, $twig, $datiAggiuntivi = array(), $facsimile = true, $download = true) {

        $rispostaIntegrazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria")->find($rispostaIntegrazioneId);
        if (!$rispostaIntegrazione->getStato()->uguale(StatoIntegrazione::INT_INSERITA)) {
            throw new SfingeException("Impossibile generare il pdf della richiesta nello stato in cui si trova");
        }

        $pdf = $this->container->get("pdf");

        $dati['rispostaIntegrazione'] = $rispostaIntegrazione;
        $dati['richiesta'] = $rispostaIntegrazione->getRichiesta();
        $dati['facsimile'] = $facsimile;
        $isFsc = $this->container->get("gestore_richieste")->getGestore($rispostaIntegrazione->getRichiesta()->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;

        $pdf->load($twig, $dati);

        if ($download) {
            return $pdf->download($this->getNomePdfIntegrazione($rispostaIntegrazione));
        } else {
            return $pdf->binaryData();
        }
    }

    protected function getNomePdfIntegrazione($rispostaIntegrazione) {
        $date = new \DateTime();
        $data = $date->format('d-m-Y');
        return "Risposta richiesta integrazione " . $rispostaIntegrazione->getId() . " " . $data;
    }

    public function inviaRisposta($id_risposta_integrazione, $opzioni = array()) {
        $risposta_integrazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria")->find($id_risposta_integrazione);
        if ($risposta_integrazione->getStato()->uguale(StatoIntegrazione::INT_FIRMATA)) {
            try {
                //Avvio la transazione
                $this->getEm()->beginTransaction();
                $risposta_integrazione->setData(new \DateTime());
                $this->container->get("sfinge.stati")->avanzaStato($risposta_integrazione, StatoIntegrazione::INT_INVIATA_PA);
                $this->getEm()->flush();


                /* Popolamento tabelle protocollazione
                 * - richieste_protocollo
                 * - richieste_protocollo_documenti
                 */

                if ($this->container->getParameter("stacca_protocollo_al_volo")) {
                    $this->container->get("docerinitprotocollazione")->setTabProtocollazione($risposta_integrazione->getId(), 'RISPOSTA_INTEGRAZIONE');
                }
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

    /**
     * @param Procedura|null $procedura
     * @param Utente|null $utente
     * @return mixed
     */
    public function getComunicazioniInterazioneInArrivo(Procedura $procedura = null, Utente $utente = null) {
        $datiRicerca = new RicercaIntegrazione();
        $datiRicerca->setTipo('RISPOSTE_INTEGRAZIONI');
        if ($procedura) {
            $datiRicerca->setProcedura($procedura);
        }

        if ($utente) {
            $datiRicerca->setIstruttore($utente->getUsername());
        }

        $risultato = $this->container->get("ricerca")->ricerca($datiRicerca);
        return $risultato['risultato'];
    }

    /**
     * @param $id_integrazione
     * @param array $opzioni
     * @return GestoreResponse
     * @throws SfingeException
     */
    public function impostaRispostaComeLetta($id_integrazione, $opzioni = []) {
        /** @var RispostaIntegrazioneIstruttoria $rispostaIntegrazione */
        $rispostaIntegrazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria")->find($id_integrazione);
        if ($rispostaIntegrazione->isPresaVisione() == false) {
            $this->getEm()->beginTransaction();
            $rispostaIntegrazione->setPresaVisione(true);
            $rispostaIntegrazione->setUtentePresaVisione($this->getUser()->getUsername());
            $rispostaIntegrazione->setDataPresaVisione(new \DateTime());
            $this->getEm()->flush();
            $this->getEm()->commit();
            $richiestaId = $rispostaIntegrazione->getRichiesta()->getId();
            $soggetto = ucfirst($rispostaIntegrazione->getIntegrazione()->getIstruttoria()->getRichiesta()->getSoggetto()->getDenominazione());
            $this->addFlash("success", "La risposta alla comunicazioni di integrazione della richiesta Id. $richiestaId - $soggetto è stata impostata come letta");
            return new GestoreResponse($this->redirect($opzioni['url_indietro']));
        }
        throw new SfingeException("La riposta è già stata impostata come letta");
    }

    /**
     * @param Procedura $procedura
     * @return StreamedResponse
     * @throws PHPExcel_Exception
     */
    public function esportazioneCruscottoComunicazioniIstruttoria(Procedura $procedura) {
        // Ask the service for a Excel5
        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();
        $PHPExcel_Style_NumberFormat = $phpExcelObject->getCellXfSupervisor()->getNumberFormat();

        $colonne = [];
        $lettera = 'A';
        while ($lettera !== 'AAA') {
            $colonne[] = $lettera++;
        }

        $phpExcelObject->getProperties()->setCreator("Sfinge 2104-2020")
                ->setLastModifiedBy("Sfinge 2104-2020")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Esportazione comunicazioni di integrazione");

        // Primo foglio
        $riga = 1;
        $column = -1;

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);
        $sheet = $phpExcelObject->getActiveSheet();
        $sheet->setTitle("Com. di integrazione ID " . $procedura->getId());

        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Istruttore nome');
        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Istruttore cognome');

        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Nr. comunicazioni totali inviate');
        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Nr. comunicazioni risposte');
        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Nr. comunicazioni non risposte');

        // Cerco gli utenti associati al bando
        $ricercaPermessiProcedura = new RicercaPermessiProcedura();
        $ricercaPermessiProcedura->setProcedura($procedura);
        /** @var PermessiProcedura[] $permessiProcedura */
        $permessiProcedura = $this->getEm()->getRepository('SfingeBundle:PermessiProcedura')->cercaPermessiProcedura($ricercaPermessiProcedura);

        foreach ($permessiProcedura as $permessoProcedura) {
            $riga++;
            $column = -1;

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $permessoProcedura->getUtente()->getPersona()->getNome());
            $sheet->getColumnDimension($colonne[$column])->setWidth(25);

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $permessoProcedura->getUtente()->getPersona()->getCognome());
            $sheet->getColumnDimension($colonne[$column])->setWidth(25);

            $ricercaIntegrazione = new RicercaIntegrazione();
            $ricercaIntegrazione->setProcedura($procedura);
            $ricercaIntegrazione->setIstruttore($permessoProcedura->getUtente()->getUsername());
            $comunicazioniTotali = $this->getEm()->getRepository('IstruttorieBundle:IntegrazioneIstruttoria')->getElencoIntegrazioni($ricercaIntegrazione);
            $comunicazioniTotali = count($comunicazioniTotali->getResult());

            $ricercaIntegrazione = new RicercaIntegrazione();
            $ricercaIntegrazione->setProcedura($procedura);
            $ricercaIntegrazione->setIstruttore($permessoProcedura->getUtente()->getPersona()->getCodiceFiscale());
            $comunicazioniNonLette = $this->getEm()->getRepository('IstruttorieBundle:IntegrazioneIstruttoria')->getElencoIntegrazioniConRispostaNonLetta($ricercaIntegrazione);
            $comunicazioniNonLette = count($comunicazioniNonLette->getResult());

            $comunicazioniLette = $comunicazioniTotali - $comunicazioniNonLette;

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $comunicazioniTotali, DataType::TYPE_NUMERIC);
            $sheet->getColumnDimension($colonne[$column])->setWidth(26);

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $comunicazioniLette, DataType::TYPE_NUMERIC);
            $sheet->getColumnDimension($colonne[$column])->setWidth(26);

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $comunicazioniNonLette, DataType::TYPE_NUMERIC);
            $sheet->getColumnDimension($colonne[$column])->setWidth(26);
        }

        // Secondo foglio
        $phpExcelObject->createSheet();
        $phpExcelObject->setActiveSheetIndex(1);
        $sheet = $phpExcelObject->getActiveSheet();
        $sheet->setTitle("El. com. integrazione ID " . $procedura->getId());

        $riga = 1;
        $column = -1;
        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Istruttore nome');
        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Istruttore cognome');

        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'ID Richiesta');
        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Soggetto');
        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Partita IVA');
        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Codice fiscale');

        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Testo PA');
        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Note');
        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Protocollo invio');
        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Data invio');
        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Protocollo risposta');
        $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Data risposta');

        $ricercaIntegrazione = new RicercaIntegrazione();
        $ricercaIntegrazione->setProcedura($procedura);
        /** @var Query $comunicazioniIntegrazione */
        $comunicazioniIntegrazione = $this->getEm()->getRepository('IstruttorieBundle:IntegrazioneIstruttoria')->getElencoIntegrazioni($ricercaIntegrazione);
        /** @var IntegrazioneIstruttoria[] $comunicazioniIntegrazione */
        $comunicazioniIntegrazione = $comunicazioniIntegrazione->getResult();

        foreach ($comunicazioniIntegrazione as $comunicazioneIntegrazione) {
            $riga++;
            $column = -1;

            // Avendo solamente il CF dell'istruttore devo ricavare la persona
            $persona = $this->getEm()->getRepository('AnagraficheBundle:Persona')->getPersonaByUsername($comunicazioneIntegrazione->getRichiesteProtocollo()->last()->getCreatoDa());
            /** @var Persona $persona */
            $persona = $persona[0];

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $persona->getNome());
            $sheet->getColumnDimension($colonne[$column])->setWidth(20);

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $persona->getCognome());
            $sheet->getColumnDimension($colonne[$column])->setWidth(20);

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $comunicazioneIntegrazione->getRichiesta()->getId(), DataType::TYPE_NUMERIC);
            $sheet->getColumnDimension($colonne[$column])->setWidth(10);

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $comunicazioneIntegrazione->getRichiesta()->getMandatario()->getDenominazione());
            $sheet->getColumnDimension($colonne[$column])->setWidth(60);

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $comunicazioneIntegrazione->getRichiesta()->getMandatario()->getSoggetto()->getPartitaIva());
            $sheet->getColumnDimension($colonne[$column])->setWidth(15);

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $comunicazioneIntegrazione->getRichiesta()->getMandatario()->getSoggetto()->getCodiceFiscale());
            $sheet->getColumnDimension($colonne[$column])->setWidth(22);

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $comunicazioneIntegrazione->getTestoEmail());
            $sheet->getColumnDimension($colonne[$column])->setWidth(20);

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $comunicazioneIntegrazione->getTesto());
            $sheet->getColumnDimension($colonne[$column])->setWidth(20);

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $comunicazioneIntegrazione->getProtocolloIntegrazione());
            $sheet->getColumnDimension($colonne[$column])->setWidth(18);

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $comunicazioneIntegrazione->getDataProtocolloIntegrazione()->format('Y-m-d H:i:s'))->getStyle($colonne[$column] . $riga)->getNumberFormat()->setFormatCode($PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
            $sheet->getColumnDimension($colonne[$column])->setWidth(20);

            if ($comunicazioneIntegrazione->getRisposta() && $comunicazioneIntegrazione->getRisposta()->getProtocolloRispostaIntegrazione() != '-') {
                $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $comunicazioneIntegrazione->getRisposta()->getProtocolloRispostaIntegrazione());
                $sheet->getColumnDimension($colonne[$column])->setWidth(18);
                $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $comunicazioneIntegrazione->getRisposta()->getDataProtocolloRispostaIntegrazione()->format('Y-m-d H:i:s'))->getStyle($colonne[$column] . $riga)->getNumberFormat()->setFormatCode($PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
                $sheet->getColumnDimension($colonne[$column])->setWidth(20);
            } else {
                $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, '-');
                $sheet->getColumnDimension($colonne[$column])->setWidth(18);
                $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, '-');
                $sheet->getColumnDimension($colonne[$column])->setWidth(20);
            }
        }

        // Ripristino il primo foglio come foglio attivo.
        $phpExcelObject->setActiveSheetIndex(0);

        // Create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // Create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        // Adding headers
        $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'esportazione_comunicazioni_integrazione_' . $procedura->getId() . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        return $response;
    }

    /**
     * @param Request $request
     * @param $id_integrazione_istruttoria
     * @return array|string[]
     */
    public function caricaDocumentoDropzone(Request $request, $id_integrazione_istruttoria): array
    {
        set_time_limit(0);
        $em = $this->getEm();

        $integrazione = $em->getRepository('IstruttorieBundle:IntegrazioneIstruttoria')->find($id_integrazione_istruttoria);
        $tipologiaDocumento = $em->getRepository('DocumentoBundle:TipologiaDocumento')->find($request->get('tipologiaDocumento'));

        if ($this->isIntegrazioneDisabilitata($integrazione)) {
            return ['status' => 'error', 'info' => 'La comunicazione di integrazione è disabilitata'];
        }

        if (!$tipologiaDocumento->isDropzone()) {
            return ['status' => 'error', 'info' => 'Tipologia di documento non caricabile tramite questa modalità'];
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        $fileId = $request->get('dzuuid');
        $chunkIndex = $request->get('dzchunkindex') + 1;

        // Imposto la directory di uplaod
        $targetPath = $this->container->get("documenti")->getRealPath(null, $tipologiaDocumento->getTipologia());

        $fileName = $fileId . '.' . $chunkIndex;

        if (!$file->move($targetPath, $fileName)) {
            return ['status' => 'error', 'info' => 'Errore nello spostamento dei file'];
        }

        return ['status' => 'success', null];
    }

    /**
     * @param Request $request
     * @param $id_integrazione_istruttoria
     * @return array
     */
    public function concatChunksDocumentoDropzone(Request $request, $id_integrazione_istruttoria): array
    {
        set_time_limit(0);
        $em = $this->getEm();

        $integrazione = $em->getRepository('IstruttorieBundle:IntegrazioneIstruttoria')->find($id_integrazione_istruttoria);
        $tipologiaDocumento = $em->getRepository('DocumentoBundle:TipologiaDocumento')->find($request->get('tipologiaDocumento'));

        $fileId = $request->get('dzuuid');
        $chunkTotal = $request->get('dztotalchunkcount');
        $filename = $request->get('filename');

        $prefix = $tipologiaDocumento->getPrefix();
        $path = $this->container->get("documenti")->getRealPath(null, $tipologiaDocumento->getTipologia());

        $originalFileName = preg_replace("/[^a-zA-Z0-9_. -]{1}/", "_", $filename);
        $nome = str_replace(' ', '_', $prefix . "_" . $this->container->get("documenti")->getMicroTime() . "_" . $originalFileName);
        $destinazione = $path . $nome;

        // prendo il nome file originale
        $originalFileName = $filename;

        for ($i = 1; $i <= $chunkTotal; $i++) {
            $temp_file_path = $path . $fileId . '.' . $i;
            $chunk = file_get_contents($temp_file_path);

            file_put_contents($destinazione, $chunk, FILE_APPEND | LOCK_SH);

            unlink($temp_file_path);
        }

        $md5 = md5_file($destinazione);

        // calcolo le dimensioni
        $fileDimension = filesize($destinazione);
        // prendo il mimeType
        $fileMimeType = mime_content_type($destinazione);

        $informazioniDocumento = $this->container->get("funzioni_utili")->getInformazioniDocumentoDropzone($tipologiaDocumento);

        $mimeAmmessi = explode(',', $informazioniDocumento['mime_ammessi']);
        $isMimeOk = false;
        foreach ($mimeAmmessi as $mimeAmmesso) {
            if ($fileMimeType == $mimeAmmesso) {
                $isMimeOk = true;
            }
        }

        if ($isMimeOk) {
            $documentoFile = new DocumentoFile();
            $documentoFile->setNomeOriginale($originalFileName);
            $documentoFile->setMimeType($fileMimeType);
            $documentoFile->setFileSize($fileDimension);
            $documentoFile->setMd5($md5);
            $documentoFile->setNome($nome);
            $documentoFile->setPath($path);
            $documentoFile->setTipologiaDocumento($tipologiaDocumento);

            $em->persist($documentoFile);

            $documentoIntegrazione = new DocumentoIntegrazioneIstruttoria();
            $documentoIntegrazione->setDocumentoFile($documentoFile);
            $documentoIntegrazione->setRispostaIntegrazione($integrazione->getRisposta());
            $em->persist($documentoIntegrazione);

            $em->persist($documentoIntegrazione);
            $em->flush();

            return [
                'status' => 'success',
                null,
                'uploaded' => true,
                'nomeOriginale' => $originalFileName,
            ];
        } else {
            unlink($destinazione);
            return [
                'status' => 'error',
                'msg' => 'Il formato del file non è ammesso',
            ];
        }
    }
}
