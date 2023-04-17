<?php

namespace Gdbnet\FatturaElettronica;

use Symfony\Component\Serializer\Encoder\XmlEncoder;

class FatturaElettronicaXmlReader
{
    /**
     * @var XmlEncoder
     */
    private $xmlEncoder;

    /**
     * FatturaElettronicaXmlReader constructor.
     */
    public function __construct()
    {
        $this->xmlEncoder = new XmlEncoder();
    }

    /**
     * @param string $source
     * @return FatturaElettronica
     * @throws FatturaElettronicaException
     */
    public function decodeXml(string $source)
    {
        $source = $this->clearSignature($source);
        $array = $this->xmlEncoder->decode($source, null);
        $fattura = FatturaElettronica::fromArray($array);

        return $fattura;
    }

    /**
     * @param string $filePath
     * @return FatturaElettronica
     * @throws FatturaElettronicaException
     */
    public static function decodeFromFile(string $filePath)
    {
        $reader = new FatturaElettronicaXmlReader();

        return $reader->decodeXml(file_get_contents($filePath));
    }

    /**
     * @param string $input
     * @return string
     */
    public static function clearSignature(string $input)
    {
        //pare non riesca a gestire i commenti
        $input = preg_replace('/<!--.*-->/', '', $input);
        $input = substr($input, strpos($input, '<?xml '));
        preg_match_all('/<\/.+?>/', $input, $matches, PREG_OFFSET_CAPTURE);
        $lastMatch = end($matches[0]);

        return substr($input, 0, $lastMatch[1]) . $lastMatch[0];
    }
}