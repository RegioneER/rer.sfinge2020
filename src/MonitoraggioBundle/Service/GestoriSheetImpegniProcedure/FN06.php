<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;
use AttuazioneControlloBundle\Repository\RichiestaPagamentoRepository;
use RichiesteBundle\Entity\RichiestaRepository;
use MonitoraggioBundle\Repository\TC39CausalePagamentoRepository;
use BaseBundle\Exception\SfingeException;
use AttuazioneControlloBundle\Entity\RichiestaPagamento;

class FN06 extends GestoreSheetImpegniProcedureBase {
    const ROW_START = 5;
    const COLUMN_START = 'D';
    const LUNGHEZZA_RIGA = 8;

    public function elabora(): array {
		$res = [];
		
        /** @var RichiestaPagamentoRepository $impegniRepository */
		$pagamentoRepository = $this->em->getRepository('AttuazioneControlloBundle:RichiestaPagamento');
		/** @var TC39CausalePagamentoRepository $causaleDisimpegnoRepository */
		$causaleRepository = $this->em->getRepository('MonitoraggioBundle:TC39CausalePagamento');

        $rowIterator = $this->sheet->getRowIterator(self::ROW_START);
        /** @var \PhpOffice\PhpSpreadsheet\Worksheet\Row $row */
        foreach ($rowIterator as $row) {
            $valoriRiga = $this->getValoriRiga($row, self::COLUMN_START, self::LUNGHEZZA_RIGA);
            if (\count($valoriRiga) < self::LUNGHEZZA_RIGA) {
                throw new SfingeException('La riga ' . $row->getRowIndex() . ' non contiene tutte le informazioni richieste');
            }
            list(
                $codiceProgetto,
                $codice,
                $tipologia,
                $data,
                $importo,
                $causale,
				$note,
				$cancellato
			) = $valoriRiga;
			
			if('S' == $cancellato || \is_null($codiceProgetto)){
                continue;
            }

			$richiesta = $this->getRichiesta($codiceProgetto);
			if (\is_null($richiesta)) {
                throw new SfingeException("Codice '$codiceProgetto' progetto non presente a sistema");
			}
            $codice = \substr($codice,0,20);

			$causalePagamento = $causaleRepository->findOneBy(['causale_pagamento' => $causale]);
			if (\is_null($causalePagamento)) {
				continue;
			}

			$data = $this->getData($data);
			if(\is_null($data)){
				throw new SfingeException("Impossbile convertire la data per il progetto '$codiceProgetto': formato non riconosciuto");
			}

			$pagamento = $pagamentoRepository->findOneBy([
				'richiesta' => $richiesta,
				'codice' => $codice,
				'tipologia_pagamento' => $tipologia,
				'data_pagamento' => $data,
			]);
			
			if(\is_null($pagamento)){
				$pagamento = new RichiestaPagamento();
				$pagamento->setRichiesta($richiesta);
				$pagamento->setCodice($codice);
				$pagamento->setDataPagamento($data);
				$pagamento->setTipologiaPagamento($tipologia);
			}
			$pagamento->setImporto($this->getImporto($importo));
			$pagamento->setNote($note);
			$pagamento->setCausalePagamento($causalePagamento);

			$res[] = $pagamento;
		}

		return $res;
    }
}
