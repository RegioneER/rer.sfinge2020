<?php

namespace Gdbnet\FatturaElettronica\Structures;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class ScontoMaggiorazione implements FatturaElettronicaInterface
{
    /**
     * @var string
     */
    protected $Tipo = null;

    /**
     * @var null|string
     */
    protected $Percentuale = null;

    /**
     * @var null|string
     */
    protected $Importo = null;

    /**
     * ScontoMaggiorazione constructor.
     * @param string $Tipo
     * @param float|null $Percentuale
     * @param float|null $Importo
     */
    public function __construct(
        string $Tipo,
        float $Percentuale = null,
        float $Importo = null
    )
    {
        $this->setTipo($Tipo);
        $this->setPercentuale($Percentuale);
        $this->setImporto($Importo);
    }

    /**
     * @return null|string
     */
    public function getTipo(): ?string
    {
        return $this->Tipo;
    }

    /**
     * @param null|string $Tipo
     * @return ScontoMaggiorazione
     */
    public function setTipo(string $Tipo): self
    {
        $this->Tipo = $Tipo;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPercentuale(): ?string
    {
        return $this->Percentuale;
    }

    /**
     * @param null|float $Percentuale
     * @return ScontoMaggiorazione
     */
    public function setPercentuale(?float $Percentuale): self
    {
        $this->Percentuale = number_format($Percentuale, 2, '.', '');

        return $this;
    }

    /**
     * @return null|string
     */
    public function getImporto(): ?string
    {
        return $this->Importo;
    }

    /**
     * @param null|float $Importo
     * @return ScontoMaggiorazione
     */
    public function setImporto(?float $Importo): self
    {
        $this->Importo = number_format($Importo, 2, '.', '');

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'Tipo' => $this->getTipo(),
            'Percentuale' => $this->getPercentuale(),
            'Importo' => $this->getImporto(),
        ];
    }

    /**
     * @param array $array
     * @return ScontoMaggiorazione
     */
    public static function fromArray(array $array): self
    {
        $scontoMaggiorazione = new self($array['Tipo']);

        if (isset($array['Percentuale']) && !empty(trim($array['Percentuale']))) {
            $scontoMaggiorazione->setPercentuale($array['Percentuale']);
        }
        if (isset($array['Importo']) && !empty(trim($array['Importo']))) {
            $scontoMaggiorazione->setImporto($array['Importo']);
        }

        return $scontoMaggiorazione;
    }
}
