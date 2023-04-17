<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class DatiRiepilogo implements FatturaElettronicaInterface
{
    /**
     * @var string
     */
    protected $AliquotaIVA;

    /**
     * @var string|null
     */
    protected $Natura;

    /**
     * @var string|null
     */
    protected $SpeseAccessorie;

    /**
     * @var string|null
     */
    protected $Arrotondamento;

    /**
     * @var string
     */
    protected $ImponibileImporto;

    /**
     * @var string
     */
    protected $Imposta;

    /**
     * @var string|null
     */
    protected $EsigibilitaIVA;

    /**
     * @var string|null
     */
    protected $RiferimentoNormativo;

    /**
     * DatiRiepilogo constructor.
     * @param float $AliquotaIVA
     * @param float $ImponibileImporto
     * @param float $Imposta
     */
    public function __construct(float $AliquotaIVA, float $ImponibileImporto, float $Imposta)
    {
        $this->setAliquotaIVA($AliquotaIVA);
        $this->setImponibileImporto($ImponibileImporto);
        $this->setImposta($Imposta);
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
     * @return DatiRiepilogo
     */
    public function setAliquotaIVA(float $AliquotaIVA): self
    {
        $this->AliquotaIVA = number_format($AliquotaIVA, 2, '.', '');

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
     * @return DatiRiepilogo
     */
    public function setNatura(?string $Natura): self
    {
        $this->Natura = $Natura;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSpeseAccessorie(): ?string
    {
        return $this->SpeseAccessorie;
    }

    /**
     * @param float|null $SpeseAccessorie
     * @param int $Precision
     * @return DatiRiepilogo
     */
    public function setSpeseAccessorie(?float $SpeseAccessorie, int $Precision = 8): self
    {
        $this->SpeseAccessorie = number_format($SpeseAccessorie, $Precision, '.', '');

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
     * @param int $Precision
     * @return DatiRiepilogo
     */
    public function setArrotondamento(?float $Arrotondamento, int $Precision = 8): self
    {
        $this->Arrotondamento = number_format($Arrotondamento, $Precision, '.', '');

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImponibileImporto(): ?string
    {
        return $this->ImponibileImporto;
    }

    /**
     * @param float $ImponibileImporto
     * @param int $Precision
     * @return DatiRiepilogo
     */
    public function setImponibileImporto(float $ImponibileImporto): self
    {
        $this->ImponibileImporto = number_format($ImponibileImporto, 2, '.', '');

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImposta(): ?string
    {
        return $this->Imposta;
    }

    /**
     * @param float $Imposta
     * @return DatiRiepilogo
     */
    public function setImposta(float $Imposta): self
    {
        $this->Imposta = number_format($Imposta, 2, '.', '');

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEsigibilitaIVA(): ?string
    {
        return $this->EsigibilitaIVA;
    }

    /**
     * @param string|null $EsigibilitaIVA
     * @return DatiRiepilogo
     */
    public function setEsigibilitaIVA(?string $EsigibilitaIVA): self
    {
        $this->EsigibilitaIVA = $EsigibilitaIVA;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRiferimentoNormativo(): ?string
    {
        return $this->RiferimentoNormativo;
    }

    /**
     * @param string|null $RiferimentoNormativo
     * @return DatiRiepilogo
     */
    public function setRiferimentoNormativo(?string $RiferimentoNormativo): self
    {
        $this->RiferimentoNormativo = $RiferimentoNormativo;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [
            'AliquotaIVA' => $this->getAliquotaIVA(),
            'Natura' => null,
            'SpeseAccessorie' => null,
            'Arrotondamento' => null,
            'ImponibileImporto' => $this->getImponibileImporto(),
            'Imposta' => $this->getImposta(),
            'EsigibilitaIVA' => null,
            'RiferimentoNormativo' => null,
        ];

        if (!empty($this->getNatura())) {
            $array['Natura'] = $this->getNatura();
        }
        if (!empty($this->getSpeseAccessorie())) {
            $array['SpeseAccessorie'] = $this->getSpeseAccessorie();
        }
        if (!empty($this->getArrotondamento())) {
            $array['Arrotondamento'] = $this->getArrotondamento();
        }
        if (!empty($this->getEsigibilitaIVA())) {
            $array['EsigibilitaIVA'] = $this->getEsigibilitaIVA();
        }
        if (!empty($this->getRiferimentoNormativo())) {
            $array['RiferimentoNormativo'] = $this->getRiferimentoNormativo();
        }

        return $array;
    }

    /**
     * @param array $array
     * @return DatiRiepilogo
     */
    public static function fromArray(array $array): self
    {
        $datiRiepilogo = new self($array['AliquotaIVA'], $array['ImponibileImporto'], $array['Imposta']);

        if (isset($array['Natura']) && !empty(trim($array['Natura']))) {
            $datiRiepilogo->setNatura($array['Natura']);
        }
        if (isset($array['SpeseAccessorie']) && !empty(trim($array['SpeseAccessorie']))) {
            $datiRiepilogo->setSpeseAccessorie($array['SpeseAccessorie']);
        }
        if (isset($array['Arrotondamento']) && !empty(trim($array['Arrotondamento']))) {
            $datiRiepilogo->setArrotondamento($array['Arrotondamento']);
        }
        if (isset($array['EsigibilitaIVA']) && !empty(trim($array['EsigibilitaIVA']))) {
            $datiRiepilogo->setEsigibilitaIVA($array['EsigibilitaIVA']);
        }
        if (isset($array['RiferimentoNormativo']) && !empty(trim($array['RiferimentoNormativo']))) {
            $datiRiepilogo->setRiferimentoNormativo($array['RiferimentoNormativo']);
        }

        return $datiRiepilogo;
    }
}