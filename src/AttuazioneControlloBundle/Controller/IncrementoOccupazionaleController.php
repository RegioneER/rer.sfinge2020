<?php

namespace AttuazioneControlloBundle\Controller;

use AnagraficheBundle\Entity\DocumentoPersonale;
use AnagraficheBundle\Entity\Personale;
use AttuazioneControlloBundle\Entity\IncrementoOccupazionale;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Service\IGestoreIncrementoOccupazionale;
use BaseBundle\Controller\BaseController;
use DocumentoBundle\Entity\DocumentoFile;
use Exception;
use RichiesteBundle\Entity\Proponente;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/beneficiario/incremento_occupazionale")
 */
class IncrementoOccupazionaleController extends BaseController
{
    /**
     * @param int $id_pagamento
     * @return Pagamento|null
     */
    protected function getPagamento(int $id_pagamento): ?Pagamento 
    {
        $pagamento = $this->getEm()->getRepository('AttuazioneControlloBundle\Entity\Pagamento')->find($id_pagamento);
        return $pagamento;
    }

    /**
     * @param int $id_personale
     * @return Personale|null
     */
    protected function getPersonale(int $id_personale): ?Personale
    {
        $personale = $this->getEm()->getRepository('AnagraficheBundle:Personale')->find($id_personale);
        return $personale;
    }

    /**
     * @param int $id_proponente
     * @return Proponente|null
     */
    protected function getProponente(int $id_proponente): ?Proponente
    {
        $proponente = $this->getEm()->getRepository('RichiesteBundle:Proponente')->find($id_proponente);
        return $proponente;
    }

    /**
     * @param int $id_documento_personale
     * @return DocumentoPersonale|null
     */
    protected function getDocumentoPersonale(int $id_documento_personale): ?DocumentoPersonale
    {
        $documentoPersonale = $this->getEm()->getRepository('AnagraficheBundle:DocumentoPersonale')->find($id_documento_personale);
        return $documentoPersonale;
    }

    /**
     * @param int $id_documento
     * @return DocumentoFile|null
     */
    protected function getDocumentoFile(int $id_documento): ?DocumentoFile
    {
        $documentoFile = $this->getEm()->getRepository('DocumentoBundle:DocumentoFile')->find($id_documento);
        return $documentoFile;
    }

    /**
     * @param int $id_incremento_occupazionale
     * @return IncrementoOccupazionale|null
     */
    protected function getIncrementoOccupazionale(int $id_incremento_occupazionale): ?IncrementoOccupazionale
    {
        $incrementoOccupazionale = $this->getEm()->getRepository('AttuazioneControlloBundle:IncrementoOccupazionale')->find($id_incremento_occupazionale);
        return $incrementoOccupazionale;
    }

    /**
     * @param Procedura|null $procedura
     * @return IGestoreIncrementoOccupazionale
     * @throws Exception
     */
    protected function getGestoreIncrementoOccupazionale(?Procedura $procedura = null): IGestoreIncrementoOccupazionale
    {
        return $this->get("gestore_incremento_occupazionale")->getGestore($procedura);
    }
    
    /**
     * @param int $id_pagamento
     * 
     * @Route("/{id_pagamento}/dettaglio", name="dettaglio_incremento_occupazionale")
     * @PaginaInfo(titolo="Incremento occupazionale", sottoTitolo="Conferma incremento occupazionale")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     * 
     * @return mixed
     * @throws Exception
     */
    public function dettaglioIncrementoOccupazionaleAction(int $id_pagamento)
    {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestoreIncrementoOccupazionale($pagamento->getProcedura())->dettaglioIncrementoOccupazionale($pagamento);
    }

