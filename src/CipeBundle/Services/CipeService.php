<?php

namespace CipeBundle\Services;

/*
 * Il Cipe possiede più web services. 
 * Questa classe rappresenta un singleton che permette di chiamare in modo trasparente qualsiasi web-service esposto e registrato in esso
 * con eventuali medesime credenziali.
 * Attualmente è disponibile solo in servizio WsGeneraCup.
 * 
 * 
 */

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Services\WsGeneraCupService;

use CipeBundle\Entity\AttivEconomicaBeneficiarioAteco2007;
use CipeBundle\Entity\ConcessioneContributiNoUnitaProduttive;
use CipeBundle\Entity\ConcessioneIncentiviUnitaProduttive;
use CipeBundle\Entity\AcquistoBeni;
use CipeBundle\Entity\LavoriPubblici;
use CipeBundle\Entity\RealizzAcquistoServiziRicerca;
use CipeBundle\Entity\RealizzAcquistoServiziNoFormazioneRicerca;
use CipeBundle\Entity\RealizzAcquistoServiziFormazione;
use CipeBundle\Entity\PartecipAzionarieConferimCapitale;
use CipeBundle\Entity\CupGenerazione;
use CipeBundle\Entity\DatiGeneraliProgetto;
use CipeBundle\Entity\Descrizione;
use CipeBundle\Entity\DettaglioCup;
use CipeBundle\Entity\Localizzazione;
use CipeBundle\Entity\Finanziamento;
use CipeBundle\Entity\RichiestaCupGenerazione;
use CipeBundle\Entity\RispostaCupGenerazione;
use CipeBundle\Entity\DettaglioEleborazione;
use CipeBundle\Entity\WsGeneraCup;
use CipeBundle\Services\CupBatchService;
use CipeBundle\Entity\Aggregazioni\DatiRichiesta;
use GeoBundle\Entity\GeoComune;
use CipeBundle\Entity\Tracciati\TracciatoBatchNatureTipologie;




/**
 * Description of CipeService
 * @see http://cb.schema31.it/cb/issue/177624
 
 * Diagramma delle classi
 * @see http://cb.schema31.it/cb/issue/173380 
 *
 * @author gaetanoborgosano
 */
class CipeService {
	
	const TIPOIND			= '05';
	const DESCSTRUMPROGR	= "POR FESR EMILIA_ROMAGNA 2014-2020 - ";
	const STRUMPROGR		= '99';
	const CUMULATIVO		= 'N';
	const MODE				= "BATCH";
	
	/**
	 * @var ContainerInterface
	 * @see http://cb.schema31.it/cb/issue/177624
	 */
	protected $container;
	protected function getContainer() { return $this->container; }
	protected function setContainer($container) { $this->container = $container; }
	protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
	
	/**
	 * @var Registry
	 */
	protected $doctrine;
	protected function getDoctrine() { return $this->doctrine; }
	protected function setDoctrine($doctrine) { $this->doctrine = $doctrine; }
	protected function getEm() { return $this->getDoctrine()->getManager(); }
	
	/**
	 * @var CipeEntityService
	 */
	protected $CipeEntityService;
	protected function getCipeEntityService() { return $this->CipeEntityService; }
	protected function setCipeEntityService(CipeEntityService $CipeEntityService) { $this->CipeEntityService = $CipeEntityService; }

	/**
	 * @var DatiRichiesta
	 */
	protected $DatiRichiesta;
	function getDatiRichiesta() { return $this->DatiRichiesta; }
	function setDatiRichiesta(DatiRichiesta $DatiRichiesta) { $this->DatiRichiesta = $DatiRichiesta; }

		
	/**
	 * @var WsGeneraCupService 
	 */
	protected $WsGeneraCupService;
	protected function getWsGeneraCupService() { return $this->WsGeneraCupService; }
	protected function setWsGeneraCupService(WsGeneraCupService $WsGeneraCupService=null) { $this->WsGeneraCupService = $WsGeneraCupService; }
	public function getLastValidatorErrors() {
		return $this->getWsGeneraCupService()->getLastValidatorErrors();
	}
	public function hasValidatorErrors() {
		return $this->getWsGeneraCupService()->hasValidatorErrors();
	}
	
	/**
	 * @var CupBatchService
	 */
	protected $CupBatchService;
	function getCupBatchService() { return $this->CupBatchService; }
	function setCupBatchService(CupBatchService $CupBatchService) { $this->CupBatchService = $CupBatchService; }

		
	

	
	/**
	 * Utilizzata come comodo elemento copia per costruzione di RichiestaCupGenerazione di WsGeneraCup
	 * Una volta costruito viene settato in WsGeneraCup e dunque utilizzato nella chiama al Ws.
	 * @var RichiestaCupGenerazione 
	 */
	protected $RichiestaCupGenerazione;
	protected function getRichiestaCupGenerazione() { return $this->RichiestaCupGenerazione; }
	protected function setRichiestaCupGenerazione(RichiestaCupGenerazione $RichiestaCupGenerazione) {	$this->RichiestaCupGenerazione = $RichiestaCupGenerazione; }
	protected function initRichiestaCupGenerazione() {
		$RichiestaCupGenerazione = new RichiestaCupGenerazione();
		$this->setRichiestaCupGenerazione($RichiestaCupGenerazione);
		$this->initCredenzialiRichiestaCupGenerazione();
	}
		
	protected function initCredenzialiRichiestaCupGenerazione() {
		$User = $this->getParameter("cipe.user");
		$Password = $this->getParameter("cipe.password");
		$this->getRichiestaCupGenerazione()->setUser($User);
		$this->getRichiestaCupGenerazione()->setPassword($Password);
	}
	
	
	
	
	public function __construct($container, $doctrine, $CipeEntityService, $WsGeneraCupService, $CupBatchService) {
		$this->setContainer($container);
		$this->setDoctrine($doctrine);
		$this->setCipeEntityService($CipeEntityService);
		if(self::MODE == 'BATCH') $WsGeneraCupService = null;
		else $CupBatchService = null;
		$this->setWsGeneraCupService($WsGeneraCupService);
		$this->setCupBatchService($CupBatchService);
		$this->initRichiestaCupGenerazione();
	}

	
	// ---------------------------------------------------------

	
	/**
	 * <!ELEMENT LOCALIZZAZIONE EMPTY>
		<!ATTLIST LOCALIZZAZIONE
          stato CDATA #REQUIRED
          regione CDATA #REQUIRED
          provincia CDATA #REQUIRED
   	 * @return Localizzazione
	 */
	protected function buildLocalizzazione(
											$stato,
											$regione,
											$provincia,
											$comune
										) {
		$Localizzazione = new Localizzazione();
		if($stato == '101') {
			$stato = '05';
		}
		$Localizzazione->setStato($stato);
		$Localizzazione->setRegione($regione);
		$Localizzazione->setProvincia($provincia);
		$Localizzazione->setComune($comune);
		return $Localizzazione;
		
	}
	
