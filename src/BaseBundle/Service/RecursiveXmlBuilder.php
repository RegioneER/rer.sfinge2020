<?php

namespace BaseBundle\Service;

/**
 * Description of RecursiveXmlBuilder
 *
 * @author gaetanoborgosano
 * @author refactoring by Davide Cannistraro & Gaetano Borgosano
*/
class RecursiveXmlBuilder {
	
    protected $encoding = "UTF-8";
    protected $dirXmlFiles;
    protected $rootXmlNode;
    protected $xsdFile;
    protected $xsdValidation = false;
    protected $errors = array();
    protected $xmlnamespace = null;


    public function getEncoding() { return $this->encoding; }
    public function setEncoding($encoding) { $this->encoding = $encoding; }
    public function getRootXmlNode() { return $this->rootXmlNode; }
    public function setRootXmlNode($rootXmlNode) { $this->rootXmlNode = $rootXmlNode; }
    public function getXmlnamespace() { return $this->xmlnamespace; }
    public function getStringXmlNamespace () { return (!\is_null($this->getXmlnamespace()) && strlen($this->getXmlnamespace()) >0) ? " ".$this->getXmlnamespace() : ""; }
    public function setXmlnamespace($xmlnamespace) { $this->xmlnamespace = $xmlnamespace; }
    public function getXsdFile() { return $this->xsdFile; }
    public function getXsdValidation() { return $this->xsdValidation; }
    public function getDirXmlFiles() { return $this->dirXmlFiles; }
    public function setDirXmlFiles($dirXmlFiles) { $this->dirXmlFiles = $dirXmlFiles; }
    public function setXsdFile($xsdFile) { $this->xsdFile = $xsdFile; }
    public function setXsdValidation($xsdValidation) { $this->xsdValidation = $xsdValidation; }
    protected function setErrors($errors) { $this->errors = $errors; }
    public function getErrors() { return $this->errors; }
    public function getStatus() { return (count($this->getErrors()) > 0) ? 500 : 200; }


    public function init(
                                                            $dirXmlFiles, 
                                                            $rootXmlNode, 
                                                            $xsdFile, 
                                                            $xsdValidation=false,
                                                            $encoding="UTF-8",
                                                            $xmlnamespace=null
                                                            ) {
            $this->setDirXmlFiles($dirXmlFiles);
            $this->setRootXmlNode($rootXmlNode);
            $this->setXsdFile($xsdFile);
            $this->setXsdValidation($xsdValidation);
            $this->setEncoding($encoding);
            $this->setXmlnamespace($xmlnamespace);
    }


    public function buildXml($param) {
            try {
                    $this->errors = array();
                    $firstResult = $this->buildXmlMetadata($param, $this->getRootXmlNode());

                    $secondResult = $this->clearXml($firstResult);

                    if (!$secondResult) {
                            throw new \Exception("Errore nella generazione dell'xml");
                    } else {
                            return $secondResult;
                    }
            } catch (\Exception $ex) {
                    $this->errors[] = "Errore generico: " . $ex->getMessage();
            }
    }

    public function buildXmlMetadata($param, $xml_nodo) {

            if (!\is_array($param))
                    return $param;

            $basePath = $this->getDirXmlFiles();

            foreach ($param as $key => $value) {
                    $many = $key;

                    if (is_array($value)) {
                            $subpath = trim(str_replace("##", "", $key));
                            $strpos = strpos($subpath, "_");
                            if($strpos !== FALSE){
                                    $subpath = substr($subpath, 0, $strpos);
                                    $many = "##$subpath##";
                            }
                            $path = $basePath . $subpath . ".xml";
                            $content = file_get_contents($path);
                            $xml_key = $this->buildXmlMetadata($value, $content);
                            $xml_nodo = str_replace($many, "$xml_key $many", $xml_nodo);
                    } else {
                            $xml_nodo = str_replace($key, $value, $xml_nodo);
                    }
            }

            return $xml_nodo;
    }

    protected function normalized($key) {

    }


