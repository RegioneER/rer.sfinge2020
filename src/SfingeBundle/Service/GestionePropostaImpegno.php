<?php
namespace SfingeBundle\Service;

use BaseBundle\Service\BaseService;
use DateTime;
use Exception;
use IstruttorieBundle\Entity\PosizioneImpegno;
use IstruttorieBundle\Entity\PropostaImpegno;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use SfingeBundle\Entity\Procedura;
use stdClass;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GestionePropostaImpegno extends BaseService
{
	protected $em;
	protected $container;

	public function __construct(ContainerInterface $container)
    {
		parent::__construct($container);
		$this->em = $this->container->get("doctrine")->getManager();
	}
    public function getEm()
    {
		return $this->em;
	}

    /**
     * @param UploadedFile $propostaImpegnoFile
     * @param Procedura $procedura
     * @return stdClass
     */
    public function importa(UploadedFile $propostaImpegnoFile, Procedura $procedura): stdClass
    {
        $esito = new stdClass();
        $spreadSheetFactory = $this->container->get('phpoffice.spreadsheet');

        try {
            $spreadSheet = $spreadSheetFactory->readUploadedFile($propostaImpegnoFile);
            $sheet = $spreadSheet->getSheet(0);
            $propostaImpegno = new PropostaImpegno();
            $propostaImpegno->setProcedura($procedura);

            $data = $sheet->getCell('D4')->getValue();
            try {
                // Provo a creare un oggetto data nel caso in cui il campo sia settato
                // come numero ma con dentro una data.
                $timestamp = Date::excelToTimestamp($data);
                $bldat = new DateTime();
                $bldat->setTimestamp($timestamp);
            } catch (ContextErrorException $e) {
                // Qui invece provo a creare l'oggetto data se invece il campo è impostato come
                // stringa e la data è in uno di questi due formati:
                // gg.mm.yyyy
                // gg-mm-yyyy
                $bldat = preg_replace("/(\d{2})[.-](\d{2})[.-](\d{4})/", '$3-$2-$1', $data);
                $bldat = new DateTime($bldat);
            }

            if (is_a($bldat, 'DateTime')) {
                $propostaImpegno->setBldat($bldat);
            } else {
                throw new Exception('Data doc nel documento (BLDAT) mancante.');
            }

            $data = $sheet->getCell('D5')->getValue();
            if ($data) {
                $propostaImpegno->setKtext($data);
            }

            $data = $sheet->getCell('D6')->getValue();
            if ($data) {
                $propostaImpegno->setBukrs($data);
            } else {
                throw new Exception('Società (BURKS) mancante, dovrebbe essere valorizzato a RER.');
            }

            $data = $sheet->getCell('D7')->getValue();
            try {
                // Provo a creare un oggetto data nel caso in cui il campo sia settato
                // come numero ma con dentro una data.
                $timestamp = Date::excelToTimestamp($data);
                $budat = new DateTime();
                $budat->setTimestamp($timestamp);
            } catch (ContextErrorException $e) {
                // Qui invece provo a creare l'oggetto data se invece il campo è impostato come
                // stringa e la data è in uno di questi due formati:
                // gg.mm.yyyy
                // gg-mm-yyyy
                $budat = preg_replace("/(\d{2})[.-](\d{2})[.-](\d{4})/", '$3-$2-$1', $data);
                $budat = new DateTime($budat);
            }

            if (is_a($budat, 'DateTime')) {
                $propostaImpegno->setBudat($budat);
            } else {
                throw new Exception('Data di registrazione nel documento (BUDAT) mancante.');
            }

            $data = $sheet->getCell('D8')->getValue();
            if ($data) {
                $propostaImpegno->setZzProtocollo($data);
            }

            $data = $sheet->getCell('D9')->getValue();
            if ($data) {
                $propostaImpegno->setZzNumRipartiz($data);
            }

            $data = $sheet->getCell('D10')->getValue();
            if ($data) {
                $propostaImpegno->setZzTipoDoc($data);
            }

            $data = $sheet->getCell('D11')->getValue();
            if ($data) {
                $propostaImpegno->setZzProgrProg($data);
            }

            $data = $sheet->getCell('D12')->getValue();
            if ($data) {
                $propostaImpegno->setZzContrImp($data);
            }

            $data = $sheet->getCell('D13')->getValue();
            if ($data) {
                $propostaImpegno->setZzAssenzaAtto($data);
            }

            $data = $sheet->getCell('D14')->getValue();
            if ($data) {
                $zzFipos = strtoupper($data);
                if (substr($zzFipos, 0, 1) === 'U') {
                    $propostaImpegno->setZzFipos($zzFipos);
                } else {
                    throw new Exception('Il capitolo deve riportare sempre la "U" iniziale.');
                }
            }

            $data = $sheet->getCell('D15')->getValue();
            if ($data) {
                $propostaImpegno->setZzPrenotazione($data);
            }

            $data = $sheet->getCell('D16')->getValue();
            if ($data) {
                $propostaImpegno->setZzBelnrRif($data);
            }

            $data = $sheet->getCell('D17')->getValue();
            if ($data) {
                $propostaImpegno->setZzProgrRif($data);
            }

            $sheet = $spreadSheet->getSheet(1);

            $lastRow = $sheet->getHighestRow();
            // Le prime quattro righe sono intestazioni
            for ($row = 5; $row <= $lastRow; $row++) {
                $cellB = $sheet->getCell('B' . $row);
                $cellC = $sheet->getCell('C' . $row);
                $cellD = $sheet->getCell('D' . $row);
                $cellE = $sheet->getCell('E' . $row);
                $cellF = $sheet->getCell('F' . $row);
                $cellG = $sheet->getCell('G' . $row);
                $cellH = $sheet->getCell('H' . $row);
                $cellI = $sheet->getCell('I' . $row);
                if (!$cellB->getValue() && !$cellC->getValue() && !$cellD->getValue() && !$cellE->getValue()
                    && !$cellF->getValue() && !$cellG->getValue() && !$cellH->getValue() && !$cellI->getValue()) {
                    continue;
                }

                $posizioneImpegno = new PosizioneImpegno();
                $posizioneImpegno->setPropostaImpegno($propostaImpegno);

                if ($cellB->getValue()) {
                    $posizioneImpegno->setPtext($cellB->getValue());
                }

                if ($cellC->getValue()) {
                    $posizioneImpegno->setLifnr($cellC->getValue());
                } else {
                    throw new Exception('Il codice LIFNR è obbligatorio.');
                }

                if ($cellD->getValue()) {
                    $posizioneImpegno->setZzCup($cellD->getValue());
                }

                if ($cellE->getValue()) {
                    $posizioneImpegno->setZzCig($cellE->getValue());
                }

                if ($cellF->getValue()) {
                    $posizioneImpegno->setZzLivello5($cellF->getValue());
                } else {
                    throw new Exception('Il Livello 5 (ZZLIVELLO5) è obbligatorio.');
                }

                if ($cellG->getValue()) {
                    $posizioneImpegno->setZzCodFormAv($cellG->getValue());
                }

                if ($cellH->getValue()) {
                    $posizioneImpegno->setWtges($cellH->getValue());
                } else {
                    throw new Exception('L’importo totale riservato in divisa transazione (WTGES) è obbligatorio.');
                }

                if ($cellI->getValue()) {
                    $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")
                        ->findOneBy(['id' => $cellI->getValue(), 'procedura' => $procedura]);

                    if ($richiesta) {
                        $posizioneImpegno->setRichiesta($richiesta);
                    } else {
                        throw new Exception('L’ID richiesta di contributo (ID richiesta: ' . $cellI->getValue() . ') non appartiene al bando (ID bando: ' . $procedura->getId() . ') per cui si sta creando la proposta di impegno.');
                    }
                } else {
                    throw new Exception('L’ID richiesta di contributo è obbligatorio');
                }

                $this->getEm()->persist($posizioneImpegno);
            }

            $this->getEm()->persist($propostaImpegno);
            $this->getEm()->flush();

            $esito->esito = 0;
            $esito->idPropostaImpegno = $propostaImpegno->getId();
            return $esito;
        } catch (Exception $e) {
            $esito->esito = 1;
            $esito->messaggi = $e->getMessage();
            return $esito;
        }
    }
}
