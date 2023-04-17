<?php
namespace ProtocollazioneBundle\Service;

use ProtocollazioneBundle\Service\DocERLogService;

/**
 * Description of CurlSoapClient
 *
 * @author gaetanoborgosano
 * @author refactoring by Davide Cannistraro
 */
class CurlSoapClient extends \SoapClient {
	
	protected $soap_WSDL;
	protected $soap_EP;	
	protected static $token;		
	protected $defaultNamespaceParams = array();
	protected $namespaceParams=array();	
	protected $namespaces=array();	
	protected $methodNamespacePrefix;
	
	protected $app_function;
	function getApp_function() { return $this->app_function; }
	function setApp_function($app_function) { $this->app_function = $app_function; }

	/**
	 *
	 * @var DocERLogService
	 */
	protected $LoggerService;
	function getLoggerService() { return $this->LoggerService; }
	function setLoggerService(DocERLogService $LoggerService) { $this->LoggerService = $LoggerService; }
	
    function getSoap_WSDL() { return $this->soap_WSDL; }
	function getSoap_EP() {	return $this->soap_EP; }
	function setSoap_WSDL($soap_WSDL) { $this->soap_WSDL = $soap_WSDL; }
	function setSoap_EP($soap_EP) { $this->soap_EP = $soap_EP; }

	static function getToken() { return self::$token; }
	static function setToken($token) { self::$token = $token; }

	function getDefaultNamespaceParams() { return $this->defaultNamespaceParams; }
	function getNamespaceParams() { return $this->namespaceParams; }
	function setDefaultNamespaceParams($defaultNamespaceParams) {
		$this->defaultNamespaceParams = $defaultNamespaceParams;
		$this->resetNamespaceParams();
	}
	function setNamespaceParams($namespaceParams) { $this->namespaceParams = $namespaceParams; }
	public function resetNamespaceParams() { $this->setNamespaceParams($this->getDefaultNamespaceParams()); }
	
	function getNamespaces() { return $this->namespaces; }
	function setNamespaces($namespaces) { $this->namespaces = $namespaces; }

	function getMethodNamespacePrefix() { return $this->methodNamespacePrefix; }
	function setMethodNamespacePrefix($methodNamespacePrefix) { $this->methodNamespacePrefix = $methodNamespacePrefix; }

	
		
	
	function buildXmlNamespaceDef() {
		$namespaces = $this->getNamespaces();
		$namespaceDef = "";
		foreach($namespaces as $prefix => $url) {
			$namespaceDef.=" xmlns:$prefix=\"$url\"";
		}
		return $namespaceDef;
	}
		
	public function buildNamespaceParamsByKeyArray($keyArray, $namespacePrefix) {
		$namespaceParams = $this->getDefaultNamespaceParams();
		foreach($keyArray as $key) {
			$namespaceParams[$key] = $namespacePrefix;
		}
		return $namespaceParams;
	}
	
	public function buildAndSetNamespaceParamsByKeyArray($keyArray, $namespacePrefix) {
		$namespaceParams = $this->buildNamespaceParamsByKeyArray($keyArray, $namespacePrefix);
		$this->setNamespaceParams($namespaceParams);
	}
	
	public function __construct($wsdl, array $options = null) {
		parent::__construct($wsdl, $options);
		$this->setSoap_WSDL($wsdl);
		$this->resetNamespaceParams();
		if(array_key_exists("location", $options)) $this->setSoap_EP($options['location']);
	}

	
	public function traceLog($message, $app_function_target, $code=null) {
		$LoggerService = $this->getLoggerService();
		$app_function = $this->getApp_function();
		
		if(!\is_null($LoggerService)) 
			$LoggerService->createLog ($message, $app_function, $app_function_target, $code);
	}
	
	public function __doRequest($request, $location, $action, $version, $one_way = 0) {
		$wsdl = $this->getSoap_WSDL();
		//$this->traceLog("wsdl:[$wsdl]", "REQU");
		//$this->traceLog("location:[$location]", "REQU");
		$this->traceLog($request, "REQU");
		$mode = "EP";
		return $this->__Post_CurlSoapCall($request, $mode, 120, null, false, null, 'curl', null);

		
//		return parent::__doRequest($request, $location, $action, $version, $one_way);
	}
	
	

	
//	public function __call($function_name, $arguments) {
//		return;
//		$this->traceLog("PRE_CALL", "RESP");
//		$resp = parent::__call($function_name, $arguments);
//		$this->traceLog("POST_CALL", "RESP");
//		$resp_log = print_r($resp, true);
//		$this->traceLog($resp_log, "RESP");
//		return $resp;
//	}

	
	protected function buildBaseRequestXmlDocument($methodName) {
		
		$soap_request_path_file = __DIR__."/../Resources/schemi/xml_schema/soap_request.xml";
		$xml_soap_request = file_get_contents($soap_request_path_file);
		$xml_soap_request = str_replace("__XML_NAMESPACES__", $this->buildXmlNamespaceDef(), $xml_soap_request);
		$xml_soap_request = str_replace("__METHOD_NAMESPACE_PREFIX__", $this->getMethodNamespacePrefix(), $xml_soap_request);
		$xml_soap_request = str_replace("__METHOD__", $methodName, $xml_soap_request);
		$xml_soap_request = str_replace("__TOKEN__", $this->getToken(), $xml_soap_request);

		return $xml_soap_request;
	}

