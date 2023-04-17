<?php

namespace AttuazioneControlloBundle\Service;

use BaseBundle\Exception\SfingeException;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\StatoVariazione;
use DocumentoBundle\Entity\TipologiaDocumento;
use RichiesteBundle\Service\GestoreResponse;

class GestoreRelazioneTecnicaBase extends AGestoreRelazioneTecnica {

	public function cercaAutoreRelazione($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle:Pagamento")->find($id_pagamento);

		if (is_null($pagamento)) {
			throw new SfingeException("Pagamento non trovato");
		}

		$isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$ricercaPersone = new \AttuazioneControlloBundle\Form\Entity\RicercaPersonaPagamento();
		$ricercaPersone->setConsentiRicercaVuota(false);
		$risultato = $this->container->get("ricerca")->ricerca($ricercaPersone);

		$dati = array('persone' => $risultato["risultato"], "form" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"], "pagamento" => $pagamento,
			"id_pagamento" => $id_pagamento, "menu" => "autore");

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Ricerca autore relazione");

		$response = $this->render("AttuazioneControlloBundle:RelazioneTecnica:cercaReferente.html.twig", $dati);

		return new GestoreResponse($response, "AttuazioneControlloBundle:RelazioneTecnica:cercaReferente.html.twig", $dati);
	}

	public function elencoAutoriRelazione($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

		$dati = array("pagamento" => $pagamento, "menu" => "autore");

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Autore relazione");

		return $this->render("AttuazioneControlloBundle:RelazioneTecnica:elencoAutori.html.twig", $dati);
	}

