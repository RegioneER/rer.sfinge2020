<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;
use Gdbnet\FatturaElettronica\Structures\Anagrafica;
use Gdbnet\FatturaElettronica\Structures\Fiscale;
use Gdbnet\FatturaElettronica\Structures\Indirizzo;

class DatiTrasporto implements FatturaElettronicaInterface
{
    /**
     * @var Fiscale|null
     */
    protected $IdFiscaleIVA;

    /**
     * @var string|null
     */
    protected $CodiceFiscale;

    /**
     * @var Anagrafica|null
     */
    protected $Anagrafica;

    /**
     * @var string|null
     */
    protected $NumeroLicenzaGuida;

    /**
     * @var string|null
     */
    protected $MezzoTrasporto;

    /**
     * @var string|null
     */
    protected $CausaleTrasporto;

    /**
     * @var int|null
     */
    protected $NumeroColli;

    /**
     * @var string|null
     */
    protected $Descrizione;

    /**
     * @var string|null
     */
    protected $UnitaMisuraPeso;

    /**
     * @var string|null
     */
    protected $PesoLordo;

    /**
     * @var string|null
     */
    protected $PesoNetto;

    /**
     * @var string|null
     */
    protected $DataOraRitiro;

    /**
     * @var string|null
     */
    protected $DataInizioTrasporto;

    /**
     * @var string|null
     */
    protected $TipoResa;

    /**
     * @var Indirizzo|null
     */
    protected $IndirizzoResa;

    /**
     * @var string|null
     */
    protected $DataOraConsegna;

    /**
     * @return Fiscale|null
     */
    public function getIdFiscaleIVA(): ?Fiscale
    {
        return $this->IdFiscaleIVA;
    }

