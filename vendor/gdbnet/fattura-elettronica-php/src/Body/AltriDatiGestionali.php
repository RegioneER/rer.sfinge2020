<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class AltriDatiGestionali implements FatturaElettronicaInterface
{
    /**
     * @var string
     */
    protected $TipoDato = null;

    /**
     * @var null|string
     */
    protected $RiferimentoTesto = null;

    /**
     * @var null|string
     */
    protected $RiferimentoNumero = null;

    /**
     * @var null|string
     */
    protected $RiferimentoData = null;

    /**
     * AltriDatiGestionali constructor.
     * @param string $TipoDato
     */
    public function __construct(string $TipoDato)
    {
        $this->setTipoDato($TipoDato);
    }

    /**
     * @return null|string
     */
    public function getTipoDato(): ?string
    {
        return $this->TipoDato;
    }

    /**
     * @param string $TipoDato
     * @return AltriDatiGestionali
     */
    public function setTipoDato(string $TipoDato): self
    {
        $this->TipoDato = $TipoDato;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRiferimentoTesto(): ?string
    {
        return $this->RiferimentoTesto;
    }

    /**
     * @param null|string $RiferimentoTesto
     * @return AltriDatiGestionali
     */
    public function setRiferimentoTesto(?string $RiferimentoTesto): self
    {
        $this->RiferimentoTesto = $RiferimentoTesto;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRiferimentoData(): ?string
    {
        return $this->RiferimentoData;
    }

    /**
     * @param null|string $RiferimentoData
     * @return AltriDatiGestionali
     */
    public function setRiferimentoData(?string $RiferimentoData): self
    {
        $this->RiferimentoData = $RiferimentoData;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRiferimentoNumero(): ?string
    {
        return $this->RiferimentoNumero;
    }

    /**
     * @param null|float $RiferimentoNumero
     * @return AltriDatiGestionali
     */
    public function setRiferimentoNumero(?float $RiferimentoNumero): self
    {
        $this->RiferimentoNumero = number_format($RiferimentoNumero, 2, '.', '');

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'TipoDato' => $this->getTipoDato(),
            'RiferimentoTesto' => $this->getRiferimentoTesto(),
            'RiferimentoNumero' => $this->getRiferimentoNumero(),
            'RiferimentoData' => $this->getRiferimentoData(),
        ];
    }

    /**
     * @param array $array
     * @return AltriDatiGestionali
     */
    public static function fromArray(array $array): self
    {
        $altriDatiGestionali = new self($array['TipoDato']);

        if (isset($array['RiferimentoTesto']) && !empty(trim($array['RiferimentoTesto']))) {
            $altriDatiGestionali->setRiferimentoTesto($array['RiferimentoTesto']);
        }
        if (isset($array['RiferimentoNumero']) && !empty(trim($array['RiferimentoNumero']))) {
            $altriDatiGestionali->setRiferimentoNumero($array['RiferimentoNumero']);
        }
        if (isset($array['RiferimentoData']) && !empty(trim($array['RiferimentoData']))) {
            $altriDatiGestionali->setRiferimentoData($array['RiferimentoData']);
        }

        return $altriDatiGestionali;
    }
}
