<?php

namespace RichiesteBundle\Controller;

use AttuazioneControlloBundle\Entity\VariazioneRichiestaRepository;
use BaseBundle\Controller\BaseController;
use DateTime;
use BaseBundle\Service\SpreadsheetFactory;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use BaseBundle\Exception\SfingeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/estrazione")
 */
class EstrazioneController extends BaseController {
    /**
     * @Route("/procedura_anagrafica/{id_procedura}", name="estrazione_richieste_procedura_anagrafica")
     * @param mixed $id_procedura
     */
    public function estrazioneRichiesteProceduraAnagraficaAction($id_procedura) {
        ini_set('memory_limit', '1G');
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find($id_procedura);
        return $this->get("gestore_esportazione")->getGestore($procedura)->estrazioneRichiesteAnagrafica();
    }

    /**
     * @Route("/procedura_progetti/{id_procedura}", name="estrazione_richieste_procedura_progetti")
     * @param mixed $id_procedura
     */
    public function estrazioneRichiesteProceduraProgettiAction($id_procedura) {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 600);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find($id_procedura);
        return $this->get("gestore_esportazione")->getGestore($procedura)->estrazioneRichiesteProgetti();
    }

    /**
     * @Route("/procedura_completa/{id_procedura}/{finestra_temporale}", name="estrazione_richieste_procedura_completa", defaults={"finestra_temporale" : 0})
     * @param mixed $id_procedura
     * @param mixed $finestra_temporale
     */
    public function estrazioneRichiesteCompletaAction($id_procedura, $finestra_temporale) {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 600);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find($id_procedura);

        $opzioni = [];
        $opzioni['finestra_temporale'] = $finestra_temporale > 0 ? $finestra_temporale : null;
        $opzioni['bando'] = $id_procedura;
        $opzioni['con_annullate'] = false;

        return $this->get("gestore_esportazione")->getGestore($procedura)->estrazioneRichiesteCompleta($opzioni);
    }

    /**
     * @Route("/procedura_completa_con_log/{id_procedura}/{finestra_temporale}", name="estrazione_richieste_procedura_completa_con_log", defaults={"finestra_temporale" : 0})
     *
     * @param $id_procedura
     * @param $finestra_temporale
     * @return mixed
     * @throws \Exception
     */
    public function estrazioneRichiesteCompletaConLogAction($id_procedura, $finestra_temporale) {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', 2500);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find($id_procedura);

        $opzioni = [];
        $opzioni['finestra_temporale'] = $finestra_temporale > 0 ? $finestra_temporale : null;
        $opzioni['bando'] = $id_procedura;

        return $this->get("gestore_esportazione")->getGestore($procedura)->estrazioneRichiesteCompletaConLog($opzioni);
    }

    /**
     * @Route("/procedura_completa_con_annullate/{id_procedura}/{finestra_temporale}", name="estrazione_richieste_procedura_completa_con_annullate", defaults={"finestra_temporale" : 0})
     *
     * @param int $id_procedura
     * @param int $finestra_temporale
     * @return mixed
     * @throws Exception
     */
    public function estrazioneRichiesteCompletaConAnnullate(int $id_procedura, int $finestra_temporale) {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 600);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find($id_procedura);

        $opzioni = [];
        $opzioni['finestra_temporale'] = $finestra_temporale > 0 ? $finestra_temporale : null;
        $opzioni['con_annullate'] = true;

        return $this->get("gestore_esportazione")->getGestore($procedura)->estrazioneRichiesteCompletaConAnnullate($opzioni);
    }

    /**
     * @Route("/procedura_rsi/{id_procedura}/{finestra_temporale}", name="estrazione_rsi", defaults={"finestra_temporale" : 0})
     * @param mixed $id_procedura
     * @param mixed $finestra_temporale
     */
    public function estrazioneRsiAction($id_procedura, $finestra_temporale) {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 600);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find($id_procedura);

        $opzioni = [];
        $opzioni['finestra_temporale'] = $finestra_temporale > 0 ? $finestra_temporale : null;

        return $this->get("gestore_esportazione")->getGestore($procedura)->estrazioneRsi($opzioni);
    }

    /**
     * @Route("variazioni/{id_procedura}/{tipologia}", name="estrazione_variazioni", defaults={"tipologia" : null})
     * @param mixed $id_procedura
     */
    public function estrazioneVariazioniAction($id_procedura, $tipologia) {
        ini_set('memory_limit', '256M');
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->findOneById($id_procedura);
        if (\is_null($procedura)) {
            throw new SfingeException('Procedura non trovata');
        }
        $gestore = $this->get("gestore_esportazione")->getGestore($procedura);
        $excel = $gestore->getReportVariazioni($tipologia);
        $excelWriter = $this->container->get('phpexcel')->createWriter($excel);

        $response = new StreamedResponse(function () use ($excelWriter) {
            $excelWriter->save('php://output');
        },
                \Symfony\Component\HttpFoundation\Response::HTTP_OK,
                [
                    'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
                    'Pragma' => 'public',
                    'Cache-Control' => 'maxage=1', ]
        );
        $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'report variazioni.xls'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route("/estrazione_rsi", name="estrazione_rsi_generale")
     */
    public function estrazioneRsiGeneraleAction() {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 600);

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Test result file");

        $em = $this->getEm();

        $campi = [
            'A-1' => 'principi_rsi.indice_rsi.trasparenza_stk_sott.trasparenza_stk_sott_2.rating',
            'A-2' => 'principi_rsi.indice_rsi.trasparenza_stk_sott.trasparenza_stk_sott_2.corruzione',
            'A-3' => 'principi_rsi.indice_rsi.trasparenza_stk_sott.trasparenza_stk_sott_2.sostenibile',
            'A-4' => 'principi_rsi.indice_rsi.trasparenza_stk_sott.trasparenza_stk_sott_2.prestazioni',
            'A-5' => 'principi_rsi.indice_rsi.trasparenza_stk_sott.trasparenza_stk_sott_2.coinvolgimento',
            'B-6' => 'principi_rsi.indice_rsi.benessere_sott.benessere_sott_2.accordi',
            'B-7' => 'principi_rsi.indice_rsi.benessere_sott.benessere_sott_2.lavoro',
            'B-8' => 'principi_rsi.indice_rsi.benessere_sott.benessere_sott_2.discriminazioni',
            'B-9' => 'principi_rsi.indice_rsi.benessere_sott.benessere_sott_2.discriminazioni_sess',
            'B-10' => 'principi_rsi.indice_rsi.benessere_sott.benessere_sott_2.conciliazione',
            'B-11' => 'principi_rsi.indice_rsi.benessere_sott.benessere_sott_2.welfare',
            'B-12' => 'principi_rsi.indice_rsi.benessere_sott.benessere_sott_2.coinvolgimento',
            'C-13' => 'principi_rsi.indice_rsi.clienti_consumatori_sott.clienti_consumatori_sott_2.iniziative_campagne',
            'C-14' => 'principi_rsi.indice_rsi.clienti_consumatori_sott.clienti_consumatori_sott_2.sostenibilita_ambiente',
            'C-15' => 'principi_rsi.indice_rsi.clienti_consumatori_sott.clienti_consumatori_sott_2.miglioramento_prodotti',
            'D-16' => 'principi_rsi.indice_rsi.green_prodotti_sott.green_prodotti_sott_2.ridurre_impatto',
            'D-17' => 'principi_rsi.indice_rsi.green_prodotti_sott.green_prodotti_sott_2.fornitori_green',
            'D-18' => 'principi_rsi.indice_rsi.green_prodotti_sott.green_prodotti_sott_2.mobilita_sostenibile',
            'D-19' => 'principi_rsi.indice_rsi.green_prodotti_sott.green_prodotti_sott_2.mobilita_merci',
            'D-20' => 'principi_rsi.indice_rsi.green_prodotti_sott.green_prodotti_sott_2.efficienza_energetica',
            'D-21' => 'principi_rsi.indice_rsi.green_prodotti_sott.green_prodotti_sott_2.efficienza_energetica_servizi',
            'E-22' => 'principi_rsi.indice_rsi.relazione_comunita_sott.relazione_comunita_sott_2.stage_tirocini',
            'E-23' => 'principi_rsi.indice_rsi.relazione_comunita_sott.relazione_comunita_sott_2.supporto_tecnico',
            'E-24' => 'principi_rsi.indice_rsi.relazione_comunita_sott.relazione_comunita_sott_2.supporto_tecnico_benessere',
            'E-25' => 'principi_rsi.indice_rsi.relazione_comunita_sott.relazione_comunita_sott_2.iniziative_dialogo',
            'E-26' => 'principi_rsi.indice_rsi.relazione_comunita_sott.relazione_comunita_sott_2.supporto_tecnico_qualificazione',
            'F-27' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.iso_14001',
            'F-28' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.emas_ue',
            'F-29' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.lca',
            'F-30' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.fsc',
            'F-31' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.pefc',
            'F-32' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.ecolabel',
            'F-33' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.iso_50001',
            'F-34' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.iso_14064',
            'F-35' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.sa8000',
            'F-36' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.oshas_18001',
            'F-37' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.b_corp',
            'F-38' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.denominazione_sb',
            'F-39' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.iso_37001',
            'F-40' => 'principi_rsi.indice_rsi.certificazioni_sott.certificazioni_sott_2.altra_certificazione',
        ];

        $istanzaFascicoloService = $this->container->get("fascicolo.istanza");

        //fetchare i pagamenti in stato inviato
        $pagamenti = $em->getRepository('AttuazioneControlloBundle\Entity\Pagamento')->getPagamentiInviati();

        $riga = 1;

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();
        $activeSheet->setTitle("Questionario RSI");

        $column = 0;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Procedura');
        ++$column;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Num. Richiesta');
        ++$column;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Protocollo');
        ++$column;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Denominazione Mandatario');
        ++$column;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Codice fiscale Mandatario');
        ++$column;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Partita iva Mandatario');
        ++$column;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Codice Ateco');
        ++$column;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Comune Sede Intervento');
        ++$column;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Provincia Sede Intervento');
        ++$column;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Indirizzo Sede Intervento');
        ++$column;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Numero sedi intervento');
        ++$column;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Email Impresa');
        ++$column;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Email istituzionale beneficiario');
        ++$column;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Telefono Impresa');

        foreach ($campi as $key => $value) {
            ++$column;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $key);
        }

        foreach ($pagamenti as $pagamento) {
            $procedura = $pagamento->getProcedura()->getId();

            if (7 == $procedura || 8 == $procedura || 32 == $procedura) {
                continue;
            }

            $phpExcelObject->setActiveSheetIndex(0);

            /* @var $richiesta Richiesta  */

            $richiesta = $pagamento->getRichiesta();
            $mandatario = $richiesta->getMandatario();
            $soggetto = $mandatario->getSoggettoVersion();
            $istanzaFascicolo = $pagamento->getIstanzaFascicolo();

            /**
             * concordato: se esiste la sede intervento(SedeOperativa) stampo i dati di quella, altrimenti stampo i dati della sede legale del mandatario
             * va valutato anche il flag $mandatario->getSedeLegaleComeOperativa()..se true vuol dire che la sede operativa è la sede legale
             */
            $mandatario = $pagamento->getRichiesta()->getMandatario();
            $sediIntervento = $mandatario->getSedi();

            $datiSede = new \stdClass();
            $datiSede->comune = null;
            $datiSede->provincia = null;
            $datiSede->via = null;
            $datiSede->numero = null;

            if (!$mandatario->getSedeLegaleComeOperativa() && count($sediIntervento) > 0) {
                $sedeIntervento = $sediIntervento->first();
                $indirizzo = $sedeIntervento->getSede()->getIndirizzo();
                $comune = $indirizzo->getComune();

                $datiSede->comune = $comune ? $comune->getDenominazione() : $indirizzo->getComuneEstero();
                $datiSede->provincia = $comune ? $comune->getProvincia()->getDenominazione() : $indirizzo->getProvinciaEstera();
                $datiSede->via = $indirizzo->getVia();
                $datiSede->numero = $indirizzo->getNumeroCivico();

                $numeroSediIntervento = count($sediIntervento);
            } else {
                $soggetto = $mandatario->getSoggetto();
                $comune = $soggetto->getComune();

                $datiSede->comune = $comune ? $comune->getDenominazione() : $soggetto->getComuneEstero();
                $datiSede->provincia = $comune ? $comune->getProvincia()->getDenominazione() : $soggetto->getProvinciaEstera();
                $datiSede->via = $soggetto->getVia();
                $datiSede->numero = $soggetto->getCivico();

                $numeroSediIntervento = 1;
            }

            ++$riga;

            $column = 0;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta->getProcedura()->getTitolo());

            ++$column;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $richiesta->getId());

            ++$column;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($richiesta->getProtocollo()) ? '-' : $richiesta->getProtocollo());

            ++$column;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getDenominazione()) ? '-' : $soggetto->getDenominazione());
            ++$column;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getCodiceFiscale()) ? '-' : $soggetto->getCodiceFiscale());
            ++$column;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getPartitaIva()) ? '-' : $soggetto->getPartitaIva());
            ++$column;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getCodiceAteco()) ? '-' : $soggetto->getCodiceAteco());
            ++$column;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($datiSede->comune) ? '-' : $datiSede->comune);
            ++$column;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($datiSede->provincia) ? '-' : $datiSede->provincia);
            ++$column;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($datiSede->via) ? '-' : $datiSede->via . ', ' . (is_null($datiSede->numero) ? '-' : $datiSede->numero));
            ++$column;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $numeroSediIntervento);
            ++$column;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getEmail()) ? '-' : $soggetto->getEmail());
            ++$column;
            $mail_ist = $istanzaFascicoloService->getOne($istanzaFascicolo, 'principi_rsi.indice_rsi.contatti_rsi.contatti_mail.mail_istituzionale_impresa');
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($mail_ist) ? '-' : $mail_ist);
            ++$column;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getTel()) ? '-' : $soggetto->getTel());

            foreach ($campi as $key => $value) {
                ++$column;
                $risposta = $istanzaFascicoloService->getOne($istanzaFascicolo, $value);
                $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, !is_null($risposta) ? $risposta : '-');
            }
        }

        $date = new \DateTime();

        $activeSheet->setTitle('Estrazione Completa');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'estrazione_rsi_generale_' . $date->getTimestamp() . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @Route("/estrazione_rsi_new", name="estrazione_rsi_generale_new")
     */
    public function estrazioneRsiGeneraleNewAction() {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', '-1');

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("SC")
                ->setLastModifiedBy("Schema31 S.p.A.")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Test result file");

        $em = $this->getEm();

        $campi_1 = [
            'A-1' => 'campo_personale_1',
            'A-2' => 'campo_personale_2',
            'A-3' => 'campo_personale_3',
            'A-4' => 'campo_personale_4',
            'A-5' => 'campo_personale_5',
            'A-6' => 'campo_personale_6',
            'B-1' => 'campo_1_1_1',
            'B-2' => 'campo_1_1_2',
            'B-3' => 'campo_1_1_3',
            'B-4' => 'campo_1_1_4',
            'B-5' => 'campo_1_1_5',
            'B-6' => 'campo_1_1_6',
            'B-7' => 'campo_1_1_7',
            'B-8' => 'campo_1_1_8',
            'B-9' => 'campo_1_1_9',
            'B-10' => 'campo_1_1_10',
            'B-11' => 'campo_1_1_11',
            'B-12' => 'campo_1_1_12',
            'C-1' => 'campo_1_2_1',
            'C-2' => 'campo_1_2_2',
            'C-3' => 'campo_1_2_3',
            'C-4' => 'campo_1_2_4',
            'C-5' => 'campo_1_2_5',
            'C-6' => 'campo_1_2_6',
            'C-7' => 'campo_1_2_7',
            'C-8' => 'campo_1_2_8',
            'D-1' => 'campo_2_1_1',
            'D-2' => 'campo_2_1_2',
            'D-3' => 'campo_2_1_3',
            'D-4' => 'campo_2_1_4',
            'D-5' => 'campo_2_1_5',
            'D-6' => 'campo_2_1_6',
            'D-7' => 'campo_2_1_7',
            'D-8' => 'campo_2_1_8',
            'E-1' => 'campo_3_1_1',
            'E-2' => 'campo_3_1_2',
            'E-3' => 'campo_3_1_3',
            'E-4' => 'campo_3_1_4',
            'E-5' => 'campo_3_1_5',
            'E-6' => 'campo_3_1_6',
            'E-7' => 'campo_3_1_7',
            'E-8' => 'campo_3_1_8',
            'E-9' => 'campo_3_1_9',
            'E-10' => 'campo_3_1_10',
            'E-11' => 'campo_3_1_11',
            'E-12' => 'campo_3_1_12',
            'E-13' => 'campo_3_1_13',
            'E-14' => 'campo_3_1_14',
            'E-15' => 'campo_3_1_15',
            'E-16' => 'campo_3_1_16',
            'E-17' => 'campo_3_1_17',
            'E-18' => 'campo_3_1_18',
            'F-1' => 'campo_4_1_1',
            'F-2' => 'campo_4_1_2',
            'F-3' => 'campo_4_1_3',
            'F-4' => 'campo_4_1_4',
            'F-5' => 'campo_4_1_5',
            'F-6' => 'campo_4_1_6',
            'F-7' => 'campo_4_1_7',
            'F-8' => 'campo_4_1_8',
            'F-9' => 'campo_4_1_9',
            'F-10' => 'campo_4_1_10',
            'F-11' => 'campo_4_1_11',
            'F-12' => 'campo_4_1_12',
            'F-13' => 'campo_4_1_13',
            'F-14' => 'campo_4_1_14',
            'F-15' => 'campo_4_1_15',
            'F-16' => 'campo_4_1_16',
            'F-17' => 'campo_4_1_17',
            'F-18' => 'campo_4_1_18',
            'F-19' => 'campo_4_1_19',
            'F-20' => 'campo_4_1_20',
            'F-21' => 'campo_4_1_21',
            'F-22' => 'campo_4_1_22',
            'F-23' => 'campo_4_1_23',
            'F-24' => 'campo_4_1_24',
            'F-25' => 'campo_4_1_25',
            'F-26' => 'campo_4_1_26',
            'F-27' => 'campo_4_1_27',
            'F-28' => 'campo_4_1_28',
            'F-29' => 'campo_4_1_29',
            'F-30' => 'campo_4_1_30',
            'F-31' => 'campo_4_1_31',
            'G-1' => 'campo_4_2_1',
            'G-2' => 'campo_4_2_2',
            'G-3' => 'campo_4_2_3',
            'G-4' => 'campo_4_2_4',
            'G-5' => 'campo_4_2_5',
            'G-6' => 'campo_4_2_6',
            'G-7' => 'campo_4_2_7',
            'G-8' => 'campo_4_2_8',
            'H-1' => 'campo_4_3_1',
            'H-2' => 'campo_4_3_2',
            'H-3' => 'campo_4_3_3',
            'H-4' => 'campo_4_3_4',
            'H-5' => 'campo_4_3_5',
            'H-6' => 'campo_4_3_6',
            'I-1' => 'campo_5_1_1',
            'I-2' => 'campo_5_1_2',
            'I-3' => 'campo_5_1_3',
            'I-4' => 'campo_5_1_4',
            'I-5' => 'campo_5_1_5',
            'I-6' => 'campo_5_1_6',
            'I-7' => 'campo_5_1_7',
            'I-8' => 'campo_5_1_8',
            'I-9' => 'campo_5_1_9',
            'I-10' => 'campo_5_1_10',
            'I-11' => 'campo_5_1_11',
            'L-1' => 'campo_5_2_1',
            'L-2' => 'campo_5_2_2',
            'L-3' => 'campo_5_2_3',
            'L-4' => 'campo_5_2_4',
            'L-5' => 'campo_5_2_5',
            'L-6' => 'campo_5_2_6',
            'M-1' => 'campo_5_3_1',
            'M-2' => 'campo_5_3_2',
            'M-3' => 'campo_5_3_3',
            'M-4' => 'campo_5_3_4',
            'M-5' => 'campo_5_3_5',
            'M-6' => 'campo_5_3_6',
            'M-7' => 'campo_5_3_7',
            'M-8' => 'campo_5_3_8',
            'M-9' => 'campo_5_3_9',
            'M-10' => 'campo_5_3_10',
            'M-11' => 'campo_5_3_11',
            'M-12' => 'campo_5_3_12',
            'M-13' => 'campo_5_3_13',
            'M-14' => 'campo_5_3_14',
            'M-15' => 'campo_5_3_15',
            'M-16' => 'campo_5_3_16',
        ];

        $campi_2 = [
            'N-1' => 'campo_5_4_1',
            'N-2' => 'campo_5_4_1',
            'N-3' => 'campo_5_4_1',
            'N-4' => 'campo_5_4_1',
            'N-5' => 'campo_5_4_1',
            'N-6' => 'campo_5_4_1',
            'N-7' => 'campo_5_4_1',
            'N-8' => 'campo_5_4_1',
            'N-9' => 'campo_5_4_1',
            'N-10' => 'campo_5_4_1',
            'N-11' => 'campo_5_4_1',
            'N-12' => 'campo_5_4_1',
            'N-13' => 'campo_5_4_1',];

        $campi_3 = ['N-14' => 'campo_5_4_2',];

        $campi_4 = [
            'O-1' => 'campo_5_5_1',
            'O-2' => 'campo_5_5_1',
            'O-3' => 'campo_5_5_1',
            'O-4' => 'campo_5_5_1',
            'O-5' => 'campo_5_5_1',
            'O-6' => 'campo_5_5_1',
            'O-7' => 'campo_5_5_1',
            'O-8' => 'campo_5_5_1',
            'O-9' => 'campo_5_5_1',
            'O-10' => 'campo_5_5_1',];

        $campi_5 = [
            'O-11' => 'campo_5_5_2',];

        $campi_6 = [
            'P-1' => 'campo_5_6_1',
            'P-2' => 'campo_5_6_1',
            'P-3' => 'campo_5_6_1',
            'P-4' => 'campo_5_6_1',
            'P-5' => 'campo_5_6_1',
            'P-6' => 'campo_5_6_1',
            'P-7' => 'campo_5_6_1',
            'P-8' => 'campo_5_6_1',
            'P-9' => 'campo_5_6_1',
            'P-10' => 'campo_5_6_1',
        ];

        $campi_7 = ['P-11' => 'campo_5_6_2',];

        $campi_8 = [
            'Q-1' => 'campo_6_1_1',
            'Q-2' => 'campo_6_1_2',
        ];

        $campi_nop = [
            'N-1' => 'Interventi strutturali per il risparmio energetico',
            'N-2' => 'Installazione di tecnologie per la fruizione di energia da fonti rinnovabili',
            'N-3' => 'Interventi per la riduzione di rifiuti e scarti di produzione',
            'N-4' => 'Attività di Ricerca & Sviluppo e interventi per l’innovazione tecnologica di processo e di prodotto',
            'N-5' => 'Interventi per l’ottimizzazione dei servizi di logistica e mobilità',
            'N-6' => 'Ottenimento certificazioni di processo o di prodotto',
            'N-7' => 'Ottenimento certificazione sistemi di gestione ambientale e/o sociale',
            'N-8' => 'Formazione personale su temi legati alla sostenibilità',
            'N-9' => 'Consulenze su temi di sostenibilità (CSR Temporary Manager, Energy Manager, Mobility Manager)',
            'N-10' => 'Interventi per la sostenibilità della filiera',
            'N-11' => 'Azioni di comunicazione per migliorare l’immagine dell’impresa su sostenibilità',
            'N-12' => 'Interventi di welfare aziendale',
            'N-13' => 'Servizio di tutoraggio aziendale permanente rivolto ai giovani per avvicinarli al mondo del lavoro e favorirne un loro inserimento qualificato',
            'O-1' => 'Incentivi pubblici a sostegno di investimenti delle imprese su azioni per la sostenibilità',
            'O-2' => 'Incentivi pubblici per potenziare la mobilità sostenibile di persone e merci',
            'O-3' => 'Contributi per interventi formativi e consulenze per migliorare le competenze interne per innovazione sostenibile',
            'O-4' => 'Incentivi pubblici per collaborazioni con università e enti di ricerca per progetti di innovazione sostenibile',
            'O-5' => 'Incentivi pubblici per progetti di reti di imprese per la sostenibilità delle filiere',
            'O-6' => 'Campagne di comunicazione, convegni e workshop per sensibilizzare consumatori e imprese sui temi della sostenibilità',
            'O-7' => 'Creazione di un marchio per aumentare la visibilità delle imprese virtuose presso i consumatori',
            'O-8' => 'Creazione di elenchi di merito e/o criteri premianti nei bandi pubblici per le imprese virtuose',
            'O-9' => 'Detrazioni fiscali/semplificazioni amministrative per le imprese virtuose',
            'O-10' => 'Appalti pubblici improntati al Green Pubblic Procurement',
            'P-1' => 'Costi di adeguamento di processi e di prodotto',
            'P-2' => 'Conflitto con altre priorità di investimento',
            'P-3' => 'Difficoltà a trovare clienti sensibili al tema',
            'P-4' => 'Aumento dei costi di produzione/servizio',
            'P-5' => 'Bassa redditività',
            'P-6' => 'Difficoltà a partecipare ai bandi di finanziamento pubblico',
            'P-7' => 'Alti costi di ricerca e sviluppo',
            'P-8' => 'Mancanza di un piano strategico sul tema',
            'P-9' => 'Mancanza di competenze interne all’azienda',
            'P-10' => 'Mancanza di commitment aziendale',
        ];

        //fetchare i pagamenti in stato inviato
        $pagamenti = $em->getRepository('AttuazioneControlloBundle\Entity\Pagamento')->getPagamentiInviatiPerQuestionarioRsi();

        $riga = 1;

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();
        $activeSheet->setTitle("Questionario RSI");

        $column = 0;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Procedura');
        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Num. Richiesta');
        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Protocollo');
        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Denominazione Mandatario');
        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Codice fiscale Mandatario');
        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Partita iva Mandatario');
        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Codice Ateco');
        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Comune Sede Intervento');
        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Provincia Sede Intervento');
        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Indirizzo Sede Intervento');
        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Numero sedi intervento');
        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Email Impresa');
        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Telefono Impresa');
        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, '0');

        $tutti_campi = array_merge($campi_1, $campi_2, $campi_3, $campi_4, $campi_5, $campi_6, $campi_7, $campi_8);

        foreach ($tutti_campi as $key => $value) {
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $key);
        }

        $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Data invio questionario');

        foreach ($pagamenti as $pagamento) {
            ++$riga;

            $column = 0;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['titolo_procedura']);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['richiesta_id']);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['protocollo']);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['denominazione']);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['codice_fiscale']);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['partita_iva']);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['codice_ateco']);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['comune']);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['provincia']);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['via'] . ', ' . $pagamento['numero']);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['count_sedi_intervento']);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['email']);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['tel']);

            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['questionario_compilato']);

            foreach ($campi_1 as $value) {
                $valore = '-';
                if ($pagamento[$value] != '-') {
                    $valore = str_replace('#', ',', $pagamento[$value]);
                }

                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $valore);
            }

            foreach ($campi_2 as $key => $value) {
                $valore = '-';

                if ($pagamento[$value] != '-') {
                    if (strstr($pagamento[$value], $campi_nop[$key])) {
                        $valore = 'Sì';
                    } else {
                        $valore = 'No';
                    }
                }

                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $valore);
            }

            foreach ($campi_3 as $value) {
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento[$value]);
            }

            foreach ($campi_4 as $key => $value) {
                $valore = '-';
                if ($pagamento[$value] != '-') {
                    if (strstr($pagamento[$value], $campi_nop[$key])) {
                        $valore = 'Sì';
                    } else {
                        $valore = 'No';
                    }
                }

                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $valore);
            }

            foreach ($campi_5 as $value) {
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento[$value]);
            }

            foreach ($campi_6 as $key => $value) {
                $valore = '-';
                if ($pagamento[$value] != '-') {
                    if (strstr($pagamento[$value], $campi_nop[$key])) {
                        $valore = 'Sì';
                    } else {
                        $valore = 'No';
                    }
                }

                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $valore);
            }

            foreach ($campi_7 as $value) {
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento[$value]);
            }

            foreach ($campi_8 as $value) {
                $valore = '-';
                if ($pagamento[$value] != '-') {
                    $valore = str_replace('#####', ',', $pagamento[$value]);
                }

                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $valore);
            }

            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['data_invio_pagamento']);
        }

        $date = new \DateTime();

        $activeSheet->setTitle('Estrazione RSI');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'estrazione_rsi_generale_' . $date->getTimestamp() . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @Route("/esporta_universo_ctrl_loco/{id_procedura}", name="esporta_universo_ctrl_loco")
     * @param mixed $id_procedura
     */
    public function estrazioneUniversoCtrlLocoAction($id_procedura) {
        ini_set('memory_limit', '1G');
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find($id_procedura);
        return $this->get("gestore_esportazione")->getGestore($procedura)->estrazioneControlliLoco(['bando' => $id_procedura]);
    }

    /**
     * @Route("/procedura_piano_costi/{id_procedura}/{finestra_temporale}", name="estrazione_richieste_procedura_piano_costi", defaults={"finestra_temporale" : 0})
     *
     * @param int $id_procedura
     * @param int $finestra_temporale
     * @return mixed
     * @throws Exception
     */
    public function estrazioneRichiestePianoCostiAction(int $id_procedura, int $finestra_temporale) {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 600);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find($id_procedura);

        $opzioni = [];
        $opzioni['finestra_temporale'] = $finestra_temporale > 0 ? $finestra_temporale : null;
        $opzioni['bando'] = $id_procedura;

        return $this->get("gestore_esportazione")->getGestore($procedura)->estrazioneRichiestePianoCosti($opzioni);
    }

    /**
     * @Route("/procedura_rna/{id_procedura}/{finestra_temporale}", name="estrazione_rna", defaults={"finestra_temporale" : 0})
     *
     * @param int $id_procedura
     * @param int $finestra_temporale
     * @return mixed
     * @throws Exception
     */
    public function estrazioneRnaAction(int $id_procedura, int $finestra_temporale) {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 900);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find($id_procedura);

        $opzioni = [];
        $opzioni['finestra_temporale'] = $finestra_temporale > 0 ? $finestra_temporale : null;
        $opzioni['bando'] = $id_procedura;

        return $this->get("gestore_esportazione")->getGestore($procedura)->estrazioneRna($opzioni);
    }

    /**
     * @Route("/estrazione_paesi_target/{id_procedura}", name="estrazione_paesi_target")
     *
     * @param int $id_procedura
     * @param int $finestra_temporale
     * @return mixed
     * @throws Exception
     */
    public function estrazionePaesiTargetAction(int $id_procedura) {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 900);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find($id_procedura);
        $finestra_temporale = 0;
        $opzioni = [];
        $opzioni['finestra_temporale'] = $finestra_temporale > 0 ? $finestra_temporale : null;
        $opzioni['bando'] = $id_procedura;

        return $this->get("gestore_esportazione")->getGestore($procedura)->estrazionePaesiTarget($opzioni);
    }

    /**
     * @Route("variazioni_generale/{id_procedura}", name="estrazione_variazioni_generale")
     * @param $id_procedura
     * @return Response
     * @throws SfingeException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function estrazioneVariazioniGeneraleAction($id_procedura): Response
    {
        ini_set('memory_limit', '256M');
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->findOneById($id_procedura);
        if (is_null($procedura)) {
            throw new SfingeException('Procedura non trovata');
        }

        /** @var SpreadsheetFactory $service */
        $service = $this->container->get('phpoffice.spreadsheet');

        /** @var Spreadsheet $phpExcelObject */
        $phpExcelObject = $service->getSpreadSheet();
        $phpExcelObject->setActiveSheetIndex(0);
        $sheet = $phpExcelObject->getActiveSheet();
        $sheet->setTitle("Variazioni");

        $riga = 1;
        $column = 1;
        $colonne = [];
        $lettera = 'A';
        while ($lettera !== 'AAAA') {
            $colonne[] = $lettera++;
        }

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Id domanda contributo');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(25);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Protocollo domanda contributo');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(30);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Soggetto');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(50);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Numero variazione');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(20);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Tipo variazione');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(25);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Data invio variazione');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(22);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Protocollo variazione');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(22);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Esito finale (ammessa/non ammessa)');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(36);

        $sheet->getStyle('A1:H1')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID,
            'color' => ['rgb' => 'FFFF99']], 'font' => ['bold' => true,]]);

        /** @var VariazioneRichiestaRepository $variazioneRepository */
        $variazioneRepository = $this->getEm()->getRepository('AttuazioneControlloBundle:VariazioneRichiesta');
        foreach ($variazioneRepository->iterateVariazioniGenerale($procedura) as $idx => $variazione) {
            $richiesta = $variazione->getAttuazioneControlloRichiesta()->getRichiesta();
            $sheet->fromArray(
                array(
                    $richiesta->getId(), //Numero Variazione
                    $richiesta->getProtocollo(), //Protocollo domanda contributo
                    $richiesta->getMandatario(), //Soggetto
                    $variazione->getId(), //Numero Variazione
                    $variazione->getTipo(), //Tipo Variazione
                    !is_null($variazione->getDataInvio()) ? $variazione->getDataInvio()->format('d-m-Y') : '-', //Data invio Variazione
                    $variazione->getProtocollo(), //Protocollo Variazione
                    $variazione->getEsitoString(), //Esito finale (ammessa/non ammessa)
                ), null, 'A' . ($idx + 2)
            );
        }

        $fileName = 'Report_variazioni_procedura_id_' . $id_procedura . '.xlsx';
        return $service->createResponse($phpExcelObject, $fileName);
    }

    /**
     * @Route("proroghe_generale/{id_procedura}", name="estrazione_proroghe_generale")
     * @param $id_procedura
     * @return Response
     * @throws SfingeException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function estrazioneProrogheGeneraleAction($id_procedura): Response
    {
        ini_set('memory_limit', '256M');
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->findOneById($id_procedura);
        if (is_null($procedura)) {
            throw new SfingeException('Procedura non trovata');
        }

        /** @var SpreadsheetFactory $service */
        $service = $this->container->get('phpoffice.spreadsheet');

        /** @var Spreadsheet $phpExcelObject */
        $phpExcelObject = $service->getSpreadSheet();
        $phpExcelObject->setActiveSheetIndex(0);
        $sheet = $phpExcelObject->getActiveSheet();
        $sheet->setTitle("Proroghe");

        $riga = 1;
        $column = 1;
        $colonne = [];
        $lettera = 'A';
        while ($lettera !== 'AAAA') {
            $colonne[] = $lettera++;
        }

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Id domanda contributo');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(25);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Protocollo domanda contributo');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(30);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Soggetto');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(50);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Numero proroga');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(18);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Data invio proroga');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(20);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Protocollo proroga');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(20);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Data inizio');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(15);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Data fine');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(15);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Data inizio approvata');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(22);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Data fine approvata');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(20);

        $sheet->setCellValueByColumnAndRow($column++, $riga, 'Esito finale (ammessa/non ammessa)');
        $sheet->getColumnDimension($colonne[$column - 2])->setWidth(36);

        $sheet->getStyle('A1:K1')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID,
            'color' => ['rgb' => 'FFFF99']], 'font' => ['bold' => true,]]);

        $prorogaRepository = $this->getEm()->getRepository('AttuazioneControlloBundle:Proroga');
        foreach ($prorogaRepository->iterateProroghe($procedura) as $idx => $proroga) {
            $richiesta = $proroga->getAttuazioneControlloRichiesta()->getRichiesta();
            $sheet->fromArray(
                [
                    $richiesta->getId(), //Numero proroga
                    $richiesta->getProtocollo(), //Protocollo domanda contributo
                    $richiesta->getMandatario(), //Soggetto
                    $proroga->getId(), //Numero proroga
                    $proroga->getDataInvio()->format('d-m-Y'), //Data invio proroga
                    $proroga->getProtocollo(), //Protocollo proroga
                    !is_null($proroga->getDataAvvioProgetto()) ? $proroga->getDataAvvioProgetto()->format('d-m-Y') : '-', //data avvio proroga
                    !is_null($proroga->getDataFineProgetto()) ? $proroga->getDataFineProgetto()->format('d-m-Y') : '-', //data fine proroga
                    !is_null($proroga->getDataAvvioApprovata()) ? $proroga->getDataAvvioApprovata()->format('d-m-Y') : '-', //data avvio proroga approvata
                    !is_null($proroga->getDataFineApprovata()) ? $proroga->getDataFineApprovata()->format('d-m-Y'): '-', //data fine proroga approvata
                    $proroga->getEsitoString() //Esito finale (ammessa/non ammessa)
                ], null, 'A' . ($idx + 2)
            );
        }

        $fileName = 'Report_proroghe_procedura_id_' . $id_procedura . '.xlsx';
        return $service->createResponse($phpExcelObject, $fileName);
    }

    /**
     * @Route("/procedura_dati_sap/{id_procedura}/{finestra_temporale}", name="estrazione_richieste_procedura_dati_sap", defaults={"finestra_temporale" : 0})
     * @param mixed $id_procedura
     * @param mixed $finestra_temporale
     * @return mixed
     * @throws Exception
     */
    public function estrazioneRichiesteDatiSapAction($id_procedura, $finestra_temporale)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 600);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->find($id_procedura);

        $opzioni = [];
        $opzioni['finestra_temporale'] = $finestra_temporale > 0 ? $finestra_temporale : null;
        $opzioni['bando'] = $id_procedura;
        $opzioni['con_annullate'] = false;

        return $this->get("gestore_esportazione")->getGestore($procedura)->estrazioneRichiesteDatiSap($opzioni);
    }

    /**
     * @Route("/estrazione_rsi_per_settore", name="estrazione_rsi_per_settore")
     */
    public function estrazioneRsiGeneralePerSettoreAction() {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', '-1');

        $em = $this->getEm();
        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("SC")
            ->setLastModifiedBy("Performer Srl");

        $campi_1 = [
            '0' => 'campo_caratterizzazione_azienda_1',
            '1' => 'campo_caratterizzazione_azienda_2',
            'A-1' => 'campo_personale_1',
            'A-2' => 'campo_personale_2',
            'A-3' => 'campo_personale_3',
            'A-4' => 'campo_personale_4',
            'A-5' => 'campo_personale_5',
            'A-6' => 'campo_personale_6',
            'B-1' => 'campo_1_1_1',
            'B-2' => 'campo_1_1_2',
            'B-3' => 'campo_1_1_3',
            'B-4' => 'campo_1_1_4',
            'B-5' => 'campo_1_1_5',
            'B-6' => 'campo_1_1_6',
            'B-7' => 'campo_1_1_7',
            'B-8' => 'campo_1_1_8',
            'B-9' => 'campo_1_1_9',
            'B-10' => 'campo_1_1_10',
            'B-11' => 'campo_1_1_11',
            'B-12' => 'campo_1_1_12',
            'B-13' => 'campo_1_1_13',
            'B-14' => 'campo_1_1_14',
            'B-15' => 'campo_1_1_15',
            'B-16' => 'campo_1_1_16',

            'C-1' => 'campo_1_2_1',
            'C-2' => 'campo_1_2_2',
            'C-3' => 'campo_1_2_3',
            'C-4' => 'campo_1_2_4',
            'C-5' => 'campo_1_2_5',
            'C-6' => 'campo_1_2_6',

            'D-1' => 'campo_2_1_1',
            'D-2' => 'campo_2_1_2',
            'D-3' => 'campo_2_1_3',
            'D-4' => 'campo_2_1_4',
            'D-5' => 'campo_2_1_5',
            'D-6' => 'campo_2_1_6',
            'D-7' => 'campo_2_1_7',
            'D-8' => 'campo_2_1_8',
            'D-9' => 'campo_2_1_9',
            'D-10' => 'campo_2_1_10',
            'D-11' => 'campo_2_1_11',
            'D-12' => 'campo_2_1_12',
            'D-13' => 'campo_2_1_13',

            'E-1' => 'campo_3_1_1',
            'E-2' => 'campo_3_1_2',
            'E-3' => 'campo_3_1_3',
            'E-4' => 'campo_3_1_4',
            'E-5' => 'campo_3_1_5',
            'E-6' => 'campo_3_1_6',
            'E-7' => 'campo_3_1_7',
            'E-8' => 'campo_3_1_8',
            'E-9' => 'campo_3_1_9',
            'E-10' => 'campo_3_1_10',
            'E-11' => 'campo_3_1_11',
            'E-12' => 'campo_3_1_12',
            'E-13' => 'campo_3_1_13',
            'E-14' => 'campo_3_1_14',
            'E-15' => 'campo_3_1_15',
            'E-16' => 'campo_3_1_16',
            'E-17' => 'campo_3_1_17',
            'E-18' => 'campo_3_1_18',

            'F-1' => 'campo_4_1_1',
            'F-2' => 'campo_4_1_2',
            'F-3' => 'campo_4_1_3',
            'F-4' => 'campo_4_1_4',
            'F-5' => 'campo_4_1_5',
            'F-6' => 'campo_4_1_6',
            'F-7' => 'campo_4_1_7',
            'F-8' => 'campo_4_1_8',
            'F-9' => 'campo_4_1_9',
            'F-10' => 'campo_4_1_10',
            'F-11' => 'campo_4_1_11',
            'F-12' => 'campo_4_1_12',
            'F-13' => 'campo_4_1_13',

            'G-1' => 'campo_4_2_1',
            'G-2' => 'campo_4_2_2',
            'G-3' => 'campo_4_2_3',
            'G-4' => 'campo_4_2_4',
            'G-5' => 'campo_4_2_5',
            'G-6' => 'campo_4_2_6',
            'G-7' => 'campo_4_2_7',

            'H-1' => 'campo_4_3_1',
            'H-2' => 'campo_4_3_2',
            'H-3' => 'campo_4_3_3',
            'H-4' => 'campo_4_3_4',
            'H-5' => 'campo_4_3_5',
            'H-6' => 'campo_4_3_6',
            'H-7' => 'campo_4_3_7',
            'H-8' => 'campo_4_3_8',
            'H-9' => 'campo_4_3_9',
            'H-10' => 'campo_4_3_10',
            'H-11' => 'campo_4_3_11',

            'I-1' => 'campo_4_4_1',
            'I-2' => 'campo_4_4_2',
            'I-3' => 'campo_4_4_3',
            'I-4' => 'campo_4_4_4',
            'I-5' => 'campo_4_4_5',
            'I-6' => 'campo_4_4_6',
            'I-7' => 'campo_4_4_7',

            'L-1' => 'campo_5_1_1',
            'L-2' => 'campo_5_1_2',
            'L-3' => 'campo_5_1_3',
            'L-4' => 'campo_5_1_4',
            'L-5' => 'campo_5_1_5',
            'L-6' => 'campo_5_1_6',
            'L-7' => 'campo_5_1_7',
            'L-8' => 'campo_5_1_8',
            'L-9' => 'campo_5_1_9',
            'L-10' => 'campo_5_1_10',

            'M-1' => 'campo_6_1_1',
            'M-2' => 'campo_6_1_2',
            'M-3' => 'campo_6_1_3',
            'M-4' => 'campo_6_1_4',
            'M-5' => 'campo_6_1_5',
            'M-6' => 'campo_6_1_6',

            'N-1' => 'campo_7_1_1',
            'N-2' => 'campo_7_1_2',
            'N-3' => 'campo_7_1_3',
            'N-4' => 'campo_7_1_4',
            'N-5' => 'campo_7_1_5',
            'N-6' => 'campo_7_1_6',
            'N-7' => 'campo_7_1_7',
            'N-8' => 'campo_7_1_8',
            'N-9' => 'campo_7_1_9',
            'N-10' => 'campo_7_1_10',
            'N-11' => 'campo_7_1_11',
            'N-12' => 'campo_7_1_12',
        ];

        $campi_8_1_1 = [
            'O-1' => 'campo_8_1_1',
            'O-2' => 'campo_8_1_1',
            'O-3' => 'campo_8_1_1',
            'O-4' => 'campo_8_1_1',
            'O-5' => 'campo_8_1_1',
            'O-6' => 'campo_8_1_1',
        ];

        $campi_8_1_1_testi = [
            'O-1' => 'Sensibilità etica/preoccupazione rispetto agli impatti dei cambiamenti climatici',
            'O-2' => 'Strategia competitiva/richiesta dei clienti/consumatori/ accesso a nuovi mercati',
            'O-3' => 'Spinta normativa',
            'O-4' => 'Accesso a incentivi pubblici',
            'O-5' => 'Risparmio nei costi di gestione',
            'O-6' => 'Altro (specificare)',
        ];

        $campi_8_1_2 = [
            'O-7' => 'campo_8_1_2',
        ];

        $campi_8_2_1 = [
            'P-1' => 'campo_8_2_1',
            'P-2' => 'campo_8_2_1',
            'P-3' => 'campo_8_2_1',
            'P-4' => 'campo_8_2_1',
            'P-5' => 'campo_8_2_1',
            'P-6' => 'campo_8_2_1',
            'P-7' => 'campo_8_2_1',
            'P-8' => 'campo_8_2_1',
            'P-9' => 'campo_8_2_1',
            'P-10' => 'campo_8_2_1',
            'P-11' => 'campo_8_2_1',
            'P-12' => 'campo_8_2_1',
            'P-13' => 'campo_8_2_1',
            'P-14' => 'campo_8_2_1',
            'P-15' => 'campo_8_2_1',
        ];

        $campi_8_2_1_testi = [
            'P-1' => 'Energia',
            'P-2' => 'Economia circolare',
            'P-3' => 'Clima',
            'P-4' => 'Blue growth',
            'P-5' => 'Materiali',
            'P-6' => 'Digital',
            'P-7' => 'Manufact 4.0',
            'P-8' => 'Connettività',
            'P-9' => 'Mobilità',
            'P-10' => 'Città',
            'P-11' => 'Beni culturali',
            'P-12' => 'Nutrizione',
            'P-13' => 'Salute',
            'P-14' => 'Innovazione sociale',
            'P-15' => 'Inclusione',
        ];

        $campi_8_3 = [
            'Q-1' => 'campo_8_3_1',
            'Q-2' => 'campo_8_3_2',
            'Q-3' => 'campo_8_3_3',
            'Q-4' => 'campo_8_3_4',
            'Q-5' => 'campo_8_3_5',
            'Q-6' => 'campo_8_3_6',
            'Q-7' => 'campo_8_3_7',
            'Q-8' => 'campo_8_3_8',
        ];

        $campi_8_4 = [
            'R-1' => 'campo_8_4_1',
            'R-2' => 'campo_8_4_2',
            'R-3' => 'campo_8_4_3',
            'R-4' => 'campo_8_4_4',
            'R-5' => 'campo_8_4_5',
            'R-6' => 'campo_8_4_6',
            'R-7' => 'campo_8_4_7',
            'R-8' => 'campo_8_4_8',
        ];

        $campi_9_1 = [
            'S-1' => 'campo_9_1_1',
            'S-2' => 'campo_9_1_2',
        ];

        $campi_da_escludere_per_settore_imprese_di_servizi = [
            'campo_1_1_6', 'campo_1_2_5',
            'campo_2_1_1', 'campo_2_1_4', 'campo_2_1_7',
            'campo_4_1_6', 'campo_4_1_7', 'campo_4_1_10', 'campo_4_1_11', 'campo_4_1_12',
            'campo_4_2_4', 'campo_4_2_5', 'campo_4_2_6',
            'campo_4_3_4', 'campo_4_3_5', 'campo_4_3_7', 'campo_4_3_8',
            'campo_4_4_3', 'campo_4_4_4', 'campo_4_4_6',
            'campo_5_1_4',
            'campo_7_1_3',
            'campo_8_3_1', 'campo_8_4_1',
        ];

        $settori = ['imprese_manifatturiere', 'imprese_di_servizi'];
        $sheet = -1;

        $colonne = [];
        $lettera = 'A';
        while ($lettera !== 'AAAA') {
            $colonne[] = $lettera++;
        }

        foreach ($settori as $settore) {
            $pagamenti = $em->getRepository('AttuazioneControlloBundle\Entity\Pagamento')->getPagamentiInviatiPerQuestionarioRsiPerSettore($settore);
            $riga = 1;

            if ($sheet > -1) {
                $phpExcelObject->createSheet();
            }

            $phpExcelObject->setActiveSheetIndex(++$sheet);
            $activeSheet = $phpExcelObject->getActiveSheet();
            $activeSheet->setTitle(ucfirst(str_replace('_', ' ', $settore)));

            $column = 0;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Procedura');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(50);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Num. Richiesta');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(15);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Protocollo');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(18);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Denominazione Mandatario');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(30);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Codice fiscale Mandatario');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(25);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Partita iva Mandatario');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(25);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Codice Ateco');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(30);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Comune Sede Intervento');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(30);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Provincia Sede Intervento');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(30);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Indirizzo Sede Intervento');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(30);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Numero sedi intervento');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(30);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Email Impresa');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(30);
            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Telefono Impresa');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(25);

            $tutti_campi = array_merge($campi_1, $campi_8_1_1, $campi_8_1_2, $campi_8_2_1, $campi_8_3, $campi_8_4, $campi_9_1);

            if ($settore == 'imprese_di_servizi') {
                foreach ($tutti_campi as $key => $campo) {
                    if (in_array($campo, $campi_da_escludere_per_settore_imprese_di_servizi)) {
                        unset($tutti_campi[$key]);
                    }
                }

                foreach ($campi_1 as $key => $campo) {
                    if (in_array($campo, $campi_da_escludere_per_settore_imprese_di_servizi)) {
                        unset($campi_1[$key]);
                    }
                }

                foreach ($campi_8_1_1 as $key => $campo) {
                    if (in_array($campo, $campi_da_escludere_per_settore_imprese_di_servizi)) {
                        unset($campi_8_1_1[$key]);
                    }
                }

                foreach ($campi_8_1_2 as $key => $campo) {
                    if (in_array($campo, $campi_da_escludere_per_settore_imprese_di_servizi)) {
                        unset($campi_8_1_2[$key]);
                    }
                }

                foreach ($campi_8_2_1 as $key => $campo) {
                    if (in_array($campo, $campi_da_escludere_per_settore_imprese_di_servizi)) {
                        unset($campi_8_2_1[$key]);
                    }
                }

                foreach ($campi_8_3 as $key => $campo) {
                    if (in_array($campo, $campi_da_escludere_per_settore_imprese_di_servizi)) {
                        unset($campi_8_3[$key]);
                    }
                }

                foreach ($campi_8_4 as $key => $campo) {
                    if (in_array($campo, $campi_da_escludere_per_settore_imprese_di_servizi)) {
                        unset($campi_8_4[$key]);
                    }
                }

                foreach ($campi_9_1 as $key => $campo) {
                    if (in_array($campo, $campi_da_escludere_per_settore_imprese_di_servizi)) {
                        unset($campi_9_1[$key]);
                    }
                }
            }

            foreach ($tutti_campi as $key => $value) {
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $key);
                $activeSheet->getColumnDimension($colonne[$column])->setWidth(19);
            }

            $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, 'Data invio questionario');
            $activeSheet->getColumnDimension($colonne[$column])->setWidth(22);

            foreach ($pagamenti as $pagamento) {
                ++$riga;

                $column = 0;
                $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['titolo_procedura']);
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['richiesta_id']);
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['protocollo']);
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['denominazione']);
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['codice_fiscale']);
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['partita_iva']);
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['codice_ateco']);
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['comune']);
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['provincia']);
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['via'] . ', ' . $pagamento['numero']);
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['count_sedi_intervento']);
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['email']);
                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['tel']);

                foreach ($campi_1 as $value) {
                    $valore = '-';
                    if ($pagamento[$value] != '-') {
                        $valore = str_replace('#', ',', $pagamento[$value]);
                    }

                    $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $valore);
                }

                foreach ($campi_8_1_1 as $key => $value) {
                    $valore = '-';

                    if ($pagamento[$value] != '-') {
                        if (strstr($pagamento[$value], $campi_8_1_1_testi[$key])) {
                            $valore = 'Sì';
                        } else {
                            $valore = 'No';
                        }
                    }

                    $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $valore);
                }

                foreach ($campi_8_1_2 as $value) {
                    $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento[$value]);
                }

                foreach ($campi_8_2_1 as $key => $value) {
                    $valore = '-';

                    if ($pagamento[$value] != '-') {
                        if (strstr($pagamento[$value], $campi_8_2_1_testi[$key])) {
                            $valore = 'Sì';
                        } else {
                            $valore = 'No';
                        }
                    }

                    $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $valore);
                }

                foreach ($campi_8_3 as $value) {
                    $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento[$value]);
                }

                foreach ($campi_8_4 as $value) {
                    $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento[$value]);
                }

                foreach ($campi_9_1 as $value) {
                    $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento[$value]);
                }

                $activeSheet->setCellValueExplicitByColumnAndRow(++$column, $riga, $pagamento['data_invio_pagamento']);
            }
        }

        $date = new DateTime();

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'estrazione_rsi_generale_per_settore_' . $date->getTimestamp() . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}
