<?php

namespace MonitoraggioBundle\Service;

use MonitoraggioBundle\Entity\MonitoraggioEsportazione;

interface IGestoreEsportazioneIgrueService
{
    public function generaStreamFile(MonitoraggioEsportazione $esportazione);

    public function generaFile(MonitoraggioEsportazione $esportazione);

    public function importaFileRisposta(MonitoraggioEsportazione &$esportazione);
}