    /**
     * @param Fiscale|null $IdFiscaleIVA
     * @return DatiTrasporto
     */
    public function setIdFiscaleIVA(?Fiscale $IdFiscaleIVA): self
    {
        $this->IdFiscaleIVA = $IdFiscaleIVA;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCodiceFiscale(): ?string
    {
        return $this->CodiceFiscale;
    }

    /**
     * @param string|null $CodiceFiscale
     * @return DatiTrasporto
     */
    public function setCodiceFiscale(?string $CodiceFiscale): self
    {
        $this->CodiceFiscale = strtoupper($CodiceFiscale);

        return $this;
    }

    /**
     * @return Anagrafica|null
     */
    public function getAnagrafica(): ?Anagrafica
    {
        return $this->Anagrafica;
    }

    /**
     * @param Anagrafica|null $Anagrafica
     * @return DatiTrasporto
     */
    public function setAnagrafica(?Anagrafica $Anagrafica): self
    {
        $this->Anagrafica = $Anagrafica;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNumeroLicenzaGuida(): ?string
    {
        return $this->NumeroLicenzaGuida;
    }

    /**
     * @param null|string $NumeroLicenzaGuida
     * @return DatiTrasporto
     */
    public function setNumeroLicenzaGuida(?string $NumeroLicenzaGuida): self
    {
        $this->NumeroLicenzaGuida = $NumeroLicenzaGuida;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMezzoTrasporto(): ?string
    {
        return $this->MezzoTrasporto;
    }

    /**
     * @param null|string $MezzoTrasporto
     * @return DatiTrasporto
     */
    public function setMezzoTrasporto(?string $MezzoTrasporto): self
    {
        $this->MezzoTrasporto = $MezzoTrasporto;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCausaleTrasporto(): ?string
    {
        return $this->CausaleTrasporto;
    }

    /**
     * @param null|string $CausaleTrasporto
     * @return DatiTrasporto
     */
    public function setCausaleTrasporto(?string $CausaleTrasporto): self
    {
        $this->CausaleTrasporto = $CausaleTrasporto;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getNumeroColli(): ?int
    {
        return $this->NumeroColli;
    }

    /**
     * @param int|null $NumeroColli
     * @return DatiTrasporto
     */
    public function setNumeroColli(?int $NumeroColli): self
    {
        $this->NumeroColli = $NumeroColli;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescrizione(): ?string
    {
        return $this->Descrizione;
    }

    /**
     * @param null|string $Descrizione
     * @return DatiTrasporto
     */
    public function setDescrizione(?string $Descrizione): self
    {
        $this->Descrizione = $Descrizione;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getUnitaMisuraPeso(): ?string
    {
        return $this->UnitaMisuraPeso;
    }

    /**
     * @param string|null $UnitaMisuraPeso
     * @return DatiTrasporto
     */
    public function setUnitaMisuraPeso(?string $UnitaMisuraPeso): self
    {
        $this->UnitaMisuraPeso = $UnitaMisuraPeso;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPesoLordo(): ?string
    {
        return $this->PesoLordo;
    }

    /**
     * @param float|null $PesoLordo
     * @return DatiTrasporto
     */
    public function setPesoLordo(?float $PesoLordo): self
    {
        $this->PesoLordo = number_format($PesoLordo, 2, '.', '');

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPesoNetto(): ?string
    {
        return $this->PesoNetto;
    }

    /**
     * @param float|null $PesoNetto
     * @return DatiTrasporto
     */
    public function setPesoNetto(?float $PesoNetto): self
    {
        $this->PesoNetto = number_format($PesoNetto, 2, '.', '');

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDataOraRitiro(): ?string
    {
        return $this->DataOraRitiro;
    }

    /**
     * @param null|string $DataOraRitiro
     * @return DatiTrasporto
     */
    public function setDataOraRitiro(?string $DataOraRitiro): self
    {
        $this->DataOraRitiro = $DataOraRitiro;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDataInizioTrasporto(): ?string
    {
        return $this->DataInizioTrasporto;
    }

    /**
     * @param null|string $DataInizioTrasporto
     * @return DatiTrasporto
     */
    public function setDataInizioTrasporto(?string $DataInizioTrasporto): self
    {
        $this->DataInizioTrasporto = $DataInizioTrasporto;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTipoResa(): ?string
    {
        return $this->TipoResa;
    }

    /**
     * @param null|string $TipoResa
     * @return DatiTrasporto
     */
    public function setTipoResa(?string $TipoResa): self
    {
        $this->TipoResa = $TipoResa;

        return $this;
    }

    /**
     * @return Indirizzo|null
     */
    public function getIndirizzoResa(): ?Indirizzo
    {
        return $this->IndirizzoResa;
    }

    /**
     * @param Indirizzo|null $IndirizzoResa
     * @return DatiTrasporto
     */
    public function setIndirizzoResa(?Indirizzo $IndirizzoResa): self
    {
        $this->IndirizzoResa = $IndirizzoResa;

        return $this;
    }


    /**
     * @return null|string
     */
    public function getDataOraConsegna(): ?string
    {
        return $this->DataOraConsegna;
    }

    /**
     * @param null|string $DataOraConsegna
     * @return DatiTrasporto
     */
    public function setDataOraConsegna(?string $DataOraConsegna): self
    {
        $this->DataOraConsegna = $DataOraConsegna;

        return $this;
    }

    public function toArray()
    {
        $array = [
            'DatiAnagraficiVettore' => [
                'IdFiscaleIVA' => null,
                'CodiceFiscale' => $this->getCodiceFiscale(),
                'Anagrafica' => null,
                'NumeroLicenzaGuida' => $this->getNumeroLicenzaGuida(),
            ],
            'MezzoTrasporto' => null,
            'CausaleTrasporto' => null,
            'NumeroColli' => null,
            'Descrizione' => null,
            'UnitaMisuraPeso' => null,
            'PesoLordo' => null,
            'PesoNetto' => null,
            'DataOraRitiro' => null,
            'DataInizioTrasporto' => null,
            'TipoResa' => null,
            'IndirizzoResa' => null,
            'DataOraConsegna' => null,
        ];

        if ($this->getIdFiscaleIVA() instanceof Fiscale) {
            $array['DatiAnagraficiVettore']['IdFiscaleIVA'] = $this->getIdFiscaleIVA()->toArray();
        }

        if ($this->getAnagrafica() instanceof Anagrafica) {
            $array['DatiAnagraficiVettore']['Anagrafica'] = $this->getAnagrafica()->toArray();
            if (empty($array['DatiAnagraficiVettore']['Anagrafica'])) {
                $array['DatiAnagraficiVettore']['Anagrafica'] = null;
            }
        }

        if (!empty($this->getMezzoTrasporto())) {
            $array['MezzoTrasporto'] = $this->getMezzoTrasporto();
        }
        if (!empty($this->getCausaleTrasporto())) {
            $array['CausaleTrasporto'] = $this->getCausaleTrasporto();
        }
        if (!empty($this->getNumeroColli())) {
            $array['NumeroColli'] = $this->getNumeroColli();
        }
        if (!empty($this->getDescrizione())) {
            $array['Descrizione'] = $this->getDescrizione();
        }
        if (!empty($this->getUnitaMisuraPeso())) {
            $array['UnitaMisuraPeso'] = $this->getUnitaMisuraPeso();
        }
        if (!empty($this->getPesoLordo())) {
            $array['PesoLordo'] = $this->getPesoLordo();
        }
        if (!empty($this->getPesoNetto())) {
            $array['PesoNetto'] = $this->getPesoNetto();
        }
        if (!empty($this->getDataOraRitiro())) {
            $array['DataOraRitiro'] = $this->getDataOraRitiro();
        }
        if (!empty($this->getDataInizioTrasporto())) {
            $array['DataInizioTrasporto'] = $this->getDataInizioTrasporto();
        }
        if (!empty($this->getTipoResa())) {
            $array['TipoResa'] = $this->getTipoResa();
        }
        if ($this->getIndirizzoResa() instanceof Indirizzo) {
            $array['IndirizzoResa'] = $this->getIndirizzoResa()->toArray();
        }
        if (!empty($this->getDataOraConsegna())) {
            $array['DataOraConsegna'] = $this->getDataOraConsegna();
        }

        return $array;
    }

    /**
     * @param array $array
     * @return DatiTrasporto
     */
    public static function fromArray(array $array): self
    {
        $datiTrasporto = new self();

        if (isset($array['DatiAnagraficiVettore']['IdFiscaleIVA']) && is_array($array['DatiAnagraficiVettore']['IdFiscaleIVA'])) {
            $datiTrasporto->setIdFiscaleIVA(Fiscale::fromArray($array['DatiAnagraficiVettore']['IdFiscaleIVA']));
        }
        if (isset($array['DatiAnagraficiVettore']['CodiceFiscale']) &&
            !empty(trim($array['DatiAnagraficiVettore']['CodiceFiscale']))) {
            $datiTrasporto->setCodiceFiscale($array['DatiAnagraficiVettore']['CodiceFiscale']);
        }
        if (isset($array['DatiAnagraficiVettore']['Anagrafica']) && is_array($array['DatiAnagraficiVettore']['Anagrafica'])) {
            $datiTrasporto->setAnagrafica(Anagrafica::fromArray($array['DatiAnagraficiVettore']['Anagrafica']));
        }
        if (isset($array['DatiAnagraficiVettore']['NumeroLicenzaGuida']) &&
            !empty(trim($array['DatiAnagraficiVettore']['NumeroLicenzaGuida']))) {
            $datiTrasporto->setNumeroLicenzaGuida($array['DatiAnagraficiVettore']['NumeroLicenzaGuida']);
        }
        if (isset($array['MezzoTrasporto']) && !empty(trim($array['MezzoTrasporto']))) {
            $datiTrasporto->setMezzoTrasporto($array['MezzoTrasporto']);
        }
        if (isset($array['CausaleTrasporto']) && !empty(trim($array['CausaleTrasporto']))) {
            $datiTrasporto->setCausaleTrasporto($array['CausaleTrasporto']);
        }
        if (isset($array['NumeroColli']) && !empty(trim($array['NumeroColli']))) {
            $datiTrasporto->setNumeroColli($array['NumeroColli']);
        }
        if (isset($array['Descrizione']) && !empty(trim($array['Descrizione']))) {
            $datiTrasporto->setDescrizione($array['Descrizione']);
        }
        if (isset($array['UnitaMisuraPeso']) && !empty(trim($array['UnitaMisuraPeso']))) {
            $datiTrasporto->setUnitaMisuraPeso($array['UnitaMisuraPeso']);
        }
        if (isset($array['PesoLordo']) && !empty(trim($array['PesoLordo']))) {
            $datiTrasporto->setPesoLordo($array['PesoLordo']);
        }
        if (isset($array['PesoNetto']) && !empty(trim($array['PesoNetto']))) {
            $datiTrasporto->setPesoNetto($array['PesoNetto']);
        }
        if (isset($array['DataOraRitiro']) && !empty(trim($array['DataOraRitiro']))) {
            $datiTrasporto->setDataOraRitiro($array['DataOraRitiro']);
        }
        if (isset($array['DataInizioTrasporto']) && !empty(trim($array['DataInizioTrasporto']))) {
            $datiTrasporto->setDataInizioTrasporto($array['DataInizioTrasporto']);
        }
        if (isset($array['TipoResa']) && !empty(trim($array['TipoResa']))) {
            $datiTrasporto->setTipoResa($array['TipoResa']);
        }
        if (isset($array['IndirizzoResa']) && is_array($array['IndirizzoResa'])) {
            $datiTrasporto->setIndirizzoResa(Indirizzo::fromArray($array['IndirizzoResa']));
        }
        if (isset($array['DataOraConsegna']) && !empty(trim($array['DataOraConsegna']))) {
            $datiTrasporto->setDataOraConsegna($array['DataOraConsegna']);
        }

        return $datiTrasporto;
    }
}