<?php

namespace Gdbnet\FatturaElettronica\Header;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;
use Gdbnet\FatturaElettronica\Structures\Anagrafica;
use Gdbnet\FatturaElettronica\Structures\Fiscale;

class RappresentanteFiscale implements FatturaElettronicaInterface
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
     * RappresentanteFiscale constructor.
     * @param Fiscale $IdFiscaleIVA
     * @param Anagrafica $Anagrafica
     */
    public function __construct(Fiscale $IdFiscaleIVA, Anagrafica $Anagrafica)
    {
        $this->setIdFiscaleIVA($IdFiscaleIVA);
        $this->setAnagrafica($Anagrafica);
    }

    /**
     * @return Fiscale
     */
    public function getIdFiscaleIVA(): Fiscale
    {
        return $this->IdFiscaleIVA;
    }

    /**
     * @param Fiscale $IdFiscaleIVA
     * @return RappresentanteFiscale
     */
    public function setIdFiscaleIVA(Fiscale $IdFiscaleIVA): self
    {
        $this->IdFiscaleIVA = $IdFiscaleIVA;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodiceFiscale(): ?string
    {
        return $this->CodiceFiscale;
    }

    /**
     * @param string|null $CodiceFiscale
     * @return RappresentanteFiscale
     */
    public function setCodiceFiscale(?string $CodiceFiscale): self
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
     * @return RappresentanteFiscale
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
            'IdFiscaleIVA' => $this->getIdFiscaleIVA()->toArray(),
            'CodiceFiscale' => $this->getCodiceFiscale(),
            'Anagrafica' => $this->getAnagrafica()->toArray(),
        ];

        return $array;
    }

    /**
     * @param array $array
     * @return RappresentanteFiscale
     */
    public static function fromArray(array $array)
    {
        $IdFiscaleIVA = Fiscale::fromArray($array['DatiAnagrafici']['IdFiscaleIVA']);
        $Anagrafica = Anagrafica::fromArray($array['DatiAnagrafici']['Anagrafica']);
        $rappresentanteFiscale = new RappresentanteFiscale($IdFiscaleIVA, $Anagrafica);

        if (isset($array['DatiAnagrafici']['CodiceFiscale']) &&
            !empty(trim($array['DatiAnagrafici']['CodiceFiscale']))) {
            $rappresentanteFiscale->setCodiceFiscale($array['DatiAnagrafici']['CodiceFiscale']);
        }

        return $rappresentanteFiscale;
    }
}