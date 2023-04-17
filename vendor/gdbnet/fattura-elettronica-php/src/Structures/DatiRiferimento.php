<?php

namespace Gdbnet\FatturaElettronica\Structures;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class DatiRiferimento implements FatturaElettronicaInterface
{
    /**
     * @var null|array
     */
    protected $RiferimentoNumeroLinea = null;

    /**
     * @var string
     */
    protected $IdDocumento = null;

    /**
     * @var null|string
     */
    protected $Data = null;

    /**
     * @var null|string
     */
    protected $NumItem = null;

    /**
     * @var null|string
     */
    protected $CodiceCommessaConvenzione = null;

    /**
     * @var null|string
     */
    protected $CodiceCUP = null;

    /**
     * @var null|string
     */
    protected $CodiceCIG = null;

    /**
     * DatiRiferimento constructor.
     * @param string $IdDocumento
     */
    public function __construct(string $IdDocumento)
    {
        $this->setIdDocumento($IdDocumento);
    }

    /**
     * @return array|null
     */
    public function getRiferimentoNumeroLinea(): ?array
    {
        return $this->RiferimentoNumeroLinea;
    }

    /**
     * @param array|null $RiferimentoNumeroLinea
     * @return DatiRiferimento
     */
    public function setRiferimentoNumeroLinea(?array $RiferimentoNumeroLinea): self
    {
        $this->RiferimentoNumeroLinea = $RiferimentoNumeroLinea;

        return $this;
    }

    /**
     * @param int $RiferimentoNumeroLinea
     * @return DatiRiferimento
     */
    public function addRiferimentoNumeroLinea(int $RiferimentoNumeroLinea): self
    {
        $this->RiferimentoNumeroLinea[] = $RiferimentoNumeroLinea;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getIdDocumento(): ?string
    {
        return $this->IdDocumento;
    }

    /**
     * @param string $IdDocumento
     * @return DatiRiferimento
     */
    public function setIdDocumento(string $IdDocumento): self
    {
        $this->IdDocumento = $IdDocumento;

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
     * @return DatiRiferimento
     */
    public function setData(?string $Data): self
    {
        $this->Data = $Data;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNumItem(): ?string
    {
        return $this->NumItem;
    }

    /**
     * @param null|string $NumItem
     * @return DatiRiferimento
     */
    public function setNumItem(?string $NumItem): self
    {
        $this->NumItem = $NumItem;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCodiceCommessaConvenzione(): ?string
    {
        return $this->CodiceCommessaConvenzione;
    }

    /**
     * @param null|string $CodiceCommessaConvenzione
     * @return DatiRiferimento
     */
    public function setCodiceCommessaConvenzione(?string $CodiceCommessaConvenzione): self
    {
        $this->CodiceCommessaConvenzione = $CodiceCommessaConvenzione;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCodiceCUP(): ?string
    {
        return $this->CodiceCUP;
    }

    /**
     * @param null|string $CodiceCUP
     * @return DatiRiferimento
     */
    public function setCodiceCUP(?string $CodiceCUP): self
    {
        $this->CodiceCUP = $CodiceCUP;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCodiceCIG(): ?string
    {
        return $this->CodiceCIG;
    }

    /**
     * @param null|string $CodiceCIG
     * @return DatiRiferimento
     */
    public function setCodiceCIG(?string $CodiceCIG): self
    {
        $this->CodiceCIG = $CodiceCIG;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'RiferimentoNumeroLinea' => $this->getRiferimentoNumeroLinea(),
            'IdDocumento' => $this->getIdDocumento(),
            'Data' => $this->getData(),
            'NumItem' => $this->getNumItem(),
            'CodiceCommessaConvenzione' => $this->getCodiceCommessaConvenzione(),
            'CodiceCUP' => $this->getCodiceCUP(),
            'CodiceCIG' => $this->getCodiceCIG()
        ];
    }

    /**
     * @param $array
     * @return DatiRiferimento
     */
    public static function fromArray(array $array): self
    {
        $datiRiferimento = new self($array['IdDocumento']);

        if (isset($array['RiferimentoNumeroLinea'])) {
            if (is_array($array['RiferimentoNumeroLinea'])) {
                $datiRiferimento->setRiferimentoNumeroLinea($array['RiferimentoNumeroLinea']);
            } else {
                $datiRiferimento->addRiferimentoNumeroLinea($array['RiferimentoNumeroLinea']);
            }
        }
        if (isset($array['Data']) && !empty(trim($array['Data']))) {
            $datiRiferimento->setData($array['Data']);
        }
        if (isset($array['NumItem']) && !empty(trim($array['NumItem']))) {
            $datiRiferimento->setNumItem($array['NumItem']);
        }
        if (isset($array['CodiceCommessaConvenzione']) && !empty(trim($array['CodiceCommessaConvenzione']))) {
            $datiRiferimento->setCodiceCommessaConvenzione($array['CodiceCommessaConvenzione']);
        }
        if (isset($array['CodiceCUP']) && !empty(trim($array['CodiceCUP']))) {
            $datiRiferimento->setCodiceCUP($array['CodiceCUP']);
        }
        if (isset($array['CodiceCIG']) && !empty(trim($array['CodiceCIG']))) {
            $datiRiferimento->setCodiceCIG($array['CodiceCIG']);
        }

        return $datiRiferimento;
    }
}