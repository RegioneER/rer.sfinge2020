<?php

namespace AttuazioneControlloBundle\Service;

use AnagraficheBundle\Entity\DocumentoPersonale;
use AnagraficheBundle\Entity\Personale;
use AttuazioneControlloBundle\Entity\DocumentoIncrementoOccupazionale;
use AttuazioneControlloBundle\Entity\IncrementoOccupazionale;
use AttuazioneControlloBundle\Entity\Pagamento;
use DocumentoBundle\Entity\DocumentoFile;
use RichiesteBundle\Entity\Proponente;


interface IGestoreIncrementoOccupazionale
{
    /**
     * @param Pagamento $pagamento
     * @return mixed
     */
    public function dettaglioIncrementoOccupazionale(Pagamento $pagamento);

    /**
     * @param Pagamento $pagamento
     * @param Proponente $proponente
     * @param string $tipo_documento
     * @return mixed
     */
    public function caricaDocumentoIncrementoOccupazionale(Pagamento $pagamento, Proponente $proponente, string $tipo_documento);

    /**
     * @param IncrementoOccupazionale $incrementoOccupazionale
     * @param DocumentoFile $documentoFile
     * @return mixed
     */
    public function eliminaDocumentoIncrementoOccupazionaleDM10(IncrementoOccupazionale $incrementoOccupazionale, DocumentoFile $documentoFile);

    /**
     * @param DocumentoIncrementoOccupazionale $documentoIncrementoOccupazionale
     * @return mixed
     */
    public function eliminaDocumentoIncrementoOccupazionale(DocumentoIncrementoOccupazionale $documentoIncrementoOccupazionale);

    /**
     * @param Pagamento $pagamento
     * @return mixed
     */
    public function validaIncrementoOccupazionale(Pagamento $pagamento);

    /**
     * @param Pagamento $pagamento
     * @return mixed
     */
    public function aggiungiNuovoDipendente(Pagamento $pagamento);

    /**
     * @param Personale $nuovoDipendente
     * @return mixed
     */
    public function modificaNuovoDipendente(Personale $nuovoDipendente);

    /**
     * @param Personale $nuovoDipendente
     * @return mixed
     */
    public function eliminaNuovoDipendente(Personale $nuovoDipendente);

    /**
     * @param Pagamento $pagamento
     * @param Personale $personale
     * @param string $tipo_documento
     * @return mixed
     */
    public function caricaDocumentoPersonaleIncrementoOccupazionale(Pagamento $pagamento, Personale $personale, string $tipo_documento);
    
    /**
     * @param DocumentoPersonale $documentoPersonale
     * @return mixed
     */
    public function eliminaDocumentoPersonaleIncrementoOccupazionale(DocumentoPersonale $documentoPersonale);
}
