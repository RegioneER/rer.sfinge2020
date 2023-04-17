<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaException;
use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;
use Gdbnet\FatturaElettronica\Structures\ScontoMaggiorazione;

class DatiGeneraliDocumento implements FatturaElettronicaInterface
{
    /**
     * @var string|null
     */
    protected $TipoDocumento;

    /**
     * @var string|null
     */
    protected $Divisa;

    /**
     * @var string|null
     */
    protected $Data;

    /**
     * @var string|null
     */
    protected $Numero;

    /**
     * @var string|null
     */
    protected $TipoRitenuta;

    /**
     * @var string|null
     */
    protected $ImportoRitenuta;

    /**
     * @var string|null
     */
    protected $AliquotaRitenuta;

    /**
     * @var string|null
     */
    protected $CausalePagamento;

    /**
     * @var bool|null
     */
    protected $BolloVirtuale;

    /**
     * @var string|null
     */
    protected $ImportoBollo;

    /**
     * @var DatiCassaPrevidenziale[]|null;
     */
    protected $DatiCassaPrevidenziale;

    /**
     * @var ScontoMaggiorazione[]|null;
     */
    protected $ScontoMaggiorazione;

    /**
     * @var string|null;
     */
    protected $ImportoTotaleDocumento;

    /**
     * @var string|null;
     */
    protected $Arrotondamento;

    /**
     * @var string[]|null;
     */
    protected $Causale;

    /**
     * @var boolean;
     */
    protected $Art73 = false;

    /**
     * DatiGeneraliDocumento constructor.
     * @param string $TipoDocumento
     * @param string $Data
     * @param string $Numero
     * @param string $Divisa
     */
    public function __construct(
        string $TipoDocumento,
        string $Data,
        string $Numero,
        string $Divisa = 'EUR'
    )
    {
        $this->setTipoDocumento($TipoDocumento);
        $this->setData($Data);
        $this->setNumero($Numero);
        $this->setDivisa($Divisa);
    }

    /**
     * @return null|string
     */
    public function getTipoDocumento(): ?string
    {
        return $this->TipoDocumento;
    }

