<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class DatiVeicoli implements FatturaElettronicaInterface
{
    /**
     * @var string
     */
    protected $Data;

    /**
     * @var string
     */
    protected $TotalePercorso;

    /**
     * DatiVeicoli constructor.
     * @param string $Data
     * @param string $TotalePercorso
     */
    public function __construct(string $Data, string $TotalePercorso)
    {
        $this->setData($Data);
        $this->setTotalePercorso($TotalePercorso);
    }

    /**
     * @return null|string
     */
    public function getData(): ?string
    {
        return $this->Data;
    }

    /**
     * @param string $Data
     * @return DatiVeicoli
     */
    public function setData(string $Data): self
    {
        $this->Data = $Data;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTotalePercorso(): ?string
    {
        return $this->TotalePercorso;
    }

    /**
     * @param string $TotalePercorso
     * @return DatiVeicoli
     */
    public function setTotalePercorso(string $TotalePercorso): self
    {
        $this->TotalePercorso = $TotalePercorso;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [
            'Data' => $this->getData(),
            'TotalePercorso' => $this->getTotalePercorso(),
        ];

        return $array;
    }

    /**
     * @param $array
     * @return DatiVeicoli
     */
    public static function fromArray(array $array): self
    {
        $datiVeicoli = new self($array['Data'], $array['TotalePercorso']);

        return $datiVeicoli;
    }
}