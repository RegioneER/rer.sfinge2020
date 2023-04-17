<?php

namespace AttuazioneControlloBundle\Service;

use BaseBundle\Exception\SfingeException;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\StatoPagamento;
use DocumentoBundle\Component\ResponseException;

class GestoreGiustificativiProcedureParticolariBase extends GestoreGiustificativiBase {

	public function aggiungiGiustificativo($id_pagamento) {
		$em = $this->getEm();
		$pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

		$voci_piano_rich = $richiesta->getVociPianoCosto();
		if (count($voci_piano_rich) == 0) {
			return $this->addErrorRedirectByTipoProcedura("Definire il piano costi prima di inserire i giustificativi", "elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId()));
		}

		$giustificativo = new \AttuazioneControlloBundle\Entity\GiustificativoPagamento();
		$giustificativo->setPagamento($pagamento);
		$giustificativo->setImportoImponibileGiustificativo(0.00);
		$giustificativo->setImportoIvaGiustificativo(0.00);

		$dati = array();

		$dati["documento_caricato"] = false;
		$tipologia_documento = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array("codice" => "GIUSTIFICATIVO"));
		$documento = new \DocumentoBundle\Entity\DocumentoFile();
		$documento->setTipologiaDocumento($tipologia_documento);
		$giustificativo->setDocumentoGiustificativo($documento);

		$dati["url_indietro"] = $this->generateUrlByTipoProcedura("elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId()));

		$form = $this->createForm("AttuazioneControlloBundle\Form\GiustificativoProceduraParticolariType", $giustificativo, $dati);

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			/* if ($giustificativo->getImportoIvaGiustificativo() + $giustificativo->getImportoImponibileGiustificativo() != $giustificativo->getImportoGiustificativo()) {
			  $form->get("importo_iva_giustificativo")->addError(new \Symfony\Component\Form\FormError("La somma dell'imponibile e dell'iva deve essere uguale all'importo totale."));
			  $form->get("importo_imponibile_giustificativo")->addError(new \Symfony\Component\Form\FormError("La somma dell'imponibile e dell'iva deve essere uguale all'importo totale."));
			  $form->get("importo_giustificativo")->addError(new \Symfony\Component\Form\FormError("La somma dell'imponibile e dell'iva deve essere uguale all'importo totale."));
			  } */

			if ($form->isValid()) {
				try {
					$em->beginTransaction();
					$giustificativo->calcolaImportoRichiesto();
					$this->aggiungiVocePianoCosto($giustificativo);
					if (!is_null($documento->getFile())) {
						$this->container->get("documenti")->carica($documento);
					} else {
						$giustificativo->setDocumentoGiustificativo(null);
					}
					$em->persist($giustificativo);
					$em->flush();
					$em->commit();
					return $this->addSuccesRedirectByTipoProcedura("Il giustificativo è stato correttamente aggiunto", "dettaglio_giustificativo", $pagamento->getProcedura(), array("id_giustificativo" => $giustificativo->getId()));
				} catch (\Exception $e) {
					$em->rollback();
					$this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
				}
			}
		}

		$dati["form"] = $form->createView();

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrlByTipoProcedura("elenco_richieste", $pagamento->getProcedura()));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrlByTipoProcedura("elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrlByTipoProcedura("elenco_giustificativi", $pagamento->getProcedura(), array("id_pagamento" => $id_pagamento)));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Aggiungi giustificativo");

