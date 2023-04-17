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
use CertificazioniBundle\Entity\CertificazioneChiusuraRepository;
use CertificazioniBundle\Entity\StatoChiusuraCertificazione;
use DocumentoBundle\Component\ResponseException;

/**
 * @Route("/inserimento")
 */
class ChiusuraController extends BaseController {

    /**
     * @Route("/aggiungi_chiusura", name="aggiungi_chiusura_certificazione")
     * @PaginaInfo(titolo="Aggiungi chiusura certificazione", sottoTitolo="pagina per l'aggiunta delle chiusura")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Aggiungi chiusura certificazione")})
     */
    public function aggiungiChiusuraCerticazioneAction() {
        $em = $this->getEm();
        $chiusure = $em->getRepository("CertificazioniBundle\Entity\CertificazioneChiusura")->findBy(array("stato" => array(
                $em->getRepository("CertificazioniBundle\Entity\StatoChiusuraCertificazione")->findOneBy(array("codice" => StatoChiusuraCertificazione::CHI_LAVORAZIONE)),
                $em->getRepository("CertificazioniBundle\Entity\StatoChiusuraCertificazione")->findOneBy(array("codice" => StatoChiusuraCertificazione::CHI_BLOCCATA)),
                $em->getRepository("CertificazioniBundle\Entity\StatoChiusuraCertificazione")->findOneBy(array("codice" => StatoChiusuraCertificazione::CHI_VALIDATA)),
                $em->getRepository("CertificazioniBundle\Entity\StatoChiusuraCertificazione")->findOneBy(array("codice" => StatoChiusuraCertificazione::CHI_INVIATA))
        )));

        if (count($chiusure) > 0) {
            $this->addFlash("error", "Impossibile aggiungere una nuova chiusura se quella in corso non è stata ancora approvata");
            return $this->redirectToRoute("elenco_certificazioni");
        }

        $chiusura = new \CertificazioniBundle\Entity\CertificazioneChiusura();

        try {
            $em->persist($chiusura);
            $em->flush();
            $this->container->get("sfinge.stati")->avanzaStato($chiusura, StatoChiusuraCertificazione::CHI_LAVORAZIONE);
            $em->flush();
            $this->addFlash("success", "La chiusura è stata correttamente salvata");
            return $this->redirectToRoute("elenco_certificazioni");
        } catch (\Exception $e) {
            $em->rollback();
            $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
        }

        return $this->redirectToRoute("elenco_certificazioni");
    }

    /**
     * @Route("/associa_certificazioni_chiusura/{id_chiusura}", name="associa_certificazioni_chiusura")
     * @PaginaInfo(titolo="Elenco certificazioni associabili",sottoTitolo="Lista certificazioni")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Associa certificazioni")})
     */
    public function associaCertificazioneAction($id_chiusura) {

        $em = $this->getEm();

        $chiusura = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->findOneById($id_chiusura);

        if ($chiusura->getStato()->getCodice() != 'CHI_LAVORAZIONE') {
            return $this->addError("La chiusura dei conti non è più lavorabile", $this->generateUrl("elenco_certificazioni"));
        }

        $certificazioni_eleggibili = $em->getRepository('CertificazioniBundle\Entity\Certificazione')->getCertificazioniSenzaChiusura($id_chiusura);

        $certificazioni_associativo = array();
        foreach ($certificazioni_eleggibili as $certificazione_eleggibile) {
            $certificazioni_associativo[$certificazione_eleggibile->getId()] = $certificazione_eleggibile;
        }

        $options = array();
        $options["url_indietro"] = $this->generateUrl("elenco_certificazioni");
        $options["em"] = $em;
        $options["id_chiusura"] = $id_chiusura;

        $form = $this->createForm("CertificazioniBundle\Form\AssociazioneCertificazioneChiusuraType", $chiusura, $options);

        $request = $this->getCurrentRequest();

        $certificazioni_pre = $chiusura->getCertificazioni();

        if ($request->isMethod('POST')) {
            foreach ($certificazioni_pre as $certificazione_pre) {
                $certificazione_pre->setChiusura(null);
            }
            $form->handleRequest($request);

            if ($form->isValid()) {

                $em = $this->getEm();
                try {
                    foreach ($chiusura->getCertificazioni() as $certificazione) {
                        $certificazione->setChiusura($chiusura);
                    }

                    if ($form->get("pulsanti")->get("pulsante_blocca")->isClicked()) {
                        $this->container->get("sfinge.stati")->avanzaStato($chiusura, StatoChiusuraCertificazione::CHI_BLOCCATA);
                        $this->addFlash('success', "Chiusura conti validata con successo");
                    } else {
                        $this->addFlash('success', "Integrazione salvata con successo");
                    }
                    $em->flush();
                    return $this->redirectToRoute("elenco_certificazioni");
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza. " . $e->getMessage());
                }
            }
        }

        $dati = array('risultati' => $certificazioni_eleggibili);
        $dati["form"] = $form->createView();
        $dati["certificazioni_associativo"] = $certificazioni_associativo;

        return $this->render("CertificazioniBundle:Certificazioni:associaCertificazioni.html.twig", $dati);
    }

