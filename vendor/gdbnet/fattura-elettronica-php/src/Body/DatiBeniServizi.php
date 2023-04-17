<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class DatiBeniServizi implements FatturaElettronicaInterface
{
    /**
     * @var  DettaglioLinea[] | null
     */
    protected $DettaglioLinee;

    /**
     * @var DatiRiepilogo[] | null
     */
    protected $DatiRiepilogo;

    /**
     * @return DettaglioLinea[]|null
     */
    public function getDettaglioLinee(): ?array
    {
        return $this->DettaglioLinee;
    }

    /**
     * @param DettaglioLinea[]|null $DettaglioLinee
     * @return DatiBeniServizi
     */
    public function setDettaglioLinee(?array $DettaglioLinee): self
    {
        $this->DettaglioLinee = $DettaglioLinee;

        return $this;
    }

    /**
     * @param DettaglioLinea $dettaglioLinea
     * @return DatiBeniServizi
     */
    public function addDettaglioLinea(DettaglioLinea $dettaglioLinea): self
    {
        $this->DettaglioLinee[] = $dettaglioLinea;

        return $this;
    }

    /**
     * @return DatiRiepilogo[]|null
     */
    public function getDatiRiepilogo(): ?array
    {
        return $this->DatiRiepilogo;
    }

    /**
     * @param DatiRiepilogo[]|null $DatiRiepilogo
     * @return DatiBeniServizi
     */
    public function setDatiRiepilogo(?array $DatiRiepilogo): self
    {
        $this->DatiRiepilogo = $DatiRiepilogo;

        return $this;
    }

    /**
     * @param DatiRiepilogo $DatiRiepilogo
     * @return DatiBeniServizi
     */
    public function addDatiRiepilogo(DatiRiepilogo $DatiRiepilogo): self
    {
        $this->DatiRiepilogo[] = $DatiRiepilogo;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [
            'DettaglioLinee' => null,
            'DatiRiepilogo' => null,
        ];

        if (!empty($this->getDettaglioLinee())) {
            if (count($this->getDettaglioLinee()) === 1) {
                $array['DettaglioLinee'] = $this->getDettaglioLinee()[0]->toArray();
            } else {
                $a = [];
                foreach ($this->getDettaglioLinee() as $dettaglioLinea) {
                    $a[] = $dettaglioLinea->toArray();
                }
                $array['DettaglioLinee'] = $a;
            }
        }

        if (!empty($this->getDatiRiepilogo())) {
            if (count($this->getDatiRiepilogo()) === 1) {
                $array['DatiRiepilogo'] = $this->getDatiRiepilogo()[0]->toArray();
            } else {
                $a = [];
                foreach ($this->getDatiRiepilogo() as $datiRiepilogo) {
                    $a[] = $datiRiepilogo->toArray();
                }
                $array['DatiRiepilogo'] = $a;
            }
        }

        return $array;
    }

    /**
     * @param array $array
     * @return DatiBeniServizi
     */
    public static function fromArray(array $array): self
    {
        $datiBeniServizi = new self();

        if (isset($array['DettaglioLinee'])) {
            if (isset($array['DettaglioLinee'][0])) {
                foreach ($array['DettaglioLinee'] as $item) {
                    $datiBeniServizi->addDettaglioLinea(DettaglioLinea::fromArray($item));
                }
            } else {
                $datiBeniServizi->addDettaglioLinea(DettaglioLinea::fromArray($array['DettaglioLinee']));
            }
        }

        if (!empty($array['DatiRiepilogo'])) {
            if (isset($array['DatiRiepilogo'][0])) {
                foreach ($array['DatiRiepilogo'] as $item) {
                    $datiBeniServizi->addDatiRiepilogo(DatiRiepilogo::fromArray($item));
                }
            } else {
                $datiBeniServizi->addDatiRiepilogo(DatiRiepilogo::fromArray($array['DatiRiepilogo']));
            }
        }

        return $datiBeniServizi;
    }
}