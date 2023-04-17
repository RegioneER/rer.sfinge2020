<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;
use MonitoraggioBundle\Repository\TC7ProgettoComplessoRepository;
use MonitoraggioBundle\Repository\TC8GrandeProgettoRepository;
use MonitoraggioBundle\Repository\TC9TipoLivelloIstituzioneRepository;
use MonitoraggioBundle\Repository\TC10TipoLocalizzazioneRepository;
use MonitoraggioBundle\Repository\TC13GruppoVulnerabileProgettoRepository;
use BaseBundle\Exception\SfingeException;

class AP02 extends GestoreSheetImpegniProcedureBase {
    const ROW_START = 5;
    const COLUMN_START = 'D';

    public function elabora(): array {
        $res = [];

        /** @var TC7ProgettoComplessoRepository $progComplessoRepository */
        $progComplessoRepository = $this->em->getRepository('MonitoraggioBundle:TC7ProgettoComplesso');
        /** @var TC8GrandeProgettoRepository $grandeProgettoRepository */
        $grandeProgettoRepository = $this->em->getRepository('MonitoraggioBundle:TC8GrandeProgetto');
        /** @var TC9TipoLivelloIstituzioneRepository $livIstituzioneRepository */
        $livIstituzioneRepository = $this->em->getRepository('MonitoraggioBundle:TC9TipoLivelloIstituzione');
        /** @var TC10TipoLocalizzazioneRepository $localizzazioneRepository */
        $localizzazioneRepository = $this->em->getRepository('MonitoraggioBundle:TC10TipoLocalizzazione');
        /** @var TC13GruppoVulnerabileProgettoRepository $vulnRepository */
        $vulnRepository = $this->em->getRepository('MonitoraggioBundle:TC13GruppoVulnerabileProgetto');

        $rowIterator = $this->sheet->getRowIterator(self::ROW_START);
        /** @var \PhpOffice\PhpSpreadsheet\Worksheet\Row $row */
        foreach ($rowIterator as $row) {
            $valoriRiga = $this->getValoriRiga($row, self::COLUMN_START, 9);
            list(
                $codiceProgetto,
                $codiceProgettoComplesso,
                $codiceGrandeProgetto,
                $codGeneratoreEntrate,
                $codLivIstStrFin,
                $codFondoFondi,
                $codTipoLocalizzazione,
                $codVulnerabili,
                $cancellato
            ) = $valoriRiga;
			
			if(is_null($codiceProgetto) || $codiceProgetto == '') {
				break;
			}

            $richiesta = $this->getRichiesta($codiceProgetto);

            $progettoComplesso = $progComplessoRepository->findOneBy(['cod_prg_complesso' => $codiceProgettoComplesso]);
           
            $grandeProgetto = $grandeProgettoRepository->findOneBy([
                'grande_progetto' => $codiceGrandeProgetto,
            ]);
            

            $generatore = 'S' == $codGeneratoreEntrate;

            $liv = $livIstituzioneRepository->findOneBy([
                'liv_istituzione_str_fin' => $codLivIstStrFin,
            ]);

            $fondoDiFondi = 'S' == $codFondoFondi;
            $tipoLocalizzazione = $localizzazioneRepository->findOneBy([
                'tipo_localizzazione' => $codTipoLocalizzazione,
            ]);
            if (\is_null($tipoLocalizzazione)) {
                throw new SfingeException('La riga ' . $row->getRowIndex() . ' non contiene un tipo localizzazione valido');
            }

            $vulnerabile = $vulnRepository->findOneBy([
                'cod_vulnerabili' => $codVulnerabili,
            ]);
            if (\is_null($vulnerabile)) {
                throw new SfingeException('La riga ' . $row->getRowIndex() . ' non contiene un tipo localizzazione valido');
            }

            $richiesta->setMonProgettoComplesso($progettoComplesso);
            $richiesta->setMonGrandeProgetto($grandeProgetto);
            $richiesta->setMonGeneratoreEntrate($generatore);
            $richiesta->setMonFondoDiFondi($fondoDiFondi);
            $richiesta->setMonLivIstituzioneStrFin($liv);
            $richiesta->setMonTipoLocalizzazione($tipoLocalizzazione);
            $richiesta->setMonGruppoVulnerabile($vulnerabile);

            $res[] = $richiesta;
        }

        return $res;
    }
}
