<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;
use RichiesteBundle\Entity\RichiestaRepository;
use BaseBundle\Exception\SfingeException;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Repository\RichiestaImpegniRepository;
use AttuazioneControlloBundle\Entity\IterProgetto;

class AP00 extends GestoreSheetImpegniProcedureBase {

	const ROW_START = 5;
	const COLUMN_START = 'D';

	public function elabora(): array {
		$res = [];

		$rowIterator = $this->sheet->getRowIterator(self::ROW_START);
		$TC5TipoOperazioneRepository = $this->em->getRepository('MonitoraggioBundle\Entity\TC5TipoOperazione');
		$TC6TipoAiutoRepository = $this->em->getRepository('MonitoraggioBundle\Entity\TC6TipoAiuto');
		$TC48TipoProceduraAttivazioneOriginariaRepository = $this->em->getRepository('MonitoraggioBundle\Entity\TC48TipoProceduraAttivazioneOriginaria');
		/** @var \PhpOffice\PhpSpreadsheet\Worksheet\Row $row */
		foreach ($rowIterator as $row) {
			$valoriRiga = $this->getValoriRiga($row, self::COLUMN_START, 12);
			list(
				$codiceProgetto,
				$titolo, 
				$sintesi, 
				$tipoOp, 
				$cup, 
				$tipoAiuto, 
				$dataInizio, 
				$dataFinePre, 
				$dataFineEff, 
				$tipoProc, 
				$codiceProc, 
				$cancellato) = $valoriRiga;
			if (\is_null($codiceProgetto)) {
				continue;
			}
			
			$dataInizio = $this->getData($dataInizio);
			$dataFinePre = $this->getData($dataFinePre);
			$dataFineEff = $this->getData($dataFineEff);

			$richiesta = $this->getRichiesta($codiceProgetto);
			if (\is_null($richiesta)) {
				throw new SfingeException("Codice '$codiceProgetto' progetto non presente a sistema");
			}

			$TC5 = $TC5TipoOperazioneRepository->findOneBy([
				'tipo_operazione' => $tipoOp,
			]);

			$TC6 = $TC6TipoAiutoRepository->findOneBy([
				'tipo_aiuto' => $tipoAiuto,
			]);

			$TC48 = $TC48TipoProceduraAttivazioneOriginariaRepository->findOneBy([
				'tip_proc_att_orig' => $tipoProc,
			]);

			$richiesta->setMonTipoOperazione($TC5);
			$richiesta->setMonTipoAiuto($TC6);
			$richiesta->setMonTipoProceduraAttOrig($TC48);
			if(!$richiesta->getTitolo() && $titolo){
				$richiesta->setTitolo($titolo);
			}
			if(!$richiesta->getAbstract() && $sintesi){
				$richiesta->setAbstract($sintesi);
			}
			// $richiesta->setDataInizioProgetto($dataInizio);
			// $richiesta->setDataFineProgetto($dataFinePre);
			$atc = $richiesta->getAttuazioneControllo();
			if(\is_null($atc)){	
				$this->addWarning("Progetto '$codiceProgetto' in riga ".$row->getRowIndex()." non presente in fase Attuazione e Controllo");

				continue;
			}
			$atc->setDataAvvio($dataInizio);
			$atc->setDataTermine($dataFinePre);
			$atc->setDataTermineEffettivo($dataFineEff);
			$istruttoria = $richiesta->getIstruttoria();
			if($istruttoria && ! $istruttoria->getCodiceCup() && $cup){
				$istruttoria->setCodiceCup($cup);
				$res[] = $istruttoria;
		}

			$res[] = $atc;
			$res[] = $richiesta;
		}

		return $res;
	}

}