	protected function isValuableKey($key) {
		if(\is_integer($key) || \is_numeric($key) || strstr($key, "![CDATA[")) return true;
		return false;
	}
	
	protected function getPrefixNamespaceParam($key,$prefixArray=array()) {
		$prefix  = (\array_key_exists($key, $prefixArray)) ? $prefixArray[$key] : $this->getMethodNamespacePrefix();
		return $prefix;
	}
	
	
	protected function xmlParamsForMethod($xmlDocument, $paramsArray, $prefixArray=array()) {
		if(!\is_array($paramsArray)) {
			return $paramsArray;
		}
		
		foreach ($paramsArray as $key => $value) {
			if ($this->isValuableKey($key)) {
					$xmlDocument = $xmlDocument. $this->xmlParamsForMethod("", $value);
			} else {
//				$prefix = ($key == "metadata" || $key == "file" || $key == "docId") ? "web" : "xsd";
				$prefix = $this->getPrefixNamespaceParam($key, $this->getNamespaceParams());
				
				$xmlDocument = $xmlDocument . "<$prefix:$key>".$this->xmlParamsForMethod("", $value)."</$prefix:$key>";
			}
		}
		return $xmlDocument;
	}
	
	protected function makeCurlXmlRequest($methodName, $paramsArray) {
		$xmlBaseRequest = $this->buildBaseRequestXmlDocument($methodName);
		$xmlParamsForMethod = $this->xmlParamsForMethod("", $paramsArray, $this->getNamespaceParams());
		$curlXmlRequest = str_replace("__PARAMS__", $xmlParamsForMethod, $xmlBaseRequest);

		return $curlXmlRequest;
	}
	
	protected function getMimeBoundary($data) {
		preg_match('/--(?<MIMEBoundary>.+?)\s/', $data, $match);
		$mimeBoundary = $match['MIMEBoundary']; // Always unique compared to content
		return $mimeBoundary;
	}
	
