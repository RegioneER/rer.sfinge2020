<?php

namespace CertificazioniBundle\Controller;

use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use CertificazioniBundle\Entity\StatoCertificazione;
use DocumentoBundle\Entity\DocumentoFile;
use CertificazioniBundle\Entity\DocumentoCertificazione;
use CertificazioniBundle\Entity\DocumentoCertificazionePagamento;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use DocumentoBundle\Component\ResponseException;

/**
 * @Route("/consultazione")
 */
class CertificazioniController extends BaseController {

    /**
     * @Route("/elenco_pagamenti/{sort}/{direction}/{page}", defaults={"sort" = "i.id", "direction" = "asc", "page" = "1"}, name="elenco_certificazione_pagamenti")
     * @PaginaInfo(titolo="Elenco pagamenti in istruttoria",sottoTitolo="mostra l'elenco dei pagamenti richiesti")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco pagamenti")})
     */
    public function elencoPagamentiAction() {

        //ini_set('memory_limit','512M');

        $datiRicerca = new \AttuazioneControlloBundle\Form\Entity\Istruttoria\RicercaPagamenti();
        $datiRicerca->setUtente($this->getUser());

        $em = $this->getEm();
        $istruttori = $em->getRepository("SfingeBundle\Entity\Utente")->cercaIstruttoriAtc();
        $datiRicerca->setIstruttori($istruttori);

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return $this->render('CertificazioniBundle:Attuazione:elencoPagamenti.html.twig', array('risultati' => $risultato["risultato"], "formRicerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]));
    }

    /**
     * @Route("/elenco_pagamenti_pulisci", name="elenco_certificazione_pagamenti_pulisci")
     */
    public function elencoCertificazionePagamentiPulisciAction() {
        $this->get("ricerca")->pulisci(new \AttuazioneControlloBundle\Form\Entity\Istruttoria\RicercaPagamenti());
        return $this->redirectToRoute("elenco_certificazione_pagamenti");
    }

    /**
     * @Route("/elenco_operazioni/{sort}/{direction}/{page}", defaults={"sort" = "a.id", "direction" = "asc", "page" = "1"}, name="elenco_gestione_certificazione")
     * @Template()
     * @PaginaInfo(titolo="Elenco operazioni in attuazione",sottoTitolo="mostra l'elenco delle operazioni in attuazione e controllo")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione operazioni")})
     */
    public function elencoRichiesteAction() {
        $datiRicerca = new \AttuazioneControlloBundle\Form\Entity\RicercaAttuazione();
        $datiRicerca->setUtente($this->getUser());

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return $this->render('CertificazioniBundle:Attuazione:elencoRichieste.html.twig', array('richieste' => $risultato["risultato"], "formRicercaIstruttoria" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]));
    }

    /**
     * @Route("/elenco_attuazione_pulisci", name="elenco_gestione_certificazione_pulisci")
     */
    public function elencoAttuazionePulisciAction() {
        $this->get("ricerca")->pulisci(new \AttuazioneControlloBundle\Form\Entity\RicercaAttuazione());
        return $this->redirectToRoute("elenco_gestione_certificazione");
    }

    /**
     * @Route("/elenco_certificazioni", name="elenco_certificazioni")
     * @PaginaInfo(titolo="Elenco certificazioni",sottoTitolo="mostra l'elenco delle certificazioni")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni")})
     */
    public function elencoCerticazioniAction() {
        $certificazioni = $this->getEm()->getRepository("CertificazioniBundle\Entity\Certificazione")->findAll();
        $chiusure = $this->getEm()->getRepository("CertificazioniBundle\Entity\CertificazioneChiusura")->findAll();

        $assi = array();
        if ($this->isGranted("ROLE_CERTIFICATORE_ASSE")) {
            foreach ($certificazioni as $certificazione) {
                if ($certificazione->isValidabile()) {
                    $assi = $this->getEm()->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->getAssiCertificazioneUtenteCompleta($certificazione->getId(), $this->getUser()->getId());
                    //$assi = $this->getEm()->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->getAssiCertificazioneUtente($certificazione->getId(), $this->getUser()->getId());
                    break;
                }
            }
        }

        return $this->render('CertificazioniBundle:Certificazioni:elencoCertificazioni.html.twig', array('certificazioni' => $certificazioni, 'chiusure' => $chiusure, 'assi' => $assi));
    }

    /**
     * @Route("/{id_certificazione}/elenco_pagamenti_certificati/{sort}/{direction}/{page}", defaults={"sort" = "pag.id", "direction" = "asc", "page" = "1"}, name="elenco_pagamenti_certificati")
     * @PaginaInfo(titolo="Elenco Pagamenti",sottoTitolo="elenco pagamenti")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Elenco pagamenti")})
     */
    public function elencoPagamentiCertificatiAction($id_certificazione) {

        $certificazione = $this->getEm()->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);
        $ex = $this->getCurrentRequest();
        if ($this->getCurrentRequest()->query->has('external')) {
            $this->get("ricerca")->pulisci(new \CertificazioniBundle\Form\Entity\RicercaPagamentiCertificati());
            return $this->redirect($this->generateUrl('elenco_pagamenti_certificati', array('id_certificazione' => $id_certificazione)));
        }

        $datiRicerca = new \CertificazioniBundle\Form\Entity\RicercaPagamentiCertificati();
        $datiRicerca->setConsentiRicercaVuota(false);
        $datiRicerca->setCertificazione($certificazione);

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        $dati = array('risultati' => $risultato["risultato"], "formRicerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]);
        $dati["certificazione"] = $certificazione;

        return $this->render("CertificazioniBundle:Certificazioni:elencoPagamenti.html.twig", $dati);
    }

    /**
     * @Route("/{id_certificazione}/elenco_pagamenti_pulisci", name="elenco_pagamenti_pulisci")
     */
    public function elencoPagamentiPulisciAction($id_certificazione) {
        $this->get("ricerca")->pulisci(new \CertificazioniBundle\Form\Entity\RicercaPagamentiCertificati());
        return $this->redirectToRoute("elenco_pagamenti_certificati", array("id_certificazione" => $id_certificazione));
    }

    /**
     * @Route("/{id_certificazione}/dettaglio", name="dettaglio_certificazione")
     * @PaginaInfo(titolo="Dettaglio certificazione",sottoTitolo="pagina di dettaglio della certificazione")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Dettaglio certificazione")})
     */
    public function dettaglioCertificazioneAction($id_certificazione) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documento_certificazione = new DocumentoCertificazione();
        $documento_file = new DocumentoFile();

        $certificazione = $em->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);

