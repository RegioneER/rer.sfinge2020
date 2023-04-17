<?php

namespace AttuazioneControlloBundle\Service;

use AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaRichiestaChiarimenti;
use AttuazioneControlloBundle\Form\Istruttoria\DocumentoRispostaChiarimentiType;
use AttuazioneControlloBundle\Form\Istruttoria\DocumentoRispostaIntegrazioneType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use BaseBundle\Entity\StatoRichiestaChiarimenti;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Service\GestoreResponse;
use BaseBundle\Exception\SfingeException;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Component\ResponseException;

/**
 * @author aturdo
 */
class GestoreRichiesteChiarimentiBase extends \BaseBundle\Service\BaseService {

	/**
	 * metodo che torna un array con in chiave la label da mostrare nel link e il link a cui andare
	 * @param $richiesta_chiarimenti
	 * @return array
	 */
	public function calcolaAzioniAmmesse($risposta_richiesta_chiarimenti) {
		$csrfTokenManager = $this->container->get("security.csrf.token_manager");
		$token = $csrfTokenManager->getToken("token")->getValue();

		$vociMenu = array();

		$stato = $risposta_richiesta_chiarimenti->getStato()->getCodice();
		if ($stato == StatoRichiestaChiarimenti::RICH_CHIAR_INSERITA && $this->isBeneficiario()) {
			// firmatario
			$voceMenu["label"] = "Firmatario";
			$voceMenu["path"] = $this->generateUrl("risposta_richiesta_chiarimenti_firmatario", array("id_richiesta_chiarimenti" => $risposta_richiesta_chiarimenti->getId()));
			$vociMenu[] = $voceMenu;

			//validazione
			$esitoValidazione = $this->controllaValiditaRispostaRichiestaChiarimenti($risposta_richiesta_chiarimenti);

			if ($esitoValidazione->getEsito()) {
				$voceMenu["label"] = "Valida";
				$voceMenu["path"] = $this->generateUrl("valida_risposta_rich_chiar", array("id_risposta_rich_chiarimenti" => $risposta_richiesta_chiarimenti->getId(), "_token" => $token));
				$vociMenu[] = $voceMenu;
			}
		}

		//scarica pdf domanda
		if ($stato != StatoRichiestaChiarimenti::RICH_CHIAR_INSERITA) {
			$voceMenu["label"] = "Scarica risposta";
			$voceMenu["path"] = $this->generateUrl("scarica_risposta_rich_chiar", array("id_risposta_rich_chiar" => $risposta_richiesta_chiarimenti->getId()));
			$vociMenu[] = $voceMenu;
		}

		//carica richiesta firmata
		if ($stato == StatoRichiestaChiarimenti::RICH_CHIAR_VALIDATA && $this->isBeneficiario()) {
			$voceMenu["label"] = "Carica risposta firmata";
			$voceMenu["path"] = $this->generateUrl("carica_risposta_rich_chiar_firmata", array("id_richiesta_chiarimenti" => $risposta_richiesta_chiarimenti->getRichiestaChiarimenti()->getId()));
			$vociMenu[] = $voceMenu;
		}


		if (!($stato == StatoRichiestaChiarimenti::RICH_CHIAR_INSERITA || $stato == StatoRichiestaChiarimenti::RICH_CHIAR_VALIDATA)) {
			$voceMenu["label"] = "Scarica risposta firmata";
			$voceMenu["path"] = $this->generateUrl("scarica_risposta_rich_chiar_firmata", array("id_risposta_rich_chiar" => $risposta_richiesta_chiarimenti->getId()));
			$vociMenu[] = $voceMenu;
		}
		
		//invio alla pa
		if ($stato == StatoRichiestaChiarimenti::RICH_CHIAR_FIRMATA && $this->isBeneficiario()) {
			$voceMenu["label"] = "Invia risposta";
			$voceMenu["path"] = $this->generateUrl("invia_risposta_rich_chiar", array("id_risposta_rich_chiar" => $risposta_richiesta_chiarimenti->getId(), "_token" => $token));
			$voceMenu["attr"] = "data-confirm=\"Continuando non sarà più possibile modificare la richiesta di chiarimenti nemmeno dall'assistenza tecnica. Si intende procedere comunque?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
			$vociMenu[] = $voceMenu;
		}

		//invalidazione
		if (($stato == StatoRichiestaChiarimenti::RICH_CHIAR_VALIDATA || $stato == StatoRichiestaChiarimenti::RICH_CHIAR_FIRMATA) && $this->isBeneficiario()) {
			$voceMenu["label"] = "Invalida";
			$voceMenu["path"] = $this->generateUrl("invalida_risposta_rich_chiar", array("id_risposta_rich_chiar" => $risposta_richiesta_chiarimenti->getId(), "_token" => $token));
			$voceMenu["attr"] = "data-confirm=\"Confermi l'invalidazione della risposta?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
			$vociMenu[] = $voceMenu;
		}

		return $vociMenu;
	}

