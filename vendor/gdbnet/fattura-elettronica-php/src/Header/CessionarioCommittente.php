<?php

namespace Gdbnet\FatturaElettronica\Header;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;
use Gdbnet\FatturaElettronica\Structures\Anagrafica;
use Gdbnet\FatturaElettronica\Structures\Fiscale;
use Gdbnet\FatturaElettronica\Structures\Indirizzo;

class CessionarioCommittente implements FatturaElettronicaInterface
{
    /**
     * @var Fiscale|null
     */
    protected $IdFiscaleIVA = null;

    /**
     * @var string
     */
    protected $CodiceFiscale;

    /**
     * @var Anagrafica
     */
    protected $Anagrafica;

    /**
     * @var Indirizzo
     */
    protected $Sede;

    /**
     * @var Indirizzo|null
     */
    protected $StabileOrganizzazione = null;

    /**
     * @var Fiscale|null
     */
    protected $RappresentanteFiscaleIdFiscaleIVA = null;

    /**
     * @var string|null
     */
    protected $RappresentanteFiscaleDenominazione = null;

    /**
     * @var string|null
     */
    protected $RappresentanteFiscaleNome = null;

    /**
     * @var string|null
     */
    protected $RappresentanteFiscaleCognome = null;

    /**
     * CessionarioCommittente constructor.
     * @param Anagrafica $Anagrafica
     * @param Indirizzo $Sede
     * @param Fiscale|null $IdFiscaleIVA
     */
    public function __construct(
        Anagrafica $Anagrafica,
        Indirizzo $Sede,
        ?Fiscale $IdFiscaleIVA = null
    )
    {
        $this->setAnagrafica($Anagrafica);
        $this->setSede($Sede);
        $this->setIdFiscaleIVA($IdFiscaleIVA);
    }

    /**
     * @return Fiscale|null
     */
    public function getIdFiscaleIVA(): ?Fiscale
    {
        return $this->IdFiscaleIVA;
    }