        $documenti_caricati_certificatore_agrea = $em->getRepository("CertificazioniBundle\Entity\DocumentoCertificazione")->findDocumentiCaricatiAgrea($id_certificazione);

        $documenti_caricati_certificatore = $em->getRepository("CertificazioniBundle\Entity\DocumentoCertificazione")->findDocumentiCaricati($id_certificazione, 'DOC_VALIDA_CERT');

        // Form per caricamento documento certificatore agrea
        $arrayTipi = array("CHECKLIST_CERT", "RELAZIONE_CERT", "DOMANDA_PAG_CERT", "ALTRO_CERT");

        $opzioni_form_agrea["lista_tipi"] = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByCodice($arrayTipi);
        $form_upload_agrea = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form_agrea);
        $form_upload_agrea->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Carica'));
        $form_upload_agrea_view = $form_upload_agrea->createView();

        // Form per caricamento documento validazione	
        $tipo_doc_validazione = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array('codice' => "DOC_VALIDA_CERT"));
        $form_upload_validazione = $this->createForm("DocumentoBundle\Form\Type\DocumentoFileType", $documento_file, array('tipo' => $tipo_doc_validazione));
        $form_upload_validazione->add("submit", "Symfony\Component\Form\Extension\Core\Type\SubmitType", array("label" => "Salva"));
        $form_upload_validazione_view = $form_upload_validazione->createView();

        if ($request->isMethod("POST")) {

            $form = $form_upload_agrea->isSubmitted() ? $form_upload_agrea : $form_upload_validazione;

            $form->handleRequest($request);
            if ($form->isValid()) {
                try {

                    $this->container->get("documenti")->carica($documento_file, 0);

                    $documento_certificazione->setDocumentoFile($documento_file);
                    $documento_certificazione->setCertificazione($certificazione);
                    $em->persist($documento_certificazione);

                    $em->flush();

                    return $this->addSuccessRedirect("Documento caricato correttamente", "dettaglio_certificazione", array("id_certificazione" => $id_certificazione));
                } catch (ResponseException $e) {
                    $this->addFlash("error", $e->getMessage());
                }
            }
        }

        $dati = array(
            'certificazione' => $certificazione,
            'stato_certificazione' => $certificazione->getStato()->getCodice(),
            'documenti_cert_agrea' => $documenti_caricati_certificatore_agrea,
            'documenti_cert' => $documenti_caricati_certificatore,
            'form_upload_validazione_view' => $form_upload_validazione_view,
            'form_upload_agrea_view' => $form_upload_agrea_view,
            'importi_asse' => $this->riepilogoPagamentiPerAsse($id_certificazione)
        );

        return $this->render('CertificazioniBundle:Certificazioni:dettaglioCertificazione.html.twig', $dati);
    }

    /**
     * @Route("/suddivisioni_certificazioni/{id_certificazione}", name="suddivisioni_certificazioni")
     * @PaginaInfo(titolo="Suddivisione spesa",sottoTitolo="Appendici")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Suddivisione spese")})
     */
    public function appendiceSuddivisioniSpesaAction($id_certificazione) {

        $em = $this->getEm();
        $certificazioni = $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getCertificazioniApprovate();
        $certificazione = $em->getRepository('CertificazioniBundle\Entity\Certificazione')->findOneById($id_certificazione);

        $array_certificazioni = array();
        $array_certificazioni[] = $certificazione;

        $anno_contabile = $certificazione->getAnnoContabile();

        foreach ($certificazioni as $certificazione_out) {
            if ($certificazione_out->getId() != $certificazione->getId() && $certificazione_out->getId() < $certificazione->getId()) {
                $array_certificazioni[] = $certificazione_out;
            }
        }

        $res = array();
        $res['A1'] = 0.00;
        $res['A2'] = 0.00;
        $res['A3'] = 0.00;
        $res['A4'] = 0.00;
        $res['A5'] = 0.00;
        $res['A6'] = 0.00;
        $res['A7'] = 0.00;
        $res['TOTALE'] = 0.00;
        foreach ($array_certificazioni as $certificazione_fin) {
            $res['A1'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAnnoContabile($certificazione_fin->getId(), 'A1', $anno_contabile);
            $res['A2'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAnnoContabile($certificazione_fin->getId(), 'A2', $anno_contabile);
            $res['A3'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAnnoContabile($certificazione_fin->getId(), 'A3', $anno_contabile);
            $res['A4'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAnnoContabile($certificazione_fin->getId(), 'A4', $anno_contabile);
            $res['A5'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAnnoContabile($certificazione_fin->getId(), 'A5', $anno_contabile);
            $res['A6'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAnnoContabile($certificazione_fin->getId(), 'A6', $anno_contabile);
            $res['A7'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAnnoContabile($certificazione_fin->getId(), 'A7', $anno_contabile);
        }
        $res['TOTALE'] = $res['A1'] + $res['A2'] + $res['A3'] + $res['A4'] + $res['A5'] + $res['A6'] + $res['A7'];
        $dati["certificazione"] = $certificazione;
        $dati["menu"] = 'suddivisione';
        $dati["importi_asse"] = $res;

        return $this->render("CertificazioniBundle:Appendici:suddivisioneSpesa.html.twig", $dati);
    }

    /**
     * @Route("/appendice_certificazione_1/{id_certificazione}", name="appendice_certificazione_1")
     * @PaginaInfo(titolo="Appendice 1",sottoTitolo="Appendici")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Strumenti finanziari")})
     */
    public function contributiStrumentiFinanziariAction($id_certificazione) {

        $em = $this->getEm();
        $certificazioni = $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getCertificazioniApprovate();
        $certificazione = $em->getRepository('CertificazioniBundle\Entity\Certificazione')->findOneById($id_certificazione);

        $array_certificazioni = array();
        $array_certificazioni[] = $certificazione;

        foreach ($certificazioni as $certificazione_out) {
            if ($certificazione_out->getId() != $certificazione->getId() && $certificazione_out->getId() < $certificazione->getId()) {
                $array_certificazioni[] = $certificazione_out;
            }
        }

        $res = array();
        $res['A1'] = 0.00;
        $res['A2'] = 0.00;
        $res['A3'] = 0.00;
        $res['A4'] = 0.00;
        $res['A5'] = 0.00;
        $res['A6'] = 0.00;
        $res['A7'] = 0.00;
        $res['TOTALE'] = 0.00;
        $res['IMPORTO_STR_TOTALE'] = 0.0;

        foreach ($array_certificazioni as $certificazione_fin) {
            $res['A1'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseStrumentiFinanziari($certificazione_fin->getId(), 'A1');
            $res['A2'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseStrumentiFinanziari($certificazione_fin->getId(), 'A2');
            $res['A3'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseStrumentiFinanziari($certificazione_fin->getId(), 'A3');
            $res['A4'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseStrumentiFinanziari($certificazione_fin->getId(), 'A4');
            $res['A5'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseStrumentiFinanziari($certificazione_fin->getId(), 'A5');
            $res['A6'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseStrumentiFinanziari($certificazione_fin->getId(), 'A6');
            $res['A7'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseStrumentiFinanziari($certificazione_fin->getId(), 'A7');
        }

        foreach ($certificazione->getCertificazioniAssi() as $certificazioneAsse) {
            $res['IMPORTO_STR_' . $certificazioneAsse->getAsse()->getCodice()] = $certificazioneAsse->getImportoStrumenti();
            $res['IMPORTO_STR_TOTALE'] += $certificazioneAsse->getImportoStrumenti();
        }

        $res['TOTALE'] = $res['A1'] + $res['A2'] + $res['A3'] + $res['A4'] + $res['A5'] + $res['A6'] + $res['A7'];
        $dati["certificazione"] = $certificazione;
        $dati["menu"] = 'suddivisione';
        $dati["importi_asse"] = $res;

        return $this->render("CertificazioniBundle:Appendici:appendiceCertificazioni1.html.twig", $dati);
    }

    /**
     * @Route("/appendice_certificazione_2/{id_certificazione}", name="appendice_certificazione_2")
     * @PaginaInfo(titolo="Appendice 2",sottoTitolo="Appendici")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Strumenti finanziari")})
     */
    public function contributiAiutiDiStatoAction($id_certificazione) {

        $em = $this->getEm();
        $certificazioni = $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getCertificazioniApprovate();
        $certificazione = $em->getRepository('CertificazioniBundle\Entity\Certificazione')->findOneById($id_certificazione);

        $array_certificazioni = array();
        $array_certificazioni[] = $certificazione;

        foreach ($certificazioni as $certificazione_out) {
            if ($certificazione_out->getId() != $certificazione->getId() && $certificazione_out->getId() < $certificazione->getId()) {
                $array_certificazioni[] = $certificazione_out;
            }
        }

        $res = array();
        $res['A1'] = 0.00;
        $res['A2'] = 0.00;
        $res['A3'] = 0.00;
        $res['A4'] = 0.00;
        $res['A5'] = 0.00;
        $res['A6'] = 0.00;
        $res['A7'] = 0.00;
        $res['TOTALE'] = 0.00;
        foreach ($array_certificazioni as $certificazione_fin) {
            $res['A1'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAiutiStato($certificazione_fin->getId(), 'A1');
            $res['A2'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAiutiStato($certificazione_fin->getId(), 'A2');
            $res['A3'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAiutiStato($certificazione_fin->getId(), 'A3');
            $res['A4'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAiutiStato($certificazione_fin->getId(), 'A4');
            $res['A5'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAiutiStato($certificazione_fin->getId(), 'A5');
            $res['A6'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAiutiStato($certificazione_fin->getId(), 'A6');
            $res['A7'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsseAiutiStato($certificazione_fin->getId(), 'A7');
        }
        $res['TOTALE'] = $res['A1'] + $res['A2'] + $res['A3'] + $res['A4'] + $res['A5'] + $res['A6'] + $res['A7'];
        $dati["certificazione"] = $certificazione;
        $dati["menu"] = 'suddivisione';
        $dati["importi_asse"] = $res;

        return $this->render("CertificazioniBundle:Appendici:appendiceCertificazioni2.html.twig", $dati);
    }

    public function riepilogoPagamentiPerAsse($id_certificazione) {
        $em = $this->getEm();

        $res = array();
        $res['A1'] = 0.00;
        $res['A2'] = 0.00;
        $res['A3'] = 0.00;
        $res['A4'] = 0.00;
        $res['A5'] = 0.00;
        $res['A6'] = 0.00;
        $res['A7'] = 0.00;
        $res['TOTALE'] = 0.00;
        $res['A1'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A1');
        $res['A2'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A2');
        $res['A3'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A3');
        $res['A4'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A4');
        $res['A5'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A5');
        $res['A6'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A6');
        $res['A7'] += $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getImportiCertificatiPerAsse($id_certificazione, 'A7');
        $res['TOTALE'] = $res['A1'] + $res['A2'] + $res['A3'] + $res['A4'] + $res['A5'] + $res['A6'] + $res['A7'];
        return $res;
    }

    /**
     * @Route("/{id_certificazione}/estrazione_pagamenti", name="estrazione_pagamenti")
     */
    public function estrazionePagamentiCertificazioneAction($id_certificazione) {
        $pagamenti = $this->getEm()->getRepository("CertificazioniBundle\Entity\Certificazione")->getPagamentiCertificati($id_certificazione);
        $certificazione = $this->getEm()->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Sfinge 2104-2020")
            ->setLastModifiedBy("Sfinge 2104-2020")
            ->setTitle("Office 2005 XLSX Test Document")
            ->setSubject("Office 2005 XLSX Test Document")
            ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
            ->setKeywords("office 2005 openxml php")
            ->setCategory("Test result file");

        $riga = 1;

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();

        $column = 0;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'ID Operazione');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'CUP Operazione');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'ID Pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Causale pagamento');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Beneficiario/soggetto');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Importo proposto');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Importo rendicontato');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Importo rendicontato ammesso');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Titolo procedura');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Titolo progetto');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Asse prioritario');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Aiuto di Stato');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Anticipi Aiuto di Stato');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Strumento finanziario');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Progetti campionati per la verifica in loco');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Progetti verificati in loco');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Progetti campionati per la verifica in loco ex post');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Progetti verificati in loco ex post');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Totali ');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Numero Atto Liquidazione');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Data Atto Liquidazione ');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Numero Mandato di Pagamento ');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Data Mandato di Pagamento ');
        $column = 0;
        foreach ($pagamenti as $key => $pagamento) {

            $riga++;
            $phpExcelObject->setActiveSheetIndex(0);

            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['id_operazione']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['cup']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['id_pagamento']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['causale']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['beneficiario_soggetto']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['importo_proposto']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, !is_null($pagamento['importo_rendicontato']) ? $pagamento['importo_rendicontato'] : '-');
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, !is_null($pagamento['importo_rendicontato_ammesso']) ? $pagamento['importo_rendicontato_ammesso'] : '-');
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['titolo_procedura']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['titolo_progetto']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['asse_prioritario']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, !is_null($pagamento['aiuto_di_stato']) ? ($pagamento['aiuto_di_stato'] == 1 ? 'SI' : 'NO') : 'NO');
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, !is_null($pagamento['anticipi_aiuto_di_stato']) ? ($pagamento['anticipi_aiuto_di_stato'] == 1 ? 'SI' : 'NO') : 'NO');
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, !is_null($pagamento['strumento_finanziario']) ? ($pagamento['strumento_finanziario'] == 1 ? 'SI' : 'NO') : 'NO');
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, !is_null($pagamento['controllo']) ? 'SI' : 'NO');
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['esito_controllo'] != '-' ? 'SI' : 'NO');
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, !is_null($pagamento['controllo2']) ? 'SI' : 'NO');
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['esito_controllo2'] != '-' ? 'SI' : 'NO');
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $pagamento['importo_certificato']);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, !is_null($pagamento['numero_atto_liquidazione']) ? $pagamento['numero_atto_liquidazione'] : '-');
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, !is_null($pagamento['data_atto_liquidazione']) ? $pagamento['data_atto_liquidazione']->format('d/m/Y') : '-');
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, !is_null($pagamento['numero_mandato_pagamento']) ? $pagamento['numero_mandato_pagamento'] : '-');
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, !is_null($pagamento['data_mandato_pagamento']) ? $pagamento['data_mandato_pagamento']->format('d/m/Y') : '-');
            $column++;
            $column = 0;
        }

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione_pagamenti_certificazione_' . $certificazione->getNumero() . ".xls"
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @Route("/{id_certificazione}/allegato_a", name="scarica_allegato_a_certificazione")
     */
    public function allegatoACertificazioneAction($id_certificazione) {
        $certificazione = $this->getEm()->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);
        $assi = $this->getEm()->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->calcolaAssiCertificazione($id_certificazione);

        $certificazioniPrecedenti = $this->getEm()->getRepository("CertificazioniBundle\Entity\Certificazione")->findPrecedentiCertificazioniInviate($certificazione);

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Sfinge 2104-2020")
            ->setLastModifiedBy("Sfinge 2104-2020")
            ->setTitle("Office 2005 XLSX Test Document")
            ->setSubject("Office 2005 XLSX Test Document")
            ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
            ->setKeywords("office 2005 openxml php")
            ->setCategory("Test result file");

        $lettere = array('A', 'B', 'C', 'D');
        $size = array(50, 30, 30, 30);

        $riga_header = 2;
        $riga = $riga_header + 1;

        $styleArray = array(
            'font' => array(
                'bold' => true,
        ));

        $styleArrayCenter = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));

        if (!is_null($certificazione->getDataPropostaAdg())) {
            $dataCertificazione = $certificazione->getDataPropostaAdg()->format('d/m/Y');
        } else {
            $dataObj = new \DateTime();
            $dataCertificazione = $dataObj->format('d/m/Y');
        }

        $phpExcelObject->setActiveSheetIndex(0)->setCellValue("A1", "ALLEGATO A. TOTALE DA CERTIFICARE SUDDIVISO PER ASSE\nProposta di Certificazione (dati al {$dataCertificazione})");
        $phpExcelObject->getActiveSheet()->getStyle("A1")->applyFromArray(array_merge($styleArrayCenter, $styleArray));
        $phpExcelObject->getActiveSheet()->mergeCells('A1:D1');
        $phpExcelObject->getActiveSheet()->getRowDimension("1")->setRowHeight(30);
        $phpExcelObject->getActiveSheet()->getStyle("A1")->getAlignment()->setWrapText(true);

        $phpExcelObject->getActiveSheet()->getStyle("B$riga_header:D" . ($riga + 10))->applyFromArray($styleArrayCenter);

        // $phpExcelObject->getActiveSheet()->getStyle($lettere[0].$riga_header.':'.$lettere[10] . $riga_header)->applyFromArray($styleArray);

        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[1] . $riga_header, "Totale Certificabile");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[2] . $riga_header, "Quota UE (FESR)");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[3] . $riga_header, "Quota Stato (FNR)");

        foreach ($lettere as $i => $lettera) {
            $phpExcelObject->getActiveSheet()->getColumnDimension($lettera)->setWidth($size[$i]);
        }

        $totale = 0;

        foreach ($assi as $asse) {
            $phpExcelObject->getActiveSheet()->setCellValue($lettere[0] . $riga, $asse[0]->getTitolo() . " - " . $asse[0]->getDescrizione());
            $phpExcelObject->getActiveSheet()->setCellValue($lettere[1] . $riga, number_format($asse[1], 2, ",", "."));

            $quotaUE = $quotaStato = $asse[1] * 50 / 100;
            $phpExcelObject->getActiveSheet()->setCellValue($lettere[2] . $riga, $quotaUE);
            $phpExcelObject->getActiveSheet()->setCellValue($lettere[3] . $riga, $quotaStato);
            $totale += $asse[1];
            $riga++;
        }

        // riga totale
        $phpExcelObject->getActiveSheet()->getStyle("A$riga:D$riga")->applyFromArray($styleArray);
        $phpExcelObject->getActiveSheet()->setCellValue("A" . $riga, "Totale complessivo");
        $phpExcelObject->getActiveSheet()->setCellValue("B" . $riga, number_format($totale, 2, ",", "."));

        $totaleUe = $totaleStato = $totale * 50 / 100;
        $phpExcelObject->getActiveSheet()->setCellValue("C" . $riga, number_format($totaleUe, 2, ",", "."));
        $phpExcelObject->getActiveSheet()->setCellValue("D" . $riga, number_format($totaleStato, 2, ",", "."));
        $riga++;

        // riga header 2
        $phpExcelObject->getActiveSheet()->setCellValue("B" . $riga, "Totale Certificabile");
        $riga++;

        // riga proposta
        $phpExcelObject->getActiveSheet()->setCellValue("A" . $riga, "Proposta di certificazione al {$dataCertificazione} \nValore incrementale della {$certificazione->getNumero()}ˆ certificazione");
        $phpExcelObject->getActiveSheet()->getStyle("A" . $riga)->getAlignment()->setWrapText(true);
        $phpExcelObject->getActiveSheet()->setCellValue("B" . $riga, number_format($totale, 2, ",", "."));
        $phpExcelObject->getActiveSheet()->setCellValue("C" . $riga, number_format($totaleUe, 2, ",", "."));
        $phpExcelObject->getActiveSheet()->setCellValue("D" . $riga, number_format($totaleStato, 2, ",", "."));
        $riga++;

        $totaleCertificazioniPrecedenti = 0;
        $dataPenultimaCertificazione = null;
        foreach ($certificazioniPrecedenti as $certificazionePrecedente) {
            $assiCP = $this->getEm()->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->calcolaAssiCertificazione($certificazionePrecedente->getId());
            // sommo gli importi di tutti gli assi di tutte le certificazioni precedenti
            foreach ($assiCP as $asseCP) {
                $totaleCertificazioniPrecedenti += $asseCP[1];
            }
            $dataPenultimaCertificazione = $certificazionePrecedente->getDataPropostaAdg();
        }

        $dataPenultimaCertificazione = is_null($dataPenultimaCertificazione) ? '-' : $dataPenultimaCertificazione->format('d/m/Y');

        $totaleUECertificazioniPrecedenti = $totaleStatoCertificazioniPrecedenti = $totaleCertificazioniPrecedenti * 50 / 100;
        // riga storico
        $phpExcelObject->getActiveSheet()->setCellValue("A" . $riga, "Totale certificato al {$dataPenultimaCertificazione}");
        $phpExcelObject->getActiveSheet()->setCellValue("B" . $riga, number_format($totaleCertificazioniPrecedenti, 2, ",", "."));
        $phpExcelObject->getActiveSheet()->setCellValue("C" . $riga, number_format($totaleUECertificazioniPrecedenti, 2, ",", "."));
        $phpExcelObject->getActiveSheet()->setCellValue("D" . $riga, number_format($totaleStatoCertificazioniPrecedenti, 2, ",", "."));
        $riga++;

        // riga storico aggiornato
        $phpExcelObject->getActiveSheet()->getStyle("A$riga:D$riga")->applyFromArray($styleArray);
        $phpExcelObject->getActiveSheet()->setCellValue("A" . $riga, "Totale certificabile al {$dataCertificazione}");
        $phpExcelObject->getActiveSheet()->setCellValue("B" . $riga, number_format($totale + $totaleCertificazioniPrecedenti, 2, ",", "."));
        $phpExcelObject->getActiveSheet()->setCellValue("C" . $riga, number_format($totaleUe + $totaleUECertificazioniPrecedenti, 2, ",", "."));
        $phpExcelObject->getActiveSheet()->setCellValue("D" . $riga, number_format($totaleStato + $totaleStatoCertificazioniPrecedenti, 2, ",", "."));

        $phpExcelObject->getActiveSheet()->setTitle('Simple');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Allegato_A_Certificazione_' . $certificazione->getNumero() . ".xls"
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @Route("/{id_certificazione}/allegato_b", name="scarica_allegato_b_certificazione")      
     */
    public function allegatoBCertificazioneAction($id_certificazione) {
        $certificazione = $this->getEm()->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);
        //$asse = $this->getEm()->getRepository("SfingeBundle\Entity\Asse")->find($id_asse);

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Sfinge 2104-2020")
            ->setLastModifiedBy("Sfinge 2104-2020")
            ->setTitle("Office 2005 XLSX Test Document")
            ->setSubject("Office 2005 XLSX Test Document")
            ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
            ->setKeywords("office 2005 openxml php")
            ->setCategory("Test result file");

        $em = $this->getDoctrine()->getManager();

        $certificazioni_pagamenti = $certificazione->getPagamenti();

        $progressivo = 1;

        $lettere = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K');
        $size = array(10, 20, 30, 40, 10, 15, 10, 15, 15, 15, 15);

        $riga_header = 1;
        $riga = $riga_header + 1;

        $styleArray = array(
            'font' => array(
                'bold' => true,
        ));

        $phpExcelObject->getActiveSheet()->getStyle($lettere[0] . $riga_header . ':' . $lettere[10] . $riga_header)->applyFromArray($styleArray);

        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[0] . $riga_header, "Progressivo");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[1] . $riga_header, "Riferimento operazione");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[2] . $riga_header, "Id");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[3] . $riga_header, "Beneficiario dell'operazione");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[4] . $riga_header, "Titolo progetto integrato");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[5] . $riga_header, "Asse");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[6] . $riga_header, "Importo Proposto");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[7] . $riga_header, "Causale (anticipo/acconto/saldo/unica soluzione)");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[8] . $riga_header, "Progetti campionati per la verifica in loco");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[9] . $riga_header, "Progetti verificati in loco");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[10] . $riga_header, "Importi detratti da AdG");

        foreach ($lettere as $i => $lettera) {
            $phpExcelObject->getActiveSheet()->getColumnDimension($lettera)->setWidth($size[$i]);
        }

        foreach ($certificazioni_pagamenti as $certificazione_pagamento) {
            $pagamento = $certificazione_pagamento->getPagamento();

            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[0] . $riga, $progressivo);
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[1] . $riga, $pagamento->getRichiesta()->getProtocollo());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[2] . $riga, $pagamento->getRichiesta()->getId());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[3] . $riga, $pagamento->getRichiesta()->getMandatario()->getSoggetto()->getDenominazione());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[4] . $riga, $pagamento->getRichiesta()->getTitolo());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[5] . $riga, substr($pagamento->getRichiesta()->getProcedura()->getAsse()->getCodice(), 1));
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[6] . $riga, $certificazione_pagamento->getImporto());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[7] . $riga, $pagamento->getModalitaPagamento());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[8] . $riga, $pagamento->getRichiesta()->hasCampionamentoLoco() == true ? 'SI' : 'NO');
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[9] . $riga, $pagamento->getRichiesta()->hasCampionamentoLocoConcluso() == true ? 'SI' : 'NO');
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[10] . $riga, "");

            $riga = $riga + 1;
            $progressivo = $progressivo + 1;
        }

        $phpExcelObject->getActiveSheet()->setTitle('Simple');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Allegato_B_Certificazione_' . $certificazione->getNumero() . ".xls"
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @Route("/scarica_cert_agrea_revoche_inviate", name="cert_agrea_scarica_report_revoche_inviate")
     * @return StreamedResponse
     */
    public function scaricaReportRevocheInviate() {
        \ini_set('memory_limit', '512M');
        $gestore = $this->get('cert_agrea_esportazioni');/** @var CertificazioniBundle\Service\GestoreEsportazioni */
        $excelWriter = $gestore->getReportRevocheInviate();

        $response = new StreamedResponse(function () use ($excelWriter) {
                $excelWriter->save('php://output');
            },
            \Symfony\Component\HttpFoundation\Response::HTTP_OK,
            array(
            'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=1')
        );
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'report revoche.xls'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route("/scarica_cert_agrea_revoche_con_recupero", name="cert_agrea_scarica_report_revoche_con_recupero")
     * @return StreamedResponse
     */
    public function scaricaReportRevocheConRecupero() {
        \ini_set('memory_limit', '512M');
        $gestore = $this->get('cert_agrea_esportazioni');/** @var CertificazioniBundle\Service\GestoreEsportazioni */
        $excelWriter = $gestore->getReportRevocheConRecupero();

        $response = new StreamedResponse(function () use ($excelWriter) {
                $excelWriter->save('php://output');
            },
            \Symfony\Component\HttpFoundation\Response::HTTP_OK,
            array(
            'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=1')
        );
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'report recuperi.xls'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route("/scarica_cert_agrea_pagamenti_certificati", name="cert_agrea_scarica_report_pagamenti_certificati")
     * @return StreamedResponse
     */
    public function scaricaReportPagamentiCertificati() {
        \ini_set('memory_limit', '512M');
        set_time_limit(0);
        $gestore = $this->get('cert_agrea_esportazioni');/** @var CertificazioniBundle\Service\GestoreEsportazioni */
        $excelWriter = $gestore->getReportPagamentiCertificati();

        $response = new StreamedResponse(function () use ($excelWriter) {
                $excelWriter->save('php://output');
            },
            \Symfony\Component\HttpFoundation\Response::HTTP_OK,
            array(
            'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=1')
        );
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'report pagamenti.xls'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route("/elenco_decertificazione_pagamenti/{sort}/{direction}/{page}", defaults={"sort" = "i.id", "direction" = "asc", "page" = "1"}, name="elenco_decertificazione_pagamenti")
     * @PaginaInfo(titolo="Elenco decertificazioni",sottoTitolo="mostra l'elenco dei pagamenti richiesti")
     * @Menuitem(menuAttivo = "elencoDecertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco pagamenti")})
     */
    public function elencoDecertificazioniPagamentiAction() {

        $datiRicerca = new \CertificazioniBundle\Form\Entity\RicercaDecertificazioni();
        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return $this->render('CertificazioniBundle:Certificazioni:elencoDecertificazioni.html.twig', array(
                'risultati' => $risultato["risultato"],
                "formRicerca" => $risultato["form_ricerca"],
                "filtro_attivo" => $risultato["filtro_attivo"],
                "menu" => 'pagamenti'));
    }

    /**
     * @Route("/elenco_decertificazione_pagamenti_pulisci", name="elenco_decertificazione_pagamenti_pulisci")
     */
    public function elencoDecertificazionePagamentiPulisciAction() {
        $this->get("ricerca")->pulisci(new \CertificazioniBundle\Form\Entity\RicercaDecertificazioni());
        return $this->redirectToRoute("elenco_decertificazione_pagamenti");
    }

    /**
     * @Route("/elenco_decertificazione_chiusure/{sort}/{direction}/{page}", defaults={"sort" = "i.id", "direction" = "asc", "page" = "1"}, name="elenco_decertificazione_chiusure")
     * @PaginaInfo(titolo="Elenco decertificazioni",sottoTitolo="mostra l'elenco dei pagamenti richiesti")
     * @Menuitem(menuAttivo = "elencoDecertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco pagamenti")})
     */
    public function elencoDecertificazioniChiusureAction() {

        $datiRicerca = new \CertificazioniBundle\Form\Entity\RicercaDecertificazioniChiusure();
        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return $this->render('CertificazioniBundle:Certificazioni:elencoDecertificazioniChiusure.html.twig', array(
                'risultati' => $risultato["risultato"],
                "formRicerca" => $risultato["form_ricerca"],
                "filtro_attivo" => $risultato["filtro_attivo"],
                "menu" => 'chiusure'));
    }

    /**
     * @Route("/elenco_decertificazione_chiusure_pulisci", name="elenco_decertificazione_chiusure_pulisci")
     */
    public function elencoDecertificazioneChiusurePulisciAction() {
        $this->get("ricerca")->pulisci(new \CertificazioniBundle\Form\Entity\RicercaDecertificazioniChiusure());
        return $this->redirectToRoute("elenco_decertificazione_chiusure");
    }

    /**
     * @Route("/scarica_cert_agrea_revoche_universo", name="scarica_cert_agrea_revoche_universo")
     * @return StreamedResponse
     */
    public function scaricaReportRevocheUniverso() {
        \ini_set('memory_limit', '512M');
        $gestore = $this->get('cert_agrea_esportazioni');/** @var CertificazioniBundle\Service\GestoreEsportazioni */
        $excelWriter = $gestore->getReportRevocheUniverso();

        $response = new StreamedResponse(function () use ($excelWriter) {
                $excelWriter->save('php://output');
            },
            \Symfony\Component\HttpFoundation\Response::HTTP_OK,
            array(
            'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=1')
        );
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'report revoche.xls'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

}