	public function inserisciReferenteRelazione($id_pagamento, $id_persona, $opzioni = array(), $twig = null) {

		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle:Pagamento")->find($id_pagamento);
		if (is_null($pagamento)) {
			throw new SfingeException("Pagamento non trovato");
		}
		$richiesta = $pagamento->getRichiesta();
		$isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$persona = $this->getEm()->getRepository("AnagraficheBundle:Persona")->find($id_persona);
		$request = $this->getCurrentRequest();
		$referente = new \AttuazioneControlloBundle\Entity\ReferentePagamento();
		$opzioni["tipi_referenza"] = $this->getTipiReferenzaAmmessi($pagamento);
		$opzioni["url_indietro"] = $this->generateUrl("cerca_autore_relazione", array("id_pagamento" => $id_pagamento));
		$type = "AttuazioneControlloBundle\Form\Bando_7\ReferenteRelazioneType";

		if (is_null($twig)) {
			$twig = "AttuazioneControlloBundle:RelazioneTecnica:inserisciReferenteRelazione.html.twig";
		}

		if (array_key_exists('form_type', $opzioni)) {
			$type = $opzioni["form_type"];
			unset($opzioni["form_type"]);
		}

		$form = $this->createForm($type, $referente, $opzioni);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$em = $this->getEm();
				try {
					$em->beginTransaction();
					$referente->setPersona($persona);
					$referente->setPagamento($pagamento);
					$em->persist($referente);

					$em->flush();
					$em->commit();
					$msg = "Dati salvati con successo";
					return new GestoreResponse($this->addSuccesRedirect($msg, "elenco_referenti_relazione", array("id_pagamento" => $pagamento->getId())));
				} catch (\Exception $e) {
					$em->rollback();
					throw new SfingeException("Referente non aggiunto");
				}
			}
		}

		$dati = array("id_richiesta" => $richiesta->getId(), "id_pagamento" => $pagamento->getId(), "persona" => $persona, "form" => $form->createView(), "menu" => "autore");

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Inserisci autore relazione");

		$response = $this->render($twig, $dati);

		return new GestoreResponse($response, $twig, $dati);
	}

	public function rimuoviAutoreRelazione($id_pagamento, $id_referente) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle:Pagamento")->find($id_pagamento);

		if ($pagamento->isRichiestaDisabilitata()) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$referente = $this->getEm()->getRepository("AttuazioneControlloBundle:ReferentePagamento")->find($id_referente);
		if (is_null($referente)) {
			throw new SfingeException("Il referente indicato non esiste");
		}

		$this->getEm()->remove($referente);
		$this->getEm()->flush();
		return $this->addSuccesRedirect("Il referente è stato rimosso correttamente", "elenco_referenti_relazione", array("id_pagamento" => $id_pagamento));
	}

	public function elencoObiettiviRealizzativi($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

		$dati = array("pagamento" => $pagamento, "richiesta" => $pagamento->getRichiesta(), "menu" => "obiettivi");

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Obiettivi realizzativi");

		return $this->render("AttuazioneControlloBundle:RelazioneTecnica:elencoObiettiviRealizzativi.html.twig", $dati);
	}
	
	public function calcolaLunghezzaStringa($stringa){
		$chars   = array('\r');
		$stringa = str_replace($chars, '', $stringa);
		if(function_exists("mb_strlen")){
			$lunghezza = mb_strlen($stringa, "utf-8");
		} else {
			$lunghezza = strlen($stringa);
		}
		return $lunghezza;
	}

	public function compilaDettaglioOr($id_obiettivo, $id_pagamento) {

		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle:Pagamento")->find($id_pagamento);
		$obiettivo = $this->getEm()->getRepository("AttuazioneControlloBundle:ObiettivoRealizzativoPagamento")->find($id_obiettivo);

		if (is_null($pagamento)) {
			throw new SfingeException("Pagamento non trovato");
		}

		if (is_null($obiettivo)) {
			throw new SfingeException("Obiettivo non trovato");
		}

		$richiesta = $pagamento->getRichiesta();
		$isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();

		/*if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}*/

		$request = $this->getCurrentRequest();

		$opzioni["url_indietro"] = $this->generateUrl("elenco_obiettivi_realizzativi", array("id_pagamento" => $id_pagamento));
        $opzioni["disabled"] = $isRichiestaDisabilitata;
        
		$type = "AttuazioneControlloBundle\Form\Bando_7\ObiettivoRealizzativoType";

		$form = $this->createForm($type, $obiettivo, $opzioni);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$em = $this->getEm();
				try {
					$em->beginTransaction();
					$em->persist($obiettivo);
					$em->flush();
					$em->commit();
					$msg = "Dati salvati con successo";
					return new GestoreResponse($this->addSuccesRedirect($msg, "elenco_obiettivi_realizzativi", array("id_pagamento" => $pagamento->getId())));
				} catch (\Exception $e) {
					$em->rollback();
					throw new SfingeException("Referente non aggiunto");
				}
			}
		}

		$dati = array("id_richiesta" => $richiesta->getId(), "pagamento" => $pagamento, "obiettivo" => $obiettivo, "form" => $form->createView(), "menu" => "obiettivi");

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco obiettivi", $this->generateUrl("elenco_obiettivi_realizzativi", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Obiettivo realizzativo");

		$response = $this->render('AttuazioneControlloBundle:RelazioneTecnica:obiettivoRealizzativo.html.twig', $dati);

		return new GestoreResponse($response, 'AttuazioneControlloBundle:RelazioneTecnica:obiettivoRealizzativo.html.twig', $dati);
	}

	public function elencoPersonale($id_pagamento, $tipoPersonale) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

		$personale = $this->getPersonaleByTipo($pagamento, $tipoPersonale);
		$dati = array(
			"pagamento" => $pagamento,
			"richiesta" => $pagamento->getRichiesta(),
			"personale" => $personale,
			"tipo" => $tipoPersonale,
			"menu" => "personale");

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Personale");

		$twig = "AttuazioneControlloBundle:RelazioneTecnica:elencoPersonaleVoci.html.twig";

		return $this->render($twig, $dati);
	}

	public function elencoCollaborazioniEsterne($id_pagamento, $tipoCollaborazione) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

		$collaborazioni = $this->getCollaborazioniByTipo($pagamento, $tipoCollaborazione);
		$dati = array(
			"pagamento" => $pagamento,
			"richiesta" => $pagamento->getRichiesta(),
			"collaborazioni" => $collaborazioni,
			"tipo" => $tipoCollaborazione,
			"menu" => "collaborazioni_esterne");

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco collaborazioni");

		$twig = "AttuazioneControlloBundle:RelazioneTecnica:elencoCollaborazioni.html.twig";

		return $this->render($twig, $dati);
	}

	public function compilaCollaborazioniEsterne($id_collaborazione, $id_pagamento, $tipoCollaborazione) {

		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle:Pagamento")->find($id_pagamento);
		$collaborazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Bando_7\EstensioneGiustificativoBando_7")->find($id_collaborazione);

		if (is_null($pagamento)) {
			throw new SfingeException("Pagamento non trovato");
		}

		if (is_null($collaborazione)) {
			throw new SfingeException("Obiettivo non trovato");
		}

		$richiesta = $pagamento->getRichiesta();
		$isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();

		/*if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}*/

		$request = $this->getCurrentRequest();

		if ($tipoCollaborazione == 'CONSULENZE') {
			$opzioni["url_indietro"] = $this->generateUrl("elenco_collaborazioni_consulenze", array("id_pagamento" => $pagamento->getId()));
		} else {
			$opzioni["url_indietro"] = $this->generateUrl("elenco_collaborazioni_laboratori", array("id_pagamento" => $pagamento->getId()));
		}
		$opzioni["alta_tecnologia"] = $collaborazione->getContratto()->getTipologiaFornitore()->getCodice() == 'RI' ? true : false;
        $opzioni["disabled"] = $isRichiestaDisabilitata;
        
		$type = "AttuazioneControlloBundle\Form\Bando_7\CollaborazioneEsternaType";
		
		$contratto = $collaborazione->getGiustificativoPagamento()->getContratto();
		if($contratto->isReteAltaTecnologia()){
			$collaborazione->setAltaTecnologia('Appartenenti alla Rete Alta Tecnologia dell\'Emilia-Romagna');
		}
		
		$collaborazione->setImportoContrattoComplessivo($contratto->getImportoContrattoComplessivo());
		$form = $this->createForm($type, $collaborazione, $opzioni);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$em = $this->getEm();
				try {
					$em->beginTransaction();
					$em->persist($collaborazione);
					$em->flush();
					$em->commit();
					$msg = "Dati salvati con successo";
					if ($tipoCollaborazione == 'CONSULENZE') {
						return new GestoreResponse($this->addSuccesRedirect($msg, "elenco_collaborazioni_consulenze", array("id_pagamento" => $pagamento->getId())));
					} else {
						return new GestoreResponse($this->addSuccesRedirect($msg, "elenco_collaborazioni_laboratori", array("id_pagamento" => $pagamento->getId())));
					}
				} catch (\Exception $e) {
					$em->rollback();
					throw new SfingeException("Referente non aggiunto");
				}
			}
		}

		$dati = array(
			"id_richiesta" => $richiesta->getId(),
			"pagamento" => $pagamento,
			"collaborazione" => $collaborazione,
			"tipo" => $tipoCollaborazione,
			"form" => $form->createView(),
			"menu" => "collaborazioni_esterne");

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));

		if ($tipoCollaborazione == 'CONSULENZE') {
			$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco collaborazioni", $this->generateUrl("elenco_collaborazioni_consulenze", array("id_pagamento" => $pagamento->getId())));
		} else {
			$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco collaborazioni", $this->generateUrl("elenco_collaborazioni_laboratori", array("id_pagamento" => $pagamento->getId())));
		}

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Collaborazione");

		$response = $this->render('AttuazioneControlloBundle:RelazioneTecnica:collaborazioneEsterna.html.twig', $dati);

		return new GestoreResponse($response, 'AttuazioneControlloBundle:RelazioneTecnica:collaborazioneEsterna.html.twig', $dati);
	}

	public function elencoAttrezzatureStrumentazioni($id_pagamento, $tipoContratto) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

		$attrezzature = $this->getAttrezzatureStrumentazioniByTipo($pagamento, $tipoContratto);
		$dati = array(
			"pagamento" => $pagamento,
			"richiesta" => $pagamento->getRichiesta(),
			"attrezzature" => $attrezzature,
			"tipo" => $tipoContratto,
			"menu" => "elenco_attrezzature");

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco attrezzature strumentazioni");

		$twig = "AttuazioneControlloBundle:RelazioneTecnica:elencoAttrStrum.html.twig";

		return $this->render($twig, $dati);
	}

	public function compilaAttrezzatureStrumentazioni($id_attrezzatura, $id_pagamento, $tipoContratto) {

		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle:Pagamento")->find($id_pagamento);
		$attrezzatura = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Bando_7\EstensioneGiustificativoBando_7")->find($id_attrezzatura);

		if (is_null($pagamento)) {
			throw new SfingeException("Pagamento non trovato");
		}

		if (is_null($attrezzatura)) {
			throw new SfingeException("Elemento non trovato");
		}

		$richiesta = $pagamento->getRichiesta();
		$isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();

		/*if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}*/

		$request = $this->getCurrentRequest();
		$em = $this->getEm();
		$tipologiaGiustificativo = $attrezzatura->getGiustificativoPagamento()->getTipologiaGiustificativo()->getCodice();
		$opzioni["percentuale"] = $tipologiaGiustificativo == '4A' ? true : false;
		$opzioni["url_indietro"] = $this->generateUrl("elenco_attrezzature", array("id_pagamento" => $pagamento->getId()));
		$opzioni["em"] = $em;
		$opzioni["richiesta"] = $richiesta;
        $opzioni["disabled"] = $isRichiestaDisabilitata;

		$type = "AttuazioneControlloBundle\Form\Bando_7\AttrezzaturaStrumentazioneType";

		$descrizione = $attrezzatura->getGiustificativoPagamento()->getDescrizioneGiustificativo();
		
		$attrezzatura->setDescrizioneAttrezzatura($descrizione);
		
		$form = $this->createForm($type, $attrezzatura, $opzioni);
		
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			
			if ($form->isValid()) {
				try {
					$em->persist($attrezzatura);
					$em->flush();
					$msg = "Dati salvati con successo";
					return new GestoreResponse($this->addSuccesRedirect($msg, "elenco_attrezzature", array("id_pagamento" => $pagamento->getId())));
				} catch (\Exception $e) {
					throw new SfingeException("Referente non aggiunto");
				}
			}
		}

		$dati = array(
			"id_richiesta" => $richiesta->getId(),
			"pagamento" => $pagamento,
			"attrezzatura" => $attrezzatura,
			"tipo" => $tipoContratto,
			"form" => $form->createView(),
			"menu" => "elenco_attrezzature");

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco attrezzature strumentazioni", $this->generateUrl("elenco_collaborazioni_laboratori", array("id_pagamento" => $pagamento->getId())));

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Attrezzatura e strumentazione");

		$response = $this->render('AttuazioneControlloBundle:RelazioneTecnica:attrezzaturaStrumentazione.html.twig', $dati);

		return new GestoreResponse($response, 'AttuazioneControlloBundle:RelazioneTecnica:attrezzaturaStrumentazione.html.twig', $dati);
	}

	public function elencoPrototipiDimostratoriPilota($id_pagamento) {
		$em = $this->getEm();
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

		$prototipi = $this->getPrototipi($pagamento);

        $isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();
		$opzioni["url_indietro"] = $this->generateUrl("elenco_prototipi", array("id_pagamento" => $pagamento->getId()));
        $opzioni["disabled"] = $isRichiestaDisabilitata;
                
		if (is_null($pagamento->getEstensione())) {
			$estensione = new \AttuazioneControlloBundle\Entity\Bando_7\EstensionePagamentoBando_7();
			$pagamento->setEstensione($estensione);
		} else {
			$estensione = $pagamento->getEstensione();
		}
        
		
		$type = "AttuazioneControlloBundle\Form\Bando_7\DescrizionePrototipiType";

		$form = $this->createForm($type, $estensione, $opzioni);

		$dati = array(
			"pagamento" => $pagamento,
			"richiesta" => $pagamento->getRichiesta(),
			"prototipi" => $prototipi,
			"form" => $form->createView(),
			"menu" => "elenco_prototipi");

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {
					$em->flush();
					$msg = "Dati salvati con successo";
					return new GestoreResponse($this->addSuccesRedirect($msg, "elenco_prototipi", array("id_pagamento" => $pagamento->getId())));
				} catch (\Exception $e) {
					$em->rollback();
					throw new SfingeException("Errore nel salvataggio");
				}
			}
		}

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco prototipi");

		$twig = "AttuazioneControlloBundle:RelazioneTecnica:elencoPrototipi.html.twig";
		$response = $this->render($twig, $dati);
		return new GestoreResponse($response, $twig, $dati);
	}

	public function compilaPrototipo($id_prototipo, $id_pagamento) {

		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle:Pagamento")->find($id_pagamento);
		$prototipo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Bando_7\EstensioneGiustificativoBando_7")->find($id_prototipo);

		if (is_null($pagamento)) {
			throw new SfingeException("Pagamento non trovato");
		}

		if (is_null($prototipo)) {
			throw new SfingeException("Elemento non trovato");
		}

		$richiesta = $pagamento->getRichiesta();
		$isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();

		/*if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}*/

		$request = $this->getCurrentRequest();
		$em = $this->getEm();
		$opzioni["url_indietro"] = $this->generateUrl("elenco_prototipi", array("id_pagamento" => $pagamento->getId()));
		$opzioni["em"] = $em;
		$opzioni["richiesta"] = $richiesta;
        $opzioni["disabled"] = $isRichiestaDisabilitata;

		$type = "AttuazioneControlloBundle\Form\Bando_7\PrototipoType";

		$form = $this->createForm($type, $prototipo, $opzioni);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {
					$em->beginTransaction();
					$em->persist($prototipo);
					$em->flush();
					$em->commit();
					$msg = "Dati salvati con successo";
					return new GestoreResponse($this->addSuccesRedirect($msg, "elenco_prototipi", array("id_pagamento" => $pagamento->getId())));
				} catch (\Exception $e) {
					$em->rollback();
					throw new SfingeException("Referente non aggiunto");
				}
			}
		}

		$dati = array(
			"id_richiesta" => $richiesta->getId(),
			"pagamento" => $pagamento,
			"prototipo" => $prototipo,
			"form" => $form->createView(),
			"menu" => "elenco_prototipi");

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco prototipi", $this->generateUrl("elenco_prototipi", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Prototipi, dimostratori, impianti pilota");

		$response = $this->render('AttuazioneControlloBundle:RelazioneTecnica:prototipo.html.twig', $dati);

		return new GestoreResponse($response, 'AttuazioneControlloBundle:RelazioneTecnica:prototipo.html.twig', $dati);
	}

	public function elencoBrevetti($id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

		$brevetti = $this->getBrevetti($pagamento);
		$dati = array(
			"pagamento" => $pagamento,
			"richiesta" => $pagamento->getRichiesta(),
			"brevetti" => $brevetti,
			"menu" => "brevetti");

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Brevetti");

		$twig = "AttuazioneControlloBundle:RelazioneTecnica:elencoBrevetti.html.twig";

		return $this->render($twig, $dati);
	}

	public function compilaBrevetto($id_brevetto, $id_pagamento) {

		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle:Pagamento")->find($id_pagamento);
		$brevetto = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Contratto")->find($id_brevetto);

		if (is_null($pagamento)) {
			throw new SfingeException("Pagamento non trovato");
		}

		if (is_null($brevetto)) {
			throw new SfingeException("Elemento non trovato");
		}

		$isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();

		/*if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}*/

		$request = $this->getCurrentRequest();
		$em = $this->getEm();
		$opzioni["url_indietro"] = $this->generateUrl("elenco_brevetti", array("id_pagamento" => $pagamento->getId()));
        $opzioni["disabled"] = $isRichiestaDisabilitata;
        
		$type = "AttuazioneControlloBundle\Form\Bando_7\BrevettoType";

		$form = $this->createForm($type, $brevetto, $opzioni);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {
					$em->beginTransaction();
					$em->persist($brevetto);
					$em->flush();
					$em->commit();
					$msg = "Dati salvati con successo";
					return new GestoreResponse($this->addSuccesRedirect($msg, "elenco_brevetti", array("id_pagamento" => $pagamento->getId())));
				} catch (\Exception $e) {
					$em->rollback();
					throw new SfingeException("Referente non aggiunto");
				}
			}
		}

		$dati = array(
			"pagamento" => $pagamento,
			"brevetto" => $brevetto,
			"form" => $form->createView(),
			"menu" => "brevetti");

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco brevetti", $this->generateUrl("elenco_brevetti", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Brevetto");

		$response = $this->render('AttuazioneControlloBundle:RelazioneTecnica:brevetto.html.twig', $dati);

		return new GestoreResponse($response, 'AttuazioneControlloBundle:RelazioneTecnica:brevetto.html.twig', $dati);
	}

	public function altreInformazioni($id_pagamento) {
		$em = $this->getEm();
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

		if (is_null($pagamento->getEstensione())) {
			$estensione = new \AttuazioneControlloBundle\Entity\Bando_7\EstensionePagamentoBando_7();
			$pagamento->setEstensione($estensione);
		} else {
			$estensione = $pagamento->getEstensione();
		}
        
        $isRichiestaDisabilitata = $pagamento->isRichiestaDisabilitata();
            

		$type = "AttuazioneControlloBundle\Form\Bando_7\AltreInformazioniType";
		$opzioni = array();
        $opzioni["disabled"] = $isRichiestaDisabilitata;    
		$form = $this->createForm($type, $estensione, $opzioni);

		$dati = array(
			"pagamento" => $pagamento,
			"richiesta" => $pagamento->getRichiesta(),
			"form" => $form->createView(),
			"menu" => "altre_informazioni");

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {
					$em->flush();
					$msg = "Dati salvati con successo";
					return new GestoreResponse($this->addSuccesRedirect($msg, "altre_informazioni", array("id_pagamento" => $pagamento->getId())));
				} catch (\Exception $e) {
					$em->rollback();
					throw new SfingeException("Errore nel salvataggio");
				}
			}
		}

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrl("elenco_pagamenti", array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio pagamento", $this->generateUrl("dettaglio_pagamento", array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Altre informazioni");

		$twig = "AttuazioneControlloBundle:RelazioneTecnica:altreInformazioni.html.twig";
		$response = $this->render($twig, $dati);
		return new GestoreResponse($response, $twig, $dati);
	}

	public function getTipiReferenzaAmmessi($pagamento) {
		$tipiAmmessi = array();
		foreach ($pagamento->getRichiesta()->getProcedura()->getTipiReferenza() as $tipoReferenzaProcedura) {
			$tipiAmmessi[] = $tipoReferenzaProcedura->getTipoReferenza();
		}
		return $tipiAmmessi;
	}

	public function getPersonaleByTipo($pagamento, $tipoPersonale) {
		$array_personale = array();
		foreach ($pagamento->getGiustificativi() as $giustificativo) {
			if ($tipoPersonale == 'RICERCATORI_VOCE_1') {
				if ($giustificativo->getTipologiaGiustificativo()->getCodice() == '1' && !is_null($giustificativo->getEstensione())) {
					$array_personale[] = $giustificativo->getEstensione();
				}
			}
			if ($tipoPersonale == 'PERSONALE_VOCE_2') {
				if ($giustificativo->getTipologiaGiustificativo()->getCodice() == '2' && !is_null($giustificativo->getEstensione())) {
					$array_personale[] = $giustificativo->getEstensione();
				}
			}
			if ($tipoPersonale == 'PERSONALE_VOCE_3') {
				if ($giustificativo->getTipologiaGiustificativo()->getCodice() == '3' && !is_null($giustificativo->getEstensione())) {
					$array_personale[] = $giustificativo->getEstensione();
				}
			}
		}

		return $array_personale;
	}

	public function getCollaborazioniByTipo($pagamento, $tipoCollaborazione) {
		$array_collaborazioni = array();
		$array_ver_laboratori = array('RI', 'UN', 'LAB');
		
		foreach ($pagamento->getGiustificativi() as $giustificativo) {
			$contratto = $giustificativo->getContratto();
			if(is_null($contratto)){
				continue;
			}
			
			$estensioneGiustificativo7 = $giustificativo->getEstensione();
			if(is_null($estensioneGiustificativo7)){
				continue;				
			}
			
			if(is_null($contratto->getTipologiaFornitore())) continue;
			
			$tipologia = $contratto->getTipologiaFornitore()->getCodice();
			if ($tipoCollaborazione == 'LABORATORI' && in_array($tipologia, $array_ver_laboratori)) {
				$array_collaborazioni[] = $estensioneGiustificativo7;
			}
			if ($tipoCollaborazione == 'CONSULENZE' && $tipologia == 'CO') {
				$array_collaborazioni[] = $estensioneGiustificativo7;
			}			
		}

		return $array_collaborazioni;
	}

	public function getBrevetti($pagamento) {
		$array_brevetti = array();
		foreach ($pagamento->getContratti() as $contratto) {
			if ($contratto->getTipologiaSpesa()->getCodice() == 'BREVETTI') {
				$array_brevetti[] = $contratto;
			}
		}
		return $array_brevetti;
	}

	public function validaRelazioneTecnica($pagamento) {
		$esito = new EsitoValidazione(true);

		if (!$this->validaAutore($pagamento)->getEsito()) {
			$esito->setEsito(false);
			$esito->addMessaggioSezione("Inserire il referente della relazione tecnica");
		}

		if (!$this->validaOr($pagamento)->getEsito()) {
			$esito->setEsito(false);
			$esito->addMessaggioSezione("I dati degli obiettivi realizzativi non sono completi");
		}

		if (!$this->validaCollaborazioniLaboratori($pagamento)->getEsito()) {
			$esito->setEsito(false);
			$esito->addMessaggioSezione("I dati relativi alle collaborazioni con laboratori non sono completi");
		}

		if (!$this->validaConsulenzeSpecialistiche($pagamento)->getEsito()) {
			$esito->setEsito(false);
			$esito->addMessaggioSezione("I dati relativi alle consulenze specialistiche non sono completi");
		}

		if (!$this->validaAttrezzatureStrumentazioni($pagamento)->getEsito()) {
			$esito->setEsito(false);
			$esito->addMessaggioSezione("I dati relativi alle attrezzature / strumentazioni non sono completi");
		}
		
		if (!$this->validaBrevetti($pagamento)->getEsito()) {
			$esito->setEsito(false);
			$esito->addMessaggioSezione("I dati relativi ai brevetti non sono completi");
		}

		return $esito;
	}

	public function validaAutore($pagamento) {
		$esito = new EsitoValidazione(true);
		$referenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\ReferentePagamento")->findBy(array("pagamento" => $pagamento));

		if (count($referenti) == 0) {
			$esito->setEsito(false);
			$esito->addMessaggio("Inserire il referente della relazione tecnica");
		}

		return $esito;
	}

	public function validaOr($pagamento) {
		$esito = new EsitoValidazione(true);
		// si controlla un solo campo dell'or, perchè sono tutti compilati o nessuno
		$or = $pagamento->getObiettiviRealizzativi();
		foreach ($or as $o) {
			if (is_null($o->getMeseFineEffettivo())) {
				$esito->setEsito(false);
				$esito->addMessaggio("I dati dell'obiettivo realizzativo " . $o->getCodiceOr() . " non sono completi");
			}
		}

		return $esito;
	}

	public function validaCollaborazioniLaboratori($pagamento) {
		$esito = new EsitoValidazione(true);
		// si controlla un solo campo, perchè sono tutti compilati o nessuno
		$laboratori = $this->getCollaborazioniByTipo($pagamento, "LABORATORI");
		foreach ($laboratori as $laboratorio) {
			if (is_null($laboratorio->getReferente())) {
				$esito->setEsito(false);
				$esito->addMessaggio("Non è stato compilato il campo referente per la collaborazione ");
			}
			if (is_null($laboratorio->getAttivita())) {
				$esito->setEsito(false);
				$esito->addMessaggio("Non è stato compilato il campo attivita per la collaborazione ");
			}
		}

		return $esito;
	}
	
	public function validaBrevetti($pagamento) {
		$esito = new EsitoValidazione(true);
		// si controlla un solo campo, perchè sono tutti compilati o nessuno
		$laboratori = $this->getBrevetti($pagamento);
		foreach ($laboratori as $laboratorio) {
			if (is_null($laboratorio->getNumeroDomandaBrevetto())) {
				$esito->setEsito(false);
				$esito->addMessaggio("Non è stato compilato il Numero domanda per il brevetto " . $laboratorio->getTitoloBrevetto());
			}
			if (is_null($laboratorio->getDataDomandaBrevetto())) {
				$esito->setEsito(false);
				$esito->addMessaggio("Non è stato compilato il campo data domanda per il brevetto ". $laboratorio->getTitoloBrevetto());
			}
			if (is_null($laboratorio->getStatoBrevetto())) {
				$esito->setEsito(false);
				$esito->addMessaggio("Non è stato compilato il campo stato per il brevetto ". $laboratorio->getTitoloBrevetto());
			}
			if (is_null($laboratorio->getAmbitoBrevetto())) {
				$esito->setEsito(false);
				$esito->addMessaggio("Non è stato compilato il campo ambito per il brevetto ". $laboratorio->getTitoloBrevetto());
			}
		}

		return $esito;
	}

	public function validaConsulenzeSpecialistiche($pagamento) {
		$esito = new EsitoValidazione(true);
		// si controlla un solo campo, perchè sono tutti compilati o nessuno
		$consulenze = $this->getCollaborazioniByTipo($pagamento, "CONSULENZE");
		foreach ($consulenze as $consulenza) {			
			if (is_null($consulenza->getReferente())) {
				$esito->setEsito(false);
				$esito->addMessaggio("I dati per il consulente " . $consulenza->getCOntratto()->getFornitore() . " non sono completi");
			}
		}

		return $esito;
	}

	public function validaAttrezzatureStrumentazioni($pagamento) {
		$esito = new EsitoValidazione(true);
		$attrezzature = $this->getAttrezzatureStrumentazioniByTipo($pagamento, "ALL");
		foreach ($attrezzature as $attrezzatura) {
			if (is_null($attrezzatura->getDescrizioneAttrezzatura()) ||
					is_null($attrezzatura->getGiustificazioneAttrezzatura()) ||
					is_null($attrezzatura->getPercentualeUso()) ||
					count($attrezzatura->getObiettiviRealizzativi()) == 0
			) {
				$esito->setEsito(false);
				$esito->addMessaggio("I dati relativi all'attrezzatura / strumentazione N. " . $attrezzatura->getId() . " non sono completi");
			}
		}

		return $esito;
	}

	public function getAttrezzatureStrumentazioniByTipo($pagamento, $tipoContratto) {
		$array_attr = array();
		foreach ($pagamento->getGiustificativi() as $giustificativo) {
			if ($tipoContratto == 'ACQUISTO' && $tipoContratto != 'ALL') {
				if ($giustificativo->getTipologiaGiustificativo()->getCodice() == '4A' && !is_null($giustificativo->getEstensione())) {
					$array_attr[] = $giustificativo->getEstensione();
				}
			}
			if ($tipoContratto == 'LEASING' && $tipoContratto != 'ALL') {
				if ($giustificativo->getTipologiaGiustificativo()->getCodice() == '4L' && !is_null($giustificativo->getEstensione())) {
					$array_attr[] = $giustificativo->getEstensione();
				}
			}
			if ($tipoContratto == 'ALL') {
				if (($giustificativo->getTipologiaGiustificativo()->getCodice() == '4L' || $giustificativo->getTipologiaGiustificativo()->getCodice() == '4A') && !is_null($giustificativo->getEstensione())) {
					$array_attr[] = $giustificativo->getEstensione();
				}
			}
		}

		return $array_attr;
	}

	public function getPrototipi($pagamento) {
		$array_prot = array();
		foreach ($pagamento->getGiustificativi() as $giustificativo) {
			if ($giustificativo->getTipologiaGiustificativo()->getCodice() == '6' && !is_null($giustificativo->getEstensione())) {
				$array_prot[] = $giustificativo->getEstensione();
			}
		}

		return $array_prot;
	}
	
	
	public function generaPdf($id_pagamento, $facsimile = true, $download = true) {

		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

		// Recupero la Richiesta:
		$richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

		$personaleRicercatori = $this->getPersonaleByTipo($pagamento, "RICERCATORI_VOCE_1");
		$dati["ricercatori"]=$personaleRicercatori;
		
		$personaleVoce2 = $this->getPersonaleByTipo($pagamento, "PERSONALE_VOCE_2");
		$dati["personale_voce_2"]=$personaleVoce2;

		$personaleVoce3 = $this->getPersonaleByTipo($pagamento, "PERSONALE_VOCE_3");
		$dati["personale_voce_3"]=$personaleVoce3;
		
		$collaborazioniLaboratori = $this->getCollaborazioniByTipo($pagamento, 'LABORATORI');
		$dati["collaborazioni_laboratori"]=$collaborazioniLaboratori;
		
		$collaborazioniSpecialistiche = $this->getCollaborazioniByTipo($pagamento, 'CONSULENZE');
		$dati["collaborazioni_specialistiche"]=$collaborazioniSpecialistiche;
		
		$attrezzature = $this->getAttrezzatureStrumentazioniByTipo($pagamento, 'ALL');
		$dati["attrezzature"]=$attrezzature;
		
		$prototipi = $this->getPrototipi($pagamento);
		$dati["prototipi"]=$prototipi;
		
		$pdf = "@AttuazioneControllo/Pdf/pdf_relazione_tecnica.html.twig";
		return $this->generaPdfRelazione($pagamento, $pdf, $dati, $facsimile, $download);
	}

}
