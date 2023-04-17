<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class DatiDDT implements FatturaElettronicaInterface
{
    /**
     * @var string
     */
    protected $NumeroDDT = null;

    /**
     * @var string
     */
    protected $DataDDT = null;

    /**
     * @var null|array
     */
    protected $RiferimentoNumeroLinea = null;

    /**
     * DatiDDT constructor.
     * @param string $NumeroDDT
     * @param string $DataDDT
     */
    public function __construct(string $NumeroDDT, string $DataDDT)
    {
        $this->setNumeroDDT($NumeroDDT);
        $this->setDataDDT($DataDDT);
    }

    /**
     * @return null|string
     */
    public function getNumeroDDT(): ?string
    {
        return $this->NumeroDDT;
    }

    /**
     * @param string $NumeroDDT
     * @return DatiDDT
     */
    public function setNumeroDDT(string $NumeroDDT): self
    {
        $this->NumeroDDT = $NumeroDDT;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDataDDT(): ?string
    {
        return $this->DataDDT;
    }

    /**
     * @param string $DataDDT
     * @return DatiDDT
     */
    public function setDataDDT(string $DataDDT): self
    {
        $this->DataDDT = $DataDDT;

        return $this;
    }

    /**
     * @return null|array
     */
    public function getRiferimentoNumeroLinea(): ?array
    {
        return $this->RiferimentoNumeroLinea;
    }

    /**
     * @param null|array $RiferimentoNumeroLinea
     * @return DatiDDT
     */
    public function setRiferimentoNumeroLinea(?array $RiferimentoNumeroLinea): self
    {
        $this->RiferimentoNumeroLinea = $RiferimentoNumeroLinea;

        return $this;
    }

    /**
     * @param null|float $RiferimentoNumeroLinea
     * @return DatiDDT
     */
    public function addRiferimentoNumeroLinea(?float $RiferimentoNumeroLinea): self
    {
        $this->RiferimentoNumeroLinea[] = $RiferimentoNumeroLinea;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'NumeroDDT' => $this->getNumeroDDT(),
            'DataDDT' => $this->getDataDDT(),
            'RiferimentoNumeroLinea' => $this->getRiferimentoNumeroLinea(),
        ];
    }

    /**
     * @param array $array
     * @return DatiDDT
     */
    public static function fromArray(array $array): self
    {
        $datiDDT = new self($array['NumeroDDT'], $array['DataDDT']);

        if (isset($array['RiferimentoNumeroLinea'])) {
            if (is_array($array['RiferimentoNumeroLinea'])) {
                $datiDDT->setRiferimentoNumeroLinea($array['RiferimentoNumeroLinea']);
            } else {
                $datiDDT->addRiferimentoNumeroLinea($array['RiferimentoNumeroLinea']);
            }
        }

        return $datiDDT;
    }
}
