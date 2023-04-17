<?php

namespace Gdbnet\FatturaElettronica\Structures;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class Anagrafica implements FatturaElettronicaInterface
{
    /**
     * @var null| string
     */
    protected $Denominazione = null;

    /**
     * @var null| string
     */
    protected $Nome = null;

    /**
     * @var null| string
     */
    protected $Cognome = null;

    /**
     * @var null| string
     */
    protected $Titolo = null;

    /**
     * @var null| string
     */
    protected $CodEORI = null;

    /**
     * Anagrafica constructor.
     * @param string|null $Denominazione
     * @param string|null $Nome
     * @param string|null $Cognome
     * @param string|null $Titolo
     * @param string|null $CodEORI
     */
    public function __construct(
        ?string $Denominazione = null,
        ?string $Nome = null,
        ?string $Cognome = null,
        ?string $Titolo = null,
        ?string $CodEORI = null
    )
    {
        $this->setDenominazione($Denominazione);
        $this->setNome($Nome);
        $this->setCognome($Cognome);
        $this->setTitolo($Titolo);
        $this->setCodEORI($CodEORI);
    }

    /**
     * @return null|string
     */
    public function getDenominazione(): ?string
    {
        return $this->Denominazione;
    }

    /**
     * @param null|string $Denominazione
     * @return Anagrafica
     */
    public function setDenominazione(?string $Denominazione): self
    {
        $this->Denominazione = $Denominazione;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNome(): ?string
    {
        return $this->Nome;
    }

    /**
     * @param null|string $Nome
     * @return Anagrafica
     */
    public function setNome(?string $Nome): self
    {
        $this->Nome = $Nome;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCognome(): ?string
    {
        return $this->Cognome;
    }

    /**
     * @param null|string $Cognome
     * @return Anagrafica
     */
    public function setCognome(?string $Cognome): self
    {
        $this->Cognome = $Cognome;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTitolo(): ?string
    {
        return $this->Titolo;
    }

    /**
     * @param null|string $Titolo
     * @return Anagrafica
     */
    public function setTitolo(?string $Titolo): self
    {
        $this->Titolo = $Titolo;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCodEORI(): ?string
    {
        return $this->CodEORI;
    }

    /**
     * @param null|string $CodEORI
     * @return Anagrafica
     */
    public function setCodEORI(?string $CodEORI): self
    {
        $this->CodEORI = $CodEORI;

        return $this;
    }

    public function toArray()
    {
        return [
            'Denominazione' => $this->getDenominazione(),
            'Nome' => $this->getNome(),
            'Cognome' => $this->getCognome(),
            'Titolo' => $this->getTitolo(),
            'CodEORI' => $this->getCodEORI(),
        ];
    }

    /**
     * @param $array
     * @return Anagrafica
     */
    public static function fromArray(array $array): Anagrafica
    {
        $anagrafica = new Anagrafica();

        if (isset($array['Denominazione']) && !empty(trim($array['Denominazione']))) {
            $anagrafica->setDenominazione($array['Denominazione']);
        }
        if (isset($array['Nome']) && !empty(trim($array['Nome']))) {
            $anagrafica->setNome($array['Nome']);
        }
        if (isset($array['Cognome']) && !empty(trim($array['Cognome']))) {
            $anagrafica->setCognome($array['Cognome']);
        }
        if (isset($array['Titolo']) && !empty(trim($array['Titolo']))) {
            $anagrafica->setTitolo($array['Titolo']);
        }
        if (isset($array['CodEORI']) && !empty(trim($array['CodEORI']))) {
            $anagrafica->setCodEORI($array['CodEORI']);
        }

        return $anagrafica;
    }
}