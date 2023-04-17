<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\AbstractModel;
use Gdbnet\FatturaElettronica\FatturaElettronicaException;
use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class FatturaElettronicaBody extends AbstractModel implements FatturaElettronicaInterface
{
    /**
     * @var DatiGenerali
     */
    protected $DatiGenerali;

    /**
     * @var DatiBeniServizi
     */
    protected $DatiBeniServizi;

    /**
     * @var DatiVeicoli|null
     */
    protected $DatiVeicoli = null;

    /**
     * @var DatiPagamento|null
     */
    protected $DatiPagamento;

    /**
     * @var Allegati|null
     */
    protected $Allegati = null;

    /**
     * FatturaElettronicaBody constructor.
     * @param DatiGenerali $DatiGenerali
     * @param DatiBeniServizi $DatiBeniServizi
     */
    public function __construct(DatiGenerali $DatiGenerali, DatiBeniServizi $DatiBeniServizi)
    {
        $this->setDatiGenerali($DatiGenerali);
        $this->setDatiBeniServizi($DatiBeniServizi);
    }

    /**
     * @return DatiGenerali|null
     */
    public function getDatiGenerali(): ?DatiGenerali
    {
        return $this->DatiGenerali;
    }

    /**
     * @param DatiGenerali $DatiGenerali
     * @return FatturaElettronicaBody
     */
    public function setDatiGenerali(DatiGenerali $DatiGenerali): self
    {
        $this->DatiGenerali = $DatiGenerali;

        return $this;
    }

    /**
     * @return DatiBeniServizi|null
     */
    public function getDatiBeniServizi(): ?DatiBeniServizi
    {
        return $this->DatiBeniServizi;
    }

    /**
     * @param DatiBeniServizi $DatiBeniServizi
     * @return FatturaElettronicaBody
     */
    public function setDatiBeniServizi(DatiBeniServizi $DatiBeniServizi): self
    {
        $this->DatiBeniServizi = $DatiBeniServizi;

        return $this;
    }

    /**
     * @return DatiVeicoli|null
     */
    public function getDatiVeicoli(): ?DatiVeicoli
    {
        return $this->DatiVeicoli;
    }

    /**
     * @param DatiVeicoli|null $DatiVeicoli
     * @return FatturaElettronicaBody
     */
    public function setDatiVeicoli(?DatiVeicoli $DatiVeicoli): self
    {
        $this->DatiVeicoli = $DatiVeicoli;

        return $this;
    }

    /**
     * @return DatiPagamento|null
     */
    public function getDatiPagamento(): ?array
    {
        return $this->DatiPagamento;
    }

    /**
     * @param DatiPagamento|null $DatiPagamento
     * @return FatturaElettronicaBody
     */
    public function setDatiPagamento(?DatiPagamento $DatiPagamento): self
    {
        $this->DatiPagamento = $DatiPagamento;

        return $this;
    }

    /**
     * @param DatiPagamento|null $DatiPagamento
     * @return FatturaElettronicaBody
     */
    public function addDatiPagamento(?DatiPagamento $DatiPagamento): self
    {
        $this->DatiPagamento[] = $DatiPagamento;

        return $this;
    }

    /**
     * @return Allegati|null
     */
    public function getAllegati(): ?array
    {
        return $this->Allegati;
    }

    /**
     * @param Allegati|null $Allegati
     * @return FatturaElettronicaBody
     */
    public function setAllegati(?Allegati $Allegati): self
    {
        $this->Allegati = $Allegati;

        return $this;
    }

    /**
     * @param Allegati|null $Allegati
     * @return FatturaElettronicaBody
     */
    public function addAllegati(?Allegati $Allegati): self
    {
        $this->Allegati[] = $Allegati;

        return $this;
    }

    /**
     * @return array
     * @throws FatturaElettronicaException
     */
    public function toArray()
    {
        $array = [
            'DatiGenerali' => null,
            'DatiBeniServizi' => null,
            'DatiVeicoli' => null,
            'DatiPagamento' => null,
            'Allegati' => null,
        ];

        if (!$this->getDatiGenerali() instanceof DatiGenerali) {
            throw new FatturaElettronicaException("Missing instance of 'DatiGenerali'", 'FatturaElettronicaBody');
        } else {
            $array['DatiGenerali'] = $this->getDatiGenerali()->toArray();
        }

        if (!$this->getDatiBeniServizi() instanceof DatiBeniServizi) {
            throw new FatturaElettronicaException("Missing instance of 'DatiBeniServizi'", 'FatturaElettronicaBody');
        } else {
            $array['DatiBeniServizi'] = $this->getDatiBeniServizi()->toArray();
        }

        if ($this->getDatiVeicoli() instanceof DatiVeicoli) {
            $array['DatiVeicoli'] = $this->getDatiVeicoli()->toArray();
        }

        if (!empty($this->getDatiPagamento())) {
            foreach ($this->getDatiPagamento() as $datiPagamento) {
                if (!$datiPagamento instanceof DatiPagamento) {
                    throw new FatturaElettronicaException("Missing instance of 'DatiPagamento'", 'FatturaElettronicaBody');
                }
                $array['DatiPagamento'][] = $datiPagamento->toArray();
            }
        }

        if (!empty($this->getAllegati())) {
            foreach ($this->getAllegati() as $allegato) {
                $array['Allegati'][] = $allegato->toArray();
            }
        }

        return $this->cleanArray($array);
    }

    /**
     * @param array $array
     * @return FatturaElettronicaBody
     * @throws FatturaElettronicaException
     */
    public static function fromArray(array $array): self
    {
        $DatiGenerali = DatiGenerali::fromArray($array['DatiGenerali']);
        $DatiBeniServizi = DatiBeniServizi::fromArray($array['DatiBeniServizi']);
        $fatturaElettronicaBody = new self($DatiGenerali, $DatiBeniServizi);

        if (isset($array['DatiVeicoli']) && is_array($array['DatiVeicoli'])) {
            $fatturaElettronicaBody->setDatiVeicoli(DatiVeicoli::fromArray($array['DatiVeicoli']));
        }

        if (isset($array['DatiPagamento'])) {
            if (isset($array['DatiPagamento'][0])) {
                foreach ($array['DatiPagamento'] as $datiPagamento) {
                    $fatturaElettronicaBody->addDatiPagamento(DatiPagamento::fromArray($datiPagamento));
                }
            } else {
                $fatturaElettronicaBody->addDatiPagamento(DatiPagamento::fromArray($array['DatiPagamento']));
            }
        }

        if (isset($array['Allegati'])) {
            if (isset($array['Allegati'][0])) {
                foreach ($array['Allegati'] as $allegato) {
                    $fatturaElettronicaBody->addAllegati(Allegati::fromArray($allegato));
                }
            } else {
                $fatturaElettronicaBody->addAllegati(Allegati::fromArray($array['Allegati']));
            }
        }

        return $fatturaElettronicaBody;
    }
}