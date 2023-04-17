<?php

namespace CipeBundle\Services;

use Symfony\Component\Validator\Context\ExecutionContext;
use Doctrine\Bundle\DoctrineBundle\Registry;
use CipeBundle\Entity\Classificazioni\CupCategoria;
use CipeBundle\Entity\Classificazioni\CupClassificazione;
use CipeBundle\Entity\Classificazioni\CupNatura;
use CipeBundle\Entity\Classificazioni\CupSettore;
use CipeBundle\Entity\Classificazioni\CupSottosettore;
use CipeBundle\Entity\Classificazioni\CupStrumentoProgrammazione;
use CipeBundle\Entity\Classificazioni\CupTipoCoperturaFinanziaria;
use CipeBundle\Entity\Classificazioni\CupTipoIndirizzo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Servizio esteso dalle entites le cui componenti sono usate per validare, serializzare, de-serializzare i dati.
 * @see http://cb.schema31.it/cb/issue/177622
 * 
 * Diagramma delle classi
 * @see http://cb.schema31.it/cb/issue/173380

 * 
 * @access public
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 * @Assert\Callback(methods={"validate"})

 */
class CipeEntityService {

	const COMMON_VALIDATE_MESSAGE_NOT_EMPTY = "L'elemento non pu' essere vuoto";
	const COMMON_VALIDATE_MESSAGE = "elemento non valido";
	const COMMON_VALIDATE_CODE_NOT_EXIST = "il valore indicato non appartiene all'insieme dei valori ammissibili";

	/**
	 * @var Registry
	 */
	protected static $doctrine;

	protected function getDoctrine() {
		return self::$doctrine;
	}

	protected function setDoctrine($doctrine) {
		self::$doctrine = $doctrine;
	}

	protected function getEm() {
		return $this->getDoctrine()->getManager();
	}

	protected $xml_load_file = false;

	function getXml_load_file() {
		return $this->xml_load_file;
	}

	function setXml_load_file($xml_load_file) {
		$this->xml_load_file = $xml_load_file;
	}

	public function __construct($doctrine) {
		if (!\is_null($doctrine))
			$this->setDoctrine($doctrine);
	}

	// ---------------------------------------------------------



	protected static function elabSpecialCharacter($string) {
		$string = str_replace("]]", "", $string);
		$string = str_replace("è", "e'", $string);
		$string = str_replace("é", "e'", $string);
		$string = str_replace("à", "a'", $string);
		$string = str_replace("á", "a'", $string);
		$string = str_replace("ò", "o'", $string);
		$string = str_replace("ó", "o'", $string);
		$string = str_replace("ì", "i'", $string);
		$string = str_replace("í", "i'", $string);
		$string = str_replace("ù", "u'", $string);
		$string = str_replace("ú", "u'", $string);
		$string = str_replace('"', "", $string);
		$string = str_replace("'", "", $string);
		$string = str_replace("&#34;", "", $string);
		$string = str_replace("&#39;", "", $string);
		$string = str_replace("&", "and", $string);

		$string = mb_convert_encoding($string, 'Windows-1252', 'UTF-8');
		return \preg_replace("/[^A-Za-z0-9<>\"\'= \s\.,:_\/+*-;]/", "", $string);
	}

	protected static function filterString($string) {
		$string = \strval($string);
		$string = self::elabSpecialCharacter($string);
		//$string = mysqli::escape_string(trim($string));
		//$pdo = self::$doctrine->getEntityManager()->getConnection()->getWrappedConnection();
		//$string = $pdo->quote(\trim($string));
		$string = self::escapeString($string);
		return $string;
	}

	protected static function setFilterParam($param, $type = null) {
		if (\is_null($param))
			return null;
		switch ($type) {
			case "string": $param = self::filterString($param);
				break;
			case "int": $param = intval($param);
				break;
			case "integer": $param = intval($param);
				break;
			case "float": $param = floatval($param);
				break;
			case "datetime":$param = \is_string($param) ? new \DateTime($param) : $param;
				break;
			default: $param = self::filterString($param);
				break;
		}
		return $param;
	}

