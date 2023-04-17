<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaException;
use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class DatiPagamento implements FatturaElettronicaInterface
{
    /**
     * @var string
     */
    protected $CondizioniPagamento;

    /**
     * @var DettaglioPagamento[]|null
     */
    protected $dettaglioPagamento = [];

    /**
     * DatiPagamento constructor.
     * @param string $CondizioniPagamento
     * @throws FatturaElettronicaException
     */
    public function __construct(string $CondizioniPagamento)
    {
        $this->setCondizioniPagamento($CondizioniPagamento);
    }

    /**
     * @return DettaglioPagamento[] | null
     */
    public function getDettaglioPagamento(): ?array
    {
        return $this->dettaglioPagamento;
    }

    /**
     * @param DettaglioPagamento[] $dettaglioPagamento
     * @return DatiPagamento
     */
    public function setDettaglioPagamento(array $dettaglioPagamento): self
    {
        $this->dettaglioPagamento = $dettaglioPagamento;

        return $this;
    }

    /**
     * @param DettaglioPagamento $dettaglioPagamento
     * @return DatiPagamento
     */
    public function addDettaglioPagamento(DettaglioPagamento $dettaglioPagamento): self
    {
        $this->dettaglioPagamento[] = $dettaglioPagamento;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCondizioniPagamento()
    {
        return $this->CondizioniPagamento;
    }

    /**
     * @param $CondizioniPagamento
     * @return DatiPagamento
     * @throws FatturaElettronicaException
     */
    public function setCondizioniPagamento($CondizioniPagamento): DatiPagamento
    {
        switch ($CondizioniPagamento) {
            case'TP01':
            case'TP02':
            case'TP03':
                $this->CondizioniPagamento = $CondizioniPagamento;
                break;
            default:
                throw new FatturaElettronicaException("Invalid value for 'CondizioniPagamento', valid value are TP01,TP02,TP03, you provide '" . $CondizioniPagamento . "'");
                break;

        }
        return $this;
    }

    /**
     * @return array
     * @throws FatturaElettronicaException
     */
    public function toArray()
    {
        $array = [
            'CondizioniPagamento' => $this->getCondizioniPagamento(),
        ];

        if (count($this->getDettaglioPagamento()) == 0) {
            throw new FatturaElettronicaException("DatiPagamento :: missed istance of DettaglioPagamento");
        } else if (count($this->getDettaglioPagamento()) > 1) {
            foreach ($this->getDettaglioPagamento() as $dp) {
                $array['DettaglioPagamento'][] = $dp->toArray();
            }
        } else {
            $array['DettaglioPagamento'] = $this->getDettaglioPagamento()[0]->toArray();
        }

        return $array;
    }

    /**
     * @param $array
     * @return DatiPagamento
     * @throws FatturaElettronicaException
     */
    public static function fromArray(array $array): self
    {
        $datiPagamento = new self($array['CondizioniPagamento']);

        if (isset($array['DettaglioPagamento'])) {
            if (isset($array['DettaglioPagamento'][0])) {
                foreach ($array['DettaglioPagamento'] as $item) {
                    $datiPagamento->addDettaglioPagamento(DettaglioPagamento::fromArray($item));
                }
            } else {
                $datiPagamento->addDettaglioPagamento(DettaglioPagamento::fromArray($array['DettaglioPagamento']));
            }
        }

        return $datiPagamento;
    }
}