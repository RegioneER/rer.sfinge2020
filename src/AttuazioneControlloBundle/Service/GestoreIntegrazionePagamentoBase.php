<?php

namespace AttuazioneControlloBundle\Service;

use AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento;
use AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento;
use AttuazioneControlloBundle\Form\Istruttoria\DocumentoRispostaIntegrazioneType;
use Exception;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use BaseBundle\Entity\StatoIntegrazione;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Service\GestoreResponse;
use BaseBundle\Exception\SfingeException;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Component\ResponseException;
use AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaIntegrazionePagamento;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of GestoreIntegrazioneBase
 *
 * @author aturdo
 */
class GestoreIntegrazionePagamentoBase extends \BaseBundle\Service\BaseService {

	protected $container;

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
	}

	/**
	 * metodo che torna un array con in chiave la label da mostrare nel link e il link a cui andare
	 * @param $integrazione
	 * @return array
	 */
	public function calcolaAzioniAmmesse($integrazione) {
		throw new \Exception("Deve essere implementato nella classe derivata");
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
		$statoRichiesta = $integrazione->getStato()->getCodice();
		$arrayStati = array('Inserita' => true, 'Validata' => false, 'Firmata' => false, 'Inviata' => false);
        $procedura = $integrazione->getRichiesta()->getProcedura();

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

		$form = $this->createForm("AttuazioneControlloBundle\Form\NotaRispostaType", $integrazione->getRisposta(), $form_options);

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

		$response = $this->render("AttuazioneControlloBundle:RispostaIntegrazione:notaRisposta.html.twig", $dati);

		return new GestoreResponse($response);
	}

	public function isIntegrazioneDisabilitata($integrazione) {

		if (!$this->isBeneficiario()) {
			return true;
		}
		$risposta = $integrazione->getRisposta();
		if(is_null($risposta)) {
			return false;
		}
		$stato = $risposta->getStato()->getCodice();
		if ($stato != StatoIntegrazione::INT_INSERITA) {
			return true;
		}

		return false;
	}

	public function elencoDocumenti($integrazione, $opzioni = [], $proponente = null) {
		$em = $this->getEm();
		$request = $this->getCurrentRequest();

		$documento_integrazione = new DocumentoRispostaIntegrazionePagamento();
		$documento_file = new DocumentoFile();
		$documento_integrazione->setDocumentoFile($documento_file);
		$documento_integrazione->setRispostaIntegrazione($integrazione->getRisposta());

		$documenti_caricati = $em->getRepository(DocumentoRispostaIntegrazionePagamento::class)->findBy([
			"risposta_integrazione" => $integrazione->getRisposta(),
			"proponente" => $proponente
		]);

		$listaTipi = $this->getTipiDocumenti($integrazione);
		$form_view = null;

		if (count($listaTipi) > 0 && !$this->isIntegrazioneDisabilitata($integrazione)) {
			$opzioni_form["lista_tipi"] = $listaTipi;
			$opzioni_form["url_indietro"] = $opzioni["url_indietro"];
			$form = $this->createForm(DocumentoRispostaIntegrazioneType::class, $documento_integrazione, $opzioni_form);
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				try {
					$this->container->get("documenti")->carica($documento_file);
					$em->persist($documento_integrazione);
					$em->flush();
					$this->addFlash("success", "Documento caricato correttamente");
					return new GestoreResponse($this->redirect($opzioni["url_corrente"]));
				} 
				catch (ResponseException $e) {
					$this->addFlash('error', $e->getMessage());
				}
				catch(Exception $e) {
					/** @var Logger $logger */
					$logger = $this->container->get('logger');
					$logger->error($e->getTraceAsString());
					$this->addError("Errore durante il caricamento del documento");
				}
			}
			$form_view = $form->createView();
		} 

		$dati = [
			"documenti" => $documenti_caricati, 
			"proponente" => $proponente,
			"form" => $form_view,
			"route_cancellazione_documento" => $opzioni["route_cancellazione_documento"],
			"url_indietro" => $opzioni["url_indietro"],
			"is_richiesta_disabilitata" => $this->isIntegrazioneDisabilitata($integrazione),
			"documenti_richiesti" => $listaTipi,
            "integrazione" => $integrazione,
		];
		
		$response = $this->render("AttuazioneControlloBundle:RispostaIntegrazione:elencoDocumentiPagamento.html.twig", $dati);
		return new GestoreResponse($response);
	}

	public function getTipiDocumenti(IntegrazionePagamento $integrazionePagamento) {
		$documenti = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByTipologia('integrazione_pagamento');
        // Se è stata richiesta un’integrazione per un video lo aggiungo
        foreach ($integrazionePagamento->getPagamento()->getDocumentiPagamento() as $documento) {
            if ($documento->getIstruttoriaOggettoPagamento() && $documento->getIstruttoriaOggettoPagamento()->getStatoValutazione() == IstruttoriaOggettoPagamento::INTEGRAZIONE
                && $documento->getDocumentoFile()->getTipologiaDocumento()->isDropzone()) {
                $documenti[] = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(['tipologia' =>'integrazione_pagamento', 'codice' => 'INTEGRAZIONE_PAGAMENTO_VIDEO']);
            }
        }
        return $documenti;
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

		$form = $this->createForm("AttuazioneControlloBundle\Form\SceltaFirmatarioType", $integrazione->getRisposta(), $form_options);

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

		$response = $this->render("AttuazioneControlloBundle:RispostaIntegrazione:sceltaFirmatario.html.twig", $dati);

		return new GestoreResponse($response);
	}

	public function validaIntegrazione($id_integrazione, $opzioni = array()) {

		$risposta_integrazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaIntegrazionePagamento")->find($id_integrazione);
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
				$this->addFlash("success", "Integrazione validata");
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

		//Rimozione di controllo validità documento da rivedere
		/*foreach ($integrazione->getDocumenti() as $documento) {
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
		}*/
		return $esito;
	}

	public function invalidaIntegrazione($id_integrazione, $opzioni = array()) {

		$integrazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaIntegrazionePagamento")->find($id_integrazione);
		if ($integrazione->getStato()->uguale(StatoIntegrazione::INT_VALIDATA) ||
				$integrazione->getStato()->uguale(StatoIntegrazione::INT_FIRMATA)) {
			$this->container->get("sfinge.stati")->avanzaStato($integrazione, StatoIntegrazione::INT_INSERITA, true);
			$this->addFlash("success", "Integrazione invalidata");
			return new GestoreResponse($this->redirect($opzioni['url_indietro']));
		}
		throw new SfingeException("Stato non valido per effettuare l'invalidazione");
	}

	public function eliminaDocumento($id_documento_integrazione, $opzioni = array()) {
		$em = $this->getEm();
		$documento = $em->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaIntegrazionePagamento")->find($id_documento_integrazione);

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
		return $this->generaPdfIntegrazione($rispostaIntegrazioneId, "@AttuazioneControllo/RispostaIntegrazione/pdfRispostaIntegrazione.html.twig", array() , false,  false);
	}

	protected function generaPdfIntegrazione($rispostaIntegrazioneId, $twig, $datiAggiuntivi = array(), $facsimile = true, $download = true) {
		
		$rispostaIntegrazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaIntegrazionePagamento")->find($rispostaIntegrazioneId);
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
	
	public function inviaRisposta($id_integrazione, $opzioni = array()) {
		$risposta_integrazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaIntegrazionePagamento")->find($id_integrazione);
		$pagamento = $risposta_integrazione->getIntegrazione()->getPagamento();
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
					$this->container->get("docerinitprotocollazione")->setTabProtocollazioneIntegrazioneRispostaPagamento($pagamento, $risposta_integrazione);
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

    /**
     * @param Request $request
     * @param $id_integrazione_pagamento
     * @return array|string[]
     */
    public function caricaDocumentoDropzone(Request $request, $id_integrazione_pagamento): array
    {
        set_time_limit(0);
        $em = $this->getEm();

        $integrazione = $em->getRepository('AttuazioneControlloBundle:Istruttoria\IntegrazionePagamento')->find($id_integrazione_pagamento);
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
     * @param $id_integrazione_pagamento
     * @return array
     */
    public function concatChunksDocumentoDropzone(Request $request, $id_integrazione_pagamento): array
    {
        set_time_limit(0);
        $em = $this->getEm();

        $integrazione = $em->getRepository('AttuazioneControlloBundle:Istruttoria\IntegrazionePagamento')->find($id_integrazione_pagamento);
        $tipologiaDocumento = $em->getRepository('DocumentoBundle:TipologiaDocumento')->find($request->get('tipologiaDocumento'));

        $fileId = $request->get('dzuuid');
        $chunkTotal = $request->get('dztotalchunkcount');
        $filename = $request->get('filename');
        $descrzioneDocumento = $request->get('descrizioneDocumento');

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

            $documentoIntegrazione = new DocumentoRispostaIntegrazionePagamento();
            $documentoIntegrazione->setDocumentoFile($documentoFile);
            $documentoIntegrazione->setRispostaIntegrazione($integrazione->getRisposta());
            $documentoIntegrazione->setDescrizione($descrzioneDocumento);
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