	/**
	 * <!ELEMENT FINANZIAMENTO (CODICE_TIPOLOGIA_COP_FINANZ+)>
		<!ATTLIST FINANZIAMENTO
          sponsorizzazione (N | P | T) #IMPLIED
          finanza_progetto (A | N | P) #IMPLIED
          costo CDATA #REQUIRED
          finanziamento CDATA #REQUIRED>
		<!ELEMENT CODICE_TIPOLOGIA_COP_FINANZ (#PCDATA)*>
	 * @return Finanziamento
	 */
	protected function buildFinanziamento(
											$sponsorizzazione,
											$finanza_progetto,
											$costo,
											$finanziamento,
											$codici_tipologia_cop_finanz = array()
										) {
		$Finanziamento = new Finanziamento();
		$Finanziamento->setSponsorizzazione($sponsorizzazione);
		$Finanziamento->setFinanza_progetto($finanza_progetto);
		$Finanziamento->setCosto($costo);
		$Finanziamento->setFinanziamento($finanziamento);
		$Finanziamento->setCodici_tipologia_cop_finanz($codici_tipologia_cop_finanz);
		return $Finanziamento;
	}
	

	/**
	 * <!ATTLIST ACQUISTO_BENI
          nome_str_infrastr CDATA #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          bene CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
	 * @return AcquistoBeni
	 */
	protected function buildAcquistoBeni(
											$nome_str_infrastr,
											$tipo_ind_area_rifer,
											$ind_area_rifer,
											$bene,
											$strum_progr,
											$desc_strum_progr,
											$altre_informazioni=null,
											$flagLeggeObiettivo=null,
											$numDeliberaCipe=null,
											$annoDelibera=null
										) {
		$AcquistoBeni = new AcquistoBeni();
		$AcquistoBeni->setNome_str_infrastr($nome_str_infrastr);
		$AcquistoBeni->setTipo_ind_area_rifer($tipo_ind_area_rifer);
		$AcquistoBeni->setInd_area_rifer($ind_area_rifer);
		$AcquistoBeni->setBene($bene);
		$AcquistoBeni->setStrum_progr($strum_progr);
		$AcquistoBeni->setDesc_strum_progr($desc_strum_progr);
		$AcquistoBeni->setAltre_informazioni($altre_informazioni);
		$AcquistoBeni->setFlagLeggeObiettivo($flagLeggeObiettivo);
		$AcquistoBeni->setNumDeliberaCipe($numDeliberaCipe);
		$AcquistoBeni->setAnnoDelibera($annoDelibera);
		
		return $AcquistoBeni;
	}
	
	
	/**
	 * <!ELEMENT LAVORI_PUBBLICI EMPTY>
<!ATTLIST LAVORI_PUBBLICI
          nome_str_infrastr CDATA #REQUIRED
          str_infrastr_unica (SI | NO) #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          descrizione_intervento CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
	 * 
	 * @return LavoriPubblici
	 */
	public function buildLavoriPubblici(
										$nome_str_infrastr,
										$str_infrastr_unica,
										$tipo_ind_area_rifer,
										$ind_area_rifer,
										$descrizione_intervento,
										$strum_progr,
										$desc_strum_progr= null,
										$altre_informazioni = null,
										$flagLeggeObiettivo=null,
										$numDeliberaCipe=null,
										$annoDelibera=null
										) {
		$LavoriPubblici = new LavoriPubblici();
		$LavoriPubblici->setNome_str_infrastr($nome_str_infrastr);
		$LavoriPubblici->setStr_infrastr_unica($str_infrastr_unica);
		$LavoriPubblici->setTipo_ind_area_rifer($tipo_ind_area_rifer);
		$LavoriPubblici->setInd_area_rifer($ind_area_rifer);
		$LavoriPubblici->setDescrizione_intervento($descrizione_intervento);
		$LavoriPubblici->setStrum_progr($strum_progr);
		$LavoriPubblici->setDesc_strum_progr($desc_strum_progr);
		$LavoriPubblici->setAltre_informazioni($altre_informazioni);
		$LavoriPubblici->setFlagLeggeObiettivo($flagLeggeObiettivo);
		$LavoriPubblici->setNumDeliberaCipe($numDeliberaCipe);
		$LavoriPubblici->setAnnoDelibera($annoDelibera);
		return $LavoriPubblici;
	}
	
	
	
	/**
	 * <!ELEMENT CONCESSIONE_INCENTIVI_UNITA_PRODUTTIVE EMPTY>
		<!ATTLIST CONCESSIONE_INCENTIVI_UNITA_PRODUTTIVE
          denominazione_impresa_stabilimento CDATA #REQUIRED
          partita_iva CDATA #REQUIRED
          denominazione_impresa_stabilimento_prec CDATA #IMPLIED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          descrizione_intervento CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
	 * @return ConcessioneIncentiviUnitaProduttive
	 */
	protected function buildConcessioneIncentiviUnitaProduttive(
																$denominazione_impresa_stabilimento,
																$partita_iva,
																$denominazione_impresa_stabilimento_prec,
																$tipo_ind_area_rifer,
																$ind_area_rifer,
																$descrizione_intervento,
																$strum_progr,
																$desc_strum_progr	= null,
																$altre_informazioni = null,
																$flagLeggeObiettivo = null,
																$numDeliberaCipe	= null,
																$annoDelibera		= null
																) {
		$ConcessioneIncentiviUnitaProduttive = new ConcessioneIncentiviUnitaProduttive();
		$ConcessioneIncentiviUnitaProduttive->setDenominazione_impresa_stabilimento($denominazione_impresa_stabilimento);
		$ConcessioneIncentiviUnitaProduttive->setPartita_iva($partita_iva);
		$ConcessioneIncentiviUnitaProduttive->setDenominazione_impresa_stabilimento_prec($denominazione_impresa_stabilimento_prec);
		$ConcessioneIncentiviUnitaProduttive->setTipo_ind_area_rifer($tipo_ind_area_rifer);
		$ConcessioneIncentiviUnitaProduttive->setInd_area_rifer($ind_area_rifer);
		$ConcessioneIncentiviUnitaProduttive->setDescrizione_intervento($descrizione_intervento);
		$ConcessioneIncentiviUnitaProduttive->setStrum_progr($strum_progr);
		$ConcessioneIncentiviUnitaProduttive->setDesc_strum_progr($desc_strum_progr);
		$ConcessioneIncentiviUnitaProduttive->setAltre_informazioni($altre_informazioni);
		$ConcessioneIncentiviUnitaProduttive->setFlagLeggeObiettivo($flagLeggeObiettivo);
		$ConcessioneIncentiviUnitaProduttive->setNumDeliberaCipe($numDeliberaCipe);
		$ConcessioneIncentiviUnitaProduttive->setAnnoDelibera($annoDelibera);
		return $ConcessioneIncentiviUnitaProduttive;
		
	}
	
