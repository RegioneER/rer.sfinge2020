<?php

namespace Gdbnet\FatturaElettronica;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

class FatturaElettronicaXmlWriter
{
    const ROOT = 'p:FatturaElettronica';

    /**
     * @var XmlEncoder
     */
    private $xmlEncoder;

    /**
     * @var FatturaElettronica
     */
    private $fatturaElettronica;

    /**
     * XmlWriter constructor.
     * @param FatturaElettronica $fatturaElettronica
     */
    public function __construct(FatturaElettronica $fatturaElettronica)
    {
        $this->fatturaElettronica = $fatturaElettronica;
        $this->xmlEncoder = new XmlEncoder();
        $this->xmlEncoder->setSerializer(new Serializer());
    }

    /**
     * @return string
     * @throws FatturaElettronicaException
     */
    public function encodeXml(): string
    {
        $xmlData = trim($this->xmlEncoder->encode(
            $this->fatturaElettronica->toArray(),
            'xml',
            [
                'xml_root_node_name' => self::ROOT,
                'xml_encoding' => 'UTF-8'
            ]
        ));

        return $xmlData;
    }

    /**
     * @param string $filePath
     * @return bool
     * @throws FatturaElettronicaException
     */
    public function writeXml(string $filePath): bool
    {
        return file_put_contents($filePath, $this->encodeXml()) !== false;
    }
}