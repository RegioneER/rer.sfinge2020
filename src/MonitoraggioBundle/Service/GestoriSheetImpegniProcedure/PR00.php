<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;
use RichiesteBundle\Entity\RichiestaRepository;
use BaseBundle\Exception\SfingeException;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Repository\RichiestaImpegniRepository;
use AttuazioneControlloBundle\Entity\IterProgetto;

class PR00 extends GestoreSheetImpegniProcedureBase {
    const ROW_START = 5;
	const COLUMN_START = 'D';
	const LEN_CODICE = 4;

	public function elabora(): array {
        $res = [];

        $rowIterator = $this->sheet->getRowIterator(self::ROW_START);
        /** @var RichiestaRepository $richiestaRepository */
        $richiestaRepository = $this->em->getRepository('RichiesteBundle:Richiesta');
		$iterRepository = $this->em->getRepository('AttuazioneControlloBundle:IterProgetto');
		$TC46FaseProceduraleRepository =  $this->em->getRepository('MonitoraggioBundle:TC46FaseProcedurale');
        /** @var \PhpOffice\PhpSpreadsheet\Worksheet\Row $row */
        foreach ($rowIterator as $row) {
			$valoriRiga = $this->getValoriRiga($row, self::COLUMN_START, 8);
            list($codiceProgetto, $codice, $dataInizioPre, $dataInizioEff, $dataFinePre, $dataFineEff, $cancellato) = $valoriRiga;
            if('S' == $cancellato || \is_null($codiceProgetto)){
                continue;
            }
			$dataInizioPre = $this->getData($dataInizioPre);
			$dataInizioEff = $this->getData($dataInizioEff);
			$dataFinePre = $this->getData($dataFinePre);
			$dataFineEff = $this->getData($dataFineEff);
					
            $richiesta = $this->getRichiesta($codiceProgetto);
            if (\is_null($richiesta)) {
                throw new SfingeException("Codice '$codiceProgetto' progetto non presente a sistema");
			}
			$codice = $this->riformattaCodiceFaseProcedurale($codice);
            $tc46 = $TC46FaseProceduraleRepository->findOneBy([
                'cod_fase' => $codice,
			]);
			if(\is_null($tc46)){
				throw new SfingeException('La riga ' . $row->getRowIndex() . ' non contiene una fase procedurale valida');
			}
			
			$iter = $iterRepository->findOneBy([
                'richiesta' => $richiesta,
				'fase_procedurale' => $tc46,
			]);
			
            if (\is_null($iter)) {
				$iter = new IterProgetto();
			}
			
			$iter->setRichiesta($richiesta);
			$iter->setFaseProcedurale($tc46);
			$iter->setDataInizioPrevista($dataInizioPre);
			$iter->setDataInizioEffettiva($dataInizioEff);
			$iter->setDataFinePrevista($dataFinePre);
			$iter->setDataFineEffettiva($dataFineEff);
					
            $res[] = $iter;
        }

        return $res;
	}
	
	protected function riformattaCodiceFaseProcedurale(string $codice) : string {
		$len = strlen($codice);
		if($len >= self::LEN_CODICE ) return $codice;
		
		return str_repeat('0', self::LEN_CODICE - $len) . $codice;
	}
}