	protected static function isNullOrEmpty($value) {
		if (\is_null($value))
			return true;
		if ($value == "")
			return true;
		return false;
	}

	protected $xmlName = null;

	function getXmlName() {
		return $this->xmlName;
	}

	function setXmlName($name) {
		$this->xmlName = $name;
	}

	protected static $validateArray = array();

	protected function getValidateArray() {
		return self::$validateArray;
	}

	protected function setValidateArray($validateArray) {
		self::$validateArray = $validateArray;
	}

	protected function setValidateStatus($name, $status, $type = "attr", $message = null) {
		if (!\in_array($type, array("attr", "inner")))
			throw new \Exception("type elemento per validazione non corretto");
		$validateArray = $this->getValidateArray();
		$node = $this->getXmlName();
		$message = (\is_null($message)) ? self::COMMON_VALIDATE_MESSAGE : $message;
		$array_status_validation = array("status" => $status, "message" => $message);
		$validateArray[$node][$type][$name][] = $array_status_validation;
		$this->setValidateArray($validateArray);
	}

	/**
	 * @var ExecutionContext
	 */
	protected static $ExecutionContext;

	function getExecutionContext() {
		return self::$ExecutionContext;
	}

	function setExecutionContext(ExecutionContext $ExecutionContext) {
		self::$ExecutionContext = $ExecutionContext;
	}

	protected static $sharedArray = array();

	function getSharedArray() {
		return self::$sharedArray;
	}

	function setSharedArray($sharedArray) {
		self::$sharedArray = $sharedArray;
	}

	function setSharedElement($sharedElement) {
		$sharedArray = $this->getSharedArray();
		$name = $sharedElement['name'];
		$value = $sharedElement['value'];
		$sharedArray[$name] = self::setFilterParam($value, self::getType($value));
		$this->setSharedArray($sharedArray);
	}

	static function getType($param) {
		if (\is_string($param))
			return "string";
		if (\is_float($param))
			return "float";
		if (\is_integer($param))
			return "integer";
		return null;
	}

	function assSharedElement($name, $value) {
		$this->setSharedElement(array("name" => $name, "value" => $value));
	}

	function getSharedElement($name) {
		$sharedArray = $this->getSharedArray();
		if (!\array_key_exists($name, $sharedArray))
			return false;
		return $sharedArray[$name];
	}

	public function serialize() {
		return "";
	}

