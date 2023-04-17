<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;
use MonitoraggioBundle\Repository\TC40TipoPercettoreRepository;
use AttuazioneControlloBundle\Repository\RichiestaPagamentoRepository;
use AttuazioneControlloBundle\Entity\PagamentiPercettori;
use AttuazioneControlloBundle\Repository\PagamentiPercettoriRepository;
use BaseBundle\Exception\SfingeException;

class FN08 extends GestoreSheetImpegniProcedureBase {
    const ROW_START = 5;
    const COLUMN_START = 'D';

	public function elabora(): array {
        $res = [];

        $rowIterator = $this->sheet->getRowIterator(self::ROW_START);
       
        /** @var TC40TipoPercettoreRepository $tipoPercettoreRepository */
		$tipoPercettoreRepository = $this->em->getRepository('MonitoraggioBundle:TC40TipoPercettore');
		/** @var RichiestaPagamentoRepository $impegniRepository */
		$pagamentoRepository = $this->em->getRepository('AttuazioneControlloBundle:RichiestaPagamento');

		/** @var RichiestaPagamentoRepository $impegniRepository */
		$pagamentoRepository = $this->em->getRepository('AttuazioneControlloBundle:RichiestaPagamento');

		/** @var PagamentiPercettoriRepository $percettoreRepository */
		$percettoreRepository =  $this->em->getRepository('AttuazioneControlloBundle:PagamentiPercettori');

        /** @var \PhpOffice\PhpSpreadsheet\Worksheet\Row $row */
        foreach ($rowIterator as $row) {
			$valoriRiga = $this->getValoriRiga($row, self::COLUMN_START, 9);
			list(
				$codiceProgetto, 
				$codice, 
				$tipologia, 
				$data, 
				$codiceFiscale, 
				$flagSoggettoPubblico, 
				$codiceTipoPercettore, 
				$importo,
				$cancellato
			) = $valoriRiga;

			if('S' == $cancellato || \is_null($codiceProgetto)){
                continue;
			}
			
			$richiesta = $this->getRichiesta($codiceProgetto);
			if (\is_null($richiesta)) {
                throw new SfingeException("Codice '$codiceProgetto' progetto non presente a sistema");
			}

			$pagamenti = $pagamentoRepository->findBy([
				'richiesta' => $richiesta,
				'codice' => $codice,
			]);
			if(\count($pagamenti) != 1 ){
				throw new SfingeException("Pagamento $codice per progetto $codiceProgetto non univoco a sistema");
			}
			$pagamento = \array_pop($pagamenti);

			$tipoPercettore = $tipoPercettoreRepository->findOneBy([
				'tipo_percettore' => $codiceTipoPercettore,
			]);
			if(\is_null($tipoPercettore)){
				throw new SfingeException("Tipo percettore '$codiceTipoPercettore' non presente a sistema");
			}

			$percettoreEsistente = $percettoreRepository->findBy([
				'pagamento' => $pagamento,
				'codice_fiscale' => $codiceFiscale,
			]);
			if(\count($percettoreEsistente)){
				throw new SfingeException("Percettore '$codiceFiscale' per pagamento $codice del progetto $codiceProgetto già presente a sistema");
			}

			$percettore = new PagamentiPercettori(
				$pagamento, 
				$tipoPercettore, 
				$codiceFiscale, 
				$this->getFlag($flagSoggettoPubblico),
				$this->getImporto($importo)
			);

			$res[] = $percettore;
		}

		return $res;
	}

	protected function getFlag(?string $flag): bool {
		return $flag == 'S';
	}
}