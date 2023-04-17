<?php

namespace MonitoraggioBundle\Service;

interface IGestoreSheetImpegniProcedure
{
    public function elabora(): array;

    public function getWarnings(): array;
}