    /**
     * @param null|string $TipoDocumento
     * @return DatiGeneraliDocumento
     */
    public function setTipoDocumento(?string $TipoDocumento): self
    {
        $this->TipoDocumento = $TipoDocumento;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDivisa(): ?string
    {
        return $this->Divisa;
    }

    /**
     * @param null|string $Divisa
     * @return DatiGeneraliDocumento
     */
    public function setDivisa(?string $Divisa): self
    {
        $this->Divisa = $Divisa;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getData(): ?string
    {
        return $this->Data;
    }

    /**
     * @param null|string $Data
     * @return DatiGeneraliDocumento
     */
    public function setData(?string $Data): self
    {
        $this->Data = $Data;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNumero(): ?string
    {
        return $this->Numero;
    }

    /**
     * @param null|string $Numero
     * @return DatiGeneraliDocumento
     */
    public function setNumero(?string $Numero): self
    {
        $this->Numero = $Numero;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTipoRitenuta(): ?string
    {
        return $this->TipoRitenuta;
    }

    /**
     * @param null|string $TipoRitenuta
     * @return DatiGeneraliDocumento
     */
    public function setTipoRitenuta(?string $TipoRitenuta): self
    {
        $this->TipoRitenuta = $TipoRitenuta;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getImportoRitenuta(): ?string
    {
        return $this->ImportoRitenuta;
    }

    /**
     * @param float|null $ImportoRitenuta
     * @return DatiGeneraliDocumento
     */
    public function setImportoRitenuta(?float $ImportoRitenuta): self
    {
        $this->ImportoRitenuta = number_format($ImportoRitenuta, 2, '.', '');

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAliquotaRitenuta(): ?string
    {
        return $this->AliquotaRitenuta;
    }

    /**
     * @param float|null $AliquotaRitenuta
     * @return DatiGeneraliDocumento
     */
    public function setAliquotaRitenuta(?string $AliquotaRitenuta): self
    {
        $this->AliquotaRitenuta = number_format($AliquotaRitenuta, 2, '.', '');

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCausalePagamento(): ?string
    {
        return $this->CausalePagamento;
    }

    /**
     * @param null|string $CausalePagamento
     * @return DatiGeneraliDocumento
     */
    public function setCausalePagamento(?string $CausalePagamento): self
    {
        $this->CausalePagamento = $CausalePagamento;

        return $this;
    }

    /**
     * @return null|bool
     */
    public function getBolloVirtuale(): ?bool
    {
        return $this->BolloVirtuale;
    }

    /**
     * @param null|bool $BolloVirtuale
     * @return DatiGeneraliDocumento
     */
    public function setBolloVirtuale(?bool $BolloVirtuale): self
    {
        $this->BolloVirtuale = $BolloVirtuale;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImportoBollo(): ?string
    {
        return $this->ImportoBollo;
    }

    /**
     * @param float|null $ImportoBollo
     * @return DatiGeneraliDocumento
     */
    public function setImportoBollo(?float $ImportoBollo): self
    {
        $this->ImportoBollo = number_format($ImportoBollo, 2, '.', '');

        return $this;
    }

    /**
     * @return DatiCassaPrevidenziale[]|null
     */
    public function getDatiCassaPrevidenziale(): ?array
    {
        return $this->DatiCassaPrevidenziale;
    }

    /**
     * @param DatiCassaPrevidenziale[] $DatiCassaPrevidenziale
     * @return DatiGenerali
     */
    public function setDatiCassaPrevidenziale(array $DatiCassaPrevidenziale): self
    {
        $this->DatiOrdineAcquisto = $DatiCassaPrevidenziale;

        return $this;
    }

    /**
     * @param DatiCassaPrevidenziale|null $DatiOrdineAcquisto
     * @return DatiGenerali
     */
    public function addDatiCassaPrevidenziale(?DatiCassaPrevidenziale $DatiCassaPrevidenziale): self
    {
        $this->DatiCassaPrevidenziale[] = $DatiCassaPrevidenziale;

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
    public function getImportoTotaleDocumento(): ?string
    {
        return $this->ImportoTotaleDocumento;
    }

    /**
     * @param float|null $ImportoTotaleDocumento
     * @return DatiGeneraliDocumento
     */
    public function setImportoTotaleDocumento(?float $ImportoTotaleDocumento): self
    {
        $this->ImportoTotaleDocumento = number_format($ImportoTotaleDocumento, 2, '.', '');

        return $this;
    }

    /**
     * @return string|null
     */
    public function getArrotondamento(): ?string
    {
        return $this->Arrotondamento;
    }

    /**
     * @param float|null $Arrotondamento
     * @return DatiGeneraliDocumento
     */
    public function setArrotondamento(?float $Arrotondamento): self
    {
        $this->Arrotondamento = number_format($Arrotondamento, 2, '.', '');

        return $this;
    }

    /**
     * @return null|string[]
     */
    public function getCausale(): ?array
    {
        return $this->Causale;
    }

    /**
     * @param null|string[] $Causale
     * @return DatiGeneraliDocumento
     */
    public function setCausale($Causale): self
    {
        if (is_array($Causale)) {
            $this->Causale = $Causale;
        } else {
            $this->Causale[] = $Causale;
        }

        return $this;
    }

    /**
     * @param string $Causale
     * @return DatiGeneraliDocumento
     */
    public function addCausale(string $Causale): self
    {
        $this->Causale[] = $Causale;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getArt73(): bool
    {
        return $this->Art73;
    }

    /**
     * @param boolean $Art73
     * @return DatiGeneraliDocumento
     */
    public function setArt73(bool $Art73): self
    {
        $this->Art73 = $Art73;

        return $this;
    }

    /**
     * @return array
     * @throws FatturaElettronicaException
     */
    public function toArray()
    {
        $array = [
            'TipoDocumento' => null,
            'Divisa' => null,
            'Data' => null,
            'Numero' => null,
            'DatiRitenuta' => null,
            'DatiBollo' => null,
            'DatiCassaPrevidenziale' => null,
            'ScontoMaggiorazione' => null,
            'ImportoTotaleDocumento' => null,
            'Arrotondamento' => null,
            'Causale' => null,
            'Art73' => null,
        ];

        if (empty($this->getTipoDocumento())) {
            throw new FatturaElettronicaException("Missing 'TipoDocumento'", 'FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento');
        } else {
            $array['TipoDocumento'] = $this->getTipoDocumento();
        }

        if (empty($this->getDivisa())) {
            throw new FatturaElettronicaException("Missing 'Divisa'", 'FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento');
        } else {
            $array['Divisa'] = $this->getDivisa();
        }

        if (empty($this->getData())) {
            throw new FatturaElettronicaException("Missing 'Data'", 'FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento');
        } else {
            $array['Data'] = $this->getData();
        }

        if (empty($this->getNumero())) {
            throw new FatturaElettronicaException("Missing 'Numero'", 'FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento');
        } else {
            $array['Numero'] = $this->getNumero();
        }

        if (!empty($this->getTipoRitenuta())) {
            $array['DatiRitenuta']['TipoRitenuta'] = $this->getTipoRitenuta();
        }
        if (!empty($this->getImportoRitenuta())) {
            $array['DatiRitenuta']['ImportoRitenuta'] = $this->getImportoRitenuta();
        }
        if (!empty($this->getAliquotaRitenuta())) {
            $array['DatiRitenuta']['AliquotaRitenuta'] = $this->getAliquotaRitenuta();
        }
        if (!empty($this->getCausalePagamento())) {
            $array['DatiRitenuta']['CausalePagamento'] = $this->getCausalePagamento();
        }

        if (!empty($this->getBolloVirtuale())) {
            $array['DatiBollo']['BolloVirtuale'] = $this->getBolloVirtuale();
        }
        if (!empty($this->getImportoBollo())) {
            $array['DatiBollo']['ImportoBollo'] = $this->getImportoBollo();
        }

        if (!empty($this->getDatiCassaPrevidenziale())) {
            foreach ($this->getDatiCassaPrevidenziale() as $datiCassaPrevidenziale) {
                $array['DatiCassaPrevidenziale'][] = $datiCassaPrevidenziale->toArray();
            }
        }

        if (!empty($this->getScontoMaggiorazione())) {
            foreach ($this->getScontoMaggiorazione() as $scontoMaggiorazione) {
                $array['ScontoMaggiorazione'][] = $scontoMaggiorazione->toArray();
            }
        }

        if ($this->getImportoTotaleDocumento() !== null) {
            $array['ImportoTotaleDocumento'] = $this->getImportoTotaleDocumento();
        }
        if (!empty($this->getArrotondamento())) {
            $array['Arrotondamento'] = $this->getArrotondamento();
        }
        if (!empty($this->getCausale())) {
            $array['Causale'] = $this->getCausale();
        }

        if ($this->getArt73()) {
            $array['Art73'] = 'SI';
        }

        return $array;
    }

    /**
     * @param array $array
     * @return DatiGeneraliDocumento
     */
    public static function fromArray(array $array): self
    {
        $datiGeneraliDocumento = new self(
            $array['TipoDocumento'],
            $array['Data'],
            $array['Numero'],
            $array['Divisa']
        );

        if (isset($array['DatiRitenuta']['TipoRitenuta']) &&
            !empty(trim($array['DatiRitenuta']['TipoRitenuta']))) {
            $datiGeneraliDocumento->setTipoRitenuta($array['DatiRitenuta']['TipoRitenuta']);
        }
        if (isset($array['DatiRitenuta']['ImportoRitenuta']) &&
            !empty(trim($array['DatiRitenuta']['ImportoRitenuta']))) {
            $datiGeneraliDocumento->setImportoRitenuta($array['DatiRitenuta']['ImportoRitenuta']);
        }
        if (isset($array['DatiRitenuta']['AliquotaRitenuta']) &&
            !empty(trim($array['DatiRitenuta']['AliquotaRitenuta']))) {
            $datiGeneraliDocumento->setAliquotaRitenuta($array['DatiRitenuta']['AliquotaRitenuta']);
        }
        if (isset($array['DatiRitenuta']['CausalePagamento']) &&
            !empty(trim($array['DatiRitenuta']['CausalePagamento']))) {
            $datiGeneraliDocumento->setCausalePagamento($array['DatiRitenuta']['CausalePagamento']);
        }

        if (isset($array['DatiBollo']['BolloVirtuale']) && !empty(trim($array['DatiBollo']['BolloVirtuale']))) {
            $datiGeneraliDocumento->setBolloVirtuale($array['DatiBollo']['BolloVirtuale']);
        }
        if (isset($array['DatiBollo']['ImportoBollo']) && !empty(trim($array['DatiBollo']['ImportoBollo']))) {
            $datiGeneraliDocumento->setImportoBollo($array['DatiBollo']['ImportoBollo']);
        }

        if (isset($array['DatiCassaPrevidenziale'])) {
            if (isset($array['DatiCassaPrevidenziale'][0])) {
                foreach ($array['DatiCassaPrevidenziale'] as $datiCassaPrevidenziale) {
                    $datiGeneraliDocumento->addDatiCassaPrevidenziale(DatiCassaPrevidenziale::fromArray($datiCassaPrevidenziale));
                }
            } else {
                $datiGeneraliDocumento->addDatiCassaPrevidenziale(DatiCassaPrevidenziale::fromArray($array['DatiCassaPrevidenziale']));
            }
        }

        if (isset($array['ScontoMaggiorazione'])) {
            if (isset($array['ScontoMaggiorazione'][0])) {
                foreach ($array['ScontoMaggiorazione'] as $scontoMaggiorazione) {
                    $datiGeneraliDocumento->addScontoMaggiorazione(ScontoMaggiorazione::fromArray($scontoMaggiorazione));
                }
            } else {
                $datiGeneraliDocumento->addScontoMaggiorazione(ScontoMaggiorazione::fromArray($array['ScontoMaggiorazione']));
            }
        }

        if (isset($array['ImportoTotaleDocumento']) && !empty(trim($array['ImportoTotaleDocumento']))) {
            $datiGeneraliDocumento->setImportoTotaleDocumento($array['ImportoTotaleDocumento']);
        }

        if (isset($array['Arrotondamento']) && !empty(trim($array['Arrotondamento']))) {
            $datiGeneraliDocumento->setArrotondamento($array['Arrotondamento']);
        }

        if (isset($array['Causale'])) {
            $datiGeneraliDocumento->setCausale($array['Causale']);
        }

        if (isset($array['Art73']) && $array['Art73'] == 'SI') {
            $datiGeneraliDocumento->setArt73(true);
        }

        return $datiGeneraliDocumento;
    }
}