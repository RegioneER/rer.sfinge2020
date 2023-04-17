<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use BaseBundle\Exception\SfingeException;
use AttuazioneControlloBundle\Entity\Economia;

class FN10 extends GestoreSheetImpegniProcedureBase {
    const ROW_START = 5;
    const COLUMN_START = 'D';

    public function elabora(): array {
        $res = [];

        /** @var \MonitoraggioBundle\Repository\TC33FonteFinanziariaRepository $tc33Repository */
        $tc33Repository = $this->em->getRepository(TC33FonteFinanziaria::class);

        /** @var \AttuazioneControlloBundle\Repository\EconomiaRepository $economiaRepository */
        $economiaRepository = $this->em->getRepository(Economia::class);

        $rowIterator = $this->sheet->getRowIterator(self::ROW_START);

        /** @var Row $row */
        foreach ($rowIterator as $row) {
            $valoriRiga = $this->getValoriRiga($row, self::COLUMN_START, 4);
            list(
                $codiceProgetto,
                $cod_fondo,
                $importo,
                $cancellato) = $valoriRiga;
            if (\is_null($codiceProgetto)) {
                continue;
            }

            $richiesta = $this->getRichiesta($codiceProgetto);
            if (\is_null($richiesta)) {
                throw new SfingeException("Codice '$codiceProgetto' progetto non presente a sistema");
            }

            $tc33 = $tc33Repository->findOneBy([
                'cod_fondo' => $cod_fondo,
            ]);
            if (\is_null($tc33)) {
                throw new SfingeException("Codice '$codiceProgetto' fondo finanziario non trovato");
            }

            $economia = $economiaRepository->findOneBy([
                'richiesta' => $richiesta,
                'tc33_fonte_finanziaria' => $tc33,
            ]) ?: new Economia($richiesta, $tc33);

            $importoNumerico = $this->getImporto($importo);
			$economia->setImporto($importoNumerico);
			
			$res[] = $economia;
        }

        return $res;
    }
}
