<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use BaseBundle\Exception\SfingeException;
use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;
use MonitoraggioBundle\Entity\TC15StrumentoAttuativo;
use AttuazioneControlloBundle\Entity\StrumentoAttuativo;

class AP05 extends GestoreSheetImpegniProcedureBase {
    const ROW_START = 5;
    const COLUMN_START = 'D';

    public function elabora(): array {
        $res = [];

        $rowIterator = $this->sheet->getRowIterator(self::ROW_START);

        /** @var TC15StrumentoAttuativo $strumentoRepository */
        $strumentoRepository = $this->em->getRepository(TC15StrumentoAttuativo::class);
        
        /** @var StrumentoAttuativo $strumentoAttuativoRepository */
        $strumentoAttuativoRepository = $this->em->getRepository(StrumentoAttuativo::class);

        /** @var \PhpOffice\PhpSpreadsheet\Worksheet\Row $row */
        foreach ($rowIterator as $row) {
            $valoriRiga = $this->getValoriRiga($row, self::COLUMN_START, 5);
            list($codiceProgetto, $codiceStrumento, $cancellato) = $valoriRiga;
            if ('S' == $cancellato || \is_null($codiceProgetto)) {
                continue;
            }
            $richiesta = $this->getRichiesta($codiceProgetto);
            if (\is_null($richiesta)) {
                throw new SfingeException("Progetto non presente a sistema: $codiceProgetto");
            }

            $strumento = $strumentoRepository->findOneBy([
                'cod_stru_att' => $codiceStrumento,
            ]);
            if (\is_null($strumento)) {
                throw new SfingeException('La riga ' . $row->getRowIndex() . ' non contiene un codice strumento valido');
            }

            $strumentoAttautivo = $strumentoAttuativoRepository->findOneBy([
                'richiesta' => $richiesta,
                'tc15_strumento_attuativo' => $strumento,
            ]);
            if (\is_null($strumentoAttautivo)) {
                $strumentoAttautivo = new StrumentoAttuativo($richiesta, $strumento);
                $res[] = $strumentoAttautivo;
            }
        }

        return $res;
    }
}
