<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class DatiCassaPrevidenziale implements FatturaElettronicaInterface
{
    /**
     * @var string|null;
     */
    protected $TipoCassa;

    /**
     * @var string|null;
     */
    protected $AlCassa;

    /**
     * @var string|null;
     */
    protected $ImportoContributoCassa;

    /**
     * @var string|null;
     */
    protected $ImponibileCassa;

    /**
     * @var string|null;
     */
    protected $AliquotaIVA;

    /**
     * @var string|null;
     */
    protected $Ritenuta;

    /**
     * @var string|null;
     */
    protected $Natura;

    /**
     * @var string|null;
     */
    protected $RiferimentoAmministrazione;

    /**
     * DatiCassaPrevidenziale constructor.
     * @param string $TipoCassa
     * @param float $AlCassa
     * @param float $ImportoContributoCassa
     * @param float $AliquotaIVA
     */
    public function __construct(
        string $TipoCassa,
        float $AlCassa,
        float $ImportoContributoCassa,
        float $AliquotaIVA
    )
    {
        $this->setTipoCassa($TipoCassa);
        $this->setAlCassa($AlCassa);
        $this->setImportoContributoCassa($ImportoContributoCassa);
        $this->setAliquotaIVA($AliquotaIVA);
    }

    /**
     * @return null|string
     */
    public function getTipoCassa(): ?string
    {
        return $this->TipoCassa;
    }

    /**
     * @param null|string $TipoCassa
     * @return DatiCassaPrevidenziale
     */
    public function setTipoCassa(string $TipoCassa): self
    {
        $this->TipoCassa = $TipoCassa;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAlCassa(): ?string
    {
        return $this->AlCassa;
    }

    /**
     * @param null|string $AlCassa
     * @return DatiCassaPrevidenziale
     */
    public function setAlCassa(string $AlCassa): self
    {
        $this->AlCassa = number_format($AlCassa, 2, '.', '');

        return $this;
    }

    /**
     * @return null|string
     */
    public function getImportoContributoCassa(): ?string
    {
        return $this->ImportoContributoCassa;
    }

    /**
     * @param float|null $ImportoContributoCassa
     * @return DatiCassaPrevidenziale
     */
    public function setImportoContributoCassa(float $ImportoContributoCassa): self
    {
        $this->ImportoContributoCassa = number_format($ImportoContributoCassa, 2, '.', '');

        return $this;
    }

    /**
     * @return null|string
     */
    public function getImponibileCassa(): ?string
    {
        return $this->ImponibileCassa;
    }

    /**
     * @param float|null $ImponibileCassa
     * @return DatiCassaPrevidenziale
     */
    public function setImponibileCassa(?float $ImponibileCassa): self
    {
        $this->ImponibileCassa = number_format($ImponibileCassa, 2, '.', '');

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
     * @param float|null $AliquotaIVA
     * @return DatiCassaPrevidenziale
     */
    public function setAliquotaIVA(float $AliquotaIVA): self
    {
        $this->AliquotaIVA = number_format($AliquotaIVA, 2, '.', '');

        return $this;
    }

    /**
     * @return null|bool
     */
    public function getRitenuta(): ?bool
    {
        return $this->Ritenuta;
    }

    /**
     * @param null|bool $Ritenuta
     * @return DatiCassaPrevidenziale
     */
    public function setRitenuta(?bool $Ritenuta): self
    {
        $this->Ritenuta = $Ritenuta;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNatura(): ?string
    {
        return $this->Natura;
    }

    /**
     * @param null|string $Natura
     * @return DatiCassaPrevidenziale
     */
    public function setNatura(?string $Natura): self
    {
        $this->Natura = $Natura;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRiferimentoAmministrazione(): ?string
    {
        return $this->RiferimentoAmministrazione;
    }

    /**
     * @param null|string $RiferimentoAmministrazione
     * @return DatiCassaPrevidenziale
     */
    public function setRiferimentoAmministrazione(?string $RiferimentoAmministrazione): self
    {
        $this->RiferimentoAmministrazione = $RiferimentoAmministrazione;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'TipoCassa' => $this->getTipoCassa(),
            'AlCassa' => $this->getAlCassa(),
            'ImportoContributoCassa' => $this->getImportoContributoCassa(),
            'ImponibileCassa' => $this->getImponibileCassa(),
            'AliquotaIVA' => $this->getAliquotaIVA(),
            'Ritenuta' => $this->getRitenuta() ? 'SI' : null,
            'Natura' => $this->getNatura(),
            'RiferimentoAmministrazione' => $this->getRiferimentoAmministrazione(),
        ];
    }

    /**
     * @param array $array
     * @return DatiCassaPrevidenziale
     */
    public static function fromArray(array $array): self
    {
        $datiCassaPrevidenziale = new self(
            $array['TipoCassa'],
            $array['AlCassa'],
            $array['ImportoContributoCassa'],
            $array['AliquotaIVA']
        );

        if (isset($array['ImponibileCassa']) && !empty(trim($array['ImponibileCassa']))) {
            $datiCassaPrevidenziale->setImponibileCassa($array['ImponibileCassa']);
        }
        if (isset($array['Ritenuta']) && !empty(trim($array['Ritenuta']))) {
            $datiCassaPrevidenziale->setRitenuta($array['Ritenuta']);
        }
        if (isset($array['Natura']) && !empty(trim($array['Natura']))) {
            $datiCassaPrevidenziale->setNatura($array['Natura']);
        }
        if (isset($array['RiferimentoAmministrazione']) && !empty(trim($array['RiferimentoAmministrazione']))) {
            $datiCassaPrevidenziale->setRiferimentoAmministrazione($array['RiferimentoAmministrazione']);
        }

        return $datiCassaPrevidenziale;
    }
}