    /**
     * @param int $id_pagamento
     * @param int $id_proponente
     * @param string $tipo_documento
     *
     * @Route("/{id_pagamento}/carica_documento_incremento_occupazionale/{id_proponente}/{tipo_documento}", name="carica_documento_incremento_occupazionale")
     * @PaginaInfo(titolo="Carica documento incremento occupazionale", sottoTitolo="pagina di caricamento dell'allegato per l'incremento occupazionale")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     *
     * @return mixed
     * @throws Exception
     */
    public function caricaDocumentoIncrementoOccupazionaleAction(int $id_pagamento, int $id_proponente, string $tipo_documento)
    {
        $pagamento = $this->getPagamento($id_pagamento);
        $proponente = $this->getProponente($id_proponente);
        return $this->getGestoreIncrementoOccupazionale($pagamento->getProcedura())->caricaDocumentoIncrementoOccupazionale($pagamento, $proponente, $tipo_documento);
    }

    /**
     * @param int id_incremento_occupazionale
     * @param int $id_documento
     *
     * @Route("/{id_incremento_occupazionale}/elimina_documento_incremento_occupazionale_dm10/{id_documento}", name="elimina_documento_incremento_occupazionale_dm10")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:IncrementoOccupazionale", opzioni={"id": "id_incremento_occupazionale"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     *
     * @return RedirectResponse
     */
    public function eliminaDocumentoIncrementoOccupazionaleDM10Action(int $id_incremento_occupazionale, int $id_documento)
    {
        $documentoFile = $this->getDocumentoFile($id_documento);
        $incrementoOccupazionale = $this->getIncrementoOccupazionale($id_incremento_occupazionale);
        $pagamento = $incrementoOccupazionale->getPagamento();

        try {
            $response = $this->getGestoreIncrementoOccupazionale($pagamento->getProcedura())->eliminaDocumentoIncrementoOccupazionaleDM10($incrementoOccupazionale, $documentoFile);
            return $response->getResponse();
        } catch (Exception $e) {
            return $this->addErrorRedirect("Errore generico", "dettaglio_incremento_occupazionale",
                ['id_pagamento' => $pagamento->getId()]);
        }
    }

    /**
     * @param int $id_pagamento
     * 
     * @Route("/aggiungi_nuovo_dipendente/{id_pagamento}", name="aggiungi_nuovo_dipendente")
     * @PaginaInfo(titolo="Dettaglio nuovo dipendente")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     * 
     * @return mixed
     * @throws Exception
     */
    public function aggiungiNuovoDipendenteAction(int $id_pagamento)
    {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestoreIncrementoOccupazionale($pagamento->getProcedura())->aggiungiNuovoDipendente($pagamento);
    }

    /**
     * @param int $id_nuovo_dipendente
     * 
     * @Route("/modifica_nuovo_dipendente/{id_nuovo_dipendente}", name="modifica_nuovo_dipendente")
     * @PaginaInfo(titolo="Dettaglio nuovo dipendente")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AnagraficheBundle:Personale", opzioni={"id": "id_nuovo_dipendente"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     * 
     * @return mixed
     * @throws Exception
     */
    public function modificaNuovoDipendenteAction(int $id_nuovo_dipendente)
    {
        $nuovoDipendente = $this->getPersonale($id_nuovo_dipendente);
        $pagamento = $nuovoDipendente->getPagamento();
        return $this->getGestoreIncrementoOccupazionale($pagamento->getProcedura())->modificaNuovoDipendente($nuovoDipendente);
    }

    /**
     * @param int $id_nuovo_dipendente
     * 
     * @Route("/elimina_nuovo_dipendente/{id_nuovo_dipendente}", name="elimina_nuovo_dipendente")
     * @ControlloAccesso(contesto="pagamento", classe="AnagraficheBundle:Personale", opzioni={"id": "id_nuovo_dipendente"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     * 
     * @return RedirectResponse
     */
    public function eliminaNuovoDipendenteAction(int $id_nuovo_dipendente)
    {
        $this->get('base')->checkCsrf('token');
        $nuovoDipendente = $this->getPersonale($id_nuovo_dipendente);

        $pagamento = $nuovoDipendente->getPagamento();
        try {
            $response = $this->getGestoreIncrementoOccupazionale($pagamento->getProcedura())->eliminaNuovoDipendente($nuovoDipendente);
            return $response->getResponse();
        } catch (Exception $e) {
            return $this->addErrorRedirect("Errore generico", "dettaglio_incremento_occupazionale",
                ['id_pagamento' => $pagamento->getId()]);
        }
    }

