<?php

namespace Gdbnet\FatturaElettronica\Header;

use Gdbnet\FatturaElettronica\AbstractModel;
use Gdbnet\FatturaElettronica\FatturaElettronicaException;
use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class FatturaElettronicaHeader extends AbstractModel implements FatturaElettronicaInterface
{
    /**
     * @var DatiTrasmissione
     */
    protected $datiTrasmissione;

    /**
     * @var CedentePrestatore
     */
    protected $cedentePrestatore;

    /**
     * @var RappresentanteFiscale
     */
    protected $rappresentanteFiscale = null;

    /**
     * @var CessionarioCommittente
     */
    protected $cessionarioCommittente;

    /**
     * @var TerzoIntermediarioOSoggettoEmittente
     */
    protected $terzoIntermediarioOSoggettoEmittente = null;

    /**
     * @var string|null
     */
    protected $soggettoEmittente = null;

    /**
     * FatturaElettronicaHeader constructor.
     * @param DatiTrasmissione $datiTrasmissione
     * @param CedentePrestatore $cedentePrestatore
     * @param CessionarioCommittente $cessionarioCommittente
     * @param RappresentanteFiscale|null $rappresentanteFiscale
     * @param TerzoIntermediarioOSoggettoEmittente|null $terzoIntermediarioOSoggettoEmittente
     * @param string|null $soggettoEmittente
     * @throws FatturaElettronicaException
     */
    public function __construct(
        DatiTrasmissione $datiTrasmissione,
        CedentePrestatore $cedentePrestatore,
        CessionarioCommittente $cessionarioCommittente,
        ?RappresentanteFiscale $rappresentanteFiscale = null,
        ?TerzoIntermediarioOSoggettoEmittente $terzoIntermediarioOSoggettoEmittente = null,
        ?string $soggettoEmittente = null
    )
    {
        $this->setDatiTrasmissione($datiTrasmissione);
        $this->setCedentePrestatore($cedentePrestatore);
        $this->setCessionarioCommittente($cessionarioCommittente);
        $this->setRappresentanteFiscale($rappresentanteFiscale);
        $this->setTerzoIntermediarioOSoggettoEmittente($terzoIntermediarioOSoggettoEmittente);
        $this->setSoggettoEmittente($soggettoEmittente);
    }

    /**
     * @return DatiTrasmissione
     */
    public function getDatiTrasmissione(): DatiTrasmissione
    {
        return $this->datiTrasmissione;
    }

    /**
     * @param DatiTrasmissione $datiTrasmissione
     * @return FatturaElettronicaHeader
     */
    public function setDatiTrasmissione(DatiTrasmissione $datiTrasmissione): self
    {
        $this->datiTrasmissione = $datiTrasmissione;

        return $this;
    }

    /**
     * @return CedentePrestatore
     */
    public function getCedentePrestatore(): CedentePrestatore
    {
        return $this->cedentePrestatore;
    }

    /**
     * @param CedentePrestatore $cedentePrestatore
     * @return FatturaElettronicaHeader
     */
    public function setCedentePrestatore(CedentePrestatore $cedentePrestatore): self
    {
        $this->cedentePrestatore = $cedentePrestatore;

        return $this;
    }

    /**
     * @return RappresentanteFiscale
     */
    public function getRappresentanteFiscale(): ?RappresentanteFiscale
    {
        return $this->rappresentanteFiscale;
    }

    /**
     * @param RappresentanteFiscale|null $rappresentanteFiscale
     * @return FatturaElettronicaHeader
     */
    public function setRappresentanteFiscale(?RappresentanteFiscale $rappresentanteFiscale): self
    {
        $this->rappresentanteFiscale = $rappresentanteFiscale;

        return $this;
    }

    /**
     * @return CessionarioCommittente
     */
    public function getCessionarioCommittente(): CessionarioCommittente
    {
        return $this->cessionarioCommittente;
    }

    /**
     * @param CessionarioCommittente $cessionarioCommittente
     * @return FatturaElettronicaHeader
     * @throws FatturaElettronicaException
     */
    public function setCessionarioCommittente(CessionarioCommittente $cessionarioCommittente): self
    {
        $this->cessionarioCommittente = $cessionarioCommittente;

        return $this;
    }

    /**
     * @return TerzoIntermediarioOSoggettoEmittente
     */
    public function getTerzoIntermediarioOSoggettoEmittente(): ?TerzoIntermediarioOSoggettoEmittente
    {
        return $this->terzoIntermediarioOSoggettoEmittente;
    }

    /**
     * @param TerzoIntermediarioOSoggettoEmittente|null $terzoIntermediarioOSoggettoEmittente
     * @return FatturaElettronicaHeader
     */
    public function setTerzoIntermediarioOSoggettoEmittente(?TerzoIntermediarioOSoggettoEmittente $terzoIntermediarioOSoggettoEmittente): self
    {
        $this->terzoIntermediarioOSoggettoEmittente = $terzoIntermediarioOSoggettoEmittente;

        return $this;
    }

    /**
     * @return string
     */
    public function getSoggettoEmittente(): ?string
    {
        return $this->soggettoEmittente;
    }

    /**
     * @param string|null $soggettoEmittente
     * @return FatturaElettronicaHeader
     */
    public function setSoggettoEmittente(?string $soggettoEmittente): self
    {
        $this->soggettoEmittente = $soggettoEmittente;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [
            'DatiTrasmissione' => $this->getDatiTrasmissione()->toArray(),
            'CedentePrestatore' => $this->getCedentePrestatore()->toArray(),
            'RappresentanteFiscale' => null,
            'CessionarioCommittente' => $this->getCessionarioCommittente()->toArray(),
            'TerzoIntermediarioOSoggettoEmittente' => null,
            'SoggettoEmittente' => $this->getSoggettoEmittente()
        ];

        if ($this->getRappresentanteFiscale() instanceof RappresentanteFiscale) {
            $array['RappresentanteFiscale'] = $this->getRappresentanteFiscale()->toArray();
        }
        if ($this->getTerzoIntermediarioOSoggettoEmittente() instanceof TerzoIntermediarioOSoggettoEmittente) {
            $array['TerzoIntermediarioOSoggettoEmittente'] = $this->getTerzoIntermediarioOSoggettoEmittente()->toArray();
        }

        return $this->cleanArray($array);
    }

    /**
     * @param array $array
     * @return FatturaElettronicaHeader
     * @throws FatturaElettronicaException
     */
    public static function fromArray(array $array): self
    {
        $datiTrasmissione = DatiTrasmissione::fromArray($array['DatiTrasmissione']);
        $cedentePrestatore = CedentePrestatore::fromArray($array['CedentePrestatore']);
        $cessionarioCommittente = CessionarioCommittente::fromArray($array['CessionarioCommittente']);
        $fatturaElettronicaHeader = new self($datiTrasmissione, $cedentePrestatore, $cessionarioCommittente);

        if (isset($array['RappresentanteFiscale'])) {
            $fatturaElettronicaHeader->setRappresentanteFiscale(RappresentanteFiscale::fromArray($array['RappresentanteFiscale']));
        }
        if (isset($array['TerzoIntermediarioOSoggettoEmittente'])) {
            $fatturaElettronicaHeader->setTerzoIntermediarioOSoggettoEmittente(TerzoIntermediarioOSoggettoEmittente::fromArray($array['TerzoIntermediarioOSoggettoEmittente']));
        }
        if (isset($array['SoggettoEmittente']) && !empty(trim($array['SoggettoEmittente']))) {
            $fatturaElettronicaHeader->setSoggettoEmittente($array['SoggettoEmittente']);
        }

        return $fatturaElettronicaHeader;
    }
}