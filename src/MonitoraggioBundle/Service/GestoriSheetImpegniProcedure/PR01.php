<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;
use RichiesteBundle\Entity\RichiestaRepository;
use BaseBundle\Exception\SfingeException;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Repository\RichiestaImpegniRepository;
use AttuazioneControlloBundle\Entity\RichiestaStatoAttuazioneProgetto;

class PR01 extends GestoreSheetImpegniProcedureBase {
    const ROW_START = 5;
    const COLUMN_START = 'D';

	public function elabora(): array {
        $res = [];

        $rowIterator = $this->sheet->getRowIterator(self::ROW_START);
        /** @var RichiestaRepository $richiestaRepository */
        $richiestaRepository = $this->em->getRepository('RichiesteBundle:Richiesta');
		$statoRepository = $this->em->getRepository('AttuazioneControlloBundle:RichiestaStatoAttuazioneProgetto');
		$TC47StatoProgettoRepository =  $this->em->getRepository('MonitoraggioBundle:TC47StatoProgetto');
        /** @var \PhpOffice\PhpSpreadsheet\Worksheet\Row $row */
        foreach ($rowIterator as $row) {
			$valoriRiga = $this->getValoriRiga($row, self::COLUMN_START, 8);
            list($codiceProgetto, $stato, $dataRif, $cancellato) = $valoriRiga;
            if('S' == $cancellato || \is_null($codiceProgetto)){
                continue;
            }
			$dataRif = $this->getData($dataRif);
			if(\is_null($dataRif)){
                throw  new SfingeException('La riga ' . $row->getRowIndex() . ' non contiene una data di riferimento valida');
            }	
            $richiesta = $this->getRichiesta($codiceProgetto);
            if (\is_null($richiesta)) {
                throw new SfingeException("Codice '$codiceProgetto' progetto non presente a sistema");
			}
			
            $tc47 = $TC47StatoProgettoRepository->findOneBy([
                'stato_progetto' => $stato,
            ]);
            if(\is_null($tc47)){
                throw  new SfingeException("Stato '{$stato->getStatoProgetto()}' pnon presente a sistema");
            }
			
			$stato = $statoRepository->findOneBy([
                'richiesta' => $richiesta,
				'stato_progetto' => $tc47,
			]) ?: new RichiestaStatoAttuazioneProgetto($richiesta, $tc47);
			
			$stato->setDataRiferimento($dataRif);
					
            $res[] = $stato;
        }

        return $res;
	}
	
	
}