	/**
	 * <!ELEMENT CONCESSIONE_CONTRIBUTI_NO_UNITA_PRODUTTIVE EMPTY>
		<!ATTLIST CONCESSIONE_CONTRIBUTI_NO_UNITA_PRODUTTIVE
          beneficiario CDATA #REQUIRED
          partita_iva CDATA #REQUIRED
          struttura CDATA #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          desc_intervento CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
		  annoDelibera CDATA #IMPLIED>
	 * @return ConcessioneContributiNoUnitaProduttive
	 */
	protected function buildConcessioneContributiNoUnitaProduttive(
																	$beneficiario,
																	$partita_iva,
																	$struttura,
																	$tipo_ind_area_rifer,
																	$ind_area_rifer,
																	$desc_intervento,
																	$strum_progr,
																	$desc_strum_progr	= null,
																	$altre_informazioni = null,
																	$flagLeggeObiettivo = null,
																	$numDeliberaCipe	= null,
																	$annoDelibera		= null			
																	) {
		$ConcessioneContributiNoUnitaProduttive = new ConcessioneContributiNoUnitaProduttive();
		$ConcessioneContributiNoUnitaProduttive->setBeneficiario($beneficiario);
		$ConcessioneContributiNoUnitaProduttive->setPartita_iva($partita_iva);
		$ConcessioneContributiNoUnitaProduttive->setStruttura($struttura);
		$ConcessioneContributiNoUnitaProduttive->setTipo_ind_area_rifer($tipo_ind_area_rifer);
		$ConcessioneContributiNoUnitaProduttive->setInd_area_rifer($ind_area_rifer);
		$ConcessioneContributiNoUnitaProduttive->setDesc_intervento($desc_intervento);
		$ConcessioneContributiNoUnitaProduttive->setStrum_progr($strum_progr);
		$ConcessioneContributiNoUnitaProduttive->setDesc_strum_progr($desc_strum_progr);
		$ConcessioneContributiNoUnitaProduttive->setAltre_informazioni($altre_informazioni);
		$ConcessioneContributiNoUnitaProduttive->setFlagLeggeObiettivo($flagLeggeObiettivo);
		$ConcessioneContributiNoUnitaProduttive->setNumDeliberaCipe($numDeliberaCipe);
		$ConcessioneContributiNoUnitaProduttive->setAnnoDelibera($annoDelibera);
		
		return $ConcessioneContributiNoUnitaProduttive;
		
	}
	
	/**
	 * <!ELEMENT PARTECIP_AZIONARIE_CONFERIM_CAPITALE EMPTY>
<!ATTLIST PARTECIP_AZIONARIE_CONFERIM_CAPITALE
          ragione_sociale CDATA #REQUIRED
          partita_iva CDATA #REQUIRED
          ragione_sociale_prec CDATA #IMPLIED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          finalita CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #REQUIRED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
	 * 
	 * @return PartecipAzionarieConferimCapitale
	 */
	protected function buildPartecipAzionarieConferimCapitale(
																$ragione_sociale,
																$partita_iva,
																$tipo_ind_area_rifer,
																$ind_area_rifer,
																$finalita,
																$strum_progr,
																$desc_strum_progr,
																$ragione_sociale_prec=null,
																$altre_informazioni=null,
																$flagLeggeObiettivo=null,
																$numDeliberaCipe=null,
																$annoDelibera=null
																) {
		$PartecipAzionarieConferimCapitale = new PartecipAzionarieConferimCapitale();

		$PartecipAzionarieConferimCapitale->setRagione_sociale($ragione_sociale);
		$PartecipAzionarieConferimCapitale->setPartita_iva($partita_iva);
		$PartecipAzionarieConferimCapitale->setRagione_sociale_prec($ragione_sociale_prec);
		$PartecipAzionarieConferimCapitale->setTipo_ind_area_rifer($tipo_ind_area_rifer);
		$PartecipAzionarieConferimCapitale->setInd_area_rifer($ind_area_rifer);
		
		// finalita
		$PartecipAzionarieConferimCapitale->setStrum_progr($strum_progr);
		$PartecipAzionarieConferimCapitale->setDesc_strum_progr($desc_strum_progr);
		$PartecipAzionarieConferimCapitale->setAltre_informazioni($altre_informazioni);
		$PartecipAzionarieConferimCapitale->setFlagLeggeObiettivo($flagLeggeObiettivo);
		$PartecipAzionarieConferimCapitale->setNumDeliberaCipe($numDeliberaCipe);
		$PartecipAzionarieConferimCapitale->setAnnoDelibera($annoDelibera);
		return $PartecipAzionarieConferimCapitale;
	}
	
	
	/**
	 * <!ELEMENT REALIZZ_ACQUISTO_SERVIZI_FORMAZIONE EMPTY>
<!ATTLIST REALIZZ_ACQUISTO_SERVIZI_FORMAZIONE
          denom_progetto CDATA #REQUIRED
          denom_ente_corso CDATA #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          obiett_corso CDATA #REQUIRED
          mod_intervento_frequenza CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #REQUIRED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
	 @return RealizzAcquistoServiziFormazione
	 */
	protected function buildRealizzAcquistoServiziFormazione(
																$denom_progetto,
																$denom_ente_corso,
																$tipo_ind_area_rifer,
																$ind_area_rifer,
																$obiett_corso,
																$mod_intervento_frequenza,
																$strum_progr,
																$desc_strum_progr,
																$altre_informazioni=null,
																$flagLeggeObiettivo=null,
																$numDeliberaCipe=null,
																$annoDelibera=null
															) {
		$RealizzAcquistoServiziFormazione = new RealizzAcquistoServiziFormazione();
		$RealizzAcquistoServiziFormazione->setDenom_progetto($denom_progetto);
		$RealizzAcquistoServiziFormazione->setDenom_ente_corso($denom_ente_corso);
		$RealizzAcquistoServiziFormazione->setTipo_ind_area_rifer($tipo_ind_area_rifer);
		$RealizzAcquistoServiziFormazione->setInd_area_rifer($ind_area_rifer);
		$RealizzAcquistoServiziFormazione->setObiett_corso($obiett_corso);
		$RealizzAcquistoServiziFormazione->setMod_intervento_frequenza($mod_intervento_frequenza);
		$RealizzAcquistoServiziFormazione->setStrum_progr($strum_progr);
		$RealizzAcquistoServiziFormazione->setDesc_strum_progr($desc_strum_progr);
		$RealizzAcquistoServiziFormazione->setAltre_informazioni($altre_informazioni);
		$RealizzAcquistoServiziFormazione->setFlagLeggeObiettivo($flagLeggeObiettivo);
		$RealizzAcquistoServiziFormazione->setNumDeliberaCipe($numDeliberaCipe);
		$RealizzAcquistoServiziFormazione->setAnnoDelibera($annoDelibera);
		return $RealizzAcquistoServiziFormazione;
	}
	
	
	
