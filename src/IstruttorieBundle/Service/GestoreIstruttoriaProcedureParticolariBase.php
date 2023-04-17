<?php

namespace IstruttorieBundle\Service;

use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Service\GestoreResponse;
use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\LocalizzazioneGeografica;
use AttuazioneControlloBundle\Entity\StrumentoAttuativo;
use AttuazioneControlloBundle\Entity\SoggettiCollegati;
use AttuazioneControlloBundle\Entity\Finanziamento;
use MonitoraggioBundle\Entity\VoceSpesa;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use AttuazioneControlloBundle\Entity\RichiestaStatoAttuazioneProgetto;
use AttuazioneControlloBundle\Entity\IterProgetto;
use MonitoraggioBundle\Entity\RichiestaPianoCosti;

class GestoreIstruttoriaProcedureParticolariBase extends AGestoreIstruttoria {

	public function aggiornaIstruttoriaRichiesta($id_richiesta, $opzioni = array()) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		if (is_null($richiesta)) {
			throw new SfingeException("Risorsa non trovata");
		}

		$istruttoria = $richiesta->getIstruttoria();
		if (is_null($istruttoria)) {
			$istruttoria = new IstruttoriaRichiesta();
			$richiesta->setIstruttoria($istruttoria);
			$istruttoria->setRichiesta($richiesta);
			$istruttoria->setSospesa(false);

			$procedura = $richiesta->getProcedura();

			if ($procedura->isAssistenzaTecnica()) {
				$cupNatura = $this->getEm()->getRepository("CipeBundle\Entity\Classificazioni\CupNatura")->find(2);	// REALIZZAZIONE E ACQUISTO DI SERVIZI
				$istruttoria->setCupNatura($cupNatura);
			}

			if ($procedura->isIngegneriaFinanziaria()) {
				$cupNatura = $this->getEm()->getRepository("CipeBundle\Entity\Classificazioni\CupNatura")->find(6);	// ACQUISTO DI PARTECIPAZIONI AZIONARIE E CONFERIMENTI DI CAPITALE
				$istruttoria->setCupNatura($cupNatura);
			}
			
			if ($procedura->isAcquisizioni()) {
				$cupNatura = $this->getEm()->getRepository("CipeBundle\Entity\Classificazioni\CupNatura")->find(2);	//  REALIZZAZIONE E ACQUISTO DI SERVIZI
				$istruttoria->setCupNatura($cupNatura);
			}

		}

		$this->avanzaFaseIstruttoriaRichiesta($istruttoria);

		try {
			$this->getEm()->flush();
		} catch (\Exception $e) {
			throw new SfingeException("Errore nell'aggiornamento dell'istruttoria");
		}

