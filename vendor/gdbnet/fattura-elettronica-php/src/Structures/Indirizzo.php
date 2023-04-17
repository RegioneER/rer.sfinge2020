<?php

namespace Gdbnet\FatturaElettronica\Structures;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class Indirizzo implements FatturaElettronicaInterface
{
    /**
     * @var string|null
     */
    protected $Indirizzo = null;

    /**
     * @var string|null
     */
    protected $NumeroCivico = null;

    /**
     * @var string|null
     */
    protected $CAP = null;

    /**
     * @var string|null
     */
    protected $Comune = null;

    /**
     * @var string|null
     */
    protected $Provincia = null;

    /**
     * @var string|null
     */
    protected $Nazione = null;

    /**
     * Indirizzo constructor.
     * @param string $Indirizzo
     * @param string $Comune
     * @param string $CAP
     * @param string $Nazione
     * @param string|null $NumeroCivico
     * @param string|null $Provincia
     */
    public function __construct(
        string $Indirizzo,
        string $Comune,
        string $CAP,
        string $Nazione = 'IT',
        string $NumeroCivico = null,
        string $Provincia = null
    )
    {
        $this->setIndirizzo($Indirizzo);
        $this->setComune($Comune);
        $this->setCAP($CAP);
        $this->setNazione($Nazione);
        $this->setNumeroCivico($NumeroCivico);
        $this->setProvincia($Provincia);
    }

    /**
     * @return null|string
     */
    public function getIndirizzo(): ?string
    {
        return $this->Indirizzo;
    }

    /**
     * @param null|string $Indirizzo
     * @return Indirizzo
     */
    public function setIndirizzo(string $Indirizzo): self
    {
        $this->Indirizzo = $Indirizzo;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNumeroCivico(): ?string
    {
        return $this->NumeroCivico;
    }

    /**
     * @param null|string $NumeroCivico
     * @return Indirizzo
     */
    public function setNumeroCivico(?string $NumeroCivico): self
    {
        $this->NumeroCivico = $NumeroCivico;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCAP(): ?string
    {
        return $this->CAP;
    }

    /**
     * @param null|string $CAP
     * @return Indirizzo
     */
    public function setCAP(string $CAP): self
    {
        $this->CAP = $CAP;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getComune(): ?string
    {
        return $this->Comune;
    }

    /**
     * @param null|string $Comune
     * @return Indirizzo
     */
    public function setComune(string $Comune): self
    {
        $this->Comune = $Comune;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getProvincia(): ?string
    {
        return $this->Provincia;
    }

    /**
     * @param null|string $Provincia
     * @return Indirizzo
     */
    public function setProvincia(?string $Provincia): self
    {
        $this->Provincia = $Provincia;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNazione(): ?string
    {
        return $this->Nazione;
    }

    /**
     * @param null|string $Nazione
     * @return Indirizzo
     */
    public function setNazione(string $Nazione): self
    {
        $this->Nazione = $Nazione;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'Indirizzo' => $this->getIndirizzo(),
            'NumeroCivico' => $this->getNumeroCivico(),
            'CAP' => $this->getCAP(),
            'Comune' => $this->getComune(),
            'Provincia' => $this->getProvincia(),
            'Nazione' => $this->getNazione()
        ];
    }

    /**
     * @param $array
     * @return Indirizzo
     */
    public static function fromArray(array $array): self
    {
        $indirizzo = new self(
            $array['Indirizzo'],
            $array['Comune'],
            $array['CAP'],
            $array['Nazione']
        );

        if (isset($array['NumeroCivico']) && !empty(trim($array['NumeroCivico']))) {
            $indirizzo->setNumeroCivico($array['NumeroCivico']);
        }
        if (isset($array['Provincia']) && !empty(trim($array['Provincia']))) {
            $indirizzo->setProvincia($array['Provincia']);
        }

        return $indirizzo;
    }
}