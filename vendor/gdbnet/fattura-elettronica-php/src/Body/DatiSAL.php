<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class DatiSAL implements FatturaElettronicaInterface
{
    /**
     * @var string
     */
    protected $RiferimentoFase;

    /**
     * DatiSAL constructor.
     * @param float $RiferimentoFase
     */
    public function __construct(float $RiferimentoFase)
    {
        $this->setRiferimentoFase($RiferimentoFase);
    }

    /**
     * @return null|string
     */
    public function getRiferimentoFase(): ?string
    {
        return $this->RiferimentoFase;
    }

    /**
     * @param null|string $RiferimentoFase
     * @return DatiSAL
     */
    public function setRiferimentoFase(?string $RiferimentoFase): self
    {
        $this->RiferimentoFase = $RiferimentoFase;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'RiferimentoFase' => $this->getRiferimentoFase(),
        ];
    }

    /**
     * @param array $array
     * @return DatiSAL
     */
    public static function fromArray(array $array): self
    {
        $datiSAL = new self($array['RiferimentoFase']);

        return $datiSAL;
    }
}
