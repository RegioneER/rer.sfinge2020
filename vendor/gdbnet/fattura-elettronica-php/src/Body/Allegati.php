<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class Allegati implements FatturaElettronicaInterface
{
    /**
     * @var string|null
     */
    protected $NomeAttachment;

    /**
     * @var string|null
     */
    protected $AlgoritmoCompressione;

    /**
     * @var string|null
     */
    protected $FormatoAttachment;

    /**
     * @var string|null
     */
    protected $DescrizioneAttachment;

    /**
     * @var string|null
     */
    protected $Attachment;

    /**
     * Allegati constructor.
     * @param string $NomeAttachment
     * @param string $Attachment
     */
    public function __construct(string $NomeAttachment, string $Attachment)
    {
        $this->setNomeAttachment($NomeAttachment);
        $this->setAttachment($Attachment);
    }

    /**
     * @return null|string
     */
    public function getNomeAttachment(): ?string
    {
        return $this->NomeAttachment;
    }

    /**
     * @param null|string $NomeAttachment
     * @return Allegati
     */
    public function setNomeAttachment(string $NomeAttachment): self
    {
        $this->NomeAttachment = $NomeAttachment;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAlgoritmoCompressione(): ?string
    {
        return $this->AlgoritmoCompressione;
    }

    /**
     * @param null|string $AlgoritmoCompressione
     * @return Allegati
     */
    public function setAlgoritmoCompressione(?string $AlgoritmoCompressione): self
    {
        $this->AlgoritmoCompressione = $AlgoritmoCompressione;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFormatoAttachment(): ?string
    {
        return $this->FormatoAttachment;
    }

    /**
     * @param null|string $FormatoAttachment
     * @return Allegati
     */
    public function setFormatoAttachment(?string $FormatoAttachment): self
    {
        $this->FormatoAttachment = $FormatoAttachment;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescrizioneAttachment(): ?string
    {
        return $this->DescrizioneAttachment;
    }

    /**
     * @param null|string $DescrizioneAttachment
     * @return Allegati
     */
    public function setDescrizioneAttachment(?string $DescrizioneAttachment): self
    {
        $this->DescrizioneAttachment = $DescrizioneAttachment;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAttachment(): ?string
    {
        return $this->Attachment;
    }

    /**
     * @param null|string $Attachment
     * @return Allegati
     */
    public function setAttachment(string $Attachment): self
    {
        $this->Attachment = $Attachment;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'NomeAttachment' => $this->getNomeAttachment(),
            'AlgoritmoCompressione' => $this->getAlgoritmoCompressione(),
            'FormatoAttachment' => $this->getFormatoAttachment(),
            'DescrizioneAttachment' => $this->getDescrizioneAttachment(),
            'Attachment' => $this->getAttachment(),
        ];
    }

    /**
     * @param $array
     * @return Allegati
     */
    public static function fromArray(array $array): self
    {
        $allegato = new self($array['NomeAttachment'], $array['Attachment']);

        if (isset($array['AlgoritmoCompressione']) && !empty(trim($array['AlgoritmoCompressione']))) {
            $allegato->setAlgoritmoCompressione($array['AlgoritmoCompressione']);
        }
        if (isset($array['FormatoAttachment']) && !empty(trim($array['FormatoAttachment']))) {
            $allegato->setFormatoAttachment($array['FormatoAttachment']);
        }
        if (isset($array['DescrizioneAttachment']) && !empty(trim($array['DescrizioneAttachment']))) {
            $allegato->setDescrizioneAttachment($array['DescrizioneAttachment']);
        }

        return $allegato;
    }
}