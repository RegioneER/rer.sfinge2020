<?php

namespace Gdbnet\FatturaElettronica\Structures;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class Fiscale implements FatturaElettronicaInterface
{
    /**
     * @var string
     */
    protected $IdPaese;

    /**
     * @var string
     */
    protected $IdCodice;

    /**
     * Fiscale constructor.
     * @param string $IdPaese
     * @param string $IdCodice
     */
    public function __construct(string $IdPaese, string $IdCodice)
    {
        $this->setIdPaese($IdPaese);
        $this->setIdCodice($IdCodice);
    }

    /**
     * @return string
     */
    public function getIdPaese(): string
    {
        return $this->IdPaese;
    }

    /**
     * @param string $IdPaese
     * @return Fiscale
     */
    public function setIdPaese(string $IdPaese): self
    {
        $this->IdPaese = $IdPaese;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdCodice(): string
    {
        return $this->IdCodice;
    }

    /**
     * @param string $IdCodice
     * @return Fiscale
     */
    public function setIdCodice(string $IdCodice): self
    {
        $this->IdCodice = $IdCodice;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'IdPaese' => $this->getIdPaese(),
            'IdCodice' => $this->getIdCodice(),
        ];
    }

    /**
     * @param $array
     * @return Fiscale
     */
    public static function fromArray(array $array): self
    {
        $fiscale = new self($array['IdPaese'], $array['IdCodice']);

        return $fiscale;
    }
}