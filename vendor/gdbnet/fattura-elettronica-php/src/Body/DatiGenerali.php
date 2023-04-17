<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaException;
use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;
use Gdbnet\FatturaElettronica\Structures\DatiRiferimento;

class DatiGenerali implements FatturaElettronicaInterface
{
    /**
     * @var DatiGeneraliDocumento
     */
    protected $DatiGeneraliDocumento;

    /**
     * @var DatiRiferimento[]|null
     */
    protected $DatiOrdineAcquisto;

    /**
     * @var DatiRiferimento[]|null
     */
    protected $DatiContratto;

    /**
     * @var DatiRiferimento[]|null
     */
    protected $DatiConvenzione;

    /**
     * @var DatiRiferimento[]|null
     */
    protected $DatiRicezione;

    /**
     * @var DatiRiferimento[]|null
     */
    protected $DatiFattureCollegate;

    /**
     * @var DatiSAL[]|null
     */
    protected $DatiSAL;

    /**
     * @var DatiDDT[]|null
     */
    protected $DatiDDT;

    /**
     * @var DatiTrasporto|null
     */
    protected $DatiTrasporto;

    /**
     * @var string|null
     */
    protected $NumeroFatturaPrincipale;

    /**
     * @var string|null
     */
    protected $DataFatturaPrincipale;

    /**
     * DatiGenerali constructor.
     * @param DatiGeneraliDocumento $DatiGeneraliDocumento
     */
    public function __construct(DatiGeneraliDocumento $DatiGeneraliDocumento)
    {
        $this->setDatiGeneraliDocumento($DatiGeneraliDocumento);
    }

    /**
     * @return DatiSAL[]|null
     */
    public function getDatiSAL(): ?array
    {
        return $this->DatiSAL;
    }

    /**
     * @param DatiSAL[]|null $datiSal
     * @return DatiGenerali
     */
    public function setDatiSAL(?array $datiSal): self
    {
        $this->DatiSAL = $datiSal;

        return $this;
    }

    /**
     * @param DatiSAL $datiSal
     * @return DatiGenerali
     */
    public function addDatiSAL(DatiSAL $datiSal): self
    {
        $this->DatiSAL[] = $datiSal;

        return $this;
    }

    /**
     * @return DatiDDT[]|null
     */
    public function getDatiDDT(): ?array
    {
        return $this->DatiDDT;
    }

    /**
     * @param DatiDDT[]|null $datiDDT
     * @return DatiGenerali
     */
    public function setDatiDDT(?array $datiDDT): self
    {
        $this->DatiDDT = $datiDDT;

        return $this;
    }

    /**
     * @param DatiDDT $datiDDT
     * @return DatiGenerali
     */
    public function addDatiDDT(DatiDDT $datiDDT): self
    {
        $this->DatiDDT[] = $datiDDT;

        return $this;
    }

    /**
     * @return DatiGeneraliDocumento|null
     */
    public function getDatiGeneraliDocumento(): ?DatiGeneraliDocumento
    {
        return $this->DatiGeneraliDocumento;
    }

    /**
     * @param DatiGeneraliDocumento $DatiGeneraliDocumento
     * @return DatiGenerali
     */
    public function setDatiGeneraliDocumento(DatiGeneraliDocumento $DatiGeneraliDocumento): self
    {
        $this->DatiGeneraliDocumento = $DatiGeneraliDocumento;

        return $this;
    }

    /**
     * @return DatiRiferimento[]|null
     */
    public function getDatiOrdineAcquisto(): ?array
    {
        return $this->DatiOrdineAcquisto;
    }

    /**
     * @param DatiRiferimento[] $DatiOrdineAcquisto
     * @return DatiGenerali
     */
    public function setDatiOrdineAcquisto(array $DatiOrdineAcquisto): self
    {
        $this->DatiOrdineAcquisto = $DatiOrdineAcquisto;

        return $this;
    }

