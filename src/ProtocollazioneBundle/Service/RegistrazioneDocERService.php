<?php

namespace ProtocollazioneBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use ProtocollazioneBundle\Service\DocERDocumentService;
use ProtocollazioneBundle\Service\DocERAuthenticationService;
use ProtocollazioneBundle\Service\DocERFascicolazioneService;

/**
 * Description of IntegrazioneDocERService
 *
 * @author gaetanoborgosano
 * @author refactoring by Davide Cannistraro
 */
class RegistrazioneDocERService extends DocERBaseService {

	/**
	 *  FLUSSO di protocollazione 
	 *  1 - inizializzazione credenziali di autenticazione e accesso a DocER - loginDocERAutenticazione
	 *  1 - inizializzazione dati di fasciolazione dell'applicazione - initDatiFascicolazione (AUTOMATICO)
	 *  2 - carica documento principale su DocER - caricaDocumentoPrincipale
	 *  3 - carica insieme di documenti allegati - caricaDocumentoAllegato
	 *  4 - definisci l'unità documentale su questi documenti - definisciUnitaDocumentale
	 *  5 - fascicola i documenti - fascicolaDocumenti
	 *  6 - setta le informazioni necessaria per la protocollazione - initDatiProtocollazione
	 *  7 - protocollare i documenti - protocollaDocumenti
	 * 
	 * Operazioni per il prelevamento dei dati
	 *  scarica documento da DocER - scaricaDocumento
	 */
	protected $serviceContainer;

	/**
	 * DOC-ER AUTHENTICATION SERVICE - SERVIZIO DI AUTENTICAZIONE
	 * @var DocERAuthenticationService
	 */
	protected $DocERAuthenticationService;

	/**
	 * DOC-ER DOCUMENT SERVICE -- SERVIZIO PER RIVERSAMENTO DEI DOCUMENTI
	 * @var DocERDocumentService 
	 */
	protected $DocERDocumentService;

	public function getDocERDocumentService() {
		return $this->DocERDocumentService;
	}

	public function setDocERDocumentService(DocERDocumentService $DocERDocumentService) {
		$this->DocERDocumentService = $DocERDocumentService;
	}

	/**
	 * DOC-ER FASCICOLAZIONE SERVICE -- SERVIZIO PER FASCICOLAZIONE DEI DOCUMENTI
	 * @var DocERFascicolazioneService
	 */
	protected $DocERFascicolazioneService;

	function getDocERFascicolazioneService() {
		return $this->DocERFascicolazioneService;
	}

	function setDocERFascicolazioneService(DocERFascicolazioneService $DocERFascicolazioneService) {
		$this->DocERFascicolazioneService = $DocERFascicolazioneService;
	}

	/**
	 * DOC-ER PROTOCOLLAZIONE SERVICE -- SERVIZIO PER PROTOCOLLAZIONE DEI DOCUMENTI
	 * @var DocERRegistrazioneService
	 */
	protected $DocERRegistrazioneService;

	function getDocERRegistrazioneService() {
		return $this->DocERRegistrazioneService;
	}

	function setDocERRegistrazioneService(DocERRegistrazioneService $DocERRegistrazioneService) {
		$this->DocERRegistrazioneService = $DocERRegistrazioneService;
	}

	protected $em;

	public function __construct(ContainerInterface $serviceContainer, DocERAuthenticationService $DocERAuthenticationService) {
		parent::__construct($serviceContainer);

		$this->serviceContainer = $serviceContainer;
		$this->DocERAuthenticationService = $DocERAuthenticationService;
		$this->em = $serviceContainer->get('doctrine')->getManager();

		//$this->loginDocERAutenticazione();
	}

	public function __destruct() {
		$this->logoutDocER();
	}

