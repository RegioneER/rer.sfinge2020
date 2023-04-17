<?php

namespace DocumentoBundle\Component;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Description of GestioneFirmaDigitale
 *
 * @author Giuseppe Di Sparti
 */
class GestioneFirmaDigitale {
	
	/**
	 * Codici errori
	 */
	const DSH_ERR_OPENSSL_PKCS7_VERIFY_DOC = 0x0100;	
	const DSH_ERR_OPENSSL_PKCS7_VERIFY_DOC_CONTENT = 0x0101;
	const DSH_ERR_OPENSSL_PKCS7_VERIFY_CERT_CONTENT = 0x0102;	
	const DSH_ERR_OPENSSL_x509_PARSE = 0x0105;
	
	
	protected $errorDesc = array(		
		GestioneFirmaDigitale::DSH_ERR_OPENSSL_PKCS7_VERIFY_DOC => "Errore nella verifica del documento: '@filename'\n[@error_openssl]",
		GestioneFirmaDigitale::DSH_ERR_OPENSSL_PKCS7_VERIFY_DOC_CONTENT => "Errore nell'estrazione del documento: '@filename'\n[@error_openssl]",
		GestioneFirmaDigitale::DSH_ERR_OPENSSL_PKCS7_VERIFY_CERT_CONTENT => "Errore nell'estrazione del certificato: '@filename'\n[@error_openssl]",
		GestioneFirmaDigitale::DSH_ERR_OPENSSL_x509_PARSE => "Errore nel parsing del certificato\n[@error_openssl]",			
	);
		
	protected $inputFileContent;
	protected $inputFileContentSMime;
	protected $signInfo;
	protected $contenutoCertificato;
	protected $contenutoDocumentoInterno;
	protected $documentoInternoMimeType;
	protected $errors;
	protected $caDirectory;
	protected $tempDir;
	protected $signInfoForControl;

	
	public function __construct($caDirectory = null) {

		if (!\is_null($caDirectory)) {
			$this->caDirectory = $caDirectory;			
		}
		
		$this->setTempDir(\sys_get_temp_dir());
	}

	public function setCADirectory($caDirectory) {
		$this->caDirectory = $caDirectory;
	}

	public function getCADirectory() {
		return $this->caDirectory;
	}
	
	public function getTempDir() {
		return $this->tempDir;
	}

	public function setTempDir($tempDir) {		
		
		/**
		 * facciamo in modo che l'ultimo carattere sia una slash
		 */		
		$length = strlen($tempDir);		
		$tempDir[$length - 1] == '/' ? $this->tempDir = $tempDir : $this->tempDir = $tempDir . '/';
	}
	
	public function getContenutoDocumentoInterno() {
		return $this->contenutoDocumentoInterno;
	}

	public function getContenutoCertificato() {
		return $this->contenutoCertificato;
	}
	
	public function getDocumentoInternoMimeType(){
		return $this->documentoInternoMimeType;		
	}
	
	/**
	 * carica il P7M tramite path
	 *  
	 * @param string $inputFilePath
	 * @return boolean
	 */
	public function loadDocumentFromPath($inputFilePath) {
		
		$this->reset();
		
		if (!file_exists($inputFilePath))
			return false;
		
		$this->inputFileContent = file_get_contents($inputFilePath);

		$this->convertiToSMime($this->inputFileContent, "temp");
		
		return is_null($this->inputFileContentSMime) ? false : true;
	}
	
	/**
	 * carica il P7M tramite il suo contenuto
	 * 
	 * @param string $inputFileContent
	 * @return boolean
	 */
	public function loadDocumentFromContent(&$inputFileContent) {
		
		$this->reset();
		$this->inputFileContent = $inputFileContent;

		$this->convertiToSMime($this->inputFileContent, "temp");
		
		return is_null($this->inputFileContentSMime) ? false : true; 
	}
	
	public function getSignInfo(){
		return $this->signInfo;
	}

	/**
	 * @return mixed
	 */
	public function getSignInfoForControl()
	{
		return $this->signInfoForControl;
	}
	
	/**
	 * Torna il primo certificato 
	 * 
	 * @return null|array
	 */
	public function getFirstSignInfo() {
		
		if (is_null($this->signInfo) || count($this->signInfo) == 0)
			return null;
		
		foreach ($this->signInfo as $cert_info) {
			
				$signInfo = array();
				$fieldKeys = array("serialeCertificato", "hash", "dataRilascio", "dataScadenza", "commonNameSoggetto", "nomeSoggetto",
					"cognomeSoggetto", "codiceFiscaleSoggetto", "organismoSoggetto", "commonNameCA", "serialeCA", "organismoCA");

				foreach ($fieldKeys as $key) {
					if (array_key_exists($key, $cert_info)) {
						$signInfo[$key] = $cert_info[$key];
					} else {
						$signInfo[$key] = "";
					}
				}

				return $signInfo;
			}		
	}
	