	/**
	 * <!ELEMENT REALIZZ_ACQUISTO_SERVIZI_NO_FORMAZIONE_RICERCA EMPTY>
		<!ATTLIST REALIZZ_ACQUISTO_SERVIZI_NO_FORMAZIONE_RICERCA
          nome_str_infrastr CDATA #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #REQUIRED
          ind_area_rifer CDATA #REQUIRED
          servizio CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
	 * @return RealizzAcquistoServiziNoFormazioneRicerca
	 */
	protected function buildRealizzAcquistoServiziNoFormazioneRicerca(
																		$nome_str_infrastr,
																		$tipo_ind_area_rifer,
																		$ind_area_rifer,
																		$servizio,
																		$strum_progr,
																		$desc_strum_progr,
																		$altre_informazioni=null,
																		$flagLeggeObiettivo=null,
																		$numDeliberaCipe=null,
																		$annoDelibera=null
																		) {
		
		$RealizzAcquistoServiziNoFormazioneRicerca = new RealizzAcquistoServiziNoFormazioneRicerca();
		$RealizzAcquistoServiziNoFormazioneRicerca->setNome_str_infrastr($nome_str_infrastr);
		$RealizzAcquistoServiziNoFormazioneRicerca->setTipo_ind_area_rifer($tipo_ind_area_rifer);
		$RealizzAcquistoServiziNoFormazioneRicerca->setInd_area_rifer($ind_area_rifer);
		$RealizzAcquistoServiziNoFormazioneRicerca->setServizio($servizio);
		$RealizzAcquistoServiziNoFormazioneRicerca->setStrum_progr($strum_progr);
		$RealizzAcquistoServiziNoFormazioneRicerca->setDesc_strum_progr($desc_strum_progr);
		$RealizzAcquistoServiziNoFormazioneRicerca->setAltre_informazioni($altre_informazioni);
		$RealizzAcquistoServiziNoFormazioneRicerca->setFlagLeggeObiettivo($flagLeggeObiettivo);
		$RealizzAcquistoServiziNoFormazioneRicerca->setNumDeliberaCipe($numDeliberaCipe);
		$RealizzAcquistoServiziNoFormazioneRicerca->setAnnoDelibera($annoDelibera);
		
		return $RealizzAcquistoServiziNoFormazioneRicerca;
	}
	
	/**
	 * <!ELEMENT REALIZZ_ACQUISTO_SERVIZI_RICERCA EMPTY>
		<!ATTLIST REALIZZ_ACQUISTO_SERVIZI_RICERCA
          denominazione_progetto CDATA #REQUIRED
          ente CDATA #REQUIRED
          tipo_ind_area_rifer (01 | 02 | 03 | 04 | 05) #IMPLIED
          ind_area_rifer CDATA #IMPLIED
          descrizione_intervento CDATA #REQUIRED
          strum_progr CDATA #REQUIRED
          desc_strum_progr CDATA #IMPLIED
          altre_informazioni CDATA #IMPLIED
          flagLeggeObiettivo CDATA #IMPLIED
          numDeliberaCipe CDATA #IMPLIED
          annoDelibera CDATA #IMPLIED>
	* @return RealizzAcquistoServiziRicerca
	 */
	protected function buildRealizzAcquistoServiziRicerca(
															$denominazione_progetto,
															$ente,
															$tipo_ind_area_rifer,
															$ind_area_rifer,
															$descrizione_intervento,
															$strum_progr,
															$desc_strum_progr,
															$altre_informazioni=null,
															$flagLeggeObiettivo=null,
															$numDeliberaCipe=null,
															$annoDelibera=null
															) {
		$RealizzAcquistoServiziRicerca = new RealizzAcquistoServiziRicerca();
		
		$RealizzAcquistoServiziRicerca->setDenominazione_progetto($denominazione_progetto);
		$RealizzAcquistoServiziRicerca->setEnte($ente);
		$RealizzAcquistoServiziRicerca->setTipo_ind_area_rifer($tipo_ind_area_rifer);
		$RealizzAcquistoServiziRicerca->setInd_area_rifer($ind_area_rifer);
		$RealizzAcquistoServiziRicerca->setDescrizione_intervento($descrizione_intervento);
		$RealizzAcquistoServiziRicerca->setStrum_progr($strum_progr);
		$RealizzAcquistoServiziRicerca->setDesc_strum_progr($desc_strum_progr);
		$RealizzAcquistoServiziRicerca->setAltre_informazioni($altre_informazioni);
		$RealizzAcquistoServiziRicerca->setFlagLeggeObiettivo($flagLeggeObiettivo);
		$RealizzAcquistoServiziRicerca->setNumDeliberaCipe($numDeliberaCipe);
		$RealizzAcquistoServiziRicerca->setAnnoDelibera($annoDelibera);
		
		return $RealizzAcquistoServiziRicerca;
	}
	