	public function controllaValiditaRispostaRichiestaChiarimenti($risposta_richiesta_chiarimenti) {
		$esito = new EsitoValidazione(true);

		$esitoValidaNota = $this->validaNotaRisposta($risposta_richiesta_chiarimenti);
		if (!$esitoValidaNota->getEsito()) {
			$esito->setEsito(false);
			$esito->setMessaggio($esitoValidaNota->getMessaggi());
			$esito->setMessaggiSezione($esitoValidaNota->getMessaggiSezione());
		}

		foreach ($risposta_richiesta_chiarimenti->getDocumenti() as $documento) {
			$proponente = $documento->getProponente();
			$esitoValidaDocumentiProponente = $this->validaDocumenti($risposta_richiesta_chiarimenti, $proponente);
			if (!$esitoValidaDocumentiProponente) {
				$esito->setEsito(false);
				$esito->setMessaggio($esitoValidaDocumentiProponente->getMessaggi());
				$esito->setMessaggiSezione($esitoValidaDocumentiProponente->getMessaggiSezione());
			}
		}

		$esitoValidaDocumentiRichiesta = $this->validaDocumenti($risposta_richiesta_chiarimenti);
		if (!$esitoValidaDocumentiRichiesta->getEsito()) {
			$esito->setEsito(false);
			$esito->setMessaggio($esitoValidaDocumentiRichiesta->getMessaggi());
			$esito->setMessaggiSezione($esitoValidaDocumentiRichiesta->getMessaggiSezione());
		}
		return $esito;
	}

	public function gestioneBarraAvanzamento($richiesta_chiarimenti) {
		$statoRichiesta = $richiesta_chiarimenti->getStato()->getCodice();
		$arrayStati = array('Inserita' => true, 'Validata' => false, 'Firmata' => false, 'Inviata' => false);

		switch ($statoRichiesta) {
			case StatoRichiestaChiarimenti::RICH_CHIAR_PROTOCOLLATA:
			case StatoRichiestaChiarimenti::RICH_CHIAR_INVIATA_PA:
				$arrayStati['Inviata'] = true;
			case StatoRichiestaChiarimenti::RICH_CHIAR_FIRMATA:
				$arrayStati['Firmata'] = true;
			case StatoRichiestaChiarimenti::RICH_CHIAR_VALIDATA:
				$arrayStati['Validata'] = true;
		}

		return $arrayStati;
	}
	
	public function isRichiestaChiarimentiDisabilitata($richiesta_chiarimenti) {

		if (!$this->isBeneficiario()) {
			return true;
		}
		$risposta = $richiesta_chiarimenti->getRisposta();
		if(is_null($risposta)) {
			return false;
		}
		$stato = $risposta->getStato()->getCodice();
		if ($stato != StatoRichiestaChiarimenti::RICH_CHIAR_INSERITA) {
			return true;
		}

		return false;
	}
	