	protected function parseBoundaryInsideFileMTOMattachment($filePathName) {
		try {
			$maxlen = 2048;
			$data = file_get_contents($filePathName, false, null, -1, $maxlen);
			$MimeBoundary = $this->getMimeBoundary($data);
			$firstPositionMimeBoundary = strpos($data, $MimeBoundary);
			$pad = str_pad("#", strlen($MimeBoundary),"#");
			$data = substr_replace($data, $pad, $firstPositionMimeBoundary, $firstPositionMimeBoundary+ strlen($MimeBoundary));

			
			$lastPositionMimeBoundary = strpos($data, $MimeBoundary);
			return array("MimeBoundary" => $MimeBoundary, "last_position" => $lastPositionMimeBoundary);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	protected function retreiveDocumentFromMTOMattachment($filePathName, $new_filePathName) {
		try {
			$parseBoundary = $this->parseBoundaryInsideFileMTOMattachment($filePathName);
			$length = $parseBoundary["last_position"];
			$fp = fopen($filePathName, "rb");
			$first = fread($fp, $length);			
			$searchLength = 5;
			for($i=0; $i<$searchLength;$i++) {
				$search = fgets($fp);
				
			}
			$fp_new = fopen($new_filePathName, "wb");
			
			while(!feof($fp)) {
				$reader = fgets($fp);
				$firstPositionMimeBoundary = strpos($reader, $parseBoundary['MimeBoundary']);
				if(!$firstPositionMimeBoundary) {
					fputs($fp_new, $reader);
				}
			}
			
			fclose($fp);
			fclose($fp_new);
			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	protected function stripSoapHeaders($response) {
		// Find first occurance of xml tag
		preg_match('/(?<xml><.*?\?xml version=.*>)/', $response, $match);
		$xml = $match['xml'];
		return $xml;
	}

	protected function parseMimeData($data) {
		// Find MIME boundary string
		preg_match('/--(?<MIMEBoundary>.+?)\s/', $data, $match);
		$mimeBoundary = $match['MIMEBoundary']; // Always unique compared to content
		// Copy headers to client
		preg_match('/(Content-Type: .+?)' . PHP_EOL . '/', $data, $match);
		preg_match('/(Content-Transfer-Encoding: .+?)' . PHP_EOL . '/', $data, $match);

		// Remove string headers and MIME boundaries from data
		preg_match('/(.*Content-ID.+' . PHP_EOL . ')/', $data, $match);
		$start = strpos($data, $match[1]) + strlen($match[1]);
		$end = strpos($data, "--$mimeBoundary--");
		$data = substr($data, $start, $end - $start);

		return trim($data, "\r\n");
	}
	
	public function filterMTOMResponse($output) {
		$data = $this->parseMimeData($output);
        $resp = $this->stripSoapHeaders($data);
		return $resp;
	}
	
	public function filterMTOMattachment($output) {
	   // Find MIME boundary string
        preg_match('/--(?<MIMEBoundary>.+?)\s/', $output, $match);
        $mimeBoundary = $match['MIMEBoundary']; // Always unique compared to content
		$parserData = explode($mimeBoundary, $output);
		$attachment = substr($parserData[2], 0, -2);
		$attachment = substr( $attachment, strpos($attachment, "\n")+1 );
		$attachment = substr( $attachment, strpos($attachment, "\n")+1 );
		$attachment = substr( $attachment, strpos($attachment, "\n")+1 );
		$attachment = substr( $attachment, strpos($attachment, "\n")+1 );
		$attachment = substr( $attachment, strpos($attachment, "\n")+1 );

        return trim($attachment);
	}

	protected static function getSysTempDir() {
		$tempDir = \sys_get_temp_dir();
		$length = strlen($tempDir);
		return $tempDir[$length - 1] == '/' ? $tempDir : $tempDir . '/';
	}
	
		
	protected function __Post_CurlSoapCall($soap_request, $mode, $timeout = 120, $MTOMFilterResponseMode = null, $response_on_file=false, $response_fileName, $function_name="curl", $arguments=null) {
		try {
			// Scrittura richiesta curl
			$this->traceLog($soap_request, "REQU");
			$soap_do = curl_init();

			$curlopt_url = ($mode == "EP") ? $this->getSoap_EP() : $this->getSoap_WSDL(); 

			curl_setopt($soap_do, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($soap_do, CURLOPT_MAXREDIRS, 100);
			curl_setopt($soap_do, CURLOPT_URL, $curlopt_url);
			curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($soap_do, CURLOPT_BINARYTRANSFER, TRUE);
			curl_setopt($soap_do, CURLOPT_HTTPHEADER, array('Content-type: application/binary'));
			curl_setopt($soap_do, CURLOPT_POST, true);

			curl_setopt($soap_do, CURLOPT_POSTFIELDS, $soap_request);

			curl_setopt($soap_do, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
			curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($soap_do, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($soap_do, CURLOPT_VERBOSE, true);
			
			if($response_on_file) {
				
				$prefix = uniqid("__Post_CurlSoapCall_", true);
				$tempFile = tempnam($this->getSysTempDir(), $prefix);
				$fp = fopen($tempFile, 'wb+');
				
				if(!$fp) throw new \Exception("impossibile aprire un file temporaneo per elaborazione della risposta");
				
				curl_setopt($soap_do, CURLOPT_FILE, $fp);
			}
			
			$output = curl_exec($soap_do);
			if($output == false) throw new \Exception("La risposta alla curl è vuota");

			$http_status_code = curl_getinfo($soap_do, CURLINFO_HTTP_CODE);
			curl_close($soap_do);
			
			// Scrittura risposta curl
			$this->traceLog($output, "RESP", $http_status_code);
			
			if(strlen($output) == 0 || (strlen(str_replace(" ", "", $output)) == 0)) {
				$output = $http_status_code;
			}

			switch ($MTOMFilterResponseMode) {
				case "response":
					$resp = $this->filterMTOMResponse($output);
					break;

				case "attachment":
					if($response_on_file) {
						fclose($fp);
						$response_fileName = (\is_null($response_fileName)) ? "_new_$tempFile" : $response_fileName;

						$status = $this->retreiveDocumentFromMTOMattachment($tempFile, $response_fileName);
						if($status === true) {
							$resp = $response_fileName;
						}

						if(!$output) throw new \Exception("impossibile legger il file temporaneo per elaborazione della risposta");
						unlink($tempFile);
					} else {
						$resp = $this->filterMTOMattachment($output);
					}
					break;

				default:
					$resp = $output;
					break;
			}


			return $resp;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	
	public function __curl_call($function_name, $arguments, $mode, $timeout = 120, $MTOMFilterResponseMode, $response_on_file=false, $response_fileName=null) {
		$soap_request = $this->makeCurlXmlRequest($function_name, $arguments);
		return $this->__Post_CurlSoapCall($soap_request, $mode, $timeout, $MTOMFilterResponseMode, $response_on_file, $response_fileName, $function_name, $arguments);
	}

}