	/**
	 * <!ELEMENT DATI_GENERALI_PROGETTO EMPTY>
		<!ATTLIST DATI_GENERALI_PROGETTO
          anno_decisione CDATA #REQUIRED
          cumulativo (N | S) "N"
          codifica_locale CDATA #IMPLIED
          natura CDATA #REQUIRED
          tipologia CDATA #REQUIRED
          settore CDATA #REQUIRED
          sottosettore CDATA #REQUIRED
          categoria CDATA #REQUIRED
          cpv1 CDATA #IMPLIED
          cpv2 CDATA #IMPLIED
          cpv3 CDATA #IMPLIED
          cpv4 CDATA #IMPLIED
          cpv5 CDATA #IMPLIED
          cpv6 CDATA #IMPLIED
          cpv7 CDATA #IMPLIED>
	 * @return DatiGeneraliProgetto
	 */
	protected function buildDatiGeneraliProgetto(
												$anno_decisione,
												$cumulativo,
												$codifica_locale = null,
												$natura,
												$tipologia,
												$settore,
												$sottosettore,
												$categoria,
												$cpv1			= null,
												$cpv2			= null,
												$cpv3			= null,
												$cpv4			= null,
												$cpv5			= null,
												$cpv6			= null,
												$cpv7			= null
												) {
		$DatiGeneraliProgetto = new DatiGeneraliProgetto();
		$DatiGeneraliProgetto->setAnno_decisione($anno_decisione);
		$DatiGeneraliProgetto->setCumulativo($cumulativo);
		$DatiGeneraliProgetto->setCodifica_locale($codifica_locale);
		$DatiGeneraliProgetto->setNatura($natura);
		$DatiGeneraliProgetto->setTipologia($tipologia);
		$DatiGeneraliProgetto->setSettore($settore);
		$DatiGeneraliProgetto->setSottosettore($sottosettore);
		$DatiGeneraliProgetto->setCategoria($categoria);
		$DatiGeneraliProgetto->setCpv1($cpv1);
		$DatiGeneraliProgetto->setCpv2($cpv2);
		$DatiGeneraliProgetto->setCpv3($cpv3);
		$DatiGeneraliProgetto->setCpv4($cpv4);
		$DatiGeneraliProgetto->setCpv5($cpv5);
		$DatiGeneraliProgetto->setCpv6($cpv7);
		$DatiGeneraliProgetto->setCpv7($cpv1);
		
		return $DatiGeneraliProgetto;
	}
	
	
	
	
	/**
	 * <!ELEMENT ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007 EMPTY>
		<!ATTLIST ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007
          sezione CDATA #IMPLIED
          divisione CDATA #IMPLIED
          gruppo CDATA #IMPLIED
          classe CDATA #IMPLIED
          categoria CDATA #IMPLIED
          sottocategoria CDATA #IMPLIED>
	 * @return AttivEconomicaBeneficiarioAteco2007
	 */
	protected function buildAttivEconomicaBeneficiarioAteco2007(
																$sezione				= null,
																$divisione				= null,
																$gruppo					= null,
																$classe					= null,
																$Ateco_categoria		= null,
																$Ateco_sottocategoria	= null
																) {
		$AttivEconomicaBeneficiarioAteco2007 = new AttivEconomicaBeneficiarioAteco2007();
		$AttivEconomicaBeneficiarioAteco2007->setSezione($sezione);
		$AttivEconomicaBeneficiarioAteco2007->setDivisione($divisione);
		$AttivEconomicaBeneficiarioAteco2007->setGruppo($gruppo);
		$AttivEconomicaBeneficiarioAteco2007->setClasse($classe);
		$AttivEconomicaBeneficiarioAteco2007->setCategoria($Ateco_categoria);
		$AttivEconomicaBeneficiarioAteco2007->setSottocategoria($Ateco_sottocategoria);
		return $AttivEconomicaBeneficiarioAteco2007;
	}
	

	
	public function getTipoDescrizione($natura, $tipologia) {
		$tipoDescrizione = null;		
		$criteria = array("codiceNatura" => $natura, "codiceTipologia" => $tipologia);
		/* @var $TracciatoBatchNatureTipologie TracciatoBatchNatureTipologie */
		$TracciatoBatchNatureTipologie = $this->getDoctrine()->getRepository("CipeBundle\Entity\Tracciati\TracciatoBatchNatureTipologie")->findOneBy($criteria);
		if(!\is_null($TracciatoBatchNatureTipologie)) $tipoDescrizione = $TracciatoBatchNatureTipologie->getTipoDescrizione();
		
		return $tipoDescrizione;
	}
	
	
	/**
	 * * <!ELEMENT CUP_GENERAZIONE (DATI_GENERALI_PROGETTO, MASTER?, LOCALIZZAZIONE+, DESCRIZIONE, ATTIV_ECONOMICA_BENEFICIARIO_ATECO_2007?, FINANZIAMENTO)>
		<!ATTLIST CUP_GENERAZIONE
          soggetto_titolare CDATA #IMPLIED
          uo_soggetto_titolare CDATA #IMPLIED
          user_titolare CDATA #IMPLIED
          id_progetto CDATA #REQUIRED>
	 * @return CupGenerazione
	 * @throws \Exception
	 */
	protected function buildCupGenerazione(
											$soggetto_titolare,
											$uo_soggetto_titolare,
											$user_titolare,
											$id_progetto
											) {
		try {
			$datiRichiesta		= $this->getDatiRichiesta();
			$anno_decisione		= $datiRichiesta->getAnno_decisione();

			$cumulativo			= $datiRichiesta->getCumulativo();
			$codifica_locale	= $datiRichiesta->getCodifica_locale();
			$natura				= $datiRichiesta->getNatura();
			$this->getRichiestaCupGenerazione ()->assSharedElement("natura", $natura);
			//   ---------------    TEST	---------------
//			$natura = "06";
			//   ---------------    TEST	---------------

			$tipologia			= $datiRichiesta->getTipologia();
			$settore			= $datiRichiesta->getSettore();
			$sottosettore		= $datiRichiesta->getSottosettore();
			$categoria			= $datiRichiesta->getCategoria();
			$this->getRichiestaCupGenerazione()->assSharedElement("natura", $natura);
			if($cumulativo != 'N') $this->getRichiestaCupGenerazione ()->assSharedElement("cup_cumulativo", $cumulativo);
			
			$DatiGeneraliProgetto  = $this->buildDatiGeneraliProgetto(
																		$anno_decisione, 
																		$cumulativo, 
																		$codifica_locale, 
																		$natura, 
																		$tipologia, 
																		$settore, 
																		$sottosettore, 
																		$categoria /*, 
																		$cpv1,
																		$cpv2, 
																		$cpv3, 
																		$cpv4, 
																		$cpv5, 
																		$cpv6, 
																		$cpv7 */
																	);
			
			$stato		= $datiRichiesta->getStato();
			$regione	= $datiRichiesta->getRegione();
			$provincia	= $datiRichiesta->getProvincia();
			$comune		= $datiRichiesta->getComune();
			$Localizzazione = $this->buildLocalizzazione($stato, $regione, $provincia, $comune);
			$Descrizione = new Descrizione();
			// DEFINIZIONE DELLA DESCRIZIONE IN BASE ALLA NATURA
			
			$beneficiario								= $datiRichiesta->getBenficiario();
			$partita_iva								= $datiRichiesta->getPartita_iva();
			$struttura									= $datiRichiesta->getStruttura();				
			$tipo_ind_area_rifer						= $datiRichiesta->getTipo_ind_area_rifer();			
			$ind_area_rifer								= $datiRichiesta->getInd_area_rifer();			
			$desc_intervento							= $datiRichiesta->getDescr_intervento();		
			$strum_progr								= is_null($datiRichiesta->getStrum_progr()) || $datiRichiesta->getStrum_progr() == "" ? "99" : $datiRichiesta->getStrum_progr();			
			$desc_strum_progr							= is_null($datiRichiesta->getDescr_strum_progr()) || $datiRichiesta->getDescr_strum_progr() == "" ? "POR FESR EMILIA_ROMAGNA 2014-2020" : $datiRichiesta->getDescr_strum_progr();				
			$altre_informazioni							= $datiRichiesta->getAltre_informazioni();		
			$nome_str_infrastr							= $datiRichiesta->getNome_str_infrastr();
			$denominazione_impresa_stabilimento			= $datiRichiesta->getDenominazione_impresa_stabilimento();
			$denominazione_impresa_stabilimento_prec	= $datiRichiesta->getDenominazione_impresa_stabilimento_prec();
			$descrizione_intervento						= $datiRichiesta->getDescr_intervento();
			$bene										= $datiRichiesta->getBene();
			$str_infrastr_unica							= $datiRichiesta->getStr_infrastr_unica();
			$ragione_sociale							= $datiRichiesta->getRagione_sociale();
			$ragione_sociale_prec						= $datiRichiesta->getRagione_sociale_prec();
			$finalita									= $datiRichiesta->getFinalita();
			$denom_progetto								= $datiRichiesta->getDenom_progetto();
			$denom_ente_corso							= $datiRichiesta->getDenom_ente_corso();
			$obiett_corso								= $datiRichiesta->getObiett_corso();
			$mod_intervento_frequenza					= $datiRichiesta->getMod_intervento_frequenza();
			$servizio									= $datiRichiesta->getServizio();
			$denominazione_progetto						= $datiRichiesta->getDenom_progetto();
			$ente										= $datiRichiesta->getEnte();
			
			$tipoDescrizione = $this->getTipoDescrizione($natura, $tipologia);
			
			switch ($tipoDescrizione) {
				
				case 'ACQUISTO_BENI':
					$AcquistoBeni = $this->buildAcquistoBeni(
																$nome_str_infrastr, 
																$tipo_ind_area_rifer, 
																$ind_area_rifer, 
																$bene, 
																$strum_progr, 
																$desc_strum_progr, 
																$altre_informazioni
															);
					$Descrizione->setAcquistoBeni($AcquistoBeni);
					break;
				
				
				case 'REALIZZ_ACQUISTO_SERVIZI_NO_FORMAZIONE_RICERCA':
					$RealizzAcquistoServiziNoFormazioneRicerca = $this->buildRealizzAcquistoServiziNoFormazioneRicerca(
																		$nome_str_infrastr,
																		$tipo_ind_area_rifer,
																		$ind_area_rifer,
																		$servizio,
																		$strum_progr,
																		$desc_strum_progr,
																		$altre_informazioni
//																		$flagLeggeObiettivo=null,
//																		$numDeliberaCipe=null,
//																		$annoDelibera=null
																		);
					
					$Descrizione->setRealizzAcquistoServiziNoFormazioneRicerca($RealizzAcquistoServiziNoFormazioneRicerca);
					
					break;

				
				case 'REALIZZ_ACQUISTO_SERVIZI_RICERCA':
					$RealizzAcquistoServiziRicerca = $this->buildRealizzAcquistoServiziRicerca(
															$denominazione_progetto,
															$ente,
															$tipo_ind_area_rifer,
															$ind_area_rifer,
															$descrizione_intervento,
															$strum_progr,
															$desc_strum_progr,
															$altre_informazioni
//															$flagLeggeObiettivo=null,
//															$numDeliberaCipe=null,
//															$annoDelibera=null
															);
					$Descrizione->setRealizzAcquistoServiziRicerca($RealizzAcquistoServiziRicerca);
					break;
				
				
				case 'REALIZZ_ACQUISTO_SERVIZI_FORMAZIONE':
					$RealizzAcquistoServiziFormazione = $this->buildRealizzAcquistoServiziFormazione(
																$denom_progetto,
																$denom_ente_corso,
																$tipo_ind_area_rifer,
																$ind_area_rifer,
																$obiett_corso,
																$mod_intervento_frequenza,
																$strum_progr,
																$desc_strum_progr,
																$altre_informazioni
//																$flagLeggeObiettivo=null,
//																$numDeliberaCipe=null,
//																$annoDelibera=null
															);
					$Descrizione->setRealizzAcquistoServiziFormazione($RealizzAcquistoServiziFormazione);
					break;
				
				// LavoriPubblici
				case 'LAVORI_PUBBLICI':
					$LavoriPubblici = $this->buildLavoriPubblici(
																	$nome_str_infrastr, 
																	$str_infrastr_unica, 
																	$tipo_ind_area_rifer, 
																	$ind_area_rifer, 
																	$descrizione_intervento, 
																	$strum_progr, 
																	$desc_strum_progr, 
																	$altre_informazioni
																);
					$Descrizione->setLavoriPubblici($LavoriPubblici);
					break;
				
				// ConcessioneContributiNoUnitaProduttive
				case 'CONCESSIONE_CONTRIBUTI_NO_UNITA_PRODUTTIVE':
					$ConcessioneContributiNoUnitaProduttive = $this->buildConcessioneContributiNoUnitaProduttive(
																		$beneficiario, 
																		$partita_iva, 
																		$struttura, 
																		$tipo_ind_area_rifer, 
																		$ind_area_rifer, 
																		$desc_intervento, 
																		$strum_progr, 
																		$desc_strum_progr, 
																		$altre_informazioni /*, 
																		$flagLeggeObiettivo
																		$numDeliberaCipe,
																		$annoDelibera*/
																		
																		);
					$Descrizione->setConcessioneContributiNoUnitaProduttive($ConcessioneContributiNoUnitaProduttive);
					break;
				
				case 'CONCESSIONE_INCENTIVI_UNITA_PRODUTTIVE':
					$ConcessioneIncentiviUnitaProduttive = $this->buildConcessioneIncentiviUnitaProduttive(
																$denominazione_impresa_stabilimento,
																$partita_iva,
																$denominazione_impresa_stabilimento_prec,
																$tipo_ind_area_rifer,
																$ind_area_rifer,
																$descrizione_intervento,
																$strum_progr,
																$desc_strum_progr	,
																$altre_informazioni /*,
																$flagLeggeObiettivo ,
																$numDeliberaCipe	,
																$annoDelibera		*/
																);
					$Descrizione->setConcessioneIncentiviUnitaProduttive($ConcessioneIncentiviUnitaProduttive);
					


					break;
				
				case 'PARTECIP_AZIONARIE_CONFERIM_CAPITALE':
					$PartecipAzionarieConferimCapitale = $this->buildPartecipAzionarieConferimCapitale(
																$ragione_sociale,
																$partita_iva,
																$tipo_ind_area_rifer,
																$ind_area_rifer,
																$finalita,
																$strum_progr,
																$desc_strum_progr,
																$ragione_sociale_prec,
																$altre_informazioni
//																$flagLeggeObiettivo=null,
//																$numDeliberaCipe=null,
//																$annoDelibera=null
																);
					$Descrizione->setPartecipAzionarieConferimCapitale($PartecipAzionarieConferimCapitale);
					
					break;

				default:
					throw new \Exception('impossibile identificare il tipo di descrizione del tracciato');
					break;
			}
			
			$sezione	= $datiRichiesta->getAteco_sezione();
			$divisione	= $datiRichiesta->getAteco_divisione();
			$gruppo		= $datiRichiesta->getAteco_gruppo();
			$classe		= $datiRichiesta->getAteco_classe();
			
			$Ateco_categoria		= $datiRichiesta->getAteco_categoria();
			$Ateco_sottocategoria	= $datiRichiesta->getAteco_sottocategoria();
			
			
			$AttivEconomicaBeneficiarioAteco2007 = $this->buildAttivEconomicaBeneficiarioAteco2007(
																									$sezione,
																									$divisione,
																									$gruppo,
																									$classe,
																									$Ateco_categoria,
																									$Ateco_sottocategoria
																							);
			
			
			$costo							= round($datiRichiesta->getCosto() / 1000, 3, PHP_ROUND_HALF_UP);
			$finanziamento					= round($datiRichiesta->getFinanziamento() / 1000, 3, PHP_ROUND_HALF_UP);
			$codici_tipologia_cop_finanz	= $datiRichiesta->getCodici_tipologia_cop_finanz();
			$sponsorizzazione				= $datiRichiesta->getSponsorizzazione();
			$finanza_progetto				= $datiRichiesta->getFinanza_progetto();
			$Finanziamento = $this->buildFinanziamento(
														$sponsorizzazione,
														$finanza_progetto,
														$costo,
														$finanziamento,
														$codici_tipologia_cop_finanz 
														);
			
			$CupGenerazione = new CupGenerazione();
			$CupGenerazione->setAttivEconomicaBeneficiarioAteco2007($AttivEconomicaBeneficiarioAteco2007);
			$CupGenerazione->setDatiGeneraliProgetto($DatiGeneraliProgetto);
			$CupGenerazione->setDescrizione($Descrizione);
			$CupGenerazione->setFinanziamento($Finanziamento);
			
			$CupGenerazione->setLocalizzazione($Localizzazione);
			$CupGenerazione->setSoggetto_titolare($soggetto_titolare);
			$CupGenerazione->setUo_soggetto_titolare($uo_soggetto_titolare);
			$CupGenerazione->setUser_titolare($user_titolare);
			$CupGenerazione->setId_progetto($id_progetto);
			
			return $CupGenerazione;
			
		} catch (\Exception $ex) {
			throw $ex;
		}
		

	}
	