		return $istruttoria;
	}

	public function avanzaFaseIstruttoriaRichiesta($istruttoria_richiesta) {
		$fase_successiva = $this->getEm()->getRepository("IstruttorieBundle:FaseIstruttoria")->findFaseSuccessiva($istruttoria_richiesta);

		if (!is_null($fase_successiva)) {
			$istruttoria_richiesta->setFase($fase_successiva);
			$this->operazioniAvanzamentoFase($istruttoria_richiesta, $fase_successiva);
		}
	}

	public function avanzamentoATC($id_richiesta) {
		$em = $this->getEm();
		$richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

		if (is_null($richiesta)) {
			throw new SfingeException("Risorsa non trovata");
		}

		$istruttoria = $richiesta->getIstruttoria();
		if (is_null($istruttoria)) {
			throw new SfingeException("Risorsa non trovata");
		}

		$istruttoria->setValidazioneAtc(true);
		$istruttoria->setUtenteValidatoreAtc($this->getUser());
		$istruttoria->setDataValidazioneAtc(new \DateTime());

		$atc = $this->generaATC($istruttoria);
		$richiesta->setAttuazioneControllo($atc);

		$this->popolaLocalizzazioneGeograficaRER($richiesta);
		$this->popolaVociSpesa($richiesta);
		$this->popolaStatoInizialeAttuazioneProgetto($richiesta);
		$this->popolaSoggettiCollegati($richiesta);
		$this->popolaIndicatoriOutput($richiesta);
		$richiesta = $this->popolaRichiestaStrumentoAttuativo($richiesta);
		$richiesta = $this->popolaFinanziamento($richiesta);
		$programma = $this->creaProgramma($richiesta);
		$richiesta->addMonProgrammi($programma);
		$classificazioni = $this->creaClassificazioni($programma);
        foreach ($classificazioni as $classificazione) {
            $programma->addClassificazioni($classificazione);
        }
		$impegno = $this->creaImpegno($richiesta);
		$richiesta->addMonImpegni($impegno);
		$livelloGerarchicoPerAsse = $this->creaLivelloGerarchicoPerAsse($programma);
		$impegnoAmmesso = $this->creaImpegnoAmmesso($impegno, $livelloGerarchicoPerAsse);
		$programma->addMonLivelliGerarchici($livelloGerarchicoPerAsse);
		$impegno->addMonImpegniAmmessi($impegnoAmmesso);

		$em->persist($richiesta);

		$gestore_istruttoria = $this->container->get("gestore_istruttoria")->getGestore($richiesta->getProcedura());
		$gestore_istruttoria->creaLogIstruttoriaAtc($istruttoria, "ATC_VALIDA_PP");

		return $atc;
	}

	public function generaATC($istruttoria_richiesta) {
		$data_limite_accettazione = new \DateTime();
		$data_limite_accettazione->modify("+30 days");
		$atc = new \AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta();
		$atc->setDataLimiteAccettazione($data_limite_accettazione);

		return $atc;
	}
	
	public function creaLogIstruttoria($istruttoria, $oggetto) {
		$log_istruttoria = new \IstruttorieBundle\Entity\IstruttoriaLog();
		$log_istruttoria->setIstruttoriaRichiesta($istruttoria);
		$log_istruttoria->setOggetto($oggetto);
		$log_istruttoria->setUtente($this->getUser());
		$log_istruttoria->setData(new \DateTime());
		
		$istruttoria->addIstruttoriaLog($log_istruttoria);
	}
	
	public function creaLogIstruttoriaAtc($istruttoria, $oggetto) {
		$log_istruttoria = new \IstruttorieBundle\Entity\IstruttoriaAtcLog();
		$log_istruttoria->setIstruttoriaRichiesta($istruttoria);
		$log_istruttoria->setOggetto($oggetto);
		$log_istruttoria->setUtente($this->getUser());
		$log_istruttoria->setData(new \DateTime());
		$log_istruttoria->setAmmissibilitaAtto($istruttoria->getAmmissibilitaAtto());
		$log_istruttoria->setConcessione($istruttoria->getConcessione());
		$log_istruttoria->setContributoAmmesso($istruttoria->getContributoAmmesso());
		$log_istruttoria->setDataContributo($istruttoria->getDataContributo());
		$log_istruttoria->setImpegnoAmmesso($istruttoria->getImpegnoAmmesso());
		$log_istruttoria->setDataImpegno($istruttoria->getDataImpegno());
		
		$istruttoria->addIstruttoriaAtcLog($log_istruttoria);
	}

	public function datiCup($id_richiesta) {
		
	}

	public function esitoFinaleIstruttoria($id_richiesta) {
		
	}

	public function getEmailATCConfig($istruttoria_richiesta) {
		
	}

	public function getScelteEsitoFinale() {
		
	}

	public function getSelezioniCup($id_richiesta, $esisteCup) {
		
	}

	public function isEsitoFinaleEmettibile($istruttoria_richiesta) {
		
	}

	public function isEsitoFinalePositivoEmettibile($istruttoria_richiesta) {
		
	}

	public function isFaseAvanzabile($istruttoria_richiesta) {
		
	}

	public function operazioniAvanzamentoFase($istruttoria_richiesta, $fase) {
		
	}

	public function validaATC($form) {
		
	}
	
	public function creaIntegrazione($id_valutazione_checklist) {
		// do nothing
	}
	
	public function nucleoIstruttoria($id_richiesta) {
		// do nothing
	}
	
	public function eliminaDocumentoNucleoIstruttoria($idRichiesta, $id_documento) {
		// do nothing
	}





	/**
	 * @param Richiesta $richiesta
	 *
	 * Metodo richiamato contestualmente al passaggio della richiesta in ATTUAZIONE, tramite il pulsante VALIDA
	 * serve per popolare automaticamente le info sulla LOCALIZZAZIONE GEOGRAFICA; utile ai fini del monitoraggio
	 */
	protected function popolaLocalizzazioneGeograficaRER( Richiesta $richiesta){

		$localizzazioneGeograficaMon = new LocalizzazioneGeografica();
		$localizzazioneGeograficaMon->setRichiesta($richiesta);
		$mandatario = $richiesta->getMandatario();
		/** @var \RichiesteBundle\Entity\SedeOperativa $sedeOperativa */
		$sedeOperativa = $mandatario->getSedi()->first();
		if($mandatario->getSedeLegaleComeOperativa() || is_null($mandatario->getSedeLegaleComeOperativa())){
			$soggetto = $mandatario->getSoggetto();
			$comune = $soggetto->getComune();
			$localizzazioneGeograficaTC16 = $comune->getTc16LocalizzazioneGeografica();
			$localizzazioneGeograficaMon->setLocalizzazione($localizzazioneGeograficaTC16);
			$localizzazioneGeograficaMon->setIndirizzo($soggetto->getVia() . ", " . $soggetto->getCivico());
			$localizzazioneGeograficaMon->setCap($soggetto->getCap());
		}
		else{
			$sede = $sedeOperativa->getSede();
			$indirizzo = $sede->getIndirizzo();
			$comune = $indirizzo->getComune();

			$localizzazioneGeograficaTC16 = $comune->getTc16LocalizzazioneGeografica();
			$localizzazioneGeograficaMon->setLocalizzazione($localizzazioneGeograficaTC16);
			$localizzazioneGeograficaMon->setIndirizzo($indirizzo->getVia() . ", " . $indirizzo->getNumeroCivico());
			$localizzazioneGeograficaMon->setCap($indirizzo->getCap());
		}

		$richiesta->addMonLocalizzazioneGeografica($localizzazioneGeograficaMon);
	}


	/**
	 * @param \RichiesteBundle\Entity\Richiesta $richiesta
	 *
	 * Metodo richiamato contestualmente al passaggio della richiesta in istruttoria, tramite il pulsante VALIDA
	 * serve per popolare automaticamente lo STATO ATTUAZIONE PROGETTO; utile ai fine del monitoraggio
	 */
	protected function popolaStatoInizialeAttuazioneProgetto( Richiesta $richiesta){

		// DESTINAZIONE
		$statoAttuazioneProgettoMon = new RichiestaStatoAttuazioneProgetto();

		$statoAttuazioneProgettoMon->setRichiesta($richiesta);

		// la data deve essere aggiornata ad ogni rilevazione (FINE BIMESTRE PRECEDENTE) (una sola volta)
		$dataRiferimento = new \DateTime();
		$dataRiferimento->modify('last day of previous month');
		$dataRiferimento->format( 'Y-m-d' );


		// STATO PROGETTO - TC47StatoProgetto - STATO INIZIALE

		$em = $this->getEm();
		$statoInizialeProgetto = $em->getRepository("MonitoraggioBundle\Entity\TC47StatoProgetto")->findBy(array("descr_stato_prg" => "In Corso di esecuzione"));

		$statoAttuazioneProgettoMon->setStatoProgetto($statoInizialeProgetto[0]);
		$statoAttuazioneProgettoMon->setDataRiferimento($dataRiferimento);

		// setto la DESTINAZIONE nella richiesta
		$richiesta->addMonStatoProgetti($statoAttuazioneProgettoMon);

	}

	/**
	 * @param \RichiesteBundle\Entity\Richiesta $richiesta
	 *
	 * Metodo richiamato contestualmente al passaggio della richiesta in istruttoria, tramite il pulsante VALIDA
	 * serve per popolare automaticamente L'IMPORTO REALIZZATO e DA REALIZZARE suddiviso per ANNO; utile ai fini del monitoraggio
	 */
	protected function popolaPianoCosti( Richiesta $richiesta){

		// DESTINAZIONE
		$pianoCostiMon = new RichiestaPianoCosti();

		// RICHIESTA
		$pianoCostiMon->setRichiesta($richiesta);

		$importoDaRealizzare = $richiesta->getIstruttoria()->getCostoAmmesso();

		$pianoCostiMon->setAnnoPiano(date("Y"));        // data_avvio di ATTUAZIONE_CONTROLLO_RICHIESTE è VUOTA,
		// Impostare per TUTTI I PROGETTI la DATA IMPEGNO (in fase di sviluppo); imposto ANNO_CORRENTE

		$pianoCostiMon->setImportoRealizzato(0.00);     // 0.00 inizialmente

		$pianoCostiMon->setImportoDaRealizzare($importoDaRealizzare);   // ISTRUTTORIE_RICHIESTE --> COSTO AMMESSO

		// setto la DESTINAZIONE nella richiesta
		$richiesta->addMonPianoCosti($pianoCostiMon);
	}


	/**
	 * @param \RichiesteBundle\Entity\Richiesta $richiesta
	 *
	 * Metodo richiamato contestualmente al passaggio della richiesta in istruttoria, tramite il pulsante VALIDA
	 * serve per popolare automaticamente le VOCI SPESA; utile ai fini del monitoraggio
	 */
	protected function popolaVociSpesa( Richiesta $richiesta){

		$em = $this->getEm();
		$vociPianoCosto = $richiesta->getVociPianoCosto();      // VocePianoCosto (N elementi)

		foreach ($vociPianoCosto as $vocePianoCosto) {
			$voceSpesaMon = new VoceSpesa($richiesta);

			$istruttoriaVocePianoCosto = $vocePianoCosto->getIstruttoria();
			$istruttoriaImportoVoce = $istruttoriaVocePianoCosto->sommaImporti();

			$totaleVocePianoCosto = is_null($istruttoriaImportoVoce) ? 0.00 :$istruttoriaImportoVoce;

			$tipoVoceSpesa = $vocePianoCosto->getPianoCosto()->getTipoVoceSpesa();   

			$codiceTipoVoceSpesa = $tipoVoceSpesa->getCodice();     // PROG, SUOLO, MURARIE, OPERA BENI, TOTALE

			$tc37VoceSpesa = $em->getRepository("MonitoraggioBundle\Entity\TC37VoceSpesa")->findBy(array("voce_spesa" => $codiceTipoVoceSpesa));

			if(count($tc37VoceSpesa) > 0){
				$voceSpesaMon->setTipoVoceSpesa($tc37VoceSpesa[0]);      // TC37 - VOCI SPESA
				$voceSpesaMon->setImporto($totaleVocePianoCosto);

				// setto la DESTINAZIONE nella richiesta
				$richiesta->addMonVoceSpesa($voceSpesaMon);
			}
		}
	}


        /**
     * @param \RichiesteBundle\Entity\Richiesta $richiesta
     *
     * Metodo richiamato contestualmente al passaggio della richiesta in ATTUAZIONE, tramite il pulsante VALIDA
     * serve per popolare automaticamente le info sui SOGGETTI CORRELATI; utile ai fini del monitoraggio
     */
    protected function popolaSoggettiCollegati( Richiesta $richiesta){

        $em = $this->getEm();

        // Il "programmatore" (RER) è il codice tipo resp procedura e denom resp procedura della PA00

        // REGIONE EMILIA ROMAGNA

        $soggettoProgrammatore = $em->getRepository('SoggettoBundle:Soggetto')->findOneBy(array("denominazione" => "Regione Emilia-Romagna", "forma_giuridica" => 42));
        if (is_null($soggettoProgrammatore)) {
            throw new SfingeException('Risorsa non trovata');
        }
        $soggettoCollegatoProgrammatore = new SoggettiCollegati($richiesta,$soggettoProgrammatore);


        $ruoloSoggettoProgrammatore = $em->getRepository("MonitoraggioBundle\Entity\TC24RuoloSoggetto")->findOneBy(array("cod_ruolo_sog" => 1));

        $soggettoCollegatoProgrammatore->setTc24RuoloSoggetto($ruoloSoggettoProgrammatore);  // Programmatore del progetto
		$soggettoCollegatoProgrammatore->setCodUniIpa(SoggettiCollegati::COD_UNI_IPA_ER);

        $richiesta->addMonSoggettiCorrelati($soggettoCollegatoProgrammatore);

        // ------------------------------------------------------------------ //

        // BENEFICIARIO del progetto
        $soggettoCollegatoBeneficiario = new SoggettiCollegati();
        $soggettoCollegatoBeneficiario->setRichiesta($richiesta);

        // Il beneficiario è il MANDATARIO del progetto
        $soggettoBeneficiario = $richiesta->getSoggetto();
        $soggettoCollegatoBeneficiario->setSoggetto($soggettoBeneficiario);

        $ruoloSoggettoBeneficiario = $em->getRepository("MonitoraggioBundle:TC24RuoloSoggetto")->findOneBy(array("cod_ruolo_sog" => 2));

        $soggettoCollegatoBeneficiario->setTc24RuoloSoggetto($ruoloSoggettoBeneficiario);    // Beneficiario del progetto

        $richiesta->addMonSoggettiCorrelati($soggettoCollegatoBeneficiario);

        return;
    }
}
