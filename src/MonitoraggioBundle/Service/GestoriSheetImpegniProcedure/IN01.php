<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;
use MonitoraggioBundle\Repository\TC44_45IndicatoriOutputRepository;
use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Repository\IndicatoreOutputRepository;
use RichiesteBundle\Entity\IndicatoreOutput;

class IN01 extends GestoreSheetImpegniProcedureBase {
    const ROW_START = 5;
    const COLUMN_START = 'D';

	public function elabora(): array {
        $res = [];

		$rowIterator = $this->sheet->getRowIterator(self::ROW_START);
		/** @var TC44_45IndicatoriOutputRepository $tc44Repository */
		$tc44Repository = $this->em->getRepository('MonitoraggioBundle:TC44_45IndicatoriOutput');
		/** @var IndicatoreOutputRepository $indicatoreRepository */
		$indicatoreRepository = $this->em->getRepository('RichiesteBundle:IndicatoreOutput');
		/** @var \PhpOffice\PhpSpreadsheet\Worksheet\Row $row */
		foreach ($rowIterator as $row) {
			$valoriRiga = $this->getValoriRiga($row, self::COLUMN_START, 6);
			list(
				$codiceProgetto, 
				$codTipoIndicatore,
				$codIndicatore,
				$programmato, 
				$realizzato, 
				$cancellato
			) = $valoriRiga;

			if('S' == $cancellato || \is_null($codiceProgetto)){
				continue;
			}
			
			$richiesta = $this->getRichiesta($codiceProgetto);
			if (\is_null($richiesta)) {
				throw new SfingeException("Codice '$codiceProgetto' progetto non presente a sistema");
			}

			$tc = $tc44Repository->findOneBy([
				'cod_indicatore' => $codIndicatore
			]);
			if(\is_null($tc)){
				throw new SfingeException('Indicatore $codIndicatore non trovato alla riga '.$row->getRowIndex());
			}
			$indicatore = $indicatoreRepository->findOneBy([
				'richiesta' => $richiesta,
				'indicatore' => $tc,
			]) ?: new IndicatoreOutput($richiesta, $tc);

			$indicatore->setValProgrammato($this->getImporto($programmato))
				->setValoreMonitoraggio($this->getImporto($realizzato));

			$res[] = $indicatore;
		}

		return $res;
	}
}