    /**
     * @param DatiRiferimento|null $DatiOrdineAcquisto
     * @return DatiGenerali
     */
    public function addDatiOrdineAcquisto(?DatiRiferimento $DatiOrdineAcquisto): self
    {
        $this->DatiOrdineAcquisto[] = $DatiOrdineAcquisto;

        return $this;
    }

    /**
     * @return DatiRiferimento[]|null
     */
    public function getDatiContratto(): ?array
    {
        return $this->DatiContratto;
    }

    /**
     * @param DatiRiferimento|null $DatiContratto
     * @return DatiGenerali
     */
    public function setDatiContratto(?DatiRiferimento $DatiContratto): self
    {
        $this->DatiContratto = $DatiContratto;

        return $this;
    }

    /**
     * @param DatiRiferimento|null $DatiContratto
     * @return DatiGenerali
     */
    public function addDatiContratto(?DatiRiferimento $DatiContratto): self
    {
        $this->DatiContratto[] = $DatiContratto;

        return $this;
    }

    /**
     * @return DatiRiferimento[]|null
     */
    public function getDatiConvenzione(): ?array
    {
        return $this->DatiConvenzione;
    }

    /**
     * @param DatiRiferimento|null $DatiConvenzione
     * @return DatiGenerali
     */
    public function setDatiConvenzione(?DatiRiferimento $DatiConvenzione): self
    {
        $this->DatiConvenzione = $DatiConvenzione;

        return $this;
    }

    /**
     * @param DatiRiferimento|null $DatiConvenzione
     * @return DatiGenerali
     */
    public function addDatiConvenzione(?DatiRiferimento $DatiConvenzione): self
    {
        $this->DatiConvenzione[] = $DatiConvenzione;

        return $this;
    }

    /**
     * @return DatiRiferimento[]|null
     */
    public function getDatiRicezione(): ?array
    {
        return $this->DatiRicezione;
    }

    /**
     * @param DatiRiferimento|null $DatiRicezione
     * @return DatiGenerali
     */
    public function setDatiRicezione(?DatiRiferimento $DatiRicezione): self
    {
        $this->DatiRicezione = $DatiRicezione;

        return $this;
    }

    /**
     * @param DatiRiferimento|null $DatiRicezione
     * @return DatiGenerali
     */
    public function addDatiRicezione(?DatiRiferimento $DatiRicezione): self
    {
        $this->DatiRicezione[] = $DatiRicezione;

        return $this;
    }

    /**
     * @return DatiRiferimento[]|null
     */
    public function getDatiFattureCollegate(): ?array
    {
        return $this->DatiFattureCollegate;
    }

    /**
     * @param DatiRiferimento|null $DatiFattureCollegate
     * @return DatiGenerali
     */
    public function setDatiFattureCollegate(?DatiRiferimento $DatiFattureCollegate): self
    {
        $this->DatiFattureCollegate = $DatiFattureCollegate;

        return $this;
    }

    /**
     * @param DatiRiferimento|null $DatiFattureCollegate
     * @return DatiGenerali
     */
    public function addDatiFattureCollegate(?DatiRiferimento $DatiFattureCollegate): self
    {
        $this->DatiFattureCollegate[] = $DatiFattureCollegate;

        return $this;
    }

    /**
     * @return DatiTrasporto|null
     */
    public function getDatiTrasporto(): ?DatiTrasporto
    {
        return $this->DatiTrasporto;
    }

