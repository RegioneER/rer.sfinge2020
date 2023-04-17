<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;
use Gdbnet\FatturaElettronica\Structures\ScontoMaggiorazione;

class DettaglioLinea implements FatturaElettronicaInterface
{
    /**
     * @var int
     */
    protected $NumeroLinea;

    /**
     * @var string|null
     */
    protected $TipoCessionePrestazione;

    /**
     * @var CodiceArticolo[]|null
     */
    protected $CodiceArticolo;

    /**
     * @var string|null
     */
    protected $Descrizione;

    /**
     * @var string|null
     */
    protected $Quantita;

    /**
     * @var string|null
     */
    protected $UnitaMisura;

    /**
     * @var string|null
     */
    protected $DataInizioPeriodo;

    /**
     * @var string|null
     */
    protected $DataFinePeriodo;

    /**
     * @var string
     */
    protected $PrezzoUnitario;

    /**
     * @var ScontoMaggiorazione[]|null;
     */
    protected $ScontoMaggiorazione;

    /**
     * @var string
     */
    protected $PrezzoTotale;

    /**
     * @var string
     */
    protected $AliquotaIVA;

    /**
     * @var bool
     */
    protected $Ritenuta = false;

    /**
     * @var string|null
     */
    protected $Natura;

    /**
     * @var string|null
     */
    protected $RiferimentoAmministrazione;

    /**
     * @var AltriDatiGestionali[]|null;
     */
    protected $AltriDatiGestionali;

    /**
     * DettaglioLinea constructor.
     * @param int $NumeroLinea
     * @param string $Descrizione
     * @param float $PrezzoUnitario
     * @param float $PrezzoTotale
     * @param float $AliquotaIVA
     */
    public function __construct(
        int $NumeroLinea,
        string $Descrizione,
        float $PrezzoUnitario,
        float $PrezzoTotale,
        float $AliquotaIVA
    )
    {
        $this->setNumeroLinea($NumeroLinea);
        $this->setDescrizione($Descrizione);
        $this->setPrezzoUnitario($PrezzoUnitario);
        $this->setPrezzoTotale($PrezzoTotale);
        $this->setAliquotaIVA($AliquotaIVA);
    }

    /**
     * @return int|null
     */
    public function getNumeroLinea(): ?int
    {
        return $this->NumeroLinea;
    }

