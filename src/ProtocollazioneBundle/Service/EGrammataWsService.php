<?php

namespace ProtocollazioneBundle\Service;

use ProtocollazioneBundle\Entity\EmailProtocollo;
use ProtocollazioneBundle\Entity\Log;

/**
 * Description of EGrammataWsService
 *
 * @author gdisparti
 */
class EGrammataWsService {

	const LOG_ERROR_CODE = 'ERROR';
	const LOG_INFO_CODE = 'INFO';

	/**
	 * Enumerazione del nodo Email->TipoMessaggio in wsInterrogazioneRicevutePec
	 * arrivano proprio in questa forma..niente codici...bello no?...noi le uniformeremo rendendo tutto minuscolo e mettendo l'underscore
	 * -Accettazione
	 * -Presa in carico
	 * -Non accettazione
	 * -Avvenuta consegna
	 * -Avviso di mancata consegna
	 * -Preavviso di mancata consegna
	 */
	const TIPO_ACCETTAZIONE = 'accettazione';
	const TIPO_PRESA_IN_CARICO = 'presa_in_carico';
	const TIPO_NON_ACCETTAZIONE = 'non_accettazione';
	const TIPO_AVVENUTA_CONSEGNA = 'avvenuta_consegna';
	const TIPO_MANCATA_CONSEGNA = 'avviso_di_mancata_consegna';
	const TIPO_PREAVVISO_MANCATA_CONSEGNA = 'preavviso_di_mancata_consegna';

	private $soapClient;
	private $wsdl;
	private $options;
	private $requestParams;
	private $em;
	private $container;

//esempio requets invio email
//	<SegnaturaGenerica>
//  <Dati>
//    <IdUteIn>152175</IdUteIn>
//    <IdUOIn>103898</IdUOIn>
//    <FlgForzaPEI>N</FlgForzaPEI>
//    <TipoReg>PG</TipoReg>
//    <AnnoReg>2017</AnnoReg>
//    <NumeroReg>268</NumeroReg>
//    <OggettoEmail>prova pec protocollazione</OggettoEmail>
//    <TestoEmail>prova testo</TestoEmail>
//    <EmailDest>
//      <IdAnag>1</IdAnag>
//      <Email>gdisparti@schema31.it</Email>
//      <FlgDestCopia>N</FlgDestCopia>
//    </EmailDest>
//  </Dati>
//</SegnaturaGenerica>
//
//--- TEST ---
//         Utente                 IdUteIn            IdUoIn
//UT_SFINGE_367           200184             116246
//UT_SFINGE_397           200179             116233
//UT_SFINGE_482           200180             116234

//--- PRODUZIONE ---
//         Utente                 IdUteIn            IdUoIn
//UT_SFINGE_367           162310             113413
//UT_SFINGE_368           162311             113414
//UT_SFINGE_397           162307             113410
//UT_SFINGE_454           162309             113412
//UT_SFINGE_482           162308             113411

	public function __construct($requestParams, $wsdl, $doctrine, $container) {

        $streamContext = stream_context_create(array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        ));

        $this->options = array(
			'connection_timeout' => 60,
			'cache_wsdl' => WSDL_CACHE_NONE,
			'exceptions' => true,
            //ATTENZIONE !!!!! la riga sotto deve essere commentata, nel caso non lo fosse, prima di mandare in produzione perchè in test hanno i certificati scaduti
            'stream_context' => $streamContext
		);

		$this->requestParams = $requestParams;

		$this->wsdl = $wsdl;

		$this->em = $doctrine->getManager();

