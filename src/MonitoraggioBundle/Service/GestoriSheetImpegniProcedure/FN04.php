<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;
use RichiesteBundle\Entity\RichiestaRepository;
use BaseBundle\Exception\SfingeException;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Repository\RichiestaImpegniRepository;
use MonitoraggioBundle\Repository\TC38CausaleDisimpegnoRepository;

class FN04 extends GestoreSheetImpegniProcedureBase {
    const ROW_START = 5;
    const COLUMN_START = 'D';

	public function elabora(): array {
        $res = [];

        $rowIterator = $this->sheet->getRowIterator(self::ROW_START);
        /** @var RichiestaRepository $richiestaRepository */
        $richiestaRepository = $this->em->getRepository('RichiesteBundle:Richiesta');
        /** @var RichiestaImpegniRepository $impegniRepository */
		$impegniRepository = $this->em->getRepository('AttuazioneControlloBundle:RichiestaImpegni');
		/** @var TC38CausaleDisimpegnoRepository $causaleRepository */
		$causaleRepository = $this->em->getRepository('MonitoraggioBundle:TC38CausaleDisimpegno');
        /** @var \PhpOffice\PhpSpreadsheet\Worksheet\Row $row */
        foreach ($rowIterator as $row) {
			$valoriRiga = $this->getValoriRiga($row, self::COLUMN_START, 8);
            list($codiceProgetto, $codice, $tipologia, $data, $importo, $causale_disimpegno, $note, $cancellato) = $valoriRiga;
            
            $codice = \substr($codice,0,20);

            if('S' == $cancellato || \is_null($codiceProgetto)){
                continue;
            }
			$importo = \abs($this->getImporto($importo));
			$data = $this->getData($data);

            $richiesta = $this->getRichiesta($codiceProgetto);
            if (\is_null($richiesta)) {
                throw new SfingeException("Codice '$codiceProgetto' progetto non presente a sistema");
			}
			
            $impegno = $impegniRepository->findOneBy([
                'richiesta' => $richiesta,
                'codice' => $codice,
                'tipologia_impegno' => $tipologia,
                'data_impegno' => $data
            ]);
            
			if($causale_disimpegno){
				$causale_disimpegno = $causaleRepository->findOneBy([
                    'causale_disimpegno' => $causale_disimpegno,
				]);
			}
			
            if (\is_null($impegno)) {
                $impegno = new RichiestaImpegni($richiesta, $tipologia);
                $impegno->setCodice($codice);
                $impegno->setDataImpegno($data);
			}
			
            $impegno->setImportoImpegno($importo);
			$impegno->setTc38CausaleDisimpegno($causale_disimpegno);
            $res[] = $impegno;
        }

        return $res;
	}
	
	
}
