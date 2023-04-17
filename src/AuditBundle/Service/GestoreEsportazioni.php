<?php

/**
 * @author lfontana
 */

namespace AuditBundle\Service;

use BaseBundle\Service\BaseService;
use Doctrine\ORM\EntityManager;
use AttuazioneControlloBundle\Entity\Revoche\Revoca;
use AttuazioneControlloBundle\Entity\Revoche\Recupero;
use RichiesteBundle\Entity\Richiesta;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use Symfony\Bridge\Monolog\Logger;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\Revoche\RevocaRepository;
use BaseBundle\Service\SpreadsheetFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GestoreEsportazioni extends BaseService {
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var RevocaRepository
     */
    protected $revocaRepository;

    /**
     * @var SpreadsheetFactory
     */
    protected $excel;

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->em = $this->getEm();
        $this->logger = $this->container->get('logger');
        $this->excel = $this->container->get('phpoffice.spreadsheet');
        $this->revocaRepository = $this->em->getRepository('AttuazioneControlloBundle:Revoche\Revoca');
    }

    public function getReportRevocheInviate(): IWriter {
        $excel = $this->excel->getSpreadSheet();
        $excel->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Report revoche inviate")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet()->setTitle("Revoche");
        $this->setIntestazioneRevocheInviate($sheet);
        $riga = 2;
        foreach ($this->revocaRepository->iterateAuditInvioConti() as $idx => $revoca) {
            $revoca = $revoca[0];/** @var Revoca */
            $attoRevoca = $revoca->getAttoRevoca();/** @var \AttuazioneControlloBundle\Entity\Revoche\AttoRevoca */
            $atc = $revoca->getAttuazioneControlloRichiesta();/** @var AttuazioneControlloRichiesta */
            $richiesta = $atc->getRichiesta();/** @var Richiesta */
            $istruttoria = $richiesta->getIstruttoria();/* @var IstruttoriaRichiesta */
            $sheet->fromArray(
                    [
                        $richiesta->getId(), //ID operazione
                        $revoca->getId(), //ID revoca
                        $richiesta->getProtocollo(), //protocollo
                        $richiesta->getMandatario()->getDenominazione(), //mandatario
                        \is_null($istruttoria->getCodiceCup()) ? $atc->getCup() : $istruttoria->getCodiceCup(), //CUP
                        \is_null($attoRevoca) ? null : $attoRevoca->getNumero(), //Numero atto revoca
                        \is_null($attoRevoca) ? null : \PHPExcel_Shared_Date::PHPToExcel($attoRevoca->getData()), //data atto
                        \is_null($attoRevoca) ? null : $attoRevoca->getTipo(), //tipo revoca
                        \is_null($attoRevoca) ? null : $attoRevoca->getTipoMotivazione(), //Motivazione
                        $revoca->getContributo(), //Contributo
                        $revoca->getTipoIrregolarita(), //tipo irregolarità
                        $revoca->getNotaInvioConti(), //Note
                        $this->getAnnoChiusura($revoca), //Anno chiusura
                        $revoca->getConRitiro() ? 'Si' : 'No', //Ritiro
                        $revoca->getConRecupero() ? 'Si' : 'No', //Recupero
                        $revoca->getTaglioAda() ? 'Si' : 'No', //taglio AdA
                    ], null, 'A' . $riga++
            );
            //Setto gli stili di visualizzazione per le colonne
            $sheet->getStyle('G' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            $sheet->getStyle('J' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }

        return $this->excel->createWriter($excel, 'Xls');
    }

    private function setIntestazioneRevocheInviate(Worksheet &$sheet) {
        $sheet->fromArray(
                [
                    'ID Operazione',
                    'ID Revoca',
                    'Protocollo del progetto',
                    'Soggetto mandatario',
                    'CUP',
                    'Numero atto di revoca',
                    'Data atto',
                    'Tipo revoca',
                    'Motivazione',
                    'Importo revoca',
                    'Tipo irregolarità',
                    'Nota invio conti',
                    'Anno chiusura',
                    'Ritiro',
                    'Recupero',
                    'Taglio AdA',
                ], null
        );

        $sheet->getStyle('A1:P1')
                ->applyFromArray(
                        [
                            'fill' => [
                                'type' => Fill::FILL_SOLID,
                                'color' => ['rgb' => 'FFFF99'],
                            ],
                            'font' => [
                                'bold' => true,
                            ],
                        ]
        );
    }

    private function getAnnoChiusura(Revoca $revoca) {
        $certificazione = $this->revocaRepository->findCertificazioneRevoca($revoca);/* @var Certificazione */
        return \is_null($certificazione) ? '-' :
                ($certificazione->getChiusura()->getIntervalliAnni());
    }

    public function getReportRevocheConRecupero(): IWriter {
        $excel = $this->excel->getSpreadSheet();
        $excel->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Report revoche con recupero")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet()->setTitle("Revoche");
        $this->setIntestazioneRevocheConRecupero($sheet);
        foreach ($this->revocaRepository->iterateAuditRevocheConRecupero() as $idx => $recupero) {
            $recupero = $recupero[0];/** @var Recupero */
            $revoca = $recupero->getRevoca();/** @var Revoca */
            $attoRevoca = $revoca->getAttoRevoca();/** @var \AttuazioneControlloBundle\Entity\Revoche\AttoRevoca */
            $atc = $revoca->getAttuazioneControlloRichiesta();/** @var AttuazioneControlloRichiesta */
            $ultimaVariazioneApprovata = $atc->getUltimaVariazioneApprovata();
            $richiesta = $atc->getRichiesta();/** @var Richiesta */
            $procedura = $richiesta->getProcedura();/** @var \SfingeBundle\Entity\ProceduraOperativa */
            $istruttoria = $richiesta->getIstruttoria();/* @var IstruttoriaRichiesta */
            $sheet->fromArray(
                    [
                        $richiesta->getId(), // 'ID Operazione',
                        $revoca->getId(), // 'ID Revoca',
                        $recupero->getId(), // 'ID Recupero',
                        $richiesta->getProtocollo(), //'Protocollo del progetto',
                        \is_null($istruttoria->getCodiceCup()) ? $atc->getCup() : $istruttoria->getCodiceCup(), // 'CUP',
                        $richiesta->getTitolo(), //  'Titolo operazione',
                        $richiesta->getMandatario()->getDenominazione(), //'Soggetto mandatario',
                        1 == \count($richiesta->getProponenti()) ? "Singolo soggetto" : "Rete di soggetti", // 'Proponenti',
                        $richiesta->getAbstract(), // 'Abstract',
                        \is_null($ultimaVariazioneApprovata) || \is_null($ultimaVariazioneApprovata->getContributoAmmesso()) ? $istruttoria->getContributoAmmesso() : $ultimaVariazioneApprovata->getContributoAmmesso(), // 'Contributo concesso',
                        $istruttoria->getImpegnoAmmesso(), // 'Impegno Concesso',
                        $richiesta->getAiutoDiStato() ? 'Si' : 'No', // 'Aiuto di stato',
                        \is_null($attoRevoca) ? null : $attoRevoca->getNumero(), //Numero atto revoca
                        \is_null($attoRevoca) ? null : $attoRevoca->getDescrizione(), // 'Descrizione revoca',
                        $revoca->getTipoIrregolarita(), // 'Tipo di revoca',
                        \is_null($attoRevoca) ? null : $attoRevoca->getTipoMotivazione(), //Motivazione
                        \is_null($attoRevoca) ? null : \PHPExcel_Shared_Date::PHPToExcel($attoRevoca->getData()), //data atto
                        $revoca->getContributo(), // 'Importo revoca',
                        // 'Contributo da recuperare', == Importo revoca?
                        $recupero->getTipoFaseRecupero(), // 'Fase recupero',
                        $recupero->getContributoCorsoRecupero(), // 'Contributo in corso di recupero',
                        $recupero->getTipoSpecificaRecupero(), // 'Specifica del recupero',
                        $revoca->getConRitiro() ? 'Si' : 'No', //Ritiro
                        $revoca->getConRecupero() ? 'Si' : 'No', //Recupero
                        $revoca->getTaglioAda() ? 'Si' : 'No', //taglio AdA
                    ], null, 'A' . ($idx + 2)
            );

            //Imposto stile delle celle della riga
            $sheet->getStyle('Q' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            $sheet->getStyle('J' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            $sheet->getStyle('K' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            $sheet->getStyle('R' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            $sheet->getStyle('T' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }

        return $this->excel->createWriter($excel, 'Xls');
    }

    private function setIntestazioneRevocheConRecupero(Worksheet &$sheet) {
        $sheet->fromArray(
                [
                    'ID Operazione',
                    'ID Revoca',
                    'ID Recupero',
                    'Protocollo del progetto',
                    'CUP',
                    'Titolo operazione',
                    'Soggetto mandatario',
                    'Proponenti',
                    'Abstract',
                    'Contributo concesso',
                    'Impegno Concesso',
                    'Aiuto di stato',
                    'Numero revoca',
                    'Descrizione revoca',
                    'Tipo di revoca',
                    'Motivazione revoca',
                    'Data atto revoca',
                    // 'Importo revoca',
                    'Contributo da recuperare',
                    'Fase recupero',
                    'Contributo in corso di recupero',
                    'Specifica del recupero',
                    'Ritiro',
                    'Recupero',
                    'Taglio AdA',
                ], null
        );

        $sheet->getStyle('A1:X1')
                ->applyFromArray(
                        [
                            'fill' => [
                                'type' => Fill::FILL_SOLID,
                                'color' => ['rgb' => 'FFFF99'],
                            ],
                            'font' => [
                                'bold' => true,
                            ],
                        ]
        );
    }

    public function getReportPagamentiCertificati() {
        $excel = $this->excel->getSpreadSheet();
        $excel->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Report pagamenti con chiusura inviata")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet()->setTitle("Pagamenti certificati");
        $this->setIntestazionePagamentiCertificati($sheet);
        $risultati = $this->revocaRepository->iteratePagamentiCertAgreaCertificati(\CertificazioniBundle\Entity\StatoCertificazione::CERT_APPROVATA);
        foreach ($risultati as $idx => $risultato /* @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {
            $sheet->fromArray(
                    [
                        $risultato['id_pagamento'], // ID pagamento
                        $risultato['id_richiesta'], // 'ID Operazione',
                        $risultato['protocollo_richiesta'], //'Protocollo del progetto',
                        $risultato['protocollo_pagamento'], // 'Protocollo del pagamnto',
                        $risultato['titolo'], //  'Titolo operazione',
                        $risultato['titolo_procedura'], //  'Titolo procedura',
                        $risultato['titolo_asse'], //  'Titolo asse',
                        $risultato['denominazione'], //'Soggetto mandatario',
                        $risultato['mod_pagamento'], // 'Modalità richiesta di pagamento',
                        $risultato['importo_pagato'], // 'Importo pagamento',
                        $risultato['importo_proposto'], //'importo richiesto'
                        $risultato['importo_certificato'], //'importo certificato'
                        !is_null($risultato['anno_contabile']) ? $risultato['anno_contabile'] : 'n.d.', //'anno contabile'
                        !is_null($risultato['numero']) ? $risultato['numero'] : 'n.d.', //'nuero certificazione'
                    ], null, 'A' . ($idx + 2)
            );

            $sheet->getStyle('J' . ($idx + 2) . ':L' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }
        return $this->excel->createWriter($excel, 'Xls');
    }

    private function setIntestazionePagamentiCertificati(Worksheet &$sheet) {
        $sheet->fromArray(
                [
                    'ID pagamento',
                    'ID operazione',
                    'Numero protocollo operazione',
                    'Numero protocollo pagamento',
                    'Titolo operazione',
                    'Titolo procedura',
                    'Asse',
                    'Mandatario',
                    'Modalità richiesta di pagamento',
                    'Importo pagato',
                    'Importo proposto certificazione',
                    'Importo certificato',
                    'Anno contabile',
                    'Numero certificazione',
                ], null
        );
        $sheet->getStyle('A1:Q1')
                ->applyFromArray(
                        [
                            'fill' => [
                                'type' => Fill::FILL_SOLID,
                                'color' => ['rgb' => 'FFFF99'],
                            ],
                            'font' => [
                                'bold' => true,
                            ],
                        ]
        );
    }
}