		$this->container = $container;
	}

	private function setupSoap($wsdlService) {
		$this->soapClient = new \SoapClient($wsdlService, $this->options);
		//$this->lookSoap($this->soapClient);
	}

	private function lookSoap(\SoapClient $soapClient) {
		$function = $this->soapClient->__getFunctions();
		$types = $this->soapClient->__getTypes();
	}

	/**
	 * 
	 * @param EmailProtocollo $emailProtocollo
	 * @return mixed false|array
	 */
	private function wsInvioEmailProtocollo(EmailProtocollo $emailProtocollo) {

		$richiestaProtocollo = $emailProtocollo->getRichiestaProtocollo();

		try {
			// per sicurezza controlliamo che sia stata davvero protocollata
			if (!$richiestaProtocollo->isPostProtocollazione()) {
				throw new \Exception('RichiestaProtocollo non in fase POST_PROTOCOLLAZIONE');
			}

			// ogni classe figlia di RichiestaProtocollo per cui si deve inviare una mail tramite egrammata dovrà implementare l'interface emailSendable
			$emailPec = $richiestaProtocollo->getDestinatarioEmailProtocollo();
			if (empty($emailPec)) {
				throw new \Exception('Pec non presente per il soggetto destinatario');
			}

			// visto che successivamente il soggetto potrebbe cambiare la sua pec, ci conserviamo la pec a cui stiamo inviando
			$emailProtocollo->setDestinatario($emailPec);

			$request = $this->makeUpInvioEmailProtocolloRequest($richiestaProtocollo, $emailPec);

			$xml = base64_encode($request);
			$hash = base64_encode(sha1($request . $this->requestParams['password'], true));

			$wsdlService = $this->wsdl['baseUrl'] . $this->wsdl['invioEmailProtocollo'];

			if (!$this->container->getParameter("invio.email.abilitato")) {
				return array('invio_email_disabled');
			}
			$dati = $this->getDatiUtenzaMail($richiestaProtocollo);
			$user = $dati['user'];
			
			$this->setupSoap($wsdlService);

			$base64Response = $this->soapClient->service(
					$this->requestParams['codEnte'], $user, $this->requestParams['password'], $this->requestParams['indirizzoIp'], $xml, $hash);


			$error = '';

			$xmlResponse = $this->handleResponse($emailProtocollo->getId(), $base64Response, $error);
			if ($xmlResponse === false) {
				throw new \Exception($error);
			}

			$isSuccess = $this->evaluateResponse($xmlResponse, $error);
			if (!$isSuccess) {
				throw new \Exception($error);
			}
		} catch (\Exception $e) {
			$this->createLog($emailProtocollo->getId(), self::LOG_ERROR_CODE, $e->getMessage(), 'wsInvioEmailProtocollo');
			return false;
		}

		// mi conservo l'id che mi ritorna il ws che identifica l'email inviata
		// a quanto pare in alcune circostanze egrammata potrebbe frammentare l'invio in più email e quindi dobbiamo gestire questo fatto
		$idEmails = array();
		foreach ($xmlResponse->IdEmails->IdEmail as $idEmailNode) {
			$idEmail = $this->getNodeValue($idEmailNode);
			$idEmails[] = $idEmail;
		}

		return $idEmails;
	}

	/**
	 * 
	 * @param EmailProtocollo $emailProtocollo
	 * @param type $idEmail
	 * @return false|array
	 */
	public function wsInterrogazioneRicevutePec(EmailProtocollo $emailProtocollo, $idEmail = null) {

		$richiestaProtocollo = $emailProtocollo->getRichiestaProtocollo();

		try {
			$request = $this->makeUpInterrogazioneRicevutePecRequest($richiestaProtocollo, $idEmail);

			$xml = base64_encode($request);
			$hash = base64_encode(sha1($request . $this->requestParams['password'], true));

			$wsdlService = $this->wsdl['baseUrl'] . $this->wsdl['interrogazioneRicevutePec'];
			$this->setupSoap($wsdlService);
			
			$dati = $this->getDatiUtenzaMail($richiestaProtocollo);
			$user = $dati['user'];

			$base64Response = $this->soapClient->getServiceBase64(
					$this->requestParams['codEnte'], $user, $this->requestParams['password'], $this->requestParams['indirizzoIp'], $xml, $hash);

			$error = '';

			$logResponse = false;
			$xmlResponse = $this->handleResponse($emailProtocollo->getId(), $base64Response, $error, $logResponse);
			if ($xmlResponse === false) {
				throw new \Exception($error);
			}

			$isSuccess = $this->evaluateResponse($xmlResponse, $error);
			if (!$isSuccess) {
				throw new \Exception($error);
			}
		} catch (\Exception $e) {
			$this->createLog($emailProtocollo->getId(), self::LOG_ERROR_CODE, $e->getMessage(), 'wsInterrogazioneRicevutePec');
			return false;
		}

		$ricevute = array();
		foreach ($xmlResponse->Emails->Email as $emailNode) {

			$idEmail = $this->getNodeValue($emailNode->IdEmail);
			$ricevute[$idEmail] = array();

			foreach ($emailNode->EmailDest as $emailDest) {

				$ricevuta = array();
				$ricevuta['tipoMessaggio'] = str_replace(' ', '_', strtolower($this->getNodeValue($emailDest->TipoMessaggio)));
				$ricevuta['timeStamp'] = $this->getNodeValue($emailDest->TimeStamp);

				// quando e se servirà
				//$ricevuta['attachFileBase64'] = $this->getNodeValue($emailDest->AttachFileBase64);    

				$ricevute[$idEmail][] = $ricevuta;
			}
		}

		return $ricevute;
	}

	private function makeUpInvioEmailProtocolloRequest($richiestaProtocollo, $emailPec) {

		$tipoReg = $richiestaProtocollo->getRegistroPg();
		$annoReg = $richiestaProtocollo->getAnnoPg();
		$numeroReg = $richiestaProtocollo->getNumPg();

		$oggetto = $this->getOggettoMailDaTipo($richiestaProtocollo);

		// ogni classe figlia di RichiestaProtocollo per cui si deve inviare una mail tramite egrammata dovrà implementare l'interface emailSendable
		$testoEmail = $richiestaProtocollo->getTestoEmailProtocollo();

		$request = new \SimpleXMLElement('<SegnaturaGenerica></SegnaturaGenerica>');
		$request->addChild('Dati');
		
		/*
		 * $request->Dati->addChild('IdUteIn', $this->requestParams['idUteIn']);
		 * $request->Dati->addChild('IdUOIn', $this->requestParams['idUoIn']);
		 * Il popolamento ora sarà dinamico
		 * 
		*/
		$utenze = $this->getDatiUtenzaMail($richiestaProtocollo);
		$request->Dati->addChild('IdUteIn', $utenze['idUteIn']);
		$request->Dati->addChild('IdUOIn', $utenze['idUoIn']);
		
		$request->Dati->addChild('FlgForzaPEI', 'N');
		$request->Dati->addChild('TipoReg', $tipoReg);
		$request->Dati->addChild('AnnoReg', $annoReg);
		$request->Dati->addChild('NumeroReg', $numeroReg);

		/**
		 * trasformiamo questi 3 caratteri che danno problemi alla addChild
		 */
		$request->Dati->addChild('OggettoEmail', str_ireplace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $oggetto));
		$request->Dati->addChild('TestoEmail', str_ireplace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $testoEmail));

		$emailDest = $request->Dati->addChild('EmailDest');
		// su indicazione di engineering mettiamo un valore qualsiasi, suggeriscono 1
		$emailDest->addChild('IdAnag', '1');
		$emailDest->addChild('Email', $emailPec);
		$emailDest->addChild('FlgDestCopia', 'N');

		return $request->asXML();
	}

	/**
	 * Se non viene valorizzato idEmail..viene tornato tutto lo storico delle ricevute legate a tutte le email generate per quel protocollo
	 * @param $richiestaProtocollo
	 * @param string $idEmail
	 * @return mixed
	 */
	private function makeUpInterrogazioneRicevutePecRequest($richiestaProtocollo, $idEmail = null) {

		$tipoReg = $richiestaProtocollo->getRegistroPg();
		$annoReg = $richiestaProtocollo->getAnnoPg();
		$numeroReg = $richiestaProtocollo->getNumPg();

		$request = new \SimpleXMLElement('<SegnaturaGenerica></SegnaturaGenerica>');
		$request->addChild('Dati');
		/*
		 * $request->Dati->addChild('IdUteIn', $this->requestParams['idUteIn']);
		 * $request->Dati->addChild('IdUOIn', $this->requestParams['idUoIn']);
		 * Il popolamento ora sarà dinamico
		 * 
		*/
		$utenze = $this->getDatiUtenzaMail($richiestaProtocollo);
		$request->Dati->addChild('IdUteIn', $utenze['idUteIn']);
		$request->Dati->addChild('IdUOIn', $utenze['idUoIn']);
		
		$request->Dati->addChild('TipoReg', $tipoReg);
		$request->Dati->addChild('AnnoReg', $annoReg);
		$request->Dati->addChild('NumeroReg', $numeroReg);
		if (!is_null($idEmail)) {
			$request->Dati->addChild('IdEmail', $idEmail);
		}

		return $request->asXML();
	}

	/**
	 * 
	 * @param string $response
	 * @param string $error
	 * @return mixed false|SimpleXmlElement
	 */
	private function handleResponse($emailProtocolloId, &$base64Response, &$error, $logResponse = true) {

		if ($logResponse) {
			$this->createLog($emailProtocolloId, self::LOG_INFO_CODE, $base64Response, 'handleResponse');
		}
		$decodedResponse = base64_decode($base64Response);
		if ($decodedResponse === false) {
			return false;
		}

		try {
			$xmlResponse = simplexml_load_string($decodedResponse);
			if ($xmlResponse === false) {
				throw new \Exception('Errore nella simplexml_load_string');
			}
		} catch (\Exception $e) {
			$error = $e->getMessage();
			return false;
		}

		return $xmlResponse;
	}

	private function createLog($richiestaProtocolloId, $code, $message, $appFunction = null) {

		$log = new Log();

		$log->setRichiesta_protocollo_id($richiestaProtocolloId);
		$log->setCode($code);
		$log->setLogTime(new \DateTime('now'));
		$log->setMessage($message);
		$log->setAppFunction($appFunction);

		$this->em->persist($log);

		try {
			$this->em->flush($log);
		} catch (\Exception $e) {
			
		}
	}

	private function getNodeValue($node) {

		if (!isset($node)) {
			return null;
		}

		$value = trim($node);

		return $value == "" ? null : $value;
	}

	/**
	 * Mi dice se la chiamata è avvenuta con successo o meno
	 * @param \SimpleXmlElement $xmlResponse
	 * @param string $error
	 * @return boolean
	 */
	private function evaluateResponse($xmlResponse, &$error) {
		$codiceStato = $this->getNodeValue($xmlResponse->Stato->Codice);
		if (is_null($codiceStato)) {
			$error = 'Nodo Stato non presente nella response';
			return false;
		}

		if ($codiceStato != '0') {
			$error = 'Codice: ' . $codiceStato . ' - ' . $this->getNodeValue($xmlResponse->Stato->Messaggio);
			return false;
		}

		return true;
	}

	public function creaEmailProtocollo($richiestaProtocollo) {

		$emailProtocollo = new EmailProtocollo();
		$emailProtocollo->setRichiestaProtocollo($richiestaProtocollo);
		$emailProtocollo->setStato(EmailProtocollo::DA_INVIARE);

		try {
			$this->em->persist($emailProtocollo);
			$this->em->flush($emailProtocollo);
		} catch (\Exception $e) {
			$this->createLog($emailProtocollo->getId(), self::LOG_ERROR_CODE, $e->getMessage(), 'creaEmailProtocollo');
			return false;
		}

		return true;
	}

	public function inviaEmail(EmailProtocollo $emailProtocollo) {

		// potrebbe tornare più di un id, ma non dovrebbe succedere nel nostro caso
		$idEmail = $this->wsInvioEmailProtocollo($emailProtocollo);
		if ($idEmail === false) {
			return false;
		}

		$emailProtocollo->setIdEmail($idEmail);
		$emailProtocollo->setStato(EmailProtocollo::INVIATA);
		$emailProtocollo->setDataInvio(new \DateTime('now'));

		try {
			$this->em->flush($emailProtocollo);
		} catch (\Exception $e) {
			$this->createLog($emailProtocollo->getId(), self::LOG_ERROR_CODE, $e->getMessage(), 'inviaEmail');
			return false;
		}

		return true;
	}

	public function aggiornaEmailProtocollo(EmailProtocollo $emailProtocollo) {

		/**
		 * ci è stato detto che in teoria un invio potrebbe essere frammentato, in base a dimensione e numero destinatari
		 * ci è stato anche detto che non dovrebbe essere il nostro caso
		 * per cui noi gestiamo il multi id durante l'invio, ma per semplificare ipotizziamo che ce ne sia sempre uno
		 * e nell'invocare il ws esplicitiamo questo idEmail nella request
		 */
		$ids = $emailProtocollo->getIdEmail();
		$idEmail = $ids[0];

		$ricevute = $this->wsInterrogazioneRicevutePec($emailProtocollo, $idEmail);
		if ($ricevute === false) {
			return false;
		}

		// se non sono ancora disponibili ricevute
		if (count($ricevute) == 0) {
			return true;
		}

		$ricevutePervenute = array();

		foreach ($ricevute[$idEmail] as $ricevuta) {

			$ricevutePervenute[] = array($ricevuta['tipoMessaggio'], $ricevuta['timeStamp']);

			/**
			 * caso A e B sono esclusivi e denotano l'evoluzione in uno dei stati finali
			 * caso C non è rilevante ai fini dell'evoluzione di stato
			 */
			switch ($ricevuta['tipoMessaggio']) {

				// caso A
				case self::TIPO_AVVENUTA_CONSEGNA:
					$emailProtocollo->setStato(EmailProtocollo::CONSEGNATA);
					break;

				// caso B
				case self::TIPO_MANCATA_CONSEGNA:
				case self::TIPO_NON_ACCETTAZIONE:
					$emailProtocollo->setStato(EmailProtocollo::NON_CONSEGNATA);
					break;

				// caso C
				case self::TIPO_ACCETTAZIONE:
				case self::TIPO_PREAVVISO_MANCATA_CONSEGNA:
				case self::TIPO_PRESA_IN_CARICO:
					// do nothing
					break;
			}
		}

		$emailProtocollo->setRicevutePervenute($ricevutePervenute);

		try {
			$this->em->flush($emailProtocollo);
		} catch (\Exception $e) {
			$this->createLog($emailProtocollo->getId(), self::LOG_ERROR_CODE, $e->getMessage(), 'aggiornaEmailProtocollo');
			return false;
		}

		return true;
	}

	private function getOggettoMailDaTipo($richiestaProtocollo) {
		$classe = $richiestaProtocollo->getNomeClasse();
		switch ($classe) {
			case 'RichiestaProtocolloIntegrazione':
			case 'ProtocolloIntegrazionePagamento':
				$oggetto = "Richiesta di integrazione protocollo n° " . $richiestaProtocollo->getProtocollo();
				break;

			case 'RichiestaProtocolloEsitoIstruttoria':
			case 'ProtocolloEsitoIstruttoriaPagamento':
				$oggetto = "Comunicazione pratica " . $richiestaProtocollo->getSoggetto()->getDenominazione();
				break;

			case 'ProtocolloRichiestaChiarimenti':
				$oggetto = "Richiesta di chiarimenti protocollo n° " . $richiestaProtocollo->getProtocollo();
				break;

			default:
				$oggetto = "Comunicazione generica n° " . $richiestaProtocollo->getProtocollo();
		}

		return $oggetto;
	}

	protected function getDatiUtenzaMail($richiestaProtocollo) {
		$procedura = $richiestaProtocollo->getProcedura();
		$classe = $richiestaProtocollo->getNomeClasse();
		switch ($classe) {
			case 'RichiestaProtocolloIntegrazione':
			case 'RichiestaProtocolloEsitoIstruttoria':
			case 'RichiestaProtocolloComunicazioneProgetto':
				$idUteIn = $procedura->getIdUteInRobot();
				$idUoIn = $procedura->getIdUoInRobot();
				$user = $procedura->getUtenteRobot();
				break;

			case 'ProtocolloIntegrazionePagamento':
			case 'ProtocolloEsitoIstruttoriaPagamento':
			case 'ProtocolloRichiestaChiarimenti':
            case 'RichiestaProtocolloComunicazioneAttuazione':
				$idUteIn = $procedura->getIdUteInRendRobot();
				$idUoIn = $procedura->getIdUoInRendRobot();
				$user = $procedura->getUtenteRobotRend();
				break;
			
			default:
				throw new \Exception('idUteIn o idUoIn non definiti o tipologia protocollo non definita.');
		}
		
		if(is_null($idUoIn) || is_null($idUteIn) ) {
			throw new \Exception('idUteIn o idUoIn non definiti o tipologia protocollo non definita.');
		}

		return array('idUteIn' => $idUteIn, 'idUoIn' => $idUoIn, 'user' => $user);
	}

}