    /**
     * @Route("/sblocca_chiusura/{id_chiusura}", name="sblocca_chiusura")  
     */
    public function sbloccaChiusuraAction($id_chiusura) {

        $em = $this->getEm();
        $chiusura = $em->getRepository("CertificazioniBundle\Entity\CertificazioneChiusura")->find($id_chiusura);

        if ($chiusura->getStato()->getCodice() == 'CHI_VALIDATA') {
            return $this->addError("La chiusura dei conti non è più lavorabile perchè validata", $this->generateUrl("elenco_certificazioni"));
        }

        try {
            $this->container->get("sfinge.stati")->avanzaStato($chiusura, StatoChiusuraCertificazione::CHI_LAVORAZIONE);
            $em->flush();
            $this->addFlash("success", "La chiusura dei conti è stata correttamente sbloccata e riportata in lavorazione");
        } catch (ResponseException $e) {
            $this->addFlash("error", "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza");
        }

        return $this->redirectToRoute("elenco_certificazioni");
    }

    /**
     * @Route("/valida_chiusura/{id_chiusura}", name="valida_chiusura")  
     */
    public function validaChiusuraAction($id_chiusura) {

        $em = $this->getEm();
        $chiusura = $em->getRepository("CertificazioniBundle\Entity\CertificazioneChiusura")->find($id_chiusura);

        try {
            $this->container->get("sfinge.stati")->avanzaStato($chiusura, StatoChiusuraCertificazione::CHI_VALIDATA);
            $em->flush();
            $this->addFlash("success", "La chiusura dei conti è stata correttamente validata e riportata in lavorazione");
        } catch (ResponseException $e) {
            $this->addFlash("error", "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza");
        }

        return $this->redirectToRoute("elenco_certificazioni");
    }

    /**
     * @Route("/dettaglio_certificazioni_chiusura/{id_chiusura}", name="dettaglio_certificazioni_chiusura")
     * @PaginaInfo(titolo="Dettaglio chiusura dei conti", sottoTitolo="pagina di dettaglio della chiusura dei conti")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Dettaglio chiusura conti")})
     */
    public function dettaglioChiusuraContiAction($id_chiusura) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $chiusura = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->findOneById($id_chiusura);

        $documento_certificazione = new \CertificazioniBundle\Entity\DocumentoCertificazioneChiusura();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();

        $documenti_caricati_certificatore_agrea = $em->getRepository("CertificazioniBundle\Entity\DocumentoCertificazioneChiusura")->findDocumentiCaricati($id_chiusura);

        $documenti_caricati_certificatore = $em->getRepository("CertificazioniBundle\Entity\DocumentoCertificazioneChiusura")->findDocumentiCaricatiCertificatore($id_chiusura);

