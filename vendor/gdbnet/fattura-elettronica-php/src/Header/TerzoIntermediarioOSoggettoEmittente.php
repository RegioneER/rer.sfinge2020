<?php

namespace Gdbnet\FatturaElettronica\Header;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;
use Gdbnet\FatturaElettronica\Structures\Anagrafica;
use Gdbnet\FatturaElettronica\Structures\Fiscale;

class TerzoIntermediarioOSoggettoEmittente implements FatturaElettronicaInterface
{
    /**
     * @var Fiscale
     */
    protected $IdFiscaleIVA;

    /**
     * @var string
     */
    protected $CodiceFiscale;

    /**
     * @var Anagrafica
     */
    protected $Anagrafica;

    /**
     * TerzoIntermediarioOSoggettoEmittente constructor.
     * @param Anagrafica $Anagrafica
     */
    public function __construct(Anagrafica $Anagrafica)
    {
        $this->setAnagrafica($Anagrafica);
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
     * @return TerzoIntermediarioOSoggettoEmittente
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
     * @return TerzoIntermediarioOSoggettoEmittente
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
     * @return TerzoIntermediarioOSoggettoEmittente
     */
    public function setAnagrafica(Anagrafica $Anagrafica): self
    {
        $this->Anagrafica = $Anagrafica;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array['DatiAnagrafici'] = [
            'IdFiscaleIVA' => null,
            'CodiceFiscale' => $this->getCodiceFiscale(),
            'Anagrafica' => $this->getAnagrafica()->toArray(),
        ];

        if ($this->getIdFiscaleIVA() instanceof Fiscale) {
            $array['DatiAnagrafici']['IdFiscaleIVA'] = $this->getIdFiscaleIVA()->toArray();
        }

        return $array;
    }

    /**
     * @param array $array
     * @return TerzoIntermediarioOSoggettoEmittente
     */
    public static function fromArray(array $array): self
    {
        $anagrafica = Anagrafica::fromArray($array['DatiAnagrafici']['Anagrafica']);
        $terzoIntermediarioOSoggettoEmittente = new self($anagrafica);

        if (isset($array['DatiAnagrafici']['IdFiscaleIVA']) &&
            is_array($array['DatiAnagrafici']['IdFiscaleIVA'])) {
            $terzoIntermediarioOSoggettoEmittente->setIdFiscaleIVA(Fiscale::fromArray($array['DatiAnagrafici']['IdFiscaleIVA']));
        }
        if (isset($array['DatiAnagrafici']['CodiceFiscale']) &&
            !empty(trim($array['DatiAnagrafici']['CodiceFiscale']))) {
            $terzoIntermediarioOSoggettoEmittente->setCodiceFiscale($array['DatiAnagrafici']['CodiceFiscale']);
        }

        return $terzoIntermediarioOSoggettoEmittente;
    }
}