	protected function preparareRichiestaCupGenerazione($IdRichiesta) {
		try {
			$this->getRichiestaCupGenerazione()->setIdRichiesta($IdRichiesta);
			$soggetto_titolare = $uo_soggetto_titolare = $user_titolare = null; // TODO
			$id_progetto = $this->getDatiRichiesta()->getIdProgetto();
			$CupGenerazione = $this->buildCupGenerazione($soggetto_titolare, $uo_soggetto_titolare, $user_titolare, $id_progetto);
			$this->getRichiestaCupGenerazione()->setIdRichiesta($IdRichiesta);
			$this->getWsGeneraCupService()->getWsGeneraCup()->setIdRichiesta($IdRichiesta);
			$this->getWsGeneraCupService()->getWsGeneraCup()->setIdProgetto($id_progetto);
			$timestampRichiesta = new \DateTime("NOW");
			$this->getRichiestaCupGenerazione()->setCupGenerazione($CupGenerazione);
			$RichiestaCupGenerazione = $this->getRichiestaCupGenerazione();
			$this->getWsGeneraCupService()->getWsGeneraCup()->setRichiestaCupGenerazione($RichiestaCupGenerazione);
			$this->getWsGeneraCupService()->getWsGeneraCup()->setTimeStampRichiesta($timestampRichiesta);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	/**
	 * 
	 * @param DatiRichiesta $DatiRichiesta
	 * @return CupGenerazione
	 * @throws \Exception
	 */
	protected function BuildRichiestaCupGenerazioneFromDatiRichiesta(DatiRichiesta $DatiRichiesta) {
		try {
			$soggetto_titolare = $uo_soggetto_titolare = $user_titolare = null; // TODO
			$id_progetto = $DatiRichiesta->getIdProgetto();
			$this->setDatiRichiesta($DatiRichiesta);
			$CupGenerazione = $this->buildCupGenerazione($soggetto_titolare, $uo_soggetto_titolare, $user_titolare, $id_progetto);
			return $CupGenerazione;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	
	
	
	protected function getNextIdRichiesta() {
		try {
			$query = "SELECT MAX(gc.idRichiesta) as maxIdRichiesta FROM CipeBundle\Entity\WsGeneraCup gc WHERE gc.timeStampRisposta IS NOT NULL";
			$q = $this->getEm()->createQuery($query);
			$max = $q->getSingleScalarResult();
			$next = (\is_null($max)) ? 1 : intval($max)+1;
			return $next;
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	
	protected function richiestaInoltrataPositivaIdProgetto($idProgetto) {
		try {
			$query = "SELECT gc.idProgetto FROM CipeBundle\Entity\WsGeneraCup gc WHERE gc.idProgetto=$idProgetto AND gc.esito = 1";
			$q = $this->getEm()->createQuery($query);
			$result = $q->getResult();
			$st = (!\is_null(count($result) > 0)) ? true : false;
			return $st;
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	protected function salvaWsGeneraCup() {
		try {
			$WsGeneraCup = $this->getWsGeneraCupService()->getWsGeneraCup();
			$this->getEm()->persist($WsGeneraCup);
			$this->getEm()->flush();
			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}
		
	}
	
	
	/**
	 * 
	 * @param $xmlRisposta
	 * @return RispostaCupGenerazione
	 * @throws \Exception
	 */
	protected function buildRispostaCupGenerazioneFromXmlRisposta($xmlRisposta) {
		try {
			$RispostaCupGenerazione = new RispostaCupGenerazione();
			$RispostaCupGenerazione = $RispostaCupGenerazione->deserialize($xmlRisposta);
			
			return $RispostaCupGenerazione;
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


		
	
	/**
	 * 
	 * @param DatiRichiesta $DatiRichiesta 
	 *	la struttura DatiRichiesta è adibita allo scambio dati. 
	 *	L'attributo IdRichiesta viene sovrascritto.
	 * @return boolean / WsGeneraCup
	 */
	public function inoltraRichiestaCupGenerazione(DatiRichiesta $DatiRichiesta) {
		try {
			if(\is_null($this->getWsGeneraCupService())) 
				throw new \Exception("web service non attivo");
			
			$IdRichiesta = $this->getNextIdRichiesta();
			$idProgetto = $DatiRichiesta->getIdProgetto();
			$isPresentIdProgetto = $this->richiestaInoltrataPositivaIdProgetto($idProgetto);
			if($isPresentIdProgetto)				
				return "Richiesta con IdProgetto:[$idProgetto] già effettuata";
			
			$this->setDatiRichiesta($DatiRichiesta);
			$this->preparareRichiestaCupGenerazione($IdRichiesta);
			$this->getWsGeneraCupService()->effettuaRichiestaCup();
			
			$this->salvaWsGeneraCup();
			$xmlRichiesta = $this->getWsGeneraCupService()->getWsGeneraCup()->getTextRispostaCupGenerazione();
			$RispostaCupGenerazione = $this->buildRispostaCupGenerazioneFromXmlRisposta($xmlRichiesta);
			$esito = ($RispostaCupGenerazione->getEsito_ws() == "OK") ? true : false;
			$this->getEm()->persist($RispostaCupGenerazione);
			$this->getEm()->flush();
			$this->getWsGeneraCupService()->getWsGeneraCup()->setRispostaCupGenerazione($RispostaCupGenerazione);
			$this->getWsGeneraCupService()->getWsGeneraCup()->setEsito($esito);
			$this->salvaWsGeneraCup();
			return $this->getWsGeneraCupService()->getWsGeneraCup();
			
		} catch (\Exception $ex) {
			if(!\is_null($this->getWsGeneraCupService())) {
				$this->getWsGeneraCupService()->getWsGeneraCup()->setErrorMessage($ex->getMessage());
				$this->salvaWsGeneraCup();
				return $this->getWsGeneraCupService()->getWsGeneraCup();
			}
			throw $ex;
		}
	}
	
	
	/**
	 * 
	 * @param int $id_progetto
	 * @throws \Exception
	 * @return DatiRichiesta
	 */
	public function findDatiRichiestaCupGenerazione($id_progetto) {
		try {
			$ret = $this->getDoctrine()->getRepository("CipeBundle\Entity\Aggregazioni\DatiRichiesta")->findOneBy(array("idProgetto" => $id_progetto));
			if(\is_null($ret))				
				throw new \Exception("Impossibile identificare i dati richiesta con id_progetto:[$id_progetto]");
			return $ret;
		} catch (\Exception $ex) {
			throw $ex;
		}
		

	}
	
	
	/**
	 * 
	 * @param GeoComune $GeoComune
	 * @return array
	 * @throws \Exception
	 */
	public function TransfromGeoComuneIntoCipeLocalizzazione(GeoComune $GeoComune) {
		try {
			/* @var $GeoProvincia \GeoBundle\Entity\GeoProvincia */
			$GeoProvincia = $GeoComune->getProvincia();
		
			/* @var $GeoRegione \GeoBundle\Entity\GeoRegione */
			$GeoRegione = $GeoProvincia->getRegione();
		
			/* @var $GeoStato \GeoBundle\Entity\GeoStato */
			$GeoStato = $GeoRegione->getStato();
			
			$stato = $GeoStato->getCodice();
			$regione = $GeoRegione->getCodice();
			$provincia = $GeoProvincia->getCodice();
			$comune = $provincia.$GeoComune->getCodice();
			
			return array(
							"stato"		=> $stato,
							"regione"	=> $regione,
							"provincia" => $provincia,
							"comune"	=> $comune
			);
			
			
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	
	/**
	 * generaRichiestaCupBatch
	 * @param String $NomefileRichiestaCup
	 * @param array $DatiRichieste
	 * @param String $ServiceBuilderDatiRichieste
	 * @param String $ServiceBuilderMethod
	 * @return mixed
	 * @throws \Exception
	 */
	public function generaRichiestaCupBatch($NomefileRichiestaCup, $DatiRichieste=array(), $ServiceBuilderDatiRichieste=null, $ServiceBuilderMethod=null) {
		try { 
			$this->getCupBatchService()->apriCupBatch($NomefileRichiestaCup);
			if(count($DatiRichieste)>0) {
				/* @var $DatiRichiesta DatiRichiesta */
				foreach ($DatiRichieste as $DatiRichiesta) {
					/* @var $CupGenerazione CupGenerazione */
					$CupGenerazione = $this->BuildRichiestaCupGenerazioneFromDatiRichiesta($DatiRichiesta);
					$this->getCupBatchService()->aggiungiCupGenerazione($CupGenerazione);
					unset($CupGenerazione);
					unset($DatiRichiesta);
				}
			}
			
			else {
				/* @var $DatiRichiesta DatiRichiesta */
				while ( $DatiRichiesta = $ServiceBuilderDatiRichieste->$ServiceBuilderMethod() ) {
					/* @var $CupGenerazione CupGenerazione */
					$CupGenerazione = $this->BuildRichiestaCupGenerazioneFromDatiRichiesta($DatiRichiesta);
					$this->getCupBatchService()->aggiungiCupGenerazione($CupGenerazione);
					unset($CupGenerazione);
					unset($DatiRichiesta);
				}
					
				
			}
			
			$this->getCupBatchService()->chiudiCupBatch();
			return $this->getCupBatchService()->salvaCupBatch();
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	
	/**
	 * generaRichiestaCupBatch
	 * @param String $NomefileRichiestaCup
	 * @param array $DatiRichieste
	 * @param String $ServiceBuilderDatiRichieste
	 * @param String $ServiceBuilderMethod
	 * @return mixed
	 * @throws \Exception
	 */
	public function validaRichiestaCupBatch($DatiRichieste=array(), $ServiceBuilderDatiRichieste=null, $ServiceBuilderMethod=null) {
		try { 
			$this->getCupBatchService()->startValidazioneMassiva();
			$array_validazione = array();
			if(count($DatiRichieste)>0) {
				/* @var $DatiRichiesta DatiRichiesta */
				foreach ($DatiRichieste as $DatiRichiesta) {
					/* @var $CupGenerazione CupGenerazione */
					$CupGenerazione = $this->BuildRichiestaCupGenerazioneFromDatiRichiesta($DatiRichiesta);
					$validazione = $this->getCupBatchService()->validaCupGenerazione($CupGenerazione);
					$array_validazione[$DatiRichiesta->getIstruttoriaRichiesta_id()] = $validazione;
				}
			}
			
			else {
				/* @var $DatiRichiesta DatiRichiesta */
				while ( $DatiRichiesta = $ServiceBuilderDatiRichieste->$ServiceBuilderMethod() ) {
					/* @var $CupGenerazione CupGenerazione */
					$CupGenerazione = $this->BuildRichiestaCupGenerazioneFromDatiRichiesta($DatiRichiesta);
					$validazione = $this->getCupBatchService()->validaCupGenerazione($CupGenerazione);
					$array_validazione[$DatiRichiesta->getIstruttoriaRichiesta_id()] = $validazione;
				}
			}
			
			$this->getCupBatchService()->endValidazioneMassiva();
			return $array_validazione;
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	

}