    /**
     * @param DatiTrasporto|null $DatiTrasporto
     * @return DatiGenerali
     */
    public function setDatiTrasporto(?DatiTrasporto $DatiTrasporto): self
    {
        $this->DatiTrasporto = $DatiTrasporto;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNumeroFatturaPrincipale(): ?string
    {
        return $this->NumeroFatturaPrincipale;
    }

    /**
     * @param null|string $NumeroFatturaPrincipale
     * @return DatiGenerali
     */
    public function setNumeroFatturaPrincipale(?string $NumeroFatturaPrincipale): self
    {
        $this->NumeroFatturaPrincipale = $NumeroFatturaPrincipale;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDataFatturaPrincipale(): ?string
    {
        return $this->DataFatturaPrincipale;
    }

    /**
     * @param null|string $DataFatturaPrincipale
     * @return DatiGenerali
     */
    public function setDataFatturaPrincipale(?string $DataFatturaPrincipale): self
    {
        $this->DataFatturaPrincipale = $DataFatturaPrincipale;

        return $this;
    }

    /**
     * @return array
     * @throws FatturaElettronicaException
     */
    public function toArray()
    {
        $array = [
            'DatiGeneraliDocumento' => null,
            'DatiOrdineAcquisto' => null,
            'DatiContratto' => null,
            'DatiConvenzione' => null,
            'DatiRicezione' => null,
            'DatiFattureCollegate' => null,
            'DatiSAL' => null,
            'DatiDDT' => null,
            'DatiTrasporto' => null,
            'FatturaPrincipale' => null,
        ];

        if (!$this->getDatiGeneraliDocumento() instanceof DatiGeneraliDocumento) {
            throw new FatturaElettronicaException("Missing instance of 'DatiGeneraliDocumento'", 'FatturaElettronicaBody->DatiGenerali');
        } else {
            $array['DatiGeneraliDocumento'] = $this->getDatiGeneraliDocumento()->toArray();
        }

        if (!empty($this->getDatiOrdineAcquisto())) {
            foreach ($this->getDatiOrdineAcquisto() as $ordineAcquisto) {
                $array['DatiOrdineAcquisto'][] = $ordineAcquisto->toArray();
            }
        }

        if (!empty($this->getDatiContratto())) {
            foreach ($this->getDatiContratto() as $datiContratto) {
                $array['DatiContratto'][] = $datiContratto->toArray();
            }
        }

        if (!empty($this->getDatiConvenzione())) {
            foreach ($this->getDatiConvenzione() as $datiConvenzione) {
                $array['DatiConvenzione'][] = $datiConvenzione->toArray();
            }
        }

        if (!empty($this->getDatiRicezione())) {
            foreach ($this->getDatiRicezione() as $datiRicezione) {
                $array['DatiRicezione'][] = $datiRicezione->toArray();
            }
        }

        if (!empty($this->getDatiFattureCollegate())) {
            foreach ($this->getDatiFattureCollegate() as $datiFattureCollegate) {
                $array['DatiFattureCollegate'][] = $datiFattureCollegate->toArray();
            }
        }

        if (!empty($this->getDatiSAL())) {
            if (count($this->getDatiSAL()) === 1) {
                $array['DatiSAL'] = $this->getDatiSAL()[0]->toArray();
            } else {
                $a = [];
                foreach ($this->getDatiSAL() as $datiSAL) {
                    $a[] = $datiSAL->toArray();
                }
                $array['DatiSAL'] = $a;
            }
        }

        if (!empty($this->getDatiDDT())) {
            if (count($this->getDatiDDT()) === 1) {
                $array['DatiDDT'] = $this->getDatiDDT()[0]->toArray();
            } else {
                $a = [];
                foreach ($this->getDatiDDT() as $DatiDDT) {
                    $a[] = $DatiDDT->toArray();
                }
                $array['DatiDDT'] = $a;
            }
        }

        if ($this->getDatiTrasporto() instanceof DatiTrasporto) {
            $array['DatiTrasporto'] = $this->getDatiTrasporto()->toArray();
        }

        if (!empty($this->getNumeroFatturaPrincipale())) {
            $array['FatturaPrincipale']['NumeroFatturaPrincipale'] = $this->getNumeroFatturaPrincipale();
        }
        if (!empty($this->getNumeroFatturaPrincipale())) {
            $array['FatturaPrincipale']['DataFatturaPrincipale'] = $this->getDataFatturaPrincipale();
        }

        return $array;
    }

    /**
     * @param array $array
     * @return DatiGenerali
     */
    public static function fromArray(array $array): self
    {
        $DatiGeneraliDocumento = DatiGeneraliDocumento::fromArray($array['DatiGeneraliDocumento']);
        $datiGenerali = new self($DatiGeneraliDocumento);

        if (isset($array['DatiOrdineAcquisto'])) {
            if (isset($array['DatiOrdineAcquisto'][0])) {
                foreach ($array['DatiOrdineAcquisto'] as $datiOrdineAcquisto) {
                    $datiGenerali->addDatiOrdineAcquisto(DatiRiferimento::fromArray($datiOrdineAcquisto));
                }
            } else {
                $datiGenerali->addDatiOrdineAcquisto(DatiRiferimento::fromArray($array['DatiOrdineAcquisto']));
            }
        }

        if (isset($array['DatiContratto'])) {
            if (isset($array['DatiContratto'][0])) {
                foreach ($array['DatiContratto'] as $datiContratto) {
                    $datiGenerali->addDatiContratto(DatiRiferimento::fromArray($datiContratto));
                }
            } else {
                $datiGenerali->addDatiContratto(DatiRiferimento::fromArray($array['DatiContratto']));
            }
        }

        if (isset($array['DatiConvenzione'])) {
            if (isset($array['DatiConvenzione'][0])) {
                foreach ($array['DatiConvenzione'] as $datiConvenzione) {
                    $datiGenerali->addDatiConvenzione(DatiRiferimento::fromArray($datiConvenzione));
                }
            } else {
                $datiGenerali->addDatiConvenzione(DatiRiferimento::fromArray($array['DatiConvenzione']));
            }
        }

        if (isset($array['DatiRicezione'])) {
            if (isset($array['DatiRicezione'][0])) {
                foreach ($array['DatiRicezione'] as $datiRicezione) {
                    $datiGenerali->addDatiRicezione(DatiRiferimento::fromArray($datiRicezione));
                }
            } else {
                $datiGenerali->addDatiRicezione(DatiRiferimento::fromArray($array['DatiRicezione']));
            }
        }

        if (isset($array['DatiFattureCollegate'])) {
            if (isset($array['DatiFattureCollegate'][0])) {
                foreach ($array['DatiFattureCollegate'] as $datiFatttureCollegate) {
                    $datiGenerali->addDatiFattureCollegate(DatiRiferimento::fromArray($datiFatttureCollegate));
                }
            } else {
                $datiGenerali->addDatiFattureCollegate(DatiRiferimento::fromArray($array['DatiFattureCollegate']));
            }
        }

        if (isset($array['DatiSAL'])) {
            if (isset($array['DatiSAL'][0])) {
                foreach ($array['DatiSAL'] as $item) {
                    $datiGenerali->addDatiSAL(DatiSAL::fromArray($item));
                }
            } else {
                $datiGenerali->addDatiSAL(DatiSAL::fromArray($array['DatiSAL']));
            }
        }

        if (isset($array['DatiDDT'])) {
            if (isset($array['DatiDDT'][0])) {
                foreach ($array['DatiDDT'] as $item) {
                    $datiGenerali->addDatiDDT(DatiDDT::fromArray($item));
                }
            } else {
                $datiGenerali->addDatiDDT(DatiDDT::fromArray($array['DatiDDT']));
            }
        }

        if (isset($array['DatiTrasporto']) && is_array($array['DatiTrasporto'])) {
            $datiGenerali->setDatiTrasporto(DatiTrasporto::fromArray($array['DatiTrasporto']));
        }

        if (isset($array['FatturaPrincipale']['NumeroFatturaPrincipale']) &&
            !empty(trim($array['FatturaPrincipale']['NumeroFatturaPrincipale']))) {
            $datiGenerali->setNumeroFatturaPrincipale($array['FatturaPrincipale']['NumeroFatturaPrincipale']);
        }
        if (isset($array['FatturaPrincipale']['DataFatturaPrincipale']) &&
            !empty(trim($array['FatturaPrincipale']['DataFatturaPrincipale']))) {
            $datiGenerali->setDataFatturaPrincipale($array['FatturaPrincipale']['DataFatturaPrincipale']);
        }

        return $datiGenerali;
    }
}