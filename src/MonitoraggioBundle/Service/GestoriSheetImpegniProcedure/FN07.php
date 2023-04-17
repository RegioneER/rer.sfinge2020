<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;
use AttuazioneControlloBundle\Repository\RichiestaPagamentoRepository;
use BaseBundle\Exception\SfingeException;
use MonitoraggioBundle\Repository\TC36LivelloGerarchicoRepository;
use MonitoraggioBundle\Repository\TC4ProgrammaRepository;
use AttuazioneControlloBundle\Repository\RichiestaLivelloGerarchicoRepository;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use AttuazioneControlloBundle\Entity\PagamentoAmmesso;
use MonitoraggioBundle\Repository\TC39CausalePagamentoRepository;
use AttuazioneControlloBundle\Repository\PagamentoAmmessoRepository;

class FN07 extends GestoreSheetImpegniProcedureBase {
    const ROW_START = 5;
    const COLUMN_START = 'D';
    const LUNGHEZZA_RIGA = 12;

    public function elabora(): array {
        $res = [];

        /** @var TC36LivelloGerarchicoRepository $livelliGerarchiciRepository */
        $livelliGerarchiciRepository = $this->em->getRepository('MonitoraggioBundle:TC36LivelloGerarchico');
        /** @var TC39CausalePagamentoRepository $causaleRepository */
        $causaleRepository = $this->em->getRepository('MonitoraggioBundle:TC39CausalePagamento');
        /** @var ImpegniAmmessiRepository $impegniAmmessiRepository */
        $impegniAmmessiRepository = $this->em->getRepository('AttuazioneControlloBundle:ImpegniAmmessi');
        /** @var RichiestaLivelloGerarchicoRepository $richiestaLivelliGerarchiciRepository */
        $richiestaLivelliGerarchiciRepository = $this->em->getRepository('AttuazioneControlloBundle:RichiestaLivelloGerarchico');
        /** @var EntityRepository $richiestaProgrammaRepository */
        $richiestaProgrammaRepository = $this->em->getRepository('AttuazioneControlloBundle:RichiestaProgramma');
        /** @var TC4ProgrammaRepository $programmaRepository */
		$programmaRepository = $this->em->getRepository('MonitoraggioBundle:TC4Programma');
		/** @var RichiestaPagamentoRepository $impegniRepository */
        $pagamentoRepository = $this->em->getRepository('AttuazioneControlloBundle:RichiestaPagamento');
        /** @var PagamentoAmmessoRepository $pagamentoAmmessoRepository */
        $pagamentoAmmessoRepository =   $this->em->getRepository('AttuazioneControlloBundle:PagamentoAmmesso');

        $rowIterator = $this->sheet->getRowIterator(self::ROW_START);
        /** @var \PhpOffice\PhpSpreadsheet\Worksheet\Row $row */
        foreach ($rowIterator as $row) {
            $valoriRiga = $this->getValoriRiga($row, self::COLUMN_START, self::LUNGHEZZA_RIGA);
            if (\count($valoriRiga) < self::LUNGHEZZA_RIGA) {
                throw new SfingeException('La riga ' . $row->getRowIndex() . ' non contiene tutte le informazioni richieste');
            }
            list(
                    $codiceProgetto,
                    $codice_pagamento,
                    $tipologia_pagamento,
                    $data_pagamento,
                    $codProgramma,
                    $codiceLivelloGerarchico,
                    $data_ammesso,
                    $tipologia_ammesso,
                    $causale_ammesso,
                    $importo_ammesso,
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
            $dataPagamento = $this->getData($data_pagamento);
            if(\is_null($data_ammesso)){
                throw new SfingeException("Data pagamento per il pagamento $codice_pagamento per il progetto $codiceProgetto non valida");
            }
            $codice_pagamento = \substr($codice_pagamento,0,20);

			
			$pagamento = $pagamentoRepository->findOneBy([
				'richiesta' => $richiesta,
                'codice' => $codice_pagamento,
                'data_pagamento' => $dataPagamento,
                'tipologia_pagamento' => $tipologia_pagamento,
			]);
			if(\is_null($pagamento)){
				throw new SfingeException("Pagamento $codice_pagamento per il progetto $codiceProgetto non presente a sistema");
			}

			$programma = $programmaRepository->findOneBy(['cod_programma' => $codProgramma]);
            if (\is_null($programma)) {
                throw  new SfingeException("Livello gerarchico '$codProgramma' non presente a sistema");
			}
			$richiestaProgramma = $richiestaProgrammaRepository->findOneBy([
                'tc4_programma' => $programma,
                'richiesta' => $richiesta,
            ]);
            if (\is_null($richiestaProgramma)) {
                $richiestaProgramma = new RichiestaProgramma($richiesta, RichiestaProgramma::STATO_ATTIVO, $programma);
                $res[] = $richiestaProgramma;
            }

			$livelloGerarchico = $livelliGerarchiciRepository->findOneBy([
				'cod_liv_gerarchico' => $codiceLivelloGerarchico,
			]);
			if (\is_null($livelloGerarchico)) {
				throw  new SfingeException("Livello gerarchico '$codiceLivelloGerarchico' non presente a sistema");
			}

			$causale = $causaleRepository->findOneBy([
				'causale_pagamento' => $causale_ammesso
			]);
			if (\is_null($causale)) {
				throw  new SfingeException("Causale pagamento '$causale_ammesso' non presente a sistema");
            }
            
            $data_ammesso = $this->getData($data_ammesso);
            if(\is_null($data_ammesso)){
                throw new SfingeException("Data ammessa per il pagamento $codice_pagamento per il progetto $codiceProgetto non valida");
            }

			$richiestaLivelloGerarchico = $richiestaLivelliGerarchiciRepository->findOneBy([
                'richiesta_programma' => $richiestaProgramma,
                'tc36_livello_gerarchico' => $livelloGerarchico,
            ]);

            if (\is_null($richiestaLivelloGerarchico)) {
                $richiestaLivelloGerarchico = new RichiestaLivelloGerarchico($programma, $livelloGerarchico);
                $res[] = $richiestaLivelloGerarchico;
            }
            $pagamentoAmmesso = $pagamentoAmmessoRepository->findOneBy([
                'richiesta_pagamento' => $pagamento,
                'livello_gerarchico' => $richiestaLivelloGerarchico,
                'data_pagamento' => $data_ammesso,
                'tipologia_pagamento' => $tipologia_ammesso,
            ]);

            if(\is_null($pagamentoAmmesso)){
                $pagamentoAmmesso = new PagamentoAmmesso($pagamento, $richiestaLivelloGerarchico);
                $pagamentoAmmesso->setTipologiaPagamento($tipologia_ammesso);
                $pagamentoAmmesso->setDataPagamento($data_ammesso);
            }

			$pagamentoAmmesso->setImporto($this->getImporto($importo_ammesso));
			$pagamentoAmmesso->setCausale($causale);
			$pagamentoAmmesso->setNote($note);

			$res[] = $pagamentoAmmesso;
		}
		
        return $res;
    }
}
