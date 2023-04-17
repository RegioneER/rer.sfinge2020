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
use AttuazioneControlloBundle\Entity\GiustificativoPagamento;

class GestoreEstrazioni extends BaseService {

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var SpreadsheetFactory
     */
    protected $excel;

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->em = $this->getEm();
        $this->logger = $this->container->get('logger');
        $this->excel = $this->container->get('phpoffice.spreadsheet');
    }

    public function getProcedure() {
        $excel = $this->excel->getSpreadSheet();
        $excel->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Estrazione Procedura")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet()->setTitle("Procedure");
        $this->setIntestazioneProcedure($sheet);
        $repository = $this->em->getRepository('SfingeBundle:Procedura');
        $risultati = $repository->getProcedurePOR();
        foreach ($risultati as $idx => $risultato /* @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {
            $sheet->fromArray(
                    [
                        $risultato->getId(), // ID pagamento
                        $risultato->getTitolo(), //  'Titolo procedura',
                        $risultato->getAsse(), //  'Titolo asse',
                        $risultato->getResponsabile(), //'Responsabile procedura',
                        $risultato->getAtto(), // 'atto procedura',
                        $risultato->getRisorseDisponibili(), // 'Risorse disponibili',
                        $risultato->getObiettiviSpcString(), //'Obiettivi specifici'
                        $risultato->getAzioniString(), //'Azioni'
                        $risultato->getTipiAiutoString(), //'Tipo aiuto'
                        $risultato->getMonCodAiutoRna(), //'Codice aiuto RNA'
                    ], null, 'A' . ($idx + 2)
            );

            $sheet->getStyle('J' . ($idx + 2) . ':L' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }
        return $this->excel->createWriter($excel, 'Xls');
    }

    private function setIntestazioneProcedure(Worksheet &$sheet) {
        $sheet->fromArray(
                [
                    'ID procedura',
                    'Titolo',
                    'Asse',
                    'Responsabile',
                    'Atto',
                    'Risorse disponibili',
                    'Obiettivi specifici',
                    'Azioni',
                    'Tipo aiuto',
                    'Codice aiuto RNA',
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

    public function getOperazioni($procedura) {
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
        
        if ($procedura != 'all') {
            $risultati = $this->em->getRepository('RichiesteBundle:Richiesta')->getEstrazioneAuditProcedura($procedura);
            $this->setIntestazioneOperazioniProcedura($sheet);
            foreach ($risultati as $idx => $risultato /* @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {
                $sheet->fromArray(
                        [
                            $risultato['id_operazione'], //ID operazione
                            $risultato['protocollo'], //  'PG Operazione',
                            $risultato['codice_cup'], //  'CUP',
                            $risultato['denominazione'], //'Beneficiario',
                            $risultato['titolo_procedura'], // 'Procedura',
                            $risultato['asse_completo'], // 'Asse',
                            $risultato['titolo'], //'Titolo progetto'
                            $risultato['abstract'], //'Abstract'
                            $risultato['richiesta']->getCostoAmmesso(),
                            $risultato['contributo_ammesso'], //'Contributo concesso'
                            $risultato['data_termine'], //'Data termine progetto'
                            $risultato['controllo_loco'] ? 'SI' : 'NO', //'Campionata per controllo in loco'
                        ], null, 'A' . ($idx + 2)
                );

                $sheet->getStyle('I' . ($idx + 2))
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            }
        } else {
            $risultati = $this->em->getRepository('RichiesteBundle:Richiesta')->getEstrazioneAudit($procedura);
            $this->setIntestazioneOperazioni($sheet);
            foreach ($risultati as $idx => $risultato /* @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {
                $sheet->fromArray(
                        [
                            $risultato['id_operazione'], //ID operazione
                            $risultato['protocollo'], //  'PG Operazione',
                            $risultato['codice_cup'], //  'CUP',
                            $risultato['denominazione'], //'Beneficiario',
                            $risultato['titolo_procedura'], // 'Procedura',
                            $risultato['asse_completo'], // 'Asse',
                            $risultato['titolo'], //'Titolo progetto'
                            $risultato['abstract'], //'Abstract'
                            $risultato['contributo_ammesso'], //'Contributo concesso'
                            $risultato['data_termine'], //'Data termine progetto'
                            $risultato['controllo_loco'] ? 'SI' : 'NO', //'Campionata per controllo in loco'
                        ], null, 'A' . ($idx + 2)
                );

                $sheet->getStyle('I' . ($idx + 2))
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
            }
        }

        return $this->excel->createWriter($excel, 'Xls');
    }

    private function setIntestazioneOperazioni(Worksheet &$sheet) {
        $sheet->fromArray(
                [
                    'ID operazione',
                    'PG Operazione',
                    'CUP',
                    'Beneficiario',
                    'Procedura',
                    'Asse',
                    'Titolo progetto',
                    'Abstract',
                    'Contributo concesso',
                    'Data termine progetto',
                    'Campionata per controllo in loco',
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

    private function setIntestazioneOperazioniProcedura(Worksheet &$sheet) {
        $sheet->fromArray(
                [
                    'ID operazione',
                    'PG Operazione',
                    'CUP',
                    'Beneficiario',
                    'Procedura',
                    'Asse',
                    'Titolo progetto',
                    'Abstract',
                    'Costo ammesso',
                    'Contributo concesso',
                    'Data termine progetto',
                    'Campionata per controllo in loco',
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

    public function getPagamenti($procedura) {
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
        $this->setIntestazionePagamenti($sheet);
        $risultati = $this->em->getRepository('AttuazioneControlloBundle:Pagamento')->getEstrazioneAudit($procedura);

        foreach ($risultati as $idx => $risultato /* @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {
            $sheet->fromArray(
                    [
                        $risultato['id_operazione'], //ID operazione
                        $risultato['id_pagamento'], //.'-'.$risultato['certpag'], //  'ID Pagamento',
                        $risultato['causale'], //  'Causale pagamento',
                        $risultato['importo_richiesto'], //'Importo richiesto',
                        $risultato['data_invio'], // 'Data invio richiesta',
                        $risultato['data_mandato'], // 'Data Mandato di Pagamento ',
                        $risultato['stato_pagamento'], //'Stato'
                        $risultato['dpi'], //'DPI'
                        $risultato['importo_proposto'], //'Importo proposto per la Certificazione'
                        $risultato['taglio_ada'], //'Taglio AdC'
                    ], null, 'A' . ($idx + 2)
            );

            $sheet->getStyle('I' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

            $sheet->getStyle('J' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

            $sheet->getStyle('K' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }
        return $this->excel->createWriter($excel, 'Xls');
    }

    private function setIntestazionePagamenti(Worksheet &$sheet) {
        $sheet->fromArray(
                [
                    'ID operazione',
                    'ID Pagamento',
                    'Causale pagamento',
                    'Importo richiesto',
                    'Data invio richiesta',
                    'Data Mandato di Pagamento ',
                    'Stato',
                    'DPI',
                    'Importo proposto per la Certificazione',
                    'Taglio AdC',
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

    public function getGiustificativi($procedura) {
        $excel = $this->excel->getSpreadSheet();
        $excel->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Report pagamenti con chiusura inviata")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet()->setTitle("Giustificativi");
        $this->setIntestazioneGiustificativi($sheet);
        $risultati = $this->em->getRepository('AttuazioneControlloBundle:GiustificativoPagamento')->getGiustificativiByProcedura($procedura);

        foreach ($risultati as $idx => $g /* @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {
            $fornitore_dipendente = $g['giustificativo']->getDenominazioneFornitore();
            if (!is_null($fornitore_dipendente)) {
                $fornitore_dipendente .= $g['giustificativo']->getCodiceFiscaleFornitore() ? (' - ' . $g['giustificativo']->getCodiceFiscaleFornitore()) : '';
            } elseif (!is_null($g['giustificativo']->getEstensione()) && !is_null($g['giustificativo']->getEstensione()->getNome())) {
                $fornitore_dipendente = $g['giustificativo']->getEstensione()->getNome() . ' ' . $g['giustificativo']->getEstensione()->getCognome();
            } else {
                $fornitore_dipendente = '-';
            }
            $sheet->fromArray(
                    [
                        $g['id_pagamento'],
                        implode($g['giustificativo']->getPianoCosto(), ', '),
                        $g['tipo_giustificativo'],
                        $fornitore_dipendente,
                        $g['descrizione'],
                        $g['numero'],
                        $g['importo'],
                        $g['data'],
                        $g['importo_contributo'],
                        $g['importo_app'],
                        $g['giustificativo']->getMotivazioniNonAmmissibilita(),
                        $g['nota_integrazione'],
                    ], null, 'A' . ($idx + 2)
            );
            $sheet->getStyle('I' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

            $sheet->getStyle('J' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }
        return $this->excel->createWriter($excel, 'Xls');
    }

    private function setIntestazioneGiustificativi(Worksheet &$sheet) {
        $sheet->fromArray(
                [
                    'ID pagamento',
                    'Voce piano costi',
                    'Tipologia giustificativo',
                    'Fornitore / Dipendente',
                    'Descrizione',
                    'Numero fattura',
                    'Importo fattura',
                    'Data fattura',
                    'Importo su cui si chiede il contributo',
                    'Importo ammesso',
                    'Motivazione di non ammissibilità',
                    'Nota alla richiesta di integrazione'
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

    public function getProcedureAggiudicazione($procedura) {
        $excel = $this->excel->getSpreadSheet();
        $excel->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Report pagamenti con chiusura inviata")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet()->setTitle("Giustificativi");
        $this->setIntestazioneProcedureAggiudicazione($sheet);
        $risultati = $this->em->getRepository('AttuazioneControlloBundle:ProceduraAggiudicazione')->getProcedureAggiudicazioneAudit($procedura);

        foreach ($risultati as $idx => $risultato /* @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {
            $sheet->fromArray(
                    [
                        $risultato['id_operazione'],
                        $risultato['codice_procedura'],
                        $risultato['cig'],
                        $risultato['senza_cig'],
                        $risultato['descrizione'],
                        $risultato['tipo'],
                        $risultato['importo'],
                        $risultato['data_p'],
                        $risultato['importo_aggiudicato'],
                        $risultato['data'],
                    ], null, 'A' . ($idx + 2)
            );
            $sheet->getStyle('G' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }
        return $this->excel->createWriter($excel, 'Xls');
    }

    private function setIntestazioneProcedureAggiudicazione(Worksheet &$sheet) {
        $sheet->fromArray(
                [
                    'ID operazione',
                    'Codice procedura aggiudicazione',
                    'CIG',
                    'Motivo assenza CIG',
                    'Descrizione procedura aggiudicazione',
                    'Tipo procedura aggiudicazione',
                    'Importo procedura aggiudicazione',
                    'Data pubblicazione',
                    'Importo aggiudicato',
                    'Data aggiudicazione',
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

    public function getControlliLoco($procedura) {
        $excel = $this->excel->getSpreadSheet();
        $excel->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Report pagamenti con chiusura inviata")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet()->setTitle("Giustificativi");
        $this->setIntestazioneControlliLoco($sheet);
        $risultati = $this->em->getRepository('AttuazioneControlloBundle:Controlli\ControlloProgetto')->estrazioneControlliRichiesteAudit($procedura);

        foreach ($risultati as $idx => $risultato /* @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {
            $sheet->fromArray(
                    [
                        $risultato['id_operazione'],
                        $risultato['data_controllo'],
                        $risultato['note_controllo'],
                        $risultato['ctr']->getRichiesta()->importoRendicontatoAmmesso(),
                        $risultato['ammesse'],
                        $risultato['rivalutare'],
                        $risultato['non_ammesse'],
                        $risultato['esito'],
                        $risultato['data_val'],
                    ], null, 'A' . ($idx + 2)
            );
            $sheet->getStyle('D' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

            $sheet->getStyle('E' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

            $sheet->getStyle('F' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

            $sheet->getStyle('G' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }

        return $this->excel->createWriter($excel, 'Xls');
    }

    private function setIntestazioneControlliLoco(Worksheet &$sheet) {
        $sheet->fromArray(
                [
                    'ID operazione',
                    'Data controllo in loco',
                    'Note',
                    'Importo delle spese ammesse dalla/e check-list relative alle verifiche sul 100% della spesa rendicontata ',
                    'Spese ammesse LOCO',
                    'Spese da rivalutare',
                    'Spese non ammesse',
                    'Esito',
                    'Data validazione',
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

    public function getDecertificazioni($procedura) {
        $excel = $this->excel->getSpreadSheet();
        $excel->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Report pagamenti con chiusura inviata")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet()->setTitle("Giustificativi");
        $this->setIntestazioneDecertificazioni($sheet);
        $risultati = $this->em->getRepository('CertificazioniBundle:CertificazionePagamento')->getDecertificazioniPagamentiProcedura($procedura);

        foreach ($risultati as $idx => $risultato /* @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {
            $sheet->fromArray(
                    [
                        $risultato['id_pagamento'],
                        $risultato['id_operazione'],
                        $risultato['importo_decertificato'],
                        $risultato['certificazione'],
                        $risultato['ritiro'] == true ? 'SI' : 'NO',
                        $risultato['recupero'] == true ? 'SI' : 'NO',
                        $risultato['articolo'] == true ? 'SI' : 'NO',
                        $risultato['ada'] == true ? 'SI' : 'NO',
                        $risultato['nota'],
                        $risultato['certificazione_p']->getChiusuraAnni(),
                    ], null, 'A' . ($idx + 2)
            );
            $sheet->getStyle('C' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }

        return $this->excel->createWriter($excel, 'Xls');
    }

    private function setIntestazioneDecertificazioni(Worksheet &$sheet) {
        $sheet->fromArray(
                [
                    'ID pagamento',
                    'ID operazione',
                    'Importo decertificato/ritirato nei conti',
                    'Certificazione',
                    'Ritiro',
                    'Recupero',
                    'Sospeso art. 137',
                    'Segnalazione ada',
                    'Nota decertificazione/Invio nei conti',
                    'Periodo contabile',
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
    
    public function getContratti($procedura) {
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
        $this->setIntestazioneContratti($sheet);
        $risultati = $this->em->getRepository('AttuazioneControlloBundle:Contratto')->getEstrazioneAudit($procedura);

        foreach ($risultati as $idx => $risultato /* @var \AttuazioneControlloBundle\Entity\Revoche\Recupero */) {
            $sheet->fromArray(
                    [
                        $risultato['id_pagamento'],
                        $risultato['tipologia_contratto'],
                        $risultato['stazione_appaltante'],
                        $risultato['altro_stazione_appaltante'],
                        $risultato['tipologia_fornitore'],
                        $risultato['fornitore'],
                        $risultato['beneficiario'],
                        $risultato['piattaforma_committenza'],
                        $risultato['numero_contratto'],
                        $risultato['descrizione_contratto'],
                        $risultato['importo_contratto_complessivo'],
                        $risultato['data_contratto'],
                        $risultato['provvediamento'],
                        $risultato['tipologia_atto'],
                        $risultato['numero_atto'],
                        $risultato['data_atto'],
                        
                    ], null, 'A' . ($idx + 2)
            );
            $sheet->getStyle('I' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

            $sheet->getStyle('J' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

            $sheet->getStyle('K' . ($idx + 2))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }
        return $this->excel->createWriter($excel, 'Xls');
    }

    private function setIntestazioneContratti(Worksheet &$sheet) {
        $sheet->fromArray(
                [
                    'ID Pagamento',
                    'Tipologia contratto',
                    'Stazione appaltante',
                    'Altro stazione appaltante',
                    'Tipologia fornitore',
                    'Fornitore',
                    'Beneficiario',
                    'Utilizzo di piattaforma telematica/centrale di committenza',
                    'Numero contratto',
                    'Descrizione contratto (255 caratteri)',
                    'Importo contratto complessivo',
                    'Data contratto',
                    'Provvedimento Avvio del Procedimento',
                    'Tipologia Atto Aggiudicazione',
                    'Numero Atto Aggiudicazione',
                    'Data atto aggiudicazione'
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