	/**
	 * 
	 * @param string $xml
	 * @return \SimpleXMLElement
	 * @throws \Exception
	 */
	public function deserialize($xml) {
		try {
			if ($this->getXml_load_file())
				$xml = simplexml_load_file($xml);
			else if (\is_string($xml))
				$xml = simplexml_load_string(utf8_encode($xml));
			return $xml;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	/**
	 * 
	 * @param array $elements
	 * @return boolean
	 */
	protected function validateElements($elements) {
		$status = true;
		foreach ($elements as $name => $element) {
			foreach ($element as $array_status) {
				if (!$array_status['status']) {
					$status = false;
					$property_path = $this->getXmlName() . ".$name";
					$message = $array_status['message'];
//					$this->getExecutionContext()->addViolationAtPath($property_path, $message);
					$this->getExecutionContext()->addViolationAt($property_path, $message);
				}
			}
		}
		return $status;
	}

	public function validateAttr() {
		$validateArray = $this->getValidateArray();
		if (!\array_key_exists($this->getXmlName(), $validateArray))
			return true;

		$vArray = $validateArray[$this->getXmlName()];
		if (\array_key_exists("attr", $vArray))
			return $this->validateElements($vArray['attr']);
		return true;
	}

	public function validateInner() {
		$validateArray = $this->getValidateArray();
		if (!\array_key_exists($this->getXmlName(), $validateArray))
			return true;

		$vArray = $validateArray[$this->getXmlName()];

		if (\array_key_exists("inner", $vArray))
			return $this->validateElements($vArray['inner']);
		return true;
	}

	public function validate(ExecutionContext $context) {
		$this->setExecutionContext($context);
		return ($this->validateAttr() && $this->validateInner());
	}

	/**
	 * 
	 * @param string $name
	 * @param string $value
	 * @return boolean
	 * @throws \Exception
	 */
	public function validateClassification($ClassificationObject, $criteria = array(), $returnObject = false) {
		try {
			$ret = $this->getClassification($ClassificationObject, $criteria);
			if (\is_null($ret))
				return false;
			if ($returnObject)
				return $ret;
			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function getClassification($ClassificationObject, $criteria = array()) {
		try {
			$ret = $this->getDoctrine()->getRepository(\get_class($ClassificationObject))->findOneBy($criteria);
			return $ret;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	protected function isNotNullAndIsNotEmpty($value) {
		if (\is_null($value))
			return false;
		if (\is_string($value) && str_replace(" ", "", $value) == "")
			return false;
		return true;
	}

	protected function isNullOrNotEmpty($value) {
		if (\is_null($value))
			return true;
		if (\is_string($value) && str_replace(" ", "", $value) == "")
			return false;
		return true;
	}

	protected function validateStrSize($value, $min, $max) {
		$str_len = strlen(strval($value));
		return ($str_len >= $min && $str_len <= $max);
	}

	protected function strMixedCrtl($value, $mixed = true) {
		if (!\is_string($value))
			return false;
		$str_len = strlen($value);
		$str_check = true;
		$prev_character_check = null;

		for ($i = 0; $i < $str_len; $i++) {
			$character = $value[$i];
			$character_check = is_numeric($character);
			if ($i != 0) {
				if ($character_check != $prev_character_check) {
					$str_check = false;
					break;
				}
			}
			$prev_character_check = $character_check;
		}
		return ($mixed) ? !$str_check : $str_check;
	}

	/**
	 * @param string $value
	 * @param integer $max
	 * @param boolean $numeric
	 * @return boolean
	 */
	protected function maxStrTypeSequenceCtrl($value, $max, $numeric = true) {
		if (!\is_string($value))
			return false;
		$str_len = strlen($value);
		$str_check = true;
		$prev_character_check = null;
		for ($i = 0; $i < $str_len; $i++) {

			$count_sequence = 1;
			$prev_character_check = $value[$i];

			for ($j = 0; $j < $str_len; $j++) {

				$character = $value[$j];

				$character_check = is_numeric($character);
				if (!$numeric)
					$character_check = !$character_check;

				if ($i != $j) {
					if ($character_check && ($character_check == $prev_character_check)) {
						$count_sequence++;
					} else {
						$count_sequence = 1;
					}
				}
				if ($count_sequence > $max) {
					return false;
				}
			}
		}
		return true;
	}

	protected function strMixedAndStrTypeSequenceCtrl($value, $maxNumeric, $maxNoNumeric, $mixed = true) {
		return true;
		return (
				$this->strMixedCrtl($value, $mixed) &&
				$this->maxStrTypeSequenceCtrl($value, $maxNumeric, true) &&
				$this->maxStrTypeSequenceCtrl($value, $maxNoNumeric, false)

				);
	}

	public function checkNumericRange($value, $start, $end) {
		if ($value >= $start && $value <= $end)
			return true;
		return false;
	}

	public function checkNumericPattern($value, $minNumberOfDigits, $maxNumberOfDigits, $minNumberOfDecimal, $maxNumberOfDecimal, $positive = true, $negative = true) {
		if (\is_null($value))
			return false;
		if (!$positive && $value > 0)
			return false;
		if (!$negative && $value < 0)
			return false;

		$strValue = strval($value);
		// Rimozione segno
		$strValue = str_replace("-", "", $strValue);

		$arraySplitValue = explode(".", $strValue);
		if (count($arraySplitValue) > 2)
			return false;

		$cifreParteIntera = count($arraySplitValue) > 0 ? strlen($arraySplitValue[0]) : 0;
		$cifreParteDecimale = count($arraySplitValue) > 1 ? strlen($arraySplitValue[1]) : 0;

		if ($this->checkNumericRange($cifreParteIntera, $minNumberOfDigits, $maxNumberOfDigits) && $this->checkNumericRange($cifreParteDecimale, $minNumberOfDecimal, $maxNumberOfDecimal))
			return true;

		return false;
	}

	protected function validateIfNotNull($element = null) {
		if (\is_null($element))
			return true;
		return $element->validate($this->getExecutionContext());
	}

	public function commonValidateStringParam($type, $val, $name, $min, $max, $strMixedCtrl = true) {
		if (!$this->isNotNullAndIsNotEmpty($val))
			$this->setValidateStatus($name, false, $type, self::COMMON_VALIDATE_MESSAGE_NOT_EMPTY);
		if (!$this->validateStrSize($val, $min, $max))
			$this->setValidateStatus($name, false, $type, "[$val] L'elemento può assumere minimo $min e massimo $max caratteri");
		if ($strMixedCtrl && !$this->strMixedAndStrTypeSequenceCtrl($val, 4, 3, false))
			$this->setValidateStatus($name, false, $type, "[$val] Ammessi massimo 4 caratteri numerici ripetuti e massimo 3 caratteri non numerici ripetuti. Non sono consentiti solo numeri o solo segni matematici.");
	}

	protected function serializeIfNotNull(CipeEntityService $element = null) {
		if (\is_null($element))
			return null;
		return $element->serialize();
	}

	protected function generateXmlNode($nodeName, $attributes = array(), $value = null, $innerElements = array(), $allowEmptyAttr = false) {
		try {
			$early_close = \is_null($value) && count($innerElements) == 0;
			$xml = "";
			if (!\is_null($nodeName)) {
				$nodeName = trim($nodeName);
				$xml = "<$nodeName";
				$attributes_array = array();

				foreach ($attributes as $attribute) {
					$attr_name = $attribute['attr_name'];
					$attr_value = (\array_key_exists("attr_value", $attribute)) ? $attribute['attr_value'] : null;
					$quote = (array_key_exists("quote", $attribute)) ? $attr_value['quote'] : '"';
//						if(!\is_string($attr_value)) $quote="";
					if (!\is_null($attr_value) || $allowEmptyAttr)
						$attributes_array[] = $attr_name . "=" . $quote . $attr_value . $quote;
				}
				if (count($attributes_array) > 0) {
					$xml_attributes = implode(" ", $attributes_array);
					$xml .= " $xml_attributes";
				}
				if ($early_close) {
					$xml .= "/>";
				} else {
					$xml .= ">";
				}
			}
			if (!\is_null($value)) {
				if (\is_string($value) && !\is_null($nodeName))
					$xml .= trim($value);
				else
					$xml .= $value;
			} else {
				foreach ($innerElements as $innerElement) {
					$inner_nodeName = $innerElement['nodeName'];
					$inner_attributes = array_key_exists("attributes", $innerElement) ? $innerElement['attributes'] : array();
					$inner_value = array_key_exists("value", $innerElement) ? $innerElement['value'] : null;
					$inner_innerElement = array_key_exists("innerElement", $innerElement) ? $innerElement['innerElement'] : array();
					$xml_ret = $this->generateXmlNode($inner_nodeName, $inner_attributes, $inner_value, $inner_innerElement);
					if ($this->isNotNullAndIsNotEmpty($xml_ret))
						$xml .= $xml_ret;
				}
			}
			if (!\is_null($nodeName) && !$early_close)
				$xml .= "</$nodeName>";
			return $xml;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	protected static function EscapeString($string) {
		$data = null;
		if ($string != null && strlen($string) > 0) {
			$string = str_replace("\\", "\\\\",$string);
			$string = str_replace("'", "\\'",$string);
			$string = str_replace("\0", "\\0",$string);
			$string = str_replace("\n", "\\n",$string);
			$string = str_replace("\r", "\\r",$string);
			$string = str_replace("\"", "\\\"",$string);
			$string = str_replace("\\x1a", "\\Z",$string);
			$data = $string;
		}
		return $data;
	}

}

?>