	/**
	 * serve a recuperare uno specifico certificato 
	 * nel caso di eventuali firme multiple
	 */
	public function getSignInfoByCf($codiceFiscale) {

		if (is_null($this->signInfo) || count($this->signInfo) == 0)
			return null;

		foreach ($this->signInfo as $cert_info) {

			if (!\is_null($cert_info['codiceFiscaleSoggetto']) && $cert_info['codiceFiscaleSoggetto'] == $codiceFiscale) {

				$signInfo = array();
				$fieldKeys = array("serialeCertificato", "hash", "dataRilascio", "dataScadenza", "commonNameSoggetto", "nomeSoggetto",
					"cognomeSoggetto", "codiceFiscaleSoggetto", "organismoSoggetto", "commonNameCA", "serialeCA", "organismoCA");

				foreach ($fieldKeys as $key) {
					if (array_key_exists($key, $cert_info)) {
						$signInfo[$key] = $cert_info[$key];
					} else {
						$signInfo[$key] = "";
					}
				}

				return $signInfo;
			}
		}
	}
	
	
	
	/**
	 * $userCf è necessario perchè il documento potrebbe avere firme multiple
	 * e bisogna quindi identificare il certificato corretto
	 * 
	 * @param string $userCf
	 * @return boolean | string
	 */
	public function getX509Hash($userCf) {
		
		$resp = true;

		if (\is_null($this->signInfo) || count($this->signInfo) == 0)
			$resp = $this->analizzaCertificato();

		if ($resp === true && count($this->signInfo) > 0) {
			
			foreach ($this->signInfo as $cert_info) {
				
				if ($cert_info['codiceFiscaleSoggetto'] == $userCf){
					
					$concat = "";
					
					foreach ($cert_info as $field){
						$concat .= $field;
					}
					
					return md5($concat);
				}		
			}
		}
		
		return false;
		
	}
	