	public function sceltaFirmatario($richiesta_chiarimenti, $opzioni = array()) {

		$request = $this->getCurrentRequest();
		$form_options["disabled"] = $this->isRichiestaChiarimentiDisabilitata($richiesta_chiarimenti);
		$form_options = array_merge($form_options, $opzioni["form_options"]);

		$form = $this->createForm("AttuazioneControlloBundle\Form\SceltaFirmatarioType", $richiesta_chiarimenti->getRisposta(), $form_options);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$em = $this->getEm();
				try {
					$em->flush();

					$this->addFlash("success", "Firmatario della richiesta di chiarimenti impostato correttamente");
					return new GestoreResponse($this->redirect($form_options["url_indietro"]));
				} catch (\Exception $e) {
					throw new SfingeException("Firmatario non impostato");
				}
			}
		}

		$dati = array("firmatario" => $richiesta_chiarimenti->getRisposta()->getFirmatario(), "form" => $form->createView());

		$response = $this->render("AttuazioneControlloBundle:RispostaRichiestaChiarimenti:sceltaFirmatario.html.twig", $dati);

		return new GestoreResponse($response);
	}

	public function isBeneficiario() {
		return $this->isGranted("ROLE_UTENTE");
	}

	public function validaNotaRisposta($risposta_richiesta_chiarimenti) {
		$esito = new EsitoValidazione(true);
		// $documenti_obbligatori = $this->getTipiDocumenti($id_richiesta, 1);

		if (is_null($risposta_richiesta_chiarimenti) || is_null($risposta_richiesta_chiarimenti->getTesto())) {
			$esito->setEsito(false);
			$esito->addMessaggio('Nota di risposta non fornita');
			$esito->addMessaggioSezione('Nota di risposta non fornita');
		}

		return $esito;
	}

	public function notaRispostaRichiestaChiarimenti($richiesta_chiarimenti, $opzioni) {

		$form_options["disabled"] = $this->isRichiestaChiarimentiDisabilitata($richiesta_chiarimenti);

		$form_options = array_merge($form_options, $opzioni["form_options"]);

		$form_options['data_class'] = 'AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti';
		$form = $this->createForm("AttuazioneControlloBundle\Form\NotaRispostaType", $richiesta_chiarimenti->getRisposta(), $form_options);

		$request = $this->getCurrentRequest();
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$em = $this->getEm();
				try {
					$em->flush();
					$this->addFlash("success", "Nota risposta alla richiesta di chiarimenti salvata correttamente");
					return new GestoreResponse($this->redirect($form_options["url_indietro"]));
				} catch (\Exception $e) {
					throw new SfingeException("Nota risposta alla richiesta di chiarimenti non salvata");
				}
			}
		}

		$dati = array("form" => $form->createView());

		$response = $this->render("AttuazioneControlloBundle:RispostaRichiestaChiarimenti:notaRisposta.html.twig", $dati);

		return new GestoreResponse($response);
	}

	public function elencoDocumenti($richiesta_chiarimenti, $opzioni = array(), $proponente = null) {
		$em = $this->getEm();
		
		$documento_richiesta_chiarimenti = new DocumentoRispostaRichiestaChiarimenti();
		$documento_file = $documento_richiesta_chiarimenti->getDocumentoFile();
		$documento_richiesta_chiarimenti->setRispostaRichiestaChiarimenti($richiesta_chiarimenti->getRisposta());
		$documenti_caricati = $em->getRepository(DocumentoRispostaRichiestaChiarimenti::class)->findBy(array("risposta_richiesta_chiarimenti" => $richiesta_chiarimenti->getRisposta(), "proponente" => $proponente));
		$listaTipi = $this->getTipiDocumenti();
		
		$form_view = null;
		if (count($listaTipi) > 0 && !$this->isRichiestaChiarimentiDisabilitata($richiesta_chiarimenti)) {
			
			$opzioni_form["lista_tipi"] = $listaTipi;
			$opzioni_form["url_indietro"] = $opzioni["url_indietro"];
			$form = $this->createForm(DocumentoRispostaChiarimentiType::class, $documento_richiesta_chiarimenti, $opzioni_form);
			$request = $this->getCurrentRequest();
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid() ) {
				try {
					$this->container->get("documenti")->carica($documento_file);
					$em->persist($documento_richiesta_chiarimenti);
					$em->flush();
					$this->addFlash("success", "Documento caricato correttamente");
					return new GestoreResponse($this->redirect($opzioni["url_corrente"]));
				} catch (ResponseException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}
			$form_view = $form->createView();
		} 
		
		$dati = array(
			"documenti" => $documenti_caricati, 
			"proponente" => $proponente,
			"form" => $form_view,
			"route_cancellazione_documento" => $opzioni["route_cancellazione_documento"],
			"url_indietro" => $opzioni["url_indietro"],
			"is_richiesta_disabilitata" => $this->isRichiestaChiarimentiDisabilitata($richiesta_chiarimenti),
			"documenti_richiesti" => $listaTipi
		);
		
		$response = $this->render("AttuazioneControlloBundle:RispostaRichiestaChiarimenti:elencoDocumentiRichiestaChiarim.html.twig", $dati);
		return new GestoreResponse($response);
	}

	public function eliminaDocumento($id_documento_rich_chiarimenti, $opzioni = array()) {
		$em = $this->getEm();
		$documento = $em->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaRichiestaChiarimenti")->find($id_documento_rich_chiarimenti);

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
	
	public function getTipiDocumenti() {
		return $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByTipologia('risposta_richiesta_chiarimenti');
	}
	
	public function validaDocumenti($risposta_richiesta_chiarimenti, $proponente = null) {
		$esito = new EsitoValidazione(true);

		if (count($risposta_richiesta_chiarimenti->getDocumenti()) == 0) {
			$esito->setEsito(false);
			$esito->addMessaggioSezione("Caricare almeno un documento");
		}

		return $esito;
	}

	public function validaRispostaRichiestaChiarimenti($id_risposta_richiesta_chiarimenti, $opzioni = array()) {

		$risposta_richiesta_chiarimenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti")->find($id_risposta_richiesta_chiarimenti);
		
		if ($risposta_richiesta_chiarimenti->getStato()->uguale(StatoRichiestaChiarimenti::RICH_CHIAR_INSERITA)) {

			$esitoValidazione = $this->controllaValiditaRispostaRichiestaChiarimenti($risposta_richiesta_chiarimenti);
			if ($esitoValidazione->getEsito()) {
				$this->getEm()->beginTransaction();
				if (!is_null($risposta_richiesta_chiarimenti->getDocumentoRisposta())) {
					$this->container->get("documenti")->cancella($risposta_richiesta_chiarimenti->getDocumentoRisposta(), 0);
				}

				//genero il nuovo pdf
				$pdf = $this->generaPdf($risposta_richiesta_chiarimenti->getId());

				//lo persisto
				$tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice(TipologiaDocumento::RICHIESTA_CHIARIMENTI);
				$documentoRisposta = $this->container->get("documenti")->caricaDaByteArray($pdf, $this->getNomePdfRispostaRichChiarimenti($risposta_richiesta_chiarimenti) . ".pdf", $tipoDocumento, false);

				//associo il documento alla richiesta
				$risposta_richiesta_chiarimenti->setDocumentoRisposta($documentoRisposta);
				$this->getEm()->persist($risposta_richiesta_chiarimenti);
				$this->getEm()->flush();
				$this->container->get("sfinge.stati")->avanzaStato($risposta_richiesta_chiarimenti, StatoRichiestaChiarimenti::RICH_CHIAR_VALIDATA);
				$this->getEm()->flush();
				$this->getEm()->commit();
				$this->addFlash("success", "Richiesta di chiarimenti validata");
				return new GestoreResponse($this->redirect($opzioni['url_indietro']));
			} else {
				throw new SfingeException("Lal richiesta di chiarimenti non è validabile");
			}
		} else {
			throw new SfingeException("Lal richiesta di chiarimenti non è validabile");
		}
	}

	public function invalidaRispostaRichChiar($id_risposta_rich_chiar, $opzioni = array()) {

		$risposta_richiesta_chiarimenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti")->find($id_risposta_rich_chiar);
		if ($risposta_richiesta_chiarimenti->getStato()->uguale(StatoRichiestaChiarimenti::RICH_CHIAR_VALIDATA) ||
				$risposta_richiesta_chiarimenti->getStato()->uguale(StatoRichiestaChiarimenti::RICH_CHIAR_FIRMATA)) {
			$this->container->get("sfinge.stati")->avanzaStato($risposta_richiesta_chiarimenti, StatoRichiestaChiarimenti::RICH_CHIAR_INSERITA, true);
			$this->addFlash("success", "Risposta alla richiesta di chiarimenti invalidata");
			return new GestoreResponse($this->redirect($opzioni['url_indietro']));
		}
		throw new SfingeException("Stato non valido per effettuare l'invalidazione");
	}

	public function generaPdf($id_risposta_rich_chiarimenti) {
		return $this->generaPdfRispostaRichChiarimenti($id_risposta_rich_chiarimenti, "@AttuazioneControllo/RichiestaChiarimenti/pdfRispostaRichiestaChiarimenti.html.twig", array() , false,  false);
	}

	protected function generaPdfRispostaRichChiarimenti($id_risposta_rich_chiarimenti, $twig, $datiAggiuntivi = array(), $facsimile = true, $download = true) {
		
		$risposta_rich_chiarimenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti")->find($id_risposta_rich_chiarimenti);
		if (!$risposta_rich_chiarimenti->getStato()->uguale(StatoRichiestaChiarimenti::RICH_CHIAR_INSERITA)) {
			throw new SfingeException("Impossibile generare il pdf della richiesta nello stato in cui si trova");
		}
		
		$pdf = $this->container->get("pdf");

		$dati['risposta_rich_chiarimenti'] = $risposta_rich_chiarimenti;
		$dati['richiesta'] = $risposta_rich_chiarimenti->getRichiesta();
		$dati['facsimile'] = $facsimile;

		$pdf->load($twig, $dati);

		if ($download) {
			return $pdf->download($this->getNomePdfRispostaRichChiarimenti($risposta_rich_chiarimenti));
		} else {
			return $pdf->binaryData();
		}
	}
	
	protected function getNomePdfRispostaRichChiarimenti($risposta_rich_chiarimenti) {
		$date = new \DateTime();
		$data = $date->format('d-m-Y');
		return "Risposta richiesta chiarimenti " . $risposta_rich_chiarimenti->getId() . " " . $data;
	}
	
	public function inviaRisposta($risposta_richiesta_chiarimenti, $opzioni = array()) {
		
		$pagamento = $risposta_richiesta_chiarimenti->getRichiestaChiarimenti()->getPagamento();
		if ($risposta_richiesta_chiarimenti->getStato()->uguale(StatoRichiestaChiarimenti::RICH_CHIAR_FIRMATA)) {
			 try {
				//Avvio la transazione
				$this->getEm()->beginTransaction();
				$risposta_richiesta_chiarimenti->setData(new \DateTime());
				$this->container->get("sfinge.stati")->avanzaStato($risposta_richiesta_chiarimenti, StatoRichiestaChiarimenti::RICH_CHIAR_INVIATA_PA);
				$this->getEm()->flush();
				

				/* Popolamento tabelle protocollazione
				 * - richieste_protocollo
				 * - richieste_protocollo_documenti
				 */

				if ($this->container->getParameter("stacca_protocollo_al_volo")) {
					$this->container->get("docerinitprotocollazione")->setTabProtocollazioneRispostaRichiestaChiarimenti($pagamento, $risposta_richiesta_chiarimenti);
				}
				$this->getEm()->flush();
				$this->getEm()->commit();
			} catch (\Exception $ex) {
				//Effettuo il rollback
				$this->getEm()->rollback();
				throw new SfingeException('Errore nell\'invio della risposta alla richiesta di chiarimenti');
			}

			return new GestoreResponse($this->redirect($opzioni['url_indietro']));
		}
		throw new SfingeException("Stato non valido per effettuare l'invio");
	}	

}
