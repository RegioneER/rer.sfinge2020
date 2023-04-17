<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use MonitoraggioBundle\Repository\TC4ProgrammaRepository;
use Doctrine\ORM\EntityRepository;
use AttuazioneControlloBundle\Entity\RichiestaProgrammaClassificazione;
use BaseBundle\Exception\SfingeException;
use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use MonitoraggioBundle\Repository\TC12ClassificazioneRepository;
use AttuazioneControlloBundle\Repository\RichiestaProgrammaClassificazioneRepository;
use MonitoraggioBundle\Entity\TC11TipoClassificazione;
use MonitoraggioBundle\Entity\TC12Classificazione;
use MonitoraggioBundle\Entity\TC4Programma;

class AP03 extends GestoreSheetImpegniProcedureBase {
    const ROW_START = 5;
    const COLUMN_START = 'D';

    public function elabora(): array {
        $res = [];

        $rowIterator = $this->sheet->getRowIterator(self::ROW_START);

        /** @var TC4ProgrammaRepository $programmaRepository */
        $programmaRepository = $this->em->getRepository(TC4Programma::class);
        /** @var EntityRepository $richiestaProgrammaRepository */
        $richiestaProgrammaRepository = $this->em->getRepository(RichiestaProgramma::class);
        /** @var TC12ClassificazioneRepository $richiestaProgrammaRepository */
        $TC12Repository = $this->em->getRepository(TC12Classificazione::class);
        /** @var EntityRepository $richiestaProgrammaRepository */
        $TC11Repository = $this->em->getRepository(TC11TipoClassificazione::class);
        /** @var RichiestaProgrammaClassificazioneRepository $richiestaProgrammaRepository */
        $richiestaProgrammaClassificazioneRepository = $this->em->getRepository(RichiestaProgrammaClassificazione::class);

        /** @var \PhpOffice\PhpSpreadsheet\Worksheet\Row $row */
        foreach ($rowIterator as $row) {
            $valoriRiga = $this->getValoriRiga($row, self::COLUMN_START, 5);
            list($codiceProgetto, $codiceProgramma, $tipoClassificazione, $codiceClassificazione, $cancellato) = $valoriRiga;
            if ('S' == $cancellato || \is_null($codiceProgetto)) {
                continue;
            }
            $richiesta = $this->getRichiesta($codiceProgetto);
            if (\is_null($richiesta)) {
                throw new SfingeException("Progetto non presente a sistema: $codiceProgetto");
            }

            $programma = $programmaRepository->findOneBy([
                'cod_programma' => $codiceProgramma,
            ]);
            if (\is_null($programma)) {
                throw new SfingeException('La riga ' . $row->getRowIndex() . ' non contiene un codice programma valido');
            }

            $richiestaProgramma = $richiestaProgrammaRepository->findOneBy([
                'richiesta' => $richiesta,
                'tc4_programma' => $programma,
            ]);
            if (\is_null($richiestaProgramma)) {
                $richiestaProgramma = new RichiestaProgramma($richiesta, (string) RichiestaProgramma::STATO_ATTIVO, $programma);
                $res[] = $richiestaProgramma;
            }

            $TC11 = $TC11Repository->findOneBy([
                'tipo_class' => $tipoClassificazione,
            ]);

            $TC12 = $TC12Repository->findByProgrammaCodiceTipo($programma, $codiceClassificazione, $TC11);

            /** @var RichiestaProgrammaClassificazione $richiestaProgrammaClassificazione */
            $richiestaProgrammaClassificazione = $richiestaProgrammaClassificazioneRepository->findOneBy([
                'richiesta_programma' => $richiestaProgramma,
                'classificazione' => $TC12,
            ]);

            if (!\is_null($richiestaProgrammaClassificazione)) {
                $this->addWarning("Classificazione in riga " . $row->getRowIndex() . " già presente");
                continue;
            } else {
                $richiestaProgrammaClassificazione = new RichiestaProgrammaClassificazione($richiestaProgramma, $TC12);
                $richiestaProgramma->addClassificazioni($richiestaProgrammaClassificazione);
                $res[] = $richiestaProgrammaClassificazione;
            }
        }

        return $res;
    }
}
