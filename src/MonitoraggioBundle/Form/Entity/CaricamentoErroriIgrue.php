<?php

namespace MonitoraggioBundle\Form\Entity;

use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;

class CaricamentoErroriIgrue
{
    /**
     * @var DocumentoFile
     */
    protected $file;

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    public function __construct(TipologiaDocumento $tipologia = null)
    {
        $this->file = new DocumentoFile();
        $this->file->setTipologiaDocumento($tipologia);
    }
}
