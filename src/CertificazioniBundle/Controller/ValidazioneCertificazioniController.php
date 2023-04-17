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
use DocumentoBundle\Component\ResponseException;

/**
 * @Route("/validazione")
 */
class ValidazioneCertificazioniController extends BaseController {

    /**
     * @Route("/{id_certificazione}/valuta_asse/{id_asse}", name="valuta_asse_certificazione")
     * @PaginaInfo(titolo="Valutazione asse certificazione",sottoTitolo="pagina di valutazione di un asse per la certificazione")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Valuta asse")})
     * 
     * @ControlloAccesso(contesto="asse", classe="SfingeBundle:Asse", opzioni={"id" = "id_asse"}, azione=\SfingeBundle\Security\AsseVoter::WRITE)
     */
    public function valutaAsseAction($id_certificazione, $id_asse) {
        $certificazione = $this->getEm()->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);
        $asse = $this->getEm()->getRepository("SfingeBundle\Entity\Asse")->find($id_asse);

        if (!$certificazione->isValidabile()) {
            $this->addFlash("error", "Azione non compatile con lo stato della certificazione");
            return $this->redirectToRoute("elenco_certificazioni");
        }

        $certificazioni_pagamenti_asse = $this->getEm()->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->getCertificazioniPagamentiAsse($id_certificazione, $id_asse);

        return $this->render('CertificazioniBundle:Certificazioni:valutaAsseCertificazione.html.twig', array('certificazione' => $certificazione, 'asse' => $asse, 'certificazioni_pagamenti_asse' => $certificazioni_pagamenti_asse));
    }

    /**
     * @Route("/{id_certificazione}/allegato_b/{id_asse}", name="scarica_allegato_b_asse_certificazione")
     * @ControlloAccesso(contesto="asse", classe="SfingeBundle:Asse", opzioni={"id" = "id_asse"}, azione=\SfingeBundle\Security\AsseVoter::WRITE)
     */
    public function allegatoBAsseCertificazioneAction($id_certificazione, $id_asse) {
        $certificazione = $this->getEm()->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);
        $asse = $this->getEm()->getRepository("SfingeBundle\Entity\Asse")->find($id_asse);

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
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[2] . $riga_header, "Beneficiario dell'operazione");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[3] . $riga_header, "Titolo progetto integrato");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[4] . $riga_header, "Asse");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[5] . $riga_header, "Importo spese rimborsabili (delta da certificare nella " . $certificazione->getNumero() . " certificazione)");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[6] . $riga_header, "Causale (anticipo/acconto/saldo)");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[7] . $riga_header, "Importo spese proposte alla certificazione alla data del periodo - importo totale");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[8] . $riga_header, "Importi detratti da AdG");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[9] . $riga_header, "Progetti campionati per la verifica in loco");
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[10] . $riga_header, "Progetti verificati in loco");

        foreach ($lettere as $i => $lettera) {
            $phpExcelObject->getActiveSheet()->getColumnDimension($lettera)->setWidth($size[$i]);
        }

        foreach ($certificazioni_pagamenti as $certificazione_pagamento) {
            $pagamento = $certificazione_pagamento->getPagamento();

            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[0] . $riga, $progressivo);
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[1] . $riga, $pagamento->getRichiesta()->getProtocollo());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[2] . $riga, $pagamento->getRichiesta()->getMandatario()->getSoggetto()->getDenominazione());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[3] . $riga, $pagamento->getRichiesta()->getTitolo());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[4] . $riga, substr($pagamento->getRichiesta()->getProcedura()->getAsse()->getCodice(), 1));
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[5] . $riga, "");
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[6] . $riga, $pagamento->getModalitaPagamento());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettere[7] . $riga, "");

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
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'Allegato_B_Certificazione_' . $certificazione->getNumero() . ".xls"
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @Route("/{id_certificazione}/valida/{id_asse}", name="valida_asse_certificazione") 
     * @ControlloAccesso(contesto="asse", classe="SfingeBundle:Asse", opzioni={"id" = "id_asse"}, azione=\SfingeBundle\Security\AsseVoter::WRITE) 
     */
    public function validaAsseCertificazioneAction($id_certificazione, $id_asse) {
        $this->get('base')->checkCsrf('token');

        $certificazione = $this->getEm()->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);
        $asse = $this->getEm()->getRepository("SfingeBundle\Entity\Asse")->find($id_asse);

        $em = $this->getEm();
        $certificazione_asse = $em->getRepository("CertificazioniBundle\Entity\CertificazioneAsse")->findOneBy(array("certificazione" => $id_certificazione, "asse" => $id_asse));

        if (!is_null($certificazione_asse)) {
            $this->addFlash("error", "Risulta già validato l'asse per la certificazione");
            return $this->redirectToRoute("valuta_asse_certificazione", array("id_certificazione" => $id_certificazione, "id_asse" => $id_asse));
        }

        $certificazione_asse = new \CertificazioniBundle\Entity\CertificazioneAsse();
        $certificazione_asse->setCertificazione($certificazione);
        $certificazione_asse->setAsse($asse);
        $certificazione_asse->setDataValidazione(new \DateTime());
        $certificazione_asse->setUtenteValidazione($this->getUser());

        $sommaImportiStrumenti = $em->getRepository("CertificazioniBundle\Entity\Certificazione")->getSommaImportiStrumenti($id_asse);
        $certificazione_asse->setImportoStrumenti($sommaImportiStrumenti);

        try {
            $em->beginTransaction();
            $em->persist($certificazione_asse);
            $em->flush();
            //$assi = $em->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->getAssiCertificazioneUtente($certificazione->getId());
            $assi = $em->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->getAssiCertificazioneUtenteCompleta($certificazione->getId());
            if (count($assi) == 0) {
                $this->container->get("sfinge.stati")->avanzaStato($certificazione, StatoCertificazione::CERT_VALIDATA);
            }
            $em->flush();
            $em->commit();
            $this->addFlash("success", "La certificazione è stata correttamente validata per l'asse");
        } catch (ResponseException $e) {
            $em->rollback();
            $this->addFlash("error", "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza");
        }

        return $this->redirectToRoute("elenco_certificazioni");
    }

    /**
     * @Route("/{id_certificazione}/valida", name="valida_asse_certificazione_vuoto") 
     */
    public function validaAsseCertificazioneVuotoAction($id_certificazione) {
        $em = $this->getEm();
        $certificazione = $this->getEm()->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);
        $assi = $this->getEm()->getRepository("SfingeBundle\Entity\Asse")->findAll();

        try {
            $em->beginTransaction();
            foreach ($assi as $asse) {
                $sommaImportiStrumenti = $em->getRepository("CertificazioniBundle\Entity\Certificazione")->getSommaImportiStrumenti($asse->getId());
                $certificazione_asse = new \CertificazioniBundle\Entity\CertificazioneAsse();
                $certificazione_asse->setAsse($asse);
                $certificazione_asse->setCertificazione($certificazione);
                $certificazione_asse->setDataValidazione(new \DateTime());
                $certificazione_asse->setUtenteValidazione($this->getUser());
                $certificazione_asse->setImportoStrumenti($sommaImportiStrumenti);
                $em->persist($certificazione_asse);
            }
            $this->container->get("sfinge.stati")->avanzaStato($certificazione, StatoCertificazione::CERT_VALIDATA);
            $em->flush();
            $em->commit();
            $this->addFlash("success", "La certificazione è stata correttamente validata per l'asse");
        } catch (ResponseException $e) {
            $em->rollback();
            $this->addFlash("error", "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza");
        }

        return $this->redirectToRoute("elenco_certificazioni");
    }

}