    protected function xmlFilters($xml) {
            $xml = preg_replace("/##[a-zA-Z0-9_]+##/", "", $xml);
            $xml = preg_replace("/<[a-zA-Z0-9:]+>__[a-zA-Z0-9]+__<\/[a-zA-Z0-9:]+>/", "", $xml);
            $xml = preg_replace("/<[a-zA-Z0-9:]+> <\/[a-zA-Z0-9:]+>/", "", $xml);
            $xml = preg_replace("/<[a-zA-Z0-9:]+><\/[a-zA-Z0-9:]+>/", "", $xml);
            $xml = str_replace("##", "", $xml);
            $xml = preg_replace('/([[:space:]])+\</', '<', $xml);
            $xml = preg_replace('/>([[:space:]])+/', '>', $xml);
        $xml = preg_replace("/<[a-zA-Z0-9:]+><!\[CDATA\[\]\]><\/[a-zA-Z0-9:]+>/", "", $xml);

            // &#39;
//		$xml = str_replace(" & ", " &amp; ", $xml);
            return $xml;
    }

    protected function buildXmlHeader() {
            return "";
            return "<?xml version=\"1.0\" encoding=\"{$this->getEncoding()}\">";
    }

    protected function clearXml($xml) {

            try {
                    $clearXml = $this->xmlFilters($xml);

                    $ctrl1 = strrpos($clearXml, "__");
                    if (!$ctrl1) {
                            $ctrl2 = strrpos($clearXml, "##");
                            if (!$ctrl2) {
                                    $doc = new \DOMDocument();
                                    $doc->loadXML($clearXml);
                                    $doc->normalize();

                                    if ($this->getXsdValidation()) {

                                            try {
                                                    $path = $this->getXsdFile();
                                                    $validazione = $doc->schemaValidate($path);
                                                    if (!$validazione) {
                                                            throw new \Exception("Errore nella validazione dell'xml: " . $clearXml);
                                                    }
                                            } catch (\Exception $ex) {
                                                    $this->errors[] = "Errore di validazione: " . $ex->getMessage();
                                            }
                                    }

                                    $exclusive = true;
                                    $withComments = false;
                                    $arXPath = $prefixList = null;
                                    $XML = $doc->C14N($exclusive, $withComments, $arXPath, $prefixList);
                                    $XML = $this->xmlFilters($XML);
                                    $XML = $doc->C14N($exclusive, $withComments, $arXPath, $prefixList);
                                    return $this->buildXmlHeader() . $XML;
                            }
                    }
            } catch (\Exception $ex) {
                    $this->errors[] = "Errore generico: " . $ex->getMessage();
            }
            return FALSE;
    }

    public function getNodo($nodo) {
            $content = NULL;
            $basePath = $this->getDirXmlFiles();
            $subpath = trim(str_replace("##", "", $nodo));
            $path = $basePath . $subpath . ".xml";
            try {
                    if (file_exists($path)) {
                            $content = file_get_contents($path);
                    } else {
                            throw new \Exception("Il nodo che si intende estrarre non esiste");
                    }
            } catch (\Exception $ex) {
                    $this->errors[] = "Errore generico: " . $ex->getMessage();
            }
            return $content;
    }

    public function getAll($mode = NULL) {
            if (\is_null($mode) || $mode == "") {
                    $path = "Array/" . $this->getType() . "/ArrayCompleto.php";
            } else {
                    $path = "Array/" . $this->getType() . "/" . $mode . "/ArrayCompleto.php";
            }
            try {
                    if (file_exists($path)) {
                            include $path;
                            $xml = $this->buildXmlMetadata($arrayCompleto, $this->getRootXmlNode());
                            $xml = str_replace("##", "", $xml);
                            return $this->buildXmlHeader() . $xml;
                    } else {
                            throw new \Exception("Impossibile caricare l'array completo del tipo " . $this->getType());
                    }
            } catch (\Exception $ex) {
                    $this->errors[] = "Errore generico: " . $ex->getMessage();
            }
    }

}
