<?php

namespace Gdbnet\FatturaElettronica;

use DOMDocument;
use InvalidArgumentException;

class XmlValidator
{
    /**
     * @var array
     */
    public $errors;

    /**
     * @return mixed
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return !$this->getErrors();
    }

    /**
     * @param $xml
     * @param $schema
     * @return bool
     */
    public function validate($xml, $schema = null): bool
    {
        if (!$schema) {
            $schema = dirname(__FILE__) . '/../xsd/fattura_pa_1.2.1.xsd';
        }
        
        $xml = preg_replace('/<!--.*-->/', '', $xml);

        if ('' === trim($xml)) {
            throw new InvalidArgumentException(sprintf('File %s does not contain valid XML, it is empty.', $xml));
        }

        $internalErrors = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);
        libxml_clear_errors();

        $dom = new DOMDocument();
        $dom->validateOnParse = true;
        if (!$dom->loadXML($xml, LIBXML_NONET | (defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0))) {
            libxml_disable_entity_loader($disableEntities);
            $this->errors = $this->getXmlErrors($internalErrors);
            return false;
        }

        $dom->normalizeDocument();

        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($disableEntities);

        foreach ($dom->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                throw new InvalidArgumentException('Document types are not allowed.');
            }
        }

        if (null !== $schema) {
            $internalErrors = libxml_use_internal_errors(true);
            libxml_clear_errors();

            $e = null;
            if (!is_array($schema) && is_file((string)$schema)) {
                $valid = @$dom->schemaValidate($schema);
            } else {
                libxml_use_internal_errors($internalErrors);

                throw new InvalidArgumentException('The schema argument has to be a valid path to XSD file or callable.');
            }

            if (!$valid) {
                $this->errors = $this->getXmlErrors($internalErrors);
                return false;
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        return true;
    }

    /**
     * @param $internalErrors
     * @return array
     */
    private function getXmlErrors($internalErrors): array
    {
        $errors = [];
        foreach (libxml_get_errors() as $error) {
            $errors[] = $error;
        }

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        return $errors;
    }
}
