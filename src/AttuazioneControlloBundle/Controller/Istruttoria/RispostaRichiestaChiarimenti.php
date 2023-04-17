<?php

namespace AttuazioneControlloBundle\Controller\Istruttoria;

use AttuazioneControlloBundle\Service\GestoreRichiesteChiarimentiBase;
use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use RichiesteBundle\Form\Entity\RicercaRichiesta;
use BaseBundle\Entity\StatoIntegrazione;
use DocumentoBundle\Entity\TipologiaDocumento;

/**
 * @Route("/beneficiario/pagamenti/rich_chiarimenti")
 */
class RispostaRichiestaChiarimenti extends BaseController {

	/**
	 * @Route("/elenco_richieste_chiarimenti/{id_pagamento}", name="elenco_richieste_chiarimenti")
	 * @PaginaInfo(titolo="Elenco richieste di chiarimenti",sottoTitolo="mostra l'elenco delle richieste di chiarimenti")
	 * @Menuitem(menuAttivo = "elencoRichiesteChiarimenti")
	 */
	public function elencoRichiesteChiarimentiAction($id_pagamento) {
		$soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
		$soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
		if (is_null($soggetto)) {
			return $this->addErrorRedirect("Soggetto non valido", "home");
		}

		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->findOneBy(array('id' => $id_pagamento));
		$richieste_chiarimenti_protocollate = array();
		foreach ($pagamento->getRichiesteChiarimenti() as $rich_chiar) {
			if ($rich_chiar->getStato() == 'RICH_CHIAR_PROTOCOLLATA'){
				$richieste_chiarimenti_protocollate[] = $rich_chiar;
			}
		}		
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti progetto", $this->generateUrl('elenco_pagamenti', array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richieste di chiarimenti");
		
		$ultima_data_consegna_pec = '-';
		$ultima_data_invio_risposta = '-';
		
		$ultima_rich_chiar = $pagamento->getRichiesteChiarimenti()->last();
		
		if($ultima_rich_chiar) {
						
			$richieste_protocollo = $ultima_rich_chiar->getRichiesteProtocollo();
			if (count($richieste_protocollo) != 0) {
				$ultima_richiesta_protocollo = $richieste_protocollo->last();
				$emails_protocollo           = $ultima_richiesta_protocollo->getEmailProtocollo();
				if (count($emails_protocollo) != 0) {
					$ultima_email_protocollo  = $emails_protocollo->last();
					$ultima_data_consegna_pec = $ultima_email_protocollo->getDataConsegna();
				}					
			}

			if (!is_null($ultima_rich_chiar->getRisposta()) && !is_null($ultima_rich_chiar->getRisposta()->getData())){
				$ultima_data_invio_risposta = $ultima_rich_chiar->getRisposta()->getData();
			}	
		
		}
		
		$messaggio_contatore = '';
		$classe_messaggio_contatore = '';
		
		if ($ultima_data_consegna_pec != '-' ) {
		
			// UNA RICHIESTA E' STATA INOLTRATA... FACCIAMO PARTIRE IL CONTATORE!!!! #}			
			$ultima_data_consegna_pec = \DateTime::createFromFormat('d/m/Y', $ultima_data_consegna_pec);
			
			if ($ultima_data_invio_risposta == '-') {
				
				// NON C'E' RISPOSTA! QUANTI GIORNI SONO TRASCORSI SENZA RISPOSTA? #}
				$today = new \DateTime();
				
				$giorni_senza_risposta = $today->diff($ultima_data_consegna_pec)->format("%a");

				if ($giorni_senza_risposta <= 7) {
					$classe_messaggio_contatore = 'alert alert-warning';
					$messaggio_contatore        = "Attenzione! E' necessario rispondere all'ultima richiesta di chiarimenti entro 7 giorni dalla data di consegna della pec. (Trascorsi $giorni_senza_risposta giorni)";
				} else {
					$classe_messaggio_contatore = 'alert alert-danger';
					$messaggio_contatore        = "Attenzione! E' scaduto il termine di 7 giorni dalla data di consegna della pec, utile per rispondere all'ultima richiesta di chiarimenti. (Trascorsi $giorni_senza_risposta giorni) - Sarà comunque possibile rispondere attraverso il tasto 'Azioni'.";
				}		
							
			} else {
				
				// C'E' RISPOSTA! E' ENTRO I TERMINI? #}
				$giorni_senza_risposta = $ultima_data_invio_risposta->diff($ultima_data_consegna_pec)->format("%a");
				
				if ($giorni_senza_risposta <= 7) {
					$classe_messaggio_contatore = 'alert alert-success';
					$messaggio_contatore        = "La risposta all'ultima richiesta di chiarimenti è stata inviata entro il termine previsto di 7 giorni dalla data di consegna della pec.";
				} else {
					$classe_messaggio_contatore = 'alert alert-danger';
					$messaggio_contatore        = "La risposta all'ultima richiesta di chiarimenti è stata inviata oltre il termine previsto di 7 giorni dalla data di consegna della pec. (Trascorsi $giorni_senza_risposta giorni)";
				}
				
			}	
		
		}