		return $this->render("AttuazioneControlloBundle:Giustificativi:aggiungiGiustificativoPP.html.twig", $dati);
	}

	public function modificaGiustificativo($id_giustificativo) {
		$giustificativo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);
		$pagamento = $giustificativo->getPagamento();
		$richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
		$dati = array();

		$dati["documento_caricato"] = true;
		if (is_null($giustificativo->getDocumentoGiustificativo())) {
			$dati["documento_caricato"] = false;
			$tipologia_documento = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array("codice" => "GIUSTIFICATIVO"));
			$documento = new \DocumentoBundle\Entity\DocumentoFile();
			$documento->setTipologiaDocumento($tipologia_documento);
			$giustificativo->setDocumentoGiustificativo($documento);
			$path = null;
		} else {
			$documentoFile = $giustificativo->getDocumentoGiustificativo();
			$path = $this->container->get("funzioni_utili")->encid($documentoFile->getPath() . $documentoFile->getNome());
		}

		$dati["disabled"] = $pagamento->isRichiestaDisabilitata() || !$giustificativo->isModificabileIntegrazione() || !$this->isUtenteAbilitato(); 
		$dati["url_indietro"] = $this->generateUrlByTipoProcedura("elenco_giustificativi", $pagamento->getProcedura(), array("id_pagamento" => $pagamento->getId()));

		$form = $this->createForm("AttuazioneControlloBundle\Form\GiustificativoProceduraParticolariType", $giustificativo, $dati);

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {
					if (isset($documento) && !is_null($documento->getFile())) {
						$this->container->get("documenti")->carica($documento);
					} 
					else {
						$giustificativo->setDocumentoGiustificativo(null);
					}
					$voci = $giustificativo->getVociPianoCosto();
					$voce_piano = $voci[0];
					$voce_piano->setImporto($giustificativo->getImportoGiustificativo());
					$voce_piano->setImportoApprovato($giustificativo->getImportoGiustificativo());
					$giustificativo->calcolaImportoRichiesto();
					$this->getEm()->persist($giustificativo);
					$this->getEm()->flush();
					return $this->addSuccesRedirectByTipoProcedura("Il giustificativo è stato salvato correttamente", "elenco_giustificativi", $pagamento->getProcedura(), array("id_pagamento" => $pagamento->getId()));
				} catch (\Exception $e) {
					$this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
				}
			}
		}

		$dati["form"] = $form->createView();
		$dati["giustificativo"] = $giustificativo;
		$dati["path"] = $path;

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrlByTipoProcedura("elenco_richieste", $pagamento->getProcedura()));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrlByTipoProcedura("elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrlByTipoProcedura("elenco_giustificativi", $pagamento->getProcedura(), array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Modifica giustificativo");

		return $this->render("AttuazioneControlloBundle:Giustificativi:modificaGiustificativoPP.html.twig", $dati);
	}

	public function eliminaGiustificativo($id_giustificativo) {
		$em = $this->getEm();
		$giustificativo = $em->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);

		if (is_null($giustificativo)) {
			throw new SfingeException("Giustificativo non trovato.");
		}
		$pagamento = $giustificativo->getPagamento();

		if (in_array($pagamento->getStato(), array(StatoPagamento::PAG_INVIATO_PA, StatoPagamento::PAG_PROTOCOLLATO))) {
			return $this->addErrorRedirectByTipoProcedura("L'operazione non è compatibile con lo stato del pagamento.", "elenco_giustificativi", $pagamento->getProcedura(), array("id_pagamento" => $pagamento->getId()));
		}

		try {
			$em->remove($giustificativo);
			$giustificativo->setIntegrazioneDi(null);
			$em->flush();
			return $this->addSuccesRedirectByTipoProcedura("Il giustificativo è stato correttamente eliminato", "elenco_giustificativi", $pagamento->getProcedura(), array("id_pagamento" => $pagamento->getId()));
		} catch (ResponseException $e) {
			return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId()));
		}
	}

	public function elencoGiustificativi($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrlByTipoProcedura("elenco_richieste", $pagamento->getProcedura()));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrlByTipoProcedura("elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi");

		$dati = array("pagamento" => $pagamento, "is_aggiungi_disabilitato" => false);
		$dati["is_aggiungi_disabilitato"] = $pagamento->isRichiestaDisabilitata() || !$this->isUtenteAbilitato();

		return $this->render("AttuazioneControlloBundle:Giustificativi:elencoGiustificativiPP.html.twig", $dati);
	}

	public function dettaglioGiustificativo($id_giustificativo) {
		$giustificativo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);
		$pagamento = $giustificativo->getPagamento();
		$richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

		$annualita = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($richiesta->getMandatario()->getId());

		$dati = array("giustificativo" => $giustificativo, "annualita" => $annualita, "is_modifica_disabilitata" => false);
		$dati["is_modifica_disabilitata"] = $pagamento->isRichiestaDisabilitata() || !$giustificativo->isModificabileIntegrazione() || !$this->isUtenteAbilitato();

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrlByTipoProcedura("elenco_richieste", $pagamento->getProcedura()));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrlByTipoProcedura("elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco giustificativi", $this->generateUrlByTipoProcedura("elenco_giustificativi", $pagamento->getProcedura(), array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio giustificativo");

		return $this->render("AttuazioneControlloBundle:Giustificativi:dettaglioGiustificativoParticolare.html.twig", $dati);
	}

	public function aggiungiVocePianoCosto($giustificativo) {

		$pagamento = $giustificativo->getPagamento();
		$richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
		$voci_piano_rich = $richiesta->getVociPianoCosto();
		$voce_piano_rich = $voci_piano_rich[0];
		$voce_piano = new \AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo();
		$giustificativo->addVocePianoCosto($voce_piano);

		$voce_piano->setAnnualita(1);
		$voce_piano->setVocePianoCosto($voce_piano_rich);

		$voce_piano->setImporto($giustificativo->getImportoGiustificativo());
		$voce_piano->setImportoApprovato($giustificativo->getImportoGiustificativo());
		$giustificativo->calcolaImportoRichiesto();
	}

	public function validaGiustificativo($giustificativo) {
		$esito = new EsitoValidazione(true);

		$esitoVoci = $this->validaVociSpesaGiustificativo($giustificativo);
		if ($esitoVoci->getEsito() == false) {
			$esito->setEsito(false);
			$errori = $esitoVoci->getMessaggiSezione();
			foreach ($errori as $errore) {
				$esito->addMessaggioSezione($errore);
			}
		}
		return $esito;
	}

	public function validaVociSpesaGiustificativo($giustificativo) {
		$esito = new EsitoValidazione(true);

		$voci = $giustificativo->getVociPianoCosto();
		//Verifico la presenza di voci spesa nel pagamento
		if (count($voci) == 0) {
			$esito->setEsito(false);
			$esito->addMessaggioSezione("Non sono presenti voci spesa per il giustificativo " . $giustificativo->getNumeroGiustificativo());
		}

		return $esito;
	}

	public function eliminaDocumentoGiustificativo($id_documento_giustificativo, $id_giustificativo) {
		$em = $this->getEm();
		$documento = $em->getRepository("DocumentoBundle\Entity\DocumentoFile")->find($id_documento_giustificativo);
		$giustificativo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);
		$pagamento = $giustificativo->getPagamento();

		if (!$giustificativo->isModificabileIntegrazione()) {
			return $this->addErrorRedirect("Il documento non è eliminabile perchè il giustificativo non è in integrazione", "modifica_giustificativo", array("id_giustificativo" => $id_giustificativo));
		}

		if (in_array($pagamento->getStato(), array(StatoPagamento::PAG_INVIATO_PA, StatoPagamento::PAG_PROTOCOLLATO))) {
			return $this->addErrorRedirect("L'operazione non è compatibile con lo stato del pagamento.", "modifica_giustificativo", array("id_giustificativo" => $id_giustificativo));
		}

		try {
			$em->remove($documento);
			$giustificativo->setDocumentoGiustificativo(null);
			$em->flush();
			return $this->addSuccesRedirectByTipoProcedura("Il documento è stato correttamente eliminato", "modifica_giustificativo", $pagamento->getProcedura(), array("id_giustificativo" => $id_giustificativo));
		} catch (ResponseException $e) {
			return $this->addErrorRedirectByTipoProcedura("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "modifica_giustificativo", $pagamento->getProcedura(), array("id_giustificativo" => $id_giustificativo));
		}
	}

	public function generateUrlByTipoProcedura($route, $procedura, $params = array()) {
		if ($procedura->isAssistenzaTecnica() == true) {
			$route = $route . "_at";
		}

		if ($procedura->isIngegneriaFinanziaria() == true) {
			$route = $route . "_ing_fin";
		}
		
		if ($procedura->isAcquisizioni() == true) {
			$route = $route . "_acquisizioni";
		}

		return $this->generateUrl($route, $params);
	}

	public function addSuccesRedirectByTipoProcedura($msg, $route, $procedura, $params = array()) {
		if ($procedura->isAssistenzaTecnica() == true) {
			$route = $route . "_at";
		}

		if ($procedura->isIngegneriaFinanziaria() == true) {
			$route = $route . "_ing_fin";
		}
		
		if ($procedura->isAcquisizioni() == true) {
			$route = $route . "_acquisizioni";
		}
		
		return $this->addSuccesRedirect($msg, $route, $params);
	}

	public function addErrorRedirectByTipoProcedura($msg, $route, $procedura, $params = array()) {
		if ($procedura->isAssistenzaTecnica() == true) {
			$route = $route . "_at";
		}

		if ($procedura->isIngegneriaFinanziaria() == true) {
			$route = $route . "_ing_fin";
		}
		
		if ($procedura->isAcquisizioni() == true) {
			$route = $route . "_acquisizioni";
		}
		return $this->addErrorRedirect($msg, $route, $params);
	}
    
    public function isUtenteAbilitato() {
        
		if ($this->getUser()->isAbilitatoStrumentiFinanziariScrittura()) {
			return true;
		}
		return false;
	}

}
