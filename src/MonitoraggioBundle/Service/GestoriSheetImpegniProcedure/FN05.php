<?php

namespace MonitoraggioBundle\Service\GestoriSheetImpegniProcedure;

use MonitoraggioBundle\Repository\TC38CausaleDisimpegnoRepository;
use MonitoraggioBundle\Repository\TC36LivelloGerarchicoRepository;
use AttuazioneControlloBundle\Repository\ImpegniAmmessiRepository;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use AttuazioneControlloBundle\Repository\RichiestaLivelloGerarchicoRepository;
use Doctrine\ORM\EntityRepository;
use MonitoraggioBundle\Repository\TC4ProgrammaRepository;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use MonitoraggioBundle\Service\GestoreSheetImpegniProcedureBase;
use BaseBundle\Exception\SfingeException;

class FN05 extends GestoreSheetImpegniProcedureBase {
    const ROW_START = 5;
    const COLUMN_START = 'D';
    const LUNGHEZZA_RIGA = 12;

    public function elabora(): array {
        $res = [];

        /** @var RichiestaImpegniRepository $impegniRepository */
        $impegniRepository = $this->em->getRepository('AttuazioneControlloBundle:RichiestaImpegni');
        /** @var TC36LivelloGerarchicoRepository $livelliGerarchiciRepository */
        $livelliGerarchiciRepository = $this->em->getRepository('MonitoraggioBundle:TC36LivelloGerarchico');
        /** @var TC38CausaleDisimpegnoRepository $causaleDisimpegnoRepository */
        $causaleDisimpegnoRepository = $this->em->getRepository('MonitoraggioBundle:TC38CausaleDisimpegno');
        /** @var ImpegniAmmessiRepository $impegniAmmessiRepository */
        $impegniAmmessiRepository = $this->em->getRepository('AttuazioneControlloBundle:ImpegniAmmessi');
        /** @var RichiestaLivelloGerarchicoRepository $richiestaLivelliGerarchiciRepository */
        $richiestaLivelliGerarchiciRepository = $this->em->getRepository('AttuazioneControlloBundle:RichiestaLivelloGerarchico');
        /** @var EntityRepository $richiestaProgrammaRepository */
        $richiestaProgrammaRepository = $this->em->getRepository('AttuazioneControlloBundle:RichiestaProgramma');
        /** @var TC4ProgrammaRepository $programmaRepository */
        $programmaRepository = $this->em->getRepository('MonitoraggioBundle:TC4Programma');

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
                $codProgramma,
                $liv_gerarchico,
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
            $dataImpegno = $this->getData($data);
            $dataAmmesso = $this->getData($data_ammesso);
            $richiesta = $this->getRichiesta($codiceProgetto);
            $codice = \substr($codice,0,20);

            if (\is_null($richiesta)) {
                throw new SfingeException("Codice '$codiceProgetto' progetto non presente a sistema");
            }

            $impegno = $impegniRepository->findOneBy([
                'richiesta' => $richiesta,
                'codice' => $codice,
                'data_impegno' => $dataImpegno,
                'tipologia_impegno' => $tipologia,
            ]);

            if (\is_null($impegno)) {
                throw  new SfingeException("Impegno '$codice' per progetto $codiceProgetto non è presente a sistema");
            }

            $livelloGerarchico = $livelliGerarchiciRepository->findOneBy([
                    'cod_liv_gerarchico' => $liv_gerarchico,
            ]);
            if (\is_null($livelloGerarchico)) {
                throw  new SfingeException("Livello gerarchico '$liv_gerarchico' non presente a sistema");
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

            $richiestaLivelloGerarchico = $richiestaLivelliGerarchiciRepository->findOneBy([
                'richiesta_programma' => $richiestaProgramma,
                'tc36_livello_gerarchico' => $livelloGerarchico,
            ]);

            if (\is_null($richiestaLivelloGerarchico)) {
                $richiestaLivelloGerarchico = new RichiestaLivelloGerarchico($richiestaProgramma, $livelloGerarchico);
                $res[] = $richiestaLivelloGerarchico;
            }

            $causale = $causaleDisimpegnoRepository->findOneBy([
                'causale_disimpegno' => $causale_ammesso,
            ]);

            $ammesso = $impegniAmmessiRepository->findOneByImpegno($impegno, $livelloGerarchico);
            if (\is_null($ammesso)) {
                $ammesso = new ImpegniAmmessi($impegno, $richiestaLivelloGerarchico);
            }

            $ammesso->setImportoImpAmm($this->getImporto($importo_ammesso));
            $ammesso->setNoteImp($note);
            $ammesso->setDataImpAmm($dataAmmesso);
            $ammesso->setTipologiaImpAmm($tipologia_ammesso);
            $ammesso->setTc38CausaleDisimpegnoAmm($causale);

            $res[] = $ammesso;
        }
        return $res;
    }
}
