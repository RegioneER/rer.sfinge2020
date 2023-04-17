<?php

namespace AuditBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use DocumentoBundle\Entity\DocumentoFile;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use DocumentoBundle\Component\ResponseException;
use DocumentoBundle\Form\Type\DocumentoFileType;
use BaseBundle\Form\SalvaType;
use AuditBundle\Entity\DocumentoCampioneGiustificativo;
use SfingeBundle\Form\Entity\RicercaProcedura;

class EstrazioniController extends BaseController {

    /**
     * @Route("/elenco_atti_amministrativi_audit/{sort}/{direction}/{page}", defaults={"sort" = "s.id", "direction" = "asc", "page" = "1"}, name="elenco_atti_amministrativi_audit")
     * @Template("AuditBundle:Estrazioni:elencoProcedure.html.twig")
     * @Menuitem(menuAttivo = "audit")
     * @PaginaInfo(titolo="Elenco procedure",sottoTitolo="pagina per la gestione delle procedure operative censite a sistema")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi")})
     */
    public function elencoProcedureAction() {
        $datiRicerca = new RicercaProcedura();
        $datiRicerca->setUtente($this->getUser());
        $datiRicerca->setAdmin($this->isAdmin());

        $em = $this->getEm();
        $responsabili = $em->getRepository("SfingeBundle\Entity\Utente")->cercaUtentiPaDTO();
        $datiRicerca->setResponsabili($responsabili);

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        $dati = array(
            'procedure' => $risultato["risultato"],
            "form_ricerca_procedure" => $risultato["form_ricerca"],
            "filtro_attivo" => $risultato["filtro_attivo"],
        );
        return $dati;
    }

    /**
     * @Route("/elenco_atti_amministrativi_audit_pulisci", name="elenco_atti_amministrativi_audit_pulisci")
     */
    public function elencoProcedurePulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaProcedura());
        return $this->redirectToRoute("elenco_atti_amministrativi_audit");
    }

    /**
     * @Route("/scarica_estrazione_procedure", name="scarica_estrazione_procedure")
     * @return StreamedResponse
     */
    public function scaricaEstrazioneProcedure() {
        \ini_set('memory_limit', '512M');
        $gestore = $this->get('audit_estrazioni');/** @var AuditBundle\Service\GestoreEstrazioni */
        $excelWriter = $gestore->getProcedure();

        $response = new StreamedResponse(function () use ($excelWriter) {
            $excelWriter->save('php://output');
        }, \Symfony\Component\HttpFoundation\Response::HTTP_OK, array(
            'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=1')
        );
        $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione procedure.xls'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route("/scarica_estrazione_operazioni/{id_procedura}", defaults={"id_procedura" = "all"}, name="scarica_estrazione_operazioni")
     * @return StreamedResponse
     */
    public function scaricaEstrazioneOperazioni($id_procedura) {
        \ini_set('memory_limit', '512M');
        $gestore = $this->get('audit_estrazioni');/** @var AuditBundle\Service\GestoreEstrazioni */
        $excelWriter = $gestore->getOperazioni($id_procedura);

        $response = new StreamedResponse(function () use ($excelWriter) {
            $excelWriter->save('php://output');
        }, \Symfony\Component\HttpFoundation\Response::HTTP_OK, array(
            'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=1')
        );
        $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione operazion..xls'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route("/scarica_estrazione_pagamenti/{id_procedura}", defaults={"id_procedura" = "all"}, name="scarica_estrazione_pagamenti")
     * @return StreamedResponse
     */
    public function scaricaEstrazionePagamenti($id_procedura) {
        \ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $gestore = $this->get('audit_estrazioni');/** @var AuditBundle\Service\GestoreEstrazioni */
        $excelWriter = $gestore->getPagamenti($id_procedura);

        $response = new StreamedResponse(function () use ($excelWriter) {
            $excelWriter->save('php://output');
        }, \Symfony\Component\HttpFoundation\Response::HTTP_OK, array(
            'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=1')
        );
        $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione pagamenti.xls'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route("/scarica_estrazione_giustificativi/{id_procedura}", defaults={"id_procedura" = "all"}, name="scarica_estrazione_giustificativi")
     * @return StreamedResponse
     */
    public function scaricaEstrazioneGiustificativi($id_procedura) {
        \ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $gestore = $this->get('audit_estrazioni');/** @var AuditBundle\Service\GestoreEstrazioni */
        $excelWriter = $gestore->getGiustificativi($id_procedura);

        $response = new StreamedResponse(function () use ($excelWriter) {
            $excelWriter->save('php://output');
        }, \Symfony\Component\HttpFoundation\Response::HTTP_OK, array(
            'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=1')
        );
        $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione giustificativi.xls'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
    
    /**
     * @Route("/scarica_estrazione_aggiudicazioni/{id_procedura}", defaults={"id_procedura" = "all"}, name="scarica_estrazione_aggiudicazioni")
     * @return StreamedResponse
     */
    public function scaricaEstrazioneAggiudicazioni($id_procedura) {
        \ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $gestore = $this->get('audit_estrazioni');/** @var AuditBundle\Service\GestoreEstrazioni */
        $excelWriter = $gestore->getProcedureAggiudicazione($id_procedura);

        $response = new StreamedResponse(function () use ($excelWriter) {
            $excelWriter->save('php://output');
        }, \Symfony\Component\HttpFoundation\Response::HTTP_OK, array(
            'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=1')
        );
        $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione procedura aggiudicazione.xls'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
    
    /**
     * @Route("/scarica_estrazione_controlli/{id_procedura}", defaults={"id_procedura" = "all"}, name="scarica_estrazione_controlli")
     * @return StreamedResponse
     */
    public function scaricaEstrazioneControlli($id_procedura) {
        \ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $gestore = $this->get('audit_estrazioni');/** @var AuditBundle\Service\GestoreEstrazioni */
        $excelWriter = $gestore->getControlliLoco($id_procedura);

        $response = new StreamedResponse(function () use ($excelWriter) {
            $excelWriter->save('php://output');
        }, \Symfony\Component\HttpFoundation\Response::HTTP_OK, array(
            'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=1')
        );
        $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione controlli.xls'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
    
    /**
     * @Route("/scarica_estrazione_decertificazioni/{id_procedura}", defaults={"id_procedura" = "all"}, name="scarica_estrazione_decertificazioni")
     * @return StreamedResponse
     */
    public function scaricaDecertificazioni($id_procedura) {
        \ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $gestore = $this->get('audit_estrazioni');/** @var AuditBundle\Service\GestoreEstrazioni */
        $excelWriter = $gestore->getDecertificazioni($id_procedura);

        $response = new StreamedResponse(function () use ($excelWriter) {
            $excelWriter->save('php://output');
        }, \Symfony\Component\HttpFoundation\Response::HTTP_OK, array(
            'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=1')
        );
        $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione decertificazioni.xls'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
    
    /**
     * @Route("/scarica_estrazione_contratti/{id_procedura}", defaults={"id_procedura" = "all"}, name="scarica_estrazione_contratti")
     * @return StreamedResponse
     */
    public function scaricaContratti() {
        \ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $gestore = $this->get('audit_estrazioni');/** @var AuditBundle\Service\GestoreEstrazioni */
        $excelWriter = $gestore->getContratti(140);

        $response = new StreamedResponse(function () use ($excelWriter) {
            $excelWriter->save('php://output');
        }, \Symfony\Component\HttpFoundation\Response::HTTP_OK, array(
            'Content-Type' => 'text/vnd.ms-excel; charset=utf-8',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=1')
        );
        $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione decertificazioni.xls'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    
}
