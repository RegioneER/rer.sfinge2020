<?php

namespace AttuazioneControlloBundle\Service;

use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\Economia;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use Symfony\Component\Form\FormError;

class GestorePagamentiIngFinanziariaBase extends GestorePagamentiProcedureParticolariBase {

    public function getRuolo() {
        return "ROLE_GESTIONE_INGEGNERIA_FINANZIARIA";
    }
	
	public function aggiungiPagamento($id_richiesta) {
		$em = $this->getEm();
		$richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        
        if(!$this->isUtenteAbilitato()) {
            $this->addError("Utente non abilitato all'operazione");
			return $this->redirect($this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $id_richiesta)));
        }
        
		
		if($richiesta->getVociPianoCosto()->count() == 0){
			$this->addError('Non è ancora stato inserito il piano costi');
			return $this->redirect($this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $id_richiesta)));
		}
			
		$pagamento = new Pagamento();
		$pagamento->setAttuazioneControlloRichiesta($richiesta->getAttuazioneControllo());

		$options = array();
		$options["url_indietro"] = $this->generateUrlByTipoProcedura("elenco_pagamenti", $richiesta->getProcedura(), array("id_richiesta" => $id_richiesta));
		$options["modalita_pagamento"] = $this->getModalitaPagamento();

		$form = $this->createForm("AttuazioneControlloBundle\Form\PagamentoProceduraParticolareType", $pagamento, $options);

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			if (is_null($pagamento->getModalitaPagamento())) {
				return $this->addErrorRedirectByTipoProcedura("Selezionare una modalità di pagamento", "aggiungi_pagamento", $pagamento->getProcedura(), array("id_richiesta" => $id_richiesta));
			}

			if ($pagamento->getModalitaPagamento()->getUnico() && ($richiesta->getAttuazioneControllo()->hasPagamentoUnicoApprovato() || $richiesta->getAttuazioneControllo()->hasPagamentoSaldoApprovato())) {
				$form->get("modalita_pagamento")->addError(new FormError("È già stato approvato un pagamento per la modalità specificata, e non è possibile inserirne ulteriori"));
			}

			if ($richiesta->getAttuazioneControllo()->hasPagamentoSaldoApprovato()) {
				$form->get("modalita_pagamento")->addError(new FormError("È già stato approvato un saldo, e non è possibile inserire ulteriori pagamenti"));
			}
			
			/*if($richiesta->getAttuazioneControllo()->hasPagamentoTrasferimento() && ($pagamento->getModalitaPagamento()->getCodice() == \AttuazioneControlloBundle\Entity\ModalitaPagamento::TRASFERIMENTO)){
				form->get("modalita_pagamento")->addError(new FormError("È possibile inserire una solo pagamento con modalità pagamento di tipo trasferimento"));
			}*/
			
			if(!$richiesta->getAttuazioneControllo()->hasPagamentoTrasferimento() && !($pagamento->getModalitaPagamento()->getCodice() == \AttuazioneControlloBundle\Entity\ModalitaPagamento::TRASFERIMENTO)){
				$form->get("modalita_pagamento")->addError(new FormError("Il primo pagamento deve essere un trasferimento"));
			}

			if ($form->isValid()) {
				$this->calcolaImportoRichiestoIniziale($pagamento);

				try {
					$em->beginTransaction();

					//$this->aggiungiFascicoloPagamento($pagamento);
					$pagamento->setAbilitaRendicontazioneChiusa(false);
					$em->persist($pagamento);
					// errore perchè il pagamento non è flushato, forse meglio fare una transazione
					$em->flush();
					$this->container->get("sfinge.stati")->avanzaStato($pagamento, "PAG_INSERITO");
					$em->flush();
					$em->commit();
					return $this->addSuccesRedirectByTipoProcedura("Il pagamento è stato correttamente aggiunto", "elenco_pagamenti", $richiesta->getProcedura(), array("id_richiesta" => $id_richiesta));
				} catch (\Exception $e) {
					$em->rollback();
					$this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
				}
			}
		}

		$dati = array();
		$dati["form"] = $form->createView();
		$dati["richiesta"] = $richiesta;

		return $this->render("AttuazioneControlloBundle:Pagamenti:aggiungiPagamento.html.twig", $dati);
	}
	
	public function getModalitaPagamento($richiesta = null) {

		$modalita_pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\ModalitaPagamento")->findByCodice(array('TRASFERIMENTO', 'SAL', 'SALDO_FINALE'));
		return $modalita_pagamento;
	}
	
	public function datiGeneraliPagamento($id_pagamento, $formType = NULL) {

		$options = array();
		$em = $this->getEm();
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		$richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

		$options["tipologia"] = $pagamento->getModalitaPagamento()->getCodice();
		$options["disabled"] = $pagamento->isRichiestaDisabilitata() || !$this->isUtenteAbilitato();
		$options["url_indietro"] = $this->generateUrlByTipoProcedura("elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId()));

		$form = $this->createForm("AttuazioneControlloBundle\Form\DatiGeneraliPagamentoPPType", $pagamento, $options);

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {
					
					// non vogliono il giustificativo, ma tocca inserirne uno subdolamente
					if($pagamento->getModalitaPagamento()->isTrasferimento()){
						$giustificativi = $pagamento->getGiustificativi();
						if(count($giustificativi) == 0){
							$giustificativo = new \AttuazioneControlloBundle\Entity\GiustificativoPagamento();
							$giustificativo->setPagamento($pagamento);
							
							$vocePianoCostoGiustificativo = new \AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo();
							$vocePianoCostoGiustificativo->setGiustificativoPagamento($giustificativo);
							$vocePianoCostoGiustificativo->setVocePianoCosto($richiesta->getVociPianoCosto()->first());
							
							$em->persist($giustificativo);
							$em->persist($vocePianoCostoGiustificativo);
						}else{
							$giustificativo = $giustificativi[0];
							$vocePianoCostoGiustificativo = $giustificativo->getVociPianoCosto()->first();
						}
						$importo = $pagamento->getImportoRichiesto();
						$vocePianoCostoGiustificativo->setImporto($importo);
						$vocePianoCostoGiustificativo->setImportoApprovato($importo);
						$vocePianoCostoGiustificativo->setAnnualita(1);
						
						$giustificativo->setImportoGiustificativo($importo);
						$giustificativo->setImportoApprovato($importo);
						$giustificativo->setImportoRichiesto($importo);
					}
					
					$em->flush();
					return $this->addSuccesRedirectByTipoProcedura("Dati correttamente salvati", "elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId()));
				} catch (\Exception $e) {
					$this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
				}
			}
		}

		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrlByTipoProcedura("elenco_richieste", $pagamento->getProcedura()));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrlByTipoProcedura("dettaglio_richiesta", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco pagamenti", $this->generateUrlByTipoProcedura("elenco_pagamenti", $pagamento->getProcedura(), array("id_richiesta" => $richiesta->getId())));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dati generali pagamento");

		$options["form"] = $form->createView();
		$options["pagamento"] = $pagamento;
		$options["richiesta"] = $richiesta;
		return $this->render("AttuazioneControlloBundle:Pagamenti:datiGeneraliPPT.html.twig", $options);
	}


	/**
	 * @param \AttuazioneControlloBundle\Entity\Pagamento $pagamento
	 *
	 * Metodo richiamato per popolare automaticamente le economie per le ING. FINANZIARIE
	 */
	protected function popolaEconomieRER(Pagamento $pagamento){

		$em = $this->getEm();

		// Valore ritornato dalla funzione
		$economie = array();

		$richiesta = $pagamento->getRichiesta();
		$istruttoriaRichiesta = $richiesta->getIstruttoria();


		// TC33/FONTE FINANZIARIA
		$fonteUE = $em->getRepository("MonitoraggioBundle:TC33FonteFinanziaria")->findBy(array("cod_fondo" => "ERDF"));
		$fonteStato = $em->getRepository("MonitoraggioBundle:TC33FonteFinanziaria")->findBy(array("cod_fondo" => "FDR"));
		$fonteRegione = $em->getRepository("MonitoraggioBundle:TC33FonteFinanziaria")->findBy(array("cod_fondo" => "FPREG"));

		// IPOTIZZATO che ING. FINANZIARIA abbia 1 PIANO COSTO con 1 sola VOCE PIANO COSTO
		$vociPianoCosto = $richiesta->getVociPianoCosto();
		$costoAmmesso = $vociPianoCosto[0]->getImportoAnno1();  // Chiedere conferma...

		// I RENDICONTATI

		// PAGAMENTI PRECEDENTI + ATTUALE
		$pagamentiPrecedenti = $richiesta->getAttuazioneControllo()->getPagamenti();

		$contributoPagato = 0.00; // il totale pagato da UE-Stato-Regione // CONTRIBUTO EROGATO A SALDO
		$rendicontatoAmmesso = 0.00;	// quanto ha rendicontato il beneficiario


		foreach($pagamentiPrecedenti as $pagamentoPrecedente){

			// Sommo gli importi dei pagamenti COPERTI DA MANDATO
			if(!is_null($pagamentoPrecedente->getMandatoPagamento())){
				$contributoPagato += $pagamentoPrecedente->getMandatoPagamento()->getImportoPagato();  // Contributo erogato a SALDO
			}

			$rendicontatoAmmesso += $pagamentoPrecedente->getRendicontatoAmmesso();		// Somma degli IMPORTI APPROVATI dei GIUSTIFICATIVI
		}

		// GLI IMPORTI DELLE ECONOMIE
		$importoEconomiaTotale = $costoAmmesso - $rendicontatoAmmesso; // economia totale(privato + UE-Stato-Regione)

		// Siamo nel CASO 1 dell'EXCEL
		if ($importoEconomiaTotale > 0){

			// Creazione ECONOMIE
			$economiaUE = new Economia();
			$economiaStato = new Economia();
			$economiaRegione = new Economia();

			$importoEconomiaUE = round($importoEconomiaTotale * 50 / 100, 2);
			$importoEconomiaStato = round($importoEconomiaTotale * 35 / 100, 2);
			$importoEconomiaRegione = $importoEconomiaTotale - ($importoEconomiaUE + $importoEconomiaStato);

			// SETTO GLI IMPORTI ALLE 4 ECONOMIE
			$economiaUE->setImporto($importoEconomiaUE);
			$economiaStato->setImporto($importoEconomiaStato);
			$economiaRegione->setImporto($importoEconomiaRegione);

			// FONTE
			$economiaUE->setTc33FonteFinanziaria($fonteUE[0]);
			$economiaStato->setTc33FonteFinanziaria($fonteStato[0]);
			$economiaRegione->setTc33FonteFinanziaria($fonteRegione[0]);

			// RICHIESTA
			$economiaUE->setRichiesta($richiesta);
			$economiaStato->setRichiesta($richiesta);
			$economiaRegione->setRichiesta($richiesta);


			// IMPOSTO il valore di ritorno
			$economie[] = $economiaUE;
			$economie[] = $economiaStato;
			$economie[] = $economiaRegione;

			/*
             * TODO: Rivedere l'aggiornamento degli IMPEGNI (Inserimento DISIMPEGNO!! Non decrementare l'IMPEGNO!!!)
             */

			// TODO: è corretto prendere la causale "02 - Minori spese realizzate" ???
			$causaleDisimpegno = $em->getRepository("MonitoraggioBundle:TC38CausaleDisimpegno")->findOneBy(array("causale_disimpegno" => "02"));


			/*
			"La particolarità degli strumenti finanziari è che per  Impegni e Pagamenti bisogna sempre tracciare un doppio flusso:

			a) gli impegni e pagamenti della Regione verso il Gestore del Fondo
				(non è beneficiario, ma attuatore, poiché il Beneficiario resta la Regione),
				segnalati con Tipologia Impegno e Tipologia Pagamento rispettivamente I-TR e P-TR;

			b) gli impegni e i pagamenti del soggetto gestore verso terzi, oggetto delle rendicontazioni,
				segnalati con tipologia impegno I e pagamento P.

			Si ricorda, inoltre, che per i soli Pagamenti, emessi dal soggetto gestore verso i singoli beneficiari (imprese finanziate)
			vanno raccolti i dati nella struttura FN08 Percettori"
			 */


			// DOPPIO FLUSSO

			// A) - REGIONE --> GESTORE del FONDO

			// DESTINAZIONE
			$richiestaDisimpegno = new RichiestaImpegni($richiesta);
			$richiestaDisimpegno->setTc38CausaleDisimpegno($causaleDisimpegno);
			$richiestaDisimpegno->setTipologiaImpegno("D-TR"); // D = DISIMPEGNO
			$richiestaDisimpegno->setImportoImpegno($importoEconomiaTotale);

			$disimpegno = new ImpegniAmmessi();
			$disimpegno->setRichiestaImpegni($richiestaDisimpegno);
			$disimpegno->setTc38CausaleDisimpegnoAmm($causaleDisimpegno);
			$disimpegno->setTipologiaImpAmm("D-TR");
			$disimpegno->setImportoImpAmm($importoEconomiaTotale);

			// se per la fn01 esiste un solo livello gerarchico, inserisco anche la IMPEGNI_AMMESSI ?? e richieste_livelli_gerarchici
			// Se il cod_programma/liv_gerarchico è uno (…della RICHIESTA ???…), il record viene creato automaticamente con gli stessi dati dell'IMPEGNO ???

			// GESTIONE equivalente ai PAGAMENTI;
			// In ATTESA di sapere quale inserire...
			$richiestaProgrammi = $richiesta->getMonProgrammi();

			// 1 PROGRAMMA
			if(count($richiestaProgrammi) == 1)
			{
				$livelliGerarchici = $richiestaProgrammi[0]->getMonLivelliGerarchici();

				// 1 LIVELLO GERARCHICO
				if(count($livelliGerarchici) == 1){

					$disimpegno->setRichiestaLivelloGerarchico($livelliGerarchici[0]);

				}

			}else{

				// Recupero un valore di appoggio per evitare che schianti in quanto obbligatorio....
				// In ATTESA di sapere quale inserire...
				$livelloGerarchico = $em->getRepository("MonitoraggioBundle:TC36LivelloGerarchico")->findBy(array("cod_liv_gerarchico" => "2014IT16RFOP008_8"));

				$disimpegno->setRichiestaLivelloGerarchico($livelloGerarchico[0]);
			}

			$richiestaDisimpegno->addMonImpegniAmmessi($disimpegno);


			// B) - SOGGETTO GESTORE --> TERZI

			// DESTINAZIONE
			$richiestaDisimpegno = new RichiestaImpegni($richiesta);
			$richiestaDisimpegno->setTc38CausaleDisimpegno($causaleDisimpegno);
			$richiestaDisimpegno->setTipologiaImpegno("D"); // D = DISIMPEGNO
			$richiestaDisimpegno->setImportoImpegno($importoEconomiaTotale);

			$disimpegno = new ImpegniAmmessi();
			$disimpegno->setRichiestaImpegni($richiestaDisimpegno);
			$disimpegno->setTc38CausaleDisimpegnoAmm($causaleDisimpegno);
			$disimpegno->setTipologiaImpAmm("D");
			$disimpegno->setImportoImpAmm($importoEconomiaTotale);

			// se per la fn01 esiste un solo livello gerarchico, inserisco anche la IMPEGNI_AMMESSI ?? e richieste_livelli_gerarchici
			// Se il cod_programma/liv_gerarchico è uno (…della RICHIESTA ???…), il record viene creato automaticamente con gli stessi dati dell'IMPEGNO ???

			// GESTIONE equivalente ai PAGAMENTI; 1 PROGRAMMA 1 LIV. GERARCHICO
			// In ATTESA di sapere quale inserire...
			$richiestaProgrammi = $richiesta->getMonProgrammi();

			// 1 PROGRAMMA
			if(count($richiestaProgrammi) == 1)
			{
				$livelliGerarchici = $richiestaProgrammi[0]->getMonLivelliGerarchici();

				// 1 LIVELLO GERARCHICO
				if(count($livelliGerarchici) == 1){

					$disimpegno->setRichiestaLivelloGerarchico($livelliGerarchici[0]);

				}

			}else{

				// Recupero un valore di appoggio per evitare che schianti in quanto obbligatorio....
				// In ATTESA di sapere quale inserire...
				$livelloGerarchico = $em->getRepository("MonitoraggioBundle:TC36LivelloGerarchico")->findBy(array("cod_liv_gerarchico" => "2014IT16RFOP008_8"));

				$disimpegno->setRichiestaLivelloGerarchico($livelloGerarchico[0]);
			}

			$richiestaDisimpegno->addMonImpegniAmmessi($disimpegno);

		}

		return $economie;

	}



}