        // Form per caricamento documento certificatore agrea	
        $tipo_doc_checklist = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array('codice' => array("CHECKLIST_CHIU", "RELAZIONE_CHIU")));
        $options_checklist["lista_tipi"] = $tipo_doc_checklist;
        $form_upload_checklist = $this->createForm("DocumentoBundle\Form\Type\DocumentoFileType", $documento_file, $options_checklist);

        $form_upload_checklist->add("submit", "Symfony\Component\Form\Extension\Core\Type\SubmitType", array("label" => "Salva"));

        $form_upload_checklist_view = $form_upload_checklist->createView();

        // Form per caricamento documento validazione	
        $tipo_doc_validazione = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array('codice' => "DOC_VALIDA_CHIU"));

        $form_upload_validazione = $this->createForm("DocumentoBundle\Form\Type\DocumentoFileType", $documento_file, array('tipo' => $tipo_doc_validazione)
        );
        $form_upload_validazione->add("submit", "Symfony\Component\Form\Extension\Core\Type\SubmitType", array("label" => "Carica"));

        $form_upload_validazione_view = $form_upload_validazione->createView();

        if ($request->isMethod("POST")) {

            $form = $form_upload_checklist->isSubmitted() ? $form_upload_checklist : $form_upload_validazione;

            $form->handleRequest($request);
            if ($form->isValid()) {
                try {

                    $this->container->get("documenti")->carica($documento_file, 0);

                    $documento_certificazione->setDocumentoFile($documento_file);
                    $documento_certificazione->setChiusura($chiusura);
                    $em->persist($documento_certificazione);

                    $em->flush();

                    return $this->addSuccessRedirect("Documento caricato correttamente", "dettaglio_certificazioni_chiusura", array("id_chiusura" => $id_chiusura));
                } catch (ResponseException $e) {
                    $this->addFlash("error", $e->getMessage());
                }
            }
        }

        $dati = array(
            'chiusura' => $chiusura,
            'documenti_cert_agrea' => $documenti_caricati_certificatore_agrea,
            'documenti_cert' => $documenti_caricati_certificatore,
            'form_upload_checklist_view' => $form_upload_checklist_view,
            'form_upload_validazione_view' => $form_upload_validazione_view,
        );

        return $this->render("CertificazioniBundle:Certificazioni:dettaglioChiusuraConti.html.twig", $dati);
    }

    /**
     * @Route("/appendici_certificazioni_chiusura/{id_chiusura}", name="appendici_certificazioni_chiusura")
     * @PaginaInfo(titolo="Elenco certificazioni associabili",sottoTitolo="Lista appendici")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Dettaglio chiusura conti")})
     */
    public function appendiciChiusuraContiAction($id_chiusura) {

        $em = $this->getEm();
        $chiusura = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->findOneById($id_chiusura);
        $dati["chiusura"] = $chiusura;

        return $this->render("CertificazioniBundle:Appendici:appendiciChiusuraConti.html.twig", $dati);
    }

    /**
     * @Route("/appendici_chiusura_1/{id_chiusura}", name="appendici_chiusura_1")
     * @PaginaInfo(titolo="Elenco certificazioni associabili",sottoTitolo="Lista appendici")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Dettaglio chiusura conti")})
     */
    public function appendiceChiusura1Action($id_chiusura) {

        $em = $this->getEm();
        /** @var CertificazioneChiusuraRepository $chiusuraRepository */
        $chiusuraRepository = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura');
        $chiusura = $chiusuraRepository->findOneById($id_chiusura);
        $res = array();
        $res['A1'] = $chiusuraRepository->getImportiCertificatiPerAsse($id_chiusura, 'A1');
        $res['A2'] = $chiusuraRepository->getImportiCertificatiPerAsse($id_chiusura, 'A2');
        $res['A3'] = $chiusuraRepository->getImportiCertificatiPerAsse($id_chiusura, 'A3');
        $res['A4'] = $chiusuraRepository->getImportiCertificatiPerAsse($id_chiusura, 'A4');
        $res['A5'] = $chiusuraRepository->getImportiCertificatiPerAsse($id_chiusura, 'A5');
        $res['A6'] = $chiusuraRepository->getImportiCertificatiPerAsse($id_chiusura, 'A6');
        $res['A7'] = $chiusuraRepository->getImportiCertificatiPerAsse($id_chiusura, 'A7');
        $res['TOTALE'] = $res['A1'] + $res['A2'] + $res['A3'] + $res['A4'] + $res['A5'] + $res['A6'] + $res['A7'];

        $res['A1TAGLI'] = $chiusuraRepository->getRevocheInvioContiAsseApp1($id_chiusura, 'A1');
        $res['A2TAGLI'] = $chiusuraRepository->getRevocheInvioContiAsseApp1($id_chiusura, 'A2');
        $res['A3TAGLI'] = $chiusuraRepository->getRevocheInvioContiAsseApp1($id_chiusura, 'A3');
        $res['A4TAGLI'] = $chiusuraRepository->getRevocheInvioContiAsseApp1($id_chiusura, 'A4');
        $res['A5TAGLI'] = $chiusuraRepository->getRevocheInvioContiAsseApp1($id_chiusura, 'A5');
        $res['A6TAGLI'] = $chiusuraRepository->getRevocheInvioContiAsseApp1($id_chiusura, 'A6');
        $res['A7TAGLI'] = $chiusuraRepository->getRevocheInvioContiAsseApp1($id_chiusura, 'A7');
        $res['TOTALETAGLI'] = $res['A1TAGLI'] + $res['A2TAGLI'] + $res['A3TAGLI'] + $res['A4TAGLI'] + $res['A5TAGLI'] + $res['A6TAGLI'] + $res['A7TAGLI'];

        $dati["chiusura"] = $chiusura;
        $dati["menu"] = 'appendice1';
        $dati["importi_asse"] = $res;

        return $this->render("CertificazioniBundle:Appendici:appendiceChiusure1.html.twig", $dati);
    }

    /**
     * @Route("/appendici_chiusura_2/{id_chiusura}", name="appendici_chiusura_2")
     * @PaginaInfo(titolo="Elenco certificazioni associabili",sottoTitolo="Lista appendici")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Dettaglio chiusura conti")})
     */
    public function appendiceChiusura2Action($id_chiusura) {

        $em = $this->getEm();
        $chiusura = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->findOneById($id_chiusura);

        $maxAnnoCont = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getMaxAnnoContabileDaChiusura($id_chiusura);
        $maxAnno = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getMaxAnnoCertificazioneDaChiusura($id_chiusura);
        //$periodoAnni = range(2015, $maxAnno);
        $periodoAnniContabili = range(1, $maxAnnoCont);

        $res = array();
        $res['A1REC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A1');
        $res['A2REC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A2');
        $res['A3REC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A3');
        $res['A4REC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A4');
        $res['A5REC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A5');
        $res['A6REC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A6');
        $res['A7REC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A7');
        $res['TOTALEREC'] = $res['A1REC'] + $res['A2REC'] + $res['A3REC'] + $res['A4REC'] + $res['A5REC'] + $res['A6REC'] + $res['A7REC'];

        $res['A1RIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A1');
        $res['A2RIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A2');
        $res['A3RIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A3');
        $res['A4RIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A4');
        $res['A5RIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A5');
        $res['A6RIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A6');
        $res['A7RIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A7');
        $res['TOTALERIT'] = $res['A1RIT'] + $res['A2RIT'] + $res['A3RIT'] + $res['A4RIT'] + $res['A5RIT'] + $res['A6RIT'] + $res['A7RIT'];

        $res['A1TAGLIREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A1', '20');
        $res['A2TAGLIREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A2', '20');
        $res['A3TAGLIREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A3', '20');
        $res['A4TAGLIREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A4', '20');
        $res['A5TAGLIREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A5', '20');
        $res['A6TAGLIREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A6', '20');
        $res['A7TAGLIREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRecuperi($id_chiusura, 'A7', '20');
        $res['TOTALETAGLIREC'] = $res['A1TAGLIREC'] + $res['A2TAGLIREC'] + $res['A3TAGLIREC'] + $res['A4TAGLIREC'] + $res['A5TAGLIREC'] + $res['A6TAGLIREC'] + $res['A7TAGLIREC'];

        $res['A1TAGLIRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A1', '20');
        $res['A2TAGLIRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A2', '20');
        $res['A3TAGLIRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A3', '20');
        $res['A4TAGLIRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A4', '20');
        $res['A5TAGLIRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A5', '20');
        $res['A6TAGLIRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A6', '20');
        $res['A7TAGLIRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevocheRitiri($id_chiusura, 'A7', '20');
        $res['TOTALETAGLIRIT'] = $res['A1TAGLIRIT'] + $res['A2TAGLIRIT'] + $res['A3TAGLIRIT'] + $res['A4TAGLIRIT'] + $res['A5TAGLIRIT'] + $res['A6TAGLIRIT'] + $res['A7TAGLIRIT'];

        $res['A1CMPRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'RIT', 'A1');
        $res['A2CMPRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'RIT', 'A2');
        $res['A3CMPRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'RIT', 'A3');
        $res['A4CMPRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'RIT', 'A4');
        $res['A5CMPRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'RIT', 'A5');
        $res['A6CMPRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'RIT', 'A6');
        $res['A7CMPRIT'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'RIT', 'A7');
        $res['TOTALECMPRIT'] = $res['A1CMPRIT'] + $res['A2CMPRIT'] + $res['A3CMPRIT'] + $res['A4CMPRIT'] + $res['A5CMPRIT'] + $res['A6CMPRIT'] + $res['A7CMPRIT'];

        $res['A1CMPREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'REC', 'A1');
        $res['A2CMPREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'REC', 'A2');
        $res['A3CMPREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'REC', 'A3');
        $res['A4CMPREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'REC', 'A4');
        $res['A5CMPREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'REC', 'A5');
        $res['A6CMPREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'REC', 'A6');
        $res['A7CMPREC'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensati($id_chiusura, 'REC', 'A7');
        $res['TOTALECMPREC'] = $res['A1CMPREC'] + $res['A2CMPREC'] + $res['A3CMPREC'] + $res['A4CMPREC'] + $res['A5CMPREC'] + $res['A6CMPREC'] + $res['A7CMPREC'];

        $rititiPrec = array();
        $recuperiPrec = array();
        $rititiPrecComp = array();
        $recuperiPrecComp = array();
        $recuperiPrecCompAda = array();
        $rititiPrecCompAda = array();

        foreach ($periodoAnniContabili as $anno) {
            $recuperiPrec["$anno"][0] = [
                'anno' => $anno,
                'tipo' => 'REC',
                'importo' => 0.0,
                'segnalazione_ada' => 0,
            ];
            $rititiPrec["$anno"][0] = [
                'anno' => $anno,
                'tipo' => 'RIT',
                'importo' => 0.0,
                'segnalazione_ada' => 0,
            ];
        }
        //decertificazioni
        foreach ($periodoAnniContabili as $anno) {
            $rec = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiDecertificatiPrecedente($id_chiusura, $maxAnnoCont, 'REC', $anno);
            if (count($rec) != 0) {
                $recuperiPrec["$anno"] = $rec;
            }
            $rit = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiDecertificatiPrecedente($id_chiusura, $maxAnnoCont, 'RIT', $anno);
            if (count($rit) != 0) {
                $rititiPrec["$anno"] = $rit;
            }
        }

        //compensazioni
        foreach ($periodoAnniContabili as $anno) {
            $rec = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensatiPrecedente($id_chiusura, $maxAnnoCont, 'REC');
            if (count($rec) != 0) {
                $recuperiPrecComp["$anno"] = $rec;
            }
            $rit = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensatiPrecedente($id_chiusura, $maxAnnoCont, 'RIT');
            if (count($rit) != 0) {
                $rititiPrecComp["$anno"] = $rit;
            }
            $rec = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensatiPrecedenteAda($id_chiusura, $maxAnnoCont, 'REC');
            if (count($rec) != 0) {
                $recuperiPrecCompAda["$anno"] = $rec;
            }
            $rit = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCompensatiPrecedenteAda($id_chiusura, $maxAnnoCont, 'RIT');
            if (count($rit) != 0) {
                $rititiPrecCompAda["$anno"] = $rit;
            }
        }

        $res = $this->creaArrayPrecedenti($rititiPrec, $recuperiPrec, $rititiPrecComp, $recuperiPrecComp, $recuperiPrecCompAda, $rititiPrecCompAda, $res);

        $dati["chiusura"] = $chiusura;
        $dati["menu"] = 'appendice2';
        $dati["importi_asse"] = $res;

        return $this->render("CertificazioniBundle:Appendici:appendiceChiusure2.html.twig", $dati);
    }

    /**
     * @Route("/appendici_chiusura_3/{id_chiusura}", name="appendici_chiusura_3")
     * @PaginaInfo(titolo="Elenco certificazioni associabili",sottoTitolo="Lista appendici")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Dettaglio chiusura conti")})
     */
    public function appendiceChiusura3Action($id_chiusura) {

        $em = $this->getEm();
        $chiusura = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->findOneById($id_chiusura);

        $dati["chiusura"] = $chiusura;
        $dati["menu"] = 'appendice3';

        return $this->render("CertificazioniBundle:Appendici:appendiceChiusure3.html.twig", $dati);
    }

    /**
     * @Route("/appendici_chiusura_4/{id_chiusura}", name="appendici_chiusura_4")
     * @PaginaInfo(titolo="Elenco certificazioni associabili",sottoTitolo="Lista appendici")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Dettaglio chiusura conti")})
     */
    public function appendiceChiusura4Action($id_chiusura) {

        $em = $this->getEm();
        $chiusura = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->findOneById($id_chiusura);

        $res = array();
        $res['A1'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A1', array('20', '21'));
        $res['A2'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A2', array('20', '21'));
        $res['A3'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A3', array('20', '21'));
        $res['A4'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A4', array('20', '21'));
        $res['A5'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A5', array('20', '21'));
        $res['A6'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A6', array('20', '21'));
        $res['A7'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A7', array('20', '21'));
        $res['TOTALE'] = $res['A1'] + $res['A2'] + $res['A3'] + $res['A4'] + $res['A5'] + $res['A6'] + $res['A7'];

        $res['A1DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A1', array('21'));
        $res['A2DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A2', array('21'));
        $res['A3DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A3', array('21'));
        $res['A4DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A4', array('21'));
        $res['A5DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A5', array('21'));
        $res['A6DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A6', array('21'));
        $res['A7DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseRevoche($id_chiusura, 'A7', array('21'));
        $res['TOTALEDICUI'] = $res['A1DICUI'] + $res['A2DICUI'] + $res['A3DICUI'] + $res['A4DICUI'] + $res['A5DICUI'] + $res['A6DICUI'] + $res['A7DICUI'];

        $dati["chiusura"] = $chiusura;
        $dati["menu"] = 'appendice4';
        $dati["importi_asse"] = $res;

        return $this->render("CertificazioniBundle:Appendici:appendiceChiusure4.html.twig", $dati);
    }

    /**
     * @Route("/appendici_chiusura_5/{id_chiusura}", name="appendici_chiusura_5")
     * @PaginaInfo(titolo="Elenco certificazioni associabili",sottoTitolo="Lista appendici")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Dettaglio chiusura conti")})
     */
    public function appendiceChiusura5Action($id_chiusura) {

        $em = $this->getEm();
        $chiusura = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->findOneById($id_chiusura);

        $dati["chiusura"] = $chiusura;
        $dati["menu"] = 'appendice5';

        return $this->render("CertificazioniBundle:Appendici:appendiceChiusure5.html.twig", $dati);
    }

    /**
     * @Route("/appendici_chiusura_6/{id_chiusura}", name="appendici_chiusura_6")
     * @PaginaInfo(titolo="Elenco certificazioni associabili",sottoTitolo="Lista appendici")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Dettaglio chiusura conti")})
     */
    public function appendiceChiusura6Action($id_chiusura) {

        $em = $this->getEm();
        $chiusura = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->findOneById($id_chiusura);
        $chiusure = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->findAll();

        $importoA1 = 0;
        $importoA2 = 0;
        $importoA3 = 0;
        $importoA4 = 0;
        $importoA5 = 0;
        $importoA6 = 0;
        $importoA7 = 0;

        $res = array();
        foreach ($chiusure as $chisuraFor) {
            if ($chisuraFor->getId() <= $chiusura->getId()) {
                $importoA1 += $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseStrumentiFinanziari($chisuraFor->getId(), 'A1');
                $importoA2 += $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseStrumentiFinanziari($chisuraFor->getId(), 'A2');
                $importoA3 += $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseStrumentiFinanziari($chisuraFor->getId(), 'A3');
                $importoA4 += $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseStrumentiFinanziari($chisuraFor->getId(), 'A4');
                $importoA5 += $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseStrumentiFinanziari($chisuraFor->getId(), 'A5');
                $importoA6 += $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseStrumentiFinanziari($chisuraFor->getId(), 'A6');
                $importoA7 += $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseStrumentiFinanziari($chisuraFor->getId(), 'A7');
            }
        }
        $res['A1'] = $importoA1;
        $res['A2'] = $importoA2;
        $res['A3'] = $importoA3;
        $res['A4'] = $importoA4;
        $res['A5'] = $importoA5;
        $res['A6'] = $importoA6;
        $res['A7'] = $importoA7;
        $res['TOTALE'] = $res['A1'] + $res['A2'] + $res['A3'] + $res['A4'] + $res['A5'] + $res['A6'] + $res['A7'];

        $ultimaCertificazione = $chiusura->getCertificazioni()->last();

        $res['IMPORTO_STR_A1'] = 0.00;
        $res['IMPORTO_STR_A2'] = 0.00;
        $res['IMPORTO_STR_A3'] = 0.00;
        $res['IMPORTO_STR_A4'] = 0.00;
        $res['IMPORTO_STR_A5'] = 0.00;
        $res['IMPORTO_STR_A6'] = 0.00;
        $res['IMPORTO_STR_A7'] = 0.00;
        $res['IMPORTO_STR_TOTALE'] = 0.00;

        if ($ultimaCertificazione != false) {
            foreach ($ultimaCertificazione->getCertificazioniAssi() as $certificazioneAsse) {
                $res['IMPORTO_STR_' . $certificazioneAsse->getAsse()->getCodice()] = $certificazioneAsse->getImportoStrumenti();
                $res['IMPORTO_STR_TOTALE'] += $certificazioneAsse->getImportoStrumenti();
            }
        }


        $dati["chiusura"] = $chiusura;
        $dati["menu"] = 'appendice6';
        $dati["importi_asse"] = $res;

        return $this->render("CertificazioniBundle:Appendici:appendiceChiusure6.html.twig", $dati);
    }

    /**
     * @Route("/appendici_chiusura_7/{id_chiusura}", name="appendici_chiusura_7")
     * @PaginaInfo(titolo="Elenco certificazioni associabili",sottoTitolo="Lista appendici")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Dettaglio chiusura conti")})
     */
    public function appendiceChiusura7Action($id_chiusura) {

        $em = $this->getEm();
        $chiusura = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->findOneById($id_chiusura);

        $res = array();
        $res['A1'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseAiutiStato($id_chiusura, 'A1');
        $res['A2'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseAiutiStato($id_chiusura, 'A2');
        $res['A3'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseAiutiStato($id_chiusura, 'A3');
        $res['A4'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseAiutiStato($id_chiusura, 'A4');
        $res['A5'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseAiutiStato($id_chiusura, 'A5');
        $res['A6'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseAiutiStato($id_chiusura, 'A6');
        $res['A7'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsseAiutiStato($id_chiusura, 'A7');
        $res['TOTALE'] = $res['A1'] + $res['A2'] + $res['A3'] + $res['A4'] + $res['A5'] + $res['A6'] + $res['A7'];

        $dati["chiusura"] = $chiusura;
        $dati["menu"] = 'appendice7';
        $dati["importi_asse"] = $res;

        return $this->render("CertificazioniBundle:Appendici:appendiceChiusure7.html.twig", $dati);
    }

    /**
     * @Route("/appendici_chiusura_8/{id_chiusura}", name="appendici_chiusura_8")
     * @PaginaInfo(titolo="Elenco certificazioni associabili",sottoTitolo="Lista appendici")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Dettaglio chiusura conti")})
     */
    public function appendiceChiusura8Action($id_chiusura) {

        $em = $this->getEm();
        $chiusura = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->findOneById($id_chiusura);

        $res = array();
        $res['A1'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsse($id_chiusura, 'A1');
        $res['A2'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsse($id_chiusura, 'A2');
        $res['A3'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsse($id_chiusura, 'A3');
        $res['A4'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsse($id_chiusura, 'A4');
        $res['A5'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsse($id_chiusura, 'A5');
        $res['A6'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsse($id_chiusura, 'A6');
        $res['A7'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getImportiCertificatiPerAsse($id_chiusura, 'A7');
        $res['TOTALE'] = $res['A1'] + $res['A2'] + $res['A3'] + $res['A4'] + $res['A5'] + $res['A6'] + $res['A7'];

        $res['A1INVIO'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsseApp1($id_chiusura, 'A1');
        $res['A2INVIO'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsseApp1($id_chiusura, 'A2');
        $res['A3INVIO'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsseApp1($id_chiusura, 'A3');
        $res['A4INVIO'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsseApp1($id_chiusura, 'A4');
        $res['A5INVIO'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsseApp1($id_chiusura, 'A5');
        $res['A6INVIO'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsseApp1($id_chiusura, 'A6');
        $res['A7INVIO'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsseApp1($id_chiusura, 'A7');
        $res['TOTALEINVIO'] = $res['A1INVIO'] + $res['A2INVIO'] + $res['A3INVIO'] + $res['A4INVIO'] + $res['A5INVIO'] + $res['A6INVIO'] + $res['A7INVIO'];

        $res['A1DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A1', true, true);
        $res['A2DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A2', true, true);
        $res['A3DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A3', true, true);
        $res['A4DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A4', true, true);
        $res['A5DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A5', true, true);
        $res['A6DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A6', true, true);
        $res['A7DICUI'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A7', true, true);
        $res['TOTALEDICUI'] = $res['A1DICUI'] + $res['A2DICUI'] + $res['A3DICUI'] + $res['A4DICUI'] + $res['A5DICUI'] + $res['A6DICUI'] + $res['A7DICUI'];

        $res['A1DICUI127'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A1', true, false);
        $res['A2DICUI127'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A2', true, false);
        $res['A3DICUI127'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A3', true, false);
        $res['A4DICUI127'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A4', true, false);
        $res['A5DICUI127'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A5', true, false);
        $res['A6DICUI127'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A6', true, false);
        $res['A7DICUI127'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse($id_chiusura, 'A7', true, false);
        $res['TOTALEDICUI127'] = $res['A1DICUI127'] + $res['A2DICUI127'] + $res['A3DICUI127'] + $res['A4DICUI127'] + $res['A5DICUI127'] + $res['A6DICUI127'] + $res['A7DICUI127'];

        $res['A1DICUI137'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse137($id_chiusura, 'A1', false, true);
        $res['A2DICUI137'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse137($id_chiusura, 'A2', false, true);
        $res['A3DICUI137'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse137($id_chiusura, 'A3', false, true);
        $res['A4DICUI137'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse137($id_chiusura, 'A4', false, true);
        $res['A5DICUI137'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse137($id_chiusura, 'A5', false, true);
        $res['A6DICUI137'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse137($id_chiusura, 'A6', false, true);
        $res['A7DICUI137'] = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->getRevocheInvioContiAsse137($id_chiusura, 'A7', false, true);
        $res['TOTALEDICUI137'] = $res['A1DICUI137'] + $res['A2DICUI137'] + $res['A3DICUI137'] + $res['A4DICUI137'] + $res['A5DICUI137'] + $res['A6DICUI137'] + $res['A7DICUI137'];

        $dati["chiusura"] = $chiusura;
        $dati["menu"] = 'appendice8';
        $dati["importi_asse"] = $res;
        $redirect = false;

        for ($i = 1; $i < 8; $i++) {
            $res = $this->formOsservazioneApp8($id_chiusura, $i);
            $dati["formAsse" . $i] = $res['form'];
            if ($res['submitted'] == true)
                $redirect = true;
        }

        if ($redirect == true) {
            return $this->redirectToRoute("appendici_chiusura_8", array('id_chiusura' => $id_chiusura));
        }

        return $this->render("CertificazioniBundle:Appendici:appendiceChiusure8.html.twig", $dati);
    }

    /**
     * @Route("/{id_documento}/cancella_documento_chiusura", name="cancella_documento_chiusura")
     * @Template("CertificazioniBundle:Certificazioni:dettaglioChiusuraConti.html.twig")
     * @PaginaInfo(titolo="Dettaglio chiusura dei conti", sottoTitolo="pagina di dettaglio della chiusura dei conti")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Dettaglio certificazione")})
     */
    public function cancellaDocumentoChiusuraAction($id_documento) {

        $em = $this->getEm();
        $documento = $em->getRepository("CertificazioniBundle\Entity\DocumentoCertificazioneChiusura")->find($id_documento);
        $id_chiusura = $documento->getChiusura()->getId();
        try {
            $em->remove($documento->getDocumentoFile());
            $em->remove($documento);
            $em->flush();
            return $this->addSuccessRedirect("Documento eliminato correttamente", "dettaglio_certificazioni_chiusura", array("id_chiusura" => $id_chiusura));
        } catch (ResponseException $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    /**
     * @Route("/{id_chiusura}/invia_chiusura", name="invia_chiusura")  
     */
    public function inviaChiusuraAction($id_chiusura) {

        $em = $this->getEm();
        $chiusura = $em->getRepository("CertificazioniBundle\Entity\CertificazioneChiusura")->find($id_chiusura);

        if (!$chiusura->isInviabile()) {
            $this->addFlash("error", "L'operazione non è compatibile con lo stato della chiusura dei conti");
            return $this->redirectToRoute("elenco_certificazioni");
        }

        try {
            $this->container->get("sfinge.stati")->avanzaStato($chiusura, StatoChiusuraCertificazione::CHI_INVIATA);
            $em->flush();
            $this->addFlash("success", "La chiusura dei conti è stata correttamente inviata");
        } catch (ResponseException $e) {
            $this->addFlash("error", "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza");
        }

        return $this->redirectToRoute("elenco_certificazioni");
    }

    /**
     * @Route("/{id_chiusura}/approva_chiusura", name="approva_chiusura")  
     */
    public function approvaChiusuraAction($id_chiusura) {

        $em = $this->getEm();
        $chiusura = $em->getRepository("CertificazioniBundle\Entity\CertificazioneChiusura")->find($id_chiusura);

        if (!$chiusura->isApprovabile()) {
            $this->addFlash("error", "L'operazione non è compatibile con lo stato della chiusura dei conti");
            return $this->redirectToRoute("elenco_certificazioni");
        }

        try {
            $this->container->get("sfinge.stati")->avanzaStato($chiusura, StatoChiusuraCertificazione::CHI_APPROVATA);
            $em->flush();
            $this->addFlash("success", "La chiusura dei conti è stata correttamente approvata");
        } catch (ResponseException $e) {
            $this->addFlash("error", "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza");
        }

        return $this->redirectToRoute("elenco_certificazioni");
    }

    public function formOsservazioneApp8($id_chiusura, $num_asse) {

        $res = array();
        $em = $this->getDoctrine()->getManager();
        $chiusura = $em->getRepository('CertificazioniBundle\Entity\CertificazioneChiusura')->find($id_chiusura);
        $clone = clone $chiusura;
        $request = $this->getCurrentRequest();
        $options["readonly"] = $chiusura->isChiusa();
        $options["num_asse"] = $num_asse;

        $form = $this->createForm('CertificazioniBundle\Form\OsservazioneType', $clone, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $form->get("pulsanti_$num_asse")->get('pulsante_submit')->isClicked()) {
            try {
                $chiusura->{'setOsservazioni8' . $num_asse}($clone->{'getOsservazioni8' . $num_asse}());
                $em->flush();
                $this->addFlash("success", "Dato salvato correttamente");
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
        $res['submitted'] = $form->isSubmitted();
        $res['form'] = $form->createView();
        return $res;
    }

    private function creaArrayPrecedenti($rititiPrec, $recuperiPrec, $rititiPrecCmp, $recuperiPrecCmp, $recuperiPrecCompAda, $rititiPrecCompAda, $res) {
        $anni = array();
        foreach ($rititiPrec as $rit) {
            for($i = 0; $i < count($rit); $i++) {
                if(!isset($anni["{$rit[0]['anno']}"]['RIT'])) {
                    $anni["{$rit[0]['anno']}"]['RIT'] = 0.00;
                }
                $anni["{$rit[0]['anno']}"]['RIT'] += $rit[$i]['importo'];               
            }
            foreach ($rit as $r) {
                if ($r['segnalazione_ada'] == true) {
                    $anni["{$r['anno']}"]['RITADA'] = $r['importo'];
                } else {
                    $anni["{$r['anno']}"]['RITADA'] = 0.00;
                }
            }
        }
        foreach ($rititiPrecCmp as $rit) {
            $anni["{$rit[0]['anno']}"]['RIT_CMP'] = $rit[0]['importo'];
        }
        foreach ($rititiPrecCompAda as $rit) {
            $anni["{$rit[0]['anno']}"]['RIT_CMP_ADA'] = $rit[0]['importo'];
        }

        foreach ($recuperiPrec as $rec) {
            for($i = 0; $i < count($rec); $i++) {
                if(!isset($anni["{$rec[0]['anno']}"]['REC'])) {
                    $anni["{$rec[0]['anno']}"]['REC'] = 0.00;
                }
                $anni["{$rec[0]['anno']}"]['REC'] += $rec[$i]['importo'];               
            }
            foreach ($rec as $r) {
                if ($r['segnalazione_ada'] == true) {
                    $anni["{$r['anno']}"]['RECADA'] = $r['importo'];
                } else {
                    $anni["{$r['anno']}"]['RECADA'] = 0.00;
                }
            }
        }
        foreach ($recuperiPrecCmp as $rec) {
            $anni["{$rec[0]['anno']}"]['REC_CMP'] = $rec[0]['importo'];
        }
        foreach ($recuperiPrecCompAda as $rec) {
            $anni["{$rec[0]['anno']}"]['REC_CMP_ADA'] = $rec[0]['importo'];
        }



        $res['ANNIPRE'] = $anni;
        return $res;
    }

}