    /**
     * @param int $id_pagamento
     * @param int $id_personale
     * @param string $tipo_documento
     * 
     * @Route("/{id_pagamento}/carica_documento_personale_incremento_occupazionale/{id_personale}/{tipo_documento}", name="carica_documento_personale_incremento_occupazionale")
     * @PaginaInfo(titolo="Carica documento incremento occupazionale", sottoTitolo="pagina di caricamento dell'allegato per l'incremento occupazionale")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     * 
     * @return mixed
     * @throws Exception
     */
    public function caricaDocumentoPersonaleIncrementoOccupazionaleAction(int $id_pagamento, int $id_personale, string $tipo_documento)
    {
        $pagamento = $this->getPagamento($id_pagamento);
        $personale = $this->getPersonale($id_personale);
        return $this->getGestoreIncrementoOccupazionale($pagamento->getProcedura())->caricaDocumentoPersonaleIncrementoOccupazionale($pagamento, $personale, $tipo_documento);
    }

    /**
     * @param int $id_documento_personale
     * 
     * @Route("/{id_pagamento}/elimina_documento_personale_incremento_occupazionale/{id_documento_personale}", name="elimina_documento_personale_incremento_occupazionale")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id": "id_pagamento"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     *
     * @return RedirectResponse
     */
    public function eliminaDocumentoPersonaleIncrementoOccupazionaleAction(int $id_documento_personale)
    {
        $documentoPersonale = $this->getDocumentoPersonale($id_documento_personale);
        $pagamento = $documentoPersonale->getPersonale()->getPagamento();

        try {
            $response = $this->getGestoreIncrementoOccupazionale($pagamento->getProcedura())->eliminaDocumentoPersonaleIncrementoOccupazionale($documentoPersonale);
            return $response->getResponse();
        } catch (Exception $e) {
            return $this->addErrorRedirect("Errore generico", "dettaglio_incremento_occupazionale",
                ['id_pagamento' => $pagamento->getId()]);
        }
    }

    /**
     * @param int id_incremento_occupazionale
     * @param int id_doc_incremento_occupazionale
     * 
     * @Route("/{id_incremento_occupazionale}/elimina_documento_incremento_occupazionale/{id_doc_incremento_occupazionale}", name="elimina_documento_incremento_occupazionale")
     * @ControlloAccesso(contesto="pagamento", classe="AttuazioneControlloBundle:IncrementoOccupazionale", opzioni={"id": "id_incremento_occupazionale"}, azione=\AttuazioneControlloBundle\Security\PagamentoVoter::WRITE)
     *
     * @return RedirectResponse
     */
    public function eliminaDocumentoIncrementoOccupazionaleAction(int $id_incremento_occupazionale, int $id_doc_incremento_occupazionale)
    {
        $documentoIncrementoOccupazionale = $this->getEm()->getRepository('AttuazioneControlloBundle:DocumentoIncrementoOccupazionale')->find($id_doc_incremento_occupazionale);
        $incrementoOccupazionale = $documentoIncrementoOccupazionale->getIncrementoOccupazionale();
        $pagamento = $incrementoOccupazionale->getPagamento();

        try {
            $response = $this->getGestoreIncrementoOccupazionale($pagamento->getProcedura())->eliminaDocumentoIncrementoOccupazionale($documentoIncrementoOccupazionale);
            return $response->getResponse();
        } catch (Exception $e) {
            return $this->addErrorRedirect("Errore generico", "dettaglio_incremento_occupazionale",
                ['id_pagamento' => $pagamento->getId()]);
        }
    }
}