		$dati = array();
		$dati["richieste_chiarimenti"] = $richieste_chiarimenti_protocollate;
		$dati["messaggio_contatore"] = $messaggio_contatore;
		$dati["classe_messaggio_contatore"] = $classe_messaggio_contatore;
		
		$view = $this->renderView("AttuazioneControlloBundle:RispostaRichiestaChiarimenti:elencoRichiesteChiarimenti.html.twig", $dati);
		return new \Symfony\Component\HttpFoundation\Response($view);
	}

	/**
	 * @Route("/{id_richiesta_chiarimenti}/dettaglio", name="dettaglio_richiesta_chiarimenti")
	 * @PaginaInfo(titolo="Richiesta di chiarimenti",sottoTitolo="pagina di dettaglio per una richiesta di chiarimenti")
	 * @Menuitem(menuAttivo = "elencoRichiestaChiarimenti")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento", opzioni={"id" = "id_richiesta_chiarimenti"})
	 * @ControlloAccesso(contesto="richiestachiarimento", classe="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento", opzioni={"id" = "id_richiesta_chiarimenti"}, azione=\AttuazioneControlloBundle\Security\RichiestaChiarimentoVoter::WRITE)
	 */
	public function dettaglioRichiestaChiarimentiAction($id_richiesta_chiarimenti) {
		$richiesta_chiarimenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento")->find($id_richiesta_chiarimenti);
		$risposta = $richiesta_chiarimenti->getRisposta();
		if (is_null($risposta) || is_null($risposta->getFirmatario())) {
			return $this->redirectToRoute("risposta_richiesta_chiarimenti_firmatario", array("id_richiesta_chiarimenti" => $id_richiesta_chiarimenti));
		}

		$this->getSession()->set("gestore_richiesta_chiarimenti_bundle", "AttuazioneControlloBundle");
		$gestore = $this->get("gestore_richieste_chiarimenti")->getGestore($this->getSession()->get("gestore_richiesta_chiarimenti_bundle"));

		$pagamento = $richiesta_chiarimenti->getPagamento();
		
		$dati = array();
		$dati["richiesta_chiarimenti"] = $richiesta_chiarimenti;
		$dati["azioni_ammesse"] = $gestore->calcolaAzioniAmmesse($richiesta_chiarimenti->getRisposta());
		$dati["avanzamenti"] = $gestore->gestioneBarraAvanzamento($richiesta_chiarimenti->getRisposta());

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti progetto", $this->generateUrl('elenco_pagamenti', array("id_richiesta" => $pagamento->getRichiesta()->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richieste di chiarimenti", $this->generateUrl('elenco_richieste_chiarimenti', array("id_pagamento" => $pagamento->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta di chiarimenti");
		
		return $this->render('AttuazioneControlloBundle:RispostaRichiestaChiarimenti:dettaglioRichiestaChiarimenti.html.twig', $dati);
	}

	/**
	 * @Route("/{id_richiesta_chiarimenti}/scelta_firmatario", name="risposta_richiesta_chiarimenti_firmatario")
	 * @PaginaInfo(titolo="Scelta firmatario",sottoTitolo="pagina per scegliere il firmatario del pagamento")
	 * @Breadcrumb(elementi={
	 * 		@ElementoBreadcrumb(testo="Dettaglio richiesta chiarimenti", route="dettaglio_richiesta_chiarimenti", parametri={"id_richiesta_chiarimenti"}),
	 * 		@ElementoBreadcrumb(testo="Scelta firmatario")
	 * })
	 * @Menuitem(menuAttivo = "elencoRichiestaChiarimenti")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento", opzioni={"id" = "id_richiesta_chiarimenti"})
	 * @ControlloAccesso(contesto="richiestachiarimento", classe="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento", opzioni={"id" = "id_richiesta_chiarimenti"}, azione=\AttuazioneControlloBundle\Security\RichiestaChiarimentoVoter::WRITE)
	 */
	public function sceltaFirmatarioAction($id_richiesta_chiarimenti) {

		try {
			$this->getSession()->set("gestore_richiesta_chiarimenti_bundle", "AttuazioneControlloBundle");
			$gestore = $this->get("gestore_richieste_chiarimenti")->getGestore($this->getSession()->get("gestore_richiesta_chiarimenti_bundle"));

			$richiesta_chiarimenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento")->find($id_richiesta_chiarimenti);
			$richiesta = $richiesta_chiarimenti->getPagamento()->getRichiesta();
			$opzioni = array("form_options" => array());
			$opzioni["form_options"]["url_indietro"] = $this->generateUrl("dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $id_richiesta_chiarimenti));
			$opzioni["form_options"]["firmatabili"] = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->getFirmatariAmmissibili($richiesta->getSoggetto());
			
			$opzioni["form_options"]["data_class"] = "AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti";
			
			$response = $gestore->sceltaFirmatario($richiesta_chiarimenti, $opzioni);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $id_richiesta_chiarimenti));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $id_richiesta_chiarimenti));
		}
	}

	/**
	 * @Route("/{id_richiesta_chiarimenti}/nota_risposta", name="nota_risposta_richiesta_chiarimenti")
	 * @PaginaInfo(titolo="Nota risposta richiesta di chiarimenti")
	 * @Menuitem(menuAttivo = "elencoRichiestaChiarimenti")
	 * @Breadcrumb(elementi={
	 * 		@ElementoBreadcrumb(testo="Dettaglio richiesta di chiarimenti", route="dettaglio_richiesta_chiarimenti", parametri={"id_richiesta_chiarimenti"}),
	 * 		@ElementoBreadcrumb(testo="Nota risposta")
	 * })
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento", opzioni={"id" = "id_richiesta_chiarimenti"})
	 * @ControlloAccesso(contesto="richiestachiarimento", classe="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento", opzioni={"id" = "id_richiesta_chiarimenti"}, azione=\AttuazioneControlloBundle\Security\RichiestaChiarimentoVoter::WRITE)
	 */
	public function notaRispostaRichiestaChiarimentiAction($id_richiesta_chiarimenti) {
		$richiesta_chiarimenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento")->find($id_richiesta_chiarimenti);

			$this->getSession()->set("gestore_richiesta_chiarimenti_bundle", "AttuazioneControlloBundle");
			$gestore = $this->get("gestore_richieste_chiarimenti")->getGestore($this->getSession()->get("gestore_richiesta_chiarimenti_bundle"));

		$opzioni = array("form_options" => array());
		$opzioni["form_options"]["url_indietro"] = $this->generateUrl("dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $id_richiesta_chiarimenti));
		return $gestore->notaRispostaRichiestaChiarimenti($richiesta_chiarimenti, $opzioni)->getResponse();
	}
	
	/**
	 * 
	 * @Route("/risposta_richiesta_chiarimenti_elenco_documenti/{id_richiesta_chiarimenti}", name="risposta_richiesta_chiarimenti_elenco_documenti")
	 * @PaginaInfo(titolo="Elenco Documenti",sottoTitolo="carica i documenti richiesti")
	 * @Menuitem(menuAttivo = "elencoRichiestaChiarimenti")
	 * @Breadcrumb(elementi={
	 *		@ElementoBreadcrumb(testo="Dettaglio richiesta di chiarimenti", route="dettaglio_richiesta_chiarimenti", parametri={"id_richiesta_chiarimenti"}),
	 *		@ElementoBreadcrumb(testo="Documenti in richiesta di chiarimenti")
	 * })
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento", opzioni={"id" = "id_richiesta_chiarimenti"})
	 * @ControlloAccesso(contesto="richiestachiarimento", classe="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento", opzioni={"id" = "id_richiesta_chiarimenti"}, azione=\AttuazioneControlloBundle\Security\RichiestaChiarimentoVoter::WRITE)
	 */
	public function elencoDocumentiAction($id_richiesta_chiarimenti) {
		$richiesta_chiarimenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento")->find($id_richiesta_chiarimenti);
		$risposta = $richiesta_chiarimenti->getRisposta();
		if (is_null($risposta) || is_null($risposta->getFirmatario())) {
			return $this->redirectToRoute("risposta_richiesta_chiarimenti_firmatario", array("id_richiesta_chiarimenti" => $id_richiesta_chiarimenti));
		}
		
		$this->getSession()->set("gestore_richiesta_chiarimenti_bundle", "AttuazioneControlloBundle");
		/** @var GestoreRichiesteChiarimentiBase $gestore */
		$gestore = $this->get("gestore_richieste_chiarimenti")->getGestore($this->getSession()->get("gestore_richiesta_chiarimenti_bundle"));

		$opzioni = array();
		$opzioni["url_corrente"] = $this->generateUrl("risposta_richiesta_chiarimenti_elenco_documenti", array("id_richiesta_chiarimenti" => $id_richiesta_chiarimenti));
		$opzioni["url_indietro"] = $this->generateUrl("dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $id_richiesta_chiarimenti));
		$opzioni["route_cancellazione_documento"] = "risposta_rich_chiarimenti_elimina_documento";
		
		$response = $gestore->elencoDocumenti($richiesta_chiarimenti, $opzioni);
		return $response->getResponse();
	}
			
	/**
	 * @Route("/elimina_documento_rich_chiarimenti/{id_documento_rich_chiarimenti}", name="risposta_rich_chiarimenti_elimina_documento")
	 */
	public function eliminaDocumentoAction($id_documento_rich_chiarimenti) {
		$this->get('base')->checkCsrf('token');
		$documento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaRichiestaChiarimenti")->find($id_documento_rich_chiarimenti);
		$richiesta_chiarimenti = $documento->getRispostaRichiestaChiarimenti()->getRichiestaChiarimenti();
		
		$contestoSoggetto = $this->get('contesto')->getContestoRisorsa($richiesta_chiarimenti, "soggetto");
		$contestoRichiesta = $this->get('contesto')->getContestoRisorsa($documento->getRispostaRichiestaChiarimenti(), "rispostarichiestachiarimenti");

		$accessoConsentitoS = $this->isGranted(\SoggettoBundle\Security\SoggettoVoter::ALL, $contestoSoggetto);
		$accessoConsentitoR = $this->isGranted(\AttuazioneControlloBundle\Security\RispostaRichiestaChiarimentoVoter::WRITE, $contestoRichiesta);
		if (!$accessoConsentitoS && !$accessoConsentitoR) {
			throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Accesso non consentito al documento di richiesta di chiarimenti');
		}		

		$this->getSession()->set("gestore_richiesta_chiarimenti_bundle", "AttuazioneControlloBundle");
		$gestore = $this->get("gestore_richieste_chiarimenti")->getGestore($this->getSession()->get("gestore_richiesta_chiarimenti_bundle"));

		$response = $gestore->eliminaDocumento($id_documento_rich_chiarimenti, array("url_indietro" => $this->generateUrl("risposta_richiesta_chiarimenti_elenco_documenti", array( "id_richiesta_chiarimenti" => $richiesta_chiarimenti->getId()))));
		
		return $response->getResponse();
	}
	
	/**
	 *
	 * @Route("/valida_risposta_rich_chiar/{id_risposta_rich_chiarimenti}", name="valida_risposta_rich_chiar")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti", opzioni={"id" = "id_risposta_rich_chiarimenti"})
	 * @ControlloAccesso(contesto="rispostarichiestachiarimenti", classe="AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti", opzioni={"id" = "id_risposta_rich_chiarimenti"}, azione=\AttuazioneControlloBundle\Security\RispostaRichiestaChiarimentoVoter::WRITE)
	 */
	public function validaRispostaRichiestaChiarimentiAction($id_risposta_rich_chiarimenti) {
		
		$risposta_richiesta_chiarimenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti")->find($id_risposta_rich_chiarimenti);
		
		$this->get('base')->checkCsrf('token');
		try {
			$this->getSession()->set("gestore_richiesta_chiarimenti_bundle", "AttuazioneControlloBundle");
			$gestore = $this->get("gestore_richieste_chiarimenti")->getGestore($this->getSession()->get("gestore_richiesta_chiarimenti_bundle"));
			
			$opzioni = array("url_indietro" => $this->generateUrl("dettaglio_richiesta_chiarimenti", array( "id_richiesta_chiarimenti" => $risposta_richiesta_chiarimenti->getRichiestaChiarimenti()->getId())));
			
			$response = $gestore->validaRispostaRichiestaChiarimenti($id_risposta_rich_chiarimenti, $opzioni);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $risposta_richiesta_chiarimenti->getRichiestaChiarimenti()->getId()));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $risposta_richiesta_chiarimenti->getRichiestaChiarimenti()->getId()));
		}
	}
	
	/**
	 * @Route("/{id_risposta_rich_chiar}/scarica_risposta_rich_chiar", name="scarica_risposta_rich_chiar")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti", opzioni={"id" = "id_risposta_rich_chiar"})
	 * @ControlloAccesso(contesto="rispostarichiestachiarimenti", classe="AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti", opzioni={"id" = "id_risposta_rich_chiar"}, azione=\AttuazioneControlloBundle\Security\RispostaRichiestaChiarimentoVoter::WRITE)
	 */
	public function scaricaRispostaAction($id_risposta_rich_chiar) {

		$risposta_richiesta_chiarimenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti")->find($id_risposta_rich_chiar);

		if (is_null($risposta_richiesta_chiarimenti)) {
			return $this->addErrorRedirect("Richiesta non valida", "elenco_richieste");
		}

		if (is_null($risposta_richiesta_chiarimenti->getDocumentoRisposta())) {
			return $this->addErrorRedirect("Nessun documento associato alla risposta", "dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $risposta_richiesta_chiarimenti->getRichiestaChiarimenti()->getId()));
		}

		return $this->get("documenti")->scaricaDaId($risposta_richiesta_chiarimenti->getDocumentoRisposta()->getId());
	}
	
	/**
	 * @Route("/{id_richiesta_chiarimenti}/carica_risposta_rich_chiar_firmata", name="carica_risposta_rich_chiar_firmata")
	 * @Template("AttuazioneControlloBundle:RichiestaChiarimenti:caricaRispostaFirmata.html.twig")
	 * @PaginaInfo(titolo="Carica risposta richiesta chiarimenti firmata",sottoTitolo="pagina per caricare la risposta a richiesta di chiarimenti firmata")
	 * @Breadcrumb(elementi={
	 *		@ElementoBreadcrumb(testo="Dettaglio richiesta di chiarimenti", route="dettaglio_richiesta_chiarimenti", parametri={"id_richiesta_chiarimenti"}),
	 *		@ElementoBreadcrumb(testo="Carica risposta")
	 * })
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento", opzioni={"id" = "id_richiesta_chiarimenti"})
	 * @ControlloAccesso(contesto="richiestachiarimento", classe="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento", opzioni={"id" = "id_richiesta_chiarimenti"}, azione=\AttuazioneControlloBundle\Security\RichiestaChiarimentoVoter::WRITE)
	 */
	public function caricaRispostaFirmataAction($id_richiesta_chiarimenti) {
		$em = $this->getEm();

		$request = $this->getCurrentRequest();

		$richiesta_chiarimenti = $em->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento")->find($id_richiesta_chiarimenti);
		$risposta_richiesta_chiarimenti = $richiesta_chiarimenti->getRisposta();
		
		$documento_file = new \DocumentoBundle\Entity\DocumentoFile();

		if (!$risposta_richiesta_chiarimenti) {
			throw $this->createNotFoundException('Risorsa non trovata');
		}

		try {
			if (!$risposta_richiesta_chiarimenti->getStato()->uguale(\BaseBundle\Entity\StatoRichiestaChiarimenti::RICH_CHIAR_VALIDATA)) {
				throw new SfingeException("Stato non valido per effettuare l'operazione");
			}
		} catch (SfingeException $e) {
			return $this->addErrorRedirect("Errore generico", "dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $id_richiesta_chiarimenti));
		}

		$opzioni_form["tipo"] = TipologiaDocumento::RICH_CHIAR_RISPOSTA_FIRMATO;
		$opzioni_form["cf_firmatario"] = $risposta_richiesta_chiarimenti->getFirmatario()->getCodiceFiscale();
		$form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
		$form->add("pultanti", "BaseBundle\Form\SalvaIndietroType", array("url" => $this->generateUrl("dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $id_richiesta_chiarimenti))));
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {
					$this->container->get("documenti")->carica($documento_file, 0);
					$risposta_richiesta_chiarimenti->setDocumentoRispostaFirmato($documento_file);
					$this->container->get("sfinge.stati")->avanzaStato($risposta_richiesta_chiarimenti, \BaseBundle\Entity\StatoRichiestaChiarimenti::RICH_CHIAR_FIRMATA, true);
					$em->flush();
					return $this->addSuccessRedirect("Documento caricato correttamente", "dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $id_richiesta_chiarimenti));
				} catch (\Exception $e) {
					//TODO gestire cancellazione del file
					$this->addFlash('error', "Errore generico");
				}
			}
		}
		$form_view = $form->createView();

		return array("id_richiesta_chiarimenti" => $id_richiesta_chiarimenti, "form" => $form_view);
	}
	
	/**
	 * @Route("/{id_risposta_rich_chiar}/invia_risposta_rich_chiar", name="invia_risposta_rich_chiar")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti", opzioni={"id" = "id_risposta_rich_chiar"})
	 * @ControlloAccesso(contesto="rispostarichiestachiarimenti", classe="AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti", opzioni={"id" = "id_risposta_rich_chiar"}, azione=\AttuazioneControlloBundle\Security\RispostaRichiestaChiarimentoVoter::WRITE)
	 */
	public function inviaRispostaAction($id_risposta_rich_chiar) {
		
		$risposta_richiesta_chiarimenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti")->find($id_risposta_rich_chiar);
			
		$this->get('base')->checkCsrf('token');
		try {
			$this->getSession()->set("gestore_richiesta_chiarimenti_bundle", "AttuazioneControlloBundle");
			$gestore = $this->get("gestore_richieste_chiarimenti")->getGestore($this->getSession()->get("gestore_richiesta_chiarimenti_bundle"));

			$opzioni = array("url_indietro" => $this->generateUrl("dettaglio_richiesta_chiarimenti", array( "id_richiesta_chiarimenti" => $risposta_richiesta_chiarimenti->getRichiestaChiarimenti()->getId())));
			
			$response = $gestore->inviaRisposta($risposta_richiesta_chiarimenti, $opzioni);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $risposta_richiesta_chiarimenti->getRichiestaChiarimenti()->getId()));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $risposta_richiesta_chiarimenti->getRichiestaChiarimenti()->getId()));
		}
	}
	
	/**
	 * @Route("/{id_risposta_rich_chiar}/invalida_risposta_rich_chiar", name="invalida_risposta_rich_chiar")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti", opzioni={"id" = "id_risposta_rich_chiar"})
	 * @ControlloAccesso(contesto="rispostarichiestachiarimenti", classe="AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti", opzioni={"id" = "id_risposta_rich_chiar"}, azione=\AttuazioneControlloBundle\Security\RispostaRichiestaChiarimentoVoter::WRITE)
	 */
	public function invalidaRispostaRichChiarAction($id_risposta_rich_chiar) {
		
		$risposta_richiesta_chiarimenti = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti")->find($id_risposta_rich_chiar);
		
		$this->get('base')->checkCsrf('token');
		try {
			$this->getSession()->set("gestore_richiesta_chiarimenti_bundle", "AttuazioneControlloBundle");
			$gestore = $this->get("gestore_richieste_chiarimenti")->getGestore($this->getSession()->get("gestore_richiesta_chiarimenti_bundle"));

			$opzioni = array("url_indietro" => $this->generateUrl("dettaglio_richiesta_chiarimenti", array( "id_richiesta_chiarimenti" => $risposta_richiesta_chiarimenti->getRichiestaChiarimenti()->getId())));
			
			$response = $gestore->invalidaRispostaRichChiar($id_risposta_rich_chiar, $opzioni);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $risposta_richiesta_chiarimenti->getRichiestaChiarimenti()->getId()));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $risposta_richiesta_chiarimenti->getRichiestaChiarimenti()->getId()));
		}
	}	
	
	/**
	 * @Route("/{id_risposta_rich_chiar}/scarica_risposta_rich_chiar_firmata", name="scarica_risposta_rich_chiar_firmata")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti", opzioni={"id" = "id_risposta_rich_chiar"})
	 * @ControlloAccesso(contesto="rispostarichiestachiarimenti", classe="AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti", opzioni={"id" = "id_risposta_rich_chiar"}, azione=\AttuazioneControlloBundle\Security\RispostaRichiestaChiarimentoVoter::WRITE)
	 */
	public function scaricaRispostaFirmataAction($id_risposta_rich_chiar) {

		$risposta = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti")->find($id_risposta_rich_chiar);
		
		if (is_null($risposta)) {
			return $this->addErrorRedirect("Richiesta non valida", "elenco_richieste");
		}

		if (is_null($risposta->getDocumentoRispostaFirmato())) {
			return $this->addErrorRedirect("Nessun documento associato alla risposta", "dettaglio_richiesta_chiarimenti", array("id_richiesta_chiarimenti" => $risposta->getRichiestaChiarimenti()->getId()));
		}

		return $this->get("documenti")->scaricaDaId($risposta->getDocumentoRispostaFirmato()->getId());
	}

}