	/**
	 * Inizializzazione servizi successivi all'autenticazione (login) in DocER
	 * @throws \Exception
	 */
	public function initServizi() {
		try {
			$this->initDocERServizio("DocERDocumentService");
			$this->initDocERServizio("DocERFascicolazioneService");
			$this->initDocERServizio("DocERRegistrazioneService");
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	/**
	 * Componente inizializzazione servizio DocER
	 * @param string $nomeServizio
	 * @return boolean
	 * @throws \Exception
	 */
	protected function initDocERServizio($nomeServizio) {
		try {

			$DocER_token = $this->getDocER_token();
			if (\is_null($DocER_token) || !$DocER_token) {
				throw new \Exception("Errore: impossibile istanziare il servizio $nomeServizio: token non valido");
			}

			$nameSpace = __NAMESPACE__ . '\\' . $nomeServizio;
			$ClasseServizio = new $nameSpace($DocER_token, $this->serviceContainer);
			$setClasseServizioMethod = "set$nomeServizio";
			$this->$setClasseServizioMethod($ClasseServizio);

//            $getClasseServizioMethod = "get$nomeServizio";
//            $SoapClientService = $this->$getClasseServizioMethod()->getSoapClient();
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	/**
	 *  ------------------- AUTENTICAZIONE DOC_ER ------------------------- 
	 */
	protected $docER_username;
	protected $docER_password;
	protected $docER_codice_ente;
	protected $docER_codice_aoo;
	protected $docER_application = "";
	protected static $docER_token = null;

	public static function getDocER_token() {
		return self::$docER_token;
	}

	static function setDocER_token($docER_token) {
		self::$docER_token = $docER_token;
	}

	/**
	 * login DocER Autenticazione
	 * @return boolean
	 * @throws \Exception
	 */
	public function loginDocERAutenticazione($tipo_protocollazione, $procedura) {
		$this->setApp_function("loginDocERAutenticazione");
		$utente = '';
		try {
			switch ($tipo_protocollazione) {
				case 'PRES':
					$utente = $procedura->getUtenteRobot();
					break;
				case 'REND':
					$utente = $procedura->getUtenteRobotRend();
					break;
				case 'CTRL':
					$utente = $procedura->getUtenteRobotCtrl();
					break;
			}
			//L'utente ora lo prendiamo dalla procedura in modo da assegnare correttamente la presa in carico dei doc in uscita
			//$this->docER_username = $this->serviceContainer->getParameter("DOCER_USERNAME");
			$this->docER_username = $utente;
			$this->docER_password = $this->serviceContainer->getParameter("DOCER_PASSWORD");
			$this->docER_codice_ente = $this->serviceContainer->getParameter("DOCER_CODICE_ENTE");
			$this->docER_codice_aoo = $this->serviceContainer->getParameter("DOCER_CODICE_AOO");
			$this->docER_application = "";

			if (\is_null($utente) || $utente == '') {
				throw new \Exception("Errore: Per la procedura ".$procedura->getId()." non è stato definito l'utente postazione robot di tipo ".$tipo_protocollazione);
			}
			
			$docER_token = $this->DocERAuthenticationService->login($this->docER_username, $this->docER_password, $this->docER_codice_ente, $this->docER_application);
			if (\is_null($docER_token) || !$docER_token) {
				throw new \Exception("Errore: non è stato restituito il token dal processo di autenticazione o il token risulta vuoto");
			}
//			$as = substr($docER_token, 0, 255); per debug
			self::setDocER_token($docER_token);
			$this->initServizi();
			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	/**
	 * logout DocER
	 * @return mixed
	 * @throws \Exception
	 */
	public function logoutDocER() {
		$this->setApp_function("logoutDocER");
		try {
			$st = null;
			$docER_token = $this->getDocER_token();
			if (!\is_null($docER_token) && $docER_token) {
				$st = $this->DocERAuthenticationService->logout();
				$this->setDocER_token(null);
			}
			return $st;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	/*
	 *  ------------------- DATI DI RIVERSAMENTO ------------------------- 
	 */

	protected $idDocument_principale;

	function getIdDocument_principale() {
		return $this->idDocument_principale;
	}

	function setIdDocument_principale($idDocument_principale) {
		$this->idDocument_principale = $idDocument_principale;
	}

	protected $idDocument_allegati = array();

	function getIdDocument_allegati() {
		return $this->idDocument_allegati;
	}

	function setIdDocument_allegati($idDocument_allegati) {
		$this->idDocument_allegati = $idDocument_allegati;
	}

	function addIdDocument_allegati($idDocument) {
		$idDocument_allegati = $this->getIdDocument_allegati();
		$idDocument_allegati[] = $idDocument;
		$this->setIdDocument_allegati($idDocument_allegati);
	}

	/**
	 * removeDocumentFromDocER
	 * @param string $idDocument
	 * @return mixed
	 * @throws \Exception
	 */
	protected function removeDocumentFromDocER($idDocument) {
		$this->setApp_function("removeDocumentFromDocER");
		$st = $this->getDocERDocumentService()->deleteDocument($idDocument);
		if ($st === false) {
			throw new \Exception();
		}
		return $st;
	}

	public function clear() {
		$this->setIdDocument_principale(null);
		$this->setIdDocument_allegati(array());
	}

	/**
	 * caricaDocumento 
	 * @param string $filePathName
	 * @param string $categoria
	 * @return type
	 * @throws \Exception
	 * @throws Exception
	 */
	protected function caricaDocumento($filePathName, $categoria) {
		$this->setApp_function("caricaDocumento");
		$step = 0;
		try {
			if (file_exists($filePathName)) {
				$fileContent = (file_get_contents($filePathName)) ? file_get_contents($filePathName) : null;
			} else {
				throw new \Exception("Avviso: documento principale o cartella inesistente");
			}
			$docname = basename($filePathName);
			$cod_ente = $this->docER_codice_ente;
			$cod_aoo = $this->docER_codice_aoo;
			$type_id = "Documento";
			$tipo_componente = $categoria;
			$idDocument = $this->getDocERDocumentService()->createDocument($filePathName, $fileContent, $docname, $cod_ente, $cod_aoo, $type_id, $tipo_componente);
			if ($idDocument === false) {
				throw new \Exception("Errore imprevisto: il documento creato su DocER verra' rimosso");
			}
			$step = 1; // documento caricato su DocER senza Acl
			$acl = array("Rimuovi" => "-1",
				"Full_Access" => "0",
				"Normal_Access" => "1",
				"Read_Only_Access" => "2"
			);
			$aclVal = $acl["Full_Access"];
			$aclStat = $this->getDocERDocumentService()->setACLDocument($idDocument, $this->docER_username, $aclVal);
			if (\is_null($aclStat) || !$aclStat) {
				throw new \Exception("Errore: impossibile generare le Acl per il documento creato su DocER");
			}
			$step = 2; // documento caricato su DocER con Acl 
			return $idDocument;
		} catch (\Exception $ex) {
			if ($step > 1) {
				$this->removeDocumentFromDocER($idDocument);
			}
			throw $ex;
		}
	}

	/**
	 * caricaDocumentoPrincipale
	 * @param string $filePathName
	 * @return null|int
	 * @throws \Exception
	 */
	public function caricaDocumentoPrincipale($filePathName) {
		$this->setApp_function("caricaDocumentoPrincipale");
		$this->clear();
		$categoria = "PRINCIPALE";
		$idDocument_principale = $this->caricaDocumento($filePathName, $categoria);
		$this->setIdDocument_principale($idDocument_principale);
		return $idDocument_principale;
	}

	/**
	 * caricaAllegato
	 * @param string $filePathName
	 * @return null|int
	 * @throws \Exception
	 */
	public function caricaAllegato($filePathName, $categoria = "ALLEGATO") {
		$this->setApp_function("caricaAllegato");
		$idDocument = $this->caricaDocumento($filePathName, $categoria);
		if ($idDocument != false) {
			$this->addIdDocument_allegati($idDocument);
		}
		return $idDocument;
	}

	/**
	 * definisci Unita Documentale
	 * @return int|null
	 * @throws \Exception
	 */
	public function definisciUnitaDocumentale() {
		$this->setApp_function("definisciUnitaDocumentale");
		$idDocument_principale = $this->getIdDocument_principale();
		$idDocument_allegati = $this->getIdDocument_allegati();
		$st = $this->getDocERDocumentService()->addRelated($idDocument_principale, $idDocument_allegati);
		if ($st === false) {
			throw new \Exception("");
		}
		return true;
	}

	/*
	 *  ------------------- DATI DI FASCICOLAZIONE ------------------------- 
	 */

	protected $DocERFascicolazione_cod_ente;
	protected $DocERFascicolazione_cod_aoo;
	protected $DocERFascicolazione_classifica;
	protected $DocERFascicolazione_des_fascicolo = null;
	protected $DocERFascicolazione_anno_fascicolo;
	protected $DocERFascicolazione_uo_in_carico;
	protected $DocERFascicolazione_parent_progr_fascicolo = null;
	protected $DocERFascicolazione_metadati_extra = array();

	/**
	 * 
	 * @param integer $docER_fascicolazione_anno_fascicolo
	 * @param string $docER_fascicolazione_progr_fascicolo
	 * @param string $docER_fascicolazione_fascicoli_sec
	 * @param string $docER_fascicolazione_classifica
	 */
	public function initDatiFascicolazione(
	$DocERFascicolazione_classifica, $DocERFascicolazione_anno_fascicolo, $DocERFascicolazione_uo_in_carico, $DocERFascicolazione_des_fascicolo = null, $DocERFascicolazione_parent_progr_fascicolo = null, $DocERFascicolazione_metadati_extra = null, $DocERFascicolazione_cod_ente = null, $DocERFascicolazione_cod_aoo = null
	) {
		if (\is_null($DocERFascicolazione_cod_ente)) {
			$DocERFascicolazione_cod_ente = $this->docER_codice_ente;
		}
		if (\is_null($DocERFascicolazione_cod_aoo)) {
			$DocERFascicolazione_cod_aoo = $this->docER_codice_aoo;
		}
		$this->DocERFascicolazione_cod_ente = $DocERFascicolazione_cod_ente;
		$this->DocERFascicolazione_cod_aoo = $DocERFascicolazione_cod_aoo;
		$this->DocERFascicolazione_classifica = $DocERFascicolazione_classifica;
		$this->DocERFascicolazione_des_fascicolo = $DocERFascicolazione_des_fascicolo;
		$this->DocERFascicolazione_anno_fascicolo = $DocERFascicolazione_anno_fascicolo;
		$this->DocERFascicolazione_uo_in_carico = $DocERFascicolazione_uo_in_carico;
		$this->DocERFascicolazione_parent_progr_fascicolo = $DocERFascicolazione_parent_progr_fascicolo;
		$this->DocERFascicolazione_metadati_extra = $DocERFascicolazione_metadati_extra;
	}

	public function clearFascicolazione() {
		$this->initDatiFascicolazione(null, null, null, null, null, null, null, null);
	}

	public function creaFascicolo($only_numFascicolo = true) {
		$this->setApp_function("creaFascicolo");
		try {
			$st = $this->getDocERFascicolazioneService()->creaFascicolo(
					$this->DocERFascicolazione_cod_ente, $this->DocERFascicolazione_cod_aoo, $this->DocERFascicolazione_classifica, $this->DocERFascicolazione_des_fascicolo, $this->DocERFascicolazione_anno_fascicolo, $this->DocERFascicolazione_uo_in_carico, $this->DocERFascicolazione_parent_progr_fascicolo, $this->DocERFascicolazione_metadati_extra
			);
			if ($st === false) {
				throw new \Exception("Avviso: il sottofascicolo su DocER risulta vuoto");
			}
			if ($only_numFascicolo) {
				return $st['NUM_FASCICOLO'];
			}
			return $st;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	// ----------- DATI DI PROTOCOLLAZIONE -------------

	protected $Protocollazione_Oggetto;
	protected $Protocollazione_Mittente_Persona_Giuridica_id;
	protected $Protocollazione_Mittente_Persona_Giuridica_Denominazione;
	protected $Protocollazione_CodiceAOO;
	protected $Protocollazione_CodiceAmministrazione;
	protected $Protocollazione_Identificativo;
	protected $Protocollazione_Classifica;
	protected $Protocollazione_Fascicolo_primario_Anno;
	protected $Protocollazione_Fascicolo_primario_Progressivo;
    protected $Protocollazione_Registro_Id;


	public function initProtocollazione(
            $Protocollazione_Oggetto, $Protocollazione_Mittente_Persona_Giuridica_id, $Protocollazione_Mittente_Persona_Giuridica_Denominazione, 
            $Protocollazione_Fascicolo_primario_Progressivo, $Protocollazione_Registro_Id,  $Protocollazione_CodiceAOO = null, $Protocollazione_CodiceAmministrazione = null, 
            $Protocollazione_Identificativo = null, $Protocollazione_Classifica = null, 
            $Protocollazione_Fascicolo_primario_Anno = null
	) {
		$this->Protocollazione_Oggetto = $Protocollazione_Oggetto;
		$this->Protocollazione_Mittente_Persona_Giuridica_id = $Protocollazione_Mittente_Persona_Giuridica_id;
		$this->Protocollazione_Mittente_Persona_Giuridica_Denominazione = $Protocollazione_Mittente_Persona_Giuridica_Denominazione;
		$this->Protocollazione_Fascicolo_primario_Progressivo = $Protocollazione_Fascicolo_primario_Progressivo;
		$this->Protocollazione_CodiceAOO = (\is_null($Protocollazione_CodiceAOO)) ? $this->DocERFascicolazione_cod_aoo : $Protocollazione_CodiceAOO;
		$this->Protocollazione_CodiceAmministrazione = (\is_null($Protocollazione_CodiceAmministrazione)) ? $this->DocERFascicolazione_cod_ente : $Protocollazione_CodiceAmministrazione;
		$this->Protocollazione_Identificativo = (\is_null($Protocollazione_Identificativo)) ? $this->DocERFascicolazione_uo_in_carico : $Protocollazione_Identificativo;
		$this->Protocollazione_Classifica = (\is_null($Protocollazione_Classifica)) ? $this->DocERFascicolazione_classifica : $Protocollazione_Classifica;
		$this->Protocollazione_Fascicolo_primario_Anno = (\is_null($Protocollazione_Fascicolo_primario_Anno)) ? $this->DocERFascicolazione_anno_fascicolo : $Protocollazione_Fascicolo_primario_Anno;
        $this->Protocollazione_Registro_Id = (\is_null($Protocollazione_Registro_Id)) ? 'CR' : $Protocollazione_Registro_Id;
	}

	public function clearProtocollazione() {
		$this->initDatiFascicolazione(null, null, null, null, null, null, null, null, null);
	}

	public function makeXmlProtocolloAzienda() {
		$this->setApp_function("makeXmlProtocolloAzienda");
		try {
			$XmlBuilderProtocollo = $this->getDocERRegistrazioneService()->getBuilderXmlProtocollo();
			$xml = $XmlBuilderProtocollo->buildXmlForAzienda($this->Protocollazione_Oggetto, $this->Protocollazione_Mittente_Persona_Giuridica_id, $this->Protocollazione_Mittente_Persona_Giuridica_Denominazione, $this->Protocollazione_CodiceAOO, $this->Protocollazione_CodiceAmministrazione, $this->Protocollazione_Identificativo, $this->Protocollazione_Classifica, $this->Protocollazione_Fascicolo_primario_Anno, $this->Protocollazione_Fascicolo_primario_Progressivo
			);
			return $xml;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	/**
	 * protocollaDocumento
	 * @return true
	 * @throws \Exception
	 */
	public function protocollaUnitaDocumentale() {
		$this->setApp_function("protocollaUnitaDocumentale");
		try {
			$documentoId = $this->getIdDocument_principale();
			$datiProtocollo = $this->makeXmlProtocolloAzienda();
			$st = $this->getDocERRegistrazioneService()->registraById($documentoId, $datiProtocollo, $this->Protocollazione_Registro_Id);
			if ($st === false) {
				throw new \Exception("Avviso: nessun dato di protocollazione restituito da DocER");
			}
			return $st;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	protected function makeXmlProtocolloInUscita($param) {
		$this->setApp_function("makeXmlProtocolloInUscita");
		try {
			//Leggo il file Xml per la protocollazione in uscita
			$path = __DIR__ . '/../Resources/schemi/xml_schema/ProtocollazioneInUscita/';
			$path .= 'protocollazioneInUscita.xml';
			if (file_exists($path)) {
				$xml = simplexml_load_file($path);

				$intestazione = $xml->Intestazione;

				$intestazione->Oggetto = $param['Oggetto'];
				$intestazione->Flusso->TipoRichiesta = $param['TipoRichiesta'];
				$intestazione->Flusso->Firma = $param['Firma'];
				$intestazione->Flusso->ForzaRegistrazione = $param['ForzaRegistrazione'];

				$amministrazione = $intestazione->Mittenti->Mittente->Amministrazione;
				$amministrazione->Denominazione = $this->serviceContainer->getParameter("DOCER_DENOMINAZIONE");
				$amministrazione->CodiceAmministrazione = $this->serviceContainer->getParameter("DOCER_CODICE_ENTE");
				$amministrazione->UnitaOrganizzativa->addAttribute('tipo', $this->serviceContainer->getParameter("DOCER_UO_TIPO_TEMPORANEA"));
				//$amministrazione->UnitaOrganizzativa->addAttribute('tipo', $this->serviceContainer->getParameter("DOCER_UO_TIPO_PERMANENTE"));
				$amministrazione->UnitaOrganizzativa->Denominazione = $this->serviceContainer->getParameter("DOCER_UO_DENOMINAZIONE");
				$amministrazione->UnitaOrganizzativa->Identificativo = $this->DocERFascicolazione_uo_in_carico;

				$aoo = $intestazione->Mittenti->Mittente->AOO;
				$aoo->Denominazione = $this->serviceContainer->getParameter("DOCER_DENOMINAZIONE_AOO");
				$aoo->CodiceAOO = $this->serviceContainer->getParameter("DOCER_CODICE_AOO_EMR");
				//$aoo->CodiceAOO = $this->serviceContainer->getParameter("DOCER_CODICE_AOO_AL");

				$destinatario = $intestazione->Destinatari->Destinatario;
				$destinatario->PersonaGiuridica->addAttribute('id', '0');
				$destinatario->PersonaGiuridica->addAttribute('tipo', 'PG');
				$persona_giuridica = $destinatario->PersonaGiuridica;
				$persona_giuridica->Denominazione = $param['DestinatarioDenominazione'];
				$metadati = $persona_giuridica->Metadati;
				$parametroCF = $metadati->addChild('Parametro');
				$parametroCF->addAttribute('nome', 'CODICE_FISCALE');
				$parametroCF->addAttribute('valore', $param['CodiceFiscale']);
				if (!\is_null($param['PartitaIVA'])) {
					$parametroPI = $metadati->addChild('Parametro');
					$parametroPI->addAttribute('nome', 'PARTITA_IVA');
					$parametroPI->addAttribute('valore', $param['PartitaIVA']);
				}

				$fascicolo_primario = $intestazione->FascicoloPrimario;
				$fascicolo_primario->CodiceAmministrazione = $this->serviceContainer->getParameter("DOCER_CODICE_ENTE");
				$fascicolo_primario->CodiceAOO = $this->serviceContainer->getParameter("DOCER_CODICE_AOO_EMR");
				$fascicolo_primario->Classifica = $param['Classifica'];
				$fascicolo_primario->Anno = $param['Anno'];
				$fascicolo_primario->Progressivo = $param['FascicoloPrimarioProgressivo'];

				$xml = $intestazione->asXML();

				$xml = '<Segnatura xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $xml . '</Segnatura>';
			} else {
				$msg = "impossibile aprire il documento Xml per la protocollazione in uscita";
				throw new \Exception($msg);
			}
			return $xml;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	/**
	 * protocollaUnitaDocumentaleInUscita
	 * @author dcannistraro
	 * @return array Dati di protocollazione (PG - Anno - Numero protocollo) oppure false
	 *
	 */
	public function protocollaUnitaDocumentaleInUscita($param, $registro_id) {
		$this->setApp_function("protocollaUnitaDocumentaleInUscita");
		try {
			$documentoId = $this->getIdDocument_principale();
			$datiProtocollo = $this->makeXmlProtocolloInUscita($param);

			$st = $this->getDocERRegistrazioneService()->registraById($documentoId, $datiProtocollo, $registro_id);
			if ($st === false) {
				throw new \Exception("Avviso: nessun dato di protocollazione restituito da DocER");
			}

			return $st;
//            return $datiProtocollo;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

}
