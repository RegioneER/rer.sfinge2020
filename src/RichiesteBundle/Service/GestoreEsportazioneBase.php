<?php

namespace RichiesteBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\HttpFoundation\Response;
use Liuggio\ExcelBundle\Factory;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\RichiestaRepository;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class GestoreEsportazioneBase extends \BaseBundle\Service\BaseService implements IGestoreEsportazione {

    /**
     * @var Procedura $procedura
     */
    protected $procedura;

    /**
     * @var Factory
     */
    protected $excelFactory;

    /**
     * @param ContainerInterface $container
     * @param Procedura $procedura
     */
    public function __construct(ContainerInterface $container, Procedura $procedura = null) {
        parent::__construct($container);
        $this->procedura = $procedura;

        $this->excelFactory = $this->container->get('phpexcel');
    }

    public function estrazioneRichieste($opzioni = array()) {
        throw new \Exception("Implementare funzionalità nella sottoclasse");
    }

    public function getFascicoli($procedura, $tipo = null): array {
        $fascicoli = array();
        foreach ($procedura->getFascicoliProcedura() as $fascioloProcedura) {
            if (!is_null($tipo)) {
                if ($fascioloProcedura->getTipoFascicolo() == $tipo) {
                    $fascicoli[] = $fascioloProcedura->getFascicolo();
                }
            } else {
                $fascicoli[] = $fascioloProcedura->getFascicolo();
            }
        }
        return $fascicoli;
    }

    public function estrazioneControlliLoco($opzioni = array()) {
        // ask the service for a Excel5
        $phpExcelObject = $this->excelFactory->createPHPExcelObject();

        $em = $this->getEm();
        $id_procedura = $opzioni['bando'];
        $richieste = $em->getRepository('RichiesteBundle\Entity\Richiesta')->getRichiesteCtrlLoco($id_procedura);
        $procedura = $em->getRepository('SfingeBundle\Entity\Procedura')->findOneById($id_procedura);
        $arrayVocePiano = array();

        $phpExcelObject->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Test result file");

        $riga = 1;
        $phpExcelObject->setActiveSheetIndex(0);
        $phpExcelObject->getActiveSheet()->setTitle("Richieste");

        $column = 0;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'ID operazione');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Protocollo');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Titolo progetto');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Tipologia misura o settore');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Regime');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Soggetto beneficiario');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Acronimo soggetto beneficiario');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Tipologia del beneficiario');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Natura del beneficiario');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Codice Ateco');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Indirizzo sede legale');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Civico sede legale');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Cap sede legale');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Comune sede legale');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Provincia sede legale');
        $column++;
        /*
         * Quì o si fa un ciclo for o un funzione specializzata a gestore per le colonne del piano dei costi
         */
        if ($procedura->isAssistenzaTecnica() == true) {
            $piano = $em->getRepository('RichiesteBundle\Entity\PianoCosto')->findOneBy(array('identificativo_pdf' => 'ASS_TECNICA'));
            $arrayVocePiano[] = $piano;
            $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, $piano->getTitolo());
            $column++;
        } elseif ($procedura->isIngegneriaFinanziaria() == true) {
            $piano = $em->getRepository('RichiesteBundle\Entity\PianoCosto')->findOneBy(array('identificativo_pdf' => 'ING_FIN'));
            $arrayVocePiano[] = $piano;
            $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, $piano->getTitolo());
            $column++;
        } elseif ($procedura->isAcquisizioni() == true) {
            $piano = $em->getRepository('RichiesteBundle\Entity\PianoCosto')->findOneBy(array('identificativo_pdf' => 'ACQUISIZIONI'));
            $arrayVocePiano[] = $piano;
            $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, $piano->getTitolo());
            $column++;
        } else {
            foreach ($procedura->getPianiCosto() as $piano) {
                if ($piano->getCodice() != 'TOT') {
                    $arrayVocePiano[] = $piano;
                    $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, $piano->getCodice() . ')' . $piano->getTitolo());
                    $column++;
                }
            }
        }
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Totale importo ammesso');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'Contributo concesso');
        $column++;
        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, 'CUP');
        $column++;

        foreach ($richieste as $richiesta) {
            foreach ($richiesta->getProponenti() as $proponente) {
                $riga++;
                $phpExcelObject->setActiveSheetIndex(0);
                $soggetto = $proponente->getSoggetto();

                $column = 0;
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($richiesta->getProtocollo()) ? '-' : $richiesta->getId());
                $column++;
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($richiesta->getProtocollo()) ? '-' : $richiesta->getProtocollo());
                $column++;
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($richiesta->getTitolo()) ? '-' : $richiesta->getTitolo());
                $column++;
                //quà ci va la misura da prendere dall'oggetto richiesta
                $oggettiRichiesta = $richiesta->getOggettiRichiesta();
                $oggettoRichiesta = $oggettiRichiesta[0];
                if (method_exists($oggettoRichiesta, 'getMisura')) {
                    $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($oggettoRichiesta->getMisura()) ? '-' : $oggettoRichiesta->getMisura());
                } else {
                    $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, '-');
                }
                $column++;
                //quà ci va il regime da prendere dall'oggetto richiesta
                if (method_exists($oggettoRichiesta, 'getRegime')) {
                    $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($oggettoRichiesta->getRegime()) ? '-' : $oggettoRichiesta->getRegime());
                } else {
                    $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, '-');
                }
                $column++;

                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getDenominazione()) ? '-' : $soggetto->getDenominazione());
                $column++;
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, !is_null($soggetto) && $soggetto->getTipo() == 'SOGGETTO' && !is_null($soggetto->getAcronimoLaboratorio()) ? $soggetto->getAcronimoLaboratorio() : '-');
                $column++;
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, $proponente->isMandatario() ? 'Capofila' : 'Partner');
                $column++;
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto) ? '-' : $soggetto->getTipo());
                $column++;
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getCodiceAteco()) ? '-' : $soggetto->getCodiceAteco());
                $column++;
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getVia()) ? '-' : $soggetto->getVia());
                $column++;
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getCivico()) ? '-' : $soggetto->getCivico());
                $column++;
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getCap()) ? '-' : $soggetto->getCap());
                $column++;
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getComune()) ? '-' : $soggetto->getComune()->getDenominazione());
                $column++;
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getProvincia()) ? '-' : $soggetto->getProvincia()->getDenominazione());
                $column++;

                foreach ($arrayVocePiano as $voce) {
                    if (count($richiesta->getProponenti()) > 1) {
                        $voceCosto = $em->getRepository('RichiesteBundle\Entity\VocePianoCosto')->getVoceDaPianoERichiestaProponente($voce->getId(), $richiesta->getId(), $proponente->getId());
                    } else {
                        $voceCosto = $em->getRepository('RichiesteBundle\Entity\VocePianoCosto')->getVoceDaPianoERichiestaProponente($voce->getId(), $richiesta->getId());
                    }
                    if ($richiesta->isProceduraParticolare() == true) {
                        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, (count($voceCosto) == 0 || is_null($voceCosto[0])) ? '-' : $voceCosto[0]->getTotale());
                    } else {
                        $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, (count($voceCosto) == 0 || is_null($voceCosto[0])) ? '-' : $voceCosto[0]->getTotaleAmmesso());
                    }
                    $column++;
                }

                //Importo ammesso da istuttoria
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($richiesta->getIstruttoria()->getCostoAmmesso()) ? '-' : $richiesta->getIstruttoria()->getCostoAmmesso());
                $column++;
                //Contributo da istuttoria
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($richiesta->getIstruttoria()->getContributoAmmesso()) ? '-' : $richiesta->getIstruttoria()->getContributoAmmesso());
                $column++;
                //CUP da istuttoria
                $phpExcelObject->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $riga, is_null($richiesta->getIstruttoria()->getCodiceCup()) ? '-' : $richiesta->getIstruttoria()->getCodiceCup());
            }
        }

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->excelFactory->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->excelFactory->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'estrazione_universo_ctrl_loco.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @return \PHPExcel
     * @throws \Exception
     */
    public function getReportVariazioni() {
        if (\is_null($this->procedura)) {
            throw new \Exception('Procedura non definita nel costruttore');
        }
        $variazioneRepository = $this->getEm()->getRepository('AttuazioneControlloBundle:VariazioneRichiesta');/** @var VariazioneRichiestaRepository $variazioneRepository */
        $excelService = $this->excelFactory;
        $excel = $excelService->createPHPExcelObject();
        $excel->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Report variazioni")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet()->setTitle("Variazioni");/** @var \PHPExcel_Worksheet $sheet */
        $this->setIntestazioneVariazioni($sheet);
        foreach ($this->getEm()->getRepository('AttuazioneControlloBundle:VariazioneRichiesta')
                ->iterateVariazioni($this->procedura) as $idx => $variazione) {
            // $variazione = $variazione[0];   /** @var VariazioneRichiesta $variazione */
            $costi = $variazioneRepository->getCostiVariazione($variazione);
            $richiesta = $variazione->getAttuazioneControlloRichiesta()->getRichiesta();
            $sheet->fromArray(
                    array(
                        $variazione->getId(), //Numero Variazione
                        $richiesta->getMandatario(), //Soggetto
                        $richiesta->getProtocollo(), //Protocollo domanda contributo
                        \PHPExcel_Shared_Date::PHPToExcel($variazione->getDataInvio()), //Data invio Variazione
                        $variazione->getProtocollo(), //Protocollo Variazione
                        $richiesta->getIstruttoria()->getCostoAmmesso(), //Costo ammesso in concessione PRENDERE DA ISTRUTTORIA
                        $costi['importo_approvato'], //Costo proposto variazione
                        $variazione->getCostoAmmesso(), //Costo Variazione ammesso
                        $variazione->getContributoAmmesso(), //Contributo ammesso
                        $variazione->getEsitoString(), //Esito finale (ammessa/non ammessa)

                    ), null, 'A' . ($idx + 2)
            );
            //Setto gli stili di visualizzazione per le colonne
            $sheet->getStyle('D' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
            $sheet->getStyle('F' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            $sheet->getStyle('G' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            $sheet->getStyle('H' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            $sheet->getStyle('I' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }


        return $excel;
    }

    /**
     * @param \PHPExcel_Worksheet &$sheet
     * @return null
     */
    protected function setIntestazioneVariazioni(\PHPExcel_Worksheet &$sheet) {
        $sheet->fromArray(
                array(
                    'Numero Variazione', //A
                    'Soggetto', //B
                    'Protocollo domanda contributo', //C
                    'Data invio Variazione', //D
                    'Protocollo Variazione', //E
                    'Costo ammesso in concessione', //F
                    'Costo proposto variazione', //G
                    'Costo Variazione ammesso', //H
                    'Contributo ammesso', //I
                    'Esito finale (ammessa/non ammessa)', //J
                ),
                null
        );

        $sheet->getStyle('A1:P1')
                ->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'FFFF99'),
                            ),
                            'font' => array(
                                'bold' => true,
                            )
                        )
        );
    }

    /**
     * @param \PHPExcel_Worksheet $sheet
     * @param int $riga
     * @param string $colonne
     * @param string $formato
     */
    protected function setFormatoCelle($sheet, $riga, $colonne, $formato) {
        foreach (\str_split($colonne) as $colonna) {
            $sheet->getStyle((\strtoupper($colonna)) . $riga)
                    ->getNumberFormat()
                    ->setFormatCode($formato);
        }
    }

    /**
     * @param array $opzioni
     * @return Response
     */
    public function estrazioneRichiesteCompleta($opzioni) {
        /** @var \PHPExcel $excel */
        $excel = $this->excelFactory->createPHPExcelObject();

        $excel->getProperties()->setCreator("SC")
                ->setTitle("Estrazione completa progetti")
                ->setSubject("Estrazione completa progetti per il bando: " . $this->procedura->getTitolo());

        $progetti = $excel->getActiveSheet()->setTitle('Progetti');

        $this->sheetProgetti($progetti);

        return $this->creaResponse($excel, 'estrazione_completa' . $this->procedura->getId());
    }

    /**
     * @param $opzioni
     * @return mixed
     */
    public function estrazioneRichiesteCompletaConLog($opzioni = [])
    {
        // Ask the service for a Excel5
        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("SC")
            ->setLastModifiedBy("Performer Srl")
            ->setTitle("Office 2005 XLSX Test Document")
            ->setSubject("Office 2005 XLSX Test Document")
            ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
            ->setKeywords("office 2005 openxml php")
            ->setCategory("Test result file");

        $em = $this->getEm();
        $finestra = isset($opzioni['finestra_temporale']) ? '_finestra_' . $opzioni['finestra_temporale'] : '';
        /** @var Richiesta[] $richieste */
        $richieste = $em->getRepository('RichiesteBundle\Entity\Richiesta')->getTutteRichiesteProcedura($this->procedura->getId(), $finestra);

        $phpExcelObject->setActiveSheetIndex(0);
        $sheet = $phpExcelObject->getActiveSheet();
        $phpExcelObject->getActiveSheet()->setTitle("Richieste");

        $riga = 1;
        $column = -1;
        $colonne = [];
        $lettera = 'A';
        while ($lettera !== 'AAA') {
            $colonne[] = $lettera++;
        }

        $sheet->setCellValueExplicitByColumnAndRow( ++$column, $riga, 'Num. Richiesta');
        $sheet->getColumnDimension($colonne[$column])->setWidth(14);
        $sheet->setCellValueExplicitByColumnAndRow( ++$column, $riga, 'Data e Ora di trasmissione domanda alla PA');
        $sheet->getColumnDimension($colonne[$column])->setWidth(36);
        $sheet->setCellValueExplicitByColumnAndRow( ++$column, $riga, 'Data Protocollo');
        $sheet->getColumnDimension($colonne[$column])->setWidth(20);
        $sheet->setCellValueExplicitByColumnAndRow( ++$column, $riga, 'Protocollo');
        $sheet->getColumnDimension($colonne[$column])->setWidth(20);
        $sheet->setCellValueExplicitByColumnAndRow( ++$column, $riga, 'Denominazione Mandatario');
        $sheet->getColumnDimension($colonne[$column])->setWidth(50);
        $sheet->setCellValueExplicitByColumnAndRow( ++$column, $riga, 'P.IVA Mandatario');
        $sheet->getColumnDimension($colonne[$column])->setWidth(15);
        $sheet->setCellValueExplicitByColumnAndRow( ++$column, $riga, 'CF Mandatario');
        $sheet->getColumnDimension($colonne[$column])->setWidth(15);
        $sheet->setCellValueExplicitByColumnAndRow( ++$column, $riga, 'Richiesta inserita nel sistema');
        $sheet->getColumnDimension($colonne[$column])->setWidth(26);
        $sheet->setCellValueExplicitByColumnAndRow( ++$column, $riga, 'Richiesta validata');
        $sheet->getColumnDimension($colonne[$column])->setWidth(20);
        $sheet->setCellValueExplicitByColumnAndRow( ++$column, $riga, 'Richiesta firmata');
        $sheet->getColumnDimension($colonne[$column])->setWidth(20);
        $sheet->setCellValueExplicitByColumnAndRow( ++$column, $riga, 'Richiesta inviata alla pubblica amministrazione');
        $sheet->getColumnDimension($colonne[$column])->setWidth(38);
        $sheet->setCellValueExplicitByColumnAndRow( ++$column, $riga, 'Richiesta protocollata');
        $sheet->getColumnDimension($colonne[$column])->setWidth(20);

        foreach ($richieste as $richiesta) {
            /** @var Richiesta $objRichiesta */
            $objRichiesta = $richiesta[0];

            $riga++;
            $phpExcelObject->setActiveSheetIndex(0);

            $column = -1;
            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $objRichiesta->getId());

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, is_null($objRichiesta->getDataInvio()) ? '-' : $objRichiesta->getDataInvio()->format('d-m-Y H:i:s'));

            $richiesteProt = $objRichiesta->getRichiesteProtocollo();
            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, is_null($richiesteProt[0]) ? '-' : (is_null($richiesteProt[0]->getData_pg()) ? '-' : $richiesteProt[0]->getData_pg()->format('d-m-Y H:i:s')));

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, is_null($objRichiesta->getProtocollo()) ? '-' : $objRichiesta->getProtocollo());

            $proponente_mandatario = $objRichiesta->getMandatario();
            $denominazione = '-';
            $piva = '-';
            $cf = '-';

            if (!is_null($proponente_mandatario)) {
                $soggetto = $proponente_mandatario->getSoggetto();
                $denominazione = is_null($soggetto->getDenominazione()) ? '-' : $soggetto->getDenominazione();
                $piva = is_null($soggetto->getPartitaIva()) ? '-' : $soggetto->getPartitaIva();
                $cf = is_null($soggetto->getCodiceFiscale()) ? '-' : $soggetto->getCodiceFiscale();
            }

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $denominazione);
            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $piva);
            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $cf);

            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, is_null($richiesta['log_inserita']) ? '-' : $richiesta['log_inserita']);
            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, is_null($richiesta['log_validata']) ? '-' : $richiesta['log_validata']);
            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, is_null($richiesta['log_firmata']) ? '-' : $richiesta['log_firmata']);
            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, is_null($richiesta['log_inviata']) ? '-' : $richiesta['log_inviata']);
            $sheet->setCellValueExplicitByColumnAndRow(++$column, $riga, is_null($richiesta['log_protocollata']) ? '-' : $richiesta['log_protocollata']);
        }

        $sheet->setTitle('Estrazione con LOG');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // Create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // Create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        // Adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'estrazione_con_log_id_bando_' . $this->procedura->getId() . $finestra . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    protected function sheetProgetti(\PHPExcel_Worksheet $sheet): void {
        $sheet->setTitle('Progetti');
        /** @var RichiestaRepository $repository */
        $ultimaRiga = \count($this->getRichieste()) + 1;

        $sheet->fromArray([
            'ID', // A
            'Protocollo', // B
            'Fascicolo', // C
            'Titolo', // D
            'Abstract', // E
            'Data invio', // F
            'Denominazione mandatario', // G
            'Partiva IVA Mandatario', // H
            'Codice fiscale Mandatario', // I
            'Sede legale', // J
            'Importo richiesto', // K
            'Email beneficiario', // L
            'PEC beneficiario', // M
            'Incarico firmatario attivo', // N
            'Ammesso in istruttoria', // O
            'Contributo ammesso', // P
            'In attuazione', // Q
            'Codice LIFNR - SAP', // R
        ]);

        $idx = 2;
        /** @var Richiesta $richiesta */
        foreach ($this->getRichieste() as $richiesta) {
            $soggetto = $richiesta->getSoggetto();
            if (!is_null($richiesta->getFirmatario())) {
                $incaricoAttivo = $this->getEm()->getRepository('SoggettoBundle\Entity\IncaricoPersona')->haIncaricoPersonaLRDELAttivo($soggetto, $richiesta->getFirmatario()->getCodiceFiscale());
            } else {
                $incaricoAttivo = false;
            }
            if (!is_null($richiesta->getIstruttoria())) {
                $avanzamentoAttuazioneControllo = !is_null($richiesta->getAttuazioneControllo()) ? 'Si' : 'No';
                if (!is_null($richiesta->getIstruttoria()->getEsito())) {
                    $esitoIst = $richiesta->getIstruttoria()->getEsito();
                } else {
                    $esitoIst = '-';
                }
                if (!is_null($richiesta->getIstruttoria()->getContributoAmmesso())) {
                    $contributo = $richiesta->getIstruttoria()->getContributoAmmesso();
                } else {
                    $contributo = 0.00;
                }
            } else {
                $esitoIst = '-';
                $contributo = 0.00;
            }

            //fine
            $sheet->fromArray([
                $richiesta->getId(),
                $richiesta->getProtocollo(),
                $richiesta->getFascicoloProtocollo(),
                $richiesta->getTitolo(),
                $richiesta->getAbstract(),
                $richiesta->getDataInvio(),
                $richiesta->getSoggetto()->getDenominazione(),
                $richiesta->getSoggetto()->getPivaOrCf(),
                $richiesta->getSoggetto()->getCfOrPiva(),
                $soggetto->getVia() . ', ' . $soggetto->getCivico() . ' ' . $soggetto->getComune(),
                $richiesta->getTotalePianoCosto(),
                $richiesta->getSoggetto()->getEmail(),
                $richiesta->getSoggetto()->getEmailPec(),
                $incaricoAttivo == true ? 'Si' : 'No',
                $esitoIst,
                $contributo,
                $avanzamentoAttuazioneControllo,
                is_null($soggetto->getLifnrSap()) ? '-' : $soggetto->getLifnrSap()
                    ], null, 'A' . $idx++);
        }

        $sheet->getStyle("E2:E$ultimaRiga")->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);

        $sheet->getStyle("G2:H$ultimaRiga")->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $sheet->getStyle("J2:J$ultimaRiga")
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

        $sheet->getStyle("O2:O$ultimaRiga")
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

        $sheet->getStyle('A1:R1')
                ->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'FFFF99'),
                            ),
                            'font' => array(
                                'bold' => true,
                            )
                        )
        );
    }

    protected function creaResponse(\PHPExcel $excel, string $nomeFile): Response {
        // create the writer
        $writer = $this->excelFactory->createWriter($excel, 'Excel5');
        // create the response
        $response = $this->excelFactory->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, "$nomeFile.xls"
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @param int $numero_ripetizioni
     * @return array
     */
    protected function creaColonne(int $numero_ripetizioni = 0) {
        $arrayRipetizioneColonne = range(0, $numero_ripetizioni);
        $colonne = range('A', 'Z');

        $retVal = [];
        foreach ($arrayRipetizioneColonne as $ripetizione) {
            foreach ($colonne as $colonna) {
                if ($ripetizione == 0) {
                    $retVal[] = $colonna;
                } else {
                    $retVal[] = $colonne[$ripetizione - 1] . $colonna;
                }
            }
        }

        return $retVal;
    }

    protected function normalizeResult($riga): array {
        return \array_map(function ($valore) {
            if ($valore instanceof \DateTimeInterface) {
                return Date::PHPToExcel($valore);
            }
            if (\is_numeric($valore)) {
                return \floatval($valore);
            }
            return $valore;
        }, $riga);
    }

    protected function elabora(Worksheet &$sheet, string $firstCell = 'A1', iterable $data, array $styles = [], ?callable $normalizeResult = null): void {
        $matches = [];
        if (!\preg_match('/^([A-Z]+)([0-9]+)$/', $firstCell, $matches)) {
            throw new \Exception("Formato cella non valido", 1);
        }
        $startColumn = $matches[1];
        $startRow = $matches[2];
        $countRow = $startRow - 1;
        foreach ($data as $row) {
            ++$countRow;
            $rowView = ($normalizeResult ?? $this->normalizeResult)($row);
            $sheet->fromArray($rowView, null, "$startColumn$countRow", true);
        }

        if ($countRow >= $startRow) {
            foreach ($styles as $column => $format) {
                $sheet->getStyle("$column$startRow:$column$countRow")
                        ->getNumberFormat()
                        ->setFormatCode($format);
            }
        }
    }

    /**
     * @return Richiesta[]
     */
    protected function getRichieste(): array {
        /** @var RichiestaRepository $richiestaRepository */
        $richiestaRepository = $this->getEm()->getRepository('RichiesteBundle:Richiesta');

        return $richiestaRepository->getRichiesteInoltrateProcedura($this->procedura->getId());
    }
}
