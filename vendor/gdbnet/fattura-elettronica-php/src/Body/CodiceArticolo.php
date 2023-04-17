<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class CodiceArticolo implements FatturaElettronicaInterface
{
    /**
     * @var string
     */
    protected $CodiceTipo = null;

    /**
     * @var string
     */
    protected $CodiceValore = null;

    /**
     * CodiceArticolo constructor.
     * @param string $CodiceTipo
     * @param string $CodiceValore
     */
    public function __construct(string $CodiceTipo, string $CodiceValore)
    {
        $this->setCodiceTipo($CodiceTipo);
        $this->setCodiceValore($CodiceValore);
    }

    /**
     * @return null|string
     */
    public function getCodiceTipo(): ?string
    {
        return $this->CodiceTipo;
    }

    /**
     * @param string $CodiceTipo
     * @return CodiceArticolo
     */
    public function setCodiceTipo(string $CodiceTipo): self
    {
        $this->CodiceTipo = $CodiceTipo;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCodiceValore(): ?string
    {
        return $this->CodiceValore;
    }

    /**
     * @param string $CodiceValore
     * @return CodiceArticolo
     */
    public function setCodiceValore(string $CodiceValore): self
    {
        $this->CodiceValore = $CodiceValore;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'CodiceTipo' => $this->getCodiceTipo(),
            'CodiceValore' => $this->getCodiceValore(),
        ];
    }

    /**
     * @param array $array
     * @return CodiceArticolo
     */
    public static function fromArray(array $array): self
    {
        $codiceArticolo = new CodiceArticolo($array['CodiceTipo'], $array['CodiceValore']);

        return $codiceArticolo;
    }
}
