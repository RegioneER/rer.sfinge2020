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
use CertificazioniBundle\Entity\StatoChiusuraCertificazione;
use DocumentoBundle\Component\ResponseException;

/**
 * @Route("/registro")
 */
class RegistroController extends BaseController {

    /**
     * @Route("/registro_elenco_assi/", name="registro_elenco_assi")
     * @PaginaInfo(titolo="Elenco importi irregolari per asse",sottoTitolo="Lista irregolarità per asse")
     * @Menuitem(menuAttivo = "elencoAssiRegistro")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco importi irregolari per asse")})
     */
    public function registroElencoAssiAction() {

        $em = $this->getEm();

        $res = array();
        $res['A1'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A1');
        $res['A2'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A2');
        $res['A3'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A3');
        $res['A4'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A4');
        $res['A5'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A5');
        $res['A6'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A6');
        $res['A7'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A7');
        $res['TOTALE'] = $res['A1'] + $res['A2'] + $res['A3'] + $res['A4'] + $res['A5'] + $res['A6'] + $res['A7'];

        $assi = $em->getRepository('SfingeBundle\Entity\Asse')->findAll();

        $data = array();
        foreach ($assi as $asse) {
            if ($asse->getCodice() != 'A0') {
                $data[] = array("asse" => $asse, "importo" => $res[$asse->getCodice()]);
            }
        }

        return $this->render("CertificazioniBundle:Registro:elencoAssi.html.twig", array('data' => $data));
    }

    /**
     * @Route("/nonregolari_elenco_assi/", name="nonregolari_elenco_assi")
     * @PaginaInfo(titolo="Elenco importi non regolari per asse",sottoTitolo="Lista non regolari per asse")
     * @Menuitem(menuAttivo = "elencoAssiRegistro")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco importi non regolari per asse")})
     */
    public function nonRegolariElencoAssiAction() {

        $em = $this->getEm();

        $res = array();
        $res['A1'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A1');
        $res['A2'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A2');
        $res['A3'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A3');
        $res['A4'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A4');
        $res['A5'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A5');
        $res['A6'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A6');
        $res['A7'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiIrregolariPerAsse('A7');
        $res['TOTALE'] = $res['A1'] + $res['A2'] + $res['A3'] + $res['A4'] + $res['A5'] + $res['A6'] + $res['A7'];

        $assi = $em->getRepository('SfingeBundle\Entity\Asse')->findAll();

        $data = array();
        foreach ($assi as $asse) {
            if ($asse->getCodice() != 'A0') {
                $data[] = array("asse" => $asse, "importo" => $res[$asse->getCodice()]);
            }
        }

        return $this->render("CertificazioniBundle:Registro:elencoAssiNonRegolari.html.twig", array('data' => $data));
    }

    /**
     * @Route("/richieste_elenco_assi/{codice_asse}/{sort}/{direction}/{page}", defaults={"sort" = "i.id", "direction" = "asc", "page" = "1"}, name="richieste_elenco_assi")
     * @PaginaInfo(titolo="Elenco richieste irregolari",sottoTitolo="Lista richieste irregolari")
     * @Menuitem(menuAttivo = "elencoAssiRegistro")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco importi irregolari per asse", route="registro_elenco_assi"),
     *                       @ElementoBreadcrumb(testo="Elenco richieste irregolari")
     * })
     */
    public function elencoRichiesteAction($codice_asse) {
        $datiRicerca = new \CertificazioniBundle\Form\Entity\RicercaDebitori();
        $datiRicerca->setAsse($codice_asse);

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return $this->render('CertificazioniBundle:Registro:elencoDebitoriAsse.html.twig', array(
                    'revoche' => $risultato["risultato"],
                    "formRicercaDebitori" => $risultato["form_ricerca"],
                    "filtro_attivo" => $risultato["filtro_attivo"],
                    'asse' => $codice_asse));
    }

    /**
     * @Route("/richieste_elenco_assi_pulisci/{codice_asse}", name="richieste_elenco_assi_pulisci")
     */
    public function elencoRichiestePulisciAction($codice_asse) {
        $this->get("ricerca")->pulisci(new \CertificazioniBundle\Form\Entity\RicercaDebitori());
        return $this->redirectToRoute("richieste_elenco_assi", array('codice_asse' => $codice_asse));
    }

    /**
     * @Route("/{codice_asse}/dettaglio_irregolarita/{id_richiesta}/{id_revoca}", name="dettaglio_irregolarita")
     * @PaginaInfo(titolo="Dettaglio irregolarità", sottoTitolo="pagina di dettaglio della irregolatrità")
     * @Menuitem(menuAttivo = "elencoAssiRegistro")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco importi irregolari per asse", route="registro_elenco_assi"),
     *                       @ElementoBreadcrumb(testo="Elenco richieste irregolari", route="richieste_elenco_assi", parametri={"codice_asse"}),
     * 						 @ElementoBreadcrumb(testo="Dettaglio irregolarità"),
     * })
     */
    public function dettaglioIrregolaritaAction($id_richiesta, $codice_asse, $id_revoca) {
        $request = $this->getCurrentRequest();
        $em = $this->getEm();
        $richiesta = $em->getRepository('RichiesteBundle\Entity\Richiesta')->findOneById($id_richiesta);
        $revoca = $em->getRepository('AttuazioneControlloBundle\Entity\Revoche\Revoca')->findOneById($id_revoca);
        $atc = $richiesta->getAttuazioneControllo();

        $ultimaVar = $atc->getUltimaVariazioneApprovata();
        $contributo = 0.00;
        if (!is_null($ultimaVar)) {
            $contributo = $ultimaVar->getContributoAmmesso();
        } else {
            $contributo = $richiesta->getIstruttoria()->getContributoAmmesso();
        }
        $importo_mandato = 0.00;
        $importo_mandato_fesr = 0.00;
        $importo_mandato_stato = 0.00;
        $importo_mandato_regione = 0.00;

        foreach ($atc->getPagamenti() as $pagamento) {
            if (!is_null($pagamento->getMandatoPagamento())) {
                $importo_mandato += $pagamento->getMandatoPagamento()->getImportoPagato();
                $importo_mandato_fesr += $pagamento->getMandatoPagamento()->getQuotaFesr();
                $importo_mandato_stato += $pagamento->getMandatoPagamento()->getQuotaStato();
                $importo_mandato_regione += $pagamento->getMandatoPagamento()->getQuotaRegione();
            }
            $arrayCert = array();
            $arrayAnnoCont = array();
            $arrayCertId = array();
            if (count($pagamento->getCertificazioni()) > 0) {
                foreach ($pagamento->getCertificazioni() as $certificazione_pagamento) {
                    if ($certificazione_pagamento->getImporto() < 0 && $certificazione_pagamento->isIrregolarita() && !in_array($certificazione_pagamento->getCertificazione()->getId(), $arrayCertId)) {
                        $arrayCert[] = $certificazione_pagamento->getCertificazione()->__toString();
                        $arrayAnnoCont[] = $certificazione_pagamento->getCertificazione()->getAnnoContabile();
                        $arrayCertId[] = $certificazione_pagamento->getCertificazione()->getId();
                    }
                }
            }
        }

        $rec_pendente = true;
        $importo_recuperato = 0.00;
        $importo_interessi_legali = 0.00;
        $importo_interessi_mora = 0.00;
        $importo_sanzione = 0.00;
        $contributo_corso_recupero = 0.00;
        $stato_recupero = 'IN_CORSO';

        $rate = array();
        foreach ($revoca->getRecuperi() as $recupero) {
            $rate = array_merge($rate, $recupero->getRate()->toArray());
            $importo_recuperato = $recupero->getContributoRestituito();
            $importo_interessi_legali = $recupero->getImportoInteresseLegale();
            $importo_interessi_mora = $recupero->getImportoInteresseMora();
            $importo_sanzione = $recupero->getImportoSanzione();
            $contributo_corso_recupero = $recupero->getContributoCorsoRecupero();
        }

        foreach ($revoca->getRecuperi() as $recupero) {
            if ($recupero->getTipoFaseRecupero()->getCodice() == 'COMPLETO') {
                $stato_recupero = 'COMPLETO';
                break;
            }
            if ($recupero->getTipoFaseRecupero()->getCodice() == 'MANCATO') {
                $stato_recupero = 'MANCATO';
                break;
            }
        }


        if (is_null($revoca->getRegistro()) == true) {
            $registro = new \CertificazioniBundle\Entity\RegistroDebitori();
            $registro->setRichiesta($richiesta);
            $registro->setRevoca($revoca);
        } else {
            $registro = $revoca->getRegistro();
        }

        $options = array();
        $options["url_indietro"] = $this->generateUrl("richieste_elenco_assi", array("codice_asse" => $codice_asse));

        $form = $this->createForm("CertificazioniBundle\Form\RegistroDebitoriType", $registro, $options);

        if ($request->isMethod("POST")) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $em->persist($registro);
                    $em->flush();
                    return $this->addSuccessRedirect("Dati salvati correttamente", "richieste_elenco_assi", array("codice_asse" => $codice_asse));
                } catch (ResponseException $e) {
                    $this->addFlash("error", $e->getMessage());
                }
            }
        }

        return $this->render("CertificazioniBundle:Registro:dettaglioIrregolarita.html.twig", array(
                    'richiesta' => $richiesta,
                    'contributo' => $contributo,
                    'importo_mandato' => $importo_mandato,
                    'importo_mandato_fesr' => $importo_mandato_fesr,
                    'importo_mandato_stato' => $importo_mandato_stato,
                    'importo_mandato_regione' => $importo_mandato_regione,
                    'importo_sanzione' => $importo_sanzione,
                    'penalita' => $revoca->hasPenalita(),
                    'revoca' => $revoca,
                    'recuperi' => $revoca->getRecuperi(),
                    'rate' => $rate,
                    'certificazioni' => $arrayCert,
                    'anni_contabili' => $arrayAnnoCont,
                    'rec_pendente' => $rec_pendente,
                    'importo_recuperato' => $importo_recuperato,
                    'contributo_corso_recupero' => $contributo_corso_recupero,
                    'importo_interessi_legali' => $importo_interessi_legali,
                    'importo_interessi_mora' => $importo_interessi_mora,
                    'stato_recupero' => $stato_recupero,
                    'art137' => $atc->isExArticolo137(),
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{codice_asse}/dettaglio_irregolarita_documenti/{id_richiesta}/{id_revoca}", name="dettaglio_irregolarita_documenti")
     * @PaginaInfo(titolo="Dettaglio irregolarità", sottoTitolo="pagina di dettaglio della irregolatrità")
     * @Menuitem(menuAttivo = "elencoAssiRegistro")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco importi irregolari per asse", route="registro_elenco_assi"),
     *                       @ElementoBreadcrumb(testo="Elenco richieste irregolari", route="richieste_elenco_assi", parametri={"codice_asse"}),
     * 						 @ElementoBreadcrumb(testo="Documenti irregolarità"),
     * })
     */
    public function caricaDocumentiRegistroAction($id_richiesta, $codice_asse, $id_revoca) {

        $em = $this->getEm();
        $request = $this->getCurrentRequest();
        $richiesta = $em->getRepository('RichiesteBundle\Entity\Richiesta')->findOneById($id_richiesta);
        $revoca = $em->getRepository('AttuazioneControlloBundle\Entity\Revoche\Revoca')->findOneById($id_revoca);

        if (is_null($revoca->getRegistro()) == true) {
            $registro = new \CertificazioniBundle\Entity\RegistroDebitori();
            $registro->setRichiesta($richiesta);
            $registro->setRevoca($revoca);
        } else {
            $registro = $revoca->getRegistro();
        }
        $asse = $richiesta->getProcedura()->getAsse();

        $documento_registro = new \CertificazioniBundle\Entity\DocumentoRegistro();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();

        $documenti_caricati = $registro->getDocumentiRegistro();

        // Form per caricamento documento certificatore agrea	
        $tipi_doc = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array('tipologia' => "registro_debitori"));

        $form_upload = $this->createForm("DocumentoBundle\Form\Type\DocumentoFileType", $documento_file, array('lista_tipi' => $tipi_doc));
        $form_upload->add("submit", "Symfony\Component\Form\Extension\Core\Type\SubmitType", array("label" => "Carica"));

        $form_upload_view = $form_upload->createView();

        if ($request->isMethod("POST")) {

            $form_upload->handleRequest($request);
            if ($form_upload->isValid()) {
                try {

                    $this->container->get("documenti")->carica($documento_file, 0);
                    $em->persist($registro);
                    $documento_registro->setDocumentoFile($documento_file);
                    $documento_registro->setRegistro($registro);
                    $em->persist($documento_registro);

                    $em->flush();

                    return $this->addSuccessRedirect("Documento caricato correttamente", "dettaglio_irregolarita_documenti", array('id_richiesta' => $id_richiesta, 'codice_asse' => $codice_asse, 'id_revoca' => $id_revoca,));
                } catch (ResponseException $e) {
                    $this->addFlash("error", $e->getMessage());
                }
            }
        }

        $dati = array(
            'id_richiesta' => $id_richiesta,
            'id_revoca' => $id_revoca,
            'documenti_caricati' => $documenti_caricati,
            'form_upload_view' => $form_upload_view,
            'asse_codice' => $asse->getCodice(),
        );

        return $this->render("CertificazioniBundle:Registro:caricaDocumentiRegistro.html.twig", $dati);
    }

    /**
     * @Route("/{codice_asse}/elimina_documento_registro/{id_documento_registro}", name="elimina_documento_registro")
     */
    public function eliminaDocumentoRegistro($id_documento_registro, $codice_asse) {
        $em = $this->getEm();
        $documento = $em->getRepository("CertificazioniBundle\Entity\DocumentoRegistro")->find($id_documento_registro);
        $id_richiesta = $documento->getRegistro()->getRichiesta()->getId();
        try {
            $em->remove($documento->getDocumentoFile());
            $em->remove($documento);
            $em->flush();
            $response = new \RichiesteBundle\Service\GestoreResponse($this->addSuccessRedirect("Documento eliminato correttamente", "dettaglio_irregolarita_documenti", array("id_richiesta" => $id_richiesta, "codice_asse" => $codice_asse)));
            return $response->getResponse();
        } catch (ResponseException $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    /**
     * @Route("/{id_atto}/scarica_atto_revoca", name="scarica_atto_revoca")
     */
    public function scaricaAttoAction($id_atto) {

        $atto = $this->getEm()->getRepository("AttuazioneControlloBundle:Revoche\AttoRevoca")->findOneById($id_atto);

        if (is_null($atto)) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "registro_elenco_assi");
        }

        if (is_null($atto->getDocumento())) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "registro_elenco_assi");
        }

        return $this->get("documenti")->scaricaDaId($atto->getDocumento()->getId());
    }

    /**
     * @Route("/olaf_elenco_assi/", name="olaf_elenco_assi")
     * @PaginaInfo(titolo="Elenco olaf per asse",sottoTitolo="Lista irregolarità per asse")
     * @Menuitem(menuAttivo = "elencoAssiRegistro")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco olaf per asse")})
     */
    public function olafElencoAssiAction() {

        $em = $this->getEm();

        $res = array();
        $res['A1'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiOlafPerAsse('A1');
        $res['A2'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiOlafPerAsse('A2');
        $res['A3'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiOlafPerAsse('A3');
        $res['A4'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiOlafPerAsse('A4');
        $res['A5'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiOlafPerAsse('A5');
        $res['A6'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiOlafPerAsse('A6');
        $res['A7'] = $em->getRepository('CertificazioniBundle\Entity\RegistroDebitori')->getImportiOlafPerAsse('A7');
        $res['TOTALE'] = $res['A1'] + $res['A2'] + $res['A3'] + $res['A4'] + $res['A5'] + $res['A6'] + $res['A7'];

        $assi = $em->getRepository('SfingeBundle\Entity\Asse')->findAll();

        $data = array();
        foreach ($assi as $asse) {
            if ($asse->getCodice() != 'A0') {
                $data[] = array("asse" => $asse, "importo" => $res[$asse->getCodice()]);
            }
        }

        return $this->render("CertificazioniBundle:Registro:elencoOlafAssi.html.twig", array('data' => $data));
    }

    /**
     * @Route("/elenco_olaf/{codice_asse}", name="elenco_olaf")
     * @PaginaInfo(titolo="Elenco olaf",sottoTitolo="Lista olaf")
     * @Menuitem(menuAttivo = "elencoAssiRegistro")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco olaf per asse", route="olaf_elenco_assi"),
     *                       @ElementoBreadcrumb(testo="Elenco olaf")
     * })
     */
    public function elencoRichiesteOlafAction($codice_asse) {
        $datiRicerca = new \CertificazioniBundle\Form\Entity\RicercaDebitoriOlaf();
        $datiRicerca->setAsse($codice_asse);

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return $this->render('CertificazioniBundle:Registro:elencoDebitoriOlafAsse.html.twig', array(
                    'revoche' => $risultato["risultato"],
                    "formRicercaDebitori" => $risultato["form_ricerca"],
                    "filtro_attivo" => $risultato["filtro_attivo"],
                    'asse' => $codice_asse));
    }

    /**
     * @Route("/elenco_olaf_pulisci/{codice_asse}", name="elenco_olaf_pulisci")
     */
    public function elencoOlafPulisciAction($codice_asse) {
        $this->get("ricerca")->pulisci(new \CertificazioniBundle\Form\Entity\RicercaDebitoriOlaf());
        return $this->redirectToRoute("elenco_olaf", array('codice_asse' => $codice_asse));
    }

    /**
     * @Route("/{codice_asse}/dettaglio_olaf/{id_richiesta}/{id_revoca}", name="dettaglio_olaf")
     * @PaginaInfo(titolo="Dettaglio irregolarità", sottoTitolo="pagina di dettaglio della olaf")
     * @Menuitem(menuAttivo = "elencoAssiRegistro")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco importi olaf asse", route="olaf_elenco_assi"),
     *                       @ElementoBreadcrumb(testo="Elenco olaf", route="elenco_olaf", parametri={"codice_asse"}),
     * 						 @ElementoBreadcrumb(testo="Dettaglio olaf"),
     * })
     */
    public function dettaglioOlafAction($id_richiesta, $codice_asse, $id_revoca) {
        $em = $this->getEm();
        $richiesta = $em->getRepository('RichiesteBundle\Entity\Richiesta')->findOneById($id_richiesta);
        $revoca = $em->getRepository('AttuazioneControlloBundle\Entity\Revoche\Revoca')->findOneById($id_revoca);
        $atc = $richiesta->getAttuazioneControllo();

        $ultimaVar = $atc->getUltimaVariazioneApprovata();
        $contributo = 0.00;
        if (!is_null($ultimaVar)) {
            $contributo = $ultimaVar->getContributoAmmesso();
        } else {
            $contributo = $richiesta->getIstruttoria()->getContributoAmmesso();
        }
        $importo_mandato = 0.00;
        $importo_mandato_fesr = 0.00;
        $importo_mandato_stato = 0.00;
        $importo_mandato_regione = 0.00;

        foreach ($atc->getPagamenti() as $pagamento) {
            if (!is_null($pagamento->getMandatoPagamento())) {
                $importo_mandato += $pagamento->getMandatoPagamento()->getImportoPagato();
                $importo_mandato_fesr += $pagamento->getMandatoPagamento()->getQuotaFesr();
                $importo_mandato_stato += $pagamento->getMandatoPagamento()->getQuotaStato();
                $importo_mandato_regione += $pagamento->getMandatoPagamento()->getQuotaRegione();
            }
            $arrayCert = array();
            $arrayAnnoCont = array();
            $arrayCertId = array();
            if (count($pagamento->getCertificazioni()) > 0) {
                foreach ($pagamento->getCertificazioni() as $certificazione_pagamento) {
                    if ($certificazione_pagamento->getImporto() < 0 && $certificazione_pagamento->isIrregolarita() && !in_array($certificazione_pagamento->getCertificazione()->getId(), $arrayCertId)) {
                        $arrayCert[] = $certificazione_pagamento->getCertificazione()->__toString();
                        $arrayAnnoCont[] = $certificazione_pagamento->getCertificazione()->getAnnoContabile();
                        $arrayCertId[] = $certificazione_pagamento->getCertificazione()->getId();
                    }
                }
            }
        }

        return $this->render("CertificazioniBundle:Registro:dettaglioOlaf.html.twig", array(
                    'richiesta' => $richiesta,
                    'contributo' => $contributo,
                    'importo_mandato' => $importo_mandato,
                    'importo_mandato_fesr' => $importo_mandato_fesr,
                    'importo_mandato_stato' => $importo_mandato_stato,
                    'importo_mandato_regione' => $importo_mandato_regione,
                    'revoca' => $revoca,
                    'recuperi' => $revoca->getRecuperi(),
                    'certificazioni' => $arrayCert,
                    'anni_contabili' => $arrayAnnoCont,
                    'art137' => $atc->isExArticolo137(),
        ));
    }

}
