<?php

/**
 * @author lfontana
 */

namespace CertificazioniBundle\Service;

use BaseBundle\Service\BaseService;
use Doctrine\ORM\EntityManager;
use AttuazioneControlloBundle\Entity\Revoche\Revoca;
use AttuazioneControlloBundle\Entity\Revoche\Recupero;
use RichiesteBundle\Entity\Richiesta;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\Pagamento;

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
     * @var \AttuazioneControlloBundle\Entity\RevocaRepository
     */
    protected $revocaRepository;

    /**
     * @var \Liuggio\ExcelBundle\Factory
     */
    protected $excel;

    public function __construct(EntityManager $em, Logger $logger, \Liuggio\ExcelBundle\Factory $excel) {
        $this->em = $em;
        $this->logger = $logger;
        $this->excel = $excel;
        $this->revocaRepository = $em->getRepository('AttuazioneControlloBundle:Revoche\Revoca');
    }

    /**
     * @return \PHPExcel_Writer_IWriter
     */
    public function getReportRevocheInviate() {
        $excel = $this->excel->createPHPExcelObject();
        $excel->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Report revoche")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet()->setTitle("Revoche");
        $this->setIntestazioneRevocheInviate($sheet);
        $riga = 2;
        $risultati = $this->revocaRepository->iterateCertAgreaInvioConti(\AttuazioneControlloBundle\Entity\Revoche\TipoFaseRecupero::CORSO);
        foreach ($risultati as $idx => $revoca /** @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {
            $atc = $revoca->getAttuazioneControlloRichiesta();/** @var AttuazioneControlloRichiesta */
            if ($atc->hasRevocaConRecuperoConclusa() == false) {
                $attoRevoca = $revoca->getAttoRevoca();/** @var \AttuazioneControlloBundle\Entity\Revoche\AttoRevoca */
                $atc = $revoca->getAttuazioneControlloRichiesta();/** @var AttuazioneControlloRichiesta */
                $richiesta = $atc->getRichiesta();/** @var Richiesta */
                $istruttoria = $richiesta->getIstruttoria();/** @var IstruttoriaRichiesta */
                $chiusura = $revoca->getChiusura();
                $anno_contabile = !is_null($chiusura) ? $chiusura->getCertificazioni()->last()->getAnnoContabile() : 'n.d.';
                $numero = !is_null($chiusura) ? $chiusura->getCertificazioni()->last()->getNumero() : 'n.d.';

                $pagamenti = $atc->getPagamenti();
                /* $certificazione = null;
                  foreach ($pagamenti as $p) {
                  $certificazioni = $p->getCertificazioni();
                  foreach ($certificazioni as $c) {
                  if ($c->getImporto() < 0) {
                  $certificazione = $c->getCertificazione();
                  break 2;
                  }
                  }
                  } */

                $primoPag = $pagamenti->first();
                $primaCert = $primoPag->getCertificazioni()->first();
                $certificazione = $primaCert ? $primaCert->getCertificazione() : null;

                $sheet->fromArray(
                        array(
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
                            implode('; ', $revoca->getTipoIrregolarita()->toArray()), // 'Tipo di revoca',
                            $revoca->getNotaInvioConti(), //Note
                            $this->getAnnoChiusura($revoca), //Anno chiusura
                            $revoca->getConRitiro() ? 'Si' : 'No', //Ritiro
                            $revoca->getConRecupero() ? 'Si' : 'No', //Recupero
                            $revoca->getTaglioAda() ? 'Si' : 'No', //taglio AdA
                            !is_null($certificazione) ? $certificazione->getAnnoContabile() : $anno_contabile,
                            $revoca->getInvioConti() ? 'Si' : 'No',
                            !is_null($certificazione) ? $certificazione->getNumero() : $numero, // Il numero di certificazione dipende dall'invio dei conti: se è SI non è definito
                        ), null, 'A' . $riga++
                );
                //Setto gli stili di visualizzazione per le colonne
                $sheet->getStyle('G' . ($idx + 2))
                        ->getNumberFormat()
                        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
                $sheet->getStyle('J' . ($idx + 2))
                        ->getNumberFormat()
                        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            }
        }


        return $this->excel->createWriter($excel);
    }

    /**
     * @param \PHPExcel_Worksheet &$sheet
     * @return null
     */
    private function setIntestazioneRevocheInviate(\PHPExcel_Worksheet &$sheet) {
        $sheet->fromArray(
                array(
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
                    'Anno contabile',
                    'Invio conti',
                    'Numero certificazione'
                ), null
        );

        $sheet->getStyle('A1:S1')
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

    private function getAnnoChiusura(Revoca $revoca) {
        $certificazione = $this->revocaRepository->findCertificazioneRevoca($revoca);/** @var Certificazione */
        return \is_null($certificazione) ? '-' :
                ($certificazione->getChiusura()->getIntervalliAnni());
    }

    /**
     * @return \PHPExcel_Writer_IWriter
     */
    public function getReportRevocheConRecupero() {
        $excel = $this->excel->createPHPExcelObject();
        $excel->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Report revoche con recuperi in chiusura")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet()->setTitle("Revoche");
        $this->setIntestazioneRevocheConRecupero($sheet);
        $risultati = $this->revocaRepository->iterateCertAgreaRevocheConRecupero(\AttuazioneControlloBundle\Entity\Revoche\TipoFaseRecupero::COMPLETO);
        foreach ($risultati as $idx => $revoca /** @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {
            $atc = $revoca->getAttuazioneControlloRichiesta();/** @var AttuazioneControlloRichiesta */
            if ($atc->hasRevocaConRecuperoConclusa() == true) {
                $recupero = $revoca->getRecuperi()->last();
                $attoRevoca = $revoca->getAttoRevoca();/** @var \AttuazioneControlloBundle\Entity\Revoche\AttoRevoca */
                $ultimaVariazioneApprovata = $atc->getUltimaVariazioneApprovata();
                $richiesta = $atc->getRichiesta();/** @var Richiesta */
                $procedura = $richiesta->getProcedura();/** @var \SfingeBundle\Entity\ProceduraOperativa */
                $istruttoria = $richiesta->getIstruttoria();/** @var IstruttoriaRichiesta */
                $chiusura = $revoca->getChiusura();
                $anno_contabile = !is_null($chiusura) ? $chiusura->getCertificazioni()->last()->getAnnoContabile() : 'n.d.';
                $numero = !is_null($chiusura) ? $chiusura->getCertificazioni()->last()->getNumero() : 'n.d.';

                $pagamenti = $atc->getPagamenti();
                /* $certificazione = null;
                  foreach ($pagamenti as $p) {
                  $certificazioni = $p->getCertificazioni();
                  foreach ($certificazioni as $c) {
                  if ($c->getImporto() < 0) {
                  $certificazione = $c->getCertificazione();
                  break 2;
                  }
                  }
                  } */

                $primoPag = $pagamenti->first();
                $primaCert = $primoPag->getCertificazioni()->first();
                $certificazione = $primaCert ? $primaCert->getCertificazione() : null;

                $sheet->fromArray(
                        array(
                            $richiesta->getId(), // 'ID Operazione',
                            $revoca->getId(), // 'ID Revoca',
                            $recupero->getId(), // 'ID Recupero',
                            $richiesta->getProtocollo(), //'Protocollo del progetto',
                            \is_null($istruttoria->getCodiceCup()) ? $atc->getCup() : $istruttoria->getCodiceCup(), // 'CUP',
                            $richiesta->getTitolo(), //  'Titolo operazione',
                            $richiesta->getMandatario()->getDenominazione(), //'Soggetto mandatario',
                            \count($richiesta->getProponenti()) == 1 ? "Singolo soggetto" : "Rete di soggetti", // 'Proponenti',
                            $richiesta->getAbstract(), // 'Abstract',
                            \is_null($ultimaVariazioneApprovata) || \is_null($ultimaVariazioneApprovata->getContributoAmmesso()) ? $istruttoria->getContributoAmmesso() : $ultimaVariazioneApprovata->getContributoAmmesso(), // 'Contributo concesso',
                            $istruttoria->getImpegnoAmmesso(), // 'Impegno Concesso',
                            $richiesta->getAiutoDiStato() ? 'Si' : 'No', // 'Aiuto di stato',
                            \is_null($attoRevoca) ? null : $attoRevoca->getNumero(), //Numero atto revoca
                            \is_null($attoRevoca) ? NULL : $attoRevoca->getDescrizione(), // 'Descrizione revoca',
                            implode('; ', $revoca->getTipoIrregolarita()->toArray()), // 'Tipo di revoca',
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
                            !is_null($certificazione) ? $certificazione->getAnnoContabile() : $anno_contabile,
                            $revoca->getInvioConti() ? 'Si' : 'No',
                            !is_null($certificazione) ? $certificazione->getNumero() : $numero, // Il numero di certificazione dipende dall'invio dei conti: se è SI non è definito
                        ), null, 'A' . ($idx + 2)
                );

                //Imposto stile delle celle della riga
                $sheet->getStyle('Q' . ($idx + 2))
                        ->getNumberFormat()
                        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
                $sheet->getStyle('J' . ($idx + 2))
                        ->getNumberFormat()
                        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
                $sheet->getStyle('K' . ($idx + 2))
                        ->getNumberFormat()
                        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
                $sheet->getStyle('R' . ($idx + 2))
                        ->getNumberFormat()
                        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
                $sheet->getStyle('T' . ($idx + 2))
                        ->getNumberFormat()
                        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            }
        }

        return $this->excel->createWriter($excel);
    }

    /**
     * @param \PHPExcel_Worksheet &$sheet
     * @return null
     */
    private function setIntestazioneRevocheConRecupero(\PHPExcel_Worksheet &$sheet) {
        $sheet->fromArray(
                array(
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
                    'Anno contabile',
                    'Invio conti',
                    'Numero certificazione'
                ), null
        );

        $sheet->getStyle('A1:AA1')
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

    public function getReportPagamentiCertificati() {
        $excel = $this->excel->createPHPExcelObject();
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
        foreach ($risultati as $idx => $risultato /** @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {

            $sheet->fromArray(
                    array(
                        $risultato['id_pagamento'], // ID pagamento
                        $risultato['id_richiesta'], // 'ID Operazione',
                        $risultato['protocollo_richiesta'], //'Protocollo del progetto',
                        $risultato['cup_richiesta'], //'Protocollo del progetto',
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
                    ), null, 'A' . ($idx + 2)
            );

            $sheet->getStyle('J' . ($idx + 2) . ':L' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }
        return $this->excel->createWriter($excel);
    }

    /**
     * @param \PHPExcel_Worksheet &$sheet
     */
    private function setIntestazionePagamentiCertificati(\PHPExcel_Worksheet &$sheet) {
        $sheet->fromArray(
                array(
                    'ID pagamento',
                    'ID operazione',
                    'Numero protocollo operazione',
                    'CUP operazione',
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
                    'Numero certificazione'
                ), null
        );
        $sheet->getStyle('A1:Q1')
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
     * @return \PHPExcel_Writer_IWriter
     */
    public function getReportRevocheUniverso() {
        $excel = $this->excel->createPHPExcelObject();
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
        $risultati = $this->revocaRepository->iterateCertAgreaUniverso();
        foreach ($risultati as $idx => $revoca /** @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {
            $atc = $revoca->getAttuazioneControlloRichiesta();/** @var AttuazioneControlloRichiesta */
            $attoRevoca = $revoca->getAttoRevoca();/** @var \AttuazioneControlloBundle\Entity\Revoche\AttoRevoca */
            $richiesta = $atc->getRichiesta();/** @var Richiesta */
            $istruttoria = $richiesta->getIstruttoria();/** @var IstruttoriaRichiesta */
            $chiusura = $revoca->getChiusura();
            $anno_contabile = !is_null($chiusura) ? $chiusura->getCertificazioni()->last()->getAnnoContabile() : 'n.d.';
            $numero = !is_null($chiusura) ? $chiusura->getCertificazioni()->last()->getNumero() : 'n.d.';

            $pagamenti = $atc->getPagamenti();

            $primoPag = $pagamenti->first();
            $primaCert = $primoPag ? $primoPag->getCertificazioni()->first() : null;
            $certificazione = $primaCert ? $primaCert->getCertificazione() : null;

            $sheet->fromArray(
                    array(
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
                        implode('; ', $revoca->getTipoIrregolarita()->toArray()), // 'Tipo di revoca',
                        $revoca->getNotaInvioConti(), //Note
                        $this->getAnnoChiusura($revoca), //Anno chiusura
                        $revoca->getConRitiro() ? 'Si' : 'No', //Ritiro
                        $revoca->getConRecupero() ? 'Si' : 'No', //Recupero
                        $revoca->getTaglioAda() ? 'Si' : 'No', //taglio AdA
                        !is_null($certificazione) ? $certificazione->getAnnoContabile() : $anno_contabile,
                        $revoca->getInvioConti() ? 'Si' : 'No',
                        !is_null($certificazione) ? $certificazione->getNumero() : $numero, // Il numero di certificazione dipende dall'invio dei conti: se è SI non è definito
                    ), null, 'A' . $riga++
            );
            //Setto gli stili di visualizzazione per le colonne
            $sheet->getStyle('G' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
            $sheet->getStyle('J' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }


        return $this->excel->createWriter($excel);
    }

}
