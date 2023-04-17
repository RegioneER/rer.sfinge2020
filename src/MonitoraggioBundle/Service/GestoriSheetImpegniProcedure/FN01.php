<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use MonitoraggioBundle\Repository\TC4ProgrammaRepository;
use Doctrine\ORM\EntityRepository;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use AttuazioneControlloBundle\Repository\RichiestaLivelloGerarchicoRepository;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use BaseBundle\Exception\SfingeException;
use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;

class FN01 extends GestoreSheetImpegniProcedureBase {
    const ROW_START = 5;
    const COLUMN_START = 'D';

    public function elabora(): array {
        $res = [];

        $rowIterator = $this->sheet->getRowIterator(self::ROW_START);
        /** @var RichiestaLivelloGerarchicoRepository $richiestaLivGerarchico */
        $richiestaLivGerarchico = $this->em->getRepository('AttuazioneControlloBundle:RichiestaLivelloGerarchico');

        /** @var TC4ProgrammaRepository $programmaRepository */
        $programmaRepository = $this->em->getRepository('MonitoraggioBundle:TC4Programma');
        /** @var TC36LivelloGerarchicoRepository $livelliGerarchiciRepository */
        $livelliGerarchiciRepository = $this->em->getRepository('MonitoraggioBundle:TC36LivelloGerarchico');
        /** @var EntityRepository $richiestaProgrammaRepository */
        $richiestaProgrammaRepository = $this->em->getRepository('AttuazioneControlloBundle:RichiestaProgramma');

        /** @var \PhpOffice\PhpSpreadsheet\Worksheet\Row $row */
        foreach ($rowIterator as $row) {
            $valoriRiga = $this->getValoriRiga($row, self::COLUMN_START, 5);
            list($codiceProgetto, $codiceProgramma, $codLivelloGerarchico, $importo, $cancellato) = $valoriRiga;
            if ('S' == $cancellato || \is_null($codiceProgetto)) {
                continue;
            }
            $richiesta = $this->getRichiesta($codiceProgetto);
            $importo = $this->getImporto($importo);
            $programma = $programmaRepository->findOneBy([
                'cod_programma' => $codiceProgramma,
            ]);
            if (\is_null($programma)) {
                throw new SfingeException('La riga ' . $row->getRowIndex() . ' non contiene un codice programma valido');
            }
            $livelloGerarchico = $livelliGerarchiciRepository->findOneBy([
                'cod_liv_gerarchico' => $codLivelloGerarchico,
            ]);

            if (\is_null($livelloGerarchico)) {
                throw new SfingeException('La riga ' . $row->getRowIndex() . ' non contiene un codice livello gerarchico valido');
            }
            $richiestaProgramma = $richiestaProgrammaRepository->findOneBy([
                'richiesta' => $richiesta,
                'tc4_programma' => $programma,
            ]);

            if (\is_null($richiestaProgramma)) {
                $richiestaProgramma = new RichiestaProgramma($richiesta, null, $programma);
                $res[] = $richiestaProgramma;
            }

            $richiestaLivello = $richiestaLivGerarchico->findOneBy([
                'richiesta_programma' => $richiestaProgramma,
                'tc36_livello_gerarchico' => $livelloGerarchico,
            ]) ?: new RichiestaLivelloGerarchico($richiestaProgramma, $livelloGerarchico);
            $richiestaLivello->setImportoCostoAmmesso($importo);

            $res[] = $richiestaLivello;
        }

        return $res;
    }
}