    /**
     * @param int $NumeroLinea
     * @return DettaglioLinea
     */
    public function setNumeroLinea(int $NumeroLinea): self
    {
        $this->NumeroLinea = $NumeroLinea;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTipoCessionePrestazione(): ?string
    {
        return $this->TipoCessionePrestazione;
    }

    /**
     * @param string|null $TipoCessionePrestazione
     * @return DettaglioLinea
     */
    public function setTipoCessionePrestazione(?string $TipoCessionePrestazione): self
    {
        $this->TipoCessionePrestazione = $TipoCessionePrestazione;

        return $this;
    }

    /**
     * @return CodiceArticolo[]|null
     */
    public function getCodiceArticolo(): ?array
    {
        return $this->CodiceArticolo;
    }

    /**
     * @param array $ScontoMaggiorazione
     * @return DettaglioLinea
     */
    public function setCodiceArticolo(array $ScontoMaggiorazione): self
    {
        $this->CodiceArticolo = $ScontoMaggiorazione;

        return $this;
    }

    /**
     * @param CodiceArticolo|null $CodiceArticolo
     * @return DettaglioLinea
     */
    public function addCodiceArticolo(?CodiceArticolo $CodiceArticolo): self
    {
        $this->CodiceArticolo[] = $CodiceArticolo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescrizione(): ?string
    {
        return $this->Descrizione;
    }

    /**
     * @param string $Descrizione
     * @return DettaglioLinea
     */
    public function setDescrizione(string $Descrizione): self
    {
        $this->Descrizione = $Descrizione;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getQuantita(): ?string
    {
        return $this->Quantita;
    }

    /**
     * @param float|null $Quantita
     * @param int $precision
     * @return DettaglioLinea
     */
    public function setQuantita(?float $Quantita, int $precision = 5): self
    {
        $this->Quantita = number_format($Quantita, $precision, '.', '');

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUnitaMisura(): ?string
    {
        return $this->UnitaMisura;
    }

    /**
     * @param string|null $UnitaMisura
     * @return DettaglioLinea
     */
    public function setUnitaMisura(?string $UnitaMisura): self
    {
        $this->UnitaMisura = $UnitaMisura;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDataInizioPeriodo(): ?string
    {
        return $this->DataInizioPeriodo;
    }

    /**
     * @param string|null $DataInizioPeriodo
     * @return DettaglioLinea
     */
    public function setDataInizioPeriodo(?string $DataInizioPeriodo): self
    {
        $this->DataInizioPeriodo = $DataInizioPeriodo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDataFinePeriodo(): ?string
    {
        return $this->DataFinePeriodo;
    }

    /**
     * @param string|null $DataFinePeriodo
     * @return DettaglioLinea
     */
    public function setDataFinePeriodo(?string $DataFinePeriodo): self
    {
        $this->DataFinePeriodo = $DataFinePeriodo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrezzoUnitario(): ?string
    {
        return $this->PrezzoUnitario;
    }

    /**
     * @param float $PrezzoUnitario
     * @param int $Precision
     * @return DettaglioLinea
     */
    public function setPrezzoUnitario(float $PrezzoUnitario, int $Precision = 8): self
    {
        $this->PrezzoUnitario = number_format($PrezzoUnitario, $Precision, '.', '');

        return $this;
    }

    /**
     * @return ScontoMaggiorazione[]|null
     */
    public function getScontoMaggiorazione(): ?array
    {
        return $this->ScontoMaggiorazione;
    }

    /**
     * @param ScontoMaggiorazione[] $ScontoMaggiorazione
     * @return DatiGenerali
     */
    public function setScontoMaggiorazione(array $ScontoMaggiorazione): self
    {
        $this->ScontoMaggiorazione = $ScontoMaggiorazione;

        return $this;
    }

    /**
     * @param ScontoMaggiorazione|null $ScontoMaggiorazione
     * @return DatiGenerali
     */
    public function addScontoMaggiorazione(?ScontoMaggiorazione $ScontoMaggiorazione): self
    {
        $this->ScontoMaggiorazione[] = $ScontoMaggiorazione;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrezzoTotale(): ?string
    {
        return $this->PrezzoTotale;
    }

    /**
     * @param float $PrezzoTotale
     * @param int $Precision
     * @return DettaglioLinea
     */
    public function setPrezzoTotale(float $PrezzoTotale, int $Precision = 8): self
    {
        $this->PrezzoTotale = number_format($PrezzoTotale, $Precision, '.', '');

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAliquotaIVA(): ?string
    {
        return $this->AliquotaIVA;
    }

    /**
     * @param float $AliquotaIVA
     * @return DettaglioLinea
     */
    public function setAliquotaIVA(float $AliquotaIVA): self
    {
        $this->AliquotaIVA = number_format($AliquotaIVA, 2, '.', '');

        return $this;
    }

    /**
     * @return bool
     */
    public function getRitenuta(): bool
    {
        return $this->Ritenuta;
    }

    /**
     * @param bool $Ritenuta
     * @return DettaglioLinea
     */
    public function setRitenuta(bool $Ritenuta): self
    {
        $this->Ritenuta = $Ritenuta;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNatura(): ?string
    {
        return $this->Natura;
    }

    /**
     * @param string|null $Natura
     * @return DettaglioLinea
     */
    public function setNatura(?string $Natura): self
    {
        $this->Natura = $Natura;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRiferimentoAmministrazione(): ?string
    {
        return $this->RiferimentoAmministrazione;
    }

    /**
     * @param string|null $RiferimentoAmministrazione
     * @return DettaglioLinea
     */
    public function setRiferimentoAmministrazione(?string $RiferimentoAmministrazione): self
    {
        $this->RiferimentoAmministrazione = $RiferimentoAmministrazione;

        return $this;
    }

    /**
     * @return AltriDatiGestionali[]|null
     */
    public function getAltriDatiGestionali(): ?array
    {
        return $this->AltriDatiGestionali;
    }

    /**
     * @param AltriDatiGestionali[] $AltriDatiGestionali
     * @return DettaglioLinea
     */
    public function setAltriDatiGestionali(array $AltriDatiGestionali): self
    {
        $this->AltriDatiGestionali = $AltriDatiGestionali;

        return $this;
    }

    /**
     * @param AltriDatiGestionali|null $AltriDatiGestionali
     * @return DettaglioLinea
     */
    public function addAltriDatiGestionali(?AltriDatiGestionali $AltriDatiGestionali): self
    {
        $this->AltriDatiGestionali[] = $AltriDatiGestionali;

        return $this;
    }

    /**
     * @return array|mixed
     */
    public function toArray()
    {
        $array = [
            'NumeroLinea' => $this->getNumeroLinea(),
            'TipoCessionePrestazione' => $this->getTipoCessionePrestazione(),
            'CodiceArticolo' => null,
            'Descrizione' => $this->getDescrizione(),
            'Quantita' => !empty($this->getQuantita()) ? $this->getQuantita() : null,
            'UnitaMisura' => $this->getUnitaMisura(),
            'DataInizioPeriodo' => $this->getDataInizioPeriodo(),
            'DataFinePeriodo' => $this->getDataFinePeriodo(),
            'PrezzoUnitario' => !empty($this->getPrezzoUnitario()) ? $this->getPrezzoUnitario() : null,
            'ScontoMaggiorazione' => null,
            'PrezzoTotale' => !empty($this->getPrezzoTotale()) ? $this->getPrezzoTotale() : null,
            'AliquotaIVA' => !empty($this->getAliquotaIVA()) ? $this->getAliquotaIVA() : null,
            'Ritenuta' => $this->getRitenuta(),
            'Natura' => $this->getNatura(),
            'RiferimentoAmministrazione' => $this->getRiferimentoAmministrazione(),
            'AltriDatiGestionali' => null
        ];

        if (!empty($this->getCodiceArticolo())) {
            foreach ($this->getCodiceArticolo() as $codiceArticolo) {
                $array['CodiceArticolo'][] = $codiceArticolo->toArray();
            }
        }

        if (!empty($this->getScontoMaggiorazione())) {
            foreach ($this->getScontoMaggiorazione() as $scontoMaggiorazione) {
                $array['ScontoMaggiorazione'][] = $scontoMaggiorazione->toArray();
            }
        }

        if (!empty($this->getAltriDatiGestionali())) {
            foreach ($this->getAltriDatiGestionali() as $datiGestionali) {
                $array['AltriDatiGestionali'][] = $datiGestionali->toArray();
            }
        }

        return $array;
    }

    /**
     * @param array $array
     * @return DettaglioLinea
     */
    public static function fromArray(array $array): self
    {
        $dettaglioLinea = new self(
            $array['NumeroLinea'],
            $array['Descrizione'],
            $array['PrezzoUnitario'],
            $array['PrezzoTotale'],
            $array['AliquotaIVA']
        );

        if (isset($array['TipoCessionePrestazione']) && !empty(trim($array['TipoCessionePrestazione']))) {
            $dettaglioLinea->setTipoCessionePrestazione($array['TipoCessionePrestazione']);
        }
        if (isset($array['Quantita']) && !empty(trim($array['Quantita']))) {
            $dettaglioLinea->setQuantita($array['Quantita']);
        }
        if (isset($array['UnitaMisura']) && !empty(trim($array['UnitaMisura']))) {
            $dettaglioLinea->setUnitaMisura($array['UnitaMisura']);
        }
        if (isset($array['DataInizioPeriodo']) && !empty(trim($array['DataInizioPeriodo']))) {
            $dettaglioLinea->setDataInizioPeriodo($array['DataInizioPeriodo']);
        }
        if (isset($array['DataFinePeriodo']) && !empty(trim($array['DataFinePeriodo']))) {
            $dettaglioLinea->setDataFinePeriodo($array['DataFinePeriodo']);
        }
        if (isset($array['Ritenuta']) && !empty(trim($array['Ritenuta']))) {
            $dettaglioLinea->setRitenuta($array['Ritenuta']);
        }
        if (isset($array['Natura']) && !empty(trim($array['Natura']))) {
            $dettaglioLinea->setNatura($array['Natura']);
        }
        if (isset($array['RiferimentoAmministrazione']) && !empty(trim($array['RiferimentoAmministrazione']))) {
            $dettaglioLinea->setRiferimentoAmministrazione($array['RiferimentoAmministrazione']);
        }

        if (isset($array['CodiceArticolo'])) {
            if (isset($array['CodiceArticolo'][0])) {
                foreach ($array['CodiceArticolo'] as $codiceArticolo) {
                    $dettaglioLinea->addCodiceArticolo(CodiceArticolo::fromArray($codiceArticolo));
                }
            } else {
                $dettaglioLinea->addCodiceArticolo(CodiceArticolo::fromArray($array['CodiceArticolo']));
            }
        }

        if (isset($array['ScontoMaggiorazione'])) {
            if (isset($array['ScontoMaggiorazione'][0])) {
                foreach ($array['ScontoMaggiorazione'] as $scontoMaggiorazione) {
                    $dettaglioLinea->addScontoMaggiorazione(ScontoMaggiorazione::fromArray($scontoMaggiorazione));
                }
            } else {
                $dettaglioLinea->addScontoMaggiorazione(ScontoMaggiorazione::fromArray($array['ScontoMaggiorazione']));
            }
        }

        if (isset($array['AltriDatiGestionali'])) {
            if (isset($array['AltriDatiGestionali'][0])) {
                foreach ($array['AltriDatiGestionali'] as $datiGestionali) {
                    $dettaglioLinea->addAltriDatiGestionali(AltriDatiGestionali::fromArray($datiGestionali));
                }
            } else {
                $dettaglioLinea->addAltriDatiGestionali(AltriDatiGestionali::fromArray($array['AltriDatiGestionali']));
            }
        }

        return $dettaglioLinea;
    }
}