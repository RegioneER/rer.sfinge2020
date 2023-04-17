<?php

namespace MonitoraggioBundle\Service;

use DocumentoBundle\Entity\DocumentoFile;

interface IGestoreImportazioneMonitoraggio {
    public function importaImpegniPagamentiEnteGestore(DocumentoFile $file): void;
}