    /**
     * @param Fiscale|null $IdFiscaleIVA
     * @return CessionarioCommittente
     */
    public function setIdFiscaleIVA(?Fiscale $IdFiscaleIVA): self
    {
        $this->IdFiscaleIVA = $IdFiscaleIVA;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodiceFiscale(): ?string
    {
        return $this->CodiceFiscale;
    }

    /**
     * @param string $CodiceFiscale
     * @return CessionarioCommittente
     */
    public function setCodiceFiscale(string $CodiceFiscale): self
    {
        $this->CodiceFiscale = strtoupper($CodiceFiscale);

        return $this;
    }

    /**
     * @return Anagrafica
     */
    public function getAnagrafica(): Anagrafica
    {
        return $this->Anagrafica;
    }

    /**
     * @param Anagrafica $Anagrafica
     * @return CessionarioCommittente
     */
    public function setAnagrafica(Anagrafica $Anagrafica): self
    {
        $this->Anagrafica = $Anagrafica;

        return $this;
    }

    /**
     * @return Indirizzo
     */
    public function getSede(): Indirizzo
    {
        return $this->Sede;
    }

    /**
     * @param Indirizzo $Sede
     * @return CessionarioCommittente
     */
    public function setSede(Indirizzo $Sede): self
    {
        $this->Sede = $Sede;

        return $this;
    }

    /**
     * @return Indirizzo|null
     */
    public function getStabileOrganizzazione(): ?Indirizzo
    {
        return $this->StabileOrganizzazione;
    }

    /**
     * @param Indirizzo|null $StabileOrganizzazione
     * @return CessionarioCommittente
     */
    public function setStabileOrganizzazione(?Indirizzo $StabileOrganizzazione): self
    {
        $this->StabileOrganizzazione = $StabileOrganizzazione;

        return $this;
    }

    /**
     * @return Fiscale|null
     */
    public function getRappresentanteFiscaleIdFiscaleIVA(): ?Fiscale
    {
        return $this->RappresentanteFiscaleIdFiscaleIVA;
    }

    /**
     * @param Fiscale|null $RappresentanteFiscaleIdFiscaleIVA
     * @return CessionarioCommittente
     */
    public function setRappresentanteFiscaleIdFiscaleIVA(?Fiscale $RappresentanteFiscaleIdFiscaleIVA): self
    {
        $this->RappresentanteFiscaleIdFiscaleIVA = $RappresentanteFiscaleIdFiscaleIVA;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRappresentanteFiscaleDenominazione(): ?string
    {
        return $this->RappresentanteFiscaleDenominazione;
    }

    /**
     * @param null|string $RappresentanteFiscaleDenominazione
     * @return CessionarioCommittente
     */
    public function setRappresentanteFiscaleDenominazione(?string $RappresentanteFiscaleDenominazione): self
    {
        $this->RappresentanteFiscaleDenominazione = $RappresentanteFiscaleDenominazione;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRappresentanteFiscaleNome(): ?string
    {
        return $this->RappresentanteFiscaleNome;
    }

    /**
     * @param null|string $RappresentanteFiscaleNome
     * @return CessionarioCommittente
     */
    public function setRappresentanteFiscaleNome(?string $RappresentanteFiscaleNome): self
    {
        $this->RappresentanteFiscaleNome = $RappresentanteFiscaleNome;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRappresentanteFiscaleCognome(): ?string
    {
        return $this->RappresentanteFiscaleCognome;
    }

    /**
     * @param null|string $RappresentanteFiscaleCognome
     * @return CessionarioCommittente
     */
    public function setRappresentanteFiscaleCognome(?string $RappresentanteFiscaleCognome): self
    {
        $this->RappresentanteFiscaleCognome = $RappresentanteFiscaleCognome;

        return $this;
    }

    public function toArray()
    {
        $array['DatiAnagrafici'] = [
            'IdFiscaleIVA' => ($this->getIdFiscaleIVA() instanceof Fiscale) ? $this->getIdFiscaleIVA()->toArray() : null,
            'CodiceFiscale' => $this->getCodiceFiscale(),
            'Anagrafica' => ($this->getAnagrafica() instanceof Anagrafica) ? $this->getAnagrafica()->toArray() : null,
        ];

        $array['Sede'] = null;
        if ($this->getSede() instanceof Indirizzo) {
            $array['Sede'] = $this->getSede()->toArray();
        }
        $array['StabileOrganizzazione'] = null;
        if ($this->getStabileOrganizzazione() instanceof Indirizzo) {
            $array['StabileOrganizzazione'] = $this->getStabileOrganizzazione()->toArray();
        }

        $array['RappresentanteFiscale'] = null;
        if ($this->getRappresentanteFiscaleIdFiscaleIVA() instanceof Fiscale) {
            $array['RappresentanteFiscale']['IdFiscaleIVA'] = $this->getRappresentanteFiscaleIdFiscaleIVA()->toArray();
        }
        if (!empty($this->getRappresentanteFiscaleDenominazione())) {
            $array['RappresentanteFiscale']['Denominazione'] = $this->getRappresentanteFiscaleDenominazione();
        }
        if (!empty($this->getRappresentanteFiscaleNome())) {
            $array['RappresentanteFiscale']['Nome'] = $this->getRappresentanteFiscaleNome();
        }
        if (!empty($this->getRappresentanteFiscaleCognome())) {
            $array['RappresentanteFiscale']['Cognome'] = $this->getRappresentanteFiscaleCognome();
        }

        return $array;
    }

    /**
     * @param array $array
     * @return CessionarioCommittente
     */
    public static function fromArray(array $array): self
    {
        $Anagrafica = Anagrafica::fromArray($array['DatiAnagrafici']['Anagrafica']);
        $Sede = Indirizzo::fromArray($array['Sede']);
        $cessionarioCommittente = new self($Anagrafica, $Sede);

        if (isset($array['DatiAnagrafici']['IdFiscaleIVA']) &&
            is_array($array['DatiAnagrafici']['IdFiscaleIVA'])) {
            $cessionarioCommittente->setIdFiscaleIVA(Fiscale::fromArray($array['DatiAnagrafici']['IdFiscaleIVA']));
        }
        if (isset($array['DatiAnagrafici']['CodiceFiscale']) &&
            !empty(trim($array['DatiAnagrafici']['CodiceFiscale']))) {
            $cessionarioCommittente->setCodiceFiscale($array['DatiAnagrafici']['CodiceFiscale']);
        }

        if (isset($array['StabileOrganizzazione']) && is_array($array['StabileOrganizzazione'])) {
            $cessionarioCommittente->setStabileOrganizzazione(Indirizzo::fromArray($array['StabileOrganizzazione']));
        }

        if (isset($array['RappresentanteFiscale']['IdFiscaleIVA']) &&
             is_array($array['RappresentanteFiscale']['IdFiscaleIVA'])) {
            $cessionarioCommittente->setRappresentanteFiscaleIdFiscaleIVA(Fiscale::fromArray($array['RappresentanteFiscale']['IdFiscaleIVA']));
        }
        if (isset($array['RappresentanteFiscale']['Denominazione']) &&
            !empty(trim($array['RappresentanteFiscale']['Denominazione']))) {
            $cessionarioCommittente->setRappresentanteFiscaleDenominazione($array['RappresentanteFiscale']['Denominazione']);
        }
        if (isset($array['RappresentanteFiscale']['Nome']) &&
            !empty(trim($array['RappresentanteFiscale']['Nome']))) {
            $cessionarioCommittente->setRappresentanteFiscaleNome($array['RappresentanteFiscale']['Nome']);
        }
        if (isset($array['RappresentanteFiscale']['Cognome']) &&
            !empty(trim($array['RappresentanteFiscale']['Cognome']))) {
            $cessionarioCommittente->setRappresentanteFiscaleCognome($array['RappresentanteFiscale']['Cognome']);
        }

        return $cessionarioCommittente;
    }
}