	/**
	 * 
	 * @param bool $analizzaCertificato
	 * @param bool $estraiContenutoDocumento
	 * @return boolean|int
	 */
	public function verificaDocumento($analizzaCertificato = false, $estraiContenutoDocumentoInterno = false) {

		$this->errors = array();
		$flag = 0;

		$inputSMimeFile = tempnam($this->tempDir, "tmpSMime");

		$handle = fopen($inputSMimeFile, "w");
		fwrite($handle, $this->inputFileContentSMime);
		fclose($handle);

		$outputCertFile = tempnam($this->tempDir, "tmpCert");

		if ($estraiContenutoDocumentoInterno){
			$outputDocumentFile = tempnam($this->tempDir, "tmpDoc");
			$resp = @openssl_pkcs7_verify($inputSMimeFile, $flag, $outputCertFile, array($this->getCADirectory()), $this->getCADirectory(), $outputDocumentFile);
		} else {
			$resp = @openssl_pkcs7_verify($inputSMimeFile, $flag, $outputCertFile, array($this->getCADirectory()));
		}
		
		if ($resp === true && $estraiContenutoDocumentoInterno){
			
			$this->contenutoDocumentoInterno = file_get_contents($outputDocumentFile);
			$file = new File($outputDocumentFile);
			$this->documentoInternoMimeType = $file->getMimeType();	
		}
		
		if ($resp === true && $analizzaCertificato){			
			$this->contenutoCertificato = file_get_contents($outputCertFile);
			$respCert = $this->analizzaCertificato();
			
			if ($respCert !== true)
				return $respCert;
		}
		
		if (file_exists($inputSMimeFile))
			unlink($inputSMimeFile);

		if (file_exists($outputCertFile))
			unlink($outputCertFile);

		if ($estraiContenutoDocumentoInterno && file_exists($outputDocumentFile))
			unlink($outputDocumentFile);

		return $this->handleResponse($resp, self::DSH_ERR_OPENSSL_PKCS7_VERIFY_DOC);
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function analizzaCertificato() {
		
		$resp = true;
		
		if (\is_null($this->contenutoCertificato))
			$resp = $this->estraiContenutoCertificato();

		if ($resp !== true) {
			return $resp;
		}

		$this->signInfo = array();

		$certs = explode("-----END CERTIFICATE-----\n", $this->contenutoCertificato);

		foreach ($certs as $cert) {

			if ($cert == '')
				continue;
			
			$signInfo = array();

			$cert .= '-----END CERTIFICATE-----';
			$cert = @openssl_x509_parse($cert);

			if ($cert === false) {
				return $this->handleResponse(false, self::DSH_ERR_OPENSSL_x509_PARSE);				
			}
			
			if (array_key_exists("serialNumber", $cert))
				$signInfo["serialeCertificato"] = $cert["serialNumber"];
			
			if (array_key_exists("hash", $cert))
				$signInfo["hash"] = $cert["hash"];
			
			if (array_key_exists("validFrom_time_t", $cert))
				$signInfo["dataRilascio"] = date("Y-m-d", $cert["validFrom_time_t"]) . "\r";

			if (array_key_exists("validTo_time_t", $cert))
				$signInfo["dataScadenza"] = date("Y-m-d", $cert["validTo_time_t"]) . "\r";
			
			/**
			 * dati del soggetto firmatario
			 */
			if (array_key_exists("subject", $cert)){
				
				if (array_key_exists("CN", $cert["subject"]))
					$signInfo["commonNameSoggetto"] = $cert["subject"]["CN"];

				if (array_key_exists("GN", $cert["subject"]))
					$signInfo["nomeSoggetto"] = $cert["subject"]["GN"];

				if (array_key_exists("SN", $cert["subject"]))
					$signInfo["cognomeSoggetto"] = $cert["subject"]["SN"];

				/**
				 * gdisparti
				 * 13/09/2017
				 * mi sono accorto che in alcuni certificati c'è un formato diverso di serialNumber..che poi rappresenterebbe il cf del soggetto
				 * 
				 * per cui è stata aggiunto un fallback che se non trova la sintassi IT:CF
				 * prova a fare l'explode per la sintassi TINIT-CF
				 */
				if (array_key_exists("serialNumber", $cert["subject"])) {
					$temp = \explode(':', $cert["subject"]["serialNumber"]);
					if(count($temp) != 2){
						$temp = \explode('-', $cert["subject"]["serialNumber"]);
					}
					if (count($temp) == 2 && \strlen($temp[1]) == 16){
						$signInfo["codiceFiscaleSoggetto"] = $temp[1];
					}
				}			
				
				if (array_key_exists("O", $cert["subject"]))
					$signInfo["organismoSoggetto"] = $cert["subject"]["O"];	
			
			}
			
			/**
			 * dati della CA
			 */
			if (array_key_exists("issuer", $cert)) {

				if (array_key_exists("CN", $cert["issuer"]))
					$signInfo["commonNameCA"] = $cert["issuer"]["CN"];

				if (array_key_exists("serialNumber", $cert["issuer"]))
					$signInfo["serialeCA"] = $cert["issuer"]["serialNumber"];
				
				if (array_key_exists("O", $cert["issuer"]))
					$signInfo["organismoCA"] = $cert["issuer"]["O"];
			}
			
			$this->signInfo[] = $signInfo;
			$this->signInfoForControl[] = $signInfo;
		}

		return count($this->signInfo) > 0 ? true : false;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	private function estraiContenutoCertificato() {
		
		if (is_null($this->inputFileContentSMime))
			return false;

		$flag = PKCS7_NOVERIFY | PKCS7_NOCHAIN | PKCS7_NOSIGS;

		$inputSMimeFile = tempnam($this->tempDir, "tmpSMime");

		$handle = fopen($inputSMimeFile, "w");
		fwrite($handle, $this->inputFileContentSMime);
		fclose($handle);

		$outputCertFile = tempnam($this->tempDir, "tmpCert");

		$resp = @openssl_pkcs7_verify($inputSMimeFile, $flag, $outputCertFile, array($this->getCADirectory()));

		$this->contenutoCertificato = file_get_contents($outputCertFile);


		if (file_exists($inputSMimeFile))
			unlink($inputSMimeFile);

		if (file_exists($outputCertFile))
			unlink($outputCertFile);

		return $this->handleResponse($resp, self::DSH_ERR_OPENSSL_PKCS7_VERIFY_CERT_CONTENT);
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function estraiContenutoDocumentoInterno() {

		$this->errors = array();

		$flag = PKCS7_NOVERIFY | PKCS7_NOCHAIN | PKCS7_NOSIGS;

		$inputSMimeFile = tempnam($this->tempDir, "tmpSMime");

		$handle = fopen($inputSMimeFile, "w");
		fwrite($handle, $this->inputFileContentSMime);
		fclose($handle);

		$outputCertFile = tempnam($this->tempDir, "tmpCert");
		$outputDocumentFile = tempnam($this->tempDir, "tmpDoc");

		$resp = @openssl_pkcs7_verify($inputSMimeFile, $flag, $outputCertFile, array($this->getCADirectory()), $this->getCADirectory(), $outputDocumentFile);

		$this->contenutoDocumentoInterno = file_get_contents($outputDocumentFile);
					
		$file = new File($outputDocumentFile);
		$this->documentoInternoMimeType = $file->getMimeType();
		
		if (file_exists($inputSMimeFile))
			unlink($inputSMimeFile);

		if (file_exists($outputDocumentFile))
			unlink($outputDocumentFile);

		return $this->handleResponse($resp, self::DSH_ERR_OPENSSL_PKCS7_VERIFY_DOC_CONTENT);
	}
	
	
	/**
	 * Verifica che tra gli eventuali certificati usati per firmare il documento
	 * ne esista uno associato al codice fiscale in ingresso
	 * 
	 * @param string $userCf codice fiscale dell'utente che carica il documento
	 * @return boolean|null
	 */
	public function verifySubjectCf($userCf) {

		/**
		 * l'attributo signInfo è popolato invocando la analizzaCertificato
		 */
		if (is_null($this->signInfo) || count($this->signInfo) == 0)
			return null;


		/**
		 * estraggo i codici fiscali dei certificati contenuti nel p7m (potrebbero esserci più firme)
		 */
		foreach ($this->getSignInfoForControl() as $cert_info) {

			if (!\is_null($cert_info['codiceFiscaleSoggetto']) && $cert_info['codiceFiscaleSoggetto'] == $userCf)
				return true;
		}

		return false;
	}
		
	/**
	 * 
	 * @param string $inputFileContent
	 * @param string $fileName
	 * @return boolean
	 */
	private function convertiToSMime($inputFileContent, $fileName = "temp") {

        if (is_null($inputFileContent))
            return false;

        if ($this->isBase64Encoded($inputFileContent)) {
            $inputFileContentBase64 = chunk_split($inputFileContent);
        } else {
            $inputFileContentBase64 = chunk_split(base64_encode($inputFileContent));
        }
        $this->inputFileContentSMime = "MIME-Version: 1.0\nContent-Disposition: attachment; filename=\"{$fileName}\"\nContent-Type: application/x-pkcs7-mime; name=\"{$fileName}\"\nContent-Transfer-Encoding: base64\n\n{$inputFileContentBase64}";
    }

    /**
	 * 
	 * @param bool|int $resp
	 * @param int $error
	 * @return boolean|-1
	 */
	private function handleResponse($resp, $error) {

		if ($resp === false || $resp === -1) {
			$this->setError($error, __METHOD__, __LINE__, array('error_openssl' => $this->getOpenSSLError()));
			return $resp;
		}

		return true;
	}
	
	public function reset() {		
		$this->signInfo = array();
		$this->errors = array();
		$this->inputFileContent = null;
		$this->inputFileContentSMime = null;
		$this->contenutoCertificato = null;
	}

	
	
	/**
	 * Setta le informazioni sull'errore
	 * @return void
	 */
	protected function setError($errorCode, $errorMethod, $errorLine, array $errorParams = null) {

		$errorDesc = $this->errorDesc[$errorCode];

		if ($errorParams != null) {
			foreach ($errorParams as $errorParam => $errorParamValue) {
				$errorDesc = str_replace('@' . $errorParam, $errorParamValue, $errorDesc);
			}
		}
		if ($errorMethod != null)
			$errorDesc .= " - method '{$errorMethod}'";
		if ($errorLine != null)
			$errorDesc .= " line {$errorLine}";

		$this->errors[] = "({$errorCode}) " . $errorDesc;
	}

	/**
	 * Restituisce l'elenco degli errori
	 * @return Array degli errori
	 */
	public function getErrors() {
		return $this->errors;
	}
	
	/**
	 * Restituisce la descrizione dell'eventuale errore openssl occorso
	 * @return string La descrizione dell'errore (stringa vuota se nessun errore si è verificato)
	 */
	protected function getOpenSSLError() {
		$error = '';
		while ($msg = openssl_error_string())
			$error .= '\n' . $msg;
		return $error;
	}
        
    protected function isBase64Encoded(string $s): bool {
        // Check if there are valid base64 characters
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s))
            return false;

        // Decode the string in strict mode and check the results
        $decoded = base64_decode($s, true);
        if (false === $decoded)
            return false;

        // Encode the string again
        if (base64_encode($decoded) != $s)
            return false;

        return true;
    }

}

?>
